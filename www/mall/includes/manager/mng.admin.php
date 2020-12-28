<?php

/**
 * ECMALL: 用户帐号管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.admin.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class AdminManager extends Manager
{
    var $store_id = 0;
    var $err = null;

    function __construct($store_id=0)
    {
        $this->AdminManager($store_id);
    }
    function AdminManager($store_id=0)
    {
        $this->store_id = intval($store_id);
    }
    /**
     *  获取管理员列表
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null)
    {
        $arg = $this->query_params($page, $condition, 'add_time');

        $where  = $this->_make_condition($condition);

        $sql = "SELECT admin.*, user.user_name FROM `ecm_admin_user` AS admin, `ecm_users` AS user ".
                "WHERE $where AND admin.user_id=user.user_id AND admin.store_id='" . $this->store_id . "' ".
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);
        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     * 获取管理员列表根据user_ids
     *
     * @author  weberliu
     * @param   strings ids     管理员id
     * @return  array   admin_list
     */
    function get_list_by_ids($ids)
    {
        $query_in = db_create_in($ids, 'admin.user_id');

        $sql = "SELECT admin.*, user.user_name FROM `ecm_admin_user` AS admin, `ecm_users` AS user ".
               "WHERE $query_in AND admin.user_id=user.user_id AND admin.store_id='" . $this->store_id . "'";
        $res = $GLOBALS['db']->getAll($sql);
        return $res;
    }

    /**
     *  获取符合条件的记录数
     *  @param  int $start_id, int $page_size
     *  @return Array
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_admin_user` WHERE $where AND store_id='" . $this->store_id . "'";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     * 添加管理员
     *
     * @param   int     $user_id
     * @param   string  $real_name
     * @param   array   $privilege
     *
     * @return  boolean
     */
    function add($user_id, $real_name = '', $privilege = array())
    {
        $privilege_data = implode(',', $privilege);
        $sql = "SELECT user_id FROM `ecm_admin_user` WHERE user_id='$user_id' AND store_id='" . $this->store_id . "'";
        if ($GLOBALS['db']->getOne($sql))
        {
            $this->err[] = 'the_admin_is_exists';
            return false;
        }
        $sql = "INSERT INTO `ecm_admin_user` (store_id, user_id, real_name, add_time, privilege)
                VALUES ('" . $this->store_id . "', '$user_id', '$real_name', '" . time() . "', '$privilege_data')";
        if ($GLOBALS['db']->query($sql))
        {
            return true;
        }
        else
        {
            $this->err[] = 'add_admin_failed';
            return false;
        }
    }

    /**
     * 获得指定user_id 的管理员的详情
     * @params int $user_id
     *
     * @return array
     */
    function get_info($user_id)
    {
        $sql = "SELECT au.*, u.user_name FROM `ecm_admin_user` au ".
                "LEFT JOIN `ecm_users` u ON au.user_id=u.user_id ".
                "WHERE au.user_id='$user_id' AND au.store_id='" . $this->store_id . "' LIMIT 1";
        return $GLOBALS['db']->getRow($sql);
    }

    function get_by_id($user_id)
    {
        $sql = "SELECT * FROM `ecm_admin_user` WHERE user_id='$user_id' LIMIT 1";
        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 删除管理员
     *
     * @author  weberliu
     * @params  string      $ids    example 1,2,3,4,6
     * @return bool
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

        $admin_list = $this->get_list_by_ids($ids);
        foreach ($admin_list as $val)
        {
            if ($val['user_id'] == $this->store_id || $val['privilege'] == 'all')
            {
                $ids = preg_replace('/,?' . $val['user_id'] . '/', '', $ids);
            }
        }

        if (empty($ids))
        {
            $this->err = 'founder_not_drop';
            return false;
        }

        $sql = "DELETE FROM `ecm_admin_user` WHERE " .db_create_in($ids, "user_id"). " AND store_id='" . $this->store_id . "'";
        $GLOBALS['db']->query($sql);
        return true;
    }

    /**
     * 取得所有者id
     *
     * @return  int
     */
    function get_owner_id()
    {
        $sql = "SELECT user_id FROM `ecm_admin_user` WHERE store_id = '" . $this->store_id . "' AND privilege = 'all' LIMIT 1";
        return $GLOBALS['db']->getOne($sql);
    }

};
?>