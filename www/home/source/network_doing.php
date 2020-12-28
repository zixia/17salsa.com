<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_doing.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 50;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;
if(empty($_SCONFIG['networkpage'])) $start = 0;

//检查开始数
ckstart($start, $perpage);

//处理查询
$list = array();
$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')),0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." USE INDEX (dateline) ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$list[] = $value;
	}
}

//分页
$multi = empty($_SCONFIG['networkpage'])?'networkpage':multi($count, $perpage, $page, $theurl);

realname_get();

$_GET = shtmlspecialchars(sstripslashes($_GET));

?>