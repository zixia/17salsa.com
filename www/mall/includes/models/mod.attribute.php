<?php

/**
 * ECMALL: ����ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.attribute.php 6060 2008-11-13 09:38:39Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Attribute extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id = 0)
    {
        $this->Attribute($id, $store_id);
    }

    function Attribute($id, $store_id = 0)
    {
        $this->_table = '`ecm_attribute`';
        $this->_key   = 'attr_id';
        parent::Model($id, $store_id);
    }

    /**
     * ȡ����Ϣ
     */
    function get_info()
    {
        $sql = "SELECT a.*, gt.type_name " .
                "FROM `ecm_attribute` AS a, `ecm_goods_type` AS gt " .
                "WHERE a.type_id = gt.type_id " .
                "AND a.attr_id = '" . $this->_id . "' ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND gt.store_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->getRow($sql);
    }
}

?>