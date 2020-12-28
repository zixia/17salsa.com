<?php

/**
 * ECMall: 网银在线插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: chinabank.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/chinabank'));

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'chinabank_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 支付费用 */
    $modules[$i]['pay_fee'] = '2.5%';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.chinabank.com.cn';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'chinabank_account', 'type' => 'text', 'value' => ''),
        array('name' => 'chinabank_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class chinabank extends Payment
{
    /* 支付的货币 */
    var $currency  = array('CNY');

    /**
     * 生成支付代码
     * @param   array   $order      订单对象
     */
    function get_code($order_obj)
    {
        $payment            = $this->get_config();
        $order              = $order_obj->get_info();
        $data_vid           = trim($payment['chinabank_account']);
        $data_orderid       = $order_obj->get_pay_log();
        $data_vamount       = $order['payable'];
        $data_vmoneytype    = 'CNY';
        $data_vpaykey       = trim($payment['chinabank_key']);
        $data_vreturnurl    = $this->get_respond_url(basename(__FILE__, '.php'));

        $MD5KEY =$data_vamount.$data_vmoneytype.$data_orderid.$data_vid.$data_vreturnurl.$data_vpaykey;
        $MD5KEY = strtoupper(md5($MD5KEY));

        /*
        $def_url  = '<br /><form style="text-align:center;" method=post action="https://pay3.chinabank.com.cn/PayGate" target="_blank">';
        $def_url .= "<input type=HIDDEN name='v_mid' value='".$data_vid."'>";
        $def_url .= "<input type=HIDDEN name='v_oid' value='".$data_orderid."'>";
        $def_url .= "<input type=HIDDEN name='v_amount' value='".$data_vamount."'>";
        $def_url .= "<input type=HIDDEN name='v_moneytype'  value='".$data_vmoneytype."'>";
        $def_url .= "<input type=HIDDEN name='v_url'  value='".$data_vreturnurl."'>";
        $def_url .= "<input type=HIDDEN name='v_md5info' value='".$MD5KEY."'>";
        $def_url .= "<input type=submit value='" .$GLOBALS['_LANG']['pay_button']. "'>";
        $def_url .= "</form>";
       */

        $fields  = array(
            'v_mid'         => $data_vid,
            'v_oid'         => $data_orderid,
            'v_amount'      => $data_vamount,
            'v_moneytype'   => $data_vmoneytype,
            'v_url'         => $data_vreturnurl,
            'v_md5info'     => $MD5KEY
        );

        return $this->form_info('https://pay3.chinabank.com.cn/PayGate',
                                'POST',
                                $fields);
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if ($this->is_paid(trim($_POST['v_oid'])))
        {
            return true;
        }
        $payment        = $this->get_info();

        $v_oid          = trim($_POST['v_oid']);
        $v_pmode        = trim($_POST['v_pmode']);
        $v_pstatus      = trim($_POST['v_pstatus']);
        $v_pstring      = trim($_POST['v_pstring']);
        $v_amount       = trim($_POST['v_amount']);
        $v_moneytype    = trim($_POST['v_moneytype']);
        $remark1        = trim($_POST['remark1' ]);
        $remark2        = trim($_POST['remark2' ]);
        $v_md5str       = trim($_POST['v_md5str' ]);

        $this->total_fee = $v_amount;

        /**
         * 重新计算md5的值
         */
        $key            = $payment['chinabank_key'];

        $md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

        /* 检查秘钥是否正确 */
        if ($v_md5str==$md5string)
        {
            if ($v_pstatus == '20')
            {
                $this->init_order($v_oid);
                $this->order_paid();
                /* 改变订单状态 */

                return ORDER_STATUS_ACCEPTTED;
            }
        }
        else
        {
            $this->err = 'wrong_sign';

            return false;
        }
    }
}

?>