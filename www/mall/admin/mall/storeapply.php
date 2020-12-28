<?php

/**
 * ECMALL: ��������������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * �鿴�����б�
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

        /* ͳ�� */
        $raw_count      = $this->manager->get_count(array('status' => APPLY_RAW));
        $accept_count   = $this->manager->get_count(array('status' => APPLY_ACCEPT));
        $deny_count     = $this->manager->get_count(array('status' => APPLY_DENY));
        $apply_count    = $raw_count + $accept_count + $deny_count;

        $this->assign('stats',  $this->str_format('apply_stats', $apply_count, $raw_count, $accept_count, $deny_count));
        $this->assign('list',   $list);
        $this->display('storeapply.view.html', 'mall');
    }

    /**
     * �鿴�������ϸ��Ϣ
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
     * �����û��Ŀ�������
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

            /*��֤�Ƿ��Ѿ��е���,����в��ڴ���*/
            if ($admin_info)
            {
                $mod->update(array('status' => 0)); //�ع�״̬
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
     * ɾ��ָ��������
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
     * ��������
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
     * ��׼���벢����µ���
     *
     * @author  weberliu
     * @param   object      $apply      ���������ʵ�����
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
                //�����ͻ������������Ϊ��
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
     * ����µĹ���Ա
     *
     * @author  weberliu
     * @param   int     $uid        �û�ID
     * @param   string  $username   ��ʵ����
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
