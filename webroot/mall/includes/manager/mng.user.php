<?php

/**
 * ECMALL: 用户帐号管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 获得用户列表
     *
     * @param int       $page
     * @param array     $condition
     *                      shopping_amount : 购物总额
     *                      name_array      : 用户名数组
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
     * 获得用户总数
     *
     * @param  string   $condition  查询条件的数组
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
     * 根据用户名称获得用户帐号
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
     * 根据用户名获得用户的ID
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
                //如果用戶存在,就在本地用戶表中激活
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
     * 创建查询条件
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