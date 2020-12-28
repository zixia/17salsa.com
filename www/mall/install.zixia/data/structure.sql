-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2008 年 05 月 05 日 08:28
-- 服务器版本: 5.0.45
-- PHP 版本: 5.2.3

--
-- 数据库: `ecmall`
--

-- --------------------------------------------------------

--
-- 表的结构 `ecm_ad`
--

DROP TABLE IF EXISTS `ecm_ad`;
CREATE TABLE `ecm_ad` (
  `ad_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `position_id` smallint(6) NOT NULL,
  `ad_type` varchar(10) NOT NULL,
  `ad_name` varchar(120) NOT NULL,
  `ad_link` varchar(255) NOT NULL,
  `ad_code` text,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `click_count` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY  (`ad_id`),
  KEY `position_id` (`position_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_admin_log`
--

DROP TABLE IF EXISTS `ecm_admin_log`;
CREATE TABLE `ecm_admin_log` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(40) NOT NULL,
  `application` varchar(30) NOT NULL,
  `action` varchar(30) NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL default '0',
  `execution_time` int(11) unsigned NOT NULL,
  `ip_address` varchar(25) NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `user_id` (`username`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_admin_user`
--

DROP TABLE IF EXISTS `ecm_admin_user`;
CREATE TABLE `ecm_admin_user` (
  `user_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL default '0',
  `real_name` varchar(60) NOT NULL,
  `add_time` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `last_ip` varchar(15) NOT NULL,
  `privilege` text,
  `status` tinyint(1) NOT NULL default '0',
  `nav_list` text,
  `todolist` longtext,
  `recent_ip` text,
  PRIMARY KEY  (`user_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_ad_position`
--

DROP TABLE IF EXISTS `ecm_ad_position`;
CREATE TABLE `ecm_ad_position` (
  `position_id` smallint(6) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `position_name` varchar(60) NOT NULL,
  `position_desc` varchar(255) NOT NULL,
  `width` smallint(6) NOT NULL default '0',
  `height` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`position_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_article`
--

DROP TABLE IF EXISTS `ecm_article`;
CREATE TABLE `ecm_article` (
  `article_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `editor_type` int(11) NOT NULL DEFAULT 0,
  `file_url` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `author` varchar(60) NOT NULL,
  `code` varchar(60) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `is_top` tinyint(4) NOT NULL,
  `if_show` tinyint(4) NOT NULL,
  `add_time` int(11) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`article_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_article_cate`
--

DROP TABLE IF EXISTS `ecm_article_cate`;
CREATE TABLE `ecm_article_cate` (
  `cate_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `cate_name` varchar(120) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `editable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`cate_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_attribute`
--

DROP TABLE IF EXISTS `ecm_attribute`;
CREATE TABLE `ecm_attribute` (
  `attr_id` int(11) NOT NULL auto_increment,
  `type_id` int(11) NOT NULL,
  `attr_name` varchar(100) NOT NULL,
  `input_type` varchar(10) NOT NULL,
  `value_range` text NOT NULL,
  `search_type` varchar(10) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `if_link` tinyint(4) NOT NULL,
  `attr_group` tinyint(4) NOT NULL,
  PRIMARY KEY  (`attr_id`),
  KEY `type_id` (`type_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_auction_log`
--

DROP TABLE IF EXISTS `ecm_auction_log`;
CREATE TABLE `ecm_auction_log` (
  `rec_id` int(11) NOT NULL auto_increment,
  `act_id` int(11) NOT NULL,
  `bid_user` int(11) NOT NULL,
  `bid_price` decimal(10,2) NOT NULL,
  `bid_time` int(11) NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `act_id` (`act_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_brand`
--

DROP TABLE IF EXISTS `ecm_brand`;
CREATE TABLE `ecm_brand` (
  `brand_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_logo` varchar(255) NOT NULL,
  `website` varchar(100) NOT NULL,
  `if_show` tinyint(4) NOT NULL,
  `is_promote` tinyint(3) unsigned NOT NULL default '0',
  `goods_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`brand_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_cart`
--

DROP TABLE IF EXISTS `ecm_cart`;
CREATE TABLE `ecm_cart` (
  `rec_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `store_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `spec_id` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `market_price` decimal(10,2) NOT NULL,
  `goods_price` decimal(10,2) NOT NULL,
  `goods_number` int(11) NOT NULL,
  `is_real` tinyint(4) NOT NULL,
  `extension_code` varchar(30) NOT NULL,
  `is_gift` tinyint(4) NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_category`
--

DROP TABLE IF EXISTS `ecm_category`;
CREATE TABLE `ecm_category` (
  `cate_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `cate_name` varchar(100) NOT NULL,
  `keywords` varchar(255) default NULL,
  `parent_id` int(11) NOT NULL,
  `sort_order` smallint(6) NOT NULL,
  `template_file` varchar(255) default NULL,
  `style` varchar(255) default NULL,
  `if_show` tinyint(4) NOT NULL default '1',
  `grade` tinyint(4) NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `goods_count` int(10) unsigned NOT NULL default '0',
  `price_interval` varchar(255) NOT NULL,
  PRIMARY KEY  (`cate_id`),
  KEY `store_id` (`store_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_collect_goods`
--

DROP TABLE IF EXISTS `ecm_collect_goods`;
CREATE TABLE `ecm_collect_goods` (
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `add_time` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_comment`
--

DROP TABLE IF EXISTS `ecm_comment`;
CREATE TABLE `ecm_comment` (
  `comment_id` int(11) NOT NULL auto_increment,
  `object_type` varchar(1) NOT NULL,
  `object_id` int(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `name` varchar(60) NOT NULL,
  `content` text NOT NULL,
  `reply` text NOT NULL,
  `grade` tinyint(4) NOT NULL,
  `add_time` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `if_show` tinyint(4) NOT NULL,
  PRIMARY KEY  (`comment_id`),
  KEY `object_type` (`object_type`,`object_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_config_item`
--

DROP TABLE IF EXISTS `ecm_config_item`;
CREATE TABLE `ecm_config_item` (
  `code` varchar(30) NOT NULL,
  `group_code` varchar(30) NOT NULL,
  `required` tinyint(1) NOT NULL default '0',
  `type` varchar(10) NOT NULL,
  `params` varchar(255) NOT NULL,
  `default_value` varchar(255) NOT NULL,
  `sort_order` tinyint(4) NOT NULL default '0',
  `owner` enum('mall','store') NOT NULL default 'store',
  PRIMARY KEY  (`code`),
  KEY `group_code` (`group_code`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_config_value`
--

DROP TABLE IF EXISTS `ecm_config_value`;
CREATE TABLE `ecm_config_value` (
  `store_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`store_id`,`code`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_coupon`
--

DROP TABLE IF EXISTS `ecm_coupon`;
CREATE TABLE `ecm_coupon` (
  `coupon_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `coupon_name` varchar(120) NOT NULL,
  `coupon_value` decimal(10,2) NOT NULL,
  `max_times` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `min_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`coupon_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_coupon_sn`
--

DROP TABLE IF EXISTS `ecm_coupon_sn`;
CREATE TABLE `ecm_coupon_sn` (
  `coupon_sn` varchar(12) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `usable_times` int(11) NOT NULL,
  PRIMARY KEY  (`coupon_sn`),
  KEY `coupon_id` (`coupon_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_custom_modules`
--

DROP TABLE IF EXISTS `ecm_custom_modules`;
CREATE TABLE `ecm_custom_modules` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `type` int(11) NOT NULL default '0',
  `config` text NOT NULL,
  `store_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_faq`
--

DROP TABLE IF EXISTS `ecm_faq`;
CREATE TABLE `ecm_faq` (
  `rec_id` smallint(6) NOT NULL auto_increment,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` smallint(6) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  PRIMARY KEY  (`rec_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods`
--

DROP TABLE IF EXISTS `ecm_goods`;
CREATE TABLE `ecm_goods` (
  `goods_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `mall_cate_id` int(11) NOT NULL,
  `store_cate_id` int(11) NOT NULL default '0',
  `goods_name_style` varchar(60) NOT NULL,
  `click_count` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `goods_weight` decimal(10,3) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `goods_brief` varchar(255) NOT NULL,
  `goods_desc` text NOT NULL,
  `editor_type` int(11) NOT NULL DEFAULT 0,
  `is_real` tinyint(4) NOT NULL,
  `extension_code` varchar(30) NOT NULL,
  `is_on_sale` tinyint(4) NOT NULL,
  `is_deny` tinyint(4) NOT NULL default '0',
  `is_alone_sale` tinyint(4) NOT NULL,
  `give_points` int(11) NOT NULL,
  `max_use_points` int(11) NOT NULL,
  `add_time` int(11) NOT NULL,
  `last_update` int(11) NOT NULL,
  `is_mi_best` tinyint(4) NOT NULL,
  `is_mw_best` tinyint(4) NOT NULL,
  `is_m_hot` tinyint(4) NOT NULL,
  `is_s_best` tinyint(4) NOT NULL,
  `is_s_new` tinyint(3) unsigned NOT NULL default '0',
  `sort_weighing` int(10) unsigned NOT NULL default '0',
  `type_id` int(11) NOT NULL,
  `cart_volumn` int(10) unsigned NOT NULL default '0',
  `order_volumn` int(10) unsigned NOT NULL default '0',
  `sales_volume` mediumint(8) unsigned NOT NULL default '0',
  `seller_note` varchar(255) NOT NULL,
  `default_spec` int(11) NOT NULL,
  `new_level` tinyint(1) NOT NULL default '10',
  PRIMARY KEY  (`goods_id`),
  KEY `store_id` (`store_id`),
  KEY `goods_name` (`goods_name`),
  KEY `cate_id` (`mall_cate_id`),
  KEY `brand_id` (`brand_id`),
  KEY `store_cate_id` (`store_cate_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods_activity`
--

DROP TABLE IF EXISTS `ecm_goods_activity`;
CREATE TABLE `ecm_goods_activity` (
  `act_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `act_name` varchar(255) NOT NULL,
  `act_desc` text NOT NULL,
  `act_type` tinyint(4) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `spec_id` varchar(255) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `is_finished` tinyint(4) NOT NULL,
  `ext_info` text NOT NULL,
  `number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`act_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods_attr`
--

DROP TABLE IF EXISTS `ecm_goods_attr`;
CREATE TABLE `ecm_goods_attr` (
  `goods_id` int(11) NOT NULL,
  `attr_id` int(11) NOT NULL,
  `attr_value` text NOT NULL,
  PRIMARY KEY  (`goods_id`,`attr_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods_color`
--

DROP TABLE IF EXISTS `ecm_goods_color`;
CREATE TABLE `ecm_goods_color` (
  `color_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_rgb` varchar(6) NOT NULL,
  PRIMARY KEY  (`color_id`),
  KEY `store_id` (`store_id`),
  KEY `color_name` (`color_name`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods_spec`
--

DROP TABLE IF EXISTS `ecm_goods_spec`;
CREATE TABLE `ecm_goods_spec` (
  `spec_id` int(11) NOT NULL auto_increment,
  `goods_id` int(11) NOT NULL,
  `color_name` varchar(60) NOT NULL,
  `color_rgb` varchar(7) NOT NULL,
  `spec_name` varchar(255) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `stock` int(11) NOT NULL,
  `market_price` decimal(10,2) NOT NULL,
  `store_price` decimal(10,2) NOT NULL,
  `default_image` int(10) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY  (`spec_id`),
  KEY `goods_id` (`goods_id`),
  KEY `sku` (`sku`),
  KEY `color_name` (`color_name`,`goods_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_goods_type`
--

DROP TABLE IF EXISTS `ecm_goods_type`;
CREATE TABLE `ecm_goods_type` (
  `type_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `type_name` varchar(100) NOT NULL,
  `attr_group` varchar(255) NOT NULL,
  PRIMARY KEY  (`type_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_group_buy`
--

DROP TABLE IF EXISTS `ecm_group_buy`;
CREATE TABLE `ecm_group_buy` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `act_id` int(10) unsigned NOT NULL default '0',
  `goods_id` int(10) unsigned NOT NULL default '0',
  `spec_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_name` varchar(60) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `number` int(10) unsigned NOT NULL default '1',
  `add_time` int(11) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `notify` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`log_id`),
  KEY `act_id` (`act_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_mail_queue`
--

DROP TABLE IF EXISTS `ecm_mail_queue`;
CREATE TABLE `ecm_mail_queue` (
  `queue_id` int(11) unsigned NOT NULL auto_increment,
  `mail_to` varchar(150) NOT NULL,
  `mail_encoding` varchar(50) NOT NULL,
  `mail_subject` varchar(255) NOT NULL,
  `mail_body` text NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL default '2',
  `err_num` tinyint(1) unsigned NOT NULL default '0',
  `add_time` int(11) NOT NULL,
  `lock_expiry` int(11) NOT NULL,
  PRIMARY KEY  (`queue_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_mail_templates`
--

DROP TABLE IF EXISTS `ecm_mail_templates`;
CREATE TABLE `ecm_mail_templates` (
  `template_id` int(11) NOT NULL auto_increment,
  `template_code` varchar(30) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`template_id`),
  KEY `template_code` (`template_code`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_message`
--

DROP TABLE IF EXISTS `ecm_message`;
CREATE TABLE `ecm_message` (
  `message_id` int(10) unsigned NOT NULL auto_increment,
  `goods_id` int(10) unsigned NOT NULL default '0',
  `buyer_id` int(10) unsigned NOT NULL default '0',
  `buyer_name` varchar(60) NOT NULL,
  `seller_id` int(10) unsigned NOT NULL,
  `seller_name` varchar(60) NOT NULL,
  `message` varchar(255) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `reply` varchar(255) NOT NULL,
  `if_show` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `goods_id` (`goods_id`,`buyer_id`,`seller_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_nav`
--

DROP TABLE IF EXISTS `ecm_nav`;
CREATE TABLE `ecm_nav` (
  `nav_id` smallint(6) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `nav_name` varchar(60) NOT NULL,
  `nav_url` varchar(255) NOT NULL,
  `nav_position` varchar(10) NOT NULL,
  `if_show` tinyint(4) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `open_new` tinyint(4) NOT NULL,
  `is_app` tinyint(1) NOT NULL default '0',
  `cate_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`nav_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_offen_used_data`
--

DROP TABLE IF EXISTS `ecm_offen_used_data`;
CREATE TABLE `ecm_offen_used_data` (
  `store_id` int(11) NOT NULL,
  `data_type` char(1) NOT NULL,
  `data_id` int(11) NOT NULL,
  PRIMARY KEY  (`store_id`,`data_type`,`data_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_order_action`
--

DROP TABLE IF EXISTS `ecm_order_action`;
CREATE TABLE `ecm_order_action` (
  `action_id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `action_user` varchar(30) NOT NULL,
  `order_status` tinyint(4) NOT NULL,
  `action_note` varchar(255) NOT NULL,
  `action_time` int(11) NOT NULL,
  PRIMARY KEY  (`action_id`),
  KEY `order_id` (`order_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_order_goods`
--

DROP TABLE IF EXISTS `ecm_order_goods`;
CREATE TABLE `ecm_order_goods` (
  `rec_id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `spec_id` int(11) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `goods_number` int(11) NOT NULL,
  `market_price` decimal(10,2) NOT NULL,
  `goods_price` decimal(10,2) NOT NULL,
  `is_real` tinyint(4) NOT NULL,
  `extension_code` varchar(30) NOT NULL,
  `is_gift` tinyint(4) NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `order_id` (`order_id`),
  KEY `goods_id` (`goods_id`),
  KEY `spec_id` (`spec_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_order_info`
--

DROP TABLE IF EXISTS `ecm_order_info`;
CREATE TABLE `ecm_order_info` (
 `order_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `order_sn` varchar(20) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `user_ip` varchar(15) NOT NULL default '',
  `order_status` tinyint(4) NOT NULL default '0',
  `consignee` varchar(60) NOT NULL default '',
  `region` varchar(255) NOT NULL default '',
  `region_id` int(10) unsigned NOT NULL default '0',
  `address` varchar(255) NOT NULL default '',
  `zipcode` varchar(60) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `office_phone` varchar(60) NOT NULL default '',
  `home_phone` varchar(60) NOT NULL default '',
  `mobile_phone` varchar(60) NOT NULL default '',
  `sign_building` varchar(120) NOT NULL default '',
  `best_time` varchar(120) NOT NULL default '',
  `post_script` varchar(255) NOT NULL default '',
  `shipping_id` int(11) NOT NULL default '0',
  `shipping_name` varchar(120) NOT NULL default '',
  `pay_id` int(11) NOT NULL default '0',
  `pay_name` varchar(120) NOT NULL default '',
  `inv_payee` varchar(120) NOT NULL default '',
  `inv_content` varchar(120) NOT NULL default '',
  `inv_type` varchar(60) NOT NULL default '',
  `goods_amount` decimal(10,2) NOT NULL default '0.00',
  `discount` decimal(10,2) NOT NULL default '0.00',
  `shipping_fee` decimal(10,2) NOT NULL default '0.00',
  `insure_fee` decimal(10,2) NOT NULL default '0.00',
  `pay_fee` decimal(10,2) NOT NULL default '0.00',
  `inv_fee` decimal(10,2) NOT NULL default '0.00',
  `points` int(11) NOT NULL default '0',
  `points_value` decimal(10,2) NOT NULL default '0.00',
  `coupon_sn` varchar(12) NOT NULL default '',
  `coupon_value` decimal(10,2) NOT NULL default '0.00',
  `money_paid` decimal(10,2) NOT NULL default '0.00',
  `order_amount` decimal(10,2) NOT NULL default '0.00',
  `from_ad` int(11) NOT NULL default '0',
  `referer` varchar(255) NOT NULL default '',
  `add_time` int(11) NOT NULL default '0',
  `pay_time` int(10) unsigned NOT NULL default '0',
  `ship_time` int(10) unsigned NOT NULL default '0',
  `invoice_no` varchar(60) NOT NULL default '',
  `extension_code` varchar(30) NOT NULL default '',
  `extension_id` int(11) NOT NULL default '0',
  `to_buyer` varchar(255) NOT NULL default '',
  `seller_evaluation` tinyint(4) NOT NULL default '0',
  `seller_comment` text NOT NULL,
  `seller_credit` float NOT NULL,
  `seller_evaluation_invalid` tinyint(1) unsigned NOT NULL,
  `buyer_evaluation` tinyint(4) NOT NULL default '0',
  `buyer_comment` text NOT NULL,
  `buyer_credit` float NOT NULL,
  `buyer_evaluation_invalid` tinyint(1) unsigned NOT NULL,
  `is_anonymous` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`order_id`),
  KEY `store_id` (`store_id`),
  KEY `order_sn` (`order_sn`),
  KEY `user_id` (`user_id`),
  KEY `order_status` (`order_status`),
  KEY `shipping_id` (`shipping_id`),
  KEY `pay_id` (`pay_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_pageview`
--

DROP TABLE IF EXISTS `ecm_pageview`;
CREATE TABLE `ecm_pageview` (
  `store_id` int(10) unsigned NOT NULL,
  `view_date` date NOT NULL,
  `view_times` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`store_id`,`view_date`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_partner`
--

DROP TABLE IF EXISTS `ecm_partner`;
CREATE TABLE `ecm_partner` (
  `partner_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `partner_name` varchar(120) NOT NULL,
  `partner_website` varchar(255) NOT NULL,
  `partner_logo` varchar(255) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY  (`partner_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_payment`
--

DROP TABLE IF EXISTS `ecm_payment`;
CREATE TABLE `ecm_payment` (
  `pay_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `pay_code` varchar(20) NOT NULL,
  `pay_name` varchar(120) NOT NULL,
  `pay_desc` text NOT NULL,
  `pay_fee` varchar(10) NOT NULL,
  `config` text NOT NULL,
  `is_cod` tinyint(4) NOT NULL,
  `is_online` tinyint(4) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `author` varchar(100) NOT NULL,
  `website` varchar(100) NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY  (`pay_id`),
  KEY `store_id` (`store_id`),
  KEY `pay_code` (`pay_code`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_pay_log`
--

DROP TABLE IF EXISTS `ecm_pay_log`;
CREATE TABLE `ecm_pay_log` (
  `log_id` varchar(20) NOT NULL,
  `order_id` int(10) unsigned NOT NULL default '0',
  `order_type` tinyint(1) unsigned NOT NULL default '0',
  `is_paid` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `order_id` (`order_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_regions`
--

DROP TABLE IF EXISTS `ecm_regions`;
CREATE TABLE `ecm_regions` (
  `region_id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL,
  `region_name` varchar(100) NOT NULL,
  `store_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`region_id`),
  KEY `parent_id` (`parent_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_related_goods`
--

DROP TABLE IF EXISTS `ecm_related_goods`;
CREATE TABLE `ecm_related_goods` (
  `goods_id` int(11) NOT NULL,
  `relation` char(1) NOT NULL,
  `related_goods_id` int(11) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY  (`goods_id`,`relation`,`related_goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_sessions`
--

DROP TABLE IF EXISTS `ecm_sessions`;
CREATE TABLE `ecm_sessions` (
  `sesskey` char(32) NOT NULL,
  `expiry` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `adminid` int(11) NOT NULL,
  `ip` char(15) NOT NULL,
  `data` char(255) NOT NULL,
  `is_overflow` TINYINT NOT NULL,
  PRIMARY KEY  (`sesskey`),
  KEY `expiry` (`expiry`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_sessions_data`
--

DROP TABLE IF EXISTS `ecm_sessions_data`;
CREATE TABLE `ecm_sessions_data` (
  `sesskey` varchar(32) NOT NULL,
  `expiry` int(11) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY  (`sesskey`),
  KEY `expiry` (`expiry`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_shipping`
--

DROP TABLE IF EXISTS `ecm_shipping`;
CREATE TABLE `ecm_shipping` (
  `shipping_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `shipping_name` varchar(120) NOT NULL,
  `shipping_desc` text NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `surcharge` decimal(10,2) NOT NULL,
  `cod_regions` text NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY  (`shipping_id`),
  KEY `store_id` (`store_id`),
  KEY `shipping_code` (`shipping_name`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_store`
--

DROP TABLE IF EXISTS `ecm_store`;
CREATE TABLE `ecm_store` (
  `store_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `store_location` int(11) NOT NULL,
  `add_time` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `is_open` tinyint(4) NOT NULL,
  `is_recommend` tinyint(4) NOT NULL,
  `is_certified` tinyint(4) NOT NULL,
  `owner_name` varchar(60) NOT NULL,
  `owner_idcard` varchar(60) NOT NULL,
  `owner_phone` varchar(60) NOT NULL,
  `owner_address` varchar(255) NOT NULL,
  `owner_zipcode` varchar(60) NOT NULL,
  `goods_limit` int(11) NOT NULL,
  `file_limit` int(11) NOT NULL,
  `goods_count` int(10) unsigned NOT NULL default '0',
  `order_count` int(10) unsigned NOT NULL default '0',
  `custom` VARCHAR( 16 ) NOT NULL,
  PRIMARY KEY  (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_store_apply`
--

DROP TABLE IF EXISTS `ecm_store_apply`;
CREATE TABLE `ecm_store_apply` (
  `apply_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL,
  `store_name` VARCHAR( 255 ) NOT NULL,
  `store_location` INT( 10 ) NOT NULL,
  `owner_name` VARCHAR( 60 ) NOT NULL,
  `owner_idcard` VARCHAR( 60 ) NOT NULL,
  `owner_phone` VARCHAR( 60 ) NOT NULL,
  `owner_address` VARCHAR( 255 ) NOT NULL,
  `owner_zipcode` VARCHAR( 60 ) NOT NULL,
  `apply_reason` TEXT NOT NULL,
  `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  `add_time` INT( 11 ) UNSIGNED NOT NULL,
  `custom` VARCHAR( 16 ) NOT NULL,
  `paper_image` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (`apply_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_templates`
--

DROP TABLE IF EXISTS `ecm_templates`;
CREATE TABLE `ecm_templates` (
  `store_id` int(11) NOT NULL default '0',
  `config` text NOT NULL,
  `filename` varchar(60) NOT NULL default '',
  `pagename` varchar(60) NOT NULL default '',
  `hash_code` varchar(60) NOT NULL default '0'
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_upload_files`
--

DROP TABLE IF EXISTS `ecm_upload_files`;
CREATE TABLE `ecm_upload_files` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL default '0',
  `item_type` enum('album','goods','article','comment','brand', 'store_intro') NOT NULL default 'album',
  `color` varchar(10) NOT NULL,
  `file_type` varchar(40) NOT NULL,
  `file_ext` varchar(10) NOT NULL,
  `file_size` int(10) unsigned NOT NULL default '0',
  `file_name` varchar(100) NOT NULL,
  `orig_name` varchar(100) NOT NULL,
  `add_time` int(10) unsigned NOT NULL default '0',
  `sort_order` smallint(5) NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`file_id`),
  KEY `item_id` (`item_id`,`item_type`,`add_time`,`store_id`),
  KEY `sort_order` (`sort_order`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_users`
--

DROP TABLE IF EXISTS `ecm_users`;
CREATE TABLE `ecm_users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `user_name` varchar(60) NOT NULL,
  `password` varchar(32) NOT NULL,
  `gender` tinyint(4) NOT NULL,
  `birthday` date NOT NULL,
  `reg_time` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `last_ip` varchar(15) NOT NULL,
  `visit_count` int(11) NOT NULL,
  `seller_credit` float NOT NULL,
  `buyer_credit` float NOT NULL,
  `msn` varchar(60) NOT NULL,
  `qq` varchar(60) NOT NULL,
  `office_phone` varchar(60) NOT NULL,
  `home_phone` varchar(60) NOT NULL,
  `mobile_phone` varchar(60) NOT NULL,
  `repwd_code` varchar(32) NOT NULL,
  `store_id` int(11) unsigned NOT NULL default '0',
  `default_feed` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_user_account`
--

DROP TABLE IF EXISTS `ecm_user_account`;
CREATE TABLE `ecm_user_account` (
  `rec_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `add_time` int(11) NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `user_id` (`user_id`,`store_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_user_address`
--

DROP TABLE IF EXISTS `ecm_user_address`;
CREATE TABLE `ecm_user_address` (
  `address_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `consignee` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `region1` int(11) NOT NULL,
  `region2` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zipcode` varchar(60) NOT NULL,
  `office_phone` varchar(60) NOT NULL,
  `home_phone` varchar(60) NOT NULL,
  `mobile_phone` varchar(60) NOT NULL,
  `sign_building` varchar(120) NOT NULL,
  `best_time` varchar(120) NOT NULL,
  PRIMARY KEY  (`address_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM  ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_badwords`
--

DROP TABLE IF EXISTS `ecm_badwords`;
CREATE TABLE `ecm_badwords` (
  `words_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `words` VARCHAR(60) NOT NULL ,
  PRIMARY KEY (`words_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_rent_scheme`
--

DROP TABLE IF EXISTS `ecm_rent_scheme`;
CREATE TABLE `ecm_rent_scheme` (
  `scheme_id` smallint(5) unsigned NOT NULL auto_increment,
  `allowed_goods` int(10) unsigned NOT NULL,
  `allowed_file` int(10) unsigned NOT NULL,
  `allowed_month` tinyint(3) unsigned NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY  (`scheme_id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_crontab`
--

DROP TABLE IF EXISTS `ecm_crontab`;
CREATE TABLE IF NOT EXISTS `ecm_crontab` (
  `task_name` varchar(60) NOT NULL,
  `plan_time` int(11) NOT NULL,
  `run_time` int(11) NOT NULL,
  KEY `plan_time` (`plan_time`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_data_call`
--

DROP TABLE IF EXISTS `ecm_data_call`;
CREATE TABLE `ecm_data_call` (
  `id` int(11) NOT NULL auto_increment,
  `cate_id` int(11) NOT NULL default '0',
  `brand_id` int(11) NOT NULL default '0',
  `content_charset` int(11) NOT NULL,
  `call_desc` varchar(255) NOT NULL,
  `goods_name_length` int(11) NOT NULL,
  `goods_number` int(11) NOT NULL,
  `recommend_option` varchar(100) NOT NULL,
  `store_id` int(11) NOT NULL default '0',
  `template` text NOT NULL,
  `cache_time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_wanted`
--

DROP TABLE IF EXISTS `ecm_wanted`;
CREATE TABLE `ecm_wanted` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `add_time` int(11) unsigned NOT NULL,
  `cate_id` int(11) unsigned NOT NULL,
  `region_id` int(11) NOT NULL,
  `price_start` decimal(10,2) unsigned NOT NULL,
  `price_end` decimal(10,2) unsigned NOT NULL,
  `subject` varchar(100) NOT NULL,
  `detail` text NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `replies` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `cate_id` (`cate_id`),
  KEY `region_id` (`region_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- 表的结构 `ecm_wanted_reply`
--

DROP TABLE IF EXISTS `ecm_wanted_reply`;
CREATE TABLE `ecm_wanted_reply` (
  `replay_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `log_id` int(10) unsigned NOT NULL,
  `add_time` int(11) unsigned NOT NULL,
  `detail` text NOT NULL,
  `goods_url` varchar(100) NOT NULL,
  PRIMARY KEY  (`replay_id`),
  KEY `user_id` (`user_id`),
  KEY `log_id` (`log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk;
