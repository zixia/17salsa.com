<?php

/**
 * ECMall: ϵͳ������⺯����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: lib.env_checker.php 6009 2008-10-31 01:55:52Z Garbin $
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

/**
 * ���Ŀ¼�Ķ�дȨ��
 *
 * @access  public
 * @param   array     $checking_dirs     Ŀ¼�б�
 * @return  array     �������Ϣ���飬
 *    �ɹ���ʽ����array('result' => 'OK', 'detail' => array(array($dir, $_LANG['can_write']), array(), ...))
 *    ʧ�ܸ�ʽ����array('result' => 'ERROR', 'd etail' => array(array($dir, $_LANG['cannt_write']), array(), ...))
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
 * �ļ���Ŀ¼Ȩ�޼�麯��
 *
 * @access          public
 * @param           string  $file_path   �ļ�·��
 * @param           bool    $rename_prv  �Ƿ��ڼ���޸�Ȩ��ʱ���ִ��rename()������Ȩ��
 *
 * @return          int     ����ֵ��ȡֵ��ΧΪ{0 <= x <= 15}��ÿ��ֵ��ʾ�ĺ��������λ������������Ƴ���
 *                          ����ֵ�ڶ����Ƽ������У���λ�ɸߵ��ͷֱ����
 *                          ��ִ��rename()����Ȩ�ޡ��ɶ��ļ�׷������Ȩ�ޡ���д���ļ�Ȩ�ޡ��ɶ�ȡ�ļ�Ȩ�ޡ�
 */
function file_mode_info($file_path)
{
    /* ��������ڣ��򲻿ɶ�������д�����ɸ� */
    if (!file_exists($file_path))
    {
        return false;
    }

    $mark = 0;

    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
        /* �����ļ� */
        $test_file = $file_path . '/cf_test.txt';

        /* �����Ŀ¼ */
        if (is_dir($file_path))
        {
            /* ���Ŀ¼�Ƿ�ɶ� */
            $dir = @opendir($file_path);
            if ($dir === false)
            {
                return $mark; //���Ŀ¼��ʧ�ܣ�ֱ�ӷ���Ŀ¼�����޸ġ�����д�����ɶ�
            }
            if (@readdir($dir) !== false)
            {
                $mark ^= 1; //Ŀ¼�ɶ� 001��Ŀ¼���ɶ� 000
            }
            @closedir($dir);

            /* ���Ŀ¼�Ƿ��д */
            $fp = @fopen($test_file, 'wb');
            if ($fp === false)
            {
                return $mark; //���Ŀ¼�е��ļ�����ʧ�ܣ����ز���д��
            }
            if (@fwrite($fp, 'directory access testing.') !== false)
            {
                $mark ^= 2; //Ŀ¼��д�ɶ�011��Ŀ¼��д���ɶ� 010
            }
            @fclose($fp);

            @unlink($test_file);

            /* ���Ŀ¼�Ƿ���޸� */
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

            /* ���Ŀ¼���Ƿ���ִ��rename()������Ȩ�� */
            if (@rename($test_file, $test_file) !== false)
            {
                $mark ^= 8;
            }
            @unlink($test_file);
        }
        /* ������ļ� */
        elseif (is_file($file_path))
        {
            /* �Զ���ʽ�� */
            $fp = @fopen($file_path, 'rb');
            if ($fp)
            {
                $mark ^= 1; //�ɶ� 001
            }
            @fclose($fp);

            /* �����޸��ļ� */
            $fp = @fopen($file_path, 'ab+');
            if ($fp && @fwrite($fp, '') !== false)
            {
                $mark ^= 6; //���޸Ŀ�д�ɶ� 111�������޸Ŀ�д�ɶ�011...
            }
            @fclose($fp);

            /* ���Ŀ¼���Ƿ���ִ��rename()������Ȩ�� */
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
 * ��ȡ����LayoutĿ¼
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

            if ($dir != '.' && $dir != '..' && (strpos($dir, '.') === false) && is_dir($f_dir))  //�Ƿ����¼�Ŀ¼
            {
                $templates_dir[] = array('type' => 'layout', 'path' => $f_dir . '/');
            }
        }
        closedir($handle);
    }
    return $templates_dir;
}

/**
 * ���ģ��Ķ�дȨ��
 *
 * @access  public
 * @param   array      $templates_root        ģ���ļ��������ڵĸ�·�����飬���磺array('dwt'=>'', 'lbi'=>'')
 * @return  array      �������Ϣ���飬ȫ����дΪ�����飬������һ���Բ���д���ļ�·����ɵ�����
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
 *  ����ض�Ŀ¼�Ƿ���ִ��rename����Ȩ��
 *
 * @access  public
 * @param   void
 *
 * @return void
 */
function check_rename_priv()
{
    /* ��ȡҪ����Ŀ¼ */
    $dir_list   = array();
    $dir_list[] = 'templates/caches';
    $dir_list[] = 'templates/compiled';
    $dir_list[] = 'templates/compiled/admin';
    /* ��ȡimagesĿ¼��ͼƬĿ¼ */
    $folder = opendir(ROOT_PATH . 'images');
    while ($dir = readdir($folder))
    {
        if (is_dir(ROOT_PATH . 'images/' . $dir) && preg_match('/^[0-9]{6}$/', $dir))
        {
            $dir_list[] = 'images/' . $dir;
        }
    }
    closedir($folder);
    /* ���Ŀ¼�Ƿ���ִ��rename������Ȩ�� */
    $msgs = array();
    foreach ($dir_list AS $dir)
    {
        $mask = file_mode_info(ROOT_PATH .$dir);
        if ((($mask & 2) > 0 ) && (($mask & 8) < 1))
        {
            /* ֻ�п�дʱ�ż��renameȨ�� */
            $msgs[] = $dir . ' ' . $GLOBALS['_LANG']['cannt_modify'];
        }
    }
    return $msgs;
}

/**
 * ���ϵͳ����
 *
 * @access  public
 * @return  array     ϵͳ������Ϣ��ɵ�����
 */
function check_system_info()
{
    global $lang;
    $system_info = array();
    /* ���PHP�汾 */
    if (PHP_VERSION < '4.3')
    {
        $system_info[] = sprintf($lang['php_ver_require'], PHP_VERSION);
    }
    // TODO : ������
    /* ���GD�汾 */
    $gd_ver = get_gd_version();

    if (empty($gd_ver))
    {
        $system_info[] = $lang['gd_require'];
    }

    /* ���MYSQL */
    if (!function_exists('mysql_connect'))
    {
        $system_info[] = $lang['mysql_require'];
    }

    return $system_info;
}

?>