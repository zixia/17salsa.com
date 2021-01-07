<?php

/**
 * ECMALL: 管理中心个人资料管理
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     *  发送邮件
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