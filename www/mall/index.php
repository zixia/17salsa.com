<?php

/**
 * ECMALL: ǰ̨��ڳ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: index.php 6048 2008-11-10 10:15:58Z Garbin $
 */

define('IN_ECM',        true);
define('ROOT_PATH',     dirname(__FILE__)); //ȡ��ecmall���ڵĸ�Ŀ¼
define('PAGE_STARTED',  (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime());

/* ����PHP_SELF���� */
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'store.php';
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
require(ROOT_PATH. '/includes/models/mod.base.php');
require(ROOT_PATH. '/includes/manager/mng.base.php');
require(ROOT_PATH. '/includes/ctl.frontend.php'); // ��������������
require(ROOT_PATH. '/includes/inc.init.php');

/* ���������URL�еĲ���������Ӧ�Ķ��� */
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']): '';
$app = isset($_REQUEST['app']) ? basename($_REQUEST['app']): 'mall';

if (isset($_REQUEST['app']))
{
    $app = basename($_REQUEST['app']);
}
else
{
    $app = 'mall';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && count($_GET) == 1)
    {
        $arr = array_keys($_GET);

        if (isset($_GET['store_id']))
        {
            $app = 'store';
        }
        elseif (is_int($arr[0]))
        {
            $app = 'store';
            $_GET['store_id'] = $arr[0];
        }
    }
}

//�����������������Ҳ���������ʱ����
if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN && (!is_main_site()) && (!IS_AJAX))
{
    //����ͨ�������������ʵ�app��������������ת��
    if (!in_array($app, array('goods', 'groupbuy', 'pm', 'regions', 'respond', 'mail', 'issue', 'crontab', 'datacall')))
    {
        //mall��store����ʱ������ʱ����app������д
        if (in_array($app, array('mall', 'store')))
        {
            //��ȡ�Զ�������
            $prefix_domain = get_prefix_domain();

            //�����Զ���������ȡstore_id
            include_once(ROOT_PATH . '/includes/manager/mng.store.php');
            $store_id = StoreManager::get_store_id_by_custom($prefix_domain);
            if ($store_id > 0)
            {
                $app = 'store';
                $_GET['app'] = $app;
                $_GET['store_id'] = $store_id;
            }
            else
            {
                //�Զ�����������������ת����վ
                header('Location: ' . MAIN_DOMAIN . "\n");
                exit;
            }
        }
        else
        {
            //���������ת������������Ӧҳ��
            $add_param = empty($query_string) ? '' : '/index.php?' . $query_string;
            header('Location: ' . trim(MAIN_DOMAIN, '/') . $add_param . "\n");
            exit;
        }
    }
}

/* ���˷Ƿ���app */
$allowed_app = array('article', 'ads', 'category', 'goods', 'groupbuy', 'groupcheckout', 'mall',
     'member', 'message', 'pm', 'regions', 'respond', 'search', 'shopping', 'store', 'storeapply',
     'storelist', 'alipay', 'mail', 'issue', 'feed', 'wanted', 'crontab', 'datacall');

if (!in_array($app, $allowed_app))
{
    die('Hack Attemping');
}

define('APPLICATION', $app);

$app_file   = ROOT_PATH . '/' . APPLICATION . '.php';
$app_class  = ucfirst(APPLICATION) . 'Controller';

require($app_file);
$controller = new $app_class($act);
$controller->destory();



//ר�к���

/**
 * ��ȡ��������ǰ׺
 * @authro  wj
 * @param   void
 * @return  void
 */
function get_prefix_domain()
{
    $domain = get_domain();
    $tmp_arr = parse_url($domain);
    $host = $tmp_arr['host'];
    $tmp_arr = explode('.', $host, 3);
    if ($tmp_arr[0] == 'www')
    {
        $prefix_domain = $tmp_arr[1];
    }
    else
    {
        $prefix_domain = $tmp_arr[0];
    }

    return $prefix_domain;
}
?>
