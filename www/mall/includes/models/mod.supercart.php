<?php

/**
 * ECMALL: 超级购物车实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.supercart.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/models/mod.goods.php');
include_once(ROOT_PATH . '/includes/models/mod.cart.php');

class SuperCart
{

    /**
     *  绑定的表名
     *
     *  @access
     */
    var $_table = '`ecm_cart`';

    /**
     *  主键
     *
     *  @access
     */
    var $_key = 'rec_id';

    /**
     *  用户UID
     *
     */

    var $_user_id;

    /**
     *  会话ID
     *
     */

    var $_session_id;

    /**
     *  构造函数
     *
     *  @access public
     *  @params
     *  @return
     */

    function __construct()
    {
        $this->SuperCart();
    }

    /**
     *  构造函数
     *
     *  @access public
     *  @params
     *  @return
     */

    function SuperCart()
    {
        $this->_user_id  =   isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $this->_session_id=  $GLOBALS['sess']->session_id;
    }

    /**
     *  获得一个购物车实例
     *
     *  @access public
     *  @params $store_id
     *  @return Cart
     */

    function get_cart($store_id)
    {
        static $instance = array();
        if (empty($instance[$store_id]))
        {
            $instance[$store_id] = new Cart($store_id);
        }

        return $instance[$store_id];
    }

    /**
     *  获得购物车列表
     *
     *  @access public
     *  @params
     *  @return Array
     */

    function list_carts()
    {
        $carts_info  =   $GLOBALS['db']->getAll("SELECT g.max_use_points,g.give_points,c.*,(goods_price*goods_number) AS goods_amount,s.store_name,gs.spec_name,gs.color_name,gs.color_rgb,gs.stock,gs.default_image " .
                                                "FROM {$this->_table} c " .
                                                "LEFT JOIN `ecm_store` s ON c.store_id=s.store_id ".
                                                "LEFT JOIN `ecm_goods` g ON c.goods_id=g.goods_id ".
                                                "LEFT JOIN `ecm_goods_spec` gs ON c.spec_id=gs.spec_id " .
                                                "WHERE c.session_id = '{$this->_session_id}'");
        if (empty($carts_info))
        {
            return;
        }
        $carts       =  array();
        foreach ($carts_info as $cart)
        {
            $cart['subtotal'] = $cart['goods_price'] * $cart['goods_number'];
            if (!isset($carts[$cart['store_id']]['store_name']))
            {
                $carts[$cart['store_id']]['store_name'] = $cart['store_name'];
            }
            $carts[$cart['store_id']]['goods'][$cart['spec_id']]   =   $cart;
            $carts[$cart['store_id']]['goods_amount'] += $cart['goods_amount'];
        }

        return $carts;
    }

    /**
     *  从SuperCart中移除购物车Cart
     *
     *  @access public
     *  @params int $store_id
     *  @return Bool
     */

    function drop_cart($store_id = NULL)
    {
        if (is_numeric($store_id))
        {
            $store_id_limit = " AND store_id={$store_id}";
        }
        $GLOBALS['db']->query("DELETE FROM {$this->_table} WHERE session_id = '{$this->_session_id}'{$store_id_limit}");
        $this->_update_stats();
    }

    /**
     *  清空购物车
     *
     *  @param
     *  @return
     */
    function clear()
    {
        $this->drop_cart();
    }

    /**
     *  更新商品数量
     *
     *  @access public
     *  @params $spec_id,$goods_number
     *  @return boot
     */

    function update_goods_number($spec_id, $goods_number)
    {
        $goods_number = intval($goods_number);
        $spec_id      = intval($spec_id);
        $where  =   " WHERE session_id='{$this->_session_id}' AND spec_id={$spec_id}";
        if ($goods_number <= 0)  //若数量是0则直接删除
        {
            $sql    =   "DELETE FROM {$this->_table}{$where}";
        }
        else        //否则更新~
        {

            $goods = GoodsFactory::build($spec_id);
            $goods_info = $goods->get_goods_detail($spec_id);
            /* 大于库存 */
            if ($goods_number > $goods_info['stock'])
            {
                $this->err = array('goods' => $goods_info['goods_name'] . $goods_info['spec_name'] . $goods_info['color_name'],
                                   'msg'   => 'stock_no_enough');
                return FALSE;
            }
            /*
            赠品
            $gifts = $goods->list_gifts();
            if ($gifts)
            {
                foreach ($gifts as $gift)
                {
                    $gift_where = " WHERE session_id='{$this->_session_id}' AND spec_id={$gift['spec_id']}";
                }
                $gift_sql = "UPDATE {$this->_table} SET goods_number=goods_number+1{$gift_where}";
            }
            */

            $sql    =   "UPDATE {$this->_table} SET goods_number={$goods_number}{$where}";
        }
        $GLOBALS['db']->query($sql);        //更新商品数量
        return TRUE;
    }

    /**
     *  更新购物车
     *
     *  @access public
     *  @params int $store_id
     *  @return
     */

    function update_cart($store_id, $goods_number)
    {
        if (!$goods_number || !$store_id)
        {
            //$this->err = '';

            return TRUE;
        }
        else
        {
            foreach ($goods_number as $_spec_id => $g_n)
            {
                $this->update_goods_number($_spec_id, $g_n);
            }

            $this->_update_stats();

            return TRUE;
        }
    }

    /**
     *  获得购物车状态
     *
     *  @access public
     *  @params
     *  @return Array
     */

    function get_stats()
    {
        if (!isset($_SESSION['cart_stats']))
        {

            return FALSE;
        }

        return explode(',', $_SESSION['cart_stats']);
    }

    /**
     *  清空购物车状态
     *
     *  @access public
     *  @params
     *  @return
     */

    function clear_stats()
    {
        unset($_SESSION['cart_stats']);
    }

    /**
     *  添加商品至购物车

     *  @author scottye
     *  @param  int     $spec_id        要加入购物车的商品的规格ID
     *  @param  int     $goods_number   要加入的数量
     *  @return Bool
     */

    function add_goods($spec_id, $goods_number = 1)
    {
        $goods_number = empty($goods_number) ? 1 : abs(intval($goods_number));
        $store_id   =   $this->goods_exists($spec_id);
        $goods  =   GoodsFactory::build($spec_id);
        if(!$goods)
        {
            $this->err  =   'goods_no_exists';

            return FALSE;
        }
        $goods_descri = addslashes_deep($goods->get_goods_detail($spec_id));

        if ($goods_number > $goods_descri['stock'])
        {
            $this->err = 'goods_not_enough';

            return FALSE;
        }
        if ($goods_descri['store_id'] == $this->_user_id)
        {
            $this->err = 'buy_self_disabled';

            return FALSE;
        }
        /* 是否上架 */
        if (!$goods_descri['is_on_sale'])
        {
            $this->err = 'goods_is_not_on_sale';

            return FALSE;
        }
        if ($goods_descri['is_deny'])
        {
            $this->err = 'goods_is_denied';

            return FALSE;
        }
        $open_stores = Manager::get_open_store();
        if (!in_array($store_id, $open_stores))
        {
            $this->err = 'store_closed';

            return FALSE;
        }
        if ($store_id) //若已存在自己的购物车中,则直接返回True
        {
            $sql    =   "SELECT goods_number ".
                        "FROM {$this->_table} ".
                        "WHERE session_id='{$this->_session_id}' AND spec_id='{$spec_id}'";
            $old_goods_number = $GLOBALS['db']->getOne($sql);
            if ($goods_number > $goods_descri['stock'])
            {
                $this->err = 'goods_not_enough';

                return FALSE;
            }
            $sql    =   "UPDATE {$this->_table}".
                        " SET goods_number={$goods_number} ".
                        "WHERE session_id='{$this->_session_id}' AND spec_id={$spec_id}";
            $GLOBALS['db']->query($sql);
            /*
            $gifts  =   $goods->has_gift();
            if(!empty($gifts))       //若有赠品，则
            {
                $GLOBALS['db']->query("INSERT INTO {$this->_table}() VALUES()goods_number=goods_number+{$goods_number} WHERE spec_id IN(" . implode(',', $gifts) . ") AND session_id='{$this->_session_id}'");
            }
            */
        }
        else                                //否则加入
        {
            if (empty($goods_descri))
            {
                $this->err  = 'goods_doesnt_exists';
                return FALSE;
            }
            $store_id = $goods_descri['store_id'];
            $sql = "INSERT INTO `ecm_cart`(user_id,session_id,store_id,goods_id,spec_id,sku,goods_name,market_price,goods_price,goods_number,is_real) VALUES({$this->_user_id},'{$this->_session_id}',{$goods_descri['store_id']},{$goods_descri['goods_id']},{$spec_id},'{$goods_descri['sku']}','{$goods_descri['goods_name']}',{$goods_descri['market_price']},{$goods_descri['store_price']},{$goods_number},'{$goods_descri['is_real']}')";
            $GLOBALS['db']->query($sql);
            $sql = "UPDATE `ecm_goods` SET cart_volumn=cart_volumn+1 WHERE goods_id={$goods_descri['goods_id']}";
            $GLOBALS['db']->query($sql);
            /*
            $gifts = $goods->list_gifts();
            $giftsql = "INSERT INTO ecm_cart(store_id,user_id,session_id,store_id,goods_id,sku,goods_name,market_price,goods_price,goods_number,goods_attr,is_gift) VALUES";
            foreach ($gifts as $gift)
            {
                $giftsql .= "($store_id,{$this->_user_id},'{$this->_session_id}',{$gift['store_id']},{$gift['goods_id']},'{$gift['sku']}','{$gift['goods_id']}',{$gift['market_price']},0,{$goods_number},'{$gift['goods_attr']}', 1)";
            }
            $GLOBALS['db']->query($giftsql);
            */
        }
        $this->_update_stats();

        return $goods_number;
    }

    /**
     *  从购物车中移除商品
     *
     *  @access public
     *  @params $goods_id
     *  @return Bool
     */

    function drop_goods($spec_id)
    {
       /* 删除商品 */
       $delete_sql  =   "DELETE FROM {$this->_table} WHERE session_id='{$this->_session_id}' AND spec_id ={$spec_id}";
       $GLOBALS['db']->query($delete_sql);

       /* 删除赠品 */
       /*
       $goods = GoodsFactory::build($spec_id);
       $gifts = $goods->has_gifts();
       if ($gifts)
       {
           $gifts_ids = implode(',', $gifts);
           $delete_gifts_sql = "DELETE FROM {$this->_table} WHERE session_id='{$this->_session_id} AND spec_id IN($gifts_ids)'";
           $GLOBALS['db']->query($delete_gifts_sql);
       }
       */
       /* 更新购物车状态 */
       $this->_update_stats();
       return $GLOBALS['db']->affected_rows();
    }

    /**
     *  检查购物车中是否存在该商品
     *
     *  @access public
     *  @params $goods_id
     *  @return Bool
     */

    function goods_exists($spec_id)
    {
        $row = $GLOBALS['db']->getRow("SELECT store_id,spec_id FROM `ecm_cart` WHERE spec_id={$spec_id} AND session_id='{$this->_session_id}'");

        return $row['store_id'] ? $row['store_id'] : FALSE;
    }

    /**
     *  检测购物车是否为空
     *
     *  @access public
     *  @params
     *  @return Bool
     */

    function is_empty()
    {
        $stats  =   $this->get_stats();

        return $stats[0];
    }

    /**
     *  获取状态
     *  @params
     *  @return
     */

    function _get_stats()
    {
        return $GLOBALS['db']->getRow("SELECT SUM(goods_number) AS goods_count,SUM(goods_number*goods_price) AS goods_amount,COUNT(goods_id) AS type_count FROM `ecm_cart` WHERE session_id='{$this->_session_id}'");

    }

    /**
     *  更新购物车状态
     *
     *  @access public
     *  @params
     *  @return
     */
    function _update_stats()
    {
        $cart_stats =   $this->_get_stats();
        $stats = $cart_stats['goods_count'] . ',' . $cart_stats['goods_amount'] . ',' . $cart_stats['type_count'];
        $_SESSION['cart_stats'] =   $stats;
    }

    /**
     *  重新获取购物车状态
     *
     *  @param  none
     *  @return void
     */
    function recount()
    {
        $this->_update_stats();
    }
}

?>