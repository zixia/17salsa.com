<?php

/**
 * ECMALL: 会员实体类基类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.userbase.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class UserBase extends Model
{
    /**
     * 构造函数
     */
    function __construct($user_id, $store_id=0)
    {
        $this->UserBase($user_id, $store_id);
    }

    function UserBase($user_id, $store_id=0)
    {
        parent::Model($user_id, $store_id);
    }
    /**
     * 用户登出
     *
     * @author wj
     * @return  void
     */
    function logout()
    {
        $GLOBALS['sess']->destroy_session();
        $_SESSION = array();
    }


    /**
     *  获取用户详细信息
     *
     *  @access public
     *  @param  none
     *  @return array
     */
    function get_user_detail()
    {
        if (!$this->_id)
        {
            return;
        }
        $sql = "SELECT * FROM `ecm_users` WHERE user_id={$this->_id}";

        return $GLOBALS['db']->getRow($sql);
    }


    /**
     *  本地登录,不到UC端去验证
     *
     *  @access public
     *  @param  int $user_id        用户ID
     *  @param  string $password    本地密码
     *  @param  string $model       ADMIN为后台用户登录,USER为前台用户登录
     *  @return mixed
     */

    function local_login($user_id, $password)
    {
        $user_id = intval($user_id);
        if ($this->_model == 'ADMIN')
        {
            $sql = "SELECT u.user_name,u.password,au.* FROM `ecm_users` u LEFT JOIN `ecm_admin_user` au ON u.user_id = au.user_id WHERE u.user_id={$user_id} AND u.password='{$password}'";
        }
        else
        {
            $sql = "SELECT * FROM `ecm_users` WHERE user_id={$user_id} AND password='{$password}'";
        }
        $row = $GLOBALS['db']->getRow($sql);

        if ($row['user_id'] > 0 )
        {
            if ($this->_model == 'ADMIN')
            {
                $this->_id = $row['user_id'];
                $this->_update_last_login();

                /* 保存session */
                $_SESSION['admin_id']   = $row['user_id'];
                $_SESSION['admin_name'] = $row['real_name'];
                $_SESSION['store_id']   = $row['store_id'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];
            }
            else
            {
                $this->_id  = $row['uid'];
                $this->_update_last_login();

                /* 保存session */
                $_SESSION['user_id']    = $row['user_id'];
                $_SESSION['user_name']  = $row['user_name'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];
            }

            return $row;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 从Ucenter获取同步登录代码
     *
     * @author  weberliu
     * @return  string
     */
    function sync_login($uid=null)
    {
        $res = uc_call('uc_user_synlogin', array($uid));
        return $this->_sync_ucenter('uc_user_synlogin', $uid);
    }

    /**
     * 从Ucenter获取同步退出代码
     *
     * @author  weberliu
     * @return  string
     */
    function sync_logout($uid=null)
    {
        return $this->_sync_ucenter('uc_user_synlogout', $uid);
    }

    /**
     * 从Ucenter获得同步登入登出的代码
     *
     * @author  weberliu
     * @param   string  $func
     * @param   int     $uid
     * @return  string
     */
    function _sync_ucenter($func, $uid=null)
    {
        if ($uid === null)
        {
            $uid = $this->_id;
        }

        $res = uc_call($func, array($uid));

        if (preg_match('/<script/i', $res))
        {
            return $res;
        }
        else
        {
            return '';
        }
    }

    /**
     * 生成随机串
     *
     * @param   int     $len
     *
     * @return  string
     */
    function generate_code($len=4)
    {
        $chars = '23457acefhkmprtvwxy';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++)
        {
            $arr[$i] = $chars[$i];
        }

        mt_srand((double) microtime() * 1000000);
        shuffle($arr);

        $code = substr(implode('', $arr), 5, $len);

        return $code;
    }

    /**
     * 更新最后登录时间和IP地址
     *
     * @return  void
     */
    function _update_last_login()
    {
        $sql = "UPDATE $this->_table SET ".
                "last_login='" .gmtime(). "',".
                "last_ip='" .real_ip(). "' ".
                "WHERE $this->_key='$this->_id'";
        $GLOBALS['db']->query($sql);
    }

    /**
     * 产生随机的密码
     *
     *
     *  @return array goods_id
     */
    function generate_password()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        $hash = '';
        for($i = 0; $i < 10; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }

        return md5($hash);
    }

    /**
     * 激活本地用户
     * @authore scottye
     *
     * @param array base_info
     *
     * @return array user_info
     */
    function activate($info)
    {
        $row = array();
        $row['user_id'] = $info['uid'];
        $row['user_name'] = addslashes($info['username']);
        $row['email'] = $info['email'];
        $row['last_login'] = gmtime();
        $row['reg_time'] = gmtime();
        $row['last_ip'] = real_ip();
        $row['visit_count'] = 1;
        $row['store_id'] = -1;
        $row['default_feed'] = 15;
        $row['password'] = $this->generate_password();
        $GLOBALS['db']->autoExecute('`ecm_users`', $row, 'INSERT');

        return $this->get_local_user_info($info['uid']);
    }

    /**
     *  更新卖家信用积分
     *
     *  @author Garbin
     *  @return void
     */
    function update_seller_credit()
    {
        $this->update_credit('seller');
    }

    /**
     *  更新买家信用积分
     *
     *  @author Garbin
     *  @return void
     */
    function update_buyer_credit()
    {
        $this->update_credit('buyer');
    }

    /**
     *  更新信用积分
     *
     *  @author Garbin
     *  @param  string $mode    要更新的信用积分名称，可选值[seller:]
     *  @return void
     */
    function update_credit($mode)
    {
        if ($mode != 'seller' && $mode != 'buyer')
        {
            return;
        }
        $user_field = $mode == 'seller' ? 'store_id' : 'user_id';
        $sql = "SELECT SUM({$mode}_credit) as s_c ".
               "FROM `ecm_order_info` WHERE {$user_field}={$this->_id} AND {$mode}_evaluation_invalid=0";
        $v   = $GLOBALS['db']->getOne($sql);
        $v   = floatval($v);

        $usql = "UPDATE `ecm_users` SET {$mode}_credit = {$v} WHERE user_id={$this->_id}";
        $GLOBALS['db']->query($usql);
    }

    /**
     * 获取本应用的用户数据
     * @author  wj
     * @param   int     $uid     用户id
     *
     * @return void
     */
    function get_local_user_info($uid)
    {
        $sql = "SELECT u.*, IFNULL(au.store_id, -1) AS store_id FROM `ecm_users` AS u ".
                    "LEFT JOIN `ecm_admin_user` AS au ON au.user_id=u.user_id WHERE u.user_id='$uid'";

        return  $GLOBALS['db']->getRow($sql);
    }
}
?>
