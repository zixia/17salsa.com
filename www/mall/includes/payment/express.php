<?php

/**
 * ECMall: ips支付系统插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: express.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/express'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'express_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://express.ips.com.cn/';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'ips_account',  'type' => 'text',   'value' => ''),
        array('name' => 'ips_key',      'type' => 'text',   'value' => '')
    );

    return;
}

class express extends Payment
{

    /* 支付的货币 */
    var $currency  = array('CNY');

    /**
    * 生成支付代码
    * @param   array   $order  订单对象
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
        $def_url .= "<input type='hidden' name='Merchant' value='" . $mer_code . "'>\n"; //商户帐号
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
        $merchant = $payment['ips_account']; // 商户号
        $amount   = $_REQUEST['Amount'];     //金额
        $billno   = $_REQUEST['BillNo'];     //订单号
        $success  = $_REQUEST['Success'];    //是否成功Y/N
        $remark   = $_REQUEST['Remark'];     //附加信息
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