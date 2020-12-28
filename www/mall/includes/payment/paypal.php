<?php

/**
 * ECMall: Paypal���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: paypal.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/paypal'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'paypal_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.paypal.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'NZD');

    /* ������Ϣ */
    $modules[$i]['config'] = array(
        array('name' => 'paypal_account', 'type' => 'text', 'value' => '')
    );

    return;
}

/**
 * ��
 */
class paypal extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'NZD');

    var $_ext_note = '';
    /**
     * ����֧������
     * @param   array   $order  ������Ϣ
     * @param   array   $payment    ֧����ʽ��Ϣ
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
        $def_url  = '<br /><form style="text-align:center;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">' .   // ����ʡ��
            "<input type='hidden' name='cmd' value='_xclick'>" .                             // ����ʡ��
            "<input type='hidden' name='business' value='$data_pay_account'>" .                 // �����ʺ�
            "<input type='hidden' name='item_name' value='$order_info[order_sn]'>" .                 // payment for
            "<input type='hidden' name='amount' value='$data_amount'>" .                        // �������
            "<input type='hidden' name='currency_code' value='$currency_code'>" .            // ����
            "<input type='hidden' name='return' value='$data_return_url'>" .                    // �����ҳ��
            "<input type='hidden' name='invoice' value='$data_order_id'>" .                      // ������
            "<input type='hidden' name='charset' value='utf-8'>" .                              // �ַ���
            "<input type='hidden' name='no_shipping' value='1'>" .                              // ��Ҫ��ͻ��ṩ�ջ���ַ
            "<input type='hidden' name='no_note' value=''>" .                                  // ����˵��
            "<input type='hidden' name='notify_url' value='$data_notify_url'>" .
            "<input type='hidden' name='rm' value='2'>" .
            "<input type='hidden' name='cancel_return' value='$cancel_return'>" .
            "<input type='submit' value='" . $GLOBALS['_LANG']['paypal_button'] . "'>" .                      // ��ť
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
     * ��Ӧ����
     */
    function respond()
    {
        if ($this->is_paid(trim($_POST['invoice'])))
        {
            $this->init_order($_POST['invoice']);

            return true;
        }
        $payment = $this->get_config();
        $merchant_id    = $payment['paypal_account'];               ///��ȡ�̻����

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
        $action_note = $txn_id . '��' . Language::get('paypal_txn_id') . '��' . $memo;

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
     *  ��ȡ������Ϣ
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