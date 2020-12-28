<?php

/**
 * ECMall: 团购购物流程控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: groupcheckout.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/shopping.php');

class GroupCheckoutController extends ShoppingController
{
    var $dont_use_last_info = TRUE;
    var $actor;
    var $actor_info;
    var $group_buy;
    var $group_buy_info;
    var $goods_info;
    var $is_activity = TRUE;
    var $extension_code = 'GROUPBUY';
    var $extension_id   = 0;
    var $_allowed_actions = array('transfer', 'check_out', 'submit_order', 'shopping_cart', 'add_to_cart', 'clear_cart', 'update_cart', 'drop_from_cart', 'validate_coupon_sn', 'dont_use_coupon', 'use_last_info', 'shipping_and_payment', 'update_shipping_and_payment', 'order_review', 'pay', 'do_pay', 'pay_faild_notice', 'evaluation', 'get_shipping_cod_surport');

    /**
     *  构造函数
     *
     *  @access public
     *  @param  string $act
     *  @return void
     */

    function __construct($act)
    {
        $this->GroupCheckoutController($act);
    }
    function GroupCheckoutController($act)
    {
        $disabled_acts = array('shopping_cart', 'validate_points', 'validate_coupon', 'add_to_cart', 'update_cart', 'drop_from_cart', 'anonymous_buy', '_list_goods', '_get_cart', '_drop_cart', '_is_payable_order');
        $this->guest_buy = 0;

        if (array_search($act, $disabled_acts) !== FALSE)
        {
            $this->show_warning('undefined_action');

            return;
        }

        if (!$act)
        {
            $this->redirect('index.php?app=groupcheckout&act=transfer&id=' . $_GET['id']);
            return;
        }

        /* 不允许匿名购物 */
        $_GET['anonymous_buy'] = 0;

        $this->actor_id = $_SESSION['actor_id'];

        if ($act == 'transfer')
        {
            $this->$act();

            return;
        }

        if (!$this->actor_id)
        {
            $this->show_warning('invalid_actor_id');
            return;
        }

        if (!$this->check_login())
        {
            return;
        }

        /* 获取参与者的申请信息 */
        include_once(ROOT_PATH . '/includes/models/mod.groupbuy_actor.php');
        $this->actor = new GroupBuyActor($this->actor_id);
        $this->actor_info = $this->actor->get_info();

        if (!$this->actor_info)
        {
            $this->show_warning('actor_not_found', 'go_to_index', 'index.php');

            return;
        }

        if ($this->actor_info['user_id'] != $_SESSION['user_id'])
        {
            $this->show_warning('actor_not_found');

            return;
        }

        /* 判断该申请记录是否已经制作完成了订单 */
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $_om = new OrderManager();
        $order_info = $_om->get_avtivity_order('GROUPBUY', $this->actor_info['act_id'], $_SESSION['user_id']);
        if ($order_info)
        {
            $this->show_warning('has_expired', 'go_to_index', 'index.php');

            return;
        }

        /* 获取活动信息 */
        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        $this->group_buy = new GroupBuy($this->actor_info['act_id']);
        $this->group_buy_info = $this->group_buy->get_info();
        if (!$this->group_buy_info)
        {
            $this->show_warning('no_this_group_buy');

            return;
        }
        if ($this->group_buy_info['is_finished'])
        {
            $this->show_warning('group_buy_finished');

            return;
        }

        $this->store_id = $this->group_buy_info['store_id'];
        $this->extension_id = $this->group_buy_info['act_id'];
        $this->assign('is_activity', TRUE);

        parent::__construct($act);
    }


    /**
     *  中转，该方法的目的是将要结算的活动申请的ＩＤ记录到SESSION中
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function transfer()
    {
         $_SESSION['actor_id'] = intval($_REQUEST['id']);
         $this->redirect('index.php?app=groupcheckout&act=check_out');
    }

    /**
     *  结算
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function check_out()
    {
         /* 显示配送支付页面 */
         $this->shipping_and_payment();
    }

    /**
     *  提交订单
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function submit_order()
    {
         if (!$this->extension_id && !$this->extension_code)
         {
             $this->show_warning('extension_info_deformity');

             return;
         }

         /* 写入扩展信息 */
         $this->new_order->set_extension_info($this->extension_code, $this->extension_id);

         parent::submit_order();
    }

    function _get_cart()
    {
        if (!$this->actor_info)
        {
            $this->show_warning('invalid_request');
            return;
        }

        $spec_id = $this->actor_info['spec_id'];
        $goods_id= $this->actor_info['goods_id'];
        if (!$spec_id || !$goods_id)
        {
            $this->show_warning('invalid_goods_id');
            return;
        }

        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        $goods = new Goods($goods_id);
        $goods_info = $goods->get_goods_detail($spec_id);
        $goods_info['store_price'] = $this->group_buy_info['price'];
        $goods_info['goods_number']= $this->actor_info['number'];
        $goods_info['goods_price']    = $goods_info['store_price'];
        $goods_info['subtotal'] = $goods_info['store_price'] * $goods_info['goods_number'];

        include_once(ROOT_PATH . '/includes/models/mod.cart.php');
        $cart = new Cart($this->store_id, array($goods_info));

        return $cart;
    }

    function _get_referer()
    {
        return $this->lang('referer_groupbuy');
    }

    function _drop_cart()
    {}

}
?>