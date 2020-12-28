<?php
  if (!defined ('IN_UCHOME'))
  {
    exit ('Access Denied');
  }
include_once (S_ROOT.'./source/function_bbcode.php');
$faceid = 0;
$message = "";
$tospace = $pic = $blog = $album = $share = $event = $poll = array ();
if ((submitcheck ('commentsubmit') || submitcheck ('editsubmit'))
    && !empty ($_POST['selectface']))
  {
    $idtype = $_POST['idtype'];
    $id = intval ($_POST['id']);
    $cid = empty ($_POST['cid']) ? 0 : intval ($_POST['cid']);
    $message = empty ($_POST['message']) ? "" : $_POST['message'];
    $_GET['op'] = 'face';
  }
elseif (submitcheck ('facesubmit'))
{
  $idtype = $_POST['idtype'];
  $id = intval ($_POST['id']);
  $cid = empty ($_GET['cid']) ? 0 : intval ($_GET['cid']);
  $message = empty ($_POST['message']) ? "" : $_POST['message'];
  $faceid = empty ($_POST['faceid']) ? 0 : intval ($_POST['faceid']);
  if (!empty ($_POST['refer']))
    {
      $_SGLOBAL['refer'] = $_POST['refer'];
    }
  if (!empty ($faceid))
    {
      $message = $message."[em:".$faceid.":]";
    }
  $_GET['op'] = "add";
  if (!empty ($cid))
    {
      $_GET['op'] = "edit";
    }
}

elseif (submitcheck ('commentsubmit'))
{
  $idtype = $_POST['idtype'];
  $id = intval ($_POST['id']);
  $cid = empty ($_POST['cid']) ? 0 : intval ($_POST['cid']);
  if (!checkperm ('allowcomment'))
    {
      ckspacelog ();
      showmessage ('no_privilege');
    }
  ckrealname ('comment');
  cknewuser ();
  $waittime = interval_check ('post');
  if ($waittime > 0)
    {
      showmessage ('operating_too_fast', '', 1, array ($waittime));
    }
  $message = getstr ($_POST['message'], 0, 1, 1, 1, 2);
  if (strlen ($message) < 2)
    {
      showmessage ('content_is_too_short');
    }
  $summay = getstr ($message, 150, 1, 1, 0, 0, -1);
  $comment = array ();
  if ($cid)
    {
      $query =
	$_SGLOBAL['db']->query ("\x53\x45\x4c\x45CT * F\x52OM ".
				tname ('comment').
				" WHERE cid='$cid' AND id='$id' AND idtype='$_POST[idtype]'");
      $comment = $_SGLOBAL['db']->fetch_array ($query);
      if ($comment && $comment['authorid'] != $_SGLOBAL['supe_uid'])
	{
	  if ($comment['author'] == '')
	    {
	      $_SN[$comment['authorid']] = lang ('hidden_username');
	    }
	  else
	    {
	      realname_set ($comment['authorid'], $comment['author']);
	      realname_get ();
	    }
	  $comment['message'] =
	    preg_replace
	    ("/\<div \x63\x6c\141\x73s=\"quote\"\>\<span \143\154\x61\163s=\"q\"\>.*?\<\/span\>\<\/div\>/\x69s",
	     '', $comment['message']);
	  $comment['message'] = html2bbcode ($comment['message']);
	  $message =
	    addslashes
	    ("<div \x63\x6c\x61\163\x73=\"quote\"><span c\154\x61ss=\"q\"><b>".
	     $_SN[$comment['authorid']]."</b>: ".getstr ($comment['message'],
							 150, 0, 0, 0, 2,
							 1).'</span></div>').
	    $message;
	  if ($comment['idtype'] == 'uid')
	    {
	      $id = $comment['authorid'];
	    }
	}
      else
	{
	  $comment = array ();
	}
    }
  $hotarr = array ();
  $stattype = '';
  switch ($idtype)
    {
    case 'uid':
      $tospace = getspace ($id);
      $stattype = 'wall';
      break;
    case 'picid':
      $query =
	$_SGLOBAL['db']->query ("\123E\x4c\105\x43\124 \x70.*, pf.hotuser
				\x46\122O\115 ".tname ('pic')." \160
				LEFT JOIN ".tname ('picfield')." pf
				\117\x4e pf.picid=p.picid
				WHERE p.picid='$id'");
      $pic = $_SGLOBAL['db']->fetch_array ($query);
      if (empty ($pic))
	{
	  showmessage ('view_images_do_not_exist');
	}
      $tospace = getspace ($pic['uid']);
      $album = array ();
      if ($pic['albumid'])
	{
	  $query =
	    $_SGLOBAL['db']->
	    query ("\123\x45\x4c\105\103\124 * F\122\x4f\x4d ".
		   tname ('album')." WHERE albumid='$pic[albumid]'");
	  if (!$album = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      updatetable ('pic', array ('albumid' = >0),
			   array ('albumid' = >$pic['albumid']));
	    }
	}
      if (!ckfriend ($album['uid'], $album['friend'], $album['target_ids']))
	{
	  showmessage ('no_privilege');
	}
      elseif (!$tospace['self'] && $album['friend'] == 4)
      {
	$cookiename = "view_pwd_album_$album[albumid]";
	$cookievalue =
	  empty ($_SCOOKIE[$cookiename]) ? '' : $_SCOOKIE[$cookiename];
	if ($cookievalue != md5 (md5 ($album['password'])))
	  {
	    showmessage ('no_privilege');
	  }
      }
      $hotarr = array ('picid', $pic['picid'], $pic['hotuser']);
      $stattype = 'piccomment';
      break;
    case 'blogid':
      $query =
	$_SGLOBAL['db']->query ("S\x45\114\105\x43T b.*, bf.target_ids, bf.hotuser
				\x46\122\x4f\x4d ".tname ('blog')." b
				LEFT JOIN ".tname ('blogfield').
				" bf \117\x4e bf.blogid=b.blogid
				WHERE b.blogid='$id'");
      $blog = $_SGLOBAL['db']->fetch_array ($query);
      if (empty ($blog))
	{
	  showmessage ('view_to_info_did_not_exist');
	}
      $tospace = getspace ($blog['uid']);
      if (!ckfriend ($blog['uid'], $blog['friend'], $blog['target_ids']))
	{
	  showmessage ('no_privilege');
	}
      elseif (!$tospace['self'] && $blog['friend'] == 4)
      {
	$cookiename = "view_pwd_blog_$blog[blogid]";
	$cookievalue =
	  empty ($_SCOOKIE[$cookiename]) ? '' : $_SCOOKIE[$cookiename];
	if ($cookievalue != md5 (md5 ($blog['password'])))
	  {
	    showmessage ('no_privilege');
	  }
      }
      if (!empty ($blog['noreply']))
	{
	  showmessage ('do_not_accept_comments');
	}
      if ($blog['target_ids'])
	{
	  $blog['target_ids']. = ",$blog[uid]";
	}
      $hotarr = array ('blogid', $blog['blogid'], $blog['hotuser']);
      $stattype = 'blogcomment';
      break;
    case 'sid':
      $query =
	$_SGLOBAL['db']->query ("SEL\105\x43T * F\x52\117\x4d ".
				tname ('share')." WHERE sid='$id'");
      $share = $_SGLOBAL['db']->fetch_array ($query);
      if (empty ($share))
	{
	  showmessage ('sharing_does_not_exist');
	}
      $tospace = getspace ($share['uid']);
      $hotarr = array ('sid', $share['sid'], $share['hotuser']);
      $stattype = 'sharecomment';
      break;
    case 'pid':
      $query = $_SGLOBAL['db']->query ("S\x45L\x45\103\x54 p.*, pf.hotuser
				\x46RO\115 ".tname ('poll')." \x70
				LEFT JOIN ".tname ('pollfield')." pf \x4f\x4e pf.pid=\x70.pid
				WHERE \160.pid='$id'");
      $poll = $_SGLOBAL['db']->fetch_array ($query);
      if (empty ($poll))
	{
	  showmessage ('voting_does_not_exist');
	}
      $tospace = getspace ($poll['uid']);
      if ($poll['noreply'])
	{
	  if (!$tospace['self']
	       && !in_array ($_SGLOBAL['supe_uid'], $tospace['friends']))
	    {
	      showmessage ('the_vote_only_allows_friends_to_comment');
	    }
	}
      $hotarr = array ('pid', $poll['pid'], $poll['hotuser']);
      $stattype = 'pollcomment';
      break;
    case 'eventid':
      $query =
	$_SGLOBAL['db']->
	query ("\123\105\x4cE\103\124 e.*, ef.* \x46\x52\117\x4d ".
	       tname ('event')." e LEFT JOIN ".tname ("eventfield").
	       " ef ON e.eventid=ef.eventid WHERE e.eventid='$id'");
      $event = $_SGLOBAL['db']->fetch_array ($query);
      if (empty ($event))
	{
	  showmessage ('event_does_not_exist');
	}
      if ($event['grade'] < -1)
	{
	  showmessage ('event_is_closed');
	}
      elseif ($event['grade'] <= 0)
      {
	showmessage ('event_under_verify');
      }
      if (!$event['allowpost'])
	{
	  $query =
	    $_SGLOBAL['db']->query ("\x53\105L\x45CT * FR\x4f\115 ".
				    tname ("userevent").
				    " WHERE eventid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
	  $value = $_SGLOBAL['db']->fetch_array ($query);
	  if (empty ($value) || $value['status'] < 2)
	    {
	      showmessage ('event_only_allows_members_to_comment');
	    }
	}
      $tospace = getspace ($event['uid']);
      $hotarr = array ('eventid', $event['eventid'], $event['hotuser']);
      $stattype = 'eventcomment';
      break;
    default:
      showmessage ('non_normal_operation');
      break;
    }
  if (empty ($tospace))
    {
      showmessage ('space_does_not_exist');
    }
  if ($tospace['videostatus'])
    {
      if ($idtype == 'uid')
	{
	  ckvideophoto ('wall', $tospace);
	}
      else
	{
	  ckvideophoto ('comment', $tospace);
	}
    }
  if (isblacklist ($tospace['uid']))
    {
      showmessage ('is_blacklist');
    }
  if ($hotarr && $tospace['uid'] != $_SGLOBAL['supe_uid'])
    {
      hot_update ($hotarr[0], $hotarr[1], $hotarr[2]);
    }
  $fs = array ();
  $fs['icon'] = 'comment';
  $fs['target_ids'] = $fs['friend'] = '';
  switch ($_POST['idtype'])
    {
    case 'uid':
      $fs['icon'] = 'wall';
      $fs['title_template'] = cplang ('feed_comment_space');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.p\x68\160?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>");
      $fs['body_template'] = '';
      $fs['body_data'] = array ();
      $fs['body_general'] = '';
      $fs['images'] = array ();
      $fs['image_links'] = array ();
      break;
    case 'picid':
      $fs['title_template'] = cplang ('feed_comment_image');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.\x70h\160?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>");
      $fs['body_template'] = '{pic_title}';
      $fs['body_data'] = array ('pic_title' = >$pic['title']);
      $fs['body_general'] = $summay;
      $fs['images'] =
	array (pic_get ($pic['filepath'], $pic['thumb'], $pic['remote']));
      $fs['image_links'] =
	array
	("space.php?uid=$tospace[uid]&\x64\x6f=album&picid=$pic[picid]");
      $fs['target_ids'] = $album['target_ids'];
      $fs['friend'] = $album['friend'];
      break;
    case 'blogid':
      $_SGLOBAL['db']->query ("UPDATE ".tname ('blog').
			       " SET replynum=replynum+1 WHERE blogid='$id'");
      $fs['title_template'] = cplang ('feed_comment_blog');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.ph\x70?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>", 'blog' =
				 >"<a href=\"space.\x70\x68\x70?uid=$tospace[uid]&d\x6f=blog&id=$id\">$blog[subject]</a>");
      $fs['body_template'] = '';
      $fs['body_data'] = array ();
      $fs['body_general'] = '';
      $fs['target_ids'] = $blog['target_ids'];
      $fs['friend'] = $blog['friend'];
      break;
    case 'sid':
      $fs['title_template'] = cplang ('feed_comment_share');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.\x70\x68\x70?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>", 'share' =
				 >"<a href=\"space.\x70h\160?uid=$tospace[uid]&\144\157=share&id=$id\">".
				 str_replace (cplang ('share_action'), '',
					      $share['title_template']).
				 "</a>");
      $fs['body_template'] = '';
      $fs['body_data'] = array ();
      $fs['body_general'] = '';
      break;
    case 'eventid':
      $fs['title_template'] = cplang ('feed_comment_event');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.\160\150\x70?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>", 'event' =
				 >'<a href="space.php?do=event&id='.
				 $event['eventid'].'">'.$event['title'].
				 '</a>');
      $fs['body_template'] = '';
      $fs['body_data'] = array ();
      $fs['body_general'] = '';
      break;
    case 'pid':
      $_SGLOBAL['db']->query ("UPDATE ".tname ('poll').
				" SET replynum=replynum+1 WHERE pid='$id'");
      $fs['title_template'] = cplang ('feed_comment_poll');
      $fs['title_data'] = array ('touser' =
				 >"<a href=\"space.\160hp?uid=$tospace[uid]\">".
				 $_SN[$tospace['uid']]."</a>", 'poll' =
				 >"<a href=\"space.ph\x70?uid=$tospace[uid]&\144\157=poll&pid=$id\">$poll[subject]</a>");
      $fs['body_template'] = '';
      $fs['body_data'] = array ();
      $fs['body_general'] = '';
      $fs['friend'] = '';
      break;
    }
  $setarr = array ('uid' = >$tospace['uid'], 'id' = >$id, 'idtype' =
		   >$_POST['idtype'], 'authorid' =
		   >$_SGLOBAL['supe_uid'], 'author' =
		   >$_SGLOBAL['supe_username'], 'dateline' =
		   >$_SGLOBAL['timestamp'], 'message' = >$message, 'ip' =
		   >getonlineip ());
  $cid = inserttable ('comment', $setarr, 1);
  $action = 'comment';
  $becomment = 'getcomment';
  switch ($_POST['idtype'])
    {
    case 'uid':
      $n_url = "space.\x70\x68\160?uid=$tospace[uid]&\x64o=wall&cid=$cid";
      $note_type = 'wall';
      $note = cplang ('note_wall', array ($n_url));
      $q_note = cplang ('note_wall_reply', array ($n_url));
      if ($comment)
	{
	  $msg = 'note_wall_reply_success';
	  $magvalues = array ($_SN[$tospace['uid']]);
	  $becomment = '';
	}
      else
	{
	  $msg = 'do_success';
	  $magvalues = array ();
	  $becomment = 'getguestbook';
	}
      $msgtype = 'comment_friend';
      $q_msgtype = 'comment_friend_reply';
      $action = 'guestbook';
      break;
    case 'picid':
      $n_url =
	"space.\x70h\160?uid=$tospace[uid]&\144\x6f=album&picid=$id&cid=$cid";
      $note_type = 'piccomment';
      $note = cplang ('note_pic_comment', array ($n_url));
      $q_note = cplang ('note_pic_comment_reply', array ($n_url));
      $msg = 'do_success';
      $magvalues = array ();
      $msgtype = 'photo_comment';
      $q_msgtype = 'photo_comment_reply';
      break;
    case 'blogid':
      $n_url =
	"space.\x70\150p?uid=$tospace[uid]&\x64\x6f=blog&id=$id&cid=$cid";
      $note_type = 'blogcomment';
      $note = cplang ('note_blog_comment', array ($n_url, $blog['subject']));
      $q_note = cplang ('note_blog_comment_reply', array ($n_url));
      $msg = 'do_success';
      $magvalues = array ();
      $msgtype = 'blog_comment';
      $q_msgtype = 'blog_comment_reply';
      break;
    case 'sid':
      $n_url =
	"space.\x70\150\x70?uid=$tospace[uid]&\x64\x6f=share&id=$id&cid=$cid";
      $note_type = 'sharecomment';
      $note = cplang ('note_share_comment', array ($n_url));
      $q_note = cplang ('note_share_comment_reply', array ($n_url));
      $msg = 'do_success';
      $magvalues = array ();
      $msgtype = 'share_comment';
      $q_msgtype = 'share_comment_reply';
      break;
    case 'pid':
      $n_url =
	"space.p\x68\160?uid=$tospace[uid]&\x64\x6f=poll&pid=$id&cid=$cid";
      $note_type = 'pollcomment';
      $note = cplang ('note_poll_comment', array ($n_url, $poll['subject']));
      $q_note = cplang ('note_poll_comment_reply', array ($n_url));
      $msg = 'do_success';
      $magvalues = array ();
      $msgtype = 'poll_comment';
      $q_msgtype = 'poll_comment_reply';
      break;
    case 'eventid':
      $n_url = "space.p\150\160?do=event&id=$id&view=comment&cid=$cid";
      $note_type = 'eventcomment';
      $note = cplang ('note_event_comment', array ($n_url));
      $q_note = cplang ('note_event_comment_reply', array ($n_url));
      $msg = 'do_success';
      $magvalues = array ();
      $msgtype = 'event_comment';
      $q_msgtype = 'event_comment_reply';
      break;
    }
  if (empty ($comment))
    {
      if ($tospace['uid'] != $_SGLOBAL['supe_uid'])
	{
	  if (ckprivacy ('comment', 1))
	    {
	      feed_add ($fs['icon'], $fs['title_template'], $fs['title_data'],
			$fs['body_template'], $fs['body_data'],
			$fs['body_general'], $fs['images'],
			$fs['image_links'], $fs['target_ids'], $fs['friend']);
	    }
	  notification_add ($tospace['uid'], $note_type, $note);
	  if ($_POST['idtype'] == 'uid'
	       && $tospace['updatetime'] == $tospace['dateline'])
	    {
	      include_once S_ROOT.'./uc_client/client.php';
	      uc_pm_send ($_SGLOBAL['supe_uid'], $tospace['uid'],
			  cplang ('wall_pm_subject'),
			  cplang ('wall_pm_message',
				  array (addslashes (getsiteurl ().$n_url))),
			  1, 0, 0);
	    }
	  smail ($tospace['uid'], '',
		  cplang ($msgtype,
			  array ($_SN[$space['uid']],
				 shtmlspecialchars (getsiteurl ().$n_url))),
		  '', $msgtype);
	}
    }
  elseif ($comment['authorid'] != $_SGLOBAL['supe_uid'])
  {
    smail ($comment['authorid'], '',
	    cplang ($q_msgtype,
		    array ($_SN[$space['uid']],
			   shtmlspecialchars (getsiteurl ().$n_url))), '',
	    $q_msgtype);
    notification_add ($comment['authorid'], $note_type, $q_note);
  }
  if ($stattype)
    {
      updatestat ($stattype);
    }
  if ($tospace['uid'] != $_SGLOBAL['supe_uid'])
    {
      $needle = $id;
      if ($_POST['idtype'] != 'uid')
	{
	  $needle = $_POST['idtype'].$id;
	}
      else
	{
	  $needle = $tospace['uid'];
	}
      getreward ($action, 1, 0, $needle);
      if ($becomment)
	{
	  if ($_POST['idtype'] == 'uid')
	    {
	      $needle = $_SGLOBAL['supe_uid'];
	    }
	  getreward ($becomment, 1, $tospace['uid'], $needle, 0);
	}
    }
  if (!empty ($n_url))
    {
      $_POST['refer'] = $n_url;
    }
  showmessage ($msg, $_POST['refer'], 0, $magvalues);
}

$cid = empty ($_GET['cid']) ? 0 : intval ($_GET['cid']);
if ($_GET['op'] == 'edit')
  {
    $query =
      $_SGLOBAL['db']->query ("\x53\x45\114\105\x43\x54 * \106\122OM ".
			      tname ('comment').
			      " WHERE cid='$cid' AND authorid='$_SGLOBAL[supe_uid]'");
    if (!$comment = $_SGLOBAL['db']->fetch_array ($query))
      {
	showmessage ('no_privilege');
      }
    if (submitcheck ('editsubmit'))
      {
	$message = getstr ($_POST['message'], 0, 1, 1, 1, 2);
	if (strlen ($message) < 2)
	  showmessage ('content_is_too_short');
	updatetable ('comment', array ('message' = >$message),
		     array ('cid' = >$comment['cid']));
	showmessage ('do_success', $_POST['refer'], 0);
      }
    if (submitcheck ('facesubmit'))
      {
	$comment['message'] = $message;
      }
    else
      {
	$comment['message'] = html2bbcode ($comment['message']);
      }
  }
elseif ($_GET['op'] == 'delete')
{
  if (submitcheck ('deletesubmit'))
    {
      include_once (S_ROOT.'./source/function_delete.php');
      if (deletecomments (array ($cid)))
	{
	  showmessage ('do_success', $_POST['refer'], 0);
	}
      else
	{
	  showmessage ('no_privilege');
	}
    }
}

elseif ($_GET['op'] == 'reply')
{
  $query =
    $_SGLOBAL['db']->query ("\123E\x4c\105\x43\x54 * \106\x52\117\115 ".
			    tname ('comment')." WHERE cid='$cid'");
  if (!$comment = $_SGLOBAL['db']->fetch_array ($query))
    {
      showmessage ('comments_do_not_exist');
    }
}

elseif ($_GET['op'] == 'face' || $_GET['op'] == 'add')
{
}

else
{
  showmessage ('no_privilege');
}

include template ('cp_comment');
? >
