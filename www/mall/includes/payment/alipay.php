<?php

/**
 * ECMall: 支付宝插件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: alipay.php 6076 2008-11-18 10:31:58Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}
include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* 加载语言包 */
Language::load_lang(lang_file('payment/alipay'));

define('COMSENZ_ALIPAY_KEY', 'zwdr8lv1uaj4b438sjvdoqn8sjrxr0mm');
define('COMSENZ_ALIPAY_PARTNER', '2088002872555901');
define('COMSENZ_ALIPAY_SIGN_TYPE_CODE', 'DISCUZ100011000101');

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'alipay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.alipay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 支持的货币 */
    $modules[$i]['currency'] = array('CNY');

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'alipay_account',        'type' => 'text',   'value' => ''),
//        array('name' => 'alipay_key',            'type' => 'text',   'value' => ''),
//        array('name' => 'alipay_partner',        'type' => 'text',   'value' => ''),
        array('name' => 'alipay_real_method',    'type' => 'select', 'value' => '0', 'desc' => true),
        array('name' => 'alipay_virtual_method', 'type' => 'select', 'value' => '0', 'desc' => true)
    );

    return;
}
/**
 * 类
 */
class alipay extends Payment
{
    var $sign_type = 'MD5';
    var $config    = array();
    var $currency  = array('CNY');

    /**
     * @author  scottye
     * @param   string  $cfg    配置
     * @return  array / false
     */
    function get_config($cfg = '')
    {
        $config = parent::get_config($cfg);
        if (is_array($config))
        {
            // discuz 专用
            $config['alipay_key']     = COMSENZ_ALIPAY_KEY;
            $config['alipay_partner'] = COMSENZ_ALIPAY_PARTNER;
            $config['is_instant']     = 1;//已开通即时到账服务
        }
        return $config;
    }

    /**
     * 生成支付代码

     * @author  wj
     * @param   Order   $order      订单信息
     * @param   Payment   $payment    支付方式信息
     */
    function get_code($order)
    {
        $payment = $this->get_config();

        $order_info =& $order->get_info();
        if (empty($payment['is_instant']))
        {
            /* 未开通即时到帐 */
            $service = 'trade_create_by_buyer';
        }
        else
        {
            if (!empty($order_info['order_id']))
            {
                /* 检查订单是否全部为虚拟商品 */
//                $has_real_goods = $order->has_real_goods();
                $has_real_goods = 1; // 现在没有虚拟商品

                if ($has_real_goods > 0)
                {
                    /* 订单中存在实体商品 */
                    $service =  (!empty($payment['alipay_real_method']) && $payment['alipay_real_method'] == 1) ?
                        'create_direct_pay_by_user' : 'trade_create_by_buyer';
                }
                else
                {
                    /* 订单中全部为虚拟商品 */
                    $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
                        'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
                }
            }
            else
            {
                /* 非订单方式，按照虚拟商品处理 */
                $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
                    'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
            }
        }


        /* 检查该卖家是否与partner id签了代扣协议 */
        if (!isset($payment['customer_code']) || !$payment['customer_code'])
        {
            if ($service != 'create_direct_pay_by_user')
            {
                $this->err = 'alipay_sign_first';

                return FALSE;
            }
        }


        $parameter = array(
            'service'           => $service,
            'partner'           => $payment['alipay_partner'],
            '_input_charset'    => CHARSET,
            'return_url'        => $this->get_respond_url(basename(__FILE__, '.php')),
            'notify_url'        => $this->get_respond_url(basename(__FILE__, '.php')),
            /* 业务参数 */
            'subject'           => 'Order SN:' . $order_info['order_sn'],
            'out_trade_no'      => $order->get_pay_log(),
            'price'             => $order_info['payable'],
            'quantity'          => 1,
            'payment_type'      => 1,
            /* 物流参数 */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* 买卖双方信息 */
            'seller_email'      => $payment['alipay_account']
        );
        if ($service == 'create_direct_pay_by_user')
        {
            /* 即时到账则直接代扣 */
            $royalty_price = sprintf('%1.2f', $order_info['payable'] * 0.015);
            if ($royalty_price > 0)
            {
                $parameter['royalty_type'] = 10;
                $parameter['royalty_parameters'] = 'comsenz@comsenz.com^'.$royalty_price.'^ComsenzRoyalty1';
            }
        }
        else
        {
            $parameter['notify_url'] = 'http://payapi.comsenz.com/alipay_notify.php?action=order_notify';
        }

        /* 获取签名 */
        $parameter['sign'] = $this->get_sign($parameter);
        $parameter['sign_type'] = $this->sign_type;

        return $this->form_info('https://www.alipay.com/cooperate/gateway.do?',
                                'GET',
                                $parameter);
    }

    /**
     * 响应操作
     *
     *  @author Garbin
     *  @param Order $order
     */
    function respond()
    {
        /* 如果有type_code */
        if (!isset($_GET['type_code']))
        {
            return $this->_pay_respond();
        }
        else
        {
            return $this->_sign_respond();
        }
    }

    /**
     *  解约
     *
     *  @author Garbin
     *  @return void
     */
    function unsign()
    {
        $payment = $this->get_info();
        unset($payment['config']['customer_code']);
        $this->save_config($payment['config']);
    }
    /**
     *  支付响应
     *
     *  @author Garbin
     *  @return bool
     */
    function _pay_respond()
    {
        if ($this->is_paid(trim($_GET['out_trade_no'])))
        {
            return true;
        }
        $payment      = $this->get_config();
        $this->config = $payment;

        $this->total_fee = $_GET['total_fee'];

        /* 初始化订单对象 */
        $this->init_order($_GET['out_trade_no']);

        /* 检查支付的金额是否相符 */
        if ($this->order->get_payable() != $this->total_fee)
        {
            $this->err = 'money_inequalit';

           return false;
        }

        /* 比对签名 */
        $alipay_pars = $_GET;
        unset($alipay_pars['sign'], $alipay_pars['sign_type'], $alipay_pars['code'], $alipay_pars['app'], $alipay_pars['act'], $alipay_pars['pay_id'], $alipay_pars['store_id']);

        $sign = $this->get_sign($alipay_pars);
        if ($sign != $_GET['sign'])
        {
            $this->err = 'wrong_sign';

            return FALSE;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
        {
            /* 改变订单状态 */
            $this->order_paid();

            return ORDER_STATUS_ACCEPTTED;
        }
        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
        {
            /* 改变订单状态 */
            $this->order_paid();


            return ORDER_STATUS_DELIVERED;
        }
        else
        {
            $this->err = 'unknow_status';
            return false;
        }
    }

    /**
     *  代扣签约响应
     *
     *  @author Garbin
     *  @return
     */
    function _sign_respond()
    {
        /* 初始化数据 */
        $customer_code = trim($_GET['customer_code']);

        $payment       = $this->get_info();
        $config        = $payment['config'];

        /* 检查签名是否正确 */
        $sign_data = $_GET;
        unset($sign_data['sign'], $sign_data['sign_type'], $sign_data['app'], $sign_data['store_id'], $sign_data['pay_id']);
        $sign = $this->get_sign($sign_data);
        if ($sign != $_GET['sign'])
        {
            $this->err = 'wrong_sign';

            return FALSE;
        }
        /* 检查签约的支付宝账号是否一致 */
        if ($config['alipay_account']['value'] != $_GET['email'])
        {
            $this->err = 'email_inequalit';

            return FALSE;
        }

        if ($_GET['is_success'] != 'T')
        {
            $this->err = 'alipay_sign_faild';

            return FALSE;
        }

        $config['customer_code']['value'] = $customer_code;

        /* 保存customer_code */
        $this->save_config($config);

        return TRUE;
    }

    /**
     *  获取支付宝签约URL
     *
     *  @author Garbin
     *  @return string
     */
    function _get_alipay_sign_url()
    {
        $config = $this->get_config();
        $args = str_replace(array('+','/'), array('.', '-'), base64_encode(join(chr(8), array('app' . chr(9) . 'alipay',
                                                                                              'pay_id' . chr(9) . $this->_id,
                                                                                              'store_id' . chr(9). $this->_store_id))));
        $pars = array(
                    '_input_charset' => CHARSET,
                    'service' => 'customer_sign',
                    'partner' => COMSENZ_ALIPAY_PARTNER,
                    'customer_email' => $config['alipay_account'],
                    'notify_url'     => 'http://payapi.comsenz.com/alipay_notify.php?action=sign_notify',//可能是支付宝方面的BUG,该URL中如果有"&","%"等都会提示错误
                    'return_url'     => site_url() . '/index.php?arg=' . $args,
                    'type_code'  => COMSENZ_ALIPAY_SIGN_TYPE_CODE
        );

        $pars['sign'] = $this->get_sign($pars);
        $pars['sign_type'] = 'MD5';
        $url_pars = '';
        foreach ($pars as $_k => $_v)
        {
            $url_pars .= "{$_k}={$_v}&";
        }
        return 'https://www.alipay.com/cooperate/gateway.do?'.substr($url_pars, 0, -1);
    }

    /**
     *  获取支付宝解约URL
     *
     *  @author Garbin
     *  @param
     *  @return
     */
    function _get_alipay_unsign_url()
    {
        $config = $this->get_config();
        $pars = array(
                    'service' => 'customer_unsign',
                    'partner' => COMSENZ_ALIPAY_PARTNER,
                    'customer_code' => $config['customer_code']
        );

        $pars['sign'] = $this->get_sign($pars);
        $pars['sign_type'] = 'MD5';
        $url_pars = '';
        foreach ($pars as $_k => $_v)
        {
            $url_pars .= "{$_k}={$_v}&";
        }
        return array('host' => 'https://www.alipay.com/cooperate/gateway.do?',
                     'pars' => substr($url_pars, 0, -1));
    }

    /**
     *  获取支付宝签约解约通知URL
     *
     *  @author Garbin
     *  @return string
     */
    function _get_alipay_sign_nurl()
    {
        return site_url() . '/admin.php?app=payment&act=alipay_sign_notify';
    }

    /**
     *  加密签名
     *
     *  @author Garbin
     *  @param  array $pars
     *  @return string
     */
    function _alipay_par_sign($pars)
    {
        ksort($pars);
        reset($pars);

        $sign  = '';

        foreach ($pars AS $key => $val)
        {
            $sign  .= "$key=$val&";
        }

        $sign  = md5(substr($sign, 0, -1) . COMSENZ_ALIPAY_KEY);

        return $sign;
    }

    /**
     *  获取签名字符串
     *
     *  @author Garbin
     *  @param  array $parameter
     *  @return string
     */
    function get_sign($parameter)
    {
        ksort($parameter);
        reset($parameter);

        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $sign  .= "$key=$val&";
        }

        return md5(substr($sign, 0, -1). COMSENZ_ALIPAY_KEY);
    }
}

?>
