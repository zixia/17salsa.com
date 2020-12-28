<?php

/**
 * ECMALL: Ʒ�ƹ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * ȡ��Ʒ���б�
     *
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
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
            $row['editable'] = in_array($this->_store_id, array(0, $row['store_id'])); // �Ƿ���Ա༭
            $list[] = $row;
        }

        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     * ���Ʒ��
     *
     * @param   array       $data       ����
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_brand`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ����ɾ��
     *
     * @param   string      $ids        Ʒ��id�����Ÿ�����
     * @return  int         ����ɾ���ļ�¼��
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
     * ��ü�¼����
     *
     * @param   array   $condition  ��ѯ����
     * @return  int
     */
    function get_count($condition = array())
    {
        $sql = "SELECT COUNT(*) FROM `ecm_brand` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ȡ��Ʒ��id
     * ƽ̨�����Ҫ���� store_id
     *
     * @param   string  $brand_name Ʒ������
     * @return  int
     */
    function get_id($brand_name)
    {
        $sql = "SELECT brand_id FROM `ecm_brand` WHERE brand_name = '$brand_name'";

        return intval($GLOBALS['db']->getOne($sql));
    }

    /**
     * ȡ��Ʒ����Ϣ
     *
     * @param   string  $brand_name Ʒ������
     * @return  array   Ʒ����Ϣ�������ڷ��ؿ�����
     */
    function get_brand_info($brand_name)
    {
        $sql = "SELECT * FROM `ecm_brand` WHERE brand_name = '$brand_name'";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * ������ѯ�������
     *
     * @param   array   $condition      ��ѯ����
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
    * �޸ķ�������Ʒ����
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
     * ����Ʒ������
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

        /* ��ȡ������Ϣ */
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