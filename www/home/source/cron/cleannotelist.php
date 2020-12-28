<?php
/*
	by zixia
	clean ucenter uc_notelist
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//清理通知
$deltime = $_SGLOBAL['timestamp'] - 2*3600*24;//只保留2天

//执行
$_SGLOBAL['db']->query("DELETE FROM uc_notelist WHERE dateline < '$deltime'");
$_SGLOBAL['db']->query("OPTIMIZE TABLE uc_notelist 'SILENT'");//优化表
?>
