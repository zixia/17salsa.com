<?php

/**
 * ECMALL: 前台入口程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: index.php 6048 2008-11-10 10:15:58Z Garbin $
 */

define('IN_ECM',        true);
define('ROOT_PATH',     dirname(__FILE__)); //取得ecmall所在的根目录
define('PAGE_STARTED',  (PHP_VERSION >= '5.0.0') ? microtime(true) : microtime());

/* 定义PHP_SELF常量 */
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
require(ROOT_PATH. '/includes/ctl.frontend.php'); // 包含控制器基类
require(ROOT_PATH. '/includes/inc.init.php');

/* 根据请求的URL中的参数调用相应的对象 */
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

//当开启二级域名并且不是主域名时进入
if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN && (!is_main_site()) && (!IS_AJAX))
{
    //允许通过二级域名访问的app，不做二级域名转换
    if (!in_array($app, array('goods', 'groupbuy', 'pm', 'regions', 'respond', 'mail', 'issue', 'crontab', 'datacall')))
    {
        //mall和store当不时主域名时，对app进行重写
        if (in_array($app, array('mall', 'store')))
        {
            //获取自定义域名
            $prefix_domain = get_prefix_domain();

            //根据自定义域名获取store_id
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
                //自定义域名不存在则跳转会主站
                header('Location: ' . MAIN_DOMAIN . "\n");
                exit;
            }
        }
        else
        {
            //其他情况跳转回主域名的相应页面
            $add_param = empty($query_string) ? '' : '/index.php?' . $query_string;
            header('Location: ' . trim(MAIN_DOMAIN, '/') . $add_param . "\n");
            exit;
        }
    }
}

/* 过滤非法的app */
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



//专有函数

/**
 * 获取二级域名前缀
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
