<?php

/**
 * ECMALL: 实体对象基类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.base.php 6057 2008-11-13 09:22:37Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Model
{
    var $_id        = 0;
    var $_table     = '';
    var $_key       = '';
    var $_store_id  = 0;
    var $err        = null;

    /**
     * 构造函数
     */
    function __construct($id, $store_id=0)
    {
        $this->Model($id, $store_id);
    }

    function Model($id, $store_id=0)
    {
        $this->_id          = intval($id);
        $this->_store_id    = intval($store_id);
    }

    /**
     * 取得信息
     *
     * @return array
     */
    function get_info()
    {
        if (!$this->_check())
        {
            return false;
        }

        $sql = "SELECT * FROM " . $this->_table .
                " WHERE " . $this->_key . " = '" . $this->_id . "' ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND store_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 更新对象的资料
     * 调用此函数的前提是 get_info() 不为空
     *
     * @return  int
     */
    function update($arr)
    {
        $where = $this->_key . " = '" . $this->_id . "'";

        if ($this->_store_id > 0)
        {
            $where .= "AND store_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->autoExecute($this->_table, $arr, 'UPDATE', $where);
    }

    /**
     * 删除对象
     * 调用此函数的前提是 get_info() 不为空
     *
     * @return  bool
     */
    function drop()
    {
        $sql = "DELETE FROM " .$this->_table. " WHERE " .$this->_key. " = '" .$this->_id. "'";

        if ($this->_store_id > 0)
        {
            $sql .= "AND store_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 检查数据
     *
     * @return  bool
     */
    function _check()
    {
        if (empty($this->_table))
        {
            $this->err = 'Table name is not defined';
            return false;
        }

        if (empty($this->_key))
        {
            $this->err = 'Key is not defined';
            return false;
        }

        if ($this->_id <= 0)
        {
            $this->err = 'Id is not defined';
            return false;
        }

        return true;
    }

    function _get_store_limit($alias = null)
    {
        if ($this->_store_id > 0)
        {
            return $alias ? ' AND ' . $alias . '.store_id = ' . $this->_store_id . ' ' : ' AND store_id = ' . $this->_store_id . ' ';
        }

        return '';
    }
}
?>
