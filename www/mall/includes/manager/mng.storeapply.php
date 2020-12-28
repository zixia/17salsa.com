<?php

/**
 * ECMALL: �������������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.storeapply.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreApplyManager extends Manager
{
    /**
     * ���캯��
     */
    function __construct()
    {
        $this->StoreApplyManager();
    }

    function StoreApplyManager() { }

    /**
     * ȡ�������б�
     *
     * @author  weberliu
     * @param   int     $page       ��ǰҳ
     * @param   array   $condition  ��ѯ����
     * @param   int     $pagesize   ÿҳ��¼��
     * @return  array
     */
    function get_list($page, $condition = array(), $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'apply_id', $pagesize);
        $sql = "SELECT a.*, u.user_name FROM `ecm_store_apply` AS a ".
            "LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id ".
            "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $row = $GLOBALS['db']->getAll($sql);

        return array('data' => $row, 'info' => $arg['info']);
    }

    /**
     * ��ӿ��������¼
     *
     * @author  weberliu
     * @param   array       $data       ����
     * @return  int
     */
    function add($data)
    {
        $data['add_time'] = gmtime();
        $GLOBALS['db']->autoExecute('`ecm_store_apply`', $data, 'INSERT');

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ����ɾ��
     *
     * @author  weberliu
     * @param   string      $ids        Ʒ��id�����Ÿ�����
     * @return  int         ����ɾ���ļ�¼��
     */
    function batch_drop($ids)
    {
        $sql = "DELETE FROM `ecm_store_apply` WHERE apply_id " . db_create_in($ids);

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * ��ü�¼����
     *
     * @author  weberliu
     * @param   array   $condition  ��ѯ����
     * @return  int
     */
    function get_count($condition = array())
    {
        $sql = "SELECT COUNT(*) FROM `ecm_store_apply` AS a LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id WHERE " . $this->_make_condition($condition);

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ������ѯ�������
     *
     * @author  weberliu
     * @param   array   $condition      ��ѯ����
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = parent::_make_condition($condition);

        if (!empty($condition['user_id']))
        {
            $where .= " AND a.user_id=$condition[user_id] ";
        }
        if (isset($condition['status']))
        {
            $where .= " AND a.status = $condition[status] ";
        }

        if (isset($condition['keywords']))
        {
            $where .= " AND (a.owner_name LIKE '%$condition[keywords]%' OR u.user_name LIKE '%$condition[keywords]%')";
        }

        return $where;
    }
}

?>