<?php

/**
 * ECMALL: 文章管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.article.php 6084 2008-11-19 09:54:38Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/manager/mng.article.php');
require_once(ROOT_PATH. '/includes/manager/mng.file.php');
require_once(ROOT_PATH. '/includes/models/mod.article.php');

class ArticleController extends ControllerBackend
{
    var $template_dir = '';
    var $app_name     = 'article';

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
        if ($_SESSION['store_id'] > 0)
        {
            $this->template_dir = 'store';
            $this->app_name     = 'store_nav';
        }
        parent::__construct($act);
    }
    /**
     * 查看文章列表
     *
     * @author  wj
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $art_mng = new ArticleManager($_SESSION['store_id']);

        $cate_list = $this->_get_options();

        $page = $this->get_page();
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if (isset($_GET['is_top']))
        {
            $condition["is_top"] = 1;
        }

        if (isset($_GET['if_show']))
        {
            $condition['if_show'] = 1;
        }

        if (isset($_GET['cate_id']))
        {
            $condition['cate_id'] = intval($_GET['cate_id']);
        }

        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] = trim($_GET['keywords']);
        }
        $res  = $art_mng->get_list($page, $condition);

        $res['data'] = deep_local_date($res['data'], 'add_time', $this->conf('mall_time_format_complete'));

        $this->assign('cate_list',      $cate_list);
        $this->assign('list',           $res);
        $this->assign('condition',      $condition);
        $this->assign('article_stats',  $this->str_format('article_stats', $res['info']['rec_count'], $res['info']['page_count']));
        $this->assign('url_format',     "admin.php?app={$this->app_name}&amp;act=view&amp;page=%d");

        $this->display('article.view.html', $this->template_dir);
    }

    /**
     * 获取分类列表的options
     *
     * @param   string  $value
     *
     * @return  string
     */
    function _get_options()
    {
        require_once(ROOT_PATH. '/includes/manager/mng.article_cate.php');
        $mng = new ArticleCateManager(0);
        return $mng->get_options();
    }

    /**
     * 添加文章
     *
     * @author  liupeng
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            require_once(ROOT_PATH. '/includes/manager/mng.article_cate.php');
            $acm = new ArticleCateManager(0);
            $cate_list = $this->_get_options();

            $this->build_editor('content', '', '80%', '300');
            $this->assign('cate_list', $cate_list);
            $this->display('article.detail.html', $this->template_dir);
        }
        else
        {
            //有上传文件时检查附件数量
            if ($_SESSION['store_id'] > 0 &&  $this->has_uploaded_file())
            {
                if (($msg = $this->check_store_file_count($_SESSION['store_id'])))
                {
                    $this->show_message($msg);
                    return;
                }
            }

            $art_mng = new ArticleManager($_SESSION['store_id']);

            $this->err = array();
            if (empty($_POST['title']))
            {
                $this->err[] = $this->lang('title_not_empty');
            }
            if ($art_mng->exists($_POST['title']))
            {
                $this->err[] = $this->str_format('article_exists', $_POST['title']);
            }

            require_once(ROOT_PATH. '/includes/cls.uploader.php');

            if (empty($this->err))
            {
                $info = array();
                $info['cate_id']      = trim($_POST['cate_id']);
                $info['title']        = trim($_POST['title']);
                $info['content']      = $this->get_editor_content($_POST['content']);
                $info['editor_type']  = $this->conf('mall_editor_type');
                $info['is_top']       = trim($_POST['is_top']);
                $info['if_show']      = trim($_POST['if_show']);
                $info['link']         = trim($_POST['link']);
                $info['add_time']     = gmtime();

                $res = $art_mng->add($info);
                if ($res)
                {
                    $art_id = $GLOBALS['db']->insert_id();
                    if (count($_POST['file_id']) > 0)
                    {
                        $fm = new FileManager($_SESSION['store_id']);
                        $fm->update_item_id($_POST['file_id'], 'article', $art_id);
                    }
                    $this->log_item = $art_id; //日志
                    $this->show_message($this->str_format('add_succeed', $info['title']), 'back_view_article', 'admin.php?app=' . $this->app_name . '&amp;act=view', 'go_on_add', 'admin.php?app=' . $this->app_name . '&amp;act=add');
                }
            }
            else
            {
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

    /**
     * 编辑文章
     *
     * @author Garbin
     * @return  void
     */
    function edit()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $fm = new FileManager($_SESSION['store_id']);

        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app={$this->app_name}");
        }

        $art_id = intval($_REQUEST["id"]);
        $this->log_item = $art_id; //日志
        $art = new Article($art_id, $_SESSION['store_id']);
        $info = $art->get_info();

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $attachments = $fm->get_list_by_item($art_id, 'article');
            $this->build_editor('content', $info['content'], '80%', '300', 'rich_editor', 'true', $attachments, $info['editor_type']);
            $this->assign('cate_list',      $this->_get_options());
            $this->assign('info',           $info);
            $this->display('article.detail.html', $this->template_dir);
        }
        else
        {
            //有上传文件时检查附件数量
            if ($_SESSION['store_id'] > 0 &&  $this->has_uploaded_file())
            {
                if (($msg = $this->check_store_file_count($_SESSION['store_id'])))
                {
                    $this->show_message($msg);
                    return;
                }
            }
            $art_mng = new ArticleManager($_SESSION['store_id']);
            $this->err = array();
            if (empty($_POST['title']))
            {
                $this->err[] = $this->lang('title_not_empty');
            }

            $id = $art_mng->get_article_id($_POST['title']);
            if ($art_mng->exists($_POST['title'], $art_id))
            {
                $this->err[] = $this->str_format('article_exists', $_POST['title']);
            }

            require_once(ROOT_PATH. '/includes/cls.uploader.php');

            $uploader = new Uploader('data/user_files', 'image', $this->conf('mall_max_file'));
            if (!empty($_FILES['upload_file']['name']))
            {
                if ($uploader->upload_files($_FILES['upload_file']))
                {
                    $files = $uploader->success_list;
                    $_POST['file_url'] = $files[0]['target'];
                }
                else
                {
                    $this->err[] = $uploader->err;
                }
            }

            if (empty($this->err))
            {
                $fm->update_item_id($_POST['file_id'], 'article', $art_id);

                $data = array();
                $data['cate_id']      = trim($_POST['cate_id']);
                $data['title']        = trim($_POST['title']);
                $data['content']      = $this->get_editor_content($_POST['content']);
                $data['editor_type']  = $this->conf('mall_editor_type');
                $data['is_top']       = trim($_POST['is_top']);
                $data['if_show']      = trim($_POST['if_show']);
                $data['link']         = trim($_POST['link']);

                $art->update($data);

                $this->show_message($this->str_format('edit_succeed', $info['title']),$this->lang('back_view_article'), 'admin.php?app=' . $this->app_name . '&amp;act=view');
            }
            else
            {
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

    /**
     * 删除文章
     *
     * @return  void
     */
    function drop()
    {
        if (empty($_GET['id']))
        {
            $this->redirect("admin.php?app={$this->app_name}");
            return;
        }
        $id = $_GET['id'];
        $this->log_item = $id;
        $art = new Article($id, $_SESSION['store_id']);
        $art->drop();
        $patterns[]     = '/act=\w+/i';
        $patterns[]     = '/[&|\?]?param=\w+/i';
        $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
        $replacement[]  = 'act=view';
        $replacement[]  = '';
        $replacement[]  = '';
        $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed', 'back_list', $location);
    }

    /**
     * 文章批量操作
     *
     * @return  void
     */
    function batch()
    {
        $type   = trim($_GET['param']);
        $in     = trim($_GET['ids']);
        if (empty($in))
        {
            $this->show_warning('batch_not_selected');
            return;
        }
        else
        {
            $manager = new ArticleManager($_SESSION['store_id']);
            $res     = $manager->batch($type, $in);

            if ($res)
            {
                $this->log_item = $in;
                $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
                $this->show_message('batch_successfully', 'back_list', $location);
            }
        }
    }

    /**
     * AJAX更新字段值
     *
     * @author wj
     * @param void
     * @return  void
     */
    function modify()
    {
        $id = intval($_GET['id']);
        $this->log_item = $id;

        $allowed_column = array('title', 'cate_id', 'is_top', 'sort_order', 'if_show');

        if (!in_array($_GET['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }

        if ($_GET['column'] == 'title')
        {
            $manager = new ArticleManager($_SESSION['store_id']);

            if ($manager->exists($_GET['value'], $id))
            {
                $this->json_error($this->str_format('article_exists', $_GET['value']));
                return;
            }
        }

        $this->_modify("Article", $_GET, 'ok');
    }

    /**
     *    检查标题是否存在
     *
     *    @author    Garbin
     *    @param     param
     *    @return    void
     */
    function check_title()
    {
        $this->logger = false;
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $title = empty($_GET['title']) ? '' : trim($_GET['title']);
        $manager = new ArticleManager($_SESSION['store_id']);
        if ($manager->exists($title, $id))
        {
            $this->json_error($this->str_format('article_exists', $title));
        }
        else
        {
            $this->json_result();
        }
    }
};
?>
