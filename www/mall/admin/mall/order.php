<?php

/**
 * ECMALL: ���������������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: order.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/models/mod.order.php');     //����orderģ��
include_once(ROOT_PATH . '/includes/models/mod.goods.php');     //����orderģ��
include_once(ROOT_PATH . '/includes/manager/mng.order.php');    //����order����ģ��
include_once(ROOT_PATH . '/includes/manager/mng.goods.php');    //����order����ģ��
include_once(ROOT_PATH . '/includes/manager/mng.region.php');    //���ص�������ģ��
include_once(ROOT_PATH . '/includes/manager/mng.payment.php');   //���ص�������ģ��
include_once(ROOT_PATH . '/includes/manager/mng.shipping.php');  //���ص�������ģ��

/*
$GLOBALS['order_status'] = array (
  //0 => 'order_status_temporary',
  1 => 'order_status_pending',
  2 => 'order_status_submitted',
  3 => 'order_status_acceptted',
  4 => 'order_status_processing',
  5 => 'order_status_shipped',
  6 => 'order_status_delivered',
  7 => 'order_status_invalid',
  8 => 'order_status_rejected',
);
*/

class OrderController extends ControllerBackend
{
    var $_order_status_list = array (
          1 => 'order_status_pending',
          2 => 'order_status_submitted',
          3 => 'order_status_acceptted',
          4 => 'order_status_processing',
          5 => 'order_status_shipped',
          6 => 'order_status_delivered',
          7 => 'order_status_invalid',
          8 => 'order_status_rejected',
        );
    function __construct($act)
    {
        $this->OrderController($act);
    }

    function OrderController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �鿴�����б�
     *
     *  @access public

     * @return  void
     */
    function view()
    {
        $this->logger = false; //����¼��־
        $conditions = array();
        /* ���� */
        foreach ($_GET as $_k => $_v)
        {
            switch($_k)
            {
            case 'start_date':
            case 'end_date':
                if ($_v)
                {
                    $conditions[$_k] = local_strtotime($_v);
                }
                break;
            case 'order_amount_start':
                if (!empty($_v))
                {
                    $conditions['order_amount']['start'] = $_v;
                }
                break;
            case 'order_amount_end':
                if (!empty($_v))
                {
                    $conditions['order_amount']['end'] = $_v;
                }
                break;
            case 'goods_amount_start':
                if (!empty($_v))
                {
                    $conditions['goods_amount']['start'] = $_v;
                }
                break;
            case 'goods_amount_end':
                if (!empty($_v))
                {
                    $conditions['goods_amount']['end'] = $_v;
                }
                break;
            case 'order_status':
                if ($_v != '-1')
                {
                    $conditions[$_k] = (int)$_v;
                }
                break;
            case 'keywords':
                if ($_v)
                {
                    $conditions['consignee'] =  $_v;
                    $conditions['address']   =  $_v;
                    $conditions['order_sn']  =  $_v;
                }
                break;
            case 'store_name':
                if ($_v)
                {
                    $conditions[$_k] = trim($_v);
                }
                break;
            case 'user_id':
                if ($_v)
                {
                    $conditions[$_k] = intval($_v);
                }
                break;
            }
        }
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        $user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
        if ($store_id)
        {
            $conditions['store_id'] = $store_id;
        }
        if ($user_id)
        {
            $conditions['user_id']  = $user_id;
        }
        $order_manager  =   new OrderManager($_SESSION['store_id']);
        $orders = $order_manager->get_list($this->get_page(), $conditions);
        foreach ($orders['data'] as $_k => $_v)
        {
            $orders['data'][$_k]['status'] = $this->lang($order_manager->get_status_code($_v['order_status']));
        }
        $orders['data'] = deep_local_date($orders['data'], 'add_time', 'm-d H:i');
        $order_status = $this->_order_status_list;
        foreach ($order_status as $_k => $_v)
        {
            $order_status[$_k] = $this->lang($_v);
        }

        /* ͳ����Ϣ���� */
        include_once(ROOT_PATH . '/admin/includes/inc.stat.php');

        /* ������������ */
        $this->assign('search_by_status', $conditions['order_status'] ? $conditions['order_status']:-1);

        /* �����б��� */
        $this->assign('order_status',   $order_status);
        $this->assign('orders',         $orders['data']);
        $this->assign('page_info',      $orders['info']);
        $this->assign('url_format',     "admin.php?app=order&amp;act=view&amp;page=%d&amp;conditions[order_status]={$_GET['order_status']}&amp;conditions[keywords]={$_GET['keywords']}&amp;conditions[start_date]={$_GET['start_date']}&amp;conditions[end_date]={$_GET['end_date']}&amp;conditions[order_amount][start]={$_GET['order_amount_start']}&amp;conditions[order_amount][end]={$_GET['order_amount_end']}");
        $this->assign('order_stats',    $this->str_format('order_stats', $orders['info']['rec_count'], $orders['info']['page_count'], get_order_count('unlimitted', 'wait_for_ship', 'number'), date('Y-m-d',get_today_timestamp()), get_order_count('today', 'new', 'number')));
        $this->display('order.view.html', 'mall');
    }

    /**
     *  �������Ķ���
     *
     *  @param
     *  @return
     */
    function processing_order()
    {
        $this->redirect('admin.php?app=order&act=view&special=1&order_status=' . ORDER_STATUS_ACCEPTTED . ',' . ORDER_STATUS_PROCESSING);
    }

    function show()
    {
        $this->logger = false; //����¼��־
        $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        if(!$order_id)
        {
            $this->show_warning('invalid_order_id');
            return;
        }
        $order  =   new Order($order_id);
        $order_info =   $order->get_info();
        $order_goods=   $order->list_goods();
        foreach ($order_goods as $_k => $_v)
        {
            $order_goods[$_k]['subtotal'] = $_v['goods_number'] * $_v['goods_price'];
        }
        $order_info['order_status'] = $this->lang($this->_order_status_list[$order_info['order_status']]);


        /* ��ȡ������־ */
        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
        $order_logger = new OrderLogger($_SESSION['store_id']);
        $action_logs  = $order_logger->get_list(1, array('order_sn' => $order_info['order_sn']));
        foreach ($action_logs['data'] as $_k => $_v)
        {
            $action_logs['data'][$_k]['order_status'] = $this->_order_status_list[$_v['order_status']];
        }
        $action_logs['data'] = deep_local_date($action_logs['data'], 'action_time', $this->conf('mall_time_format_complete'));

        $this->assign('order', $order_info);
        $this->assign('display_seller_eval', $this->_get_display_eval($order_info['seller_evaluation']));
        $this->assign('display_buyer_eval', $this->_get_display_eval($order_info['buyer_evaluation']));
        $this->assign('goods', $order_goods);
        $this->assign('action_logs', $action_logs);
        $this->display('order.detail.html', 'mall');
    }

    /**
     *    ��������Ϊ��Ч
     *
     *    @author    Garbin
     *    @return    void
     */
    function evaluation_invalid()
    {
        $body = empty($_GET['body']) ? 0 : trim($_GET['body']);
        $id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $reason = empty($_GET['reason']) ? '' : trim($_GET['reason']);
        if (!$body || !$id)
        {
            $this->show_warning('invalid_request');

            return;
        }
        if (!$reason)
        {
            $this->show_warning('reason_required');

            return;
        }
        $order = new Order($id);
        $info  = $order->get_info();

        if ($info[$body . '_evaluation_invalid'])
        {
            $this->show_warning('invalid_request');

            return;
        }

        if ($order->evaluation_invalid($body))
        {
            if ($body == 'seller')
            {
                include_once(ROOT_PATH . '/includes/models/mod.adminuser.php');
                $seller = new AdminUser($info['store_id']);
                $seller->update_seller_credit();    //�������ҵ����û���

                $to_seller_mail_tpl = 'evaluation_invalid_from_seller';
                $to_buyer_mail_tpl  = 'evaluation_invalid_to_buyer';
                $to_seller_pm_contents = 'evaluation_invalid_from_seller_contents';
                $to_buyer_pm_contents = 'evaluation_invalid_to_buyer_contents';
            }
            elseif ($body == 'buyer')
            {
                include_once(ROOT_PATH . '/includes/models/mod.user.php');
                $buyer  = new User($info['user_id']);
                $buyer->update_buyer_credit();      //������ҵ���Ϣ����

                $to_seller_mail_tpl = 'evaluation_invalid_to_seller';
                $to_buyer_mail_tpl  = 'evaluation_invalid_from_buyer';
                $to_seller_pm_contents = 'evaluation_invalid_to_seller_contents';
                $to_buyer_pm_contents = 'evaluation_invalid_from_buyer_contents';
            }
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $store      = new Store($info['store_id']);
            $store_info = $store->get_info();

            /* �����ҷ���PM������ */
            $this->send_mail($store_info['email'], $to_seller_mail_tpl, array('order' => $info,
                                                                                  'mall_name' => $this->conf('mall_name'),
                                                                                  'buyer'  => $info['user_name'],
                                                                                  'seller' => $store_info['store_name'],
                                                                                  'sent_date'=> local_date('Y-m-d H:i'),
                                                                                  'reason'=> stripslashes($reason)));
            uc_call('uc_pm_send', array(0, $info['store_id'], $this->lang('evaluation_invalid_notify'), $this->str_format($to_seller_pm_contents, $info['user_name'], $info['order_sn'], $reason)), 1);

            /* ����ҷ���PM������ */
            $this->send_mail($info['email'], $to_buyer_mail_tpl, array('order' => $info,
                                                                                  'mall_name' => $this->conf('mall_name'),
                                                                                  'buyer'  => $info['user_name'],
                                                                                  'seller' => $store_info['store_name'],
                                                                                  'sent_date'=> local_date('Y-m-d H:i'),
                                                                                  'reason'=> stripslashes($reason)));
            uc_call('uc_pm_send', array(0, $info['user_id'], $this->lang('evaluation_invalid_notify'), $this->str_format($to_buyer_pm_contents, $store_info['store_name'], $info['order_sn'], $reason), 1));

            /* ��¼����������־ */
            include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
            $order_logger = new OrderLogger(0);
            $order_logger->write(array('action_user' => $_SESSION['admin_name'],
                                       'order_id'    => $info['order_id'],
                                       'order_status'=> ORDER_STATUS_DELIVERED,
                                       'action_note' => $this->lang($body . '_evaluation_invalid') . ':' . $reason,
                                       'action_time' => gmtime()));
            $this->log_item = $info['order_id'];

            $this->show_message('evaluation_invalid_success', 'view_order', 'admin.php?app=order&amp;act=view');

            return;
        }

    }

    /**
     *    ��ȡ����������
     *
     *    @author    Garbin
     *    @param     int $evalid
     *    @return    void
     */
    function _get_display_eval($evalid)
    {
        switch ($evalid)
        {
            case 1:
                return $this->lang('order_evaluation_poor');
            break;
            case 2:
                return $this->lang('order_evaluation_common');
            break;
            case 3:
                return $this->lang('order_evaluation_good');
            break;
            default:
                return $this->lang('order_evaluation_unevaluated');
            break;
        }
    }
};

?>
