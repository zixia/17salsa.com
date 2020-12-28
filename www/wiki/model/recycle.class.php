<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class recyclemodel {

	var $db;
	var $base;

	function recyclemodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function get_recycle($id){
		return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."recycle WHERE id=$id");
	}
	
	function get_count(){
		return $this->db->result_first("SELECT COUNT(*) num FROM ".DB_TABLEPRE."recycle WHERE 1");
	}
	
	function get_list ($start,$limit){
		$recyclelist=array();
		$query=$this->db->query("SELECT id,type,keyword,adminid,admin,dateline FROM ".DB_TABLEPRE."recycle WHERE 1 ORDER BY dateline DESC LIMIT $start,$limit");
		while($recycle=$this->db->fetch_array($query)){
			$recycle['dateline']=$this->base->date($recycle['dateline']);
			$recyclelist[]=$recycle;
		}
		return $recyclelist;
	}
	
	function search_recycle($start,$limit,$keyword,$author,$starttime,$endtime,$type){
		$recyclelist=array();
		$sql="SELECT id,type,keyword,adminid,admin,dateline FROM ".DB_TABLEPRE."recycle where 1=1 ";
		
		if($keyword){
			$sql=$sql." AND keyword like '%$keyword%' ";
		}
		if($author){
			$sql=$sql." AND admin = '$author' ";
		}
		if($starttime){
			$sql=$sql." AND dateline>=$starttime ";
		}
		if($endtime){
			$sql=$sql." AND dateline<=$endtime ";
		}
		if($type){
			$sql=$sql." AND type='$type' ";
		}
		$sql=$sql." ORDER BY dateline DESC LIMIT $start,$limit ";
		$query=$this->db->query($sql);
		while($recycle=$this->db->fetch_array($query)){
			$recycle['dateline'] = $this->base->date($recycle['dateline']);
			$recyclelist[]=$recycle;
		}
		return $recyclelist;
	}
	
	function search_recycle_num($keyword,$author,$starttime,$endtime,$type){
		$sql="SELECT count(*) num FROM ".DB_TABLEPRE."recycle where 1=1 ";
		if($keyword){
			$sql=$sql." AND keyword like '%$keyword%' ";
		}
		if($author){
			$sql=$sql." AND admin = '$author' ";
		}
		if($starttime){
			$sql=$sql." AND dateline>=$starttime ";
		}
		if($endtime){
			$sql=$sql." AND dateline<=$endtime ";
		}
		if($type){
			$sql=$sql." AND type='$type' ";
		}
		return $this->db->result_first($sql);
	}
	
	function  remove($ids){
		$query=$this->db->query("SELECT file FROM ".DB_TABLEPRE."recycle WHERE id IN ($ids) ");
		while($recycle=$this->db->fetch_array($query)){
			if($recycle['file']!='N;'){
				$files=unserialize($recycle['file']);
				foreach($files as $file){
					@unlink($file);
				}
			}
		}
		$this->db->query("DELETE FROM ".DB_TABLEPRE."recycle where id IN ($ids)");
	}
	
	function clear($pernum=100){
		$query=$this->db->query("SELECT file FROM ".DB_TABLEPRE."recycle WHERE 1 LIMIT $pernum ");
		while($recycle=$this->db->fetch_array($query)){
			if($recycle['file']!='N;'){
				$files=unserialize($recycle['file']);
				foreach($files as $file){
					@unlink($file);
				} 
			}
		}
		$this->db->query("DELETE FROM ".DB_TABLEPRE."recycle where 1 LIMIT $pernum");
	}
	
	function recover($eids){
		set_time_limit(0);
		$models=array('doc'=>array('model'=>'doc','function'=>'recover'),
				  'edition'=>array('model'=>'doc','function'=>'recover_edition'),
					 'user'=>array('model'=>'user','function'=>'recover'),
			   'category'=>array('model'=>'category','function'=>'recover'),
			   'attachment'=>array('model'=>'attachment','function'=>'recover'),
				'comment'=>array('model'=>'comment','function'=>'recover'),
					 'gift'=>array('model'=>'gift','function'=>'recover'));
		$query=$this->db->query("SELECT id,type,content FROM ".DB_TABLEPRE."recycle WHERE id IN ($eids) ");
		while($recycle=$this->db->fetch_array($query)){
			$this->base->load($models[$recycle['type']]['model']);
			$_ENV[$models[$recycle['type']]['model']]->$models[$recycle['type']]['function'](unserialize($recycle['content']));
		}
		$this->db->query("DELETE FROM ".DB_TABLEPRE."recycle where id IN ($eids)");
	}
}
?>