<?php

/**
 * ECMALL: ���͵���ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * ȡ����Ϣ
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
     *  ȡ�����ͷ���
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
     *  ��ȡ��Ʒ�����ͷ���
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
            /* ���������Ʒ� */
            $shipping_fee = $info['first_price'];
        }
        else
        {
            /* �������ͷ��� */
            if ($goods_weight_total > 0)
            {

                /* С�����ػ������ز�ҪǮ */
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
     * ����
     */
    function update($arr)
    {
        $where = "shipping_id = '" . $this->_shipping_id . "' " .
                "AND region_id = '" . $this->_region_id . "'";
        return $GLOBALS['db']->autoExecute('`ecm_shipping_fee`', $arr, 'UPDATE', $where);
    }

    /**
     * ɾ��
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