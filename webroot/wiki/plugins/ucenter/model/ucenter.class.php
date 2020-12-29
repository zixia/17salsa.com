<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class ucentermodel {

	var $db;
	var $base;

	function ucentermodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	/*插件必须具有的安装方法*/
	function install(){
		$hdir=HDWIKI_ROOT.'/api';
		if(!is_dir($hdir)){
			mkdir($hdir,0777);
		}
		$fromfile=HDWIKI_ROOT.'/plugins/ucenter/api/uc.php';
		$tofile=HDWIKI_ROOT.'/api/uc.php';
		copy($fromfile,$tofile);

		$this->db->query("INSERT INTO `".DB_TABLEPRE."regular` (`name`,`regular`,`type`) VALUES ('UC积分兑换','exchange-default','2')");
		$this->db->query("UPDATE `".DB_TABLEPRE."usergroup` SET regulars =  CONCAT(regulars,'|exchange-default') WHERE groupid = 2");

		$plugin=array(
		'name'=>'UC整合',
		'identifier'=>'ucenter',
		'description'=>'整合ucenter',
		'datatables'=>'ucenter',
		'type'=>'0',
		'copyright'=>'hudong.com',
		'homepage'=>'http://kaiyuan.hudong.com',
		'version'=>'1.51',
		'suit'=>'4.1',
		'modules'=>''
		);
		$hook=array(
		array(
			'available'=>"1",
			'title'=>'include',
			'description'=>'包含必要的文件，比如UC客户端文件',
			'code'=>'$ucurl=HDWIKI_ROOT."/plugins/ucenter/ucconfig.inc.php";
	if(file_exists($ucurl) && isset($this->plugin["ucenter"]["available"])){
		include_once ($ucurl);
		include_once (HDWIKI_ROOT."/plugins/ucenter/uc_client/client.php");
	}else{
		define("UC_OPEN",false);
	}'),
		array(
			'available'=>"1",
			'title'=>'register',
			'description'=>'用户注册同步注册到ucenter。',
			'code'=>'
				if(UC_OPEN){
					$this->loadplugin("ucenter");
					$uid = uc_user_register($username,$password,$email);
					if($uid <= 0) {
						if($uid == -1) {
							$msg= "用户名不合法";
						} elseif($uid == -2) {
							$msg= "包含要允许注册的词语";
						} elseif($uid == -3) {
							$msg= "用户名已经存在";
						} elseif($uid == -4) {
							$msg= "Email 格式有误";
						} elseif($uid == -5) {
							$msg= "Email 不允许注册";
						} elseif($uid == -6) {
							$msg= "该 Email 已经被注册";
						} else {
							$msg= "未定义";
						}
						$this->message($msg,"BACK",0);
					} else {
						$result=$_ENV["user"]->add_user($username, md5($password), $email,$this->time,$this->ip,2,$uid);
						$_ENV["user"]->refresh_user($result);
						$_ENV["user"]->add_credit($this->user["uid"],"user-register",$this->setting["credit_register"]);
						$synlogin=uc_user_synlogin($uid);
						$this->message("恭喜 <b>$username</b> 注册成功！".$synlogin,"index.php",0);
					}
				}
				'),
		array(
			'available'=>"1",
			'title'=>'login',
			'description'=>'UC用户登录代码',
			'code'=>'
				if(UC_OPEN){
				list($uid, $username, $password, $email) = uc_user_login($username, $this->post["password"]);
				if($uid > 0) {
					$msg= "OK";
					$synlogin=uc_user_synlogin($uid);
				} elseif($uid == -1) {
					$msg= "用户不存在,或者被删除";
				} elseif($uid == -2) {
					$msg= "密码错";
				}elseif($uid == -3) {
					$msg= "安全提问错";
				} else {
					$msg= "不明错误，可能是连接UC服务端失败了。";
				}
				if($msg != "OK") {
					if ($this->post["submit"] == "ajax"){
						exit($msg);
					} else{
						$this->message($msg,"BACK",0);
					}
				}else{
					$user = $_ENV["user"]->get_user("username",$username);
					if(is_array($user) && $user["uid"]!=$uid){
						$this->loadplugin("ucenter");
						if($usernum=$this->db->result_first("select count(*) from ".DB_TABLEPRE."user where uid= $uid")){
							$maxuid=$this->db->result_first("select max(uid) from ".DB_TABLEPRE."user");
							$maxuid+=1;
							$this->db->query("update ".DB_TABLEPRE."user set uid=$maxuid where uid=$uid");
							$_ENV["ucenter"]->update_field($uid,$maxuid);
						}
						$_ENV["user"]->update_field("uid",$uid,$user["uid"],1);
						$_ENV["ucenter"]->update_field($user["uid"],$uid);
						$user["uid"]=$uid;
					}elseif(!is_array($user)){
						$this->loadplugin("ucenter");
						if($usernum=$this->db->result_first("select count(*) from ".DB_TABLEPRE."user where uid= $uid")){
							$maxuid=$this->db->result_first("select max(uid) from ".DB_TABLEPRE."user");
							$maxuid+=1;
							$this->db->query("update ".DB_TABLEPRE."user set uid=$maxuid where uid=$uid");
							$_ENV["ucenter"]->update_field($uid,$maxuid);
						}
						$_ENV["user"]->add_user($username, md5($password), $email,$this->time,$this->ip,2,$uid);
						$_ENV["user"]->add_credit($uid,"user-register",$this->setting["credit_register"]);
						$user = $_ENV["user"]->get_user("username",$username);
					}else{
						$lasttime=$user["lasttime"];
						if($this->time>($lasttime+24*3600)){
							$_ENV["user"]->add_credit($user["uid"],"user-login",$this->setting["credit_login"]);
						}
						//修改用户的资料。
						$_ENV["user"]->edit_user($user["uid"],$password,$email,$user["groupid"]);
						$_ENV["user"]->update_user($user["uid"],$this->time,$this->ip);
					}
					$_ENV["user"]->refresh_user($user["uid"]);
					$this->view->assign("adminlogin",$this->checkable("admin_main-login"));

					if ($this->post["submit"] == "ajax"){
						echo $synlogin;
						exit;
					} else{
						$this->message("登录成功".$synlogin,"index.php",0);
					}
				}
			}
			'),
		array(
			'available'=>"1",
			'title'=>'checkname',
			'description'=>'UC端检测用户名是否可用',
			'code'=>'
				if(UC_OPEN){
				$uid = uc_user_checkname($username);
					if($uid <= 0) {
						if($uid == -1) {
							$msg= "用户名不合法";
						} elseif($uid == -2) {
							$msg=  "包含不允许注册的词语";
						} elseif($uid == -3) {
							if($type>0){
								$msg=  "用户名已经存在";
							}elseif($type==0){
								$msg=  "OK";
							}
						}
					} else {
						if($type>0){
							$msg= "OK";
						}elseif($type==0){
							$msg= "用户名不存在";
						}
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'checkemail',
			'description'=>'检测UCenter端的邮箱。',
			'code'=>'
				if(UC_OPEN){
					$ucresult = uc_user_checkemail($email);
					if($ucresult == -4) {
						$msg= "Email 格式有误";
					} elseif($ucresult == -5) {
						$msg="Email 不允许注册";
					} elseif($ucresult == -6) {
						$msg="该 Email 已经被注册";
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'logout',
			'description'=>'UC用户注销',
			'code'=>'
			if(UC_OPEN){
				$synlogout=uc_user_synlogout();
			}
		'),
		array(
			'available'=>"1",
			'title'=>'editpass',
			'description'=>'UC修改密码',
			'code'=>'if(UC_OPEN){
							$userarr = $_ENV["user"]->get_user("uid",$this->user["uid"]);
							$username=$userarr["username"];
							$ucresult = uc_user_edit($username,$this->post["oldpass"],$newpass);
							if($ucresult == -1) {
								$this->message("旧密码不正确","BACK",0);
							} elseif($ucresult == 1) {
								$msg = "OK";
							} else{
								$this->message("某些原因出错了，没做任何修改","BACK",0);
							}
						}'),
		array(
			'available'=>"1",
			'title'=>'getpass',
			'description'=>'UC用户找回密码时修改密码',
			'code'=>'
						if(UC_OPEN){
							$userarr = $_ENV["user"]->get_user("uid",$uid);
							$username=$userarr["username"];
							$ucresult = uc_user_edit($username,"",$newpass,"",1);
							if($ucresult == -1) {
								$this->message("旧密码不正确","BACK",0);
							} elseif($ucresult == 1) {
								$msg = "OK";
							}else{
								$this->message("某些原因出错了，没做任何修改","BACK",0);
							}
						}
					'),
		array(
			'available'=>"1",
			'title'=>'admin_register',
			'description'=>'UC添加用户（用于后台操作）',
			'code'=>'
				if(UC_OPEN){
					$username=$this->post["username"];
					$password=$this->post["password"];
					$email=$this->post["email"];
					$uid = uc_user_register($username,$password,$email);
					if($uid <= 0) {
						if($uid == -1) {
							$msg= "用户名不合法";
						} elseif($uid == -2) {
							$msg= "包含要允许注册的词语";
						} elseif($uid == -3) {
							$msg= "用户名已经存在";
						} elseif($uid == -4) {
							$msg= "Email 格式有误";
						} elseif($uid == -5) {
							$msg= "Email 不允许注册";
						} elseif($uid == -6) {
							$msg= "该 Email 已经被注册";
						} else {
							$msg= "未定义";
						}
						$this->message($msg,"BACK",0);
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'edituser',
			'description'=>'UC编辑用户（后台操作）',
			'code'=>'
				if(UC_OPEN){
					$userarr = $_ENV["user"]->get_user("uid",$uid);
					$username=$userarr["username"];
					$newpass=$this->post["password"];
					$email=$this->post["email"];
					$ismail=$userarr["email"]!=$email?1:0;
					if((bool)$newpass && $ismail){
						$ucresult = uc_user_edit($username,"",$newpass,$email,1);
					}elseif((bool)$newpass && !$ismail){
						$ucresult = uc_user_edit($username,"",$newpass,"",1);
					}elseif($ismail){
						$ucresult = uc_user_edit($username,"","",$email,1);
					}else{
						$ucresult = 1;
					}
					if($ucresult == 1) {
						$msg= "OK";
					} elseif($ucresult == 0) {
						$msg= "新旧资料相同，操作完成。";
					} elseif($ucresult == -4) {
						$msg= "Email 格式有误";
					} elseif($ucresult == -5) {
						$msg= "Email 不允许注册";
					} elseif($ucresult == -6) {
						$msg= "该 Email 已经被注册";
					}else {
						$msg= "UCenter更新失败";
					}
					if($msg != "OK"){
						$this->message($msg,"BACK");
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'delete',
			'description'=>'UC删除用户（后台操作）',
			'code'=>'
				if(UC_OPEN){
					foreach($this->post["uid"] as $uid){
					$userarr = $_ENV["user"]->get_user("uid",$uid);
					$username=$userarr["username"];
					uc_user_delete($username);
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'iscredit',
			'description'=>'判断是否开启积分',
			'code'=>'
				if(UC_OPEN){
					$outextcredits=unserialize($this->setting["outextcredits"]);
					if((bool)$outextcredits){
						$this->view->assign("iscredit",true);
					}
				}
			'),
		array(
			'available'=>"1",
			'title'=>'create_feed',
			'description'=>'UC创建词条feed',
			'code'=>'
		if(UC_OPEN){
			$isimg=util::getfirstimg($doc[content]);
			if(false!==strpos($isimg,"http://")){
				$img=empty($isimg)?"":$isimg;
			}else{
				$img=empty($isimg)?"":$this->setting[site_url]."/".$isimg;
			}
			$doc[did]=$did;
			$feed=unserialize($this->setting[feed]);
			$uid=$this->user[uid];
			if(@in_array("create",$feed)){
				$this->loadplugin("ucenter");
				$feed = array();
				$feed["icon"] = "post";
				$feed["type"] = "create";
				$feed["title_data"] = array(
					"title" => "<a href=\\\"{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}\\\">$doc[title]</a>",
					"author" => "<a href=\\\"space.php?uid={$uid}\\\">$this->user[username]</a>",
					"app"=>$this->setting["site_name"]
				);
				$feed["body_data"] = array(
					"subject"=> "<a href=\\\"{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}\\\">$doc[title]</a>",
					"message"=> $doc["summary"]
				);
				$feed["images"][]= array("url" => "{$img}", "link" => "{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}");
				$_ENV["ucenter"]->postfeed($feed);
			}
		}'),
		array(
			'available'=>"1",
			'title'=>'edit_feed',
			'description'=>'UC编辑词条feed',
			'code'=>'
				if(UC_OPEN){
					$isimg=util::getfirstimg($doc[content]);
					if(false!==strpos($isimg,"http://")){
						$img=empty($isimg)?"":$isimg;
					}else{
						$img=empty($isimg)?"":$this->setting[site_url]."/".$isimg;
					}
					$feed=unserialize($this->setting[feed]);
					$uid=$this->user[uid];
					if(@in_array("edit",$feed)){
							$this->loadplugin("ucenter");
							$feed = array();
							$feed["icon"] = "post";
							$feed["type"] = "edit";
							$feed["title_data"] = array(
								"title" => "<a href=\\\"{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}\\\">$doc[title]</a>",
								"author" => "<a href=\\\"space.php?uid={$uid}\\\">$this->user[username]</a>",
								"app"=>$this->setting["site_name"]
							);
							$feed["body_data"] = array(
								"subject"=> "<a href=\\\"{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}\\\">$doc[title]</a>",
								"message"=> $doc["summary"]
							);
							$feed["images"][]= array("url" => "{$img}", "link" => "{$this->setting[site_url]}/{$this->setting[seo_prefix]}doc-view-$doc[did]{$this->setting[seo_suffix]}");
							$_ENV["ucenter"]->postfeed($feed);
					}
				}'),
		array(
			'available'=>"1",
			'title'=>'doc_user_image',
			'description'=>'UC头像在doc文件中调用',
			'code'=>'
			if(UC_OPEN){
				if(uc_check_avatar($editors[$doc["author"]][uid])){
					$editors[$doc["author"]]["image"]=UC_API."/avatar.php?uid=".$editors[$doc["author"]][uid]."&size=small";
				}
				if(uc_check_avatar($editors[$doc["lasteditor"]][uid])){
					$editors[$doc["lasteditor"]]["image"]=UC_API."/avatar.php?uid=".$editors[$doc["lasteditor"]][uid]."&size=small";
				}
		}'),
		array(
			'available'=>"1",
			'title'=>'base_user_image',
			'description'=>'UC头像在HDwiki的base文件中初始化。',
			'code'=>'
		if(UC_OPEN && uc_check_avatar($this->user["uid"])){
			$this->user["image"]=UC_API."/avatar.php?uid=".$this->user["uid"]."&size=middle";
		}'),
		array(
			'available'=>"1",
			'title'=>'edit_user_image',
			'description'=>'UC编辑头像',
			'code'=>'
		if(UC_OPEN){
			$image_html=uc_avatar($this->user["uid"]);
			if(uc_check_avatar($this->user["uid"])){
				$uid_image=UC_API."/avatar.php?uid=".$this->user["uid"]."&size=middle";
				$this->view->assign("uid_image",$uid_image);
			}
			$this->view->assign("image_html",$image_html);
		}'));

		$var=array(
			array('displayorder'=>"0",
			'title'=>'UCenter 的URL',
			'description'=>'填写UCenter的路径,结尾不加/',
			'variable'=>'ucapi',
			'type'=>'text',
			'value'=>'',
			'extra'=>''),
			array('displayorder'=>"1",
			'title'=>'UCenter IP地址',
			'description'=>'填写UCenter的IP地址（注意：一般不用填写，通信失败后可尝试填写。）',
			'variable'=>'ucip',
			'type'=>'text',
			'value'=>'',
			'extra'=>''),
			array('displayorder'=>"2",
			'title'=>'UCenter密码',
			'description'=>'UCenter 的创始人密码',
			'variable'=>'ucpassword',
			'type'=>'password',
			'value'=>'',
			'extra'=>'')
		);
		$plugin['hooks']=$hook;
		$plugin['vars']=$var;
		return $plugin;
	}

	/*插件变量修改后，调用的方法。*/
	function update($vars){
		$ucopen=1;
		$ucip=$vars['ucip'];
		$ucapi=$vars['ucapi'];
		$ucpassword=$vars['ucpassword'];

		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		$ucip = trim($ucip);

		if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
			return ('您输入的URL地址不正确！');
		}
		if(!$ucip) {
			$temp = @parse_url($ucapi);
			$ucip = gethostbyname($temp['host']);
			if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) {
				$ucip = '';
			}
		}
		define('UC_API',true);
		if(!@include_once HDWIKI_ROOT.'/plugins/ucenter/uc_client/client.php') {
			return('uc_client目录不存在，请上传安装包中的 ./upload/uc_client 到程序根目录');
		}

		$ucinfo = uc_fopen2($ucapi.'/index.php?m=app&a=ucinfo&a=ucinfo', 500, '', '', 1, $ucip);
		list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);

		$dbcharset = strtolower(trim(WIKI_CHARSET));
		$ucdbcharset = strtolower(trim(str_replace('-', '', $ucdbcharset)));
		$apptypes = strtolower(trim($apptypes));

		if($status != 'UC_STATUS_OK') {
			return('UCenter is not UC_STATUS_OK');
			exit();
		}

		$tagtemplates = '';
		$app_url=$this->base->setting['site_url'];
		$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucpassword)."&apptype=".urlencode('OTHER')."&appname=".urlencode('HDWIKI')."&appurl=".urlencode($app_url)."&appip=&appcharset=".$dbcharset.'&appdbcharset='.$dbcharset.'&'.$tagtemplates;
		$s = uc_fopen2($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
		if(empty($s)) {
			return('不能连接到UCenter服务端。');
			exit;
		} elseif($s == '-1') {
			return('UCenter密码错误。');
			exit;
		} else {
			$ucs = explode('|', $s);
			if(empty($ucs[0]) || empty($ucs[1])) {
				return('某些原因，不能返回数据。');
				exit;
			}
		}
		$ucdata="<?php
					define('UC_OPEN','$ucopen');
					define('UC_CONNECT', 'uc_api_post');

					define('UC_DBHOST', '$ucs[2]');
					define('UC_DBUSER', '$ucs[4]');
					define('UC_DBPW', '$ucs[5]');
					define('UC_DBNAME', '$ucs[3]');
					define('UC_DBCHARSET', '$ucs[6]');
					define('UC_DBTABLEPRE', '$ucs[7]');

					define('UC_KEY', '$ucs[0]');
					define('UC_API', '$ucapi');
					define('UC_CHARSET', '$ucs[8]');
					define('UC_IP', '$ucip');
					define('UC_APPID', '$ucs[1]');
					?>";
		$byte=file::writetofile(HDWIKI_ROOT.'/plugins/ucenter/ucconfig.inc.php',$ucdata);
		if($byte==0){
			return('不能写ucconfig.inc.php文件。');
			exit();
		}
		//避免数据库密码为明文。
		$this->db->query("UPDATE ".DB_TABLEPRE."pluginvar SET value=md5(value) WHERE  variable='ucpassword'");
		return(true);
	}

	/*插件必须具有的卸载方法*/
	function uninstall(){
		#这里要删除积分插到setting表中的记录
		$this->db->query("delete from ".DB_TABLEPRE."setting where variable in ('feed','outextcredits')");
		$hdir=HDWIKI_ROOT.'/api';
		if(is_dir($hdir)){
			file::removedir($hdir);
		}
	}

	#积分的控制，输入用户名和积分，发送到ucenter。
	function  send_credit($uid,$credit,$outextcredits){
		if((bool)$outextcredits){
			foreach($outextcredits as $outextcredit){
				$credit=intval($credit/$outextcredit["ratio"]);
				uc_credit_exchange_request($uid, $outextcredit["creditsrc"] , $outextcredit["creditdesc"] , $outextcredit["appiddesc"] , $credit);
				return $uid;
			}
		}
	}

	function postfeed($feed) {
		$feed['title_template'] = $feed['type']=='create' ? '<b>{actor} 在 {app} 创建了新词条</b>':'<b>{actor} 在 {app} 编辑了词条</b>';
		$feed['body_template'] = '<b>{subject}</b><br />{message}';
		uc_feed_add($feed['icon'], $this->base->user['uid'], $this->base->user['username'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', '', $feed['images']);
	}

	//这个方法是在uid同步的时候使用
	function update_field($uid,$newuid) {
		$this->db->query("UPDATE ".DB_TABLEPRE."activation SET uid='$newuid' WHERE uid='$uid'");
		$this->db->query("UPDATE ".DB_TABLEPRE."attachment SET uid='$newuid' WHERE uid='$uid'");
		$this->db->query("UPDATE ".DB_TABLEPRE."creditdetail SET uid='$newuid' WHERE uid='$uid'");
		$this->db->query("UPDATE ".DB_TABLEPRE."doc SET authorid='$newuid' WHERE authorid='$uid'");
		$this->db->query("UPDATE ".DB_TABLEPRE."edition SET authorid='$newuid' WHERE authorid='$uid'");
		$this->db->query("UPDATE ".DB_TABLEPRE."comment SET authorid='$newuid' WHERE authorid='$uid'");
	}
}
?>