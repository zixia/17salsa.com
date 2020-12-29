<?php

/**
 * ECMall: 快钱插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: kuaiqian.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/kuaiqian'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'kq_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.99bill.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'kq_account', 'type' => 'text', 'value' => ''),
        array('name' => 'kq_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class kuaiqian extends Payment
{

    /* 支付的货币 */
    var $currency  = array('CNY');

   /**
     * 将变量值不为空的参数组成字符串
     * @param   string   $strs  参数字符串
     * @param   string   $key   参数键名
     * @param   string   $val   参数键对应值
     */
    function append_param($strs, $key, $val)
    {
        if ($strs != "")
        {
            if ($val != "")
            {
                $strs .= '&'.$key.'='.$val;
            }
        }
        else
        {
            if ($val != "")
            {
                $strs .= $key.'='.$val;
            }
        }
        return $strs;
    }

   /**
     * 生成支付代码
     * @param   array   $order_obj  订单对象
     */
    function get_code($order_obj)
    {
        $payment    =   $this->get_config();
        $order      =   $order_obj->get_info();
        $msg_val = '';

        $input_charset = 1;
        $msg_val = $this->append_param($msg_val, 'inputCharset', $input_charset);

        $page_url = $this->get_respond_url(basename(__FILE__, '.php'));
        $msg_val = $this->append_param($msg_val, 'pageUrl', $page_url);

        $bg_url = '';
        $msg_val = $this->append_param($msg_val, 'bgUrl', $bg_url);

        $version = 'v2.0';
        $msg_val = $this->append_param($msg_val, 'version', $version);

        $language = 1;
        $msg_val = $this->append_param($msg_val, 'language', $language);

        $sign_type = 1;
        $msg_val = $this->append_param($msg_val, 'signType', $sign_type);

        $merchant_acct_id = $payment['kq_account'];
        $msg_val = $this->append_param($msg_val, 'merchantAcctId', $merchant_acct_id);

        $payer_name = !empty($order['user_name']) ? $order['user_name'] : '';
        $msg_val = $this->append_param($msg_val, 'payerName', $payer_name);

        $payer_contact_type = '';
        $msg_val = $this->append_param($msg_val, 'payerContactType', $payer_contact_type);

        $payer_contact = '';
        $msg_val = $this->append_param($msg_val, 'payerContact', $payer_contact);

        $order_id = $order_obj->get_pay_log();
        $msg_val = $this->append_param($msg_val, 'orderId', $order_id);

        $order_amount = $order['payable'] * 100;
        $msg_val = $this->append_param($msg_val, 'orderAmount', $order_amount);

        $order_time = local_date('YmdHis', $order['add_time']);
        $msg_val = $this->append_param($msg_val, 'orderTime', $order_time);

        $product_name = 'Order SN:' . $order['order_sn'];
        $msg_val = $this->append_param($msg_val, 'productName', $product_name);

        $product_num = '';
        $msg_val = $this->append_param($msg_val, 'productNum', $product_num);

        $product_id = '';
        $msg_val = $this->append_param($msg_val, 'productId', $product_id);

        $product_desc = '';
        $msg_val = $this->append_param($msg_val, 'productDesc', $product_desc);

        $ext1 = '';
        $msg_val = $this->append_param($msg_val, 'ext1', $ext1);

        $ext2 = '';
        $msg_val = $this->append_param($msg_val, 'ext2', $ext2);

        $pay_type = '00';
        $msg_val = $this->append_param($msg_val, 'payType', $pay_type);

        $bank_id = '';
        $msg_val = $this->append_param($msg_val, 'bankId', $bank_id);

        $pid = '';
        $msg_val = $this->append_param($msg_val, 'pid', $pid);

        $key = $payment['kq_key'];
        $msg_val = $this->append_param($msg_val, 'key', $key);

        $this->total_fee = $order_amount;

        $sign_msg= strtoupper(md5($msg_val));

        /*

        $def_url  = '<br /><form  name="kqPay" style="text-align:center;" action="https://www.99bill.com/gateway/recvMerchantInfoAction.htm" method="post" target="_blank">';
        $def_url .= "<input type='hidden' name='inputCharset' value='".$input_charset."'>\n";
        $def_url .= "<input type='hidden' name='bgUrl' value='".$bg_url."'>\n";
        $def_url .= "<input type='hidden' name='pageUrl' value='".$page_url."'>\n";
        $def_url .= "<input type='hidden' name='version' value='".$version."'>\n";
        $def_url .= "<input type='hidden' name='language' value='".$language."'>\n";
        $def_url .= "<input type='hidden' name='signType' value='".$sign_type."'>\n";
        $def_url .= "<input type='hidden' name='signMsg' value='".$sign_msg."'>\n";
        $def_url .= "<input type='hidden' name='merchantAcctId' value='".$merchant_acct_id."'>\n";
        $def_url .= "<input type='hidden' name='payerName' value='".$payer_name."'>\n";
        $def_url .= "<input type='hidden' name='payerContactType' value='".$payer_contact_type."'>\n";
        $def_url .= "<input type='hidden' name='payerContact' value='".$payer_contact."'>\n";
        $def_url .= "<input type='hidden' name='orderId' value='".$order_id."'>\n";
        $def_url .= "<input type='hidden' name='orderAmount' value='".$order_amount."'>\n";
        $def_url .= "<input type='hidden' name='orderTime' value='".$order_time."'>\n";
        $def_url .= "<input type='hidden' name='productName' value='".$product_name."'>\n";
        $def_url .= "<input type='hidden' name='productNum' value='".$product_num."'>\n";
        $def_url .= "<input type='hidden' name='productId' value='".$product_id."'>\n";
        $def_url .= "<input type='hidden' name='productDesc' value='".$product_desc."'>\n";
        $def_url .= "<input type='hidden' name='ext1' value='".$ext1."'>\n";
        $def_url .= "<input type='hidden' name='ext2' value='".$ext2."'>\n";
        $def_url .= "<input type='hidden' name='payType' value='".$pay_type."'>\n";
        $def_url .= "<input type='hidden' name='bankId' value='".$bank_id."'>\n";
        $def_url .= "<input type='hidden' name='pid' value='".$pid."'>\n";
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form><br />";
        */

        $fields     =   array(
                'inputCharset'      =>  $input_charset,
                'bgUrl'             =>  $bg_url,
                'pageUrl'           =>  $page_url,
                'version'           =>  $version,
                'language'          =>  $language,
                'signType'          =>  $sign_type,
                'signMsg'           =>  $sign_msg,
                'merchantAcctId'    =>  $merchant_acct_id,
                'payerName'         =>  $payer_name,
                'payerContactType'  =>  $payer_contact_type,
                'payerContact'      =>  $payer_contact,
                'orderId'           =>  $order_id,
                'orderAmount'       =>  $order_amount,
                'orderTime'         =>  $order_time,
                'productName'       =>  $product_name,
                'productNum'        =>  $product_num,
                'productId'         =>  $product_id,
                'productDesc'       =>  $product_desc,
                'ext1'              =>  $ext1,
                'ext2'              =>  $ext2,
                'payType'           =>  $pay_type,
                'bankId'            =>  $bank_id,
                'pid'               =>  $pid,
        );

        return $this->form_info('https://www.99bill.com/gateway/recvMerchantInfoAction.htm',
                                'POST',
                                $fields);
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $payment    =   $this->get_config();

        $merchant_id    = $payment['kq_account'];               ///获取商户编号
        $key   = $payment['kq_key'];                   ///获取秘钥



        $msg_val = '';

        $merchant_acct_id=trim(@$_REQUEST['merchantAcctId']);
        $msg_val = $this->append_param($msg_val, 'merchantAcctId', $merchant_acct_id);

        $version=trim(@$_REQUEST['version']);
        $msg_val = $this->append_param($msg_val, 'version', $version);

        $language=trim(@$_REQUEST['language']);
        $msg_val = $this->append_param($msg_val, 'language', $language);

        $sign_type=trim(@$_REQUEST['signType']);
        $msg_val = $this->append_param($msg_val, 'signType', $sign_type);

        $pay_type=trim(@$_REQUEST['payType']);
        $msg_val = $this->append_param($msg_val, 'payType', $pay_type);

        $bank_id=trim(@$_REQUEST['bankId']);
        $msg_val = $this->append_param($msg_val, 'bankId', $bank_id);

        $order_id=trim(@$_REQUEST['orderId']);
        $msg_val = $this->append_param($msg_val, 'orderId', $order_id);

        $order_time=trim(@$_REQUEST['orderTime']);
        $msg_val = $this->append_param($msg_val, 'orderTime', $order_time);

        $order_amount=trim(@$_REQUEST['orderAmount']);
        $msg_val = $this->append_param($msg_val, 'orderAmount', $order_amount);

        $deal_id=trim(@$_REQUEST['dealId']);
        $msg_val = $this->append_param($msg_val, 'dealId', $deal_id);

        $bank_deal_id=trim(@$_REQUEST['bankDealId']);
        $msg_val = $this->append_param($msg_val, 'bankDealId', $bank_deal_id);

        $deal_time=trim(@$_REQUEST['dealTime']);
        $msg_val = $this->append_param($msg_val, 'dealTime', $deal_time);

        //获取实际支付金额
        ///单位为分
        ///比方 2 ，代表0.02元
        $pay_amount=trim(@$_REQUEST['payAmount']);
        $msg_val = $this->append_param($msg_val, 'payAmount', $pay_amount);

        //获取交易手续费
        ///单位为分
        ///比方 2 ，代表0.02元
        $fee=trim(@$_REQUEST['fee']);
        $msg_val = $this->append_param($msg_val, 'fee', $fee);

        $ext1=trim(@$_REQUEST['ext1']);
        $msg_val = $this->append_param($msg_val, 'ext1', $ext1);

        $ext2=trim(@$_REQUEST['ext2']);
        $msg_val = $this->append_param($msg_val, 'ext2', $ext2);

        //获取处理结果
        ///10代表 成功; 11代表 失败
        ///00代表 下订单成功（仅对电话银行支付订单返回）;01代表 下订单失败（仅对电话银行支付订单返回）
        $pay_result=trim(@$_REQUEST['payResult']);
        $msg_val = $this->append_param($msg_val, 'payResult', $pay_result);

        //获取错误代码
        ///详细见文档错误代码列表
        $err_code=trim(@$_REQUEST['errCode']);
        $msg_val = $this->append_param($msg_val, 'errCode', $err_code);

        $msg_val = $this->append_param($msg_val, 'key', $key);

        //获取加密签名串
        $sign_msg=trim(@$_REQUEST['signMsg']);

        if ($this->is_paid(trim($order_id)))
        {
            return true;
        }
        if ($merchant_acct_id != $merchant_id)
        {
            return false;
        }
        if (strtoupper($sign_msg) == strtoupper(md5($msg_val)))
        {
            if ($pay_result == 10 || $pay_result == 00)
            {
                $this->init_order($order_id);
                $this->order_paid();

                return ORDER_STATUS_ACCEPTTED;
            }
        }

        return false;
    }
}

?>
