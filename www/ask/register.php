<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

define('CURSCRIPT', 'register');
require './include/common.inc.php';
require_once CYASK_ROOT.'./uc_client/client.php';

$url=empty($_GET['url']) ? $_POST['url'] : $_GET['url'];
if($command=='registed')
{
	if($cyask_uid)
	{
		show_message('login_succeed_member', $url);
	}

	$query = $dblink->query("SELECT count(*) FROM {$tablepre}member WHERE regip='$onlineip'");
	$usernum = $dblink->result($query,0);
	if($usernum >= $count_ip_register)
	{
		show_message('regist_ip_used', '');
    }
	
	if(check_submit($_POST['registsubmit'], $_POST['formhash']))
	{
		$cyask_user = trim($_POST['username']);
		$cyask_user = strtolower($cyask_user);
		$cyask_email = trim($_POST['email']);
		
		$email_ok = uc_user_checkemail($cyask_email);
		if(!$email_ok)
		{
			show_message('regist_email_error_'.abs($email_ok), '');
		}
		
		$usernum = uc_user_checkname($cyask_user);

		if($usernum == 1)
		{
			$password = $_POST['password'];
			
			$cyask_uid = uc_user_register($cyask_user,$password,$cyask_email);
			if($cyask_uid)
			{
				list($cyask_uid,$cyask_uname,$cyask_pw,$cyask_email) = uc_user_login($cyask_user,$password);
				
				$cyask_pw = md5(time().rand(100000, 999999));
				$sql="INSERT INTO {$tablepre}member SET uid='$cyask_uid',username='$cyask_uname',password='$cyask_pw',email='$cyask_email',adminid='5',regip='$onlineip',lastlogin='$timestamp'";
				$dblink->query($sql);
			
				update_score($cyask_uid, $score_register, '+');
				
				$cookietime = 86400*30;
				set_cookie('compound', authcode("$cyask_uid\t$cyask_uname\t$cyask_pw", 'ENCODE', $cyask_key), $cookietime);
				$syninfo=uc_user_synlogin($cyask_uid);
				echo $syninfo;
				show_message('regist_succeed', $url);
			}
			else
			{
				show_message('regist_error', '');
			}
		}
		elseif($usernum == -3)
		{
			show_message('regist_name_used', '');
        }
        elseif($usernum == -1 || $usernum == -2)
		{
			show_message('regist_name_error', '');
        }
	}
	else
	{
		exit("url error");
	}
}
else if($command == 'getpw')
{
	if($cyask_uid)
	{
		show_message('login_succeed_member', './');
	}
	if(check_submit($_POST['getpwsubmit'], $_POST['formhash']))
	{
		$email=trim($_POST['email']);
		
		$query=$dblink->query("SELECT uid,username FROM {$tablepre}member WHERE email='$email'");
		$usernum=$dblink->num_rows($query);
		if($usernum)
		{
			$member=$dblink->fetch_array($query);
			$idstring = rand_code(6);
			$dblink->query("UPDATE {$tablepre}member SET authstr='$timestamp\t1\t$idstring' WHERE uid='$member[uid]'");
			
			$get_passwd_subject='找回“'.$site_name.'”登录密码';
			$get_passwd_message=$member[username].' ，你好，您正在使用“'.$site_name.'”密码找回功能。
----------------------------------------------------------------------<br />

您需要在三天之内，通过点击下面的链接重置您的密码：<br />

http://www.cyask.com/getpw.php?uid='.$member[uid].'&id='.$idstring.'

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)<br />

上面的页面打开后，输入新的密码后提交，即可使用新的密码登录了。您可以在个人空间中随时修改您的密码。<br />

本请求提交者的 IP 为 '.$onlineip.' &nbsp;时间为 '.date("Y年n月j日 H时i分").'<br />
<br />
'.$site_name;

			sendmail($member['email'],$get_passwd_subject,$get_passwd_message);

			show_message('getpw_send_succeed', './');
			exit;
		}
		else
		{
			show_message('email_not_exist', '');
        }
	}
	else
	{
		include template('getpw');
	}
}
else
{
	include template('register');
}
?>