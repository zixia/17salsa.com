<?php

if(!defined('IN_UCHOME') || !defined('qVh0gqGnK')) { exit('Access Denied'); }  


function thumb_replace($message) {
    //$message = preg_replace("/\[flash\=?(media|real)*\](.+?)\[\/flash\]/ie", "blog_flash('\\2', '\\1')", $message);
    //$message = preg_replace( "/\\[mp3\\=?(auto)*\\](.+?)\\[\\/mp3\\]/ie","blog_mp3(\"\\2\", \"\\1\")",$message);
    $message = preg_replace("/jpg/", "jpg.thumb.jpg", $message);
    return $message;
}

function wap_process($message)
{
  $message = str_replace('attachment','http://www.17salsa.net/home/attachment',$message);
  $message = thumb_replace($message);

  return $message;
}

function wap_pic_save($FILE, $albumid, $title, $topicid=0) {
	global $_SGLOBAL, $_SCONFIG, $space, $_SC;

	if($albumid<0) $albumid = 0;
	
	//允许上传类型
	$allowpictype = array('jpg','gif','png');

	//检查
	$FILE['size'] = intval($FILE['size']);
	if(empty($FILE['size']) || empty($FILE['tmp_name']) || !empty($FILE['error'])) {
		return cplang('lack_of_access_to_upload_file_size');
	}

	//判断后缀
	$fileext = fileext($FILE['name']);
	if(!in_array($fileext, $allowpictype)) {
		return cplang('only_allows_upload_file_types');
	}

	//获取目录
	if(!$filepath = getfilepath($fileext, true)) {
		return cplang('unable_to_create_upload_directory_server');
	}

	//检查空间大小
	if(empty($space)) {
		$space = getspace($_SGLOBAL['supe_uid']);
	}
	
	//用户组
	if(!checkperm('allowupload')) {
		ckspacelog();
		return cplang('inadequate_capacity_space');
	}
	
	//实名认证
	if(!ckrealname('album', 1)) {
		return cplang('inadequate_capacity_space');
	}
	
	//视频认证
	if(!ckvideophoto('album', array(), 1)) {
		return cplang('inadequate_capacity_space');
	}
	
	//新用户见习
	if(!cknewuser(1)) {
		return cplang('inadequate_capacity_space');
	}

	$maxattachsize = intval(checkperm('maxattachsize'));//单位MB
	if($maxattachsize) {//0为不限制
		if($space['attachsize'] + $FILE['size'] > $maxattachsize + $space['addsize']) {
			return cplang('inadequate_capacity_space');
		}
	}

	//相册选择
	$showtip = true;
	$albumfriend = 0;
	if($albumid) {
		preg_match("/^new\:(.+)$/i", $albumid, $matchs);
		if(!empty($matchs[1])) {
			$albumname = shtmlspecialchars(trim($matchs[1]));
			if(empty($albumname)) $albumname = sgmdate('Ymd');
			$albumid = album_creat(array('albumname' => $albumname));
		} else {
			$albumid = intval($albumid);
			if($albumid) {
				$query = $_SGLOBAL['db']->query("SELECT albumname,friend FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
				if($value = $_SGLOBAL['db']->fetch_array($query)) {
					$albumname = addslashes($value['albumname']);
					$albumfriend = $value['friend'];
				} else {
					$albumname = sgmdate('Ymd');
					$albumid = album_creat(array('albumname' => $albumname));
				}
			}
		}
	} else {
		$albumid = 0;
		$showtip = false;
	}

	//本地上传
	$new_name = '../'.$_SC['attachdir'].'./'.$filepath;
	$tmp_name = $FILE['tmp_name'];
	if(@copy($tmp_name, $new_name)) {
		@unlink($tmp_name);
	} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
	} elseif(@rename($tmp_name, $new_name)) {
	} else {
		return cplang('mobile_picture_temporary_failure');
	}
	
	//检查是否图片
	if(function_exists('getimagesize')) {
		$tmp_imagesize = @getimagesize($new_name);
		list($tmp_width, $tmp_height, $tmp_type) = (array)$tmp_imagesize;
		$tmp_size = $tmp_width * $tmp_height;
		if($tmp_size > 16777216 || $tmp_size < 4 || empty($tmp_type)) {
			@unlink($new_name);
			return cplang('only_allows_upload_file_types');
		}
	}

	//缩略图
	include_once(S_ROOT.'./source/function_image.php');
	$thumbpath = makethumb($new_name);
	$thumb = empty($thumbpath)?0:1;

	//是否压缩
	//获取上传后图片大小
	if(@$newfilesize = filesize($new_name)) {
		$FILE['size'] = $newfilesize;
	}

	//水印
	if($_SCONFIG['allowwatermark']) {
		makewatermark($new_name);
	}

	//进行ftp上传
	if($_SCONFIG['allowftp']) {
		include_once(S_ROOT.'./source/function_ftp.php');
		if(ftpupload($new_name, $filepath)) {
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			@unlink($new_name);
			@unlink($new_name.'.thumb.jpg');
			runlog('ftp', 'Ftp Upload '.$new_name.' failed.');
			return cplang('ftp_upload_file_size');
		}
	} else {
		$pic_remote = 0;
		$album_picflag = 1;
	}
	
	//入库
	$title = getstr($title, 200, 1, 1, 1);

	//入库
	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'filename' => addslashes($FILE['name']),
		'postip' => getonlineip(),
		'title' => $title,
		'type' => addslashes($FILE['type']),
		'size' => $FILE['size'],
		'filepath' => $filepath,
		'thumb' => $thumb,
		'remote' => $pic_remote,
		'topicid' => $topicid
	);
	$setarr['picid'] = inserttable('pic', $setarr, 1);

	//更新附件大小
	//积分
	$setsql = '';
	if($showtip) {
		$reward = getreward('uploadimage', 0);
		if($reward['credit']) {
			$setsql = ",credit=credit+$reward[credit]";
		}
		if($reward['experience']) {
			$setsql .= ",experience=experience+$reward[experience]";
		}
	}
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$FILE[size]', updatetime='$_SGLOBAL[timestamp]' $setsql WHERE uid='$_SGLOBAL[supe_uid]'");

	//相册更新
	if($albumid) {
		$file = $filepath.($thumb?'.thumb.jpg':'');
		$_SGLOBAL['db']->query("UPDATE ".tname('album')."
			SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='$album_picflag'
			WHERE albumid='$albumid'");
	}
	
	//统计
	updatestat('pic');

	return $setarr;
}


function saddslashes($string) { if(is_array($string)) { foreach($string as $key => $val) { $string[$key] = saddslashes($val); } } else { $string = addslashes($string); } return $string; }  
function shtmlspecialchars($string) { if(is_array($string)) { foreach($string as $key => $val) { $string[$key] = shtmlspecialchars($val); } } else { $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string)); } return $string; }  
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) { $ckey_length = 4;  
 
 
 
$key = md5($key ? $key : UC_KEY); $keya = md5(substr($key, 0, 16)); $keyb = md5(substr($key, 16, 16)); $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : ''; $cryptkey = $keya.md5($keya.$keyc); $key_length = strlen($cryptkey); $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string; $string_length = strlen($string); $result = ''; $box = range(0, 255); $rndkey = array(); for($i = 0; $i <= 255; $i++) { $rndkey[$i] = ord($cryptkey[$i % $key_length]); } for($j = $i = 0; $i < 256; $i++) { $j = ($j + $box[$i] + $rndkey[$i]) % 256; $tmp = $box[$i]; $box[$i] = $box[$j]; $box[$j] = $tmp; } for($a = $j = $i = 0; $i < $string_length; $i++) { $a = ($a + 1) % 256; $j = ($j + $box[$a]) % 256; $tmp = $box[$a]; $box[$a] = $box[$j]; $box[$j] = $tmp; $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256])); } if($operation == 'DECODE') { if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) { return substr($result, 26); } else { return ''; } } else { return $keyc.str_replace('=', '', base64_encode($result)); } }  
function clearcookie() { global $_SGLOBAL; obclean(); ssetcookie('auth', '', -86400 * 365); $_SGLOBAL['supe_uid'] = 0; $_SGLOBAL['supe_username'] = ''; $_SGLOBAL['member'] = array(); }  
function ssetcookie($var, $value, $life=0) { global $_SGLOBAL, $_SC, $_SERVER; $_SESSION['m_'.$var]=$value; setcookie($_SC['cookiepre'].$var, $value, $life?($_SGLOBAL['timestamp']+$life):0, $_SC['cookiepath'], $_SC['cookiedomain'], $_SERVER['SERVER_PORT']==443?1:0); }  
function dbconnect() { global $_SGLOBAL, $_SC; include_once(XnP3g6CaJ.'./source/class_mysql.php'); if(empty($_SGLOBAL['db'])) { $_SGLOBAL['db'] = new dbstuff; $_SGLOBAL['db']->rZJ6glaFc = $_SC['dbcharset']; $_SGLOBAL['db']->charset_wap = $_SC['charset_wap']; $_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']); } }  
function getonlineip($format=0) { global $_SGLOBAL; if(empty($_SGLOBAL['onlineip'])) { if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) { $onlineip = getenv('HTTP_CLIENT_IP'); } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) { $onlineip = getenv('HTTP_X_FORWARDED_FOR'); } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) { $onlineip = getenv('REMOTE_ADDR'); } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) { $onlineip = $_SERVER['REMOTE_ADDR']; } preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches); $_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown'; } if($format) { $ips = explode('.', $_SGLOBAL['onlineip']); for($i=0;$i<3;$i++) { $ips[$i] = intval($ips[$i]); } return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]); } else { return $_SGLOBAL['onlineip']; } }  

function checkauth() 
{
  global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $_SN,$KGn1g4iOB; 
  $_SGLOBAL['supe_auth']="t0";

  if($_GET['m_sid']) 
    $_SCOOKIE['auth'] = $_SESSION['m_auth']; 
  if($_SCOOKIE['auth'])
  {
    @list($password, $uid) = explode("\t", authcode($_SCOOKIE['auth'], 'DECODE')); 
    $_SGLOBAL['supe_uid'] = intval($uid); 
    if($password && $_SGLOBAL['supe_uid'])
    {
       $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid='$_SGLOBAL[supe_uid]'");
       if($member = $_SGLOBAL['db']->fetch_array($query))
       {
          if($member['password'] == $password)
          {
             $_SGLOBAL['supe_username'] = addslashes($member['username']);
             $_SGLOBAL['session'] = $member;
          }
          else
          {
             $_SGLOBAL['supe_uid'] = 0;
          }
       }
       else
       {
          $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('member')." WHERE uid='$_SGLOBAL[supe_uid]'");
          if($member = $_SGLOBAL['db']->fetch_array($query))
          {
             if($member['password'] == $password)
             {
                $_SGLOBAL['supe_username'] = addslashes($member['username']); $session = array('uid' => $_SGLOBAL['supe_uid'], 'username' => $_SGLOBAL['supe_username'], 'password' => $password);
                include_once(S_ROOT.'./source/function_space.php');
                insertsession($session); 
             }
             else
             {
                $_SGLOBAL['supe_uid'] = 0;
             }
          }
          else
          {
              $_SGLOBAL['supe_uid'] = 0; 
          }
       }
     }
  }
  if(empty($_SGLOBAL['supe_uid'])) 
  {
     clearcookie();
  }
  else 
  {
     $_SGLOBAL['username'] = $member['username'];
  }
  $JRHdg7niZ = "/[\w-]+\.(com|net|org|gov|cc|biz|info|cn)(\.(cn|hk))*/"; 
  if(@preg_match($JRHdg7niZ,$KGn1g4iOB['SERVER_NAME'],$match))
  {
     $KGn1g4iOB['SERVER_NAME']=$match[0];
  }

///  if((md5($KGn1g4iOB['SERVER_NAME'] .$KGn1g4iOB['SERVER_PORT']) .md5($KGn1g4iOB['SERVER_SOFTWARE'].$_SC['WAP_TYPE']))==$_SC['WAP_LICENCE'])
///  {
     $_SGLOBAL['supe_auth']=$_SC['WAP_TYPE'];
///  }
}  

function getuserapp() { global $_SGLOBAL, $_SCONFIG; $_SGLOBAL['my_userapp'] = $_SGLOBAL['my_menu'] = array(); $_SGLOBAL['my_menu_more'] = 0; if($_SGLOBAL['supe_uid'] && $_SCONFIG['my_status']) { $space = getspace($_SGLOBAL['supe_uid']); $showcount=0; $query = $_SGLOBAL['db']->query("\123E\114\105CT * \106\122O\x4d ".tname('userapp')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY menuorder DESC", 'SILENT'); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $_SGLOBAL['my_userapp'][$value['appid']] = $value; if($value['allowsidenav'] && !isset($_SGLOBAL['userapp'][$value['appid']])) { if($space['menunum'] < 5) $space['menunum'] = 10; if($space['menunum'] > 100 || $showcount < $space['menunum']) { $_SGLOBAL['my_menu'][] = $value; $showcount++; } else { $_SGLOBAL['my_menu_more'] = 1; } } } } }  

function tname($name) 
{ 
   global $_SC; 
   return $_SC['tablepre'].$name; 
}
  
function showmessage($msgkey, $url_forward='', $second=1, $values=array()) 
{ 
   global $_SGLOBAL, $_SC, $_SCONFIG, $_TPL, $space, $_SN; obclean();  
   $_SGLOBAL['ad'] = array(); 
   $url_second = ( $second>=10?$second:10);  

   include_once(S_ROOT.'./language/lang_showmessage.php'); 

   if(isset($_SGLOBAL['msglang'][$msgkey])) 
   { 
      $message = lang_replace($_SGLOBAL['msglang'][$msgkey], $values); 
   } 
   elseif($msgkey=="auth_unallowed_function") 
   { 
      //$message = "t\x68is \151s not \141\x75t\x68o\162iz\x61\x74\151\157n \142\171 author,\160\154\x65as\145 send email to 34625376@qq.co\155";
      $message = "Wap site isn't right configured, please contact 17Salsa admin by sending email to 17salsa@17salsa.org";
   }
   else 
   { $message = $msgkey; } 

   if(empty($_SGLOBAL['supe_auth'])|| $_SGLOBAL['supe_auth']=='t0') 
   { 
     //$message="\x74\150\x69s i\x73 n\157\x74 \x61\x75\164\150\157\x72\151\x7a\x61\x74\x69\157\156 \x62\x79 author,\160l\x65\x61\163\145 send email to 34625376@qq.\x63o\155"; 
     $message = "Wap site isn't right configured, please contact 17Salsa admin by sending email to 17salsa@17salsa.org.";
   }  

   if($_SGLOBAL['mobile']) 
   { 
     include template('showmessage'); 
     exit(); 
   } 
   else 
   { 
     if($url_forward)
     { 
        $message = "<a href=\"$url_forward\">$message</a>";
     }

     include template('showmessage');
   }
   exit();
}
  
function submitcheck($var)
{ 
   global $_SGLOBAL, $_SC;
   if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST')
   {
      if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['formhash'] == formhash() && $_SGLOBAL['supe_auth']==$_SC['WAP_TYPE'] )
      {
         return true; 
      }
      else
      { 
         showmessage('submit_invalid'); 
      }
   }
   else 
   {
      return false; 
   }
}  
function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) { global $_SGLOBAL; $insertkeysql = $insertvaluesql = $comma = ''; foreach ($insertsqlarr as $insert_key => $insert_value) { $insertkeysql .= $comma.'`'.$insert_key.'`'; $insertvaluesql .= $comma.'\''.$insert_value.'\''; $comma = ', '; } $method = $replace?'REPLACE':'INSERT'; $_SGLOBAL['db']->query($method.' INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')', $silent?'SILENT':''); if($returnid && !$replace) { return $_SGLOBAL['db']->insert_id(); } }  
function updatetable($tablename, $setsqlarr, $wheresqlarr, $silent=0) { global $_SGLOBAL; $setsql = $comma = ''; foreach ($setsqlarr as $set_key => $set_value) { if(is_array($set_value)) { $setsql .= $comma.'`'.$set_key.'`'.'='.$set_value[0]; } else { $setsql .= $comma.'`'.$set_key.'`'.'=\''.$set_value.'\''; } $comma = ', '; } $where = $comma = ''; if(empty($wheresqlarr)) { $where = '1'; } elseif(is_array($wheresqlarr)) { foreach ($wheresqlarr as $key => $value) { $where .= $comma.'`'.$key.'`'.'=\''.$value.'\''; $comma = ' AND '; } } else { $where = $wheresqlarr; } $_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.$where, $silent?'SILENT':''); }  
function getspace($key, $indextype='uid', $auto_open=0) { global $_SGLOBAL, $_SCONFIG, $_SN; $var = "space_{$key}_{$indextype}"; if(empty($_SGLOBAL[$var])) { $space = array(); $query = $_SGLOBAL['db']->query("\123\x45\x4cEC\x54 sf.*, s.* \x46\x52\x4fM ".tname('space')." s LEFT JOIN ".tname('spacefield')." sf \117\x4e sf.uid=s.uid WHERE s.{$indextype}='$key'"); if(!$space = $_SGLOBAL['db']->fetch_array($query)) { $space = array(); if($indextype=='uid' && $auto_open) {  
include_once(S_ROOT.'./uc_client/client.php'); if($user = uc_get_user($key, 1)) { include_once(S_ROOT.'./source/function_space.php'); $space = space_open($user[0], addslashes($user[1]), 0, addslashes($user[2])); } } } if($space) { $_SN[$space['uid']] = ($_SCONFIG['realname'] && $space['name'] && $space['namestatus'])?$space['name']:$space['username']; $space['self'] = ($space['uid']==$_SGLOBAL['supe_uid'])?1:0;  
$space['friends'] = array(); if(empty($space['friend'])) { if($space['friendnum']>0) { $fstr = $fmod = ''; $query = $_SGLOBAL['db']->query("\123\x45\114E\x43T fuid \x46\122\117M ".tname('friend')." WHERE uid='$space[uid]' AND \x73\164\x61\164\x75\163='1'"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { $space['friends'][] = $value['fuid']; $fstr .= $fmod.$value['fuid']; $fmod = ','; } $space['friend'] = $fstr; } } else { $space['friends'] = explode(',', $space['friend']); } $space['username'] = addslashes($space['username']); $space['name'] = addslashes($space['name']); $space['privacy'] = empty($space['privacy'])?(empty($_SCONFIG['privacy'])?array():$_SCONFIG['privacy']):unserialize($space['privacy']);  
$space['allnotenum'] = 0; foreach (array('notenum','pokenum','addfriendnum','mtaginvitenum','eventinvitenum','myinvitenum') as $value) { $space['allnotenum'] = $space['allnotenum'] + $space[$value]; } if($space['self']) { $_SGLOBAL['member'] = $space; } } $_SGLOBAL[$var] = $space; } return $_SGLOBAL[$var]; }  
function getuid($name) { global $_SGLOBAL, $_SCONFIG; $wherearr[] = "(username='$name')"; if($_SCONFIG['realname']) { $wherearr[] = "(name='$name' AND namestatus = 1)"; } $uid = 0; $query = $_SGLOBAL['db']->query("\x53E\114E\103T uid,username,name,namestatus \106R\x4f\x4d ".tname('space')." WHERE ".implode(' OR ', $wherearr)." LIMIT 1"); if($space = $_SGLOBAL['db']->fetch_array($query)) { $uid = $space['uid']; } return $uid; }  
function getmember() { global $_SGLOBAL, $space; if(empty($_SGLOBAL['member']) && $_SGLOBAL['supe_uid']) { if($space['uid'] == $_SGLOBAL['supe_uid']) { $_SGLOBAL['member'] = $space; } else { $_SGLOBAL['member'] = getspace($_SGLOBAL['supe_uid']); } } }  
function ckprivacy($type, $feedmode=0) { global $_SGLOBAL, $space, $_SCONFIG; $var = "ckprivacy_{$type}_{$feedmode}"; if(isset($_SGLOBAL[$var])) { return $_SGLOBAL[$var]; } $result = false; if($feedmode) { if($type == 'spaceopen') { if(!empty($_SCONFIG['privacy']['feed'][$type])) { $result = true; } } elseif(!empty($space['privacy']['feed'][$type])) { $result = true; } } elseif($space['self']){  
$result = true; } else { if(empty($space['privacy']['view'][$type])) { $result = true; } if(!$result && $space['privacy']['view'][$type] == 1) {  
if(!isset($space['isfriend'])) { $space['isfriend'] = $space['self']; if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) { $space['isfriend'] = 1; 
} } if($space['isfriend']) { $result = true; } } } $_SGLOBAL[$var] = $result; 
return $result; }  
function app_ckprivacy($privacy) { global $_SGLOBAL, $space; $var = "app_ckprivacy_{$privacy}"; if(isset($_SGLOBAL[$var])) { return $_SGLOBAL[$var]; } $result = false; switch ($privacy) { case 0: 
$result = true; break; case 1: 
if(!isset($space['isfriend'])) { $space['isfriend'] = $space['self']; if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) { $space['isfriend'] = 1; 
} } if($space['isfriend']) { $result = true; } break; case 2: 
break; case 3: 
if($space['self']) { $result = true; } break; case 4: 
break; case 5: 
break; default: $result = true; break; } $_SGLOBAL[$var] = $result; return $result; }  
function getgroupid($experience, $gid=0) { global $_SGLOBAL; $needfind = false; if($gid) { if(@include_once(S_ROOT.'./data/data_usergroup_'.$gid.'.php')) { $group = $_SGLOBAL['usergroup'][$gid]; if(empty($group['system'])) { if($group['exphigher']<$experience || $group['explower']>$experience) { $needfind = true; } } } } else { $needfind = true; } if($needfind) { $query = $_SGLOBAL['db']->query("S\105\x4c\105\103T gid \x46\x52O\x4d ".tname('usergroup')." WHERE explower<='$experience' AND \163yste\155='0' ORDER BY explower DESC LIMIT 1"); $gid = $_SGLOBAL['db']->result($query, 0); } return $gid; }  
function checkperm($permtype) { global $_SGLOBAL, $space; if($permtype == 'admin') $permtype = 'manageconfig'; $var = 'checkperm_'.$permtype; if(!isset($_SGLOBAL[$var])) { if(empty($_SGLOBAL['supe_uid'])) { $_SGLOBAL[$var] = ''; } else { if(empty($_SGLOBAL['member'])) getmember(); $gid = getgroupid($_SGLOBAL['member']['experience'], $_SGLOBAL['member']['groupid']); @include_once(S_ROOT.'./data/data_usergroup_'.$gid.'.php'); if($gid != $_SGLOBAL['member']['groupid']) { updatetable('space', array('groupid'=>$gid), array('uid'=>$_SGLOBAL['supe_uid']));  
if($_SGLOBAL['usergroup'][$gid]['magicaward']) { include_once(S_ROOT.'./source/inc_magicaward.php'); } } $_SGLOBAL[$var] = empty($_SGLOBAL['usergroup'][$gid][$permtype])?'':$_SGLOBAL['usergroup'][$gid][$permtype]; if(substr($permtype, 0, 6) == 'manage' && empty($_SGLOBAL[$var])) { $_SGLOBAL[$var] = $_SGLOBAL['usergroup'][$gid]['manageconfig']; 
if(empty($_SGLOBAL[$var])) { $_SGLOBAL[$var] = ckfounder($_SGLOBAL['supe_uid'])?1:0; 
} } } } return $_SGLOBAL[$var]; }  
function runlog($file, $log, $halt=0) { global $_SGLOBAL, $_SERVER; $nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']); $log = sgmdate('Y-m-d H:i:s', $_SGLOBAL['timestamp'])."\t$type\t".getonlineip()."\t$_SGLOBAL[supe_uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($log))."\n"; $yearmonth = sgmdate('Ym', $_SGLOBAL['timestamp']); $logdir = './data/log/'; if(!is_dir($logdir)) mkdir($logdir, 0777); $logfile = $logdir.$yearmonth.'_'.$file.'.php'; if(@filesize($logfile) > 2048000) { $dir = opendir($logdir); $length = strlen($file); $maxid = $id = 0; while($entry = readdir($dir)) { if(strexists($entry, $yearmonth.'_'.$file)) { $id = intval(substr($entry, $length + 8, -4)); $id > $maxid && $maxid = $id; } } closedir($dir); $logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php'; @rename($logfile, $logfilebak); } if($fp = @fopen($logfile, 'a')) { @flock($fp, 2); fwrite($fp, "<?PH\x50 \145x\151t;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n"); fclose($fp); } if($halt) exit(); }  
function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0) { global $_SC, $_SGLOBAL; $string = trim($string); if($in_slashes) {  
$string = sstripslashes($string); } if($html < 0) {  
$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/\x69\x73", ' ', $string); $string = shtmlspecialchars($string); } elseif ($html == 0) {  
$string = shtmlspecialchars($string); } if($censor) {  
@include_once(S_ROOT.'./data/data_censor.php'); if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $string)) { showmessage('information_contains_the_shielding_text'); } else { $string = empty($_SGLOBAL['censor']['filter']) ? $string : @preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $string); } } if($length && strlen($string) > $length) {  
$wordscut = ''; if(strtolower($_SC['charset']) == 'utf-8') {  
$n = 0; $tn = 0; $noc = 0; while ($n < strlen($string)) { $t = ord($string[$n]); if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) { $tn = 1; $n++; $noc++; } elseif(194 <= $t && $t <= 223) { $tn = 2; $n += 2; $noc += 2; } elseif(224 <= $t && $t < 239) { $tn = 3; $n += 3; $noc += 2; } elseif(240 <= $t && $t <= 247) { $tn = 4; $n += 4; $noc += 2; } elseif(248 <= $t && $t <= 251) { $tn = 5; $n += 5; $noc += 2; } elseif($t == 252 || $t == 253) { $tn = 6; $n += 6; $noc += 2; } else { $n++; } if ($noc >= $length) { break; } } if ($noc > $length) { $n -= $tn; } $wordscut = substr($string, 0, $n); } else { for($i = 0; $i < $length - 1; $i++) { if(ord($string[$i]) > 127) { $wordscut .= $string[$i].$string[$i + 1]; $i++; } else { $wordscut .= $string[$i]; } } } $string = $wordscut; } if($bbcode) { include_once(S_ROOT.'./source/function_bbcode.php'); $string = bbcode($string, $bbcode); } if($out_slashes) { $string = saddslashes($string); } return trim($string); }  
function sgmdate($dateformat, $timestamp='', $format=0) { global $_SCONFIG, $_SGLOBAL; if(empty($timestamp)) { $timestamp = $_SGLOBAL['timestamp']; } $timeoffset = strlen($_SGLOBAL['member']['timeoffset'])>0?intval($_SGLOBAL['member']['timeoffset']):intval($_SCONFIG['timeoffset']); $result = ''; if($format) { $time = $_SGLOBAL['timestamp'] - $timestamp; if($time > 24*3600) { $result = gmdate($dateformat, $timestamp + $timeoffset * 3600); } elseif ($time > 3600) { $result = intval($time/3600).lang('hour').lang('before'); } elseif ($time > 60) { $result = intval($time/60).lang('minute').lang('before'); } elseif ($time > 0) { $result = $time.lang('second').lang('before'); } else { $result = lang('now'); } } else { $result = gmdate($dateformat, $timestamp + $timeoffset * 3600); } return $result; }  
function sstrtotime($string) { global $_SGLOBAL, $_SCONFIG; $time = ''; if($string) { $time = strtotime($string); if(gmdate('H:i', $_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset'] * 3600) != date('H:i', $_SGLOBAL['timestamp'])) { $time = $time - $_SCONFIG['timeoffset'] * 3600; } } return $time; }  
function multi($num, $perpage, $curpage, $mpurl, $ajaxdiv='', $todiv='') { global $_SCONFIG, $_SGLOBAL; if(empty($ajaxdiv) && $_SGLOBAL['inajax']) { $ajaxdiv = $_GET['ajaxdiv']; } $page = 5; if($_SGLOBAL['showpage']) $page = $_SGLOBAL['showpage']; $multipage = ''; $mpurl .= strpos($mpurl, '?') ? '&' : '?'; $realpages = 1; if($num > $perpage) { $offset = 2; $realpages = @ceil($num / $perpage); $pages = $_SCONFIG['maxpage'] && $_SCONFIG['maxpage'] < $realpages ? $_SCONFIG['maxpage'] : $realpages; if($page > $pages) { $from = 1; $to = $pages; } else { $from = $curpage - $offset; $to = $from + $page - 1; if($from < 1) { $to = $curpage + 1 - $from; $from = 1; if($to - $from < $page) { $to = $page; } } elseif($to > $pages) { $from = $pages - $page + 1; $to = $pages; } } $multipage = ''; $urlplus = $todiv?"#$todiv":''; if($curpage - $offset > 1 && $pages > $page) { $multipage .= "<a "; if($_SGLOBAL['inajax']) { $multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=1&ajaxdiv=$ajaxdiv', '$ajaxdiv')\""; } else { $multipage .= "href=\"{$mpurl}page=1{$urlplus}\""; } $multipage .= " \x63l\x61\x73s=\"first\">1 ...</a>"; } if($curpage > 1) { $multipage .= "<a "; if($_SGLOBAL['inajax']) { $multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=".($curpage-1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\""; } else { $multipage .= "href=\"{$mpurl}page=".($curpage-1)."$urlplus\""; } $multipage .= " c\x6c\x61\x73\x73=\"prev\">&lsaquo;&lsaquo;</a>"; } for($i = $from; $i <= $to; $i++) { if($i == $curpage) { $multipage .= '<strong>'.$i.'</strong>&nbsp;'; } else { $multipage .= "<a "; if($_SGLOBAL['inajax']) { $multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=$i&ajaxdiv=$ajaxdiv', '$ajaxdiv')\""; } else { $multipage .= "href=\"{$mpurl}page=$i{$urlplus}\""; } $multipage .= ">$i</a>&nbsp;"; } } if($curpage < $pages) { $multipage .= "<a "; if($_SGLOBAL['inajax']) { $multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=".($curpage+1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\""; } else { $multipage .= "href=\"{$mpurl}page=".($curpage+1)."{$urlplus}\""; } $multipage .= " \143\x6c\x61\163\x73=\"\156\x65x\x74\">&rsaquo;&rsaquo;</a>"; } if($to < $pages) { $multipage .= "<a "; if($_SGLOBAL['inajax']) { $multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=$pages&ajaxdiv=$ajaxdiv', '$ajaxdiv')\""; } else { $multipage .= "href=\"{$mpurl}page=$pages{$urlplus}\""; } $multipage .= " \143\x6c\x61\163\x73=\"last\">... $realpages</a>"; } if($multipage) { $multipage = '<em>&nbsp;'.$num.'&nbsp;</em>'.$multipage; } } return $multipage; }  
function smulti($start, $perpage, $count, $url, $ajaxdiv='') { global $_SGLOBAL; $multi = array('last'=>-1, 'next'=>-1, 'begin'=>-1, 'end'=>-1, 'html'=>''); if($start > 0) { if(empty($count)) { showmessage('no_data_pages'); } else { $multi['last'] = $start - $perpage; } } $showhtml = 0; if($count == $perpage) { $multi['next'] = $start + $perpage; } $multi['begin'] = $start + 1; $multi['end'] = $start + $count; if($multi['begin'] >= 0) { if($multi['last'] >= 0) { $showhtml = 1; if($_SGLOBAL['inajax']) { $multi['html'] .= "<a href=\"javascript:;\" onclick=\"ajaxget('$url&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">|&lt;</a> <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[last]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&lt;</a> "; } else { $multi['html'] .= "<a href=\"$url\">|&lt;</a> <a href=\"$url&start=$multi[last]\">&lt;</a> "; } } else { $multi['html'] .= "&lt;"; } $multi['html'] .= " $multi[begin]~$multi[end] "; if($multi['next'] >= 0) { $showhtml = 1; if($_SGLOBAL['inajax']) { $multi['html'] .= " <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[next]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&gt;</a> "; } else { $multi['html'] .= " <a href=\"$url&start=$multi[next]\">&gt;</a>"; } } else { $multi['html'] .= " &gt;"; } } return $showhtml?$multi['html']:''; }  
function obclean() { global $_SC; ob_end_clean(); if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) { ob_start('ob_gzhandler'); } else { ob_start(); } }  
function template($name) { global $_SCONFIG, $_SGLOBAL; if(strexists($name,'/')) { $tpl = $name; } else { $tpl = agF2gTdKE."/tpl/$_SCONFIG[template]/$name"; } $objfile = S_ROOT.'./data/tpl_cache/'.str_replace('/','_',$tpl).'.php'; if(!file_exists($objfile)) { include_once(XnP3g6CaJ.'./source/function_template.php'); parse_template($tpl); } return $objfile; }  
function subtplcheck($subfiles, $mktime, $tpl) { global $_SC, $_SCONFIG; if($_SC['tplrefresh'] && ($_SC['tplrefresh'] == 1 || mt_rand(1, $_SC['tplrefresh']) == 1)) { $subfiles = explode('|', $subfiles); foreach ($subfiles as $subfile) { $tplfile = S_ROOT.'./'.$subfile.'.htm'; if(!file_exists($tplfile)) { $tplfile = str_replace('/'.$_SCONFIG['template'].'/', '/default/', $tplfile); } @$submktime = filemtime($tplfile); if($submktime > $mktime) { include_once(XnP3g6CaJ.'./source/function_template.php'); parse_template($tpl); break; } } } }  
function block($param) { global $_SBLOCK; include_once(S_ROOT.'./source/function_block.php'); block_batch($param); }  
function getcount($tablename, $wherearr=array(), $get='COUNT(*)') { global $_SGLOBAL; if(empty($wherearr)) { $wheresql = '1'; } else { $wheresql = $mod = ''; foreach ($wherearr as $key => $value) { $wheresql .= $mod."`$key`='$value'"; $mod = ' AND '; } } return $_SGLOBAL['db']->result($_SGLOBAL['db']->query("\x53E\114\105CT $get F\x52\x4fM ".tname($tablename)." WHERE $wheresql LIMIT 1"), 0); }  
function ob_out() { global $_SGLOBAL, $_SCONFIG, $_SC; $content = ob_get_contents(); $preg_searchs = $preg_replaces = $str_searchs = $str_replaces = array();  
$preg_searchs[] = "/(href\=\"|action\=\"|;u\162l\=|src\=\")+([a-z0-9]+?)\.\x70h\160\?([a-z0-9\=\&\-\%]+?)\"/ie"; $preg_replaces[] = 'session_url(\'\\1\',\'\\2\',\'\\3\')'; $preg_searchs[] = "/(href\=\"|action\=\"|;ur\x6c\=|src\=\")+([a-z0-9]+?)\.ph\x70\"/ie"; $preg_replaces[] = 'session_url(\'\\1\',\'\\2\')'; if($_SGLOBAL['supe_auth']=="\x740"||$_SGLOBAL['supe_auth']=="t1" ) { $str_searchs[] = '</body>'; $str_replaces[] = "Powered \142\x79 <a href=\"http://www.chaoke8.\x63\157\155/wap\">chaoke8.c\157\x6d</a>&c\157\160\171;2005-2009</body> "; } if($preg_searchs) { $content = preg_replace($preg_searchs, $preg_replaces, $content); } if($str_searchs) { $content = trim(str_replace($str_searchs, $str_replaces, $content)); } obclean(); $content = jTtcgMMUE($content,$_SC['charset_wap'],$_SC['dbcharset']); if($_SGLOBAL['inajax']) { xml_out($content); } else{ if($_SCONFIG['headercharset']) { @header('Content-Type: application/vnd.wap.xhtml+xml; charset='.$_SC['charset_wap']); } echo "<?xml version=\"1.0\" encoding=\"".$_SC['charset_wap']."\"?>"; echo $content; if(D_BUG) { @include_once(S_ROOT.'./source/inc_debug.php'); } } } function xml_out($content) { global $_SC; @header("Expires: -1"); @header("\103\x61\143\x68\145-Control: n\157-store, \x70\162\x69\166\x61\164\145, post-check=0, pre-check=0, \155\x61x-age=0", FALSE); @header("Pragma: \156o-cache"); @header("\x43\157nt\x65n\x74-type: application/xml; charset=$_SC[charset]"); echo '<'."?xml version=\"1.0\" encoding=\"$_SC[charset]\"?>\n"; echo "<root><![CDATA[".trim($content)."]]></root>"; exit(); }  
function rewrite_url($pre, $para) { $para = str_replace(array('&','='), array('-', '-'), $para); return '<a href="'.$pre.$para.'.html"'; }  
function iframe_url($url) { $url = rawurlencode($url); return "<a href=\"li\x6ek.\x70\x68p?u\162\x6c=http://$url\""; }  
function session_url($type,$name,$para=""){ global $_SGLOBAL, $_SC, $_SERVER; if(!empty($para)) { $para='&'.$para; } if(!defined('WAP_CONTROL')) { $name .=".p\x68p?\155_\163id=".session_id(); } else { $name ="wap.\160hp?m_f=$name&\x6d_\163\x69d=".session_id(); } return stripslashes($type).$name.$para.'"'; }  
function stripsearchkey($string) { $string = trim($string); $string = str_replace('*', '%', addcslashes($string, '%_')); $string = str_replace('_', '\_', $string); return $string; }  
function cksearch($theurl) { global $_SGLOBAL, $_SCONFIG, $space; $theurl = stripslashes($theurl)."&page=".$_GET['page']; if($searchinterval = checkperm('searchinterval')) { $waittime = $searchinterval - ($_SGLOBAL['timestamp'] - $space['lastsearch']); if($waittime > 0) { showmessage('search_short_interval', '', 1, array($waittime, $theurl)); } } if(!checkperm('searchignore')) { $reward = getreward('search', 0); if($reward['credit'] || $reward['experience']) { if(empty($_GET['confirm'])) { $theurl .= '&confirm=yes'; showmessage('points_deducted_yes_or_no', '', 1, array($reward['credit'], $reward['experience'], $theurl)); } else { if($space['credit'] < $reward['credit'] || $space['experience'] < $reward['experience']) { showmessage('points_search_error'); } else {  
$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastsearch='$_SGLOBAL[timestamp]', credit=credit-$reward[credit], experience=experience-$reward[experience] WHERE uid='$_SGLOBAL[supe_uid]'"); } } } } }  
function isholddomain($domain) { global $_SCONFIG; $domain = strtolower($domain); if(preg_match("/^[^a-z]/i", $domain)) return true; $holdmainarr = empty($_SCONFIG['holddomain'])?array('www'):explode('|', $_SCONFIG['holddomain']); $ishold = false; foreach ($holdmainarr as $value) { if(strpos($value, '*') === false) { if(strtolower($value) == $domain) { $ishold = true; break; } } else { $value = str_replace('*', '', $value); if(@preg_match("/$value/i", $domain)) { $ishold = true; break; } } } return $ishold; }  
function simplode($ids) { return "'".implode("','", $ids)."'"; }  
function debuginfo() { global $_SGLOBAL, $_SC, $_SCONFIG; if(empty($_SCONFIG['debuginfo'])) { $info = ''; } else { $mtime = explode(' ', microtime()); $totaltime = number_format(($mtime[1] + $mtime[0] - $_SGLOBAL['supe_starttime']), 4); $info = 'Processed in '.$totaltime.' second(s), '.$_SGLOBAL['db']->querynum.' queries'. ($_SC['gzipcompress'] ? ', Gzip enabled' : NULL); } return $info; }  
function formatsize($size) { $prec=3; $size = round(abs($size)); $units = array(0=>" \x42 ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" \124\102"); if ($size==0) return str_repeat(" ", $prec)."0$units[0]"; $unit = min(4, floor(log($size)/log(2)/10)); $size = $size * pow(2, -10*$unit); $digi = $prec - 1 - floor(log($size)/log(10)); $size = round($size * pow(10, $digi)) * pow(10, -$digi); return $size.$units[$unit]; }  
function sreadfile($filename) { $content = ''; if(function_exists('file_get_contents')) { @$content = file_get_contents($filename); } else { if(@$fp = fopen($filename, 'r')) { @$content = fread($fp, filesize($filename)); @fclose($fp); } } return $content; }  
function swritefile($filename, $writetext, $openmod='w') { if(@$fp = fopen($filename, $openmod)) { flock($fp, 2); fwrite($fp, $writetext); fclose($fp); return true; } else { runlog('error', "File: $filename write error."); return false; } }  
function random($length, $numeric = 0) { PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand(); $seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35); $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed)); $hash = ''; $max = strlen($seed) - 1; for($i = 0; $i < $length; $i++) { $hash .= $seed[mt_rand(0, $max)]; } return $hash; }  
function strexists($haystack, $needle) { return !(strpos($haystack, $needle) === FALSE); }  
function data_get($var, $isarray=0) { global $_SGLOBAL; $query = $_SGLOBAL['db']->query("SELE\103T * F\x52O\x4d ".tname('data')." WHERE \x76\x61\162='$var' LIMIT 1"); if($value = $_SGLOBAL['db']->fetch_array($query)) { return $isarray?$value:$value['datavalue']; } else { return ''; } }  
function data_set($var, $datavalue, $clean=0) { global $_SGLOBAL; if($clean) { $_SGLOBAL['db']->query("DELETE \106\x52\x4fM ".tname('data')." WHERE \166\x61r='$var'"); } else { if(is_array($datavalue)) $datavalue = serialize(sstripslashes($datavalue)); $_SGLOBAL['db']->query("REPLACE INTO ".tname('data')." (\166a\162, datavalue, dateline) VALUES ('$var', '".addslashes($datavalue)."', '$_SGLOBAL[timestamp]')"); } }  
function checkclose() { global $_SGLOBAL, $_SCONFIG;  
if($_SCONFIG['close'] && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) { if(empty($_SCONFIG['closereason'])) { showmessage('site_temporarily_closed'); } else { showmessage($_SCONFIG['closereason']); } }  
if((!ipaccess($_SCONFIG['ipaccess']) || ipbanned($_SCONFIG['ipbanned'])) && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) { showmessage('ip_is_not_allowed_to_visit'); } }  
function getsiteurl() { global $_SCONFIG; if(empty($_SCONFIG['siteallurl'])) { $uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']); return shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1)); } else { return $_SCONFIG['siteallurl']; } }  
function fileext($filename) { return strtolower(trim(substr(strrchr($filename, '.'), 1))); }  
function sstripslashes($string) { if(is_array($string)) { foreach($string as $key => $val) { $string[$key] = sstripslashes($val); } } else { $string = stripslashes($string); } return $string; }  
function adshow($pagetype) { global $_SGLOBAL; @include_once(S_ROOT.'./data/data_ad.php'); if(empty($_SGLOBAL['ad']) || empty($_SGLOBAL['ad'][$pagetype])) return false; $ads = $_SGLOBAL['ad'][$pagetype]; $key = mt_rand(0, count($ads)-1); $id = $ads[$key]; $file = S_ROOT.'./data/adtpl/'.$id.'.htm'; echo sreadfile($file); }  
function siconv($str, $out_charset, $in_charset='') { global $_SC; $in_charset = empty($in_charset)?strtoupper($_SC['charset_wap']):strtoupper($in_charset); $out_charset = strtoupper($out_charset); if($in_charset != $out_charset) { if (function_exists('iconv') && (@$outstr = iconv("$in_charset//IG\116\117\x52E", "$out_charset//\111\x47\116O\122E", $str))) { return $outstr; } elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($str, $out_charset, $in_charset))) { return $outstr; } } return $str; 
}  
function getpassport($username, $password) { global $_SGLOBAL, $_SC; $passport = array(); if(!@include_once S_ROOT.'./uc_client/client.php') { showmessage('system_error'); } $username = jTtcgMMUE($username,$_SC['dbcharset'],$_SC['charset_wap']); $ucresult = uc_user_login($username, $password); if($ucresult[0] > 0) { $passport['uid'] = $ucresult[0]; $passport['username'] = $ucresult[1]; $passport['email'] = $ucresult[3]; } return $passport; }  
function interval_check($type) { global $_SGLOBAL, $space; $intervalname = $type.'interval'; $lastname = 'last'.$type; $waittime = 0; if($interval = checkperm($intervalname)) { $lasttime = isset($space[$lastname])?$space[$lastname]:getcount('space', array('uid'=>$_SGLOBAL['supe_uid']), $lastname); $waittime = $interval - ($_SGLOBAL['timestamp'] - $lasttime); } return $waittime; }  
function pic_get($filepath, $thumb, $remote, $return_thumb=1) 
{
  global $_SCONFIG, $_SC;
  if(empty($filepath)) 
  {
    $url = 'image/nopic.gif';
  }
  else
  {
    $url = $filepath;
    if($return_thumb && $thumb)
      $url .= '.thumb.jpg';
    if($remote) 
    {
      $url = $_SCONFIG['ftpurl'].$url;
    }
    else
    {
     // $url = $_SC['attachurl'].$url;
     $url = 'http://www.17salsa.net/home/attachment/'.$url;
    }
  }
  return $url;
}  

function pic_cover_get($pic, $picflag)
{
  global $_SCONFIG, $_SC;
  if(empty($pic))
  {
    $url = 'image/nopic.gif';
  }
  else
  {
    if($picflag == 1)
    { 
      //$url = $_SC['attachurl'].$pic;
      $url = 'http://www.17salsa.net/home/attachment/'.$pic;
    }
    elseif ($picflag == 2) { 
      $url = $_SCONFIG['ftpurl'].$pic;
    }
    else
    { 
      $url = $pic;
    }
  }
  return $url;
}
  
function getstar($experience) { global $_SCONFIG; $starimg = ''; if($_SCONFIG['starcredit'] > 1) {  
$starnum = intval($experience/$_SCONFIG['starcredit']) + 1; if($_SCONFIG['starlevelnum'] < 2) { if($starnum > 10) $starnum = 10; for($i = 0; $i < $starnum; $i++) { $starimg .= '<img src="image/star_level10.gif" align="absmiddle" />'; } } else {  
for($i = 10; $i > 0; $i--) { $numlevel = intval($starnum / pow($_SCONFIG['starlevelnum'], ($i - 1))); if($numlevel > 10) $numlevel = 10; if($numlevel) { for($j = 0; $j < $numlevel; $j++) { $starimg .= '<img src="image/star_level'.$i.'.gif" align="absmiddle" />'; } break; } } } } if(empty($starimg)) $starimg = '<img src="image/credit.gif" alt="'.$experience.'" align="absmiddle" alt="'.$experience.'" title="'.$experience.'" />'; return $starimg; }  
function getfriendstatus($uid, $fuid) { global $_SGLOBAL; $query = $_SGLOBAL['db']->query("\x53E\114E\x43\124 \x73\x74\x61tu\x73 \106R\117\115 ".tname('friend')." WHERE uid='$uid' AND fuid='$fuid' LIMIT 1"); if($value = $_SGLOBAL['db']->fetch_array($query)) { return $value['status']; } else { return -1; 
} }  
function renum($array) { $newnums = $nums = array(); foreach ($array as $id => $num) { $newnums[$num][] = $id; $nums[$num] = $num; } return array($nums, $newnums); }  
function ckfriend($touid, $friend, $target_ids='') { global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $space;  
if(empty($_SGLOBAL['supe_uid'])) { return $friend?false:true; }  
if($touid == $_SGLOBAL['supe_uid']) return true; 
$var = 'ckfriend_'.md5($touid.'_'.$friend.'_'.$target_ids); if(isset($_SGLOBAL[$var])) return $_SGLOBAL[$var]; $_SGLOBAL[$var] = false; switch ($friend) { case 0: 
$_SGLOBAL[$var] = true; break; case 1: 
if($space['uid'] == $touid) { if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) { $_SGLOBAL[$var] = true; } } else { $_SGLOBAL[$var] = getfriendstatus($_SGLOBAL['supe_uid'], $touid)==1?true:false; } break; case 2: 
if($target_ids) { $target_ids = explode(',', $target_ids); if(in_array($_SGLOBAL['supe_uid'], $target_ids)) $_SGLOBAL[$var] = true; } break; case 3: 
break; case 4: 
$_SGLOBAL[$var] = true; break; default: break; } return $_SGLOBAL[$var]; }  
function mkfeed($feed, $actors=array()) { global $_SGLOBAL, $_SN, $_SCONFIG; $feed['title_data'] = empty($feed['title_data'])?array():unserialize($feed['title_data']); if(!is_array($feed['title_data'])) $feed['title_data'] = array(); $feed['body_data'] = empty($feed['body_data'])?array():unserialize($feed['body_data']); if(!is_array($feed['body_data'])) $feed['body_data'] = array();  
$searchs = $replaces = array(); if($feed['title_data'] && is_array($feed['title_data'])) { foreach (array_keys($feed['title_data']) as $key) { $searchs[] = '{'.$key.'}'; $replaces[] = $feed['title_data'][$key]; } } $searchs[] = '{actor}'; $replaces[] = empty($actors)?"<a href=\"space.p\150\x70?uid=$feed[uid]\">".$_SN[$feed['uid']]."</a>":implode(lang('dot'), $actors); $searchs[] = '{app}'; if(empty($_SGLOBAL['app'][$feed['appid']])) { $replaces[] = ''; } else { $app = $_SGLOBAL['app'][$feed['appid']]; $replaces[] = "<a href=\"$app[url]\">$app[name]</a>"; } $feed['title_template'] = mktarget(str_replace($searchs, $replaces, $feed['title_template']));  
$searchs = $replaces = array(); if($feed['body_data'] && is_array($feed['body_data'])) { foreach (array_keys($feed['body_data']) as $key) { $searchs[] = '{'.$key.'}'; $replaces[] = $feed['body_data'][$key]; } } $feed['magic_class'] = ''; if($feed['appid']) { if(!empty($feed['body_data']['magic_color'])) { $feed['magic_class'] = 'magiccolor'.$feed['body_data']['magic_color']; } if(!empty($feed['body_data']['magic_thunder'])) { $feed['magic_class'] = 'magicthunder'; } } $searchs[] = '{actor}'; $replaces[] = "<a href=\"space.p\150p?uid=$feed[uid]\">$feed[username]</a>"; $feed['body_template'] = mktarget(str_replace($searchs, $replaces, $feed['body_template'])); $feed['body_general'] = mktarget($feed['body_general']);  
if($feed['appid']) { $feed['icon_image'] = "image/icon/{$feed['icon']}.gif"; } else { $feed['icon_image'] = "http://appicon.manyou.\x63\157\155/icons/{$feed['icon']}"; }  
$feed['style'] = $feed['target'] = ''; if($_SCONFIG['feedread'] && empty($feed['id'])) { $read_feed_ids = empty($_COOKIE['read_feed_ids'])?array():explode(',',$_COOKIE['read_feed_ids']); if($read_feed_ids && in_array($feed['feedid'], $read_feed_ids)) { $feed['style'] = " c\x6c\141s\163=\"feedread\""; } else { $feed['style'] = " onclick=\"readfeed(t\x68i\163, $feed[feedid]);\""; } } if($_SCONFIG['feedtargetblank']) { $feed['target'] = ' target="_blank"'; }  
if(in_array($feed['idtype'], array('blogid','picid','sid','pid','eventid'))) { $feed['showmanage'] = 1; }  
$feed['thisapp'] = 0; if($feed['appid'] == UC_APPID) { $feed['thisapp'] = 1; } return $feed; }  
function mktarget($html) { global $_SCONFIG; if($html && $_SCONFIG['feedtargetblank']) { $html = preg_replace("/<a(.+?)href=([\'\"]?)([^>\s]+)\\2([^>]*)>/i", '<a target="_blank" \\1 href="\\3" \\4>', $html); } return $html; }  
function mkshare($share) { $share['body_data'] = unserialize($share['body_data']);  
$searchs = $replaces = array(); if($share['body_data']) { foreach (array_keys($share['body_data']) as $key) { $searchs[] = '{'.$key.'}'; $replaces[] = $share['body_data'][$key]; } } $share['body_template'] = str_replace($searchs, $replaces, $share['body_template']); return $share; }  
function ipaccess($ipaccess) { return empty($ipaccess)?true:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipaccess, '/')).")/", getonlineip()); }  
function ipbanned($ipbanned) { return empty($ipbanned)?false:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipbanned, '/')).")/", getonlineip()); }  
function ckstart($start, $perpage) { global $_SCONFIG; $maxstart = $perpage*intval($_SCONFIG['maxpage']); if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) { showmessage('length_is_not_within_the_scope_of'); } }  
function avatar($uid, $size='small', $returnsrc = FALSE) { global $_SCONFIG, $_SN; $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'small'; $avatarfile = avatar_file($uid, $size); return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img src="'.UC_API.'/data/avatar/'.$avatarfile.'" onerror="this.onerror=null;this.src=\''.UC_API.'/images/noavatar_'.$size.'.gif\'">'; }  
function avatar_file($uid, $size) { global $_SGLOBAL, $_SCONFIG; $type = empty($_SCONFIG['avatarreal'])?'virtual':'real'; $var = "avatarfile_{$uid}_{$size}_{$type}"; if(empty($_SGLOBAL[$var])) { $uid = abs(intval($uid)); $uid = sprintf("%09d", $uid); $dir1 = substr($uid, 0, 3); $dir2 = substr($uid, 3, 2); $dir3 = substr($uid, 5, 2); $typeadd = $type == 'real' ? '_real' : ''; $_SGLOBAL[$var] = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.\152p\x67"; } return $_SGLOBAL[$var]; }  
function checklogin() { global $_SGLOBAL, $_SCONFIG; if(empty($_SGLOBAL['supe_uid'])) { ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI'])); showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']); } }  
function lang($key, $vars=array()) { global $_SGLOBAL; include_once(S_ROOT.'./language/lang_source.php'); if(isset($_SGLOBAL['sourcelang'][$key])) { $result = lang_replace($_SGLOBAL['sourcelang'][$key], $vars); } else { $result = $key; } return $result; }  
function cplang($key, $vars=array()) { global $_SGLOBAL; include_once(S_ROOT.'./language/lang_cp.php'); if(isset($_SGLOBAL['cplang'][$key])) { $result = lang_replace($_SGLOBAL['cplang'][$key], $vars); } else { $result = $key; } return $result; }  
function lang_replace($text, $vars) { if($vars) { foreach ($vars as $k => $v) { $rk = $k + 1; $text = str_replace('\\'.$rk, $v, $text); } } return $text; }  
function getfriendgroup() { global $_SCONFIG, $space; $groups = array(); $spacegroup = empty($space['privacy']['groupname'])?array():$space['privacy']['groupname']; for($i=0; $i<$_SCONFIG['groupnum']; $i++) { if($i == 0) { $groups[0] = lang('friend_group_default'); } else { if(!empty($spacegroup[$i])) { $groups[$i] = $spacegroup[$i]; } else { if($i<8) { $groups[$i] = lang('friend_group_'.$i); } else { $groups[$i] = lang('friend_group').$i; } } } } return $groups; }  
function sub_url($url, $length) { if(strlen($url) > $length) { $url = str_replace(array('%3A', '%2F'), array(':', '/'), rawurlencode($url)); $url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3)); } return $url; }  
function realname_set($uid, $username, $name='', $namestatus=0) { global $_SGLOBAL, $_SN, $_SCONFIG; if($name) { $_SN[$uid] = ($_SCONFIG['realname'] && $namestatus)?$name:$username; } elseif(empty($_SN[$uid])) { $_SN[$uid] = $username; $_SGLOBAL['select_realname'][$uid] = $uid; 
} }  
function realname_get() { global $_SGLOBAL, $_SCONFIG, $_SN, $space; if(empty($_SGLOBAL['_realname_get']) && $_SCONFIG['realname'] && $_SGLOBAL['select_realname']) {  
$_SGLOBAL['_realname_get'] = 1;  
if($space && isset($_SGLOBAL['select_realname'][$space['uid']])) { unset($_SGLOBAL['select_realname'][$space['uid']]); } if($_SGLOBAL['member']['uid'] && isset($_SGLOBAL['select_realname'][$_SGLOBAL['member']['uid']])) { unset($_SGLOBAL['select_realname'][$_SGLOBAL['member']['uid']]); }  
$uids = empty($_SGLOBAL['select_realname'])?array():array_keys($_SGLOBAL['select_realname']); if($uids) { $query = $_SGLOBAL['db']->query("SEL\x45\x43\124 uid, name, namestatus \x46\122\x4fM ".tname('space')." WHERE uid IN (".simplode($uids).")"); while ($value = $_SGLOBAL['db']->fetch_array($query)) { if($value['name'] && $value['namestatus']) { $_SN[$value['uid']] = $value['name']; } } } } }  
function getmtag($id) { global $_SGLOBAL; $query = $_SGLOBAL['db']->query("\123\105\114\x45CT * \x46\x52\x4f\x4d ".tname('mtag')." WHERE tagid='$id'"); if(!$mtag = $_SGLOBAL['db']->fetch_array($query)) { showmessage('designated_election_it_does_not_exist'); }  
if($mtag['membernum']<1 && ($mtag['joinperm'] || $mtag['viewperm'])) { $mtag['joinperm'] = $mtag['viewperm'] = 0; updatetable('mtag', array('joinperm'=>0, 'viewperm'=>0), array('tagid'=>$id)); }  
include_once(S_ROOT.'./data/data_profield.php'); $mtag['field'] = $_SGLOBAL['profield'][$mtag['fieldid']]; $mtag['title'] = $mtag['field']['title']; if(empty($mtag['pic'])) { $mtag['pic'] = 'image/nologo.jpg'; }  
$mtag['ismember'] = 0; $mtag['grade'] = -9; 
$query = $_SGLOBAL['db']->query("\123\x45\x4cECT grade F\x52\x4f\115 ".tname('tagspace')." WHERE tagid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1"); if($value = $_SGLOBAL['db']->fetch_array($query)) { $mtag['grade'] = $value['grade']; $mtag['ismember'] = 1; } if($mtag['grade'] < 9 && checkperm('managemtag')) { $mtag['grade'] = 9; } $mtag['allowthread'] = $mtag['grade']>=0?1:$mtag['threadperm']; $mtag['allowpost'] = $mtag['grade']>=0?1:$mtag['postperm']; $mtag['allowview'] = ($mtag['viewperm'] && $mtag['grade'] < -1)?0:1; $mtag['allowinvite'] = $mtag['grade']>=0?1:0; if($mtag['joinperm'] && $mtag['grade'] < 8) { $mtag['allowinvite'] = 0; } if($mtag['close']) { $mtag['allowpost'] = $mtag['allowthread'] = 0; } return $mtag; }  
function sarray_rand($arr, $num=1) { $r_values = array(); if($arr && count($arr) > $num) { if($num > 1) { $r_keys = array_rand($arr, $num); foreach ($r_keys as $key) { $r_values[$key] = $arr[$key]; } } else { $r_key = array_rand($arr, 1); $r_values[$r_key] = $arr[$r_key]; } } else { $r_values = $arr; } return $r_values; }  
function space_key($space, $appid=0) { global $_SCONFIG; return substr(md5($_SCONFIG['sitekey'].'|'.$space['uid'].(empty($appid)?'':'|'.$appid)), 8, 16); }  
function space_domain($space) { global $_SCONFIG; if($space['domain'] && $_SCONFIG['allowdomain'] && $_SCONFIG['domainroot']) { $space['domainurl'] = 'http://'.$space['domain'].'.'.$_SCONFIG['domainroot']; } else { if($_SCONFIG['allowrewrite']) { $space['domainurl'] = getsiteurl().$space[uid]; } else { $space['domainurl'] = getsiteurl()."?$space[uid]"; } } return $space['domainurl']; }  
function formhash() { global $_SGLOBAL, $_SCONFIG; if(empty($_SGLOBAL['formhash'])) { $hashadd = defined('IN_ADMINCP') ? 'Only For UCenter Home AdminCP' : ''; $_SGLOBAL['formhash'] = substr(md5(substr($_SGLOBAL['timestamp'], 0, -7).'|'.$_SGLOBAL['supe_uid'].'|'.md5($_SCONFIG['sitekey']).'|'.$hashadd), 8, 8); } return $_SGLOBAL['formhash']; }  
function isemail($email) { return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email); }  
function FNGbgKPZ3($mobile) { return strlen(trim($mobile)) == 11 && preg_match("/[13|15|18]{1}[0-9]{9}/", $mobile); }  
function question() { global $_SGLOBAL; include_once(S_ROOT.'./data/data_spam.php'); if($_SGLOBAL['spam']['question']) { $count = count($_SGLOBAL['spam']['question']); $key = $count>1?mt_rand(0, $count-1):0; ssetcookie('seccode', $key); echo $_SGLOBAL['spam']['question'][$key]; } }  
function my_checkupdate() { global $_SGLOBAL, $_SCONFIG; if($_SCONFIG['my_status'] && empty($_SCONFIG['my_closecheckupdate']) && checkperm('admin')) { $sid = $_SCONFIG['my_siteid']; $ts = $_SGLOBAL['timestamp']; $key = md5($sid.$ts.$_SCONFIG['my_sitekey']); echo '<script type="text/javascript" src="http://notice.uchome.manyou.com/notice?sId='.$sid.'&ts='.$ts.'&key='.$key.'" charset="UTF-8"></script>'; } }  
function g_icon($gid) { global $_SGLOBAL; include_once(S_ROOT.'./data/data_usergroup.php'); if(empty($_SGLOBAL['grouptitle'][$gid]['icon'])) { echo ''; } else { echo ' <img src="'.$_SGLOBAL['grouptitle'][$gid]['icon'].'" align="absmiddle"> '; } }  
function g_color($gid) { global $_SGLOBAL; include_once(S_ROOT.'./data/data_usergroup.php'); if(empty($_SGLOBAL['grouptitle'][$gid]['color'])) { echo ''; } else { echo ' style="color:'.$_SGLOBAL['grouptitle'][$gid]['color'].';"'; } }  
function ckfounder($uid) { global $_SC; $founders = empty($_SC['founder'])?array():explode(',', $_SC['founder']); if($uid && $founders) { return in_array($uid, $founders); } else { return false; } }  
function sreaddir($dir, $extarr=array()) { $dirs = array(); if($dh = opendir($dir)) { while (($file = readdir($dh)) !== false) { if(!empty($extarr) && is_array($extarr)) { if(in_array(strtolower(fileext($file)), $extarr)) { $dirs[] = $file; } } else if($file != '.' && $file != '..') { $dirs[] = $file; } } closedir($dh); } return $dirs; }  
function getreward($action, $update=1, $uid=0, $needle='', $setcookie = 1) { global $_SGLOBAL, $_SCOOKIE; $credit = 0; $reward = array( 'credit' => 0, 'experience' => 0 ); $creditlog = array(); @include_once(S_ROOT.'./data/data_creditrule.php'); $rule = $_SGLOBAL['creditrule'][$action]; if($rule['credit'] || $rule['experience']) { $uid = $uid ? intval($uid) : $_SGLOBAL['supe_uid']; if($rule['rewardtype']) {  
$query = $_SGLOBAL['db']->query("\x53\x45\x4c\105CT * FRO\115 ".tname('creditlog')." WHERE uid='$uid' AND rid='$rule[rid]'"); $creditlog = $_SGLOBAL['db']->fetch_array($query); if(empty($creditlog)) { $reward['credit'] = $rule['credit']; $reward['experience'] = $rule['experience']; $setarr = array( 'uid' => $uid, 'rid' => $rule['rid'], 'total' => 1, 'cyclenum' => 1, 'credit' => $rule['credit'], 'experience' => $rule['experience'], 'dateline' => $_SGLOBAL['timestamp'] );  
if($rule['norepeat']) { if($rule['norepeat'] == 1) { $setarr['info'] = $needle; } elseif($rule['norepeat'] == 2) { $setarr['user'] = $needle; } elseif($rule['norepeat'] == 3) { $setarr['app'] = $needle; } } if(in_array($rule['cycletype'], array(2,3))) { $setarr['starttime'] = $_SGLOBAL['timestamp']; } $clid = inserttable('creditlog', $setarr, 1); } else { $newcycle = false; $setarr = array(); $clid = $creditlog['clid']; switch($rule['cycletype']) { case 0:  
break; case 1:  
case 4:  
$sql = 'cyclenum+1'; if($rule['cycletype'] == 1) { $today = sstrtotime(sgmdate('Y-m-d'));  
if($creditlog['dateline'] < $today && $rule['rewardnum']) { $creditlog['cyclenum'] = 0; $sql = 1; $newcycle = true; } } if(empty($rule['rewardnum']) || $creditlog['cyclenum'] < $rule['rewardnum']) {  
if($rule['norepeat']) { $repeat = checkcheating($creditlog, $needle, $rule['norepeat']); if($repeat && !$newcycle) { return $reward; } } $reward['credit'] = $rule['credit']; $reward['experience'] = $rule['experience'];  
$setarr = array( 'cyclenum' => "cyclenum=$sql", 'total' => 'total=total+1', 'dateline' => "dateline='$_SGLOBAL[timestamp]'", 'credit' => "credit='$reward[credit]'", 'experience' => "experience='$reward[experience]'", ); } break; case 2:  
case 3:  
$nextcycle = 0; if($creditlog['starttime']) { if($rule['cycletype'] == 2) {  
$start = sstrtotime(sgmdate('Y-m-d H:00:00', $creditlog['starttime'])); $nextcycle = $start+$rule['cycletime']*3600; } else { $nextcycle = $creditlog['starttime']+$rule['cycletime']*60; } } if($_SGLOBAL['timestamp'] <= $nextcycle && $creditlog['cyclenum'] < $rule['rewardnum']) {  
if($rule['norepeat']) { $repeat = checkcheating($creditlog, $needle, $rule['norepeat']); if($repeat && !$newcycle) { return $reward; } } $reward['experience'] = $rule['experience']; $reward['credit'] = $rule['credit']; $setarr = array( 'cyclenum' => "cyclenum=cyclenum+1", 'total' => 'total=total+1', 'dateline' => "dateline='$_SGLOBAL[timestamp]'", 'credit' => "credit='$reward[credit]'", 'experience' => "experience='$reward[experience]'", ); } elseif($_SGLOBAL['timestamp'] >= $nextcycle) { $newcycle = true; $reward['experience'] = $rule['experience']; $reward['credit'] = $rule['credit']; $setarr = array( 'cyclenum' => "cyclenum=1", 'total' => 'total=total+1', 'dateline' => "dateline='$_SGLOBAL[timestamp]'", 'credit' => "credit='$reward[credit]'", 'starttime' => "starttime='$_SGLOBAL[timestamp]'", 'experience' => "experience='$reward[experience]'", ); } break; }  
if($rule['norepeat'] && $needle) { switch($rule['norepeat']) { case 0: break; case 1:  
$info = empty($creditlog['info'])||$newcycle ? $needle : $creditlog['info'].','.$needle; $setarr['info'] = "`info`='$info'"; break; case 2:  
$user = empty($creditlog['user'])||$newcycle ? $needle : $creditlog['user'].','.$needle; $setarr['user'] = "`user`='$user'"; break; case 3:  
$app = empty($creditlog['app'])||$newcycle ? $needle : $creditlog['app'].','.$needle; $setarr['app'] = "`app`='$app'"; break; } } if($setarr) { $_SGLOBAL['db']->query("UPDATE ".tname('creditlog')." SET ".implode(',', $setarr)." WHERE clid='$creditlog[clid]'"); } } if($setcookie && $uid = $_SGLOBAL['supe_uid']) {  
if($reward['credit'] || $reward['experience']) { $logstr = $action.','.$clid; ssetcookie('reward_log', $logstr); $_SCOOKIE['reward_log'] = $logstr; } } } else {  
$reward['credit'] = "-$rule[credit]"; $reward['experience'] = "-$rule[experience]"; } if($update && ($reward['credit'] || $reward['experience'])) { $setarr = array(); if($reward['credit']) { $setarr['credit'] = "credit=credit+$reward[credit]"; } if($reward['experience']) { $setarr['experience'] = "experience=experience+$reward[experience]"; } $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$uid'"); } } return array('credit'=>abs($reward['credit']), 'experience' => abs($reward['experience'])); }  
function checkcheating($creditlog, $needle, $norepeat) { $repeat = false; switch($norepeat) { case 0: break; case 1:  
$infoarr = explode(',', $creditlog['info']); if(in_array($needle, $infoarr)) { $repeat = true; } break; case 2:  
$userarr = explode(',', $creditlog['user']); if(in_array($needle, $userarr)) { $repeat = true; } break; case 3:  
$apparr = explode(',', $creditlog['app']); if(in_array($needle, $apparr)) { $repeat = true; } break; } return $repeat; }  

function topic_get($topicid) 
{ 
  global $_SGLOBAL; 
  $topic = array(); 
  if($topicid) 
  { 
    $typearr = array('blog','pic','thread','poll','event','share'); 
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." WHERE topicid='$topicid'"); 
    if($topic = $_SGLOBAL['db']->fetch_array($query)) 
    { 
      $topic['pic'] = $topic['pic']?pic_get($topic['pic'], $topic['thumb'], $topic['remote'], 0):''; 
      $topic['joingid'] = empty($topic['joingid'])?array():explode(',', $topic['joingid']); 
      $topic['jointype'] = empty($topic['jointype'])? $typearr:explode(',', $topic['jointype']); 
      $topic['lastpost'] = sgmdate('Y-m-d H:i', $topic['lastpost']); 
      $topic['dateline'] = sgmdate('Y-m-d H:i', $topic['dateline']); 
      $topic['allowjoin'] = $topic['endtime'] && $_SGLOBAL['timestamp']>$topic['endtime']?0:1; 
      $topic['endtime'] = $topic['endtime']?sgmdate('Y-m-d H:i', $topic['endtime']):''; 

      include_once(S_ROOT.'./source/function_bbcode.php'); 
      $topic['message'] = bbcode($topic['message'], 1); $topic['joinurl'] = ''; 
      foreach ($typearr as $value) 
      { 
        if(in_array($value, $topic['jointype'])) 
        { 
          if($value == 'pic') $value = 'upload'; 
          $topic['joinurl'] = "cp.\x70\x68\160?ac=$value&topicid=$topicid";
          break; 
        }
      } 
    } 
  } 
  return $topic; 
}  

function mob_perpage($perpage) { global $_SGLOBAL; $newperpage = isset($_GET['perpage'])?intval($_GET['perpage']):0; if($_SGLOBAL['mobile'] && $newperpage>0 && $newperpage<500) { $perpage = $newperpage; } return $perpage; }  
function ckspacelog() { global $_SGLOBAL; if(empty($_SGLOBAL['supe_uid'])) return false; $query = $_SGLOBAL['db']->query("S\x45\x4c\x45\x43\124 * \106\x52\117\115 ".tname('spacelog')." WHERE uid='$_SGLOBAL[supe_uid]'"); if($value = $_SGLOBAL['db']->fetch_array($query)) { if($value['expiration'] && $value['expiration'] <= $_SGLOBAL['timestamp']) { 
$_SGLOBAL['db']->query("DELETE \x46RO\115 ".tname('spacelog')." WHERE uid='$_SGLOBAL[supe_uid]'"); } $expiration = sgmdate('Y-m-d H:i', $value['expiration']); showmessage('no_authority_expiration'.($value['expiration']?'_date':''), '', 1, array($expiration)); } }  
function wap_seccode(){ return '<img src="do.php?ac=seccode&rand='. mt_rand().'" alt="seccode"/>'; }  
function jTtcgMMUE($content, $out_charset, $in_charset) { global $_SCONFIG,$_SC; if( $out_charset == $in_charset ) { return $content; } else { if(!empty($content) && is_array($content)) { foreach($content as $key => $value) { $content[$key]=jTtcgMMUE($value,$out_charset,$in_charset); } } else { if(!empty($content) && is_string($content) && $in_charset != $out_charset) { $content = siconv($content,$out_charset,$in_charset); } } } return $content; } ; ?>
