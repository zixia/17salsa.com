<?php

/**
 * ECMALL: Install Initialization
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: init.php 6009 2008-10-31 01:55:52Z Garbin $
*/

/* ����վ��� */
define('ROOT_PATH', str_replace('install/includes/init.php', '', str_replace('\\', '/', __FILE__)));

/* ����PHP_SELF���� */
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

require(ROOT_PATH. '/includes/lib.common.php');          // �������ߺ�����
require(ROOT_PATH. '/includes/lib.time.php');            // ����ʱ�亯����
require(ROOT_PATH. '/install/includes/lib.install.php'); // ������װ��������
require(ROOT_PATH. '/includes/cls.template.php');        // ����ģ���������ļ�
require(ROOT_PATH. '/languages/'.$_lang.'/install.php'); // �������԰��ļ�

$template = new ecsTemplate();
$template->template_dir = ROOT_PATH . "/install/templates";
$template->direct_output = true;

$template->assign('cur_lang', $_lang); // ��ǰ��������
$template->assign('lang', $lang);      // ���������

header("Content-type:text/html;charset=" . CHARSET, true);

?>