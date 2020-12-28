<?php

/**
 * ECMall: YeePay�ױ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: yeepay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/yeepay'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'yp_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'newbj <ffo@21cn.com>';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.yeepay.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.1';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'yp_account', 'type' => 'text', 'value' => ''),
        array('name' => 'yp_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * ��
 */
class yeepay extends Payment
{

    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
     * ����֧������
     * @param   array   $order_obj  ��������
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
     * ��Ӧ����
     */
    function respond()
    {
        if ($this->is_paid(trim($_REQUEST['r6_Order'])))
        {
            return true;
        }
        $payment        = $this->get_config();

        $merchant_id    = $payment['yp_account'];       // ��ȡ�̻����
        $merchant_key   = $payment['yp_key'];           // ��ȡ��Կ

        $message_type   = trim($_REQUEST['r0_Cmd']);
        $succeed        = trim($_REQUEST['r1_Code']);   // ��ȡ���׽��,1�ɹ�,-1ʧ��
        $trxId          = trim($_REQUEST['r2_TrxId']);
        $amount         = trim($_REQUEST['r3_Amt']);    // ��ȡ�������
        $cur            = trim($_REQUEST['r4_Cur']);    // ��ȡ�������ҵ�λ
        $product_id     = trim($_REQUEST['r5_Pid']);    // ��ȡ��ƷID
        $orderid        = trim($_REQUEST['r6_Order']);  // ��ȡ����ID
        $userId         = trim($_REQUEST['r7_Uid']);    // ��ȡ��ƷID
        $merchant_param = trim($_REQUEST['r8_MP']);     // ��ȡ�̻�˽�в���
        $bType          = trim($_REQUEST['r9_BType']);  // ��ȡ����ID

        $mac            = trim($_REQUEST['hmac']);      // ��ȡ��ȫ���ܴ�

        $this->total_fee = $amount;

        /* ȡ�ö������� */
        $this->init_order($orderid);

        ///���ɼ��ܴ�,ע��˳��
        $ScrtStr  = $merchant_id . $message_type . $succeed . $trxId . $amount . $cur . $product_id .
                      $orderid . $userId . $merchant_param . $bType;
        $mymac    = hmac($ScrtStr, $merchant_key);

        $v_result = false;

        if (strtoupper($mac) == strtoupper($mymac))
        {
            if ($succeed == '1')
            {
                ///֧���ɹ�
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