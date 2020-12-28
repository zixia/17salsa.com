<?php

/**
 * ECMALL: 管理员日志管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.admin_logs.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/* 加载日志记录器基类 */
include_once(ROOT_PATH . '/includes/manager/mng.logger.php');

class AdminLogManager extends Logger
{
    /**
     *  记录在这张表中
     *
     *  @access
     */

    var $_table = '`ecm_admin_log`';

    /**
     *  要记录的信息字段
     *
     *  @access
     */

    var $_fields = array('username',
                         'application',
                         'action',
                         'item_id',
                         'store_id',
                         'execution_time',
                         'ip_address');

    /**
     *  这个字段表示事件发生的时间
     *
     *  @access
     */

    var $_time_field_name = 'execution_time';

    /**
     *  这个字段则表示记录的唯一标识
     *
     *  @access
     */

    var $_primary_key = 'log_id';

    /**
     *  增加日志
     *
     *  @access
     *  @params
     *  @return
     */

    function add($username, $app, $act, $item_id=0)
    {
        $info = array('username'        => $username,
                      'application'     => $app,
                      'action'          => $act,
                      'item_id'         => $item_id,
                      'ip_address'      => real_ip(),
                      'execution_time'  => gmtime(),
                      'store_id'        => $this->_store_id);

        return $this->write($info);
    }

    /**
     * 创建查询条件语句
     *
     * @param   array   $condition
     *
     * @return  string
     */

    function _make_condition($condition)
    {
        $where  = "store_id = '" . $this->_store_id . "'";
        if (!empty($condition))
        {
            $where = "username LIKE '%$condition[username]%'";
        }

        return $where;
    }
};
?>