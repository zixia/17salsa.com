<?php
  if (!defined ('IN_UCHOME'))
  {
    exit ('Access Denied');
  }
$albumid = empty ($_GET['albumid']) ? 0 : intval ($_GET['albumid']);
$eventid = empty ($_GET['eventid']) ? 0 : intval ($_GET['eventid']);
if ($eventid)
  {
    $query =
      $_SGLOBAL['db']->
      query ("\x53\x45\114\105\x43\124 e.*, ef.* \x46\122\x4f\115 ".
	     tname ("event")." e LEFT JOIN ".tname ("eventfield").
	     " ef \117N e.eventid=ef.eventid WHERE e.eventid='$_GET[eventid]'");
    $event = $_SGLOBAL['db']->fetch_array ($query);
    if (empty ($event))
      {
	showmessage ('event_does_not_exist');
      }
    if ($event['grade'] == -2)
      {
	showmessage ('event_is_closed');
      }
    elseif ($event['grade'] < 1)
    {
      showmessage ('event_under_verify');
    }
    $query =
      $_SGLOBAL['db']->query ("\123ELECT * F\122\117M ".tname ("userevent").
			      " WHERE uid = '$_SGLOBAL[supe_uid]' AND eventid = '$eventid'");
    $userevent = $_SGLOBAL['db']->fetch_array ($query);
    if ($event['allowpic'] == 0 && $userevent['status'] < 3)
      {
	showmessage ('event_only_allows_admins_to_upload');
      }
    if ($event['allowpic'] && $userevent['status'] < 2)
      {
	showmessage ("event_only_allows_members_to_upload");
      }
  }
if (submitcheck ('albumsubmit'))
  {
    if ($_POST['albumop'] == 'creatalbum')
      {
	$_POST['albumname'] =
	  empty ($_POST['albumname']) ? '' : getstr ($_POST['albumname'], 50,
						     1, 1);
	if (empty ($_POST['albumname']))
	  $_POST['albumname'] = gmdate ('Ymd');
	$_POST['friend'] = intval ($_POST['friend']);
	$_POST['target_ids'] = '';
	if ($_POST['friend'] == 2)
	  {
	    $uids = array ();
	    $names =
	      empty ($_POST['target_names']) ? array () : explode (' ',
								   str_replace
								   (array
								    (cplang
								     ('tab_space'),
								     "\r\n",
								     "\n",
								     "\r"),
								    ' ',
								    $_POST
								    ['target_names']));
	    if ($names)
	      {
		$query =
		  $_SGLOBAL['db']->
		  query ("\123\105\114E\x43T uid \106R\x4f\x4d ".
			 tname ('space')." WHERE username IN (".
			 simplode ($names).")");
		while ($value = $_SGLOBAL['db']->fetch_array ($query))
		  {
		    $uids[] = $value['uid'];
		  }
	      }
	    if (empty ($uids))
	      {
		$_POST['friend'] = 3;
	      }
	    else
	      {
		$_POST['target_ids'] = implode (',', $uids);
	      }
	  }
	elseif ($_POST['friend'] == 4)
	{
	  $_POST['password'] = trim ($_POST['password']);
	  if ($_POST['password'] == '')
	    $_POST['friend'] = 0;
	}
	if ($_POST['friend'] !== 2)
	  {
	    $_POST['target_ids'] = '';
	  }
	if ($_POST['friend'] !== 4)
	  {
	    $_POST['password'] = '';
	  }
	$setarr = array ();
	$setarr['albumname'] = $_POST['albumname'];
	$setarr['uid'] = $_SGLOBAL['supe_uid'];
	$setarr['username'] = $_SGLOBAL['supe_username'];
	$setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
	$setarr['friend'] = $_POST['friend'];
	$setarr['password'] = $_POST['password'];
	$setarr['target_ids'] = $_POST['target_ids'];
	$albumid = inserttable ('album', $setarr, 1);
	if (empty ($space['albumnum']))
	  {
	    $space['albumnum'] =
	      getcount ('album', array ('uid' = >$space['uid']));
	    $albumnumsql = "albumnum=".$space['albumnum'];
	  }
	else
	  {
	    $albumnumsql = 'albumnum=albumnum+1';
	  }
	$_SGLOBAL['db']->query ("UPDATE ".tname ('space').
				" SET {$albumnumsql}, updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
      }
    else
      {
	$albumid = intval ($_POST['albumid']);
      }
    $_POST['topicid'] = topic_check ($_POST['topicid'], 'pic');
    if ($_SGLOBAL['mobile'])
      {
	showmessage ('do_success', 'cp.php?ac=upload');
      }
    else
      {
	echo "<script>";
	echo "parent.no_insert = 1;";
	echo "parent.albumid = $albumid;";
	echo "parent.topicid = $_POST[topicid];";
	echo "parent.start_upload();";
	echo "</script>";
      }
    exit ();
  }
elseif (submitcheck ('uploadsubmit'))
{
  $albumid = $picid = 0;
  if (!checkperm ('allowupload'))
    {
      if ($_SGLOBAL['mobile'])
	{
	  showmessage (cplang ('not_allow_upload'));
	}
      else
	{
	  echo "<script>";
	  echo "alert(\"".cplang ('not_allow_upload')."\")";
	  echo "</script>";
	  exit ();
	}
    }
  if ($_SGLOBAL['mobile'] && empty ($_POST['albumid']))
    {
      $setarr = array ();
      $setarr['albumname'] = $_POST['albumname'];
      $setarr['uid'] = $_SGLOBAL['supe_uid'];
      $setarr['username'] = $_SGLOBAL['supe_username'];
      $setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
      $setarr['friend'] = 0;
      $setarr['password'] = '';
      $setarr['target_ids'] = '';
      $_POST['albumid'] = inserttable ('album', $setarr, 1);
    }
  $_FILES['attach']['size'] = intval ($_FILES['attach']['size']);
  if (!empty ($_FILES['attach']['size'])
      && !empty ($_FILES['attach']['tmp_name'])
      && empty ($_FILES['attach']['error']))
    {
      $_POST['pic_title'] = $_FILES['attach']['name'];
    }
  $_POST['topicid'] = topic_check ($_POST['topicid'], 'pic');
  $uploadfiles =
    pic_save ($_FILES['attach'], $_POST['albumid'], $_POST['pic_title'],
	      $_POST['topicid']);
  if ($uploadfiles && is_array ($uploadfiles))
    {
      $albumid = $uploadfiles['albumid'];
      $picid = $uploadfiles['picid'];
      $uploadStat = 1;
      if ($eventid)
	{
	  $arr = array ("eventid" = >$eventid, "picid" = >$picid, "uid" =
			>$_SGLOBAL['supe_uid'], "username" =
			>$_SGLOBAL['supe_username'], "dateline" =
			>$_SGLOBAL['timestamp']);
	  inserttable ("eventpic", $arr);
	}
    }
  else
    {
      $uploadStat = $uploadfiles;
    }
  if ($_SGLOBAL['mobile'])
    {
      if ($picid)
	{
	  showmessage ('do_success',
		       "space.\x70h\x70?d\157=album&picid=$picid");
	}
      else
	{
	  showmessage ($uploadStat, 'cp.php?ac=upload');
	}
    }
  else
    {
      echo "<script>";
      echo "parent.albumid = $albumid;";
      echo "parent.topicid = $_POST[topicid];";
      echo "parent.uploadStat = '$uploadStat';";
      echo "parent.picid = $picid;";
      echo "parent.upload();";
      echo "</script>";
    }
  exit ();
}

elseif (submitcheck ('viewAlbumid'))
{
  if ($eventid)
    {
      $imgs = array ();
      $imglinks = array ();
      $dateline = $_SGLOBAL['timestamp'] - 600;
      $query =
	$_SGLOBAL['db']->query ("SE\x4c\x45C\124 pic.* \106R\117M ".
				tname ("eventpic")." ep LEFT JOIN ".
				tname ("pic").
				" pic \x4f\x4e ep.picid=pic.picid WHERE ep.uid='$_SGLOBAL[supe_uid]' AND ep.eventid='$eventid' AND ep.dateline > $dateline ORDER BY ep.dateline DESC LIMIT 4");
      while ($value = $_SGLOBAL['db']->fetch_array ($query))
	{
	  $imgs[] =
	    pic_get ($value['filepath'], $value['thumb'], $value['remote']);
	  $imglinks[] =
	    "space.p\x68\160?\x64\157=event&id=$eventid&view=pic&picid=".
	    $value['picid'];
	}
      $picnum = 0;
      if ($imgs)
	{
	  $picnum =
	    $_SGLOBAL['db']->result ($_SGLOBAL['db']->
				     query
				     ("\123\105\114E\103\x54 COUNT(*) \x46\122\117\x4d ".
				      tname ("eventpic").
				      " WHERE eventid='$eventid'"), 0);
	  feed_add ('event', cplang ('event_feed_share_pic_title'), '',
		    cplang ('event_feed_share_pic_info'), array ("eventid" =
								 >$eventid,
								 "title" =
								 >$event
								 ['title'],
								 "picnum" =
								 >$picnum),
		    '', $imgs, $imglinks);
	}
      $_SGLOBAL['db']->query ("UPDATE ".tname ("event").
			      " SET picnum='$picnum', updatetime='$_SGLOBAL[timestamp]' WHERE eventid='$eventid'");
      showmessage ('do_success', 'space.php?do=event&view=pic&id='.$eventid,
		   0);
    }
  else
    {
      if (ckprivacy ('upload', 1))
	{
	  include_once (S_ROOT.'./source/function_feed.php');
	  feed_publish ($_POST['opalbumid'], 'albumid');
	}
      if ($_POST['topicid'])
	{
	  topic_join ($_POST['topicid'], $_SGLOBAL['supe_uid'],
		      $_SGLOBAL['supe_username']);
	  $url =
	    "space.\x70h\160?\144\x6f=topic&topicid=$_POST[topicid]&view=pic";
	}
      else
	{
	  $url =
	    "space.\x70hp?uid=$_SGLOBAL[supe_uid]&\144\157=album&id=".(empty
								       ($_POST
									['opalbumid'])
								       ? -1 :
								       $_POST
								       ['opalbumid']);
	}
      showmessage ('upload_images_completed', $url, 0);
    }
}

else
{
  if (!checkperm ('allowupload'))
    {
      ckspacelog ();
      showmessage ('no_privilege');
    }
  ckrealname ('album');
  ckvideophoto ('album');
  cknewuser ();
  $siteurl = getsiteurl ();
  $albums = getalbums ($_SGLOBAL['supe_uid']);
  $actives = ($_GET['op'] == 'flash'
	       || $_GET['op'] == 'cam') ? array ($_GET['op'] =
						 >' class="active"') :
    array ('js' = >' class="active"');
  $maxattachsize = checkperm ('maxattachsize');
  if (!empty ($maxattachsize))
    {
      $maxattachsize = $maxattachsize + $space['addsize'];
      $haveattachsize = formatsize ($maxattachsize - $space['attachsize']);
    }
  else
    {
      $haveattachsize = 0;
    }
  $groups = getfriendgroup ();
  $topic = array ();
  $topicid = $_GET['topicid'] = intval ($_GET['topicid']);
  if ($topicid)
    {
      $topic = topic_get ($topicid);
    }
  if ($topic)
    $actives = array ('upload' = >' class="active"');
}

include_once template ("cp_upload");
? >
