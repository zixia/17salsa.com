<?php

/**
 * ECMALL: Manager Base
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.base.php 6108 2008-11-25 08:40:18Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempting.', E_USER_ERROR);
}

class Manager
{
    var $err = null;
    var $_pagesize = 20;

    /**
     * 构造函数
     *
     * @author   wj
     * @return   void
     */
    function __construct()
    {
        $this->Manager();
    }

    /**
     * Constructor
     *
     * @return object
     */
    function Manager()
    {
        //TODO: insert your code
    }
    /**
     * Get query params
     * @author  redstone
     * @param   int     $page
     * @param   array   $condition
     * @param   string  $default_sort
     * @param   int     $pagesize
     *
     * @return array
     */
    function query_params($page, $condition, $default_sort, $pagesize = 0)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arr['number']  = $this->_get_pagesize();
        $arr['start']   = ($page - 1) * $arr['number'];
        $arr['count']   = $this->get_count($condition);
        $arr['where']   = $this->_make_condition($condition);
        $arr['info']    = $this->_page_info($page, $arr['count']);
        $arr['sort']    = (!empty($_GET['sort']) && preg_match('/^[\w]+$/', $_GET['sort']) > 0) ? trim($_GET['sort']) : $default_sort;
        $arr['order']   = (!empty($_GET['order']) && strtolower($_GET['order']) == 'asc') ? 'ASC' : 'DESC';

        return $arr;
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
        return 0;
    }

    function _page_info($page, $rec_count)
    {
        $segment    = 4;
        $pagesize   = $this->_get_pagesize();
        $page_count = ceil((int)$rec_count / $pagesize);

        if ($page_count < 1)
        {
            $page_count = 1;
        }
        $prev_page  = ($page > 1) ? $page - 1 : 1;
        $next_page  = ($page < $page_count) ? $page + 1 : $page_count;

        $result     = array('rec_count'     => $rec_count,
                            'page_count'    => $page_count,
                            'prev_page'     => $prev_page,
                            'next_page'     => $next_page,
                            'curr_page'     => $page);
        return $result;
    }
    /**
     * 获得每页的记录数。
     *
     * @return  int
     */
    function _get_pagesize()
    {
        if ($this->_pagesize <= 0) $this->_pagesize = 20;

        return $this->_pagesize;
    }

    /**
     * 设置pagesize
     */
    function set_pagesize($pagesize = 0)
    {
        $this->_pagesize = $pagesize;
    }

    /**
     * 创建查询条件语句
     *
     * @author  scottye
     * @param   array   $condition
     * @return  string
     */
    function _make_condition($condition)
    {
        $where  = '1';
        if (!empty($condition['store_is_open']))
        {
            $where .= " AND store_id " . db_create_in($this->get_open_store());
        }
        return $where;
    }

    /**
     * 取得开启的店铺
     *
     * @author  scottye
     * @param   array   $ids    店铺id范围
     * @param   array   $region 地区ID
     * @return  array   店铺id数组
     */
    function get_open_store($ids = array(), $region = 0)
    {
        $now = gmtime();
        $sql = "SELECT store_id FROM `ecm_store` WHERE is_open = 1 AND (end_time = 0 OR end_time >= '$now') ";
        if ($ids)
        {
            $sql .= " AND store_id " . db_create_in($ids);
        }

        if ($region > 0)
        {
            $sql .= " AND store_location = '$region'";
        }

        $ids = $GLOBALS['db']->getCol($sql);

        $sql = "SELECT store_id FROM `ecm_config_value` WHERE code = 'store_status' AND value = 1";
        $ids = array_intersect($ids, $GLOBALS['db']->getColCached($sql));

        return array_merge($ids, array(0));
    }
};

?>
