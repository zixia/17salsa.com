<?php

/**
 * ECMALL: �ƻ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
     * @author  wj
     * @param   string      $act
     * @return  void
     */
    function __construct($act)
    {
        $this->CrontabController($act);
    }

    /**
     * ���캯��
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
     * ���񴥷�����
     * @author      wj
     * @param       void
     * @return      void
     */
    function run()
    {
        $cur_time = gmtime();
        $list = $this->get_task($cur_time);
        //�������ִ��
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
     * ��ȡ��ǰ����
     * @author  wj
     * @param   int     $cur_time
     * @return  boolen
     */
    function get_task($cur_time)
    {
        return $this->obj->get_task($cur_time);
    }

    /**
     * ����������´�ִ��ʱ��
     * @author  wj
     * @param   int     $plan_time
     * @return  void
     */
    function update_time($plan_time)
    {
        $this->obj->update_time($this->cur_fun, $plan_time);
    }

    /**
     * �������Ƿ�رգ��Ƿ�Ҫ���͵��������ʼ�
     * @author      wj
     * @param       int     $plan_time
     * @param       int     $run_time
     * @return      void
     */
    function auto_store_handle($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 86400); //�Ѽƻ�ʱ���Ϊ24Сʱ֮��

        //ִ�д���
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
     * �Զ���������
     * @author      wj
     * @param       int     $plan_time
     * @param       int     $run_time
     * @return      void
     */
    function auto_order_handle($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 43200); //�Ѽƻ�ʱ���Ϊ12Сʱ֮��

        include_once(ROOT_PATH . '/includes/manager/mng.order.php');

        $order_manager = new OrderManager(0);
        $order_manager->set_conf(array( 'mall_store_repeat_limit' => $this->conf('mall_store_repeat_limit'),
                                        'mall_goods_repeat_limit' => $this->conf('mall_goods_repeat_limit'),
                                        'mall_min_goods_amount'   => $this->conf('mall_min_goods_amount'),
                                        'mall_max_goods_amount'   => $this->conf('mall_max_goods_amount')));

        /* ��14����δȷ���ջ��Ķ����Զ���Ϊȷ��״̬���������14δ�����۵��Զ����� */
        $order_manager->auto_delivered(constant(strtoupper($this->conf('mall_auto_evaluation_value'))));
        $order_manager->auto_evaluation(constant(strtoupper($this->conf('mall_auto_evaluation_value'))));
    }

    /**
     * �Զ����ʼ�
     * @author  wj
     * @param   int     $plan_time
     * @param   int     $run_time
     * @return  void
     */
    function auto_send_mail($plan_time, $run_time)
    {
        $cur_time = gmtime();
        $this->update_time($cur_time + 3000); //��ʱ���Ϊ50����֮��

        require_once ROOT_PATH . '/includes/cls.mail_queue.php';
        $mail_protocol = ($this->conf('mall_email_type') != 'smtp') ? MAIL_PROTOCOL_LOCAL : MAIL_PROTOCOL_SMTP;
        $mailer = new MailQueue($this->conf('mall_name'), $this->conf('mall_email_addr'), $mail_protocol,
            $this->conf('mall_email_host'), $this->conf('mall_email_port'), $this->conf('mall_email_id'),
            $this->conf('mall_email_pass'));
        $result = $mailer->send(5);
    }
}
?>
