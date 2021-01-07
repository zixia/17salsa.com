<?php

/**
 * ECMALL: 文件管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.file.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class FileManager extends Manager
{
    var $store_id = 0;
    var $item_id = 0;
    var $allow_size = 300000;
    var $file_type = 'image';
    var $item_type = 'album';

    var $water_mark = FALSE;
    var $mark_position = 'rb';
    var $mark_quality = 85;
    var $mark_pct = 80;

    var $thumb_status = false;
    var $thumb_width = null;
    var $thumb_height = null;
    var $thumb_quality = 85;

    function __construct($store_id = 0)
    {
        $this->FileManager($store_id);
    }
    function FileManager($store_id = 0)
    {
        $this->store_id = intval($store_id);
    }

    /**
     * 根据ID获取文件列表
     *
     * @author  wj
     * @param   string  $item_ids       文件的id
     * @param   string  $item_type      文件类型
     * @param   string  $color_name     指定的颜色
     * @return  Array
     */
    function get_list_by_item($item_ids, $item_type='', $color_name = null)
    {
        $item_ids   = empty($item_ids) ? 0 : $item_ids;
        $type       = (empty($item_type) ? $this->item_type : $item_type);
        $sql        = "SELECT * FROM `ecm_upload_files` WHERE item_id " .db_create_in($item_ids). " AND item_type='$type'";

        if (!empty($color_name)) $sql .= " AND color IN ('$color_name', '') ";
        if ($type == 'album')
        {
            $sql .= " ORDER BY color DESC , sort_order ASC, file_id ASC";
        }
        $res = $GLOBALS['db']->getAll($sql);

        return $res;
    }
    /**
     * 取得指定商品指定颜色的默认图片
     *
     * @author  weberliu
     * @param   string  $color  颜色
     * @param   int     $id     商品ID
     * @return  int,bool
     */
    function get_goods_image($color, $id=null)
    {
        $idx = ($id === null) ? $this->item_id : intval($id);
        $sql = "SELECT file_id FROM `ecm_upload_files`
                WHERE item_id='$idx' AND item_type='album' AND color IN ('" . $color . "', '')
                ORDER BY color DESC, sort_order ASC , file_id ASC LIMIT 1";
        return $GLOBALS['db']->getOne($sql);
    }
    /**
     * 获取文件列表按照ids
     *
     * @author  weberliu
     * @param   string      ids         文件的ID
     * @param   int         store_id    店铺ID 如果要限制取某个店铺的文件，指定这个值
     * @return  array
     */
    function get_list_by_ids($ids, $store_id=0)
    {
        $sql = "SELECT * FROM `ecm_upload_files` WHERE file_id " .db_create_in($ids);

        if ($store_id > 0)
        {
            $sql .= " AND store_id = $store_id";
        }

        $res = $GLOBALS['db']->getAll($sql);
        return $res;
    }
    /**
     * 添加文件
     *
     * @param   array   $files
     * @param   string  $color
     * @param   string  $order
     *
     * @return  boolean
     */
    function add($files, $color='', $order='')
    {
        $timestamp = time();
        $dst_path = 'data/user_files/' . date('Ym', $timestamp) . '/' . date('d', $timestamp);
        include_once(ROOT_PATH . '/includes/cls.uploader.php');
        $uploader = new Uploader($dst_path, $this->file_type, $this->allow_size);
        if ($uploader->upload_files($files))
        {
            if ($this->thumb_status && intval($this->thumb_width))
            {
                $this->make_thumb($uploader->success_list);
            }
            if ($this->water_mark)
            {
                $this->water_mark($uploader->success_list);
            }
            return $this->save_db($uploader->success_list, $color, $order);
        }
        else
        {
            $this->err = $uploader->err;
            return false;
        }
    }
    /**
     * 删除上传的文件按照文件id
     *
     * @author  garbin
     * @param   string  $ids    文件ID
     * @return  bool
     */
    function drop_by_ids($ids)
    {
        if ($list = $this->get_list_by_ids($ids, $this->store_id))
        {
            $this->drop($list);
            $GLOBALS['db']->query("DELETE FROM `ecm_upload_files` WHERE file_id " .db_create_in($ids). " AND store_id='" . $this->store_id . "'");
            return true;
        }
        else
        {
            $this->err = 'file_not_exists';
            return false;
        }
    }
    /**
     * 删除上传的文件按照内容 item_id
     *
     * @author  wj
     * @param   string  $item_ids   内容的ID
     * @return  bool
     */
    function drop_by_item($item_ids)
    {
        if (($list = $this->get_list_by_item($item_ids)) && count($list) > 0)
        {
            $this->drop($list);
            $sql = "DELETE FROM `ecm_upload_files` WHERE item_id " .db_create_in($item_ids). " AND item_type='" . $this->item_type . "'";
            if ($this->store_id > 0)
            {
                $sql .= " AND store_id='" . $this->store_id . "' ORDER BY sort_order";
            }
            $GLOBALS['db']->query($sql);
            return true;
        }
        else
        {
            $this->err = 'file_not_exists';
            return false;
        }
    }
    /**
     * 删除上传的文件按照给定的数据
     * @param array $list 文件列表
     *
     * @return void
     */
    function drop($list)
    {
        foreach ($list as $val)
        {
            $file_name = ROOT_PATH . "/". $val['file_name'];

            if (is_file($file_name))
            {
                @unlink($file_name);
            }
        }
        return true;
    }
    /**
     * 给图片添加水印功能
     * @param array $images
     *
     * @return void;
     */
    function water_mark($images)
    {
        include_once(ROOT_PATH . '/includes/lib.image.php');

        foreach ($images as $key => $val)
        {
            water_mark($val['target'], $this->water_mark, $this->mark_position, $this->mark_quality, $this->mark_pct);
        }
    }
    /**
     * 生成缩略图功能
     * @param array $images
     *
     * @return void;
     */
    function make_thumb($images)
    {
        include_once(ROOT_PATH . '/includes/lib.image.php');
        foreach ($images as $key => $val)
        {
            $thumb = str_replace('user_files', 'thumb');
            $thumb = substr($thumb, 0, strpos($thumb, '.')).'_' . $this->thumb_width . '_' . $this->thumb_height . '.jpg';
            make_thumb($val['target'], $thumb, $this->thumb_width, $this->thumb_height, $this->thumb_quality);
        }
    }
    /**
     * 将文件信息记录到数据库中
     * @param array $images
     * @param int $item_id
     *
     * @return mix 失败返回 flase 成功返回 成功后的列表 array(key => file_id);
     */
    function save_db($images, $color, $order)
    {
        $id_list = array();
        foreach ($images as $key => $val)
        {
            $color_name = isset($color[$key]) ? $color[$key] : '';
            $sort_order = isset($order[$key]) ? $order[$key] : 0;
            $sql = "INSERT INTO `ecm_upload_files` (item_id, item_type, color, file_type, file_ext, file_size, file_name, orig_name, add_time, sort_order, store_id)
                    VALUES ('" . $this->item_id . "', '" . $this->item_type . "', '$color_name', '$val[file_type]', '$val[file_ext]',
                        '$val[file_size]', '$val[target]', '$val[file_name]', '" . time() . "', '$sort_order', '" . $this->store_id . "')";
            if ($GLOBALS['db']->query($sql))
            {
                $id_list[$key]['file_id'] = $GLOBALS['db']->insert_id();
                $id_list[$key]['file_path'] = $val['target'];
                $id_list[$key]['file_size'] = $val['file_size'];
                $id_list[$key]['file_name'] = $val['file_name'];
            }
        }
        return $id_list;
    }
    /**
     * 更正文件所属内容 tiem_id
     *
     * @author  weberliu
     * @param   string  $ids        待更新文件记录id
     * @param   string  $item_type  文件所属类型
     * @param   int     $item_id    目标内容item_id
     * @return  boolean 成功返回true 失败返回false
     */
    function update_item_id($ids, $item_type, $item_id=null)
    {
        $idx = ($item_id === null) ? $this->item_id : $item_id;

        return $GLOBALS['db']->query("UPDATE `ecm_upload_files` SET item_id='$idx', item_type='$item_type' WHERE file_id ". db_create_in($ids). " AND store_id='" . $this->store_id . "'");
    }

    /**
     * 获取店铺文件数量
     * @param   int     $store_id
     * @return  int
     */
    function get_store_file_count($store_id)
    {
        $sql = "SELECT COUNT(*) FROM `ecm_upload_files` WHERE store_id='$store_id'";

        return $GLOBALS['db']->getOne($sql);
    }
};
?>