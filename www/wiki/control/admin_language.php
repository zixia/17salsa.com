<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->view->setlang($this->setting['lang_name'],'back');
		$this->load("language");
	}
	
	function dodefault(){
		$languagelist=$_ENV['language']->get_all_list();
		$this->view->assign("languagelist",$languagelist);
		$this->view->assign("lang_name",$this->setting['lang_name']);
		$this->view->display('admin_lang');
	}
	
	function doaddlanguage(){
		$addlanguage['addlangname']=trim($this->post['addlangname']);
		$addlanguage['addlangpath']=trim($this->post['addlangpath']);
		$addlanguage['addlangcopyright']=trim($this->post['addlangcopyright']);
		if($addlanguage['addlangname']==''||$addlanguage['addlangpath']==''||$addlanguage['addlangcopyright']==''){
			$this->message($this->view->lang['langConNull'],'index.php?admin_language');
		}
		$langname=$_ENV['language']->add_check_language($addlanguage);
		if($langname){
			$this->message($this->view->lang['langFileExist'],'index.php?admin_language');			
		}else{
			$_ENV['language']->add_language($addlanguage);
		}
		$this->cache->removecache('language');
		header("Location:index.php?admin_language");
	}
	
	function doremovelanguage(){
		$removelanguageid = isset($this->post['lang_id'])?$this->post['lang_id']:array();
		$this->load('setting');
		$lang_name=$this->setting['lang_name'];
		if(is_array($removelanguageid)){
			foreach($removelanguageid as $languageid){
				$lang=$this->db->fetch_by_field('language','id',$languageid);
				if($lang_name!=$lang['path']){
					$_ENV['language']->remove_language($languageid);
				}
			}
			$this->cache->removecache('language');
		}
		header("Location:index.php?admin_language");
	}
	
	function doupdatelanguage(){
		$languageids = isset($this->post["all_lang_id"])?$this->post["all_lang_id"]:array();
		if(is_array($languageids)){
			foreach($languageids as $id){
				$name = $this->post["lang_name_".$id];
				$path = $this->post["lang_path_".$id];
				$state = isset($this->post["lang_state_".$id])?1:0;
				$_ENV['language']->update_language($name,$path,$state,$id);
			}
			$this->cache->removecache('language');
		}
		header("Location:index.php?admin_language");
	}
	
	function dosetdefaultlanguage(){
		$langpath = "lang_path_".$this->post['lang_id'][0];
		$langfilepath = HDWIKI_ROOT.'/lang/'.$this->post[$langpath];
		if(is_dir($langfilepath)){
			$_ENV['language']->default_language($this->post[$langpath]);
			$this->cache->removecache('setting');
		}else{
			$this->message($this->view->lang['langFileNone'],'index.php?admin_language');
		}
		header("Location:index.php?admin_language");
	}
}
?>
