<?php

/**
 * ECMALL: 地址信息实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.address.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Address extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id = 0)
    {
        $this->Address($id, $store_id);
    }

    function Address($id, $store_id = 0)
    {
        $this->_table = '`ecm_user_address`';
        $this->_key   = 'address_id';
        parent::Model($id, $store_id);
    }

    /**
     * 从地址信息中取得最下级的 region_id
     *
     * @param   array   $address    地址信息
     */
    function get_region_id($address)
    {
        if ($address['region4'] > 0)
        {
            return $address['region4'];
        }
        elseif ($address['region3'] > 0)
        {
            return $address['region3'];
        }
        elseif ($address['region2'] > 0)
        {
            return $address['region2'];
        }
        else
        {
            return $address['region1'];
        }
    }
}

?>