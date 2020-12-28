<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_sitefeed.php 10118 2008-11-25 07:21:33Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managesitefeed')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('feedsubmit')) {
	$setarr = array(
		'title_template' => trim($_POST['title_template']),
		'body_template' => trim($_POST['body_template'])
	);
	if(empty($setarr['title_template']) && empty($setarr['body_template'])) {
		cpmessage('sitefeed_error');
	}
	
	$feedid = intval($_POST['feedid']);
	
	//时间问题
	$_POST['dateline'] = trim($_POST['dateline']);
	if($_POST['dateline']) {
		$newtimestamp = sstrtotime($_POST['dateline']);
		if($newtimestamp > $_SGLOBAL['timestamp']) {
			$_SGLOBAL['timestamp'] = $newtimestamp;
		}
	}
	
	if(empty($feedid)) {
		$_SGLOBAL['supe_uid'] = 0;
		
		include_once(S_ROOT.'./source/function_cp.php');
		feed_add('sitefeed',
			trim($_POST['title_template']),array(),
			trim($_POST['body_template']),array(),
			trim($_POST['body_general']),
			array(trim($_POST['image_1']),trim($_POST['image_2']),trim($_POST['image_3']),trim($_POST['image_4'])),
			array(trim($_POST['image_1_link']),trim($_POST['image_2_link']),trim($_POST['image_3_link']),trim($_POST['image_4_link']))
		);
	} else {
		$setarr['body_general'] = trim($_POST['body_general']);
		$setarr['image_1'] = trim($_POST['image_1']);
		$setarr['image_1_link'] = trim($_POST['image_1_link']);
		$setarr['image_2'] = trim($_POST['image_2']);
		$setarr['image_2_link'] = trim($_POST['image_2_link']);
		$setarr['image_3'] = trim($_POST['image_3']);
		$setarr['image_3_link'] = trim($_POST['image_3_link']);
		$setarr['image_4'] = trim($_POST['image_4']);
		$setarr['image_4_link'] = trim($_POST['image_4_link']);
		
		$setarr['dateline'] = $_SGLOBAL['timestamp'];
		
		updatetable('feed', $setarr, array('feedid'=>$feedid, 'uid'=>0));
	}
	cpmessage('do_success', 'admincp.php?ac=sitefeed', 0);
	
} elseif (submitcheck('deletesubmit')) {
	
	if($_POST['ids']) {
		$_SGLOBAL['usergroup'][$space['groupid']]['managefeed'] = 1;
		include_once(S_ROOT.'./source/function_delete.php');
		deletefeeds($_POST['ids']);
	}
	cpmessage('do_success', 'admincp.php?ac=sitefeed', 0);
}

if($_GET['op'] == 'add') {
	$feed = array();
	$feed['dateline'] = sgmdate('Y-m-d H:i', $_SGLOBAL['timestamp']);
	
} elseif($_GET['op'] == 'edit') {
	$_GET['feedid'] = intval($_GET['feedid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='0' AND feedid='$_GET[feedid]'");
	$feed = $_SGLOBAL['db']->fetch_array($query);
	$feed = shtmlspecialchars($feed);
	
	if($feed['dateline'] < $_SGLOBAL['timestamp']) $feed['dateline'] = $_SGLOBAL['timestamp'];
	$feed['dateline'] = sgmdate('Y-m-d H:i', $feed['dateline']);
} else {

	//浏览
	$perpage = 20;
	
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('feed')." WHERE uid='0'"), 0);
	
	$list = array();
	$multi = '';
	
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='0' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value = mkfeed($value);
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}
}

?>