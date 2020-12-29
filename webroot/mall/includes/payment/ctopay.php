<?php

/**
 * ECMall: Ctopay ֧�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctopay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/ctopay'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'ctopay_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.ctopay.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY', 'USD');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'MerNo', 'type' => 'text', 'value' => ''),
        array('name' => 'MD5key', 'type' => 'text', 'value' => ''),
        array('name' => 'Language', 'type' => 'select', 'value' => ''),
    );

    return;
}

/**
 * ��
 */
class ctopay extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY', 'USD');

    /**
     * ����֧������
     * @param   array   $order      ��������
     */
    function get_code($order_obj)
    {
         $payment   =   $this->get_config();
         $order     =   $order_obj->get_info();
         $MD5key = $payment['MD5key'];                             //MD5˽Կ
         $MerNo = $payment['MerNo'];                               //�̻���
         $BillNo = $order_obj->get_pay_log();                               //������
         $Currency = $this->_get_currency();                         //����
         $Amount = $order['payable'];                         //���
         $DispAmount= 0;                                           //��ҽ��
         $Language = $payment['Language'];                         //����
         $ReturnURL = $this->get_respond_url(basename(__FILE__, '.php'));      //���ص�ַ
         $Remark = 'Order SN:' . $order['order_sn']; //��ע

         $md5src = $MerNo.$BillNo.$Currency.$Amount.$Language.$ReturnURL.$MD5key; //У��Դ�ַ���
         $MD5info = strtoupper(md5($md5src));                                     //MD5������

        /*
        $button = '<form action="http://219.133.36.139/payment/Interface" method="post">'.
                    "  <input type='hidden' name='MerNo' value='". $MerNo ."'>".
                    "  <input type='hidden' name='Currency' value='". $Currency ."'>".
                    "  <input type='hidden' name='BillNo' value='". $BillNo ."'>".
                    "  <input type='hidden' name='Amount' value='". $Amount ."'>".
                    "  <input type='hidden' name='DispAmount' value='". $DispAmount ."'>".
                    "  <input type='hidden' name='ReturnURL' value='". $ReturnURL ."'>".
                    "  <input type='hidden' name='Language' value='". $Language ."'>".
                    "  <input type='hidden' name='MD5info' value='". $MD5info ."'>".
                    "  <input type='hidden' name='Remark' value='". $Remark ."'>".
                    "  <input type='submit' name='b1' value='" . $GLOBALS['_LANG']['pay_button'] . "'>".
                    "</form>";
        */

        $fields =   array(
            'MerNo'         =>  $MerNo,
            'Currency'      =>  $Currency,
            'BillNo'        =>  $BillNo,
            'Amount'        =>  $Amount,
            'DispAmount'    =>  $DispAmount,
            'ReturnURL'     =>  $ReturnURL,
            'Language'      =>  $Language,
            'MD5info'       =>  $MerNo,
            'MerNo'         =>  $MD5info,
            'Remark'        =>  $Remark,
        );

        return $this->form_info('http://219.133.36.139/payment/Interface',
                                'POST',
                                $fields);
    }

    /**
     * ��Ӧ����
     */
    function respond($order_obj)
    {
        if ($this->is_paid(trim($_REQUEST["BillNo"])))
        {
            return true;
        }
        $payment  = $this->get_config();

        $BillNo = $_REQUEST["BillNo"];     //������
        $Currency = $_REQUEST["Currency"]; //����
        $BankID = $_REQUEST["BankID"];     //����ID��
        $Amount = $_REQUEST["Amount"];     //���
        $Succeed = $_REQUEST["Succeed"];   //֧��״̬
        $TradeNo = $_REQUEST["TradeNo"];   //֧��ƽ̨��ˮ��
        $Result = $_REQUEST["Result"];     //֧�����
        $MD5info = $_REQUEST["MD5info"];   //ȡ�õ�MD5У����Ϣ
        $Remark = $_REQUEST["Remark"];     //��ע
        //$Drawee = $_REQUEST["Drawee"];   //֧��������

        $this->total_fee = $Amount;

        $MD5key = $payment['MD5key'];                         //MD5˽Կ
        $md5src = $BillNo.$Currency.$Amount.$Succeed.$MD5key; //У��Դ�ַ���
        $md5sign = strtoupper(md5($md5src));                  //MD5������

        /* ��֤ */
        if ($MD5info!= $md5sign)
        {
            $this->err = 'wrong_sign';

            return false;
        }

        if ($Succeed == 1)
        {
            $this->init_order($BillNo);

            $this->order_paid();

            /* �ı䶩��״̬ */
            return ORDER_STATUS_ACCEPTTED;
        }
        else
        {
            return false;
        }
    }

    function _get_currency()
    {
        switch (CURRENCY)
        {
            case 'CNY':
            return 1;
            case 'USD':
            return 2;
        }
    }
}

?>