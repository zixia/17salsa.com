<?php

/**
 * ECMall: ֧�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: alipay.php 6076 2008-11-18 10:31:58Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}
include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/alipay'));

define('COMSENZ_ALIPAY_KEY', 'zwdr8lv1uaj4b438sjvdoqn8sjrxr0mm');
define('COMSENZ_ALIPAY_PARTNER', '2088002872555901');
define('COMSENZ_ALIPAY_SIGN_TYPE_CODE', 'DISCUZ100011000101');

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'alipay_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.alipay.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.1';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
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
 * ��
 */
class alipay extends Payment
{
    var $sign_type = 'MD5';
    var $config    = array();
    var $currency  = array('CNY');

    /**
     * @author  scottye
     * @param   string  $cfg    ����
     * @return  array / false
     */
    function get_config($cfg = '')
    {
        $config = parent::get_config($cfg);
        if (is_array($config))
        {
            // discuz ר��
            $config['alipay_key']     = COMSENZ_ALIPAY_KEY;
            $config['alipay_partner'] = COMSENZ_ALIPAY_PARTNER;
            $config['is_instant']     = 1;//�ѿ�ͨ��ʱ���˷���
        }
        return $config;
    }

    /**
     * ����֧������

     * @author  wj
     * @param   Order   $order      ������Ϣ
     * @param   Payment   $payment    ֧����ʽ��Ϣ
     */
    function get_code($order)
    {
        $payment = $this->get_config();

        $order_info =& $order->get_info();
        if (empty($payment['is_instant']))
        {
            /* δ��ͨ��ʱ���� */
            $service = 'trade_create_by_buyer';
        }
        else
        {
            if (!empty($order_info['order_id']))
            {
                /* ��鶩���Ƿ�ȫ��Ϊ������Ʒ */
//                $has_real_goods = $order->has_real_goods();
                $has_real_goods = 1; // ����û��������Ʒ

                if ($has_real_goods > 0)
                {
                    /* �����д���ʵ����Ʒ */
                    $service =  (!empty($payment['alipay_real_method']) && $payment['alipay_real_method'] == 1) ?
                        'create_direct_pay_by_user' : 'trade_create_by_buyer';
                }
                else
                {
                    /* ������ȫ��Ϊ������Ʒ */
                    $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
                        'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
                }
            }
            else
            {
                /* �Ƕ�����ʽ������������Ʒ���� */
                $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
                    'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
            }
        }


        /* ���������Ƿ���partner idǩ�˴���Э�� */
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
            /* ҵ����� */
            'subject'           => 'Order SN:' . $order_info['order_sn'],
            'out_trade_no'      => $order->get_pay_log(),
            'price'             => $order_info['payable'],
            'quantity'          => 1,
            'payment_type'      => 1,
            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* ����˫����Ϣ */
            'seller_email'      => $payment['alipay_account']
        );
        if ($service == 'create_direct_pay_by_user')
        {
            /* ��ʱ������ֱ�Ӵ��� */
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

        /* ��ȡǩ�� */
        $parameter['sign'] = $this->get_sign($parameter);
        $parameter['sign_type'] = $this->sign_type;

        return $this->form_info('https://www.alipay.com/cooperate/gateway.do?',
                                'GET',
                                $parameter);
    }

    /**
     * ��Ӧ����
     *
     *  @author Garbin
     *  @param Order $order
     */
    function respond()
    {
        /* �����type_code */
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
     *  ��Լ
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
     *  ֧����Ӧ
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

        /* ��ʼ���������� */
        $this->init_order($_GET['out_trade_no']);

        /* ���֧���Ľ���Ƿ���� */
        if ($this->order->get_payable() != $this->total_fee)
        {
            $this->err = 'money_inequalit';

           return false;
        }

        /* �ȶ�ǩ�� */
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
            /* �ı䶩��״̬ */
            $this->order_paid();

            return ORDER_STATUS_ACCEPTTED;
        }
        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
        {
            /* �ı䶩��״̬ */
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
     *  ����ǩԼ��Ӧ
     *
     *  @author Garbin
     *  @return
     */
    function _sign_respond()
    {
        /* ��ʼ������ */
        $customer_code = trim($_GET['customer_code']);

        $payment       = $this->get_info();
        $config        = $payment['config'];

        /* ���ǩ���Ƿ���ȷ */
        $sign_data = $_GET;
        unset($sign_data['sign'], $sign_data['sign_type'], $sign_data['app'], $sign_data['store_id'], $sign_data['pay_id']);
        $sign = $this->get_sign($sign_data);
        if ($sign != $_GET['sign'])
        {
            $this->err = 'wrong_sign';

            return FALSE;
        }
        /* ���ǩԼ��֧�����˺��Ƿ�һ�� */
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

        /* ����customer_code */
        $this->save_config($config);

        return TRUE;
    }

    /**
     *  ��ȡ֧����ǩԼURL
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
                    'notify_url'     => 'http://payapi.comsenz.com/alipay_notify.php?action=sign_notify',//������֧���������BUG,��URL�������"&","%"�ȶ�����ʾ����
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
     *  ��ȡ֧������ԼURL
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
     *  ��ȡ֧����ǩԼ��Լ֪ͨURL
     *
     *  @author Garbin
     *  @return string
     */
    function _get_alipay_sign_nurl()
    {
        return site_url() . '/admin.php?app=payment&act=alipay_sign_notify';
    }

    /**
     *  ����ǩ��
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
     *  ��ȡǩ���ַ���
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
