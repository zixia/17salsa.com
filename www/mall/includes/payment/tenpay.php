<?php

/**
 * ECMall: �Ƹ�ͨ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: tenpay.php 3811 2008-05-26 08:59:13Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/tenpay'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'tenpay_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.tenpay.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'tenpay_account',   'type' => 'text', 'value' => ''),
        array('name' => 'tenpay_key',       'type' => 'text', 'value' => ''),
        array('name' => 'tenpay_type',       'type' => 'select', 'value'=>'1'),
    );

    return;
}

/**
 * ��
 */
class tenpay extends Payment
{

    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
     * ����֧������
     * @param   array    $order       ������Ϣ
     * @param   array    $payment     ֧����ʽ��Ϣ
     */
    function get_code($order_obj)
    {
        $payment = $this->get_config();
        $order   = $order_obj->get_info();
        /* �汾�� */
        $version = '2';

        /* ������룬��ֵ��12 */
        $cmdno = '12';

        /* �����׼ */
        if (!defined('CHARSET'))
        {
            $encode_type = 2;
        }
        else
        {
            if (CHARSET == 'utf-8')
            {
                $encode_type = 2;
            }
            else
            {
                $encode_type = 1;
            }
        }

        /* ƽ̨�ṩ��,�����̵ĲƸ�ͨ�˺� */
        $chnid = $payment['tenpay_account'];

        /* �տ�Ƹ�ͨ�˺� */
        $seller = $payment['tenpay_account'];

        /* ��Ʒ���� */
        $mch_name = 'Order SN:' . $order['order_sn'];

        /* �ܽ�� */
        $mch_price = floatval($order['payable']) * 100;

        /* ��������˵�� */
        $transport_desc = '';
        $transport_fee = '';

        /* ����˵�� */
        $mch_desc = $order['order_sn'];
        $need_buyerinfo = '2' ;

        /* �������ͣ�2�����⽻�ף�1��ʵ�ｻ�� */
        $mch_type = $payment['tenpay_type'];

        /* ����һ��������� */
        $rand_num = rand(1,9);
        for ($i = 1; $i < 10; $i++)
        {
            $rand_num .= rand(0,9);
        }

        /* ��ö�������ˮ�ţ����㵽10λ */
        $mch_vno = $order_obj->get_pay_log();

        /* ���ص�·�� */
        $mch_returl = $this->get_respond_url('tenpay');
        $show_url   = $this->get_respond_url('tenpay');
        $attach = $rand_num;

        /* ����ǩ�� */
        $sign_text = "attach=" . $attach . "&chnid=" . $chnid . "&cmdno=" . $cmdno . "&encode_type=" . $encode_type . "&mch_desc=" . $mch_desc . "&mch_name=" . $mch_name . "&mch_price=" . $mch_price ."&mch_returl=" . $mch_returl . "&mch_type=" . $mch_type . "&mch_vno=" . $mch_vno . "&need_buyerinfo=" . $need_buyerinfo ."&seller=" . $seller . "&show_url=" . $show_url . "&version=" . $version . "&key=" . $payment['tenpay_key'];

        $sign =md5($sign_text);

        /* ���ײ��� */
        $parameter = array(
            'attach'            => $attach,
            'chnid'             => $chnid,
            'cmdno'             => $cmdno,                     // ҵ�����, �Ƹ�֧ͨ��֧���ӿ���  1
            'encode_type'       => $encode_type,                //�����׼
            'mch_desc'          => $mch_desc,
            'mch_name'          => $mch_name,
            'mch_price'         => $mch_price,                  // �������
            'mch_returl'        => $mch_returl,                 // ���ղƸ�ͨ���ؽ����URL
            'mch_type'          => $mch_type,                   //��������
            'mch_vno'           => $mch_vno,             // ���׺�(������)�����̻���վ����(����˳���ۼ�)
            'need_buyerinfo'    => $need_buyerinfo,             //�Ƿ���Ҫ�ڲƸ�ͨ�������Ϣ
            'seller'            => $seller,  // �̼ҵĲƸ�ͨ�̻���
            'show_url'          => $show_url,
            'transport_desc'    => $transport_desc,
            'transport_fee'     => $transport_fee,
            'version'           => $version,                    //�汾�� 2
            'key'               => $payment['tenpay_key'],
            'sign'              => $sign,                       // MD5ǩ��
            'sys_id'            => '542554970'                  //ecshop C�˺� ������ǩ��
        );

        return $this->form_info('https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi',
                                'GET',
                                $parameter);
    }

    /**
     * ��Ӧ����
     */
    function respond()
    {
        if ($this->is_paid(trim($_GET['mch_vno'])))
        {
            return true;
        }
        /*ȡ���ز���*/
        $cmd_no         = $_GET['cmdno'];
        $retcode        = $_GET['retcode'];
        $status         = $_GET['status'];
        $seller         = $_GET['seller'];
        $total_fee      = $_GET['total_fee'];
        $trade_price    = $_GET['trade_price'];
        $transport_fee  = $_GET['transport_fee'];
        $buyer_id       = $_GET['buyer_id'];
        $chnid          = $_GET['chnid'];
        $cft_tid        = $_GET['cft_tid'];
        $mch_vno        = $_GET['mch_vno'];
        $attach         = !empty($_GET['attach']) ? $_GET['attach'] : '';
        $version        = $_GET['version'];
        $sign           = $_GET['sign'];

        $payment    = $this->get_config();

        $log_id = $mch_vno; //ȡ��֧����log_id

        /* ���$retcode����0���ʾ֧��ʧ�� */
        if ($retcode > 0)
        {
            //echo '����ʧ��';
            return false;
        }

        $this->init_order($log_id);

        $this->total_fee = $total_fee / 100;

         /* ���֧���Ľ���Ƿ���� */
        if ($this->order->get_payable() != $this->total_fee)
        {
            $this->err = 'money_inequalit';

            return false;
        }

        /* �������ǩ���Ƿ���ȷ */
        $sign_text = "attach=" . $attach . "&buyer_id=" . $buyer_id . "&cft_tid=" . $cft_tid . "&chnid=" . $chnid . "&cmdno=" . $cmd_no . "&mch_vno=" . $mch_vno . "&retcode=" . $retcode . "&seller=" .$seller . "&status=" . $status . "&total_fee=" . $total_fee . "&trade_price=" . $trade_price . "&transport_fee=" . $transport_fee . "&version=" . $version . "&key=" . $payment['tenpay_key'];
        $sign_md5 = strtoupper(md5($sign_text));
        if ($sign_md5 != $sign)
        {
            $this->err = 'wrong_sign';

            return false;
        }
        elseif ($status == 3)
        {
            $this->order_paid();

            return ORDER_STATUS_ACCEPTTED;
        }
        else
        {
            return false;
        }
    }
}

?>