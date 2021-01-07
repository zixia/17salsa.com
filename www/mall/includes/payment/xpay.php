<?php

/**
 * ECMall: �׸�ͨ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: xpay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/xpay'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'xpay_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.xpay.cn';

    /* �汾�� */
    $modules[$i]['version'] = '2.0.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config']  = array(
                                array('name' => 'xpay_tid', 'type' => 'text', 'value' => ''),
                                array('name' => 'xpay_key', 'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * ��
 */
class xpay extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
     * ����֧������
     * @param   array   $order_obj  ��������
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
        $def_url  .= "<input type=hidden name=tid value='$data_tid'>";           // �̻����׺�
        $def_url  .= "<input type=hidden name=bid value='$data_order_id'>";      // ������
        $def_url  .= "<input type=hidden name=prc value='$data_amount'>";        // �����ܽ��
        $def_url  .= "<input type=hidden name=card value='bank'>";               // Ĭ��֧����ʽ
        $def_url  .= "<input type=hidden name=scard value=''>";                  // ֧��֧������
        $def_url  .= "<input type=hidden name=actioncode value='sell'>";         // ������
        $def_url  .= "<input type=hidden name=actionParameter value=''>";        // ҵ��������
        $def_url  .= "<input type=hidden name=ver value='2.0'>";                 // �汾��
        $def_url  .= "<input type=hidden name=md value='$data_key'>";            // ����MD5У����
        $def_url  .= "<input type=hidden name=url value='$data_return_url'>";    // ֧��������ɺ󷵻ص���url��֧�������get��ʽ����
        $def_url  .= "<input type='hidden' name='pdt' value='$data_order_id'>";  // ��Ʒ���ƻ���˵��
        $def_url  .= "<input type='hidden' name='type' value=''>";               // ��Ʒ���ͻ��׷���
        $def_url  .= "<input type='hidden' name='username' value=''>";           // ���ѹ����û���
        $def_url  .= "<input type='hidden' name='lang' value='gb2312'>";         // ����
        $def_url  .= "<input type='hidden' name='remark1' value=''>";            // ��ע�ֶ�
        $def_url  .= "<input type='hidden' name='disableemail' value=''>";       // ���ؽ�������
        $def_url  .= "<input type='hidden' name='disablealert' value=''>";       // ���ص�����ʾ
        $def_url  .= "<input type='hidden' name='sitename' value=''>";           // �̻���վ����
        $def_url  .= "<input type='hidden' name='siteurl' value=''>";            // �̻���վ����
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
     * ��Ӧ����
     */
    function respond()
    {
        if ($this->is_paid(trim($_REQUEST["bid"])))
        {
            return true;
        }
        /*ȡ���ز���*/
        $tid             = $_REQUEST["tid"];             // �̻�Ψһ���׺�
        $bid             = $_REQUEST["bid"];             // �̻���վ������
        $sid             = $_REQUEST["sid"];             // �׸�ͨ���׳ɹ� ��ˮ��
        $prc             = $_REQUEST["prc"];             // ֧���Ľ��
        $actionCode      = $_REQUEST["actioncode"];      // ������
        $actionParameter = $_REQUEST["actionparameter"]; // ҵ�����
        $card            = $_REQUEST["card"];            // ֧����ʽ
        $success         = $_REQUEST["success"];         // �ɹ���־��
        $bankcode        = $_REQUEST["bankcode"];        // ֧������
        $remark1         = $_REQUEST["remark1"];         // ��ע��Ϣ
        $username        = $_REQUEST["username"];        // �̻���վ֧���û�
        $md              = $_REQUEST["md"];              // 32λmd5��������

        $this->total_fee = $prc;

        $payment = $this->get_config();
        if ($success == 'false')
        {
            return false;
        }
        // ��֤�����Ƿ���ȷ
        $ymd = md5($payment['xpay_key'] . ":" . $bid . "," . $sid . "," . $prc . "," . $actionCode  ."," . $actionParameter . "," . $tid . "," . $card . "," . $success); // ���ؽ������ݼ���
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