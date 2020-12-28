<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: client.php 898 2008-12-24 09:22:35Z cnteacher $
*/

if(!defined('UC_API')) {
	exit('Access denied');
}

error_reporting(0);

define('IN_UC', TRUE);
define('UC_CLIENT_VERSION', '1.5.0');
define('UC_CLIENT_RELEASE', '20090121');
define('UC_ROOT', substr(__FILE__, 0, -10));
define('UC_DATADIR', UC_ROOT.'./data/');
define('UC_DATAURL', UC_API.'/data');
define('UC_API_FUNC', UC_CONNECT == 'mysql' ? 'uc_api_mysql' : 'uc_api_post');
$GLOBALS['uc_controls'] = array();

function uc_addslashes($string, $force = 0, $strip = FALSE) 
{
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) 
	{
		if(is_array($string)) 
		{
			foreach($string as $key => $val) 
			{
				$string[$key] = uc_addslashes($val, $force, $strip);
			}
		} 
		else 
		{
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

if(!function_exists('daddslashes')) 
{
	function daddslashes($string, $force = 0)
	{
		return uc_addslashes($string, $force);
	}
}

function uc_stripslashes($string) 
{
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(MAGIC_QUOTES_GPC) {
		return stripslashes($string);
	} else {
		return $string;
	}
}

/**
 *  dfopen 方式取指定的模块和动作的数据
 *
 * @param string $module	请求的模块
 * @param string $action 	请求的动作
 * @param array $arg		参数（会加密的方式传送）
 * @return string
 */
function uc_api_post($module, $action, $arg = array()) 
{
	$s = $sep = '';
	foreach($arg as $k => $v) 
	{
		if(is_array($v)) 
		{
			$s2 = $sep2 = '';
			foreach($v as $k2=>$v2) 
			{
				$s2 .= "$sep2{$k}[$k2]=".urlencode(uc_stripslashes($v2));
				$sep2 = '&';
			}
			$s .= $sep.$s2;
		} 
		else 
		{
			$s .= "$sep$k=".urlencode(uc_stripslashes($v));
		}
		$sep = '&';
	}
	$postdata = uc_api_requestdata($module, $action, $s);

	return uc_fopen2(UC_API.'/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
}

/**
 * 构造发送给用户中心的请求数据
 *
 * @param string $module	请求的模块
 * @param string $action	请求的动作
 * @param string $arg		参数（会加密的方式传送）
 * @param string $extra		附加参数（传送时不加密）
 * @return string
 */
function uc_api_requestdata($module, $action, $arg='', $extra='') 
{
	$input = uc_api_input($arg);
	$post = "m=$module&a=$action&inajax=2&release=".UC_CLIENT_RELEASE."&input=$input&appid=".UC_APPID.$extra;
	return $post;
}


function uc_api_url($module, $action, $arg='', $extra='') 
{
	$url = UC_API.'/index.php?'.uc_api_requestdata($module, $action, $arg, $extra);
	return $url;
}

function uc_api_input($data) 
{
	$s = urlencode(uc_authcode($data.'&agent='.md5($_SERVER['HTTP_USER_AGENT'])."&time=".time(), 'ENCODE', UC_KEY));
	return $s;
}

function uc_api_mysql($model, $action, $args=array()) 
{
	global $uc_controls;
	if(empty($uc_controls[$model])) 
	{
		include_once UC_ROOT.'./lib/db.class.php';
		include_once UC_ROOT.'./model/base.php';
		include_once UC_ROOT."./control/$model.php";
		eval("\$uc_controls['$model'] = new {$model}control();");
	}
	if($action{0} != '_') 
	{
		$args = uc_addslashes($args, 1, TRUE);
		$action = 'on'.$action;
		$uc_controls[$model]->input = $args;
		return $uc_controls[$model]->$action($args);
	} 
	else 
	{
		return '';
	}
}

function uc_serialize($arr, $htmlon = 0) 
{
	include_once UC_ROOT.'./lib/xml.class.php';
	return xml_serialize($arr, $htmlon);
}

function uc_unserialize($s) 
{
	include_once UC_ROOT.'./lib/xml.class.php';
	return xml_unserialize($s);
}

function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) 
{

	$ckey_length = 4;	//note 随机密钥长度 取值 0-32;
				//note 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
				//note 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
				//note 当此值为 0 时，则不产生随机密钥

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) 
	{
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) 
	{
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) 
	{
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') 
	{
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) 
		{
			return substr($result, 26);
		} 
		else 
		{
			return '';
		}
	} 
	else 
	{
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

function uc_fopen2($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) 
{
	$__times__ = isset($_GET['__times__']) ? intval($_GET['__times__']) + 1 : 1;
	if($__times__ > 2) 
	{
		return '';
	}
	$url .= (strpos($url, '?') === FALSE ? '?' : '&')."__times__=$__times__";
		
	return uc_fopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
}

function uc_fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) 
{
	$return = '';
	$matches = parse_url($url);
	!isset($matches['host']) && $matches['host'] = '';
	!isset($matches['path']) && $matches['path'] = '';
	!isset($matches['query']) && $matches['query'] = '';
	!isset($matches['port']) && $matches['port'] = '';
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) 
	{
		
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
	} 
	else 
	{
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
	if(!$fp) 
	{
		return '';
	} 
	else 
	{
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) 
		{
			while (!feof($fp)) 
			{
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) 
				{
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) 
			{
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) 
				{
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function uc_app_ls() 
{
	$return = call_user_func(UC_API_FUNC, 'app', 'ls', array());
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 添加 feed
 *
 * @param string $icon			图标
 * @param string $uid			uid
 * @param string $username		用户名
 * @param string $title_template	标题模板
 * @param array  $title_data		标题内容
 * @param string $body_template		内容模板
 * @param array  $body_data		内容内容
 * @param string $body_general		保留
 * @param string $target_ids		保留
 * @param array $images		图片
 * 	格式为:
 * 		array(
 * 			array('url'=>'http://domain1/1.jpg', 'link'=>'http://domain1'),
 * 			array('url'=>'http://domain2/2.jpg', 'link'=>'http://domain2'),
 * 			array('url'=>'http://domain3/3.jpg', 'link'=>'http://domain3'),
 * 		)
 * 	示例:
 * 		$feed['images'][] = array('url'=>$vthumb1, 'link'=>$vthumb1);
 * 		$feed['images'][] = array('url'=>$vthumb2, 'link'=>$vthumb2);
 * @return int feedid
 */
function uc_feed_add($icon, $uid, $username, $title_template='', $title_data='', $body_template='', $body_data='', $body_general='', $target_ids='', $images = array())
{
	return call_user_func(UC_API_FUNC, 'feed', 'add',
		array(  'icon'=>$icon,
			'appid'=>UC_APPID,
			'uid'=>$uid,
			'username'=>$username,
			'title_template'=>$title_template,
			'title_data'=>$title_data,
			'body_template'=>$body_template,
			'body_data'=>$body_data,
			'body_general'=>$body_general,
			'target_ids'=>$target_ids,
			'image_1'=>$images[0]['url'],
			'image_1_link'=>$images[0]['link'],
			'image_2'=>$images[1]['url'],
			'image_2_link'=>$images[1]['link'],
			'image_3'=>$images[2]['url'],
			'image_3_link'=>$images[2]['link'],
			'image_4'=>$images[3]['url'],
			'image_4_link'=>$images[3]['link']
		)
	);
}

/**
 * 每次取多少条
 *
 * @param int $limit
 * @return array()
 */
function uc_feed_get($limit = 100, $delete = TRUE) {
	$return = call_user_func(UC_API_FUNC, 'feed', 'get', array('limit'=>$limit, 'delete'=>$delete));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

function uc_friend_add($uid, $friendid, $comment='') {
	return call_user_func(UC_API_FUNC, 'friend', 'add', array('uid'=>$uid, 'friendid'=>$friendid, 'comment'=>$comment));
}

function uc_friend_delete($uid, $friendids) {
	return call_user_func(UC_API_FUNC, 'friend', 'delete', array('uid'=>$uid, 'friendids'=>$friendids));
}

function uc_friend_totalnum($uid, $direction = 0) {
	return call_user_func(UC_API_FUNC, 'friend', 'totalnum', array('uid'=>$uid, 'direction'=>$direction));
}

function uc_friend_ls($uid, $page = 1, $pagesize = 10, $totalnum = 10, $direction = 0) {
	$return = call_user_func(UC_API_FUNC, 'friend', 'ls', array('uid'=>$uid, 'page'=>$page, 'pagesize'=>$pagesize, 'totalnum'=>$totalnum, 'direction'=>$direction));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

function uc_user_register($username, $password, $email, $questionid = '', $answer = '') {
	return call_user_func(UC_API_FUNC, 'user', 'register', array('username'=>$username, 'password'=>$password, 'email'=>$email, 'questionid'=>$questionid, 'answer'=>$answer));
}

function uc_user_login($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '') 
{
	$isuid = intval($isuid);
	$return = call_user_func(UC_API_FUNC, 'user', 'login', array('username'=>$username, 'password'=>$password, 'isuid'=>$isuid, 'checkques'=>$checkques, 'questionid'=>$questionid, 'answer'=>$answer));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}


/**
 * 进入同步登录代码
 *
 * @param int $uid		用户ID
 * @return string 		HTML代码
 */
function uc_user_synlogin($uid) 
{
    return  uc_api_post('user', 'synlogin', array('uid'=>$uid));
}

/**
 * 进入同步登出代码
 *
 * @return string 		HTML代码
 */
function uc_user_synlogout() 
{
	$return = uc_api_post('user', 'synlogout', array());
	return $return;
}

/**
 * 编辑用户
 *
 * @param string $username	用户名
 * @param string $oldpw		旧密码
 * @param string $newpw		新密码
 * @param string $email		Email
 * @param int $ignoreoldpw 	是否忽略旧密码, 忽略旧密码, 则不进行旧密码校验.
 * @return int
 	1  : 修改成功
 	0  : 没有任何修改
  	-1 : 旧密码不正确
	-4 : email 格式有误
	-5 : email 不允许注册
	-6 : 该 email 已经被注册
	-7 : 没有做任何修改
	-8 : 受保护的用户，没有权限修改
*/
function uc_user_edit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $questionid = '', $answer = '')
{
	return call_user_func(UC_API_FUNC, 'user', 'edit', array('username'=>$username, 'oldpw'=>$oldpw, 'newpw'=>$newpw, 'email'=>$email, 'ignoreoldpw'=>$ignoreoldpw, 'questionid'=>$questionid, 'answer'=>$answer));
}

/**
 * 删除用户
 *
 * @param string/array $uid	用户的 UID
 * @return int
 	>0 : 成功
 	0 : 失败
 */
function uc_user_delete($uid)
{
	return call_user_func(UC_API_FUNC, 'user', 'delete', array('uid'=>$uid));
}

function uc_user_deleteavatar($uid) 
{
	uc_api_post('user', 'deleteavatar', array('uid'=>$uid));
}

function uc_user_checkname($username) 
{
	return call_user_func(UC_API_FUNC, 'user', 'check_username', array('username'=>$username));
}

function uc_user_checkemail($email) 
{
	return call_user_func(UC_API_FUNC, 'user', 'check_email', array('email'=>$email));
}

function uc_user_addprotected($username, $admin='') 
{
	return call_user_func(UC_API_FUNC, 'user', 'addprotected', array('username'=>$username, 'admin'=>$admin));
}

function uc_user_deleteprotected($username) 
{
	return call_user_func(UC_API_FUNC, 'user', 'deleteprotected', array('username'=>$username));
}

function uc_user_getprotected() 
{
	$return = call_user_func(UC_API_FUNC, 'user', 'getprotected', array('1'=>1));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 取得用户数据
 *
 * @param string $username	用户名
 * @param int $isuid	是否为UID
 * @return array (uid, username, email)
 */
function uc_get_user($username, $isuid=0) 
{
	$return = call_user_func(UC_API_FUNC, 'user', 'get_user', array('username'=>$username, 'isuid'=>$isuid));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

function uc_user_merge($oldusername, $newusername, $uid, $password, $email) 
{
	return call_user_func(UC_API_FUNC, 'user', 'merge', array('oldusername'=>$oldusername, 'newusername'=>$newusername, 'uid'=>$uid, 'password'=>$password, 'email'=>$email));
}

function uc_user_merge_remove($username) 
{
	return call_user_func(UC_API_FUNC, 'user', 'merge_remove', array('username'=>$username));
}

function uc_user_getcredit($appid, $uid, $credit) 
{
	return call_user_func(UC_API_FUNC, 'user', 'getcredit', array('appid'=>$appid, 'uid'=>$uid, 'credit'=>$credit));
}

/**
 * 进入短消息界面
 *
 * @param int $uid	用户ID
 * @param int $newpm	是否直接进入newpm
 */
function uc_pm_location($uid, $newpm = 0) 
{
	$apiurl = uc_api_url('pm_client', 'ls', "uid=$uid", ($newpm ? '&folder=newbox' : ''));
	@header("Expires: 0");
	@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("location: $apiurl");
}

/**
 * 检查新短消息
 *
 * @param  int $uid	用户ID
 * @return int	 	是否存在新短消息
 * 	1	是
 * 	0	否
 */
function uc_pm_checknew($uid, $more = 0) 
{
	$return = call_user_func(UC_API_FUNC, 'pm', 'check_newpm', array('uid'=>$uid, 'more'=>$more));

	return (!$more || UC_CONNECT == 'mysql') ? $return : uc_unserialize($return);
}

/**
 * 发送短消息
 *
 * @param int $fromuid		发件人uid 0 为系统消息
 * @param mix $msgto		收件人 uid/username 多个逗号分割
 * @param mix $subject		标题
 * @param mix $message		内容
 * @param int $instantly	立即发送 1 立即发送(默认)  0 进入短消息发送界面
 * @param int $replypid		回复的消息Id
 * @param int $isusername	0 = $msgto 为 uid、1 = $msgto 为 username
 * @return
 * 	>1	发送成功的人数
 * 	0	收件人不存在
 */
function uc_pm_send($fromuid, $msgto, $subject, $message, $instantly = 1, $replypmid = 0, $isusername = 0) 
{
	if($instantly) 
	{
		$replypmid = @is_numeric($replypmid) ? $replypmid : 0;
		return call_user_func(UC_API_FUNC, 'pm', 'sendpm', array('fromuid'=>$fromuid, 'msgto'=>$msgto, 'subject'=>$subject, 'message'=>$message, 'replypmid'=>$replypmid, 'isusername'=>$isusername));
	} 
	else 
	{
		$fromuid = intval($fromuid);
		$subject = urlencode($subject);
		$msgto = urlencode($msgto);
		$message = urlencode($message);
		$replypmid = @is_numeric($replypmid) ? $replypmid : 0;
		$replyadd = $replypmid ? "&pmid=$replypmid&do=reply" : '';
		$apiurl = uc_api_url('pm_client', 'send', "uid=$fromuid", "&msgto=$msgto&subject=$subject&message=$message$replyadd");
		@header("Expires: 0");
		@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("location: ".$apiurl);
	}
}

/**
 * 删除短消息
 *
 * @param int $uid		用户Id
 * @param string $folder	打开的目录 inbox=收件箱，outbox=发件箱
 * @param array	$pmids		要删除的消息Id数组
 * @return
 * 	>0 成功
 * 	<=0 失败
 */
function uc_pm_delete($uid, $folder, $pmids) 
{
	return call_user_func(UC_API_FUNC, 'pm', 'delete', array('uid'=>$uid, 'folder'=>$folder, 'pmids'=>$pmids));
}

function uc_pm_deleteuser($uid, $touids) 
{
	return call_user_func(UC_API_FUNC, 'pm', 'deleteuser', array('uid'=>$uid, 'touids'=>$touids));
}

function uc_pm_readstatus($uid, $uids, $pmids = array(), $status = 0) 
{
	return call_user_func(UC_API_FUNC, 'pm', 'readstatus', array('uid'=>$uid, 'uids'=>$uids, 'pmids'=>$pmids, 'status'=>$status));
}

/**
 * 获取短消息列表
 *
 * @param int $uid		用户Id
 * @param int $page 		当前页
 * @param int $pagesize 	每页最大条目数
 * @param string $folder	打开的目录 newbox=未读消息，inbox=收件箱，outbox=发件箱
 * @param string $filter	过滤方式 newpm=未读消息，systempm=系统消息，announcepm=公共消息
 				$folder		$filter
 				--------------------------
 				newbox
 				inbox		newpm
 						systempm
 						announcepm
 				outbox		newpm
 * @param string $msglen 	截取的消息文字长度
 * @return array('count' => 消息总数, 'data' => 短消息数据)
 */
function uc_pm_list($uid, $page = 1, $pagesize = 10, $folder = 'inbox', $filter = 'newpm', $msglen = 0) 
{
	$uid = intval($uid);
	$page = intval($page);
	$pagesize = intval($pagesize);
	$return = call_user_func(UC_API_FUNC, 'pm', 'ls', array('uid'=>$uid, 'page'=>$page, 'pagesize'=>$pagesize, 'folder'=>$folder, 'filter'=>$filter, 'msglen'=>$msglen));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 忽略未读消息提示
 *
 * @param int $uid		用户Id
 */
function uc_pm_ignore($uid) 
{
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'ignore', array('uid'=>$uid));
}

/**
 * 获取短消息内容
 *
 * @param int $uid		用户Id
 * @param int $pmid		消息Id
 * @return array() 短消息内容数组
 */
function uc_pm_view($uid, $pmid, $touid = 0, $daterange = 1) 
{
	$uid = intval($uid);
	$touid = intval($touid);
	$pmid = @is_numeric($pmid) ? $pmid : 0;
	$return = call_user_func(UC_API_FUNC, 'pm', 'view', array('uid'=>$uid, 'pmid'=>$pmid, 'touid'=>$touid, 'daterange'=>$daterange));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 获取单条短消息内容
 *
 * @param int $uid		用户Id
 * @param int $pmid		消息Id
 * @param int $type		0 = 获取指定单条消息
 				1 = 获取指定用户发的最后单条消息
 				2 = 获取指定用户收的最后单条消息
 * @return array() 短消息内容数组
 */
function uc_pm_viewnode($uid, $type = 0, $pmid = 0) 
{
	$uid = intval($uid);
	$pmid = @is_numeric($pmid) ? $pmid : 0;
	$return = call_user_func(UC_API_FUNC, 'pm', 'viewnode', array('uid'=>$uid, 'pmid'=>$pmid, 'type'=>$type));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 获取黑名单
 *
 * @param int $uid		用户Id
 * @return string 黑名单内容
 */
function uc_pm_blackls_get($uid) 
{
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_get', array('uid'=>$uid));
}

/**
 * 设置黑名单
 *
 * @param int $uid		用户Id
 * @param int $blackls		黑名单内容
 */
function uc_pm_blackls_set($uid, $blackls) 
{
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_set', array('uid'=>$uid, 'blackls'=>$blackls));
}

function uc_pm_blackls_add($uid, $username) 
{
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_add', array('uid'=>$uid, 'username'=>$username));
}

function uc_pm_blackls_delete($uid, $username) 
{
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_delete', array('uid'=>$uid, 'username'=>$username));
}

function uc_domain_ls() 
{
	$return = call_user_func(UC_API_FUNC, 'domain', 'ls', array('1'=>1));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 积分兑换请求
 *
 * @param int $uid		用户ID
 * @param int $from		原积分
 * @param int $to		目标积分
 * @param int $toappid		目标应用ID
 * @param int $amount		积分数额
 * @return
 *  	1  : 成功
 *	0  : 失败
 */
function uc_credit_exchange_request($uid, $from, $to, $toappid, $amount) 
{
	$uid = intval($uid);
	$from = intval($from);
	$toappid = intval($toappid);
	$to = intval($to);
	$amount = intval($amount);
	return uc_api_post('credit', 'request', array('uid'=>$uid, 'from'=>$from, 'to'=>$to, 'toappid'=>$toappid, 'amount'=>$amount));
}

/**
 * 返回指定的相关TAG数据
 *
 * @param string $tagname	TAG名称
 * @param int $totalnum		返回数据的条目数
 * @return array() 序列化过的数组，数组内容为当前或其他应用的相关TAG数据
 */
function uc_tag_get($tagname, $nums = 0) {
	$return = call_user_func(UC_API_FUNC, 'tag', 'gettag', array('tagname'=>$tagname, 'nums'=>$nums));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 修改头像
 *
 * @param int $uid		用户ID
 * @return string
 */
function uc_avatar($uid, $type = 'virtual', $returnhtml = 1) 
{
	$uid = intval($uid);
	$uc_input = uc_api_input("uid=$uid");
	$uc_avatarflash = UC_API.'/images/camera.swf?inajax=1&appid='.UC_APPID.'&input='.$uc_input.'&agent='.md5($_SERVER['HTTP_USER_AGENT']).'&ucapi='.urlencode(UC_API).'&avatartype='.$type;
	if($returnhtml) 
	{
		return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="447" height="477" id="mycamera" align="middle">
			<param name="allowScriptAccess" value="always" />
			<param name="scale" value="exactfit" />
			<param name="wmode" value="transparent" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<param name="movie" value="'.$uc_avatarflash.'" />
			<param name="menu" value="false" />
			<embed src="'.$uc_avatarflash.'" quality="high" bgcolor="#ffffff" width="447" height="477" name="mycamera" align="middle" allowScriptAccess="always" allowFullScreen="false" scale="exactfit"  wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>';
	} 
	else 
	{
		return array(
			'width', '447',
			'height', '477',
			'scale', 'exactfit',
			'src', $uc_avatarflash,
			'id', 'mycamera',
			'name', 'mycamera',
			'quality','high',
			'bgcolor','#ffffff',
			'wmode','transparent',
			'menu', 'false',
			'swLiveConnect', 'true',
			'allowScriptAccess', 'always'
		);
	}
}

function uc_mail_queue($uids, $emails, $subject, $message, $frommail = '', $charset = 'gbk', $htmlon = FALSE, $level = 1)
{
	return call_user_func(UC_API_FUNC, 'mail', 'add', array('uids' => $uids, 'emails' => $emails, 'subject' => $subject, 'message' => $message, 'frommail' => $frommail, 'charset' => $charset, 'htmlon' => $htmlon, 'level' => $level));
}

function uc_check_avatar($uid, $size = 'middle', $type = 'virtual') 
{
	$url = UC_API."/avatar.php?uid=$uid&size=$size&type=$type&check_file_exists=1";
	$res = @file_get_contents($url);
	if($res == 1) 
	{
		return 1;
	} 
	else 
	{
		return 0;
	}
}

function uc_check_version() 
{
	$return = uc_api_post('version', 'check', array());
	$data = uc_unserialize($return);
	return is_array($data) ? $data : $return;
}

?>