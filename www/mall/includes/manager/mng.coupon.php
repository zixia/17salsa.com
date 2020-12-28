<?php

/**
 * ECMall: : 促销券管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 获得促销券列表
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
     * 获得符合条件的记录总数
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
     * 新增一个促销券
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
     * 创建查询条件
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