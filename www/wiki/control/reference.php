<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base($get, $post);
		$this->load("reference");
	}

	function doadd(){
		$data=$this->post['data'];		
		if (WIKI_CHARSET == 'GBK'){
			$data['name']=string::hiconv($data['name']);
		}
		
		if (empty($data['name'])){
			exit('0');
		}
		
		$insert_id = $_ENV['reference']->add($data);
		if (is_int($insert_id)){
			echo $insert_id;
		}else{
			echo $insert_id? '1':'0';
		}
	}
	
	function doremove(){
		$id = $this->get[2];
		if(@is_numeric($id)){
			echo $_ENV['reference']->remove($id)?'1':'0';
		}else{
			echo '0';
		}
	}
}
?>