<?php

/**
 * ECMALL: 管理员帐号维护模块
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.admin.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.admin.php');
include_once(ROOT_PATH . '/includes/models/mod.adminuser.php');

class AdminController extends ControllerBackend
{
    var $manager = null;
    var $mod = null;

    function __construct($act)
    {
        $this->AdminController($act);
    }

    function AdminController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        $this->manager = new AdminManager($_SESSION['store_id']);
        if ($act == 'edit' && $user_id = intval($_GET['user_id']))
        {
            $this->mod = new AdminUser($user_id, $_SESSION['store_id']);
        }
        parent::__construct($act);
    }

    /**
     * 查看管理员帐号列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $condition = array();
        $res  = $this->manager->get_list($this->get_page(), $condition);

        $res['data'] = deep_local_date($res['data'], 'add_time', $this->conf('mall_time_format_complete'));
        $this->assign('list', $res);
        $this->assign('admin_stats',  $this->str_format('admin_stats', $res['info']['rec_count'], $res['info']['page_count']));
        $this->assign('url_format', "admin.php?app=admin&amp;act=view&amp;page=%d");
        $this->display('admin.view.html');
    }

    /**
     * 查看管理员操作日志
     *
     * @author  Xzf
     * @return  void
     */
    function logs()
    {
        $this->logger = false;
        $lang_delete_logs = $this->lang('delete_logs_by');
        $delete_logs_by = array('all'=>$lang_delete_logs['all'], 'week'=>$lang_delete_logs['week'], 'month'=>$lang_delete_logs['month'], 'halfyear'=>$lang_delete_logs['halfyear'], 'year'=>$lang_delete_logs['year']);
        include_once(ROOT_PATH. '/includes/manager/mng.admin_logs.php');
        $mng = new AdminLogManager($_SESSION['store_id']);
        $res = (isset($_POST['username'])) ?
            $mng->get_list($this->get_page(), array('username' => trim($_POST['username']))) :
            $mng->get_list($this->get_page());
        $res['data'] = deep_local_date($res['data'], 'execution_time', $this->conf('mall_time_format_complete'));

        /* 汉化日志 */
        Language::load_lang(lang_file('admin/admin'));
        foreach ($res['data'] as $ki => $i)
        {
            $res['data'][$ki]['application'] = $this->lang("log_" . $res['data'][$ki]['application']);
            $res['data'][$ki]['action'] = $this->lang("log_" . $res['data'][$ki]['action']);
        }
        $this->assign('log_stats',  $this->str_format('log_stats', $res['info']['rec_count'], $res['info']['page_count']));
        $this->assign('delete_logs_by',       $delete_logs_by);
        $this->assign('selected',           'month');
        $this->assign('list',       $res);
        $this->assign('url_format', "admin.php?app=admin&amp;act=logs&amp;page=%d");
        $this->display('admin.logs.html');
    }

    /**
     * 添加管理员
     *
     * @return  void
     */
    function add()
    {
        $this->process_admin('add');
    }

    /**
     * 编辑管理员
     *
     * @return  void
     */
    function edit()
    {
        if (empty($_GET['user_id'])) $this->log_item = $_GET['user_id'];
        $this->process_admin('update');
    }

    /**
     * 处理一个管理员，添加或者编辑
     *
     * @author      wj
     * @params      string      $act
     *
     * @return void
     */
    function process_admin($act)
    {
        if ($act == 'update')
        {
            $user_id = trim($_GET['user_id']);
            $this->log_item = $user_id;
            if ($user_id != intval($user_id))
            {
                $this->show_warning('user_id_illegal');
                return;
            }

            if (!$admin = $this->manager->get_info($user_id))
            {
                $this->show_warning('the_admin_not_exists');
                return false;
            }
            $this->assign('admin_info', $admin);
        }
        include(ROOT_PATH . '/admin/' . ($_SESSION['store_id'] ? 'store' : 'mall') . '/inc.privilege.php');
        if ($_POST['value_submit'])
        {
            $real_name = htmlspecialchars(trim($_POST['real_name']));
            if (empty($real_name))
            {
                $this->show_warning('real_name_empty');
                return false;
            }
            if ($act == 'add')
            {
                $user_name = trim($_POST['user_name']);
                if (empty($user_name))
                {
                    $this->show_warning('user_name_empty');
                    return false;
                }
                include(ROOT_PATH . '/includes/manager/mng.user.php');
                $user_manager = new UserManager($_SESSION['store_id']);
                if (!$user_id = $user_manager->get_id_by_name($user_name))
                {
                    $this->show_warning('user_not_exists');
                    return false;
                }
                if ($this->manager->get_by_id($user_id))
                {
                    $admin_mod = new AdminUser($user_id);
                    $admin_info = $admin_mod->get_info();
                    $msg = $admin_info['store_id'] == 0 ? 'is_mall_admin' : $this->str_format('is_other_store_admin', $admin_info['store_id'], $this->conf('store_name', $admin_info['store_id']));
                    $this->show_warning($msg);
                    return false;
                }
                require_once(ROOT_PATH . '/includes/models/mod.user.php');
                $user_mod = new User($user_id);
                if (!$user_mod->get_info())
                {
                    $arr = uc_call('uc_get_user', $user_name);
                    $user_mod->activate(array('uid' => $arr[0], 'username' => $arr[1], 'email' => $arr[2]));
                }

                if ($_SESSION['store_id'])
                {
                    include_once(ROOT_PATH . '/includes/models/mod.user.php');
                    $add_admin = new User($user_id);
                    $admin_info = $add_admin->get_info();

                    $admin_url = site_url() . '/admin.php';
                    $store_name = $this->conf(($_SESSION['store_id'] ? 'store_name' : 'mall_name'), $_SESSION['store_id']);
                    $store_url = site_url() . ($_SESSION['store_id'] ? '/index.php?app=store&store_id=' . $_SESSION['store_id'] : '');
                    $cur_time = time();
                    $auth_key = md5($user_id . $_SESSION['store_id'] . $cur_time . $real_name . ECM_KEY);
                    $accept_url = site_url() . '/index.php?app=member&act=active_admin&user_id=' . $user_id . '&store_id=' . $_SESSION['store_id'] . '&time=' . $cur_time . '&real_name=' . urlencode($real_name) . '&auth_key=' . $auth_key;
                    $values = array();
                    $values['store_name'] = $store_name;
                    $values['store_url'] = $store_url;
                    $values['accept_url'] = $accept_url;
                    $values['admin_url'] = $admin_url;
                    $values['user_name'] = $admin_info['user_name'];

                    /* 先发送pm */
                    $pm_body = $this->str_format('add_admin_pm_body', $store_url, $store_name, $accept_url, $admin_url);
                    uc_call('uc_pm_send', array(0, $user_id, $this->lang('add_admin_pm_subject'), str_replace('<br>', "\r\n", $pm_body), 1));

                    /* 发送邮件 */
                    $this->send_mail($admin_info['email'], 'add_admin', $values);

                    $this->show_message('add_admin_success', 'return_view', 'admin.php?app=admin&amp;act=view');
                    return true;

                }
                else
                {
                    /* 网站管理员直接添加 */
                    $privilege = $this->process_priv($privilege_item, $_POST['priv']);
                    $res = $this->manager->$act($user_id, $real_name, $privilege);
                    if ($res)
                    {
                        $this->show_message('add_mall_admin_success', 'return_view', 'admin.php?app=admin&amp;act=view');
                        return true;
                    }
                    else
                    {
                        $this->show_warning($this->manager->err);
                        return false;
                    }
                }
            }
            else
            {
                if (empty($this->mod))
                {
                    $this->show_warning('undefined');
                    return false;
                }
                if ($act == 'update' && $admin['privilege'] == 'all')
                {
                    $priv_data = 'all';
                }
                else
                {
                    $privilege = $this->process_priv($privilege_item, $_POST['priv']);
                    $priv_data = implode(',', $privilege);
                }
                $arr = array('real_name' => $real_name, 'privilege' => $priv_data);

                if ($this->mod->update($arr))
                {
                    $this->show_message('edit_admin_success', 'return_view', 'admin.php?app=admin&amp;act=view');
                    return true;
                }
                else
                {
                    $this->show_warning($this->manager->err);
                    return false;
                }
            }

        }
        else
        {
            $this->logger = false;
            $this->assign('act', $act);
            if ($act == 'update')
            {
                $this->assign('admin', $admin);
                if ($user_id != $_SESSION['store_id'] && $admin['privilege'] != 'all')
                {
                    foreach ($privilege_item as $ikey => $value)
                    {
                        foreach ($value as $key => $val)
                        {
                            if (strpos($admin['privilege'], $val['appact']) !== false)
                            {
                                $privilege_item[$ikey][$key]['checked'] = ' checked="checked"';
                            }
                        }
                    }
                }
            }
            $this->assign('priv_items', $privilege_item);
            $this->assign('store_id', $_SESSION['store_id']);
            $this->display('admin.detail.html');
        }
    }

    /**
     * 删除管理员
     *
     * @return void
     */
    function drop()
    {
        $ids = trim($_GET['ids']);
        $this->log_item = $ids;
        if ($this->manager->drop($ids))
        {
            $this->show_message('operate_success', 'back_to_list', 'admin.php?app=admin&act=view');
            return true;
        }
        else
        {
            $this->show_warning($this->manager->err);
            return false;
        }
    }

    /**
     * 处理提交上来的权限
     * @params array $item 权限设置项目
     * @params array $priv 提交上来的值
     *
     * @return array 处理后的权限
     */
    function process_priv($item, $priv)
    {
        $privilege = array();
        foreach ($item as $group => $value)
        {
            foreach ($value as $val)
            {
                if (!empty($priv[$val['title']]))
                {
                    $privilege[] = $val['appact'];
                }
            }
        }
        return $privilege;
    }

    /**
     * 删除指定时间内的日志
     *
     * @author  wj
     * @return  void
     */
    function remove_logs()
    {
        include_once(ROOT_PATH. '/includes/manager/mng.admin_logs.php');
        switch ($_GET['by'])
        {
            case 'year':
                $time = gmstr2time('-1 year');
                break;
            case 'halfyear':
                $time = gmstr2time('-6 month');
                break;
            case 'month':
                $time = gmstr2time('-1 month');
                break;
            case 'week':
                $time = gmstr2time('-1 week');
                break;
            default:
                $time = gmtime();
        }

        $this->log_item = $time;
        $mng = new AdminLogManager($_SESSION['store_id']);
        $res = $mng->drop($time);

        $this->show_message($this->str_format('remove_log_successfully', $res), 'back_to_list', 'admin.php?app=admin&act=logs');
        return true;
    }

};
?>