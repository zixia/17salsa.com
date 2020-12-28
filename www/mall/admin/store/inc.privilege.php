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
 * $Id: inc.privilege.php 6090 2008-11-20 04:07:45Z Garbin $
 */

if (!defined('IN_ECM'))
{
    //trigger_error('Hacking attempt', E_USER_ERROR);
}

$privilege_item = array
(
    'store_admin' => array
    (
        array(
            'title' => 'store_conf',
            'appact' => 'conf|setting, conf|update_store_msn, conf|drop_store_msn',
            ),
        array(
            'title' => 'order_admin',
            'appact' => 'order|view,order|edit,order|add,order|drop,order|modify,order|batch,order|change_status,order|processing_order,order|view_logs,order|print_order, order|evaluation',
            ),
        array(
            'title' => 'store_nav_admin',
            'appact' => 'store_nav|view,store_nav|edit,store_nav|add,store_nav|drop,store_nav|modify,store_nav|batch,store_nav|check_title',
            ),
        array(
            'title' => 'shipping_admin',
            'appact' => 'shipping|view,shipping|edit,shipping|add,shipping|drop,shipping|modify,shipping|batch',
            ),
        array(
            'title' => 'payment_admin',
            'appact' => 'payment|view,payment|add,payment|edit,payment|drop,payment|modify,payment|batch|,payment|alipay_sign',
            ),
        array(
            'title' => 'message_admin',
            'appact' => 'message|view,message|edit,message|add,message|drop,message|modify,message|batch',
            ),
        array(
            'title' => 'template_admin',
            'appact' => 'template|view,template|edit,template|update_template,template|get_modules,template|create_module,template|get_layouts,template|set_layout,template|get_skins,template|get_css,template|restore',
            ),
        array(
            'title'  => 'partner_admin',
            'appact' => 'partner|view,partner|modify,partner|add,partner|edit,partner|duplicate_name,partner|drop,partner|batch,partner|delete_picture',
            ),
        array(
            'title' => 'data_call',
            'appact' => 'datacall|view,datacall|add,datacall|edit,datacall|get_brand_list,datacall|drop',
            ),
    ),
    'goods_admin' => array
    (
        array(
            'title' => 'goods_admin',
            'appact' => 'goods|view,goods|edit,goods|add,goods|drop,goods|modify,goods|batch,goods|relate,goods|get_brand_list,goods|get_attribute,goods|get_goods_type,goods|search,goods|set_related,goods|unset_related',
            ),
        array(
            'title' => 'category_admin',
            'appact' => 'category|view,category|edit,category|add,category|drop,category|modify,category|batch,category|ajax_update|update_goods_count,category',
            ),
        array(
            'title' => 'brand_admin',
            'appact' => 'brand|view,brand|edit,brand|add,brand|duplicate_name,brand|drop,brand|modify,brand|batch,brand|view,brand|view',
            ),
        array(
            'title' => 'goods_swap',
            'appact' => 'goods_swap|import,goods_swap|export,goods_swap|download,goods_swap|preview,goods_swap|complete',
            ),
    ),
    'administrator' => array
    (
        array(
            'title' => 'admin_admin',
            'appact' => 'admin|view,admin|add,admin|edit,admin|drop,admin|modify,admin|batch',
            ),
        array(
            'title' => 'admin_logs',
            'appact' => 'admin|logs,admin|remove_logs',
            ),
    ),
    'promotional' => array
    (
        array(
            'title' => 'coupon_admin',
            'appact' => 'coupon|view,coupon|add,coupon|sent,coupon|export,coupon|modify',
            ),
        array(
            'title' => 'groupbuy_admin',
            'appact' => 'groupbuy|view,groupbuy|edit,groupbuy|add,groupbuy|drop,groupbuy|modify,groupbuy|batch,groupbuy|view_log,groupbuy|end_activity,groupbuy|cancel_activity,groupbuy|reopen_activity,groupbuy|pm_buy_link,groupbuy|drop_actor',
            ),
    ),
    'stats_view' => array
    (
        array(
            'title' => 'view_stats',
            'appact'   => 'statistics|guest_stats,statistics|sale_list,statistics|sale_order,statistics|visit_sold,statistics|view_flow,statistics|view_sale,statistics|view_order',
            ),
    ),

);

?>
