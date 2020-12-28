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
     * 查看短消息
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
     * 发送短消息
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