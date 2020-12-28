<?php

/**
 * ECMALL: ����ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class Messages extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id)
    {
        $this->Messages($id, $store_id);
    }

    function Messages($id, $store_id)
    {
        $this->_table = '`ecm_message`';
        $this->_key   = 'message_id';
        parent::Model($id, $store_id);
    }

    function get_info()
    {
        $sql = "SELECT m.*, g.goods_name FROM " . $this->_table . " AS m " .
                " LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                " WHERE m.message_id = '" . $this->_id . "' ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND m.seller_id = '" . $this->_store_id . "'";
        }
        $row = $GLOBALS['db']->getRow($sql);
        if ($row)
        {
            $row['formated_add_time'] = local_date('Y-m-d H:i', $row['add_time']);
        }

        return $row;
    }

    function update($arr)
    {
        $where = $this->_key . " = '" . $this->_id . "'";

        if ($this->_store_id > 0)
        {
            $where .= "AND seller_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->autoExecute($this->_table, $arr, 'UPDATE', $where);
    }

    function drop()
    {
        $sql = "DELETE FROM " .$this->_table. " WHERE " .$this->_key. " = '" .$this->_id. "'";

        if ($this->_store_id > 0)
        {
            $sql .= "AND seller_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->query($sql);
    }
}

?>