<?php
  if (!defined ('IN_UCHOME') || !defined ('qVh0gqGnK'))
  {
    exit ('Access Denied');
  }
$minhot = $_SCONFIG['feedhotmin'] < 1 ? 3 : $_SCONFIG['feedhotmin'];
$id = empty ($_GET['id']) ? 0 : intval ($_GET['id']);
$picid = empty ($_GET['picid']) ? 0 : intval ($_GET['picid']);
$page = empty ($_GET['page']) ? 1 : intval ($_GET['page']);
if ($page < 1)
  $page = 1;
@include_once (S_ROOT.'./data/data_click.php');
$clicks =
empty ($_SGLOBAL['click']['picid']) ? array () : $_SGLOBAL['click']['picid'];
if ($id)
  {
    $perpage = 6;
    $perpage = mob_perpage ($perpage);
    $start = ($page - 1) * $perpage;
    ckstart ($start, $perpage);
    if ($id > 0)
      {
	$query =
	  $_SGLOBAL['db']->query ("\x53\105\114E\103T * \106\x52\x4fM ".
				  tname ('album').
				  " WHERE albumid='$id' AND uid='$space[uid]' LIMIT 1");
	$album = $_SGLOBAL['db']->fetch_array ($query);
	if (empty ($album))
	  {
	    showmessage ('to_view_the_photo_does_not_exist');
	  }
	ckfriend_album ($album);
	$wheresql = "albumid='$id'";
	$count = $album['picnum'];
      }
    else
      {
	$wheresql = "albumid='0' AND uid='$space[uid]'";
	$count =
	  getcount ('pic', array ('albumid' = >0, 'uid' = >$space['uid']));
	$album = array ('uid' = >$space['uid'], 'albumid' = >-1, 'albumname' =
			>lang ('default_albumname'), 'picnum' = >$count);
      }
    $list = array ();
    if ($count)
      {
	$query =
	  $_SGLOBAL['db']->query ("SE\x4c\x45C\x54 * \106\122OM ".
				  tname ('pic').
				  " WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array ($query))
	  {
	    $value['pic'] =
	      pic_get ($value['filepath'], $value['thumb'], $value['remote']);
	    $list[] = $value;
	  }
      }
    $multi =
      multi ($count, $perpage, $page,
	     "space.\x70\150p?uid=$album[uid]&do=$do&id=$id");
    $_TPL['css'] = 'album';
    include_once template ("space_album_view");
  }
elseif ($picid)
{
  if (empty ($_GET['goto']))
    $_GET['goto'] = '';
  $eventid = intval ($eventid);
  if (empty ($eventid))
    {
      $query =
	$_SGLOBAL['db']->query ("\123\x45L\x45\103\x54 * \106\122\117M ".
				tname ('pic').
				" WHERE picid='$picid' AND uid='$space[uid]' LIMIT 1");
      $pic = $_SGLOBAL['db']->fetch_array ($query);
    }
  if ($_GET['goto'] == 'up')
    {
      if ($eventid)
	{
	  $query =
	    $_SGLOBAL['db']->query ("\123\x45\114ECT pic.*, ep.* FRO\115 ".
				    tname ('eventpic')." ep LEFT JOIN ".
				    tname ("pic").
				    " pic O\x4e ep.picid = pic.picid WHERE ep.eventid='$eventid' AND ep.picid > '$pic[picid]' ORDER BY ep.picid A\x53\x43 LIMIT 0,1");
	  if (!$newpic = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      $query =
		$_SGLOBAL['db']->
		query ("\123E\x4c\x45C\x54 pic.*, ep.* \106\x52\x4f\x4d ".
		       tname ('eventpic')." ep LEFT JOIN ".tname ("pic").
		       " pic \117N ep.picid = pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid \101\123C LIMIT 1");
	      $pic = $_SGLOBAL['db']->fetch_array ($query);
	    }
	  else
	    {
	      $pic = $newpic;
	    }
	}
      else
	{
	  $query =
	    $_SGLOBAL['db']->
	    query ("S\x45\x4c\105\103\x54 * \x46\122\117\x4d ".tname ('pic').
		   " WHERE albumid='$pic[albumid]' AND uid='$space[uid]' AND picid>$picid ORDER BY picid LIMIT 1");
	  if (!$newpic = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      $query =
		$_SGLOBAL['db']->
		query ("\123\x45\x4cE\x43\124 * \106R\x4f\x4d ".tname ('pic').
		       " WHERE albumid='$pic[albumid]' AND uid='$space[uid]' ORDER BY picid LIMIT 1");
	      $pic = $_SGLOBAL['db']->fetch_array ($query);
	    }
	  else
	    {
	      $pic = $newpic;
	    }
	}
    }
  elseif ($_GET['goto'] == 'down')
  {
    if ($eventid)
      {
	$query =
	  $_SGLOBAL['db']->
	  query ("\x53\105\x4cE\103\x54 pic.*, ep.* \106\122O\115 ".
		 tname ('eventpic')." ep LEFT JOIN ".tname ("pic").
		 " pic \117\116 ep.picid = pic.picid WHERE ep.eventid='$eventid' AND ep.picid < '$pic[picid]' ORDER BY ep.picid DESC LIMIT 0,1");
	if (!$newpic = $_SGLOBAL['db']->fetch_array ($query))
	  {
	    $query =
	      $_SGLOBAL['db']->
	      query ("\x53\x45L\x45\103\x54 pic.*, ep.* \x46\x52\117\115 ".
		     tname ('eventpic')." ep LEFT JOIN ".tname ("pic").
		     " pic O\116 ep.picid = pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid DESC LIMIT 1");
	    $pic = $_SGLOBAL['db']->fetch_array ($query);
	  }
	else
	  {
	    $pic = $newpic;
	  }
      }
    else
      {
	$query =
	  $_SGLOBAL['db']->query ("SE\x4cEC\124 * \x46R\117\115 ".
				  tname ('pic').
				  " WHERE albumid='$pic[albumid]' AND uid='$space[uid]' AND picid<$picid ORDER BY picid DESC LIMIT 1");
	if (!$newpic = $_SGLOBAL['db']->fetch_array ($query))
	  {
	    $query =
	      $_SGLOBAL['db']->query ("\x53EL\x45C\x54 * \x46R\117\x4d ".
				      tname ('pic').
				      " WHERE albumid='$pic[albumid]' AND uid='$space[uid]' ORDER BY picid DESC LIMIT 1");
	    $pic = $_SGLOBAL['db']->fetch_array ($query);
	  }
	else
	  {
	    $pic = $newpic;
	  }
      }
  }
  $picid = $pic['picid'];
  if (empty ($picid))
    {
      showmessage ('view_images_do_not_exist');
    }
  if ($eventid)
    {
      $theurl =
	"space.\160\x68\160?\x64\157=event&id=$eventid&view=pic&picid=$picid";
    }
  else
    {
      $theurl = "space.\160\x68\x70?uid=$pic[uid]&d\x6f=$do&picid=$picid";
    }
  $album = array ();
  if ($pic['albumid'])
    {
      $query =
	$_SGLOBAL['db']->query ("\123\x45\114\105CT * \x46\122O\115 ".
				tname ('album').
				" WHERE albumid='$pic[albumid]'");
      if (!$album = $_SGLOBAL['db']->fetch_array ($query))
	{
	  updatetable ('pic', array ('albumid' = >0),
		       array ('albumid' = >$pic['albumid']));
	}
    }
  if ($album)
    {
      if ($eventid)
	{
	  $query =
	    $_SGLOBAL['db']->query ("S\x45\114\105CT * \x46\x52\x4f\115 ".
				    tname ("eventpic").
				    " WHERE eventid='$eventid' AND picid='$picid'");
	  if (!$eventpic = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      showmessage ('pic_not_share_to_event');
	    }
	  $album['picnum'] = $piccount;
	}
      else
	{
	  ckfriend_album ($album);
	}
    }
  else
    {
      $album['picnum'] =
	getcount ('pic', array ('uid' = >$pic['uid'], 'albumid' = >0));
      $album['albumid'] = $pic['albumid'] = '-1';
    }
  if ($album['picnum'])
    {
      if ($_GET['goto'] == 'down')
	{
	  $sequence =
	    empty ($_SCOOKIE['pic_sequence']) ? $album['picnum'] :
	    intval ($_SCOOKIE['pic_sequence']);
	  $sequence++;
	  if ($sequence > $album['picnum'])
	    {
	      $sequence = 1;
	    }
	}
      elseif ($_GET['goto'] == 'up')
      {
	$sequence =
	  empty ($_SCOOKIE['pic_sequence']) ? $album['picnum'] :
	  intval ($_SCOOKIE['pic_sequence']);
	$sequence--;
	if ($sequence < 1)
	  {
	    $sequence = $album['picnum'];
	  }
      }
      else
      {
	$sequence = 1;
      }
      ssetcookie ('pic_sequence', $sequence);
    }
  $pic['pic'] = pic_get ($pic['filepath'], $pic['thumb'], $pic['remote'], 0);
  $pic['pic_thumb'] =
    pic_get ($pic['filepath'], $pic['thumb'], $pic['remote']);
  $pic['size'] = formatsize ($pic['size']);
  $exifs = array ();
  $allowexif = function_exists ('exif_read_data');
  if (isset ($_GET['exif']) && $allowexif)
    {
      include_once (S_ROOT.'./source/function_exif.php');
      $exifs = getexif ($pic['pic']);
    }
  $perpage = 10;
  $perpage = mob_perpage ($perpage);
  $start = ($page - 1) * $perpage;
  ckstart ($start, $perpage);
  $cid = empty ($_GET['cid']) ? 0 : intval ($_GET['cid']);
  $csql = $cid ? "cid='$cid' AND" : '';
  $siteurl = getsiteurl ();
  $list = array ();
  $count =
    $_SGLOBAL['db']->result ($_SGLOBAL['db']->
			     query ("\123\105\114\105CT COUNT(*) \106RO\115 ".
				    tname ('comment').
				    " WHERE $csql id='$pic[picid]' AND idtype='picid'"),
			     0);
  if ($count)
    {
      $query =
	$_SGLOBAL['db']->query ("\123\x45LEC\124 * \106\x52OM ".
				tname ('comment').
				" WHERE $csql id='$pic[picid]' AND idtype='picid' ORDER BY dateline DESC LIMIT $start,$perpage");
      while ($value = $_SGLOBAL['db']->fetch_array ($query))
	{
	  realname_set ($value['authorid'], $value['author']);
	  $list[] = $value;
	}
    }
  $multi = multi ($count, $perpage, $page, $theurl, '', 'pic_comment');
  if (empty ($album['albumname']))
    $album['albumname'] = lang ('default_albumname');
  $pic_url = $pic['pic'];
  if (!preg_match ("/^http\:\/\/.+?/i", $pic['pic']))
    {
      $pic_url = getsiteurl ().$pic['pic'];
    }
  $pic_url2 = rawurlencode ($pic['pic']);
  if (!$space['self'])
    {
      inserttable ('log', array ('id' = >$space['uid'], 'idtype' = >'uid'));
    }
  if (!$eventid)
    {
      $query =
	$_SGLOBAL['db']->query ("\123\x45L\105\x43\124 * F\x52O\115 ".
				tname ("eventpic")." ep LEFT JOIN ".
				tname ("event").
				" e \x4f\x4e ep.eventid=e.eventid WHERE ep.picid='$picid'");
      $event = $_SGLOBAL['db']->fetch_array ($query);
    }
  $hash = md5 ($pic['uid']."\t".$pic['dateline']);
  $id = $pic['picid'];
  $idtype = 'picid';
  foreach ($clicks as $key = >$value)
  {
    $value['clicknum'] = $pic["click_$key"];
    $value['classid'] = mt_rand (1, 4);
    if ($value['clicknum'] > $maxclicknum)
      $maxclicknum = $value['clicknum'];
    $clicks[$key] = $value;
  }
  $clickuserlist = array ();
  $query =
    $_SGLOBAL['db']->query ("S\x45\x4c\105CT * \x46\x52O\115 ".
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
  $topic = topic_get ($pic['topicid']);
  if (empty ($eventid))
    {
      realname_get ();
      $_TPL['css'] = 'album';
      include_once template ("space_album_pic");
    }
}

else
{
  $perpage = 6;
  $perpage = mob_perpage ($perpage);
  $start = ($page - 1) * $perpage;
  ckstart ($start, $perpage);
  $_GET['friend'] = intval ($_GET['friend']);
  $default = array ();
  $f_index = '';
  $list = array ();
  $pricount = 0;
  $picmode = 0;
  if (empty ($_GET['view'])
      && ($space['friendnum'] < $_SCONFIG['showallfriendnum']))
    {
      $_GET['view'] = 'all';
    }
  if ($_GET['view'] == 'click')
    {
      $theurl = "space.\160\x68\x70?uid=$space[uid]&\144\157=$do&view=click";
      $actives = array ('click' = >' class="active"');
      $clickid = intval ($_GET['clickid']);
      if ($clickid)
	{
	  $theurl. = "&clickid=$clickid";
	  $wheresql = " AND c.clickid='$clickid'";
	  $click_actives = array ($clickid = >' class="current"');
	}
      else
	{
	  $wheresql = '';
	  $click_actives = array ('all' = >' class="current"');
	}
      $picmode = 1;
      $count =
	$_SGLOBAL['db']->result ($_SGLOBAL['db']->
				 query
				 ("\x53E\x4c\105CT COUNT(*) FR\x4f\x4d ".
				  tname ('clickuser').
				  " c WHERE c.uid='$space[uid]' AND c.idtype='picid' $wheresql"),
				 0);
      if ($count)
	{
	  $query =
	    $_SGLOBAL['db']->query ("S\105L\x45\103\x54 \x70.*,a.albumname, a.username, c.clickid \x46\x52\x4fM ".tname ('clickuser')." c
				LEFT JOIN ".tname ('pic')." \x70 \x4fN p.picid=c.id
				LEFT JOIN ".tname ('album').
				    " a ON a.albumid=\160.albumid
				WHERE c.uid='$space[uid]' AND c.idtype='picid' $wheresql
				ORDER BY c.dateline DESC LIMIT $start,$perpage");
	  while ($value = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      realname_set ($value['uid'], $value['username']);
	      $value['pic'] =
		pic_get ($value['filepath'], $value['thumb'],
			 $value['remote']);
	      $list[] = $value;
	    }
	}
    }
  elseif ($_GET['view'] == 'all')
  {
    $theurl = "space.\160\x68\x70?uid=$space[uid]&d\x6f=$do&view=all";
    $actives = array ('all' = >' class="active"');
    $wheresql = '1';
    $orderarr = array ('hot', 'dateline');
    foreach ($clicks as $value)
    {
      $orderarr[] = "click_$value[clickid]";
    }
    if (!in_array ($_GET['orderby'], $orderarr))
      $_GET['orderby'] = '';
    $_GET['day'] = intval ($_GET['day']);
    $_GET['hotday'] = 7;
    if ($_GET['orderby'] == 'dateline')
      {
	$all_actives = array ('dateline' = >' class="current"');
	$day_actives = array ();
	$theurl =
	  "space.\x70\x68p?uid=$space[uid]&\144\157=album&view=all&orderby=$_GET[orderby]";
      }
    else
      {
	if ($_GET['orderby'])
	  {
	    $ordersql = 'p.'.$_GET['orderby'];
	    $theurl =
	      "space.\160hp?uid=$space[uid]&d\x6f=album&view=all&orderby=$_GET[orderby]";
	    $all_actives = array ($_GET['orderby'] = >' class="current"');
	    if ($_GET['day'])
	      {
		$_GET['hotday'] = $_GET['day'];
		$daytime = $_SGLOBAL['timestamp'] - $_GET['day'] * 3600 * 24;
		$wheresql. = " AND \x70.dateline>='$daytime'";
		$theurl. = "&day=$_GET[day]";
		$day_actives = array ($_GET['day'] = >' class="active"');
	      }
	    else
	      {
		$day_actives = array (0 = >' class="active"');
	      }
	  }
	else
	  {
	    $ordersql = 'p.dateline';
	    $wheresql. = " AND \160.hot>='$minhot'";
	    $theurl = "space.p\x68p?uid=$space[uid]&\x64\157=album&view=all";
	    $all_actives = array ('all' = >' class="current"');
	  }
	$picmode = 1;
	$count =
	  $_SGLOBAL['db']->result ($_SGLOBAL['db']->
				   query
				   ("\x53\105\114E\103T COUNT(*) \106\x52\x4f\x4d ".
				    tname ('pic')." p WHERE $wheresql"), 0);
	if ($count)
	  {
	    $query =
	      $_SGLOBAL['db']->query ("\x53\x45\x4cECT \x70.*, a.albumname, a.friend, a.target_ids \x46\x52\117\x4d ".tname ('pic')." \x70
					LEFT JOIN ".tname ('album').
				      " a \117N a.albumid=\160.albumid
					WHERE $wheresql
					ORDER BY $ordersql DESC
					LIMIT $start,$perpage");
	    while ($value = $_SGLOBAL['db']->fetch_array ($query))
	      {
		if ($value['friend'] != 4
		    && ckfriend ($value['uid'], $value['friend'],
				 $value['target_ids']))
		  {
		    realname_set ($value['uid'], $value['username']);
		    $value['pic'] =
		      pic_get ($value['filepath'], $value['thumb'],
			       $value['remote']);
		    $list[] = $value;
		  }
		else
		  {
		    $pricount++;
		  }
	      }
	  }
      }
  }
  else
  {
    if (empty ($space['feedfriend']))
      $_GET['view'] = 'me';
    if ($_GET['view'] == 'me')
      {
	$wheresql = "uid='$space[uid]'";
	$theurl = "space.\x70\150\160?uid=$space[uid]&do=$do&view=me";
	$actives = array ('me' = >' class="active"');
      }
    else
      {
	$wheresql = "uid IN ($space[feedfriend])";
	$theurl = "space.ph\160?uid=$space[uid]&\x64\157=$do&view=we";
	$f_index = 'USE INDEX(updatetime)';
	$actives = array ('we' = >' class="active"');
	$fuid_actives = array ();
	$fusername = trim ($_GET['fusername']);
	$fuid = intval ($_GET['fuid']);
	if ($fusername)
	  {
	    $fuid = getuid ($fusername);
	  }
	if ($fuid && in_array ($fuid, $space['friends']))
	  {
	    $wheresql = "uid = '$fuid'";
	    $theurl =
	      "space.\x70\x68\160?uid=$space[uid]&\144o=$do&fuid=$fuid";
	    $f_index = '';
	    $fuid_actives = array ($fuid = >' selected');
	  }
	$query =
	  $_SGLOBAL['db']->
	  query ("\x53\x45\x4c\105\103\x54 * \x46\122\117\x4d ".
		 tname ('friend').
		 " WHERE uid='$space[uid]' AND \x73\164\141tus='1' ORDER BY num DESC, dateline DESC LIMIT 0,500");
	while ($value = $_SGLOBAL['db']->fetch_array ($query))
	  {
	    realname_set ($value['fuid'], $value['fusername']);
	    $userlist[] = $value;
	  }
      }
  }
  if (empty ($picmode))
    {
      if ($_GET['friend'])
	{
	  $wheresql. = " AND friend='$_GET[friend]'";
	  $theurl. = "&friend=$_GET[friend]";
	}
      if ($searchkey = stripsearchkey ($_GET['searchkey']))
	{
	  $wheresql. = " AND albumname LIKE '%$searchkey%'";
	  $theurl. = "&searchkey=$_GET[searchkey]";
	  cksearch ($theurl);
	}
      $count =
	$_SGLOBAL['db']->result ($_SGLOBAL['db']->
				 query
				 ("\123ELE\x43T COUNT(*) \x46\122\117\115 ".
				  tname ('album')." WHERE $wheresql"), 0);
      if ($wheresql == "uid='$space[uid]'" && $space['albumnum'] != $count)
	{
	  updatetable ('space', array ('albumnum' = >$count),
		       array ('uid' = >$space['uid']));
	}
      if ($count)
	{
	  $query =
	    $_SGLOBAL['db']->query ("SE\x4c\x45CT * \x46\x52\x4f\115 ".
				    tname ('album').
				    " $f_index WHERE $wheresql ORDER BY updatetime DESC LIMIT $start,$perpage");
	  while ($value = $_SGLOBAL['db']->fetch_array ($query))
	    {
	      realname_set ($value['uid'], $value['username']);
	      if ($value['friend'] != 4
		  && ckfriend ($value['uid'], $value['friend'],
			       $value['target_ids']))
		{
		  $value['pic'] =
		    pic_cover_get ($value['pic'], $value['picflag']);
		}
	      else
		{
		  $value['pic'] = 'image/nopublish.jpg';
		}
	      $list[] = $value;
	    }
	}
    }
  $multi = multi ($count, $perpage, $page, $theurl);
  realname_get ();
  $_TPL['css'] = 'album';
  include_once template ("space_album_list");
}

function ckfriend_album ($album)
{
  global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $space, $_SN;
  if (!ckfriend ($album['uid'], $album['friend'], $album['target_ids']))
    {
      include template ('space_privacy');
      exit ();
    }
  elseif (!$space['self'] && $album['friend'] == 4)
  {
    $cookiename = "view_pwd_album_$album[albumid]";
    $cookievalue =
      empty ($_SCOOKIE[$cookiename]) ? '' : $_SCOOKIE[$cookiename];
    if ($cookievalue != md5 (md5 ($album['password'])))
      {
	$invalue = $album;
	include template ('do_inputpwd');
	exit ();
      }
  }
};

? >
