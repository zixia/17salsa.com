<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

define('CURSCRIPT', 'js_get_question');
require_once ('include/common.inc.php');
	
$website = 'http://www.cyask.com';	//设置为你的域名
$title_len = 58;					//显示问题长度
	
$status = intval($_GET['status']); // 1 待解决问题，2=已解决问题，3=推荐问题
$count = intval($_GET['count']);	//调用问题条数
 
$status = $status ? $status : 1;

$query=$dblink->query("select qid,title from {$tablepre}question where status=$status order by qid desc limit $count");
while($tmp=$dblink->fetch_array($query))
{
	$tmp['url'] = $htmlopen == 1 ? $website.'/question/'.$tmp['qid'].'.html' : $website.'/question.php?qid='.$tmp['qid'];
	
	$tmp['title']=cut_str($tmp['title'],$title_len);
	
	echo "document.write('<li><a href=\"$tmp[url]\" target=\"_blank\">$tmp[title]</a></li>');";
}

?>