<?php

/**
 * ECMALL: 品牌实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.brand.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class Brand extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id)
    {
        $this->Brand($id, $store_id);
    }

    function Brand($id, $store_id)
    {
        $this->_table = '`ecm_brand`';
        $this->_key   = 'brand_id';
        parent::Model($id, $store_id);
    }

    /**
     * 设为公共品牌（创建对象时store用0）
     */
    function set_public()
    {
        if ($this->_store_id == 0)
        {
            return $this->update(array('store_id' => 0));
        }
        else
        {
            $this->err = '';
            return false;
        }
    }

    /*
     * 根据给定的ID获得品牌的名称
     *
     * @author  weberliu
     * @param   string      $keywords   查询关键字
     * @param   string      $limit      返回的记录数量
     * @return  array
     */
    function get_brand_name($keywords, $limit)
    {
        $sql = "SELECT brand_name FROM {$this->_table} ".
               " WHERE store_id IN (0, {$this->_store_id}) AND brand_name LIKE '$keywords%'".
               " LIMIT $limit";
        return $GLOBALS['db']->getCol($sql);
    }
}
?>