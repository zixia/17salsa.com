<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-30
	Author: zhaoshunyao
	QQ: 240508015
*/
define('CURSCRIPT', 'share');
require_once ('./include/common.inc.php');

$title=$site_name;

if(!$cyask_uid)
{
	$url='share.php?word='.$_GET['word'];
	header("location:login.php?url=$url");
	exit;
}

/*
$time_exceed=strtotime(date("Y-m-d"));
$query=$dblink->query("SELECT count(*) FROM {$tablepre}question WHERE uid=$cyask_uid AND asktime>$time_exceed");
$exceed_count=$dblink->result($query,0);
if($exceed_count >= $count_ques_exceed)
{
	show_message('ques_exceed', '');
	exit;
}
*/

$share_title=empty($_GET['word']) ? $_POST['word'] : $_GET['word'];
$share_title=trim($share_title);

if($command=='share')
{
	if(check_submit($_POST['submit'], $_POST['formhash']))
	{
		if(empty($_POST['share_title']))
		{
			show_message('title_null', '');
			exit;
		}
		$sid=intval($_POST['cid']);
		if($sid)
		{
			$query=$dblink->query("SELECT * FROM {$tablepre}sort WHERE sid=$sid");
			$sortrow=$dblink->fetch_array($query);
			switch($sortrow['grade'])
			{
				case 1 : $sid1=$sortrow['sid'];$sid2=0;$sid3=0;break;
				case 2 : $sid1=$sortrow['sid1'];$sid2=$sortrow['sid'];$sid3=0;break;
				case 3 : $sid1=$sortrow['sid1'];$sid2=$sortrow['sid2'];$sid3=$sortrow['sid'];break;
			}
		}
		else
		{
			show_message('class_error', '');
			exit;
		}
		
		$share_title = filters_title($_POST['share_title']);
		$share_content = filters_content($_POST['share_content']);

		$share_title = $dblink->escape_string($share_title);
		$share_content = $dblink->escape_string($share_content);
		
		$sql = "INSERT INTO {$tablepre}question SET sid='$sid',sid1='$sid1',sid2='$sid2',sid3='$sid3',uid='$cyask_uid',username='$cyask_user',title='$share_title',asktime='$timestamp',status=7";
		if($dblink->query($sql))
		{
			$qid = $dblink->insert_id();
		}
		
		$do=$dblink->query("INSERT INTO {$tablepre}question_1 SET qid='$qid',supplement='$share_content'");
		if($do)
		{
			require_once CYASK_ROOT.'./uc_client/client.php';
			
			$title_template = '{actor} 分享知识：“{stitle} ”';
			
			$feed_content = '<a href="'.$boardurl.'question.php?qid='.$qid.'" target="_blank">'.$share_title.'</a>';
			$title_data = array('stitle'=>$feed_content);
			$body_template = '';
			$body_data = '';
			
			uc_feed_add('thread', $cyask_uid, $cyask_user,$title_template, $title_data, $body_template, $body_data);
			
			update_score($cyask_uid, $score_share, '+');
			
	        header("location:signal.php?resultno=113&url=$url");
			exit;
		}
		else
		{
			show_message('ask_error', 'share.php?word='.$word);
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
	$query=$dblink->query("SELECT sid,sort1 FROM {$tablepre}sort WHERE grade=1");
	$count1=$dblink->num_rows($query);
	$class1='';
	$c=1;
	while($row1=$dblink->fetch_array($query))
	{
		$class1.='new Array("'.$row1[sid].'","'.$row1[sort1].'")';
		if($c==$count1) $class1.="\n"; else $class1.=",\n";
		$c++;
	}
	$query=$dblink->query("SELECT sid,sid1,sort2 FROM {$tablepre}sort WHERE grade=2");
	$count2=$dblink->num_rows($query);
	$class2='';
	$c=1;
	while($row2=$dblink->fetch_array($query))
	{
		$class2.='new Array("'.$row2[sid1].'","'.$row2[sid].'","'.$row2[sort2].'")';
		if($c==$count2) $class2.="\n"; else $class2.=",\n";
		$c++;
	}
	
	$query=$dblink->query("SELECT sid,sid2,sort3 FROM {$tablepre}sort WHERE grade=3");
	$count3=$dblink->num_rows($query);
	$class3='';
	$c=1;
	while($row3=$dblink->fetch_array($query))
	{
		$class3.='new Array("'.$row3[sid2].'","'.$row3[sid].'","'.$row3[sort3].'")';
		if($c==$count3) $class3.="\n"; else $class3.=",\n";
		$c++;
	}
}
include template('share');
?>