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
 * $Id: pm.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class PmController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'send');

    function __construct($act)
    {
        $this->PmController($act);
    }

    function PmController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �鿴����Ϣ
     *
     * @return  void
     */
    function view()
    {
        if ($_GET['local'] == 'backend')
        {
            $user_id = $_SESSION['admin_id'];
        }
        else
        {
            $user_id = $_SESSION['user_id'];
        }
        uc_call('uc_pm_location', array($user_id));
        return;
    }

    /**
     * ���Ͷ���Ϣ
     *
     * @return  void
     */
    function send()
    {
        uc_call('uc_pm_send', array($_SESSION['user_id'], $_REQUEST['msgto'], $_REQUEST['subject'], $_REQUEST['message'], 0, $_REQUEST['replypid'], $_REQUEST['isusername']));
        return;
    }
};
?>