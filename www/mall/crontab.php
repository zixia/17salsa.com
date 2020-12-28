<?php

/**
 * ECMALL: 计划任务
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: crontab.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/manager/mng.crontab.php');
class CrontabController extends ControllerFrontend
{
    var $_allowed_actions = array('run');
    var $cur_fun = '';
    var $obj = null;

    /**
     * 构造函数
     * @author  wj
     * @param   string      $act
     * @return  void
     */
    function __construct($act)
    {
        $this->CrontabController($act);
    }

    /**
     * 构造函数
     * @author  wj
     * @param   string      $act
     * @return  void
     */
    function CrontabController($act)
    {
        if (empty($act))
        {
            $act = 'run';
        }
        $this->obj = new CrontabManager();
        parent::__construct($act);
    }

    /**
     * 任务触发函数
     * @author      wj
     * @param       void
     * @return      void
     */
    function run()
    {
        $cur_time = gmtime();
        $list = $this->get_task($cur_time);
        //有任务就执行
        if ($list)
        {
            foreach($list as $task)
            {
                $this->cur_fun = $task['task_name'];
                echo date('H:i:s') . ': Run task ' . $this->cur_fun . '<br />' . "\r\n";
                call_user_method($this->cur_fun , $this, $task['plan_time'], $task['run_time']);
            }
        }
        else
        {
            echo 'no task!';
        }
    }

    /**
     * 获取当前任务
     * @author  wj
     * @param   int     $cur_time
     * @return  boolen
     */
    function get_task($cur_time)
    {
        return $this->obj->get_task($cur_time);
    }

    /**
     * 更新任务的下次执行时间
     * @author  wj
     * @param   int     $plan_time
     * @return  void
     */
    function update_time($plan_time)
    {
        $this->obj->update_time($this->cur_fun, $plan_time);
    }

    /**
     * 检查店铺是否关闭，是否要发送店铺续费邮件
     * @author      wj
     * @param       int     $plan_time
     * @param       int     $run_time
     * @return      void
     */
    function auto_store_handle($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 86400); //把计划时间改为24小时之后

        //执行代码
        $now = gmtime();
        $sql = "SELECT s.store_id, u.user_name, u.email, FLOOR((s.end_time - $now) / 86400) AS days_left " .
                "FROM `ecm_store` AS s LEFT JOIN `ecm_users` AS u ON s.store_id = u.user_id " .
                "WHERE s.is_open = 1 " .
                "AND s.end_time > $now " .
                "AND CEIL((s.end_time - $now) / 86400) IN (7, 14, 30) ";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $uid = $row['store_id'];
            $email = $row['email'];
            $this->send_mail($email, 'relet_remind', $row);
        }
    }

    /**
     * 自动订单处理
     * @author      wj
     * @param       int     $plan_time
     * @param       int     $run_time
     * @return      void
     */
    function auto_order_handle($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 43200); //把计划时间改为12小时之后

        include_once(ROOT_PATH . '/includes/manager/mng.order.php');

        $order_manager = new OrderManager(0);
        $order_manager->set_conf(array( 'mall_store_repeat_limit' => $this->conf('mall_store_repeat_limit'),
                                        'mall_goods_repeat_limit' => $this->conf('mall_goods_repeat_limit'),
                                        'mall_min_goods_amount'   => $this->conf('mall_min_goods_amount'),
                                        'mall_max_goods_amount'   => $this->conf('mall_max_goods_amount')));

        /* 将14天尚未确认收货的订单自动置为确认状态，交易完成14未作评价的自动评价 */
        $order_manager->auto_delivered(constant(strtoupper($this->conf('mall_auto_evaluation_value'))));
        $order_manager->auto_evaluation(constant(strtoupper($this->conf('mall_auto_evaluation_value'))));
    }

    /**
     * 自动发邮件
     * @author  wj
     * @param   int     $plan_time
     * @param   int     $run_time
     * @return  void
     */
    function auto_send_mail($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 3000); //把时间改为50分钟之后

        require_once ROOT_PATH . '/includes/cls.mail_queue.php';
        $mail_protocol = ($this->conf('mall_email_type') != 'smtp') ? MAIL_PROTOCOL_LOCAL : MAIL_PROTOCOL_SMTP;
        $mailer = new MailQueue($this->conf('mall_name'), $this->conf('mall_email_addr'), $mail_protocol,
            $this->conf('mall_email_host'), $this->conf('mall_email_port'), $this->conf('mall_email_id'),
            $this->conf('mall_email_pass'));
        $result = $mailer->send(5);
    }
}
?>
