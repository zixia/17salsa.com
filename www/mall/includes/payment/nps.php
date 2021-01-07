<?php

/**
 * ECMall: NPS支付插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: nps.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/*
    对于使用NPS实时反馈接口的商户请注意：

    为了从根本上解决订单支付成功而商户收不到反馈信息的问题(简称掉单).
    我公司决定在信息反馈方面实行服务器端对服务器端的反馈方式.即客户支付过后.
    我们系统会对商户的网站进行两次支付信息的反馈(即对同一笔订单信息进行两次反馈).
    第一次是服务器端对服务器端的反馈.第二次是以页面的形式反馈.两次反馈的时延差在10秒之内.
    请商户那边做好对我们反馈信息的处理. 对我们系统反馈相同的订单信息您那边只
    做一次处理就可以了.以确保消费者的每一笔订单信息在您那边只得到一次相应的服务!!
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}


include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/nps'));

/**
 * 模块信息
 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'nps_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author'] = 'NPS CORP.';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.nps.cn';

    /* 版本号 */
    $modules[$i]['version'] = '4.0';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'nps_account', 'type' => 'text', 'value' => ''),
        array('name' => 'nps_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

class nps extends Payment
{

    /* 支付的货币 */
    var $currency  = array('CNY');

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     */
    function get_code($order_obj)
    {
        $payment     = $this->get_config();
        $order       = $order_obj->get_info();
        $m_id        = trim($payment['nps_account']);
        $m_orderid   = $order_obj->get_pay_log();
        $m_oamount   = $order['payable'];
        $m_ocurrency = '1';
        $m_url       = $this->get_respond_url(basename(__FILE__, '.php'));
        $m_language  = '1';
        $s_name      = 'null';
        $s_addr      = 'null';
        $s_postcode  = 'null';
        $s_tel       = 'null';
        $s_eml       = 'null';
        $r_name      = 'null';
        $r_addr      = 'null';
        $r_postcode  = 'null';
        $r_tel       = 'null';
        $r_eml       = 'null';
        $m_ocomment  = 'Order SN:' . $order['order_sn'];
        $modate      = date('y-m-d H:i:s',time());
        $m_status    = 0;

        //组织订单信息
        $m_info = $m_id . '|' . $m_orderid . '|' . $m_oamount . '|' . $m_ocurrency . '|' . $m_url . '|' . $m_language;
        $s_info = $s_name . '|' . $s_addr . '|' . $s_postcode . '|' . $s_tel . '|' . $s_eml;
        $r_info = $r_name . '|' . $r_addr . '|' . $r_postcode . '|' . $r_tel . '|' . $r_eml . '|' . $m_ocomment . '|' . $m_status . '|' . $modate;

        $OrderInfo = $m_info . '|' . $s_info . '|' . $r_info;

        //订单信息先转换成HEX，然后再加密
        $key = $payment['nps_key'];     //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致

        $OrderInfo = $this->StrToHex($OrderInfo);
        $digest = strtoupper(md5($OrderInfo . $key));

        /*
        $def_url =  "<form method=post action='https://payment.nps.cn/PHPReceiveMerchantAction.do' target='_blank'>";
        $def_url .= "<input type=HIDDEN name='OrderMessage' value='" . $OrderInfo . "'>";
        $def_url .= "<input type=HIDDEN name='digest' value='" . $digest . "'>";
        $def_url .= "<input type=HIDDEN name='M_ID' value='" . $m_id . "'>";
        $def_url .= "<input type=submit value='"  . $GLOBALS['_LANG']['pay_button'] .  "'>";

        $def_url .= '</form>';
        */

        $fields   = array(
                'OrderMessage'  => $OrderInfo,
                'digest'        => $digest,
                'M_ID'          => $m_id
        );

        return $this->form_info('https://payment.nps.cn/PHPReceiveMerchantAction.do',
                                'POST',
                                $fields);
    }

    /**
     * 响应操作
     */

    function respond()
    {
        if ($this->is_paid(trim($_POST['m_orderid'])))
        {
            return true;
        }
        $payment     = $this->get_config();

        $m_id        = $_POST['m_id'];        // 商家号
        $m_orderid   = $_POST['m_orderid'];   // 商家订单号
        $m_oamount   = $_POST['m_oamount'];   // 支付金额
        $m_ocurrency = $_POST['m_ocurrency']; // 币种
        $m_language  = $_POST['m_language'];  // 语言选择
        $s_name      = $_POST['s_name'];      // 消费者姓名
        $s_addr      = $_POST['s_addr'];      // 消费者住址
        $s_postcode  = $_POST['s_postcode'];  // 邮政编码
        $s_tel       = $_POST['s_tel'];       // 消费者联系电话
        $s_eml       = $_POST['s_eml'];       // 消费者邮件地址
        $r_name      = $_POST['r_name'];      // 消费者姓名
        $r_addr      = $_POST['r_addr'];      // 收货人住址
        $r_postcode  = $_POST['r_postcode'];  // 收货人邮政编码
        $r_tel       = $_POST['r_tel'];       // 收货人联系电话
        $r_eml       = $_POST['r_eml'];       // 收货人电子地址
        $m_ocomment  = $_POST['m_ocomment'];  // 备注
        $State       = $_POST['m_status'];    // 支付状态2成功,3失败
        $modate      = $_POST['modate'];      // 返回日期
        $order_sn    =  $_POST['m_orderid'];

        $this->total_fee = $m_oamount;

        //接收组件的加密
        $OrderInfo   = $_POST['OrderMessage'];// 订单加密信息
        $signMsg     = $_POST['Digest'];      // 密匙

        //接收新的md5加密认证
        $newmd5info  = $_POST['newmd5info'];

        //检查签名
        $key    = $payment['nps_key']; //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致
        $digest = strtoupper(md5($OrderInfo . $key));

        //新的整合md5加密
        $newtext      = $m_id . $m_orderid . $m_oamount . $key . $State;
        $newMd5digest = strtoupper(md5($newtext));

        if ($digest == $signMsg)
        {
            //解密
            //$decode = $DES->Descrypt($OrderInfo, $key);
            $OrderInfo = $this->HexToStr($OrderInfo);
            //md5密匙认证
            if ($newmd5info == $newMd5digest)
            {
                if ($State == 2)
                {
                    $this->init_order($m_orderid);
                    $this->order_paid();

                    return ORDER_STATUS_ACCEPTTED;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
       }
       else
       {
            return false;
        }
    }

    function StrToHex($string)
    {
        $hex = '';

        for ($i = 0, $count = strlen($string); $i < $count; $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }

        return strtoupper($hex);
    }

    function HexToStr($hex)
    {
        $string = '';

        for ($i = 0, $count = strlen($hex) - 1; $i < $count; $i += 2)
        {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
}

?>