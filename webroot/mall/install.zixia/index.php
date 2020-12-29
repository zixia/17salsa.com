<?php

/**
 * ECMALL: 安装程序控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: index.php 6071 2008-11-14 10:42:21Z Garbin $
 */

define('IN_ECM', true);

require(dirname(__FILE__) . '/includes/init.php');

$step = isset($_REQUEST['step']) ? trim($_REQUEST['step']) : 'start';

/* 检测是否已经安装 */
if (file_exists(ROOT_PATH.'/data/install.lock'))
{
    exit($lang['install_lock']);
}

if ($step == 'start')
{
    /* 判断是否push安装 */
    if (isset($_POST['ucapi']))
    {
        $push_data['ucapi'] =  $_POST['ucapi'];
        $push_data['ucfounderpw'] =  $_POST['ucfounderpw'];

        $template->assign("data", $push_data);
    }

    $template->display("install/templates/index.html");
}
elseif ($step == 'env_setting')
{
    $uc_info = array();
    $error   = array();
    $dns_error = false;

    if (!isset($_POST['save_env_setting']))
    {
        include_once(ROOT_PATH . 'install/includes/lib.env_checker.php');

        /* 需要检测权限的目录 */
        $checking_dirs = array(
                '/admin',
                '/data',
                '/temp',
                '/temp/query_caches',
                '/temp/js',
                '/temp/style',
                '/uc_client',
                '/uc_client/data/cache',
        );

        $dir_checking    = check_dirs_priv($checking_dirs);
        $system_checking = check_system_info();

        $mall_layout_dir  = ROOT_PATH . 'themes/mall/layout';
        $store_layout_dir = ROOT_PATH . 'themes/store/layout';

        /* 获取所有LAYOUT目录 */
        $templates_dir = get_layouts($mall_layout_dir);
        $templates_dir = array_merge($templates_dir, get_layouts($store_layout_dir));

        $templates_dir[] = array('type'=> 'html', 'path' => ROOT_PATH . 'themes/store/resource/');

        $templates_dir[] = array('type'=> 'html', 'path' => ROOT_PATH . 'themes/mall/resource/');

        $template_checking = check_templates_priv($templates_dir);

        /* 是否环境验证成功 */
        if ($dir_checking['result'] == "ERROR" || !empty($system_checking) || !empty($template_checking))
        {
            $template->assign('passed', false);
        }
        else
        {
            $template->assign('passed', true);
        }

        $error = array_merge($system_checking, $template_checking, $dir_checking['detail']);

        $site_url = site_url();
        $site_url = str_replace('/install', '', strtolower($site_url));
        $template->assign('site_url', $site_url);

        if (isset($_POST['ucapi']) && isset($_POST['ucfounderpw']))
        {
           $uc_info['uc_api'] = $_POST['ucapi'];
           $uc_info['pwd']    = $_POST['ucfounderpw'];
        }
        $uc_info['uc_connect'] = 'mysql';
    }
    else
    {
        $uc_info['pwd']        = trim($_POST['uc_password']);
        $uc_info['uc_api']     = trim($_POST['uc_api']);
        $uc_info['app_url']    = trim($_POST['app_url']);
        $uc_info['uc_connect'] = trim($_POST['uc_connect']);
        $uc_info['app_name']   = trim($_POST['app_name']);
        $uc_info['uc_ip']      = isset($_POST['uc_ip']) ? trim($_POST['uc_ip']) : '';

        if (empty($uc_info['pwd']) || empty($uc_info['app_url'])  || strtolower($uc_info['uc_api']) == 'http://' || empty($uc_info['app_name']) || empty($uc_info['pwd']))
        {
            $error[] = $lang['uc_info_invalid'];
        }
        else
        {
            /* 如果没有设置UCIP */
            if(empty($uc_info['uc_ip']))
            {
                $temp = @parse_url($uc_info['uc_api']);
                $uc_info['uc_ip'] = gethostbyname($temp['host']);
                if (ip2long($uc_info['uc_ip']) == -1 || ip2long($uc_info['uc_ip']) === FALSE)
                {
                    $uc_info['uc_ip'] = '';
                    $dns_error = true;
                    $error[] = $lang['dns_error'];
                }
            }

            $tmp = @ecm_fopen($uc_info['uc_api'].'/index.php?m=app&a=ucinfo', 500, '', false, 1, $uc_info['uc_ip']);
            if (!empty($tmp))
            {
                $arr = explode('|', $tmp);
                if (count($arr) > 1)
                {
                    list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = $arr;
                    define('UC_API', 1);
                    require_once(ROOT_PATH . '/uc_client/client.php');

                    /* 检测是否连接成功 */
                    if ($status != 'UC_STATUS_OK')
                    {
                        $error[] = $lang['get_ucinfo_failed'];
                    }
                    /* 检测字符集是否匹配 */
                    elseif (CHARSET != strtolower($uccharset))
                    {
                        $error[] = sprintf($lang['charset_error'], $uccharset);
                    }
                    /* 检测UC客户端版本 */
                    elseif(UC_VERSION > $ucversion)
                    {
                        $error[] = sprintf($lang['version_error'], $ucversion);
                    }
                    /* 检测UC客户端版本 */
                    elseif(strpos($apptypes, 'ECMALL') !== false)
                    {
                        $error[] = sprintf($lang['app_exists'], $ucversion);
                    }
                }
                else
                {
                    $error[] = $lang['get_ucinfo_failed'];
                }
            }
            else
            {
                $error[] = $lang['get_ucinfo_failed'];
            }

            /* 如果连接UC Server没有错误 */
            if (empty($error) && !$dns_error)
            {
                $app_type = 'ECMALL';

                /* tag 模板 */
                $app_tagtemplates = 'apptagtemplates[template]='.urlencode('<dl><dt>{goods_name}</dt><dd><a href="{url}"><img src="{image}"></a></dd><dd>{goods_price}</dd></dl>').'&'.
                                    'apptagtemplates[fields][goods_name]='.urlencode($lang['apptagtemplates']['goods_name']).'&'.
                                    'apptagtemplates[fields][uid]='.urlencode($lang['apptagtemplates']['uid']).'&'.
                                    'apptagtemplates[fields][username]='.urlencode($lang['apptagtemplates']['username']).'&'.
                                    'apptagtemplates[fields][dateline]='.urlencode($lang['apptagtemplates']['dateline']).'&'.
                                    'apptagtemplates[fields][url]='.urlencode($lang['apptagtemplates']['url']) . '&'.
                                    'apptagtemplates[fields][image]='.urlencode($lang['apptagtemplates']['image']) . '&'.
                                    'apptagtemplates[fields][goods_price]='.urlencode($lang['apptagtemplates']['goods_price']);

                $postdata = 'm=app&a=add&ucfounder=&ucfounderpw='.urlencode($uc_info['pwd']).
                            '&apptype='.urlencode($app_type).'&appname='.urlencode($uc_info['app_name']).
                            '&appurl='.urlencode($uc_info['app_url']).'&appip=&appcharset='.CHARSET.
                            '&appdbcharset='.CHARSET.'&'.$app_tagtemplates;

                $uc_config = @ecm_fopen($uc_info['uc_api'].'/index.php', 500, $postdata, '', 1, $uc_info['uc_ip']);

                $arr = explode('|', $uc_config);
            }
        }
        /* 如果添加应用成功则将UC配置信息写入配置文件 */
        if (!empty($uc_config) && $uc_config != '-1' && count($arr) > 0)
        {
            $conf['LANG']          = $_lang;
            $conf['UC_DBCHARSET']  = $arr[6];
            $conf['UC_DBTABLEPRE'] = '`'. $arr[3] .'`.' . $arr[7];
            $conf['UC_KEY']        = $arr[0];
            $conf['UC_APPID']      = $arr[1];
            $conf['UC_DBHOST']     = $arr[2];
            $conf['UC_DBNAME']     = $arr[3];
            $conf['UC_DBUSER']     = $arr[4];
            $conf['UC_DBPW']       = $arr[5];
            $conf['UC_CHARSET']    = $arr[8];

            $conf['UC_API']        = $uc_info['uc_api'];
            $conf['UC_PATH']       = 'uc_client';
            $conf['UC_CONNECT']    = $uc_info['uc_connect'];
            $conf['UC_IP']         = $uc_info['uc_ip'];
            $conf['UC_DBCONNECT']  = '0';

            /* 验证UC数据库是否连接成功 */
            if ($conf['UC_CONNECT'] == 'mysql')
            {
                $link = @mysql_connect($conf['UC_DBHOST'], $conf['UC_DBUSER'], $conf['UC_DBPW'], 1);
                $res = $link && mysql_select_db($conf['UC_DBNAME'], $link) ? true : false;
            }

            if ($conf['UC_CONNECT'] == '' || ($conf['UC_CONNECT'] == 'mysql' && $res))
            {
                /* 生成 UC 配置文件 */
                create_uc_config($conf);

                header("location:./index.php?step=base_setting&language_type=".$_lang);
                exit;
            }
            else
            {
                $error[] = $lang['uc_db_error'];
            }
        }
        elseif (!empty($uc_config))
        {
            if ($uc_config != '-1')
            {
                $error[] = $lang['connect_error'];
            }
            else
            {
                $error[] = $lang['password_error'];
            }
        }

        $template->assign('passed', 1);
    }

    $template->assign('uc_info'  , $uc_info);
    $template->assign('dns_error', $dns_error);
    $template->assign('error'    , $error);
    $template->display("install/templates/env_setting.html");
}
elseif ($step == 'check_db_name')
{
    $db_host = trim($_POST['db_host']);
    $db_port = trim($_POST['db_port']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pwd']);
    $db_name = trim($_POST['db_name']);
    $db_tablepre = trim($_POST['db_tablepre']);
    $db_host = construct_db_host($db_host, $db_port);
    $conn = @mysql_connect($db_host, $db_user, $db_pass);

    if ($conn)
    {
        if (mysql_select_db($db_name))
        {
            if (mysql_query("SELECT * FROM `$db_name`.`{$db_tablepre}config_item` LIMIT 1"))
            {
                echo '<scr' . 'ipt>';
                echo "confirm('".$lang['ecmall_exists']."');";
                echo '</scr' . 'ipt>';
            }
        }
    }

}
elseif ($step == 'base_setting')
{
    /* 基本信息设置 */
    if (isset($_POST['save_base_setting']))
    {
        $db_error         = array();
        $admin_user_error = array();

        $info['db_host']      = trim($_POST['db_host']);
        $info['db_port']      = trim($_POST['db_port']);
        $info['db_user']      = trim($_POST['db_user']);
        $info['db_pass']      = trim($_POST['db_pwd']);
        $info['db_name']      = trim($_POST['db_name']);
        $info['db_tablepre']  = trim($_POST['db_tablepre']);
        $info['forceinstall'] = isset($_POST['forceinstall']) ? 1 : 0;
        $info['username']         = trim($_POST['username']);
        $info['password']         = trim($_POST['password']);
        $info['email']            = trim($_POST['email']);
        $info['confirm_password'] = trim($_POST['confirm_password']);
        $info['userradio']        = trim($_POST['userradio']);

        $info['goods_type']   = isset($_POST['goods_type']) ? $_POST['goods_type'] : array();
        $info['install_demo'] = isset($_POST['install_demo']) ? trim($_POST['install_demo']) : 0;

        $db_host = construct_db_host($info['db_host'], $info['db_port']);

        /* 验证数据库连接只否成功 */
        $conn = @mysql_connect($db_host, $info['db_user'], $info['db_pass']);

        if (!$conn || empty($info['db_user']) || empty($info['db_host']))
        {
            $db_error[] = $lang['db_connect_failed'];
        }
        else
        {
            if (!mysql_select_db($info['db_name']))
            {
                if (!empty($info['db_name']))
                {
                    $res = create_database($info['db_name']);
                    if (!$res)
                    {
                        $db_error[] = $lang['create_db_failed'];
                    }
                }
                else
                {
                    $db_error[] = $lang['db_name_invalid'];
                }
            }

            @mysql_close($conn);
        }


        $invalid = false;
        if ($_POST['userradio'] == '0')
        {
            $invalid = (empty($info['email']) || empty($info['confirm_password']));
        }

        if ((empty($info['username']) || empty($info['password'])) || $invalid)
        {
            $admin_user_error[] = $lang['admin_user_invalid'];

        }
        else
        {
            include_once(ROOT_PATH . '/data/inc.config.php');
            include_once(ROOT_PATH . '/uc_client/client.php');

            if ($_POST['userradio'] == '1')
            {
                $uc_res = uc_user_login($info['username'], $info['password']);

                if ($uc_res[0] < 0)
                {
                    $admin_user_error[] = $lang['admin_password_error'];
                }
                else
                {
                    list($user_info['uid'], $user_info['username'], $user_info['password'], $user_info['email']) = uc_addslashes($uc_res);
                }
            }
            else
            {
                if ($info['confirm_password'] != $info['password'])
                {
                    $admin_user_error[] = $lang['admin_confirm_password_invalid'];
                }
                else
                {
                    $user_info = check_user($info['username'], $info['password'], $info['email']);

                    if (!empty($user_info['error']))
                    {
                        $admin_user_error[] = $lang[$user_info['error']];
                    }
                }
            }
        }

        if (!empty($db_error) || !empty($admin_user_error))
        {
            $template->assign('db_error',         $db_error);
            $template->assign('admin_user_error', $admin_user_error);
            $template->assign('info', $info);
        }
        else
        {
            /* 生成配置文件 */
            create_config_file($info['db_host'], $info['db_port'], $info['db_user'], $info['db_pass'], $info['db_name'], $info['db_tablepre'], $_lang, '', '');

            $info = array_merge($info, $user_info);
            $template->assign('options', $info);

            $template->display("install/templates/succeed.html");
            exit;
        }
    }
    else
    {
        $info = array('db_port' => '3306',
                      'db_host' => 'localhost',
                      'db_name' => 'ecmall',
                      'db_tablepre' => 'ecm_'
                     );
        $template->assign("info", $info);
    }

    $template->display("install/templates/base_setting.html");
}
elseif ($step == "install_data")
{

    /* 安装数据结构以及初始数据 */
    include_once(ROOT_PATH . '/data/inc.config.php');
    include_once(ROOT_PATH . '/uc_client/client.php');
    include_once(ROOT_PATH . '/includes/manager/mng.base.php');
    include_once(ROOT_PATH . '/includes/manager/mng.nav.php');
    include_once(ROOT_PATH . '/includes/inc.constant.php');

    $db = db();

    /* 安装数据结构 */
    $structure = array(ROOT_PATH . 'install/data/structure.sql');

    if (!install_data($structure))
    {
        install_failed();
        log_write("install data structure", 'failed');
        exit;
    }

    /* 安装初始数据 */
    show_js_message($lang['initialize_data']);

    $sql_files = array( ROOT_PATH . 'install/data/'. LANG .'.sql');
    if (!install_data($sql_files))
    {
        install_failed();
        log_write("install initial data", 'failed');
        exit;
    }

    if (!install_docs(LANG))
    {
        install_failed();
        log_write("install documents", 'failed');
        exit;
    }

    $app_list = uc_app_ls();

    if (!empty($app_list))
    {
        show_js_message($lang['get_ucenter_data']);
        $nav_mng = new NavManager(0);
        foreach($app_list AS $app)
        {
            if ($app['appid'] != UC_APPID && $app['type'] != 'ECMALL')
            {
                $nav['nav_url']      = $app['url'];
                $nav['nav_name']     = $app['name'];
                $nav['nav_position'] = 'middle';
                $nav['if_show']      = 1;
                $nav['is_app']       = $app['appid'];
                $nav_mng->add($nav);
            }
        }
    }

    /* 安装预选商品类型 */
    if (isset($_POST['goods_type']))
    {
        install_goods_types($_POST['goods_type'], LANG);
    }

    $demo_info = $lang['succeed_info'];

    /* 安装测试数据 */
    if (!empty($_POST['install_demo']))
    {
        show_js_message($lang['install_demo']);
        $demo_username = 'ecmall';

        for ($i = 0; $i < 10; $i++)
        {
            if ($i > 0)
            {
                $demo_username .= rand(1,30);
            }

            $tester_info = check_user($demo_username, 'demo', $demo_username.'@domain.com');
            if (empty($tester_info['error']))
            {
                break;
            }
        }
        if (empty($tester_info['error']))
        {
            /* 测试帐号 */
            $sql = "INSERT INTO `ecm_users`(user_id, user_name, email, reg_time)" .
            " VALUES('$tester_info[uid]', '$demo_username', '$demo_username@domain.com', ". time() .")";

            $GLOBALS['db']->query($sql);

            $tester_id = $tester_info['uid'];

            $sql = "INSERT INTO `ecm_admin_user`(user_id, privilege, store_id, real_name)" .
            " VALUES('$tester_info[uid]', 'all', '$tester_info[uid]', 'emall')";
            $GLOBALS['db']->query($sql);
            if (install_demo(LANG))
            {
                copy_files(ROOT_PATH . '/install/data/demo/images/demo/',  'data/user_files/demo/');
            }
            else
            {
                log_write("install test data", 'failed');
            }
        }
        else
        {
            log_write("register test user", 'failed');
        }
        $temp = $lang['demo_installed'];
        $temp = sprintf($temp, $demo_username, 'demo');
        $demo_info = sprintf($lang['succeed_info'], $temp);
    }
    else
    {
        $demo_info = sprintf($demo_info, '');
    }
    /* 更新版本号 */
    $sql = "UPDATE `ecm_config_value` SET value = '". VERSION ."' WHERE code = 'mall_version'";
    $GLOBALS['db']->query($sql);

    /* 完成安装 */
    install_succeed($demo_info);
}
else
{
    header("location:./index.php\n");
}

?>
