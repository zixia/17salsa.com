<?php

/**
 * ECMALL: ��Աʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     *  ��ȡ�ù���Ա����ϸ��Ϣ
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
     * �û���¼
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
            /* ����Ƿ������ecmall����Ա�� */
            $sql = "SELECT user_id, real_name, store_id, last_login, last_ip FROM $this->_table WHERE $this->_key='$tmp[uid]'";
            $row = $GLOBALS['db']->getRow($sql);

            if ($row)
            {
                $this->_id = $tmp['uid'];
                $this->_update_last_login();
                $this->set_recent_ip(real_ip());

                /* ����session */
                $_SESSION['admin_id']   = $row['user_id'];
                $_SESSION['admin_name'] = $row['real_name'];
                $_SESSION['store_id']   = $row['store_id'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];

                /* ����Ƿ������ecmall�û���,�����ڼ��� */
                $sql = "SELECT * FROM `ecm_users` WHERE user_id='$tmp[uid]'";
                $row = $GLOBALS['db']->getRow($sql);
                if (empty($row))
                {
                    $row = $this->activate($tmp);
                }

                /* �жϵ����Ƿ�Ϊ���Թ���״̬ */

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
     * ���¹���Ա�ĳ��õ���
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
     * �������ԱȨ��
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
     * ȡ�������¼����ip
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
     * ���������¼����ip
     *
     * @author  scottye
     * @param   string      $this_ip    ���ε�¼��ip
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