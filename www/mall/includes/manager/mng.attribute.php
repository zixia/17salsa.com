<?php

/**
 * ECMALL: 属性管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.attribute.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class AttributeManager extends Manager
{
    var $_store_id   = 0;
    var $goods_type = array();


    /**
     * 构造函数
     * 调用此函数后要判断 goods_type 是否为空，为空说明 type_id 不存在
     */
    function __construct($store_id, $type_id)
    {
        $this->AttributeManager($store_id, $type_id);
    }

    function AttributeManager($store_id, $type_id)
    {
        $this->_store_id = intval($store_id);

        include_once(ROOT_PATH . '/includes/models/mod.goodstype.php');
        $mod = new GoodsType($type_id, $this->_store_id);
        $this->goods_type = $mod->get_info();
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
        $arg = $this->query_params($page, $condition, 'attr_id');
        $sql = "SELECT * " .
                "FROM `ecm_attribute` " .
                "WHERE $arg[where] " .
                "ORDER BY $arg[sort] $arg[order]";
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
        $data['type_id']  = $this->goods_type['type_id'];

        $GLOBALS['db']->autoExecute('`ecm_attribute`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @param   string      $ids        属性id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $ids = explode(',', $ids);

        /* 排除掉其他商品类型的属性 */
        $sql = "SELECT attr_id " .
                "FROM `ecm_attribute` " .
                "WHERE type_id = '" . $this->goods_type['type_id'] . "'";
        $ids = array_intersect($ids, $GLOBALS['db']->getCol($sql));

        /* 删除商品属性 */
        $sql = "DELETE FROM `ecm_goods_attr` " .
                "WHERE attr_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        /* 删除 */
        $sql = "DELETE FROM `ecm_attribute` " .
                "WHERE attr_id " . db_create_in($ids);
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
        $sql = "SELECT COUNT(*) FROM `ecm_attribute` WHERE " . $this->_make_condition($condition);

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
        $sql = "SELECT attr_id FROM `ecm_attribute` " .
                "WHERE attr_name = '$name' " .
                "AND type_id = '" . $this->goods_type['type_id'] . "'";

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
        return "type_id = '" . $this->goods_type['type_id'] . "'";
    }
}

?>