<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_share.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//��ҳ
$perpage = 50;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;
if(empty($_SCONFIG['networkpage'])) $start = 0;

//����
if($_GET['type']) {
	$sub_actives = array('type_'.$_GET['type'] => ' class="active"');
	$wheresql = "type='$_GET[type]'";
} else {
	$wheresql = '1';
	$sub_actives = array('type_all' => ' class="active"');
}
	
//��鿪ʼ��
ckstart($start, $perpage);

//�����ѯ
$list = array();
$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('share')),0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." USE INDEX (dateline) WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value = mkshare($value);
		$list[] = $value;
	}
}

//��ҳ
$multi = empty($_SCONFIG['networkpage'])?'networkpage':multi($count, $perpage, $page, $theurl."&type=$_GET[type]");

realname_get();

$_GET = shtmlspecialchars(sstripslashes($_GET));

?>