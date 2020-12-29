<?php
/**
 * ECMALL: ���ݵ��ù�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.ad.php 3849 2008-05-27 05:19:55Z Weberliu $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class DataCallManager extends Manager
{
    var $position_id = 0;

    /**
     *  ���캯��
     *  @param int $position_id, $store_id
     *  @return void
     */
    function __construct($store_id = 0)
    {
        $this->DataCallManager($store_id);
    }

    function DataCallManager($store_id = 0)
    {
        $this->_store_id = $store_id;
        parent::__construct($store_id);
    }

    /**
     *  ��ȡ���ݵ����б�
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, 'dc.id');

        $sql = "SELECT *, c.cate_name ".
                "FROM `ecm_data_call` AS dc " .
                "LEFT JOIN `ecm_category` AS c ON c.cate_id = dc.cate_id " .
                "WHERE $arg[where] " .
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";

        $res = $GLOBALS['db']->getAll($sql);
        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     *  ��ȡ�������
     *  @param int $condition
     *  @return int
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_data_call` AS dc WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  ������ݵ���
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_data_call`", $info);
    }

    /**
     *  ��������ʽ��$conditionsת����SQL��WHERE�������
     *  @param mixed $conditions
     *  @return string
     */
    function _make_condition($condition)
    {
        $where = '1';
        if ($this->_store_id > 0)
        {
            $where .= " AND dc.store_id = " . $this->_store_id;
        }

        return $where;
    }
}

?>