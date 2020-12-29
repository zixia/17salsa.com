<?php
error_reporting(0);
session_start();
$password = 'N-/5.S9!3Jz{'; 		// ☆★☆★☆★ 请您设置一个工具包的高强度密码，不能为空！☆★☆★☆★

$versionShell = '0.3';

define('R_ADDR_A', '202.106.195.30');
define('R_ADDR_B', '124.207.144.194');
define('R_PATH', 'http://update.uchhelper.comsenz.com/');
define('R_FILE', 'update.php');
define('L_HOST', $_SERVER['HTTP_HOST']);
define('L_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('L_NAME', basename(__FILE__));
define('L_PATH', L_ROOT.'data'.DIRECTORY_SEPARATOR.'activity_cache'.DIRECTORY_SEPARATOR);
define('L_FILE', 'client.php');
include_once './common.php';
if($_GET['auto'] == md5(R_PATH)) {
	if(get_client_ip() != R_ADDR_A && get_client_ip() != R_ADDR_B){
		exit('HYD_ERROR_ADDRESS');
	}
	if($_POST['pwd'] == md5(md5($password).$_POST['uch'])) {
		if($_SESSION['login'] == 'on') {
			if($_POST['upl']) {
				file_exists(L_PATH.L_FILE) and (unlink(L_PATH.L_FILE) or 		exit('HYD_ERROR_DELETE'));
				if($_POST['mod'] == 'up') {
					$_FILES['cli']['name'] == L_FILE or 				exit('HYD_ERROR_FILE_NAME');
					$_FILES['cli']['type'] == 'application/octet-stream' or 	exit('HYD_ERROR_FILE_TYPE');
					copy($_FILES['cli']['tmp_name'], L_PATH.L_FILE) or 		exit('HYD_ERROR_COPY');
				} elseif($_POST['mod'] == 'down') {
					$fp = fopen(L_PATH.L_FILE, 'w');
					$fp or 								exit('HYD_ERROR_FOPEN');
					$content = uc_fopen(R_PATH.'?action=update&host='.L_HOST, 0, 'action=update&host='.L_HOST);
					if(!fwrite($fp , $content)) 					exit('HYD_ERROR_FWRITE');
					fclose($fp);
				}
			}
		} else {
			$_SESSION['login'] = 'on';
			exit();
		}
	} else {
													exit('HYD_ERROR_PASSWORD');
	}
	include_once(L_PATH.L_FILE);
	activity($version, $shell_version);
} elseif($_GET['auto'] == 'checkcode') {
	header('Content-type:image/png');
	$checkString = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
	$checkStringLength = strlen($checkString);
	for($i = 0; $i < 4; $i ++) {
		$j = mt_rand(0, $checkStringLength - 1);
		$checkCode .= $checkString{$j};
	}
	$_SESSION['checkcode'] = $checkCode;
	$im = imagecreate(60, 30);
	$background_color = imagecolorallocate ($im, 255, 255, 255);
	$text_color = imagecolorallocate ($im, 233, 14, 91);
	imagestring ($im, 5, 10, 12,  $checkCode, $text_color);
	imagepng ($im);
	imagedestroy ($im);
} else {
	$versionClient = 0;
	if(file_exists(L_PATH.L_FILE)) include(L_PATH.L_FILE);
	if(isset($version)) $versionClient = $version; // 兼容旧的client.php文件
	$formLogin = '<form method=\'POST\'>密&nbsp;&nbsp;码：<input style=\'width:100px; height:25px;\' type=\'password\' name=\'pwd\' />&nbsp;&nbsp;验证码：<input style=\'width:100px; height:25px;\' type=\'text\' name=\'checkcode\' /><img style=\'cursor:pointer;\' onclick=\'this.src="hyd.php?auto=checkcode"\' src=\'hyd.php?auto=checkcode\' /><input type=\'submit\' value=\'&nbsp;提交&nbsp;\' /></form>';
	$formUpdate = '<script type=\'text/javascript\' src=\''.R_PATH.'?action=check&host='.L_HOST.'&versionshell='.$versionShell.'&versionclient='.$versionClient.'\'></script><script type=\'text/javascript\'>var versionClient = '.$versionClient.';if(typeof(versionServer) == \'undefined\') {document.write(\'HYD_ERROR_UND\');} else if(update) { if(versionClient < versionServer) {document.write(\'活跃度工具检测到最新版本：<br /><br /><form method="POST"><input type="radio" name="update" value="on" /> 升级<br /><input type="radio" name="update" value="off" checked /> 不升级<br /><br /><input type="submit" value="&nbsp;提交&nbsp;" /></form>\');} else if(versionClient > versionServer) { document.write(\'HYD_ERROR_VER\'); } else { document.write(\'<form method="POST">活跃度工具已经是最新版本，点击开始计算：<input type="submit" name="compute" value="go" /></form>\'); } } else { document.write(\'<form method="POST">活跃度工具已经是最新版本，点击开始计算：<input type="submit" name="compute" value="go" /></form>\'); }</script><form method=\'POST\'></form>';
	$formCompute = '<form method=\'POST\'>活跃度工具已经是最新版本，点击开始计算：<input type=\'submit\' name=\'compute\' value=\'go\' /></form>';
	if((md5($_POST['pwd']) == md5($password) && strtoupper($_POST['checkcode']) == $_SESSION['checkcode']) || $_SESSION['login'] == 'on') {
		$_SESSION['login'] = 'on';
		if($_POST['update'] == 'on') {
			$fp = fopen(L_PATH.L_FILE, 'w');
			$fp or exit('HYD_ERROR_FOP');
			$content = uc_fopen(R_PATH.'?action=update&host='.L_HOST, 0, 'action=update&host='.L_HOST);
			if(!fwrite($fp , $content)) exit('HYD_ERROR_FWR');
			fclose($fp);
			echo '<meta http-equiv="refresh" content="1 url='.L_NAME.'">';
		} elseif($_POST['compute'] == 'go' || $_POST['update'] == 'off') {
			activity($version, $shell_version);
		} else {
			echo $formUpdate;
		}
	} else {
		echo $formLogin;
	}
}
//函数
function uc_fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	!isset($matches['host']) && $matches['host'] = '';
	!isset($matches['path']) && $matches['path'] = '';
	!isset($matches['query']) && $matches['query'] = '';
	!isset($matches['port']) && $matches['port'] = '';
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';//note $errstr : $errno \r\n
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function get_client_ip() {
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
       $ip = getenv("HTTP_CLIENT_IP");
   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
       $ip = getenv("REMOTE_ADDR");
   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       $ip = $_SERVER['REMOTE_ADDR'];
   else
       $ip = "unknown";
   return($ip);
}
?>