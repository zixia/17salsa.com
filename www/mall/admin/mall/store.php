<?php

/**
 * ECMALL: 店铺管理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: store.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.store.php');
require(ROOT_PATH . '/includes/models/mod.store.php');

class StoreController extends ControllerBackend
{
    var $manager = null;

    function __construct($act)
    {
        $this->StoreController($act);
    }

    function StoreController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }

        $this->manager = new StoreManager();

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
        $condition = array();
        if (isset($_GET['is_recommend']))
        {
            $condition["is_recommend"] = 1;
        }

        if (isset($_GET['is_certified']))
        {
            $condition['is_certified'] = 1;
        }

        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] = trim($_GET['keywords']);
        }
        $list = $this->manager->get_list($this->get_page(), $condition);

        deep_local_date($list['data'], 'add_time', 'Y-m-d');

        $recommend_num = $this->manager->get_count(array('is_recommend'=>1));
        $certified_num = $this->manager->get_count(array('is_certified'=>1));

        $this->assign('list',   $list);
        $this->assign('stats',  $this->str_format('store_stats', $list['info']['rec_count'], $certified_num, $recommend_num));
        $this->display('store.view.html', 'mall');
    }

    /**
     * 添加店铺
     *
     * @author  liupeng
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
            include_once(ROOT_PATH. '/includes/manager/mng.admin.php');
            $post = $this->_post_handler();

            if ($post['user_id'] <= 0)
            {
                $this->show_warning($this->str_format('user_not_exists', $post['username']));
                return;
            }

            /* 检查店铺是否重名 */
            $retval = $this->manager->get_store_id($store_name);
            if (empty($_POST['store_name']) || ($retval > 0))
            {
                $this->show_warning('duplicate_store_name');
                return;
            }

            if (ENABLED_CUSTOM_DOMAIN && (!empty($post['custom'])))
            {
                include_once(ROOT_PATH . '/includes/manager/mng.store.php');
                if (StoreManager::exists_custom($post['custom'], $post['store_id']))
                {
                    $this->show_warning('exists_custom');
                    return;
                }
            }

            if (AdminManager::get_by_id($post['user_id']))
            {
                $this->show_warning('mall_admin_or_store_admin');
                return;
            }
            else
            {
                include_once(ROOT_PATH. "/includes/manager/mng.store.php");

                if (StoreManager::exists($post['store_id']))
                {
                    $this->show_warning('user_store_exists');
                    return;
                }
                else
                {
                    $res = $this->manager->add($post);
                    $this->log_item = $res;
                    if ($res)
                    {
                        /* 添加店铺管理员 */
                        $admin_manager = new AdminManager($post['store_id']);

                        $admin_manager->add($post['user_id'], $post['owner_name'], array('all'));

                        $this->log_item = $post['store_id'];
                        $this->show_message($this->str_format('add_store_successfully', stripslashes($post['store_name'])),
                            'store_view', "admin.php?app=store&amp;act=view");
                        return;
                    }
                }
            }
        }
    }
    /**
     * Ajax方式修改店铺资料
     *
     * @author  liupeng
     * @return  void
     */
    function modify()
    {
        $store_id = $_GET['id'];

        if ($_GET['column'] == 'store_name')
        {
            $value  = trim($_GET['value']);
            $retval = $this->manager->get_store_id($value);
            if ($retval>0 && $retval != $store_id)
            {
                $this->json_error('duplicate_store_name');
                return;
            }
        }elseif ($_GET['column'] == 'custom')
        {
            $value  = trim($_GET['value']);
            if (!preg_match('/^[a-z0-9\_\-]+$/', $value))
            {
                $this->json_error('custom_rule');
                return;
            }
            if (StoreManager::exists_custom($value, $store_id))
            {
                $this->json_error('custom_exists');
                return;
            }
        }

        if (in_array($_GET['column'], array('is_recommend', 'is_open', 'is_certified')))
        {
            $this->clean_cache();
        }

        if (!empty($_GET['id'])) $this->log_item = $_GET['id'];
        $this->_modify("Store", $_GET);
    }

    /**
     * 编辑店铺信息
     *
     * @author  liupeng
     * @return  void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $id     = intval($_GET['id']);
            $store  = new Store($id);
            if (!($info = $store->get_info()))
            {
                $this->show_message('store_no_exists');
                return false;
            }

            if (empty($info))
            {
                $this->show_warning("not_found");
                return;
            }

            $info['end_time'] = ($info['end_time'] > 0) ? local_date('Y-m-d', $info['end_time']) : '';
            $this->assign('store', $info);
            $this->build_editor('store_desc', $info['store_desc'], '480px', '200px');
            $this->display('store.detail.html', 'mall');
        }
        else
        {
            $post = $this->_post_handler('edit');
            $id   = intval($post['store_id']);

            $retval = $this->manager->get_store_id($post['store_name']);

            if (empty($post['store_name']) || ($store_id > 0 && $retval != $id))
            {
                $this->show_warning('duplicate_store_name');
                return;
            }

            if (ENABLED_CUSTOM_DOMAIN && (!empty($post['custom'])))
            {
                include_once(ROOT_PATH . '/includes/manager/mng.store.php');
                if (StoreManager::exists_custom($post['custom'], $post['store_id']))
                {
                    $this->show_warning('exists_custom');
                    return;
                }
            }

            $store  = new Store($post['store_id']);
            $res    = $store->update($post);

            if ($res)
            {
                $this->logger   = true;
                $this->log_item = $post['store_id'];
                $this->clean_cache();
                $this->show_message('edit_store_successfully', 'back_list', 'admin.php?app=store&amp;act=view', $this->lang('go_back'));
                return;
            }
            else
            {
                $this->show_warning('edit_store_failed');
                return;
            }
        }
    }
    /**
     * 批量处理
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
            $manager    = new StoreManager();
            $res        = $manager->batch($type, $in);
            $this->log_item = $in;

            if ($res)
            {
                $patterns[]     = '/act=\w+/i';
                $patterns[]     = '/[&|\?]?param=\w+/i';
                $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
                $replacement[]  = 'act=view';
                $replacement[]  = '';
                $replacement[]  = '';
                $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
                $this->show_message($this->lang('batch_successfully.' .$type), 'back_list', $location);
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
            exit(print_r($arr));
            $row = array();
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
     * @author  scottye
     * @return  void
     */
    function duplicate_name()
    {
        $this->logger = false; // 不记录日志
        $store_id   = (isset($_GET['store_id'])) ? intval($_GET['store_id']) : 0;
        $store_name = trim($_GET['store_name']);
        $retval     = $this->manager->get_store_id($store_name);

        if ($retval > 0 && $store_id != $retval)
        {
            $this->json_error('duplicate_store_name');
        }
        else
        {
            $this->json_result();
        }
    }

    /**
     * 判断用户是否已为其他店铺或者网站管理员
     *
     * @author  scottye
     * @return  void
     */
    function admin_exists()
    {
        include_once(ROOT_PATH. '/includes/manager/mng.admin.php');
        include_once(ROOT_PATH. "/includes/manager/mng.user.php");

        $this->logger = false; // 不记录日志
        $user_name = trim($_GET['user_name']);

        if (!$user_id   = UserManager::get_id_by_name($user_name))
        {
            $this->show_warning($this->str_format('user_not_exists', $user_name));
            return;
        }

        if (AdminManager::get_by_id($user_id))
        {
            $this->show_warning('mall_admin_or_store_admin');
            return;
        }
        else
        {
            $this->show_message('');
            return;
        }
    }

    /**
     * 处理提交的数据
     *
     * @author  liupeng
     * @params  enum    $act (insert,edit) 操作类型
     * @return  array
     */
    function _post_handler($act='insert')
    {
        $post = array();

        if ($act == 'insert')
        {
            /* get user id */
            include_once(ROOT_PATH. "/includes/manager/mng.user.php");

            $post['user_id']    = UserManager::get_id_by_name(trim($_POST['username']));
            $post['store_id']   = $post['user_id'];
        }
        else
        {
            $post['store_id']   = intval($_POST['id']);
        }

        $post['owner_name']     = empty($_POST['owner_name']) ? $_POST['username'] : sub_str($_POST['owner_name'], 60);
        $post['store_name']     = sub_str(trim($_POST['store_name']), 100);
        $post['store_location'] = sub_str($_POST['store_location'], 100);
        $post['goods_limit']    = intval($_POST['goods_limit']);
        $post['file_limit']     = intval($_POST['file_limit']);
        $post['is_recommend']   = (isset($_POST['is_recommend'])) ? intval($_POST['is_recommend']) : 0;
        $post['is_certified']   = (isset($_POST['is_certified'])) ? intval($_POST['is_certified']) : 0;
        $post['owner_idcard']   = sub_str($_POST['owner_idcard'], 60);
        $post['owner_phone']    = sub_str($_POST['owner_phone'], 60);
        $post['owner_address']  = sub_str($_POST['owner_address'], 255);
        $post['owner_zipcode']  = sub_str($_POST['owner_zipcode'], 60);
        $post['store_location'] = trim($_POST['store_location']);
        $post['custom']         = trim($_POST['custom']);
        $post['end_time']       = trim($_POST['end_time']);
        $post['end_time']       = empty($post['end_time']) ? 0 : gmstr2time($post['end_time']);

        return $post;
    }

    /**
     * 检查自定义域名是否重复
     *
     * @author  wj
     * @param   void
     * @return  void
     */
    function test_custom_name()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        if (StoreManager::exists_custom($_POST['custom'], $_POST['store_id']))
        {
            $this->json_error($this->lang('custom_exists'));
        }
        else
        {
            $this->json_result(1);
        }
    }

    /**
     * 更新商品总数
     *
     * @author  liupeng
     * @return  void
     */
    function update_goods_count()
    {
        $manager = new StoreManager();

        $manager->update_goods_count();
        $this->json_result('', 'ok');
    }
};
?>