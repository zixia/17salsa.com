<?php

/**
 * ECMALL: 样式文件压缩器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ads.php 2783 2008-05-07 08:07:50Z Liupeng $
 */
define('IN_ECM', true);
define('ROOT_PATH',     dirname(__FILE__)); //取得ecmall所在的根目录

include(ROOT_PATH . '/data/inc.config.php');
include(ROOT_PATH . '/includes/cls.mysql.php');
include(ROOT_PATH . '/includes/lib.common.php');
include(ROOT_PATH . '/includes/manager/mng.conf.php');

define('CHARSET', substr(LANG, strpos(LANG, '-') + 1));
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'store.php';
}
define('PHP_SELF',  htmlentities($php_self));

$app         = isset($_GET['app']) ? trim($_GET['app']) : '';
$mall_skin   = isset($_GET['mall_skin']) ? trim($_GET['mall_skin']) : '';
$store_skin  = isset($_GET['store_skin']) ? trim($_GET['store_skin']) : '';

$style_files = array();
$style_files[] = ROOT_PATH . "/themes/mall/skin/$mall_skin/global.css";

$check_list = array(
                    'help' => 'article',
                    'groupcheckout' => 'shopping',
                    'search' => 'category',
                    'storeapply' => 'member',
                    'wanted' => 'wanted,member'
                    );

if (isset($check_list[$app]))
{
    $app = $check_list[$app];
}

$skin_dir = '';

if (!empty($store_skin))
{
    $skin_dir = ROOT_PATH . "/themes/store/skin/$store_skin/";
    $style_files[] = $skin_dir ."storebase.css";
    if ($app == 'groupbuy')
    {
        $app = 'goods';
    }
}
else
{
    $skin_dir = ROOT_PATH . "/themes/mall/skin/$mall_skin/";
    $style_files[] = $skin_dir . "mallbase.css";
    if ($app == 'groupbuy')
    {
        $app = 'category';
    }
}

$arr = split(',', $app);
foreach($arr AS $value)
{
    $tmp = $skin_dir . "$value.css";

    if (is_file($tmp))
    {
        $style_files[] = $tmp;
    }
}

header("Content-type:text/css;\n");

$target = ROOT_PATH . "/temp/style/" . md5(join('', $style_files)) . '.css';
$modified = false;
$exists   = false;
if (DEBUG_MODE > 0)
{
    $exists = file_exists($target);
    if ($exists)
    {
        $modified = check_modified($style_files, $target);

        if (!$modified && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }

    header("Last-Modified:" . date('r'));
}

if ($modified || !$exists)
{
    $code = '';
    foreach($style_files AS $file)
    {
        if (!is_file($file))
        {
            die("hacking");
        }
        else
        {
            $content = file_get_contents($file);
            $skin_img = str_replace(ROOT_PATH , '', dirname($file)) . '/images';
            $skin_img = site_url() . $skin_img;
            $patterns[]     = '/\s+\/\/.*+\n/';
            $replacement[]  = '';
            $content = preg_replace($patterns, $replacement, $content);
            $content = str_replace("\r", '', $content);
            $content = str_replace("\n", '', $content);

            $content = preg_replace('/url\(images/i', "url($skin_img", $content);
            $code .= $content;
        }
    }
    file_put_contents($target, $code, LOCK_EX);
}
$file = str_replace(ROOT_PATH . '/', '', $target);

header("Location:$file\n");

/**
 * 检测文件是否已经被修改
 *
 * @author liupeng
 * @param  array  $files   文件列表
 * @param  array  $target  目标文件
 * @return  bool
 **/
function check_modified($files, $target)
{
    $mtime = filemtime($target);
    foreach($files AS $val)
    {
        if (filemtime($val) > $mtime)
        {
            return true;
        }
    }
    return false;
}
?>
