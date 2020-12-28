<?php

/**
 * 800pay ֧�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: pay800.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}


include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/pay800'));

/**
 * ģ����Ϣ
 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc'] = 'pay800_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod'] = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author'] = '800-pay';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.800-pay.com';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.2';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY', 'KRW', 'USD');

    /* ������Ϣ,��ͬ�û�ע���޸�value */
    $modules[$i]['config'] = array(
        array('name' => 'pay800_account', 'type' => 'text', 'value' => ''),
        array('name' => 'pay800_key',     'type' => 'text', 'value' => ''),
        array('name' => 'pay800_language', 'type' => 'select', 'value' => ''),
    );

    return;
}


class pay800 extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY', 'KRW', 'USD');

    /**
     * ����֧������
     * @param   array   $order_obj  ��������
     */
    function get_code($order_obj)
    {
        $payment = $this->get_config();
        $order   = $order_obj->get_info();
        $data_M_ID          = $payment['pay800_account'];             //�� �� �ţ�
        $data_M_OrderID     = $order_obj->get_pay_log();                       //�� �� �ţ�
        $data_M_OAmount     = $order['payable'];                 //������
        $data_M_OCurrency   = CURRENCY == 'CNY' ? 'RMB' : CURRENCY;            //�� �֣�
        $data_M_URL         = $this->get_respond_url(basename(__FILE__, '.php')); //���ص�ַ��
        $data_M_Language    = $payment['pay800_language'];            //����ѡ��

        $data_T_TradeName   = 'Order SN:' . $order['order_sn'];  //$order['order_sn'];              //��Ʒ���ƣ�
        $data_T_Unit        = '';  //$order['order_sn'];              //��Ʒ��λ��
        $data_T_UnitPrice   = '';  //$order['order_sn'];              //��Ʒ���ۣ�
        $data_T_quantity    = '';  //$order['order_sn'];              //��Ʒ������
        $data_T_carriage    = '';  //$order['shipping_fee'];          //��Ʒ�˷ѣ�

        $data_S_Name        = '';  //$order['order_sn'];              //������������
        $data_S_Address     = '';  //$order['order_sn'];              //������סַ��
        $data_S_PostCode    = '';  //$order['order_sn'];              //���������룺
        $data_S_Telephone   = '';  //$order['order_sn'];              //�����ߵ绰��
        $data_S_Email       = '';  //$order['order_sn'];              //�������ʼ���

        $data_R_Name        = '';  //$order['consignee'];             //�ջ���������
        $data_R_Address     = '';  //$order['address'];               //�ջ���סַ��
        $data_R_PostCode    = '';  //$order['zipcode'];               //�ջ������룺
        $data_R_Telephone   = '';  //$order['tel'];                   //�ջ��˵绰��
        $data_R_Email       = '';  //$order['email'];                 //�ջ����ʼ���

        $data_M_OComment    = '';  //$order['inv_content'];           //�� ע
        $data_M_OState      = '0';                                    //����״̬��
        $data_M_ODate       = date('Y-m-d H:i:s');                    //ʱ���ֶΣ�

        $data_PrivateKey    = $payment['pay800_key'];

        //$data_R_Telephone2   = $order['mobile'];                    //�ջ����ֻ���

        if (empty($data_M_OComment))
        {
            $data_M_OComment = 'From ECMall order ' . $payment['pay800_account'];
        }


        $data_m_info =  '' .
                        $data_M_ID           . '|' .
                        $data_M_OrderID      . '|' .
                        $data_M_OAmount      . '|' .
                        $data_M_OCurrency    . '|' .
                        $data_M_URL          . '|' .
                        $data_M_Language     . ''  ;

        $data_t_info =  ''.
                        $data_T_TradeName    . '|' .
                        $data_T_Unit         . '|' .
                        $data_T_UnitPrice    . '|' .
                        $data_T_quantity     . '|' .
                        $data_T_carriage     . ''  ;

        $data_s_info =  ''.
                        $data_S_Name         . '|' .
                        $data_S_Address      . '|' .
                        $data_S_PostCode     . '|' .
                        $data_S_Telephone    . '|' .
                        $data_S_Email        . '|' .
                        $data_R_Name         . ''  ;

        $data_r_info =  ''.
                        $data_R_Address      . '|' .
                        $data_R_PostCode     . '|' .
                        $data_R_Telephone    . '|' .
                        $data_R_Email        . '|' .
                        $data_M_OComment     . '|' .
                        $data_M_OState       . '|' .
                        $data_M_ODate        . ''  ;


        $data_OrderInfo     =  $data_m_info .'|'. $data_t_info .'|'. $data_s_info .'|'. $data_r_info ;
        $data_OrderMessage  =  $data_OrderInfo . $data_PrivateKey ;
        $data_Digest        =  strtoupper(trim(md5($data_OrderMessage)));

        /*
        $def_url =  "<form name='FORM' method='post' action='https://www.800-pay.com/PayAction/ReceivePay.aspx'>".
                    "   <input type='hidden' name='OrderMessage' value='". $data_OrderInfo ."'>".
                    "   <input type='hidden' name='Digest' value='". $data_Digest ."'>".
                    "   <input type='hidden' name='m_id' value='". $data_M_ID ."'>".
                    "   <input type='submit' name='s' value='" . $GLOBALS['_LANG']['pay_button'] . "'>".
                    "</form>" ;
        */

        $fields = array(
                    'OrderMessage' => $data_OrderInfo,
                    'Digest' => $data_Digest,
                    'm_id' => $data_M_ID,
        );

        return $this->form_info('https://www.800-pay.com/PayAction/ReceivePay.aspx',
                                'POST',
                                $fields);
    }

    /**
     * ��Ӧ����
     */
    function respond()
    {
        $payment = $this->get_config();

        $data_PrivateKey    = $payment['pay800_key'];
        $get_PayResult      = false;

        $rec_M_id           = $_REQUEST['M_ID'];
        $rec_OrderMessage   = $_REQUEST['OrderMessage'];
        $rec_Digest         = $_REQUEST['digest'];



        $data_OrderMessage  = $rec_OrderMessage . $data_PrivateKey;
        $data_Digest        = strtoupper(trim(md5($data_OrderMessage)));

        if ($rec_OrderMessage == '')
        {
            //echo '����������ϢΪ��ֵ';
            return $get_PayResult;
        }

        if ($rec_Digest == '')
        {
            //echo '��֤ǩ��Ϊ��ֵ';
            return $get_PayResult;
        }

        if ($data_Digest == $rec_Digest)
        {
            $tempStr = $rec_OrderMessage;
            $V = explode('|',$tempStr);
            $num = count($V);
            if ($num !== 25)  //����ʱ�������һ������ m_serial������Ӧ����25
            {
                //echo 'error message = '. $tempStr .'<br /><br />';
                return $get_PayResult;
            }

            $data_m_id          =   $V[0];
            $data_m_orderid     =   $V[1];
            $data_m_oamount     =   $V[2];
            $data_m_ocurrency   =   $V[3];
            $data_m_url         =   $V[4];
            $data_m_language    =   $V[5];

            $data_T_TradeName   =   $V[6];
            $data_T_Unit        =   $V[7];
            $data_T_UnitPrice   =   $V[8];
            $data_T_quantity    =   $V[9];
            $data_T_carriage    =   $V[10];

            $data_s_name        =   $V[11];
            $data_s_addr        =   $V[12];
            $data_s_postcode    =   $V[13];
            $data_s_tel         =   $V[14];
            $data_s_eml         =   $V[15];

            $data_r_name        =   $V[16];
            $data_r_addr        =   $V[17];
            $data_r_postcode    =   $V[18];
            $data_r_tel         =   $V[19];
            $data_r_eml         =   $V[20];

            $data_m_ocomment    =   $V[21];
            $data_m_status      =   $V[22];
            $data_m_odate       =   $V[23];

            $data_m_serial      =   $V[24];

            $this->total_fee = $data_m_oamount;
            if ($this->is_paid(trim($data_m_orderid)))
            {
                return true;
            }
            /*
            if ($data_m_status == 2)
            {
                echo '��֤�ɹ�!'    . '<br><br>';
                echo '�� �� ��    ='        . $data_m_id        . '<br>';
                echo '֧������    ='        . $data_m_orderid   . '<br>';
                echo '֧�����    ='        . $data_m_oamount   . '<br>';
                echo '��   �� ��  ='        . $data_m_ocurrency . '<br>';
                echo '�����ַ    ='        . $data_m_url       . '<br>';
                echo '����ѡ��    ='        . $data_m_language  . '<br>';

                echo '��Ʒ����    ='        . $data_T_TradeName . '<br>';
                echo '��Ʒ��λ    ='        . $data_T_Unit      . '<br>';
                echo '��Ʒ����    ='        . $data_T_UnitPrice . '<br>';
                echo '��Ʒ����    ='        . $data_T_quantity  . '<br>';
                echo '��Ʒ�˷�    ='        . $data_T_carriage  . '<br>';

                echo '����������     ='     . $data_s_name      . '<br>';
                echo '������סַ  ='        . $data_s_addr      . '<br>';
                echo '����������     ='     . $data_s_postcode  . '<br>';
                echo '�����ߵ绰     ='     . $data_s_tel       . '<br>';
                echo '�������ʼ�     ='     . $data_s_eml       . '<br>';

                echo '�ջ�����    ='        . $data_r_name      . '<br>';
                echo '�ջ�סַ    ='        . $data_r_addr      . '<br>';
                echo '�ջ�����    ='        . $data_r_postcode  . '<br>';
                echo '�ջ��绰    ='        . $data_r_tel       . '<br>';
                echo '�ջ��ʼ�    ='        . $data_r_eml       . '<br>';

                echo '��      ע     ='     . $data_m_ocomment  . '<br>';
                echo '֧��״̬    ='        . $data_m_status    . '<br>';
                echo '֧������    ='        . $data_m_odate     . '<br>';

                echo 'ϵͳ�ο���     ='     . $data_m_serial    . '<br>';

                echo '<br>���ص���֤����� ';
            }
            else
            {
                echo '֧��ʧ��!<br />';
            }
            */

            switch ($data_m_status)
            {
                case '0':
                    //echo '0.δ֧��';
                    break;
                case '2':
                    //echo '2.֧���ɹ�';
                    $get_PayResult = true;

                    $this->init_order($data_m_orderid);
                    $this->order_paid();

                    return ORDER_STATUS_ACCEPTTED;
                    break;
                case '3':
                    //echo '3.֧��ʧ��';
                    break;
                default:
                    //echo '֧��״̬ ����';
                    break;
            }
        }
        else
        {
            //echo 'ʧ�ܣ���Ϣ���ܱ��۸�';
        }

        return $get_PayResult;
    }
}

?>