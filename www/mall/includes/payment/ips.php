<?php

/**
 * ECMall: ips֧��ϵͳ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ips.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/ips'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'ips_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.ips.com.cn';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'ips_account',  'type' => 'text',   'value' => ''),
        array('name' => 'ips_key',      'type' => 'text',   'value' => ''),
        array('name' => 'ips_currency', 'type' => 'select', 'value' => '01'),
        array('name' => 'ips_lang',     'type' => 'select', 'value' => 'GB')
    );

    return;
}

class ips extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
    * ����֧������
    * @param   array   $order_obj  ��������
    */

    function get_code($order_obj)
    {
        $payment    =   $this->get_config();
        $order      =   $order_obj->get_info();
        $billstr    = date('His', time());
        $datestr    = date('Ymd', time());
        $mer_code   = $payment['ips_account'];
        $billno     = str_pad($order_obj->get_pay_log(), 10, '0', STR_PAD_LEFT) . $billstr;
        $amount     = sprintf("%0.02f", $order['payable']);
        $strcert    = $payment['ips_key'];
        $strcontent = $billno . $amount . $datestr . 'RMB' . $strcert; // ǩ����֤�� //
        $signmd5    = MD5($strcontent);

        /*
        $def_url  = '<br /><form style="text-align:center;" action="https://pay.ips.com.cn/ipayment.aspx" method="post" target="_blank">';
        $def_url .= "<input type='hidden' name='Mer_code' value='" . $mer_code . "'>\n";
        $def_url .= "<input type='hidden' name='Billno' value='" . $billno . "'>\n";
        $def_url .= "<input type='hidden' name='Gateway_type' value='" . $payment['ips_currency'] . "'>\n";
        $def_url .= "<input type='hidden' name='Currency_Type'  value='RMB'>\n";
        $def_url .= "<input type='hidden' name='Lang'  value='" . $payment['ips_lang'] . "'>\n";
        $def_url .= "<input type='hidden' name='Amount'  value='" . $amount . "'>\n";
        $def_url .= "<input type='hidden' name='Date' value='" . $datestr . "'>\n";
        $def_url .= "<input type='hidden' name='DispAmount' value='" . $amount . "'>\n";
        $def_url .= "<input type='hidden' name='OrderEncodeType' value='2'>\n";
        $def_url .= "<input type='hidden' name='RetEncodeType' value='12'>\n";
        $def_url .= "<input type='hidden' name='Merchanturl' value='" . $this->get_respond_url(basename(__FILE__, '.php')) . "'>\n";
        $def_url .= "<input type='hidden' name='SignMD5' value='" . $signmd5 . "'>\n";
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form><br />";
        */

        $fields = array(
                'Mer_code' => $mer_code,
                'Billno' => $billno,
                'Gateway_type' => $payment['ips_currency'],
                'Currency_Type' => 'RMB',
                'Lang' => $payment['ips_lang'],
                'Amount' => $amount,
                'Date' => $datestr,
                'DispAmount' => $amount,
                'OrderEncodeType' => 2,
                'RetEncodeType' => 12,
                'Merchanturl' => $this->get_respond_url(basename(__FILE__, '.php')),
                'SignMD5' => $signmd5,
        );

        return $this->form_info('https://pay.ips.com.cn/ipayment.aspx',
                                'POST',
                                $fields);
    }

    function respond()
    {
        if ($this->is_paid(trim($_GET["billno"])))
        {
            return true;
        }
        $payment       = $this->get_config();
        $billno        = $_GET['billno'];
        $amount        = $_GET['amount'];
        $mydate        = $_GET['date'];
        $succ          = $_GET['succ'];
        $msg           = $_GET['msg'];
        $ipsbillno     = $_GET['ipsbillno'];
        $retEncodeType = $_GET['retencodetype'];
        $currency_type = $_GET['Currency_type'];
        $signature     = $_GET['signature'];
        $order_sn      = intval(substr($billno, 0, 10));

        $this->total_fee = $amount;

        if ($succ == 'Y')
        {
            $content = $billno . $amount . $mydate . $succ . $ipsbillno . $currency_type;
            $cert = $payment['ips_key'];
            $signature_1ocal = md5($content . $cert);

            if ($signature_1ocal == $signature)
            {
                $this->init_order($billno);

                if ($this->order->get_payable() != $amount)
                {
                   $this->err = 'money_inequalit';

                   return false;
                }
                $this->order_paid();

                return ORDER_STATUS_ACCEPTTED;
            }
            else
            {
                $this->err = 'wrong_sign';
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}

?>