<?php
!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{
	
	function control(& $get,& $post){
		$this->base($get, $post);
		$this->load('pic');
		$this->load('comment');
		$this->view->assign('isimage',true);
	}

	function dopiclist(){
		$num=20;
		$type=isset($this->get[2])?$this->get[2]:1;
		$page = max(1, intval($this->get[3]));
		$start_limit = ($page - 1) * $num;
		$pic_cache=$_ENV['pic']->get_pic_cache($type);
		$count=count($pic_cache);
		$list=array_slice($pic_cache,$start_limit,$num);
		$departstr=$this->multi($count, $num, $page,'pic-piclist-'.$type);
		
		$strarray=array(1=>'精选图片',2=>'最新图片',3=>'点击排行');
		$this->view->assign('type',$type);
		$this->view->assign("piclist",$list);
		$this->view->assign('navtitle',$strarray[$type].'-');
		$this->view->assign("departstr",$departstr);
		$this->view->display('piclist');
	}

	function doview(){
		$id=$this->get[2];
		$did=$this->get[3];
		if(!is_numeric($id)||!is_numeric($did)){
			$this->message($this->view->lang['parameterError'],'BACK',0);
		}
		$pic=$_ENV['pic']->get_pic_by_id($id);
		$piclist=$_ENV['pic']->get_pic_by_did($did);
		$comments=$_ENV['comment']->get_comments($did,$start=0,$limit=5);
		foreach($comments as $key=>$comment){
			$comments[$key]['comment']=(string::hstrlen($comment['comment'])>60)?string::substring($comment['comment'],0,60)."...":$comment['comment'];
		}
		
		$countnum=count($piclist);
		foreach($piclist as $key=>$val){
			if($val['id']==$id){
				$all_key=$key;
				break;
			}
		}
		if($countnum<=12){
			$returnlist=&$piclist;
			$li_key=$all_key;
		}else{
			$i=12-($countnum-$all_key);
			$li_key=$i>0?$i:0;
			$returnlist=array_slice($piclist,$all_key-$li_key,12);
		}
		
		$this->view->assign('did',$did);
		$this->view->assign('max_num',$countnum);
		$this->view->assign('all_key',$all_key);
		$this->view->assign('li_key',$li_key);
		
		$this->view->assign('comments',$comments);
		$this->view->assign('pic',$pic);
		$this->view->assign("piclist",$returnlist);
		$this->view->assign('navtitle',$pic['title'].'图片-');
		$this->view->display('viewpic');
	}
	
	function doajax(){
		if(isset($this->post['id'])){
			$id=$this->post['id'];
			if(!is_numeric($id)){
				$this->message($this->view->lang['parameterError'],'',2);
			}
			$pic=$_ENV['pic']->get_pic_by_id($id);
		}elseif(isset($this->post['did'])){
			$piclist=$_ENV['pic']->get_pic_by_did($this->post['did']);
			$countnum=count($piclist);
			if($countnum<=12){
				$returnlist=&$piclist;
			}else{
				$returnlist=array_slice($piclist,$this->post['start_key'],12);
			}
			$pic=$_ENV['pic']->get_pic_by_id($returnlist[$this->post['li_key']]['id']);
			$this->view->assign("piclist",$returnlist);
		}
		if(isset($pic)||isset($returnlist)){
			$this->view->assign("pic",$pic);
			$this->view->display('pic_ajax');
		}
	}
	
	function dosearch(){
		$num = 16;
		$page=isset($this->get[3])?$this->get[3]:'';
		if(empty($page) || !is_numeric($page)){
			$page=1;
			//下面的search_time代码只有在此时执行。
			$search_time=isset($this->setting['search_time'])?$this->setting['search_time']:30;
			if(''!=$this->hgetcookie('searchtime') && $search_time > $this->time-$this->hgetcookie('searchtime'))
				$this->message($this->view->lang['search_time_error1'].$search_time.$this->view->lang['search_time_error2'],"BACK",0);
			else
				$this->hsetcookie('searchtime',$this->time,24*3600*365);
		}
		
		$searchtext=isset($this->post['searchtext'])?$this->post['searchtext']:string::haddslashes(urldecode($this->get[2]),1);
		$searchtext= string::hiconv(trim($searchtext));
		if(empty($searchtext)){
			$this->message($this->view->lang['pic_no_title'],'BACK',0);
		}
		
		$start_limit = ($page - 1) * $num;
		$count=$_ENV['pic']->search_pic_num($searchtext);
		$piclist=$_ENV['pic']->search_pic($searchtext,$start_limit,$num);
		
		if(empty($piclist)){
			$this->message('没有搜到相关的图片，请返回重新搜索。','BACK',0);
		}
		
		$departstr=$this->multi($count, $num, $page,"pic-search-".urlencode($searchtext));
		$this->view->assign("leftpic",array_shift($piclist));
		$this->view->assign("piclist",$piclist);
		$this->view->assign('departstr',$departstr);
		$this->view->assign('count',$count);
		$this->view->assign('navtitle',$searchtext.' 图片搜索-');
		$this->view->assign('searchtext',$searchtext);
		$this->view->display('searchpic');
	}


}
?>
