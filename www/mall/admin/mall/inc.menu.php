<?php

/**
 * ECMALL: 网站后台管理左侧菜单数据
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: inc.menu.php 16 2007-12-23 15:36:24Z Redstone $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'mall_admin' => array
    (
        'mall_conf'     => array('app' => 'conf', 'act' => 'setting'),
        'regions'       => array('app' => 'region', 'act' => 'view'),
        'order_view'    => array('app' => 'order', 'act' => 'view'),
        'user_view'     => array('app' => 'user', 'act' => 'view'),
        'app_setting'   => array('app' => 'appsetting', 'act' => 'setting'),
        'sitemap'       => array('app' => 'sitemap', 'act' => 'setting'),
        'message_view'  => array('app' => 'message', 'act' => 'view'),
        'wanted_view'   => array('app' => 'wanted', 'act' => 'view'),
        'badwords_view' => array('app' => 'badwords', 'act'=> 'view'),
        'notify_owner'  => array('app' => 'notify','act' => 'show'),
        'payment_view'  => array('app' => 'payment', 'act' => 'view'),
        'data_call' => array('app' => 'datacall', 'act' => 'view'),
    ),
    'goods_admin' => array
    (
        'goods_view'    => array('app' => 'goods', 'act'=>'view'),
        'category_view' => array('app' => 'category', 'act'=>'view'),
        'goods_type'    => array('app' => 'goodstype', 'act' => 'view'),
        'brand_view'    => array('app' => 'brand', 'act'=>'view')
    ),
    'store_admin' => array
    (
        'store_view'    => array('app' => 'store', 'act' => 'view'),
        'store_add'     => array('app' => 'store', 'act' => 'add'),
        'store_apply'   => array('app' => 'storeapply', 'act' => 'view'),
        'rentscheme_view'   => array('app' => 'rentscheme', 'act' => 'view'),
        'relet_order'   => array('app' => 'storerelet', 'act' => 'view'),
    ),
    'adv_admin' => array
    (
        'adv_position'  => array('app' => 'ad_position', 'act' => 'view'),
        'adv_view'      => array('app' => 'ad', 'act' => 'view'),
        'cycle_image'   => array('app' => 'cycleimage', 'act' => 'view'),
    ),
    'article_admin' => array
    (
        'article_add'   => array('app' => 'article', 'act' => 'add'),
        'article_view'  => array('app' => 'article', 'act' => 'view'),
        'builtin_article'  => array('app' => 'builtinarticle', 'act' => 'view')
    ),
    'administrator' => array
    (
        'admin_view'    => array('app' => 'admin', 'act' => 'view'),
        'admin_add'     => array('app' => 'admin', 'act' => 'add'),
        'admin_logs'    => array('app' => 'admin', 'act' => 'logs'),
    ),
    'template_admin' => array
    (
        'template_edit' => array('app' => 'template', 'act' => 'view'),
        'mail_template_setting'   => array('app' => 'mailtemplate', 'act' => 'setting'),
    ),
    'mixed_admin'   => array
    (
        'nav_admin'     => array('app' => 'nav', 'act' => 'view'),
        'partner_view'  => array('app' => 'partner', 'act' => 'view'),
    ),
    'statistics'    => array
    (
        'guest_stats'       => array('app' => 'statistics', 'act' => 'guest_stats'),
        'store_order'       => array('app' => 'statistics', 'act' => 'store_order'),
        'sale_list'         => array('app' => 'statistics', 'act' => 'sale_list'),
        'sale_order'        => array('app' => 'statistics', 'act' => 'sale_order'),
        'user_order'        => array('app' => 'statistics', 'act' => 'user_order'),
        'visit_sold'        => array('app' => 'statistics', 'act' => 'visit_sold'),
        'view_flow_stats'   => array('app' => 'statistics', 'act' => 'view_flow'),
        'view_sale_stats'   => array('app' => 'statistics', 'act' => 'view_sale'),
        'view_order_stats'  => array('app' => 'statistics', 'act' => 'view_order'),
    ),
);

?>
