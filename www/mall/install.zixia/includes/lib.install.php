<?php

/**
 * ECMALL: ��װ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: lib.install.php 6024 2008-11-03 06:29:46Z Garbin $
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

/**
 * ���GD�İ汾��
 *
 * @access  public
 * @return  string     ���ذ汾�ţ����ܵ�ֵΪ0��1��2
 */
function get_gd_version()
{
    include_once(ROOT_PATH . 'includes/cls.image.php');

    return imageProcessor::gd_version();
}

/**
 * �Ƿ�֧��GD
 *
 * @access  public
 * @return  boolean     �ɹ�����true��ʧ�ܷ���false
 */
function has_supported_gd()
{
    return get_gd_version() === 0 ? false : true;
}

/**
 * �����������Ƿ����ָ�����ļ�����
 *
 * @access  public
 * @param   array     $file_types        �ļ�·�����飬����array('dwt'=>'', 'lbi'=>'', 'dat'=>'')
 * @return  string    ȫ����д���ؿմ������򷵻��Զ��ŷָ����ļ�������ɵ���Ϣ��
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
 * ������ݿ��Ƿ����
 *
 * @access  public
 * @param   string      $db_host        ����
 * @param   string      $db_port        �˿ں�
 * @param   string      $db_user        �û���
 * @param   string      $db_pass        ����
 * @param   string      $db_name        ���ݿ���
 * @return  boolean     �ɹ�����true��ʧ�ܷ���false
 */
function database_exists($db_name)
{
    return mysql_query('USE '.$db_name);
}

/**
 * ����ָ�����ֵ����ݿ�
 *
 * @access  public
 * @param   string      $db_host        ����
 * @param   string      $db_port        �˿ں�
 * @param   string      $db_user        �û���
 * @param   string      $db_pass        ����
 * @param   string      $db_name        ���ݿ���
 * @return  boolean     �ɹ�����true��ʧ�ܷ���false
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
 * ��֤������ȷ�����ݿ����ӣ����ַ������ã�
 *
 * @access  public
 * @param   string      $conn                      ���ݿ�����
 * @param   string      $mysql_version        mysql�汾��
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
 * ���������ļ�
 *
 * @author  liupeng
 * @param   string      $db_host        ����
 * @param   string      $db_port        �˿ں�
 * @param   string      $db_user        �û���
 * @param   string      $db_pass        ����
 * @param   string      $db_name        ���ݿ���
 * @param   string      $prefix         ���ݱ�ǰ׺
 * @param   string      $timezone       ʱ��
 * @return  boolean     �ɹ�����true��ʧ�ܷ���false
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

    //��ȡ��վ��������
    $site_url = site_url();
    $pos1 = strpos($site_url, '/') + 2;
    $pos2 = strpos($site_url, '.', $pos1);
    if ($pos1 && $pos2 && (($pos2-$pos1) > 0))
    {
        $site_domain = substr($site_url, $pos1, $pos2 - $pos1);
    }
    $main_domain = substr($site_url, 0, strlen($site_url) - 7); //��url �����installĿ¼ȥ��

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
 * ��host��port�����ָ���Ĵ�
 *
 * @access  public
 * @param   string      $db_host        ����
 * @param   string      $db_port        �˿ں�
 * @return  string      host��port�����Ĵ�������host:port
 */
function construct_db_host($db_host, $db_port)
{
    return $db_host . ':' . $db_port;
}

/**
 * ��װ����
 *
 * @author  liupeng
 * @param   array         $sql_files        SQL�ļ�·����ɵ�����
 * @return  boolean       �ɹ�����true��ʧ�ܷ���false
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
 * ��װ��������
 *
 * @author  liupeng
 * @param   string     $lang_type  ����
 * @return  boolean    �ɹ�����true��ʧ�ܷ���false
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
 * ��װĬ�ϵ������ĵ�
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
 * ��װԤѡ��Ʒ����
 *
 * @access  public
 * @param   array      $goods_types     Ԥѡ��Ʒ����
 * @param   string     $lang            ����
 * @return  boolean    �ɹ�����true��ʧ�ܷ���false
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
 * ��һ���ļ���һ��Ŀ¼���Ƶ���һ��Ŀ¼
 *
 * @access  public
 * @param   string      $source    ԴĿ¼
 * @param   string      $target    Ŀ��Ŀ¼
 * @return  boolean     �ɹ�����true��ʧ�ܷ���false
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
 * ��ɰ�װ
 *
 * author  liupeng
 * access  public
 * return  void
 */
function install_succeed($info)
{
    global $lang;
    /* д���ļ��� */
    file_put_contents(ROOT_PATH . '/data/install.lock', gmtime());

    /* ��������Ա */
    $sql = "INSERT INTO `ecm_users`(user_id, user_name, email, reg_time)" .
            " VALUES('$_POST[uid]', '$_POST[username]', '$_POST[email]', ". gmtime() .")";
    $GLOBALS['db']->query($sql);

    $sql = "INSERT INTO `ecm_admin_user`(user_id, privilege, real_name)" .
            " VALUES('$_POST[uid]', 'all', '$_POST[username]')";
    $GLOBALS['db']->query($sql);

    /* ����SITE_ID */
    $site_id = product_id();
    $sql = "UPDATE `ecm_config_value` SET value='$site_id' WHERE code='mall_site_id'";
    $GLOBALS['db']->query($sql);
    /* ��ջ��� */
    clean_cache();


    echo "<script type=\"text/javascript\">parent.install_result('$lang[mall_install_succeed]', '$info');</script>";
}

/**
 * ��װʧ��
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
 * ��ȡSITEID
 *
 * author  liupeng
 * param   string  $type  ��Ʒ����
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
 * ����UC API ��Ⲣע���û�
 *
 * author liupeng
 * param  string  $username  �û���
 * param  string  $password  ����
 * param  string  $email  ����
 * return string
 */
function check_user($username, $password, $email) {

    include_once(ROOT_PATH . '/data/inc.config.php');
    include_once(ROOT_PATH . '/uc_client/client.php');
    $error = '';
    $uid = uc_user_register($username, $password, $email);

    /*
    -1 : �û������Ϸ�
    -2 : ����������ע��Ĵ���
    -3 : �û����Ѿ�����
    -4 : email ��ʽ����
    -5 : email ������ע��
    -6 : �� email �Ѿ���ע��
    >1 : ��ʾ�ɹ�����ֵΪ UID
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
 * д��Log
 *
 * @author  liupeng
 * @param   string  $action  ����
 * @param   string  $result  �ɹ����
 * @return  void
 */
function log_write($action, $result)
{
    $text = "$action $result ". date("Y-m-d H:i:s", time()) ."\n";
    file_put_contents(ROOT_PATH . "temp/install.log", $text, FILE_APPEND);
}

?>
