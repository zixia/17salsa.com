<?php
/**
 * ECMALL: ����֪ͨ
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: notify.php  2008-09-12 14:20:24Z XZF $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class NotifyController extends ControllerBackend
{
    function __construct($act)
    {
        $this->NotifyController($act);
    }

    function NotifyController($act)
    {
        if (empty($act))
        {
            $act = 'show';
        }
        parent::ControllerBackend($act);
    }

    /**
     * ����֪ͨ������ʾ
     *
     * @author  xzf
     * @return  void
     */
    function show()
    {
        $this->logger = false;
        $this->display('notify.html','mall');
    }

    /**
     * ���͵���֪ͨ
     *
     * @author  xzf
     * @return  void
     */
    function send_notify_owner()
    {
        $data['post'] = $_POST;
        if (!trim($data['post']['pm_title']))
        {
            $this->show_warning('no_pm_title');
            return;
        }
        if (!trim($data['post']['pm_contents']))
        {
            $this->show_warning('no_pm_contents');
            return;
        }

        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $store = new StoreManager();
        $count = $store->get_count(array());
        $ownerlist = $store->get_list(1, array(), $count);

        for ($i = 0; $i < count($ownerlist['data']); $i++)
        {
            $data['ownerlist'][] = $ownerlist['data'][$i]['store_id'];
        }
        $msgto = join(',', $data['ownerlist']);
        $subject = $data['post']['pm_title'];
        $message = $data['post']['pm_contents'];
        $success = uc_call('uc_pm_send', array($_SESSION['admin_id'], $msgto, $subject, $message, 1));
        if ($success > 0)
        {
            $this->show_message('notify_success');
        }
        else
        {
            $this->show_message('notify_failure');
        }
    }
}