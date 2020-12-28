<?php

/**
 * ECMALL: 文章
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: article.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/lib.editor.php');

class ArticleController extends ControllerFrontend
{
    var $_allowed_actions = array('site_news', 'builtin', 'help_center');

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->ArticleController($act);
    }

    function ArticleController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 站内快讯
     *
     * @author wj
     * @return void
     */
    function site_news()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($id == 0)
        {
            /* 取得快讯列表 */
            include_once(ROOT_PATH . '/includes/manager/mng.article.php');
            $mng_article = new ArticleManager(0);
            $condition = array(
                'cate_id'   => ARC_NEWS,
                'if_show'   => 1
            );
            $_GET['sort'] = 'add_time';
            $_GET['order'] = 'desc';
            $article_list = $mng_article->get_list($this->get_page(), $condition, 20);
            $this->assign('article_list', $article_list);

            /* 当前位置和title */
            $this->assign('location_data', array(
                array('name' => $this->lang('site_news'))
            ));
            $this->set_title(array($article['site_news']));
            $this->assign('title', $this->lang('site_news'));
        }
        else
        {
            /* 取得快讯详情 */
            include_once(ROOT_PATH . '/includes/models/mod.article.php');
            $mod_article = new Article($id, 0);
            $article = $mod_article->get_info();
            if (empty($article))
            {
                $this->show_warning('article_not_exists');
                return;
            }
            if ($article['editor_type'] == 0)
            {
                $article['content'] = Editor::parse($article['content']);
            }

            $this->assign('article', $article);

            /* 当前位置和title */
            $this->assign('location_data', array(
                array('name' => $this->lang('site_news'), 'url' => 'index.php?app=article&amp;act=site_news'),
                array('name' => $article['title'])
            ));
            $this->set_title(array($this->lang('site_news'), $article['title']));
        }

        /* 取得图片推荐商品 */
        $_GET['sort'] = 'sort_weighing';
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager(0);
        $condition = array(
            'is_on_sale'    => 1,
            'is_mi_best'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('image_goods', $mng_goods->get_list(1, $condition, 6));

        /* 取得文字推荐商品 */
        $condition = array(
            'is_on_sale'    => 1,
            'is_mw_best'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('words_goods', $mng_goods->get_list(1, $condition, 6));

        /* 取得热销商品 */
        $condition = array(
            'is_on_sale'    => 1,
            'is_m_hot'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('sales_goods', $mng_goods->get_list(1, $condition, 10));

        $this->display('site_news', 'mall');
    }

    /**
     * 查看内置的文章内容
     *
     * @return  void
     */
    function builtin()
    {
        $code = trim($_GET['code']);

        if (empty($code))
        {
            $this->show_warning('article_not_exists');

            return;
        }

        include_once(ROOT_PATH . '/includes/models/mod.article.php');
        $mod_article = new Article(0, 0, $code);
        $article = $mod_article->get_info();
        if (empty($article))
        {
            $this->show_warning('article_not_exists');

            return;
        }

        include_once(ROOT_PATH . '/includes/lib.editor.php');
        if ($article['editor_type'] == 0)
        {
            $article['content'] = Editor::parse($article['content']);
        }

        $this->assign('article',    $article);

        /* 取得图片推荐商品 */
        $_GET['sort'] = 'sort_weighing';
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager(0);
        $condition = array(
            'is_on_sale'    => 1,
            'is_mi_best'    => 1
        );
        $this->assign('image_goods', $mng_goods->get_list(1, $condition, 6));

        /* 取得文字推荐商品 */
        $condition = array(
            'is_on_sale'    => 1,
            'is_mw_best'    => 1
        );
        $this->assign('words_goods', $mng_goods->get_list(1, $condition, 6));

        /* 取得热销商品 */
        $condition = array(
            'is_on_sale'    => 1,
            'is_m_hot'    => 1
        );
        $this->assign('sales_goods', $mng_goods->get_list(1, $condition, 10));

        /* 当前位置 */
        $this->assign('location_data', array(
            array('name' => $article['title'])
        ));

        $this->set_title(array($article['title']));

        $this->display('site_news', 'mall');
    }

    /**
     * 帮助中心
     */
    function help_center()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $cache_id = 'help_center_' . $id;
        if (!$this->is_cached($cache_id))
        {
            /* 取得文章列表 */
            include_once(ROOT_PATH . '/includes/manager/mng.article.php');
            $mng_article = new ArticleManager(0);
            $condition = array(
                'cate_id'   => ARC_HELP,
                'if_show'   => 1
            );
            $_GET['sort'] = 'is_top';
            $_GET['order'] = 'asc';
            $article_list = $mng_article->get_list(1, $condition, 1000);
            $this->assign('article_list', $article_list);

            /* 取得文章内容 */
            $article = array();
            if ($article_list['info']['rec_count'] > 0)
            {
                if ($id == 0)
                {
                    $cur_article = $article_list['data'][0];
                }
                else
                {
                    foreach ($article_list['data'] as $article)
                    {
                        if ($article['article_id'] == $id)
                        {
                            $cur_article = $article;
                            break;
                        }
                    }
                }

                if ($cur_article['editor_type'] == 0)
                {
                    $cur_article['content'] = Editor::parse($cur_article['content']);
                }
                $this->assign('cur_article', $cur_article);
            }

            /* 当前位置和title */
            $location_data = array(array('name' => $this->lang('help_center')));
            if ($cur_article)
            {
                $location_data[] = array('name' => $cur_article['title']);
            }
            $this->assign('location_data', $location_data);

            $this->set_title(array($this->lang('help_center'), $cur_article['title']));
        }

        $this->display('help_center', 'mall', $cache_id);
    }

}

?>
