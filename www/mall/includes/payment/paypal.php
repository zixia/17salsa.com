<?php

/**
 * ECMall: Paypal插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: paypal.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/paypal'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'paypal_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.paypal.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'NZD');

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'paypal_account', 'type' => 'text', 'value' => '')
    );

    return;
}

/**
 * 类
 */
class paypal extends Payment
{
    /* 支付的货币 */
    var $currency  = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'NZD');

    var $_ext_note = '';
    /**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order)
    {
        $payment = $this->get_config();
        $order_info = $order->get_info();
        $data_order_id      = $order->get_pay_log();
        $data_amount        = $order_info['payable'];
        $data_return_url    = $this->get_respond_url(basename(__FILE__, '.php'));
        $data_pay_account   = $payment['paypal_account'];
        $currency_code      = CURRENCY;
        $data_notify_url    = $this->get_respond_url(basename(__FILE__, '.php'));
        $cancel_return      = site_url();

/*
        $def_url  = '<br /><form style="text-align:center;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">' .   // 不能省略
            "<input type='hidden' name='cmd' value='_xclick'>" .                             // 不能省略
            "<input type='hidden' name='business' value='$data_pay_account'>" .                 // 贝宝帐号
            "<input type='hidden' name='item_name' value='$order_info[order_sn]'>" .                 // payment for
            "<input type='hidden' name='amount' value='$data_amount'>" .                        // 订单金额
            "<input type='hidden' name='currency_code' value='$currency_code'>" .            // 货币
            "<input type='hidden' name='return' value='$data_return_url'>" .                    // 付款后页面
            "<input type='hidden' name='invoice' value='$data_order_id'>" .                      // 订单号
            "<input type='hidden' name='charset' value='utf-8'>" .                              // 字符集
            "<input type='hidden' name='no_shipping' value='1'>" .                              // 不要求客户提供收货地址
            "<input type='hidden' name='no_note' value=''>" .                                  // 付款说明
            "<input type='hidden' name='notify_url' value='$data_notify_url'>" .
            "<input type='hidden' name='rm' value='2'>" .
            "<input type='hidden' name='cancel_return' value='$cancel_return'>" .
            "<input type='submit' value='" . $GLOBALS['_LANG']['paypal_button'] . "'>" .                      // 按钮
            "</form><br />";
*/
        $parameter = array(
            'cmd' => '_xclick',
            'business' => $data_pay_account,
            'item_name' => $order_info['order_sn'],
            'amount' => $data_amount,
            'currency_code' => $currency_code,
            'return' => $data_return_url,
            'invoice' => $data_order_id,
            'charset' => 'utf-8',
            'no_shipping' => '1',
            'no_note' => '',
            'notify_url' => $data_notify_url,
            'rm' => '2',
            'cancel_return' => $cancel_return
        );

        return $this->form_info('https://www.paypal.com/cgi-bin/webscr',
                                'POST',
                                $parameter);
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if ($this->is_paid(trim($_POST['invoice'])))
        {
            $this->init_order($_POST['invoice']);

            return true;
        }
        $payment = $this->get_config();
        $merchant_id    = $payment['paypal_account'];               ///获取商户编号

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value)
        {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) ."\r\n\r\n";
        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

        // assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
        $order_sn = $_POST['invoice'];
        $memo = !empty($_POST['memo']) ? $_POST['memo'] : '';
        $action_note = $txn_id . '（' . Language::get('paypal_txn_id') . '）' . $memo;

        if (!$fp)
        {
            fclose($fp);

            return false;
        }
        else
        {
            fputs($fp, $header . $req);
            while (!feof($fp))
            {
                $res = fgets($fp, 1024);
                if (strcmp($res, 'VERIFIED') == 0)
                {
                    // check the payment_status is Completed or Pending
                    if ($payment_status != 'Completed' && $payment_status != 'Pending')
                    {
                        fclose($fp);

                        return false;
                    }
/*

                    // check that txn_id has not been previously processed
                    $sql = "SELECT COUNT(*) FROM `ecm_order_action` WHERE action_note LIKE '%" . strtr($txn_id, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%')) . "%'";
                    if ($GLOBALS['db']->getOne($sql) > 0)
                    {
                        fclose($fp);

                        return false;
                    }
*/

                    // check that receiver_email is your Primary PayPal email
                    if ($receiver_email != $merchant_id)
                    {
                        fclose($fp);

                        return false;
                    }

                    $this->init_order($order_sn);
                    // check that payment_amount/payment_currency are correct
                    if ($this->order->get_payable() != $payment_amount)
                    {
                        fclose($fp);
                        $this->err = 'money_inequalit';

                        return false;
                    }
                    if (CURRENCY != $payment_currency)
                    {
                        fclose($fp);

                        return false;
                    }

                    // process payment
                    $this->total_fee = $payment_amount;
                    $this->order_paid($order_sn);
                    $this->_ext_note = '[' . $action_note . ']';
                    fclose($fp);

                    return true;
                }
                elseif (strcmp($res, 'INVALID') == 0)
                {
                    // log for manual investigation
                    fclose($fp);

                    return false;
                }
            }
        }
    }

    /**
     *  获取附加信息
     *
     *  @author Garbin
     *  @return string
     */
    function get_ext_note()
    {
        return $this->_ext_note;
    }
}

?>