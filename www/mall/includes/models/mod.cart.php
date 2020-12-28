<?php

/**
 * ECMALL: 购物车实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.cart.php 6009 2008-10-31 01:55:52Z Garbin $
 */


class Cart
{

    /**
     *  购物车标识
     *
     */

    var $store_id;

    /**
     *  构造函数
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
     *  构造函数
     *
     *  @access public
     *  @params $store_id, $goods_list
     *  @return
     */

    function Cart($store_id, $goods_list = array())
    {
        !is_int($store_id) && $store_id = 0;
        $this->store_id =   $store_id;
        if (!empty($goods_list)) //若为空,则去数据库中取出并填充
        {
            $this->goods_list = $goods_list;
        }
    }

    /**
     *  获得该购物车中的商品列表
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
     *  获得商品总价
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
     *  获取商品商品总数
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
     *  获得打折信息
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
     *  计算购物后所能得到的积分
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
     *  计算可用积分
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
