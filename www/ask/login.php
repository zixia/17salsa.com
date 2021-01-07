<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/
define('CURSCRIPT', 'login');
require './include/common.inc.php';
require_once CYASK_ROOT.'./uc_client/client.php';

$url=empty($_GET['url']) ? $_POST['url'] : $_GET['url'];
if($command=='login')
{
	if($cyask_uid)
	{
		$url=empty($url) ? './' : $url;
		show_message('login_succeed', $url);
	}
	
	if(check_submit($_POST['loginsubmit'], $_POST['formhash']))
	{
		$cyask_user = trim($_POST['username']);
		$cyask_user = daddslashes($cyask_user);
		$password = $_POST['password'];
		
		list($cyask_uid,$cyask_uname,$cyask_pw,$cyask_email)=uc_user_login($cyask_user,$password);
		if($cyask_uid > 0)
		{
			$query = $dblink->query("SELECT uid,password FROM {$tablepre}member WHERE username='$cyask_uname'");
			if($members = $dblink->fetch_array($query))
			{
				$cyask_pw = $members['password'];
				$dblink->query("UPDATE {$tablepre}member SET lastlogin='$timestamp' WHERE uid='$members[uid]'");
			}
			else
			{
				$cyask_pw = md5(time().rand(100000, 999999));
				$dblink->query("INSERT INTO {$tablepre}member SET uid='$cyask_uid',username='$cyask_uname',password='$cyask_pw',email='$cyask_email',adminid=5,lastlogin='$timestamp'");
				update_score($cyask_uid, $score_register, '+');
			}
			
			$url=empty($url) ? './' : $url;
			$cookietime = $_POST['cookietime'] ? 86400 * 30 : 0;
			set_cookie('compound', authcode("$cyask_uid\t$cyask_uname\t$cyask_pw", 'ENCODE', $cyask_key), $cookietime);
			set_cookie('styleid', $styleid, $cookietime);
			$syninfo=uc_user_synlogin($cyask_uid);
			echo $syninfo;
			show_message('login_succeed_member', $url);
		}
		else
		{
			$url='login.php?url='.$url;
			if($cyask_uid == -1)
			{
				show_message('login_invalid', $url);
			}
			else
			{
				show_message('login_password_error', $url);
			}
		}
	}
	else
	{
		exit(url_error);
	}
}
else if($command == 'logout')
{
	clear_cookies();
	
	$syninfo=uc_user_synlogout($cyask_uid);
	echo $syninfo;

	$url = empty($url) ? './' : $url;
	
	show_message('logout_succeed', $url);

}
else
{
	include template('login');
}
?>