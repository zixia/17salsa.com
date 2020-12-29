<?php
!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base($get, $post);
		$this->view->setlang($this->setting['lang_name'],'back');
		$this->load("style");
	}
	
	function dodefault(){
		$toaddlist=$_ENV['style']->get_style_list(1);
		$pathlist=$_ENV['style']->get_path_list();
		$addlist=array_diff($toaddlist,$pathlist);
		$defaultstyle=$_ENV['style']->choose_style_name($this->setting['style_name']);
		
		$stylearray=$_ENV['style']->get_all_list_num();
		$count=count($stylearray);
		$num = 10;
		$page = max(1, intval(end($this->get)));
		$start_limit = ($page - 1) * $num;
		$stylelist=$_ENV['style']->get_all_list($start_limit,$num);
		$departstr=$this->multi($count, $num, $page,'admin_style-default');
		$this->view->assign('departstr',$departstr);
		
		$this->view->assign('stylelist',$stylelist);
		$this->view->assign('toaddlist',$addlist);
		$this->view->assign('appurl',$this->setting['app_url']);
		$this->view->assign('defaultstyle',$defaultstyle);
		$this->view->display('admin_style');
	}
	
	function doeditxml(){
		if(!isset($this->post['stylesave']) && !isset($this->post['styleshare'])){
			$xmlcon=$_ENV['style']->read_xml($this->get[2]);
			$this->view->assign('stylename',$this->get[2]);
			$this->view->assign('style',$xmlcon);
			$this->view->assign('share',$this->get[3]);
			$this->view->display('admin_stylexml_edit');
		}else{
			if($_FILES['styleimg']['name']!=''){
				$image = $_FILES['styleimg'];
				$extname=file::extname($image['name']);
				if($extname=='jpg'){
					$destfile='style/'.$this->get[2].'/screenshot.'.$extname;
					$result = file::uploadfile($image,$destfile);
					if($result['result']){
						util::image_compress($destfile,NULL,158,118);
					}
				}else{
					$this->message($this->view->lang['uploadFormatWrong'],'BACK');
				}
			}
			//insert into db
			$style = $this->post['style'];
			$style['hdversion']=HDWIKI_VERSION;
			$style['path']=trim($this->get[2]);
			$style['charset']=$_ENV['style']->style_charset($style[path]);
			$stylecon=$_ENV['style']->add_check_style($style['path']);
			if($stylecon==null){
				$_ENV['style']->add_style($style);
			}else{
				$_ENV['style']->update_style($style);
			}
			//write to xml
			$_ENV['style']->write_xml($style);
			if(isset($this->post['stylesubmit'])){
				$this->message($this->view->lang['docEditSuccess'],'index.php?admin_style');
			}else if(isset($this->post['stylesave'])){
				$this->message($this->view->lang['docEditSuccess'],'index.php?admin_style-editxml-'.$style[path].'-share');
			}else if(isset($this->post['styleshare'])){
				//check
				$filename='style/'.$style['path'].'/share.lock';
				if(is_file($filename)){
					$this->message($this->view->lang['style_share_lock'].$filename,'BACK');
				}else{
					file::writetofile( $filename , $a='' );
				}
				//zip
				require HDWIKI_ROOT."/lib/zip.class.php";
				$zip = new Zip;
				$filedir=array('style/'.$style['path'],'view/'.$style['path']);
				$zipdir=array('hdwiki/style/'.$style['path'],'hdwiki/view/'.$style['path']);
				file::forcemkdir(HDWIKI_ROOT.'/data/tmp/');
				$tmpname=HDWIKI_ROOT.'/data/tmp/'.util::random(6).'.zip';
				@$zip->zip_dir($filedir,$tmpname,$zipdir);
				//share
				if(is_file($tmpname)){
					$zip_content=file::readfromfile($tmpname);
					$upload_url=$this->setting['app_url']."/hdapp.php?action=upload&type=template";
					$data='data='.base64_encode($zip_content);
					unlink($tmpname);
					if('1'==@util::hfopen($upload_url,0,$data)){
						$this->message($this->view->lang['styleShareSuccess'],'index.php?admin_style');
					}else{
						$this->message($this->view->lang['styleShareFaile'],'index.php?admin_style');
					}
				}else{
					$this->message($this->view->lang['styleFileZipFail'],'index.php?admin_style');
				}
			}
		}
	}

	function doadd(){
		$style['path']=trim($this->get[2]);
		$style=$_ENV['style']->read_xml($style['path']);
		if($style['path']=='' || $style['name']=='' || $style['copyright']==''){
			$this->message($this->view->lang['stylexmlwrong'],'index.php?admin_style');
		}
		$stylecon=$_ENV['style']->add_check_style($path);
		if($stylecon==null){
			$_ENV['style']->add_style($style);
			$this->cache->removecache('style');
			$this->message($this->view->lang['styleaddsuccess'],'index.php?admin_style');
		}else{
			$this->message($this->view->lang['stylepathexist'],'index.php?admin_style');
		}
	}
	
	function docreate(){
		if($this->setting['style_name']){
			$defaultstyle=$_ENV['style']->choose_style_name($this->setting['style_name']);
		}
		$this->view->assign('appurl',$this->setting['app_url']);
		$this->view->assign('defaultstyle',$defaultstyle);
		$this->view->display('admin_addstyle');
	}
	
	function docreatestyle(){
		$style = $this->post['style'];
		$stylelist=$_ENV['style']->get_style_list(0);
		if(in_array($style[path],$stylelist)){
			$this->message($this->view->lang['styledocpathexist'],BACK);
			exit;
		}else{
			@file::copydir(HDWIKI_ROOT.'/style/'.$this->setting['style_name'],HDWIKI_ROOT.'/style/'.$style[path]);
			if($this->setting['style_name']!='default'){
				@file::copydir(HDWIKI_ROOT.'/view/'.$this->setting['style_name'],HDWIKI_ROOT.'/view/'.$style[path]);
			}
			//insert into db
			$style['hdversion'] = HDWIKI_VERSION;
			$style['charset']=$_ENV['style']->style_charset($style[path]);
			$stylecon=$_ENV['style']->add_check_style($style['path']);
			if($stylecon==null){
				$_ENV['style']->add_style($style);
			}else{
				$_ENV['style']->update_style($style);
			}
			//write to xml
			$_ENV['style']->write_xml($style);
			$this->cache->removecache('style');
			$this->header("admin_style-edit-$style[path]");
		}
	}
	
	function doedit(){
		if(!isset($this->post['imgupload'])){
			$filedir=HDWIKI_ROOT.'/style/'.$this->get[2].'/';
			file::forcemkdir($filedir);
			$handle=opendir($filedir);
			while($filename=readdir($handle)){
				if (!is_dir($filedir.$filename) && '.'!=$filename && '..'!=$filename && substr($filename,0,6)!='admin_' && substr($filename,-4)=='.css' || substr($filename,-4)=='.xml'){
					$style_key=str_replace('.','',substr($filename,-4));
					$stylelist[substr($filename,0,-4)]=$style_key;
					$stylelang[substr($filename,0,-4)]=$this->view->lang['style'.substr($filename,0,-4)];
				}
				if(is_file($filedir.'screenshot.jpg')){
					$styleimg=1;
				}
			}
			closedir($handle);
			$stylecss=$_ENV['style']->add_check_style($this->get[2]);
			$style=unserialize($stylecss['css']);
			$this->view->assign('style_path',$this->get[2]);
			$this->view->assign('style',$style);
			$this->view->assign('styleimg',$styleimg);
			$this->view->display('admin_style_edit');
		}else{
			$imgform=array('jpg','jpeg','png','gif');
			if($_FILES['bg_file']['name']!=''){
				$background_img = explode('.',$this->post['background_img']);
				$_FILES['bg_file']['rename']=$background_img[0];
				$bg_extname=file::extname($_FILES['bg_file']['name']);
				if(!in_array($bg_extname,$imgform)){
					$this->message($this->view->lang['stylebgimgwrong'],BACK);
				}
				$uploadimg[]=$_FILES['bg_file'];
			}
			if($_FILES['title_file']['name']!=''){
				$titlebg_img = explode('.',$this->post['titlebg_img']);
				$_FILES['title_file']['rename']=$titlebg_img[0];
				$title_extname=file::extname($_FILES['title_file']['name']);
				if(!in_array($title_extname,$imgform)){
					$this->message($this->view->lang['styledetitimgwrong'],BACK);
				}
				$uploadimg[]=$_FILES['title_file'];
			}
			if($_FILES['screenshot_file']['name']!=''){
				$_FILES['screenshot_file']['rename']='screenshot';
				$screenshot_extname=file::extname($_FILES['screenshot_file']['name']);
				$imgform=array('jpg');
				if(!in_array($screenshot_extname,$imgform)){
					$this->message($this->view->lang['styledescreenwrong'],BACK);
				}
				$uploadimg[]=$_FILES['screenshot_file'];
			}
			$_ENV['style']->upload_img($uploadimg,$this->get[2]);
			
			$stylelist = array('bg_color' => '#fff',
								'titlebg_color' => '',
								'title_framcolor' => '#ccc',
								'nav_framcolor' => '#ccc',
								'input_bgcolor' => '#7f9db9',
								'input_color' => '#666',
								'link_color' => '#0268cd',
								'link_hovercolor' => '#f30',
								'link_difcolor' => '#ff3300'
								);
			foreach($stylelist as $key => $value){
				$style[$key] = $this->post['style'][$key]?$this->post['style'][$key]:$value;
			}
			$style['bg_imgname'] = $background_img[0]?$background_img[0].'.'.$bg_extname:'';
			$style['titbg_imgname'] = $titlebg_img[0]?$titlebg_img[0].'.'.$title_extname:'col_h2_bg.gif';	
			$style['path']=$this->get[2];
			$_ENV['style']->write_css($style);
			$stylecss=serialize($style);
			$_ENV['style']->update_stylecss($stylecss,$style['path']);
			$this->cache->removecache('style');
			$this->message($this->view->lang['styleEditSuccess'],'index.php?admin_style-edit-'.$this->get[2]);
		}
	}
	
	function doadvancededit(){
		$stylelang=array();
		$filedir=HDWIKI_ROOT.'/view/'.$this->get[2].'/';
		file::forcemkdir($filedir);
		$handle=opendir($filedir);
		while($filename=readdir($handle)){
			if (!is_dir($filedir.$filename) && '.'!=$filename && '..'!=$filename && substr($filename,0,6)!="admin_" && substr($filename,-4)==".htm"){
				$view_key=str_replace(".","",substr($filename,-4));
				$viewlist[substr($filename,0,-4)]=$view_key;
				$stylelang[substr($filename,0,-4)]=$this->view->lang['style'.substr($filename,0,-4)];
			}
		}
		closedir($handle);
		$filedir=HDWIKI_ROOT.'/style/'.$this->get[2].'/';
		file::forcemkdir($filedir);
		$handle=opendir($filedir);
		while($filename=readdir($handle)){
			if (!is_dir($filedir.$filename) && '.'!=$filename && '..'!=$filename && substr($filename,0,6)!='admin_' && substr($filename,-4)=='.css' || substr($filename,-4)=='.xml'){
				$style_key=str_replace('.','',substr($filename,-4));
				$stylelist[substr($filename,0,-4)]=$style_key;
				$stylelang[substr($filename,0,-4)]=$this->view->lang['style'.substr($filename,0,-4)];
			}
			if(is_file($filedir.'screenshot.jpg')){
				$styleimg=1;
			}
		}
		closedir($handle);
		if($this->get[3]!=''){
			$select_style=$this->get[3];
			$select_con=$_ENV['style']->choose_style_name($this->get[3]);
		}else{
			$select_style=$this->get[2];
			$select_con=$_ENV['style']->choose_style_name($this->get[2]);
		}
		$defaultstyle=$_ENV['style']->choose_style_name($this->setting['style_name']);
		$this->cache->removecache('style');
		$this->view->assign('viewlist',$viewlist);
		$this->view->assign('stylelist',$stylelist);
		$this->view->assign('select_con',$select_con);
		$this->view->assign('style_path',$this->get[2]);
		$this->view->assign('defaultstyle',$defaultstyle);
		$this->view->assign('stylelang',$stylelang);
		$this->view->assign('styleimg',$styleimg);
		$this->view->assign('editfilename','hdwiki.css');
		$this->view->display('admin_style_advancededit');
	}
	
	function doreadfile(){
		$filename=str_replace('*','.',$this->post['id']);
		$type=explode('.',$filename);
		if($type[1]=='htm'){
			$filedir=HDWIKI_ROOT.'/view/'.$this->post['style_path'].'/'.$filename;
		}else{
			$filedir=HDWIKI_ROOT.'/style/'.$this->post['style_path'].'/'.$filename;
		}
		if( file_exists($filedir) ){
			$data=file::readfromfile($filedir);
		}
		$title=string::hiconv($data,'utf-8','gbk');
		echo $title;
	}
	
	function dosavefile(){
		$type=explode('.',$this->post['filename']);
		if($type[1]=="htm"){
			$filedir=HDWIKI_ROOT.'/view/'.$this->get[2].'/';
		}else{
			$filedir=HDWIKI_ROOT.'/style/'.$this->get[2].'/';
		}
		file::forcemkdir($filedir);
		$filename=$this->post['filename'].'.bak';
		if (!copy($filedir.$this->post['filename'],$filedir.$filename)) {
		    echo $this->view->lang['styleFileBackFail'];
		}else{
			$this->post['html_con']=string::hiconv($this->post['html_con']);
			file::writetofile($filedir.$this->post['filename'],stripcslashes($this->post['html_con']));
			$this->message($this->view->lang['styleEditSuccess'],'index.php?admin_style-advancededit-'.$this->get[2]);
		}
	}
	
	function doremovestyle(){
		$style_path=$this->setting['style_name'];
		if($style_path!=$this->get[2]){
			$_ENV['style']->remove_style($this->get[2]);
		}
		$this->cache->removecache('style');
		$this->message($this->view->lang['styleDelSucess'],'index.php?admin_style');
	}

	function dosetdefaultstyle(){
		$defaultpath=trim($this->get[2]);
		$stylefilepath = HDWIKI_ROOT.'/style/'.$defaultpath;
		if(is_dir($stylefilepath)){
			$_ENV['style']->default_style($defaultpath);
			$this->cache->removecache('setting');
		}else{
			$this->message($this->view->lang['styleNotExist'],'BACK');
		}
		$this->header("admin_style");
	}
	
	function dolist(){
		$num = 10;
		$page = max(1, intval($this->get[3]));
		$orderby = $this->get[2];
		if(!$orderby)$orderby='time';
		$wiki_list_url=$this->setting['app_url']."/hdapp.php?action=download&type=template&charset=".WIKI_CHARSET."&page=".$page."&orderby=".$orderby;
		$style_content=@util::hfopen($wiki_list_url);
		$style_content=unserialize(base64_decode($style_content));
		if(empty($style_content)){
			$this->message($this->view->lang['styleEmpty'],'index.php?admin_style');
		}
		$count = $style_content['count'];
		$departstr=$this->multi($count, $num, $page,'admin_style-list-'.$orderby);
		$stylelist=$style_content['data'];
		if(is_array($stylelist)){
			foreach($stylelist as $key=>$style){
				$stylelist[$key]['image']=$this->setting['app_url'].$style['image'];
				$stylelist[$key]['install_list']=$this->setting['app_url']."/template.php?action=stat&id=".$style['appid'];
				$stylelist[$key]['tag']=explode(' ',$style['tag']);
			}
		}
		$this->view->assign('tag_url',$this->setting['app_url']."/template.php?action=search&tag=");
		$this->view->assign("departstr",$departstr);
		$this->view->assign('stylelist',$stylelist);
		$this->view->assign('orderby',$orderby);
		$this->view->display('admin_stylelist');
	}
	
	function doinstall(){
		if(isset($this->get[2])&&is_numeric($this->get[2])){
			$style_download_url=$this->setting['app_url']."/hdapp.php?action=download&type=template&install=1&id=".$this->get[2]."&url=".$this->setting['site_url'];
			$zipcontent=@util::hfopen($style_download_url);
			$tmpdir=HDWIKI_ROOT.'/data/tmp/';
			file::forcemkdir($tmpdir);
			$tmpname=$tmpdir.util::random(6).'.zip';
			file::writetofile($tmpname,$zipcontent);
			require HDWIKI_ROOT."/lib/zip.class.php";
			require HDWIKI_ROOT."/lib/xmlparser.class.php";
			$zip=new zip();
			if(!$zip->chk_zip){
				$this->message($this->view->lang['styleInstallNoZlib'],'');
			}
			$ziplist=@$zip->get_List($tmpname);
			if(!(bool)$ziplist){
			  @unlink($tmpname);
			  $this->message($this->view->lang['styleZipFail'],'BACK');
			}
			$style_name=$_ENV['style']->get_style_name($ziplist);
			@$zip->Extract($tmpname,$tmpdir);
			@unlink($tmpname);
			//move file
			$syle_path=$tmpdir.'hdwiki';
			if(is_dir(HDWIKI_ROOT.'/style/'.$style_name)){
				@file::removedir($syle_path);
				$this->message($this->view->lang['stylePathRepeat'],'BACK');
			}
			@file::copydir($syle_path,HDWIKI_ROOT);
			@file::removedir($syle_path);
			//save db
			$style_xml=HDWIKI_ROOT.'/style/'.$style_name.'/desc.xml';
			if(!is_file($style_xml)){
				$this->message($this->view->lang['styleXmlNotExist'],'BACK');
			}
			$xmlnav=$_ENV['style']->read_xml($style_name);
			$style['name']=$xmlnav['name'];
			$style['copyright']=$xmlnav['copyright'];
			$style['path']=$style_name;
			$stylecon=$_ENV['style']->add_check_style($style['path']);
			if($stylecon==null){
				$_ENV['style']->add_style($style);
				$this->cache->removecache('style');
				$this->message($this->view->lang['styleInstallSuccess'],'BACK');
			}else{
				$this->message($this->view->lang['styleDbPathRepeat'],'index.php?admin_style');
			}
		}else{
			$this->message($this->view->lang['commonParametersInvalidTip'],'index.php?admin_style');
		}
	}
	
}
?>