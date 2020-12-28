<?php

/**
 * ECMALL: 会员实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.adminuser.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.userbase.php');

class AdminUser extends UserBase
{
    var $_model = 'ADMIN';

    /**
     * 构造函数
     */
    function __construct($user_id, $store_id=0)
    {
        $this->AdminUser($user_id, $store_id);
    }

    function AdminUser($user_id, $store_id=0)
    {
        $this->_table = '`ecm_admin_user`';
        $this->_key   = 'user_id';
        parent::UserBase($user_id, $store_id);
    }

    /**
     *  获取该管理员的详细信息
     *
     *  @access public
     *  @param  none
     *  @return array
     */

    function get_user_detail()
    {
        if (!$this->_id)
        {
            $this->_err = 'Id not define';

            return false;
        }
        $sql = "SELECT u.*,au.* FROM `ecm_admin_user` au LEFT JOIN `ecm_users` u ON au.user_id=u.user_id WHERE u.user_id={$this->_id}";

        return $GLOBALS['db']->getRow($sql);
    }
    /**
     * 用户登录
     *
     * @param   string  $username
     * @param   string  $password
     *
     * @return  array
     */
    function login($username, $password)
    {
        $res = uc_call('uc_user_login', array($username, $password));
        list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email']) = addslashes_deep($res);

        if ($tmp['uid'] > 0)
        {
            /* 检查是否存在于ecmall管理员表 */
            $sql = "SELECT user_id, real_name, store_id, last_login, last_ip FROM $this->_table WHERE $this->_key='$tmp[uid]'";
            $row = $GLOBALS['db']->getRow($sql);

            if ($row)
            {
                $this->_id = $tmp['uid'];
                $this->_update_last_login();
                $this->set_recent_ip(real_ip());

                /* 保存session */
                $_SESSION['admin_id']   = $row['user_id'];
                $_SESSION['admin_name'] = $row['real_name'];
                $_SESSION['store_id']   = $row['store_id'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];

                /* 检查是否存在于ecmall用户表,不存在激活 */
                $sql = "SELECT * FROM `ecm_users` WHERE user_id='$tmp[uid]'";
                $row = $GLOBALS['db']->getRow($sql);
                if (empty($row))
                {
                    $row = $this->activate($tmp);
                }

                /* 判断店铺是否为可以管理状态 */

                return true;
            }
            else
            {
                $this->err = 'invalid_manager';
                return false;
            }
        }
        else
        {
            $this->err = ($tmp['uid'] == -1) ? 'user_not_exists' : 'incorrect_password';
            return $tmp;
        }
    }

    /**
     * 更新管理员的常用导航
     *
     * @param   string  $key
     * @param   array   $menu
     *
     * @return  void
     */
    function update_nav($key, $data)
    {
        $info = $this->get_info();
        $menu = empty($info['nav_list']) ? array() : unserialize($info['nav_list']);

        if (array_key_exists($key, $menu))
        {
            $menu[$key]['times'] ++;
        }
        else
        {
            $menu[$key] = array('app' => $data['app'], 'act' => $data['act'], 'times' => 1);
        }

        if (count($menu) > 5)
        {
            $func = create_function('$a, $b', 'return $a[\'times\']<$b[\'times\'] ? 1:-1;');
            uasort($menu, $func);

            $menu = array_slice($menu, 0, 5);
        }

        $this->update(array('nav_list' => serialize($menu)));
    }

    /**
     * 检验管理员权限
     * @param string $app
     * @param string $act
     *
     * @return boolean true or false
     */
    function check_priv($app, $act)
    {
        $default_allow_priv = array('home|home', 'home|welcome', 'profile|logout', 'profile|captcha');
        $app_act = $app . '|' . $act;
        $admin_info = $this->get_info();
        if ($act == 'jslang' || in_array($app_act, $default_allow_priv) || $admin_info['privilege'] == 'all' || $_SESSION['admin_id'] == $_SESSION['store_id'] ||
        strpos($admin_info['privilege'], $app_act) !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 取得最近登录过的ip
     *
     * @author  scottye
     * @return  array
     */
    function get_recent_ip()
    {
        $info = $this->get_info();
        if (!empty($info['recent_ip']))
        {
            return explode(',', $info['recent_ip']);
        }
        else
        {
            return array();
        }
    }

    /**
     * 设置最近登录过的ip
     *
     * @author  scottye
     * @param   string      $this_ip    本次登录的ip
     * @return  void
     */
    function set_recent_ip($this_ip)
    {
        $recent_ip = $this->get_recent_ip();
        if (!in_array($this_ip, $recent_ip))
        {
            if (count($recent_ip) >= 20)
            {
                array_pop($recent_ip);
            }
            array_push($recent_ip, $this_ip);
            $this->update(array('recent_ip' => join(',', $recent_ip)));
        }
    }
}
?>