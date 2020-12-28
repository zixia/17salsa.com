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

$username = trim($_GET['username']);
$usernum = uc_user_checkname($username);

if($usernum == 1)
{
	echo 'yes';
	exit;
}
elseif($usernum == -3)
{
	echo 'no';
	exit;
}
elseif($usernum == -1 || $usernum == -2)
{
	echo 'error';
	exit;
}
?>