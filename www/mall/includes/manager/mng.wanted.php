<?php

/**
 * ECMALL: 求购信息管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * =======================s=====================================================
 * $Id: mng.wanted.php 6050 2008-11-11 03:16:50Z Garbin $
 */

class WantedManager extends Manager
{
    var $_store_id = 0;
    var $_primary_key = 'log_id';


    function __construct($store_id = 0)
    {
        $this->WantedManager($store_id);
    }

    /**
     *  构造函数,PHP4
     *
     *  @author Garbin
     *  @params int $store_id
     *  @return void
     */

    function WantedManager($store_id = 0)
    {
        $this->_store_id = $store_id;
    }

    /**
     *  添加求购信息
     *
     *  @access public
     *  @params array $log_info
     *  @return int
     */

    function add($log_info)
    {
        $now = gmtime();
        $user_id = intval($log_info['user_id']);
        $cate_id = intval($log_info['cate_id']);
        $subject = trim($log_info['subject']);
        $detail  = trim($log_info['detail']);
        $expiry  = $now + abs($log_info['expiry']) * 24 * 3600;
        $region_id= intval($log_info['region_id']);
        $price_start= floatval(abs($log_info['price_start']));
        $price_end  = floatval(abs($log_info['price_end']));
        $sql = 'INSERT INTO `ecm_wanted`(user_id, add_time, cate_id, region_id, price_start, price_end, subject, detail, expiry) ' .
               "VALUES({$user_id}, {$now}, {$cate_id}, '{$region_id}', {$price_start}, {$price_end}, '{$subject}', '{$detail}', {$expiry})";

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->insert_id();
    }

    /**
     *  读取日志列表
     *
     *  @author Garbin
     *  @params int $page
     *  @params mixed $condition
     *  @return array
     */

    function get_list($page, $condition = null, $page_size = 10)
    {
        $arg = $this->query_params($page, $condition, 'add_time', $page_size);
        $sql = 'SELECT al.*,u.user_name,r.region_name FROM `ecm_wanted` al '.
               'LEFT JOIN `ecm_users` u ON al.user_id=u.user_id ' .
               'LEFT JOIN `ecm_regions` r ON al.region_id=r.region_id '.
               "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);
        $now = gmtime();
        foreach ($res as $_k => $_v)
        {
            if ($_v['expiry'] < $now)
            {
                $res[$_k]['is_expire'] = 1;
            }
            else
            {
                $res[$_k]['is_expire'] = 0;
            }
            list($res[$_k]['start'], $res[$_k]['end']) = explode('-', $_v['price']);
            $res[$_k]['avatar'] = UC_API . '/avatar.php?uid=' . $_v['user_id'] . '&amp;size=small';
            $res[$_k]['uchome_url'] = uc_home_url($val['user_id']);
            $res[$_k]['expiry_days'] = floor(($_v['expiry'] - $_v['add_time']) / (24 * 3600));
        }

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
        $sql        = "SELECT COUNT(*) FROM `ecm_wanted` al WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  删除日志
     *
     *  @author Garbin
     *  @params mixed $ids
     *  @return int
     */

    function drop($ids)
    {
        $in_ids = db_create_in($ids);

        /* 删除求购主体 */
        $sql = "DELETE FROM `ecm_wanted` WHERE log_id{$in_ids}";
        $GLOBALS['db']->query($sql);
        $del_rows = $GLOBALS['db']->affected_rows();

        /* 删除回复 */
        $sql = "DELETE FROM `ecm_wanted_reply` WHERE log_id{$in_ids}";
        $GLOBALS['db']->query($sql);

        return $del_rows;
    }

    /**
     *    make conditions
     *
     *    @author:    Garbin
     *    @param:     param
     *    @return:    void
     */
    function _make_condition($conditions)
    {
        $where_and = array();
        if (isset($conditions['cate_id']) && $conditions['cate_id'])
        {
            $where_and[]= 'al.cate_id =' . intval($conditions['cate_id']);
        }
        if (isset($conditions['user_id']) && $conditions['user_id'])
        {
            $where_and[]= 'al.user_id = ' . intval($conditions['user_id']);
        }
        if (isset($conditions['keywords']) && $conditions['keywords'])
        {
            $where_and[]= 'al.subject LIKE \'%' . trim($conditions['keywords']) . '%\'';
        }
        if (isset($conditions['region_id']) && $conditions['region_id'])
        {
            $where_and[]= 'al.region_id ' . db_create_in($conditions['region_id']);
        }
        if (isset($conditions['valid']) && $conditions['valid'])
        {
            $now = gmtime();
            $where_and[] = 'al.expiry > ' . $now;
        }

        return empty($where_and) ? 1 : implode(' AND ', $where_and);
    }
}
?>
