<?php

/**
 * ECMall: ������֧�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cappay.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/cappay'));

/**
 * ģ����Ϣ
 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'cappay_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.beijing.com.cn';

    /* �汾�� */
    $modules[$i]['version'] = 'V4.3';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY', 'USD');

    /* ������Ϣ */
    $modules[$i]['config'] = array(
        array('name' => 'cappay_account',  'type' => 'text',   'value' => ''),
        array('name' => 'cappay_key',      'type' => 'text',   'value' => '')
    );

    return;
}

class cappay extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY', 'USD');

    /**
     * ����֧������
     * @param   array   $order      ������Ϣ
     */
    function get_code($order)
    {
        $payment = $this->get_config();
        $order_info  =& $order->get_info();
        $v_rcvname   = trim($payment['cappay_account']);
        $m_orderid   = $order->get_pay_log();
        $v_amount    = $order_info['payable'];
        $v_moneytype = $this->_get_currency();
        $v_url       = $this->get_respond_url(basename(__FILE__, '.php'));
        $m_ocomment  = 'Order SN:' . $order_info['order_sn'];
        $v_ymd       = date('Ymd',time());
        $MD5Key     = $payment['cappay_key'];     //<--֧����Կ--> ע:�˴���Կ�������̼Һ�̨�����Կһ��
        $v_oid      = "$v_ymd-$v_rcvname-$m_orderid";
        $sourcedata = $v_moneytype.$v_ymd.$v_amount.$v_rcvname.$v_oid.$v_rcvname.$v_url;
        $result     = $this->hmac_md5($MD5Key,$sourcedata);

          /*��֧��ƽ̨*/
          /*
        $def_url  = '<form method=post action="http://pay.beijing.com.cn/prs/user_payment.checkit" target="_blank">';
        $def_url .= "<input type= 'hidden' name = 'v_mid'     value= '".$v_rcvname."'>";     //�̻����
        $def_url .= "<input type= 'hidden' name = 'v_oid'     value= '".$v_oid."'>";         //�������
        $def_url .= "<input type= 'hidden' name = 'v_rcvname' value= '".$v_rcvname."'>";     //�ջ�������
        $def_url .= "<input type= 'hidden' name = 'v_rcvaddr' value= '".$v_rcvname."'>";     //�ջ��˵�ַ
        $def_url .= "<input type= 'hidden' name = 'v_rcvtel'  value= '".$v_rcvname."'>";     //�ջ��˵绰
        $def_url .= "<input type= 'hidden' name = 'v_rcvpost'  value= '".$v_rcvname."'>";    //�ջ����ʱ�
        $def_url .= "<input type= 'hidden' name = 'v_amount'   value= '".$v_amount."'>";     //�����ܽ��
        $def_url .= "<input type= 'hidden' name = 'v_ymd'      value= '".$v_ymd."'>";        //������������
        $def_url .= "<input type= 'hidden' name = 'v_orderstatus' value ='0'>";              //���״̬
        $def_url .= "<input type= 'hidden' name = 'v_ordername'   value ='".$v_rcvname."'>"; //����������
        $def_url .= "<input type= 'hidden' name = 'v_moneytype'   value ='".$v_moneytype."'>"; //����,0Ϊ�����,1Ϊ��Ԫ
        $def_url .= "<input type= 'hidden' name='v_url' value='".$v_url."'>";             //֧��������ɺ󷵻ص���url��֧�������GET��ʽ����
        $def_url .= "<input type='hidden' name='v_md5info' value=$result>";              //��������ָ��
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['cappay_button'] . "'>";

        $def_url .= '</form>';
        */

        $fields = array(
                'v_mid' => $v_rcvname,
                'v_oid' => $v_oid,
                'v_rcvname' => $v_rcvname,
                'v_rcvaddr' => $v_rcvname,
                'v_rcvtel' => $v_rcvname,
                'v_rcvpost' => $v_rcvname,
                'v_amount' => $v_amount,
                'v_ymd' => $v_ymd,
                'v_orderstatus' => 0,
                'v_ordername' => $v_rcvname,
                'v_moneytype' => $v_moneytype,
                'v_url' => $v_url,
                'v_md5info' => $result
        );

        return $this->form_info('http://pay.beijing.com.cn/prs/user_payment.checkit',
                                'POST',
                                $fields,
                                'cappay_button');
    }

    /**
     * ��Ӧ����
     */

    function respond()
    {
        $payment = $this->get_info();
        $v_tempdate = explode('-', $_GET['v_oid']);

        //���ܷ���������֤��ʼ
        //v_md5info��֤
        $md5info_paramet = $_GET['v_oid'].$_GET['v_pstatus'].$_GET['v_pstring'].$_GET['v_pmode'];
        $md5info_tem     = $this->hmac_md5($payment['cappay_key'],$md5info_paramet);

        //v_md5money��֤
        $md5money_paramet = $_GET['v_amount'].$_GET['v_moneytype'];
        $md5money_tem     = $this->hmac_md5($payment['cappay_key'],$md5money_paramet);

        $this->total_fee  = $_GET['v_amount'];

        if ($md5info_tem == $_GET['v_md5info'] && $md5money_tem == $_GET['v_md5money'])
        {
            if ($this->is_paid(trim($v_tempdate[2])))
            {
                return true;
            }
            $this->init_order($v_tempdate[2]);
            $this->order_paid();

            return ORDER_STATUS_ACCEPTTED;
        }
        else
        {
            return false;
        }

    }
    function hmac_md5($key, $data)
    {
        if (extension_loaded('mhash'))
        {
            return bin2hex(mhash(MHASH_MD5, $data, $key));
        }

        // RFC 2104 HMAC implementation for php. Hacked by Lance Rushing
        $b = 64;
        if (strlen($key) > $b)
        {
            $key = pack('H*', md5($key));
        }
        $key  = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));

        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack('H*', md5($k_ipad . $data)));
    }

    function _get_currency()
    {
        switch (CURRENCY)
        {
            case 'CNY':
            return 0;
            case 'USD':
            return 1;
        }
    }
}

?>