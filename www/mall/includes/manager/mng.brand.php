<?php

/**
 * ECMALL: 品牌管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.brand.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class BrandManager extends Manager
{
    var $_store_id = 0;

    /**
     * 构造函数
     */
    function __construct($store_id)
    {
        $this->BrandManager($store_id);
    }

    function BrandManager($store_id)
    {
        $this->_store_id = $store_id;
    }

    /**
     * 取得品牌列表
     *
     * @param   int     $page       当前页
     * @param   array   $condition  查询条件
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'brand_id', $pagesize);
        $sql = "SELECT * FROM `ecm_brand` WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $list = array();
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $row['editable'] = in_array($this->_store_id, array(0, $row['store_id'])); // 是否可以编辑
            $list[] = $row;
        }

        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     * 添加品牌
     *
     * @param   array       $data       数据
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_brand`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 批量删除
     *
     * @param   string      $ids        品牌id（逗号隔开）
     * @return  int         返回删除的记录数
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_brand` " .
                "WHERE brand_id " . db_create_in($ids) . " " .
                "AND goods_count = 0 ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND store_id = '" . $this->_store_id . "'";
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
        $sql = "SELECT COUNT(*) FROM `ecm_brand` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 取得品牌id
     * 平台版的需要考虑 store_id
     *
     * @param   string  $brand_name 品牌名称
     * @return  int
     */
    function get_id($brand_name)
    {
        $sql = "SELECT brand_id FROM `ecm_brand` WHERE brand_name = '$brand_name'";

        return intval($GLOBALS['db']->getOne($sql));
    }

    /**
     * 取得品牌信息
     *
     * @param   string  $brand_name 品牌名称
     * @return  array   品牌信息，不存在返回空数组
     */
    function get_brand_info($brand_name)
    {
        $sql = "SELECT * FROM `ecm_brand` WHERE brand_name = '$brand_name'";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 创建查询条件语句
     *
     * @param   array   $condition      查询条件
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = parent::_make_condition($condition);
        if ($this->_store_id > 0)
        {
            $where .= " AND store_id IN ('0', '" . $this->_store_id . "') ";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND brand_name LIKE '%" . $condition['keywords'] . "%' ";
        }
        if (isset($condition['is_promote']))
        {
            $where .= " AND is_promote = 1 ";
        }

        return $where;
    }

    /**
    * 修改分类下商品数量
    *
    * @author scottye
    * @param
    *
    * @return void
    */
    function alter_goods_num($goods_num, $brand_id)
    {
        $goods_num = intval($goods_num);
        $brand_id = intval($brand_id);

        if ($goods_num > 0)
        {
            $sql = "UPDATE `ecm_brand` SET goods_count = goods_count + {$goods_num} WHERE brand_id = '$brand_id'";
        }
        else
        {
            $goods_num = 0 - $goods_num;
            $sql = "UPDATE `ecm_brand` SET goods_count = IF(goods_count > $goods_num, goods_count - $goods_num, 0) WHERE brand_id = '$brand_id'";
        }
        $GLOBALS['db']->query($sql);
    }

    /**
     * 更新品牌数量
     *
     * @author liupeng
     * @return void
     **/
    function update_goods_count()
    {
        $sql = "SELECT brand_id, count(*) AS goods_num FROM `ecm_goods` GROUP BY brand_id";

        $results = $GLOBALS['db']->query($sql);

        $data = array();
        while (($item = $GLOBALS['db']->fetchRow($results)))
        {
            $data[$item['brand_id']] = $item['goods_num'];
        }

        /* 获取店铺信息 */
        $sql = "SELECT brand_id, goods_count FROM `ecm_brand`";

        $brands = $GLOBALS['db']->getAll($sql);
        foreach($brands AS $item)
        {
            $bid = $item['brand_id'];
            $data[$bid] = isset($data[$bid]) ? intval($data[$bid]) : 0;
            if ($data[$bid] != $item['goods_count'])
            {
                $sql = "UPDATE `ecm_brand` SET goods_count='$data[$bid]' WHERE brand_id='$bid'";

                $GLOBALS['db']->query($sql);
            }
        }
    }

}

?>