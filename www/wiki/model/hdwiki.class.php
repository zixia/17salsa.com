<?php

!defined('IN_HDWIKI') && exit('Access Denied');

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require HDWIKI_ROOT.'/config.php';
require HDWIKI_ROOT.'/lib/string.class.php';
require HDWIKI_ROOT.'/model/base.class.php';

class hdwiki {

	var $get = array();
	var $post = array();
	var $querystring;
	
	function hdwiki() {
		$this->init_request();
		$this->load_control();
	}
	
	function init_request(){
		global $encoding;
		if (!file_exists(HDWIKI_ROOT.'/data/install.lock')) {
			header('location:install/install.php');
			exit();
		}
		$querystring=$_SERVER['QUERY_STRING'];
		$pos = strpos($querystring , '.');
		if($pos!==false){
			$querystring=substr($querystring,0,$pos);
		}
		$this->get = explode('-' , $querystring);
		
		if (count($this->get) <= 3 && count($_POST) == 0 && substr($querystring, 0, 6) == 'admin_' && substr($querystring, 0, 10) != 'admin_main'){
			$this->querystring = $querystring;
		}
		
		if(empty($this->get[0])){
		   $this->get[0]='index';
		}		
		if(empty($this->get[1])){
		   $this->get[1]='default';
		}
		if(count($this->get)<2){
			exit(' Access Denied !');
		}
		unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
		$encoding = WIKI_CHARSET;
	 	$this->get=string::haddslashes($this->get,1);
		$this->post=string::haddslashes($_POST);
		unset($_GET,$_POST);
	}
	
	function load_control(){
		if($this->get[0]=='plugin'){
			if(empty($this->get[2])){
				$this->get[2]=$this->get[1];
			}
			if(empty($this->get[3])){
				$this->get[3]='default';
			}
			$pluginfile=HDWIKI_ROOT.'/plugins/'.$this->get[1].'/control/'.$this->get[2].'.php';
			if(false===@include($pluginfile)){
				$this->notfound('plugin control "'.$this->get[2].'"  not found!');
			}
			$this->get=array_slice($this->get,2);
		}else{
			$controlfile=HDWIKI_ROOT.'/control/'.$this->get[0].'.php';
			if(false===@include($controlfile)){
				$this->notfound('control "'.$this->get[0].'"  not found!');
			}
		}
	}

	function run(){
		$control = new control($this->get,$this->post);
		if ($this->querystring){
			$control->hsetcookie('querystring',$this->querystring, 3600);
		}
		$method = $this->get[1];
		$exemption=true; //免检方法的标志，免检方法不需要经过权限检测
		if('hd'!= substr($method, 0, 2)){
			$exemption=false;
			$method = 'do'.$this->get[1];
		}
		if ($control->user['uid'] == 0	&& $control->setting['close_website'] === '1'	&& strpos('dologin,dologout,docheckusername,docheckcode,docode',$method) === false
		){
			@header('Content-type: text/html; charset='.WIKI_CHARSET);
			exit($control->setting['close_website_reason']);
		}
		
		if(method_exists($control, $method)) {
			$regular=$this->get[0].'-'.$this->get[1];
			$isadmin= ('admin'==substr($this->get[0],0,5));
			if($exemption || $control->checkable($regular)){
				$control->$method();
			}else{
				$control->message($regular.$control->view->lang['refuseAction'],'', $isadmin);
			}
		}else {
			$this->notfound('method "'.$method.'" not found!');
		}
	}
	
	function notfound($error){
		@header('HTTP/1.0 404 Not Found');
		exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
	}

}


?>