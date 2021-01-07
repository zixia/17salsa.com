<?php

/**
 * ECMALL: 与uc的接口
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: member.php 1704 2008-03-27 05:25:18Z zhaoxiongfei $
 */


define('ROOT_PATH', substr(dirname(__FILE__), 0, -4));
define('IN_ECM', TRUE);

define('UC_CLIENT_VERSION', '1.5.0');
define('UC_CLIENT_RELEASE', '20081212');

define('API_DELETEUSER', 1);
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
define('API_UPDATECREDITSETTINGS', 1);

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');


//added by zixia 2008-12-21 to fix the next line's warning
//define('CHARSET', substr(LANG, strpos(LANG, '-') + 1)); // 定义字符集常量
define('CHARSET', 'utf-8');

define('IS_AJAX', (!empty($_SERVER['HTTP_AJAX_REQUEST'])) || (!empty($_REQUEST['ajax']))); // 定义是否为ajax请求

require(ROOT_PATH. '/includes/models/mod.base.php');
require(ROOT_PATH. '/includes/manager/mng.base.php');
require(ROOT_PATH. '/includes/lib.common.php'); // 包含工具函数库
require(ROOT_PATH. '/includes/lib.time.php'); // 包含时间函数库
require(ROOT_PATH. '/includes/lib.insert.php'); // 包含动态内容函数库
require(ROOT_PATH. '/includes/inc.constant.php'); // 包含常量文件
require(ROOT_PATH. '/data/inc.config.php'); // 包含配置文件

if(!defined('IN_UC')) {
	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

	$code = @$_GET['code'];
	parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = _stripslashes($get);
	}

	$timestamp = time();
	if($timestamp - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	if(empty($get)) {
		exit('Invalid Request');
	}
	$action = $get['action'];
    $get['id'] = $_GET['name'];

	require_once ROOT_PATH . '/uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
		$GLOBALS['db'] =& db();
		$uc_note = new uc_note();
		exit($uc_note->$get['action']($get, $post));
	} else {
		exit(API_RETURN_FAILED);
	}

} else {
	/*$uc_note = new uc_note('../', '../config.inc.php');
	$uc_note->deleteuser('3');*/

	$GLOBALS['db'] =& db();
}

class uc_note {

	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once ROOT_PATH . '/uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) {
       if (!API_DELETEUSER)
       {
            return API_RETURN_FORBIDDEN;
       }

        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $get['ids'] = str_replace("'", '', $get['ids']);
        $uids = explode(',', $get['ids']);
        foreach ($uids as $uid)
        {
            $user_mod = new User($uid);
            $user_mod->drop();
        }
        return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
	    if (!API_RENAMEUSER)
	    {
	        return API_RETURN_FORBIDDEN;
	    }

        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $uid = $get['uid'];
        $usernamenew = $get['newusername'];
        $user_mod = new User($uid);
        $user_mod->re_user_name($usernamenew);

        return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
	    if (!API_GETTAG)
	    {
	        return API_RETURN_FORBIDDEN;
	    }

        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $name = trim($get['id']);
        if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
            return API_RETURN_FAILED;
        }
        $goods_mng = new GoodsManager();
        $goods_list = $goods_mng->get_list(1, array('tag_words' => $name), 10);
        $res_list = array();
        if (is_array($goods_list['data']))
        {
            foreach ($goods_list['data'] as $val)
            {
                $res_list[] = array(
                        'goods_name'    => $val['goods_name'],
                        'uid'           => $val['store_id'],
                        'username'      => $val['store_name'],
                        'dateline'      => $val['add_time'],
                        'url'           => site_url() . '/index.php?app=goods&id=' . $val['goods_id'],
                        'image'         => site_url() . '/image.php?file_id=' . $val['default_image'] . '&hash_path=' . md5(ECM_KEY . $val['default_image'] . 80 . 80) . '&width=80&height=80',
                        'goods_price'   => $val['store_price'],
                        );
            }
        }

        $return = array($name, $res_list);

	    return $this->_serialize($return, 1);
	}

	function synlogin($get, $post) {
	    if (!API_SYNLOGIN)
	    {
	        return API_RETURN_FORBIDDEN;
	    }
        $uid = $get['uid'];
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $user_mod = new User($uid);
        $user_info = $user_mod->get_info();
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        ecm_setcookie('ECM_USERNAME', $user_info['user_name']); //记录登录用户名
        ecm_setcookie('ECM_AUTH', md5($user_info['user_id'] . ECM_KEY . $user_info['user_name'])); //记录验证串
	}

	function synlogout($get, $post) {
	    if(!API_SYNLOGOUT) {
		    return API_RETURN_FORBIDDEN;
	    }
        //note 同步登出 API 接口
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        ecm_setcookie('ECM_ID', '');
        ecm_setcookie('ECM_AUTH', '');
	}

	function updatepw($get, $post) {
	    if(!API_UPDATEPW) {
		    return API_RETURN_FORBIDDEN;
	    }
        include_once(ROOT_PATH . '/includes/manger/mng.user.php');
        $user_name = $get['username'];
        $user_mng = new UserManager();
        if ($user_id = $user_mng->get_id_by_name($user_name))
        {
            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            $user_mod = new User($user_id);
            $user_info['password'] = $user_mod->generate_password();
            $user_mod->update($user_info);
        }

	    return API_RETURN_SUCCEED;
	}

    /*
	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
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
	*/

	function updatehosts($get, $post) {
	    if(!API_UPDATEHOSTS) {
		    return API_RETURN_FORBIDDEN;
	    }
		$cachefile = ROOT_PATH . '/uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}
        $UC_API = $post['UC_API'];
        unset($post['UC_API']);
        $cachefile = ROOT_PATH .'/uc_client/data/cache/apps.php';
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);
        if (is_writeable(ROOT_PATH . '/data/inc.config.php')) {
            foreach ($post as $appdata)
            {
                if ($appdata['appid'] == UC_APPID)
                {
                    $fp = fopen(ROOT_PATH . '/data/inc.config.php', 'r');
                    $configfile = fread($fp, filesize(ROOT_PATH . '/data/inc.config.php'));
                    $configfile = trim($configfile);
                    $configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
                    fclose($fp);
                    $configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
                    $configfile = preg_replace("/define\('UC_IP',\s*'.*?'\);/i", "define('UC_IP', '$appdata[ip]');", $configfile);

                    if ($fp = @fopen(ROOT_PATH . '/data/inc.config.php', 'w'))
                    {
                        @fwrite($fp, trim($configfile));
                        @fclose($fp);
                    }
                }
            }
        }

		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}
        $cachefile = ROOT_PATH .'/uc_client/data/cache/settings.php';
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);

		return API_RETURN_SUCCEED;
	}
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
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
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

?>
