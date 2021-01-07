<?php
/**
 * ECMALL: ͼƬ�������� ˮӡ ����ͼ
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: lib.image.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/**
 * ����gd����������ͼ
 *
 * @author  weberliu
 * @param   string      $src            ԭͼƬ·��
 * @param   string      $dst            ����ͼ����·��
 * @param   int         $thumb_width    ����ͼ�߶�
 * @param   int         $thumb_height   ����ͼ�߶� ��ѡ
 * @param   int         $quality        ����ͼƷ�� 100֮�ڵ�������
 * @return  boolean     �ɹ����� true ʧ�ܷ��� false
 */
function make_thumb($src, $dst, $thumb_width, $thumb_height = 0, $quality = 85)
{
    if (function_exists('imagejpeg'))
    {
        $func_imagecreate = function_exists('imagecreatetruecolor') ? 'imagecreatetruecolor' : 'imagecreate';
        $func_imagecopy = function_exists('imagecopyresampled') ? 'imagecopyresampled' : 'imagecopyresized';
        $dirpath = substr(dirname($dst), strlen(ROOT_PATH));
        if (!ecm_mkdir($dirpath, 0777))
        {
            return false;
        }
        $data = getimagesize($src);
        $src_width = $data[0];
        $src_height = $data[1];
        if ($thumb_height == 0)
        {
            if ($src_width > $src_height)
            {
                $thumb_height = $src_height * $thumb_width / $src_width;
            }
            else
            {
                $thumb_height = $thumb_width;
                $thumb_width = $src_width * $thumb_height / $src_height;
            }
            $dst_x = 0;
            $dst_y = 0;
            $dst_w = $thumb_width;
            $dst_h = $thumb_height;
        }
        else
        {
            if ($src_width / $src_height > $thumb_width / $thumb_height)
            {
                $dst_w = $thumb_width;
                $dst_h = ($dst_w * $src_height) / $src_width;
                $dst_x = 0;
                $dst_y = ($thumb_height - $dst_h) / 2;
            }
            else
            {
                $dst_h = $thumb_height;
                $dst_w = ($src_width * $dst_h) / $src_height;
                $dst_y = 0;
                $dst_x = ($thumb_width - $dst_w) / 2;
            }
        }

        switch ($data[2])
        {
            case 1:
                $im = imagecreatefromgif($src);
                break;
            case 2:
                $im = imagecreatefromjpeg($src);
                break;
            case 3:
                $im = imagecreatefrompng($src);
                break;
            default:
                trigger_error("Cannot process this picture format: " .$data['mime']);
                break;
        }
        $ni = $func_imagecreate($thumb_width, $thumb_height);
        if ($func_imagecreate == 'imagecreatetruecolor')
        {
            imagefill($ni, 0, 0, imagecolorallocate($ni, 255, 255, 255));
        }
        else
        {
            imagecolorallocate($ni, 255, 255, 255);
        }
        $func_imagecopy($ni, $im, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_width, $src_height);
        imagejpeg($ni, $dst, $quality);
        return is_file($dst) ? $dst : false;
    }
    else
    {
        trigger_error("Unable to process picture.", E_USER_ERROR);
    }
}

/**
 * ��ͼƬ���ˮӡ
 * @param filepath $src ������ͼƬ
 * @param filepath $mark_img ˮӡͼƬ·��
 * @param string $position ˮӡλ�� lt����  rt����  rb����  lb���� ����ȡֵΪ�м�
 * @param int $quality jpgͼƬ����������jpg��Ч Ĭ��85 ȡֵ 0-100֮������
 * @param int $pct ˮӡͼƬ�ں϶�(͸����)
 *
 * @return void
 */
function water_mark($src, $mark_img, $position = 'rb', $quality = 85, $pct = 80) {
    if(function_exists('imagecopy') && function_exists('imagecopymerge')) {
        $data = getimagesize($src);
        if ($data[2] > 3)
        {
            return false;
        }
        $src_width = $data[0];
        $src_height = $data[1];
        $src_type = $data[2];

        $data = getimagesize($mark_img);
        $mark_width = $data[0];
        $mark_height = $data[1];
        $mark_type = $data[2];

        if ($src_width < ($mark_width + 20) || $src_width < ($mark_height + 20))
        {
            return false;
        }
        switch ($src_type)
        {
            case 1:
                $src_im = imagecreatefromgif($src);
                $imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
                break;
            case 2:
                $src_im = imagecreatefromjpeg($src);
                $imagefunc = function_exists('imagegif') ? 'imagejpeg' : '';
                break;
            case 3:
                $src_im = imagecreatefrompng($src);
                $imagefunc = function_exists('imagepng') ? 'imagejpeg' : '';
                break;
        }
        switch ($mark_type)
        {
            case 1:
                $mark_im = imagecreatefromgif($mark_img);
                break;
            case 2:
                $mark_im = imagecreatefromjpeg($mark_img);
                break;
            case 3:
                $mark_im = imagecreatefrompng($mark_img);
                break;
        }

        switch ($position)
        {
            case 'lt':
                $x = 10;
                $y = 10;
                break;
            case 'rt':
                $x = $src_width - $mark_width - 10;
                $y = 10;
                break;
            case 'rb':
                $x = $src_width - $mark_width - 10;
                $y = $src_height - $mark_height - 10;
                break;
            case 'lb':
                $x = 10;
                $y = $src_height - $mark_height - 10;
                break;
            default:
                $x = ($src_width - $mark_width - 10) / 2;
                $y = ($src_height - $mark_height - 10) / 2;
                break;
        }

        if (function_exists('imagealphablending')) imageAlphaBlending($mark_im, true);
        imageCopyMerge($src_im, $mark_im, $x, $y, 0, 0, $mark_width, $mark_height, $pct);

        if ($src_type == 2)
        {
            $imagefunc($src_im, $src, $quality);
        }
        else
        {
            $imagefunc($dst_photo, $src);
        }
    }
}
?>