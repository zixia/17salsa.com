<?php

/**
 * ECMALL: 广告位管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ad_position.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/manager/mng.ad_position.php');
require_once(ROOT_PATH. '/includes/models/mod.ad_position.php');

class Ad_PositionController extends ControllerBackend
{
    function __construct($act)
    {
        $this->Ad_PositionController($act);
    }

    function Ad_PositionController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::ControllerBackend($act);
    }

    /**
     * 查看文章列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false; //不记录日志
        $mng = new AdPositionManager(0);

        $res  = $mng->get_list($this->get_page());

        $res['data'] = deep_local_date($res['data'], 'add_time', $this->conf('mall_time_format_complete'));

        $this->assign('list', $res);
        $this->assign('position_stats',  $this->str_format('position_stats', $res['info']['rec_count'], $res['info']['page_count']));

        $this->display('ad.position.view.html', 'mall');
    }


    /**
     * 添加广告位
     *
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //不记录日志
            $this->display('ad.position.detail.html', 'mall');
        }
        else
        {
            $this->err = array();
            if (empty($_POST['position_name']))
            {
                $this->err[] = $this->lang('name_not_empty');
            }

            if (empty($this->err))
            {
                $info = array();
                $info['position_name'] = trim($_POST['position_name']);
                $info['position_desc'] = trim($_POST['position_desc']);
                $info['height']        = trim($_POST['height']);
                $info['width']         = trim($_POST['width']);
                $info['position_desc'] = trim($_POST['desc']);

                $mng = new AdPositionManager(0);
                $res = $mng->add($info);
                if ($res)
                {
                    $this->log_item = $GLOBALS['db']->insert_id();
                    $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
                    $this->show_message('add_succeed', 'back_list', $location);
                    return;
                }
            }
            else
            {
                $this->logger = false; //不记录日志
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

    /**
     * 编辑广告位
     *
     * @return  void
     */
    function edit()
    {
        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app=article");
        }

        $pos_id = intval($_REQUEST["id"]);
        $ad_pos = new AdPosition($pos_id);
        $this->log_item = $pos_id; //日志

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //不记录日志
            $info = $ad_pos->get_info();
            $this->assign('info',        $info);
            $this->display('ad.position.detail.html', 'mall');
        }
        else
        {
            $this->err = array();

            if (empty($_POST['position_name']))
            {
                $this->err[] = $this->lang('name_not_empty');
            }

            if (empty($this->err))
            {
                $info = array();
                $info['position_name'] = trim($_POST['position_name']);
                $info['position_desc'] = trim($_POST['position_desc']);
                $info['height']        = trim($_POST['height']);
                $info['width']         = trim($_POST['width']);
                $info['position_desc'] = trim($_POST['desc']);
                $ad_pos->update($info);
                $this->show_message($this->str_format('edit_succeed', $info['position_name']));
                return;
            }
            else
            {
                $this->logger = false; //不记录日志
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

     /**
     * 删除广告位
     *
     * @return  void
     */
    function drop()
    {
        if (empty($_GET['id']))
        {
            $this->redirect("admin.php?app=article");
            return;
        }
        $id = $_GET['id'];

        $this->log_item = $id; //日志
        $adp = new AdPosition($id);
        $adp->drop();

        $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed', 'back_list', $location);
        return;
    }


    /**
     * AJAX更新字段值
     *
     * @return  void
     */
    function modify()
    {
        if (empty($_GET['id']))
        {
            $this->log_item = $_GET['id'];
        }

        $allowed_column = array('position_name', 'height', 'width'); // 允许编辑的字段名

        if (!in_array($_REQUEST['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }

        $this->_modify('AdPosition', $_GET, 'error');
    }

};
?>