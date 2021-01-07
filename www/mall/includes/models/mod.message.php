<?php

/**
 * ECMALL: 留言实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class Messages extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id)
    {
        $this->Messages($id, $store_id);
    }

    function Messages($id, $store_id)
    {
        $this->_table = '`ecm_message`';
        $this->_key   = 'message_id';
        parent::Model($id, $store_id);
    }

    function get_info()
    {
        $sql = "SELECT m.*, g.goods_name FROM " . $this->_table . " AS m " .
                " LEFT JOIN `ecm_goods` AS g ON m.goods_id = g.goods_id " .
                " WHERE m.message_id = '" . $this->_id . "' ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND m.seller_id = '" . $this->_store_id . "'";
        }
        $row = $GLOBALS['db']->getRow($sql);
        if ($row)
        {
            $row['formated_add_time'] = local_date('Y-m-d H:i', $row['add_time']);
        }

        return $row;
    }

    function update($arr)
    {
        $where = $this->_key . " = '" . $this->_id . "'";

        if ($this->_store_id > 0)
        {
            $where .= "AND seller_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->autoExecute($this->_table, $arr, 'UPDATE', $where);
    }

    function drop()
    {
        $sql = "DELETE FROM " .$this->_table. " WHERE " .$this->_key. " = '" .$this->_id. "'";

        if ($this->_store_id > 0)
        {
            $sql .= "AND seller_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->query($sql);
    }
}

?>