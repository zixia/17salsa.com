<?php

/**
 * ECMALL: 店铺申请管理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: storeapply.php 6063 2008-11-13 10:18:48Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.storeapply.php');
require(ROOT_PATH . '/includes/models/mod.storeapply.php');

class StoreApplyController extends ControllerBackend
{
    var $manager = null;

    function __construct($act)
    {
        $this->StoreApplyController($act);
    }

    function StoreApplyController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }

        $this->manager = new StoreApplyManager();

        parent::__construct($act);
    }

    /**
     * 查看申请列表
     *
     * @author  weberliu
     */
    function view()
    {
        $this->logger = false;

        $condition = array();
        if (isset($_GET['status']) && $_GET['status'] != '9')
        {
            $condition['status'] = intval($_GET['status']);
        }

        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] =trim($_GET['keywords']);
        }

        $list = $this->manager->get_list($this->get_page(), $condition);
        deep_local_date($list['data'], 'add_time', 'Y-m-d');

        /* 统计 */
        $raw_count      = $this->manager->get_count(array('status' => APPLY_RAW));
        $accept_count   = $this->manager->get_count(array('status' => APPLY_ACCEPT));
        $deny_count     = $this->manager->get_count(array('status' => APPLY_DENY));
        $apply_count    = $raw_count + $accept_count + $deny_count;

        $this->assign('stats',  $this->str_format('apply_stats', $apply_count, $raw_count, $accept_count, $deny_count));
        $this->assign('list',   $list);
        $this->display('storeapply.view.html', 'mall');
    }

    /**
     * 查看申请的详细信息
     *
     * @author  weberliu
     */
    function detail()
    {
        $mod = new StoreApply(intval($_GET['id']));

        $this->assign('apply',  $mod->get_info());
        $this->display('storeapply.detail.html', 'mall');
    }

    /**
     * 处理用户的开店申请
     *
     * @author  wj
     * @return  void
     */
    function process()
    {
        $do     = trim($_POST['do']) == 'accept' ? APPLY_ACCEPT : APPLY_DENY;

        if (empty($_POST['id']))
        {
            $this->redirect("admin.php?app=storeapply");
            return;
        }

        $mod    = new StoreApply(intval($_POST['id']));
        $info   = $mod->get_info();

        $mod->update(array('status' => $do));

        if ($do == APPLY_ACCEPT)
        {
            include_once(ROOT_PATH . '/includes/manager/mng.admin.php');
            $mng_admin = new AdminManager();
            $admin_info = $mng_admin->get_by_id($info['user_id']);

            /*验证是否已经有店铺,如果有不在处理*/
            if ($admin_info)
            {
                $mod->update(array('status' => 0)); //回滚状态
                $this->show_warning($this->str_format('apple_is_overdue', $info['user_name']), 'go_back', 'admin.php?app=storeapply', 'delete_apply', 'admin.php?app=storeapply&act=drop&id='.$info['apply_id']);
            }
            else
            {
                $this->add_store($mod);
                $this->add_admin_user($info['user_id'], $info['user_name']);

                $msg = 'accept_apply_success';
                uc_call('uc_pm_send', array(0, $info['user_id'], $this->lang('accept_apply_subject'), $this->lang('accept_apply_message'), 1));
                $tmp_arr = uc_call('uc_get_user', array($info['user_id'], 1));
                $this->send_mail($tmp_arr[2], '', '', $this->lang('accept_apply_subject'), $this->lang('accept_apply_message'));

                $this->show_message($this->lang($msg), 'manage_store',
                    "javascript:parent.openFrame('" .$this->lang(store_admin). "', 'store', 'edit', 'id={$info[user_id]}');",
                    'go_back', 'admin.php?app=storeapply');
            }

        }
        else
        {
            $msg    = 'deny_apply_success';
            $notice = $this->str_format('deny_apply_message', htmlspecialchars($_POST['deny_reason']));

            uc_call('uc_pm_send', array(0, $info['user_id'], $this->lang('deny_apply_subject'), $notice, 1));
            $tmp_arr = uc_call('uc_get_user', array($info['user_id'], 1));
            $this->send_mail($tmp_arr[2], '', '', $this->lang('deny_apply_subject'), $notice);

            $this->show_message($this->lang($msg), 'go_back', 'admin.php?app=storeapply');
        }
    }

    /**
     * 删除指定的申请
     *
     * @author  weberliu
     * @return  void
     */
    function drop()
    {
        $mod = new StoreApply(intval($_GET['id']));
        $mod->drop();

        $this->show_message('delete_apply_success', 'go_back', 'admin.php?app=storeapply');
    }

    /**
     * 批量处理
     *
     * @author  weberliu
     * @return  void
     */
    function batch()
    {
        if ($_GET['param'] == 'drop')
        {
            $num = $this->manager->batch_drop($_GET['ids']);

            $this->show_message('batch_drop_success', 'go_back', 'admin.php?app=storeapply&act=view');
        }
        else
        {
            $this->redirect('admin.php?app=storeapply&act=view');
        }
    }

    /**
     * 批准申请并添加新店铺
     *
     * @author  weberliu
     * @param   object      $apply      开店申请的实体对象
     * @return  int
     */
    function add_store($apply)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');

        $info   = $apply->get_info();
        $idx    = 0;

        if ($info['status'] !== APPLY_ACCEPT)
        {
            $store['store_name']        = addslashes($info['store_name']);
            $store['store_location']    = addslashes($info['store_location']);
            $store['owner_name']        = addslashes($info['owner_name']);
            $store['owner_idcard']      = addslashes($info['owner_idcard']);
            $store['owner_phone']       = addslashes($info['owner_phone']);
            $store['owner_address']     = addslashes($info['owner_address']);
            $store['owner_zipcode']     = addslashes($info['owner_zipcode']);
            $store['custom']            = addslashes($info['custom']);
            $store['add_time']          = gmtime();
            $store['store_id']          = $info['user_id'];

            $free_days = max(0, intval($this->conf('mall_store_free_days')));
            if ($free_days > 0)
            {
                $store['end_time'] = gmtime() + $free_days * 86400;
            }
            else
            {
                $store['end_time'] = 0;
            }

            $store['goods_limit'] = intval($this->conf('mall_default_allowed_goods'));
            $store['file_limit']  = intval($this->conf('mall_default_allowed_file'));

            if ($store['custom'])
            {
                include_once(ROOT_PATH . '/includes/manager/mng.store.php');
                //如果冲突将二级域名置为空
                if (StoreManager::exists_custom($store['custom']))
                {
                    $store['custom'] = '';
                }
            }

            $manager = new StoreManager();
            $idx = $manager->add($store);
        }

        return $idx;
    }

    /**
     * 添加新的管理员
     *
     * @author  weberliu
     * @param   int     $uid        用户ID
     * @param   string  $username   真实姓名
     * @return  void
     */
    function add_admin_user($uid, $username)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.admin.php');

        $manager = new AdminManager($uid);
        $manager->add($uid, $username, array('all'));
    }

};
?>
