<?php

/**
 * ECMall: ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: storerelet.php 6026 2008-11-03 07:00:04Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StorereletController extends ControllerBackend
{
    function __construct($act)
    {
        $this->StorereletController($act);
    }

    function StorereletController($act)
    {
        if (empty($act))
        {
            $act = 'reletview';
        }
        parent::__construct($act);
    }

    /**
     * �����б�
     */
    function view()
    {
        $this->logger = false;
        $condition = array('store_id' => 0, 'extension_code' => 'storerelet');
        if (!empty($_GET['order_sn']))
        {
            $condition['order_sn'] = trim($_GET['order_sn']);
        }
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $mng_order = new OrderManager(0);
        $order_list = $mng_order->get_list($this->get_page(), $condition);
        $order_ids = array();
        foreach ($order_list['data'] as $order)
        {
            $order_ids[] = $order['order_id'];
        }
        $order_list['data'] = $mng_order->get_list_by_ids(join(',', $order_ids));
        $order_list['data'] = deep_local_date($order_list['data'], 'add_time', 'm-d H:i');
        $this->assign('order_list', $order_list);
        $this->display('storerelet.view.html', 'mall');
    }

    /**
     * ��Ϊ�Ѹ���
     */
    function set_paid()
    {
        /* ������ */
        $order_id = intval($_GET['id']);
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $mod_order = new Order($order_id);
        $order_info = $mod_order->get_info_with_payment();
        if (empty($order_info) || $order_info['store_id'] != 0)
        {
            $this->show_warning('Invalid Params');
            return;
        }

        /* ����Ƿ񸶿� */
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('order_is_paid', 'list_relet_order', 'admin.php?app=storerelet&act=view');
            return;
        }

        /* ��Ϊ�Ѹ��� */
        $mod_order->set_status(ORDER_STATUS_SHIPPED);
        $mod_order->submit(false);

        /* �ӳ��������� */
        $scheme_info = unserialize($order_info['seller_comment']);
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $mod_store = new Store($order_info['user_id']);
        $store_info = $mod_store->get_info();
        $arr = array(
            'goods_limit'   => $scheme_info['allowed_goods'],
            'file_limit'    => $scheme_info['allowed_file'],
            'end_time'      => strtotime('+' . $scheme_info['allowed_month'] . ' month', max($store_info['end_time'], gmtime()))
        );
        $mod_store->update($arr);

        /* ֪ͨ���� */
        uc_call('uc_pm_send', array($_SESSION['admin_id'], $order_info['user_id'], $this->lang('order_settled'), $this->lang('order_settled')));

        /* ��ת���б�ҳ�� */
        $this->redirect('admin.php?app=storerelet&act=view');
    }

    /**
     * ɾ��
     */
    function drop()
    {
        /* ������ */
        $order_id = intval($_GET['id']);
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $mod_order = new Order($order_id);
        $order_info = $mod_order->get_info_with_payment();
        if (empty($order_info) || $order_info['store_id'] != 0)
        {
            $this->show_warning('Invalid Params');
            return;
        }

        /* ����Ƿ񸶿� */
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('order_is_paid', 'list_relet_order', 'admin.php?app=storerelet&act=view');
            return;
        }

        /* ɾ�� */
        $mod_order->set_status(ORDER_STATUS_INVALID);
        $mod_order->submit(false);
        $mod_order->drop();

        /* ��ת���б�ҳ�� */
        $this->show_message('drop_ok');
    }
};

?>
