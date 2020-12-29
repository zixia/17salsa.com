<?php

/**
 * ECMALL: �ƻ����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.crontab.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class CrontabManager extends Manager
{
    /**
     * ���캯��
     * @author  wj
     * @param   void
     * @return  void
     */
    function __construct()
    {
        $this->CrontabManager();
    }

    /**
     * ���캯��
     * @author  wj
     * @param   void
     * @return  void
     */
    function CrontabManager()
    {

    }

    /**
     * ��ȡ��ǰ��ִ�е�����
     * @author  wj
     * @param   int     $cur_time
     * @return  array
     */
    function get_task($cur_time)
    {
        $cur_time = intval($cur_time);
        //���sql $cur_time ���ܼ����ţ����˾ͳ��ַ��Ƚ��ˡ�
        $sql = "SELECT * FROM `ecm_crontab` WHERE plan_time < " . $cur_time;

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * ���¼ƻ�ִ��ʱ���ִ��ʱ��
     * @author  wj
     * @param   string  $task_name
     * @param   int     plan_time
     * @return  boolen
     */
    function update_time($task_name, $plan_time)
    {
        $cur_time = gmtime();

        $sql = "UPDATE `ecm_crontab` SET run_time='$cur_time', plan_time='$plan_time'".
               " WHERE task_name = '$task_name'";

        return $GLOBALS['db']->query($sql);
    }

};
?>