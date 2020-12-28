<?php

/**
 * ECMALL: �û��ʺŹ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.user.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class UserManager extends Manager
{
    var $store_id = 0;

    function __construct($store_id=0)
    {
        $this->UserManager($store_id);
    }

    function UserManager($store_id=0)
    {
        $this->store_id = intval($store_id);
    }
    /**
     * ����û��б�
     *
     * @param int       $page
     * @param array     $condition
     *                      shopping_amount : �����ܶ�
     *                      name_array      : �û�������
     *
     * @return  void
     */
    function get_list($page, $condition)
    {
        $arg = $this->query_params($page, $condition, 'user_id');
        $having = '';

        if (isset($condition['shopping_amount']))
        {
            $having .= " HAVING shopping_amount > '$arr[shopping_amount]'";
        }

        $cond_store = ($this->store_id > 0) ? ' AND o.store_id=$this->store_id' : '';

        $sql = "SELECT u.*, SUM(IFNULL(o.order_amount, 0)) AS shopping_amount ".
                "FROM `ecm_users` AS u ".
                "LEFT JOIN `ecm_order_info` AS o ON o.user_id=u.user_id $cond_store ".
                "WHERE $arg[where] GROUP BY u.user_id $having ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";

        return array('data' => $GLOBALS['db']->getAll($sql), 'info' => $arg['info']);
    }

    /**
     * ����û�����
     *
     * @param  string   $condition  ��ѯ����������
     *
     * @return  int
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql = "SELECT COUNT(*) FROM `ecm_users` AS u WHERE $where";

        $rec_count  = $GLOBALS['db']->getOne($sql);
        return $rec_count;
    }

    /**
     * �����û����ƻ���û��ʺ�
     *
     * @param  string   $name
     *
     * @return  array
     */
    function get_users_by_name($name, $limit=30)
    {
        if (!empty($name))
        {
            $sql = "SELECT user_id, user_name FROM `ecm_users` WHERE user_name LIKE '%$name%' AND store_id=$this->store_id LIMIT $limit";

            return $GLOBALS['db']->getAll($sql);
        }
        else
        {
            return array();
        }
    }
    /**
     * �����û�������û���ID
     *
     * @author wj
     * @param  string   $name
     *
     * @return  int
     */
    function get_id_by_name($name)
    {
        if (empty($name))
        {
            return 0;
        }
        else
        {
            $sql = "SELECT user_id FROM `ecm_users` WHERE user_name='$name'";
            $res = $GLOBALS['db']->getOne($sql);

            if ($res === '')
            {
                $arr = uc_call('uc_get_user', $name);

                $res = 0;
                //����Ñ�����,���ڱ����Ñ����м���
                if ($arr !== 0)
                {
                    $res = intval($arr[0]);
                    include_once(ROOT_PATH . '/includes/models/mod.user.php');
                    $mod_user = new User($res);
                    $user_info = array();
                    list($user_info['uid'], $user_info['username'], $user_info['email']) = addslashes_deep($arr);
                    $mod_user->activate($user_info);
                }

                $res = ($arr !== 0) ? $arr[0] : 0;
            }

            return intval($res);
        }
    }
    /**
     * ������ѯ����
     *
     * @return  string
     */
    function _make_condition($arr)
    {
        $where = "1";
        if (isset($arr['user_name']))
        {
            $where .= " AND u.user_name LIKE '%$arr[user_name]%'";
        }

        if (isset($arr['name_array']) && !empty($arr['name_array']))
        {
            $arr_name = explode(' ', $arr['name_array']);
            $where .= " AND u.user_name" .db_create_in($arr_name);
        }

        return $where;
    }

};
?>