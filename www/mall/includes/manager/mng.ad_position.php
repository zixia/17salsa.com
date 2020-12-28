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
 * $Id: mng.ad_position.php 6009 2008-10-31 01:55:52Z Garbin $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class AdPositionManager extends Manager
{
    /**
     *  ���캯��
     *  @param int $store_id
     *  @return none
     */

    function __construct()
    {
        $this->AdPositionManager();
    }

    function AdPositionManager()
    {
        parent::__construct();
    }

    /**
     *  ��ȡ���λ�б�
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'adp.position_id', $pagesize);

        $sql = "SELECT count(ad.ad_id) AS num, adp.* ".
                "FROM `ecm_ad_position` AS adp ".
                "LEFT JOIN `ecm_ad` AS ad ON adp.position_id=ad.position_id ".
                "WHERE $arg[where] GROUP BY adp.position_id ".
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";

        $res = $GLOBALS['db']->getAll($sql);
        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     *  ��ȡ���λ����
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_ad_position` WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  ��ӹ��λ
     *  @param array $info
     *  @return int
     */
    function add($info)
    {
       return $GLOBALS['db']->autoExecute("`ecm_ad_position`", $info, "INSERT");
    }

    /**
     *  ��ȡ���λ�����б���
     *  @param string $ids
     *  @return void
     */
    function get_options()
    {
        $sql = "SELECT position_name, position_id FROM `ecm_ad_position`";
        $res = $GLOBALS['db']->query($sql);

        $options = array();

        while ($row = mysql_fetch_assoc($res))
        {
            $options[$row['position_id']] = $row['position_name'];
        }
        return $options;
    }
}

?>