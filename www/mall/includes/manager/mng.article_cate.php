<?php

/**
 * ECMALL: ���·��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.article_cate.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ArticleCateManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->ArticleCateManager($store_id);
    }

    function ArticleCateManager($store_id)
    {
        $this->_store_id = $store_id;
    }

    /**
     * ��ȡ���·����б�
     *
     * @param   int  $page
     * @param   array  $condition
     *
     * @return  array
     */
    function get_list($page = 0, $condition = null)
    {
        $start  = ($page - 1) * $number;
        $where  = $this->_make_condition($condition);

        $sql .= "SELECT * FROM `ecm_article_cate` WHERE $where LIMIT $start, $number";
        $res = $GLOBALS['db']->getAll($sql);
        return $res;
    }

    /**
     * �����ѯ����
     *
     * @param   array  $condition
     *
     * @return  array
     */
    function _make_condition($condition)
    {
        $where = '1';

        if ($this->_store_id > 0)
        {
            $where .= " AND store_id = " . $this->_store_id;
        }
        return $where;
    }

    /**
     * ��ȡ���·����б�
     *
     * @param   string  $app
     * @param   string  $act
     * @param   int     $item_id
     *
     * @return  array
     */
    function get_options($selected_value = 0)
    {
        $where = $this->_make_condition(null);

        $sql = "SELECT cate_id, cate_name FROM `ecm_article_cate` WHERE $where";
        $res = $GLOBALS['db']->getAll($sql);

        $arr = array();

        foreach($res AS $key => $val)
        {
            $arr[$val['cate_id']] = $val['cate_name'];
        }
        return $arr;
    }
};
?>