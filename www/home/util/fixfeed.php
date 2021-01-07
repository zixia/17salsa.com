<?php
//Í¨ÓÃÎÄ¼þ
include_once('./common.php');
include_once(S_ROOT.'./source/function_cp.php');

//uchomeµØÖ·
$uchUrl = getsiteurl().'cp.php?ac=userapp';
	
//manyou
$my_prefix = 'http://uchome.manyou.com';
if(empty($_GET['my_suffix'])) {
	$appId = intval($_GET['appid']);
	if ($appId) {
		$mode = $_GET['mode'];
		if ($mode == 'about') {
			$my_suffix = '/userapp/about?appId='.$appId;
		} else {
			$my_suffix = '/userapp/privacy?appId='.$appId;
		}
	} else {
		$my_suffix = '/userapp/list';
	}
} else {
	$my_suffix = $_GET['my_suffix'];
}
$my_extra = isset($_GET['my_extra']) ? $_GET['my_extra'] : '';

$delimiter = strrpos($my_suffix, '?') ? '&' : '?';
$myUrl = $my_prefix.urldecode($my_suffix.$delimiter.'my_extra='.$my_extra);
	
//±¾µØÁÐ±í
$my_userapp = $my_default_userapp = array();
if($my_suffix == '/userapp/list') {
	$_GET['op'] = 'menu';//Ä£°å
	$max_order = 0;
	foreach ($_SGLOBAL['my_userapp'] as $value) {
		if(!isset($_SGLOBAL['userapp'][$value['appid']])) {
			$my_userapp[$value['appid']] = $value;
			if($value['displayorder']>$max_order) $max_order = $value['displayorder'];
		} else {
			$my_default_userapp[$value['appid']] = $value;
		}
	}
}
	

$appIds = array(1004186,1003094,1021978,1006424);

foreach ( $appIds as $appId )
{

for ( $uch_id=1; $uch_id<700; $uch_id++ )
{
################# vars start ######################

#$appId 	= 1021978;
#$uch_id	= 354;

$s_id	= 1014728;
$uch_url = "http%3A%2F%2F17salsa.net%2Fhome%2Fcp.php%3Fac%3Duserapp&my_suffix=%2Fuserapp%2FmodifyPrivacy%3FappId%3D1021978";
$my_suffix	= "%2Fuserapp%2FmodifyPrivacy%3FappId%3D1021978";

################# vars end ######################

$_SGLOBAL['supe_uid'] = $uch_id;


$timestamp = $_SGLOBAL['timestamp'];

$hash = $_SCONFIG['my_siteid'].'|'.$_SGLOBAL['supe_uid'].'|'.$_SCONFIG['my_sitekey'].'|'.$timestamp;

$hash = md5($hash);
$delimiter = strrpos($myUrl, '?') ? '&' : '?';

$my_sign = $hash;
	
$url = $myUrl.$delimiter.'s_id='.$_SCONFIG['my_siteid'].'&uch_id='.$_SGLOBAL['supe_uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.urlencode($my_suffix).'&timestamp='.$timestamp.'&my_sign='.$hash;


$url = "http://uchome.manyou.com/userapp/modifyPrivacy?appId=$appId&my_extra=&s_id=$s_id&uch_id=$uch_id&uch_url=$uch_url&my_suffix=$my_suffix&timestamp=$timestamp&my_sign=$my_sign";

#if ( $uch_id == 40 )
	echo "$url\n";


#if ( $uch_id > 50 )
#	break;

}}
?>
