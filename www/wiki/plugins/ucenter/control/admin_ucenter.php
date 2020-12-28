<?php

!defined('IN_HDWIKI') && exit('Access Denied');
 
class control extends base{

	function control(& $get,& $post){
		$this->base($get, $post);
		$this->load('plugin');
		$this->load('user');
		$this->load('setting');
		$this->loadplugin('ucenter');
		$this->view->setlang('zh','back');
	}
	
	function dodefault() {
		$plugin=$_ENV['plugin']->get_plugin_by_identifier('ucenter');
		$pluginid=$plugin['pluginid'];

		$url="./plugins/ucenter/ucconfig.inc.php";
		if(!file_exists($url)){
			$iscon=1;
		}else{
			include($url);
			if(!(UC_OPEN && isset($this->plugin["ucenter"]["available"])))
				$isopen=1;
			$url=HDWIKI_ROOT."/data/import_uc.lock";
			if(file_exists($url))
				$islock=1;
		}
		
		$feed=unserialize($this->setting[feed]);
		$this->view->assign('create',@in_array('create',$feed));
		$this->view->assign('edit',@in_array('edit',$feed));
		
		$this->view->assign('iscon',$iscon);
		$this->view->assign('isopen',$isopen);
		$this->view->assign('islock',$islock);
		$this->view->assign('pluginid',$pluginid);
		$this->view->display('file://plugins/ucenter/view/admin_ucenter');
	}
	
	function doimport() {
 		set_time_limit(0);
 		$url=HDWIKI_ROOT."/plugins/ucenter/ucconfig.inc.php";
 		if(!file_exists($url)){
 			$this->message('您还没有设置过UCenter!','BACK');
 		}else{
	 		include($url);
			if(!(UC_OPEN && isset($this->plugin["ucenter"]["available"]))){
				$this->message('您还没有开通ucenter！','BACK');
			}
			$url=HDWIKI_ROOT."/data/import_uc.lock";
			if(file_exists($url)){
				$this->message('您已经导入过用户，想重新导入请删除'.$url.'文件。','BACK');
			}
		}
		#包含uc相应文件。
		require_once(HDWIKI_ROOT."/plugins/ucenter/uc_client/client.php");
		require_once(HDWIKI_ROOT."/plugins/ucenter/uc_client/lib/db.class.php");
		#
		#实例uc数据类
		$ucdb = new db();
		$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
		#
		$ucuserlist='';
		$usercount=$_ENV['user']->get_total_num('',0);
		for($i=0;$i<$usercount;$i=$i+100){
			$userlist=$_ENV['user']->get_list('',0,$i,100);
			foreach($userlist as $temUser){
				if($data = uc_get_user($temUser['username'])) {
					$ucuserlist .="username:".$temUser['username']."	user_email:".$temUser['email']."\r\n";
					
				}else{
					$salt = substr(uniqid(rand()), -6);
					$password = md5($temUser['password'].$salt);
					$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."members SET username='".$temUser['username']."', password='$password', email='".$temUser['email']."', regip='".$temUser['regip']."', regdate='".time()."', salt='".$salt."'");
					$uid = $ucdb->insert_id();
					$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."memberfields SET uid='$uid'");
				}
			}
		}
		
		$mes="import complate!";
		if(empty($ucuserlist)){
			$ucuserlist=$mes;
		}
		file :: writetofile(HDWIKI_ROOT."/data/hdwiki_user_uc.txt",$ucuserlist);
		file :: writetofile(HDWIKI_ROOT."/data/import_uc.lock",$mes);
		$plugin=$_ENV['plugin']->get_plugin_by_identifier('ucenter');
		$pluginid=$plugin['pluginid'];
		$this->message('导入数据成功!','index.php?admin_plugin-setvar-'.$pluginid);
		exit();
	}
	function dofeed(){
		if(isset($this->post['submit'])){
			$feed['feed']=serialize($this->post['feed']);
			
			$setting=$_ENV['setting']->update_setting($feed);
			$this->cache->removecache('setting');
			
			$plugin=$_ENV['plugin']->get_plugin_by_identifier('ucenter');
			$pluginid=$plugin['pluginid'];
			$this->message('保存数据成功!','index.php?admin_plugin-manage-'.$pluginid);
		}
		$this->view->display('file://plugins/ucenter/view/admin_ucenter');
	}

}
?>