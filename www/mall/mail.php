<?php

/**
 * ECMALL: �������ĸ������Ϲ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: pm.php 5311 2008-07-23 02:54:12Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class MailController extends ControllerFrontend
{
    var $_allowed_actions = array('send');

    function __construct($act)
    {
        $this->MailController($act);
    }

    function MailController($act)
    {
        if (empty($act))
        {
            $act = 'send';
        }
        parent::__construct($act);
    }

    /**
     *  �����ʼ�
     *
     * @author  wj
     * @return  void
     */
    function send()
    {
        require_once ROOT_PATH . '/includes/cls.mail_queue.php';
        $mail_protocol = ($this->conf('mall_email_type') != 'smtp') ? MAIL_PROTOCOL_LOCAL : MAIL_PROTOCOL_SMTP;
        $mailer = new MailQueue($this->conf('mall_name'), $this->conf('mall_email_addr'), $mail_protocol,
            $this->conf('mall_email_host'), $this->conf('mall_email_port'), $this->conf('mall_email_id'),
            $this->conf('mall_email_pass'));
        $result = $mailer->send(5);
        if (DEBUG_MODE)
        {
            echo 'alert("' . strtr(ecm_json_encode($result), array('"'=>'')) . '");';
        }
        else
        {
            echo 'var result = "' . strtr(ecm_json_encode($result), array('"'=>'')) . '";';
        }

    }
};
?>