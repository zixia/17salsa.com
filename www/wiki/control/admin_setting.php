<?php
!defined('IN_HDWIKI') && exit('Access Denied');
class control extends base{

	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('setting');
		$this->view->setlang($this->setting['lang_name'],'back');
	}
	/*base setting*/
	function dobase(){
		if(!isset($this->post['settingsubmit'])){
			$this->view->assign('timeoffset',$this->view->lang['timeoffset']);
			
			if(!is_numeric($this->setting['img_width_big'])) $this->setting['img_width_big']=300;
			if(!is_numeric($this->setting['img_height_big'])) $this->setting['img_height_big']=300;
			if(!is_numeric($this->setting['img_width_small'])) $this->setting['img_width_small']=140;
			if(!is_numeric($this->setting['img_height_small'])) $this->setting['img_height_small']=140;
			
			$this->view->assign('basecfginfo',$this->setting);
			$this->view->display("admin_base");
		}else{
			$settings = $this->post['setting'];
			if(!is_numeric($settings['time_diff'])){
				$this->message($this->view->lang['baseConfigMustNum'], 'BACK');
			}
			if(!$settings['cookie_pre']){
				$settings['cookie_pre']='hd_';
			}
			foreach($settings as $key =>$value){
				$settings[$key] = trim($value);
			}
			if('/'==substr($settings['site_url'],-1)){
				$settings['site_url']=substr($settings['site_url'],0,-1);
			}
			$setting=$_ENV['setting']->update_setting($settings);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['baseConfigSuccess'],'index.php?admin_setting-base');
		}
	}
	
	function dobaseregister(){
		if(!isset($this->post['settingsubmit'])){
			$this->view->assign('basecfginfo',$this->setting);
			$this->view->display("admin_baseregister");
		}else{
			$settings = $this->post['setting'];
			foreach($settings as $key =>$value){
				$settings[$key] = trim($value);
			}
			$error_names = $settings['error_names'];
			$error_names = str_replace(",", "\n", $error_names);
			$error_names = str_replace('"', '', $error_names);
			$error_names = explode("\n", $error_names);
			foreach($error_names as $key =>$value){
				$error_names[$key] = trim($value);
				if ('' === $error_names[$key]){unset($error_names[$key]);}
			}
			$settings['error_names'] = implode("\n", $error_names);
			$settings=$_ENV['setting']->update_setting($settings);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['baseConfigSuccess'],'index.php?admin_setting-baseregister');
		}
	}
	/*security*/
	function dosec(){
		if(!isset($this->post['secsubmit'])){
			$this->view->assign('basecfginfo',$this->setting);
			$this->view->display("admin_sec");
		}else{
			$settings['doc_verification_edit_code']=isset($this->post['doc_verification_edit_code'])?$this->post['doc_verification_edit_code']:0;
			$settings['doc_verification_create_code']=isset($this->post['doc_verification_create_code'])?$this->post['doc_verification_create_code']:0;
			$settings['forbidden_edit_time']=trim($this->post['forbidden_edit_time']);
			if(!is_numeric($settings['forbidden_edit_time'])||($settings['forbidden_edit_time']>999 || $settings['forbidden_edit_time'] < 0)){
				$this->message($this->view->lang['secForbidEditTimeWarning'],'BACK');
			}
			$settings=$_ENV['setting']->update_setting($settings);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['baseConfigSuccess'],'index.php?admin_setting-sec');
		}
	}
	
	/*upload logo*/
	function dologo(){
		if(!isset($this->post['logsubmit'])){
			$this->view->display("admin_uploadlogo");
		}else{
			if(isset($_FILES['logo'])){
				$imgname=$_FILES['logo']['name'];
				$filetype = array('image/jpeg','image/gif','image/x-png','image/png','image/pjpeg');
				if(in_array($_FILES['logo']['type'],$filetype)){
					$destfile='style/default/logo.gif';
					$arrupload=file::uploadfile($_FILES['logo'],$destfile,1024);
				}else{
					$this->message($this->view->lang['uploadFormatWrong'],'BACK');
				}
			}
			if(isset($arrupload) && $arrupload['result'] == true){
				$this->message($arrupload['msg'],'index.php?admin_setting-logo');
			}else{
				$this->message($this->view->lang['uploadFail'],'BACK');
			}
		}
	}

	/*set credit*/
	function docredit(){
		if(!isset($this->post['creditsubmit'])){
			$this->view->assign('creditconfig',$this->setting);
			$this->view->display("admin_credit");
		}else{
			$creditlist=array(
				'credit_register'=>20,
				'credit_login'=>1,
				'credit_create'=>5,
				'credit_edit'=>3,
				'credit_upload'=>2,
				'credit_pms'=>1,
				'credit_comment'=>2,
				
				'coin_register'=>20,
				'coin_login'=>1,
				'coin_create'=>5,
				'coin_edit'=>3,
				'coin_upload'=>2,
				'coin_pms'=>1,
				'coin_comment'=>2,
				
				'credit_exchangeRate'=>10,
				'coin_exchangeRate'=>1,
				'credit_exchange'=>0,
				'coin_exchange'=>0
			);
			foreach($creditlist as $creditkey => $creditvalue){
				$setting[$creditkey]=(is_numeric($this->post[$creditkey]))?intval($this->post[$creditkey]):$creditvalue;
			}
			$setting['credit_unit']=trim($this->post['credit_unit']);
			$setting['coin_unit']=trim($this->post['coin_unit']);
			$setting['coin_name']=trim($this->post['coin_name']);
			
			$_ENV['setting']->update_setting($setting);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['bonusSuccess'],'index.php?admin_setting-credit');
		}
	}
	
	function dolistdisplay(){
		if(!isset($this->post['submit'])){
			$this->view->assign('listdisplay',$this->setting);
			$this->view->display("admin_listdisplay");
		}else{
			$listdisplay = $this->post[listplay];
			foreach($listdisplay as $key=>$display){
				$setting[$key]=(intval($display)>0)?intval($display):10;
			}
			$_ENV['setting']->update_setting($setting);
			$this->cache->removecache('setting');
			$this->cache->removecache('indexcache');
			$this->message($this->view->lang['listDisplaySucess'],'index.php?admin_setting-listdisplay');
		}
	}
	
	function doattachment(){
		if(!isset($this->post['attachmentsubmit'])){
 			$this->view->display("admin_attachment");
 		}else{
 			if(!is_numeric($this->post['attachment_size'])){$this->message($this->view->lang['attachmentSizeTrip'],'index.php?admin_setting-attachment');}
 			if($this->post['attachment_type']==""){$this->post['attachment_type']="jpg|jpeg|bmp|gif|zip|rar|doc|ppt|mp3|xls|txt|swf|flv";}
 			unset($this->post['attachmentsubmit']);
 			$_ENV['setting']->update_setting($this->post);
 			$this->cache->removecache('setting');
 			$this->message($this->view->lang['attachmentConfigSucess'],'index.php?admin_setting-attachment');
 		}
	}

	function domail(){
		if(!isset($this->post['mailsubmit'])){
			if(isset($this->setting['mail_config'])){
				$mail_config=unserialize($this->setting['mail_config']);
				$this->view->assign('mailconfig',$mail_config);
			}
 			$this->view->display("admin_mail");
 		}else{
 			$set["mail_host"]=$this->post["mail_host"];
 			$set["mail_port"]=$this->post["mail_port"];
 			$set["mail_mail"]=$this->post["mail_mail"];
 			$set["mail_user"]=$this->post["mail_user"];
 			$set["mail_pass"]=$this->post["mail_pass"];
 			$mail['mail_config']=serialize($set);
 			$setting=$_ENV['setting']->update_setting($mail);
 			$this->cache->removecache('setting');
 			$this->message($this->view->lang['emaliSuc'],'BACK');
 		}
	}

	/*set seo*/
	function doseo(){
		$servertype=strtoupper(substr($_SERVER['SERVER_SOFTWARE'],0,6));
		if(!isset($this->post['seosubmit'])){
			$this->view->assign('servertype',$servertype);
			$this->view->assign('seoconfig',$this->setting);
			$this->view->display("admin_seo");
		}else{
			if(empty($this->post['seo_prefix'])){$this->post['seo_prefix']='index.php?';}
			$seotype['seo_type']=$this->post['seo_type'];
			if($seotype['seo_type']==1){
			/*	if($servertype!='APACHE'){
					$seotype['seo_type']=0;
					$seotype['seo_prefix']=$this->post['seo_prefix'];
					$_ENV['setting']->update_setting($seotype);
					$this->cache->removecache('setting');
					$this->message('Sorry!The server is not apache!','index.php?admin_setting-seo');
				}*/
				$base_root=substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],"index.php"));
				
				if('1'==$this->post['seo_type_doc']){
				$data="
<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteBase $base_root
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^wiki/(.*)$ index.php?doc-innerlink-$1
</IfModule>";
				}else{
				 $data="
<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteBase $base_root
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^.*$ index.php?\$0
</IfModule>";
				}
				
				$bytes=file::writetofile(HDWIKI_ROOT."/.htaccess",$data);
				if($bytes==0){
					$seotype['seo_type']=0;
					$seotype['seo_prefix']=$this->post['seo_prefix'];
					$_ENV['setting']->update_setting($seotype);
					$this->cache->removecache('setting');
					$this->message($this->view->lang['seoConfigFileWriteError'],'BACK');
				}
				$this->post['seo_prefix']=('1'==$this->post['seo_type_doc'])?"index.php?":"";
			}else if(file_exists(HDWIKI_ROOT."/.htaccess")){
				unlink (HDWIKI_ROOT."/.htaccess");
			}
			$setting=$_ENV['setting']->update_setting($this->post);
			$this->cache->removecache('setting');
			file::cleardir(HDWIKI_ROOT.'/data/view');
 			$this->message($this->view->lang['seoConfigSuccess'],'index.php?admin_setting-seo');
		}
	}

	function docache(){
		$arr_cachelist=array(array("variable"=>"index_cache_time","value"=>$this->setting['index_cache_time'],"describ"=>$this->view->lang['delIndeCache'],"cache_name"=>$this->view->lang['indeCache']),
		array("variable"=>"list_cache_time","value"=>$this->setting['list_cache_time'],"describ"=>$this->view->lang['delListCache'],"cache_name"=>$this->view->lang['listCache'])
		);
		$this->view->assign("cachelist",$arr_cachelist);
		$this->view->display("admin_cache");
	}
	
	function doupdatecache(){
		$cachelist = array('index_cache_time','list_cache_time');
		foreach($cachelist as $cache){
			if(!is_numeric($this->post[$cache."_value"])){
				$this->message($this->view->lang['cacheTimeIsNum'],'BACK');
			}
		}
		$_ENV['setting']->update_cache($cachelist,$this->post);
		$this->cache->removecache('setting');
		$this->message($this->view->lang['cacheConfigSuccess'],'index.php?admin_setting-cache');
	}

	function doremovecache(){
		file::cleardir(HDWIKI_ROOT.'/data/cache');
		file::cleardir(HDWIKI_ROOT.'/data/view');
		$this->message($this->view->lang['cacheDelSuccess'],'index.php?admin_setting-cache');
	}
	
	function donotice(){
		if(isset($this->post['notice'])){
			$notice=$this->post['notice'];
			if(empty($notice)){
				$this->load('user');
				$this->load('doc');
				$usernum=$_ENV['user']->get_total_num('',0);
				$docnum=$this->db->fetch_total('doc','1');
				$notice=$this->view->lang['resetnotice'].' '.number_format($usernum).' '.$this->view->lang['resetnotice0'].' '.number_format($docnum).' '.$this->view->lang['resetnotice2'];
			}
			$setting['site_notice']=$notice;
			$_ENV['setting']->update_setting($setting);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_setting-notice');
		}
		$this->view->display("admin_notice");
	}
	
	function dopassport(){
		$ppfile=HDWIKI_ROOT.'/data/passport.inc.php';
		if(isset($this->post['passport'])){
			$passportdata="<?php
define('PP_OPEN', '".$this->post['ppopen']."');
define('PP_TYPE', '".$this->post['pptype']."');
define('PP_API', '".$this->post['ppapi']."');
define('PP_NAME', '".$this->post['ppname']."');
define('PP_LOGIN', '".$this->post['pplogin']."');
define('PP_LOGOUT', '".$this->post['pplogout']."');
define('PP_REG', '".$this->post['ppreg']."');
define('PP_KEY', '".$this->post['ppkey']."');
?>";
			$byte=file::writetofile($ppfile,$passportdata);
			if($byte==0){
				$this->message($this->view->lang['passportnotwrite'],'BACK');
			}else{
				$this->message($this->view->lang['passportsucess'],'index.php?admin_setting-passport');
			}
		}else{
			if(file_exists($ppfile)){
				include($ppfile);
				if(defined('PP_API')){
					$this->view->assign('pp_open',PP_OPEN);
					$this->view->assign('pp_type',PP_TYPE);
					$this->view->assign('pp_api',PP_API);
					$this->view->assign('pp_name',PP_NAME);
					$this->view->assign('pp_login',PP_LOGIN);
					$this->view->assign('pp_logout',PP_LOGOUT);
					$this->view->assign('pp_reg',PP_REG);
					$this->view->assign('pp_key',PP_KEY);
				}
			}
			$this->view->display("admin_passport");
		}
	}
	
	function dowatermark(){
		if(!isset($this->post['settingsubmit'])){
			if(isset($this->setting['watermark'])){
				$settingsnew=unserialize($this->setting['watermark']);
				$settingsnew['imageimpath']=urldecode($settingsnew['imageimpath']);
				$this->view->assign('settingsnew',$settingsnew);
			}
			$ttf=file::get_file_by_ext("./style/default",array('ttf','ttc','fon'));
			$this->view->assign('ttf',$ttf);
 			$this->view->display("admin_watermark");
 		}else{
 			$settingsnew=$this->post['settingsnew'];
 			$settingsnew['imageimpath']=urlencode(stripslashes($settingsnew['imageimpath']));
			if($settingsnew['watermarktype'] == 2) {
				$settingsnew['watermarktext']['size'] = intval($this->post['settingsnew']['watermarktext']['size']);
				$settingsnew['watermarktext']['angle'] = intval($this->post['settingsnew']['watermarktext']['angle']);
				$settingsnew['watermarktext']['shadowx'] = intval($this->post['settingsnew']['watermarktext']['shadowx']);
				$settingsnew['watermarktext']['shadowy'] = intval($this->post['settingsnew']['watermarktext']['shadowy']);
				$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $this->post['settingsnew']['watermarktext']['fontpath']);
				if($settingsnew['watermarktype'] == 2 && $settingsnew['watermarktext']['fontpath']) {
					$fontpath = $settingsnew['watermarktext']['fontpath'];
					$settingsnew['watermarktext']['fontpath'] = file_exists('./style/default/'.$fontpath) ? $fontpath : (file_exists('./style/default/'.$fontpath) ? $fontpath : '');
					if(!$settingsnew['watermarktext']['fontpath']){
						$this->message($this->view->lang['watermark_fontpath_error'],'index.php?admin_setting-watermark');
					}
				}
			}else{
				unset($settingsnew['watermarktext']);
			}
 			$set['watermark']=serialize($settingsnew);
 			$setting=$_ENV['setting']->update_setting($set);
 			$this->cache->removecache('setting');
 			$this->message($this->view->lang['watermarkSuc'],'BACK');
 		}
	}
	
	function dopreview(){
		$settingsnew=$this->post['settingsnew'];
		if($settingsnew['watermarktype'] == 2) {//文字水印
			$settingsnew['watermarktext']['size'] = intval($this->post['settingsnew']['watermarktext']['size']);
			$settingsnew['watermarktext']['angle'] = intval($this->post['settingsnew']['watermarktext']['angle']);
			$settingsnew['watermarktext']['shadowx'] = intval($this->post['settingsnew']['watermarktext']['shadowx']);
			$settingsnew['watermarktext']['shadowy'] = intval($this->post['settingsnew']['watermarktext']['shadowy']);
			$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $this->post['settingsnew']['watermarktext']['fontpath']);
			if($settingsnew['watermarktype'] == 2 && $settingsnew['watermarktext']['fontpath']) {
				$fontpath = $settingsnew['watermarktext']['fontpath'];
				$settingsnew['watermarktext']['fontpath'] = file_exists('./style/default/'.$fontpath) ? $fontpath : (file_exists('./style/default/'.$fontpath) ? $fontpath : '');
				if(!$settingsnew['watermarktext']['fontpath']){
					$this->message($this->view->lang['watermark_fontpath_error'],'index.php?admin_setting-watermark');
				}
			}
		}else{
			unset($settingsnew['watermarktext']);
		}
		if($settingsnew[watermarkstatus]=='0'){
			$this->message($this->view->lang['noWaterMark'],'');
		}
		$this->load("watermark");
		$pic="./style/default/watermark/preview.jpg";
		$previewpic="./style/default/watermark/preview_tem.jpg";
		@unlink($previewpic);
		if(!is_file($pic)){
			$this->message('预览需要的图片不存在。','');
		}
		if($_ENV['watermark']->image($pic,$previewpic,$settingsnew)){
			echo '<img src="'.$previewpic.'" />';
		}else{
			$this->message('图片加水印失败，请检查你设置的参数。','');
		}
	}
	
	function dorandomstr(){
		if(!isset($this->post['settingsubmit'])){
			if(isset($this->setting['randomstr'])){
				$settingsnew=unserialize(base64_decode($this->setting['randomstr']));
				if(is_array($settingsnew[random_text])){
					$settingsnew['random_text']=implode("\n",$settingsnew['random_text']);
				}
				$this->view->assign('settingsnew',$settingsnew);
			}
 			$this->view->display("admin_randomstr");
 		}else{
 			$settingsnew=$this->post['setting'];
 			$randomstr_array=explode("\n",$settingsnew['random_text']);
 			$settingsnew[random_text]=array();
 			foreach($randomstr_array as $value){
 				$settingsnew[random_text][]=trim($value,"\n\r ");
 			}
 			$set['randomstr']=base64_encode(serialize($settingsnew));
 			$setting=$_ENV['setting']->update_setting($set);
 			$this->cache->removecache('setting');
 			$this->message($this->view->lang['randomstr_notice'],'BACK');
 		}
	}
}
?>