<?php
/**
 * ECMALL: 店主通知
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: notify.php  2008-09-12 14:20:24Z XZF $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class NotifyController extends ControllerBackend
{
    function __construct($act)
    {
        $this->NotifyController($act);
    }

    function NotifyController($act)
    {
        if (empty($act))
        {
            $act = 'show';
        }
        parent::ControllerBackend($act);
    }

    /**
     * 店主通知界面显示
     *
     * @author  xzf
     * @return  void
     */
    function show()
    {
        $this->logger = false;
        $this->display('notify.html','mall');
    }

    /**
     * 发送店主通知
     *
     * @author  xzf
     * @return  void
     */
    function send_notify_owner()
    {
        $data['post'] = $_POST;
        if (!trim($data['post']['pm_title']))
        {
            $this->show_warning('no_pm_title');
            return;
        }
        if (!trim($data['post']['pm_contents']))
        {
            $this->show_warning('no_pm_contents');
            return;
        }

        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $store = new StoreManager();
        $count = $store->get_count(array());
        $ownerlist = $store->get_list(1, array(), $count);

        for ($i = 0; $i < count($ownerlist['data']); $i++)
        {
            $data['ownerlist'][] = $ownerlist['data'][$i]['store_id'];
        }
        $msgto = join(',', $data['ownerlist']);
        $subject = $data['post']['pm_title'];
        $message = $data['post']['pm_contents'];
        $success = uc_call('uc_pm_send', array($_SESSION['admin_id'], $msgto, $subject, $message, 1));
        if ($success > 0)
        {
            $this->show_message('notify_success');
        }
        else
        {
            $this->show_message('notify_failure');
        }
    }
}