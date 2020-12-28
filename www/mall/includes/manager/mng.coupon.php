<?php

/**
 * ECMall: : ����ȯ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.coupon.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class CouponManager extends Manager
{
    function __construct($store_id=0)
    {
        $this->CouponManager($store_id);
    }

    function CouponManager($store_id)
    {
        $this->store_id = intval($store_id);
    }

    /**
     * ��ô���ȯ�б�
     *
     * @return  array
     */
    function get_list($page, $condition=array())
    {
        $arg = $this->query_params($page, $condition, 'coupon_id');

        $sql = "SELECT * ".
                " FROM `ecm_coupon` WHERE " .$arg['where'].
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
        $sql    = "SELECT COUNT(*) FROM `ecm_coupon` WHERE $where";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }
    /**
     * ����һ������ȯ
     *
     * @param  array    $post
     *
     * @return  int
     */
    function add($post)
    {
        $post['store_id'] = $this->store_id;

        return $GLOBALS['db']->autoExecute('`ecm_coupon`', $post);
    }
    /**
     * ������ѯ����
     *
     * @param  array    $condition
     *
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = "store_id=$this->store_id";

        if (isset($condition['start_time']))
        {
            $where .= " AND start_time>='$condition[start_time]'";
        }

        if (isset($condition['end_time']))
        {
            $where .= " AND (end_time<='$condition[end_time]' OR end_time=0)";
        }

        return $where;
    }

};
?>