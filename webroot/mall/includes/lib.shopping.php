<?php

/**
 * ECMall: 购物流程函数库
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: lib.shopping.php 6009 2008-10-31 01:55:52Z Garbin $
 */


/**
 *  计算订单总金额
 *
 *  @access
 *  @param
 *  @return
 */
function get_order_amount($charge_info)
{
    /* 商品总额＋配送费用＋发票税费＋支付手续费用 */
    return round($charge_info['goods_amount'] + $charge_info['shipping_fee'] + $charge_info['inv_fee'] + $charge_info['pay_fee'], 2);
}

/**
 *  获取应支付金额
 *
 *  @access
 *  @param
 *  @return
 */
function get_payable($charge_info)
{
    /* 订单总金额－优惠券优惠金额－积分价值－现金折扣-已支付费用 */
    return round($charge_info['order_amount'] - $charge_info['coupon_value'] - $charge_info['points_value'] - $charge_info['discount'] - $charge_info['money_paid'], 2);
}

/**
 *  获取发票金额
 *
 *  @access
 *  @param
 *  @return
 */
function get_inv_amount($charge_info)
{
    /* 订单总金额－优惠券优惠金额－积分价值－现金折扣-已支付费用 */
    return round($charge_info['goods_amount'] - $charge_info['coupon_value'] - $charge_info['points_value'] - $charge_info['discount'] - $charge_info['money_paid'], 2);
}

/**
 *  获取支付手续费用
 *
 *  @author Garbin
 *  @param  int   $pay_id
 *  @param  float $amount
 *  @return float
 */
function get_pay_fee($store_id, $pay_id, $amount)
{
    include_once(ROOT_PATH . '/includes/models/mod.payment.php');

    /* 支付手续费 */
    $payment = new Payment($pay_id, $store_id);
    $pay_info = $payment->get_info();
    if (!$pay_info)
    {
        return FALSE;
    }
    $pay_fee      = compute_fee($amount, $pay_info['pay_fee'], 'p');

    return $pay_fee;
}

?>