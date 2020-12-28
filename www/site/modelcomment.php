<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: modelcomment.php 11674 2009-03-18 08:52:20Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/model.func.php');

$modelname = empty($_SGET['name']) ? trim(postget('name')) : trim($_SGET['name']);
$cacheinfo = getmodelinfoall('modelname', $modelname);
$categories = $cacheinfo['categories'];
if(empty($cacheinfo['models'])) {
	showmessage('visit_the_channel_does_not_exist', S_URL);
}
$modelsinfoarr = $cacheinfo['models'];

if(empty($modelsinfoarr['allowcomment'])) {
	showmessage('not_found', S_URL);
}

if(submitcheck('submitcomm', 1)) {
	$itemid = empty($_POST['itemid']) ? 0 : intval($_POST['itemid']);
	if(empty($itemid)) showmessage('not_found', S_URL);

	if(empty($_SGLOBAL['supe_uid'])) {
		if(empty($_SCONFIG['allowguest'])) {
			$referarr = array();
			$referquery = empty($_SERVER['HTTP_REFERER']) ? '' : parse_url($_SERVER['HTTP_REFERER']);
			$referquery = empty($referquery['query']) ? '' : $referquery['query'];
			if(!empty($referquery)) {
				$referquery = addslashes($referquery);
				$referarr = parseparameter(str_replace(array('-','_'), '/', $referquery));
			}

			if(!empty($referarr['action']) && $referarr['action'] == 'model') {
				setcookie('_refer', rawurlencode(geturl('action/model/name/'.$modelname.'/itemid/'.$itemid, 1)));
			} else {
				setcookie('_refer', rawurlencode(geturl('action/modelcomment/name/'.$modelname.'/itemid/'.$itemid, 1)));
			}
			showmessage('no_login', geturl('action/login'));
		}
	}
	
	if($_SGLOBAL['supe_uid']) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));	
	}

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelname.'items').' WHERE itemid=\''.$itemid.'\' AND allowreply=\'1\'');
	if(!$item = $_SGLOBAL['db']->fetch_array($query)) showmessage('no_permission', S_URL);

	$_POST['messagecomm'] = shtmlspecialchars(trim($_POST['messagecomm']));
	if(strlen($_POST['messagecomm']) < 2 || strlen($_POST['messagecomm']) > 10000) showmessage('message_length_error');
	$_POST['messagecomm'] = str_replace('[br]', '<br>', $_POST['messagecomm']);
	$_POST['messagecomm'] = preg_replace("/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is", "<blockquote class=\"xspace-quote\">\\1</blockquote>", $_POST['messagecomm']);
	
	$setsqlarr = array(
		'itemid' => $itemid,
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'ip' => $_SGLOBAL['onlineip'],
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $_POST['messagecomm']
	);
	if(!empty($modelsinfoarr['allowfilter'])) $setsqlarr = scensor($setsqlarr, 1);
	inserttable($modelname.'comments', $setsqlarr);

	$_SGLOBAL['db']->query('UPDATE '.tname($modelname.'items').' SET lastpost='.$_SGLOBAL['timestamp'].', replynum=replynum+1 WHERE itemid=\''.$itemid.'\'');
	if(allowfeed() && $_SGLOBAL['supe_uid'] != $item['uid'] && $item['uid'] != 0 && $_SGLOBAL['supe_uid'] != 0 && $_POST['addfeed']) {
		$feed['icon'] = 'post';
		$feed['title_template'] = 'feed_model_comment_title';
		$feed['title_data'] = array(
			'author' =>'<a href="space.php?uid='.$item['uid'].'" >'.$item['username'].'</a>',
			'modelname' => '<a href="'.S_URL_ALL.'/m.php?name='.$modelname.'" >'.$cacheinfo['models']['modelalias'].'</a>',
			'modelpost' =>'<a href="'.geturl('action/model/name/'.$modelname.'/itemid/'.$itemid, 1).'" >'.$item['subject'].'</a>'
		);
		postfeed($feed);
	}
	showmessage('do_success', geturl('action/modelcomment/name/'.$modelname.'/itemid/'.$itemid));
}

if(!empty($_SGET['op']) && $_SGET['op'] == 'delete') {
	$cid = empty($_SGET['cid']) ? 0 : intval($_SGET['cid']);
	if(empty($cid)) {
		showmessage('not_found', S_URL);
	}
	$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);
	if(empty($itemid)) {
		showmessage('not_found', S_URL);
	}

	$deleteflag = false;

	if(empty($_SGLOBAL['group'])) {
		showmessage('no_permission');
	}

	if($cid && $itemid && $_SGLOBAL['supe_uid']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelname.'comments').' WHERE cid=\''.$cid.'\'');
		if($comment = $_SGLOBAL['db']->fetch_array($query)) {
			if($_SGLOBAL['group']['groupid'] == 1 || $comment['authorid'] == $_SGLOBAL['supe_uid']) {
				$_SGLOBAL['db']->query('UPDATE '.tname($modelname.'items').' SET replynum=replynum-1 WHERE itemid=\''.$comment['itemid'].'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname($modelname.'comments').' WHERE cid=\''.$cid.'\'');
				$deleteflag = true;
			}
		}
	}
	if($deleteflag) {
		showmessage('do_success', geturl('action/modelcomment/name/'.$modelname.'/itemid/'.$itemid));
	} else {
		showmessage('no_permission');
	}
}

$perpage = !empty($modelsinfoarr['listperpage']) ? $modelsinfoarr['listperpage'] : 20;

$page = empty($_SGET['page']) ? 0 : intval($_SGET['page']);
$page = ($page<1)?1:$page;
$start = ($page-1)*$perpage;

$itemid = empty($_SGET['itemid'])?0:intval($_SGET['itemid']);
if(empty($itemid)) {
	showmessage('not_found', S_URL);
}

//导航
$channelsmore = array();
if(!empty($channels['menus']) && count($channels['menus']) > 12) {
	$channelsmore = $channels['menus'];
	for($i = 0; $i < 12; $i++) {
		array_shift($channelsmore);
	}
}

//自定义类别
if(!empty($cacheinfo['columns'])) {
	foreach($cacheinfo['columns'] as $tmpvalue) {
		if(!empty($tmpvalue['fielddata'])) {
			$temparr = explode("\r\n", $tmpvalue['fielddata']);
			if($tmpvalue['formtype'] != 'linkage') {
				$gatherarr[$tmpvalue['fieldname']] = $temparr;
			}
		}
	}
}

//投稿链接
$posturl = '';
if($modelsinfoarr['allowpost']) {
	$posturl = S_URL.'/admincp.php?action=modelmanages&op=add&mid='.$modelsinfoarr['mid'];
}

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelname.'items').' WHERE itemid=\''.$itemid.'\' AND allowreply=\'1\'');
if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
	showmessage('not_found', S_URL);
}

if(!empty($item['subjectimage'])) {
	$item['subjectimage'] = A_URL.'/'.$item['subjectimage'];
}

//分类
$guidearr = array();
$guidearr[] = array('url' => empty($channels['menus'][$modelsinfoarr['modelname']]) ? S_URL.'/m.php?name='.$modelsinfoarr['modelname'] : $channels['menus'][$modelsinfoarr['modelname']]['url'],'name' => $modelsinfoarr['modelalias']);
$guidearr[] = array('url' => geturl('action/model/name/'.$modelsinfoarr['modelname'].'/itemid/'.$itemid),'name' => $item['subject']);

$listcount = $item['replynum'];
$iarr = array();
$multipage = '';

if($listcount) {
	$i = ($page-1)*$perpage + 1;
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($modelname.'comments').' WHERE itemid=\''.$itemid.'\' ORDER BY dateline DESC LIMIT '.$start.','.$perpage);
	while ($comment = $_SGLOBAL['db']->fetch_array($query)) {
		$comment['message'] = snl2br($comment['message']);
		$comment['num'] = $i;
		$i++;
		if(empty($comment['author'])) $comment['author'] = 'Guest';
		$iarr[] = $comment;
	}
	$urlarr = array('action'=>'modelcomment', 'name' =>$modelname, 'itemid' => $itemid);
	$multipage = multi($listcount, $perpage, $page, $urlarr, 0);
}

$title = $item['subject'].' - '.$_SCONFIG['sitename'];
$keywords = !empty($modelsinfoarr['seokeywords']) ? $modelsinfoarr['seokeywords'] : $modelsinfoarr['modelalias'];
$description = !empty($modelsinfoarr['seodescription']) ? $modelsinfoarr['seodescription'] : $modelsinfoarr['modelalias'];
$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

if(allowfeed()) {
	$addfeedcheck = addfeedcheck(8) ? 'checked="checked"' : '';
}
//模板
if(empty($modelsinfoarr['tpl'])) {
	$tpldir = 'model/data/'.$modelsinfoarr['modelname'];
} else {
	$tpldir = 'mthemes/'.$modelsinfoarr['tpl'];
}

include template($tpldir.'/viewcomment.html.php', 1);
?>