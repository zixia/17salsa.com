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
 * $Id: ad.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/manager/mng.ad_position.php');
require_once(ROOT_PATH. '/includes/manager/mng.ad.php');
require_once(ROOT_PATH. '/includes/models/mod.ad.php');
require_once(ROOT_PATH. '/includes/cls.uploader.php');

class AdController extends ControllerBackend
{
    function __construct($act)
    {
        $this->AdController($act);
    }

    function AdController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看广告列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false; //不需要记录日志
        $mng = new AdManager();
        $adp_mng = new AdPositionManager();

        $res  = $mng->get_list($this->get_page());

        $res['data'] = deep_local_date($res['data'], 'start_time', 'Y-m-d');
        $res['data'] = deep_local_date($res['data'], 'end_time', 'Y-m-d');

        $this->assign('list', $res);
        $this->assign('position_list', $adp_mng->get_options());
        $this->assign('ad_stats', $this->str_format('ad_stats', $res['info']['rec_count'], $res['info']['page_count']));

        $this->display('ad.view.html', 'mall');
    }


    /**
     * 添加图片
     *
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $adp_mng = new AdPositionManager();
            $this->assign('position_list', $adp_mng->get_options());
            $this->assign('ad_type', $this->lang("ad_type_options"));
            if (isset($_GET['position_id']))
            {
                $this->assign('info', array('position_id'=>$_GET['position_id']));
            }
            $this->display('ad.detail.html', 'mall');
        }
        else
        {
            $this->err = array();

            if (empty($_POST['ad_name']))
            {
                $this->err[] = $this->lang('name_not_empty');
            }

            if($_POST['file_radio'] == '1')
            {
                if (intval($_POST['ad_type']) == FALSH_AD)
                {
                    $uploader = new Uploader('data/user_files', 'flash', $this->conf('mall_max_file'));
                }
                else
                {
                    $uploader = new Uploader('data/user_files', 'image', $this->conf('mall_max_file'));
                }

                if (!empty($_FILES['file']['name']))
                {
                    if ($uploader->upload_files($_FILES['file']))
                    {
                        $files = $uploader->success_list;
                        $_POST['ad_code'] = $files[0]['target'];
                    }
                    else
                    {
                        $this->err[] = $uploader->err;
                    }
                }
            }
            elseif ($_POST['file_radio'] == '0')
            {
                $_POST['ad_code'] = $_POST['file'];
            }

            if (empty($this->err))
            {
                $info = array();
                $info['ad_name'] = trim($_POST['ad_name']);
                $info['position_id'] = trim($_POST['position_id']);
                $info['ad_type'] = trim($_POST['ad_type']);
                $info['enabled'] = trim($_POST['ad_enabled']);
                $info['start_time'] = gmstr2time(trim($_POST['start_date']));
                $info['end_time']   = gmstr2time(trim($_POST['end_date']));
                $info['ad_link'] = trim($_POST['ad_link']);
                $info['ad_code'] = trim($_POST['ad_code']);
                $mng = new AdManager(0);

                $res = $mng->add($info);
                if ($res)
                {
                    $this->log_item = $GLOBALS['db']->insert_id(); // 日志
                    $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
                    $this->show_message('add_succeed', 'back_list', $location);
                    return;
                }
            }
            else
            {
                $this->logger = false;
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

    /**
     * 编辑广告
     *
     * @return  void
     */
    function edit()
    {
        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app=ad");
        }
        $id = intval($_REQUEST["id"]);
        $ad = new Ad($id);
        $info = $ad->get_info();

        $this->log_item = $id;
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //不记录日志

            $info['start_time'] = local_date('Y-m-d', $info['start_time']);
            $info['end_time']   = local_date('Y-m-d', $info['end_time']);

            $adp_manager = new AdPositionManager();
            $this->assign('position_list', $adp_manager->get_options());

            $this->assign('info', $info);
            $this->assign('act',  'edit');
            $ad_type_options = $this->lang("ad_type_options");

            $arr = $this->lang("ad_type_options");
            $this->assign('ad_type', $arr[$info['ad_type']]);
            $this->display('ad.detail.html', 'mall');
        }
        else
        {
            $this->err = array();

            if (empty($_POST['ad_name']))
            {
                $this->err[] = $this->lang('name_not_empty');
            }

            if($_POST['file_radio'] == '1')
            {
                require_once(ROOT_PATH. '/includes/cls.uploader.php');

                if (intval($info['ad_type']) == FALSH_AD)
                {
                    $uploader = new Uploader('data/user_files', 'flash', $this->conf('mall_max_file'));
                }
                else
                {
                    $uploader = new Uploader('data/user_files', 'image', $this->conf('mall_max_file'));
                }

                if (!empty($_FILES['file']['name']))
                {
                    if ($uploader->upload_files($_FILES['file']))
                    {
                        $files = $uploader->success_list;
                        $_POST['ad_code'] = $files[0]['target'];
                    }
                    else
                    {
                        $this->err[] = $uploader->err;
                    }
                }
            }
            elseif ($_POST['file_radio'] == '0')
            {
                $_POST['ad_code'] = $_POST['file'];
            }

            if (empty($this->err))
            {
                $info = array();
                $info['ad_name']     = trim($_POST['ad_name']);
                $info['position_id'] = trim($_POST['position_id']);

                $info['enabled']     = trim($_POST['ad_enabled']);
                $info['start_time']  = gmstr2time(trim($_POST['start_date']));
                $info['end_time']    = gmstr2time(trim($_POST['end_date']));
                $info['ad_link']     = trim($_POST['ad_link']);
                $info['ad_code']     = trim($_POST['ad_code']);

                $res = $ad->update($info);
                if ($res)
                {
                    $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
                    $this->show_message('edit_succeed', 'back_list', $location);
                    return;
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
     * 删除广告
     *
     * @return  void
     */
    function drop()
    {
        if (empty($_GET['id']))
        {
            $this->redirect("admin.php?app=ad");
            return;
        }
        $id = $_GET['id'];
        $this->log_item = $id;  //日志

        $adp = new Ad($id);
        $ad_info = $adp->get_info();
        $filename = $ad_info['ad_code'];
        if (substr($filename, 0, 16) == 'data/user_files/' && file_exists(ROOT_PATH . '/' . $filename))
        {
            @unlink(ROOT_PATH . '/' . $filename);
        }
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
        $allowed_column = array('position_id', 'ad_name', 'enabled', 'start_time', 'end_time'); // 允许编辑的字段名

        if (!in_array($_REQUEST['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }

        if ($_GET['column'] == 'start_time')
        {
            $_GET['value'] = local_strtotime($_GET['value']);
        }

        if ($_GET['column'] == 'end_time')
        {
            $_GET['value'] = local_strtotime($_GET['value']);
        }

        $this->_modify('Ad', $_GET, 'error');
    }

};

?>