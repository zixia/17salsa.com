<?php

/**
 * ECMALL: �������ù�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * ȡ�õ�����һ����������ĳ�������¼�������
     *
     * @param   int     $parent_id      �ϼ�����id��0��ʾһ��������
     * @return  array   ��������
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
     *  ȡ�����е���
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
     * ȡ�õ���
     *
     * @param   int     $parent_id  �ϼ�id
     * @return  array   id => name ��
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
     * ���һ�����в㼶��ϵ�ĵ�������
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
     * ��ӵ���
     *
     * @param   string  $region_name    ��������
     * @param   int     $parent_id      �ϼ�������0��ʾһ��������
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
     * �༭����
     *
     * @param   int     $region_id      ����id
     * @param   string  $region_name    ��������
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
     * ɾ������
     *
     * @param   int     $region_id      ����id
     * @return  bool
     */
    function drop($region_id)
    {
        $sql = "DELETE FROM `ecm_regions` WHERE region_id = '$region_id' AND store_id = '" . $this->_store_id . "'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * ȡ��ĳ������Ϣ
     * @param   int     $region_id      ����id
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
     * ȡ��ĳ���������ȵ����������õ�����
     *
     * @param   int     $region_id      ����id
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
     * ȡ��ĳ���������ȵ����������õ�����������
     *
     * @param   int     $region_id      ����id
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
     * �����������Ƿ����
     *
     * @param   string  $region_name    ��������
     * @param   int     $parent_id      �ϼ�����id
     * @param   int     $region_id      ����id���༭ʱҪ�ų���id��Ӧ�ĵ�����
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
     *  ��ȡָ��ID�ĵ�����Ϣ
     *
     *  @param  string $ids     ��','�����ӵĶ��ID�γɵ��ַ���
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