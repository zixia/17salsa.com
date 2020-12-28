<?php    if(!defined('IN_UCHOME')) { exit('Access Denied'); } include_once(S_ROOT.'./source/function_cp.php'); if($_SGLOBAL['supe_uid']) { showmessage('do_success', 'space.php', 0); } $refer = empty($_GET['refer'])?rawurldecode($_SCOOKIE['_refer']):$_GET['refer']; preg_match("/(admincp|d\157|cp)\.\x70\x68\160\?ac\=([a-z]+)/i", $refer, $ms); if($ms) { if($ms[1] != 'cp' || $ms[2] != 'sendmail') $refer = ''; } if(empty($refer)) { $refer = 'space.php?do=home'; }  
$uid = empty($_GET['uid'])?0:intval($_GET['uid']); $code = empty($_GET['code'])?'':$_GET['code']; $app = empty($_GET['app'])?'':intval($_GET['app']); $invite = empty($_GET['invite'])?'':$_GET['invite']; $invitearr = array(); $reward = getreward('invitecode', 0); if($uid && $code && !$reward['credit']) { $m_space = getspace($uid); if($code == space_key($m_space, $app)) { 
$invitearr['uid'] = $uid; $invitearr['username'] = $m_space['username']; } $url_plus = "uid=$uid&app=$app&code=$code"; } elseif($uid && $invite) { include_once(S_ROOT.'./source/function_cp.php'); $invitearr = invite_get($uid, $invite); $url_plus = "uid=$uid&invite=$invite"; }  
$_SGLOBAL['nologinform'] = 1; if(submitcheck('loginsubmit')) { $password = $_POST['password']; $username = trim($_POST['username']); $cookietime = intval($_POST['cookietime']); $cookiecheck = $cookietime?' checked':''; $membername = $username; if(empty($_POST['username'])) { showmessage('users_were_not_empty_please_re_login', 'do.php?ac='.$_SCONFIG['login_action']); } if($_SCONFIG['seccode_login']) { include_once(S_ROOT.'./source/function_cp.php'); if(!ckseccode($_POST['seccode'])) { $_SGLOBAL['input_seccode'] = 1; include template('do_login'); exit; } }  
if(!$passport = getpassport($username, $password)) { showmessage('login_failure_please_re_login', 'do.php?ac='.$_SCONFIG['login_action']); } $setarr = array( 'uid' => $passport['uid'], 'username' => addslashes($passport['username']), 'password' => md5("$passport[uid]|$_SGLOBAL[timestamp]") 
); include_once(S_ROOT.'./source/function_space.php');  
$query = $_SGLOBAL['db']->query("SE\x4c\105CT * \x46\x52\x4f\115 ".tname('space')." WHERE uid='$setarr[uid]'"); if(!$space = $_SGLOBAL['db']->fetch_array($query)) { $space = space_open($setarr['uid'], $setarr['username'], 0, $passport['email']); } $_SGLOBAL['member'] = $space;  
realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);  
$query = $_SGLOBAL['db']->query("SELE\x43\124 password \x46\x52\117M ".tname('member')." WHERE uid='$setarr[uid]'"); if($value = $_SGLOBAL['db']->fetch_array($query)) { $setarr['password'] = addslashes($value['password']); } else {  
inserttable('member', $setarr, 0, true); }  
insertsession($setarr); 

ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
ssetcookie('loginuser', $passport['username'], 31536000); ssetcookie('_refer', '');  
  $ucsynlogin = '';  

if($invitearr)
{  
  invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
}
$_SGLOBAL['supe_uid'] = $space['uid'];  
$reward = $setarr = array(); 
$experience = $credit = 0; 
$avatar_exists = ckavatar($space['uid']);
if($avatar_exists) 
{ 
  if(!$space['avatar']) 
  {  
    $reward = getreward('setavatar', 0);
    $credit = $reward['credit']; 
    $experience = $reward['experience']; 
    if($credit) 
    { 
       $setarr['credit'] = "credit=credit+$credit";
    }
    if($experience) 
    {
      $setarr['experience'] = "experience=experience+$experience";
    }
    $setarr['avatar'] = 'avatar=1';
    $setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
  }
} 
else
{
  if($space['avatar']) 
  { 
    //$setarr['avatar'] = 'avatar=0';
  }
} 
if($setarr) 
{ 
  $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
} 
if(empty($_POST['refer'])) { $_POST['refer'] = 'space.php?do=home'; } realname_get(); showmessage('login_success', $app?"userapp.\160\150\160?id=$app":$_POST['refer'], 1, array($ucsynlogin)); } $membername = empty($_SCOOKIE['loginuser'])?'':sstripslashes($_SCOOKIE['loginuser']); $cookiecheck = ' checked'; include template('do_login');  ?>
