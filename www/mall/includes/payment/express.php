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
 * $Id: express.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/express'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'express_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://express.ips.com.cn/';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'ips_account',  'type' => 'text',   'value' => ''),
        array('name' => 'ips_key',      'type' => 'text',   'value' => '')
    );

    return;
}

class express extends Payment
{

    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
    * ����֧������
    * @param   array   $order  ��������
    */
    function get_code($order_obj)
    {
        $payment    = $this->get_config();
        $order      = $order_obj->get_info();
        $mer_code   = $payment['ips_account'];
        $billno     = $order_obj->get_pay_log();
        $amount     = sprintf("%0.02f", $order['payable']);
        $strcert    = $payment['ips_key'];

        $remark     = 'Order SN:' . $order['order_sn'];
        $signmd5    = MD5($mer_code . $billno . $amount . $remark . $strcert);

        /*
        $def_url  = '<br /><form style="text-align:center;" action="http://express.ips.com.cn/pay/payment.asp" method="post" target="_blank" onsubmit="if(document.getElementById(\'paybank\').value==\'\'){alert(\''.$GLOBALS['_LANG']['please_select_bank'].'\');return false;}">';
        $def_url .= "<input type='hidden' name='Merchant' value='" . $mer_code . "'>\n"; //�̻��ʺ�
        $def_url .= "<input type='hidden' name='Billno' value='" . $billno . "'>\n";
        $def_url .= "<input type='hidden' name='Amount'  value='" . $amount . "'>\n";
        $def_url .= "<input type='hidden' name='Remark' value=''>\n";
        $def_url .= "<input type='hidden' name='BackUrl' value='" . return_url(basename(__FILE__, '.php')) . "'>\n";
        $def_url .= "<input type='hidden' name='Sign' value='" .$signmd5 . "'>\n";
        $def_url .= $GLOBALS['_LANG']['please_select_bank'] . ':';
        $def_url .= "<select name='paybank' id='paybank'>";
        $def_url .= "<option value=''>". $GLOBALS['_LANG']['please_select_bank'] ."</option>";
        $def_url .= "<option value='00018'>" . $GLOBALS['_LANG']['icbc'] . "</option>";
        $def_url .= "<option value='00021'>" . $GLOBALS['_LANG']['cmb'] . "</option>";
        $def_url .= "<option value='00003'>" . $GLOBALS['_LANG']['ccb'] . "</option>";
        $def_url .= "<option value='00017'>" . $GLOBALS['_LANG']['agricultural_bank'] . "</option>";
        $def_url .= "<option value='00013'>" . $GLOBALS['_LANG']['cmbc'] . "</option>";
        $def_url .= "<option value='00030'>" . $GLOBALS['_LANG']['cebbank'] . "</option>";
        $def_url .= "<option value='00016'>" . $GLOBALS['_LANG']['cib'] . "</option>";
        $def_url .= "<option value='00111'>" . $GLOBALS['_LANG']['boc'] . "</option>";
        $def_url .= "<option value='00211'>" . $GLOBALS['_LANG']['bankcomm'] . "</option>";
        $def_url .= "<option value='00311'>" . $GLOBALS['_LANG']['bankcommsh'] . "</option>";
        $def_url .= "<option value='00411'>" . $GLOBALS['_LANG']['gdb'] . "</option>";
        $def_url .= "<option value='00023'>" . $GLOBALS['_LANG']['sdb'] . "</option>";
        $def_url .= "<option value='00032'>" . $GLOBALS['_LANG']['spdb'] . "</option>";
        $def_url .= "<option value='00511'>" . $GLOBALS['_LANG']['cnbb'] . "</option>";
        $def_url .= "<option value='00611'>" . $GLOBALS['_LANG']['gzcb'] . "</option>";
        $def_url .= "<option value='00711'>" . $GLOBALS['_LANG']['chinapost'] . "</option>";
        $def_url .= "<option value='00811'>" . $GLOBALS['_LANG']['hxb'] . "</option>";
        $def_url .= "</select><br><input type='submit' value='".$GLOBALS['_LANG']['pay_button']."'></form><br />";
        */

        $fields = array(
                'Merchant' =>   $mer_code,
                'Billno' =>   $billno,
                'Amount' =>   $amount,
                'Remark' =>   $remark,
                'BackUrl' =>   $this->get_respond_url(basename(__FILE__, '.php')),
                'Sign' =>   $signmd5,
                'paybank' =>   array(
                    'type'    => 'select',
                    'data'    => array(
                        ''        => Language::get('please_select_bank'),
                        '00018'   => Language::get('icbc'),
                        '00021'   => Language::get('cmb'),
                        '00003'   => Language::get('ccb'),
                        '00017'   => Language::get('agricultural_bank'),
                        '00013'   => Language::get('cmbc'),
                        '00030'   => Language::get('cebbank'),
                        '00016'   => Language::get('cib'),
                        '00111'   => Language::get('boc'),
                        '00211'   => Language::get('bankcomm'),
                        '00311'   => Language::get('bankcommsh'),
                        '00411'   => Language::get('gdb'),
                        '00023'   => Language::get('sdb'),
                        '00511'   => Language::get('cnbb'),
                        '00611'   => Language::get('gzcb'),
                        '00711'   => Language::get('chinapost'),
                        '00811'   => Language::get('hxb'),
                    )
                ),
        );

        return $this->form_info('http://express.ips.com.cn/pay/payment.asp',
                                'POST',
                                $fields);
    }

    function respond()
    {
        if ($this->is_paid(trim($_REQUEST["BillNo"])))
        {
            return true;
        }
        $payment = $this->get_config();
        $merchant = $payment['ips_account']; // �̻���
        $amount   = $_REQUEST['Amount'];     //���
        $billno   = $_REQUEST['BillNo'];     //������
        $success  = $_REQUEST['Success'];    //�Ƿ�ɹ�Y/N
        $remark   = $_REQUEST['Remark'];     //������Ϣ
        $sign     = $_REQUEST['Sign'];

        $this->total_fee = $amount;

        $strcert = $payment['ips_key'];
        $signmd5  = md5($merchant . $billno . $amount . $remark . $success . $payment['ips_key']);
        if ($sign != $signmd5)
        {
            echo $billno;
            return false;
        }

        if ($success != 'Y')
        {
            return false;
        }
        else
        {
            $this->init_order($billno);
            if ($order_obj->get_payable() != $amount)
            {
                $this->err = 'money_inequalit';

                return false;
            }
        }
        $fp = @fopen("http://express.ips.com.cn/merchant/confirm.asp?Merchant=".$merchant ."&BillNo=".$billno."&Amount=".$amount."&Success=".$success."&Remark=".$remark. "&sign=".$sign, 'rb');
        if (!empty($fp))
        {
            fclose($fp);
        }

        $this->order_paid();

        return ORDER_STATUS_ACCEPTTED;
    }
}

?>