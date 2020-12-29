<?php

/**
 * ECMall: ��̨���ϴ��ļ��Ĵ�����(ʵ��ͼƬ�ϴ���ͼƬ��С�� ����ˮӡ)
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.image.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/*
 * ��Ҫ�������³���
 *  define('ERR_INVALID_IMAGE',             1);
 *  define('ERR_NO_GD',                     2);
 *  define('ERR_IMAGE_NOT_EXISTS',          3);
 *  define('ERR_DIRECTORY_READONLY',        4);
 *  define('ERR_UPLOAD_FAILURE',            5);
 *  define('ERR_INVALID_PARAM',             6);
 *  define('ERR_INVALID_IMAGE_TYPE',        7);
 *  define('ROOT_PATH',                     '��վ��Ŀ¼')
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class imageProcessor
{
    var $error_no    = 0;
    var $error_msg   = '';
    var $images_dir  = 'images';
    var $data_dir    = 'data';
    var $bgcolor     = '';
    var $type_maping = array(1 => 'image/gif', 2 => 'image/jpeg', 3 => 'image/png');

    function __construct($bgcolor='')
    {
        $this->cls_image($bgcolor);
    }

    function imageProcessor($bgcolor='')
    {
        if ($bgcolor)
        {
            $this->bgcolor = $bgcolor;
        }
        else
        {
            $this->bgcolor = "#FFFFFF";
        }
    }

    /**
     * ͼƬ�ϴ��Ĵ�����
     *
     * @access      public
     * @param       array       upload       �����ϴ���ͼƬ�ļ���Ϣ������
     * @param       array       dir          �ļ�Ҫ�ϴ���$this->data_dir�µ�Ŀ¼�������Ϊ��ͼƬ��������$this->images_dir���Ե���������Ŀ¼��
     * @param       array       img_name     �ϴ�ͼƬ���ƣ�Ϊ�����������
     * @return      mix         ����ɹ��򷵻��ļ��������򷵻�false
     */
    function upload_image($upload, $dir = '', $img_name = '')
    {
        /* û��ָ��Ŀ¼Ĭ��Ϊ��Ŀ¼images */
        if (empty($dir))
        {
            /* ��������Ŀ¼ */
            $dir = date('Ym');
            $dir = ROOT_PATH . $this->images_dir . '/' . $dir . '/';
        }
        else
        {
            /* ����Ŀ¼ */
            $dir = ROOT_PATH . $this->data_dir . '/' . $dir . '/';
            if ($img_name)
            {
                $img_name = $dir . $img_name; // ��ͼƬ��λ����ȷ��ַ
            }
        }

        /* ���Ŀ��Ŀ¼�����ڣ��򴴽��� */
        if (!is_dir($dir))
        {
            if (!make_dir($dir))
            {
                /* ����Ŀ¼ʧ�� */
                $this->error_msg = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
                $this->error_no  = ERR_DIRECTORY_READONLY;

                return false;
            }
        }

        if (empty($img_name))
        {
            $img_name = $this->unique_name($dir);
            $img_name = $dir . $img_name . $this->get_filetype($upload['name']);
        }

        if (!$this->check_img_type($upload['type']))
        {
            $this->error_msg = $GLOBALS['_LANG']['invalid_upload_image_type'];
            $this->error_no  =  ERR_INVALID_IMAGE_TYPE;

            return false;
        }

        if ($this->move_file($upload, $img_name))
        {
            return str_replace(ROOT_PATH, '', $img_name);
        }
        else
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['upload_failure'], $upload['name']);
            $this->error_no  = ERR_UPLOAD_FAILURE;

            return false;
        }
    }

    /**
     * ����ͼƬ������ͼ
     *
     * @access  public
     * @param   string      $img    ԭʼͼƬ��·��
     * @param   int         $thumb_width  ����ͼ���
     * @param   int         $thumb_height ����ͼ�߶�
     * @param   strint      $path         ָ������ͼƬ��Ŀ¼��
     * @return  mix         ����ɹ���������ͼ��·����ʧ���򷵻�false
     */
    function make_thumb($img, $thumb_width = 0, $thumb_height = 0, $path = '', $bgcolor='')
    {
         $gd = $this->gd_version(); //��ȡ GD �汾��0 ��ʾû�� GD �⣬1 ��ʾ GD 1.x��2 ��ʾ GD 2.x
         if ($gd == 0)
         {
             $this->error_msg = $GLOBALS['_LANG']['missing_gd'];
             return false;
         }

        /* �������ͼ��Ⱥ͸߶��Ƿ�Ϸ� */
        if ($thumb_width == 0 && $thumb_height == 0)
        {
            return str_replace(ROOT_PATH, '', str_replace('\\', '/', realpath($img)));
        }

        /* ���ԭʼ�ļ��Ƿ���ڼ����ԭʼ�ļ�����Ϣ */
        $org_info = @getimagesize($img);
        if (!$org_info)
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['missing_orgin_image'], $img);
            $this->error_no  = ERR_IMAGE_NOT_EXISTS;

            return false;
        }

        if (!$this->check_img_function($org_info[2]))
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['nonsupport_type'], $this->type_maping[$org_info[2]]);
            $this->error_no  =  ERR_NO_GD;

            return false;
        }

        $img_org = $this->img_resource($img, $org_info[2]);

        /* ԭʼͼƬ�Լ�����ͼ�ĳߴ���� */
        $scale_org      = $org_info[0] / $org_info[1];
        /* ����ֻ������ͼ��͸���һ��Ϊ0���������ʱ����������ͼһ���� */
        if ($thumb_width == 0)
        {
            $thumb_width = $thumb_height * $scale_org;
        }
        if ($thumb_height == 0)
        {
            $thumb_height = $thumb_width / $scale_org;
        }

        /* ��������ͼ�ı�־�� */
        if ($gd == 2)
        {
            $img_thumb  = imagecreatetruecolor($thumb_width, $thumb_height);
        }
        else
        {
            $img_thumb  = imagecreate($thumb_width, $thumb_height);
        }

        /* ������ɫ */
        if (empty($bgcolor))
        {
            $bgcolor = $this->bgcolor;
        }
        $bgcolor = trim($bgcolor,"#");
        sscanf($bgcolor, "%2x%2x%2x", $red, $green, $blue);
        $clr = imagecolorallocate($img_thumb, $red, $green, $blue);
        imagefilledrectangle($img_thumb, 0, 0, $thumb_width, $thumb_height, $clr);

        if ($org_info[0] / $thumb_width > $org_info[1] / $thumb_height)
        {
            $lessen_width  = $thumb_width;
            $lessen_height  = $thumb_width / $scale_org;
        }
        else
        {
            /* ԭʼͼƬ�Ƚϸߣ����Ը߶�Ϊ׼ */
            $lessen_width  = $thumb_height * $scale_org;
            $lessen_height = $thumb_height;
        }

        $dst_x = ($thumb_width  - $lessen_width)  / 2;
        $dst_y = ($thumb_height - $lessen_height) / 2;

        /* ��ԭʼͼƬ�������Ŵ��� */
        if ($gd == 2)
        {
            imagecopyresampled($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
        }
        else
        {
            imagecopyresized($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
        }

        /* ��������Ŀ¼ */
        if (empty($path))
        {
            $dir = ROOT_PATH . $this->images_dir . '/' . date('Ym').'/';
        }
        else
        {
            $dir = $path;
        }


        /* ���Ŀ��Ŀ¼�����ڣ��򴴽��� */
        if (!is_dir($dir))
        {
            if (!make_dir($dir))
            {
                /* ����Ŀ¼ʧ�� */
                $this->error_msg  = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
                $this->error_no   = ERR_DIRECTORY_READONLY;
                return false;
            }
        }

        /* ����ļ���Ϊ�գ����ɲ���������ļ��� */
        $filename = $this->unique_name($dir);

        /* �����ļ� */
        if (function_exists('imagejpeg'))
        {
            $filename .= '.jpg';
            imagejpeg($img_thumb, $dir . $filename);
        }
        elseif (function_exists('imagegif'))
        {
            $filename .= '.gif';
            imagegif($img_thumb, $dir . $filename);
        }
        elseif (function_exists('imagepng'))
        {
            $filename .= '.png';
            imagepng($img_thumb, $dir . $filename);
        }
        else
        {
            $this->error_msg = $GLOBALS['_LANG']['creating_failure'];
            $this->error_no  =  ERR_NO_GD;

            return false;
        }

        imagedestroy($img_thumb);
        imagedestroy($img_org);

        //ȷ���ļ��Ƿ�����
        if (is_file($dir . $filename))
        {
            return str_replace(ROOT_PATH, '', $dir) . $filename;
        }
        else
        {
            $this->error_msg = $GLOBALS['_LANG']['writting_failure'];
            $this->error_no   = ERR_DIRECTORY_READONLY;

            return false;
        }
    }

    /**
     * ΪͼƬ����ˮӡ
     *
     * @access      public
     * @param       string      filename            ԭʼͼƬ�ļ�������������·��
     * @param       string      target_file         ��Ҫ��ˮӡ��ͼƬ�ļ�������������·�������Ϊ���򸲸�Դ�ļ�
     * @param       string      $watermark          ˮӡ����·��
     * @param       int         $watermark_place    ˮӡλ�ô���
     * @return      mix         ����ɹ��򷵻��ļ�·�������򷵻�false
     */
    function add_watermark($filename, $target_file='', $watermark='', $watermark_place='', $watermark_alpha = 0.65)
    {
        // �Ƿ�װ��GD
        $gd = $this->gd_version();
        if ($gd == 0)
        {
            $this->error_msg = $GLOBALS['_LANG']['missing_gd'];
            $this->error_no  = ERR_NO_GD;

            return false;
        }

        // �ļ��Ƿ����
        if (!is_file($filename))
        {
            $this->error_msg  = sprintf($GLOBALS['_LANG']['missing_orgin_image'], $filename);
            $this->error_no   = ERR_IMAGE_NOT_EXISTS;

            return false;
        }

        /* ���ˮӡ��λ��Ϊ0���򷵻�ԭͼ */
        if ($watermark_place == 0 || empty($watermark))
        {
            return str_replace(ROOT_PATH, '', str_replace('\\', '/', realpath($filename)));
        }

        if (!$this->validate_image($watermark))
        {
            /* �Ѿ���¼�˴�����Ϣ */
            return false;
        }

        // ���ˮӡ�ļ��Լ�Դ�ļ�����Ϣ
        $watermark_info     = @getimagesize($watermark);
        $watermark_handle   = $this->img_resource($watermark, $watermark_info[2]);

        if (!$watermark_handle)
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['create_watermark_res'], $this->type_maping[$watermark_info[2]]);
            $this->error_no  = ERR_INVALID_IMAGE;

            return false;
        }

        // �����ļ����ͻ��ԭʼͼƬ�Ĳ������
        $source_info    = @getimagesize($filename);
        $source_handle  = $this->img_resource($filename, $source_info[2]);
        if (!$source_handle)
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['create_origin_image_res'], $this->type_maping[$source_info[2]]);
            $this->error_no = ERR_INVALID_IMAGE;

            return false;
        }

        // ����ϵͳ���û��ˮӡ��λ��
        switch ($watermark_place)
        {
            case '1':
                $x = 0;
                $y = 0;
                break;
            case '2':
                $x = $source_info[0] - $watermark_info[0];
                $y = 0;
                break;
            case '4':
                $x = 0;
                $y = $source_info[1] - $watermark_info[1];
                break;
            case '5':
                $x = $source_info[0] - $watermark_info[0];
                $y = $source_info[1] - $watermark_info[1];
                break;
            default:
                $x = $source_info[0]/2 - $watermark_info[0]/2;
                $y = $source_info[1]/2 - $watermark_info[1]/2;
        }

        imagecopymerge($source_handle, $watermark_handle, $x, $y, 0, 0,
                        $watermark_info[0], $watermark_info[1], $watermark_alpha);

        $target = empty($target_file) ? $filename : $target_file;

        switch ($source_info[2] )
        {
            case 'image/gif':
            case 1:
                imagegif($source_handle,  $target);
                break;

            case 'image/pjpeg':
            case 'image/jpeg':
            case 2:
                imagejpeg($source_handle, $target);
                break;

            case 'image/x-png':
            case 'image/png':
            case 3:
                imagepng($source_handle,  $target);
                break;

            default:
                $this->error_msg = $GLOBALS['_LANG']['creating_failure'];
                $this->error_no = ERR_NO_GD;

                return false;
        }

        imagedestroy($source_handle);

        $path = realpath($target);
        if ($path)
        {
            return str_replace(ROOT_PATH, '', str_replace('\\', '/', $path));
        }
        else
        {
            $this->error_msg = $GLOBALS['_LANG']['writting_failure'];
            $this->error_no  = ERR_DIRECTORY_READONLY;

            return false;
        }
    }

    /**
     *  ���ˮӡͼƬ�Ƿ�Ϸ�
     *
     * @access  public
     * @param   string      $path       ͼƬ·��
     *
     * @return boolen
     */
    function validate_image($path)
    {
        if (empty($path))
        {
            $this->error_msg = $GLOBALS['_LANG']['empty_watermark'];
            $this->error_no  = ERR_INVALID_PARAM;

            return false;
        }

        /* �ļ��Ƿ���� */
        if (!is_file($path))
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['missing_watermark'], $path);
            $this->error_no = ERR_IMAGE_NOT_EXISTS;
            return false;
        }

        // ����ļ��Լ�Դ�ļ�����Ϣ
        $image_info     = @getimagesize($path);

        if (!$image_info)
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['invalid_image_type'], $path);
            $this->error_no = ERR_INVALID_IMAGE;
            return false;
        }

        /* ��鴦�����Ƿ���� */
        if (!$this->check_img_function($image_info[2]))
        {
            $this->error_msg = sprintf($GLOBALS['_LANG']['nonsupport_type'], $this->type_maping[$image_info[2]]);
            $this->error_no  =  ERR_NO_GD;
            return false;
        }

        return true;
    }

    /**
     * ���ش�����Ϣ
     *
     * @return  string   ������Ϣ
     */
    function error_msg()
    {
        return $this->error_msg;
    }

    /*------------------------------------------------------ */
    //-- ���ߺ���
    /*------------------------------------------------------ */

    /**
     * ���ͼƬ����
     * @param   string  $img_type   ͼƬ����
     * @return  bool
     */
    function check_img_type($img_type)
    {
        return $img_type == 'image/pjpeg' ||
               $img_type == 'image/x-png' ||
               $img_type == 'image/png'   ||
               $img_type == 'image/gif'   ||
               $img_type == 'image/jpeg';
    }

    /**
     * ���ͼƬ��������
     *
     * @access  public
     * @param   string  $img_type   ͼƬ����
     * @return  void
     */
    function check_img_function($img_type)
    {
        switch ($img_type)
        {
            case 'image/gif':
            case 1:

                if (PHP_VERSION >= '4.3')
                {
                    return function_exists('imagecreatefromgif');
                }
                else
                {
                    return (imagetypes() & IMG_GIF) > 0;
                }
            break;

            case 'image/pjpeg':
            case 'image/jpeg':
            case 2:
                if (PHP_VERSION >= '4.3')
                {
                    return function_exists('imagecreatefromjpeg');
                }
                else
                {
                    return (imagetypes() & IMG_JPG) > 0;
                }
            break;

            case 'image/x-png':
            case 'image/png':
            case 3:
                if (PHP_VERSION >= '4.3')
                {
                     return function_exists('imagecreatefrompng');
                }
                else
                {
                    return (imagetypes() & IMG_PNG) > 0;
                }
            break;

            default:
                return false;
        }
    }

    /**
     * ������������ִ�
     *
     * @author: weber liu
     * @return string
     */
    function random_filename()
    {
        $str = '';
        for($i = 0; $i < 9; $i++)
        {
            $str .= mt_rand(0, 9);
        }

        return gmtime() . $str;
    }

    /**
     *  ����ָ��Ŀ¼���������ļ���
     *
     * @access  public
     * @param   string      $dir        Ҫ����Ƿ���ͬ���ļ���Ŀ¼
     *
     * @return  string      �ļ���
     */
    function unique_name($dir)
    {
        $filename = '';
        while (empty($filename))
        {
            $filename = cls_image::random_filename();
            if (is_file($dir . $filename . '.jpg') || file_exists($dir . $filename . '.gif') || is_file($dir . $filename . '.png'))
            {
                $filename = '';
            }
        }

        return $filename;
    }

    /**
     *  �����ļ���׺�����确.php��
     *
     * @access  public
     * @param
     *
     * @return  string      �ļ���׺��
     */
    function get_filetype($path)
    {
        $pos = strrpos($path, '.');
        if ($pos !== false)
        {
            return substr($path, $pos);
        }
        else
        {
            return '';
        }
    }

     /**
     * ������Դ�ļ����ļ����ʹ���һ��ͼ������ı�ʶ��
     *
     * @access  public
     * @param   string      $img_file   ͼƬ�ļ���·��
     * @param   string      $mime_type  ͼƬ�ļ����ļ�����
     * @return  resource    ����ɹ��򷵻�ͼ�������־������֮�򷵻ش������
     */
    function img_resource($img_file, $mime_type)
    {
        switch ($mime_type)
        {
            case 1:
            case 'image/gif':
                $res = imagecreatefromgif($img_file);
                break;

            case 2:
            case 'image/pjpeg':
            case 'image/jpeg':
                $res = imagecreatefromjpeg($img_file);
                break;

            case 3:
            case 'image/x-png':
            case 'image/png':
                $res = imagecreatefrompng($img_file);
                break;

            default:
                return false;
        }

        return $res;
    }

    /**
     * ��÷������ϵ� GD �汾
     *
     * @return      int         ���ܵ�ֵΪ0��1��2
     */
    function gd_version()
    {
        static $version = -1;

        if ($version >= 0)
        {
            return $version;
        }

        if (!extension_loaded('gd'))
        {
            $version = 0;
        }
        else
        {
            // ����ʹ��gd_info����
            if (PHP_VERSION >= '4.3')
            {
                if (function_exists('gd_info'))
                {
                    $ver_info = gd_info();
                    preg_match('/\d/', $ver_info['GD Version'], $match);
                    $version = $match[0];
                }
                else
                {
                    if (function_exists('imagecreatetruecolor'))
                    {
                        $version = 2;
                    }
                    elseif (function_exists('imagecreate'))
                    {
                        $version = 1;
                    }
                }
            }
            else
            {
                if (preg_match('/phpinfo/', ini_get('disable_functions')))
                {
                    /* ���phpinfo�����ã��޷�ȷ��gd�汾 */
                    $version = 1;
                }
                else
                {
                  // ʹ��phpinfo����
                   ob_start();
                   phpinfo(8);
                   $info = ob_get_contents();
                   ob_end_clean();
                   $info = stristr($info, 'gd version');
                   preg_match('/\d/', $info, $match);
                   $version = $match[0];
                }
             }
        }

        return $version;
     }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function move_file($upload, $target)
    {
        if (isset($upload['error']) && $upload['error'] > 0)
        {
            return false;
        }

        if (!move_uploaded_file($upload['tmp_name'], $target))
        {
             return false;
        }

        return true;
    }
}

?>