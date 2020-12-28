<?php

/**
 * ECSHOP
 * ============================================================================
 * 版权所有 (C) 2005-2007 康盛创想（北京）科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: molyx.php 14769 2008-07-31 07:10:31Z testyang $
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
    $modules[$i]['code']    = 'molyx';

    /* 被整合的第三方程序的名称 */
    $modules[$i]['name']    = 'MolyX';

    /* 被整合的第三方程序的版本 */
    $modules[$i]['version'] = '2.6.x';

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECSHOP R&D TEAM';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ecshop.com';

    /* 插件的初始的默认值 */
    $modules[$i]['default']['db_host'] = 'localhost';
    $modules[$i]['default']['db_user'] = 'root';
    $modules[$i]['default']['prefix'] = 'mxb_';

    return;
}

require_once(ROOT_PATH . 'includes/modules/integrates/integrate.php');
class molyx extends integrate
{
    var $cookie_prefix = '';

    function __construct($cfg)
    {
        $this->molyx($cfg);
    }

    /**
     *  初始化函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function molyx($cfg)
    {
        parent::integrate($cfg);
        if ($this->error)
        {
            /* 数据库连接出错 */
            return false;
        }
        $this->field_id = 'id';
        $this->field_name = 'name';
        $this->field_email = 'email';
        $this->field_gender = 'gender';
        $this->field_bday = 'birthday';
        $this->field_pass = 'password';
        $this->field_reg_date = 'joindate';
        $this->user_table = 'user';

        /* 检查数据表是否存在 */
        $sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";

        $exist_tables = $this->db->getCol($sql);

        if (empty($exist_tables) || (!in_array($this->prefix.$this->user_table, $exist_tables)) || (!in_array($this->prefix.'setting', $exist_tables)))
        {
            $this->error = 2;
            /* 缺少数据表 */
            return false;
        }

        $cookie_prefix = $this->db->getOne("SELECT value FROM " .$this->table('setting'). " WHERE varname='cookieprefix'");
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
            $post_username = strtolower(ecs_iconv('UTF8', $this->charset, $username));
        }
        else
        {
            $post_username = strtolower($username);
        }

        $sql = "SELECT " . $this->field_id . " AS user_id, ". $this->field_pass . " AS password, salt".
               " FROM " . $this->table($this->user_table).
               " WHERE " . $this->field_name . "='" . $post_username . "'";

        $row = $this->db->getRow($sql);

        if (empty($row))
        {
            return 0;
        }

        if ($password === null)
        {
            return $row['user_id'];
        }

        if ($row['password'] == $this->compile_password(array('type'=>PWD_SUF_SALT, 'salt'=>$row['salt'], 'md5password'=>md5($password))))
        {
            return $row['user_id'];
        }
        else
        {
            return 0;
        }
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
            $time = time() - 3600 * 24;
            setcookie($this->cookie_prefix.'sessionid', '', $time, $this->cookie_path, $this->cookie_domain);
            setcookie($this->cookie_prefix.'userid', '', $time, $this->cookie_path, $this->cookie_domain);
            setcookie($this->cookie_prefix.'password', '', $time, $this->cookie_path, $this->cookie_domain);
        }
        else
        {
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset, $username);
            }
            $sql = "SELECT " . $this->field_id . " AS user_id, salt, " . $this->field_pass . " As password ".
                   " FROM " . $this->table($this->user_table) .
                   " WHERE " . $this->field_name . "='$username'";

            $row = $this->db->getRow($sql);

            $time = time() + 3600 * 24 * 30;
            setcookie($this->cookie_prefix.'sessionid', '', time() - 3600 * 24, $this->cookie_path, $this->cookie_domain);
            setcookie($this->cookie_prefix.'userid', $row['user_id'], time() + 3600 * 24 * 30, $this->cookie_path, $this->cookie_domain);
            setcookie($this->cookie_prefix.'password', $row['password'], time() + 3600 * 24 * 30, $this->cookie_path, $this->cookie_domain);
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
        if ((!isset($_COOKIE[$this->cookie_prefix.'userid'])) || (!isset($_COOKIE[$this->cookie_prefix.'password'])))
        {
            return false;
        }

        $sql = "SELECT " . $this->field_name .
               " FROM " .$this->table($this->user_table).
               " WHERE " .$this->field_id ."='". $_COOKIE[$this->cookie_prefix.'userid'] . "'".
               " AND " . $this->field_pass . "='" . $_COOKIE[$this->cookie_prefix.'password'] . "'";
        $username = $this->db->getOne($sql);

        if ($username && ($this->charset != 'UTF8'))
        {
            $username = ecs_iconv($this->charset, 'UTF8', $username);
        }

        return $username;
    }

    /**
     *  获取论坛有效积分及单位
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_points_name ()
    {
        static $ava_credits = NULL;
        if ($ava_credits === NULL)
        {
            $sql = "SELECT IF(value>'' , value, defaultvalue)".
                   " FROM " . $this->table('setting').
                   " WHERE varname = 'bankcurrency'";
            $unit = $this->db->getOne($sql);

            $ava_credits['cash']['title'] = 'CASH';
            $ava_credits['cash']['unit'] = empty($unit)? '' : ($this->charset != 'UTF8') ? ecs_iconv($this->charset, 'UTF8', $unit) : $unit;
        }

        return $ava_credits;
    }
}
?>