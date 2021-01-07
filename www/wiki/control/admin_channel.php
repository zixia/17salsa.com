<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base($get, $post);
		$this->load('channel');
		$this->view->setlang($this->setting['lang_name'],'back');
	}
	
	function dodefault(){
		$channellist=$_ENV['channel']->get_all_list();
		$this->view->assign('channellist',$channellist);
		$this->view->display('admin_channel');
	}
	
	function doadd(){
		if(!isset($this->post['channelsubmit'])){
			$this->view->display('admin_addchannel');
		}else{
			$channel['name']=trim($this->post['name']);
			$channel['url']=trim($this->post['url']);
			$channel['available']=trim($this->post['available']);
			if(!empty($channel['name']) && !empty($channel['url'])){
				if(substr($channel['url'],0,7)!="http://"){
					$channel['url']="http://".$channel['url'];
				}
				$_ENV['channel']->add_channel($channel);
				$this->cache->removecache('channel');
				$this->message($this->view->lang['channel_AddSuccess'],'index.php?admin_channel');
			}else{
				$this->message($this->view->lang['pluginParErrorRewrite'],'index.php?admin_channel');
			}
		}
	}
	
	function doedit(){
		if(!isset($this->post['channelsubmit'])){
			$channel=$this->db->fetch_by_field('channel','id',$this->get[2]);
			$this->view->assign('channel',$channel);
			$this->view->display('admin_editchannel');
		}else{
			$channel['name']=trim($this->post['name']);
			$channel['url']=trim($this->post['url']);
			$channel['available']=trim($this->post['available']);
			$channel['id']=$this->post['id'];
			if(!empty($channel['name']) && !empty($channel['url']) && is_numeric($channel['id'])){
				if(substr($channel['url'],0,7)!="http://"){
					$channel['url']="http://".$channel['url'];
				}
				$_ENV['channel']->edit_channel($channel);
				$this->cache->removecache('channel');
				$this->message($this->view->lang['channel_EditSuccess'],'index.php?admin_channel');
			}else{
				$this->message($this->view->lang['pluginParErrorRewrite'],'index.php?admin_channel');
			}
		}
	}
	
	function doremove(){
		if(count($this->post['channel_id'])!=0){
			$_ENV['channel']->remove_channel($this->post['channel_id']);
			$this->cache->removecache('channel');
			$this->message($this->view->lang['channel_RemoveSuccess'],'index.php?admin_channel');
		}else{
			$this->message($this->view->lang['pluginParErrorRewrite'],'index.php?admin_channel');
		}
	}

	function doupdateorder(){
		$channel_num=string::stripspecialcharacter(trim($this->post['order']));
		$order=explode(",",$channel_num);
		$_ENV['channel']->updateorder($order);
		$this->cache->removecache('channel');
	}
	
}
?>