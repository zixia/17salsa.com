<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class usermodel {

	var $db;
	var $base;

	function usermodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	/*Description: This method has already expired*/
	function get_user($field,$value){
		return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."user WHERE  $field='$value'");
	}
	
	function get_users($field,$value){
		$sql="SELECT * FROM ".DB_TABLEPRE."user WHERE  $field='$value'";
		$query=$this->db->query($sql);
		while($user=$this->db->fetch_array($query)){
			$userlist[]=$user;
		}
		return $userlist;
	}
	
	function add_referer(){
		if($_SERVER['HTTP_REFERER']){
			$this->db->query("UPDATE ".DB_TABLEPRE."session SET referer ='".$_SERVER['HTTP_REFERER']."' WHERE sid='".base::hgetcookie('sid')."'");
		}
	}
	function get_referer(){
		$session=$this->db->fetch_first("SELECT referer FROM ".DB_TABLEPRE."session  WHERE sid='".base::hgetcookie('sid')."'");
		if($session['referer']==""){
			$session['referer']="index.php";
		}else{
			if(strpos($session['referer'],'admin_')!==false){
				$session['referer']="index.php?admin_main";
			}
		}
		return $session['referer'];
	}
	function  refresh_user($uid){
		$sid=base::hgetcookie('sid');
		$this->base->user=$this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."user u,".DB_TABLEPRE."usergroup g WHERE u.uid=$uid AND u.groupid=g.groupid");
		$session=$this->db->fetch_first("SELECT referer FROM ".DB_TABLEPRE."session  WHERE sid='".$sid."'");
		$this->db->query("REPLACE INTO ".DB_TABLEPRE."session (sid,uid,username,islogin,`time`,referer) VALUES ('$sid',$uid,'".$this->base->user['username']."',1,{$this->base->time},'".$session['referer']."')");
		$password=$this->base->user['password'];
		$auth = $this->base->authcode("$uid\t$password",'ENCODE');
		$this->base->hsetcookie('auth',$auth,24*3600*365);
	}
	
 	
	function update_user($uid,$time,$ip){
		 $this->db->query("UPDATE ".DB_TABLEPRE."user SET lasttime='$time',lastip='$ip' WHERE uid='$uid'");
	}
	
	function user_logout(){
		$sid=$this->base->user['sid'];
		$this->base->hsetcookie('sid','',0);
		$this->base->hsetcookie('auth','',0);
		if($sid){
			$this->db->query('DELETE FROM '.DB_TABLEPRE.'session WHERE sid=\''.$sid.'\'');
		}
	}
	
	function  send_email($to,$subject,$message,$from){
		if('UTF-8'==WIKI_CHARSET){
			$subject=string::hiconv($subject,'gbk','utf-8');
			$message=string::hiconv($message,'gbk','utf-8');
		}
		$mail_config=unserialize($this->base->setting['mail_config']);
		if(isset($mail_config) && !empty($mail_config['mail_host'])){
			require('lib/smtp.class.php');
			$smtp = new smtp ($mail_config['mail_host'],$mail_config['mail_port'],true,$mail_config['mail_user'],$mail_config['mail_pass']);
			$smtp->debug = false;
			$smtp->sendmail($to, $mail_config['mail_mail'], $subject, $message, 'HTML');
		}else{
			require('lib/mail.class.php');
			$m= new Mail;
			$m->From($from);
			$m->To($to);
			$m->Subject($subject);
			$m->Body($message,'gbk');
			$m->Priority(2);
			$m->Send();
		}
	}
	
	function get_admin_email(){
		return $this->db->result_first("SELECT email FROM ".DB_TABLEPRE."user WHERE groupid=4");
	}
	
	function  update_getpass($uid,$code=''){
		if(empty($code)){
			$this->db->query("UPDATE ".DB_TABLEPRE."activation SET available=0 WHERE uid=$uid");
		}else{
			$this->db->query("DELETE  FROM ".DB_TABLEPRE."activation WHERE uid=$uid AND available=0  ");
			$this->db->query("REPLACE INTO  ".DB_TABLEPRE."activation (uid,code,time,type) VALUES ($uid,'$code',{$this->base->time},1)");
		}
	}
	function  get_idstring_by_uid($uid,$time){
		return $this->db->result_first("SELECT code FROM ".DB_TABLEPRE."activation WHERE uid=$uid AND available=1  AND type=1 AND time>($time-3*24*3600) ORDER BY time DESC");
	}
	
	function add_user($username,$password,$email,$time,$ip='127.0.0.1',$groupid=2,$uid='') {
		$language=$this->base->setting['lang_name'];
		$style=$this->base->setting['style_name'];
		
		$checkup = $this->base->setting['register_check'] == '1'? '0' : '1';
		
		if ($this->base->user['islogin'] == 2){	$checkup = 1;}
		
		$credits = 0;
		$usergroup = $this->db->fetch_first('SELECT creditslower,`type` FROM '.DB_TABLEPRE.'usergroup  WHERE  groupid='.$groupid);
		if($usergroup && $usergroup['type'] == 2){
			$credits = $usergroup['creditslower'];
		}
		
		if($uid){
			$sql = "REPLACE INTO  ".DB_TABLEPRE."user (uid,username,`password`,email,regip,regtime,groupid,credit2,language,style,checkup) VALUES"
			." ($uid,'$username','$password','$email','$ip',$time,$groupid,$credits,'$language','$style',$checkup)";
		}else{
			$sql ="INSERT INTO  ".DB_TABLEPRE."user (username,`password`,email,regip,regtime,groupid,credit2,language,style,checkup) VALUES "
			."('$username','$password','$email','$ip',$time,$groupid,$credits,'$language','$style',$checkup)";
		}
		
		if($this->db->query($sql)){
			$uid = $this->db->insert_id();
			if($credits != 0){
				$this->db->query("INSERT INTO ".DB_TABLEPRE."creditdetail(uid,operation,credit2,time) VALUES ($uid,'user-changegroup',$credits,{$this->base->time}) ");
			}
			return $uid;
		}else{
			return false;
		}
	}
	
	function save_code($code,$sid) {
		$uid=$this->base->user['uid'];
		$islogin=$this->db->result_first("SELECT islogin FROM ".DB_TABLEPRE."session WHERE sid='$sid'");
		$islogin=$islogin?$islogin:0;
		$this->db->query("REPLACE INTO ".DB_TABLEPRE."session (sid,uid,code,islogin,`time`) VALUES ('$sid',$uid,'$code','$islogin',{$this->base->time})");
	}
	
	function get_code(){
		$sid=$this->base->user['sid'];
		return $this->db->result_first('SELECT code FROM '.DB_TABLEPRE.'session  WHERE sid=\''.$sid.'\'');
	}
	
	function checkcode($code='',$type=0){
		$msg="OK";
		$code=empty($code)?$this->base->post['code']:$code;
 		if(empty($code)||strtolower($code)!=$this->get_code()){
			$msg=$this->base->view->lang['codeError'];
 		}
		if($type==1){
			return $msg;
		}
		$this->base->message($msg,'',2);
	}
	
	function checkpassword($password,$repassword){
 		$msg='OK';
 		if($password!=$repassword){
 			$msg=$this->base->view->lang['editPassTip3'];
 		}else{
	 		$lenmax=strlen($password);
			if(0 == $lenmax || $lenmax>32){
				$msg=$this->base->view->lang['editPassTip1'];
			}
 		}
		return $msg;
	}
	
	function is_login(){
		$sid=$this->base->user['sid'];
		$islogin=$this->db->result_first('SELECT islogin FROM '.DB_TABLEPRE.'session  WHERE sid=\''.$sid.'\'');
		if(! $islogin){
			if($this->base->user['uid']>0){
				$islogin=1;
			}
		}
		return $islogin;
	}
	
	function edit_user($uid, $password, $email, $groupid) {
		$credits = 0;
		$newcredits = 0;
		$diffcredits = 0;
		$sqladd =' ';
		
		$user = $this->get_user('uid',$uid);
		$user=$this->db->fetch_by_field('user','uid',$uid);
		if (is_array($user)){
			$credits = $user['credits'];
		}
		
		$usergroup = $this->db->fetch_first("SELECT `type`,`creditslower`,`creditshigher` FROM ".DB_TABLEPRE."usergroup WHERE `groupid`=$groupid");
		
		if ($user["groupid"] != $groupid && is_array($usergroup) && $usergroup['type'] == 2){
			$newcredits = $usergroup['creditslower'];
			$diffcredits = $newcredits - $credits;
			$sqladd=" ,credit2=$newcredits ";
			$this->db->query("INSERT INTO ".DB_TABLEPRE."creditdetail(uid,operation,credit2,time) VALUES ($uid,'user-changegroup',$diffcredits,{$this->base->time}) ");
		}
		if(empty($password)){
			$this->db->query("UPDATE ".DB_TABLEPRE."user SET email='$email' ,groupid=$groupid $sqladd WHERE uid=$uid ");
		}else{
			$password = md5($password);
			$this->db->query("UPDATE ".DB_TABLEPRE."user SET password='$password' ,email='$email' ,groupid=$groupid $sqladd WHERE uid=$uid ");
		}
	}

	function remove_user($uids){
		$uidsql = implode($uids,',');
		$query=$this->db->query("SELECT * FROM ".DB_TABLEPRE."user WHERE uid IN ($uidsql)");
		while($user=$this->db->fetch_array($query)){
			$sqladd.=" ('user','".$user['username']."','".addslashes(serialize($user))."','N;','".$this->base->user['uid']."','".$this->base->user['username']."','".$this->base->time."'),";
		}
		$this->db->query("INSERT INTO ".DB_TABLEPRE."recycle (type,keyword,content,file,adminid,admin,dateline) values ".substr($sqladd,0,-1));
			
		$this->db->query("DELETE FROM  ".DB_TABLEPRE."user WHERE uid IN ($uidsql) ");
	}
	
	function recover($data){
		$data=string::haddslashes($data,1);
		$this->db->query("INSERT INTO  ".DB_TABLEPRE."user (uid,username,password,email,gender,credit2,credit1,birthday,image,location,regip,regtime,lastip,lasttime,groupid,timeoffset,style,language,signature,creates,edits,views,checkup) 
					VALUES ('".$data['uid']."','".$data['username']."','".$data['password']."','".$data['email']."','".$data['gender']."','".$data['credits']."','".$data['credit1']."','".$data['birthday']."','".$data['image']."','".$data['location']."','".$data['regip']."','".$data['regtime']."','".$data['lastip']."','".$data['lasttime']."','".$data['groupid']."','".$data['timeoffset']."','".$data['style']."','".$data['language']."','".$data['signature']."','".$data['creates']."','".$data['edits']."','".$data['views']."','".$data['checkup']."')");
	}
	
	function checkup_user($uids, $value=1){
		$uidsql = implode($uids,',');
		$this->db->query("UPDATE ".DB_TABLEPRE."user set checkup=$value WHERE uid IN ($uidsql) ");
	}
	
	function add_credit($uid,$operation,$credit2,$credit1=0){
		$set=$this->base->setting;
		$coin_list=array(
			'user-register'=>$set['coin_register'],
			'user-login'=>$set['coin_login'],
			'doc-create'=>$set['coin_create'],
			'doc-edit'=>$set['coin_edit'],
			'attachment-upload'=>$set['coin_upload'],
			'user-pms'=>$set['coin_pms'],
			'user-comment'=>$set['coin_comment']
		);
		if(isset($coin_list[$operation])){
			$credit1=$coin_list[$operation];
			if(is_numeric($credit1)){
				$credit1 = intval($credit1);
			}else{
				$credit1 = 0;
			}
		}
		
		$this->db->query("INSERT INTO ".DB_TABLEPRE."creditdetail(uid,operation,credit2,credit1,time) VALUES ($uid,'$operation',$credit2,$credit1,{$this->base->time}) ");
		$this->db->query("UPDATE ".DB_TABLEPRE."user SET credit2=credit2+$credit2,credit1=credit1+$credit1 WHERE uid=$uid ");

		$usergroup = $this->db->fetch_first("SELECT g.`type` FROM ".DB_TABLEPRE."user u,".DB_TABLEPRE."usergroup g WHERE u.uid=$uid AND u.`groupid`=g.`groupid`");
		
		if ($usergroup['type'] == 2){
			$sql = "SELECT g.groupid FROM ".DB_TABLEPRE."user u,".DB_TABLEPRE."usergroup g WHERE u.uid=$uid AND g.`type`=2  AND u.credit2 >= g.creditslower ORDER BY g.creditslower DESC LIMIT 0,1";
			$usergroup = $this->db->fetch_first($sql);
			
			if (is_array($usergroup) && isset($usergroup['groupid'])){
				$groupid = $usergroup['groupid'];
				$this->db->query("UPDATE ".DB_TABLEPRE."user SET groupid=$groupid WHERE uid=$uid ");
			}
		}
	}
	
	function add_coin($uid,$coin){
		$this->db->query("UPDATE ".DB_TABLEPRE."user SET credit1=credit1+$coin WHERE uid in($uid) ");
	}
	
	function get_credit($uid){
		$credit = array();
		$query = $this->db->query("SELECT operation, sum(`credit2`) AS `total` FROM ".DB_TABLEPRE."creditdetail WHERE uid=$uid GROUP BY operation");
		
		$credit['dailyOperate'] = 0;
		$credit['createDoc'] = 0;
		$credit['editDoc'] = 0;
		$credit['removedDoc'] = 0;
		$credit['promotion-register'] = 0;
		$credit['promotion-visit'] = 0;
		
		while ($creditdetail = $this->db->fetch_array($query)){
			switch ($creditdetail['operation']){
				case 'doc-create':
					$credit['createDoc'] += $creditdetail['total'];
					break;
				case 'doc-edit':
					$credit['editDoc'] += $creditdetail['total'];
					break;
				case 'remove_doc':
					$credit['removedDoc'] += $creditdetail['total'];
					break;
				case 'promotion-visit':
					$credit['promotion-visit'] += $creditdetail['total'];
					break;
				case 'promotion-register':
					$credit['promotion-register'] += $creditdetail['total'];
					break;
				default:
					$credit['dailyOperate'] += $creditdetail['total'];
			}
		}
		$credit['creditTotal'] = $credit['dailyOperate'] + $credit['createDoc'] + $credit['editDoc']+ $credit['removedDoc']+$credit['promotion-register']+$credit['promotion-visit'];
		return $credit;
	}
	
	function get_total_num($username='',$groupid=2, $checkup=1){
		$groupid = (0 == $checkup)? 2: $groupid;
		
		if($groupid==0){
			$dsql='SELECT COUNT(*) FROM '.DB_TABLEPRE.'user where checkup='.$checkup;
		}else{
			$dsql='SELECT COUNT(*) FROM '.DB_TABLEPRE.'user u, '.DB_TABLEPRE.'usergroup g  WHERE u.groupid=g.groupid AND g.groupid='.$groupid.' AND u.checkup='.$checkup;
			if(!empty($username)){
				$dsql.=" AND u.username LIKE '%{$username}%' ";
			}
		}
		return $this->db->result_first($dsql);
	}
	
	function get_list($username='',$groupid=2,$start=0,$limit=10, $checkup=1){
		$userlist=array();
		$sql='SELECT * FROM '.DB_TABLEPRE.'user u, '.DB_TABLEPRE.'usergroup g  WHERE u.groupid=g.groupid';
		
		if($groupid != 0){
		  $sql.=' AND g.groupid='.$groupid;
		}
		
		if(!empty($username)){
		  $sql.=" AND u.username LIKE '%{$username}%' ";
		}
		
		$sql.=" AND u.checkup=$checkup ORDER BY u.uid DESC LIMIT $start,$limit";
		$query=$this->db->query($sql);
		while($user=$this->db->fetch_array($query)){
			$user['lasttime']=$this->base->date($user['lasttime']);
			$user['regtime']=$this->base->date($user['regtime']);
			$userlist[]=$user;
		}
		return $userlist;
	}
	
	function set_profile($gender,$birthday,$location,$signature,$uid){
		$this->db->query("UPDATE `".DB_TABLEPRE."user` SET gender = '$gender',birthday = '$birthday',location = '$location',signature = '$signature' WHERE uid = $uid");		
	}
	
	function update_field($field,$value,$uid,$type=1){
		if($type){
			$sql="UPDATE ".DB_TABLEPRE."user SET $field='$value' WHERE uid= $uid ";
		}else{
			$sql="UPDATE ".DB_TABLEPRE."user SET $field=$field+$value WHERE uid= $uid ";
		}
		$this->db->query($sql);
	} 
	function update_extra($uid,$truename,$telephone,$email,$location,$postcode,$qq){
		$sql="UPDATE ".DB_TABLEPRE."user SET ";
		if($truename){
			$sql.=" truename='$truename',";
		}
		if($telephone){
			$sql.=" telephone='$telephone',";
		}
		if($email){
			$sql.=" email='$email',";
		}
		if($location){
			$sql.=" location='$location',";
		}
		if($postcode){
			$sql.=" postcode='$postcode',";
		}
		if($qq){
			$sql.=" qq='$qq',";
		}
		$sql=substr($sql,0,-1)." WHERE uid= $uid ";
		$this->db->query($sql);
	}
	
	function space($uid,$type = 0,$start,$limit){
		$sql=array(
			"SELECT d.did, d.title, d.author, d.authorid, d.time, d.tag, d.summary, d.edits, d.views FROM  ".DB_TABLEPRE."doc d  WHERE authorid = $uid  ORDER BY did DESC LIMIT $start,$limit",
			"SELECT d.did, d.title, d.author, d.authorid, d.time, d.tag, d.summary, d.edits, d.views FROM  ".DB_TABLEPRE."edition  e, ".DB_TABLEPRE."doc d  WHERE e.authorid = $uid AND d.authorid !=$uid AND e.did = d.did GROUP BY d.did ORDER BY eid DESC LIMIT  $start,$limit"
		);
		$doclist = array();
		$query = $this->db->query($sql[$type]);
		while($doc = $this->db->fetch_array($query)){
			$doc['tag'] = explode(";",$doc['tag']);
			$doc['rawtitle'] = $doc['title'];
			$doc['title'] = htmlspecialchars($doc['title']);
			$doc['time'] = $this->base->date($doc['time']);
			$doclist[] = $doc;
		}
		return $doclist;
	}
	
 
	
	function update_session($session,$sid){
		$sql="UPDATE ".DB_TABLEPRE."session ";
		foreach($session as $key => $value){
			$sql.=" SET $key ='$value',";
		}
		$sql=substr($sql,0,-1);
		$sql.=" WHERE sid='$sid' ";
		$this->db->query($sql);
	}
	
	function get_popularity_user($start=0,$limit=10){
		$userlist=array();
		$query=$this->db->query("SELECT * FROM ".DB_TABLEPRE."user  WHERE checkup=1 ORDER BY views DESC LIMIT $start,$limit");		
		while($user=$this->db->fetch_array($query)){
			$userlist[]=$user;
		}
		return $userlist;
	}
	
	function get_week_num(){
		$week = (int)date('w');
		if(0==$week){$week = 7;}
		
		$t = strtotime(date('Y-m-d'));
		$endtime = $t - $week * 24 * 60 * 60;
		$starttime = $endtime - 7 * 24 * 60 * 60;
		
		$sql="SELECT COUNT(DISTINCT u.uid) FROM ".DB_TABLEPRE."user u,".DB_TABLEPRE."creditdetail c WHERE u.uid=c.uid AND c.time>$starttime AND c.time <$endtime AND u.groupid!=1  GROUP BY u.uid ";
		return $this->db->result_first($sql);
	}
	
	function get_week_user($start=0,$limit=10,$starttime=0){
		$weekuserlist=array();
		$week=(int)date('w');
		if (0==$week){$week = 7;}
		$t = strtotime(date('Y-m-d'));
		$endtime = $t - $week * 24 * 60 * 60;
		$starttime = $endtime - 7 * 24 * 60 * 60;
		
		$query=$this->db->query("SELECT u.uid, u.username, u.image, sum( c.credit2 ) credit, u.creates, u.views  FROM ".DB_TABLEPRE."user u,".DB_TABLEPRE."creditdetail c   WHERE u.uid=c.uid AND c.time>$starttime AND c.time <$endtime AND u.groupid!=1  GROUP BY u.uid ORDER BY credit  DESC LIMIT $start,$limit");
		while($user=$this->db->fetch_array($query)){
			$weekuserlist[]=$user;
		}
		return $weekuserlist;
	}
	
	function get_top_user($start=0,$limit=10){
		$userlist=array();
		$query=$this->db->query("SELECT u.uid, u.username,u.credit2 credit FROM ".DB_TABLEPRE."user u  ORDER BY u.credit2  DESC LIMIT $start,$limit");
		while($user=$this->db->fetch_array($query)){
			$userlist[]=$user;
		}
		return $userlist;
	}
	
	function check_name($name){
		$error_names = $this->base->setting['error_names'];
		if (empty($error_names)) return true;
		
		$error_names = explode("\n", $error_names);
		
		foreach($error_names as $value){
			$v = str_replace('*','',$value);
			$v = trim($v);
			if ($v == '') {continue;}
			if ($v == $name) {return false;	}
			
			$pos = strpos($name, $v);
			
			if (substr($value,0,1) == '*' && substr($value,-1) == '*'){
				if (false !== $pos){return false;}
				
			}else if (substr($value,0,1) == '*'){
				if (substr($name, 0-strlen($v)) == $v){return false;}
				
			}else if (substr($value,-1) == '*'){
				if (substr($name, 0, strlen($v)) == $v){return false;}
				
			}else{
				if ($value == $name){return false;}
			}
		}
		return true;
	}
	
	function check_ip($ip){
		$minute = $this->base->setting['register_least_minute'];
		if (empty($minute)) {return true;}
		$user = $this->db->fetch_first("SELECT regtime FROM ".DB_TABLEPRE."user WHERE  `regip`='$ip' order by `uid` desc");
		if ($user && ( 60*$minute+$user['regtime']>time() ) ){return false;}
		return true;
	}
	
	function passport_server($type,$position='',$user=''){
		if(defined('PP_OPEN')&&PP_OPEN && PP_TYPE=='server'){
			if($type=='register'){
				if($position=='1'){
					$this->base->view->assign('forward',$this->base->forward);
				}elseif($position=='2'){
					$userarr=$this->base->user;
					if(PP_NAME=='PHPCMS'){
						$userarr['password']=$this->base->post['password'];
					}
					$this->pp_interface('login',$userarr,$this->base->post['forward']);
				}
			}elseif($type=='login'){
				if($position=='1'){
					$pos=strpos($_SERVER['QUERY_STRING'],'?');
					if($pos!==false){
						parse_str(substr($_SERVER['QUERY_STRING'],$pos+1),$userdb);
						$userdb['submit']=1;
						$this->base->post=$userdb;
					}
				}elseif($position=='2'){
					$this->base->view->assign('forward',$this->base->forward);
				}elseif($position=='3'){
					if(PP_NAME=='PHPCMS'){
						$user['password']=$this->base->post['password'];
					}
					$this->pp_interface('login',$user,$this->base->post['forward']);
				}
			}elseif($type=='logout'){
				$this->pp_interface('logout',$this->base->user);
			}
		}
	}
	
	function passport_client($type){
		if(defined('PP_OPEN')&&PP_OPEN && PP_TYPE=='client'){
			if($type=='register'){
				$url = PP_API.PP_REG;
			}elseif($type=='login'){
				$url = PP_API.PP_LOGIN;
			}elseif($type=='logout'){
				$url = PP_API.PP_LOGOUT;
			}
			header('location:'.$url.(false===strpos($url,'?')?'?':'&').'forward='.$this->base->forward);
			exit;
		}
	}
	
	function pp_interface($action,$user,$forward=''){
		if(!$forward)$forward = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:$this->base->setting['site_url'];
		$jumpurl = PP_API;
		if(PP_NAME=='PHPCMS'){
			$jumpurl.='/member/api/passport_client.php';
		}else if(PP_NAME=='DEDECMS'){
			$jumpurl.='/member/passport_client.php';
			if($action == 'logout'){
				$action = 'quit';
			}
		}else if(PP_NAME=='PHPWIND'){
			$jumpurl.='/passport_client.php';
			if($action == 'logout'){
				$action = 'quit';
			}
		}
		$member=array();
		$member['username']=$user['username'];
		$member['password']=$user['password'];
		$member['email']=$user['email'];
		$member['cktime']=$this->base->time+(60*60*24*365);
		$userdb_encode = '';
		foreach($member as $key=>$val){
			$userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
		}
		$userdb_encode .= '&time='.$this->base->time;
		$auth = util::strcode($userdb_encode, 'ENCODE');

		$verify = md5($action.$auth.$forward.PP_KEY);
		$url = $jumpurl.'?action='.$action.'&userdb='.urlencode($auth).'&forward='.urlencode($forward).'&verify='.$verify;

		header('location:'.$url);
		exit;
	}
	
}


?>
