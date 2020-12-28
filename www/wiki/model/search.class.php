<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class searchmodel {

	var $db;
	var $base;

	function searchmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function get_total_num($sql){
		$query=$this->db->query($sql);
		$data=$this->db->fetch_array($query);
		return $data['num'];
	}
	
	function fulltext_search($sql,$start=0,$limit=10){
		$doclist=array();
		$query=$this->db->query($sql." limit $start,$limit");
		while($doc=$this->db->fetch_array($query)){
			$doc['time']=$this->base->date($doc['time']);
			$doc['tag']=$_ENV['doc']->spilttags($doc['tag']);
			$doc['rawtitle']=$doc['title'];
			$doc['title']=htmlspecialchars($doc['title']);
			$doclist[]=$doc;
		}
		return $doclist;
	}
	
	function join_sql($element){
		$keywords = $element['keyword'];
		$searchtitle=($_ENV['doc']->get_total_num() < $this->base->setting['search_num'])?0:1;
		if($searchtitle){
			$sqlkeywords .="d.title LIKE '$keywords%'";
			$element['author']="";
			$element['categoryid'][0]='all';
		}else{
			$sqlkeywords .="d.".$element['searchtype']." LIKE '%$keywords%'";
		}	
		
		$sqladd=(trim($sqlkeywords)!='')?' AND ('.$sqlkeywords.")":"";
		$sqladd .=(bool)$element['author']?" AND d.author='".$element['author']."' ":"";
		$sqladd .=('all'!=$element['categoryid'][0])?' AND c.cid in ('.implode(',',$element['categoryid']).') ':'';
		if(0!=$element['timelimited']){
			$sqladd .=('within'==$element['withinbefore'])?" AND d.`time` > '".($this->base->time-$element['timelimited'])."'" :" AND d.`time` < '".($this->base->time-$element['timelimited'])."'";
		}
		$order=" ORDER BY d.".$element['ordertype']." ".$element['ascdesc']."";
		$sqladdcat = ('all'!=$element['categoryid'][0])?"INNER JOIN ".DB_TABLEPRE."categorylink c ON d.did=c.did":'';
		$result['sql']='SELECT d.did,d.tag,d.title,d.author,d.authorid,d.time,d.summary,d.edits, d.views,d.comments FROM '.DB_TABLEPRE.'doc d '.$sqladdcat.' WHERE 1 '.$sqladd.$order;
		$result['dsql']='SELECT COUNT(*) as num FROM '.DB_TABLEPRE.'doc d '.$sqladdcat.' WHERE 1 '.$sqladd;
		return $result;
	}
}


?>