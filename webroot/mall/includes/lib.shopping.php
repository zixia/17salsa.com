<?php

/**
 * ECMall: �������̺�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: lib.shopping.php 6009 2008-10-31 01:55:52Z Garbin $
 */


/**
 *  ���㶩���ܽ��
 *
 *  @access
 *  @param
 *  @return
 */
function get_order_amount($charge_info)
{
    /* ��Ʒ�ܶ���ͷ��ã���Ʊ˰�ѣ�֧���������� */
    return round($charge_info['goods_amount'] + $charge_info['shipping_fee'] + $charge_info['inv_fee'] + $charge_info['pay_fee'], 2);
}

/**
 *  ��ȡӦ֧�����
 *
 *  @access
 *  @param
 *  @return
 */
function get_payable($charge_info)
{
    /* �����ܽ��Ż�ȯ�Żݽ����ּ�ֵ���ֽ��ۿ�-��֧������ */
    return round($charge_info['order_amount'] - $charge_info['coupon_value'] - $charge_info['points_value'] - $charge_info['discount'] - $charge_info['money_paid'], 2);
}

/**
 *  ��ȡ��Ʊ���
 *
 *  @access
 *  @param
 *  @return
 */
function get_inv_amount($charge_info)
{
    /* �����ܽ��Ż�ȯ�Żݽ����ּ�ֵ���ֽ��ۿ�-��֧������ */
    return round($charge_info['goods_amount'] - $charge_info['coupon_value'] - $charge_info['points_value'] - $charge_info['discount'] - $charge_info['money_paid'], 2);
}

/**
 *  ��ȡ֧����������
 *
 *  @author Garbin
 *  @param  int   $pay_id
 *  @param  float $amount
 *  @return float
 */
function get_pay_fee($store_id, $pay_id, $amount)
{
    include_once(ROOT_PATH . '/includes/models/mod.payment.php');

    /* ֧�������� */
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