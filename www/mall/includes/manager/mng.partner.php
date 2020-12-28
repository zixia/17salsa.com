<?php

/**
 * ECMALL: �������ӹ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.partner.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class PartnerManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->PartnerManager($store_id);
    }

    function PartnerManager($store_id)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * ������������б�
     *
     * @return  array
     */
    function get_list($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'sort_order', $pagesize);

        $sql = "SELECT * FROM `ecm_partner` WHERE " .$arg['where'].
               " ORDER BY sort_order LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);

        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     * ��÷��������ļ�¼����
     *
     * @param   array   $condition
     *
     * @return  int
     */
    function get_count($condition)
    {
        $where  = $this->_make_condition($condition);
        $sql    = "SELECT COUNT(*) FROM `ecm_partner` WHERE $where";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }
    /**
     * ����һ����������
     *
     * @param  array    $post
     *
     * @return  int
     */
    function add($post)
    {
        $post['store_id']   = $this->_store_id;

        $res = $GLOBALS['db']->autoExecute('`ecm_partner`', $post);

        return $res;
    }
    /**
     * ���ݸ����������������ƻ���������ӵ�ID
     *
     * @author  weberliu
     * @param   string      $name       ������������
     * @param   string      $not_ids    ��Ҫ�ų����������ӵ�ID��ʹ�ö��ŷָ�
     * @return  int
     */
    function get_partner_id($name, $not_ids = '0')
    {
        $sql = "SELECT partner_id FROM `ecm_partner` WHERE partner_name='$name' AND partner_id NOT " .db_create_in($not_ids). " AND store_id=" . $this->_store_id;
        $res = $GLOBALS['db']->getOne($sql);

        return ($res) ? $res : 0;
    }

    /**
     * ������ѯ����
     *
     * @param   array   $condition
     *
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = " store_id = '" . $this->_store_id . "'";

        if (!empty($condition['is_recommend']))
        {
            $where .= " AND is_recommend=1";
        }

        if (!empty($condition['is_certified']))
        {
            $where .= " AND is_certified=1";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND store_name LIKE '%$condition[keywords]%'";
        }

        return $where;
    }

    /**
     * ɾ����������
     *
     * @author  weberliu
     * @params  string      $ids    ��Ҫɾ�����������ӵ�ID��ʹ�ö��ŷָ�
     * @return  bool
     */
    function drop($ids)
    {
        if (empty($ids))
        {
            $this->err = 'undefined';
            return false;
        }
        $tmp = array();
        $arr_id = explode(',', $ids);
        foreach ($arr_id as $key=>$val)
        {
            if ($val = intval(trim($val)))
            {
                $tmp[] = $val;
            }
        }
        $ids = implode(',', $tmp);
        if (empty($ids))
        {
            $this->err = 'data_illegal';
            return false;
        }
        $sql = "DELETE FROM `ecm_partner` WHERE partner_id IN ($ids) AND store_id='" . $this->_store_id . "'";
        $GLOBALS['db']->query($sql);
        return true;
    }

};
?>