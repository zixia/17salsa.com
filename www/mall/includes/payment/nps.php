<?php

/**
 * ECMall: NPS֧�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: nps.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/*
    ����ʹ��NPSʵʱ�����ӿڵ��̻���ע�⣺

    Ϊ�˴Ӹ����Ͻ������֧���ɹ����̻��ղ���������Ϣ������(��Ƶ���).
    �ҹ�˾��������Ϣ��������ʵ�з������˶Է������˵ķ�����ʽ.���ͻ�֧������.
    ����ϵͳ����̻�����վ��������֧����Ϣ�ķ���(����ͬһ�ʶ�����Ϣ�������η���).
    ��һ���Ƿ������˶Է������˵ķ���.�ڶ�������ҳ�����ʽ����.���η�����ʱ�Ӳ���10��֮��.
    ���̻��Ǳ����ö����Ƿ�����Ϣ�Ĵ���. ������ϵͳ������ͬ�Ķ�����Ϣ���Ǳ�ֻ
    ��һ�δ���Ϳ�����.��ȷ�������ߵ�ÿһ�ʶ�����Ϣ�����Ǳ�ֻ�õ�һ����Ӧ�ķ���!!
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}


include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/nps'));

/**
 * ģ����Ϣ
 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc'] = 'nps_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod'] = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author'] = 'NPS CORP.';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.nps.cn';

    /* �汾�� */
    $modules[$i]['version'] = '4.0';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config'] = array(
        array('name' => 'nps_account', 'type' => 'text', 'value' => ''),
        array('name' => 'nps_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

class nps extends Payment
{

    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
     * ����֧������
     * @param   array   $order      ������Ϣ
     */
    function get_code($order_obj)
    {
        $payment     = $this->get_config();
        $order       = $order_obj->get_info();
        $m_id        = trim($payment['nps_account']);
        $m_orderid   = $order_obj->get_pay_log();
        $m_oamount   = $order['payable'];
        $m_ocurrency = '1';
        $m_url       = $this->get_respond_url(basename(__FILE__, '.php'));
        $m_language  = '1';
        $s_name      = 'null';
        $s_addr      = 'null';
        $s_postcode  = 'null';
        $s_tel       = 'null';
        $s_eml       = 'null';
        $r_name      = 'null';
        $r_addr      = 'null';
        $r_postcode  = 'null';
        $r_tel       = 'null';
        $r_eml       = 'null';
        $m_ocomment  = 'Order SN:' . $order['order_sn'];
        $modate      = date('y-m-d H:i:s',time());
        $m_status    = 0;

        //��֯������Ϣ
        $m_info = $m_id . '|' . $m_orderid . '|' . $m_oamount . '|' . $m_ocurrency . '|' . $m_url . '|' . $m_language;
        $s_info = $s_name . '|' . $s_addr . '|' . $s_postcode . '|' . $s_tel . '|' . $s_eml;
        $r_info = $r_name . '|' . $r_addr . '|' . $r_postcode . '|' . $r_tel . '|' . $r_eml . '|' . $m_ocomment . '|' . $m_status . '|' . $modate;

        $OrderInfo = $m_info . '|' . $s_info . '|' . $r_info;

        //������Ϣ��ת����HEX��Ȼ���ټ���
        $key = $payment['nps_key'];     //<--֧����Կ--> ע:�˴���Կ�������̼Һ�̨�����Կһ��

        $OrderInfo = $this->StrToHex($OrderInfo);
        $digest = strtoupper(md5($OrderInfo . $key));

        /*
        $def_url =  "<form method=post action='https://payment.nps.cn/PHPReceiveMerchantAction.do' target='_blank'>";
        $def_url .= "<input type=HIDDEN name='OrderMessage' value='" . $OrderInfo . "'>";
        $def_url .= "<input type=HIDDEN name='digest' value='" . $digest . "'>";
        $def_url .= "<input type=HIDDEN name='M_ID' value='" . $m_id . "'>";
        $def_url .= "<input type=submit value='"  . $GLOBALS['_LANG']['pay_button'] .  "'>";

        $def_url .= '</form>';
        */

        $fields   = array(
                'OrderMessage'  => $OrderInfo,
                'digest'        => $digest,
                'M_ID'          => $m_id
        );

        return $this->form_info('https://payment.nps.cn/PHPReceiveMerchantAction.do',
                                'POST',
                                $fields);
    }

    /**
     * ��Ӧ����
     */

    function respond()
    {
        if ($this->is_paid(trim($_POST['m_orderid'])))
        {
            return true;
        }
        $payment     = $this->get_config();

        $m_id        = $_POST['m_id'];        // �̼Һ�
        $m_orderid   = $_POST['m_orderid'];   // �̼Ҷ�����
        $m_oamount   = $_POST['m_oamount'];   // ֧�����
        $m_ocurrency = $_POST['m_ocurrency']; // ����
        $m_language  = $_POST['m_language'];  // ����ѡ��
        $s_name      = $_POST['s_name'];      // ����������
        $s_addr      = $_POST['s_addr'];      // ������סַ
        $s_postcode  = $_POST['s_postcode'];  // ��������
        $s_tel       = $_POST['s_tel'];       // ��������ϵ�绰
        $s_eml       = $_POST['s_eml'];       // �������ʼ���ַ
        $r_name      = $_POST['r_name'];      // ����������
        $r_addr      = $_POST['r_addr'];      // �ջ���סַ
        $r_postcode  = $_POST['r_postcode'];  // �ջ�����������
        $r_tel       = $_POST['r_tel'];       // �ջ�����ϵ�绰
        $r_eml       = $_POST['r_eml'];       // �ջ��˵��ӵ�ַ
        $m_ocomment  = $_POST['m_ocomment'];  // ��ע
        $State       = $_POST['m_status'];    // ֧��״̬2�ɹ�,3ʧ��
        $modate      = $_POST['modate'];      // ��������
        $order_sn    =  $_POST['m_orderid'];

        $this->total_fee = $m_oamount;

        //��������ļ���
        $OrderInfo   = $_POST['OrderMessage'];// ����������Ϣ
        $signMsg     = $_POST['Digest'];      // �ܳ�

        //�����µ�md5������֤
        $newmd5info  = $_POST['newmd5info'];

        //���ǩ��
        $key    = $payment['nps_key']; //<--֧����Կ--> ע:�˴���Կ�������̼Һ�̨�����Կһ��
        $digest = strtoupper(md5($OrderInfo . $key));

        //�µ�����md5����
        $newtext      = $m_id . $m_orderid . $m_oamount . $key . $State;
        $newMd5digest = strtoupper(md5($newtext));

        if ($digest == $signMsg)
        {
            //����
            //$decode = $DES->Descrypt($OrderInfo, $key);
            $OrderInfo = $this->HexToStr($OrderInfo);
            //md5�ܳ���֤
            if ($newmd5info == $newMd5digest)
            {
                if ($State == 2)
                {
                    $this->init_order($m_orderid);
                    $this->order_paid();

                    return ORDER_STATUS_ACCEPTTED;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
       }
       else
       {
            return false;
        }
    }

    function StrToHex($string)
    {
        $hex = '';

        for ($i = 0, $count = strlen($string); $i < $count; $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }

        return strtoupper($hex);
    }

    function HexToStr($hex)
    {
        $string = '';

        for ($i = 0, $count = strlen($hex) - 1; $i < $count; $i += 2)
        {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
}

?>