INSERT INTO `ecm_store` (`store_id`, `store_name`, `store_location`, `add_time`, `start_time`, `end_time`, `is_open`, `is_recommend`, `is_certified`, `owner_name`, `owner_idcard`, `owner_phone`, `owner_address`, `owner_zipcode`, `goods_limit`, `file_limit`, `goods_count`, `order_count`) VALUES
({uid}, '测试店铺', 2, 1200435654, 0, 0, 1, 1, 1, 'test', '', '', '', '', 2000, 200, 16, 0);



--
-- 导出表中的数据 `ecm_config_value`
--


INSERT INTO `ecm_config_value` (`store_id`, `code`, `value`) VALUES ({uid}, 'store_name', 'ANDY的店铺'),
({uid}, 'store_title', '测试店铺'),
({uid}, 'store_logo', ''),
({uid}, 'store_keywords', '女装 休闲'),
({uid}, 'store_description', '店铺描述'),
({uid}, 'store_porto_arrived_pay', '0'),
({uid}, 'store_status', '1'),
({uid}, 'store_inv_enable', '0'),
({uid}, 'store_tax_rate', '0.1'),
({uid}, 'store_inv_content', ''),
({uid}, 'store_feed_default_status', '1'),
({uid}, 'store_page_size', '12'),
({uid}, 'store_declaration', ''),
({uid}, 'store_skin', 'default'),
({uid}, 'store_intro', '这里是店铺介绍'),
({uid}, 'store_qq', ''),
({uid}, 'store_ww', ''),
({uid}, 'store_msn', '');

--
-- 导出表中的数据 `ecm_payment`
--


INSERT INTO `ecm_payment` (`pay_id`, `store_id`, `pay_code`, `pay_name`, `pay_desc`, `pay_fee`, `config`, `is_cod`, `is_online`, `enabled`, `sort_order`, `author`, `website`, `version`) VALUES
(null, {uid}, 'tenpay', '财付通中介担保接口', '[color=red]该帐号仅供测试，且支付的钱不能退回，测试时请特别注意！[/color]\r\n[b]商户号： 1202437801 操组员号码：1202437801001 登录密码：TenpayTest[/b]', '0', 'a:3:{s:14:"tenpay_account";a:3:{s:4:"name";s:18:"财付通商户号";s:4:"type";s:4:"text";s:5:"value";s:9:"805308604";}s:10:"tenpay_key";a:3:{s:4:"name";s:15:"财付通密钥";s:4:"type";s:4:"text";s:5:"value";s:32:"tenpaytesttenpaytesttenpaytest12";}s:11:"tenpay_type";a:4:{s:4:"name";s:12:"交易类型";s:4:"type";s:6:"select";s:5:"value";s:1:"1";s:5:"range";a:2:{i:1;s:18:"实物商品交易";i:2;s:18:"虚拟物品交易";}}}', 0, 1, 1, 0, 'ECMall TEAM', 'http://www.tenpay.com', '1.0.0');
--
-- 导出表中的数据 `ecm_attribute`
--

INSERT INTO `ecm_attribute` (`attr_id`, `type_id`, `attr_name`, `input_type`, `value_range`, `search_type`, `sort_order`, `if_link`, `attr_group`) VALUES
(1, 1, '款式', 'text', '', '', 1, 0, 0),
(2, 1, '主要质地', 'text', '', '', 2, 0, 0),
(3, 1, '图案', 'text', '', '', 3, 0, 0),
(4, 1, '适合季节', 'text', '', '', 4, 0, 0),
(5, 2, '介质', 'select', 'DVD\r\nCD', '', 0, 0, 0),
(6, 2, '碟片数', 'text', '', '', 0, 0, 0),
(7, 2, 'ISRC/ISBN', 'text', '', '', 0, 0, 0),
(8, 2, '包装', 'select', '简装\r\n精装', '', 0, 0, 0),
(9, 2, '发行', 'text', '', '', 0, 0, 0),
(10, 3, '网络制式', 'select', 'GSM\r\nCDMA\r\nWCDMA\r\nTDCDMA', '', 0, 0, 0),
(11, 3, '外观样式', 'select', '直板\r\n翻盖\r\n旋转', '', 0, 0, 0),
(12, 3, '主屏', 'text', '', '', 0, 0, 0),
(13, 3, '数据传输', 'text', '', '', 0, 0, 0),
(14, 3, '摄像头像素', 'text', '', '', 0, 0, 0),
(15, 3, '摄像头位置', 'select', '内置\r\n无', '', 0, 0, 0),
(16, 3, '屏幕种类', 'text', '', '', 0, 0, 0),
(17, 3, '存储功能', 'text', '', '', 0, 0, 0),
(18, 3, '高级功能', 'text', '', '', 0, 0, 0),
(19, 4, '有效像素', 'text', '', '', 0, 0, 0),
(20, 4, '光学变焦倍数', 'text', '', '', 0, 0, 0),
(21, 4, '数码变焦倍数', 'text', '', '', 0, 0, 0),
(22, 4, '操作模式', 'text', '', '', 0, 0, 0),
(23, 4, '传感器尺寸', 'text', '', '', 0, 0, 0),
(24, 4, '传感器类型', 'text', '', '', 0, 0, 0),
(25, 4, '传感器描述', 'text', '', '', 0, 0, 0),
(26, 4, '最大分辨率', 'text', '', '', 0, 0, 0),
(27, 4, '短片拍摄功能', 'text', '', '', 0, 0, 0),
(28, 4, '液晶屏尺寸', 'text', '', '', 0, 0, 0),
(29, 4, '液晶屏特性', 'text', '', '', 0, 0, 0),
(30, 4, '取景器类型', 'text', '', '', 0, 0, 0),
(31, 4, '取景器描述', 'text', '', '', 0, 0, 0),
(32, 4, '镜头类型', 'text', '', '', 0, 0, 0),
(33, 4, '镜头描述', 'text', '', '', 0, 0, 0),
(34, 4, '焦距', 'text', '', '', 0, 0, 0),
(35, 4, '实际焦距', 'text', '', '', 0, 0, 0),
(36, 4, '对焦方式', 'text', '', '', 0, 0, 0),
(37, 4, '普通对焦范围', 'text', '', '', 0, 0, 0),
(38, 4, '光圈范围', 'text', '', '', 0, 0, 0),
(39, 4, '曝光模式', 'text', '', '', 0, 0, 0),
(40, 4, '测光方式', 'text', '', '', 0, 0, 0),
(41, 4, 'ISO感光度', 'text', '', '', 0, 0, 0),
(42, 4, '白平衡模式', 'text', '', '', 0, 0, 0),
(43, 4, '模式', 'text', '', '', 0, 0, 0),
(44, 4, '防抖功能', 'text', '', '', 0, 0, 0),
(45, 4, '连拍', 'text', '', '', 0, 0, 0),
(46, 4, '自拍', 'text', '', '', 0, 0, 0),
(47, 4, '机身闪光灯', 'text', '', '', 0, 0, 0),
(48, 4, '闪光模式', 'text', '', '', 0, 0, 0),
(49, 4, '存储介质', 'text', '', '', 0, 0, 0),
(50, 4, '照片格式', 'text', '', '', 0, 0, 0),
(51, 4, '数据接口', 'text', '', '', 0, 0, 0),
(52, 4, '电池', 'text', '', '', 0, 0, 0),
(53, 4, '尺寸', 'text', '', '', 0, 0, 0),
(54, 4, '重量', 'text', '', '', 0, 0, 0);


--
-- 导出表中的数据 `ecm_category`
--

INSERT INTO `ecm_category` (`cate_id`, `store_id`, `cate_name`, `keywords`, `parent_id`, `sort_order`, `template_file`, `style`, `if_show`, `grade`, `type_id`, `goods_count`, `price_interval`) VALUES
(1, 0, '男女服饰', NULL, 0, 0, NULL, NULL, 1, 0, 1, 0, ''),
(2, 0, '女装', NULL, 1, 0, NULL, NULL, 1, 0, 1, 0, ''),
(3, 0, 'T恤', NULL, 2, 1, NULL, NULL, 1, 0, 1, 4, ''),
(4, 0, '裙子', NULL, 2, 2, NULL, NULL, 1, 0, 1, 8, ''),
(5, 0, '裤子', NULL, 2, 3, NULL, NULL, 1, 0, 1, 3, ''),
(6, 0, '男装', NULL, 1, 0, NULL, NULL, 1, 0, 1, 0, ''),
(7, 0, 'T恤', NULL, 6, 0, NULL, NULL, 1, 0, 1, 0, ''),
(8, 0, '衬衣', NULL, 6, 0, NULL, NULL, 1, 0, 1, 0, ''),
(9, 0, '裤子', NULL, 6, 0, NULL, NULL, 1, 0, 1, 1, ''),
(10, 0, '运动', NULL, 1, 0, NULL, NULL, 1, 0, 1, 0, ''),
(11, 0, '服装', NULL, 10, 0, NULL, NULL, 1, 0, 1, 1, ''),
(12, 0, '鞋', NULL, 10, 0, NULL, NULL, 1, 0, 0, 0, ''),
(13, 0, '泳衣', NULL, 10, 0, NULL, NULL, 1, 0, 0, 0, ''),
(14, 0, '美容时尚', NULL, 0, 0, NULL, NULL, 1, 0, 0, 0, ''),
(15, 0, '护肤', NULL, 14, 0, NULL, NULL, 1, 0, 0, 0, ''),
(16, 0, '面膜', NULL, 15, 0, NULL, NULL, 1, 0, 0, 0, ''),
(17, 0, '爽肤水', NULL, 15, 0, NULL, NULL, 1, 0, 0, 0, ''),
(18, 0, '眼霜', NULL, 15, 0, NULL, NULL, 1, 0, 0, 0, ''),
(19, 0, '彩妆', NULL, 14, 0, NULL, NULL, 1, 0, 0, 0, ''),
(20, 0, '睫毛膏', NULL, 19, 0, NULL, NULL, 1, 0, 0, 0, ''),
(21, 0, '蜜粉', NULL, 19, 0, NULL, NULL, 1, 0, 0, 0, ''),
(22, 0, '香水', NULL, 19, 0, NULL, NULL, 1, 0, 0, 0, ''),
(23, 0, '数码家电', NULL, 0, 0, NULL, NULL, 1, 0, 0, 0, ''),
(24, 0, '手机', NULL, 23, 0, NULL, NULL, 1, 0, 0, 0, ''),
(25, 0, '诺基亚', NULL, 24, 0, NULL, NULL, 1, 0, 0, 0, ''),
(26, 0, '摩托', NULL, 24, 0, NULL, NULL, 1, 0, 0, 0, ''),
(27, 0, '三星', NULL, 24, 0, NULL, NULL, 1, 0, 0, 1, ''),
(28, 0, '数码', NULL, 23, 0, NULL, NULL, 1, 0, 0, 0, ''),
(29, 0, '摄像机', NULL, 28, 0, NULL, NULL, 1, 0, 0, 1, ''),
(30, 0, '相机', NULL, 28, 0, NULL, NULL, 1, 0, 0, 5, ''),
(31, 0, 'MP3/4', NULL, 28, 0, NULL, NULL, 1, 0, 0, 0, ''),
(32, 0, '家居生活', NULL, 0, 0, NULL, NULL, 1, 0, 0, 0, ''),
(33, 0, '居家', NULL, 32, 0, NULL, NULL, 1, 0, 0, 0, ''),
(34, 0, '床品', NULL, 33, 0, NULL, NULL, 1, 0, 0, 0, ''),
(35, 0, '厨具', NULL, 33, 0, NULL, NULL, 1, 0, 0, 0, ''),
(36, 0, '卫浴', NULL, 33, 0, NULL, NULL, 1, 0, 0, 0, ''),
(37, 0, '影音', NULL, 32, 0, NULL, NULL, 1, 0, 0, 0, ''),
(38, 0, '音乐', NULL, 37, 0, NULL, NULL, 1, 0, 0, 0, ''),
(39, 0, '电影', NULL, 37, 0, NULL, NULL, 1, 0, 0, 0, ''),
(40, 0, '电视剧', NULL, 37, 0, NULL, NULL, 1, 0, 0, 0, ''),
(41, {uid}, '服装', NULL, 0, 0, NULL, NULL, 1, 0, 0, 4, ''),
(42, {uid}, '上衣', NULL, 41, 0, NULL, NULL, 1, 0, 0, 2, ''),
(43, {uid}, '长裤', NULL, 41, 0, NULL, NULL, 1, 0, 0, 2, ''),
(44, {uid}, '短裤', NULL, 41, 0, NULL, NULL, 1, 0, 0, 0, ''),
(45, {uid}, '裙子', NULL, 41, 0, NULL, NULL, 1, 0, 0, 2, ''),
(46, {uid}, '手机', NULL, 0, 0, NULL, NULL, 1, 0, 0, 0, ''),
(47, {uid}, 'GSM手机', NULL, 46, 0, NULL, NULL, 1, 0, 0, 0, ''),
(48, {uid}, 'CDMA手机', NULL, 46, 0, NULL, NULL, 1, 0, 0, 2, ''),
(49, {uid}, '双网双待', NULL, 46, 0, NULL, NULL, 1, 0, 0, 3, ''),
(50, {uid}, '数码', NULL, 0, 0, NULL, NULL, 1, 0, 0, 1, ''),
(51, {uid}, '数码相机', NULL, 50, 0, NULL, NULL, 1, 0, 0, 1, ''),
(52, {uid}, '数码摄像机', NULL, 50, 0, NULL, NULL, 1, 0, 0, 1, '');

--
-- 导出表中的数据 `ecm_goods_type`
--

INSERT INTO `ecm_goods_type` (`type_id`, `store_id`, `type_name`, `attr_group`) VALUES
(1, 0, '服装', ''),
(2, 0, '影音', ''),
(3, 0, '手机', ''),
(4, 0, '相机', '');

--
-- 导出表中的数据 `ecm_brand`
--

INSERT INTO `ecm_brand` (`brand_id`, `store_id`, `brand_name`, `brand_logo`, `website`, `if_show`, `is_promote`, `goods_count`) VALUES
(1, 0, '诺基亚', 'data/user_files/demo/9f68f3d58c2e77fcce7163955eaa0ce3.jpg', '', 1, 1, 0),
(2, 0, '索尼爱立信', 'data/user_files/demo/b518016c028e4778044d764a671488b2.jpg', '', 1, 1, 0),
(3, 0, '佳能', 'data/user_files/demo/cca8957f7157943b93bdc09595aaa08e.jpg', '', 1, 1, 1),
(4, 0, '多普达', 'data/user_files/demo/ad372f7570aa621725270e8287c24564.jpg', '', 1, 1, 1),
(5, 0, '苹果', 'data/user_files/demo/bcb3c80ca654586867c208c5295dda50.jpg', '', 1, 1, 1),
(6, 0, '索尼', 'data/user_files/demo/28353efcbdc5394a2faf1be026114230.jpg', '', 1, 1, 2);

--
-- 导出表中的数据 `ecm_goods`
--

INSERT INTO `ecm_goods` (`goods_id`, `store_id`, `goods_name`, `mall_cate_id`, `store_cate_id`, `goods_name_style`, `click_count`, `brand_id`, `goods_weight`, `keywords`, `goods_brief`, `goods_desc`, `is_real`, `extension_code`, `is_on_sale`, `is_deny`, `is_alone_sale`, `give_points`, `max_use_points`, `add_time`, `last_update`, `is_mi_best`, `is_mw_best`, `is_m_hot`, `is_s_best`, `is_s_new`, `sort_weighing`, `type_id`, `sales_volume`, `seller_note`, `default_spec`, `new_level`) VALUES (1, {uid}, '多普达S1', 24, 47, '', 4, 4, '0.000', '', '', '2.8”全液晶Touch智慧触屏\r\nTocuhFLO全界面手指触控\r\n纤薄小巧造型\r\n两种界面选择:HTC home  我的多普达', 0, '', 1, 0, 0, 0, 0, 1213149475, 1213149475, 1, 0, 1, 0, 1, 0, 3, 0, '', 1, 10),
(2, {uid}, '诺基亚N96', 25, 47, '', 2, 1, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213150361, 1213150361, 0, 0, 1, 0, 1, 0, 3, 0, '', 2, 10),
(3, {uid}, '索尼爱立信W960i', 24, 47, '', 2, 2, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213212942, 1213212942, 1, 1, 1, 0, 1, 0, 3, 0, '', 3, 10),
(4, {uid}, 'RIM 黑莓 8830', 24, 49, '', 3, 0, '0.000', '黑莓', '', '', 0, '', 1, 0, 0, 0, 0, 1213213992, 1213213992, 1, 1, 1, 0, 0, 0, 3, 0, '', 4, 10),
(5, {uid}, 'RIM 黑莓 8310', 24, 47, '', 1, 0, '0.000', '黑莓', '', '', 0, '', 1, 0, 0, 0, 0, 1213214364, 1213214364, 0, 1, 1, 0, 1, 0, 3, 0, '', 6, 10),
(6, {uid}, '苹果iPhone', 24, 47, '', 1, 5, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213217589, 1213217589, 1, 1, 1, 0, 0, 0, 3, 0, '', 7, 10),
(7, {uid}, '时尚款，芭蕾女孩休闲T恤', 3, 42, '', 3, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213218865, 1213218865, 1, 1, 1, 1, 0, 0, 1, 0, '', 8, 10),
(8, {uid}, '阿森纳女短T恤', 3, 42, '', 1, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213219871, 1213219871, 1, 1, 1, 1, 0, 0, 1, 0, '', 10, 10),
(9, {uid}, '阿森纳女梭织短裤', 5, 44, '', 7, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213220127, 1213220127, 1, 1, 1, 1, 0, 0, 1, 0, '', 12, 10),
(10, {uid}, '主格 08款花色高腰吊带裙', 5, 45, '', 0, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213220603, 1213220603, 1, 1, 1, 1, 0, 0, 1, 0, '', 13, 10),
(13, {uid}, '佳能S5 IS', 30, 51, '', 1, 3, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213222736, 1213222736, 1, 1, 1, 0, 1, 0, 0, 0, '', 16, 10),
(14, {uid}, '索尼W300 (SONY W300)', 30, 51, '', 0, 6, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213222959, 1213222959, 1, 1, 1, 0, 1, 0, 0, 0, '', 17, 10),
(15, {uid}, '索尼α350 (SONY α350)', 30, 51, '', 1, 6, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213223093, 1213223093, 1, 0, 1, 0, 1, 0, 0, 0, '', 18, 10),
(16, {uid}, '三星i100 (Samsung i100)', 30, 51, '', 4, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213223400, 1213223400, 0, 1, 1, 0, 0, 0, 0, 0, '', 19, 10),
(12, {uid}, '佳能450D 单反数码相机', 30, 51, '', 3, 3, '0.000', '', '', '普及型产品，专业级画质 ，迅捷反应，高速连拍 \r\n先进的9点宽区自动对焦系统 \r\n3.0&quot;宽视角LCD液晶监视器与实时显示拍摄模式 \r\nEOS综合除尘系统 \r\n强大功能，人性操作 ，完善的系统支持', 0, '', 1, 0, 0, 0, 0, 1213221543, 1213221543, 1, 0, 1, 0, 0, 5, 4, 0, '', 15, 10),
(17, {uid}, '尼康S550 (Nikon S550)', 30, 51, '', 10, 0, '0.000', '', '', '', 0, '', 1, 0, 0, 0, 0, 1213223740, 1213223740, 0, 1, 1, 0, 1, 0, 0, 0, '', 23, 10);

--
-- 导出表中的数据 `ecm_goods_attr`
--

INSERT INTO `ecm_goods_attr` (`goods_id`, `attr_id`, `attr_value`) VALUES
(1, 12, '65K色'),
(1, 13, '蓝牙，USB'),
(1, 14, '200万像素'),
(1, 15, '内置'),
(1, 16, 'TFT'),
(1, 11, '直板'),
(1, 10, 'GSM'),
(2, 10, 'GSM'),
(2, 11, '直板'),
(2, 12, '2.8英寸'),
(2, 13, '蓝牙，USB，WiFi'),
(2, 14, '500万像素'),
(2, 15, '内置'),
(2, 16, 'TFT'),
(2, 17, '支持MicroSD卡'),
(2, 18, ''),
(3, 18, ''),
(3, 10, 'GSM'),
(3, 11, '直板'),
(3, 12, '2.6英寸'),
(3, 13, ''),
(3, 14, '320万像素'),
(3, 15, '内置'),
(3, 16, 'QVGA分辨率240x320像素'),
(3, 17, '8GB'),
(4, 10, 'CDMA'),
(4, 11, '直板'),
(4, 12, '320×240像素'),
(4, 13, '支持蓝牙 2.0'),
(4, 14, ''),
(4, 15, '无'),
(4, 16, 'TFT'),
(4, 17, '64MB 闪存；16MB RAM'),
(4, 18, ''),
(5, 10, 'GSM'),
(5, 11, '直板'),
(5, 12, '65K色彩屏'),
(5, 13, '蓝牙，USB 2.0'),
(5, 14, '200万像素'),
(5, 15, '内置'),
(5, 16, 'TFT'),
(5, 17, '64MB'),
(5, 18, ''),
(6, 16, '1600万色TFT'),
(6, 15, '内置'),
(6, 14, '200万像素'),
(6, 13, '蓝牙，USB 2.0'),
(6, 12, '3.5英寸'),
(6, 11, '直板'),
(6, 10, 'GSM'),
(7, 1, '长款'),
(7, 2, '棉'),
(7, 3, ''),
(7, 4, '夏季'),
(8, 1, ''),
(8, 2, '94%棉,6%氨纶'),
(8, 3, ''),
(8, 4, '春夏秋'),
(9, 3, ''),
(9, 2, '100%涤纶；里料：100%涤纶'),
(9, 1, ''),
(9, 4, '春夏秋'),
(10, 1, '中款'),
(10, 2, '面料：100%涤纶；里料：100%涤纶'),
(10, 3, ''),
(10, 4, '春夏'),
(12, 49, ''),
(12, 48, ''),
(12, 47, ''),
(12, 46, ''),
(12, 45, ''),
(12, 44, ''),
(12, 43, ''),
(12, 42, ''),
(12, 41, ''),
(12, 40, ''),
(12, 39, ''),
(12, 38, ''),
(12, 37, ''),
(12, 36, ''),
(12, 35, ''),
(12, 34, ''),
(12, 33, ''),
(12, 32, ''),
(12, 31, ''),
(12, 30, ''),
(12, 29, ''),
(12, 28, ''),
(12, 27, ''),
(12, 26, ''),
(12, 25, ''),
(12, 24, ''),
(12, 23, ''),
(12, 22, ''),
(12, 21, ''),
(12, 20, ''),
(12, 19, ''),
(12, 50, ''),
(12, 51, ''),
(12, 52, ''),
(12, 53, ''),
(12, 54, ''),
(6, 17, '8GB'),
(6, 18, ''),
(1, 17, '支持MicroSD卡'),
(1, 18, '');

--
-- 导出表中的数据 `ecm_goods_color`
--


--
-- 导出表中的数据 `ecm_goods_spec`
--

INSERT INTO `ecm_goods_spec` (`spec_id`, `goods_id`, `color_name`, `color_rgb`, `spec_name`, `sku`, `stock`, `market_price`, `store_price`, `default_image`, `sort_order`) VALUES
(1, 1, '黑色', '#000000', '', 'ECM00001-01', 10, '2399.00', '2000.00', 1, 0),
(2, 2, '黑色', '#000000', '', 'ECM00002-01', 90, '5800.00', '5800.00', 4, 0),
(3, 3, '黑色', '#000000', '', 'ECM00003-01', 30, '3180.00', '2350.00', 9, 0),
(4, 4, '银色', '#C0C0C0', '', 'ECM00004-01', 30, '3200.00', '2500.00', 10, 0),
(5, 4, '红色', '#FF0000', '', 'ECM00004-02', 30, '3200.00', '2500.00', 12, 0),
(6, 5, '银色', '#C0C0C0', '', 'ECM00005-01', 90, '1750.00', '1600.00', 13, 0),
(7, 6, '黑色', '#000000', '', 'ECM00006-01', 40, '4280.00', '3800.00', 14, 0),
(8, 7, '粉色', '#FF99CC', '', 'ECM00007-01', 80, '150.00', '60.00', 16, 0),
(9, 7, '蓝色', '#00CCFF', '', 'ECM00007-02', 80, '150.00', '60.00', 19, 0),
(10, 8, '黑色', '#000000', '', 'ECM00008-01', 80, '198.00', '150.00', 22, 0),
(12, 9, '白色', '#FFFFFF', '', 'ECM00009-02', 40, '128.00', '102.00', 25, 0),
(13, 10, '', '', '', 'ECM00010-01', 1, '679.00', '380.00', 31, 0),
(16, 13, '', '', '', 'ECM00013-01', 25, '3200.00', '2800.00', 44, 0),
(15, 12, '', '', '', 'ECM00012-01', 12, '5999.00', '5655.00', 39, 0),
(17, 14, '', '', '', 'ECM00014-01', 13, '2800.00', '2100.00', 48, 0),
(18, 15, '', '', '', 'ECM00015-01', 12, '4590.00', '4300.00', 51, 0),
(19, 16, '黑色', '#000000', '', 'ECM00016-01', 12, '2000.00', '1800.00', 57, 0),
(20, 16, '褐色', '#993300', '', 'ECM00016-02', 12, '2000.00', '1800.00', 60, 0),
(21, 16, '红色', '#FF0000', '', 'ECM00016-03', 12, '2000.00', '1800.00', 61, 0),
(22, 16, '银色', '#C0C0C0', '', 'ECM00016-04', 12, '2000.00', '1800.00', 59, 0),
(23, 17, '蓝色', '#0000FF', '', 'ECM00017-01', 15, '2150.00', '1850.00', 62, 0),
(24, 17, '紫罗兰', '#800080', '', 'ECM00017-02', 6, '2150.00', '1750.00', 64, 0),
(25, 17, '银色', '#C0C0C0', '', 'ECM00017-03', 10, '2150.00', '1650.00', 66, 0),
(26, 17, '黑色', '#000000', '', 'ECM00017-04', 10, '2150.00', '1650.00', 68, 0);

--
-- 导出表中的数据 `ecm_upload_files`
--

INSERT INTO `ecm_upload_files` (`file_id`, `item_id`, `item_type`, `color`, `file_type`, `file_ext`, `file_size`, `file_name`, `orig_name`, `add_time`, `sort_order`, `store_id`) VALUES
(1, 1, 'album', '', 'image/jpeg', 'jpg', 86829, 'data/user_files/demo/ee9250c6b2259d922455468b0a837985.jpg', '1.jpg', 1213178275, 0, 10),
(2, 1, 'album', '', 'image/jpeg', 'jpg', 180693, 'data/user_files/demo/00e801203457ff1ec9cabe921fbc8d40.jpg', '1.jpg', 1213178487, 0, 10),
(3, 1, 'album', '', 'image/jpeg', 'jpg', 239358, 'data/user_files/demo/cd9527fb3226f121db77f843dd1f146f.jpg', '2.jpg', 1213178487, 0, 10),
(4, 2, 'album', '', 'image/jpeg', 'jpg', 91511, 'data/user_files/demo/8ca33954aeae5df1f77bdb157a7e519f.jpg', '1.jpg', 1213179161, 0, 10),
(9, 3, 'album', '', 'image/pjpeg', 'jpg', 75773, 'data/user_files/demo/f484d6bc21f0bbaecaa57b58f444cf7a.jpg', '1.jpg', 1213242286, 0, 10),
(10, 4, 'album', '银色', 'image/pjpeg', 'jpg', 183333, 'data/user_files/demo/5ee100a78cfabc6002b91b5d7dbc5208.jpg', '2.jpg', 1213242792, 0, 10),
(11, 4, 'album', '银色', 'image/pjpeg', 'jpg', 137532, 'data/user_files/demo/b6e395d925a19b962bd08bad3c6ea05e.jpg', '3.jpg', 1213242792, 0, 10),
(12, 4, 'album', '红色', 'image/pjpeg', 'jpg', 108838, 'data/user_files/demo/8c9c4e503e6ef814f23d9346c074810b.jpg', '1.jpg', 1213242792, 0, 10),
(13, 5, 'album', '', 'image/pjpeg', 'jpg', 72481, 'data/user_files/demo/e8d82a66c1fd406ac1572d6e92a96347.jpg', '1.jpg', 1213243165, 0, 10),
(14, 6, 'album', '', 'image/pjpeg', 'jpg', 74121, 'data/user_files/demo/11039f201ee5bc999f334ac798dd3dbd.jpg', '1.jpg', 1213246390, 0, 10),
(15, 6, 'album', '', 'image/pjpeg', 'jpg', 67321, 'data/user_files/demo/c0be65df5e69fe9fa4bff8e75912d4c5.jpg', '2.jpg', 1213246390, 0, 10),
(16, 7, 'album', '粉色', 'image/pjpeg', 'jpg', 67383, 'data/user_files/demo/0fc597371fb03fdff514bbc526b4311f.jpg', '1.jpg', 1213247665, 0, 10),
(17, 7, 'album', '粉色', 'image/pjpeg', 'jpg', 82662, 'data/user_files/demo/b4dba3a8d7b5a561e10fea88cf760eef.jpg', '2.jpg', 1213247665, 0, 10),
(18, 7, 'album', '粉色', 'image/pjpeg', 'jpg', 83006, 'data/user_files/demo/5c6a9114ac6a664aaf38d15066f9e476.jpg', '3.jpg', 1213247665, 0, 10),
(19, 7, 'album', '蓝色', 'image/pjpeg', 'jpg', 62726, 'data/user_files/demo/b0d839b8839a6713e697fbfc8f95486e.jpg', '4.jpg', 1213247665, 0, 10),
(20, 7, 'album', '蓝色', 'image/pjpeg', 'jpg', 51850, 'data/user_files/demo/4c0ea7486429a85c5a41b237139b9d83.jpg', '5.jpg', 1213247665, 0, 10),
(21, 7, 'album', '蓝色', 'image/pjpeg', 'jpg', 62182, 'data/user_files/demo/38a62e2f53bf3a8d04904617e04bd73f.jpg', '6.jpg', 1213247665, 0, 10),
(22, 8, 'album', '', 'image/pjpeg', 'jpg', 121722, 'data/user_files/demo/9ad41558ca857927410bb1a6d57c56be.jpg', '1.jpg', 1213248671, 0, 10),
(23, 8, 'album', '', 'image/pjpeg', 'jpg', 119674, 'data/user_files/demo/54d89001cfe0dfbf6e43c51871a4dc95.jpg', '2.jpg', 1213248671, 0, 10),
(24, 8, 'album', '', 'image/pjpeg', 'jpg', 141447, 'data/user_files/demo/606fff27c85737fbc020a5fbdeecf247.jpg', '3.jpg', 1213248671, 0, 10),
(25, 9, 'album', '白色', 'image/pjpeg', 'jpg', 121944, 'data/user_files/demo/87525966b4b1a5cfbf0b7773d2283674.jpg', '1.jpg', 1213248927, 0, 10),
(26, 9, 'album', '白色', 'image/pjpeg', 'jpg', 95728, 'data/user_files/demo/f3bfb811425822645a974e769df6fbdb.jpg', '2.jpg', 1213248927, 0, 10),
(27, 9, 'album', '白色', 'image/pjpeg', 'jpg', 121944, 'data/user_files/demo/df315e2177f13a8be9c3d9b350c7fe92.jpg', '3.jpg', 1213248927, 0, 10),
(31, 10, 'album', '', 'image/pjpeg', 'jpg', 158241, 'data/user_files/demo/bd491a387d4e69655b470acd3f1e91ac.jpg', '1.jpg', 1213249403, 0, 10),
(32, 10, 'album', '', 'image/pjpeg', 'jpg', 119911, 'data/user_files/demo/5d59c03add212c0bef38964f99033efa.jpg', '2.jpg', 1213249403, 0, 10),
(33, 10, 'album', '', 'image/pjpeg', 'jpg', 229801, 'data/user_files/demo/5ae40dd6afd41dcb8d300f9003c482ec.jpg', '3.jpg', 1213249403, 0, 10),
(43, 12, 'album', '', 'image/pjpeg', 'jpg', 229127, 'data/user_files/demo/9519cb35db108d0f75577d3e32446383.jpg', '5.jpg', 1213250536, 0, 10),
(42, 12, 'album', '', 'image/pjpeg', 'jpg', 210294, 'data/user_files/demo/0fcddc7c4ecafb6334921040cd34dbca.jpg', '4.jpg', 1213250536, 0, 10),
(41, 12, 'album', '', 'image/pjpeg', 'jpg', 205953, 'data/user_files/demo/9a3a0fe6e379bba2895c8def3a420b14.jpg', '3.jpg', 1213250536, 0, 10),
(40, 12, 'album', '', 'image/pjpeg', 'jpg', 98873, 'data/user_files/demo/2130270e96d0dc7d3e60c395b87623c2.jpg', '2.jpg', 1213250536, 0, 10),
(39, 12, 'album', '', 'image/pjpeg', 'jpg', 111931, 'data/user_files/demo/dee685024f09a860da30ffdd157e7ea1.jpg', '1.jpg', 1213250536, 0, 10),
(44, 13, 'album', '', 'image/pjpeg', 'jpg', 100691, 'data/user_files/demo/c5486c24885826af00c10274678b5548.jpg', '1.jpg', 1213251536, 0, 10),
(45, 13, 'album', '', 'image/pjpeg', 'jpg', 50278, 'data/user_files/demo/ae52391aa8d79c1fe47c93f9366f9d2f.jpg', '2.jpg', 1213251536, 0, 10),
(46, 13, 'album', '', 'image/pjpeg', 'jpg', 130317, 'data/user_files/demo/570cfa2fde70e4add2c0ab11e9542239.jpg', '3.jpg', 1213251536, 0, 10),
(47, 13, 'album', '', 'image/pjpeg', 'jpg', 79006, 'data/user_files/demo/a9fb3b2c935c92feceb3fda8efd23038.jpg', '4.jpg', 1213251536, 0, 10),
(48, 14, 'album', '', 'image/pjpeg', 'jpg', 93089, 'data/user_files/demo/9c868eba18330618d920c4518cfeced4.jpg', '1.jpg', 1213251759, 0, 10),
(49, 14, 'album', '', 'image/pjpeg', 'jpg', 137301, 'data/user_files/demo/78b2fe2b4f27a6598ef0e9578e7950ad.jpg', '2.jpg', 1213251759, 0, 10),
(50, 14, 'album', '', 'image/pjpeg', 'jpg', 110044, 'data/user_files/demo/0f25429266b6cf294f2e08b4ca018ba4.jpg', '3.jpg', 1213251759, 0, 10),
(51, 15, 'album', '', 'image/pjpeg', 'jpg', 279534, 'data/user_files/demo/f3334e1fcc426520deb01efd371dc35d.jpg', '1.jpg', 1213251893, 0, 10),
(52, 15, 'album', '', 'image/pjpeg', 'jpg', 188895, 'data/user_files/demo/2b4f6f15ee8342a6895415e631429e2c.jpg', '2.jpg', 1213251893, 0, 10),
(53, 15, 'album', '', 'image/pjpeg', 'jpg', 220884, 'data/user_files/demo/9df3b7ccd52bc0b3d69ba1984baaa978.jpg', '3.jpg', 1213251893, 0, 10),
(54, 15, 'album', '', 'image/pjpeg', 'jpg', 74248, 'data/user_files/demo/980a8dd853396f3df8c87745f7b84ec1.jpg', '4.jpg', 1213251893, 0, 10),
(55, 15, 'album', '', 'image/pjpeg', 'jpg', 96651, 'data/user_files/demo/a84a7dbc6bc0e8b7b46cee42ece4ca6e.jpg', '5.jpg', 1213251893, 0, 10),
(56, 15, 'album', '', 'image/pjpeg', 'jpg', 128631, 'data/user_files/demo/42b97e24c1a64e848d78d27729aa75bd.jpg', '6.jpg', 1213251893, 0, 10),
(57, 16, 'album', '黑色', 'image/pjpeg', 'jpg', 43967, 'data/user_files/demo/b16c78e806604b99e9c5bc059d786daf.jpg', '1.jpg', 1213252201, 0, 10),
(58, 16, 'album', '', 'image/pjpeg', 'jpg', 22802, 'data/user_files/demo/117297b8592f9ae98dee57eba0ab3149.jpg', '2.jpg', 1213252201, 0, 10),
(59, 16, 'album', '银色', 'image/pjpeg', 'jpg', 17638, 'data/user_files/demo/268f8e90c0d74fbf1d5571abf80b90ee.jpg', '3.jpg', 1213252201, 0, 10),
(60, 16, 'album', '褐色', 'image/pjpeg', 'jpg', 24442, 'data/user_files/demo/d8a4d2a55d0d45e4251c859b8024fbd6.jpg', '4.jpg', 1213252201, 0, 10),
(61, 16, 'album', '红色', 'image/pjpeg', 'jpg', 16729, 'data/user_files/demo/bcaee1c70f640943f03023dce0668cc8.jpg', '5.jpg', 1213252201, 0, 10),
(62, 17, 'album', '蓝色', 'image/pjpeg', 'jpg', 58413, 'data/user_files/demo/c0982c46dbd562298ca43334a2b4c3b0.jpg', '1.jpg', 1213252540, 0, 10),
(63, 17, 'album', '蓝色', 'image/pjpeg', 'jpg', 47439, 'data/user_files/demo/4ee7431280eaf9612db1ffe13968c12a.jpg', '2.jpg', 1213252540, 0, 10),
(64, 17, 'album', '紫罗兰', 'image/pjpeg', 'jpg', 52730, 'data/user_files/demo/e92ea6caa1e88b518c0da9edbfe7b1cd.jpg', '3.jpg', 1213252540, 0, 10),
(65, 17, 'album', '紫罗兰', 'image/pjpeg', 'jpg', 28414, 'data/user_files/demo/c530d43ea346988323f086ca673791ad.jpg', '4.jpg', 1213252540, 0, 10),
(66, 17, 'album', '银色', 'image/pjpeg', 'jpg', 37762, 'data/user_files/demo/38934ec5be2d2d4a1b9d213ffcd61106.jpg', '5.jpg', 1213252540, 0, 10),
(67, 17, 'album', '银色', 'image/pjpeg', 'jpg', 42196, 'data/user_files/demo/fcd1b89fdd9a0c828452d6ec9c7824b9.jpg', '6.jpg', 1213252540, 0, 10),
(68, 17, 'album', '黑色', 'image/pjpeg', 'jpg', 64413, 'data/user_files/demo/faaabeed13db83a6bbd3910cbdf064d9.jpg', '1.jpg', 1213252581, 0, 10),
(69, 17, 'album', '黑色', 'image/pjpeg', 'jpg', 49752, 'data/user_files/demo/449523a1181572ec485830df07feba26.jpg', '2.jpg', 1213252581, 0, 10);

--
-- 导出表中的数据 `ecm_related_goods`
--

INSERT INTO `ecm_related_goods` (`goods_id`, `relation`, `related_goods_id`, `sort_order`) VALUES (5, 's', 4, 0),
(4, 's', 5, 0),
(9, 'c', 8, 0),
(8, 'c', 9, 0),
(9, 'c', 7, 0),
(7, 'c', 9, 0);


--
-- 导出表中的数据 `ecm_shipping`
--

INSERT INTO `ecm_shipping` (`shipping_id`, `store_id`, `shipping_name`, `shipping_desc`, `shipping_fee`, `surcharge`, `cod_regions`, `enabled`) VALUES (1, {uid}, '快递', '', '10.00', '2.00', '2', 1);
