<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_guide.php 12233 2009-05-26 01:17:46Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$step = empty($_GET['step'])?1:intval($_GET['step']);
if(!in_array($step, array(1,2,3,4,5))) $step = 1;

$actives = array($step => ' class="active"');
$_SGLOBAL['guidemode'] = 1;

if($step == 1) {
	include_once(S_ROOT.'./source/cp_avatar.php');
} elseif($step == 2) {
	include_once(S_ROOT.'./source/cp_profile.php');
} elseif($step == 3) {
	//找好友
	$_GET['op'] = 'find';
	include_once(S_ROOT.'./source/cp_friend.php');
} elseif($step == 4) {
	//关闭向导
	updatetable('space', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('do_success', 'space.php?do=home&view=all', 0);
}

?>