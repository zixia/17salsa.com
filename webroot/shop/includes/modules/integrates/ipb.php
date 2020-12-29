<?php

/**
 * ECSHOP 会员数据处理类
 * ============================================================================
 * 版权所有 (C) 2005-2007 康盛创想（北京）科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: ipb.php 14769 2008-07-31 07:10:31Z testyang $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    /* 会员数据整合插件的代码必须和文件名保持一致 */
    $modules[$i]['code']    = 'ipb';

    /* 被整合的第三方程序的名称 */
    $modules[$i]['name']    = 'Invision Power Board';

    /* 被整合的第三方程序的版本 */
    $modules[$i]['version'] = '2.1.7/2.2.x';

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECSHOP R&D TEAM';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ecshop.com';

    /* 插件的初始的默认值 */
    $modules[$i]['default']['db_host'] = 'localhost';
    $modules[$i]['default']['db_user'] = 'root';
    $modules[$i]['default']['prefix'] = 'ibf_';
    //$modules[$i]['default']['cookie_prefix'] = 'xnW_';

    return;
}

require_once(ROOT_PATH . 'includes/modules/integrates/integrate.php');
class ipb extends integrate
{
    var $cookie_prefix = '';

    function __construct($cfg)
    {
        $this->ipb($cfg);
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function ipb($cfg)
    {
        parent::integrate($cfg);
        if ($this->error)
        {
            /* 数据库连接出错 */
            return false;
        }
        //$this->cookie_prefix = $cfg['cookie_prefix'];
        $this->field_id = 'id';
        $this->field_name = 'name';
        $this->field_email = 'email';
        $this->field_gender = 'NULL';
        $this->field_bday = 'NULL';
        $this->field_pass = 'NULL';
        $this->field_reg_date = 'joined';
        $this->user_table = 'members';


        /* 检查数据表是否存在 */
        $sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";

        $exist_tables = $this->db->getCol($sql);

        if (empty($exist_tables) || (!in_array($this->prefix.$this->user_table, $exist_tables)))
        {
            $this->error = 2;
            /* 缺少数据表 */
            return false;
        }
    }

    /**
     *  检查指定用户是否存在及密码是否正确
     *
     * @access  public
     * @param   string  $username   用户名
     *
     * @return  int
     */
    function check_user($username, $password = null)
    {
        if ($this->charset != 'UTF8')
        {
            $post_username = ecs_iconv('UTF8', $this->charset, $username);
        }
        else
        {
            $post_username = $username;
        }

        if ($password === null)
        {
            $sql = "SELECT " . $this->field_id .
                   " FROM " . $this->table($this->user_table).
                   " WHERE " . $this->field_name . "='" . $post_username . "'";

            return $this->db->getOne($sql);
        }
        else
        {
            /*$sql = "SELECT " . $this->field_id .
                   " FROM " . $this->table($this->user_table).
                   " WHERE " . $this->field_name . "='" . $post_username . "' AND " . $this->field_pass . " ='" . $this->compile_password(array('password'=>$password)) . "'";*/

            $sql = "SELECT m.id, m.name, m.email, m.member_login_key, mc.converge_pass_hash, mc.converge_pass_salt".
               " FROM ".$this->table('members')." AS m, ".$this->table('members_converge')." AS mc".
               " WHERE m.name = '$post_username' AND m.email = mc.converge_email";

            $row = $this->db->getRow($sql);

            if ($row['converge_pass_hash'] != $this->compile_password(array('password'=>$password, 'salt'=>$row['converge_pass_salt'])))
            {
                return 0;
            }
            else
            {
                return $row['id'];
            }
        }
    }

    /**
     *  添加一个新用户
     *
     * @access  public
     * @param
     *
     * @return int
     */
    function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date=0, $md5password='')
    {
        /* 将用户添加到整合方 */
        if ($this->check_user($username) > 0)
        {
            $this->error = ERR_USERNAME_EXISTS;

            return false;
        }
        /* 检查email是否重复 */
        $sql = "SELECT " . $this->field_id .
               " FROM " . $this->table($this->user_table).
               " WHERE " . $this->field_email . " = '$email'";
        if ($this->db->getOne($sql, true) > 0)
        {
            $this->error = ERR_EMAIL_EXISTS;

            return false;
        }

        if ($this->charset != 'UTF8')
        {
            $post_username = ecs_iconv('UTF8', $this->charset, $username);
        }
        else
        {
            $post_username = $username;
        }

        /* 生成随机串 */
        $salt = $this->generate_password_salt(5);

        /* 生成加密密码 */
        $converge_pass_hash = $this->compile_password(array('password'=>$password, 'salt'=>$salt));

        /* 规格化随机串 */
        $converge_pass_salt = str_replace( '\\', "\\\\", $salt);

        /* 插入数据到members_converge表 */
        $sql = "INSERT INTO ".$this->table('members_converge')." (`converge_id`, `converge_email`,`converge_joined`, `converge_pass_hash`, `converge_pass_salt`) VALUES (null, '$email', " . time() . ", '$converge_pass_hash', '$converge_pass_salt')";
        $this->db->query($sql);

        /* 得到新加用户的UID */
        $uid = $this->db->Insert_ID();

        /* 获得默认的用户组 */
        $grp = 3;

        /* 生成自动登录密钥，存于COOKIE中 */
        $auto_login_key = $this->generate_auto_log_in_key();

        /* 插入数据到members表 */
        $sql = "INSERT INTO ".$this->table('members')." (`id`, `name`, `mgroup`, `email`, `joined`, `ip_address`, `member_login_key`, `members_display_name`, `members_l_username`)
                VALUES ($uid, '$post_username', $grp, '$email', " . time() . ", '" . real_ip() . "', '$auto_login_key', '$post_username', '$post_username')";
        $result = $this->db->query($sql);

        if ($this->need_sync)
        {
            $this->sync($username);
        }

        return true;
   }

    /**
     *  设置论坛cookie
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function set_cookie ($username="")
    {
        parent::set_cookie($username);
        if (empty($username))
        {
            $time = time() - 3600;
            setcookie('session_id', '', $time, $this->cookie_path, $this->cookie_domain);
            setcookie('member_id', '', $time, $this->cookie_path, $this->cookie_domain);
            setcookie('pass_hash', '', $time, $this->cookie_path, $this->cookie_domain);
        }
        else
        {
            $time = time() +  3600 * 24 * 30;
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset, $username);
            }
            $sql = "SELECT " . $this->field_id . " AS user_id, member_login_key ".
                   " FROM " . $this->table($this->user_table) . " WHERE " . $this->field_name . "='$username'";

            $row = $this->db->getRow($sql);

            if ($row)
            {
                setcookie('member_id', $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
                setcookie('pass_hash', $row['member_login_key'], $time, $this->cookie_path, $this->cookie_domain);
            }
        }
    }

    /**
     * 检查cookie
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function check_cookie ()
    {
        if (empty($_COOKIE['member_id']) || empty($_COOKIE['pass_hash']))
        {
            return '';
        }

        $user_id = intval($_COOKIE['member_id']);
        $auto_login_key = addslashes_deep($_COOKIE['pass_hash']);

        $sql = "SELECT " . $this->field_name ." AS user_name".
               " FROM " . $this->table($this->user_table) .
               " WHERE `id` = '$user_id' AND `member_login_key` = '$auto_login_key'";

        $username = $this->db->getOne($sql);
        if ($username && ($this->charset != 'UTF8'))
        {
            $username = ecs_iconv($this->charset, 'UTF8', $username);
        }

        return $username;
    }

    /**
     *  编译密码函数
     *
     * @access  public
     * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
     *
     * @return void
     */
    function compile_password ($cfg)
    {
       if ((!empty($cfg['password'])) && empty($cfg['md5password']))
       {
            $cfg['md5password'] = md5($cfg['password']);
       }

       if (!isset($cfg['salt']))
       {
           $cfg['salt'] = '';
       }

       return md5(md5($cfg['salt']).$cfg['md5password']);
    }

   /**
    * Generates a password salt
    *
    * Returns n length string of any char except backslash
    *
    * @access   private
    * @param    integer Length of desired salt, 5 by default
    * @return   string  n character random string
    */
    function generate_password_salt($len = 5)
    {
        $salt = '';

        //srand( (double)microtime() * 1000000 );
        // PHP 4.3 is now required ^ not needed

        for ($i = 0; $i < $len; $i++)
        {
            $num = mt_rand(33, 126);

            if ($num == '92')
            {
                $num = 93;
            }

            $salt .= chr($num);
        }

        return $salt;
    }

   /**
    * Generates a log in key
    *
    * @access   private
    * @param    integer Length of desired random chars to MD5
    * @return   string  MD5 hash of random characters
    */
    function generate_auto_log_in_key($len = 60)
    {
        $pass = $this->generate_password_salt(60);

        return md5($pass);
    }
}