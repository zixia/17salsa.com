<?php

/**
 * ECMALL: �ļ�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ����ID��ȡ�ļ��б�
     *
     * @author  wj
     * @param   string  $item_ids       �ļ���id
     * @param   string  $item_type      �ļ�����
     * @param   string  $color_name     ָ������ɫ
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
     * ȡ��ָ����Ʒָ����ɫ��Ĭ��ͼƬ
     *
     * @author  weberliu
     * @param   string  $color  ��ɫ
     * @param   int     $id     ��ƷID
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
     * ��ȡ�ļ��б���ids
     *
     * @author  weberliu
     * @param   string      ids         �ļ���ID
     * @param   int         store_id    ����ID ���Ҫ����ȡĳ�����̵��ļ���ָ�����ֵ
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
     * ����ļ�
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
     * ɾ���ϴ����ļ������ļ�id
     *
     * @author  garbin
     * @param   string  $ids    �ļ�ID
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
     * ɾ���ϴ����ļ��������� item_id
     *
     * @author  wj
     * @param   string  $item_ids   ���ݵ�ID
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
     * ɾ���ϴ����ļ����ո���������
     * @param array $list �ļ��б�
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
     * ��ͼƬ���ˮӡ����
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
     * ��������ͼ����
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
     * ���ļ���Ϣ��¼�����ݿ���
     * @param array $images
     * @param int $item_id
     *
     * @return mix ʧ�ܷ��� flase �ɹ����� �ɹ�����б� array(key => file_id);
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
     * �����ļ��������� tiem_id
     *
     * @author  weberliu
     * @param   string  $ids        �������ļ���¼id
     * @param   string  $item_type  �ļ���������
     * @param   int     $item_id    Ŀ������item_id
     * @return  boolean �ɹ�����true ʧ�ܷ���false
     */
    function update_item_id($ids, $item_type, $item_id=null)
    {
        $idx = ($item_id === null) ? $this->item_id : $item_id;

        return $GLOBALS['db']->query("UPDATE `ecm_upload_files` SET item_id='$idx', item_type='$item_type' WHERE file_id ". db_create_in($ids). " AND store_id='" . $this->store_id . "'");
    }

    /**
     * ��ȡ�����ļ�����
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