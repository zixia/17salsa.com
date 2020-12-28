<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_profile.php 11150 2009-02-20 01:35:59Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

$_GET['op'] = trim($_GET['op']) ? $_GET['op'] : ''; 
$email = '';

if(empty($_SGLOBAL['email'])) {
	
	include_once(S_ROOT.'./uc_client/client.php');
	$userinfo = uc_get_user($_SGLOBAL['supe_username']);
	$email = $userinfo[2];

} else {
	$email = $_SGLOBAL['email'];
}

if (submitcheck('updateemailvalue')) {
	
	$_POST['email'] = isemail($_POST['email'])?$_POST['email']:'';
	if(empty($_POST['email'])) {
		showmessage('email_error');
	}
	if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['password'])) {
		showmessage('password_is_not_passed');
	}
	//更新资料
	updatetable('members', array('email'=>$_POST['email']), array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('email_change_success', S_URL.'/'.$theurl);
	
} 

include template('admin/tpl/profile.htm', 1);
?>