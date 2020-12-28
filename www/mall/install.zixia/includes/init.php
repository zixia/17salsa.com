<?php

/**
 * ECMALL: Install Initialization
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Id: init.php 6009 2008-10-31 01:55:52Z Garbin $
*/

/* 定义站点根 */
define('ROOT_PATH', str_replace('install/includes/init.php', '', str_replace('\\', '/', __FILE__)));

/* 定义PHP_SELF常量 */
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}

define('PHP_SELF',      $php_self);

if (isset($_REQUEST['language_type']))
{
    $_lang = trim($_REQUEST['language_type']);
}
else
{
    $_lang = 'sc-gbk';
}

define('CHARSET', substr($_lang, strpos($_lang, '-') + 1));

if (function_exists('set_magic_quotes_runtime'))
{
    set_magic_quotes_runtime(0);
}

require(ROOT_PATH. '/includes/lib.common.php');          // 包含工具函数库
require(ROOT_PATH. '/includes/lib.time.php');            // 包含时间函数库
require(ROOT_PATH. '/install/includes/lib.install.php'); // 包含安装程序函数库
require(ROOT_PATH. '/includes/cls.template.php');        // 包含模板引擎类文件
require(ROOT_PATH. '/languages/'.$_lang.'/install.php'); // 包含语言包文件

$template = new ecsTemplate();
$template->template_dir = ROOT_PATH . "/install/templates";
$template->direct_output = true;

$template->assign('cur_lang', $_lang); // 当前语言类型
$template->assign('lang', $lang);      // 输出语言项

header("Content-type:text/html;charset=" . CHARSET, true);

?>