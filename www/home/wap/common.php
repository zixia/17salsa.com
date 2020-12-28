<?php    @define('IN_UCHOME', TRUE); @define('qVh0gqGnK', TRUE); @define('agF2gTdKE', "wap"); define('D_BUG', '0'); D_BUG?error_reporting(7):error_reporting(0); set_magic_quotes_runtime(0); error_reporting(7); $_SGLOBAL = $_SCONFIG = $_SBLOCK = $_TPL = $_SCOOKIE = $_SN = $space =$KGn1g4iOB= array();  
define('XnP3g6CaJ', dirname(__FILE__).DIRECTORY_SEPARATOR); define('S_ROOT', str_replace(agF2gTdKE.'/common.php', '', str_replace('\\', '/', __FILE__)));  
include_once(S_ROOT.'./ver.php'); if(!@include_once(S_ROOT.'./config.php')) { header("Lo\143\141\164\x69\x6f\x6e: install/index.\160\x68\160"); 
exit(); } if(empty($_SC['charset_wap'])) { $_SC['charset_wap'] = $_SC['charset']; } include_once(XnP3g6CaJ.'./source/function_common.php');  
$mtime = explode(' ', microtime()); $_SGLOBAL['timestamp'] = $mtime[1]; $_SGLOBAL['supe_starttime'] = $_SGLOBAL['timestamp'] + $mtime[0];  
$magic_quote = get_magic_quotes_gpc(); if(empty($magic_quote)) { $_GET = saddslashes($_GET); $_POST = saddslashes($_POST); }  
if(empty($_SC['siteurl'])) $_SC['siteurl'] = getsiteurl();  
dbconnect();  
if(!@include_once(S_ROOT.'./data/data_config.php')) { include_once(WAP_BASE.'./source/function_cache.php'); config_cache(); include_once(S_ROOT.'./data/data_config.php'); } foreach (array('app', 'userapp', 'ad', 'magic') as $value) { @include_once(S_ROOT.'./data/data_'.$value.'.php'); }  
if(!empty($_GET["\x6d_\163\151d"])) { session_id($_GET["\x6d_\x73\x69\144"]); } session_start(); $prelength = strlen($_SC['cookiepre']); foreach($_COOKIE as $key => $val) { if(substr($key, 0, $prelength) == $_SC['cookiepre']) { $_SCOOKIE[(substr($key, $prelength))] = empty($magic_quote) ? saddslashes($val) : $val; } } $KGn1g4iOB = $_SERVER;  
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) { ob_start('ob_gzhandler'); } else { ob_start(); }  
$_SGLOBAL['supe_uid'] = 0; $_SGLOBAL['supe_username'] = ''; $_SGLOBAL['inajax'] = empty($_GET['inajax'])?0:intval($_GET['inajax']); $_SGLOBAL['ajaxmenuid'] = empty($_GET['ajaxmenuid'])?'':$_GET['ajaxmenuid']; $_SGLOBAL['refer'] = empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER']; $_SGLOBAL['mobile'] = '1';  
if(!empty($_SGLOBAL['refer'])) { $poGfgcg4G = parse_url($_SGLOBAL['refer']); if($poGfgcg4G) { $_SGLOBAL['back_refer'] = basename($poGfgcg4G['path'])."?".$poGfgcg4G['query']; } }  
if(empty($_SCONFIG['login_action'])) $_SCONFIG['login_action'] = md5('login'.md5($_SCONFIG['sitekey'])); if(empty($_SCONFIG['register_action'])) $_SCONFIG['register_action'] = md5('register'.md5($_SCONFIG['sitekey']));  
if(empty($_SCONFIG['template'])) { $_SCONFIG['template'] = 'default'; } if($_SCOOKIE['mytemplate']) { $_SCOOKIE['mytemplate'] = str_replace('.','',trim($_SCOOKIE['mytemplate'])); if(file_exists(XnP3g6CaJ.'./tpl/'.$_SCOOKIE['mytemplate'].'/style.css')) { $_SCONFIG['template'] = $_SCOOKIE['mytemplate']; } else { ssetcookie('mytemplate', '', 365000); } }  
if(!isset($_SERVER['REQUEST_URI'])) { $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF']; if(isset($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING']; } if($_SERVER['REQUEST_URI']) { $temp = urldecode($_SERVER['REQUEST_URI']); if(strexists($temp, '<') || strexists($temp, '"')) { $_GET = shtmlspecialchars($_GET); 
} }  
checkauth(); $_SGLOBAL['uhash'] = md5($_SGLOBAL['supe_uid']."\t".substr($_SGLOBAL['timestamp'], 0, 6));  
getuserapp();  
$_SCONFIG['uc_status'] = 0; $_SGLOBAL['appmenus'] = $_SGLOBAL['appmenu'] = array(); if($_SGLOBAL['app']) { foreach ($_SGLOBAL['app'] as $appid => $value) { if(UC_APPID != $appid) { $_SCONFIG['uc_status'] = 1; } if($value['open']) { if(empty($_SGLOBAL['appmenu'])) { $_SGLOBAL['appmenu'] = $value; } else { $_SGLOBAL['appmenus'][] = $value; } } } } ; ?>
