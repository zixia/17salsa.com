<?php

/**
 * ECMALL: 开店申请管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.storeapply.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreApplyManager extends Manager
{
    /**
     * 构造函数
     */
    function __construct()
    {
        $this->StoreApplyManager();
    }

    function StoreApplyManager() { }

    /**
     * 取得申请列表
     *
     * @author  weberliu
     * @param   int     $page       当前页
     * @param   array   $condition  查询条件
     * @param   int     $pagesize   每页记录数
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'apply_id', $pagesize);
        $sql = "SELECT a.*, u.user_name FROM `ecm_store_apply` AS a ".
            "LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id ".
            "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $row = $GLOBALS['db']->getAll($sql);

        return array('data' => $row, 'info' => $arg['info']);
    }

    /**
     * 添加开店申请记录
     *
     * @author  weberliu
     * @param   array       $data       数据
     * @return  int
     */
    function add($data)
    {
        $data['add_time'] = gmtime();
        $GLOBALS['db']->autoExecute('`ecm_store_apply`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @author  weberliu
     * @param   string      $ids        品牌id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_store_apply` WHERE apply_id " . db_create_in($ids);

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * 获得记录总数
     *
     * @author  weberliu
     * @param   array   $condition  查询条件
     * @return  int
     */
    function get_count($condition = array())
    {
        $sql = "SELECT COUNT(*) FROM `ecm_store_apply` AS a LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 创建查询条件语句
     *
     * @author  weberliu
     * @param   array   $condition      查询条件
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = parent::_make_condition($condition);

        if (!empty($condition['user_id']))
        {
            $where .= " AND a.user_id=$condition[user_id] ";
        }
        if (isset($condition['status']))
        {
            $where .= " AND a.status = $condition[status] ";
        }

        if (isset($condition['keywords']))
        {
            $where .= " AND (a.owner_name LIKE '%$condition[keywords]%' OR u.user_name LIKE '%$condition[keywords]%')";
        }

        return $where;
    }
}

?>