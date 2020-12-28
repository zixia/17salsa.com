<?php

/**
 * ECMall ����֧�������Ӧ����ҳ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2007 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: respond.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
include_once(ROOT_PATH . '/includes/models/mod.order.php');

class RespondController extends ControllerFrontend
{

    var $_allowed_actions = array('respond');

    function __construct($act)
    {
        $this->RespondController($act);
    }
    function RespondController($act)
    {
        $act = 'respond';
        parent::__construct($act);
    }

    /**
     *  ���մ�֧����վ���ص�֧�������Ϣ
     *
     *  @author Garbin
     *  @return void
     */

    function respond()
    {
        /* ֧����ʽ���� */
        $pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';

        /* ��ʼ������ */
        $pay_result = FALSE;
        if (empty($_GET))
        {
            /* �������ͨ��GET���أ�����Ϊ��POST���� */
            $_GET = $_POST;
        }

        if (!$pay_code)
        {
            $pay_code = 'alipay';
        }

        /* �����Ƿ�Ϊ�� */
        if (empty($pay_code))
        {
            $msg = $this->lang('pay_not_exist');
        }
        else
        {
            /* ���code������û���ʺ� */
            if (strpos($pay_code, '?') !== false)
            {
                $arr1 = explode('?', $pay_code);
                $arr2 = explode('=', $arr1[1]);

                $_REQUEST['code']   = $arr1[0];
                $_REQUEST[$arr2[0]] = $arr2[1];
                $_GET['code']       = $arr1[0];
                $_GET[$arr2[0]]     = $arr2[1];
                $pay_code           = $arr1[0];
            }

            $plugin_file = ROOT_PATH . '/includes/payment/' . $pay_code . '.php';

            /* ������ļ��Ƿ���ڣ������������֤֧���Ƿ�ɹ��������򷵻�ʧ����Ϣ */
            if (is_file($plugin_file))
            {
                /* ����֧����ʽ���봴��֧����Ķ��󲢵�������Ӧ�������� */
                include_once($plugin_file);

                /* ����֧����ʽʵ�� */
                $payment = new $pay_code((int)$_GET['pay_id'], (int)$_GET['store_id']);

                /* ��֧��ʵ������Ӧ��֧��������������֧�������������Ӧ��� */
                $result = $payment->respond();
                if ($result)
                {
                    $buyer = (string)$_SESSION['user_name'];

                    $msg = $this->lang('pay_sucess');

                    /* ��¼֧����־ */
                    include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
                    $order_logger = new OrderLogger($pay_info['store_id']);
                    $order_logger->write(array('action_user' => 0,
                                               'order_id'    => $payment->order->_order_data['order_id'],
                                               'order_status'=> ORDER_STATUS_ACCEPTTED,
                                               'action_note' => $this->str_format('buyer_paid', $buyer, $pay_info['pay_name'], price_format($payment->total_fee)) . $payment->get_ext_note(),
                                               'action_time' => gmtime()));
                    $pay_result = TRUE;
                }
                else
                {
                    if ($payment->err)
                    {
                        $msg = $payment->err;
                    }
                    else
                    {
                        $msg = 'pay_faild';
                    }
                }
            }
            else
            {
                $msg = 'pay_not_exist';
            }
        }

        if ($pay_result)
        {
            if ($payment->ext_msg)
            {
                $msg .= $payment->ext_msg;
            }
            $this->show_message($msg, 'view_order_detail', 'index.php?app=member&amp;act=order_detail&amp;id=' . $payment->order->_id, 'view_order', 'index.php?app=member&amp;act=order_view');
            return;
        }
        else
        {
            $this->show_warning($msg, 'view_order', 'index.php?app=member&amp;act=order_view');
            return;
        }
    }
}



?>