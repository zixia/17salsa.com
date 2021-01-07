<?php

/**
 * ECMALL: Initialization
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: inc.init.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/* ����Ƿ��Ѿ���ȷ��װ */
if (!is_file(ROOT_PATH . '/data/install.lock') && !defined('NO_CHECK_INSTALL'))
{
    header("Location: ./install/index.php\n");
    exit;
}

require(ROOT_PATH. '/includes/lib.common.php'); // �������ߺ�����
require(ROOT_PATH. '/includes/lib.time.php'); // ����ʱ�亯����
require(ROOT_PATH. '/includes/lib.insert.php'); // ������̬���ݺ�����
require(ROOT_PATH. '/includes/cls.message.php'); // ������Ϣ�������
require(ROOT_PATH. '/includes/cls.session.php'); // ������Ϣ�������
require(ROOT_PATH. '/includes/inc.constant.php'); // ���������ļ�
require(ROOT_PATH. '/data/inc.config.php'); // ���������ļ�

/* ����PHP�Ļ���ֵ */
@ini_set('memory_limit',          '16M');
@ini_set('display_errors',        1);
@ini_set('magic_quotes_runtime',  0);

/* ����ȱʡ���� */
if (!defined('CURRENCY'))
{
    define('CURRENCY', 'CNY');  //ȱʡʹ�������
}

define('CHARSET', substr(LANG, strpos(LANG, '-') + 1)); // �����ַ�������
$GLOBALS['db'] =& db();
define('IS_AJAX', (!empty($_SERVER['HTTP_AJAX_REQUEST'])) || (!empty($_REQUEST['ajax']))); // �����Ƿ�Ϊajax����

/* �������ģʽ���� */
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

/* ������Ϣ���� */
set_error_handler('exception_handler');

/* ���û�����ı�������ת�������*/
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

/* ���������URL��ַ */
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

/* ajax �ύpost����Ҫת�� */
if (IS_AJAX && strcasecmp(CHARSET, 'utf-8'))
{
    $_POST = ecm_iconv_deep('UTF8', CHARSET, $_POST);
    $_GET = ecm_iconv_deep('UTF8', CHARSET, $_GET);
}

/* ��ʼ�� Session */
$GLOBALS['sess'] =& new SessionProcessor($GLOBALS['db'], '`ecm_sessions`', '`ecm_sessions_data`', 'ECM_ID');
$GLOBALS['sess']->add_related_table('`ecm_cart`', 'c', 'session_id');
define('SESS_ID', $GLOBALS['sess']->get_session_id());
$GLOBALS['sess']->my_session_start();

?>