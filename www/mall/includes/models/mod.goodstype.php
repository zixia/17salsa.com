<?php

/**
 * ECMALL: 商品类型实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.goodstype.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class GoodsType extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id)
    {
        $this->GoodsType($id, $store_id);
    }

    function GoodsType($id, $store_id)
    {
        $this->_table = '`ecm_goods_type`';
        $this->_key   = 'type_id';
        parent::Model($id, $store_id);
    }

    /**
     * 取得信息
     */
    function get_info()
    {
        $info = parent::get_info();
        if (!empty($info))
        {
            $sql = "SELECT COUNT(*) FROM `ecm_attribute` WHERE type_id = '" . $this->_id . "'";
            $info['attr_count'] = $GLOBALS['db']->getOne($sql);
        }

        return $info;
    }

    /**
     * 取得属性
     * @author  wj
     * @param void
     * @return array
     */
    function get_attr_list()
    {
        $sql = "SELECT * FROM `ecm_attribute` " .
                "WHERE type_id = '" . $this->_id . "'".
                " ORDER BY sort_order ASC";
        $res = $GLOBALS['db']->query($sql);
        $list = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if ($row['input_type'] == 'select' && $row['value_range'])
            {
                $range = explode("\n", trim($row['value_range']));
                $row['value_range'] = array();
                foreach ($range as $val)
                {
                    $row['value_range'][trim($val)] = trim($val);
                }
            }
            $list[] = $row;
        }

        return $list;
    }
}

?>