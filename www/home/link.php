<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: link.php 10953 2009-01-12 02:55:37Z liguode $
*/

// by zixia
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' ); 


include_once('./common.php');

if(empty($_GET['url'])) {
	showmessage('do_success', $refer, 0);
} else {
	$url = $_GET['url'];
	if(!$_SCONFIG['linkguide']) {
		showmessage('do_success', $url, 0);//直接跳转
	}
}


$space = array();
if($_SGLOBAL['supe_uid']) {
	$space = getspace($_SGLOBAL['supe_uid']);
}
if(empty($space)) {
	//游客直接跳转
	showmessage('do_success', $url, 0);
}

$url = shtmlspecialchars($url);
if(!preg_match("/^http\:\/\//i", $url)) $url = "http://".$url;

// by zixia 201003
	$realurl = preg_replace('#http://17salsa\.com/home/link.php\?url=#','',$url);
	$realurl = preg_replace('#http://17salsa\.net/home/link.php\?url=#','',$realurl);

	$logfile = "../dl/bin/link.csv";
	$fh = fopen($logfile, 'a');
	$logstr = sprintf("%s,%s,%s,%s,%s,%s\r\n"
					,date("Y-m-d H:i:s", time())
					,$realurl
					,$space[uid]
					,$_SN[$space[uid]]
					,$_SERVER["HTTP_X_FORWARDED_FOR"]
					,$_SGLOBAL[refer]
				);
	fputs($fh, $logstr); 
	fclose($fh);



//模板调用
include_once template("iframe");

?>
