<?php

/**
 * ECMall: ��Ǯ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: kuaiqian.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/kuaiqian'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'kq_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.99bill.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.1';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
        array('name' => 'kq_account', 'type' => 'text', 'value' => ''),
        array('name' => 'kq_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * ��
 */
class kuaiqian extends Payment
{

    /* ֧���Ļ��� */
    var $currency  = array('CNY');

   /**
     * ������ֵ��Ϊ�յĲ�������ַ���
     * @param   string   $strs  �����ַ���
     * @param   string   $key   ��������
     * @param   string   $val   ��������Ӧֵ
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
     * ����֧������
     * @param   array   $order_obj  ��������
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
     * ��Ӧ����
     */
    function respond()
    {
        $payment    =   $this->get_config();

        $merchant_id    = $payment['kq_account'];               ///��ȡ�̻����
        $key   = $payment['kq_key'];                   ///��ȡ��Կ



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

        //��ȡʵ��֧�����
        ///��λΪ��
        ///�ȷ� 2 ������0.02Ԫ
        $pay_amount=trim(@$_REQUEST['payAmount']);
        $msg_val = $this->append_param($msg_val, 'payAmount', $pay_amount);

        //��ȡ����������
        ///��λΪ��
        ///�ȷ� 2 ������0.02Ԫ
        $fee=trim(@$_REQUEST['fee']);
        $msg_val = $this->append_param($msg_val, 'fee', $fee);

        $ext1=trim(@$_REQUEST['ext1']);
        $msg_val = $this->append_param($msg_val, 'ext1', $ext1);

        $ext2=trim(@$_REQUEST['ext2']);
        $msg_val = $this->append_param($msg_val, 'ext2', $ext2);

        //��ȡ������
        ///10���� �ɹ�; 11���� ʧ��
        ///00���� �¶����ɹ������Ե绰����֧���������أ�;01���� �¶���ʧ�ܣ����Ե绰����֧���������أ�
        $pay_result=trim(@$_REQUEST['payResult']);
        $msg_val = $this->append_param($msg_val, 'payResult', $pay_result);

        //��ȡ�������
        ///��ϸ���ĵ���������б�
        $err_code=trim(@$_REQUEST['errCode']);
        $msg_val = $this->append_param($msg_val, 'errCode', $err_code);

        $msg_val = $this->append_param($msg_val, 'key', $key);

        //��ȡ����ǩ����
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
