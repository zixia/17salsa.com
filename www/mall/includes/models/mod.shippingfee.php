<?php

/**
 * ECMALL: 配送地区实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.shippingfee.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ShippingFee extends Model
{
    var $_shipping_id = 0;
    var $_region_id   = 0;

    /**
     * 构造函数
     */
    function __construct($shipping_id, $region_id, $store_id)
    {
        $this->ShippingFee($shipping_id, $region_id, $store_id);
    }

    function ShippingFee($shipping_id, $region_id, $store_id)
    {
        $this->_shipping_id = intval($shipping_id);
        $this->_region_id   = intval($region_id);
        parent::Model(0, $store_id);
    }

    /**
     * 取得信息
     */
    function get_info()
    {
        $sql = "SELECT sf.* " .
                "FROM `ecm_shipping_fee` AS sf, `ecm_shipping` AS s " .
                "WHERE sf.shipping_id = '" . $this->_shipping_id . "' " .
                "AND sf.region_id = '" . $this->_region_id . "' " .
                "AND sf.shipping_id = s.shipping_id " .
                "AND s.store_id = '" . $this->_store_id . "'";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     *  取得配送费用
     *
     *  @access public
     *  @params none
     *  @return array
     */

    function get_shipping_fee()
    {
        $sql = "SELECT sf.*,s.* " .
                "FROM `ecm_shipping_fee` AS sf, `ecm_shipping` AS s " .
                "WHERE sf.shipping_id = '" . $this->_shipping_id . "' " .
                "AND sf.shipping_id = s.shipping_id " .
                "AND s.store_id = '" . $this->_store_id . "'";
        $info = $GLOBALS['db']->getAll($sql);
        $return_value = array();
        foreach ($info as $fee)
        {
            if ($fee['region_id'] == $this->_region_id)
            {
                return $fee;
            }
            elseif (!$fee['region_id'])
            {
                $return_value = $fee;
            }
        }

        return $return_value;
    }

    /**
     *  获取商品的配送费用
     *
     *  @access
     *  @params
     *  @return
     */

    function get_fee_by_weight($goods_weight_total)
    {
        $info = $this->get_shipping_fee();
        if (!$info['by_weight'])
        {
            /* 不按重量计费 */
            $shipping_fee = $info['first_price'];
        }
        else
        {
            /* 计算配送费用 */
            if ($goods_weight_total > 0)
            {

                /* 小于首重或者续重不要钱 */
                if ($goods_weight_total < $info['first_weight'] || $info['next_weight'] == 0)
                {
                    $shipping_fee = $info['first_price'];
                }
                else
                {
                    $shipping_fee = $info['first_price'] + ceil(($goods_weight_total - $info['first_weight']) / $info['next_weight']) * $info['next_price'];
                }
            }
        }
        return $shipping_fee;
    }

    /**
     * 更新
     */
    function update($arr)
    {
        $where = "shipping_id = '" . $this->_shipping_id . "' " .
                "AND region_id = '" . $this->_region_id . "'";
        return $GLOBALS['db']->autoExecute('`ecm_shipping_fee`', $arr, 'UPDATE', $where);
    }

    /**
     * 删除
     */
    function drop()
    {
        $sql = "DELETE FROM `ecm_shipping_fee` " .
                "WHERE shipping_id = '" . $this->_shipping_id . "' " .
                "AND region_id = '" . $this->_region_id . "'";

        return $GLOBALS['db']->query($sql);
    }
}

?>