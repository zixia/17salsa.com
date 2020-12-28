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
 * $Id: discuz.php 15345 2008-11-28 02:29:11Z testyang $
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
    $modules[$i]['code']    = 'discuz';

    /* 被整合的第三方程序的名称 */
    $modules[$i]['name']    = 'Discuz!';

    /* 被整合的第三方程序的版本 */
    $modules[$i]['version'] = '4.1/5.0';

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECSHOP R&D TEAM';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ecshop.com';

    /* 插件的初始的默认值 */
    $modules[$i]['default']['db_host'] = 'localhost';
    $modules[$i]['default']['db_user'] = 'root';
    $modules[$i]['default']['prefix'] = 'cdb_';

    return;
}

require_once(ROOT_PATH . 'includes/modules/integrates/integrate.php');
class discuz extends integrate
{
    /* 上次访问的时间字段 */
    var $lastvisit     = 'lastvisit';

    /* 论坛加密key */
    var $authkey        = '';

    var $error          = 0;

    function __construct($cfg)
    {
        $this->discuz($cfg);
    }

    /*------------------------------------------------------ */
    //-- PUBLIC METHODs
    /*------------------------------------------------------ */

    /**
     * 会员数据整合插件类的构造函数
     *
     * @access      public
     * @param       string  $db_host    数据库主机
     * @param       string  $db_name    数据库名
     * @param       string  $db_user    数据库用户名
     * @param       string  $db_pass    数据库密码
     * @return      void
     */
    function discuz($cfg)
    {
        parent::integrate($cfg);
        if ($this->error)
        {
            /* 数据库连接出错 */
            return false;
        }
        $this->cookie_prefix = isset($cfg['prefix']) ? $cfg['prefix'] : '';
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

        if (empty($exist_tables) || (!in_array($this->prefix.$this->user_table, $exist_tables)) || (!in_array($this->prefix.'settings', $exist_tables)))
        {
            $this->error = 2;
            /* 缺少数据表 */
            return false;
        }

        $key = $this->db->GetOne('SELECT value FROM ' . $this->table('settings') . " WHERE variable = 'authkey'");
        $this->authkey = md5($key . $_SERVER['HTTP_USER_AGENT']);
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
            $sql = "SELECT value FROM " . $this->table('settings') . " WHERE variable='extcredits'";
            $str = $this->db->getOne($sql);
            $extcredits = @unserialize($str);

            $ava_credits = array();
            if ($extcredits)
            {
                $count = count($extcredits);
                for ($i=1; $i <= $count; $i++)
                {
                    if (!empty($extcredits[$i]['available']))
                    {
                        $ava_credits['extcredits' . $i]['title']  = empty($extcredits[$i]['title'])? '' : ($this->charset != 'UTF8') ? ecs_iconv($this->charset, 'UTF8', $extcredits[$i]['title']) : $extcredits[$i]['title'];
                        $ava_credits['extcredits' . $i]['unit']  = '';
                    }
                }
            }
        }

        return $ava_credits;
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function set_points ($username, $credits)
    {
        $user_set = array_keys($credits);
        $points_set = array_keys($this->get_points_name());

        $set = array_intersect($user_set, $points_set);

        if ($set)
        {
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset,  $username);
            }
            $tmp = array();
            foreach ($set as $credit)
            {
               $tmp[] = $credit . '=' . $credit . '+' . $credits[$credit];
            }
            $sql = "UPDATE " . $this->table($this->user_table).
                   " SET " . implode(', ', $tmp).
                   " WHERE " . $this->field_name . " = '$username'";
            $this->db->query($sql);
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
            setcookie($this->cookie_prefix.'sid', '', $time, $this->cookie_path, $this->cookie_domain);
            setcookie($this->cookie_prefix.'auth', '', $time, $this->cookie_path, $this->cookie_domain);
        }
        else
        {
            if ($this->charset != 'UTF8')
            {
                $username = ecs_iconv('UTF8', $this->charset, $username);
            }
            $sql = "SELECT " . $this->field_id . " AS user_id, secques AS salt, " . $this->field_pass . " As password ".
                   " FROM " . $this->table($this->user_table) . " WHERE " . $this->field_name . "='$username'";

            $row = $this->db->getRow($sql);

            setcookie($this->prefix.'sid', '', time()-3600, $this->cookie_path, $this->cookie_domain);
            setcookie($this->prefix.'auth', $this->authcode($row['password']."\t".$row['salt']."\t".$row['user_id'], 'ENCODE'), time() + 3600 * 24 * 30, $this->cookie_path, $this->cookie_domain);
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
        if (isset($_COOKIE[$this->cookie_prefix . 'auth']))
        {
            $arr = addslashes_deep(explode("\t", $this->authcode($_COOKIE[$this->cookie_prefix . 'auth'], 'DECODE')));
            if (count($arr) != 3)
            {
                return false;
            }
            else
            {
                list($discuz_pw, $discuz_secques, $discuz_uid) = $arr;
            }

            $sql = "SELECT " . $this->field_name ." AS user_name".
                   " FROM " . $this->table($this->user_table) .
                   " WHERE ".$this->field_id." = '$discuz_uid' AND ".$this->field_pass." = '$discuz_pw'";
            $username = $this->db->getOne($sql);
            if ($username && ($this->charset != 'UTF8'))
            {
                $username = ecs_iconv($this->charset, 'UTF8', $username);
            }

            return $username;
        }
        else
        {
            return '';
        }
    }

    /**
     * 添加新用户的函数
     *
     * @access      public
     * @param       string      username    用户名
     * @param       string      password    登录密码
     * @param       string      email       邮件地址
     * @param       string      bday        生日
     * @param       string      gender      性别
     * @return      int         返回最新的ID
     */
    function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date=0, $md5password='')
    {
        $result = parent::add_user($username, $password, $email, $gender, $bday, $reg_date, $md5password);

        if (!$result)
        {
            return false;
        }

        /* 获得默认的用户组 */
        $sql = 'SELECT groupid FROM ' .$this->table('usergroups'). ' WHERE creditshigher <= 0 AND creditslower > 0';

        $grp = $this->db->getOne($sql);

        if ($this->charset != 'UTF8')
        {
            $username = ecs_iconv('UTF8', $this->charset, $username);
        }

        /* 更新组id */
        $sql = "UPDATE " . $this->table($this->user_table) .
               " SET groupid= '$grp', ".
               " regip = '" . real_ip() . "',".
               " regdate = '" . time() . "'".
               " WHERE " . $this->field_name . "='$username'";
        $this->db->query($sql);

        /* 更新memberfields表 */
        $sql = 'INSERT INTO '. $this->table('memberfields') .' ('. $this->field_id .") " .
               " SELECT " . $this->field_id .
               " FROM " . $this->table($this->user_table) .
               " WHERE " . $this->field_name . "='$username'";
        $this->db->query($sql);

        return true;
    }


    /**
     * discuz 5.0 加密函数,从/include/global.func.php获得
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function authcode($string, $operation, $key = '')
    {
        $key = md5($key ? $key : $this->authkey);
        $key_length = strlen($key);

        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string. $key), 0, 8) . $string;
        $string_length = strlen($string);

        $rndkey = $box = array();
        $result = '';

        for ($i = 0; $i <= 255; $i++)
        {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }

        for ($j = $i = 0; $i < 256; $i++)
        {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE')
        {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8))
            {
                return substr($result, 8);
            }
            else
            {
                return '';
            }
        }
        else
        {
            return str_replace('=', '', base64_encode($result));
        }

    }
}

?>