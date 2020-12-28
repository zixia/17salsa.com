<?php

/**
 * ECMALL: ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * վ�ڿ�Ѷ
     *
     * @author wj
     * @return void
     */
    function site_news()
    {
        /* ���� */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($id == 0)
        {
            /* ȡ�ÿ�Ѷ�б� */
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

            /* ��ǰλ�ú�title */
            $this->assign('location_data', array(
                array('name' => $this->lang('site_news'))
            ));
            $this->set_title(array($article['site_news']));
            $this->assign('title', $this->lang('site_news'));
        }
        else
        {
            /* ȡ�ÿ�Ѷ���� */
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

            /* ��ǰλ�ú�title */
            $this->assign('location_data', array(
                array('name' => $this->lang('site_news'), 'url' => 'index.php?app=article&amp;act=site_news'),
                array('name' => $article['title'])
            ));
            $this->set_title(array($this->lang('site_news'), $article['title']));
        }

        /* ȡ��ͼƬ�Ƽ���Ʒ */
        $_GET['sort'] = 'sort_weighing';
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager(0);
        $condition = array(
            'is_on_sale'    => 1,
            'is_mi_best'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('image_goods', $mng_goods->get_list(1, $condition, 6));

        /* ȡ�������Ƽ���Ʒ */
        $condition = array(
            'is_on_sale'    => 1,
            'is_mw_best'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('words_goods', $mng_goods->get_list(1, $condition, 6));

        /* ȡ��������Ʒ */
        $condition = array(
            'is_on_sale'    => 1,
            'is_m_hot'    => 1,
            'sell_able'     => 1,
        );
        $this->assign('sales_goods', $mng_goods->get_list(1, $condition, 10));

        $this->display('site_news', 'mall');
    }

    /**
     * �鿴���õ���������
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

        /* ȡ��ͼƬ�Ƽ���Ʒ */
        $_GET['sort'] = 'sort_weighing';
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager(0);
        $condition = array(
            'is_on_sale'    => 1,
            'is_mi_best'    => 1
        );
        $this->assign('image_goods', $mng_goods->get_list(1, $condition, 6));

        /* ȡ�������Ƽ���Ʒ */
        $condition = array(
            'is_on_sale'    => 1,
            'is_mw_best'    => 1
        );
        $this->assign('words_goods', $mng_goods->get_list(1, $condition, 6));

        /* ȡ��������Ʒ */
        $condition = array(
            'is_on_sale'    => 1,
            'is_m_hot'    => 1
        );
        $this->assign('sales_goods', $mng_goods->get_list(1, $condition, 10));

        /* ��ǰλ�� */
        $this->assign('location_data', array(
            array('name' => $article['title'])
        ));

        $this->set_title(array($article['title']));

        $this->display('site_news', 'mall');
    }

    /**
     * ��������
     */
    function help_center()
    {
        /* ���� */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $cache_id = 'help_center_' . $id;
        if (!$this->is_cached($cache_id))
        {
            /* ȡ�������б� */
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

            /* ȡ���������� */
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

            /* ��ǰλ�ú�title */
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
