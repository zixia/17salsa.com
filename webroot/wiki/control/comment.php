<?php
!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{
	function control(& $get,& $post){
		$this->base($get, $post);
		$this->load('user');
		$this->load("doc");
		$this->load("pms");
		$this->load("comment");
	}
	function doview(){
		if(!is_numeric($this->get[2])){
			$this->message($this->view->lang['parameterError'],'index.php',0);
		}
		$doc=$this->db->fetch_by_field('doc','did',$this->get[2]);
		if(!(bool)$doc){
			$this->message($this->view->lang['docNotExist'],'index.php',0);
		}elseif($doc['visible']=='0'&&!$this->checkable('admin_doc-audit')){
			$this->message($this->view->lang['viewDocTip4'],'index.php',0);
		}elseif(@$this->get[3]=="locked"&&$doc['locked']==1){
			$this->view->assign('locked','1');
		}
		$doc['rawtitle']=urlencode($doc['title']);
		$page = max(1, intval($this->get[3]));
		$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
		$start_limit = ($page - 1) * $num;
		
		$comments=$_ENV['comment']->get_comments($this->get[2],$start_limit,$num);
		$comnum=count($comments);
		if($comnum<$num){
			$comments = array_pad($comments,$num,'');
			if($comnum!=$doc['comments'] && $page==1){
				$doc['comments']=$comnum;
				$_ENV['doc']->update_field('comments',$comnum,$this->get[2]);
			}
		}
		$departstr=$this->multi($doc['comments'], $num, $page,"comment-view-".$this->get[2]);
		
		$hotcomment=$_ENV['comment']->hot_comment_cache();
		$navigation= $_ENV['doc']->get_cids_by_did($doc['did']);
		if($navigation){
			$cids=array();
			foreach($navigation as $value){
				$cids[]=$value['cid'];
			}
			$categorydocs=$_ENV['doc']->get_docs_by_cid($cids,0,20);
		}
		$this->view->assign('doc',$doc);
		$page=$doc['comments']<=$num?0:$page;
		$this->view->assign('page',$page);
		$this->view->assign('num',$num);
		
		$this->view->assign('audit_edit',$this->checkable('comment-edit'));
		$this->view->assign('audit_delete',$this->checkable('comment-delete'));
		$this->view->assign('audit_add',$this->checkable('comment-add'));
		
		$this->view->assign("navigation",$navigation);
		$this->view->assign("departstr",$departstr);
		$this->view->assign("anonymity",$this->setting['comments']);
		$this->view->assign('comments',$comments);
		
		$this->view->assign('hotcomment',$hotcomment);
		$this->view->assign('categorydocs',$categorydocs);
		
		$this->view->display('viewcomment');
	}
	
	function doremove(){
		$id=$this->post['id'];
		$did=$this->post['did'];
		$page=$this->post['page'];
		
		if($_ENV["comment"]->remove_comment_by_id($id))
			$_ENV['doc']->update_field('comments',-1,$did,0);
		if($page<=0){
			$this->message(1,'',2);
		}
		$doc=$this->db->fetch_by_field('doc','did',$did);
		$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
		$start_limit = ($page - 1) * $num;
		$comments=$_ENV['comment']->get_comments($did,$start_limit,$num);
		$comments = array_pad($comments,$num,'');
		$departstr=$this->multi($doc['comments'], $num, $page,"comment-view-$did");
		
		$this->view->assign("type",1);
		$this->view->assign("page",$page);
		$this->view->assign("commentEdit",$this->checkable('comment-edit'));
		$this->view->assign("comments",$comments);
		$this->view->assign("departstr",$departstr);
		$this->view->display('comment_ajax');
	}
	
	function doadd(){
		$did=@$this->get[2];
		$type=isset($this->post['submit'])?0:2;
		$message=$type==2?'0;':'';
		$comment=trim($this->post['comment']);
		if(empty($did) || empty($comment)){
			$this->message($message.'参数错误','',$type);
		}
		if($this->setting['checkcode']!=3){
			$msg=$_ENV['user']->checkcode('',1);
			if($msg!='OK'){
				$this->message($message.'验证码不正确','',$type);
			}
		}
		$c_class=$this->post['c_class'];
		$re_id=$this->post['re_id'];
		$anonymity=$this->post['anonymity'];
		
		if (WIKI_CHARSET == 'GBK'){$comment=string::hiconv($comment);}
		$comment=string::stripscript($_ENV['doc']->replace_danger_word($comment));
		$comment=nl2br(htmlspecialchars($comment));
		if(empty($comment)){
			$this->message(0,'',2);
		}elseif(strlen($comment)>200){
			$comment=string::substring($comment,0,200);
		}
		$reply=$re_id?$_ENV['comment']->get_re_comment_by_id($re_id):'';
		$id=$_ENV['comment']->add_comment($did,$comment,addslashes($reply),$anonymity);
		
		if($id){
			$_ENV['user']->add_credit($this->user['uid'],'user-comment',$this->setting['credit_comment']);
			$_ENV['doc']->update_field('comments',1,$did,0);
			if($type==0){
				$this->header('comment-view-'.$did);
			}
			if($c_class){
				$page=1;
				$doc=$this->db->fetch_by_field('doc','did',$did);
				$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
				$start_limit = ($page - 1) * $num;
				
				$comments=$_ENV['comment']->get_comments($did,$start_limit,$num);
				$comments = array_pad($comments,$num,'');
				$departstr=$this->multi($doc['comments'], $num, $page,"comment-view-$did");
				
				$this->view->assign("type",2);
				$this->view->assign("page",$page);
				$this->view->assign("departstr",$departstr);
			}else{
				$comments=$this->db->fetch_by_field('comment','id',$id);
				$comments['author']=$_ENV['comment']->ip_show($comments['author']);
				$this->view->assign("type",3);
				$this->view->assign("comment",stripslashes($comment));
				$this->view->assign('reply', $reply);
				$this->view->assign('time', $this->date($this->time));
				$this->view->assign('id', $id);
			}
			$this->view->assign("commentEdit",$this->checkable('comment-edit'));
			$this->view->assign("comments",$comments);
			$this->view->display('comment_ajax');
		}else{
			$this->message($message.'插入数据库出错了','',$type);
		}
	}
	
	function doedit(){
		$id=$this->post['id'];
		if(empty($id)){
			$this->message($this->view->lang['commentError'],'',2);
		}
		$comment=trim($this->post['comment']);
		if (WIKI_CHARSET == 'GBK'){$comment=string::hiconv($comment);}

		$comment=string::stripscript($_ENV['doc']->replace_danger_word($comment));
		$_ENV["comment"]->edit_comment_by_id($id,$comment);
		$this->message(1,'',2);
	}
	
	function doaegis(){
		$id=$this->post['id'];
		if(empty($id)){
			$this->message(-1,'',2);
		}
		if($_ENV["comment"]->is_in_cookie('aegis',$id)){
			$this->message(-2,'',2);
		}
		$_ENV["comment"]->update_field('aegis',1,$id,0);
		$this->message($id,'',2);
	}
	
	function dooppose(){
		$id=$this->post['id'];
		if(empty($id)){
			$this->message(-1,'',2);
		}
		if($_ENV["comment"]->is_in_cookie('oppose',$id)){
			$this->message(-2,'',2);
		}
		$_ENV["comment"]->update_field('oppose',1,$id,0);
		$this->message($id,'',2);
	}
	
	function doreport(){
		$usernames=array();
		$id=$this->post['id'];
		$report=trim(htmlspecialchars(WIKI_CHARSET==GBK?string::hiconv($this->post['report']):$this->post['report']));
		if(empty($id)||empty($report)){
			$this->message(-1,'',2);
		}
		$users=$_ENV["user"]->get_users('groupid',4);
		if(!(bool)$users){
			$this->message(-2,'',2);
		}else{
			foreach($users as $user){
				$usernames[]=$user['username'];
			}
		}
		$sendto=join(',',$usernames);
		$subject=$this->view->lang['commentReportObj'];
		if($this->user['uid']=='0'){
			$from=$this->ip;
		}else{
			$from=$this->user['username'];
		}
		$comment=$this->db->fetch_by_field('comment','id',$id);
		if(!(bool)$comment){
			$this->message(-1,'',2);
		}
		$doc=$this->db->fetch_by_field('doc','did',$comment['did']);
		$report=$this->view->lang['commentCom'].$this->view->lang['commentUser'].$comment['author'].'<br/>'
				.$this->view->lang['commentCom'].$this->view->lang['commentTime'].$this->date($comment['time'])."<br/>"
				.$this->view->lang['commentId'].$comment['id'].'<br/>'.$this->view->lang['commentsDocTitle'].$doc['title']."<br/>"
				.$this->view->lang['commentContent'].$comment['comment'].'<br/>'
				.$this->view->lang['commentReportReason'].$report;
		$sendarray = array(
				'sendto'=>$sendto,
				'subject'=>$subject,
				'content'=>$report,
				'isdraft'=>1,
				'user'=>$this->user
			);
		$_ENV['pms']->send_ownmessage($sendarray);
		$this->message(1,'',2);
	}
}
?>