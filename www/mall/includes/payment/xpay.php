<?php

/**
 * ECMall: 易付通插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: xpay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/xpay'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'xpay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.xpay.cn';

    /* 版本号 */
    $modules[$i]['version'] = '2.0.0';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config']  = array(
                                array('name' => 'xpay_tid', 'type' => 'text', 'value' => ''),
                                array('name' => 'xpay_key', 'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class xpay extends Payment
{
    /* 支付的货币 */
    var $currency  = array('CNY');

    /**
     * 生成支付代码
     * @param   array   $order_obj  订单对象
     */
    function get_code($order_obj)
    {
        $payment    =   $this->get_config();
        $order      =   $order_obj->get_info();
        $data_order_id   = $order_obj->get_pay_log();
        $data_amount     = $order['payable'];
        $data_return_url = $this->get_respond_url('xpay');
        $data_tid        = $payment['xpay_tid'];
        $data_key        = md5("$payment[xpay_key]:$data_amount,$data_order_id,$data_tid,bank,,sell,,2.0");

        /*
        $def_url  = '<br /><form style="text-align:center;" method=post action="http://pay.xpay.cn/pay.aspx">';
        $def_url  .= "<input type=hidden name=tid value='$data_tid'>";           // 商户交易号
        $def_url  .= "<input type=hidden name=bid value='$data_order_id'>";      // 订单号
        $def_url  .= "<input type=hidden name=prc value='$data_amount'>";        // 订单总金额
        $def_url  .= "<input type=hidden name=card value='bank'>";               // 默认支付方式
        $def_url  .= "<input type=hidden name=scard value=''>";                  // 支持支付种类
        $def_url  .= "<input type=hidden name=actioncode value='sell'>";         // 交易码
        $def_url  .= "<input type=hidden name=actionParameter value=''>";        // 业务代码参数
        $def_url  .= "<input type=hidden name=ver value='2.0'>";                 // 版本号
        $def_url  .= "<input type=hidden name=md value='$data_key'>";            // 订单MD5校验码
        $def_url  .= "<input type=hidden name=url value='$data_return_url'>";    // 支付交易完成后返回到该url，支付结果以get方式发送
        $def_url  .= "<input type='hidden' name='pdt' value='$data_order_id'>";  // 产品名称或交易说明
        $def_url  .= "<input type='hidden' name='type' value=''>";               // 产品类型或交易分类
        $def_url  .= "<input type='hidden' name='username' value=''>";           // 消费购买用户名
        $def_url  .= "<input type='hidden' name='lang' value='gb2312'>";         // 语言
        $def_url  .= "<input type='hidden' name='remark1' value=''>";            // 备注字段
        $def_url  .= "<input type='hidden' name='disableemail' value=''>";       // 隐藏交易邮箱
        $def_url  .= "<input type='hidden' name='disablealert' value=''>";       // 隐藏弹窗提示
        $def_url  .= "<input type='hidden' name='sitename' value=''>";           // 商户网站名称
        $def_url  .= "<input type='hidden' name='siteurl' value=''>";            // 商户网站域名
        $def_url  .= "<input type=submit value='" . $GLOBALS['_LANG']['xpay_button'] . "'>";
        $def_url  .= "</form>";
        */

        $fields =   array(
                'tid'               =>  $data_tid,
                'bid'               =>  $data_order_id,
                'prc'               =>  $data_amount,
                'card'              =>  'bank',
                'scard'             =>  '',
                'actioncode'        =>  'sell',
                'actionParameter'   =>  '',
                'ver'               =>  '2.0',
                'md'                =>  $data_key,
                'url'               =>  $data_return_url,
                'pdt'               =>  $data_order_id,
                'type'              =>  '',
                'username'          =>  '',
                'lang'              =>  'gb2312',
                'remark1'           =>  'Order SN:' . $order['order_sn'],
                'disableemail'      =>  '',
                'disablealert'      =>  '',
                'sitename'          =>  '',
                'siteurl'           =>  '',
        );

        return $this->form_info('http://pay.xpay.cn/pay.aspx',
                                'POST',
                                $fields,
                                'xpay_button');
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if ($this->is_paid(trim($_REQUEST["bid"])))
        {
            return true;
        }
        /*取返回参数*/
        $tid             = $_REQUEST["tid"];             // 商户唯一交易号
        $bid             = $_REQUEST["bid"];             // 商户网站订单号
        $sid             = $_REQUEST["sid"];             // 易付通交易成功 流水号
        $prc             = $_REQUEST["prc"];             // 支付的金额
        $actionCode      = $_REQUEST["actioncode"];      // 交易码
        $actionParameter = $_REQUEST["actionparameter"]; // 业务代码
        $card            = $_REQUEST["card"];            // 支付方式
        $success         = $_REQUEST["success"];         // 成功标志，
        $bankcode        = $_REQUEST["bankcode"];        // 支付银行
        $remark1         = $_REQUEST["remark1"];         // 备注信息
        $username        = $_REQUEST["username"];        // 商户网站支付用户
        $md              = $_REQUEST["md"];              // 32位md5加密数据

        $this->total_fee = $prc;

        $payment = $this->get_config();
        if ($success == 'false')
        {
            return false;
        }
        // 验证数据是否正确
        $ymd = md5($payment['xpay_key'] . ":" . $bid . "," . $sid . "," . $prc . "," . $actionCode  ."," . $actionParameter . "," . $tid . "," . $card . "," . $success); // 本地进行数据加密
        if($md != $ymd)
        {
            return false;
        }
        else
        {
            $this->init_order($bid);
            $this->order_paid();

            return ORDER_STATUS_ACCEPTTED;
        }
    }
}

?>