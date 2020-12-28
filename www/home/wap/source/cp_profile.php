<?php    if(!defined('IN_UCHOME')) { exit('Access Denied'); } if(!in_array($_GET['op'], array('base','contact','edu','work','info'))) { $_GET['op'] = 'base'; } $theurl = "cp.ph\x70?ac=profile&op=$_GET[op]"; if($_GET['op'] == 'base') { if(submitcheck('profilesubmit') || submitcheck('nextsubmit')|| submitcheck('birthchangesubmit')|| submitcheck('residechangesubmit')) { if(!@include_once(S_ROOT.'./data/data_profilefield.php')) { include_once(S_ROOT.'./source/function_cache.php'); profilefield_cache(); } $profilefields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];  
$setarr = array( 'birthyear' => intval($_POST['birthyear']), 'birthmonth' => intval($_POST['birthmonth']), 'birthday' => intval($_POST['birthday']), 'blood' => getstr($_POST['blood'], 5, 1, 1), 'marry' => intval($_POST['marry']), 'birthprovince' => getstr($_POST['birthprovince'], 20, 1, 1), 'birthcity' => getstr($_POST['birthcity'], 20, 1, 1), 'resideprovince' => getstr($_POST['resideprovince'], 20, 1, 1), 'residecity' => getstr($_POST['residecity'], 20, 1, 1) );  
$_POST['sex'] = intval($_POST['sex']); if($_POST['sex'] && empty($space['sex'])) $setarr['sex'] = $_POST['sex']; foreach ($profilefields as $field => $value) { if($value['formtype'] == 'select') $value['maxsize'] = 255; $setarr['field_'.$field] = getstr($_POST['field_'.$field], $value['maxsize'], 1, 1); if($value['required'] && empty($setarr['field_'.$field])) { showmessage('field_required', '', 1, array($value['title'])); } } updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));  
$inserts = array(); foreach ($_POST['friend'] as $key => $value) { $value = intval($value); $inserts[] = "('base','$key','$space[uid]','$value')"; } if($inserts) { $_SGLOBAL['db']->query("DELETE \x46\x52O\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='base'"); $_SGLOBAL['db']->query("I\116S\105\122\124 INTO ".tname('spaceinfo')." (type,subtype,uid,friend)
				VALUES ".implode(',', $inserts)); }  
$setarr = array( 'name' => getstr($_POST['name'], 10, 1, 1, 1), 'namestatus' => $_SCONFIG['namecheck']?0:1 ); if(checkperm('managename')) { $setarr['namestatus'] = 1; } if(strlen($setarr['name']) < 4) { 
showmessage('realname_too_short'); } if($setarr['name'] != $space['name'] || $setarr['namestatus']) {  
if($_SCONFIG['realname'] && empty($space['name']) && $setarr['name'] != $space['name'] && $setarr['namestatus']) { $reward = getreward('realname', 0); if($reward['credit']) { $setarr['credit'] = $space['credit'] + $reward['credit']; } if($reward['experience']) { $setarr['experience'] = $space['experience'] + $reward['experience']; } } elseif($_SCONFIG['realname'] && $space['namestatus'] && !checkperm('managename')) {  
$reward = getreward('editrealname', 0);  
if($space['name'] && $setarr['name'] != $space['name'] && ($reward['credit'] || $reward['experience'])) {  
if($space['experience'] >= $reward['experience']) { $setarr['experience'] = $space['experience'] - $reward['experience']; } else { showmessage('experience_inadequate', '', 1, array($space['experience'], $reward['experience'])); } if($space['credit'] >= $reward['credit']) { $setarr['credit'] = $space['credit'] - $reward['credit']; } else { showmessage('integral_inadequate', '', 1, array($space['credit'], $reward['credit'])); } } } updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid'])); }  
if($_SCONFIG['my_status']) { inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>0), 0, true); }  
if(ckprivacy('profile', 1)) { feed_add('profile', cplang('feed_profile_update_base')); } if(submitcheck('nextsubmit')) { $url = 'cp.php?ac=profile&op=contact'; showmessage('update_on_successful_individuals', $url); } elseif(submitcheck('profilesubmit')) { $url = 'cp.php?ac=profile&op=base'; showmessage('update_on_successful_individuals', $url); } else {  
$space['birthprovince'] =getstr($_POST['birthprovince'], 20, 1, 1); $space['birthcity'] =getstr($_POST['birthcity'], 20, 1, 1); $space['resideprovince']=getstr($_POST['resideprovince'], 20, 1, 1); $space['residecity'] =getstr($_POST['residecity'], 20, 1, 1); } }  
$sexarr = array($space['sex']=>' checked');  
$birthyeayhtml = ''; $nowy = sgmdate('Y'); for ($i=0; $i<50; $i++) { $they = $nowy - $i; $selectstr = $they == $space['birthyear']?' selected':''; $birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>"; }  
$birthmonthhtml = ''; for ($i=1; $i<13; $i++) { $selectstr = $i == $space['birthmonth']?' selected':''; $birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>"; }  
$birthdayhtml = ''; for ($i=1; $i<32; $i++) { $selectstr = $i == $space['birthday']?' selected':''; $birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>"; }  
$bloodhtml = ''; foreach (array('A','B','O','AB') as $value) { $selectstr = $value == $space['blood']?' selected':''; $bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>"; }  
include_once XnP3g6CaJ.'./source/function_city.php';  
$mtU11g8fs=''; $OdO10gEiB=''; $resideprovince = $space['resideprovince']; $residecity = $space['residecity']; $residetype=$_POST['residetype']; if($residetype=="resideprovince" && submitcheck('residechangesubmit')) { $resideprovince=""; } elseif($residetype=="residecity" && submitcheck('residechangesubmit')) { $resideprovince=$_POST['resideprovince']; } if(empty($resideprovince)) { $mtU11g8fs = fYUag2UGE($space['resideprovince']); } else { $OdO10gEiB = CMX9gYotI($resideprovince,$residecity); }  
$pzi5gnXVg=''; $fYZ4g87Nj=''; $birthprovince = $space['birthprovince']; $birthcity = $space['birthcity']; $birthtype=$_POST['birthtype']; if($birthtype=="birthprovince" && submitcheck('birthchangesubmit')) { $birthprovince=""; } elseif($birthtype=="birthcity" && submitcheck('birthchangesubmit')) { $birthprovince=$_POST['birthprovince']; } if(empty($birthprovince)) { $pzi5gnXVg = fYUag2UGE($space['birthprovince']); } else { $fYZ4g87Nj = CMX9gYotI($birthprovince,$birthcity); }  
$marryarr = array($space['marry'] => ' selected');  
$profilefields = array(); $query = $_SGLOBAL['db']->query("\x53ELE\x43\124 * \x46\122O\x4d ".tname('profilefield')." ORDER BY displayorder"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $fieldid = $value['fieldid']; $value['formhtml'] = ''; if($value['formtype'] == 'text') { $value['formhtml'] = "<input type=\"text\" name=\"field_$fieldid\" value=\"".$space["field_$fieldid"]."\" cla\x73s=\"t_input\">"; } else { $value['formhtml'] .= "<select name=\"field_$fieldid\">"; if(empty($value['required'])) { $value['formhtml'] .= "<option value=\"\"></option>"; } $optionarr = explode("\n", $value['choice']); foreach ($optionarr as $ov) { $ov = trim($ov); if($ov) { $selectstr = $space["field_$fieldid"]==$ov?' selected':''; $value['formhtml'] .= "<option value=\"$ov\"$selectstr>$ov</option>"; } } $value['formhtml'] .= "</select>"; } $profilefields[$value['fieldid']] = $value; } if(empty($_SCONFIG['namechange'])) { $_GET['namechange'] = 0; 
}  
$friendarr = array(); $query = $_SGLOBAL['db']->query("\123E\x4cEC\124 * \x46\x52O\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='base'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $friendarr[$value['subtype']][$value['friend']] = ' selected'; } } elseif ($_GET['op'] == 'contact') { if($_GET['resend']) {  
$toemail = $space['newemail']?$space['newemail']:$space['email']; emailcheck_send($space['uid'], $toemail); showmessage('do_success', "cp.\160\150p?ac=profile&op=contact"); } if(submitcheck('profilesubmit') || submitcheck('nextsubmit')) {  
$setarr = array( 'mobile' => getstr($_POST['mobile'], 40, 1, 1), 'qq' => getstr($_POST['qq'], 20, 1, 1), 'msn' => getstr($_POST['msn'], 80, 1, 1), );  
$newemail = isemail($_POST['email'])?$_POST['email']:''; if(isset($_POST['email']) && $newemail != $space['email']) {  
if($_SCONFIG['uniqueemail']) { if(getcount('spacefield', array('email'=>$newemail, 'emailcheck'=>1))) { showmessage('uniqueemail_check'); } }  
if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['password'])) { showmessage('password_is_not_passed'); }  
if(empty($newemail)) {  
$setarr['email'] = ''; $setarr['emailcheck'] = 0; } elseif($newemail != $space['email']) {  
if($space['emailcheck']) {  
$setarr['newemail'] = $newemail; } else {  
$setarr['email'] = $newemail; } emailcheck_send($space['uid'], $newemail); } } updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));  
$inserts = array(); foreach ($_POST['friend'] as $key => $value) { $value = intval($value); $inserts[] = "('contact','$key','$space[uid]','$value')"; } if($inserts) { $_SGLOBAL['db']->query("DELETE FR\117\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='contact'"); $_SGLOBAL['db']->query("\x49N\x53\105\x52T INTO ".tname('spaceinfo')." (type,subtype,uid,friend)
				VALUES ".implode(',', $inserts)); }  
if($_SCONFIG['my_status']) { inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>2), 0, true); }  
if(ckprivacy('profile', 1)) { feed_add('profile', cplang('feed_profile_update_contact')); } if(submitcheck('nextsubmit')) { $url = 'cp.php?ac=profile&op=edu'; } else { $url = 'cp.php?ac=profile&op=contact'; } showmessage('update_on_successful_individuals', $url); }  
$friendarr = array(); $query = $_SGLOBAL['db']->query("S\105LE\103\x54 * \x46\122O\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='contact'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $friendarr[$value['subtype']][$value['friend']] = ' selected'; } } elseif ($_GET['op'] == 'edu') { if($_GET['subop'] == 'delete') { $infoid = intval($_GET['infoid']); if($infoid) { $_SGLOBAL['db']->query("DELETE \x46\122\117M ".tname('spaceinfo')." WHERE infoid='$infoid' AND uid='$space[uid]' AND type='edu'"); } } if(submitcheck('profilesubmit') || submitcheck('nextsubmit')) {  
$inserts = array(); foreach ($_POST['title'] as $key => $value) { $value = getstr($value, 100, 1, 1); if($value) { $subtitle= getstr($_POST['subtitle'][$key], 20, 1, 1); $startyear = intval($_POST['startyear'][$key]); $friend = intval($_POST['friend'][$key]); $inserts[] = "('$space[uid]','edu','$value','$subtitle','$startyear','$friend')"; } } if($inserts) { $_SGLOBAL['db']->query("\x49N\123\105\x52T INTO ".tname('spaceinfo')."(uid,type,title,subtitle,startyear,friend) VALUES ".implode(',', $inserts)); }  
if($_SCONFIG['my_status']) { inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>2), 0, true); }  
if(ckprivacy('profile', 1)) { feed_add('profile', cplang('feed_profile_update_edu')); } if(submitcheck('nextsubmit')) { $url = 'cp.php?ac=profile&op=work'; } else { $url = 'cp.php?ac=profile&op=edu'; } showmessage('update_on_successful_individuals', $url); }  
$list = array(); $query = $_SGLOBAL['db']->query("\123E\114\105\x43T * FR\117\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='edu'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $value['title_s'] = urlencode($value['title']); $list[] = $value; } } elseif ($_GET['op'] == 'work') { if($_GET['subop'] == 'delete') { $infoid = intval($_GET['infoid']); if($infoid) { $_SGLOBAL['db']->query("DELETE F\122\117\115 ".tname('spaceinfo')." WHERE infoid='$infoid' AND uid='$space[uid]' AND type='work'"); } } if(submitcheck('profilesubmit') || submitcheck('nextsubmit')) {  
$inserts = array(); foreach ($_POST['title'] as $key => $value) { $value = getstr($value, 100, 1, 1); if($value) { $subtitle= getstr($_POST['subtitle'][$key], 20, 1, 1); $startyear = intval($_POST['startyear'][$key]); $startmonth = intval($_POST['startmonth'][$key]); $endyear = intval($_POST['endyear'][$key]); $endmonth = $endyear?intval($_POST['endmonth'][$key]):0; $friend = intval($_POST['friend'][$key]); $inserts[] = "('$space[uid]','work','$value','$subtitle','$startyear','$startmonth','$endyear','$endmonth','$friend')"; } } if($inserts) { $_SGLOBAL['db']->query("\x49N\123E\x52T INTO ".tname('spaceinfo')."
				(uid,type,title,subtitle,startyear,startmonth,\x65\x6e\144\171\x65\141\x72,e\156\x64\155o\x6e\164\x68,friend)
				VALUES ".implode(',', $inserts)); }  
if($_SCONFIG['my_status']) { inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>2), 0, true); }  
if(ckprivacy('profile', 1)) { feed_add('profile', cplang('feed_profile_update_work')); } if(submitcheck('nextsubmit')) { $url = 'cp.php?ac=profile&op=info'; } else { $url = 'cp.php?ac=profile&op=work'; } showmessage('update_on_successful_individuals', $url); }  
$list = array(); $query = $_SGLOBAL['db']->query("\x53E\x4cE\x43\x54 * \106RO\x4d ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='work'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $value['title_s'] = urlencode($value['title']); $list[] = $value; } } elseif ($_GET['op'] == 'info') { if(submitcheck('profilesubmit')) { $inserts = array(); foreach ($_POST['info'] as $key => $value) { $value = getstr($value, 500, 1, 1); $friend = intval($_POST['info_friend'][$key]); $inserts[] = "('$space[uid]','info','$key','$value','$friend')"; } if($inserts) { $_SGLOBAL['db']->query("DELETE F\x52\x4f\115 ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='info'"); $_SGLOBAL['db']->query("I\x4e\123E\x52\124 INTO ".tname('spaceinfo')."
				(uid,type,subtype,title,friend)
				VALUES ".implode(',', $inserts)); }  
if($_SCONFIG['my_status']) { inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp'], 'type'=>2), 0, true); }  
if(ckprivacy('profile', 1)) { feed_add('profile', cplang('feed_profile_update_info')); } $url = 'cp.php?ac=profile&op=info'; showmessage('update_on_successful_individuals', $url); }  
$list = $friends = array(); $query = $_SGLOBAL['db']->query("\123\x45\114\105\103\x54 * \x46\122\x4fM ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type='info'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $list[$value['subtype']] = $value; $friends[$value['subtype']][$value['friend']] = ' selected'; } } $cat_actives = array($_GET['op'] => ' class="active"'); if($_GET['op'] == 'edu' || $_GET['op'] == 'work') { $yearhtml = ''; $nowy = sgmdate('Y'); for ($i=0; $i<50; $i++) { $they = $nowy - $i; $yearhtml .= "<option value=\"$they\">$they</option>"; } $monthhtml = ''; for ($i=1; $i<13; $i++) { $monthhtml .= "<option value=\"$i\">$i</option>"; } } include template("cp_profile");  ?>