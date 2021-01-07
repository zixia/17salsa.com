<?php
/**
 * ECMALL: ���λ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.ad.php 6009 2008-10-31 01:55:52Z Garbin $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class AdManager extends Manager
{
    var $position_id = 0;

    /**
     *  ���캯��
     *  @param int $position_id, $store_id
     *  @return void
     */
    function __construct($store_id = 0)
    {
        $this->AdManager($store_id);
    }

    function AdManager($store_id = 0)
    {
        parent::__construct($store_id);
    }

    /**
     *  ��ȡ����б�
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, 'ad.ad_id');

        $sql = "SELECT ad.*, adp.position_name ".
                "FROM `ecm_ad` AS ad ".
                "LEFT JOIN `ecm_ad_position` AS adp ON adp.position_id=ad.position_id ".
                "WHERE $arg[where] ".
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
        $sql        = "SELECT COUNT(*) FROM `ecm_ad` WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  ��ӹ��
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_ad`", $info);
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
            $where .= " AND ad.store_id = " . $this->_store_id;
        }

        if (!empty($condition['is_top']))
        {
            $where .= " AND is_top=1";
        }

        if (!empty($condition['cate_id']))
        {
            $where .= " AND art.cate_id='$condition[cate_id]'";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND title LIKE '%$condition[keywords]%'";
        }

        return $where;
    }
}

?>