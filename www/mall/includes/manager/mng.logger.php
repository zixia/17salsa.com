<?php

/**
 * ECMALL: ����Ա��־������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * =======================s=====================================================
 * $Id: mng.logger.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class Logger extends Manager
{
    var $_store_id = 0;

    /**
     *  ָ��Ҫ��¼�ı�
     *
     *  @access
     */

    var $_table;


    /**
     *  ָ��Ҫ��¼���ֶ�
     *
     *  @access
     */

    var $_fields = array();

    /**
     *  ��ʾ��¼ʱ����ֶ�����
     *
     *  @access
     */

    var $_time_filed_name = 'execution_time';

    /**
     *  ������
     *
     *  @access
     */

    var $_primary_key = 'log_id';

    /**
     *  ���캯�� PHP5
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
     *  ���캯��,PHP4
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
     *  д����־
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
     *  ��ȡ��־�б�
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
     * ��÷��������ļ�¼����
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
     *  ��ȡ��־
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
     *  ɾ����־
     *
     *  @author wj
     *  @params int $time   С�����ʱ����ļ�¼����ɾ��
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