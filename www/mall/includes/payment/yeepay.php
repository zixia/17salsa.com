<?php

/**
 * ECMall: YeePay易宝插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: yeepay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/yeepay'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'yp_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'newbj <ffo@21cn.com>';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.yeepay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'yp_account', 'type' => 'text', 'value' => ''),
        array('name' => 'yp_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class yeepay extends Payment
{

    /* 支付的货币 */
    var $currency  = array('CNY');

    /**
     * 生成支付代码
     * @param   array   $order_obj  订单对象
     */
    function get_code($order_obj)
    {
        $payment = $this->get_config();
        $order   = $order_obj->get_info();
        $data_merchant_id = $payment['yp_account'];
        $data_order_id    = $order_obj->get_pay_log();
        $data_amount      = $order['payable'];
        $message_type     = 'Buy';
        $data_cur         = 'CNY';
        $product_id       = 'Order SN:' . $order['order_sn'];
        $product_cat      = '';
        $product_desc     = '';
        $address_flag     = '0';

        $data_return_url  = $this->get_respond_url(basename(__FILE__, '.php'));

        $data_pay_key     = $payment['yp_key'];
        $data_pay_account = $payment['yp_account'];
        $mct_properties   = '';
        $def_url = $message_type . $data_merchant_id . $data_order_id . $data_amount . $data_cur . $product_id . $product_cat
                             . $product_desc . $data_return_url . $address_flag . $mct_properties;
        $MD5KEY = hmac($def_url, $data_pay_key);

        /*
        $def_url  = "\n<form action='https://www.yeepay.com/app-merchant-proxy/node' method='post' target='_blank'>\n";
        $def_url .= "<input type='hidden' name='p0_Cmd' value='".$message_type."'>\n";
        $def_url .= "<input type='hidden' name='p1_MerId' value='".$data_merchant_id."'>\n";
        $def_url .= "<input type='hidden' name='p2_Order' value='".$data_order_id."'>\n";
        $def_url .= "<input type='hidden' name='p3_Amt' value='".$data_amount."'>\n";
        $def_url .= "<input type='hidden' name='p4_Cur' value='".$data_cur."'>\n";
        $def_url .= "<input type='hidden' name='p5_Pid' value='".$product_id."'>\n";
        $def_url .= "<input type='hidden' name='p6_Pcat' value='".$product_cat."'>\n";
        $def_url .= "<input type='hidden' name='p7_Pdesc' value='".$product_desc."'>\n";
        $def_url .= "<input type='hidden' name='p8_Url' value='".$data_return_url."'>\n";
        $def_url .= "<input type='hidden' name='p9_SAF' value='".$address_flag."'>\n";
        $def_url .= "<input type='hidden' name='pa_MP' value='".$mct_properties."'>\n";
        $def_url .= "<input type='hidden' name='hmac' value='".$MD5KEY."'>\n";
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form>\n";
        */

        $fields = array(
                'p0_Cmd'  =>  $message_type,
                'p1_MerId'  =>  $data_merchant_id,
                'p2_Order'  =>  $data_order_id,
                'p3_Amt'  =>  $data_amount,
                'p4_Cur'  =>  $data_cur,
                'p5_Pid'  =>  $product_id,
                'p6_Pcat'  =>  $product_cat,
                'p7_Pdesc'  =>  $product_desc,
                'p8_Url'  =>  $data_return_url,
                'p9_SAF'  =>  $address_flag,
                'pa_MP'  =>  $mct_properties,
                'hmac'  =>  $MD5KEY,
        );


        return $this->form_info('https://www.yeepay.com/app-merchant-proxy/node',
                                'POST',
                                $fields);
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if ($this->is_paid(trim($_REQUEST['r6_Order'])))
        {
            return true;
        }
        $payment        = $this->get_config();

        $merchant_id    = $payment['yp_account'];       // 获取商户编号
        $merchant_key   = $payment['yp_key'];           // 获取秘钥

        $message_type   = trim($_REQUEST['r0_Cmd']);
        $succeed        = trim($_REQUEST['r1_Code']);   // 获取交易结果,1成功,-1失败
        $trxId          = trim($_REQUEST['r2_TrxId']);
        $amount         = trim($_REQUEST['r3_Amt']);    // 获取订单金额
        $cur            = trim($_REQUEST['r4_Cur']);    // 获取订单货币单位
        $product_id     = trim($_REQUEST['r5_Pid']);    // 获取产品ID
        $orderid        = trim($_REQUEST['r6_Order']);  // 获取订单ID
        $userId         = trim($_REQUEST['r7_Uid']);    // 获取产品ID
        $merchant_param = trim($_REQUEST['r8_MP']);     // 获取商户私有参数
        $bType          = trim($_REQUEST['r9_BType']);  // 获取订单ID

        $mac            = trim($_REQUEST['hmac']);      // 获取安全加密串

        $this->total_fee = $amount;

        /* 取得订单对象 */
        $this->init_order($orderid);

        ///生成加密串,注意顺序
        $ScrtStr  = $merchant_id . $message_type . $succeed . $trxId . $amount . $cur . $product_id .
                      $orderid . $userId . $merchant_param . $bType;
        $mymac    = hmac($ScrtStr, $merchant_key);

        $v_result = false;

        if (strtoupper($mac) == strtoupper($mymac))
        {
            if ($succeed == '1')
            {
                ///支付成功
                $v_result = true;
                $this->order_paid();

                return ORDER_STATUS_ACCEPTTED;

            }
        }

        return $v_result;
    }
}

function hmac($data, $key)
{
    // RFC 2104 HMAC implementation for php.
    // Creates an md5 HMAC.
    // Eliminates the need to install mhash to compute a HMAC
    // Hacked by Lance Rushing(NOTE: Hacked means written)

    $key  = ecm_iconv('GB2312', 'UTF8', $key);
    $data = ecm_iconv('GB2312', 'UTF8', $data);

    $b = 64; // byte length for md5
    if (strlen($key) > $b)
    {
        $key = pack('H*', md5($key));
    }

    $key    = str_pad($key, $b, chr(0x00));
    $ipad   = str_pad('', $b, chr(0x36));
    $opad   = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack('H*', md5($k_ipad . $data)));
}

?>