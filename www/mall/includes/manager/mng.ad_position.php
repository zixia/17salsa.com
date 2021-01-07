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
 * $Id: mng.ad_position.php 6009 2008-10-31 01:55:52Z Garbin $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class AdPositionManager extends Manager
{
    /**
     *  构造函数
     *  @param int $store_id
     *  @return none
     */

    function __construct()
    {
        $this->AdPositionManager();
    }

    function AdPositionManager()
    {
        parent::__construct();
    }

    /**
     *  获取广告位列表
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'adp.position_id', $pagesize);

        $sql = "SELECT count(ad.ad_id) AS num, adp.* ".
                "FROM `ecm_ad_position` AS adp ".
                "LEFT JOIN `ecm_ad` AS ad ON adp.position_id=ad.position_id ".
                "WHERE $arg[where] GROUP BY adp.position_id ".
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";

        $res = $GLOBALS['db']->getAll($sql);
        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     *  获取广告位总数
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_ad_position` WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  添加广告位
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_ad_position`", $info, "INSERT");
    }

    /**
     *  获取广告位下拉列表项
     *  @param string $ids
     *  @return void
     */
    function get_options()
    {
        $sql = "SELECT position_name, position_id FROM `ecm_ad_position`";
        $res = $GLOBALS['db']->query($sql);

        $options = array();

        while ($row = mysql_fetch_assoc($res))
        {
            $options[$row['position_id']] = $row['position_name'];
        }
        return $options;
    }
}

?>