<?php

/**
 * ECMALL: 安装程序函数库
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Id: lib.install.php 6024 2008-11-03 06:29:46Z Garbin $
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

/**
 * 获得GD的版本号
 *
 * @access  public
 * @return  string     返回版本号，可能的值为0，1，2
 */
function get_gd_version()
{
    include_once(ROOT_PATH . 'includes/cls.image.php');

    return imageProcessor::gd_version();
}

/**
 * 是否支持GD
 *
 * @access  public
 * @return  boolean     成功返回true，失败返回false
 */
function has_supported_gd()
{
    return get_gd_version() === 0 ? false : true;
}

/**
 * 检测服务器上是否存在指定的文件类型
 *
 * @access  public
 * @param   array     $file_types        文件路径数组，形如array('dwt'=>'', 'lbi'=>'', 'dat'=>'')
 * @return  string    全部可写返回空串，否则返回以逗号分隔的文件类型组成的消息串
 */
function file_types_exists($file_types)
{
    global $lang;

    $msg = '';
    foreach ($file_types as $file_type => $file_path)
    {
        if (!file_exists($file_path))
        {
            $msg .= $lang['cannt_support_' . $file_type] . ', ';
        }
    }

    $msg = preg_replace("/,\s*$/", '', $msg);

    return $msg;
}


/**
 * 检查数据库是否存在
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @param   string      $db_name        数据库名
 * @return  boolean     成功返回true，失败返回false
 */
function database_exists($db_name)
{
    return mysql_query('USE '.$db_name);
}

/**
 * 创建指定名字的数据库
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @param   string      $db_name        数据库名
 * @return  boolean     成功返回true，失败返回false
 */
function create_database($db_name)
{
    $mysql_version = mysql_get_server_info($GLOBALS['conn']);
    $charset = str_replace('-', '', CHARSET);
    if (mysql_select_db($db_name) === false)
    {
        $sql = $mysql_version >= '4.1' ? "CREATE DATABASE `$db_name` DEFAULT CHARACTER SET ". $charset ."" : "CREATE DATABASE `$db_name`";
        if (mysql_query($sql) === false)
        {
            return false;
        }
    }

    return true;
}

/**
 * 保证进行正确的数据库连接（如字符集设置）
 *
 * @access  public
 * @param   string      $conn                      数据库连接
 * @param   string      $mysql_version        mysql版本号
 * @return  void
 */
function keep_right_conn($conn, $mysql_version='')
{
    if ($mysql_version === '')
    {
        $mysql_version = mysql_get_server_info($conn);
    }
    $charset = str_replace('-', '', CHARSET);
    if ($mysql_version >= '4.1')
    {
        mysql_query('SET character_set_connection='. $charset .', character_set_results=$charset, character_set_client=binary', $conn);

        if ($mysql_version > '5.0.1')
        {
            mysql_query("SET sql_mode=''", $conn);
        }
    }
}

/**
 * 创建配置文件
 *
 * @author  liupeng
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @param   string      $db_name        数据库名
 * @param   string      $prefix         数据表前缀
 * @param   string      $timezone       时区
 * @return  boolean     成功返回true，失败返回false
 */
function create_config_file($db_host, $db_port, $db_user, $db_pass, $db_name, $prefix, $lang_type, $cookie_domain, $cookie_path)
{
    global $err, $lang;

    $filename = ROOT_PATH . '/data/inc.config.php';

    if (!file_exists($filename))
    {
        return false;
    }

    $ecm_key = md5(ROOT_PATH . time() . site_url() . rand());

    include_once($filename);

    //获取主站二级域名
    $site_url = site_url();
    $pos1 = strpos($site_url, '/') + 2;
    $pos2 = strpos($site_url, '.', $pos1);
    if ($pos1 && $pos2 && (($pos2-$pos1) > 0))
    {
        $site_domain = substr($site_url, $pos1, $pos2 - $pos1);
    }
    $main_domain = substr($site_url, 0, strlen($site_url) - 7); //将url 后面的install目录去掉

    $content = "<?php\r\n/*{$lang[comment][config]}*/\r\n\r\n";
    $content .= "define('DB_CONFIG',        'mysql://" . urlencode($db_user) . ':' . urlencode($db_pass) . "@$db_host:$db_port/$db_name'); //{$lang[comment][db_config]}\r\n";
    $content .= "define('DB_PREFIX',        '$prefix'); // {$lang[comment][db_prefix]}\r\n";
    $content .= "define('LANG',             '$lang_type'); // {$lang[comment][lang]}\r\n";
    $content .= "define('COOKIE_DOMAIN',    '$cookie_domain'); // {$lang[comment][cookie_domain]}\r\n";
    $content .= "define('COOKIE_PATH',      '/'); // {$lang[comment][cookie_path]}\r\n";
    $content .= "define('EMPTY_REFERER',    1); // {$lang[comment][empty_referer]}\r\n";
    $content .= "define('ECM_KEY',          '$ecm_key'); // {$lang[comment][ecm_key]}\r\n";
    $content .= "define('ENABLED_GZIP',     0); // {$lang[comment][gzip]}\r\n";
    $content .= "define('CURRENCY', 'CNY');     // {$lang[comment][currency]}\r\n";
    $content .= "define('DEBUG_MODE',       0); // {$lang[comment][debug_mode]}\r\n";
    $content .= "define('MAIN_DOMAIN',      '$main_domain'); // {$lang[comment][main_domain]}\r\n";
    $content .= "define('ENABLED_CUSTOM_DOMAIN',0); // {$lang[comment][enabled_custom_domain]}\r\n";
    $content .= "define('DENY_DOMAIN',      'www,$site_domain'); // {$lang[comment][deny_domain]}\r\n\r\n";

    $content .= "/* UCenter Configuration */\r\n";
    $content .= "define('UC_DBCHARSET',     '". UC_DBCHARSET ."');  // {$lang[comment][uc_dbcharset]}\r\n";
    $content .= "define('UC_DBTABLEPRE',    '". UC_DBTABLEPRE ."'); // {$lang[comment][uc_dbtablepre]}\r\n";
    $content .= "define('UC_KEY',           '". UC_KEY ."'); // {$lang[comment][uc_key]}\r\n";
    $content .= "define('UC_APPID',         '". UC_APPID ."'); // {$lang[comment][uc_appid]}\r\n";
    $content .= "define('UC_DBHOST',        '". UC_DBHOST ."'); // {$lang[comment][uc_dbhost]}\r\n";
    $content .= "define('UC_DBNAME',        '". UC_DBNAME ."'); // {$lang[comment][uc_dbname]}\r\n";
    $content .= "define('UC_DBUSER',        '". UC_DBUSER ."'); // {$lang[comment][uc_dbuser]}\r\n";
    $content .= "define('UC_DBPW',          '". UC_DBPW ."'); // {$lang[comment][uc_dbpw]}\r\n";
    $content .= "define('UC_CHARSET',       '". UC_CHARSET ."'); // {$lang[comment][uc_charset]}\r\n";
    $content .= "define('UC_API',           '". UC_API ."'); // {$lang[comment][uc_api]}\r\n";
    $content .= "define('UC_PATH',          '". UC_PATH ."'); // {$lang[comment][uc_path]}\r\n";
    $content .= "define('UC_CONNECT',       '". UC_CONNECT ."'); // {$lang[comment][uc_connect]}\r\n";
    $content .= "define('UC_IP',            '". UC_IP ."'); // {$lang[comment][uc_ip]}\r\n";
    $content .= "define('UC_DBCONNECT',     '". UC_DBCONNECT ."'); // {$lang[comment][uc_dbconnect]}\r\n";
    $content .= "\r\n?>";

    file_put_contents($filename, $content);
    return true;
}

function create_uc_config($config)
{
    $ln = "\r\n";
    $content = "<?php $ln$ln";
    foreach($config AS $key => $value)
    {
        $content .= "define('$key', '$value');$ln";
    }
    $content .= "$ln$ln?>";

    file_put_contents(ROOT_PATH . "/data/inc.config.php", $content);
}

/**
 * 把host、port重组成指定的串
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @return  string      host、port重组后的串，形如host:port
 */
function construct_db_host($db_host, $db_port)
{
    return $db_host . ':' . $db_port;
}

/**
 * 安装数据
 *
 * @author  liupeng
 * @param   array         $sql_files        SQL文件路径组成的数组
 * @return  boolean       成功返回true，失败返回false
 */
function install_data($sql_files)
{
    include_once(ROOT_PATH . 'install/includes/cls.sql_executor.php');
    $charset = str_replace('-', '', CHARSET);

    $se = new sql_executor($GLOBALS['db'], $charset, 'ecm_', DB_PREFIX);
    $result = $se->run_all($sql_files);

    if ($result === false)
    {
        log_write($se->error);
        return false;
    }
    return true;
}

function show_js_message($str)
{
    echo "<script>parent.showInfo(\"$str\");</script>";
    flush();
    ob_flush();
}

/**
 * 安装测试数据
 *
 * @author  liupeng
 * @param   string     $lang_type  语言
 * @return  boolean    成功返回true，失败返回false
 */
function install_demo($lang_type)
{
    $charset = str_replace('-', '', CHARSET);

    $sql = file_get_contents(ROOT_PATH . '/install/data/demo/'.$lang_type.'.sql');

    $se = new sql_executor($GLOBALS['db'], $charset, 'ecm_', DB_PREFIX);

    $sql = str_replace('{uid}', $GLOBALS['tester_id'], $sql);
    $sql = str_replace('{time}', time(), $sql);
    $se->load_string($sql);

    $result = $se->run();

    if ($result == false)
    {
        log_write($se->error);
    }

    return $result;
}

/**
 * 安装默认的内置文档
 *
 * @author  wj
 * @param   string  $lang_type
 *
 * @return  boolean
 */
function install_docs($lang_type)
{
    $docs = array('eula', 'certification', 'reputation', 'msn_privacy', 'setup_store');

    foreach ($docs AS $val)
    {
        $filename = ROOT_PATH . 'install/data/docs/' .$lang_type. '/' .$val. '.txt';
        if (is_file($filename))
        {
            $content    = addslashes(file_get_contents($filename));
            $title      = $GLOBALS['lang']['docs'][$val];

            $sql        = "INSERT INTO `ecm_article` (`article_id`, `store_id`, `cate_id`, `title`, `content`, `code`, `add_time`) VALUES ".
                            "(NULL, 0, 0, '$title', '$content', '" .strtoupper($val). "', " .time(). ")";

            if (!$GLOBALS['db']->query($sql, 'SILENT'))
            {
                break;
                return false;
            }
        }
    }

    return true;
}

/**
 * 安装预选商品类型
 *
 * @access  public
 * @param   array      $goods_types     预选商品类型
 * @param   string     $lang            语言
 * @return  boolean    成功返回true，失败返回false
 */
function install_goods_types($goods_types, $lang)
{
    global $err;

    if (!$goods_types)
    {
        return true;
    }

    if (file_exists(ROOT_PATH . 'install/data/'. $lang .'_goods_type.php'))
    {
        include(ROOT_PATH . 'install/data/'. $lang .'_goods_type.php');
    }
    else
    {
        include(ROOT_PATH . 'install/data/sc_goods_type.php');
    }


    foreach ($attributes as $key=>$val)
    {
        if (!in_array($key, $goods_types))
        {
            continue;
        }

        if (!$GLOBALS['db']->query($val['type'], 'SILENT'))
        {
            return false;
        }
        $cat_id = $GLOBALS['db']->insert_id();

        $sql = str_replace("{type_id}", $cat_id, $val['attr']);
        if (!$GLOBALS['db']->query($sql, 'SILENT'))
        {
            return false;
        }
    }

    return true;
}

/**
 * 把一个文件从一个目录复制到另一个目录
 *
 * @access  public
 * @param   string      $source    源目录
 * @param   string      $target    目标目录
 * @return  boolean     成功返回true，失败返回false
 */
function copy_files($source, $target)
{
    global $err, $_LANG;

    if (!file_exists($target))
    {
        if (!ecm_mkdir(rtrim($target, '/')))
        {
            return false;
        }
    }
    $dir = opendir($source);
    while (($file = @readdir($dir)) !== false)
    {
        if (is_file($source . $file))
        {
            if (!copy($source . $file, ROOT_PATH. $target . $file))
            {
                return false;
            }
            chmod($target . $file, 0777);
        }
    }
    closedir($dir);
    return true;
}

/**
 * 完成安装
 *
 * author  liupeng
 * access  public
 * return  void
 */
function install_succeed($info)
{
    global $lang;
    /* 写入文件锁 */
    file_put_contents(ROOT_PATH . '/data/install.lock', gmtime());

    /* 创建管理员 */
    $sql = "INSERT INTO `ecm_users`(user_id, user_name, email, reg_time)" .
            " VALUES('$_POST[uid]', '$_POST[username]', '$_POST[email]', ". gmtime() .")";
    $GLOBALS['db']->query($sql);

    $sql = "INSERT INTO `ecm_admin_user`(user_id, privilege, real_name)" .
            " VALUES('$_POST[uid]', 'all', '$_POST[username]')";
    $GLOBALS['db']->query($sql);

    /* 生成SITE_ID */
    $site_id = product_id();
    $sql = "UPDATE `ecm_config_value` SET value='$site_id' WHERE code='mall_site_id'";
    $GLOBALS['db']->query($sql);
    /* 清空缓存 */
    clean_cache();


    echo "<script type=\"text/javascript\">parent.install_result('$lang[mall_install_succeed]', '$info');</script>";
}

/**
 * 安装失败
 *
 * @access  public
 * @return  void
 */
function install_failed()
{
    global $lang;
    $info = $lang['failed_info'];

    show_js_message($lang['mall_install_failed']);
    echo "<script type=\"text/javascript\">parent.install_result('','$info', true);</script>";
}

/**
 * 获取SITEID
 *
 * author  liupeng
 * param   string  $type  产品类型
 * return  void
 */
function product_id($type = 'EM') {
    $type = strtoupper($type);
    $type = in_array($type, array('DZ', 'UC', 'UH', 'EC', 'EM', 'XS', 'SS', 'SV')) ? $type : 'DZ';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $productid = $type.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_SERVER['REMOTE_ADDR']),-2);
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    $max = strlen($chars) - 1;
    for($i = 0; $i < 6; $i++)
    {
        $productid .= $chars[mt_rand(0, $max)];
    }
    return $productid;
}

/**
 * 调用UC API 检测并注册用户
 *
 * author liupeng
 * param  string  $username  用户名
 * param  string  $password  密码
 * param  string  $email  邮箱
 * return string
 */
function check_user($username, $password, $email) {

    include_once(ROOT_PATH . '/data/inc.config.php');
    include_once(ROOT_PATH . '/uc_client/client.php');
    $error = '';
    $uid = uc_user_register($username, $password, $email);

    /*
    -1 : 用户名不合法
    -2 : 包含不允许注册的词语
    -3 : 用户名已经存在
    -4 : email 格式有误
    -5 : email 不允许注册
    -6 : 该 email 已经被注册
    >1 : 表示成功，数值为 UID
    */
    if($uid == -1 || $uid == -2)
    {
        $error = 'admin_username_invalid';
    }
    elseif ($uid == -4 || $uid == -5 || $uid == -6)
    {
        $error = 'admin_email_invalid';
    }
    elseif($uid == -3)
    {
        $ucresult = uc_user_login($username, $password);
        list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email']) = uc_addslashes($ucresult);
        $ucresult = $tmp;
        if($ucresult['uid'] <= 0)
        {
            $error = 'admin_exist_password_error';
        }
        else
        {
            $uid = $ucresult['uid'];
            $email = $ucresult['email'];
            $password = $ucresult['password'];
        }
    }

    if(!$error && $uid > 0)
    {
        $password = md5($password);
        uc_user_addprotected($username, '');
    }
    else
    {
        $uid = 0;
        $error = empty($error) ? 'error_unknow_type' : $error;
    }
    return array('uid' => $uid, 'username' => $username, 'password' => $password, 'email' => $email, 'error' => $error);
}

/**
 * 写入Log
 *
 * @author  liupeng
 * @param   string  $action  操作
 * @param   string  $result  成功与否
 * @return  void
 */
function log_write($action, $result)
{
    $text = "$action $result ". date("Y-m-d H:i:s", time()) ."\n";
    file_put_contents(ROOT_PATH . "temp/install.log", $text, FILE_APPEND);
}

?>
