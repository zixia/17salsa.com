<?php

/**
 * ECMall: �������߲��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: chinabank.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/models/mod.payment.php');

/* �������԰� */
Language::load_lang(lang_file('payment/chinabank'));

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'chinabank_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ֧������ */
    $modules[$i]['pay_fee'] = '2.5%';

    /* ���� */
    $modules[$i]['author']  = 'ECMall TEAM';

    /* ��ַ */
    $modules[$i]['website'] = 'http://www.chinabank.com.cn';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.1';

    /* ֧�ֵĻ��� */
    $modules[$i]['currency'] = array('CNY');

    /* ������Ϣ */
    $modules[$i]['config'] = array(
        array('name' => 'chinabank_account', 'type' => 'text', 'value' => ''),
        array('name' => 'chinabank_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * ��
 */
class chinabank extends Payment
{
    /* ֧���Ļ��� */
    var $currency  = array('CNY');

    /**
     * ����֧������
     * @param   array   $order      ��������
     */
    function get_code($order_obj)
    {
        $payment            = $this->get_config();
        $order              = $order_obj->get_info();
        $data_vid           = trim($payment['chinabank_account']);
        $data_orderid       = $order_obj->get_pay_log();
        $data_vamount       = $order['payable'];
        $data_vmoneytype    = 'CNY';
        $data_vpaykey       = trim($payment['chinabank_key']);
        $data_vreturnurl    = $this->get_respond_url(basename(__FILE__, '.php'));

        $MD5KEY =$data_vamount.$data_vmoneytype.$data_orderid.$data_vid.$data_vreturnurl.$data_vpaykey;
        $MD5KEY = strtoupper(md5($MD5KEY));

        /*
        $def_url  = '<br /><form style="text-align:center;" method=post action="https://pay3.chinabank.com.cn/PayGate" target="_blank">';
        $def_url .= "<input type=HIDDEN name='v_mid' value='".$data_vid."'>";
        $def_url .= "<input type=HIDDEN name='v_oid' value='".$data_orderid."'>";
        $def_url .= "<input type=HIDDEN name='v_amount' value='".$data_vamount."'>";
        $def_url .= "<input type=HIDDEN name='v_moneytype'  value='".$data_vmoneytype."'>";
        $def_url .= "<input type=HIDDEN name='v_url'  value='".$data_vreturnurl."'>";
        $def_url .= "<input type=HIDDEN name='v_md5info' value='".$MD5KEY."'>";
        $def_url .= "<input type=submit value='" .$GLOBALS['_LANG']['pay_button']. "'>";
        $def_url .= "</form>";
       */

        $fields  = array(
            'v_mid'         => $data_vid,
            'v_oid'         => $data_orderid,
            'v_amount'      => $data_vamount,
            'v_moneytype'   => $data_vmoneytype,
            'v_url'         => $data_vreturnurl,
            'v_md5info'     => $MD5KEY
        );

        return $this->form_info('https://pay3.chinabank.com.cn/PayGate',
                                'POST',
                                $fields);
    }

    /**
     * ��Ӧ����
     */
    function respond()
    {
        if ($this->is_paid(trim($_POST['v_oid'])))
        {
            return true;
        }
        $payment        = $this->get_info();

        $v_oid          = trim($_POST['v_oid']);
        $v_pmode        = trim($_POST['v_pmode']);
        $v_pstatus      = trim($_POST['v_pstatus']);
        $v_pstring      = trim($_POST['v_pstring']);
        $v_amount       = trim($_POST['v_amount']);
        $v_moneytype    = trim($_POST['v_moneytype']);
        $remark1        = trim($_POST['remark1' ]);
        $remark2        = trim($_POST['remark2' ]);
        $v_md5str       = trim($_POST['v_md5str' ]);

        $this->total_fee = $v_amount;

        /**
         * ���¼���md5��ֵ
         */
        $key            = $payment['chinabank_key'];

        $md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

        /* �����Կ�Ƿ���ȷ */
        if ($v_md5str==$md5string)
        {
            if ($v_pstatus == '20')
            {
                $this->init_order($v_oid);
                $this->order_paid();
                /* �ı䶩��״̬ */

                return ORDER_STATUS_ACCEPTTED;
            }
        }
        else
        {
            $this->err = 'wrong_sign';

            return false;
        }
    }
}

?>