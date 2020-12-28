<?php    if(!defined('IN_UCHOME')) { exit('Access Denied'); } $uid = empty($_GET['uid'])?0:intval($_GET['uid']); if($uid == $_SGLOBAL['supe_uid']) { showmessage('not_to_their_own_greeted'); } if($op == 'send' || $op == 'reply') { if(!checkperm('allowpoke')) { ckspacelog(); showmessage('no_privilege'); }  
ckrealname('poke');  
cknewuser(); $tospace = array();  
if($uid) { $tospace = getspace($uid); } elseif ($_POST['username']) { $tospace = getspace($_POST['username'], 'username'); }  
if($tospace['videostatus']) { ckvideophoto('poke', $tospace); }  
if($tospace && isblacklist($tospace['uid'])) { showmessage('is_blacklist'); }  
if(submitcheck('pokesubmit')) { if(empty($tospace)) { showmessage('space_does_not_exist'); } $oldpoke = getcount('poke', array('uid'=>$uid, 'fromuid'=>$_SGLOBAL['supe_uid'])); $setarr = array( 'uid' => $uid, 'fromuid' => $_SGLOBAL['supe_uid'], 'fromusername' => $_SGLOBAL['supe_username'], 'note' => getstr($_POST['note'], 50, 1, 1), 'dateline' => $_SGLOBAL['timestamp'], 'iconid' => intval($_POST['iconid']) ); inserttable('poke', $setarr, 0, true);  
if(!$oldpoke) { $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET pokenum=pokenum+1 WHERE uid='$uid'"); }  
addfriendnum($tospace['uid'], $tospace['username']);  
smail($uid, '',cplang('poke_subject',array($_SN[$space['uid']], getsiteurl().'cp.php?ac=poke')), '', 'poke'); if($op == 'reply') {  
$_SGLOBAL['db']->query("DELETE \x46\x52OM ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' AND fromuid='$uid'"); $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET pokenum=pokenum-1 WHERE uid='$_SGLOBAL[supe_uid]' AND pokenum>0"); }  
getreward('poke', 1, 0, $uid);  
updatestat('poke'); showmessage('poke_success', $_POST['refer'], 1, array($_SN[$tospace['uid']])); } } elseif($op == 'ignore') { $where = empty($uid)?'':"AND fromuid='$uid'"; $_SGLOBAL['db']->query("DELETE \x46RO\x4d ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' $where");  
$pokenum = getcount('poke', array('uid'=>$space['uid'])); if($pokenum != $space['pokenum']) { updatetable('space', array('pokenum'=>$pokenum), array('uid'=>$space['uid'])); } showmessage('has_been_hailed_overlooked'); } else { $perpage = 20; $page = empty($_GET['page'])?0:intval($_GET['page']); if($page<1) $page = 1; $start = ($page-1)*$perpage;  
ckstart($start, $perpage);  
$list = array(); $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("\123\105LECT COUNT(*) F\122\x4f\115 ".tname('poke')." WHERE uid='$space[uid]'"), 0); if($count) { $query = $_SGLOBAL['db']->query("\x53\x45L\x45\103T * FR\x4fM ".tname('poke')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start,$perpage"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $value['uid'] = $value['fromuid']; $value['username'] = $value['fromusername']; realname_set($value['uid'], $value['username']); $value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0; $list[] = $value; } } $multi = multi($count, $perpage, $page, "cp.p\150p?ac=poke");  
if($count != $space['pokenum']) { updatetable('space', array('pokenum'=>$count), array('uid'=>$space['uid'])); } } realname_get(); include_once template('cp_poke');  ?>
