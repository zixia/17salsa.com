<?php

/**
 * ECMALL: 商品类型管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.goodstype.php 6061 2008-11-13 09:56:28Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class GoodsTypeManager extends Manager
{
    var $_store_id = 0;

    /**
     * 构造函数
     */
    function __construct($store_id)
    {
        $this->GoodsTypeManager($store_id);
    }

    function GoodsTypeManager($store_id)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * 取得列表
     *
     * @param   int     $page       当前页
     * @param   array   $condition  查询条件
     * @return  array
     */
    function get_list($page, $condition = array())
    {
        $arg = $this->query_params($page, $condition, 'type_id');
        $sql = "SELECT gt.*, COUNT(a.attr_id) AS attr_count " .
                "FROM `ecm_goods_type` AS gt " .
                    "LEFT JOIN `ecm_attribute` AS a ON gt.type_id = a.type_id " .
                "WHERE $arg[where] " .
                "GROUP BY gt.type_id " .
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
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
        $GLOBALS['db']->autoExecute('`ecm_goods_type`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @param   string      $ids        商品类型id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $ids = explode(',', $ids);

        /* 排除掉有属性的商品类型 */
        $sql = "SELECT DISTINCT type_id " .
                "FROM `ecm_attribute` " .
                "WHERE type_id " . db_create_in($ids);
        $ids = array_diff($ids, $GLOBALS['db']->getCol($sql));

        /* 更新商品和分类 */
        $sql = "UPDATE `ecm_goods` " .
                "SET type_id = 0 " .
                "WHERE type_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        $sql = "UPDATE `ecm_category` " .
                "SET type_id = 0 " .
                "WHERE type_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        /* 删除 */
        $sql = "DELETE FROM `ecm_goods_type` " .
                "WHERE type_id " . db_create_in($ids) . " " .
                "AND store_id = '" . $this->_store_id . "'";
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
        $sql = "SELECT COUNT(*) FROM `ecm_goods_type` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 取得id
     *
     * @param   string      $name   名称
     * @return  int         返回id，没找到返回0
     */
    function get_id($name)
    {
        $sql = "SELECT type_id FROM `ecm_goods_type` " .
                "WHERE type_name = '$name' " .
                "AND store_id = '" . $this->_store_id . "' ";
        $id = $GLOBALS['db']->getOne($sql);

        return is_null($id) ? 0 : $id;
    }

    /**
     * 创建查询条件语句
     *
     * @param   array   $condition      查询条件
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = "1 ";
        if (!empty($condition['keywords']))
        {
            $where .= "AND type_name LIKE '%" . $condition['keywords'] . "%' ";
        }

        return $where;
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_options ()
    {
        $sql = "SELECT type_name, type_id FROM `ecm_goods_type` WHERE store_id='{$this->_store_id}'";
        $res = $GLOBALS['db']->query($sql);
        $data = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $data[$row['type_id']] = $row['type_name'];
        }
        return  $data;
    }
}

?>