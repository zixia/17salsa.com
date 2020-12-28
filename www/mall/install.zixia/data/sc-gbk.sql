
--
-- 导出表中的数据 `ecm_regions`
--


INSERT INTO `ecm_regions` (region_id, parent_id, region_name, store_id) VALUES
(1, 0, '中国', 0),
(2, 1, '北京', 0),
(3, 1, '天津', 0),
(4, 1, '河北', 0),
(5, 1, '山西', 0),
(6, 1, '内蒙古', 0),
(7, 1, '辽宁', 0),
(8, 1, '吉林', 0),
(9, 1, '黑龙江', 0),
(10, 1, '上海', 0),
(11, 1, '江苏', 0),
(12, 1, '浙江', 0),
(13, 1, '安徽', 0),
(14, 1, '福建', 0),
(15, 1, '江西', 0),
(16, 1, '山东', 0),
(17, 1, '河南', 0),
(18, 1, '湖北', 0),
(19, 1, '湖南', 0),
(20, 1, '广东', 0),
(21, 1, '广西', 0),
(22, 1, '海南', 0),
(23, 1, '重庆', 0),
(24, 1, '四川', 0),
(25, 1, '贵州', 0),
(26, 1, '云南', 0),
(27, 1, '西藏', 0),
(28, 1, '陕西', 0),
(29, 1, '甘肃', 0),
(30, 1, '青海', 0),
(31, 1, '宁夏', 0),
(32, 1, '新疆', 0),
(33, 1, '香港', 0),
(34, 1, '台湾', 0);


--
-- 导出表中的数据 `ecm_config_item`
--

INSERT INTO `ecm_config_item` (`code`, `group_code`, `required`, `type`, `params`, `default_value`, `sort_order`, `owner`) VALUES ('mall_language', 'mall_conf', 0, 'hidden', 'languages', 'utf-8', 1, 'mall'),
('mall_time_zone', 'mall_conf', 1, 'time_zone', '', '8', 2, 'mall'),
('mall_editor_type', 'mall_conf', 1, 'radio', '0=BBCode,1=HTML', '0', 3, 'mall'),
('mall_version', 'mall_conf', 1, 'hidden', '', '3', 3, 'mall'),
('mall_time_format_simple', 'mall_conf', 1, 'string', '^[^\\<|\\>]+$', 'd/m/y', 3, 'mall'),
('mall_time_format_complete', 'mall_conf', 1, 'string', '^[^\\<|\\>]+$', 'Y-m-d H:i:s', 4, 'mall'),
('mall_url_rewrite', 'mall_conf', 0, 'radio', '0=off,1=on', '0', 5, 'mall'),
('mall_max_file', 'mall_conf', 0, 'int', '0,*', '300', 7, 'mall'),
('mall_cache_life', 'mall_conf', 0, 'int', '0,*', '1800', 8, 'mall'),
('mall_goods_default_img', 'mall_conf', 0, 'file', 'image', './data/common/goods_default_img.gif', 10, 'mall'),
('mall_thumb_quality', 'mall_conf', 0, 'int', '60,100', '85', 11, 'mall'),
('mall_name', 'mall_base', 1, 'string', '^.{0,50}$', 'ECMall', 1, 'mall'),
('mall_title', 'mall_base', 1, 'string', '^[^\\<|\\>]+$', 'ECMall', 2, 'mall'),
('mall_keywords', 'mall_base', 0, 'string', '^[^\\<|\\>]+$', '', 3, 'mall'),
('mall_description', 'mall_base', 0, 'text', '', '', 3, 'mall'),
('mall_copyright', 'mall_base', 0, 'string', '', '', 4, 'mall'),
('mall_logo', 'mall_base', 1, 'file', 'image', './data/common/logo.gif', 5, 'mall'),
('mall_icp_number', 'mall_base', 0, 'string', '^[^\\<|\\>]+$', '', 6, 'mall'),
('mall_region_id', 'mall_base', 0, 'region', '', '', 8, 'mall'),
('mall_address', 'mall_base', 0, 'string', '^.{0,200}$', '', 9, 'mall'),
('mall_post_code', 'mall_base', 0, 'post_code', '', '', 10, 'mall'),
('mall_tel_num', 'mall_base', 0, 'tel_num', '', '', 11, 'mall'),
('mall_email', 'mall_base', 0, 'email', '', '', 12, 'mall'),
('mall_page_size', 'mall_base', 1, 'int', '1,*', '12', 13, 'mall'),
('mall_status', 'mall_base', 0, 'radio', '0=off,1=on', '', 14, 'mall'),
('mall_closed_reason', 'mall_base', 0, 'text', '', '商城维护中，暂时关闭，请稍后访问', 15, 'mall'),
('mall_email_type', 'mall_email', 0, 'radio', 'mail,smtp', 'mail', 1, 'mall'),
('mall_email_host', 'mall_email', 0, 'string', '^.{0,50}$', 'localhost', 2, 'mall'),
('mall_email_port', 'mall_email', 0, 'int', '0,*', '25', 3, 'mall'),
('mall_email_id', 'mall_email', 0, 'string', '^[^\<|\>]+$', '', 5, 'mall'),
('mall_email_pass', 'mall_email', 0, 'password', '', '', 6, 'mall'),
('mall_email_addr', 'mall_email', 0, 'email', '', '', 4, 'mall'),
('mall_test_email', 'mall_email', 0, 'email', 'test', '', 8, 'mall'),
('mall_captcha_status', 'mall_captcha', 0, 'checkbox', '0=login,1=regist,2=comment,3=admin', '14', 1, 'mall'),
('mall_captcha_error_login', 'mall_captcha', 0, 'int', '0,*', '5', 2, 'mall'),
('mall_allow_guest_buy', 'mall_conf', 0, 'radio', '0=off,1=on', '1', 20, 'mall'),
('mall_storeapply', 'mall_store', 0, 'radio', '0=off,1=on', '1', 1, 'mall'),
('mall_need_paper', 'mall_store', 0, 'radio', '0=off,1=on', '1', 2, 'mall'),
('mall_store_free_days', 'mall_store', 0, 'int', '0,*', '0', 3, 'mall'),
('mall_default_allowed_goods', 'mall_store', 0, 'int', '0,*', '0', 4, 'mall'),
('mall_default_allowed_file', 'mall_store', 0, 'int', '0,*', '0', 5, 'mall'),
('mall_allow_comment', 'mall_conf', 1, 'radio', '0=off,1=on', '1', 23, 'mall'),
('mall_display_volumn', 'mall_conf', 1, 'radio', '0=off,1=on', '1', 24, 'mall'),
('mall_hot_search', 'mall_base', 0, 'string', '', '', 15, 'mall'),
('mall_skin', 'mall_conf', 0, 'hidden', '', 'default', 0, 'mall'),
('mall_site_id', 'mall_conf', 0, 'hidden', '', '0', 0, 'mall'),
('mall_cycle_image', 'mall_conf', 0, 'hidden', '', '', 0, 'mall'),
('mall_min_goods_amount', 'mall_credit', 1, 'int', '0,*', 1, 1, 'mall'),
('mall_store_repeat_limit', 'mall_credit', 1, 'int', '0,*', 5, 2, 'mall'),
('mall_goods_repeat_limit', 'mall_credit', 1, 'int', '0,*', 10, 3, 'mall'),
('mall_max_goods_amount', 'mall_credit', 1, 'int', '1,*', 1000, 4, 'mall'),
('mall_max_address_num', 'mall_conf', 1, 'int', '1,20', '5', 6, 'mall'),
('mall_value_of_heart', 'mall_credit', 1, 'int', '1,*', 10, 5, 'mall'),
('mall_auto_evaluation_value', 'mall_credit', '1', 'radio', 'order_evaluation_poor,order_evaluation_common,order_evaluation_good', 'order_evaluation_good', '6', 'mall'),
('mall_auto_allow', 'mall_conf', 1, 'radio', '0=no,1=all,2=certified', 0, 0, 'mall'),
('store_intro', 'store_intro', 0, 'html', '', '', 1, 'store'),
('store_title', 'store_conf', 1, 'string', '^[^\\<|\\>]+$', '我的店铺', 2, 'store'),
('store_logo', 'store_conf', 1, 'file', 'image', '', 3, 'store'),
('store_keywords', 'store_conf', 0, 'string', '^[^\\<|\\>]+$', '', 4, 'store'),
('store_porto_arrived_pay', 'store_conf', 0, 'radio', '0=off,1=on', '0', 9, 'store'),
('store_page_size', 'store_conf', 1, 'int', '1,*', '12', 18, 'store'),
('store_status', 'store_conf', 0, 'radio', '0=off,1=on', '1', 19, 'store'),
('store_inv_enable', 'store_conf', 0, 'radio', '0=off,1=on', '0', 5, 'store'),
('store_tax_rate', 'store_conf', 1, 'float', '0,1', '0', 6, 'store'),
('store_inv_content', 'store_conf', 0, 'text', '', '', 7, 'store'),
('store_feed_default_status', 'store_conf', 0, 'radio', '0=off,1=on', '1', 14, 'store'),
('store_skin', 'store_conf', 0, 'hidden', '', 'default', 0, 'store'),
('store_qq', 'store_conf', '0', 'string', '^\\d{5,15}$', '', '4', 'store'),
('store_ww', 'store_conf', '0', 'string', '', '', '4', 'store'),
('store_msn', 'store_conf', '0', 'email', '', '', '4', 'store');


--
-- 导出表中的数据 `ecm_config_value`
--

INSERT INTO `ecm_config_value` (`store_id`, `code`, `value`) VALUES (0, 'mall_language', ''),
(0, 'mall_time_zone', '8'),
(0, 'mall_editor_type', '0'),
(0, 'mall_version', '1.0.0'),
(0, 'mall_time_format_simple', 'Y-m-d'),
(0, 'mall_time_format_complete', 'Y-m-d H:i:s'),
(0, 'mall_url_rewrite', '0'),
(0, 'mall_max_file', '300'),
(0, 'mall_cache_life', '1800'),
(0, 'mall_store_apply', '0'),
(0, 'mall_goods_default_img', './data/common/default_img.jpg'),
(0, 'mall_thumb_quality', '85'),
(0, 'mall_name', '您的网站名称'),
(0, 'mall_title', '您的网站标题'),
(0, 'mall_keywords', ''),
(0, 'mall_description', '这是一个用ECMall架设的网上商城'),
(0, 'mall_copyright', '&copy; 2008 Company Name, All rights reserved.'),
(0, 'mall_logo', ''),
(0, 'mall_icp_number', ''),
(0, 'mall_region_id', ''),
(0, 'mall_address', ''),
(0, 'mall_post_code', ''),
(0, 'mall_tel_num', ''),
(0, 'mall_email', ''),
(0, 'mall_page_size', '12'),
(0, 'mall_status', '1'),
(0, 'mall_closed_reason', '商城维护中，暂时关闭，请稍候访问。'),
(0, 'mall_email_type', 'smtp'),
(0, 'mall_email_host', ''),
(0, 'mall_email_port', '25'),
(0, 'mall_email_id', ''),
(0, 'mall_email_pass', ''),
(0, 'mall_email_addr', ''),
(0, 'mall_test_email', ''),
(0, 'mall_captcha_status', '15'),
(0, 'mall_captcha_error_login', '3'),
(0, 'mall_captcha_width', '200'),
(0, 'mall_captcha_height', '50'),
(0, 'mall_allow_guest_buy', '0'),
(0, 'mall_storeapply', 1),
(0, 'mall_need_paper', 1),
(0, 'mall_store_free_days', 0),
(0, 'mall_default_allowed_goods', 100),
(0, 'mall_default_allowed_file', 500),
(0, 'mall_hot_search', ''),
(0, 'mall_max_address_num', '3'),
(0, 'mall_skin', 'default'),
(0, 'mall_site_id', '0'),
(0, 'mall_min_goods_amount', 1),
(0, 'mall_store_repeat_limit', 5),
(0, 'mall_goods_repeat_limit', 10),
(0, 'mall_max_goods_amount', 1000),
(0, 'mall_value_of_heart', 10),
(0, 'mall_auto_evaluation_value', 'order_evaluation_good'),
(0, 'mall_auto_allow', 1),
(0, 'mall_cycle_image', '<bcaster><item id="1" link="http://www.shopex.cn" item_url="data/images/default.jpg"></item></bcaster>'),
(0, 'mall_allow_comment', 1),
(0, 'mall_display_volumn', 1);


--
-- 导出表中的数据 `ecm_mail_templates`
--

INSERT INTO `ecm_mail_templates` (`template_id`, `template_code`, `subject`, `content`) VALUES
(1, 'send_coupon', '{$store_name}送给您的优惠券', '{$user_name}，您好：\r\n　　{$store_name}通过{$mall_name}赠送给您一个价值{$coupon_value}的优惠券。\r\n　　优惠券号码为：{$coupon_sn} 请记住这个号码，该优惠券可使用{$max_times}次，请在{$start_time}至{$end_time}期间使用，并且订单中包含的商品金额大于{$min_amount}的订单才可使用，点击下面的链接前往[url={$store_url}]{$store_name}[/url]选购您喜欢的商品。 \r\n[url={$store_url}]{$store_url}[/url]\r\n　　　　　　　　　　　　　　　　　　　　　　　　　　　{$send_date}'),
(2, 'add_admin', '恭喜您升为{$store_name}管理员', '恭喜您！\r\n　　店铺[url={$store_url}]{$store_name}[/url]要把您加为管理员，您是否接受，若您接受，请点击以下地址接受并成为店铺的管理员\r\n　　[url={$accept_url}]接受地址：{$accept_url}[/url]\r\n　　提示：若您接受并成为店铺的管理员后，您将失去开独立开店的机会，除非您不再是任何店铺的管理员\r\n　　　　　　{$store_name}的管理地址:{$admin_url} '),
(3, 'shipping_notice', '卖家 {$boss} 已经给你发货了,请注意查收', '亲爱的{$order.consignee}。你好！\r\n　　您的订单{$order.order_sn}已于{$send_date}按照您预定的配送方式给您发货了。\r\n　　给您的发货单号是{$order.invoice_no}。\r\n　　在您收到货物之后请点击下面的链接确认您已经收到货物：\r\n　　[url={$confirm_url}]{$confirm_url}[/url]\r\n　　警告：若您还没有收到货，请不要点击确认收货。若卖家发货后14天您还没有确认收货，系统将自动为您收货\r\n　　再次感谢您对我们的支持。欢迎您的再次光临。 \r\n\r\n　　　　　　　　　　　　　　　{$boss}\r\n　　　　　　　　　　　　　　　　　　　　　{$send_date}\r\n　　本邮件为ECMall系统自动发出，您无需回复'),
(4, 'order_cancel', '{$boss} 取消了您的订单', '亲爱的{$order.consignee}，你好！ \r\n　　您的编号为：{$order.order_sn}的订单已取消。\r\n　　{$boss}\r\n　　　　　　　　　　　　　　　　{$send_date}\r\n\r\n本邮件为ECMall系统自动发出'),
(5, 'order_acceptted', '{$boss} 接受了您的订单', '亲爱的{$order.consignee}，你好！ \r\n\r\n　　我们已经收到您于 {$order.add_time|date:Y-m-d H:i} 提交的订单，该订单编号为：{$order.order_sn} 请记住这个编号以便日后的查询。\r\n\r\n　　　　　　　　　　　　　　　　　　　　　　　{$boss}\r\n　　　　　　　　　　　　　　　　　　　　　　　　　{$sent_date}\r\n\r\n\r\n'),
(6, 'get_pwd', '取回密码', '您好！{$cur_date}申请了密码取回。\r\n　　点击：[url={$repwd_url}]点{$repwd_url}[/url]重置您的密码。\r\n　　此链接有效期至：{$expire_date}\r\n　　请您尽快点击上面的重置密码链接重置您的密码'),
(7, 'new_order_notify', '{$mall_name}：您的订单信息', '亲爱的{$order.consignee}：\r\n　　你好！\r\n　　您于 {$order.add_time|date:Y-m-d H:i}在{$mall_name} 提交了订单。\r\n　　以下是该订单的简要信息，供您查询\r\n　　订单编号：{$order.order_sn}\r\n　　　　收货人信息\r\n　　　　　　姓名：{$order.consignee}\r\n　　　　　　地址：[{$order.region}]  {$order.address}\r\n　　　　　　邮编：{$order.zipcode}\r\n　　　　　　EMail：{$order.email}\r\n　　　　　　手机：{$order.mobile_phone}\r\n　　　　　　电话：{$order.home_phone}\r\n　　　　　　办公电话：{$order.office_phone}\r\n　　　　　　最佳配送时间：{$order.best_time}\r\n　　　　　　标志建筑：{$order.sign_building}\r\n　　　　　　配送支付信息\r\n　　　　　　配送方式：{$order.shipping_name}\r\n　　　　　　支付方式：{$order.pay_name}\r\n　　请记住订单编号以便日后的查询\r\n　　若以上信息有误，请及时联系卖家解决，您可以通过登录卖家的店铺首页找到该卖家的联系方式，卖家的店铺首页是：[url={$store_url}]{$boss}:{$store_url}[/url]\r\n　　请及时支付，支付地址是[url={$pay_url}]{$pay_url}[/url]\r\n　　　　　　　　　　　　　　　　　　　　　　　　{$boss}\r\n　　　　　　　　　　　　　　　　　　　　　　　　　　{$sent_date}'),
(8, 'seller_new_order_notify', '{$mall_name}:{$order.consignee}在您的店铺下了一个新订单', '亲爱的{$boss}：\r\n　　你好！\r\n　　您于 {$order.add_time|date:Y-m-d H:i}收到了由{$order.consignee}提交的订单。\r\n　　以下是该订单的简要信息，供您查询\r\n　　订单编号：{$order.order_sn}\r\n　　　　收货人信息\r\n　　　　　　姓名：{$order.consignee}\r\n　　　　　　地址：[{$order.region}]  {$order.address}\r\n　　　　　　邮编：{$order.zipcode}\r\n　　　　　　EMail：{$order.email}\r\n　　　　　　手机：{$order.mobile_phone}\r\n　　　　　　电话：{$order.home_phone}\r\n　　　　　　办公电话：{$order.office_phone}\r\n　　　　　　最佳配送时间：{$order.best_time}\r\n　　　　　　标志建筑：{$order.sign_building}\r\n　　　　　　配送支付信息\r\n　　　　　　配送方式：{$order.shipping_name}\r\n　　　　　　支付方式：{$order.pay_name}\r\n　　请及时根据处理该订单,[url={$parse_order_url}]点击我马上处理:{$parse_order_url}[/url]\r\n　　登录您的店铺管理后台[url={$store_admin_url}]{$store_admin_url}[/url]\r\n{$mall_name}　　　　　　　　　　　　　　　　　　　　　　　　　　{$sent_date}'),
(9, 'evaluation_invalid_to_buyer', '{$mall_name}:您给商家{$seller}的评价无效', '亲爱的{$buyer}:\r\n   您给您于{$order.add_time|date}下的订单(订单号:{$order.order_sn})的评价被视为无效的评价，因此该评价将不对商家{$seller}的信用积分造成影响。\r\n   具体的原因为：{$reason|escape}\r\n\r\n   点击以下链接访问{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(10, 'evaluation_invalid_to_seller', '{$mall_name}:您给买家{$buyer}的评价无效', '亲爱的{$seller}:\r\n   您给{$buyer}于{$order.add_time|date}在您店铺下的订单(订单号:{$order.order_sn})的评价被视为无效的评价，因此该评价将不对该买家的信用积分造成影响。\r\n   具体的原因为：{$reason|escape}\r\n\r\n   点击以下链接访问{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(11, 'evaluation_invalid_from_seller', '{$mall_name}：买家给您的评价已无效', '亲爱的{$seller}:\r\n   {$order.user_name|escape}给您的交易(订单号：{$order.order_sn})的评价已被视为无效，因此这次评价将不会给您的信用积分造成影响\r\n   具体原因：{$reason|escape}\r\n\r\n\r\n   点击以下链接访问{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(12, 'evaluation_invalid_from_buyer', '{$mall_name}：商家给您的评价已无效', '亲爱的{$buyer}:\r\n   商家{$boss}给您的交易(订单号：{$order.order_sn})的评价已被视为无效，因此这次评价将不会给您的信用积分造成影响\r\n   具体原因：{$reason|escape}\r\n\r\n\r\n   点击以下链接访问{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(13, 'to_seller_evaluation_notify', '{$mall_name}:与您交易的买家{$buyer}给您做了评价', '亲爱的{$seller}:\r\n   买家{$buyer}给与您的交易(订单号：{$order.order_sn})做了评价，请尽快给对方评价。\r\n   \r\n   点击以下链接管理订单\r\n   [url={$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}]{$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}[/url]\r\n\r\n   点击以下链接访问商城\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(14, 'to_buyer_evaluation_notify', '{$mall_name}:与您交易的卖家{$seller}给您做了评价', '亲爱的{$buyer}:\r\n   卖家{$seller}给与您的交易(订单号：{$order.order_sn})做了评价，谢谢您的惠顾。\r\n\r\n   点击以下链接查看订单\r\n   [url={$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}]{$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}[/url]\r\n\r\n   点击以下链接访问商城\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(15, 'relet_remind', '您的店铺即将到期', '亲爱的 {$user_name}\r\n\r\n      您的店铺将于 {$days_left} 日后到期，请您及时续费。\r\n\r\n\r\n                                                {$mall_name} {$site_url}\r\n                                                {$sent_date}\r\n');



--
-- 导出表中的数据 `ecm_article_cate`
--

INSERT INTO `ecm_article_cate` (`cate_id`, `store_id`, `cate_name`, `parent_id`, `keywords`, `sort_order`, `editable`) VALUES
(1, 0, '商城帮助', 0, '', 0, 0),
(2, 0, '商城快讯', 0, '', 0, 0);


--
-- 导出表中的数据 `ecm_partner`
--

INSERT INTO `ecm_partner` VALUES
(1, 0, 'Shopex', 'http://www.shopex.cn', '', 0),
(2, 0, 'ECShop', 'http://www.ecshop.com', '', 0);


--
-- 导出表中的数据 `ecm_templates`
--

INSERT INTO `ecm_templates` (`store_id`, `config`, `filename`, `pagename`, `hash_code`) VALUES (0, 'a:6:{s:7:"region1";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:2:{s:11:"page_header";a:2:{s:2:"id";s:11:"page_header";s:3:"src";s:37:"themes/mall/resource/page_header.html";}s:11:"search_form";a:2:{s:2:"id";s:11:"search_form";s:3:"src";s:37:"themes/mall/resource/search_form.html";}}}s:7:"region4";a:1:{s:8:"children";a:5:{s:14:"goods_category";a:3:{s:2:"id";s:14:"goods_category";s:3:"src";s:40:"themes/mall/resource/goods_category.html";s:6:"parent";s:7:"region4";}s:17:"recommended_store";a:3:{s:2:"id";s:17:"recommended_store";s:3:"src";s:43:"themes/mall/resource/recommended_store.html";s:6:"parent";s:7:"region4";}s:17:"recommended_brand";a:3:{s:2:"id";s:17:"recommended_brand";s:3:"src";s:43:"themes/mall/resource/recommended_brand.html";s:6:"parent";s:7:"region4";}s:11:"latest_sold";a:3:{s:2:"id";s:11:"latest_sold";s:3:"src";s:37:"themes/mall/resource/latest_sold.html";s:6:"parent";s:7:"region4";}s:7:"partner";a:3:{s:2:"id";s:7:"partner";s:3:"src";s:33:"themes/mall/resource/partner.html";s:6:"parent";s:7:"region4";}}}s:7:"region6";a:1:{s:8:"children";a:1:{s:10:"cycleimage";a:3:{s:2:"id";s:10:"cycleimage";s:3:"src";s:36:"themes/mall/resource/cycleimage.html";s:6:"parent";s:7:"region6";}}}s:7:"region7";a:1:{s:8:"children";a:1:{s:16:"latest_site_news";a:3:{s:2:"id";s:16:"latest_site_news";s:3:"src";s:42:"themes/mall/resource/latest_site_news.html";s:6:"parent";s:7:"region7";}}}s:7:"region8";a:1:{s:8:"children";a:4:{s:9:"group_buy";a:3:{s:2:"id";s:9:"group_buy";s:3:"src";s:35:"themes/mall/resource/group_buy.html";s:6:"parent";s:7:"region8";}s:4:"cm_2";a:6:{s:2:"id";s:4:"cm_2";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:11:"Lady\'s Wear";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_4";a:6:{s:2:"id";s:4:"cm_4";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:14:"Digital Camera";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_1";a:6:{s:2:"id";s:4:"cm_1";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:6:"Mobile";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}}}s:7:"region3";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:1:{s:11:"page_footer";a:2:{s:2:"id";s:11:"page_footer";s:3:"src";s:37:"themes/mall/resource/page_footer.html";}}}}', 'default.layout', 'homepage', '527e4d037cdf839f58c9720fb85ffc95');

--
-- 导出表中的数据 `ecm_custom_modules`
--

INSERT INTO `ecm_custom_modules` (`id`, `name`, `type`, `config`) VALUES (1, '手机', 0, 'a:10:{s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(2, '女装', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(4, '数码相机', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}');

--
-- 导出表中的数据 `ecm_crontab`
--

INSERT INTO `ecm_crontab` (`task_name`, `plan_time`, `run_time`) VALUES
('auto_order_handle', 0, 0),
('auto_store_handle', 0, 0),
('auto_send_mail', 0, 0);
