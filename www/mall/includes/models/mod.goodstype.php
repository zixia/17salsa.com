<?php

/**
 * ECMALL: ��Ʒ����ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.goodstype.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class GoodsType extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id)
    {
        $this->GoodsType($id, $store_id);
    }

    function GoodsType($id, $store_id)
    {
        $this->_table = '`ecm_goods_type`';
        $this->_key   = 'type_id';
        parent::Model($id, $store_id);
    }

    /**
     * ȡ����Ϣ
     */
    function get_info()
    {
        $info = parent::get_info();
        if (!empty($info))
        {
            $sql = "SELECT COUNT(*) FROM `ecm_attribute` WHERE type_id = '" . $this->_id . "'";
            $info['attr_count'] = $GLOBALS['db']->getOne($sql);
        }

        return $info;
    }

    /**
     * ȡ������
     * @author  wj
     * @param void
     * @return array
     */
    function get_attr_list()
    {
        $sql = "SELECT * FROM `ecm_attribute` " .
                "WHERE type_id = '" . $this->_id . "'".
                " ORDER BY sort_order ASC";
        $res = $GLOBALS['db']->query($sql);
        $list = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if ($row['input_type'] == 'select' && $row['value_range'])
            {
                $range = explode("\n", trim($row['value_range']));
                $row['value_range'] = array();
                foreach ($range as $val)
                {
                    $row['value_range'][trim($val)] = trim($val);
                }
            }
            $list[] = $row;
        }

        return $list;
    }
}

?>