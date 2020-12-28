<?php

/**
 * ECMall: 系统环境检测函数库
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Id: lib.env_checker.php 6009 2008-10-31 01:55:52Z Garbin $
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

/**
 * 检查目录的读写权限
 *
 * @access  public
 * @param   array     $checking_dirs     目录列表
 * @return  array     检查后的消息数组，
 *    成功格式形如array('result' => 'OK', 'detail' => array(array($dir, $_LANG['can_write']), array(), ...))
 *    失败格式形如array('result' => 'ERROR', 'd etail' => array(array($dir, $_LANG['cannt_write']), array(), ...))
 */
function check_dirs_priv($checking_dirs)
{
    include_once(ROOT_PATH . 'includes/lib.common.php');

    global $lang;
    $msgs = array('result' => 'OK', 'detail' => array());

    foreach ($checking_dirs AS $dir)
    {
        $dir_type = $lang['template_dir'];
        if (strpos($dir, 'data/') !== false)
        {
            $dir_type = $lang['data_dir'];
        }
        elseif (strpos($dir, '/uc_client') !== false)
        {
            $dir_type = $lang['uc_client_dir'];
        }
        if (!file_exists(ROOT_PATH . $dir))
        {
            $msgs['result'] = 'ERROR';
            $str = $dir_type . '<strong>.'. $dir . '</strong>&nbsp;' . $lang['not_exists'];
            $msgs['detail'][] = $str;
            continue;
        }

        if (file_mode_info(ROOT_PATH . $dir) < 2)
        {
            $msgs['result'] = 'ERROR';

            $str = $dir_type .'<strong>.'. $dir . '</strong>&nbsp;' . $lang['cannt_write'];
            $msgs['detail'][] = $str;
        }
    }

    return $msgs;
}


/**
 * 文件或目录权限检查函数
 *
 * @access          public
 * @param           string  $file_path   文件路径
 * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
 *
 * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
 *                          返回值在二进制计数法中，四位由高到低分别代表
 *                          可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
 */
function file_mode_info($file_path)
{
    /* 如果不存在，则不可读、不可写、不可改 */
    if (!file_exists($file_path))
    {
        return false;
    }

    $mark = 0;

    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
        /* 测试文件 */
        $test_file = $file_path . '/cf_test.txt';

        /* 如果是目录 */
        if (is_dir($file_path))
        {
            /* 检查目录是否可读 */
            $dir = @opendir($file_path);
            if ($dir === false)
            {
                return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
            }
            if (@readdir($dir) !== false)
            {
                $mark ^= 1; //目录可读 001，目录不可读 000
            }
            @closedir($dir);

            /* 检查目录是否可写 */
            $fp = @fopen($test_file, 'wb');
            if ($fp === false)
            {
                return $mark; //如果目录中的文件创建失败，返回不可写。
            }
            if (@fwrite($fp, 'directory access testing.') !== false)
            {
                $mark ^= 2; //目录可写可读011，目录可写不可读 010
            }
            @fclose($fp);

            @unlink($test_file);

            /* 检查目录是否可修改 */
            $fp = @fopen($test_file, 'ab+');
            if ($fp === false)
            {
                return $mark;
            }
            if (@fwrite($fp, "modify test.\r\n") !== false)
            {
                $mark ^= 4;
            }
            @fclose($fp);

            /* 检查目录下是否有执行rename()函数的权限 */
            if (@rename($test_file, $test_file) !== false)
            {
                $mark ^= 8;
            }
            @unlink($test_file);
        }
        /* 如果是文件 */
        elseif (is_file($file_path))
        {
            /* 以读方式打开 */
            $fp = @fopen($file_path, 'rb');
            if ($fp)
            {
                $mark ^= 1; //可读 001
            }
            @fclose($fp);

            /* 试着修改文件 */
            $fp = @fopen($file_path, 'ab+');
            if ($fp && @fwrite($fp, '') !== false)
            {
                $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
            }
            @fclose($fp);

            /* 检查目录下是否有执行rename()函数的权限 */
            if (@rename($test_file, $test_file) !== false)
            {
                $mark ^= 8;
            }
        }
    }
    else
    {
        if (@is_readable($file_path))
        {
            $mark ^= 1;
        }

        if (@is_writable($file_path))
        {
            $mark ^= 14;
        }
    }

    return $mark;
}

/**
 * 获取所有Layout目录
 *
 * @access  public
 * @param   array  $layout_dir
 * @return  array
 */
function get_layouts($layout_dir)
{
    $templates_dir = array();
    if (is_dir($layout_dir))
    {
        $handle = opendir($layout_dir);

        while ($dir = readdir($handle))
        {
            $f_dir = $layout_dir . '/' . $dir;

            if ($dir != '.' && $dir != '..' && (strpos($dir, '.') === false) && is_dir($f_dir))  //是否还有下级目录
            {
                $templates_dir[] = array('type' => 'layout', 'path' => $f_dir . '/');
            }
        }
        closedir($handle);
    }
    return $templates_dir;
}

/**
 * 检查模板的读写权限
 *
 * @access  public
 * @param   array      $templates_root        模板文件类型所在的根路径数组，形如：array('dwt'=>'', 'lbi'=>'')
 * @return  array      检查后的消息数组，全部可写为空数组，否则是一个以不可写的文件路径组成的数组
 */
function check_templates_priv($templates_root)
{
    global $lang;

    $msgs = array();
    $filename = '';
    $filepath = '';

    foreach ($templates_root as $tpl_dirs)
    {
        $tpl_type = $tpl_dirs['type'];
        $tpl_root = $tpl_dirs['path'];

        if (!file_exists($tpl_root))
        {
            $msgs[] = str_replace(ROOT_PATH, '', $tpl_root);
            continue;
        }
        $tpl_handle = @opendir($tpl_root);
        while (($filename = @readdir($tpl_handle)) !== false)
        {
            if (is_dir($filename))
            {
                continue;
            }
            $filepath = $tpl_root ."". $filename;

            $mode = file_mode_info($filepath);
            if (file_ext($filepath) == $tpl_type
                    && ($mode < 7 || empty($mode)))
            {
                $str = $lang['template_dir'] . '<strong>'. str_replace(ROOT_PATH, './', $tpl_root) . '</strong> &nbsp;' . $lang['cannt_write'];
                $msgs[] = $str;
                break 1;
            }
        }
        @closedir($tpl_handle);
    }

    return $msgs;
}


/**
 *  检查特定目录是否有执行rename函数权限
 *
 * @access  public
 * @param   void
 *
 * @return void
 */
function check_rename_priv()
{
    /* 获取要检查的目录 */
    $dir_list   = array();
    $dir_list[] = 'templates/caches';
    $dir_list[] = 'templates/compiled';
    $dir_list[] = 'templates/compiled/admin';
    /* 获取images目录下图片目录 */
    $folder = opendir(ROOT_PATH . 'images');
    while ($dir = readdir($folder))
    {
        if (is_dir(ROOT_PATH . 'images/' . $dir) && preg_match('/^[0-9]{6}$/', $dir))
        {
            $dir_list[] = 'images/' . $dir;
        }
    }
    closedir($folder);
    /* 检查目录是否有执行rename函数的权限 */
    $msgs = array();
    foreach ($dir_list AS $dir)
    {
        $mask = file_mode_info(ROOT_PATH .$dir);
        if ((($mask & 2) > 0 ) && (($mask & 8) < 1))
        {
            /* 只有可写时才检查rename权限 */
            $msgs[] = $dir . ' ' . $GLOBALS['_LANG']['cannt_modify'];
        }
    }
    return $msgs;
}

/**
 * 检测系统环境
 *
 * @access  public
 * @return  array     系统各项信息组成的数组
 */
function check_system_info()
{
    global $lang;
    $system_info = array();
    /* 检查PHP版本 */
    if (PHP_VERSION < '4.3')
    {
        $system_info[] = sprintf($lang['php_ver_require'], PHP_VERSION);
    }
    // TODO : 语言项
    /* 检查GD版本 */
    $gd_ver = get_gd_version();

    if (empty($gd_ver))
    {
        $system_info[] = $lang['gd_require'];
    }

    /* 检查MYSQL */
    if (!function_exists('mysql_connect'))
    {
        $system_info[] = $lang['mysql_require'];
    }

    return $system_info;
}

?>