<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

define('CURSCRIPT', 'comment');
require_once ('./include/common.inc.php');

if($command=='share_comment')
{
	$qid=intval($_POST['qid']);
	$query=$dblink->query("select uid,username,title from {$tablepre}question where qid=$qid");
	$share=$dblink->fetch_array($query);
	if(!$dblink->num_rows($query))
	{
		show_message('action_error', './');
		exit;
	}
	if(!$cyask_uid)
	{
		$referer=get_referer();
		show_message('user_nologin', $referer);
		exit;
	}

	if(check_submit($_POST['dosubmit'], $_POST['formhash']))
	{
		if(empty($_POST['content']))
		{
			show_message('answer_null', '');
			exit;
		}
	
		$content = filters_content($_POST['content']);
		$content = $dblink->escape_string($content);
		
		$sql="INSERT INTO {$tablepre}answer set qid=$qid,uid=$cyask_uid,answertime=$timestamp";
		if($dblink->query($sql))
		{
			$aid = $dblink->insert_id();
			$dblink->query("INSERT INTO {$tablepre}answer_1 SET aid='$aid',username='$cyask_user',content='$content'");

			$dblink->query("UPDATE {$tablepre}question SET answercount=answercount+1 WHERE qid=$qid");
				
			require_once CYASK_ROOT.'./uc_client/client.php';
				
			$title_template = '{actor} 评论了分享：“{qtitle} ”';
			$feed_content = '<a href="'.$boardurl.'question.php?qid='.$qid.'" target="_blank">'.$share[title].'</a>';
			$title_data = array('qtitle'=>$feed_content);
			uc_feed_add('thread', $cyask_uid, $cyask_user,$title_template, $title_data);
			
			$referer=get_referer('./');
			header("location:signal.php?resultno=114&url=$referer");
			exit;
		}
	}
	else
	{
		show_message('url_error', './');
		exit;
	}
}
else
{
	show_message('action_error', './');
	exit;
}
?>