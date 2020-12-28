<?php

/**
 * ECMALL: 配送方式实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.shipping.php 6064 2008-11-13 10:19:42Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Shipping extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id)
    {
        $this->Shipping($id, $store_id);
    }

    function Shipping($id, $store_id)
    {
        $this->_table = '`ecm_shipping`';
        $this->_key   = 'shipping_id';
        parent::Model($id, $store_id);
    }

    /**
     *  获取配送方式的信息
     *
     *  @param
     *  @return
     */
    function get_info()
    {
        $info = parent::get_info();
        $info['cod_regions_array'] = explode(',', $info['cod_regions']);

        return $info;
    }

    /**
     *  开关
     *
     *  @param  int $status 0为禁用，1为启用
     *  @return int
     */
    function enable($status = 1)
    {
        $status = intval($status) ? 1 : 0;
        $sql = "UPDATE `ecm_shipping` SET enabled={$status} WHERE shipping_id={$this->_id}" . $this->_get_store_limit();

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }
}

?>