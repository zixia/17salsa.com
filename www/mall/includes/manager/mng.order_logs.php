<?php

/**
 * ECMALL: ����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.order_logs.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

/* ������־��¼������ */
include_once(ROOT_PATH . '/includes/manager/mng.logger.php');

class OrderLogger extends Logger
{
    var $_table = '`ecm_order_action`';
    var $_fields= array('order_id',
                        'action_user',
                        'order_status',
                        'action_note',
                        'action_time');
    var $_time_field_name = 'action_time';
    var $_primary_key = 'action_id';

    /**
     *  ��ȡ����
     *
     *  @access
     *  @params
     *  @return
     */

    function get_count($conditions)
    {

        $count = $GLOBALS['db']->getRow("SELECT count(*) AS rec_count FROM `ecm_order_action` oa LEFT JOIN `ecm_order_info` oi ON oa.order_id = oi.order_id WHERE 1 " . $this->_make_condition($conditions));

        return $count['rec_count'];
    }

    /**
     *  ��������ʽ��$conditionsת����SQL��WHERE�������
     *  @param mixed $conditions
     *  @return string
     */

    function _make_condition($conditions)
    {
        if ($this->_store_id > 0)
        {
            $where = 'oi.store_id=' . $this->_store_id;
        }
        else
        {
            $where = '';
        }
        if(is_numeric($conditions))
        {
            $where .= 'oa.action_id = ' . $conditions;
        }
        elseif (is_string($conditions))
        {
            $where .= $conditions;
        }
        elseif(is_array($conditions))
        {
            $where_array = array();
            foreach ($conditions as $_k => $_v)
            {
                switch ($_k)
                {
                case 'order_sn':
                    $where_array[] = 'oi.order_sn = \'' . $_v . '\'';
                    break;
                case 'action_time':
                    $where_array[] = 'action_time < ' . $_v;
                    break;
                case 'remark':
                    $where_array[] = 'action_note LIKE \'%' . $_v . '%\'';
                    break;
                }
            }
            if ($where)
            {
                $where .= ' AND ' . implode(' AND ', $where_array);
            }
            else
            {
                $where .= implode(' AND ', $where_array);
            }
        }
        if ($where)
        {
            return ' AND ' . $where;
        }
    }

    /**
     *  �б������־
     *
     *  @access public
     *  @params int $page
     *  @params mixed $conditions
     *  @params string $orderby
     *  @return array
     */

    function get_list($page = 1, $conditions = NULL, $orderby = 'action_time')
    {
        $pars = $this->query_params($page, $conditions, $orderby);
        $sql = "SELECT oa.*,oi.order_sn FROM `ecm_order_action` oa LEFT JOIN `ecm_order_info` oi ON oa.order_id=oi.order_id WHERE 1 {$pars['where']} ORDER BY {$pars['sort']} {$pars['order']} LIMIT {$pars['start']},{$pars['number']}";

        return array('data'=>$GLOBALS['db']->getAll($sql),
                     'info'=>$this->_page_info($page, $pars['count']));
    }
}

?>
