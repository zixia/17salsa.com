<?php

/**
 * ECMALL: ��̨�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: admin.php 6009 2008-10-31 01:55:52Z Garbin $
 */
unset($GLOBALS, $_ENV);

define('IN_ECM',        true);
define('IS_BACKEND',    true);
define('PAGE_STARTED',  (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime());
define('ROOT_PATH',     dirname(__FILE__)); // ȡ��ecmall���ڵĸ�Ŀ¼

$app_start_time = (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime();
/* ����PHP_SELF���� */
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
require(ROOT_PATH. '/includes/ctl.backend.php'); // ��������������
require(ROOT_PATH. '/includes/inc.init.php');

//��������������ʱ��ֻ�����û����������ʺ�̨
if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN && (!is_main_site()))
{
    $add_param = empty($query_string) ? '/admin.php' : '/admin.php?' . $query_string;
    header('Location: ' . trim(MAIN_DOMAIN, '/') . $add_param . "\n");
    exit;
}

/* ���������URL�еĲ���������Ӧ�Ķ��� */
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
    /* ���referer�Ƿ�Ϊ��վ�ĺ�̨��ַ */
    $backend_dir = site_url() . '/admin.php';
    if ((empty($_SERVER['HTTP_REFERER']) && EMPTY_REFERER === 0) &&
        substr($_SERVER['HTTP_REFERER'], 0, strlen($backend_dir)) != $backend_dir)
    {
        die('Hack Attemping.');
    }
}

/* ���˷Ƿ������� */
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
