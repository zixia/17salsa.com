<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_app.php 8307 2008-08-01 02:47:02Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$appid = empty($_GET['appid'])?'':intval($_GET['appid']);
$app = empty($_SGLOBAL['app'][$appid])?array():$_SGLOBAL['app'][$appid];
if(empty($app)) {
	showmessage('correct_choice_for_application_show');
} 
	
//分页
$perpage = 50;
$start = empty($_GET['start'])?0:intval($_GET['start']);
//检查开始数
ckstart($start, $perpage);

//处理查询
if(empty($space['feedfriend'])) {
	$wheresql = "uid='$space[uid]' AND appid='$appid'";
	$theurl = "space.php?uid=$space[uid]&do=app&appid=$appid&view=me";
	$f_index = '';
	$actives = array('me'=>' class="active"');
} else {
	$wheresql = "uid IN ($space[feedfriend]) AND appid='$appid'";
	$theurl = "space.php?uid=$space[uid]&do=app&appid=$appid";
	$f_index = 'FORCE INDEX(dateline)';
	$actives = array('we'=>' class="active"');
}

$list = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $f_index
	WHERE $wheresql
	ORDER BY dateline DESC
	LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if(ckfriend($value)) {
		$value = mkfeed($value);
		$list[] = $value;
	}
	$count++;
}

//分页
$multi = smulti($start, $perpage, $count, $theurl);

include_once template("space_app");

?>