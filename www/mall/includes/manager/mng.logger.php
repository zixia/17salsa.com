<?php

/**
 * ECMALL: 管理员日志管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * =======================s=====================================================
 * $Id: mng.logger.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class Logger extends Manager
{
    var $_store_id = 0;

    /**
     *  指定要记录的表
     *
     *  @access
     */

    var $_table;


    /**
     *  指定要记录的字段
     *
     *  @access
     */

    var $_fields = array();

    /**
     *  表示记录时间的字段名称
     *
     *  @access
     */

    var $_time_filed_name = 'execution_time';

    /**
     *  主键名
     *
     *  @access
     */

    var $_primary_key = 'log_id';

    /**
     *  构造函数 PHP5
     *
     *  @access
     *  @params
     *  @return
     */

    function __construct($store_id = 0)
    {
        $this->Logger($store_id);
    }

    /**
     *  构造函数,PHP4
     *
     *  @access public
     *  @params int $store_id
     *  @return void
     */

    function Logger($store_id = 0)
    {
        $this->_store_id = $store_id;
    }

    /**
     *  写入日志
     *
     *  @access public
     *  @params array $log_info
     *  @return int
     */

    function write($log_info)
    {
        $sql = "INSERT INTO {$this->_table} SET ";
        $values = array();
        foreach ($log_info as $_k => $_v)
        {
            !$_v && $_v = 0;
            in_array($_k, $this->_fields) && $values[] = is_string($_v) ? "{$_k}='{$_v}'" : "{$_k}={$_v}" ;
        }
        $sql .= implode(',', $values);
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->insert_id();
    }

    /**
     *  读取日志列表
     *
     *  @access public
     *  @params int $page
     *  @params mixed $condition
     *  @return array
     */

    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, $this->_primary_key);
        $sql = "SELECT * FROM {$this->_table} WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);

        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     * 获得符合条件的记录总数
     *
     * @param   array   $condition
     *
     * @return  int
     */

    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM {$this->_table} WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  读取日志
     *
     *  @access
     *  @params
     *  @return
     */

    function read($log_id)
    {
        //$sql = "SELECT * FROM {$this->_table} WHERE ";
    }

    /**
     *  删除日志
     *
     *  @author wj
     *  @params int $time   小于这个时间戳的记录将被删除
     *  @return int
     */

    function drop($time)
    {
        $sql = "DELETE FROM {$this->_table} WHERE store_id = '{$this->_store_id}' AND {$this->_time_field_name} <= $time";

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }
}
?>