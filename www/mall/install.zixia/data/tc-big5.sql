
--
-- �ɥX�����ƾ� `ecm_regions`
--


INSERT INTO `ecm_regions` (region_id, parent_id, region_name, store_id) VALUES
(1, 0, '����', 0),
(2, 1, '�_��', 0),
(3, 1, '�Ѭz', 0),
(4, 1, '�e�_', 0),
(5, 1, '�s��', 0),
(6, 1, '���X�j', 0),
(7, 1, '���', 0),
(8, 1, '�N�L', 0),
(9, 1, '���s��', 0),
(10, 1, '�W��', 0),
(11, 1, '��Ĭ', 0),
(12, 1, '����', 0),
(13, 1, '�w��', 0),
(14, 1, '�֫�', 0),
(15, 1, '����', 0),
(16, 1, '�s�F', 0),
(17, 1, '�e�n', 0),
(18, 1, '��_', 0),
(19, 1, '��n', 0),
(20, 1, '�s�F', 0),
(21, 1, '�s��', 0),
(22, 1, '���n', 0),
(23, 1, '���y', 0),
(24, 1, '�|�t', 0),
(25, 1, '�Q�{', 0),
(26, 1, '���n', 0),
(27, 1, '����', 0),
(28, 1, '����', 0),
(29, 1, '�̵�', 0),
(30, 1, '�C��', 0),
(31, 1, '��L', 0),
(32, 1, '�sæ', 0),
(33, 1, '����', 0),
(34, 1, '�x�W', 0);


--
-- �ɥX�����ƾ� `ecm_config_item`
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
('mall_closed_reason', 'mall_base', 0, 'text', '', '�ӫ����@���A�Ȯ������A�еy��X��', 15, 'mall'),
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
('store_title', 'store_conf', 1, 'string', '^[^\\<|\\>]+$', '�ڪ����E', 2, 'store'),
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
-- �ɥX�����ƾ� `ecm_config_value`
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
(0, 'mall_name', '�z�������W��'),
(0, 'mall_title', '�z���������D'),
(0, 'mall_keywords', ''),
(0, 'mall_description', '�o�O�@�ӥ�ECMall�[�]�����W�ӫ�'),
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
(0, 'mall_closed_reason', '�ӫ����@���A�Ȯ������A�еy�ԳX�ݡC'),
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
-- �ɥX�����ƾ� `ecm_mail_templates`
--

INSERT INTO `ecm_mail_templates` (`template_id`, `template_code`, `subject`, `content`) VALUES
(1, 'send_coupon', '{$store_name}�e���z���u�f��', '{$user_name}�A�z�n�G\r\n�@�@{$store_name}�q�L{$mall_name}�ذe���z�@�ӻ���{$coupon_value}���u�f��C\r\n�@�@�u�f�鸹�X���G{$coupon_sn} �аO��o�Ӹ��X�A���u�f��i�ϥ�{$max_times}���A�Цb{$start_time}��{$end_time}�����ϥΡA�åB�q�椤�]�t���ӫ~���B�j��{$min_amount}���q��~�i�ϥΡA�I���U�����챵�e��[url={$store_url}]{$store_name}[/url]���ʱz���w���ӫ~�C \r\n[url={$store_url}]{$store_url}[/url]\r\n�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@�@{$send_date}'),
(2, 'add_admin', '���߱z�ɬ�{$store_name}�޲z��', '���߱z�I\r\n���Q[url={$store_url}]{$store_name}[/url]�n��z�[���޲z���A�z�O�_�����A�Y�z�����A���I���H�U�a�}�����æ������E���޲z��\r\n[url={$accept_url}]�����a�}�G{$accept_url}[/url]\r\n���ܡG�Y�z�����æ������E���޲z����A�z�N���h�}�W�߶}�������|�A���D�z���A�O�����E���޲z��\r\n{$store_name}���޲z�a�}: {$admin_url} ') ,
(3, 'shipping_notice', '��a{$boss}�w�g���A�o�f�F,�Ъ`�N�d��', '�˷R��{$order.consignee}�C�A�n�I\r\n�z���q��{$order. order_sn}�w��{$send_date}���ӱz�w�w���t�e�覡���z�o�f�F�C\r\n���z���o�f�渹�O{$order.invoice_no}�C \r\n�b�z����f��������I���U�����챵�T�{�z�w�g����f���G\r\n[url={$confirm_url}]{$confirm_url} [/url]\r\nĵ�i�G�Y�z�٨S������f�A�Ф��n�I���T�{���f�C�Y��a�o�f��14�ѱz�٨S���T�{���f�A�t�αN�۰ʬ��z���f\r\n�A���P�±z��ڭ̪�����C�w��z���A�����{�C \r\n\r\n {$boss}\r\n{$send_date}\r\n���l��ECMall�t�Φ۰ʵo�X�A�z�L�ݦ^�`'),
(4, 'order_cancel', '{$boss}�����F�z���q��', '�˷R��{$order.consignee}�A�A�n�I \r\n�z���s�����G{$order.order_sn}���q��w�����C\r\n{$boss}\r\n{$send_date}\r\n\r\n���l��ECMall�t�Φ۰ʵo�X'),
(5, 'order_acceptted', '{$boss}�����F�z���q��', '�˷R��{$order.consignee}�A�A�n�I \r\n\r\n�ڭ̤w�g����z��{$order. add_time|date:Ymd H:i}���檺�q��A�ӭq��s�����G{$order.order_sn}�аO��o�ӽs���H�K��᪺�d�ߡC\r\n\r\n{$boss}\r\n {$sent_date}\r\n\r\n\r\n'),
(6, 'get_pwd', '���^�K�X', '�z�n�I{$cur_date}�ӽФF�K�X���^�C\r\n�I���G[url={$repwd_url}]�I{$repwd_url}[/url]���m�z���K�X�C\r\n���챵���Ĵ��ܡG{$expire_date}\r\n�бz�ɧ��I���W�������m�K�X�챵���m�z���K�X'),
(7, 'new_order_notify', '{$mall_name}�G�z���q��H��', '�˷R��{$order.consignee}�G\r\n�A�n�I\r\n�z��{$order.add_time|date: Ymd H:i}�b{$mall_name}����F�q��C\r\n�H�U�O�ӭq�檺²�n�H���A�ѱz�d��\r\n�q��s���G{$order.order_sn}\r \n���f�H�H��\r\n�m�W�G{$order.consignee}\r\n�a�}�G[{$order.region}] {$order.address}\r\n�l�s�G{$order.zipcode}\r\nEMail�G {$order.email}\r\n����G{$order.mobile_phone}\r\n�q�ܡG{$order.home_phone}\r\n �줽�q�ܡG{$order.office_phone}\r\n�̨ΰt�e�ɶ��G{$order.best_time}\r\n�лx�ؿv�G {$order.sign_building}\r\n�t�e��I�H��\r\n�t�e�覡�G{$order.shipping_name}\r\n��I�覡�G {$order.pay_name}\r\n�аO��q��s���H�K��᪺�d��\r\n�Y�H�W�H�����~�A�Фή��pô��a�ѨM�A�z�i�H�q�L�n����a�����E�������ӽ�a���pô�覡�A��a�����E�����O�G[url={$store_url}]{$boss}:{$store_url}[/url]\r\n�Фήɤ�I�A��I�a�}�O[url={$pay_url}]{$pay_url}[/url]\r\n{$boss}\r\n{$sent_date}'),
(8, 'seller_new_order_notify', '{$mall_name}:{$order.consignee}�b�z�����Q�U�F�@�ӷs�q��', '�˷R��{$boss}�G\r\n�A�n�I\r\n�z��{$order.add_time|date:Ymd H:i}����F��{$order.consignee}���檺�q��C\r\n�H�U�O�ӭq�檺²�n�H���A�ѱz�d��\r\n�q��s���G{ $order.order_sn}\r\n���f�H�H��\r\n�m�W�G{$order.consignee}\r\n�a�}�G [{$order.region}] {$order.address}\r\n�l�s�G{$order.zipcode}\r\nEMail�G{$order.email}\r\n����G {$order.mobile_phone}\r\n�q�ܡG{$order.home_phone}\r\n�줽�q�ܡG {$order.office_phone}\r\n�̨ΰt�e�ɶ��G{$order.best_time}\r\n�лx�ؿv�G {$order.sign_building}\r\n�t�e��I�H��\r\n�t�e�覡�G{$ order.shipping_name}\r\n��I�覡�G{$order.pay_name}\r\n�ФήɮھڳB�z�ӭq��,[url= {$parse_order_url}]�I���ڰ��W�B�z:{$parse_order_url}[/url]\ r\n�n���z�����E�޲z��x[url={$store_admin_url}]{$store_admin_url}[/url]\r \n{$mall_name}{$sent_date}'),
(9, 'evaluation_invalid_to_buyer', '{$mall_name}:�z���Ӯa{$seller}�������L��', '�˷R��{$buyer}:\r\n   �z���z�_{$order.add_time|date}�U���q��(�q�渹:{$order.order_sn})�������Q�����L�Ī������A�]���ӵ����N����Ӯa{$seller}���H�οn���y���v�T�C\r\n   ���骺��]���G{$reason|escape}\r\n\r\n   �I���H�U�챵�X��{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(10, 'evaluation_invalid_to_seller', '{$mall_name}:�z���R�a{$buyer}�������L��', '�˷R��{$seller}:\r\n   �z��{$buyer}�_{$order.add_time|date}�b�z���Q�U���q��(�q�渹:{$order.order_sn})�������Q�����L�Ī������A�]���ӵ����N����ӶR�a���H�οn���y���v�T�C\r\n   ���骺��]���G{$reason|escape}\r\n\r\n   �I���H�U�챵�X��{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(11, 'evaluation_invalid_from_seller', '{$mall_name}�G�R�a���z�������w�L��', '�˷R��{$seller}:\r\n   {$order.user_name|escape}���z�����(�q�渹�G{$order.order_sn})�������w�Q�����L�ġA�]���o�������N���|���z���H�οn���y���v�T\r\n   �����]�G{$reason|escape}\r\n\r\n\r\n   �I���H�U�챵�X��{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(12, 'evaluation_invalid_from_buyer', '{$mall_name}�G�Ӯa���z�������w�L��', '�˷R��{$buyer}:\r\n   �Ӯa{$boss}���z�����(�q�渹�G{$order.order_sn})�������w�Q�����L�ġA�]���o�������N���|���z���H�οn���y���v�T\r\n   �����]�G{$reason|escape}\r\n\r\n\r\n   �I���H�U�챵�X��{$mall_name}\r\n   [url={$site_url}]{$site_url}[/url]\r\n\r\n{$sent_date}'),
(13, 'to_seller_evaluation_notify', '{$mall_name}:�P�z������R�a{$buyer}���z���F����', '�˷R��{$seller}:\r\n   �R�a{$buyer}���P�z�����(�q�渹�G{$order.order_sn})���F�����A�кɧֵ��������C\r\n   \r\n   �I���H�U�챵�޲z�q��\r\n   [url={$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}]{$site_url}/admin.php?app=order&act=change_status&order_id={$order.order_id}[/url]\r\n\r\n   �I���H�U�챵�X�ݰӫ�\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(14, 'to_buyer_evaluation_notify', '{$mall_name}:�P�z�������a{$seller}���z���F����', '�˷R��{$buyer}:\r\n   ��a{$seller}���P�z�����(�q�渹�G{$order.order_sn})���F�����A���±z���f�U�C\r\n\r\n   �I���H�U�챵�d�ݭq��\r\n   [url={$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}]{$site_url}/index.php?app=member&act=order_detail&id={$order.order_id}[/url]\r\n\r\n   �I���H�U�챵�X�ݰӫ�\r\n   [url={$site_url}]{$site_url}[/url]\r\n{$sent_date}'),
(15, 'relet_remind', '�z�����Q�Y�N���', '�˷R��{$user_name}\r\n\r\n�z�����Q�N��{$days_left}������A�бz�ή���O�C \r\n\r\n\r\n {$mall_name} {$site_url}\r\n {$sent_date}\r\n');




--
-- �ɥX�����ƾ� `ecm_article_cate`
--

INSERT INTO `ecm_article_cate` (`cate_id`, `store_id`, `cate_name`, `parent_id`, `keywords`, `sort_order`, `editable`) VALUES
(1, 0, '�ӫ����U', 0, '', 0, 0),
(2, 0, '�ӫ��ְT', 0, '', 0, 0);


--
-- �ɥX�����ƾ� `ecm_partner`
--

INSERT INTO `ecm_partner` VALUES
(1, 0, 'Shopex', 'http://www.shopex.cn', '', 0),
(2, 0, 'ECShop', 'http://www.ecshop.com', '', 0);


--
-- �ɥX�����ƾ� `ecm_templates`
--

INSERT INTO `ecm_templates` (`store_id`, `config`, `filename`, `pagename`, `hash_code`) VALUES (0, 'a:6:{s:7:"region1";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:2:{s:11:"page_header";a:2:{s:2:"id";s:11:"page_header";s:3:"src";s:37:"themes/mall/resource/page_header.html";}s:11:"search_form";a:2:{s:2:"id";s:11:"search_form";s:3:"src";s:37:"themes/mall/resource/search_form.html";}}}s:7:"region4";a:1:{s:8:"children";a:5:{s:14:"goods_category";a:3:{s:2:"id";s:14:"goods_category";s:3:"src";s:40:"themes/mall/resource/goods_category.html";s:6:"parent";s:7:"region4";}s:17:"recommended_store";a:3:{s:2:"id";s:17:"recommended_store";s:3:"src";s:43:"themes/mall/resource/recommended_store.html";s:6:"parent";s:7:"region4";}s:17:"recommended_brand";a:3:{s:2:"id";s:17:"recommended_brand";s:3:"src";s:43:"themes/mall/resource/recommended_brand.html";s:6:"parent";s:7:"region4";}s:11:"latest_sold";a:3:{s:2:"id";s:11:"latest_sold";s:3:"src";s:37:"themes/mall/resource/latest_sold.html";s:6:"parent";s:7:"region4";}s:7:"partner";a:3:{s:2:"id";s:7:"partner";s:3:"src";s:33:"themes/mall/resource/partner.html";s:6:"parent";s:7:"region4";}}}s:7:"region6";a:1:{s:8:"children";a:1:{s:10:"cycleimage";a:3:{s:2:"id";s:10:"cycleimage";s:3:"src";s:36:"themes/mall/resource/cycleimage.html";s:6:"parent";s:7:"region6";}}}s:7:"region7";a:1:{s:8:"children";a:1:{s:16:"latest_site_news";a:3:{s:2:"id";s:16:"latest_site_news";s:3:"src";s:42:"themes/mall/resource/latest_site_news.html";s:6:"parent";s:7:"region7";}}}s:7:"region8";a:1:{s:8:"children";a:4:{s:9:"group_buy";a:3:{s:2:"id";s:9:"group_buy";s:3:"src";s:35:"themes/mall/resource/group_buy.html";s:6:"parent";s:7:"region8";}s:4:"cm_2";a:6:{s:2:"id";s:4:"cm_2";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:11:"Lady\'s Wear";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_4";a:6:{s:2:"id";s:4:"cm_4";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:14:"Digital Camera";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}s:4:"cm_1";a:6:{s:2:"id";s:4:"cm_1";s:5:"mtype";s:2:"cm";s:4:"type";s:1:"0";s:4:"name";s:6:"Mobile";s:8:"store_id";s:1:"0";s:4:"conf";a:10:{s:2:"ic";i:4;s:2:"wc";i:4;s:2:"hc";i:8;s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}}}}s:7:"region3";a:2:{s:8:"denyEdit";i:1;s:8:"children";a:1:{s:11:"page_footer";a:2:{s:2:"id";s:11:"page_footer";s:3:"src";s:37:"themes/mall/resource/page_footer.html";}}}}', 'default.layout', 'homepage', '527e4d037cdf839f58c9720fb85ffc95');

--
-- �ɥX�����ƾ� `ecm_custom_modules`
--

INSERT INTO `ecm_custom_modules` (`id`, `name`, `type`, `config`) VALUES (1, '���', 0, 'a:10:{s:1:"c";i:24;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(2, '�k��', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:2;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}'),
(4, '�ƽX�۾�', 0, 'a:10:{s:2:"ic";s:1:"4";s:2:"wc";s:1:"4";s:2:"hc";s:2:"10";s:1:"c";i:28;s:4:"tbgc";s:0:"";s:3:"tfc";s:0:"";s:4:"cbgc";s:0:"";s:3:"cfc";s:0:"";s:4:"bbgc";s:0:"";s:3:"bfc";s:0:"";}');

--
-- �ɥX�����ƾ� `ecm_crontab`
--

INSERT INTO `ecm_crontab` (`task_name`, `plan_time`, `run_time`) VALUES
('auto_order_handle', 0, 0),
('auto_store_handle', 0, 0),
('auto_send_mail', 0, 0);
