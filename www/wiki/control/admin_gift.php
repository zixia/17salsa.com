<?php
!defined('IN_HDWIKI') && exit('Access Denied');
class control extends base{
	
	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('gift');
		$this->load('setting');
		$this->view->setlang($this->setting['lang_name'],'back');
	}

	function dodefault(){
 		$this->dosearch();
	}

	
	/*礼品搜索(后台操作)*/
	function dosearch(){
		$title=isset($this->post['title'])?$this->post['title']:$this->get[2];//礼品名称
		$type=isset($this->post['type'])?$this->post['type']:$this->get[3];//价格区间
		$qstarttime=isset($this->post['qstarttime'])?strtotime($this->post['qstarttime']):(int)$this->get[4];
		$endtime=isset($this->post['endtime'])?strtotime($this->post['endtime']):(int)$this->get[5];
		/*读取价格区间*/
		$gift_range=array();
		$gift_range=unserialize($this->setting['gift_range']);
		$minprice=array_keys($gift_range);
		$maxprice=array_values($gift_range);
		$this->view->assign("minprice",$minprice);
		$this->view->assign("maxprice",$maxprice);
		$startprice = $endprice = '';
		if(-1!=$type && $type !=NULL){
			$startprice = $minprice[$type]; //价格起始值
			$endprice = $maxprice[$type];//价格结束值
		}
		$page = max(1, intval($this->get[6])); //当前页面
		$total=$this->db->fetch_total('gift','1');//总礼品记录数
		$limit=20;//每页显示数
	 	$start_limit = ($page - 1) * $limit;
		$giftlist=$_ENV['gift']->get_list($title,$startprice,$endprice,$qstarttime,$endtime,$start_limit,$limit,3);		
		/*分页字符串*/
		$departstr=$this->multi($total, $limit, $page,"admin_gift-search-$title-$type-$starttime-$endtime");
		$this->view->assign('giftlist',$giftlist);
		$this->view->assign('departstr',$departstr);
		$this->view->assign('page',$page);

		$this->view->assign("title",$title);
		$this->view->assign("type",$type);
		$this->view->assign("qstarttime",$qstarttime?date("Y-m-d",$qstarttime):"");
		$this->view->assign("endtime",$endtime?date("Y-m-d",$endtime):"");

		$this->view->display('admin_gift');
	}
	
	/*添加礼品(后台操作)*/
	function doadd(){
		if(!isset($this->post['submit'])){
			$this->view->display('admin_addgift');
		}else{
			$title = trim($this->post['title']);
			$credit = trim($this->post['credit']);
			$description = trim($this->post['description']);
			$imgname=$_FILES['giftfile']['name'];
			$extname=file::extname($imgname);
			$destfile = 'uploads/gift/'.util::random(8).'.'.$extname;
			$uploadreturn = file::uploadfile($_FILES['giftfile'],$destfile);
			
			util::image_compress($destfile,'',500,500,'');
			$iamge=util::image_compress($destfile,'',106,106,'_s');
			
			$destfile=$iamge['tempurl'];
			
			if($uploadreturn['result'] === false){
				$this->message($uploadreturn['msg'],'index.php?admin_gift-search');
			}
			$_ENV['gift']->add($title, $destfile, $credit, $description);
			$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-search');
		}
	}
	
	/*编辑礼品(后台操作)*/
	function doedit(){
		if(!isset($this->post['submit'])){
			$id=$this->get[2];
			$gift=$_ENV['gift']->get($id);
			$this->view->assign("gift",$gift);
			$this->view->display('admin_editgift');
		}else{
			$id=trim($this->post['id']);
			$gift=$_ENV['gift']->get($id);
			$title = trim($this->post['title']);
			$credit = trim($this->post['credit']);
			$description = trim($this->post['description']);
			$imgname=$_FILES['giftfile']['name'];
			if(''!=$imgname){
				file::uploadfile($_FILES['giftfile'],$gift['image']);
			}
			$_ENV['gift']->edit($id,$title, $credit, $description);
			$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-search');
		}
	}
	
	/*删除礼品(后台操作)*/
	function doremove(){
		$chkid=$this->post['chkid'];
		$_ENV['gift']->remove($chkid);
		$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-search');
	}
	
	
	
	/*设置礼品状态(后台操作)*/
	function doavailable(){
		$chkid=$this->post['chkid'];
		$available=$this->get[2];
		$ids=implode(',',$chkid);
		$this->db->update_field('gift','available',$available,"  id IN ($ids)  " );
		$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-search');
	}
	
	
	/*礼品价格区间设置(后台操作)*/
	function doprice(){
		if(!isset($this->post['submit'])){
			$gift_range=array();
			$gift_range=unserialize($this->setting['gift_range']);
			$minprice=array_keys($gift_range);
			$maxprice=array_values($gift_range);
			$this->view->assign("minprice",$minprice);
			$this->view->assign("maxprice",$maxprice);
			$this->view->display('admin_giftprice');
		}else{
			$minprice=$this->post['minprice'];
			$maxprice=$this->post['maxprice'];
			$gift_range=array_combine($minprice,$maxprice);//一个数组做键，另一个数组的做value
			if(isset($gift_range[''])){
				unset($gift_range['']);//清除空值
			}
			$setting['gift_range']=serialize($gift_range); //序列化成字符串
			$_ENV['setting']->update_setting($setting);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-price');
		}
	}
	
	/*礼品公告设置(后台操作)*/
	function donotice(){
		if(isset($this->post['submit'])){
			$setting['gift_notice']=$this->post['gift_notice'];
			$_ENV['setting']->update_setting($setting);
			$this->cache->removecache('setting');
			$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-notice');
		}
		$this->view->display('admin_giftnotice');
	}
	
	/*兑换礼品日志*/
	function dolog(){
		$title=isset($this->post['title'])?$this->post['title']:$this->get[2];//礼品名称
		$username=isset($this->post['username'])?$this->post['username']:$this->get[3];
		$type=isset($this->post['type'])?$this->post['type']:$this->get[4];//价格区间
		$qstarttime=isset($this->post['qstarttime'])?strtotime($this->post['qstarttime']):(int)$this->get[5];
		$endtime=isset($this->post['endtime'])?strtotime($this->post['endtime']):(int)$this->get[6];
		/*读取价格区间*/
		$gift_range=unserialize($this->setting['gift_range']);
		$minprice=array_keys($gift_range);
		$maxprice=array_values($gift_range);
		$this->view->assign("minprice",$minprice);
		$this->view->assign("maxprice",$maxprice);
		if(-1!=$type){
			$startprice = $minprice[$type]; //价格起始值
			$endprice = $maxprice[$type];//价格结束值
		}
		$page = max(1, intval($this->get[7])); //当前页面
		$total=$this->db->fetch_total('giftlog','1');//总礼品记录数
		$limit=20;//每页显示数
	 	$start_limit = ($page - 1) * $limit;
		$loglist=$_ENV['gift']->get_loglist($title,$username,$startprice ,$endprice ,$qstarttime,$endtime,$start_limit,$limit);		
		/*分页字符串*/
		$departstr=$this->multi($total, $limit, $page,"admin_gift-log-$title-$username-$type-$starttime-$endtime");
		$this->view->assign('loglist',$loglist);
		$this->view->assign('departstr',$departstr);
		$this->view->assign('page',$page);

		$this->view->assign("title",$title);
		$this->view->assign("type",$type);
		$this->view->assign("username",$username);
		$this->view->assign("qstarttime",$qstarttime?date("Y-m-d",$qstarttime):"");
		$this->view->assign("endtime",$endtime?date("Y-m-d",$endtime):"");

		$this->view->display('admin_giftlog');
	}
	
	/*设为已经寄送状态*/
	function doverify(){
		$chkid=$this->post['chkid'];
		$names=$this->post['names'];
		$subject = '寄送礼品';
		$content = '您兑换的礼品已寄出,请注意查收!';
		$sendarray = array(
			'sendto'=>$names,
			'subject'=>$subject,
			'content'=>$content,
			'isdraft'=>0,
			'user'=>$this->user
		);
		if($names){
			$this->load('pms');
			$_ENV['pms']->send_ownmessage($sendarray);
		}
		$ids=implode(',',$chkid);
		$this->db->update_field('giftlog','status',1,"  id IN ($ids)  " );
		$this->message($this->view->lang['usermanageOptSuccess'],'index.php?admin_gift-log');
	}
	

}
?>
