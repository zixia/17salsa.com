<?php    if(!defined('IN_UCHOME') || !defined('qVh0gqGnK')) { exit('Access Denied'); } $minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin']; $page = empty($_GET['page'])?1:intval($_GET['page']); if($page<1) $page=1; $id = empty($_GET['id'])?0:intval($_GET['id']); $classid = empty($_GET['classid'])?0:intval($_GET['classid']);  
@include_once(S_ROOT.'./data/data_click.php'); $clicks = empty($_SGLOBAL['click']['blogid'])?array():$_SGLOBAL['click']['blogid']; if($id) {  
$query = $_SGLOBAL['db']->query("\123EL\x45\x43T bf.*, b.* \x46RO\x4d ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf \117N bf.blogid=b.blogid WHERE b.blogid='$id' AND b.uid='$space[uid]'"); $blog = $_SGLOBAL['db']->fetch_array($query);  
if(empty($blog)) { showmessage('view_to_info_did_not_exist'); }  
if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {  
include template('space_privacy'); exit(); } elseif(!$space['self'] && $blog['friend'] == 4) {  
$cookiename = "view_pwd_blog_$blog[blogid]"; $cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename]; if($cookievalue != md5(md5($blog['password']))) { $invalue = $blog; include template('do_inputpwd'); exit(); } }  
$blog['tag'] = empty($blog['tag'])?array():unserialize($blog['tag']);  

include_once(S_ROOT.'./source/function_blog.php'); 
$blog['message'] = blog_bbcode($blog['message']); 
$blog['message'] = thumb_replace($blog['message']);
$otherlist = $newlist = array();  

if($_SCONFIG['uc_tagrelatedtime'] && ($_SGLOBAL['timestamp'] - $blog['relatedtime'] > $_SCONFIG['uc_tagrelatedtime'])) { $blog['related'] = array(); } if($blog['tag'] && empty($blog['related'])) { @include_once(S_ROOT.'./data/data_tagtpl.php'); $b_tagids = $b_tags = $blog['related'] = array(); $tag_count = -1; foreach ($blog['tag'] as $key => $value) { $b_tags[] = $value; $b_tagids[] = $key; $tag_count++; } if(!empty($_SCONFIG['uc_tagrelated']) && $_SCONFIG['uc_status']) { if(!empty($_SGLOBAL['tagtpl']['limit'])) { include_once(S_ROOT.'./uc_client/client.php'); $tag_index = mt_rand(0, $tag_count); $blog['related'] = uc_tag_get($b_tags[$tag_index], $_SGLOBAL['tagtpl']['limit']); } } else {  
$tag_blogids = array(); $query = $_SGLOBAL['db']->query("\123E\x4c\x45\103\x54 DISTINCT blogid \106\x52O\x4d ".tname('tagblog')." WHERE tagid IN (".simplode($b_tagids).") AND blogid<>'$blog[blogid]' ORDER BY blogid DESC LIMIT 0,10"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $tag_blogids[] = $value['blogid']; } if($tag_blogids) { $query = $_SGLOBAL['db']->query("\x53\x45\114\x45\103T uid,username,subject,blogid F\x52OM ".tname('blog')." WHERE blogid IN (".simplode($tag_blogids).")"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['uid'], $value['username']); 
$value['url'] = "space.p\x68\x70?uid=$value[uid]&d\x6f=blog&id=$value[blogid]"; $blog['related'][UC_APPID]['data'][] = $value; } $blog['related'][UC_APPID]['type'] = 'UCHOME'; } } if(!empty($blog['related']) && is_array($blog['related'])) { foreach ($blog['related'] as $appid => $values) { if(!empty($values['data']) && $_SGLOBAL['tagtpl']['data'][$appid]['template']) { foreach ($values['data'] as $itemkey => $itemvalue) { if(!empty($itemvalue) && is_array($itemvalue)) { $searchs = $replaces = array(); foreach (array_keys($itemvalue) as $key) { $searchs[] = '{'.$key.'}'; $replaces[] = $itemvalue[$key]; } $blog['related'][$appid]['data'][$itemkey]['html'] = stripslashes(str_replace($searchs, $replaces, $_SGLOBAL['tagtpl']['data'][$appid]['template'])); } else { unset($blog['related'][$appid]['data'][$itemkey]); } } } else { $blog['related'][$appid]['data'] = ''; } if(empty($blog['related'][$appid]['data'])) { unset($blog['related'][$appid]); } } } updatetable('blogfield', array('related'=>addslashes(serialize(sstripslashes($blog['related']))), 'relatedtime'=>$_SGLOBAL['timestamp']), array('blogid'=>$blog['blogid'])); 
} else { $blog['related'] = empty($blog['related'])?array():unserialize($blog['related']); }  
$otherlist = array(); $query = $_SGLOBAL['db']->query("\x53\105LE\x43T * FR\117\x4d ".tname('blog')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,6"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) { $otherlist[] = $value; } }  
$newlist = array(); $query = $_SGLOBAL['db']->query("S\105\114\x45C\x54 * F\122OM ".tname('blog')." WHERE hot>=3 ORDER BY dateline DESC LIMIT 0,6"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) { realname_set($value['uid'], $value['username']); $newlist[] = $value; } }  
$perpage = 10; $perpage = mob_perpage($perpage); $start = ($page-1)*$perpage;  
ckstart($start, $perpage); $count = $blog['replynum']; $list = array(); if($count) { $cid = empty($_GET['cid'])?0:intval($_GET['cid']); $csql = $cid?"cid='$cid' AND":''; $query = $_SGLOBAL['db']->query("\123\105\x4c\105\x43\124 * \x46\122\x4f\x4d ".tname('comment')." WHERE $csql id='$id' AND idtype='blogid' ORDER BY dateline DESC LIMIT $start,$perpage"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['authorid'], $value['author']); 
$list[] = $value; } }  
$multi = multi($count, $perpage, $page, "space.\160\x68\160?uid=$blog[uid]&\144o=$do&id=$id", '', 'content');  
if(!$space['self'] && $_SCOOKIE['view_blogid'] != $blog['blogid']) { $_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET viewnum=viewnum+1 WHERE blogid='$blog[blogid]'"); inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid')); 
ssetcookie('view_blogid', $blog['blogid']); }  
$hash = md5($blog['uid']."\t".$blog['dateline']); $id = $blog['blogid']; $idtype = 'blogid'; foreach ($clicks as $key => $value) { $value['clicknum'] = $blog["click_$key"]; $value['classid'] = mt_rand(1, 4); if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum']; $clicks[$key] = $value; }  
$clickuserlist = array(); $query = $_SGLOBAL['db']->query("S\x45\114\x45\x43T * F\122OM ".tname('clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,18"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['uid'], $value['username']); 
$value['clickname'] = $clicks[$value['clickid']]['name']; $clickuserlist[] = $value; }  
$topic = topic_get($blog['topicid']);  
realname_get(); $_TPL['css'] = 'blog'; include_once template("space_blog_view"); } else {  
$perpage = 10; $perpage = mob_perpage($perpage); $start = ($page-1)*$perpage;  
ckstart($start, $perpage);  
$summarylen = 300; $classarr = array(); $list = array(); $userlist = array(); $count = $pricount = 0; $ordersql = 'b.dateline'; if(empty($_GET['view']) && ($space['friendnum']<$_SCONFIG['showallfriendnum'])) { $_GET['view'] = 'all'; 
}  
$f_index = ''; if($_GET['view'] == 'click') {  
$theurl = "space.p\150\x70?uid=$space[uid]&d\157=$do&view=click"; $actives = array('click'=>' class="active"','view'=>'click'); $clickid = intval($_GET['clickid']); if($clickid) { $theurl .= "&clickid=$clickid"; $wheresql = " AND c.clickid='$clickid'"; $click_actives = array($clickid => ' class="current"'); } else { $wheresql = ''; $click_actives = array('all' => ' class="current"'); } $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("S\105\x4c\105\x43T COUNT(*) \x46\122\x4f\115 ".tname('clickuser')." c WHERE c.uid='$space[uid]' AND c.idtype='blogid' $wheresql"),0); if($count) { $query = $_SGLOBAL['db']->query("S\105\x4cE\x43T b.*, bf.message, bf.target_ids, bf.magiccolor F\122\x4fM ".tname('clickuser')." c
				LEFT JOIN ".tname('blog')." b \x4f\116 b.blogid=c.id
				LEFT JOIN ".tname('blogfield')." bf \117\116 bf.blogid=c.id
				WHERE c.uid='$space[uid]' AND c.idtype='blogid' $wheresql
				ORDER BY c.dateline DESC LIMIT $start,$perpage"); } } else { if($_GET['view'] == 'all') {  
$wheresql = '1'; $actives = array('all'=>' class="active"','view'=>'all');  
$orderarr = array('dateline','replynum','viewnum','hot'); foreach ($clicks as $value) { $orderarr[] = "click_$value[clickid]"; } if(!in_array($_GET['orderby'], $orderarr)) $_GET['orderby'] = '';  
$_GET['day'] = intval($_GET['day']); $_GET['hotday'] = 7; if($_GET['orderby']) { $ordersql = 'b.'.$_GET['orderby']; $theurl = "space.\160\x68\x70?uid=$space[uid]&d\157=blog&view=all&orderby=$_GET[orderby]"; $all_actives = array($_GET['orderby']=>' class="current"'); if($_GET['day']) { $_GET['hotday'] = $_GET['day']; $daytime = $_SGLOBAL['timestamp'] - $_GET['day']*3600*24; $wheresql .= " AND b.dateline>='$daytime'"; $theurl .= "&day=$_GET[day]"; $day_actives = array($_GET['day']=>' class="active"'); } else { $day_actives = array(0=>' class="active"'); } } else { $theurl = "space.\x70h\160?uid=$space[uid]&d\x6f=$do&view=all"; $wheresql .= " AND b.hot>='$minhot'"; $all_actives = array('all'=>' class="current"'); $day_actives = array(); } } else { if(empty($space['feedfriend']) || $classid) $_GET['view'] = 'me'; if($_GET['view'] == 'me') {  
$wheresql = "b.uid='$space[uid]'"; $theurl = "space.\x70\150\160?uid=$space[uid]&\x64o=$do&view=me"; $actives = array('me'=>' class="active"','view'=>'me');  
$query = $_SGLOBAL['db']->query("S\105L\x45CT classid, classname F\122\117\x4d ".tname('class')." WHERE uid='$space[uid]'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $classarr[$value['classid']] = $value['classname']; } } else { $wheresql = "b.uid IN ($space[feedfriend])"; $theurl = "space.\x70\150\160?uid=$space[uid]&d\157=$do&view=we"; $f_index = 'USE INDEX(dateline)'; $fuid_actives = array();  
$fusername = trim($_GET['fusername']); $fuid = intval($_GET['fuid']); if($fusername) { $fuid = getuid($fusername); } if($fuid && in_array($fuid, $space['friends'])) { $wheresql = "b.uid = '$fuid'"; $theurl = "space.\160\x68\x70?uid=$space[uid]&do=$do&view=we&fuid=$fuid"; $f_index = ''; $fuid_actives = array($fuid=>' selected'); } $actives = array('we'=>' class="active"','view'=>'we');  
$query = $_SGLOBAL['db']->query("\x53\105LE\103T * \x46\122\x4f\x4d ".tname('friend')." WHERE uid='$space[uid]' AND \x73\164\141tu\x73='1' ORDER BY num DESC, dateline DESC LIMIT 0,500"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['fuid'], $value['fusername']); $userlist[] = $value; } } }  
if($classid) { $wheresql .= " AND b.classid='$classid'"; $theurl .= "&classid=$classid"; }  
$_GET['friend'] = intval($_GET['friend']); if($_GET['friend']) { $wheresql .= " AND b.friend='$_GET[friend]'"; $theurl .= "&friend=$_GET[friend]"; }  
if($searchkey = stripsearchkey($_GET['searchkey'])) { $wheresql .= " AND b.subject LIKE '%$searchkey%'"; $theurl .= "&searchkey=$_GET[searchkey]"; cksearch($theurl); } $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("S\x45\114E\x43\124 COUNT(*) \x46\x52O\115 ".tname('blog')." b WHERE $wheresql"),0);  
if($wheresql == "b.uid='$space[uid]'" && $space['blognum'] != $count) { updatetable('space', array('blognum' => $count), array('uid'=>$space['uid'])); } if($count) { $query = $_SGLOBAL['db']->query("\123\x45\x4c\105\x43\x54 bf.message, bf.target_ids, bf.magiccolor, b.* \x46RO\115 ".tname('blog')." b $f_index
				LEFT JOIN ".tname('blogfield')." bf \117N bf.blogid=b.blogid
				WHERE $wheresql
				ORDER BY $ordersql DESC LIMIT $start,$perpage"); } } if($count) { while ($value = $_SGLOBAL['db']->fetch_array($query)) { if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) { realname_set($value['uid'], $value['username']); if($value['friend'] == 4) { $value['message'] = $value['pic'] = ''; } else { $value['message'] = getstr($value['message'], $summarylen, 0, 0, 0, 0, -1); } if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']); $list[] = $value; } else { $pricount++; } } }  
$multi = multi($count, $perpage, $page, $theurl);  
realname_get(); $_TPL['css'] = 'blog'; include_once template("space_blog_list"); } ; 

function blog_mp3( $mp3_url, $state )
{
    $optauto = "";
    if ( $state == "auto" )
    {
        $optauto = "&autostart=yes";
    }

    $html = '<script language="JavaScript" src="image/audio-player.js"></script><object type="application/x-shockwave-flash" data="image/player.swf" id="_RANDOM_ID_" height="24" width="290"><param name="movie" value="image/player.swf"><param name="FlashVars" value="playerID=_RANDOM_ID_&soundFile='.$mp3_url.$optauto.'"><param name="quality" value="high"><param name="menu" value="false"><param name="wmode" value="transparent"></object>';

    return $html;
}

?>
