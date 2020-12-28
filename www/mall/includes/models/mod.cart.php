<?php

/**
 * ECMALL: ���ﳵʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.cart.php 6009 2008-10-31 01:55:52Z Garbin $
 */


class Cart
{

    /**
     *  ���ﳵ��ʶ
     *
     */

    var $store_id;

    /**
     *  ���캯��
     *
     *  @access public
     *  @params $store_id, $goods_list
     *  @return
     */

    function __construct($store_id, $goods_list = array())
    {
        $this->Cart($store_id, $goods_list);
    }

    /**
     *  ���캯��
     *
     *  @access public
     *  @params $store_id, $goods_list
     *  @return
     */

    function Cart($store_id, $goods_list = array())
    {
        !is_int($store_id) && $store_id = 0;
        $this->store_id =   $store_id;
        if (!empty($goods_list)) //��Ϊ��,��ȥ���ݿ���ȡ�������
        {
            $this->goods_list = $goods_list;
        }
    }

    /**
     *  ��øù��ﳵ�е���Ʒ�б�
     *
     *  @access public
     *  @params
     *  @return Array
     */

    function list_goods()
    {
        if (empty($this->goods_list))
        {
            $store_id_where = $this->store_id ? ' AND c.store_id='.$this->store_id : '';
            $this->goods_list =   $GLOBALS['db']->getAll("SELECT g.give_points,g.goods_name,g.goods_weight,g.max_use_points,gs.default_image,c.*,c.goods_price AS store_price,(goods_price*goods_number) AS goods_amount,s.store_name,gs.spec_name,gs.color_name,gs.color_rgb,gs.stock FROM `ecm_cart` c LEFT JOIN `ecm_store` s ON c.store_id=s.store_id LEFT JOIN `ecm_goods` g ON c.goods_id=g.goods_id LEFT JOIN `ecm_goods_spec` gs ON c.spec_id=gs.spec_id WHERE session_id='{$GLOBALS['sess']->session_id}'{$store_id_where}");
        }
        $rtn_arr = array();
        foreach ($this->goods_list as $_k => $_v)
        {
            $_v['subtotal'] = $_v['goods_price'] * $_v['goods_number'];
            $rtn_arr[$_v['spec_id']] = $_v;
        }

        return $rtn_arr;
    }

    function get_weight()
    {
        $goods_list = $this->list_goods();
        $weight = 0;
        foreach ($goods_list as $_k => $_v)
        {
            $weight += $_v['goods_weight'] * $_v['goods_number'];
        }
        return $weight;
    }

    /**
     *  �����Ʒ�ܼ�
     *
     *  @access public
     *  @params
     *  @return Float
     */

    function get_amount()
    {
        if (!$this->goods_list)
        {
            $this->list_goods();
        }
        $amount = 0;
        foreach ($this->goods_list as $goods)
        {
            $amount += $goods['goods_price'] * $goods['goods_number'];
        }
        return $amount;
    }

    /**
     *  ��ȡ��Ʒ��Ʒ����
     *
     *  @param  none
     *  @return int
     */
    function get_goods_count()
    {
        if (!$this->goods_list)
        {
            $this->list_goods();
        }
        $count = 0;
        foreach ($this->goods_list as $goods)
        {
            $count += $goods['goods_number'];
        }

        return $count;
    }

    /**
     *  ��ô�����Ϣ
     *
     *  @access public
     *  @params
     *  @return Float
     */

    function get_discount()
    {
        $goods_manager  =   new GoodsManager();
        return $goods_manager->get_discount($this->goods_list);
    }

    /**
     *  ���㹺������ܵõ��Ļ���
     *
     *  @access public
     *  @params
     *  @return Int
     */

    function get_points()
    {
        $goods_list = $this->list_goods();
        $points = 0;
        foreach ($goods_list as $goods)
        {
            $points += $goods['give_points'] * $goods['goods_number'];
        }

        return $points;
    }

    /**
     *  ������û���
     *
     *  @access public
     *  @params
     *  @return Int
     */

    function get_usable_points()
    {
        $goods_list = $this->list_goods();
        $points = 0;
        foreach ($goods_list as $goods)
        {
            $points += $goods['max_use_points'] * $goods['goods_number'];
        }

        return $points;
    }
}

?>
