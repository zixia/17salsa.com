<?php
  if (!defined ('IN_UCHOME'))
  {
    exit ('Access Denied');
  }
@include_once (S_ROOT.'./data/data_profield.php');
$eventid = empty ($_GET['eventid']) ? 0 : intval ($_GET['eventid']);
if ($eventid)
  {
    $query =
      $_SGLOBAL['db']->query ("S\x45L\x45\x43T e.* \x46\x52O\x4d ".
			      tname ("event").
			      " e WHERE e.eventid='$_GET[eventid]'");
    $event = $_SGLOBAL['db']->fetch_array ($query);
    if (empty ($event))
      {
	showmessage ('event_does_not_exist');
      }
    $query =
      $_SGLOBAL['db']->query ("S\105LE\x43\x54 * \106\122O\115 ".
			      tname ("userevent").
			      " WHERE uid = '$_SGLOBAL[supe_uid]' AND eventid = '$eventid'");
    $userevent = $_SGLOBAL['db']->fetch_array ($query);
  }
@include_once (S_ROOT.'./data/data_click.php');
$clicks =
empty ($_SGLOBAL['click']['tid']) ? array () : $_SGLOBAL['click']['tid'];
$page = empty ($_GET['page']) ? 1 : intval ($_GET['page']);
if ($page < 1)
  $page = 1;
$id = empty ($_GET['id']) ? 0 : intval ($_GET['id']);
if ($id)
  {
    $query =
      $_SGLOBAL['db']->query ("\x53\x45LE\103T * FRO\x4d ".tname ('thread').
			      " WHERE tid='$id' LIMIT 1");
    if (!$thread = $_SGLOBAL['db']->fetch_array ($query))
      {
	showmessage ('topic_does_not_exist');
      }
    $space = getspace ($thread['uid']);
    if ($space['flag'] == -1)
      {
	showmessage ('space_has_been_locked');
      }
    realname_set ($thread['uid'], $thread['username']);
    $tagid = $thread['tagid'];
    if ($eventid && $event['tagid'] != $tagid)
      {
	showmessage ('event_mtag_not_match');
      }
    if (!$eventid && $thread['eventid'])
      {
	$query =
	  $_SGLOBAL['db']->query ("\x53\x45\114E\103\x54 * \x46R\117M ".
				  tname ("event").
				  " WHERE eventid='$thread[eventid]' LIMIT 1");
	$event = $_SGLOBAL['db']->fetch_array ($query);
	if (empty ($event))
	  {
	    updatetable ('thread', array ("eventid" = >0),
			 array ("eventid" = >$thread['eventid']));
	  }
      }
    $mtag = getmtag ($tagid);
    if ($mtag['close'])
      {
	showmessage ('mtag_close');
      }
    if ($eventid && $event['public'] == 0 && $userevent['status'] < 2)
      {
	showmessage ('event_memberstatus_limit',
		     "space.\160h\x70?\x64o=event");
      }
    elseif (empty ($mtag['allowview']))
    {
      showmessage ('mtag_not_allow_to_do',
		   "space.\160\x68p?\144o=mtag&tagid=$tagid");
    }
    $perpage = 10;
    $start = ($page - 1) * $perpage;
    $count = $thread['replynum'];
    if ($count % $perpage == 0)
      {
	$perpage = $perpage + 1;
      }
    ckstart ($start, $perpage);
    $pid = empty ($_GET['pid']) ? 0 : intval ($_GET['pid']);
    $psql = $pid ? "(isthread='1' OR pid='$pid') AND" : '';
    $list = array ();
    $postnum = $start;
    $query =
      $_SGLOBAL['db']->query ("\123\x45L\105\x43\124 * \106R\x4f\115 ".
			      tname ('post').
			      " WHERE $psql tid='$thread[tid]' ORDER BY dateline LIMIT $start,$perpage");
    while ($value = $_SGLOBAL['db']->fetch_array ($query))
      {
	realname_set ($value['uid'], $value['username']);
	$value['num'] = $postnum;
	$list[] = $value;
	$postnum++;
      }
    if ($list[0]['isthread'])
      {
	$thread['content'] = $list[0];
	include_once (S_ROOT.'./source/function_blog.php');
	$thread['content']['message'] =
	  blog_bbcode ($thread['content']['message']);
	unset ($list[0]);
      }
    else
      {
	$thread['content'] = array ();
      }
    $multi =
      multi ($count, $perpage, $page,
	     "space.p\150\160?uid=$thread[uid]&\144\157=$do&id=$id");
    if (!$space['self'] && $_SCOOKIE['view_tid'] != $id)
      {
	$_SGLOBAL['db']->query ("UPDATE ".tname ('thread').
				" SET viewnum=viewnum+1 WHERE tid='$id'");
	inserttable ('log', array ('id' = >$space['uid'], 'idtype' = >'uid'));
	ssetcookie ('view_tid', $id);
      }
    $hash = md5 ($thread['uid']."\t".$thread['dateline']);
    $id = $thread['tid'];
    $idtype = 'tid';
    foreach ($clicks as $key = >$value)
    {
      $value['clicknum'] = $thread["click_$key"];
      $value['classid'] = mt_rand (1, 4);
      if ($value['clicknum'] > $maxclicknum)
	$maxclicknum = $value['clicknum'];
      $clicks[$key] = $value;
    }
    $clickuserlist = array ();
    $query =
      $_SGLOBAL['db']->query ("\x53\x45L\x45C\x54 * F\122\117\x4d ".
			      tname ('clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,18");
    while ($value = $_SGLOBAL['db']->fetch_array ($query))
      {
	realname_set ($value['uid'], $value['username']);
	$value['clickname'] = $clicks[$value['clickid']]['name'];
	$clickuserlist[] = $value;
      }
    $topic = topic_get ($thread['topicid']);
    realname_get ();
    $_TPL['css'] = 'thread';
    include_once template ("space_thread_view");
  }
else
  {
    $perpage = 10;
    $start = ($page - 1) * $perpage;
    ckstart ($start, $perpage);
    if (!in_array ($_GET['view'], array ('hot', 'new', 'me', 'all')))
      {
	$_GET['view'] = 'hot';
      }
    $wheresql = $f_index = '';
    if ($_GET['view'] == 'hot')
      {
	$minhot = $_SCONFIG['feedhotmin'] < 1 ? 3 : $_SCONFIG['feedhotmin'];
	$wheresql = "\x6d\x61\151\x6e.hot>='$minhot'";
	if ($page == 1)
	  {
	    $query =
	      $_SGLOBAL['db']->
	      query ("S\105\x4c\x45\103\124 * \106\122\117\115 ".
		     tname ('mtag').
		     " mt ORDER BY mt.threadnum DESC LIMIT 0,6");
	    while ($value = $_SGLOBAL['db']->fetch_array ($query))
	      {
		$value['title'] =
		  $_SGLOBAL['profield'][$value['fieldid']]['title'];
		if (empty ($value['pic']))
		  $value['pic'] = 'image/nologo.jpg';
		$rlist[] = $value;
	      }
	  }
      }
    elseif ($_GET['view'] == 'me')
    {
      $wheresql = "\x6d\x61\151\156.uid='$space[uid]'";
    }
    else
    {
      $wheresql = "1";
      $f_index = 'USE INDEX (lastpost)';
    }
    $theurl =
      "space.\x70\150\x70?uid=$space[uid]&\x64o=thread&view=$_GET[view]";
    $actives = array ($_GET['view'] = >' class="active"');
    $list = array ();
    $count = 0;
    if ($searchkey = stripsearchkey ($_GET['searchkey']))
      {
	$wheresql = "\x6d\x61\151n.subject LIKE '%$searchkey%'";
	$theurl. = "&searchkey=$_GET[searchkey]";
	cksearch ($theurl);
      }
    if ($wheresql)
      {
	$count =
	  $_SGLOBAL['db']->result ($_SGLOBAL['db']->
				   query
				   ("\123\105\x4c\105\x43T COUNT(*) \106\x52O\115 ".
				    tname ('thread').
				    " \155a\151n WHERE $wheresql"), 0);
	if ($wheresql == "\x6da\x69\x6e.uid='$space[uid]'"
	     && $space['threadnum'] != $count)
	  {
	    updatetable ('space', array ('threadnum' = >$count),
			 array ('uid' = >$space['uid']));
	  }
	if ($count)
	  {
	    $query =
	      $_SGLOBAL['db']->query ("\123EL\105C\x54 \x6da\151\x6e.*,\x66i\x65\154d.tagname,\x66\151\x65\x6cd.membernum,\146i\145\x6cd.fieldid,f\x69el\144.pic F\122O\115 ".tname ('thread')." m\x61\151n $f_index
				LEFT JOIN ".tname ('mtag').
				      " fie\x6c\144 \117N \x66\x69\145l\x64.tagid=\155a\x69\156.tagid WHERE $wheresql
				ORDER BY m\141i\x6e.lastpost DESC
				LIMIT $start,$perpage");
	    while ($value = $_SGLOBAL['db']->fetch_array ($query))
	      {
		realname_set ($value['uid'], $value['username']);
		realname_set ($value['lastauthorid'], $value['lastauthor']);
		$value['tagname'] = getstr ($value['tagname'], 20);
		$list[] = $value;
		if (empty ($value['pic']))
		  {
		    $value['pic'] = 'image/nologo.jpg';
		  }
	      }
	  }
      }
    $multi = multi ($count, $perpage, $page, $theurl);
    realname_get ();
    $_TPL['css'] = 'thread';
    include_once template ("space_thread_list");
  };

? >
