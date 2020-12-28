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
 * $Id: mng.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class MessageManager extends Manager
{
    var $_store_id = 0;

    /**
     * ���캯��
     */
    function __construct($store_id = 0)
    {
        $this->MessageManager($store_id);
    }

    function MessageManager($store_id = 0)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * ȡ�������б�
     *
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
     * @param   int   $pagesize ÿҳ����
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'message_id', $pagesize);
        $sql = "SELECT m.*, g.goods_name " .
                "FROM `ecm_message` AS m " .
                " LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $list = array();
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $row['formated_add_time'] = local_date('Y-m-d H:i', $row['add_time']);
            $list[] = $row;
        }

        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     * �������
     *
     * @param   array       $data       ����
     * @return  int
     */
    function add($data)
    {
        $GLOBALS['db']->autoExecute('`ecm_message`', $data, 'INSERT');

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
        $sql = "DELETE FROM `ecm_message` " .
                "WHERE message_id " . db_create_in($ids);
        if ($this->_store_id != 0)
        {
             $sql .= " AND seller_id = '" . $this->_store_id . "'";
        }
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * ��������
     */
    function batch_update($ids, $field, $value)
    {
        $sql = "UPDATE `ecm_message` " .
                "SET $field = '$value' " .
                "WHERE message_id " . db_create_in($ids);
        if ($this->_store_id != 0)
        {
             $sql .= " AND seller_id = '" . $this->_store_id . "'";
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
        $sql = "SELECT COUNT(*) FROM `ecm_message` AS m " .
                "LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                "WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ������ѯ�������
     *
     * @param   array   $condition      ��ѯ������not_reply = 1��ʾδ�ظ���show��ʾ������ǰ̨��ʾ��
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = " (m.goods_id = 0 OR g.goods_id IS NOT NULL) ";
        if ($this->_store_id != 0)
        {
            $where .= " AND m.seller_id = '" . $this->_store_id . "' ";
        }
        elseif (!empty($condition['seller_id']))
        {
            $where .= " AND m.seller_id = '" . $condition['seller_id'] . "' ";
        }
        if (isset($condition['goods_id']))
        {
            $where .= " AND m.goods_id = '" . $condition['goods_id'] . "' ";
        }
        if (!empty($condition['buyer_id']))
        {
            $where .= " AND m.buyer_id = '" . $condition['buyer_id'] . "' ";
        }
        if (!empty($condition['not_reply']))
        {
            $where .= " AND m.reply = '' ";
        }
        if (!empty($condition['if_show']))
        {
            $where .=" AND m.if_show = 1 ";
        }
        if (!empty($condition['keywords']))
        {
            $where .= " AND m.message LIKE '%" . $condition['keywords'] . "%' ";
        }

        return $where;
    }
}

?>