<?php

/**
 * ECMALL: Initialization
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: inc.init.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/* 检查是否已经正确安装 */
if (!is_file(ROOT_PATH . '/data/install.lock') && !defined('NO_CHECK_INSTALL'))
{
    header("Location: ./install/index.php\n");
    exit;
}

require(ROOT_PATH. '/includes/lib.common.php'); // 包含工具函数库
require(ROOT_PATH. '/includes/lib.time.php'); // 包含时间函数库
require(ROOT_PATH. '/includes/lib.insert.php'); // 包含动态内容函数库
require(ROOT_PATH. '/includes/cls.message.php'); // 包含消息处理类库
require(ROOT_PATH. '/includes/cls.session.php'); // 包含消息处理类库
require(ROOT_PATH. '/includes/inc.constant.php'); // 包含常量文件
require(ROOT_PATH. '/data/inc.config.php'); // 包含配置文件

/* 设置PHP的环境值 */
@ini_set('memory_limit',          '16M');
@ini_set('display_errors',        1);
@ini_set('magic_quotes_runtime',  0);

/* 设置缺省货币 */
if (!defined('CURRENCY'))
{
    define('CURRENCY', 'CNY');  //缺省使用人民币
}

define('CHARSET', substr(LANG, strpos(LANG, '-') + 1)); // 定义字符集常量
$GLOBALS['db'] =& db();
define('IS_AJAX', (!empty($_SERVER['HTTP_AJAX_REQUEST'])) || (!empty($_REQUEST['ajax']))); // 定义是否为ajax请求

/* 定义调试模式常量 */
if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}
elseif ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ E_NOTICE);
}

/* 设置消息接收 */
set_error_handler('exception_handler');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    //$_REQUEST  = addslashes_deep($_REQUEST);
}

/* 解码请求的URL地址 */
if (isset($_GET['arg']))
{
    $tmp = explode(chr(8), base64_decode(str_replace(array('.', '-'), array('+', '/'), $_GET['arg'])));
    $arr = addslashes_deep($tmp);

    foreach ($arr AS $key=>$val)
    {
        $tmp = explode(chr(9), $val);
        if (!isset($_GET[$tmp[0]]))
        {
            $_GET[$tmp[0]] = $tmp[1];
            $_REQUEST[$tmp[0]] = $tmp[1];
        }
    }
    unset($_GET['arg'], $arr, $tmp);
}

/* ajax 提交post数据要转码 */
if (IS_AJAX && strcasecmp(CHARSET, 'utf-8'))
{
    $_POST = ecm_iconv_deep('UTF8', CHARSET, $_POST);
    $_GET = ecm_iconv_deep('UTF8', CHARSET, $_GET);
}

/* 初始化 Session */
$GLOBALS['sess'] =& new SessionProcessor($GLOBALS['db'], '`ecm_sessions`', '`ecm_sessions_data`', 'ECM_ID');
$GLOBALS['sess']->add_related_table('`ecm_cart`', 'c', 'session_id');
define('SESS_ID', $GLOBALS['sess']->get_session_id());
$GLOBALS['sess']->my_session_start();

?>