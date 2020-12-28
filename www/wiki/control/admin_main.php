<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('user');
		$this->load('db');
		$this->view->setlang($this->setting['lang_name'],'back');
	}

	function dodefault(){
		$this->dologin();
	}

	function doupdate(){
		include_once HDWIKI_ROOT.'/lib/xmlparser.class.php';
		$sendversion = base64_encode( serialize( array('v'=>HDWIKI_VERSION,'r'=>HDWIKI_RELEASE,'c'=>WIKI_CHARSET,'u'=>$this->setting['site_url']) ) );
		$xmlfile="http://kaiyuan.hudong.com/update.php?v={$sendversion}";
		$xmlparser = new XMLParser();
		$xmlnav=$xmlparser->parse($xmlfile);
		$isupdate = $xmlnav[0]['child'][0]['content'];
		$version = $xmlnav[0]['child'][1]['content'];
		$release = $xmlnav[0]['child'][2]['content'];
		$url = $xmlnav[0]['child'][3]['content'];
		$description = $xmlnav[0]['child'][4]['content'];
		$json = '{"isupdate":"'.$isupdate.'","version":"'.$version.'","release":"'.$release.'","url":"'.$url.'","description":"'.$description.'"}';
		echo $json;
	}

 	function dologin(){
		$admin_mainframe = $this->hgetcookie('querystring') ? $this->hgetcookie('querystring'):'admin_main-mainframe';
		$this->view->assign('admin_mainframe', $admin_mainframe);
		$islogin=$_ENV['user']->is_login();
 		if(2==$islogin){
 			$this->view->display("admin_main");
 			exit;
 		}
 		if($islogin){
			if(!isset($this->post['password'])){
				$this->view->display("admin_login");
			}else{
				if( $this->user['password'] != md5($this->post['password']) ){
					$this->view->assign('loginmsg',$this->view->lang['commonPasswdIsWrong']);
					$this->view->display("admin_login");
					exit;
				}else{
					$session['islogin']=2;
					$_ENV['user']->update_session($session,$this->user['sid']);
					$this->view->assign('env',$this->env());
					$this->view->display("admin_main");
					exit;
				}
			}
		}else{
			$this->header('user-login');
		}
	}

 	function dologout(){
 		$session['islogin']=1;
		$_ENV['user']->update_session($session,$this->user['sid']);
		$this->header();
	}

	function domainframe(){
		$sys['server'] = PHP_OS.' / '.$_SERVER['SERVER_SOFTWARE'];
		if (strpos($sys['server'],'PHP') === false){
			$sys['server'] .= ' / PHP v'.PHP_VERSION;
		}
		$mysql=$this->db->fetch_first('SELECT VERSION() AS version');
		$sys['mysql']=$mysql['version'];
		
		$sizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if(file_exists(HDWIKI_ROOT.'/data/attachment')){
			$attsize = file::getdirsize(HDWIKI_ROOT.'/data/attachment');
			$attachsize = sprintf("%u", $attsize);
			if($attachsize == 0) {
				$attachsize = "0 Bytes";
			}else{
		 		$attachsize =round($attachsize/pow(1024, ($i = floor(log($attachsize, 1024)))), 2) . $sizename[$i];
			}
		}
		if(file_exists(HDWIKI_ROOT.'/data/attachment')){
			$uploadsize = file::getdirsize(HDWIKI_ROOT.'/uploads');
			$uploadssize = sprintf("%u", $uploadsize);
			if($uploadssize == 0) {
				$uploadssize = "0 Bytes";
			}else{
		 		$uploadssize =round($uploadssize/pow(1024, ($i = floor(log($uploadssize, 1024)))), 2) . $sizename[$i];
			}
		}
		$dbsize = $_ENV['db']->databasesize();
		if($dbsize == 0) {
			$dbsize = "0 Bytes";
		}else{
	 		$dbsize =round($dbsize/pow(1024, ($i = floor(log($dbsize, 1024)))), 2) . $sizename[$i];
		}
		
		$this->view->assign('sys', $sys);
		$this->view->assign('attsize', $attachsize);
		$this->view->assign('uploadsize', $uploadssize);
		$this->view->assign('dbsize', $dbsize);
		$this->view->display("admin_mainframe");
	}
	
	function env(){
		$adminmainenv = $this->cache->getcache('adminmainenv');
		if ($adminmainenv == date('W')) return '';
		$this->load('doc');
		
		$url = $this->setting['app_url'].'/count2/'.'en'.'v.'.'php'.'?'.'q';
		$mysql=$this->db->fetch_first('SELECT VERSION() AS version');
		$maxdid=$_ENV['doc']->get_maxid();
		$info = array();
		$info[0] = PHP_OS;
		$info[1] = $_SERVER['SERVER_SOFTWARE'];
		$info[2] = PHP_VERSION;
		$info[3] = $mysql['version'];
		$info[4] = function_exists('phpinfo')? '1':'0';
		if (function_exists('extension_loaded')){
			$info[5] = extension_loaded('gd')? '1':'0';
			$info[6] = extension_loaded('iconv')? '1':'0';
			$info[7] = extension_loaded('xml')? '1':'0';
			$info[8] = extension_loaded('json')? '1':'0';
			$info[9] = extension_loaded('zlib')? '1':'0';
		}else{
			$info[5] = function_exists('imagecreatetruecolor')? '1':'0';
			$info[6] = function_exists('iconv')? '1':'0';
			$info[7] = function_exists('xml_parse')? '1':'0';
			$info[8] = function_exists('json_encode')? '1':'0';
			$info[9] = function_exists('gzopen')? '1':'0';
		}
		
		$info[10] = $maxdid;
		
		$info = implode(';',$info);
		$this->cache->writecache('adminmainenv',date('W'));
		return $url.'='.chr(rand(65,90)).rawurlencode(base64_encode($info));
	}

}
?>