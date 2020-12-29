<?php

/**
 * ECMall: 店铺续租
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: storerelet.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StorereletController extends ControllerBackend
{
    function __construct($act)
    {
        $this->StorereletController($act);
    }

    function StorereletController($act)
    {
        if (empty($act))
        {
            $act = 'reletview';
        }
        parent::__construct($act);
    }

    /**
     * 新增订单
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            /* 判断当前是否可以续租 */
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $mod_store = new Store($_SESSION['store_id']);
            $store_info = $mod_store->get_info();
            if (!$store_info['reletable'])
            {
                $this->show_warning('relet_not_allowed', 'js.close', 'javascript:parent.closeCurrentTab()');
                return;
            }

            /* 判断是否有续租方案 */
            include_once(ROOT_PATH . '/includes/manager/mng.rentscheme.php');
            $mng_scheme = new RentSchemeManager();
            $condition = array('min_goods' => $mod_store->get_goods_count(), 'min_file' => $mod_store->get_file_count());
            if ($mng_scheme->get_count($condition) <= 0)
            {
                $this->show_warning('no_rent_scheme', 'js.close', 'javascript:parent.closeCurrentTab()');
                return;
            }
            $scheme_list = $mng_scheme->get_list(1, $condition, 1000);
            $this->assign('scheme_list', $scheme_list['data']);

            /* 判断是否有支付方式 */
            include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
            $mng_payment = new PaymentManager(0);
            $payment_list = $mng_payment->get_payment_list(false, true);
            if (count($payment_list) <= 0)
            {
                $this->show_warning('no_payment', 'js.close', 'javascript:parent.closeCurrentTab()');
                return;
            }
            $this->assign('payment_list', $payment_list);

            $this->display('storerelet.detail.html', 'store');
        }
        else
        {
            /* 检查参数 */
            $store_id = $_SESSION['store_id'];
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $mod_store = new Store($store_id);

            $scheme_id = intval($_POST['rent_scheme']);
            include_once(ROOT_PATH . '/includes/models/mod.rentscheme.php');
            $mod_scheme = new RentScheme($scheme_id);
            $scheme_info = $mod_scheme->get_info();
            if (empty($scheme_info))
            {
                $this->show_warning('Invalid Params');
                return;
            }
            if ($scheme_info['allowed_goods'] > 0 && $scheme_info['allowed_goods'] < $mod_store->get_goods_count())
            {
                $this->show_warning('scheme_goods_lacking');
                return;
            }
            if ($scheme_info['allowed_file'] > 0 && $scheme_info['allowed_file'] < $mod_store->get_file_count())
            {
                $this->show_warning('scheme_file_lacking');
                return;
            }

            $pay_id = intval($_POST['payment']);
            include_once(ROOT_PATH . '/includes/models/mod.payment.php');
            $mod_payment = new Payment($pay_id, 0);
            $payment_info = $mod_payment->get_info();

            /* 生成订单 */
            include_once(ROOT_PATH . '/includes/models/mod.order.php');
            $mod_order = new Order();
            $mod_order->_store_id = 0;
            $mod_order->_user_id  = $store_id;
            $mod_order->write_payment_info($payment_info);
            $consignee = array(
                'consignee' => 'store' . $store_id,
                'region'    => 'region',
                'region_id' => 1,
                'address'   => 'address',
                'zipcode'   => '100082',
                'email'     => 'store@mall.com',
                'home_phone'    => '010-72727272',
                'mobile_phone'  => '13988888888',
            );
            $mod_order->write_consignee_info($consignee);
            $mod_order->write_charge_info(array('goods_amount' => $scheme_info['price'], 'order_amount' => $scheme_info['price']));
            $mod_order->write_shipping_info(array('shipping_id' => 1, 'shipping_name' => 'neednot'));
            $mod_order->set_status(ORDER_STATUS_PENDING);
            $mod_order->set_extension_info('STORERELET', 0);
            $mod_order->seller_comment(serialize($scheme_info));
            $goods_info = array(
                'goods_id'  => 1,
                'spec_id'   => 1,
                'goods_name'=> $this->lang('allowed_goods') . ':' . $scheme_info['allowed_goods'] . '    ' .
                               $this->lang('allowed_file')  . ':' . $scheme_info['allowed_file'] . '    ' .
                               $this->lang('allowed_month') . ':' . $scheme_info['allowed_month'],
                'sku'       => '88888888',
                'goods_number'  => 1,
                'market_price'  => $scheme_info['price'],
                'store_price'   => $scheme_info['price'],
                'is_real'   => 0,
                'extension_code'=> 'STORERELET'
            );
            $mod_order->add_goods(0, $goods_info);
            if (!$mod_order->submit())
            {
                $this->show_warning('Data Error: ' . $mod_order->err[0]);
                return;
            }

            /* 跳转到付款页面 */
            $this->redirect('admin.php?app=storerelet&act=pay&id=' . $mod_order->_id);
        }
    }

    /**
     * 确认付款
     *
     * @return  void
     */
    function pay()
    {
        /* 检查参数 */
        $order_id = intval($_GET['id']);
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $mod_order = new Order($order_id);
        $order_info = $mod_order->get_info_with_payment();
        if (empty($order_info) || $order_info['store_id'] != 0 || $order_info['user_id'] != $_SESSION['store_id'])
        {
            $this->show_warning('Invalid Params');
            return;
        }

        /* 检查是否付款 */
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('order_is_paid', 'list_relet_order', 'admin.php?app=storerelet&act=view');
            return;
        }

        $this->assign('order', $order_info);
        $goods = $mod_order->list_goods();
        $this->assign('goods', current($goods));
        $this->display('storerelet.pay.html', 'store');
    }

    /**
     * 在线支付
     *
     */
    function do_pay()
    {
        /* 检查参数 */
        $order_id = intval($_GET['id']);
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $mod_order = new Order($order_id);
        $order_info = $mod_order->get_info_with_payment();
        if (empty($order_info) || $order_info['store_id'] != 0 || $order_info['user_id'] != $_SESSION['store_id'])
        {
            $this->show_warning('Invalid Params');
            return;
        }

        /* 检查是否付款 */
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('order_is_paid', 'list_relet_order', 'admin.php?app=storerelet&act=view');
            return;
        }

        /* 生成支付代码 */
        $pay_code = $order_info['pay_code'];
        include_once(ROOT_PATH . '/includes/payment/' . $pay_code . '.php');
        $pay_obj = new $pay_code($order_info['pay_id'], 0);
        if ((is_array($pay_obj->currency) && !in_array(CURRENCY, $pay_obj->currency)) || (is_string($pay_obj->currency) && $pay_obj->currency != 'all'))
        {
            $this->show_warning('pay_disabled_currency');
            return;
        }
        $pay_info= $pay_obj->get_info();
        if (!$pay_info)
        {
            $this->show_warning('pay_disabled');
            return;
        }
        $pay_form = $pay_obj->get_code($mod_order);
        if ($pay_form === FALSE)
        {
            $this->show_warning($pay_obj->err);
            return;
        }

        $this->assign('pay_form', $pay_form);
        $this->display('storerelet.dopay.html', 'store');
    }

    /**
     * 订单列表
     */
    function view()
    {
        $condition = array('store_id' => 0, 'user_id' => $_SESSION['store_id'], 'extension_code' => 'storerelet');
        if (!empty($_GET['order_sn']))
        {
            $condition['order_sn'] = trim($_GET['order_sn']);
        }
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $mng_order = new OrderManager(0);
        $order_list = $mng_order->get_list($this->get_page(), $condition);
        $order_ids = array();
        foreach ($order_list['data'] as $order)
        {
            $order_ids[] = $order['order_id'];
        }
        $order_list['data'] = $mng_order->get_list_by_ids(join(',', $order_ids));
        $order_list['data'] = deep_local_date($order_list['data'], 'add_time', 'm-d H:i');
        $this->assign('order_list', $order_list);
        $this->display('storerelet.view.html', 'store');
    }

    /**
     * 删除
     */
    function drop()
    {
        /* 检查参数 */
        $order_id = intval($_GET['id']);
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $mod_order = new Order($order_id);
        $order_info = $mod_order->get_info_with_payment();
        if (empty($order_info) || $order_info['store_id'] != 0)
        {
            $this->show_warning('Invalid Params');
            return;
        }

        /* 检查是否付款 */
        if ($order_info['order_status'] != ORDER_STATUS_PENDING)
        {
            $this->show_warning('order_is_paid', 'list_relet_order', 'admin.php?app=storerelet&act=view');
            return;
        }

        /* 删除 */
        $mod_order->set_status(ORDER_STATUS_INVALID);
        $mod_order->drop();

        /* 跳转到列表页面 */
        $this->redirect('admin.php?app=storerelet&act=view');
    }
};

?>
