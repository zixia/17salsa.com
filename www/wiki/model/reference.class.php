<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class referencemodel {

	var $db;
	var $base;

	function referencemodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function getall($did){
		$list = array();
		$query=$this->db->query("SELECT `id`,`name`,`url` FROM ".DB_TABLEPRE."docreference where did=$did ORDER BY id ASC");
		while($row=$this->db->fetch_array($query)){
			$list[]=$row;
		}
		return $list;
	}
	
	function add($data){
		if (isset($data['id'])) return $this->edit($data);
		$sql = "INSERT INTO  ".DB_TABLEPRE."docreference(did,name,url) VALUES('{$data['did']}','{$data['name']}','{$data['url']}')";
		if ($this->db->query($sql)) return $this->db->insert_id();
	}

	function edit($data){
		$sql = "UPDATE ".DB_TABLEPRE."docreference SET name='{$data['name']}',url='{$data['url']}' WHERE id={$data['id']}";
		return $this->db->query($sql);
	}
	
	function remove($id){
		$sql = "DELETE FROM ".DB_TABLEPRE."docreference WHERE id=$id";
		return $this->db->query($sql);
	}
}
?>