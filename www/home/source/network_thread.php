<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_thread.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

//初始化
$gets = $list = array();
$multi = '';

if(!empty($_GET['searchmode'])) {
	//需要登录
	checklogin();
	
	//判断是否搜索太快
	$waittime = interval_check('search');
	if($waittime > 0) {
		showmessage('search_short_interval');
	}
	
	$gets['key'] = empty($_GET['key'])?'':(stripsearchkey($_GET['key'])?$_GET['key']:'');
	$gets['tagid'] = empty($_GET['tagid'])?'':intval($_GET['tagid']);
	$gets['username'] = empty($_GET['username'])?'':(stripsearchkey($_GET['username'])?$_GET['username']:'');
	$_GET['starttime'] = empty($_GET['starttime'])?'':sstrtotime($_GET['starttime']);
	if($_GET['starttime']) $gets['starttime'] = sgmdate('Y-m-d', $_GET['starttime']);
	$_GET['endtime'] = empty($_GET['endtime'])?'':sstrtotime($_GET['endtime']);
	if($_GET['endtime']) $gets['endtime'] = sgmdate('Y-m-d', $_GET['endtime']);
	if($gets['endtime'] && $gets['endtime'] <= $gets['starttime']) {
		$gets['starttime'] = $gets['endtime'] = '';
	}
	
	//搜索积分
	cksearchcredit($ac);
	
	//开始搜索
	$wherearr = array();
	if($gets['username']) {
		$wherearr[] = "main.username = '$gets[username]'";
	}
	if($gets['tagid']) {
		$wherearr[] = "main.tagid = '$gets[tagid]'";
	}
	if($value = sstrtotime($gets['starttime'])) {
		$wherearr[] = "main.dateline >= '$value'";
	}
	if($value = sstrtotime($gets['endtime'])) {
		$wherearr[] = "main.dateline <= '$value'";
	}
	//关键字
	if($inkey = stripsearchkey($gets['key'])) {
		if(preg_match("/( AND |\+|&|\s)/i", $inkey) && !preg_match("/( OR |\|)/i", $inkey)) {
			$keys = preg_replace("/( AND |&| )/i", "+", $inkey);
			$andor = ' AND ';
		} else {
			$keys = preg_replace("/( OR |\|)/i", "+", $inkey);
			$andor = ' OR ';
		}
		$is = array();
		foreach (explode('+', $keys) as $value) {
			if($value = trim($value)) {
				$is[] = "main.subject LIKE '%$value%'";
			}
		}
		if($is) {
			$wherearr[] = '('.implode($andor, $is).')';
		}
	}
	if(empty($wherearr)) {
		showmessage('set_the_correct_search_content');
	}

	$query = $_SGLOBAL['db']->query("SELECT main.*,mtag.tagname,mtag.membernum,mtag.fieldid 
		FROM ".tname('thread')." main, ".tname('mtag')." mtag
		WHERE ".implode(' AND ', $wherearr)." AND mtag.tagid=main.tagid AND mtag.viewperm='0'
		ORDER BY main.dateline DESC LIMIT 0, 100");//最多100条
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$list[] = $value;
	}
	
	//更新最后操作时间
	updatespacestatus('pay', 'search');
	
} else {
	//分页
	$perpage = 50;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);

	//处理查询
	$tagids = array();
	$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." USE INDEX(lastpost) 
			ORDER BY lastpost DESC 
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			realname_set($value['lastauthorid'], $value['lastauthor']);
			$list[] = $value;
			$tagids[$value['tagid']] = $value['tagid'];
		}
	}
	$mtags = array();
	if($tagids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$mtags[$value['tagid']] = $value;
		}
	}
	
	//分页
	$multi = empty($_SCONFIG['networkpage'])?'networkpage':multi($count, $perpage, $page, $theurl);
}

realname_get();

$_GET = shtmlspecialchars(sstripslashes($_GET));

?>