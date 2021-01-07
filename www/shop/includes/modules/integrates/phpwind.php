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
 * $Id: phpwind.php 14769 2008-07-31 07:10:31Z testyang $
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
    $modules[$i]['code']    = 'phpwind';

    /* 被整合的第三方程序的名称 */
    $modules[$i]['name']    = 'PHPWind';

    /* 被整合的第三方程序的版本 */
    $modules[$i]['version'] = '4.0.1';

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECSHOP R&D TEAM';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ecshop.com';

    /* 插件的初始的默认值 */
    $modules[$i]['default']['db_host'] = 'localhost';
    $modules[$i]['default']['db_user'] = 'root';
    $modules[$i]['default']['prefix'] = 'pw_';

    return;
}

require_once(ROOT_PATH . 'includes/modules/integrates/integrate.php');
class phpwind extends integrate
{
    /* 论坛加密密钥 */
    var $db_hash = '';

    function __construct($cfg)
    {
        $this->phpwind($cfg);
    }

    /**
     *  插件类初始化函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function phpwind ($cfg)
    {
        parent::integrate($cfg);
        if ($this->error)
        {
            /* 数据库连接出错 */
            return false;
        }
        $this->field_id = 'uid';
        $this->field_name = 'username';
        $this->field_email = 'email';
        $this->field_gender = 'gender';
        $this->field_bday = 'bday';
        $this->field_pass = 'password';
        $this->field_reg_date = 'regdate';
        $this->user_table = 'members';

        /* 检查数据表是否存在 */
        $sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";

        $exist_tables = $this->db->getCol($sql);

        if (empty($exist_tables) || (!in_array($this->prefix.$this->user_table, $exist_tables)) || (!in_array($this->prefix.'config', $exist_tables)))
        {
            $this->error = 2;
            /* 缺少数据表 */
            return false;
        }

        /* 设置论坛的加密密钥 */
        $this->db_hash = $this->db->GetOne("SELECT `db_value` FROM ".$this->table('config')." WHERE `db_name` = 'db_hash'");
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
        $cookie_name = 'winduser';
        if (empty($username))
        {
            $time = time() - 3600;
            setcookie($cookie_name, '', $time, $this->cookie_path, $this->cookie_domain);
        }
        else
        {
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset, $username);
            }


            $sql = "SELECT " . $this->field_id . " AS user_id, " . $this->field_pass . " As password ".
                   " FROM " . $this->table($this->user_table) .
                   " WHERE " . $this->field_name . "='$username'";

            $row = $this->db->getRow($sql);

            $cookie_name = 'winduser';
            $salt =  md5($_SERVER["HTTP_USER_AGENT"] . $row['password'] . $this->db_hash);

            //$auto_login_key = $this->code_string($row['user_id'] . "\t" . $salt, 'ENCODE');
            $auto_login_key = $row['user_id'] . "\t" . $salt;

            setcookie($cookie_name, $auto_login_key, time()+3600*24*30, $this->cookie_path, $this->cookie_domain);
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
        $cookie_name = 'winduser';

        if (!isset($_COOKIE[$cookie_name]))
        {
            return '';
        }

        $arr = addslashes_deep(explode("\t", $_COOKIE[$cookie_name]));
        if (count($arr) != 2)
        {
            return false;
        }
        list($user_id, $salt_probe) = $arr;

        $sql = "SELECT " .$this->field_id. " AS user_id, " . $this->field_name . " As user_name, ".
               $this->field_pass . " AS password ".
               " FROM ".$this->table($this->user_table).
               " WHERE " . $this->field_id . " = '$user_id'";
        $row = $this->db->getRow($sql);

        if (!$row)
        {
            return '';
        }

        $salt = md5($_SERVER["HTTP_USER_AGENT"] . $row['password'] . $this->db_hash);

        if ($salt != $salt_probe)
        {
            return '';
        }

        if ($this->charset != 'UTF8')
        {
            $row['user_name'] = ecs_iconv($this->charset, 'UTF8', $row['user_name']);
        }

        return $row['user_name'];

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
            $sql = "SELECT db_value FROM " . $this->table('config') . " WHERE db_name='db_credits'";
            $str = $this->db->getOne($sql);
            if (empty($str))
            {
                return array();
            }

            if ($this->charset != 'UTF8')
            {
                $str = empty($str) ? '' : ecs_iconv($this->charset, 'UTF8', $str);
            }

            list($ava_credits['money']['title'], $ava_credits['money']['unit'],$ava_credits['rvrc']['title'],$ava_credits['rvrc']['unit'],$ava_credits['credit']['title'], $ava_credits['credit']['unit'])=explode("\t",$str);
        }

        return $ava_credits;
    }

    /**
     *  获取用户积分
     *
     * @access  public
     * @param
     *
     * @return array
     */
    function get_points($username)
    {
        $credits = $this->get_points_name();
        $fileds = array_keys($credits);

        if ($fileds)
        {
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset,  $username);
            }
            $sql = "SELECT ud." . $this->field_id . ', ' . implode(', ',$fileds).
                   " FROM " . $this->table('memberdata'). "AS ud, ".
                   $this->table($this->user_table). " AS u ".
                   " WHERE u." . $this->field_id . "= ud." .$this->field_id . " AND u." . $this->field_name . "='$username'";
            $row = $this->db->getRow($sql);
            if (isset($row['rvrc']))
            {
                $row['rvrc'] = floor($row['rvrc'] /10);
            }
            return $row;
        }
        else
        {
            return false;
        }
    }

    /**
     * 积分设置
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function set_points ($username, $credits)
    {
        If ($this->charset != 'UTF8')
        {
            $username = ecs_iconv('UTF8', $this->charset,  $username);
        }

        if (isset($credits['rvrc']))
        {
            $credits['rvrc'] = $credits['rvrc'] * 10;
        }

        $sql = "SELECT " . $this->field_id .
               " FROM " . $this->table($this->user_table).
               " WHERE " . $this->field_name . "='$username'";
        $uid = $this->db->getOne($sql);

        $user_set = array_keys($credits);
        $points_set = array_keys($this->get_points_name());

        $set = array_intersect($user_set, $points_set);

        if ($set)
        {
            $tmp = array();
            foreach ($set as $credit)
            {
               $tmp[] = $credit . '=' . $credit . '+' . $credits[$credit];
            }
            $sql = "UPDATE " . $this->table('memberdata').
                   " SET " . implode(', ', $tmp).
                   " WHERE " . $this->field_id . " = '$uid'";
            $this->db->query($sql);
        }

        return true;
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date=0, $md5password='')
    {
        $result = parent::add_user($username, $password, $email, $gender, $bday, $reg_date, $md5password);
        if (!$result)
        {
            return false;
        }

        $user_id = $this->check_user($username);
        if ($user_id > 0)
        {
            $sql = "REPLACE INTO " . $this->table('memberdata') . " (" . $this->field_id .") VALUES ('$user_id')";
            $this->db->query($sql);
        }

        return true;
    }
}
?>