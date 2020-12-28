<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_index.php 10498 2008-12-05 08:04:15Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$network = array();

$cachelost = true;
$writestate = $_SCONFIG['networkupdate'] ? true : false;
if($_SCONFIG['networkupdate']) {
	$cachefile = S_ROOT.'./data/data_network.php';
	$lockfile = S_ROOT.'./data/data_network.lock';

	if(file_exists($cachefile)) {
		@$mktime = filemtime($cachefile);
		if($_SGLOBAL['timestamp'] - $mktime > $_SCONFIG['networkupdate']) {
			if(file_exists($lockfile)) {
				$writestate = false;
				@touch($lockfile);
			}
			
		} else {
			include_once($cachefile);
			$cachelost = false;
		}
	}
}
if($cachelost) {

	//���뻺������
	@include_once(S_ROOT.'./data/data_network_setting.php');
	$sql = '';
	//��Ա�б�
	$netcache['spacelist'] = array();
	if(empty($network['space'])) {
		$sql = " ORDER BY updatetime DESC LIMIT 0,12";
	} else {
		eval("\$network['space'] = \"$network[space]\";");
		$sql = ' '.trim($network['space']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$netcache['spacelist'][] = $value;
	}
	
	//��¼
	$netcache['doinglist'] = array();
	if(empty($network['doing'])) {
		$sql = " ORDER BY d.dateline DESC LIMIT 0,6";
	} else {
		eval("\$network['doing'] = \"$network[doing]\";");
		$sql = ' '.trim($network['doing']);
	}
	$query = $_SGLOBAL['db']->query("SELECT d.* FROM ".tname('doing').' d '.$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$netcache['doinglist'][] = $value;
	}
	
	//���˷���
	$netcache['sharelist'] = array();
	if(empty($network['share'])) {
		$sql = " ORDER BY sh.dateline DESC LIMIT 0,10";
	} else {
		eval("\$network['share'] = \"$network[share]\";");
		$sql = ' '.trim($network['share']);
	}
	$query = $_SGLOBAL['db']->query("SELECT sh.* FROM ".tname('share').' sh '.$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value = mkshare($value);
		$netcache['sharelist'][] = $value;
	}
	
	//��־
	$netcache['bloglist'] = array();
	if(empty($network['blog'])) {
		$sql = " b.friend='0' ORDER BY b.dateline DESC LIMIT 0,10";
	} else {
		eval("\$network['blog'] = \"$network[blog]\";");
		$sql = ' '.trim($network['blog']);
	}
	$blogfrom = isset($network['blogfrom']) ? $network['blogfrom'] : '';
	$query = $_SGLOBAL['db']->query("SELECT b.blogid, b.subject, b.uid, b.username, b.dateline, b.viewnum, b.replynum, b.friend, bf.message 
				FROM $blogfrom ".tname('blog')." b 
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE ".$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			realname_set($value['uid'], $value['username']);
			$value['message'] = $value['friend']==4?'':getstr($value['message'], 150, 0, 0, 0, 0, -1);
			$netcache['bloglist'][] = $value;
		}
	}
	
	//���
	$netcache['albumlist'] = array();
	if(empty($network['album'])) {
		$sql = " WHERE a.friend='0' AND a.picnum>0 ORDER BY a.updatetime DESC LIMIT 0,10";
	} else {
		eval("\$network['album'] = \"$network[album]\";");
		$sql = ' '.trim($network['album']);
	}
	$query = $_SGLOBAL['db']->query("SELECT a.* FROM ".tname('album').' a '.$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			realname_set($value['uid'], $value['username']);
			$value['pic'] = mkpicurl($value);
			$netcache['albumlist'][] = $value;
		}
	}
	
	//����Ⱥ��
	$netcache['mtaglist'] = array();
	if(empty($network['mtag'])) {
		$sql = " ORDER BY membernum DESC LIMIT 0,6";
	} else {
		eval("\$network['mtag'] = \"$network[mtag]\";");
		$sql = ' '.trim($network['mtag']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$netcache['mtaglist'][] = $value;
	}
	
	//�õ�ͼƬ
	$netcache['piclist'] = array();
	if(empty($network['slide'])) {
		$sql = ",".tname('album')." a WHERE a.friend='0' AND a.albumid=p.albumid ORDER BY p.dateline DESC LIMIT 0,5";
	} else {
		eval("\$network['slide'] = \"$network[slide]\";");
		$sql = ' '.trim($network['slide']);
	}
	$query = $_SGLOBAL['db']->query("SELECT p.* FROM ".tname('pic')." p $sql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['pic'] = mkpicurl($value, 0);
		$value['title'] = getstr($value['title'], 50, 0, 1, 0, 0, -1);
		$netcache['piclist'][] = $value;
	}
	
	realname_get();
	
	$netcache['SN'] = $_SN;
	
	//д�������
	if($writestate) {
		include_once(S_ROOT.'./source/function_cache.php');
		cache_write('network', "netcache", $netcache);
		@unlink($lockfile);
	}
}

if($netcache['mtaglist']) {
	@include_once(S_ROOT.'./data/data_profield.php');
}

$_SN = $netcache['SN'];

//����
@include_once(S_ROOT.'./data/data_task.php');
include_once(S_ROOT.'./source/function_space.php');
if($task = gettask()) {
	$task['note'] = getstr($task['note'], 100, 0, 0, 0, 0, -1);
}

//��ҵ����¶�̬
$feedlist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE friend='0' ORDER BY dateline DESC LIMIT 0,10");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['uid'], $value['username']);
	$feedlist[] = $value;
}

//��ǰ����
$onlinelist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." ORDER BY lastactivity DESC LIMIT 0,42");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['uid'], $value['username']);
	$onlinelist[] = $value;
}

//��������
$olcount = getcount('session', array());
$onlinehold = intval($_SCONFIG['onlinehold']/60);

//��������
$showlist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('show')." ORDER BY credit DESC LIMIT 0,14");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['uid'], $value['username']);
	$value['note'] = addslashes(getstr($value['note'], 80, 0, 0, 0, 0, -1));
	$showlist[] = $value;
}

//�����Ӧ��
$myapplist = array();
if($_SCONFIG['my_status']) {
	$query = $_SGLOBAL['db']->query("SELECT appid,appname FROM ".tname('myapp')." WHERE flag>=0 ORDER BY flag DESC, displayorder LIMIT 0,12");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$myapplist[] = $value;
	}
}

//վ���Ƽ���Ա
$barlist = array();
if($_SCONFIG['spacebarusername']) {
	$query = $_SGLOBAL['db']->query("SELECT uid,username,name,namestatus FROM ".tname('space')." WHERE username IN (".simplode(explode(',', $_SCONFIG['spacebarusername'])).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
		$barlist[] = $value;
	}
}
	
//��ȡʵ��
realname_get();

//��ʽ����̬
foreach ($feedlist as $key => $value) {
	$feedlist[$key] = mkfeed($value);
}

?>