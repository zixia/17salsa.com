<?php

/**
 * ECMALL: ���̹�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.nav.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class NavManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id = 0)
    {
        $this->NavManager($store_id);
    }

    function NavManager($store_id = 0)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * ��õ����б�
     *
     * @param   bool    $if_show    �Ƿ�ֻ��ʾ���õ�
     * @return  array
     */
    function get_list($if_show = false)
    {
        $sql = "SELECT * FROM `ecm_nav` ";
        if ($if_show)
        {
            $sql .= " WHERE if_show = 1 ";
        }
        $sql .= " ORDER BY sort_order";
        $res = $GLOBALS['db']->getAll($sql);
        $tmp = array();
        foreach ($res as $val)
        {
            $tmp[$val['nav_position']][] = $val;
        }
        return $tmp;
    }

    /**
     * ��õ�ǰ���ݿ��е�app
     *
     * @return array apps
     */
    function get_app_list()
    {
        $sql = "SELECT * FROM `ecm_nav` WHERE is_app>0";
        $query = $GLOBALS['db']->query($sql);
        $res_list = array();
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            $res_list[$res['is_app']] = $res;
        }
        return $res_list;
    }

    /**
     * ����һ������
     *
     * @param  array    $post
     *
     * @return  boolean
     */
    function add($nav)
    {
        if (empty($nav['nav_name']))
        {
            $this->err = 'nav_name_empty';
            return false;
        }
        if ($this->get_by_name($nav['nav_name']))
        {
            $this->err = 'nav_name_duplication';
            return false;
        }
        if ($GLOBALS['db']->autoExecute('`ecm_nav`', $nav))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function get_by_name($name)
    {
        $sql = "SELECT nav_id FROM `ecm_nav` WHERE nav_name='$name'";
        return $GLOBALS['db']->getOne($sql);
    }
};
?>