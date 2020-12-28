<?php
/**
 * ECMALL: 处理上传类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: cls.uploader.php 7 2007-12-19 09:38:41Z Redstone $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/**
 * uploader
 *
 * pubilc attributes
 * err    记录错误信息的数组 每条为一个string
 * upload_list  符合条件的附件列表
 * success_list 成功上传的附件列表
 * file_type    设定上传的文件类型
 * dst_path     文件存储的目录
 * allow_size   允许的单个文件的最大字节数
 *---------------------------------------
 * private attributes
 *
 * _allow_file_type:  允许上传的附件后缀类型
 * _black_exts: 后缀黑名单，再次列的后缀存储时都会在末尾加上 _attach
 *
 * 使用示例
 * $goods_img = new Uploader($dst_path, 'image', '120000');
 * $goods_img->upload_files($_FILES['goods_img']);
 */
class Uploader
{
    /* public attributes */
    var $err = null;
    var $upload_list = null;
    var $success_list = null;
    var $file_type = null;
    var $dst_path = null;
    var $allow_size = null;

    /* private attributes */
    var $_allow_file_type = array(
                                    'image' => array('jpg', 'gif', 'png', 'jpeg'),
                                    'zip' => array('zip', 'gz', 'tar', 'rar', 'iso'),
                                    'wave' => array('wav', 'mp3', 'midi', 'avi', 'mpeg', 'mpg', 'asf'),
                                    'flash' => array('swf'),
                                 );
    var $_black_exts = array('php', 'phtml', 'php3', 'php4', 'jsp', 'exe', 'dll', 'asp', 'cer', 'asa', 'shtml', 'shtm', 'aspx', 'asax', 'cgi', 'fcgi', 'pl', 'sh');

    /**
     * @构造函数
     * @param   string  $dst_path   文件上传的目录
     * @param   string    $allow_type 允许的文件类型
     * @param   int  $allow_size 允许的文件大小
     */
    function __construct($dst_path, $file_type = 'image', $allow_size = '300000')
    {
        $this->Uploader($dst_path, $file_type, $allow_size);
    }
    function Uploader($dst_path, $file_type = 'image', $allow_size = '300000')
    {
        if (!empty($file_type) && is_string($file_type) && isset($this->_allow_file_type[$file_type]))
        {
            $this->file_type = $file_type;
        }
        else
        {
            $this->file_type = null;
        }
        if (!empty($allow_size) && is_numeric($allow_size) && $allow_size > 0)
        {
            $this->allow_size = $allow_size * 1024;
        }
        else
        {
            $this->allow_size = null;
        }
        if (!empty($dst_path) && is_string($dst_path) && strpos($dst_path, '..') === false && strpos($dst_path, '//') === false)
        {
            if (substr($dst_path, -1) != '/')
            {
                $dst_path .= '/';
            }
            $this->dst_path = $dst_path;
        }
        else
        {
            $this->dst_path = null;
        }
    }
    /**
     * @处理上传文件，将其放到指定目录。
     * @param   string      $dst_path   文件上传的目录
     * @param   string      $allow_type 允许的文件类型
     * @param   int         $allow_size 允许的文件大小
     *
     * @return   boolean     成功返回ture，具体结果保存在 $this->success_list，失败返回false，失败信息在 $this->err
     */
    function upload_files($src_files)
    {
        if (empty($this->dst_path))
        {
            $this->err = 'e_undifined_dst_path';
            return false;
        }
        if (!is_dir($this->dst_path))
        {
            if (!ecm_mkdir($this->dst_path, 0777))
            {
                $this->err = 'e_mkdir_failed';
                return false;
            }
        }
        elseif (!is_dir($this->dst_path))
        {
            $this->err = 'e_dst_path_illegal';
            return false;
        }
        if (empty($this->file_type))
        {
            $this->err = 'e_file_not_allowed';
            return false;
        }
        if (empty($this->allow_size))
        {
            $this->err = 'e_allow_size_illegal';
            return false;
        }
        if (empty($src_files) || !is_array($src_files))
        {
            $this->err = 'e_src_files_empty';
            return false;
        }
        if (is_array($src_files['error']))
        {
            foreach ($src_files['error'] as $key => $errorno)
            {
                if ($errorno != 0)
                {
                    continue;
                }
                if (!$this->_check_file($key, $src_files['tmp_name'][$key], $src_files['name'][$key], $src_files['size'][$key], $src_files['type'][$key]))
                {
                    return false;
                }
            }
        }
        else
        {
            if (!$this->_check_file(0, $src_files['tmp_name'], $src_files['name'], $src_files['size'], $src_files['type']))
            {
                return false;
            }
        }
        if (!is_array($this->upload_list))
        {
            $this->err = 'e_noupload_files';
            return false;
        }
        foreach ($this->upload_list as $key => $val)
        {
            if (in_array($val['file_ext'], $this->_black_exts))
            {
                $val['file_ext'] .= '_attach';
            }

            $dst_file = md5($val['file_name'].microtime()).'.'.$val['file_ext'];
            $target = ROOT_PATH. '/' .$this->dst_path.$dst_file;
            if (!copy($val['tmp_name'], $target))
            {
                if (!move_uploaded_file($val['tmp_name'], $target))
                {
                    if (is_readable($attach['tmp_name']))
                    {
                        if (!$fp = fopen($val['tmp_name'], 'rb'))
                        {
                            $this->err = 'e_save_file_failed';
                            continue;
                        }
                        flock($fp, 2);
                        if (!$file_data = fread($fp, $val['file_size']))
                        {
                            $this->err = 'e_save_file_failed';
                            continue;
                        }
                        fclose($fp);
                        if (!$fp = fopen($target, 'wb'))
                        {
                            $this->err = 'e_save_file_failed';
                            continue;
                        }
                        flock($fp, 2);
                        if (!fwrite($fp, $file_data))
                        {
                            $this->err = 'e_save_file_failed';
                            continue;
                        }
                        fclose($fp);
                        unlink($val['tmp_name']);
                    }
                    else
                    {
                        $this->err = 'e_save_file_failed';
                        continue;
                    }

                }
            }
            unset($val['tmp_name']);
            $val['target'] = str_replace(ROOT_PATH . '/', '', $target);
            $this->success_list[$key] = $val;
        }

        return true;
    }
    /**
     * @检查上传文件是件，并将其放入upload_list
     *
     * @author  scottye
     * @param   int      $key       序号
     * @param   string   $tmp_name 临时文件名，包含路径
     * @param   string   $file_name 文件原始名称
     * @param   int      $file_size 文件大小
     *
     * @return  boolean  成功返回true，且将合格的数据存入 $this->upload_list，失败返回false，失败信息在 $this->err
     */
    function _check_file($key, $tmp_name, $file_name, $file_size, $file_type)
    {
        if (!is_uploaded_file($tmp_name))
        {
            $this->err = 'e_file_not_uploaded';
            return false;
        }
        if ($file_size > $this->allow_size)
        {
            $this->err = 'e_file_too_big';
            return false;
        }
        $file_ext = strtolower(file_ext($file_name));
        if (empty($file_ext) || !in_array($file_ext, $this->_allow_file_type[$this->file_type]))
        {
            $this->err = 'e_file_not_allowed_' . $this->file_type;
            return false;
        }
        if ($this->file_type == 'image' && !getimagesize($tmp_name))
        {
            $this->err = 'e_file_not_allowed';
            return false;
        }
        $this->upload_list[$key] = array('tmp_name' => $tmp_name, 'file_name' => $file_name, 'file_ext' => $file_ext, 'file_size' => $file_size, 'file_type' => $file_type);
        return true;
    }
}
?>