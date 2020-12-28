<?php

/**
 * ECMALL: 购物流程控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: shopping.php 6079 2008-11-19 06:24:20Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/*

进入购物车页面,用户看到他放入购物车的所有商品,在界面上,这些商品是根据Store_id进行分组显示的,每个组即一个表单,可分别结算
    ↓用户点击结算按钮
来到配送支付页面,在该页面,有三大块内容,第一块选择/添加收货人信息,第二块选择配送方式,第三块选择支付方式
    ↓用户填写完成表单(选择或者添加了收货人信息,选择了配送及支付方式),点击"下一步"
来到订单确认页面,该页面不提供信息编辑功能,但有相关修改链接
    ↓用户确认无误,点击提交
用户来到订单提交完成后的页面即订单详情页
    ↓用户点击"在线付款"按钮
系统根据订单的支付方式给出对应的在线付款页面
    ↓用户付款成功
提示等待收货

*/

include_once(ROOT_PATH . '/includes/lib.shopping.php');

class ShoppingController extends ControllerFrontend
{
    var $_allowed_actions = array('shopping_cart', 'add_to_cart', 'clear_cart', 'update_cart', 'drop_from_cart', 'validate_coupon_sn', 'dont_use_coupon', 'use_last_info', 'shipping_and_payment', 'update_shipping_and_payment', 'order_review', 'submit_order', 'pay', 'do_pay', 'pay_faild_notice', 'evaluation', 'get_shipping_cod_surport');

    /**
     *  是否是活动的购物流程
     *
     *  @access
     */

    var $is_activity = FALSE;

    /**
     *  向哪个商店购物
     *
     *  @access
     */

    var $store_id;

    /**
     *  购物车对象
     *
     */

    var $super_cart;

    /**
     *  订单对象
     *
     */

    var $new_order;

    /**
     *  构造函数
     *  @params
     *  @return
     */
    function __construct($act)
    {
        $this->ShoppingController($act);
    }

    /**
     *  构造函数
     *  @params
     *  @return
     */
    function ShoppingController($act)
    {
        $protected_methods = array('_list_goods', '_get_cart', '_drop_cart', '_get_shipping_fee', '_get_pay_fee', '_check_amount', '_is_payable_order');
        $pay_methods       = array('pay', 'do_pay');

        /* 加载语言项 */
        $this->_common_lang[] = 'shopping';

        if (in_array($act, $protected_methods))
        {
            $this->show_warning('undefined');

            return;
        }
        if (!$this->is_activity)
        {
            $this->store_id =   empty($_COOKIE['store_id']) ? 0 : intval($_COOKIE['store_id']);
        }

        $this->guest_buy = intval($_GET['guest_buy']);
        if ($this->guest_buy)
        {
            if (!$this->conf('mall_allow_guest_buy'))
            {
                $this->show_warning('no_surport_guest_buy');

                return;
            }
        }

        $this->assign('guest_buy', $this->guest_buy);
        $this->assign('application', APPLICATION);

        if (!in_array($act, $pay_methods))
        {
            include_once(ROOT_PATH . '/includes/models/mod.order.php');
            include_once(ROOT_PATH . '/includes/models/mod.supercart.php');
            $this->super_cart   =   new SuperCart();        //在此只进行实例化该类,供其它动作使用

            /* 这是一个新订单，所以第一个参数指定为NULL即可 */
            $this->new_order   =   new Order(NULL, $this->store_id);

            /* 非匿名购买时指定此订单购买者在本站的用户ID */
            $this->new_order->set_user_id($_SESSION['user_id']);
        }

        parent::__construct($act);
    }

    /**
     *  查看购物车(大购物车)
     *  @params
     *  @return
     */
    function shopping_cart()
    {
        $carts = $this->super_cart->list_carts();

        $order_info = $this->new_order->get_info();
        if (empty($carts))
        {
            $this->show_warning('cart_is_empty', 'go_index', 'index.php');

            return;
        }
        foreach ($carts as $store_id => $cart)
        {
            $carts[$store_id]['coupon'] = $_SESSION['coupons'][$store_id];
        }
        $this->assign('guest_buy', $this->guest_buy);
        $this->assign('title', $this->lang('view_cart'));

        $this->assign('go_back', $_GET['go_back'] ? TRUE : FALSE);
        $this->assign('carts', $carts);    //取出购物车,并将得到的数据传给视图
        $this->assign('order', $this->new_order->get_info());
        $this->display('shopping_view_cart', 'mall');            //显示购物车详情
    }

    /**
     *  添加至购物车
     *
     *  @author Garbin
     *  @return void
     */
    function add_to_cart()
    {
        $goods_info = $this->_get_goods_info($_GET['spec_id']);
        $store_id   = $goods_info['store_id'];
        if ($this->_is_closed_store($store_id))
        {
            $this->show_warning('store_is_invalid');

            return;
        }
        $add_result = $this->super_cart->add_goods((int)$_GET['spec_id'], (int)$_GET['goods_number']);
        if ($add_result)
        {
            if (IS_AJAX)
            {
                $cart_stats = $this->super_cart->get_stats();
                $this->lang();
                $this->json_result(array('msg'=>$this->str_format('view_cart_confirm', $cart_stats[0], '<span class="cart_price">' . price_format($cart_stats[1])) . '</span>','amount'=>$cart_stats[1], 'count'=>$cart_stats[0]), $add_result === TRUE ? 0 : $cart_stats[0]);

                return;
            }
            $this->show_message('add_to_cart_sucessful', 'view_cart', 'index.php?app=' . APPLICATION . '&act=shopping_cart');
            return;
        }
        else
        {
            if (IS_AJAX)
            {
                $this->json_error($this->lang('add_to_cart_faild') . "\n" . $this->lang($this->super_cart->err));
                return;
            }
            $this->show_warning($this->super_cart->err);
            return;
        }
    }

    /**
     *  清空购物车
     *
     *  @param  none
     *  @return void
     */
    function clear_cart()
    {
        $this->super_cart->clear();
        $this->_drop_coupon_info();
        $this->show_message('clear_cart_sucessful', 'go_to_index', 'index.php');
        return;
    }

    /**
     *  更新购物车
     *  @params
     *  @return
     */
    function update_cart()
    {
        !$this->store_id && $this->store_id = intval($_REQUEST['store_id']);
        $go_checkout = intval($_POST['go_checkout']);
        if (!$this->store_id)
        {
            $this->show_warning('invalid_store_id');

            return;
        }
        $this->super_cart->update_cart($this->store_id, $_POST['goods_number']);

        if (!$this->super_cart->err)
        {
            if (isset($_SESSION['coupons'][$this->store_id]) && $_SESSION['coupons'][$this->store_id]['value'])
            {
                include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
                $cart = $this->_get_cart();
                $goods_amount = $cart->get_amount();
                $coupon =   new Coupon();
                $coupon_info = $coupon->get_info_by_sn($_SESSION['coupons'][$this->store_id]['sn'], $this->store_id);
                if ($goods_amount < $coupon_info['min_amount'] || $_SESSION['coupons'][$this->store_id]['value'] > $goods_amount)
                {
                    $this->_drop_coupon_info();
                }
            }

            if ($go_checkout == 1)
            {
                $go_url = 'index.php?app=' . APPLICATION . '&act=shipping_and_payment';
            }
            elseif ($go_checkout == 2)
            {
                $go_url = 'index.php?app=' . APPLICATION . '&act=order_review&guest_buy=' . intval($_POST['guest_buy']);
            }
            elseif ($go_checkout == 3)
            {
                return;
            }
            else
            {
                $go_url = 'index.php?app=' . APPLICATION . '&act=shopping_cart&go_back=' . intval($_POST['go_back']) . '&guest_buy=' . intval($_POST['guest_buy']);
            }
            $this->redirect($go_url); //更新成功则跳转到购物车页面
        }
        else
        {
            $this->show_message($this->lang($this->super_cart->err['msg']) . '<br />' . $this->super_cart->err['goods']);
            return;
        }
    }

    /**
     *  从购物车中移除商品
     *  @params
     *  @return
     */
    function drop_from_cart()
    {
        if ($this->super_cart->drop_goods((int)$_GET['spec_id']))
        {
            $this->_drop_coupon_info();
            $this->redirect('index.php?app=' . APPLICATION . '&act=shopping_cart&go_back=' . intval($_GET['go_back']));
        }
        else
        {
            $this->show_warning($this->super_cart->err);
            return;
        }
    }

    /**
     *  验证促销码
     *  @params
     *  @return
     */
    function validate_coupon_sn()
    {
        include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
        $this->lang();
        $coupon_sn = empty($_GET['coupon_sn']) ? '' : trim($_GET['coupon_sn']);
        $store_id = intval($_GET['store_id']);
        if ($coupon_sn == '')
        {
            $_SESSION['coupons'][$store_id] = array('value' => 0, 'sn' => '');
            $this->json_error('cancel_use_coupon_sucess',
                              array('store_id' => $store_id, 'close' => true, 'is_cancel' => true));   //取消使用

            return;
        }

        $cart       =   $this->_get_cart($store_id);
        $goods_amount = $cart->get_amount();
        $order_info = $this->new_order->get_info();

        /*
        if ($order_info['coupon_sn'] == $coupon_sn)
        {
            $this->json_error('coupon_has_used', array('store_id' => $store_id));
            //$this->show_warning('coupon_has_used');

            return;
        }
        */

        $coupon =   new Coupon();
        $coupon_info = $coupon->get_info_by_sn($coupon_sn, $store_id);

        /* 判断该优惠券是否有使用次数 */
        if (!$coupon->is_usable())
        {
            //$_SESSION['coupons'][$store_id] = array('value' => 0, 'sn' => '');
            $this->json_error($coupon->_err, array('store_id' => $store_id));

            return;
        }

        /* 判断该订单的商品金额是否大于优惠券的订单限额 */
        if ($goods_amount < $coupon_info['min_amount'])
        {
            $_SESSION['coupons'][$store_id] = array('value' => 0, 'sn' => '');
            $this->json_error($this->str_format('goods_amount_lt_coupon_min_amount', price_format($coupon_info['min_amount'], $this->lang('price_format'))), array('store_id' => $store_id));

            return;
        }

        $money  =   $coupon->get_value();   //获取该优惠券的价值
        if ($money) //若是个有价值的优惠券,则
        {
            if ($money > $goods_amount)
            {
                $money = $goods_amount;
            }
            $_SESSION['coupons'][$store_id] = array('value' => $money, 'sn' => $coupon_sn);
            $this->json_result(array('store_id'=>$store_id), price_format($money));         //返回数据
            return;
        }
        else
        {
            //$_SESSION['coupons'][$store_id] = array('value' => 0, 'sn' => '');
            $this->json_error('invalid_coupon_sn',
                              array('store_id' => $store_id));   //错误
            return;
        }

    }

    /**
     *  取消使用优惠券
     *
     *  @access public
     *  @param  none
     *  @return void
     */

    function dont_use_coupon()
    {
        $this->new_order->use_coupon('', 0);
        $this->new_order->save_session();

        $this->show_message('clear_coupon_sucess');
    }

    /**
     *  使用上一次购买时所使用的配送支付信息
     *
     *  @author Garbin
     *  @return void
     */
    function use_last_info()
    {
        if (!$_SESSION['user_id'])
        {
            $this->show_warning('no_login');

            return;
        }
        if (!$this->store_id)
        {
            $this->show_warning('invalid_store_id');

            return;
        }
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $order_manager = new OrderManager();

        /* 取得该用户上一次订单 */
        $last_order_info = addslashes_deep($order_manager->get_last_order_by_user($_SESSION['user_id']));
        if (!empty($last_order_info))
        {
            /* 初始化变量 */
            $need_inv       = 0;

            /* 当前使用的购物车实例 */
            $cart = $this->_get_cart();
            $goods_amount = $cart->get_amount();

            /* 获取当前的订单信息 */
            $order  =   $this->new_order->get_info();

            if (!$cart->list_goods())
            {
                $this->show_warning('cart_is_empty', 'go_index', 'index.php');

                return;
            }

            /* 写入收货人信息 */
            $this->new_order->write_consignee_info(array(
                'consignee' => $last_order_info['consignee'],
                'region'    => $last_order_info['region'],
                'region_id' => $last_order_info['region_id'],
                'address'   => $last_order_info['address'],
                'zipcode'   => $last_order_info['zipcode'],
                'email'   => $last_order_info['email'],
                'office_phone'   => $last_order_info['office_phone'],
                'home_phone'   => $last_order_info['home_phone'],
                'mobile_phone'   => $last_order_info['mobile_phone'],
                'sign_building'   => $last_order_info['sign_building'],
                'best_time'   => $last_order_info['best_time'],
            ));

            /* 判断配送方式是否可用 */
            include_once(ROOT_PATH . '/includes/models/mod.shipping.php');
            $shipping = new Shipping($last_order_info['shipping_id'], $this->store_id);
            $ship_info = $shipping->get_info();
            if (empty($ship_info) || !$ship_info['enabled'])
            {
                $this->new_order->save_session();
                $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

                return;
            }

            /* 写入配送信息 */
            $this->new_order->write_shipping_info(array(
                'shipping_id' => $ship_info['shipping_id'],
                'shipping_name' => $ship_info['shipping_name']
            ));

            /* 判断支付方式是否可用 */
            include_once(ROOT_PATH . '/includes/models/mod.payment.php');
            $payment = new Payment($last_order_info['pay_id'], $this->store_id);
            $pay_info = $payment->get_info();
            if (empty($pay_info))
            {
                $this->new_order->save_session();
                $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

                return;
            }
            $set_modules = true;
            include(ROOT_PATH . '/includes/payment/' . $pay_info['pay_code'] . '.php');
            if ($modules[0]['currency'] != 'all' && (is_array($modules[0]['currency']) && !in_array(CURRENCY, $modules[0]['currency'])))
            {
                $this->new_order->save_session();
                $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

                return;
            }

            /* 写入支付信息 */
            $this->new_order->write_payment_info(array(
                'pay_id' => $pay_info['pay_id'],
                'pay_name' => $pay_info['pay_name']
            ));


            /* 发票费用 */
            if ($last_order_info['inv_fee'] > 0) //如果他上一个订单的发票费用不为0,我们就认为,他需要开发票
            {
                $inv_info = array();
                $inv_info['inv_payee']      = $last_order_info['inv_payee'];
                $inv_info['inv_type']       = $last_order_info['inv_type'];
                $inv_info['inv_content']    = $last_order_info['inv_content'];
                $this->new_order->write_inv_info($inv_info);
                $need_inv = 1;
                setcookie('need_inv', 1);
            }
            else
            {
                setcookie('need_inv', 0);
            }

            $this->new_order->save_session();
            $this->redirect('index.php?app=' . APPLICATION . '&act=order_review');

            return;
        }
        else
        {
            $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

            return;
        }
    }

    /**
     *  查看配送和支付信息
     *  @author wj
     *  @return void
     */
    function shipping_and_payment()
    {
        if(!$this->check_login())
        {
            return;
        }
        if (!$this->dont_use_last_info)
        {
            /* 如果是登录状态，则取出最后一次的订单信息，然后跳过该步，直接到订单确认页 */
            if ($_SESSION['user_id'] && !$_GET['go_back'])
            {
                $this->redirect('index.php?app=' . APPLICATION . '&act=use_last_info');
                return;
            }
        }
        if (!$this->store_id)
        {
            $this->show_warning('invalid_store_id');

            return;
        }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) //已登录
        {
            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            $user = new User($_SESSION['user_id']);
            $address_list = $user->get_address_list();
            $this->assign('user_address', $address_list);
            $this->assign('address_data', 'var addressData = ' . ecm_json_encode($address_list) . ';');
        }

        $cart = $this->_get_cart();

        if (!$cart->list_goods())
        {
            $this->show_warning('cart_is_empty', 'go_index', 'index.php');

            return;
        }

        $order_info = array();
        if ($_GET['go_back'])
        {
            $order_info = stripslashes_deep($this->new_order->get_info());                  //获得存在于会话中的数据
        }
        include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
        include_once(ROOT_PATH . '/includes/manager/mng.shipping.php');

        /* 取得支付方式 */
        $payment_manager = new PaymentManager($this->store_id);
        $order_payments  = $payment_manager->get_payment_list();
        if (empty($order_payments))
        {
            $this->show_warning('store_no_set_payments', 'go_to_index', 'index.php', 'go_to_store', 'index.php?app=store&amp;store_id=' . $this->store_id);

            return;
        }
        $online_payments = array();
        $offline_payments = array();
        $price_format = $this->lang('price_format');
        if ($order_payments)foreach ($order_payments as $_k => $_v)
        {
            $_v['pay_fee'] = strpos($_v['pay_fee'], '%') !== FALSE ? $_v['pay_fee'] : price_format($_v['pay_fee'], $price_format) ;
            if ($_v['is_online'])
            {
                $set_modules = true;
                $modules     = array();
                include(ROOT_PATH . '/includes/payment/' . $_v['pay_code'] . '.php');
                if ($modules[0]['currency'] == 'all' || (is_array($modules[0]['currency']) && in_array(CURRENCY, $modules[0]['currency'])))
                {
                    $online_payments[$_v['pay_id']] = $_v;

                }
            }
            else
            {
                $offline_payments[$_v['pay_id']] = $_v;
            }
        }

        if (!empty($online_payments) && isset($online_payments['tenpay']))
        {
            /* 将财付通放在支付列表的第一位 */
            $tenpay = array($_k => $_v);
            unset($online_payments[$_k]);
            $online_payments = array_merge($tenpay, $online_payments);
        }

        /* 取得配送方式 */
        $shipping_manager = new ShippingManager($this->store_id);
        $shipping_methods  = $shipping_manager->get_enabled();
        if (empty($shipping_methods))
        {
            $this->show_warning('store_no_set_shippings', 'go_to_index', 'index.php');

            return;
        }
        include_once(ROOT_PATH . '/includes/manager/mng.region.php');
        $region_mng = new RegionManager(0);
        foreach ($shipping_methods['data'] as $_k=>$_v)
        {
            if ($_v['cod_regions'])
            {
                $shipping_methods['data'][$_k]['cod_regions_name'] = $region_mng->get_regions($_v['cod_regions']);
            }
        }

        $address_id = $_GET['address_id'] ? intval($_GET['address_id']) : $_SESSION['address_id'] ;
        $inv_options = explode("\n", $this->conf('store_inv_content', $this->store_id));
        $inv_enable = 0;
        foreach ($inv_options as $_v)
        {
            $_v = str_replace("\r", '', trim($_v));
            if ($_v)
            {
                $inv_content_options[$_v] = $_v;
            }
        }
        unset($inv_options);
        if ($this->conf('store_inv_enable', $this->store_id) && !empty($inv_content_options))
        {
            $inv_enable = 1;
        }


        $best_time_options[$this->lang('best_time_all')]= $this->lang('best_time_all');
        $best_time_options[$this->lang('best_time_workday')]= $this->lang('best_time_workday');
        $best_time_options[$this->lang('best_time_offday')]= $this->lang('best_time_offday');

        $this->assign('inv_enable', $inv_enable);
        $this->assign('inv_content_options', $inv_content_options);
        $this->assign('inv_content', $order_invo['inv_content']);
        $this->assign('address_id', $address_id);
        $this->assign('order', $order_info);
        $this->assign('online_payments', $online_payments);
        $this->assign('offline_payments', $offline_payments);
        $this->assign('shipping', $shipping_methods['data']);
        $this->assign('title', $this->lang('shipping_and_payment'));
        $this->assign('best_time_options', $best_time_options);

        /* 显示配送和支付信息填写表单 */
        $this->display('shipping_payment', 'mall');
    }

    /**
     *  更新配送和支付信息
     *
     *  @author Garbin
     *  @return
     */
    function update_shipping_and_payment()
    {
        if(!$this->check_login())
        {
            return;
        }
        if (!$this->store_id)
        {
            $this->show_warning('invalid_store_id');
            return;
        }
        if ($_POST)
        {


            $order = $this->new_order->get_info();

            $cart = $this->_get_cart();

            $goods_amount = $cart->get_amount();

            if (!$cart->list_goods())
            {
                $this->show_warning('cart_is_empty', 'go_index', 'index.php');

                return;
            }

            include_once(ROOT_PATH . '/includes/manager/mng.region.php');

            $address_id = intval($_POST['address_id']);
            $use_new_address = !isset($_COOKIE['use_new_address']) ? 0 : intval($_COOKIE['use_new_address']);
            $need_inv = !isset($_COOKIE['need_inv']) ? 0 : intval($_COOKIE['need_inv']);

            $_shipping_id = empty($_POST['shipping']['shipping_id']) ? 0 : intval($_POST['shipping']['shipping_id']);
            if (!$_shipping_id)
            {
                $this->show_warning('please_select_shipping_method');

                return;
            }
            $_pay_id     = empty($_POST['payment']['pay_id'])? 0 : intval($_POST['payment']['pay_id']);
            if (!$_pay_id)
            {
                $this->show_warning('please_select_payment');

                return;
            }
            if ($use_new_address)
            {
                $consignee_info = $_POST['consignee_info'];
                $region_id = intval($_POST['region_id']);
                $consignee_info['region_id'] = $region_id;
            }
            else
            {
                if (!$address_id)
                {
                    $this->show_warning('invalid_address_info');

                    return;
                }
                /* 获取收货地址 */
                include_once(ROOT_PATH . '/includes/models/mod.address.php');
                $Address = new Address($address_id, 0);
                $consignee_info = addslashes_deep($Address->get_info());
                $region_id = $Address->get_region_id($consignee_info);
                $consignee_info['region_id'] = $region_id;
            }
            $_SESSION['address_id'] = $address_id;
            setcookie('address_id', $address_id);
            if (!$consignee_info['region_id'])
            {
                $this->show_warning('region_id_required');

                return;
            }

            if (!$consignee_info['mobile_phone'] && !$consignee_info['home_phone'])
            {
                $this->show_warning('phone_number_required');

                return FALSE;
            }

            /* 获取地区 */
            $region_manager = new RegionManager(0);
            $consignee_info['region'] = $region_manager->get_ancestors_name($region_id);

            /* 至此，收货人信息验证完毕，写入收货人信息 */
            $this->new_order->write_consignee_info($consignee_info);

            /* 配送信息 */
            $this->new_order->write_shipping_info($_POST['shipping']);

            /* 支付方式 */
            $this->new_order->write_payment_info($_POST['payment']);
            $inv_info = array();
            $inv_info['inv_payee']      = trim($_POST['inv_info']['inv_payee']);
            $inv_info['inv_content']    = trim($_POST['inv_info']['inv_content']);
            //setcookie('need_inv', $need_inv);

            /* 写入发票信息 */
            $this->new_order->write_inv_info($inv_info);

            /* 订单附言 */
            $this->new_order->set('post_script', trim($_POST['post_script']));

            /* 保存会话 */
            if($this->new_order->has_err())
            {
                $msg = '';
                foreach($this->new_order->err as $err)
                {
                    $msg .= $this->lang($err) . '<br />';
                }
                $this->show_warning($msg);

                return;
            }
            $this->new_order->save_session();

            /* 如果是新增的地址，且用户是登录状态，则保存该地址到收货地址中 */
            if ($_SESSION['user_id'] && $_POST['save_address'] && $use_new_address)
            {
                /* 登录状态下要保存其新增的收货地址 */
                include_once(ROOT_PATH . '/includes/models/mod.user.php');
                $user = new User($_SESSION['user_id']);
                $address_count = $user->get_address_count();
                if ($address_count < $this->conf('mall_max_address_num'))
                {
                    if (!$consignee_info['region1'] && $consignee_info['region_id'])
                    {
                        $region_info = $region_manager->get_ancestors($consignee_info['region_id']);
                        $i = 1;
                        foreach ($region_info as $_r)
                        {
                            $consignee_info['region' . $i] = $_r['region_id'];
                            $i++;
                        }
                    }
                    $user->add_address($consignee_info);
                }
            }

            $this->redirect("index.php?app=" . APPLICATION . "&act=order_review&guest_buy={$this->guest_buy}");
        }
    }

    /**
     *  订单确认（结算）
     *
     *  @author wj
     *  @return void
     */
    function order_review()
    {
        if(!$this->check_login())
        {
            return;
        }

        /* 获取商品信息 */
        $goods  =   $this->_list_goods();

        if (!$goods)
        {
            $this->show_warning('cart_is_empty', 'go_index', 'index.php');

            return;
        }

        $order_info = $this->new_order->get_info();

        if ($_SESSION['user_id'])
        {
            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            $cur_user = new User($_SESSION['user_id']);
            $user_info = $cur_user->get_info();
            $this->assign('feed_status', sprintf('%04b', $user_info['default_feed']));
        }

        /* 计算订单费用信息 */
        $charge_info=  $this->_fee_count($order_info);
        $this->new_order->write_charge_info($charge_info);

        /* 优惠券 */
        $coupon = isset($_SESSION['coupons'][$this->store_id]) ? $_SESSION['coupons'][$this->store_id] : array('value' => 0, 'sn' => '');
        $this->new_order->use_coupon($coupon['sn'], $coupon['value']);
        $this->new_order->save_session();

        /* 判断是否有UCHOME */
        $has_uchome = has_uchome();
        if ($has_uchome)
        {
            $this->assign('shopping_send_feed' , $this->str_format('shopping_send_feed', uc_home_url($_SESSION['user_id'])));
        }

        $order_info = stripslashes_deep($this->new_order->get_info());
        if ($this->conf('store_inv_enable', $this->store_id) && $this->conf('store_inv_content', $this->store_id))
        {
            $this->assign('inv_enable', 1);
        }
        $this->assign('need_inv', intval($_COOKIE['need_inv']));
        $this->assign('guest_buy', $this->guest_buy);
        $this->assign('order', $order_info);
        $this->assign('goods', $goods);
        $this->assign('title', $this->lang('order_review'));
        $this->assign('has_uchome', $has_uchome ? 'true' : 'false');
        $this->display('shopping_order_review', 'mall');
    }

    /**
     *  提交订单
     *
     *  @author Garbin
     *  @return void
     */
    function submit_order()
    {
        if(!$this->check_login())
        {
            return;
        }

        /* 初始化店铺实例 */
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $store = new Store($this->store_id);

        /* 判断是否是购买自己的商品 */
        if ($_SESSION['user_id'] ==  $this->store_id)
        {
            $this->_drop_cart();
            $this->show_warning('buy_self_disabled');

            return;
        }

        $this->_get_cart();
        /* 获取保存在会话中的订单数据 */
        $info = $this->new_order->get_info();

        /* 将购物车中的商品列表写入订单 */
        $goods_info = $this->_list_goods();
        if (!$goods_info)
        {
            $this->show_warning('cart_is_empty', 'go_index', 'index.php');

            return;
        }
        foreach ($goods_info as $_k => $_v)
        {
            if ($_v['is_deny'])
            {
                $this->show_warning('has_denied_goods');

                return FALSE;
            }
        }
        $this->new_order->set_goods(addslashes_deep($goods_info));
        include_once(ROOT_PATH . '/includes/models/mod.payment.php');

        /* 检查订单所使用的支付方式是否是货到付款 */
        $payment = new Payment($info['pay_id'], $this->store_id);
        $pay_info = $payment->get_info();

        if ($pay_info['is_cod'])
        {
            /* 如果是货到付款，则将订单状态置为已提交 */
            $this->new_order->set_status(ORDER_STATUS_SUBMITTED);
        }
        else
        {
            /* 否则将订单状态置为待付款 */
            $this->new_order->set_status(ORDER_STATUS_PENDING);
        }

        $this->new_order->set('referer', $this->_get_referer());

        $this->new_order->set('is_anonymous', $_GET['logon_anonymous_buy'] ? 1 : 0);
        $this->new_order->set('user_ip', real_ip());

        $new_order_id = $this->new_order->submit();

        /* 提交订单到数据库 */
        if(!$new_order_id)
        {
            if (is_array($this->new_order->err))
            {
                foreach ($this->new_order->err as $err)
                {
                    $msg .= $this->lang($err);
                }
            }
            else
            {
                $msg = $this->new_order->err;
            }
            $this->show_warning($msg, $this->lang('go_back'), 'index.php?app=shopping&act=shipping_and_payment');

            return;
        }

        /* 清空在该店的购物车内的所有内容 */
        $this->_drop_cart();

        /* 清空优惠信息 */
        $this->_drop_coupon_info();

        /* 若有使用优惠券，则将优惠券的使用次数减1 */
        if ($info['coupon_sn'])
        {
            include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
            $coupon = new Coupon();
            if($coupon->init_by_sn($info['coupon_sn'], $this->store_id))
            {
                $coupon->update_usable_times(-1);
            }
        }

        /* 更新商品库存 */
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        $goods = new Goods(0, 0);
        foreach ($goods_info as $_k => $_v)
        {
            $goods->set_stock($_v['spec_id'], '-' . $_v['goods_number']);
        }

        /* 更新进单数 */
        $goods_index_by_goods_id = array();
        foreach ($goods_info as $_k => $_v)
        {
            $goods_index_by_goods_id[$_v['goods_id']] = $_v['goods_id'];
        }
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $_g_m = new GoodsManager(0);
        $_g_m->add_order_volumn($goods_index_by_goods_id);

        /* 更新店铺订单总数 */
        $store->update_order_count(1);
        $store_info = $store->get_info();
        if ($_SESSION['user_id'] && $_GET['send_feed'] && !$_GET['logon_anonymous_buy'])
        {
            /* send feed to uc */
            $site_url = site_url();
            $feed_info['icon']              =   'goods';
            $feed_info['user_id']           =   $_SESSION['user_id'];
            $feed_info['user_name']         =   addslashes($_SESSION['user_name']);
            $feed_info['title']['template'] =   $this->lang('feed_buy_goods_title');
            $feed_info['body']['template']  =   $this->lang('feed_buy_goods_message');

            foreach ($goods_info as $v)
            {
                $link_url = $site_url . '/index.php?app=goods&id=' . $v['goods_id'];
                $feed_info['body']['data']['subject'] .= '<a href="' . $link_url . '" target="_blank">' . $v['goods_name'] . '</a> &nbsp; ';
                $feed_info['images'][] = array('url' => $site_url . '/image.php?file_id=' . $v['default_image'] . '&hash_path=' . md5(ECM_KEY . $v['default_image'] . 100 . 100) . '&width=100&height=100', 'link' => $link_url);
            }
            $feed_info['body']['data']['store'] = '<a href="' . $site_url . '/index.php?app=store&store_id=' . $this->store_id . '" target="_blank">' . $this->conf('store_name', $this->store_id) . '</a>';
            $feed_info['body']['data']['price'] = $info['order_amount'];
            $feed_info['body']['data']['time']  = local_date($this->conf('mall_time_format_complete'));
            add_feed($feed_info);
        }
        $info['order_sn'] = $this->new_order->_order_sn;
        $info['add_time'] = $this->new_order->_add_time;

        uc_call('uc_pm_send', array(0, $this->store_id, $this->lang('new_order_notify'), $this->str_format('new_order_notify_content', $_SESSION['user_name'], date('Y-m-d'), price_format($info['order_amount']), $info['order_sn'], site_url() . '/admin.php?app=order&act=change_status&order_id=' . $new_order_id), 1));

        /* 发送订单信息给买家 */
        $this->send_mail($info['email'], 'new_order_notify', array('order'=>$info,
                                                                   'store_url' => site_url() . "/index.php?app=store&store_id=" . $store_info['store_id'],
                                                                   'boss' => $store_info['store_name'],
                                                                   'mall_name' => $this->conf('mall_name'),
                                                                   'pay_url'=> site_url() . '/index.php?app=shopping&act=pay&order_sn=' . $info['order_sn']));
        /* 发送新订单通知给卖家 */
        $this->send_mail($store_info['email'], 'seller_new_order_notify', array('order'=>$info,
                                                                                'store_admin_url' => site_url() . "/admin.php",
                                                                                'parse_order_url' => site_url() . "/admin.php?app=order&act=change_status&order_id={$new_order_id}",
                                                                                'boss' => $store_info['store_name'],
                                                                                'mall_name' => $this->conf('mall_name')));

        /* 跳转到order_info查看订单详情并给出在线支付链接 */
        ecm_setcookie('shopping_send_mail', 1);
        $this->redirect('index.php?app=shopping&act=pay&order_sn=' . $info['order_sn']);


        return TRUE;
    }

    /**
     *  支付页面
     *
     *  @access public
     *  @params none
     *  @return void
     */
    function pay()
    {
        $order_sn   =   trim($_GET['order_sn']);
        if ($_COOKIE['shopping_send_mail'] == '1')
        {
            define('SEND_MAIL', 1);
            ecm_setcookie('shopping_send_mail', 0);
        }
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $order  =   new Order($order_sn, NULL, 'order_sn');
        $order_info = $order->get_info_with_payment();

        if (!$this->_is_payable_order($order_info))
        {
            return;
        }
        $payment =& $order_info;

        $this->assign('pay_note', $this->str_format('pay_note', $order_info['pay_name']));
        $this->assign('order', $order_info);
        $this->assign('order_goods', $order->list_goods());
        $this->assign('pay_form', $pay_form);
        $this->assign('title', $this->lang('pay'));
        $this->display('shopping_pay', 'mall');
    }

    /**
     *  支付中转页面
     *
     *  @access
     *  @param
     *  @return
     */

    function do_pay()
    {
        $order_sn   =   trim($_GET['order_sn']);

        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $order  =   new Order($order_sn, NULL, 'order_sn');
        $order_info = $order->get_info_with_payment();
        if (!$this->_is_payable_order($order_info))
        {
            return;
        }
        $payment =& $order_info;

        /* 生成支付代码 */
        $payment_name = $payment['pay_code'];
        include_once(ROOT_PATH . '/includes/payment/' . $payment['pay_code'] . '.php');
        $pay_obj = new $payment_name($payment['pay_id'], $order_info['store_id']);
        if ((is_array($pay_obj->currency) && !in_array(CURRENCY, $pay_obj->currency)) || (is_string($pay_obj->currency) && $pay_obj->currency != 'all'))
        {
            $this->show_warning('pay_disabled_currency', 'go_store_index', 'index.php?' . $order_info['store_id'], 'comment_to_store', 'index.php?app=store&amp;act=comment&amp;store_id=' . $order_info['store_id']);

            return false;
        }
        $pay_info= $pay_obj->get_info();
        if (!$pay_info)
        {
            $this->show_warning('pay_disabled', 'go_store_index', 'index.php?' . $order_info['store_id'], 'comment_to_store', 'index.php?app=store&amp;act=comment&amp;store_id=' . $order_info['store_id']);

            return false;
        }

        $pay_form= $pay_obj->get_code($order);
        if ($pay_form === FALSE)
        {
            $this->show_warning($pay_obj->err);

            return FALSE;
        }

        $this->assign('pay_form', $pay_form);
        $this->display('shopping_pay_form', 'mall');
    }

    /**
     *  支付失败提示信息
     *
     *  @author Garbin
     *  @return void
     */
    function pay_faild_notice()
    {
        $this->show_message('pay_faild_content', 'go_on_pay', $_SERVER['HTTP_REFERER'], 'view_order_list', 'index.php?app=member&amp;act=order_view', 'go_to_index', 'index.php');
    }

    /**
     *  评价
     *
     *  @author Garbin
     *  @param  none
     *  @return void
     */
    function evaluation()
    {
        if (!$this->check_login())
        {
            return;
        }

        $order_id = intval($_POST['order_id']);
        $rank     = intval($_POST['rank']);
        $is_add_friend = intval($_POST['is_add_friend']);
        $comment  = trim($_POST['comment']);

        if (!$order_id)
        {
            $this->show_warning('invalid_order_id');

            return;
        }
        if ($rank < 1 || $rank > 3)
        {
            $this->show_warning('out_of_range');

            return;
        }

        $order = new Order($order_id);
        $order_info = $order->get_info_with_payment();
        if ($order_info['user_id'] != $_SESSION['user_id'])
        {
            $this->show_warning('not_your_order');

            return;
        }

        if ($order_info['order_status'] != ORDER_STATUS_SHIPPED || $order_info['seller_evaluation'])
        {
            $this->show_warning('not_evaluable_order');

            return;
        }

        /* 计算信用积分 */
        include_once(ROOT_PATH . '/includes/manager/mng.credit.php');
        $credit_manager = new CreditManager();
        $credit_manager->init($order, 'seller', $rank);
        $credit_manager->set_conf(array( 'mall_store_repeat_limit' => $this->conf('mall_store_repeat_limit'),
                                         'mall_goods_repeat_limit' => $this->conf('mall_goods_repeat_limit'),
                                         'mall_min_goods_amount'   => $this->conf('mall_min_goods_amount'),
                                         'mall_max_goods_amount'   => $this->conf('mall_max_goods_amount')));

        $is_invalid = $credit_manager->is_invalid();
        $credits    = $credit_manager->get_credits();
        $order->add_seller_credit($credits);

        $order->seller_evaluation($rank);
        $order->seller_comment($comment);
        $order->set_status(ORDER_STATUS_DELIVERED);
        $order->submit();

        $seller =& $credit_manager->get_body();

        /* 若有计算信用积分 */
        if (!$is_invalid && $credits != 0)
        {
            /* 刷新卖家的总信用积分 */
            $seller->update_seller_credit();
        }

        /* 加好友 */
        if ($is_add_friend)
        {
            $value = uc_call('uc_friend_add', array($_SESSION['user_id'], $order_info['store_id']));
        }

        /* 发送PM给卖家 */
        uc_call('uc_pm_send', array(0, $order_info['store_id'], $this->lang('to_seller_evaluation_notify'), $this->str_format('to_seller_evaluation_notify_contents', $_SESSION['user_name'], $order_info['order_sn']), 1));

        /* 发送Email给卖家 */
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $store = new Store($order_info['store_id']);
        $store_info = $store->get_info();
        $this->send_mail($store_info['email'], 'to_seller_evaluation_notify', array('order'=>$order_info,
                                                                                    'seller' => $store_info['store_name'],
                                                                                    'buyer'=> $order_info['user_name']));

        /* 记录订单日志 */
        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
        $order_logger = new OrderLogger($order_info['store_id']);
        $order_logger->write(array('action_user' => '0',
                                   'order_id'    => $order_info['order_id'],
                                   'order_status'=> ORDER_STATUS_DELIVERED,
                                   'action_note' => $this->lang('buyer_delivered'),
                                   'action_time' => gmtime()));

        $this->show_message('evaluate_sucess');
        return;
    }

    /**
     *  检查是否已登录
     *
     *  @access public
     *  @params none
     *  @return bool
     */
    function check_login()
    {
        /* 不允许匿名购买 */
        if (!$_SESSION['user_id'] && !$this->guest_buy)
        {
            $this->redirect('index.php?app=member&act=login&from=shopping&ret_url=' .urlencode('index.php?' . $_SERVER['QUERY_STRING']));

            return FALSE;
        }

        return TRUE;
    }


    /**
     *  获取配送方式是否支持货到付款
     *
     *  @author weberliu
     *  @params none
     *  @return void
     */
    function get_shipping_cod_surport()
    {
        $region_id     = intval($_GET['region_id']);
        $shipping_id   = intval($_GET['shipping_id']);

        if (!$region_id || !$shipping_id || !$this->store_id)
        {
            $this->json_error('');
            return;
        }
        else
        {
            include_once(ROOT_PATH . '/includes/models/mod.shipping.php');
            include_once(ROOT_PATH . '/includes/manager/mng.region.php');

            $shipping  = new Shipping($shipping_id, $this->store_id);
            $info = $shipping->get_info();

            if (!$info)
            {
                $this->json_error('');

                return;
            }

            $region_manager = new RegionManager();
            $region_info    = $region_manager->get_region($region_id);
            $cod_regions    = explode(',', $info['cod_regions']);

            if ($cod_regions &&
                (in_array($region_id, $cod_regions) ||
                in_array($region_info['parent_id'], $cod_regions)))
            {
                $this->json_result('');
            }
            else
            {
                $this->json_error('');
            }
        }
    }

    /**
     *  获取商品列表
     *
     *  @access protected
     *  @params none
     *  @return array
     */

    function _list_goods()
    {
        $cart   =   $this->_get_cart();
        return $cart->list_goods();
    }

    /**
     *  获得当前要结算的购物车对象
     *
     *  @access protected
     *  @param  none
     *  @return Cart
     */
    function _get_cart($store_id = NULL)
    {
        static $cart = NULL;
        if ($cart === NULL)
        {
            $store_id = $store_id === NULL ? $this->store_id : $store_id;
            $cart = $this->super_cart->get_cart($store_id);
        }

        return $cart;
    }


    /**
     *  清空购物车
     *
     *  @access
     *  @param
     *  @return
     */
    function _drop_cart()
    {
        $this->super_cart->drop_cart($this->store_id);
    }

    /**
     *  判断该订单是否是可支付的订单
     *
     *  @access public
     *  @param  array $order_info
     *  @return bool
     */

    function _is_payable_order($order_info)
    {
        if ($order_info['order_status'] == ORDER_STATUS_SUBMITTED)
        {
            return TRUE;
        }
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('has_paid', 'view_order', 'index.php?app=member&act=order_detail&id=' . $order_info['order_id']);

            return FALSE;
        }

        return TRUE;
    }

    /**
     *  获取配送费用
     *
     *  @access protected
     *  @param  int $shipping_id
     *  @param  int $region_id
     *  @return float
     */
    function _get_shipping_fee($goods_count, $shipping_id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.shipping.php');

        if ($goods_count <= 0)
        {
            return 0;
        }

        /* 配送费用 */
        $shipping = new Shipping($shipping_id, $this->store_id);
        $info = $shipping->get_info();
        if (!$info || !$info['enabled'])
        {
            $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

            return FALSE;
        }

        $shipping_fee = $goods_count > 1 ? $info['shipping_fee'] + ($goods_count - 1) * $info['surcharge'] : $info['shipping_fee'];

        return $shipping_fee;
    }

    /**
     *  获取支付手续费
     *
     *  @access public
     *  @param  int $pay_id
     *  @param  int $order_amount
     *  @return float
     */

    function _get_pay_fee($pay_id, $order_amount)
    {
        $pay_fee = get_pay_fee($this->store_id, $pay_id, $order_amount);
        if ($pay_fee === false)
        {
            $this->redirect("index.php?app=" . APPLICATION . "&act=shipping_and_payment&go_back=1");

            return false;
        }

        return $pay_fee;
    }

    /**
     *  检查总价及优惠是否有效
     *
     *  @param  float $goods_amount
     *  @param  float $coupon_value
     *  @return bool
     */

    function _check_amount($goods_amount, $coupon_value)
    {
        if ($goods_amount - $coupon_value< 0)
        {
            if ($goods_amount - $coupon_value < 0)
            {
                $this->new_order->set('coupon_value', $goods_amount);
            }
            $this->new_order->save_session();
            //$this->show_warning('not_allow_use', 'go_on', 'index.php?app=' . APPLICATION . '&act=shopping_cart');

            //return FALSE;
        }

        return TRUE;
    }

    /**
     *  计算订单费用
     *
     *  @access protected
     *  @param
     *  @return array
     */

    function _fee_count($order_info)
    {
        $need_inv = $_GET['need_inv'] ? intval($_GET['need_inv']) : $_COOKIE['need_inv'];

        $cart         = $this->_get_cart();
        $goods_amount = $cart->get_amount();
        $goods_count  = $cart->get_goods_count();
        $charge_info  = array('goods_amount' => $goods_amount,
                              'shipping_fee' => 0,
                              'pay_fee'      => 0,
                              'inv_fee'      => 0,
                              'order_amount' => 0);

        $this->_check_amount($goods_amount, $order_info['coupon_value']);

        /* 配送费用 */
        //$goods_weight_total = $cart->get_weight();

        $shipping_fee = $this->_get_shipping_fee($goods_count, $order_info['shipping_id']);

        $charge_info['shipping_fee']    = $shipping_fee;

        /* 发票费用 */
        if ($need_inv)
        {
            $charge_info['inv_fee']     = compute_fee($goods_amount, $this->conf('store_tax_rate'), 'i');
        }

        /* 优惠费用 */
        $coupon_value = isset($_SESSION['coupons'][$this->store_id]) ? $_SESSION['coupons'][$this->store_id]['value'] : 0;

        /* 支付手续费 */
        $tmp_amount = $charge_info['goods_amount'] + $charge_info['shipping_fee'] + $charge_info['inv_fee'] - $coupon_value;
        $charge_info['pay_fee'] = $this->_get_pay_fee($order_info['pay_id'], $tmp_amount);

        /* 订单总价 */
        $charge_info['order_amount'] = get_order_amount($charge_info);

        return $charge_info;

    }

    /**
     *  获得订单来源信息
     *
     *  @access protected
     *  @param  none
     *  @return string
     */

    function _get_referer()
    {
        return $this->lang('from_local');
    }

    /**
     *  获取商品信息
     *
     *  @author Garbin
     *  @param  int $spec_id        //规格ID
     *  @return array
     */
    function _get_goods_info($spec_id)
    {
        $spec_id = intval($spec_id);
        $goods  =   GoodsFactory::build($spec_id);
        if(!$goods)
        {
            $this->err  =   'goods_no_exists';

            return FALSE;
        }
        $goods_descri = addslashes_deep($goods->get_goods_detail($spec_id));
        return $goods_descri;
    }

    /**
     *  判断店铺是否有安装支付和配送方式
     *
     *  @author Garbin
     *  @param  int $store_id
     *  @return bool
     */
    function _is_closed_store($store_id)
    {
        $store_id = intval($store_id);
        if (!$store_id)
        {
            return FALSE;
        }
        include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
        include_once(ROOT_PATH . '/includes/manager/mng.shipping.php');
        /* 判断是否有设置支付方式 */
        $payment = new PaymentManager($store_id);
        $payment_list = $payment->get_installed();
        $is_set_payment = TRUE;
        if (empty($payment_list))
        {
            $is_set_payment = FALSE;
        }
        $is_avaliable = FALSE;
        foreach ($payment_list as $key => $value)
        {
            $modules = array();
            $set_modules = true;
            include(ROOT_PATH . '/includes/payment/' . $value['pay_code'] . '.php');
            if ($modules[0]['currency'] == 'all' || (is_array($modules[0]['currency']) && in_array(CURRENCY, $modules[0]['currency'])))
            {
                $is_avaliable = TRUE;
            }
        }
        $is_set_payment = $is_avaliable;

        /* 判断是否有设置配送方式 */
        $shipping = new ShippingManager($store_id);
        $shipping_list = $shipping->get_enabled();
        $is_set_shipping = TRUE;
        if ($shipping_list['info']['rec_count'] <= 0)
        {
            $is_set_shipping = FALSE;
        }
        if (!$is_set_payment || !$is_set_shipping)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     *    清空优惠券信息
     *
     *    @author    Garbin
     *    @param     param
     *    @return    void
     */
    function _drop_coupon_info()
    {
        $this->new_order->use_coupon('', '');
        $this->new_order->set_extension_info('', ''); //清空活动信息
        unset($_SESSION['coupons'][$this->store_id]);
        $this->new_order->save_session();
    }
}

?>
