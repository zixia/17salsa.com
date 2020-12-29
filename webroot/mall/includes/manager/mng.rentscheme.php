<?php

/**
 * ECMALL: ���ⷽ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.rentscheme.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class RentSchemeManager extends Manager
{
    /**
     * ���캯��
     */
    function __construct()
    {
        $this->RentSchemeManager();
    }

    function RentSchemeManager()
    {
        parent::__construct();
    }

    /**
     * ȡ���б�
     *
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'scheme_id', $pagesize);
        $sql = "SELECT * FROM `ecm_rent_scheme` WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
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
        $GLOBALS['db']->autoExecute('`ecm_rent_scheme`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ����ɾ��
     *
     * @param   string      $ids        id�����Ÿ�����
     * @return  int         ����ɾ���ļ�¼��
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_rent_scheme` " .
                "WHERE scheme_id " . db_create_in($ids);
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
        $sql = "SELECT COUNT(*) FROM `ecm_rent_scheme` WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ������ѯ�������
     *
     * @param   array   $condition      ��ѯ����
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = '1';
        if (!empty($condition['min_goods']))
        {
            $where .= " AND (allowed_goods = 0 OR allowed_goods >= " . intval($condition['min_goods']) . ") ";
        }
        if (!empty($condition['min_file']))
        {
            $where .= " AND (allowed_file = 0 OR allowed_file >= " . intval($condition['min_file']) . ") ";
        }

        return $where;
    }
}

?>