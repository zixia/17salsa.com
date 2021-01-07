<?php

/**
 * ECMALL: 属性实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.attribute.php 6060 2008-11-13 09:38:39Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Attribute extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id = 0)
    {
        $this->Attribute($id, $store_id);
    }

    function Attribute($id, $store_id = 0)
    {
        $this->_table = '`ecm_attribute`';
        $this->_key   = 'attr_id';
        parent::Model($id, $store_id);
    }

    /**
     * 取得信息
     */
    function get_info()
    {
        $sql = "SELECT a.*, gt.type_name " .
                "FROM `ecm_attribute` AS a, `ecm_goods_type` AS gt " .
                "WHERE a.type_id = gt.type_id " .
                "AND a.attr_id = '" . $this->_id . "' ";
        if ($this->_store_id > 0)
        {
            $sql .= "AND gt.store_id = '" . $this->_store_id . "'";
        }

        return $GLOBALS['db']->getRow($sql);
    }
}

?>