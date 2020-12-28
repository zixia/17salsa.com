<?php

/**
 * ECMALL: ��վ��̨�������˵�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: inc.menu.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'goods_manage' => array
    (
        'goods_view'        => array('app' => 'goods', 'act' => 'view'),
        'goods_add'         => array('app' => 'goods', 'act' => 'add'),
        'goods_import'      => array('app' => 'goods_swap', 'act' => 'import'),
        'goods_export'      => array('app' => 'goods_swap', 'act' => 'export')
    ),
    'category_brand' => array
    (
        'category_view'     => array('app' => 'category', 'act' => 'view'),
        'category_add'      => array('app' => 'category', 'act' => 'add'),
        'brand_view'        => array('app' => 'brand', 'act' => 'view')
    ),
    'order_admin' => array
    (
        'order_list'        => array('app' => 'order', 'act' => 'view'),
        'order_processing_order' => array('app' => 'order', 'act' => 'processing_order'),
        'order_action_logs' => array('app' => 'order', 'act' => 'view_logs')
    ),
    'promotional'   => array(
        'coupon_view'       => array('app' => 'coupon',     'act' => 'view'),
        'group_buy'         => array('app' => 'groupbuy',   'act' => 'view')
    ),
    'store_admin' => array
    (
        'store_conf'        => array('app' => 'conf', 'act' => 'setting'),
        'store_nav'         => array('app' => 'store_nav', 'act' => 'view'),
        'shipping_view'     => array('app' => 'shipping', 'act' => 'view'),
        'payment_view'      => array('app' => 'payment', 'act' => 'view'),
        'message_view'      => array('app' => 'message', 'act' => 'view'),
        'template_edit'     => array('app' => 'template', 'act' => 'view'),
        'partner_view'      => array('app' => 'partner', 'act' => 'view'),
        'data_call'         => array('app' => 'datacall', 'act' => 'view'),
    ),
    'administrator' => array
    (
        'admin_view'        => array('app' => 'admin', 'act' => 'view'),
        'admin_add'         => array('app' => 'admin', 'act' => 'add'),
        'admin_logs'        => array('app' => 'admin', 'act' => 'logs'),
    ),
    'statistics'    => array
    (
        'guest_stats'       => array('app' => 'statistics', 'act' => 'guest_stats'),
        'sale_list'         => array('app' => 'statistics', 'act' => 'sale_list'),
        'sale_order'        => array('app' => 'statistics', 'act' => 'sale_order'),
        'visit_sold'        => array('app' => 'statistics', 'act' => 'visit_sold'),
        'view_flow_stats'   => array('app' => 'statistics', 'act' => 'view_flow'),
        'view_sale_stats'   => array('app' => 'statistics', 'act' => 'view_sale'),
        'view_order_stats'  => array('app' => 'statistics', 'act' => 'view_order'),
    ),
);

?>