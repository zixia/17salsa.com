<?php    if(!defined('IN_UCHOME')) { exit('Access Denied'); } $op = empty($_GET['op'])?'':$_GET['op']; $uid = empty($_GET['uid'])?0:intval($_GET['uid']); $space['key'] = space_key($space); $actives = array($op=>' class="active"'); if($op == 'add') { if(!checkperm('allowfriend')) { ckspacelog(); showmessage('no_privilege'); }  
if($uid == $_SGLOBAL['supe_uid']) { showmessage('friend_self_error'); } if($space['friends'] && in_array($uid, $space['friends'])) { showmessage('you_have_friends'); }  
ckrealname('friend'); $tospace = getspace($uid); if(empty($tospace)) { showmessage('space_does_not_exist'); }  
if(isblacklist($tospace['uid'])) { showmessage('is_blacklist'); }  
$groups = getfriendgroup();  
$status = getfriendstatus($_SGLOBAL['supe_uid'], $uid); if($status == 1) { showmessage('you_have_friends'); } else {  
$maxfriendnum = checkperm('maxfriendnum'); if($maxfriendnum && $space['friendnum'] >= $maxfriendnum + $space['addfriend']) { if($_SGLOBAL['magic']['friendnum']) { showmessage('enough_of_the_number_of_friends_with_magic'); } else { showmessage('enough_of_the_number_of_friends'); } }  
$fstatus = getfriendstatus($uid, $_SGLOBAL['supe_uid']); if($fstatus == -1) {  
if($status == -1) {  
if($tospace['videostatus']) { ckvideophoto('friend', $tospace); }  
if(submitcheck('addsubmit')) { $setarr = array( 'uid' => $_SGLOBAL['supe_uid'], 'fuid' => $uid, 'fusername' => addslashes($tospace['username']), 'gid' => intval($_POST['gid']), 'note' => getstr($_POST['note'], 50, 1, 1), 'dateline' => $_SGLOBAL['timestamp'] ); inserttable('friend', $setarr);  
smail($uid, '', cplang('friend_subject',array($_SN[$space['uid']], getsiteurl().'cp.php?ac=friend&amp;op=request')), '', 'friend_add');  
$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+1 WHERE uid='$uid'"); showmessage('request_has_been_sent'); } else { include_once template('cp_friend'); exit(); } } else { showmessage('waiting_for_the_other_test'); } } else {  
if(submitcheck('add2submit')) {  
$gid = intval($_POST['gid']); friend_update($space['uid'], $space['username'], $tospace['uid'], $tospace['username'], 'add', $gid);  
 
if(ckprivacy('friend', 1)) { $fs = array(); $fs['icon'] = 'friend'; $fs['title_template'] = cplang('feed_friend_title'); $fs['title_data'] = array('touser'=>"<a href=\"space.\x70\150\160?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>"); $fs['body_template'] = ''; $fs['body_data'] = array(); $fs['body_general'] = ''; feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']); }  
$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");  
notification_add($uid, 'friend', cplang('note_friend_add')); showmessage('friends_add', $_POST['refer'], 1, array($_SN[$tospace['uid']])); } else { $op = 'add2'; include_once template('cp_friend'); exit(); } } } } elseif($op == 'ignore' || $op == 'delete' ) { if($op == 'ignore') { $Piq15gG3A = 'cp.php?ac=friend&op=request'; } else { $Piq15gG3A = 'space.php?do=friend'; }  
if($uid) { if(submitcheck('friendsubmit')) {  
$fstatus = getfriendstatus($uid, $space['uid']); if($fstatus == 1) {  
friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $uid, '', 'ignore'); } elseif ($fstatus == 0) { request_ignore($uid); } showmessage('do_success',$Piq15gG3A, 0); } } elseif($_GET['key'] == $space['key']) {  
$query = $_SGLOBAL['db']->query("\x53E\114\x45\103\124 uid F\x52\117M ".tname('friend')." WHERE fuid='$space[uid]' AND s\164\141\164\x75s='0' LIMIT 0,1"); if($value = $_SGLOBAL['db']->fetch_array($query)) {  
$uid = $value['uid']; $username = getcount('space', array('uid'=>$uid), 'username'); request_ignore($uid); showmessage('friend_ignore_next', 'cp.php?ac=friend&op=ignore&confirm=1&key='.$space['key'], 1, array($username)); } else { showmessage('do_success', $Piq15gG3A, 0); } } } elseif($op == 'addconfirm') { if($_GET['key'] == $space['key']) {  
$maxfriendnum = checkperm('maxfriendnum'); if($maxfriendnum && $space['friendnum'] >= $maxfriendnum + $space['addfriend']) { if($_SGLOBAL['magic']['friendnum']) { showmessage('enough_of_the_number_of_friends_with_magic'); } else { showmessage('enough_of_the_number_of_friends'); } }  
$query = $_SGLOBAL['db']->query("S\x45\x4c\x45\103\124 uid \106ROM ".tname('friend')." WHERE fuid='$space[uid]' AND \163t\141\x74\x75\x73='0' LIMIT 0,1"); if($value = $_SGLOBAL['db']->fetch_array($query)) { $uid = $value['uid']; $username = getcount('space', array('uid'=>$uid), 'username'); friend_update($space['uid'], $space['username'], $uid, $tospace['username'], 'add', 0);  
$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");  
showmessage('friend_addconfirm_next', 'cp.php?ac=friend&op=addconfirm&key='.$space['key'], 1, array($username)); } } showmessage('do_success', 'cp.php?ac=friend&op=request', 0); } elseif($op == 'syn') {  
if(isset($_SCOOKIE['synfriend']) || empty($_SCONFIG['uc_status'])) { exit(); } include_once S_ROOT.'./uc_client/client.php'; $buddylist = uc_friend_ls($_SGLOBAL['supe_uid'], 1, 999, 999, 2); 
$havas = array(); if($buddylist && is_array($buddylist)) { foreach($buddylist as $key => $buddy) { $uids[] = $buddy['uid']; } $members = array(); if($uids) { $query = $_SGLOBAL['db']->query("\123EL\105\103\x54 uid \106\122O\x4d ".tname('space')." WHERE uid IN (".simplode($uids).")"); while($member = $_SGLOBAL['db']->fetch_array($query)) { $members[] = $member['uid']; } } if($members) { foreach($buddylist as $key => $buddy) { if(in_array($buddy['uid'], $members)) { $havas[$buddy['uid']] = $buddy; } } } }  
if($havas) { $query = $_SGLOBAL['db']->query("S\105\114\105\x43T uid FR\x4fM ".tname('friend')." WHERE fuid='$_SGLOBAL[supe_uid]'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if(isset($havas[$value['uid']])) { unset($havas[$value['uid']]); } } }  
$blacklist = array(); $query = $_SGLOBAL['db']->query("\123\x45\114\105\103\x54 buid F\122\x4f\115 ".tname('blacklist')." WHERE uid='$_SGLOBAL[supe_uid]'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $blacklist[$value['buid']] = $value['buid']; }  
$addnum = 0; $inserts = array(); if($havas) { foreach ($havas as $value) { if($_SGLOBAL['supe_uid'] != $value['uid'] && empty($blacklist[$value['uid']])) { $value['username'] = addslashes($value['username']); if($value['direction'] == 3) { 
$inserts[] = "('$_SGLOBAL[supe_uid]','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')"; $inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','1','$_SGLOBAL[timestamp]')"; } else { 
$addnum++; $inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','0','$_SGLOBAL[timestamp]')"; } } } } if($inserts) { $_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,s\x74\141tu\163,dateline) VALUES ".implode(',',$inserts)); friend_cache($_SGLOBAL['supe_uid']); } if($addnum) { $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+$addnum WHERE uid='$_SGLOBAL[supe_uid]'"); } ssetcookie('synfriend', 1, 1800); 
exit(); } elseif($op == 'find') {  
$maxnum = 18; $nouids = $space['friends']; $nouids[] = $space['uid'];  
$nearlist = array(); $i=0; $myip = getonlineip(1); $query = $_SGLOBAL['db']->query("\123ELE\x43T * \x46\x52O\x4d ".tname('session')."
		WHERE ip='$myip' LIMIT 0,200"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if(!in_array($value['uid'], $nouids)) { realname_set($value['uid'], $value['username']); $nearlist[] = $value; $i++; if($i>=$maxnum) break; } }  
$i = 0; $friendlist = array(); if($space['feedfriend']) { $query = $_SGLOBAL['db']->query("\123\105\x4cE\103T fuid AS uid, fusername AS username \106\x52\117\115 ".tname('friend')."
			WHERE uid IN (".$space['feedfriend'].") LIMIT 0,200"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if(!in_array($value['uid'], $nouids) && $value['username']) { realname_set($value['uid'], $value['username']); $friendlist[$value['uid']] = $value; $i++; if($i>=$maxnum) break; } } }  
$i = 0; $onlinelist = array(); $query = $_SGLOBAL['db']->query("SE\114E\x43T * \x46\x52\117\x4d ".tname('session')." LIMIT 0,200"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if(!in_array($value['uid'], $nouids)) { realname_set($value['uid'], $value['username']); $onlinelist[] = $value; $i++; if($i>=$maxnum) break; } }  
realname_get(); } elseif($op == 'changegroup') { if(submitcheck('changegroupsubmit')) { updatetable('friend', array('gid'=>intval($_POST['group'])), array('uid'=>$_SGLOBAL['supe_uid'], 'fuid'=>$uid)); friend_cache($_SGLOBAL['supe_uid']); showmessage('do_success', $_SGLOBAL['refer']); }  
$query = $_SGLOBAL['db']->query("\123\x45L\x45\x43\x54 * \106\x52OM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'"); if(!$friend = $_SGLOBAL['db']->fetch_array($query)) { showmessage('specified_user_is_not_your_friend'); } $groupselect = array($friend['gid'] => ' checked'); $groups = getfriendgroup(); } elseif($op == 'changenum') { if(submitcheck('changenumsubmit')) { updatetable('friend', array('num'=>intval($_POST['num'])), array('uid'=>$_SGLOBAL['supe_uid'], 'fuid'=>$uid)); friend_cache($_SGLOBAL['supe_uid']); showmessage('do_success', $_SGLOBAL['refer'], 0); }  
$query = $_SGLOBAL['db']->query("\x53\x45\x4cE\x43\x54 * \106\x52\x4f\x4d ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'"); if(!$friend = $_SGLOBAL['db']->fetch_array($query)) { showmessage('specified_user_is_not_your_friend'); } } elseif($op == 'group') { if(submitcheck('groupsubmin')) { if(empty($_POST['fuids'])) { showmessage('please_correct_choice_groups_friend'); } $ids = simplode($_POST['fuids']); $groupid = intval($_POST['group']); updatetable('friend', array('gid'=>$groupid), "uid='$_SGLOBAL[supe_uid]' AND fuid IN ($ids) AND \x73\164\141t\165\x73='1'"); friend_cache($_SGLOBAL['supe_uid']); showmessage('do_success', $_SGLOBAL['refer']); } $perpage = 50; $page = empty($_GET['page'])?1:intval($_GET['page']); if($page<1) $page = 1; $start = ($page-1)*$perpage; $list = array(); $multi = ''; if($space['friendnum']) { $groups = getfriendgroup(); $theurl = 'cp.php?ac=friend&op=group'; $group = !isset($_GET['group'])?'-1':intval($_GET['group']); if($group > -1) { $wheresql = "AND \155ai\156.gid='$group'"; $theurl .= "&group=$group"; } $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("\x53\105L\105C\x54 COUNT(*) \x46\x52O\115 ".tname('friend')." ma\151\156
			WHERE \x6d\141\151n.uid='$space[uid]' AND \155\x61\x69\x6e.\163\164\141\164u\163='1' $wheresql"), 0); $query = $_SGLOBAL['db']->query("\x53\x45\114E\x43T main.fuid AS uid,\x6da\x69\156.fusername AS username, m\x61\151\x6e.gid, ma\151\x6e.num F\122\117M ".tname('friend')." \x6dai\156
			WHERE \x6d\x61\x69n.uid='$space[uid]' AND \155a\151\156.s\x74\141t\x75s='1' $wheresql
			ORDER BY m\141i\156.dateline DESC
			LIMIT $start,$perpage"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['uid'], $value['username']); $value['group'] = $groups[$value['gid']]; $list[] = $value; } $multi = multi($count, $perpage, $page, $theurl); } $groups = getfriendgroup(); $actives = array('group'=>' class="active"');  
realname_get(); } elseif($op == 'request') { if(submitcheck('requestsubmin')) { showmessage('do_success', $_SGLOBAL['refer']); } $maxfriendnum = checkperm('maxfriendnum'); if($maxfriendnum) { $maxfriendnum = $maxfriendnum + $space['addfriend']; }  
$perpage = 20; $page = empty($_GET['page'])?0:intval($_GET['page']); if($page<1) $page = 1; $start = ($page-1)*$perpage; $friend1 = $space['friends']; $list = array(); $count = getcount('friend', array('fuid'=>$space['uid'], 'status'=>0)); if($count) { $query = $_SGLOBAL['db']->query("\123\x45\x4cE\103\124 s.*, sf.friend, f.* F\122OM ".tname('friend')." f
			LEFT JOIN ".tname('space')." s \117\x4e s.uid=f.uid
			LEFT JOIN ".tname('spacefield')." sf \117N sf.uid=f.uid
			WHERE f.fuid='$space[uid]' AND f.\x73\x74a\164\x75s='0'
			ORDER BY f.dateline DESC
			LIMIT $start,$perpage"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['uid'], $value['username']);  
$cfriend = array(); $friend2 = empty($value['friend'])?array():explode(',',$value['friend']); if($friend1 && $friend2) { $cfriend = array_intersect($friend1, $friend2); } $value['cfriend'] = implode(',', $cfriend); $value['cfcount'] = count($cfriend); $list[] = $value; } }  
if($count != $space['addfriendnum']) { updatetable('space', array('addfriendnum'=>$count), array('uid'=>$space['uid'])); }  
$multi = multi($count, $perpage, $page, "cp.\160hp?ac=friend&op=request"); realname_get(); } elseif($op == 'groupname') { $groups = getfriendgroup(); $group = intval($_GET['group']); if(!isset($groups[$group])) { showmessage('change_friend_groupname_error'); } if(submitcheck('groupnamesubmit')) { $space['privacy']['groupname'][$group] = getstr($_POST['groupname'], 20, 1, 1); privacy_update(); showmessage('do_success', $_POST['refer']); } } elseif($op == 'groupignore') { $groups = getfriendgroup(); $group = intval($_GET['group']); if(!isset($groups[$group])) { showmessage('change_friend_groupname_error'); } if(submitcheck('groupignoresubmit')) { if(isset($space['privacy']['filter_gid'][$group])) { unset($space['privacy']['filter_gid'][$group]); } else { $space['privacy']['filter_gid'][$group] = $group; } privacy_update(); friend_cache($_SGLOBAL['supe_uid']); 
showmessage('do_success', $_POST['refer'], 0); } } elseif($op == 'blacklist') { if($_GET['subop'] == 'delete') { $_GET['uid'] = intval($_GET['uid']); $_SGLOBAL['db']->query("DELETE FR\x4f\x4d ".tname('blacklist')." WHERE uid='$space[uid]' AND buid='$_GET[uid]'"); showmessage('do_success', "space.\160\x68\160?\x64o=friend&view=blacklist&start=$_GET[start]", 0); } if(submitcheck('blacklistsubmit')) { $_POST['username'] = trim($_POST['username']); $query = $_SGLOBAL['db']->query("SEL\x45\103\x54 * \x46ROM ".tname('space')." WHERE username='$_POST[username]'"); if(!$tospace = $_SGLOBAL['db']->fetch_array($query)) { showmessage('space_does_not_exist'); } if($tospace['uid'] == $space['uid']) { showmessage('unable_to_manage_self'); }  
if($space['friends'] && in_array($tospace['uid'], $space['friends'])) { friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $tospace['uid'], '', 'ignore'); } inserttable('blacklist', array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_SGLOBAL['timestamp']), 0, true); showmessage('do_success', "space.p\x68\x70?d\157=friend&view=blacklist&start=$_GET[start]", 0); } } elseif($op == 'rand') { $randuids = array(); if($space['friendnum']<5) {  
$onlinelist = array(); $query = $_SGLOBAL['db']->query("\x53EL\x45\103T uid F\122\x4f\115 ".tname('session')." LIMIT 0,100"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if($value['uid'] != $space['uid']) { $onlinelist[] = $value['uid']; } } $randuids = sarray_rand(array_merge($onlinelist, $space['friends']), 1); } else { $randuids = sarray_rand($space['friends'], 1); } showmessage('do_success', "space.\x70\x68p?uid=".array_pop($randuids), 0); } elseif ($op == 'getcfriend') { $fuids = empty($_GET['fuid'])?array():explode(',', $_GET['fuid']); $newfuids = array(); foreach ($fuids as $value) { $value = intval($value); if($value) $newfuids[$value] = $value; }  

  $list = array();
  if($newfuids) 
  {
    $query = $_SGLOBAL['db']->query("SE\114E\103\124 uid,username,name,namestatus \x46\x52OM ".tname('space')." WHERE uid IN (".simplode($newfuids).") LIMIT 0,15"); 
    while ($value = $_SGLOBAL['db']->fetch_array($query))
    {
       realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
       $list[] = $value;
    }
    realname_get();
   }
}
elseif($op == 'search')
{ 
   if($_SGLOBAL['supe_auth']=='t0' || $_SGLOBAL['supe_auth']=='t1' ) 
   { showmessage('auth_unallowed_function');
}

 @include_once(S_ROOT.'./data/data_profilefield.php');
 $fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield']; if(!empty($_REQUEST['searchsubmit']) || !empty($_REQUEST['searchmode'])) { $_REQUEST['searchsubmit'] = $_REQUEST['searchmode'] = 1;  
$wherearr = $fromarr = $uidjoin = array(); $fsql = ''; $fromarr['space'] = tname('space').' s'; if($searchkey = stripsearchkey($_REQUEST['searchkey'])) { $wherearr[] = "(s.name='$searchkey' OR s.username='$searchkey')"; } else { foreach (array('uid','username','name','videostatus','avatar') as $value) { if($_REQUEST[$value]) { $wherearr[] = "s.$value='{$_REQUEST[$value]}'"; } } }  
foreach (array('sex','qq','msn','birthyear','birthmonth','birthday','blood','marry','birthprovince','birthcity','resideprovince','residecity') as $value) { if($_REQUEST[$value]) { $fromarr['spacefield'] = tname('spacefield').' sf'; $wherearr['spacefield'] = "sf.uid=s.uid"; $wherearr[] = "sf.$value='{$_REQUEST[$value]}'"; $fsql .= ", sf.$value"; } }  
$startage = $endage = 0; if($_REQUEST['endage']) { $startage = sgmdate('Y') - intval($_REQUEST['endage']); } if($_REQUEST['startage']) { $endage = sgmdate('Y') - intval($_REQUEST['startage']); } if($startage || $endage) { $fromarr['spacefield'] = tname('spacefield').' sf'; $wherearr['spacefield'] = "sf.uid=s.uid"; } if($startage && $endage && $endage > $startage) { $wherearr[] = '(sf.birthyear>='.$startage.' AND sf.birthyear<='.$endage.')'; } else if($startage && empty($endage)) { $wherearr[] = 'sf.birthyear>='.$startage; } else if(empty($startage) && $endage) { $wherearr[] = 'sf.birthyear<='.$endage; }  
$havefield = 0; foreach ($fields as $fkey => $fvalue) { if($fvalue['allowsearch']) { $_REQUEST['field_'.$fkey] = empty($_REQUEST['field_'.$fkey])?'':stripsearchkey($_REQUEST['field_'.$fkey]); if($_REQUEST['field_'.$fkey]) { $havefield = 1; $wherearr[] = "sf.field_$fkey LIKE '%".$_REQUEST['field_'.$fkey]."%'"; } } } if($havefield) { $fromarr['spacefield'] = tname('spacefield').' sf'; $wherearr['spacefield'] = "sf.uid=s.uid"; }  
if($_REQUEST['type'] == 'edu' || $_REQUEST['type'] == 'work') { foreach (array('type','title','subtitle','startyear') as $value) { if($_REQUEST[$value]) { $fromarr['spaceinfo'] = tname('spaceinfo').' si'; $wherearr['spaceinfo'] = "si.uid=s.uid"; $wherearr[] = "si.$value='{$_REQUEST[$value]}'"; } } } $list = array(); if($wherearr) { $query = $_SGLOBAL['db']->query("S\105LECT s.* $fsql \106\122O\115 ".implode(',', $fromarr)." WHERE ".implode(' AND ', $wherearr)." LIMIT 0,500"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']); $value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0; $list[$value['uid']] = $value; } } realname_get(); } else { $yearhtml = ''; $nowy = sgmdate('Y'); for ($i=0; $i<50; $i++) { $they = $nowy - $i; $yearhtml .= "<option value=\"$they\">$they</option>"; }  
$sexarr = array($space['sex']=>' checked');  
$birthyeayhtml = ''; $nowy = sgmdate('Y'); for ($i=0; $i<100; $i++) { $they = $nowy - $i; if(empty($_GET['all'])) $selectstr = $they == $space['birthyear']?' selected':''; $birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>"; }  
$birthmonthhtml = ''; for ($i=1; $i<13; $i++) { if(empty($_GET['all'])) $selectstr = $i == $space['birthmonth']?' selected':''; $birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>"; }  
$birthdayhtml = ''; for ($i=1; $i<29; $i++) { if(empty($_GET['all'])) $selectstr = $i == $space['birthday']?' selected':''; $birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>"; }  
$bloodhtml = ''; foreach (array('A','B','O','AB') as $value) { if(empty($_GET['all'])) $selectstr = $value == $space['blood']?' selected':''; $bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>"; }  
include_once XnP3g6CaJ.'./source/function_city.php';  
$mtU11g8fs=''; $OdO10gEiB=''; $resideprovince = $space['resideprovince']; $residecity = $space['residecity']; if($_GET['searchby']==2 && submitcheck('changeprovince')) { $resideprovince=""; } elseif($_GET['searchby']==2 && submitcheck('changecity')) { $resideprovince=$_POST['resideprovince']; } if(empty($resideprovince)) { $mtU11g8fs = fYUag2UGE($space['resideprovince']); } else { $OdO10gEiB = CMX9gYotI($resideprovince,$residecity); }  
$pzi5gnXVg=''; $fYZ4g87Nj=''; $birthprovince = $space['birthprovince']; $birthcity = $space['birthcity']; if($_GET['searchby']==3 && submitcheck('changeprovince')) { $birthprovince=""; } elseif($_GET['searchby']==3 && submitcheck('changecity')) { $birthprovince=$_POST['birthprovince']; } if(empty($birthprovince)) { $pzi5gnXVg = fYUag2UGE($space['birthprovince']); } else { $fYZ4g87Nj = CMX9gYotI($birthprovince,$birthcity); }  
$marryarr = array($space['marry'] => ' selected');  
foreach ($fields as $fkey => $fvalue) { if($fvalue['allowsearch']) { if($fvalue['formtype'] == 'text') { $fvalue['html'] = '<input type="text" name="field_'.$fkey.'" value="'.$gets["field_$fkey"].'" class="t_input">'; } else { $fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>"; $optionarr = explode("\n", $fvalue['choice']); foreach ($optionarr as $ov) { $ov = trim($ov); if($ov) { $selectstr = $gets["field_$fkey"]==$ov?' selected':''; $fvalue['html'] .= "<option value=\"$ov\"$selectstr>$ov</option>"; } } $fvalue['html'] .= "</select>"; } $fields[$fkey] = $fvalue; } else { unset($fields[$fkey]); } } } } include template('cp_friend');  ?>
