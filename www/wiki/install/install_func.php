<?php
function gd_version() {
	if (function_exists('gd_info')) {
		$GDArray = gd_info();
		if ($GDArray['GD Version']) {
			$gd_version_number = $GDArray['GD Version'];
		} else {
			$gd_version_number = "Off";
		}
		unset ($GDArray);
	} else {
		$gd_version_number = "Off";
	}
	return $gd_version_number;
}

function result($result = 1, $output = 1) {
	if ($result) {
		$text = '... <font color="#0000EE">Yes</font><br />';
		if (!$output) {
			return $text;
		}
		echo $text;
	} else {
		$text = '... <font color="#FF0000">No</font><br />';
		if (!$output) {
			return $text;
		}
		echo $text;
	}
}

function runquery($sql) {
	global $db, $tablenum, $lang, $strCreateTable;
	$sql = str_replace("\r", "\n", str_replace('wiki_', ' ' .DB_TABLEPRE, $sql));
	$ret = array ();
	$num = 0;
	foreach (explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach ($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset ($sql);
	$strtip = "";
	foreach ($ret as $query) {
		$query = trim($query);
		if ($query) {
			if (substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE\s*([a-z0-9_]+)\s*.*/is", "\\1", $query);
				$res = $db->query(createtable($query, DB_CHARSET));
				$strtip .= $strCreateTable . $name . " ... <font color=\"#0000EE\">{$lang['commonSuccess']}</font><br />";
				$tablenum++;
			} else {
				$res = $db->query($query);
			}
		}
	}
	return $strtip;
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array (
		'MYISAM',
		'HEAP'
	)) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
	 (mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET='".DB_CHARSET."'" : " TYPE=$type");
}

function replace_string($str_content) {
	if (strpos($str_content, "var") > 0) {
		$string = str_replace("[var]", "", $str_content);
		$string = str_replace("[/var]", "", $string);
		global $$string;
		$return = $$string;
	} else {
		$return = $str_content;
	}
	return $return;
}

function encode($string) {
	$string = trim($string);
	$string = str_replace("&", "&amp;", $string);
	$string = str_replace("'", "&#39;", $string);
	$string = str_replace("&amp;amp;", "&amp;", $string);
	$string = str_replace("&amp;quot;", "&quot;", $string);
	$string = str_replace("\"", "&quot;", $string);
	$string = str_replace("&amp;lt;", "&lt;", $string);
	$string = str_replace("<", "&lt;", $string);
	$string = str_replace("&amp;gt;", "&gt;", $string);
	$string = str_replace(">", "&gt;", $string);
	$string = str_replace("&amp;nbsp;", "&nbsp;", $string);

	$string = nl2br($string);
	return $string;
}

function check_email($email) {
	if ($email != "") {
		if (ereg("^.+@.+\\..+$", $email)) {
			return 1;
		} else {
			return 0;
		}
	} else {
		return 1;
	}
}

function check_user($username) {
	if ($username == "") {
		return 0;
	} else {
		if (preg_match("/[\s\'\"\\\]+/is", $username)) {
			return 0;
		}
		elseif (strlen(str_replace("/[^\x00-\xff]/g", "**", $username)) < 3) {
			return 0;
		} else {
			return 1;
		}
	}
}

function check_nickname($username) {
	if ($username == "") {
		return 0;
	} else {
		if (preg_match("/[\'\"\\\]+/is", $username)) {
			return 0;
		}
		elseif (strlen(str_replace("/[^\x00-\xff]/g", "**", $username)) < 3) {
			return 0;
		} else {
			return 1;
		}
	}
}

function check_password($password) {
	if ($password == "") {
		return 0;
	} else {
		if (preg_match("/[\'\"\\\]+/", $password) || strlen($password) < 5) {
			return 0;
		} else {
			return 1;
		}
	}
}

function hstrtoupper($str) {
	$i = 0;
	$total = strlen($str);
	$restr = '';
	for ($i = 0; $i < $total; $i++) {
		$str_acsii_num = ord($str[$i]);
		if ($str_acsii_num >= 97 AND $str_acsii_num <= 122) {
			$restr .= chr($str_acsii_num -32);
		} else {
			$restr .= chr($str_acsii_num);
		}
	}
	return $restr;
}

function subString($text, $start = 0, $limit = 12) {
	global $g_db_charset;
		if (strtolower($g_db_charset) == 'gbk') {
			$strlen = strlen($text);
			if ($start >= $strlen)return $text;
			$clen = 0;
			for ($i = 0; $i < $strlen; $i++, $clen++) {
				if (ord(substr($text, $i, 1)) > 0xa0) {
					if ($clen >= $start)
						$tmpstr .= substr($text, $i, 2);
					$i++;
				} else {
					if ($clen >= $start)
						$tmpstr .= substr($text, $i, 1);
				}
				if ($clen >= $start + $limit)
					break;
			}
			$text = $tmpstr;
		}else{
			$patten = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($patten, $text, $regs);
			$v = 0;
			$s = '';
			for ($i = 0; $i < count($regs[0]); $i++) {
				(ord($regs[0][$i]) > 129) ? $v += 2 : $v++;
				$s .= $regs[0][$i];
				if ($v >= $limit * 2) {
					break;
				}
			}
			$text = $s;
	}
	return $text;
}

function cleardir($dir) {
	if(!is_dir($dir))return;
	$directory = dir($dir);
	while ($entry = $directory->read()) {
		$filename = $dir . '/' . $entry;
		if (is_file($filename)) {
			@ unlink($filename);
		}
	}
	$directory->close();
}

function  file_writeable($file){
  if(is_dir($file)){
        $dir=$file;
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
  }else{
		if($fp = @fopen($file, 'a+')) {
			@fclose($fp);
			$writeable = 1;
		}else {
			$writeable = 0;
		}
  }
  return $writeable;
}


function forceMkdir($path) {
  if (!file_exists($path))
	{
		forceMkdir(dirname($path));
	   mkdir($path, 0777);
	}
}


require HDWIKI_ROOT.'/model/plugin.class.php';
class pluginbase {
	var $db;
	var $model;
	function pluginbase(&$db){
		$this->db = $db;
		$this->model = new pluginmodel($this);
	}
	
	function install($identifier){
		require HDWIKI_ROOT."/plugins/$identifier/model/$identifier.class.php";
		$identifiermodel=$identifier.'model';
		$themodel = new $identifiermodel($this);
		$plugin=$themodel->install();
		$this->model->add_plugin($plugin);
	}
}

 
?>