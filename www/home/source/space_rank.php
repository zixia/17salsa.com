<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_rank.php 8427 2008-08-11 08:26:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(empty($_GET['view'])) $_GET['view'] ='show';

//缓存时间
$cachetime = 1800;//30分钟
$updatetime = 3600*24*2;//48小时

$datas = $fuids = $list = array();

$now_pos = 0;
$wheresql = $order = $join = '';
if($_GET['view'] == 'credit') {
	$order = 'credit';
	$cachename = "space_rank_credit";
	$join = "LEFT";
} elseif ($_GET['view'] == 'friendnum') {
	$order = 'friendnum';
	$cachename = "space_rank_friendnum";
	$join = "LEFT";
} elseif ($_GET['view'] == 'show') {
	//分页
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	$need_clean = 0;
	$i = $start;
	$count = getcount('show', array());
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT f.sex, main.* FROM ".tname('show')." main
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid
			WHERE 1
			ORDER BY main.credit DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$i++;
			realname_set($value['uid'], $value['username']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$fuids[] = $value['uid'];
			$value['i'] = $i;
			if(empty($need_clean) && empty($value['credit'])) $need_clean = 1;
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, "space.php?do=rank&view=show");
	}
	
	//清理
	if($need_clean) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('show')." WHERE credit<1");//清理小于1的数据
	}
	
	//我的竞价积分
	$space['showcredit'] = getcount('show', array('uid'=>$space['uid']), 'credit');
	$space['showcredit'] = intval($space['showcredit']);
	
	//我的位置
	if($space['showcredit']) {
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('show')." WHERE credit>'$space[showcredit]'"), 0);
	} else {
		$now_pos = 0;
	}
	
} else {
	$order = 'viewnum';
	if($_GET['view']=='gg') {
		$wheresql = " AND f.sex='1'";
		$cachename = "space_rank_viewnum_1";
	} else {
		$wheresql = " AND f.sex='2'";
		$cachename = "space_rank_viewnum_2";
	}
	$join = "INNER";
}

if($order) {
	$cachedata = data_get($cachename, 1);
	if(!empty($cachedata['dateline']) && $_SGLOBAL['timestamp'] - $cachedata['dateline'] < $cachetime) {
		//缓存
		$datas = unserialize($cachedata['datavalue']);
		extract($datas);
	
	} else {
		$i=1;
		$query = $_SGLOBAL['db']->query("SELECT s.uid, s.username, s.name, s.namestatus, s.credit, s.viewnum, s.friendnum, f.resideprovince, f.residecity, f.note, f.sex
			FROM ".tname('space')." s
			$join JOIN ".tname('spacefield')." f ON f.uid=s.uid $wheresql
			WHERE s.updatetime>'".($_SGLOBAL['timestamp']-$updatetime)."'
			ORDER BY s.{$order} DESC
			LIMIT 0,50");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$value['i'] = $i;
			$fuids[] = $value['uid'];
			$list[] = $value;
			$i++;
		}
		
		//缓存
		$datas['fuids'] = $fuids;
		$datas['list'] = $list;
		
		data_set($cachename, $datas);
	}
	
	//我的位置
	$c_pos = 0;
	$have_pos = 0;
	foreach ($list as $value) {
		if($value['uid'] == $_SGLOBAL['supe_uid']) {
			$have_pos = 1;
			break;
		}
		$c_pos++;
	}
	
	if(!$have_pos) {
		$sql = '';
		if($_GET['view']=='gg') {
			if($space['sex']==1) {
				$sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE f.uid=s.uid AND f.sex='1' AND s.updatetime>'".($_SGLOBAL['timestamp']-$updatetime)."' AND s.viewnum>'$space[viewnum]'";
			}
		} elseif($_GET['view']=='mm') {
			if($space['sex']==2) {
				$sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE f.uid=s.uid AND f.sex='2' AND s.updatetime>'".($_SGLOBAL['timestamp']-$updatetime)."' AND s.viewnum>'$space[viewnum]'";
			}
		} elseif($_GET['view']=='friendnum' || $_GET['view']=='credit') {
			$sql = "SELECT COUNT(*) FROM ".tname('space')." WHERE updatetime>'".($_SGLOBAL['timestamp']-$updatetime)."' AND $order>'".$space[$order]."'";
		}
		if($sql) {
			$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($sql), 0);
		} else {
			$now_pos = -1;
		}
	} else {
		$now_pos = $c_pos;
	}
}

$now_pos = $now_pos+1;

//实名
foreach ($list as $key => $value) {
	$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
	realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
	$list[$key] = $value;
}

//在线状态
if($fuids) {
	$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$ols[$value['uid']] = $value['lastactivity'];
	}
}

//更新的空间
$updatelist = array();
if($_GET['view']!='show') {
	$query = $_SGLOBAL['db']->query("SELECT s.uid, s.username, s.name, s.namestatus, s.credit, s.viewnum, s.friendnum, s.updatetime
		FROM ".tname('space')." s
		ORDER BY s.updatetime DESC
		LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
		$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
		$updatelist[] = $value;
	}
}

realname_get();

$actives = array($_GET['view'] => ' class="active"');

include template('space_rank');

?>