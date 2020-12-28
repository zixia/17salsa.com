<?php

/**
 * ECMALL: 计划任务管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 构造函数
     * @author  wj
     * @param   void
     * @return  void
     */
    function __construct()
    {
        $this->CrontabManager();
    }

    /**
     * 构造函数
     * @author  wj
     * @param   void
     * @return  void
     */
    function CrontabManager()
    {

    }

    /**
     * 获取当前能执行的任务
     * @author  wj
     * @param   int     $cur_time
     * @return  array
     */
    function get_task($cur_time)
    {
        $cur_time = intval($cur_time);
        //这个sql $cur_time 不能加引号，加了就成字符比较了。
        $sql = "SELECT * FROM `ecm_crontab` WHERE plan_time < " . $cur_time;

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * 更新计划执行时间和执行时间
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