<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

define('CURSCRIPT', 'msgcheck');
require '../include/common.inc.php';
require_once '../uc_client/client.php';

if($cyask_uid)
{
	$msg = uc_pm_checknew($cyask_uid);
	
	$msg = empty($msg) ? 0:$msg;
	echo $msg;
	exit;
}
else
{
	echo 0;
	exit;
}
?>