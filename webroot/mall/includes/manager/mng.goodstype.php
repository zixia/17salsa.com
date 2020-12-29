<?php

/**
 * ECMALL: ��Ʒ���͹�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * ȡ���б�
     *
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
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
     * ���
     *
     * @param   array       $data       ����
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_goods_type`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ����ɾ��
     *
     * @param   string      $ids        ��Ʒ����id�����Ÿ�����
     * @return  int         ����ɾ���ļ�¼��
     */
    function batch_drop($ids)
    {
        $ids = explode(',', $ids);

        /* �ų��������Ե���Ʒ���� */
        $sql = "SELECT DISTINCT type_id " .
                "FROM `ecm_attribute` " .
                "WHERE type_id " . db_create_in($ids);
        $ids = array_diff($ids, $GLOBALS['db']->getCol($sql));

        /* ������Ʒ�ͷ��� */
        $sql = "UPDATE `ecm_goods` " .
                "SET type_id = 0 " .
                "WHERE type_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        $sql = "UPDATE `ecm_category` " .
                "SET type_id = 0 " .
                "WHERE type_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        /* ɾ�� */
        $sql = "DELETE FROM `ecm_goods_type` " .
                "WHERE type_id " . db_create_in($ids) . " " .
                "AND store_id = '" . $this->_store_id . "'";
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
        $sql = "SELECT COUNT(*) FROM `ecm_goods_type` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ȡ��id
     *
     * @param   string      $name   ����
     * @return  int         ����id��û�ҵ�����0
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
     * ������ѯ�������
     *
     * @param   array   $condition      ��ѯ����
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