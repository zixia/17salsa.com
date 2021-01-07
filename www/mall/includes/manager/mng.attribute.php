<?php

/**
 * ECMALL: ���Թ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
     * ���ô˺�����Ҫ�ж� goods_type �Ƿ�Ϊ�գ�Ϊ��˵�� type_id ������
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
     * ȡ���б�
     *
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
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
     * ���
     *
     * @param   array       $data       ����
     * @return  int
     */
    function add($data)
    {
        $data['type_id']  = $this->goods_type['type_id'];

        $GLOBALS['db']->autoExecute('`ecm_attribute`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ����ɾ��
     *
     * @param   string      $ids        ����id�����Ÿ�����
     * @return  int         ����ɾ���ļ�¼��
     */
    function batch_drop($ids)
    {
        $ids = explode(',', $ids);

        /* �ų���������Ʒ���͵����� */
        $sql = "SELECT attr_id " .
                "FROM `ecm_attribute` " .
                "WHERE type_id = '" . $this->goods_type['type_id'] . "'";
        $ids = array_intersect($ids, $GLOBALS['db']->getCol($sql));

        /* ɾ����Ʒ���� */
        $sql = "DELETE FROM `ecm_goods_attr` " .
                "WHERE attr_id " . db_create_in($ids);
        $GLOBALS['db']->query($sql);

        /* ɾ�� */
        $sql = "DELETE FROM `ecm_attribute` " .
                "WHERE attr_id " . db_create_in($ids);
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
        $sql = "SELECT COUNT(*) FROM `ecm_attribute` WHERE " . $this->_make_condition($condition);

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
        $sql = "SELECT attr_id FROM `ecm_attribute` " .
                "WHERE attr_name = '$name' " .
                "AND type_id = '" . $this->goods_type['type_id'] . "'";

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
        return "type_id = '" . $this->goods_type['type_id'] . "'";
    }
}

?>