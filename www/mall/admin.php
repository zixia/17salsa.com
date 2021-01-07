<?php

/**
 * ECMALL: 后台管理入口
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: admin.php 6009 2008-10-31 01:55:52Z Garbin $
 */
unset($GLOBALS, $_ENV);

define('IN_ECM',        true);
define('IS_BACKEND',    true);
define('PAGE_STARTED',  (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime());
define('ROOT_PATH',     dirname(__FILE__)); // 取得ecmall所在的根目录

$app_start_time = (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime();
/* 定义PHP_SELF常量 */
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF',  htmlentities($php_self));
define('ROOT_DIR',  substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')));

$query_string = isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : $_SERVER['QUERY_STRING'];
if (!isset($_SERVER['REQUEST_URI']))
{
    $_SERVER['REQUEST_URI'] = PHP_SELF . '?' . $query_string;
}
else
{
    if (strpos($_SERVER['REQUEST_URI'], '?') === false && $query_string)
    {
        $_SERVER['REQUEST_URI'] .= '?' . $query_string;
    }
}

require(ROOT_PATH. '/includes/manager/mng.base.php');
require(ROOT_PATH. '/includes/models/mod.base.php');
require(ROOT_PATH. '/includes/ctl.backend.php'); // 包含控制器基类
require(ROOT_PATH. '/includes/inc.init.php');

//当开启二级域名时，只允许用户主域名访问后台
if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN && (!is_main_site()))
{
    $add_param = empty($query_string) ? '/admin.php' : '/admin.php?' . $query_string;
    header('Location: ' . trim(MAIN_DOMAIN, '/') . $add_param . "\n");
    exit;
}

/* 根据请求的URL中的参数调用相应的对象 */
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']): '';
$donot_need_login = array('profile|captcha', 'profile|logout');

if (empty($_SESSION['admin_id']) &&
    ! in_array($_REQUEST['app']. '|' .$_REQUEST['act'], $donot_need_login))
{
    define('APPLICATION', 'profile');
    $act = "login";
}
else
{
    define('APPLICATION', (isset($_REQUEST['app']) ? trim($_REQUEST['app']): 'home'));
    /* 检查referer是否为本站的后台地址 */
    $backend_dir = site_url() . '/admin.php';
    if ((empty($_SERVER['HTTP_REFERER']) && EMPTY_REFERER === 0) &&
        substr($_SERVER['HTTP_REFERER'], 0, strlen($backend_dir)) != $backend_dir)
    {
        die('Hack Attemping.');
    }
}

/* 过滤非法的请求 */
$allowed_app = array('about', 'ad', 'ad_position', 'admin', 'appsetting', 'article', 'wanted', 'attribute',
    'brand', 'builtinarticle', 'category', 'conf', 'coupon', 'cycleimage', 'goods', 'goods_swap', 'goodstype',
    'groupbuy', 'home', 'mailtemplate', 'message', 'nav', 'order', 'partner', 'payment', 'profile',
    'region', 'shipping', 'store', 'storeapply', 'store_nav', 'template', 'user', 'statistics', 'sitemap', 'badwords',
    'notify', 'rentscheme', 'datacall', 'storerelet');
if (!in_array(APPLICATION, $allowed_app))
{
    die('Hack Attemping');
}

$app_file   = ($_SESSION['store_id'] == 0) ?
    ROOT_PATH. '/admin/mall/' .APPLICATION. '.php':
    ROOT_PATH. '/admin/store/' .APPLICATION. '.php';
$app_class  = APPLICATION. 'Controller';

require($app_file);

$controller = new $app_class($act);
$controller->destory();

?>
