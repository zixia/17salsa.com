<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_mtag.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

//��ʼ��
$gets = $list = array();
$multi = '';

if(!empty($_GET['searchmode'])) {
	//��Ҫ��¼
	checklogin();
	
	//�ж��Ƿ�����̫��
	$waittime = interval_check('search');
	if($waittime > 0) {
		showmessage('search_short_interval');
	}
	
	$gets['key'] = empty($_GET['key'])?'':(stripsearchkey($_GET['key'])?$_GET['key']:'');
	$gets['fieldid'] = empty($_GET['fieldid'])?0:intval($_GET['fieldid']);
	
	//��������
	cksearchcredit($ac);
	
	//��ʼ����
	$wherearr = array();
	if($gets['fieldid']) {
		$wherearr[] = "main.fieldid = '$gets[fieldid]'";
	}
	//�ؼ���
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
				$is[] = "main.tagname LIKE '%$value%'";
			}
		}
		if($is) {
			$wherearr[] = '('.implode($andor, $is).')';
		}
	}
	if(empty($wherearr)) {
		showmessage('set_the_correct_search_content');
	}

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." main WHERE ".implode(' AND ', $wherearr)." ORDER BY main.membernum DESC LIMIT 0, 100");//���100��
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$list[] = $value;
	}
	
	//����������ʱ��
	updatespacestatus('pay', 'search');
	
} else {
	//��ҳ
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//��鿪ʼ��
	ckstart($start, $perpage);
	
	//�����ѯ
	$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('mtag')),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." USE INDEX(membernum) ORDER BY membernum DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($value['pic'])) {
				$value['pic'] = 'image/nologo.jpg';
			}
			$tagids[$value['tagid']] = $value['tagid'];//�Ѿ���׼��Ⱥ��
			$list[] = $value;
		}
	}
	
	//��ҳ
	$multi = empty($_SCONFIG['networkpage'])?'networkpage':multi($count, $perpage, $page, $theurl."&view=$_GET[view]");
	
	//���»���
	$threadlist = array();
	$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid FROM ".tname('thread')." main
		USE INDEX (lastpost)
		LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid
		WHERE main.tagid IN (".simplode($tagids).")
		ORDER BY main.lastpost DESC
		LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$threadlist[] = $value;
	}
	
}

//Ⱥ��
$fieldids = array($gets['fieldid']=>' selected');

realname_get();

$_GET = shtmlspecialchars(sstripslashes($_GET));

?>