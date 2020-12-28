<?php

/**
 * ECMall: : �������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.activity.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ActivityManager extends Manager
{
    var $_store_id = 0;
    var $_type = null;

    function __construct($type, $store_id=0)
    {
        $this->ActivityManager($type, $store_id);
    }

    function ActivityManager($type, $store_id=0)
    {
        $this->_type     = $type;
        $this->_store_id = intval($store_id);
    }

    /**
     * ��û�б�
     *
     * @return  array
     */
    function get_list($page, $condition=array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'act_id', $pagesize);

        $sql = "SELECT a.*, gs.default_image " .
                " FROM `ecm_goods_activity` AS a " .
                " LEFT JOIN `ecm_goods` AS g ON a.goods_id = g.goods_id " .
                " LEFT JOIN `ecm_goods_spec` gs ON g.default_spec = gs.spec_id" .
                " WHERE " .$arg['where'] .
                " ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
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
    function get_count($condition=array())
    {
        $where  = $this->_make_condition($condition);
        $sql    = "SELECT COUNT(*) FROM `ecm_goods_activity` AS a, `ecm_goods` AS g WHERE g.goods_id=a.goods_id AND $where";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }

    /**
     * ����һ���
     *
     * @param  array    $post
     *
     * @return  int
     */
    function add($post)
    {
        $post['store_id'] = $this->_store_id;
        $post['act_type'] = $this->_type;

        return $GLOBALS['db']->autoExecute('`ecm_goods_activity`', $post);
    }

    /**
     * ������ѯ����
     *
     * @author  scottye
     * @param   array       $condition
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = " act_type = '" . $this->_type . "' AND g.is_deny=0";
        if ($this->_store_id)
        {
            $where .= " AND a.store_id = '" . $this->_store_id . "' ";
        }

        if (!empty($condition['store_is_open']))
        {
            $where .= " AND a.store_id " . db_create_in($this->get_open_store());
        }

        if (isset($condition['underway']))
        {
            // �����е�
            $now = gmtime();
            $where .= " AND start_time < '$now' AND end_time > '$now'";
        }
        else
        {
            if (isset($condition['start_time']))
            {
                $where .= " AND start_time>='$condition[start_time]'";
            }

            if (isset($condition['end_time']))
            {
                $where .= " AND end_time<='$condition[end_time]'";
            }

            if (isset($condition['actual']))
            {
                $where .= " AND start_time <= " .gmtime();
            }
        }

        return $where;
    }

};
?>