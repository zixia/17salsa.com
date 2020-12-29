<?php
/**
 * ECMALL: 常量
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: inc.constant.php 6102 2008-11-21 04:52:37Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


/* 系统信息常量 */

define('VERSION', '1.1 final');
define('RELEASE', '20081124');


define('IMG_AD',   0); // 图片广告
define('FALSH_AD', 1); // flash广告
define('CODE_AD',  2); // 代码广告
define('TEXT_AD',  3); // 文字广告

/* 邮件的优先级 */
define('MAIL_PRIORITY_LOW',     1);
define('MAIL_PRIORITY_MID',     2);
define('MAIL_PRIORITY_HIGH',    3);

/* 发送邮件的协议类型 */
define('MAIL_PROTOCOL_LOCAL',       0, true);
define('MAIL_PROTOCOL_SMTP',        1, true);

/* 活动的类型 */
define('ACT_GROUPBUY',      1); // 团购活动
define('GROUPBUY_CANCEL',   1); //团购取消状态

/* PAYLOG Type */

define('SHOPPING_ORDER', 1);    //购物的订单

/* 订单状态常量 */
define('ORDER_STATUS_TEMPORARY', 0);    //临时
define('ORDER_STATUS_PENDING', 1);      //待付款
define('ORDER_STATUS_SUBMITTED', 2);    //已提交
define('ORDER_STATUS_ACCEPTTED', 3);    //已接受
define('ORDER_STATUS_PROCESSING', 4);   //处理中
define('ORDER_STATUS_SHIPPED', 5);      //已发货
define('ORDER_STATUS_DELIVERED', 6);    //已收货
define('ORDER_STATUS_INVALID', 7);      //无效
define('ORDER_STATUS_REJECTED', 8);     //已拒绝

/* 文章类型 */
define('ARC_HELP', 1); // 网站帮助
define('ARC_NEWS', 2); // 网站快讯

/* 订单评价常量 */
define('ORDER_EVALUATION_UNEVALUATED', 0); //未评价
define('ORDER_EVALUATION_POOR', 1);        //差评
define('ORDER_EVALUATION_COMMON', 2);       //中评
define('ORDER_EVALUATION_GOOD', 3);         //好评

/* 开店申请的状态 */
define('APPLY_RAW',     0); // 未处理
define('APPLY_ACCEPT',  1); // 已接受
define('APPLY_DENY',    2); // 被拒绝

/* 店铺到期日前多少时间可以续租 */
define('STORE_RELET_TIME', 30 * 86400);

/* 店铺租期状态 */
//define('RELET_STATUS_')

?>
