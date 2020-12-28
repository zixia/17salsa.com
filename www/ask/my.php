<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/
define('CURSCRIPT', 'my');
require './include/common.inc.php';
require_once CYASK_ROOT.'./uc_client/client.php';

if(!$cyask_uid)
{
	$url='my.php';
	header("location:login.php?url=$url");
	exit;
}

$command= empty($command) ? 'myscore': $command;

if($command=='myscore')
{
	$query=$dblink->query("select allscore from {$tablepre}member where uid=$cyask_uid");
	$totalscore=$dblink->result($query,0);
	
	$lastweek=get_weeks()-1;
	$query=$dblink->query("select sum(score) from {$tablepre}score where uid=$cyask_uid and week=$lastweek");
	$lastweekscore=$dblink->result($query,0);
	
	$year=intval(date("Y"));
	$month=date("n")-1;
	$month=intval($month);
	if(!$month)
	{
		$year=$year-1;
		$month=12;
	}
	$lastmonth=intval($year.$month);
	$query=$dblink->query("select sum(score) from {$tablepre}score where uid=$cyask_uid and month=$lastmonth");
	$lastmonthscore=$dblink->result($query,0);
	
	$query=$dblink->query("select count(*) from {$tablepre}answer where uid=$cyask_uid");
	$answercount=$dblink->result($query,0);
	$query=$dblink->query("select count(*) from {$tablepre}answer where uid=$cyask_uid and adopttime<>0");
	$adoptcount=$dblink->result($query,0);
	
	if($answercount)
	{
		$rightvalage=$adoptcount/$answercount*100;
		$rightvalage=round($rightvalage).'%';
	}
	else
	{
		$rightvalage='0%';
	}
	$query=$dblink->query("select count(*) from {$tablepre}question where uid=$cyask_uid");
	$question_allcount=$dblink->result($query,0);
	$query=$dblink->query("select count(*) from {$tablepre}question where uid=$cyask_uid and status=2");
	$questionOK=$dblink->result($query,0);
	$query=$dblink->query("select count(*) from {$tablepre}question where uid=$cyask_uid and status=1");
	$questionASK=$dblink->result($query,0);
	$query=$dblink->query("select count(*) from {$tablepre}question where uid=$cyask_uid and status=3");
	$questionVOTE=$dblink->result($query,0);
	$query=$dblink->query("select count(*) from {$tablepre}question where uid=$cyask_uid and status=4");
	$questionCLOSE=$dblink->result($query,0);
	unset($query);
}
elseif($command=='myask')
{
	$page=intval($_GET['page']);
	if($page<1) $page=1;
	$pagerow=10;
	$query=$dblink->query("SELECT count(*) FROM {$tablepre}question WHERE uid=$cyask_uid AND status IN (1,2,3)");
	$quescount=$dblink->result($query,0);     
	$pagecount=ceil($quescount/$pagerow);
	if($page>$pagecount) $page=1;
	$pagestart=($page-1)*$pagerow;
	$query=$dblink->query("SELECT qid,title,status,score,asktime,answercount FROM {$tablepre}question where uid=$cyask_uid AND status IN (1,2,3) ORDER BY asktime desc LIMIT $pagestart,$pagerow");
	
	while($ques_temp=$dblink->fetch_array($query))
	{
		$ques_temp['stitle']=cut_str($ques_temp['title'],54);
		$ques_temp['asktime']=date("y-n-d",$ques_temp['asktime']);
		$ques_list[] = $ques_temp;
	}
	unset($query);
	$page_front	=$page-1;
	$page_next	=$page+1;
	$pagelinks = get_pages($page,$pagecount,'command='.$command);
}
elseif($command=='myoverdue')
{
	$page=intval($_GET['page']);
	if($page<1) $page=1;
	$pagerow=10;
	$query=$dblink->query("SELECT count(*) FROM {$tablepre}question WHERE uid=$cyask_uid AND endtime<$timestamp AND status IN (1,3)");
	$quescount=$dblink->result($query,0);     
	$pagecount=ceil($quescount/$pagerow);
	if($page>$pagecount) $page=1;
	$pagestart=($page-1)*$pagerow;
	$query=$dblink->query("SELECT qid,title,status,score,asktime,answercount FROM {$tablepre}question where uid=$cyask_uid AND endtime<$timestamp AND status IN (1,3) ORDER BY asktime desc LIMIT $pagestart,$pagerow");
	
	while($ques_temp=$dblink->fetch_array($query))
	{
		$ques_temp['stitle']=cut_str($ques_temp['title'],54);
		$ques_temp['asktime']=date("y-n-j",$ques_temp['asktime']);
		$ques_list[] = $ques_temp;
	}
	unset($query);
	$page_front	=$page-1;
	$page_next	=$page+1;
	$pagelinks = get_pages($page,$pagecount,'command='.$command);
}
elseif($command=='myanswer')
{
	$page=intval($_GET['page']);
	if($page<1) $page=1;
	$pagerow=10;
	$query=$dblink->query("select count(*) from {$tablepre}answer where uid=$cyask_uid");
	$answercount=$dblink->result($query,0);     
	$pagecount=ceil($answercount/$pagerow);
	if($page>$pagecount) $page=1;
	$pagestart=($page-1)*$pagerow;
	$query=$dblink->query("SELECT a.aid,a.answertime,a.adopttime,a.response,q.title,q.status,q.score,q.answercount FROM {$tablepre}answer AS a,{$tablepre}question AS q WHERE a.uid=$cyask_uid AND a.qid=q.qid ORDER BY a.answertime DESC LIMIT $pagestart,$pagerow");
	
	while($ques_temp=$dblink->fetch_array($query))
	{
		$ques_temp['stitle']=cut_str($ques_temp['title'],54);
		$ques_temp['answertime']=date("y-n-d",$ques_temp['answertime']);
		$ques_list[] = $ques_temp;
	}
	
	unset($query,$query2);
	$page_front	=$page-1;
	$page_next	=$page+1;
	$pagelinks = get_pages($page,$pagecount,'command='.$command);
}
elseif($command=='myshare')
{
	$page=intval($_GET['page']);
	if($page<1) $page=1;
	$pagerow=10;
	$query=$dblink->query("SELECT count(*) FROM {$tablepre}question WHERE uid=$cyask_uid and status=7");
	$quescount=$dblink->result($query,0);     
	$pagecount=ceil($quescount/$pagerow);
	if($page>$pagecount) $page=1;
	$pagestart=($page-1)*$pagerow;
	$query=$dblink->query("SELECT qid,title,status,score,asktime,answercount FROM {$tablepre}question where uid=$cyask_uid and status=7 ORDER BY asktime desc LIMIT $pagestart,$pagerow");
	
	while($ques_temp=$dblink->fetch_array($query))
	{
		$ques_temp['stitle']=cut_str($ques_temp['title'],54);
		$ques_temp['asktime']=date("y-n-d",$ques_temp['asktime']);
		$ques_list[] = $ques_temp;
	}
	unset($query);
	$page_front	=$page-1;
	$page_next	=$page+1;
	$pagelinks = get_pages($page,$pagecount,'command='.$command);
}
elseif($command=='mymessage')
{
	$boxtype = isset($_POST['boxtype']) ? $_POST['boxtype'] : $_GET['boxtype'];
	$boxtype = $boxtype ? $boxtype : 'inbox';
	
	if($boxtype == 'inbox')
	{
		$msgtype = $_GET['msgtype'] ? $_GET['msgtype'] : 'newpm';
	}
	else
	{
		$msgtype = 'newpm';
	}
	
	$page = intval($_GET['page']);
	$page = $page ? $page : 1;
	$pagesize =10;
	
	$msglist = uc_pm_list($cyask_uid, $page, $pagesize, $boxtype, $msgtype, 0);
	$msgcount = $msglist[count];
	
	$pagecount = ceil($msgcount/$pagesize);

	$msg_list = array();
	
	foreach($msglist['data'] as $m)
	{
		$msg_temp['pmid']=$m['pmid'];
		$msg_temp['fromuser']=$m['msgfrom'];
		$msg_temp['fromuid']=$m['msgfromid'];
		$msg_temp['touser']=$cyask_user;
		$msg_temp['mdate']=date("Y-m-d H:i",$m['dateline']);
		$msg_temp['title']=cut_str($m['subject'],50);
		$msg_temp['new']=$m['new'];
		$msg_temp['delstatus']=$m['delstatus'];
		$msg_list[] = $msg_temp;
	}
	
	
	$page_front	=$page-1;
	$page_next	=$page+1;
	$parameter = 'command='.$command.'&boxtype='.$boxtype.'&msgtype='.$msgtype;
	$pagelinks = get_pages($page,$pagecount,$parameter);
	
}
elseif($command=='sendmsg')
{
	if(isset($_POST['submit']))
	{
		if(check_submit($_POST['submit'], $_POST['formhash']))
		{
			$subject = $_POST['title'];
			$message = $_POST['content'];

			$msgto = trim($_POST['username']);
			$msgto = str_replace('，', ',', $msgto);
		
			$result = uc_pm_send($cyask_uid, $msgto, $subject, $message, 1, 0, 1);
			
			if($result)
			{
				$dourl='my.php?command=mymessage&boxtype=outbox';
				show_message('sendmsg_succeed', $dourl);
				exit;
			}
			else
			{
				$dourl='my.php?command=mymessage&boxtype=outbox';
				show_message('sendmsg_usernull', $dourl);
				exit;
			}
		}
		else
		{
			show_message('url_error', './');
			exit;
		}
	}
}
elseif($command=='readmsg')
{
	$pmid=intval($_GET['pmid']);
	
	$msg=uc_pm_viewnode($cyask_uid, 0, $pmid);

    $msg['mdate']=date("y-m-d",$msg[dateline]);
    
}
elseif($command=='replymsg')
{
	$boxtype = $_POST['boxtype'];
	$page = intval($_POST['page']);
	$replypmid = intval($_POST['pmid']);
	$msgto = intval($_POST['backuid']);
	
	if(check_submit($_POST['submit'], $_POST['formhash']))
	{
	
		$subject='[回复]'.filters_content($_POST['title']);
		$message=filters_content($_POST['content']);
		
		uc_pm_send($cyask_uid, $msgto, $subject, $message, 1, $replypmid);

		$backurl='my.php?command=mymessage&boxtype='.$boxtype.'&page='.$page;
		show_message('sendmsg_succeed', $backurl);
		exit;
	}
	else
	{
		show_message('url_error', './');
		exit;
	}
}
elseif($command=='myinfo')
{
	//$userinfo = uc_get_user($cyask_uid, 1);
	
	$query=$dblink->query("select * from {$tablepre}member where uid=$cyask_uid");
	$members=$dblink->fetch_array($query);
	$members['avatar'] = UC_API.'/avatar.php?uid='.$members['uid'].'&size=big';
	$members['signature'] = nl2br($members['signature']);
	unset($query);
	
}
elseif($command=='upinfo')
{
	$query=$dblink->query("select * from {$tablepre}member where uid=$cyask_uid");
	$members=$dblink->fetch_array($query);
	unset($query);
}
elseif($command=='upinfosubmit')
{
	if(check_submit($_POST['upinfosubmit'], $_POST['formhash']))
	{
		$query=$dblink->query("update {$tablepre}member set gender='$_POST[gender]',bday='$_POST[bday]',qq='$_POST[qq]',msn='$_POST[msn]',signature='$_POST[signature]' where uid=$cyask_uid");

		$backurl='my.php?command=myinfo';
		show_message('upinfo_succeed', $backurl);
		exit;
	}
	else
	{
		show_message('url_error', './');
		exit;
	}
	
}
elseif($command=='uppassword')
{
}
elseif($command=='uppwsubmit')
{
	if(check_submit($_POST['uppwsubmit'], $_POST['formhash']))
	{
		$email = $_POST['email'];
		$oldpw = $_POST['opw'];
		$newpw = $_POST['npw'];
		
		$info = uc_user_edit($cyask_user, $oldpw, $newpw, $email);

		if($info == 1)
		{
			$newpwmd5 = md5($newpw);
			$dblink->query("update {$tablepre}member set password='$newpwmd5',email='$email' where uid='$cyask_uid'");
			
			$backurl='my.php?command=myinfo';
			show_message('uppw_succeed', $backurl);
			exit;
		}
		else
		{
			$backurl='my.php?command=uppassword';
			show_message('uppw_error_'.abs($info), $backurl);
			exit;
		}
	}
	else
	{
		show_message('url_error', './');
		exit;
	}
	
}
elseif($command=='delmessage')
{
	$boxtype = isset($_POST['boxtype']) ? $_POST['boxtype'] : $_GET['boxtype'];
	$boxtype = $boxtype=='inbox' ? 'inbox' : 'outbox';
	
	$pmids = array();
	if(isset($_POST['pmid']) && is_array($_POST['pmid']))
	{
		$pmids = $_POST['pmid'];
	}
	else
	{
		$pmid = intval($_GET['pmid']);
		$pmids[] = $pmid;
	}

	$result = uc_pm_delete($cyask_uid, $boxtype, $pmids);
	if($result)
	{
		$referer=get_referer();
		show_message('delmessage_succeed', $referer);
		exit;
	}
}
else
{
	show_message('action_error', './');
	exit;
}
include template('my');
?>