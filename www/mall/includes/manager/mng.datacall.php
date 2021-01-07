<?php
/**
 * ECMALL: 数据调用管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.ad.php 3849 2008-05-27 05:19:55Z Weberliu $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class DataCallManager extends Manager
{
    var $position_id = 0;

    /**
     *  构造函数
     *  @param int $position_id, $store_id
     *  @return void
     */
    function __construct($store_id = 0)
    {
        $this->DataCallManager($store_id);
    }

    function DataCallManager($store_id = 0)
    {
        $this->_store_id = $store_id;
        parent::__construct($store_id);
    }

    /**
     *  获取数据调用列表
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, 'dc.id');

        $sql = "SELECT *, c.cate_name ".
                "FROM `ecm_data_call` AS dc " .
                "LEFT JOIN `ecm_category` AS c ON c.cate_id = dc.cate_id " .
                "WHERE $arg[where] " .
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";

        $res = $GLOBALS['db']->getAll($sql);
        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     *  获取广告总数
     *  @param int $condition
     *  @return int
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_data_call` AS dc WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  添加数据调用
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_data_call`", $info);
    }

    /**
     *  将数组形式的$conditions转换成SQL的WHERE部分语句
     *  @param mixed $conditions
     *  @return string
     */
    function _make_condition($condition)
    {
        $where = '1';
        if ($this->_store_id > 0)
        {
            $where .= " AND dc.store_id = " . $this->_store_id;
        }

        return $where;
    }
}

?>