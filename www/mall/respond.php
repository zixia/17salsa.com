<?php

/**
 * ECMall 在线支付结果响应处理页面
 * ============================================================================
 * 版权所有 (C) 2005-2007 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
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
     *  接收从支付网站返回的支付结果信息
     *
     *  @author Garbin
     *  @return void
     */

    function respond()
    {
        /* 支付方式代码 */
        $pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';

        /* 初始化变量 */
        $pay_result = FALSE;
        if (empty($_GET))
        {
            /* 如果不是通过GET返回，则视为由POST返回 */
            $_GET = $_POST;
        }

        if (!$pay_code)
        {
            $pay_code = 'alipay';
        }

        /* 参数是否为空 */
        if (empty($pay_code))
        {
            $msg = $this->lang('pay_not_exist');
        }
        else
        {
            /* 检查code里面有没有问号 */
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

            /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
            if (is_file($plugin_file))
            {
                /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
                include_once($plugin_file);

                /* 创建支付方式实例 */
                $payment = new $pay_code((int)$_GET['pay_id'], (int)$_GET['store_id']);

                /* 由支付实例来响应该支付结果并获得最终支付结果，返回响应结果 */
                $result = $payment->respond();
                if ($result)
                {
                    $buyer = (string)$_SESSION['user_name'];

                    $msg = $this->lang('pay_sucess');

                    /* 记录支付日志 */
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