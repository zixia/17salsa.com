<?php
/**
 * ECMALL: 广告位管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.ad.php 6009 2008-10-31 01:55:52Z Garbin $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class AdManager extends Manager
{
    var $position_id = 0;

    /**
     *  构造函数
     *  @param int $position_id, $store_id
     *  @return void
     */
    function __construct($store_id = 0)
    {
        $this->AdManager($store_id);
    }

    function AdManager($store_id = 0)
    {
        parent::__construct($store_id);
    }

    /**
     *  获取广告列表
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, 'ad.ad_id');

        $sql = "SELECT ad.*, adp.position_name ".
                "FROM `ecm_ad` AS ad ".
                "LEFT JOIN `ecm_ad_position` AS adp ON adp.position_id=ad.position_id ".
                "WHERE $arg[where] ".
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
        $sql        = "SELECT COUNT(*) FROM `ecm_ad` WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  添加广告
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_ad`", $info);
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
            $where .= " AND ad.store_id = " . $this->_store_id;
        }

        if (!empty($condition['is_top']))
        {
            $where .= " AND is_top=1";
        }

        if (!empty($condition['cate_id']))
        {
            $where .= " AND art.cate_id='$condition[cate_id]'";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND title LIKE '%$condition[keywords]%'";
        }

        return $where;
    }
}

?>