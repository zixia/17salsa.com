<?php

/**
 * ECMALL: 地区设置管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.region.php 6018 2008-10-31 08:30:07Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class RegionManager extends Manager
{
    var $_store_id = 0;

    /**
     * 构造函数
     */
    function __construct($store_id = 0)
    {
        $this->RegionManager($store_id);
    }

    function RegionManager($store_id = 0)
    {
        $this->_store_id = $store_id;
    }

    /**
     * 取得地区（一级地区或者某地区的下级地区）
     *
     * @param   int     $parent_id      上级地区id（0表示一级地区）
     * @return  array   地区数组
     */
    function get_list($parent_id = 0)
    {
        $sql = "SELECT * " .
                "FROM `ecm_regions` " .
                "WHERE store_id = '" . $this->_store_id . "' " .
                "AND parent_id = '$parent_id'";
        return $GLOBALS['db']->getAll($sql);
    }

    /**
     *  取得所有地区
     *
     *  @access public
     *  @param  none
     *  @return array
     */

    function get_all()
    {
        $sql = "SELECT region_id,region_name,parent_id FROM `ecm_regions`";
        $query = $GLOBALS['db']->query($sql);
        $rtn = array();
        while ($row = $GLOBALS['db']->fetch_array($query))
        {
            $rtn[] = $row;
        }

        return $rtn;
    }

    /**
     * 取得地区
     *
     * @param   int     $parent_id  上级id
     * @return  array   id => name 对
     */
    function get_options($parent_id = 0)
    {
        $list = array();
        $sql = "SELECT region_id, region_name " .
                "FROM `ecm_regions` " .
                "WHERE store_id = '" . $this->_store_id . "' " .
                "AND parent_id = '$parent_id'";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $list[$row['region_id']] = $row['region_name'];
        }

        return $list;
    }

    /**
     * 获得一个含有层级关系的地区数组
     * @author  Weber Liu
     * @return  array
     */
    function get_regions_list($parent_id = 0, $level=0)
    {
        static $regions = null;

        if ($regions === null)
        {
            $regions = array();
            $sql = "SELECT region_id,region_name,parent_id FROM `ecm_regions`";
            $res = $GLOBALS['db']->getAll($sql);
            foreach ($res AS $key=>$val)
            {
                $regions[$val['parent_id']][] = $val;
            }
        }

        $arr = array();
        foreach ($regions AS $key=>$val)
        {
            if ($key == $parent_id)
            {
                foreach ($val AS $k=>$v)
                {
                    $arr[] = array_merge($v, array('level'=>$level));
                    isset($regions[$v['region_id']]) && $arr = array_merge($arr, $this->get_regions_list($v['region_id'], $level+1));
                }
            }
        }
        return $arr;
    }

    /**
     * 添加地区
     *
     * @param   string  $region_name    地区名称
     * @param   int     $parent_id      上级地区（0表示一级地区）
     * @return  int
     */
    function add($region_name, $parent_id = 0)
    {
        $sql = "INSERT INTO `ecm_regions` (region_name, parent_id, store_id) " .
                "VALUES('$region_name', '$parent_id', '" . $this->_store_id . "')";
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->insert_id();
    }

    /**
     * 编辑地区
     *
     * @param   int     $region_id      地区id
     * @param   string  $region_name    地区名称
     * @return  bool
     */
    function update($region_id, $region_name)
    {
        $sql = "UPDATE `ecm_regions` " .
                "SET region_name = '$region_name' " .
                "WHERE region_id = '$region_id' " .
                "AND store_id = '" . $this->_store_id . "'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 删除地区
     *
     * @param   int     $region_id      地区id
     * @return  bool
     */
    function drop($region_id)
    {
        $sql = "DELETE FROM `ecm_regions` WHERE region_id = '$region_id' AND store_id = '" . $this->_store_id . "'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 取得某地区信息
     * @param   int     $region_id      地区id
     * @return  array
     */
    function get_region($region_id)
    {
        $sql = "SELECT * FROM `ecm_regions` " .
                "WHERE region_id = '" . intval($region_id) . "' " .
                "AND store_id = '" . $this->_store_id . "'";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 取得某地区的祖先地区（包括该地区）
     *
     * @param   int     $region_id      地区id
     * @return  array
     */
    function get_ancestors($region_id)
    {
        $result = array();
        while ($region_id > 0)
        {
            $region = $this->get_region($region_id);
            $result[] = $region;
            $region_id = $region['parent_id'];
        }

        return array_reverse($result);
    }

    /**
     * 取得某地区的祖先地区（包括该地区）的名字
     *
     * @param   int     $region_id      地区id
     * @return  array
     */
    function get_ancestors_name($region_id)
    {
        $ancestors = $this->get_ancestors($region_id);
        $result = '';
        foreach ($ancestors as $region)
        {
            $result .= $region['region_name'];
        }

        return $result;
    }

    /**
     * 检查地区名称是否存在
     *
     * @param   string  $region_name    地区名称
     * @param   int     $parent_id      上级地区id
     * @param   int     $region_id      地区id（编辑时要排除改id对应的地区）
     * @return  bool
     */
    function region_name_exist($region_name, $parent_id = 0, $region_id = 0)
    {
        $sql = "SELECT COUNT(*) FROM `ecm_regions` " .
                "WHERE parent_id = '$parent_id' " .
                "AND region_name = '$region_name' ";
        if ($region_id > 0)
        {
            $sql .= "AND region_id <> '$region_id'";
        }

        return $GLOBALS['db']->getOne($sql) > 0;
    }

    /**
     *  获取指定ID的地区信息
     *
     *  @param  string $ids     由','号连接的多个ID形成的字符串
     *  @return array
     */
    function get_regions($ids)
    {
        if (!$ids)
        {
            return;
        }
        $sql = 'SELECT * FROM `ecm_regions` WHERE region_id IN(' . $ids . ')';

        return $GLOBALS['db']->getAll($sql);
    }
}

?>