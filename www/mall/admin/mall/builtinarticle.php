<?php

/**
 * ECMALL: 内置文章管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: builtinarticle.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/manager/mng.article.php');
require_once(ROOT_PATH. '/includes/manager/mng.file.php');
require_once(ROOT_PATH. '/includes/models/mod.article.php');

class BuiltInArticleController extends ControllerBackend
{
    var $template_dir = '';

    function __construct($act)
    {
        $this->BuiltInArticleController($act);
    }

    function BuiltInArticleController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }

        parent::__construct($act);
    }

    /**
     * 查看文章列表
     *
     * @return  void
     */
    function view()
    {
        $this->log_item = false;

        $art_mng    = new ArticleManager($_SESSION['store_id']);
        $page       = $this->get_page();

        $condition  = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['built_in'] = true;

        $res  = $art_mng->get_list($page, $condition);
        $res['data'] = deep_local_date($res['data'], 'add_time', $this->conf('mall_time_format_complete'));

        $this->assign('list', $res);
        $this->assign('article_stats',  $this->str_format('builtinarticle_stats', $res['info']['rec_count']));

        $this->display('builtinarticle.view.html', 'mall');
    }

    /**
     * 编辑文章
     *
     * @author  liupeng
     * @return  void
     */
    function edit()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $fm = new FileManager($_SESSION['store_id']);

        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app=builtinartilce");
        }

        $art_id = intval($_REQUEST["id"]);
        $this->log_item = $art_id; //日志

        $art = new Article($art_id);
        $info = $art->get_info();

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            $attachments = $fm->get_list_by_item($art_id, 'article');

            $this->build_editor('content', $info['content'], '80%', '300', 'rich_editor', 'true', $attachments, 0);
            $this->assign('info',           $info);
            $this->display('builtinarticle.detail.html', 'mall');
        }
        else
        {
            $art_mng = new ArticleManager($_SESSION['store_id']);
            $this->err = array();

            if (empty($this->err))
            {
                $info = array();
                $info['cate_id']      = trim($_POST['cate_id']);
                $info['content']      = $this->get_editor_content($_POST['content'], $info['editor_type']);

                $art = new Article($art_id);
                $art->update($info);
                if (count($_POST['file_id']) > 0)
                {
                    $fm->update_item_id($_POST['file_id'], 'article', $art_id);
                }

                $this->show_message($this->str_format('edit_succeed', $info['title']),$this->lang('view_builtinarticle'), 'admin.php?app=builtinarticle&amp;act=view');
                return;
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
     * AJAX更新字段值
     *
     * @author  liupeng
     * @return  void
     */
    function modify()
    {
        $id = intval($_REQUEST['id']);
        $this->log_item = $id;

        $allowed_column = array('title');

        if (!in_array($_REQUEST['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }

        if ($_REQUEST['column'] == 'title')
        {
            $manager = new ArticleManager($_SESSION['store_id']);

            $art_id = $manager->get_article_id($_GET['title']);

            if ($id != $art_id && $art_id != 0)
            {
                $this->json_error($this->str_format('article_exists', $_GET['value']));
                return;
            }
        }
        $this->_modify("Article", $_GET, 'ok');
    }
};

?>