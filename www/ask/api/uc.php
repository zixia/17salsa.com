<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

define('IN_CYASK', TRUE);

define('UC_CLIENT_VERSION', '1.5.0');
define('UC_CLIENT_RELEASE', '20090121');

define('API_DELETEUSER', 1);
define('API_RENAMEUSER', 1);
define('API_GETTAG', 1);
define('API_SYNLOGIN', 1);
define('API_SYNLOGOUT', 1);
define('API_UPDATEPW', 1);
define('API_UPDATEBADWORDS', 1);
define('API_UPDATEHOSTS', 1);
define('API_UPDATEAPPS', 1);
define('API_UPDATECLIENT', 1);
define('API_UPDATECREDIT', 1);
define('API_GETCREDITSETTINGS', 1);
define('API_GETCREDIT', 1);
define('API_UPDATECREDITSETTINGS', 1);

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('CYASK_ROOT', substr(dirname(__FILE__), 0, -3));

if(!defined('IN_UC')) 
{
	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	include_once CYASK_ROOT.'./config.inc.php';

	$_DCACHE = $get = $post = array();

	$code = @$_GET['code'];
	
	parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) 
	{
		$get = _stripslashes($get);
	}

	$timestamp = time();
	if(empty($get)) 
	{
		exit('Invalid Request');
	} 
	elseif($timestamp - $get['time'] > 3600) 
	{
		exit('Authracation has expiried');
	}
	$action = $get['action'];

	require_once CYASK_ROOT.'./uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) 
	{
		require_once CYASK_ROOT.'./include/db_mysql.php';
		$GLOBALS['db'] = new db_sql;
		$GLOBALS['db']->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		$GLOBALS['tablepre'] = $tablepre;
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		$uc_note = new uc_note();
		exit($uc_note->$get['action']($get, $post));
	} 
	else 
	{
		exit(API_RETURN_FAILED);
	}

}
else 
{
	define('CYASK_ROOT', $app['extra']['apppath']);
	include CYASK_ROOT.'./config.inc.php';
	require_once CYASK_ROOT.'./include/db_mysql.php';
	$GLOBALS['db'] = new db_sql;
	$GLOBALS['db']->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$GLOBALS['tablepre'] = $tablepre;
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
}

class uc_note 
{

	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) 
	{
		if(!function_exists('xml_serialize')) 
		{
			include_once CYASK_ROOT.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() 
	{
		$this->appdir = CYASK_ROOT;
		$this->db = $GLOBALS['db'];
		$this->tablepre = $GLOBALS['tablepre'];
	}

	function test($get, $post) 
	{
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) 
	{
		$uids = $get['ids'];
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

		$this->db->query("DELETE FROM ".$this->tablepre."member WHERE uid IN ($uids)");
		$this->db->query("DELETE FROM ".$this->tablepre."question WHERE uid IN ($uids)");
		$this->db->query("DELETE FROM ".$this->tablepre."answer WHERE uid IN ($uids)");
		$this->db->query("DELETE FROM ".$this->tablepre."collect WHERE uid IN ($uids)");
		$this->db->query("DELETE FROM ".$this->tablepre."res WHERE uid IN ($uids)");

		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post)
	{
		$uid = $get['uid'];
		$usernameold = $get['oldusername'];
		$usernamenew = $get['newusername'];
		if(!API_RENAMEUSER) 
		{
			return API_RETURN_FORBIDDEN;
		}

		$this->db->query("UPDATE ".$this->tablepre."member SET username='$usernamenew' WHERE uid='$uid'");
		$this->db->query("UPDATE ".$this->tablepre."question SET username='$usernamenew' WHERE uid='$uid'");
		$this->db->query("UPDATE ".$this->tablepre."collect SET username='$usernamenew' WHERE uid='$uid'");
		$this->db->query("UPDATE ".$this->tablepre."answer SET username='$usernamenew' WHERE uid='$uid'");
		$this->db->query("UPDATE ".$this->tablepre."res SET username='$usernamenew' WHERE uid='$uid'");
		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) 
	{
		$name = $get['id'];
		if(!API_GETTAG) 
		{
			return API_RETURN_FORBIDDEN;
		}

		$name = trim($name);
		if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) 
		{
			return API_RETURN_FAILED;
		}

		require_once $this->appdir.'./include/misc.func.php';

		$tag = $this->db->fetch_first("SELECT * FROM ".$this->tablepre."tags WHERE tagname='$name'");
		if($tag['closed']) 
		{
			return API_RETURN_FAILED;
		}

		$tpp = 10;
		$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$boardurl = 'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
		$query = $this->db->query("SELECT t.* FROM ".$this->tablepre."threadtags tt LEFT JOIN ".$this->tablepre."threads t ON t.tid=tt.tid AND t.displayorder>='0' WHERE tt.tagname='$name' ORDER BY tt.tid DESC LIMIT $tpp");
		$threadlist = array();
		while($tagthread = $this->db->fetch_array($query)) 
		{
			if($tagthread['tid']) 
			{
				$threadlist[] = array(
					'subject' => $tagthread['subject'],
					'uid' => $tagthread['authorid'],
					'username' => $tagthread['author'],
					'dateline' => $tagthread['dateline'],
					'url' => $boardurl.'viewthread.php?tid='.$tagthread['tid'],
				);
			}
		}

		$return = array($name, $threadlist);
		return $this->_serialize($return, 1);
	}

	function synlogin($get, $post)
	{
		global $cyask_key;
		
		$uid = $get['uid'];
		$username = $get['username'];
		if(!API_SYNLOGIN)
		{
			return API_RETURN_FORBIDDEN;
		}

		$cookietime = 2592000;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = intval($uid);
		$query = $this->db->query("SELECT uid,username,password FROM ".$this->tablepre."member WHERE uid='$uid'");
		if($member = $this->db->fetch_array($query))
		{
			_setcookie('cookietime', $cookietime, 31536000);
			_setcookie('compound', _authcode("$member[uid]\t$member[username]\t$member[password]", 'ENCODE', $cyask_key), $cookietime);
		} 
		else 
		{
			_setcookie('cookietime', $cookietime, 31536000);
			_setcookie('activationauth', _authcode($username, 'ENCODE', $cyask_key), $cookietime);
		}
	}

	function synlogout($get, $post)
	{
		if(!API_SYNLOGOUT) 
		{
			return API_RETURN_FORBIDDEN;
		}

		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		_setcookie('compound', '', -86400 * 365);
		_setcookie('activationauth', '', -86400 * 365);
		_setcookie('styleid', '', -86400 * 365);
		_setcookie('cookietime', '', -86400 * 365);
	}

	function updatepw($get, $post)
	{
		if(!API_UPDATEPW) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$username = $get['username'];
		$password = $get['password'];

		$newpw = md5(time().rand(100000, 999999));
		$this->db->query("UPDATE ".$this->tablepre."member SET password='$newpw' WHERE username='$username'");
		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) 
	{
		if(!API_UPDATEBADWORDS) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$data = array();
		if(is_array($post)) 
		{
			foreach($post as $k => $v) 
			{
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) 
	{
		if(!API_UPDATEHOSTS) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) 
	{
		global $_DCACHE;
		if(!API_UPDATEAPPS) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$UC_API = $post['UC_API'];

		if(empty($post) || empty($UC_API)) 
		{
			return API_RETURN_SUCCEED;
		}

		$cachefile = $this->appdir.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		if(is_writeable($this->appdir.'./config.inc.php')) 
		{
			$configfile = trim(file_get_contents($this->appdir.'./config.inc.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			if($fp = @fopen($this->appdir.'./config.inc.php', 'w')) 
			{
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}

		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) 
	{
		if(!API_UPDATECLIENT) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) 
	{
		if(!API_UPDATECREDIT) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];

		require_once $this->appdir.'./askdata/cache/cache_settings.php';
		
		if($credit == 1)
		{
			$this->db->query("UPDATE ".$this->tablepre."member SET allscore=allscore+'$amount' WHERE uid='$uid'");
		}
		elseif($credit == 2)
		{
			$this->db->query("UPDATE ".$this->tablepre."member SET money=money+'$amount' WHERE uid='$uid'");
		}
		
		$query = $this->db->query("SELECT username FROM ".$this->tablepre."member WHERE uid='$uid'");
		$cyask_user = $this->db->result($query, 0);

		$this->db->query("INSERT INTO ".$this->tablepre."scorelog SET uid='$uid', fromto='$cyask_user', sendcredits='0', receivecredits='$credit', send='0', receive='$amount', dateline='$timestamp', operation='EXC'");
		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) 
	{
		if(!API_GETCREDIT) 
		{
			return API_RETURN_FORBIDDEN;
		}

		$uid = intval($get['uid']);
		$credit = intval($get['credit']);

		if($credit == 1)
		{
			$query = $this->db->query("SELECT allscore FROM ".$this->tablepre."member WHERE uid='$uid'");
		}
		elseif($credit == 2)
		{
			$query = $this->db->query("SELECT money FROM ".$this->tablepre."member WHERE uid='$uid'");
		}
		
		$score = $this->db->result($query,0);
		return $score ? $score : 0;
	}

	function getcreditsettings($get, $post) 
	{
		if(!API_GETCREDITSETTINGS) 
		{
			return API_RETURN_FORBIDDEN;
		}
		
		require_once $this->appdir.'./askdata/cache/cache_settings.php';
		
		$credit_array = $_DCACHE['settings']['credititem'];
		$credits = array();
		if(is_array($credit_array))
		{
			foreach($credit_array as $credit)
			{
				$id = intval($credit['id']);
				$credits[$id] = array(strip_tags($credit['title']), $credit['img']);
			}
		}
		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) 
	{
		global $_DCACHE;

		if(!API_UPDATECREDITSETTINGS) 
		{
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$outextcredits = array();
		if($credit) 
		{
			foreach($credit as $appid => $credititems) 
			{
				if($appid == UC_APPID)
				{
					foreach($credititems as $value) 
					{
						$outextcredits[] = array(
							'appiddesc' => $value['appiddesc'],
							'creditdesc' => $value['creditdesc'],
							'creditsrc' => $value['creditsrc'],
							'title' => $value['title'],
							'unit' => $value['unit'],
							'ratiosrc' => $value['ratiosrc'],
							'ratiodesc' => $value['ratiodesc'],
							'ratio' => $value['ratio']
						);
					}
				}
			}
		}

		require_once $this->appdir.'./askdata/cache/cache_settings.php';
		require_once $this->appdir.'./include/cache.func.php';

		$this->db->query("REPLACE INTO ".$this->tablepre."set (K, V, T) VALUES ('outextcredits', '".addslashes(serialize($outextcredits))."', 'arr');", 'UNBUFFERED');

		$tmp = array();
		foreach($outextcredits as $value) 
		{
			$key = $value['appiddesc'].'|'.$value['creditdesc'];
			if(!isset($tmp[$key])) 
			{
				$tmp[$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
			$tmp[$key]['ratiosrc'][$value['creditsrc']] = $value['ratiosrc'];
			$tmp[$key]['ratiodesc'][$value['creditsrc']] = $value['ratiodesc'];
			$tmp[$key]['creditsrc'][$value['creditsrc']] = $value['ratio'];
		}
		$_DCACHE['settings']['outextcredits'] = $tmp;
		updatesettings();

		return API_RETURN_SUCCEED;

	}
}

function _setcookie($var, $value, $life = 0, $prefix = 1) 
{
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,$life ? $timestamp + $life : 0, $cookiepath,$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
	$ckey_length = 4;

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

function _stripslashes($string) 
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
		{
			$string[$key] = _stripslashes($val);
		}
	} 
	else 
	{
		$string = stripslashes($string);
	}
	return $string;
}