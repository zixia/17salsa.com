<?php

/**
 * ECMALL: 出租方案管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.rentscheme.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class RentSchemeManager extends Manager
{
    /**
     * 构造函数
     */
    function __construct()
    {
        $this->RentSchemeManager();
    }

    function RentSchemeManager()
    {
        parent::__construct();
    }

    /**
     * 取得列表
     *
     * @param   int     $page       当前页
     * @param   array   $condition  查询条件
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'scheme_id', $pagesize);
        $sql = "SELECT * FROM `ecm_rent_scheme` WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $list = array();
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $list[] = $row;
        }

        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     * 添加
     *
     * @param   array       $data       数据
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_rent_scheme`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @param   string      $ids        id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_rent_scheme` " .
                "WHERE scheme_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * 获得记录总数
     *
     * @param   array   $condition  查询条件
     * @return  int
     */
    function get_count($condition = array())
    {
        $sql = "SELECT COUNT(*) FROM `ecm_rent_scheme` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 创建查询条件语句
     *
     * @param   array   $condition      查询条件
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = '1';
        if (!empty($condition['min_goods']))
        {
            $where .= " AND (allowed_goods = 0 OR allowed_goods >= " . intval($condition['min_goods']) . ") ";
        }
        if (!empty($condition['min_file']))
        {
            $where .= " AND (allowed_file = 0 OR allowed_file >= " . intval($condition['min_file']) . ") ";
        }

        return $where;
    }
}

?>