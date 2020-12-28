<?php

/**
 * ECMALL: 留言管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class MessageManager extends Manager
{
    var $_store_id = 0;

    /**
     * 构造函数
     */
    function __construct($store_id = 0)
    {
        $this->MessageManager($store_id);
    }

    function MessageManager($store_id = 0)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * 取得留言列表
     *
     * @param   int     $page       当前页
     * @param   array   $condition  查询条件
     * @param   int   $pagesize 每页条数
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'message_id', $pagesize);
        $sql = "SELECT m.*, g.goods_name " .
                "FROM `ecm_message` AS m " .
                " LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $list = array();
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $row['formated_add_time'] = local_date('Y-m-d H:i', $row['add_time']);
            $list[] = $row;
        }

        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     * 添加留言
     *
     * @param   array       $data       数据
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_message`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @param   string      $ids        留言id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_message` " .
                "WHERE message_id " . db_create_in($ids);
        if ($this->_store_id != 0)
        {
             $sql .= " AND seller_id = '" . $this->_store_id . "'";
        }
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * 批量更新
     */
    function batch_update($ids, $field, $value)
    {
        $sql = "UPDATE `ecm_message` " .
                "SET $field = '$value' " .
                "WHERE message_id " . db_create_in($ids);
        if ($this->_store_id != 0)
        {
             $sql .= " AND seller_id = '" . $this->_store_id . "'";
        }
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
        $sql = "SELECT COUNT(*) FROM `ecm_message` AS m " .
                "LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                "WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 创建查询条件语句
     *
     * @param   array   $condition      查询条件（not_reply = 1表示未回复，show表示允许在前台显示）
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = " (m.goods_id = 0 OR g.goods_id IS NOT NULL) ";
        if ($this->_store_id != 0)
        {
            $where .= " AND m.seller_id = '" . $this->_store_id . "' ";
        }
        elseif (!empty($condition['seller_id']))
        {
            $where .= " AND m.seller_id = '" . $condition['seller_id'] . "' ";
        }
        if (isset($condition['goods_id']))
        {
            $where .= " AND m.goods_id = '" . $condition['goods_id'] . "' ";
        }
        if (!empty($condition['buyer_id']))
        {
            $where .= " AND m.buyer_id = '" . $condition['buyer_id'] . "' ";
        }
        if (!empty($condition['not_reply']))
        {
            $where .= " AND m.reply = '' ";
        }
        if (!empty($condition['if_show']))
        {
            $where .=" AND m.if_show = 1 ";
        }
        if (!empty($condition['keywords']))
        {
            $where .= " AND m.message LIKE '%" . $condition['keywords'] . "%' ";
        }

        return $where;
    }
}

?>