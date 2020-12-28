<?php
!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{
	
	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('user');
		$this->load('usergroup');
	}
	
 	function doregister(){
		if ($this->user['islogin']){
			$this->header('');
		}
		if(!isset($this->post['submit'])){
			if('0' === $this->setting['allow_register']){
				$this->message($this->setting['close_register_reason'],'',0);
			}
			
			if(isset($this->setting['register_least_minute']) && !$_ENV['user']->check_ip($this->ip)){
				$msg = $this->view->lang['registerTip9'];
				$msg = str_replace('30', $this->setting['register_least_minute'], $msg);
				$this->message($msg,'',0);
			}
			
			$_ENV['user']->passport_server('register','1');
			$_ENV['user']->passport_client('register');
			
			if(isset($this->setting['checkcode'])){
				$this->view->assign('checkcode',$this->setting['checkcode']);
			}else{
				$this->view->assign('checkcode',"0");
			}
			if (!isset($this->setting['name_min_length'])) {$this->setting['name_min_length'] = 3;}
			if (!isset($this->setting['name_max_length'])) {$this->setting['name_max_length'] = 15;}
			$this->view->assign('minlength',$this->setting['name_min_length']);
			$this->view->assign('maxlength',$this->setting['name_max_length']);
			$loginTip2 = $this->view->lang['loginTip2'];
			$loginTip2 = str_replace('3',$this->setting['name_min_length'],$loginTip2);
			$loginTip2 = str_replace('15',$this->setting['name_max_length'],$loginTip2);
			$this->view->assign('loginTip2',$loginTip2);
			$this->view->display('register');
		}else{
			$username=trim($this->post['username']);
			$password=$this->post['password'];
			$repassword=$this->post['repassword'];
			$email=trim($this->post['email']);
			$code=$this->setting['checkcode']!=3?trim($this->post['code']):'';
			$error=$this->docheck($username,$password,$repassword,$email,$code);
			if($error=='OK'){
				eval($this->plugin["ucenter"]["hooks"]["register"]);
				$uid=$_ENV['user']->add_user($username, md5($password), $email,$this->time,$this->ip);
				if($uid){
					$_ENV['user']->refresh_user($uid);
					$_ENV['user']->add_credit($this->user['uid'],'user-register',$this->setting['credit_register']);
					$this->view->assign('user',$this->user);
					$this->view->assign('newpms',$_ENV['global']->newpms($this->user['uid']));
					$_ENV['user']->passport_server('register','2');
					$this->message($this->view->lang['registerSuccess']."<b>$username</b>",'index.php',0);
				}else{
					$this->message($this->view->lang['registerFaild'],'',0);
				}
			}else{
				$this->view->assign('error',$error);
				$this->view->assign('username',$username);
				$this->view->assign('email',$email);
				$this->view->assign('code',$code);
				$this->view->display('register');
			}
		}
	}

	function dologin(){
		$_ENV['user']->passport_server('login','1');
		if(!isset($this->post['submit'])){
			$this->view->assign('checkcode',isset($this->setting['checkcode'])?$this->setting['checkcode']:0);
			
			$_ENV['user']->add_referer();
			$_ENV['user']->passport_server('login','2');
			$_ENV['user']->passport_client('login');
			
			if (!isset($this->setting['name_min_length'])) {$this->setting['name_min_length'] = 3;}
			if (!isset($this->setting['name_max_length'])) {$this->setting['name_max_length'] = 15;}
			$loginTip2 = str_replace(array('3','15'),array($this->setting['name_min_length'],$this->setting['name_max_length']),$this->view->lang['loginTip2']);
			$this->view->assign('name_min_length',$this->setting['name_min_length']);
			$this->view->assign('name_max_length',$this->setting['name_max_length']);
			$this->view->assign('loginTip2',$loginTip2);
			$this->view->display('login');
			exit;
		}else{
			$username=string::hiconv(trim($this->post['username']));
			$password=md5($this->post['password']);
			$error=$this->setting['checkcode']!=3?$this->docheckcode($this->post['code'],1):'OK';
			if($error=='OK'){
				$user=$this->db->fetch_by_field('user','username',$username);
				if ($this->setting['close_website'] === '1' && $user['groupid'] != 4){
					@header('Content-type: text/html; charset='.WIKI_CHARSET);
					exit($this->setting['close_website_reason']);
				}
				eval($this->plugin["ucenter"]["hooks"]["login"]);//UC返回登录js代码，退出了。
				if(is_array($user)&&($password==$user['password'])){
					if($this->time>($user['lasttime']+24*3600)){
						$_ENV['user']->add_credit($user['uid'],'user-login',$this->setting['credit_login']);
					}
					$_ENV['user']->update_user($user['uid'],$this->time,$this->ip);
					$_ENV['user']->refresh_user($user['uid']);
					$_ENV['user']->passport_server('login','3',$user);
					$newpms=$_ENV['global']->newpms($this->user['uid']);
					$adminlogin=$this->checkable('admin_main-login');
					if($this->post['indexlogin']==1){
						$usergroup=$this->db->fetch_by_field('usergroup','groupid',$user['groupid'],'grouptitle');
						$user['grouptitle'] = $usergroup['grouptitle'];
						$user['image'] = ($user['image'])?$user['image']:'style/default/user_l.jpg';
						$user['news'] = $newpms[0];
						//公共短消息个数
						$user['pubpms'] = $newpms[3];
						$user['adminlogin'] = $adminlogin;
						unset($user['signature']);
						//channel
						$user['channel'] = '';
						foreach($this->channel as $channel){
							$user['channel'].='<li class="l bor_no"><a href="'.$channel['url'].'" target="_blank">'.$channel['name'].'</a></li>';
						}
						$data='{';
						foreach($user as $key=>$value){
							$data.=$key.":'".$value."',";
						}
						$data=substr($data,0,-1).'}';
						$this->message($data,"",2);
					}else{
						$this->view->assign('user',$this->user);
						$this->view->assign('newpms',$newpms);
						$this->view->assign('adminlogin',$adminlogin);
						$this->message($this->view->lang['loginSuccess'],$_ENV['user']->get_referer() ,0);
					}
				}else{
					$this->message($this->view->lang['userInfoError'],'BACK',$this->post['indexlogin']?2:0);
				}
			}else{
				$this->message($error, 'BACK',$this->post['indexlogin']?2:0);
			}
		}
	}

	function docheck($username,$password,$repassword,$email,$code=''){
		if(($error=$this->docheckusername($username,1))=="OK"){
			if(($error=$_ENV['user']->checkpassword($password,$repassword))=="OK"){
				if(($error=$this->docheckemail($email,1))=="OK"){
					if($code!=''){
						$error=$this->docheckcode($code,1);
					}
				}
			}
		}
		return $error;
	}

	function docheckusername($username='',$type=0){
		$msg='OK';
		$type=isset($this->post['type'])?$this->post['type']:$type;
		if(empty($username)&&($username=string::hiconv(trim($this->post['username'])))==''){
			$msg='用户名为空';
		}else{
			if(!preg_match("/^[\w\s\d_\x80-\xff]+$/is", $username) || !$_ENV['user']->check_name($username)){
				$msg=$this->view->lang['registerTip8'];
			}else{
				eval($this->plugin["ucenter"]["hooks"]["checkname"]);
				if(!isset($uid)){
					$user=$this->db->fetch_by_field('user','username',$username);
					if(!empty($user)){//有此用户
						if($type>0){
							$msg=$this->view->lang['registerTip6'];
						}
					}else{
						if($type==0){
							$msg='无此用户';
						}
					}
				}
			}
		}
		if($type==1){
			return $msg;
		}else{
			$this->message($msg,'',2);
		}
	}

 	function docheckcode($code='',$type=0){
 		return $_ENV['user']->checkcode($code,$type);
	}

	function docheckoldpass(){
		$oldpass=md5($this->post['oldpass']);
		if($oldpass==$this->user['password']){
			$this->message('OK','',2);
		}else{
			$this->message($this->view->lang['oldPassError'],'',2);
		}
	}

 	function docheckemail($email='',$type=0){
 		$msg="OK";
		if($email==''){
			$email=$this->post['email'];
		}
		$lenmax=strlen($email);
		if($lenmax<6){
			$msg=$this->view->lang['getPassTip2'];
		}elseif((bool)$this->db->fetch_by_field('user','email',$email)){
			$msg=$this->view->lang['registerTip7'];
		}else{
			eval($this->plugin["ucenter"]["hooks"]["checkemail"]);
		}
		if($type==1){
			return $msg;
		}
		$this->message($msg,'',2);
	}

 	function dologout(){
 		$_ENV['user']->passport_client('logout');
		$synlogout="";
		$_ENV['user']->user_logout();
		$_ENV['user']->passport_server('logout');

		eval($this->plugin["ucenter"]["hooks"]["logout"]);
		$this->hsetcookie('style','',0);
		$this->message($this->view->lang['logoutSuccess'].$synlogout, $this->setting['site_url'], 0);
	}

	function doprofile(){
		if($this->user['birthday']){
			$this->user['birthday'] = date('Y-m-d',$this->user['birthday']);
		}else{
			$this->user['birthday']='';
		}
		eval($this->plugin["ucenter"]["hooks"]["iscredit"]);
		$this->user['editorstar']=$_ENV['usergroup']->get_star($this->user['stars']);
		$this->user['regtime'] = $this->date($this->user['regtime']);
		$creditDetail = $_ENV['user']->get_credit($this->user['uid']);
		$this->user['credits']= $creditDetail['creditTotal'];
		
		$this->view->assign('creditDetail',$creditDetail);
		$this->view->assign('user',$this->user);
		$this->view->assign('randnum',rand(0,100000));
		$this->view->display('profile');
	}

	function doeditprofile(){
		if(isset($this->post['submit'])){
			$gender = intval($this->post['gender']);
			$birthday = strtotime($this->post['birthday']);
			$location = $this->post['location'];
			$signature = $this->post['signature'];
			if (WIKI_CHARSET == 'GBK'){
				$location = string::hiconv($location);
				$signature = string::hiconv($signature);
			}
			$location = htmlspecialchars($location);
			$signature = htmlspecialchars(str_replace(array('\n','\r'),'',$signature));

			$_ENV['user']->set_profile($gender,$birthday,$location,$signature,$this->user['uid']);
		}else{
			if(0 == $this->user['birthday']){
				$birthday = '';
			}else{
				$birthday=$this->setting['time_offset']*3600+$this->setting['time_diff']*60+$this->user['birthday'];
				$birthday = date('Y-m-d',$birthday);
			}
			
			$this->view->assign('birthday',$birthday);
			$this->view->display('editprofile');
		}
	}

	function doeditpass(){
		if(isset($this->post['submit'])){
			$oldpass = $this->post['oldpass'];
			$newpass = $this->post['newpass'];
			$renewpass = $this->post['renewpass'];
			$oldpass=md5($oldpass);
			if(strlen($newpass)<1){
				$this->message($this->view->lang['editPassTip1'],"BACK",0);
			}
			if($oldpass==$this->user['password']){
				if($newpass==$renewpass){
					eval($this->plugin["ucenter"]["hooks"]["editpass"]);
					$_ENV['user']->update_field('password',md5($newpass),$this->user['uid']);
					$_ENV['user']->refresh_user($this->user['uid']);
					$this->view->assign('message',$this->view->lang['viewDocTip1']);
					$this->view->display('editpass');
					exit;
				}else{
					$this->message($this->view->lang['editPassTip3'],'BACK',0);
				}
			}else{
				$this->message($this->view->lang['oldPassError'],'BACK',0);
			}
		}
		if(defined('PP_OPEN')&&PP_OPEN){
			if(PP_TYPE=='client'){
				$this->message('go to modify the password on server','BACK',0);
			}
		}
		$this->view->display('editpass');
	}

	function doeditimage(){
		if(isset($this->post['submit'])){
			$image = $_FILES['image'];
			$extname=file::extname($image['name']);
			if( util::isimage($extname) ){
				$destfile = 'uploads/userface/'.($this->user['uid']%10).'/'.$this->user['uid'].'.'.$extname;
				$result = file::uploadfile($image,$destfile);
				if($result['result']){
					util::image_compress($destfile,NULL,90,90);
					$_ENV['user']->update_field("image",$destfile,$this->user['uid']);
					$this->view->assign('message',$this->view->lang['editImageSuccess']);
				}else{
					$this->view->assign('message',$result['msg']);
				}
			}else{
				$this->view->assign('message',$this->view->lang['editImageIllegal']);
			}
		}
		eval($this->plugin['ucenter']['hooks']['edit_user_image']);
		$this->view->display('editimage');
	}

	function dogetpass(){
		if(isset($this->get[2])){
			$uid=$this->get[2];
			$encryptstring=$this->get[3];
			$idstring=$_ENV['user']->get_idstring_by_uid($uid,$this->time);
			if($idstring==$encryptstring){
				$this->view->assign('uid',$uid);
				$this->view->assign('encryptstring',$encryptstring);
				$this->view->display('resetpass');
			}else{
				$this->message($this->view->lang['resetPassMessage'], $this->setting['site_url'] ,0);
			}
		}elseif(isset($this->post['verifystring'])){
			$uid=$this->post['uid'];
			$encryptstring=$this->post['verifystring'];
			$idstring=$_ENV['user']->get_idstring_by_uid($uid,$this->time);
			if($idstring==$encryptstring){
				$newpass = $this->post['password'];
				$renewpass = $this->post['repassword'];
				$error=$_ENV['user']->checkpassword($newpass,$renewpass);
				if($error=='OK'){
					eval($this->plugin["ucenter"]["hooks"]["getpass"]);
					$_ENV['user']->update_field('password',md5($newpass),$uid);
					$_ENV['user']->update_getpass($uid);
					$this->message($this->view->lang['resetPassSucess'],'index.php?user-login',0);
				}else{
				  $this->message($error,'BACK',0);
				}
			}else{
				$this->message($this->view->lang['resetPassMessage'], $this->setting['site_url'] ,0);
			}
		}
		if(!isset($this->post['submit'])){
			if(isset($this->setting['checkcode'])){
				$this->view->assign('checkcode',$this->setting['checkcode']);
			}else{
				$this->view->assign('checkcode',"0");
			}
			$this->view->display('getpass');
			exit;
		}
		$email=$this->post['email'];
		if(isset($this->setting['checkcode'])){
			if($this->setting['checkcode']!=3){
				$code=$this->post['code'];
				$error=$this->docheckcode($code,1);
			}else{
				$error = "OK";
			}
		}else{
			$code=$this->post['code'];
			$error=$this->docheckcode($code,1);
		}
		if("OK" != $error){
			$this->message($error,'BACK',0);
		}
		$user=$this->db->fetch_by_field('user','email',$email);
		if(!(bool)$user){
			$this->message($this->view->lang['noMail'],'BACK',0);
		}else{
			$timetemp=date("Y-m-d H:i:s",$this->time);
			$verification= rand(1000,9999);
			$encryptstring=md5($this->time.$verification);
			$reseturl=$this->setting['site_url']."/index.php?user-getpass-".$user['uid'].'-'.$encryptstring;
			$_ENV['user']->update_getpass($user['uid'],$encryptstring);
			$mail_subject = $this->setting['site_name'].$this->view->lang['getPass'];
			$mail_message = $this->view->lang['resetPassMs1'].$user['username'].$this->view->lang['resetPassMs2'].$timetemp.$this->view->lang['resetPassMs3']."<a href='".$reseturl."' target='_blank'>".$reseturl."</a>".$this->view->lang['resetPassMs4'].$this->setting['site_name'].$this->view->lang['resetPassMs5'].$this->setting['site_name'].$this->view->lang['resetPassMs6'];
			$adminemail = $_ENV['user']->get_admin_email();
			$_ENV['user']->send_email($email,$mail_subject,$mail_message,$adminemail);
			$this->message($this->view->lang['emailSucess'],'index.php?user-login',0);
		}
	}

	function docode(){
		if(isset($this->setting['checkcode'])){
			$codecase = $this->setting['checkcode'];
		}else{
			$codecase = 0;
		}
 		$code=util::random(4,$codecase);
		if(function_exists("imagecreate")){
			$_ENV['user']->save_code(strtolower($code),$this->user['sid']);
			util::makecode($code);
		}else{
			$_ENV['user']->save_code('abcd',$this->user['sid']);
			header('location:style/default/vdcode.jpg');
		}
	}

	function dospace(){
		$uid = $this->get[2];
		$type=(isset($this->get[3]))?intval($this->get[3]):0;
		$page = max(1, intval($this->get[4]));
		$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
		$start_limit = ($page - 1) * $num;
		
		$_ENV['user']->update_field('views',1,$uid,0);
		if($uid == $this->user['uid']){
			$spaceuser = $this->user;
			$spaceuser['views']++;
			$spaceuser['editorstar'] = $_ENV['usergroup']->get_star($spaceuser['stars']);
			
		}elseif(is_numeric($uid)){
			$spaceuser = $_ENV['usergroup']->get_groupinfo($uid);
		}else{
			$spaceuser = $_ENV['usergroup']->get_groupinfo($uid,'u.username');
			$uid=$spaceuser['uid'];
		}
		if(!(bool)$spaceuser){
			$this->message($this->view->lang['loginTip3'],'BACK',0);
		}
		//eval($this->plugin['ucenter']['hooks']['space_user_image']);
		$spaceuser['regtime'] = $this->date($spaceuser['regtime']);
		$doccount=($type)? $spaceuser['edits'] : $spaceuser['creates'];
		$doclist = $_ENV['user']->space($uid,$type,$start_limit,$num);
		$departstr=$this->multi($doccount, $num, $page,"user-space-$uid-$type");
		
		$this->view->assign('departstr',$departstr);
		$this->view->assign('type',$type);
		$this->view->assign('doclist',$doclist);
		$this->view->assign('spaceuser',$spaceuser);
		$this->view->display('space');
	}

	function doclearcookies(){
		$cookiepre=$this->setting['cookie_pre']?$this->setting['cookie_pre']:'hd_';
		$lenth=strlen($cookiepre);
		if(is_array($_COOKIE)) {
			foreach ($_COOKIE as $key => $val) {
				if(substr($key,0,$lenth)==$cookiepre){
					$this->hsetcookie(substr($key,$lenth), '',0);
				}
			}
		}
	 	$this->header();
	}
	


}
?>
