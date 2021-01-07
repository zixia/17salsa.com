<?php

!defined('IN_HDWIKI') && exit('Access Denied');
 
class control extends base{

	function control(& $get,& $post){
		$this->base(& $get,& $post);
		$this->load('search');
		$this->load('doc');
		$this->load("synonym");
		$this->load('category');
	}
	
	function dodefault() {
		$title=trim($this->post['searchtext']);
		if($title==''){
			$this->header();
			exit;
		}
		
		if($synonym=$_ENV['synonym']->get_synonym_by_src($title)){
			header('Location:index.php?doc-innerlink-'.urlencode($synonym['srctitle']));
			exit;
		}
		$doc=$this->db->fetch_by_field('doc','title',$title);
		$this->view->assign("searchtext",$title);
		$this->view->assign("searchword",urlencode(string::hiconv($title,'utf-8')));
		
		if(!(bool)$doc){
			$sqladd=$this->db->fetch_total('doc','1')<$this->setting['search_num']?"%$title%":"$title%";
			$sql="select d.did,d.tag,d.title,d.author,d.authorid,d.time,d.summary from ".DB_TABLEPRE."doc d where d.title LIKE '$sqladd' order by d.time desc";
			$list=$_ENV['search']->fulltext_search($sql,0,8);
			foreach($list as $key => $value){
				$list[$key]['title'] = preg_replace("/($title)/i", "<span style='color:red'>\$1</span>", $value['title']);
			}
			$title=htmlspecialchars(stripslashes(trim($this->post['searchtext'])));
			$this->view->assign("list",$list);
			$this->view->assign("title",$title);
			$this->view->assign("searchtext", $title);
			$this->view->assign("search_tip_switch", $this->setting['search_tip_switch']);
			$this->view->display("notexist");
		}else{
			$this->header("doc-view-".$doc['did']);
		}
	}
	
	function dofulltext(){
		if(!$this->post['full'] && !$this->get[10]){
			$all_category=$_ENV['category']->get_category_cache();
			$categorytree=$_ENV['category']->get_categrory_tree($all_category);
			if(strtoupper(substr($this->post['searchtext'],0,4))=='TAG:'){
				$keyword=substr($this->post['searchtext'],4);
				$this->view->assign("seachtype","tag");
			}else{
				$keyword=$this->post['searchtext'];
			}
			$this->view->assign("keyword",$keyword);
			$this->view->assign("categorytree",$categorytree);
			$this->view->display("search");
		}else{
			$page=isset($this->get[11])?$this->get[11]:'';
			if(empty($page) || !is_numeric($page)){
				$page=1;
				$search_time=isset($this->setting['search_time'])?$this->setting['search_time']:30;
				if(''!=$this->hgetcookie('searchtime') && $search_time > $this->time-$this->hgetcookie('searchtime'))
					$this->message($this->view->lang['search_time_error1'].$search_time.$this->view->lang['search_time_error2'],"BACK",0);
				else
					$this->hsetcookie('searchtime',$this->time,24*3600*365);
			}
			$element['searchtype']=isset($this->post['seachtype'])?$this->post['seachtype']:$this->get[2];
			$element['keyword']=isset($this->post['searchtext'])?$this->post['searchtext']:string::haddslashes(urldecode($this->get[3]),1);
			$element['keyword']= string::hiconv(trim($element['keyword']));
			//$element['keyword']=string::haddslashes($element['keyword'],1);
			$author=isset($this->post['author'])?$this->post['author']:string::haddslashes(urldecode($this->get[4]),1);
			$element['author']=$author?str_replace('*','%',$author):'';
			$element['categoryid']=isset($this->post['categoryid'])?$this->post['categoryid']:explode(",",$this->get[5]);
			$element['timelimited']=isset($this->post['timelimited'])?$this->post['timelimited']:$this->get[6];
			$element['withinbefore']=isset($this->post['withinbefore'])?$this->post['withinbefore']:$this->get[7];
			$element['ordertype']=isset($this->post['ordertype'])?$this->post['ordertype']:$this->get[8];
			$element['ascdesc']=isset($this->post['ascdesc'])?$this->post['ascdesc']:$this->get[9];
			if(!(bool)$element['keyword']){
				$this->message($this->view->lang['searchKeywordNull'],"BACK",0);
			}elseif(strtoupper(substr($element['keyword'],0,4))=='TAG:'&&strlen($element['keyword'])>4){
				$element['keyword']=substr($element['keyword'],4);
				$element['searchtype']='tag';
			}
			if($element['searchtype']!="title"&&$element['searchtype']!="tag"&&$element['searchtype']!="content"){
				$element['searchtype']="title";
			}
			if($element['categoryid']!="all"&&!preg_match("/^\d[\d\,]*?$/i",implode(",",$element['categoryid']))){
				$element['categoryid'][0]="all";
			}
			if(!is_numeric($element['timelimited'])){
				$element['timelimited']=0;
			}
			if($element['withinbefore']!="within" && $element['timelimited']!="before"){
				$element['timelimited']="within";
			}
			if($element['ordertype']!="time"&& $element['ordertype']!="comments"&&$element['ordertype']!="views"){
				$element['ordertype']="time";
			}
			if($element['ascdesc']!="asc"&&$element['ascdesc']!="desc"){
				$element['ascdesc']="desc";
			}
			$result=$_ENV['search']->join_sql($element);
			$count=$_ENV['search']->get_total_num($result['dsql']);
			$count=$count<=500?$count:500;
			$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
			$start_limit = ($page - 1) * $num;
			$list=$_ENV['search']->fulltext_search($result['sql'],$start_limit,$num);
			$keyword_for_view=str_replace("|","\|",$element['keyword']);
			foreach($list as $key => $value){
				$list[$key]['title'] = preg_replace("|({$keyword_for_view})|i", "<span style='color:red'>\$1</span>", $value['title']);
			}			
			$url="search-fulltext-$element[searchtype]-".urlencode($element[keyword])."-".urlencode($element[author])."-".implode(',',$element[categoryid])."-$element[timelimited]-$element[withinbefore]-$element[ordertype]-$element[ascdesc]-1";
			$departstr=$this->multi($count, $num, $page,$url);
			
			$allcategory=$_ENV['category']->get_category_cache(); 
			$categorylist=$_ENV['category']->get_site_category(0,$allcategory);
			$searchtext=stripslashes($element['searchtype']=="tag"?"TAG:".stripslashes($element['keyword']):stripslashes($element['keyword']));
 			$this->view->assign('categorylist',$categorylist);
			$this->view->assign("searchtext",htmlspecialchars($searchtext));
			$this->view->assign("list",$list);
			$this->view->assign("count",$count);
			$this->view->assign('navtitle',$this->view->lang['search'].'-'.stripslashes(stripslashes($element['keyword'])));
			$this->view->assign("departstr",$departstr);
			$this->view->display("searchresult");
		}
	}
	
	function dotag() {
		@$keyword=trim($this->get[2]);
		$this->header("search-fulltext-tag-$keyword--all-0-within-time-desc-1");
	}	
}
?>