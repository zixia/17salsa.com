<?php
!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load("synonym");
		$this->load("doc");
	}
	
	function doremovesynonym(){
		$destdid=$this->post['destdid'];
		if(is_numeric($destdid)){
			$num=$_ENV['synonym']->removesynonym($destdid);
			$this->message($num,'',2);
		}else{
			$this->message(-1,'',2);
		}
	}
	
	function dosavesynonym(){
		$destdid=$this->post['destdid'];
		if(!is_numeric($destdid)){
			exit;
		}
		$synonyms=trim($this->post['srctitles']);
		$desttitle=trim($this->post['desttitle']);
		if (WIKI_CHARSET == 'GBK'){
			$synonyms=string::hiconv($synonyms);
			$desttitle=string::hiconv($desttitle);
		}
		
		if(empty($synonyms)){
			$_ENV['synonym']->removesynonym($destdid);
			exit("ok");
		}
		$srctitles=array_unique(explode(';',$synonyms));
		$returnsyn=stripslashes(implode(';',$srctitles));
		$filter=$_ENV["synonym"]->is_filter($srctitles,$desttitle);
		if($filter[0]<0){
			exit($filter[0]);
		}
		if(is_array($srctitles)&&!empty($desttitle)){
			$num=$_ENV['synonym']->savesynonym($destdid,$desttitle,$srctitles);
			exit("ok");
		}else{
			exit($filter[0]);
		}
	}
}

?>
