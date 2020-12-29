<?php

/**
 * ECMALL: 用户管理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: user.php 6086 2008-11-19 10:14:10Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.user.php');
require(ROOT_PATH . '/includes/models/mod.user.php');

class UserController extends ControllerBackend
{
    var $manager = null;

    /**
     * 构造函数
     *
     * @author  wj
     * @param    string      $act
     * @return  void
     */
    function __construct($act)
    {
        $this->UserController($act);
    }

    /**
     * 构造函数
     *
     * @author  wj
     * @param    string      $act
     * @return  void
     */
    function UserController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }

        $this->manager = new UserManager();

        parent::__construct($act);
    }
    /**
     * 查看店铺列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $condition = isset($_GET['keywords']) ? array('user_name' => trim($_GET['keywords'])) : array();

        $list = $this->manager->get_list($this->get_page(), $condition);
        $list['data'] = deep_local_date($list['data'], 'last_login', $this->conf('mall_time_format_complete'));

        $this->assign('list',   $list);
        $this->assign('stats',  $this->str_format('user_stats', $list['info']['rec_count'], $list['info']['page_count']));
        $this->display('user.view.html', 'mall');
    }
    /**
     * 添加店铺
     *
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; // 不记录日志

            $store['goods_limit']   = 0;
            $store['file_limit']    = 0;

            $this->assign('store',          $store);
            $this->build_editor('store_desc', '', '480px', '200px');
            $this->display('store.detail.html', 'mall');
        }
        else
        {
            /* get user id */
            include_once(ROOT_PATH. "/includes/manager/mng.user.php");
            $manager = new UserManager();
            $_POST['user_id'] = $manager->get_id_by_name(trim($_POST['username']));

            if ($_POST['user_id'] <= 0)
            {
                $this->show_warning($this->str_format('user_not_exists', $_POST['username']));
                return;
            }
            elseif (empty($_POST['store_name']) || $this->duplicate_name($_POST['store_name']) > 0)
            {
                $this->show_warning('duplicate_store_name');
                return;
            }
            else
            {
                include_once(ROOT_PATH. "/includes/manager/mng.store.php");
                $res = $this->manager->add($_POST);

                if ($res > 0)
                {
                    $this->log_item = $res;
                    $this->show_message($this->str_format('add_store_successfully', $_POST['store_name']),
                        $this->lang('store_view'), "admin.php?app=user&amp;act=view");
                    return;
                }
            }
        }
    }
    /**
     * Ajax方式修改店资料
     *
     * @return  void
     */
    function modify()
    {
        if (!empty($_GET['id'])) $this->log_item = $_GET['id'];
        $this->_modify("User", $_GET, 'edit_store_failed');
    }
    /**
     * 编辑店铺信息
     *
     * @return  void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $id     = intval($_GET['id']);
            $user   = new User($id);
            $info   = $user->get_info();

            if (empty($info))
            {
                $this->show_warning("not_found");
                return;
            }
            else
            {
                $this->assign('user', $info);
                $this->display('user.detail.html', 'mall');
            }
        }
        else
        {
            $post = array();
            $post['user_id']        = intval($_POST['id']);
            $post['gender']            = intval($_POST['gender']);
            $post['msn']            = trim($_POST['msn']);
            $post['qq']             = trim($_POST['qq']);
            $post['office_phone']   = trim($_POST['office_phone']);
            $post['home_phone']     = trim($_POST['home_phone']);
            $post['mobile_phone']   = trim($_POST['mobile_phone']);
            $post['birthday']       = $_POST['birthday'];

            $store  = new User($post['user_id']);
            $res    = $store->update($post);

            if ($res)
            {
                $this->log_item = $post['user_id'];
                $this->show_message('edit_user_successfully', 'back_list', 'admin.php?app=user&amp;act=view', $this->lang('go_back'));
                return;
            }
            else
            {
                $this->show_warning('edit_user_failed');
                return;
            }
        }
    }

    /**
     * 根据用户输入获得用户列表
     *
     * @return  void
     */
    function get_userlist()
    {
        $this->logger = false; // 不记录日志

        include_once(ROOT_PATH. '/includes/manager/mng.user.php');
        $manager = new UserManager();
        $username = trim($_GET['q']);

        $arr = $manager->get_users_by_name($username);
        if (!empty($arr))
        {
            foreach ($arr AS $val)
            {
                $row[] = array($val['user_name'], $val['user_name'], $val['user_id']);
            }
            $this->json_result($row);
            return;
        }
        else
        {
            $this->json_error();
            return;
        }
    }

    /**
     * 判断店铺名称是否重复
     *
     * @return  void
     */
    function duplicate_name()
    {
        $this->logger = false; // 不记录日志
        $store_id   = (isset($_POST['store_id'])) ? intval($_POST['store_id']) : 0;
        $store_name = trim($_POST['store_name']);
        $retval     = $this->manager->get_store_id($store_name);

        if ($retval == 0 || $store_id != $retval)
        {
            $this->json_result();
        }
        else
        {
            $this->json_error('duplicate_store_name');
            return;
        }
    }

};
?>
