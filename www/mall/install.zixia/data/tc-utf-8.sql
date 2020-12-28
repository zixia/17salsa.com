
--
-- 導出表中的數據 `ecm_regions`
--


INSERT INTO `ecm_regions` (region_id, parent_id, region_name, store_id) VALUES
(1, 0, '中國', 0),
(2, 1, '北京', 0),
(3, 1, '天津', 0),
(4, 1, '河北', 0),
(5, 1, '山西', 0),
(6, 1, '內蒙古', 0),
(7, 1, '遼寧', 0),
(8, 1, '吉林', 0),
(9, 1, '黑龍江', 0),
(10, 1, '上海', 0),
(11, 1, '江蘇', 0),
(12, 1, '浙江', 0),
(13, 1, '安徽', 0),
(14, 1, '福建', 0),
(15, 1, '江西', 0),
(16, 1, '山東', 0),
(17, 1, '河南', 0),
(18, 1, '湖北', 0),
(19, 1, '湖南', 0),
(20, 1, '廣東', 0),
(21, 1, '廣西', 0),
(22, 1, '海南', 0),
(23, 1, '重慶', 0),
(24, 1, '四川', 0),
(25, 1, '貴州', 0),
(26, 1, '雲南', 0),
(27, 1, '西藏', 0),
(28, 1, '陝西', 0),
(29, 1, '甘肅', 0),
(30, 1, '青海', 0),
(31, 1, '寧夏', 0),
(32, 1, '新疆', 0),
(33, 1, '香港', 0),
(34, 1, '台灣', 0);


--
-- 導出表中的數據 `ecm_config_item`
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
('mall_closed_reason', 'mall_base', 0, 'text', '', '商城維護中，暫時關閉，請稍後訪問', 15, 'mall'),
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
('mall_max_address_num', 'mall_conf', 1, 'int', '1,20', '5', 6, 'mall'),
('mall_skin', 'mall_conf', 0, 'hidden', '', 'default', 0, 'mall'),
('mall_site_id', 'mall_conf', 0, 'hidden', '', '0', 0, 'mall'),
('mall_cycle_image', 'mall_conf', 0, 'hidden', '', '', 0, 'mall'),
('mall_min_goods_amount', 'mall_credit', 1, 'int', '0,*', 1, 1, 'mall'),
('mall_store_repeat_limit', 'mall_credit', 1, 'int', '0,*', 5, 2, 'mall'),
('mall_goods_repeat_limit', 'mall_credit', 1, 'int', '0,*', 10, 3, 'mall'),
('mall_max_goods_amount', 'mall_credit', 1, 'int', '1,*', 1000, 4, 'mall'),
('mall_value_of_heart', 'mall_credit', 1, 'int', '1,*', 10, 5, 'mall'),
('mall_auto_evaluation_value', 'mall_credit', '1', 'radio', 'order_evaluation_poor,order_evaluation_common,order_evaluation_good', 'order_evaluation_good', '6', 'mall'),
('mall_auto_allow', 'mall_conf', 1, 'radio', '0=no,1=all,2=certified', 0, 0, 'mall'),
('store_intro', 'store_intro', 0, 'html', '', '', 1, 'store'),
('store_title', 'store_conf', 1, 'string', '^[^\\<|\\>]+$', '我的店舖', 2, 'store'),
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
-- 導出表中的數據 `ecm_config_value`
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
(0, 'mall_name', '您的網站名稱'),
(0, 'mall_title', '您的網站標題'),
(0, 'mall_keywords', ''),
(0, 'mall_description', '這是一個用ECMall架設的網上商城'),
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
(0, 'mall_closed_reason', '商城維護中，暫時關閉，請稍候訪問。'),
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
-- 導出表中的數據 `ecm_mail_templates`
--

INSERT INTO `ecm_mail_templates` (`template_id`, `template_code`, `subject`, `content`) VALUES
(1, 'send_coupon', '{$store_name}送給您的優惠券', '{$user_name}，您好：\r\n　　{$store_name}通過{$mall_name}贈送給您一個價值{$coupon_value}的優惠券。\r\n　　優惠券號碼為：{$coupon_sn} 請記住這個號碼，該優惠券可使用{$max_times}次，請在{$start_time}至{$end_time}期間使用，並且訂單中包含的商品金額大於{$min_amount}的訂單才可使用，點擊下面的鏈接前往[url={$store_url}]{$store_name}[/url]選購您喜歡的商品。 \r\n[url={$store_url}]{$store_url}[/url]\r\n　　　　　　　　　　　　　　　　　　　　　　　　　　　{$send_date}'),
(2, 'add_admin', '恭喜您升為{$store_name}管理員', '恭喜您！\r\n店鋪[url={$store_url}]{$store_name}[/url]要把您加為管理員，您是否接受，若您接受，請點擊以下地址接受並成為店舖的管理員\r\n[url={$accept_url}]接受地址：{$accept_url}[/url]\r\n提示：若您接受並成為店舖的管理員後，您將失去開獨立開店的機會，除非您不再是任何店舖的管理員\r\n{$store_name}的管理地址: {$admin_url} ') ,
(3, 'shipping_notice', '賣家{$boss}已經給你發貨了,請注意查收', '親愛的{$order.consignee}。你好！\r\n您的訂單{$order. order_sn}已於{$send_date}按照您預定的配送方式給您發貨了。\r\n給您的發貨單號是{$order.invoice_no}。 \r\n在您收到貨物之後請點擊下面的鏈接確認您已經收到貨物：\r\n[url={$confirm_url}]{$confirm_url} [/url]\r\n警告：若您還沒有收到貨，請不要點擊確認收貨。若賣家發貨後14天您還沒有確認收貨，系統將自動為您收貨\r\n再次感謝您對我們的支持。歡迎您的再次光臨。 \r\n\r\n {$boss}\r\n{$send_date}\r\n本郵件為ECMall系統自動發出，您無需回复'),
(4, 'order_cancel', '{$boss}取消了您的訂單', '親愛的{$order.consignee}，你好！ \r\n您的編號為：{$order.order_sn}的訂單已取消。\r\n{$boss}\r\n{$send_date}\r\n\r\n本郵件為ECMall系統自動發出'),
(5, 'order_acceptted', '{$boss}接受了您的訂單', '親愛的{$order.consignee}，你好！ \r\n\r\n我們已經收到您於{$order. add_time|date:Ymd H:i}提交的訂單，該訂單編號為：{$order.order_sn}請記住這個編號以便日後的查詢。\r\n\r\n{$boss}\r\n {$sent_date}\r\n\r\n\r\n'),
(6, 'get_pwd', '取回密碼', '您好！{$cur_date}申請了密碼取回。\r\n點擊：[url={$repwd_url}]點{$repwd_url}[/url]重置您的密碼。\r\n此鏈接有效期至：{$expire_date}\r\n請您盡快點擊上面的重置密碼鏈接重置您的密碼'),
(7, 'new_order_notify', '{$mall_name}：您的訂單信息', '親愛的{$order.consignee}：\r\n你好！\r\n您於{$order.add_time|date: Ymd H:i}在{$mall_name}提交了訂單。\r\n以下是該訂單的簡要信息，供您查詢\r\n訂單編號：{$order.order_sn}\r \n收貨人信息\r\n姓名：{$order.consignee}\r\n地址：[{$order.region}] {$order.address}\r\n郵編：{$order.zipcode}\r\nEMail： {$order.email}\r\n手機：{$order.mobile_phone}\r\n電話：{$order.home_phone}\r\n 辦公電話：{$order.office_phone}\r\n最佳配送時間：{$order.best_time}\r\n標誌建築： {$order.sign_building}\r\n配送支付信息\r\n配送方式：{$order.shipping_name}\r\n支付方式： {$order.pay_name}\r\n請記住訂單編號以便日後的查詢\r\n若以上信息有誤，請及時聯繫賣家解決，您可以通過登錄賣家的店舖首頁找到該賣家的聯繫方式，賣家的店舖首頁是：[url={$store_url}]{$boss}:{$store_url}[/url]\r\n請及時支付，支付地址是[url={$pay_url}]{$pay_url}[/url]\r\n{$boss}\r\n{$sent_date}'),
(8, 'seller_new_order_notify', '{$mall_name}:{$order.consignee}在您的店鋪下了一個新訂單', '親愛的{$boss}：\r\n你好！\r\n您於{$order.add_time|date:Ymd H:i}收到了由{$order.consignee}提交的訂單。\r\n以下是該訂單的簡要信息，供您查詢\r\n訂單編號：{ $order.order_sn}\r\n收貨人信息\r\n姓名：{$order.consignee}\r\n地址： [{$order.region}] {$order.address}\r\n郵編：{$order.zipcode}\r\nEMail：{$order.email}\r\n手機： {$order.mobile_phone}\r\n電話：{$order.home_phone}\r\n辦公電話： {$order.office_phone}\r\n最佳配送時間：{$order.best_time}\r\n標誌建築： {$order.sign_building}\r\n配送支付信息\r\n配送方式：{$ order.shipping_name}\r\n支付方式：{$order.pay_name}\r\n請及時根據處理該訂單,[url= {$parse_order_url}]點擊我馬上處理:{$parse_order_url}[/url]\ r\n登錄您的店舖管理後台[url={$store_admin_url}]{$store_admin_url}[/url]\r \n{$mall_name}{$sent_date}'),
(9, 'evaluation_invalid_to_buyer', '{$mall_name}:您給商家{$seller}的評價無效', '親愛的{$buyer}:\r\n   您給您于{$order.add_time|date}下的訂單(訂單號:{$order.order_sn})的評價被視為無效的評價，因此該評價將不對商家{$seller}的信用積分造成影響。\r\n   具體的原因為：{$reason|escape}\r\n\r\n   點擊以下鏈接訪問{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(10, 'evaluation_invalid_to_seller', '{$mall_name}:您給買家{$buyer}的評價無效', '親愛的{$seller}:\r\n   您給{$buyer}于{$order.add_time|date}在您店鋪下的訂單(訂單號:{$order.order_sn})的評價被視為無效的評價，因此該評價將不對該買家的信用積分造成影響。\r\n   具體的原因為：{$reason|escape}\r\n\r\n   點擊以下鏈接訪問{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(11, 'evaluation_invalid_from_seller', '{$mall_name}：買家給您的評價已無效', '親愛的{$seller}:\r\n   {$order.user_name|escape}給您的交易(訂單號：{$order.order_sn})的評價已被視為無效，因此這次評價將不會給您的信用積分造成影響\r\n   具體原因：{$reason|escape}\r\n\r\n\r\n   點擊以下鏈接訪問{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(12, 'evaluation_invalid_from_buyer', '{$mall_name}：商家給您的評價已無效', '親愛的{$buyer}:\r\n   商家{$boss}給您的交易(訂單號：{$order.order_sn})的評價已被視為無效，因此這次評價將不會給您的信用積分造成影響\r\n   具體原因：{$reason|escape}\r\n\r\n\r\n   點擊以下鏈接訪問{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(13, 'to_seller_evaluation_notify', '{$mall_name}:與您交易的買家{$buyer}給您做了評價', '親愛的{$seller}:\r\n   買家{$buyer}給與您的交易(訂單號：{$order.order_sn})做了評價，請盡快給對方評價。\r\n   \r\n   點擊以下鏈接管理訂單\r\n   [url={$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}]{$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}[/url]\r\n\r\n   點擊以下鏈接訪問商城\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(14, 'to_buyer_evaluation_notify', '{$mall_name}:與您交易的賣家{$seller}給您做了評價', '親愛的{$buyer}:\r\n   賣家{$seller}給與您的交易(訂單號：{$order.order_sn})做了評價，謝謝您的惠顧。\r\n\r\n   點擊以下鏈接查看訂單\r\n   [url={$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}]{$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}[/url]\r\n\r\n   點擊以下鏈接訪問商城\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(15, 'relet_remind', '您的店鋪即將到期', '親愛的{$user_name}\r\n\r\n您的店鋪將於{$days_left}日後到期，請您及時續費。 \r\n\r\n\r\n {$mall_name} {$site_url}\r\n {$sent_date}\r\n');

--
-- 導出表中的數據 `ecm_article_cate`
--

INSERT INTO `ecm_article_cate` (`cate_id`, `store_id`, `cate_name`, `parent_id`, `keywords`, `sort_order`, `editable`) VALUES
(1, 0, '商城幫助', 0, '', 0, 0),
(2, 0, '商城快訊', 0, '', 0, 0);


--
-- 導出表中的數據 `ecm_partner`
--

INSERT INTO `ecm_partner` VALUES
(1, 0, 'Shopex', 'http://www.shopex.cn', '', 0),
(2, 0, 'ECShop', 'http://www.ecshop.com', '', 0);


--
-- 導出表中的數據 `ecm_templates`
--

INSERT INTO `ecm_templates` (`store_id`, `config`, `filename`, `pagename`, `hash_code`) VALUES (0, 'a:6:{s:7:"region1";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:2:{s:11:"page_header";a:2:{s:2:"id";s:11:"page_header";s:3:"src";s:37:"themes/mall/resource/page_header.html";}s:11:"search_form";a:2:{s:2:"id";s:11:"search_form";s:3:"src";s:37:"themes/mall/resource/search_form.html";}}}s:7:"region4";a:1:{s:8:"children";a:5:{s:14:"goods_category";a:3:{s:2:"id";s:14:"goods_category";s:3:"src";s:40:"themes/mall/resource/goods_category.html";s:6:"parent";s:7:"region4";}s:17:"recommended_store";a:3:{s:2:"id";s:17:"recommended_store";s:3:"src";s:43:"themes/mall/resource/recommended_store.html";s:6:"parent";s:7:"region4";}s:17:"recommended_brand";a:3:{s:2:"id";s:17:"recommended_brand";s:3:"src";s:43:"themes/mall/resource/recommended_brand.html";s:6:"parent";s:7:"region4";}s:11:"latest_sold";a:3:{s:2:"id";s:11:"latest_sold";s:3:"src";s:37:"themes/mall/resource/latest_sold.html";s:6:"parent";s:7:"region4";}s:7:"partner";a:3:{s:2:"id";s:7:"partner";s:3:"src";s:33:"themes/mall/resource/partner.html";s:6:"parent";s:7:"region4";}}}s:7:"region6";a:1:{s:8:"children";a:1:{s:10:"cycleimage";a:3:{s:2:"id";s:10:"cycleimage";s:3:"src";s:36:"themes/mall/resource/cycleimage.html";s:6:"parent";s:7:"region6";}}}s:7:"region7";a:1:{s:8:"children";a:1:{s:16:"latest_site_news";a:3:{s:2:"id";s:16:"latest_site_news";s:3:"src";s:42:"themes/mall/resource/latest_site_news.html";s:6:"parent";s:7:"region7";}}}s:7:"region8";a:1:{s:8:"children";a:4:{s:9:"group_buy";a:3:{s:2:"id";s:9:"group_buy";s:3:"src";s:35:"themes/mall/resource/group_buy.html";s:6:"parent";s:7:"region8";}s:4:"cm_2";a:6:{s:2:"id";s:4:"cm_2";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:11:"Lady\'s Wear";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_4";a:6:{s:2:"id";s:4:"cm_4";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:14:"Digital Camera";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_1";a:6:{s:2:"id";s:4:"cm_1";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:6:"Mobile";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}}}s:7:"region3";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:1:{s:11:"page_footer";a:2:{s:2:"id";s:11:"page_footer";s:3:"src";s:37:"themes/mall/resource/page_footer.html";}}}}', 'default.layout', 'homepage', '527e4d037cdf839f58c9720fb85ffc95');

--
-- 導出表中的數據 `ecm_custom_modules`
--

INSERT INTO `ecm_custom_modules` (`id`, `name`, `type`, `config`) VALUES (1, '手機', 0, 'a:10:{s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(2, '女裝', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(4, '數碼相機', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}');

--
-- 導出表中的數據 `ecm_crontab`
--

INSERT INTO `ecm_crontab` (`task_name`, `plan_time`, `run_time`) VALUES
('auto_order_handle', 0, 0),
('auto_store_handle', 0, 0),
('auto_send_mail', 0, 0);
