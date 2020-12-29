<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_CYASK', TRUE);
define('CYASK_ROOT', substr(dirname(__FILE__), 0, -7));
include (CYASK_ROOT.'./config.inc.php');
$version = '3.2 for UC';
$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];
$language = $_POST['language'] ? $_POST['language'] : $_GET['language'];
$step = $_POST['step'] ? $_POST['step'] : $_GET['step'];


$lockfile = CYASK_ROOT.'./askdata/install.lock';
$configfile = CYASK_ROOT.'./config.inc.php';
$language = 'simplified_chinese_utf8';
$languagefile = CYASK_ROOT.'./install/'.$language.'.lang.php';
$sqlfile = CYASK_ROOT.'./install/cyask.sql';

require_once $languagefile;

header('Content-Type: text/html; charset='.$charset);

if(PHP_VERSION < '4.3.5')
{
	exit('你的PHP版本: '.PHP_VERSION.' 过低，不能安装Cyask');
}

if(file_exists($lockfile)) 
{
	show_header();
	show_msg('警告!您已经安装过Cyask<br>
		为了保证数据安全，请立即手动删除 ./install/index.php 文件<br>
		如果您想重新安装Cyask，请删除 ./askdata/install.lock 文件，再次运行安装文件');
}

if(!is_readable($languagefile) || !is_readable($sqlfile))
{
	show_header();
	show_msg('请上传 install 目录下所有文件');
}

if(empty($action))
{
	$cyask_license = str_replace('  ', '&nbsp; ', $lang['license']);
	
	show_header();

	print<<<END
        <tr>
          <td><strong>$lang[current_process] $lang[show_license]</strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td>strong><font color="#FF0000">&gt;</font><font color="#000000"> $lang[agreement]</font></strong></td>
        </tr>
        <tr>
          <td><br>
            <table width="90%" cellspacing="1" bgcolor="#000000" border="0" align="center">
              <tr>
                <td bgcolor="#E3E3EA">
                  <table width="99%" cellspacing="1" border="0" align="center">
                    <tr>
                      <td>
                        $cyask_license
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td align="center">
            <br>
            <form method="post" action="?language=$language ">
              <input type="hidden" name="action" value="config_ucenter" />
              <input type="submit" name="submit" value="$lang[agreement_yes]" style="height: 25" />&nbsp;
              <input type="button" name="exit" value="$lang[agreement_no]" style="height: 25" onclick="javascript: window.close();" />
            </form>
          </td>
        </tr>
END;

	show_footer();
}
elseif($action == 'config_ucenter')
{
	$step = 1;
	
	$exist_error = FALSE;
	$write_error = FALSE;
	if(file_exists($configfile))
	{
		$fileexists = result(1, 0);
	}
	else
	{
		$fileexists = result(0, 0);
		$exist_error = TRUE;
		$config_info = $lang['config_nonexistence'];
	}
	if(is_writeable($configfile))
	{
		$filewriteable = result(1, 0);
		$config_info = $lang['config_ucenter_comment'];
	}
	else
	{
		$filewriteable = result(0, 0);
		$write_error = TRUE;
		$config_info = $lang['config_unwriteable'];
	}

	show_header();

	print<<<END
        <tr>
          <td><strong>$lang[current_process] $lang[config_ucenter]</strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><strong><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang[check_config]?></font></strong></td>
        </tr>
        <tr>
          <td>config.inc.php $lang[check_existence] $fileexists</td>
        </tr>
        <tr>
          <td>config.inc.php $lang[check_writeable] $filewriteable</td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><strong><font color="#FF0000">&gt;</font><font color="#000000"> $lang[edit_config]</font></strong></td>
        </tr>
        <tr>
          <td align="center">
			<table width="650" cellspacing="1" border="0">
			 <tr><td align="left">$config_info</td></tr>
			</table>
        </td>
        </tr>
END;

	if(!$exist_error)
	{

		if(!$write_error)
		{
		
			@include_once $configfile;

	print<<<END
        <tr>
          <td align="center">
            <br>
            <form method="post" action="?language=$language">
              <table width="650" cellspacing="1" bgcolor="#000000" border="0" align="center">
                <tr bgcolor="#3A4273">
                  <td align="center" width="30%" style="color: #FFFFFF">$lang[variable]</td>
                  <td align="center" width="40%" style="color: #FFFFFF">$lang[value]</td>
                  <td align="center" width="30%" style="color: #FFFFFF">$lang[comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_url]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="ucapi" value="http://" size="40"></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_url_comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_founder]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="password" name="ucfounderpw" value="" size="40"></td>
                  <td bgcolor="#E3E3EA">&nbsp;</td>
                </tr>
              </table>
              <br />
              <input type="hidden" name="ucfounder" size="20" value="" />
              <input type="hidden" name="action" value="app_ucenter" />
              <input type="submit" name="submit" value="$lang[save_config_ucenter]" style="height: 25" />
              <input type="button" name="exit" value="$lang[exit]" style="height: 25" onclick="javascript: window.close();" />
            </form>
          </td>
        </tr>
END;

			show_footer();
		}
		else
		{
			show_msg('config no write');
		}
	}
	else
	{
		show_msg('config no exist');
	}
}
elseif($action == 'app_ucenter')
{

	$step = 1;

	$app_name = 'Cyask';
	$app_type = 'OTHER';
	
	$ucfounderpw = $_POST['ucfounderpw'];
	//判断域名是否解析
	$ucapi = preg_replace("/\/$/", '', trim($_POST['ucapi']));
	$ucip = trim($_POST['ucip']);

	show_header();
	
	if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi))
	{
		show_msg('UCenter的URL地址不正确');
	} 
	else 
	{
		//检查服务器 dns 解析是否正常, dns 解析不正常则要求用户输入ucenter的ip地址
		if(!$ucip) 
		{
			$temp = @parse_url($ucapi);
			$ucip = gethostbyname($temp['host']);
			if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) 
			{
				$ucip = '';
			}
		}
	}

	if(!@include_once CYASK_ROOT.'./uc_client/client.php')
	{
		show_msg('uc_client目录不存在');
	}

	$ucinfo = dfopen($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
	
	list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
		
	$dbcharset = strtolower(trim($dbcharset ? str_replace('-', '', $dbcharset) : $dbcharset));
	$ucdbcharset = strtolower(trim($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset));
	$apptypes = strtolower(trim($apptypes));
		
	if($status != 'UC_STATUS_OK') 
	{

		print<<<END
		<tr>
          <td><strong>$lang[current_process] $lang[install_interrupt]</strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
		<td>
            ... <font color="#FF0000">$lang[fail_reason] UCenter无法正常连接，返回错误 ( $status )，请确认UCenter 的 URL是否正确</font></td>
        </tr>
		<tr>
		<td align="center">
		<br />
			<br />
			<form method="post" action="?language=$language">
			<table width="650" cellspacing="1" bgcolor="#000000" border="0" align="center">
			 <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_url]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="ucapi" value="$ucapi" size="40"></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_url_comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[ucenter_founder]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="test" name="ucfounderpw" value="$_POST[ucfounderpw]" size="40"></td>
                  <td bgcolor="#E3E3EA">&nbsp;</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;UCenter服务器的IP地址: </td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="ucip" value="$ucip" size="40"></td>
                  <td bgcolor="#E3E3EA">&nbsp;</td>
                </tr>
			</table>
			<br />
				<input type="hidden" name="action" value="save_config_ucenter" />
				<input type="submit" name="submit" value="确认IP地址" style="height: 25" />
				<input type="button" name="exit" value="$lang[exit]" style="height: 25" onclick="javascript: window.close();" />
			</form>
		</td>
		</tr>
END;

		show_footer();

	} 
	elseif(UC_CLIENT_VERSION > $ucversion) 
	{
		show_msg('您的 UCenter 服务端版本 ('.$ucversion.') 过低，请升级 UCenter 服务端到最新版本，并且升级，下载地址：http://download.comsenz.com/');
	}
	elseif($dbcharset && $ucdbcharset != $dbcharset) 
	{
		show_msg('UCenter 服务端字符集与当前应用的字符集不同，请下载 '.$ucdbcharset.' 编码的 UCenter Home 进行安装，下载地址：http://download.comsenz.com/');
	} 
	elseif(strexists($apptypes, 'cyask')) 
	{
		show_msg('已经安装过一个Cyask产品，如果想继续安装，请先到 UCenter 应用管理中删除已有的Cyask！');
	}
	
	$uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	
	$app_url = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/install/'));
	
	$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
		'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
		'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
		'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
		'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
		'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);
	
	$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucfounderpw)."&apptype=".urlencode($app_type)."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".$charset.'&appdbcharset='.$dbcharset.'&'.$app_tagtemplates.'&release='.UC_CLIENT_RELEASE;
	
	$s = dfopen($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
			
	if(empty($s)) 
	{
		show_msg('UCenter用户中心无法连接');
	} 
	elseif($s == '-1') 
	{
		show_msg('UCenter管理员帐号密码不正确');
	} 
	else 
	{
		$ucs = explode('|', $s);
		if(empty($ucs[0]) || empty($ucs[1])) 
		{
			show_msg('UCenter返回的数据出现问题，请参考:<br />'.shtmlspecialchars($s));
		} 
		else 
		{

			//处理成功
			$apphidden = '';
			//验证是否可以直接联接MySQL
			$link = mysql_connect($ucs[2], $ucs[4], $ucs[5], 1);
			$connect = $link && mysql_select_db($ucs[3], $link) ? 'mysql' : '';
			//返回
			foreach (array('key', 'appid', 'dbhost', 'dbname', 'dbuser', 'dbpw', 'dbcharset', 'dbtablepre', 'charset') as $key => $value) 
			{
				if($value == 'dbtablepre') 
				{
					$ucs[$key] = '`'.$ucs[3].'`.'.$ucs[$key];
				}
				$apphidden .= "<input type=\"hidden\" name=\"uc[$value]\" value=\"".$ucs[$key]."\" />";
			}
			//内置
			$apphidden .= "<input type=\"hidden\" name=\"uc[connect]\" value=\"$connect\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[api]\" value=\"$_POST[ucapi]\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[ip]\" value=\"$ucip\" />";

			print<<<END
			<tr>
			<td align="center">
				<form method="post" action="?language=$language">
				<br />
				<br />
				<table width="600" cellspacing="1" bgcolor="#000000" border="0" align="center">
				<tr valign="middle" bgcolor="#3A4273">
				<td height="35" bgcolor="#E3E3EA">UCenter注册成功！当前程序ID标识为: $ucs[1]</td></tr>
				</table>
				<br />
				$apphidden
				<input type="hidden" name="action" value="save_uc_config" />
				<input type="submit" name="submit" value="进入下一步" style="height: 25" />
				</form>
			</td>
			</tr>
END;
			show_footer();
		}
	}

}
elseif($action == 'save_uc_config')
{

	//增加congfig配置
	$step = 2;

	@include $configfile;
	
	//写入config文件
	$fp = fopen($configfile, 'r');
	$configcontent = fread($fp, filesize($configfile));
	fclose($fp);
				
	$keys = array_keys($_POST['uc']);
	foreach ($keys as $value) 
	{
		$upkey = strtoupper($value);
		$configcontent = preg_replace("/define\('UC_".$upkey."'\s*,\s*'.*?'\)/i", "define('UC_".$upkey."', '".$_POST['uc'][$value]."')", $configcontent);
	}
	
	show_header();
	
	if(!$fp = fopen($configfile, 'w')) 
	{
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

	
	//开始配置Cyask

	print<<<END
        <tr>
          <td><strong>$lang[current_process] $lang[config_cyaskdb]</strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><strong><font color="#FF0000">&gt;</font><font color="#000000">$lang[edit_config]</font></strong></td>
        </tr>
        <tr>
          <td align="center"><br />$lang[config_cyaskdb_comment]</td>
        </tr>
        
        <tr>
          <td align="center">
            <br>
            <form method="post" action="?language=$language">
              <table width="650" cellspacing="1" bgcolor="#000000" border="0" align="center">
                <tr bgcolor="#3A4273">
                  <td align="center" width="20%" style="color: #FFFFFF">$lang[variable]</td>
                  <td align="center" width="30%" style="color: #FFFFFF">$lang[value]</td>
                  <td align="center" width="50%" style="color: #FFFFFF">$lang[comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA" style="color: #FF0000">&nbsp;$lang[dbhost]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="dbhost" value="$dbhost" size="30" /></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[dbhost_comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[dbuser]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="dbuser" value="$dbuser" size="30"></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[dbuser_comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[dbpw]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="password" name="dbpw" value="$dbpw" size="30"></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[dbpw_comment]</td>
                </tr>
                <tr>
                  <td bgcolor="#E3E3EA" style="color: #FF0000">&nbsp;$lang[tablepre]</td>
                  <td bgcolor="#EEEEF6" align="center"><input type="text" name="tablepre" value="$tablepre" size="30"></td>
                  <td bgcolor="#E3E3EA">&nbsp;$lang[tablepre_comment]</td>
                </tr>
          
              </table>
              <br>
              <input type="hidden" name="action" value="dbselect" />
              <input type="submit" name="submit" value="$lang[save_config]" style="height: 25" />
              <input type="button" name="exit" value="$lang[exit]" style="height: 25" onclick="javascript: window.close();" />
            </form>
          </td>
        </tr>
END;

	show_footer();
}
elseif($action == 'dbselect')
{
	$step = 3;
	
	show_header();
	
?>
        <tr>
          <td><strong><?php echo $lang['current_process']?> <?php echo $lang['create_cyaskdb'];?></strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['check_config']?></font></b></td>
        </tr>
        <tr>
          <td>config.inc.php <?php echo $lang['check_existence']?> <?php echo $fileexists?></td>
        </tr>
        <tr>
          <td>config.inc.php <?php echo $lang['check_writeable']?> <?php echo $filewriteable?></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"><?php echo $lang['show_and_edit_db_conf']?></font></b></td>
        </tr>
        <tr>
          <td align="center"><br><?php echo $lang['choice_or_new_db'];?></td>
        </tr>
        <tr>
          <td align="center">
            <br>
            <form method="post" action="?language=<?php echo $language?>">
            <table width="40%" cellspacing="1" bgcolor="#000000" border="0" align="center">
              <tr bgcolor="#3A4273">
                <td align="center" colspan="3" style="color: #FFFFFF"><?php echo $lang['db_set']?></td>
              </tr>
<?php

		if(is_writeable($configfile))
		{
				$dbhost = setconfig($_POST['dbhost']);
				$dbuser = setconfig($_POST['dbuser']);
				$dbpw = setconfig($_POST['dbpw']);
				$adminemail = setconfig($_POST['adminemail']);
				$tablepre = setconfig($_POST['tablepre']);

				$fp = fopen($configfile, 'r');
				$configcontent = fread($fp, filesize($configfile));
				fclose($fp);

				$configcontent = preg_replace("/[$]dbhost\s*\=\s*[\"'].*?[\"'];/is", "\$dbhost = '$dbhost';", $configcontent);
				$configcontent = preg_replace("/[$]dbuser\s*\=\s*[\"'].*?[\"'];/is", "\$dbuser = '$dbuser';", $configcontent);
				$configcontent = preg_replace("/[$]dbpw\s*\=\s*[\"'].*?[\"'];/is", "\$dbpw = '$dbpw';", $configcontent);
				$configcontent = preg_replace("/[$]tablepre\s*\=\s*[\"'].*?[\"'];/is", "\$tablepre = '$tablepre';", $configcontent);
				
				$fp = fopen($configfile, 'w');
				fwrite($fp, trim($configcontent));
				fclose($fp);
		}
		else
		{
			show_msg('dbconfig error');
		}
		
		include_once $configfile;
		include_once CYASK_ROOT.'./include/db_mysql.php';
		$db = new db_sql;
		$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

		$query = $db->query("CREATE DATABASE ask_temp", 'SILENT');
		if($db->error())
		{
			$createerror = TRUE;
		}
		else
		{
			$query = $db->query("DROP DATABASE ask_temp", 'SILENT');
			$createerror = FALSE;
		}

		$query = $db->query("SHOW DATABASES", 'SILENT');

		$option = '';
		if($query)
		{
			while($database = $db->fetch_array($query))
			{
				if($database['Database'] != 'mysql')
				{
					$option .= '<option value="'.$database['Database'].'"' .($dbname == $database['Database'] ? ' selected' : '') . '>'.$database['Database']."</option>";
				}
			}
		}
		
		if(!empty($option))
		{
?>
			<tr>
              	<td bgcolor="#EEEEF6">&nbsp;
                  <input name="type" type="radio" value="2" checked style="background-color:#EEEEF6">
        	  <?php echo $lang['db_use_existence']?>:
                </td>
                <td bgcolor="#EEEEF6">&nbsp;
                  <select name="dbnameselect" style="width:200px"><?php echo $option?></select>
                </td>
            </tr>

<?php
		}
		if(!$createerror)
		{
?>
            <tr>
                <td bgcolor="#EEEEF6">&nbsp;
                  <input name="type" type="radio" value="1" style="background-color:#EEEEF6" <?php echo ((empty($option)) ? ' checked' : '')?>>
                  <?php echo $lang['db_create_new']?>:
                </td>
                <td bgcolor="#EEEEF6">&nbsp;
                  <input type="text" name="dbname" value="<?php echo $dbname?>" style="width:200px">
                </td>
            </tr>
<?php
		}
		if($createerror && empty($option))
		{
?>
            <tr>
                <td bgcolor="#EEEEF6">&nbsp;
                  <?php echo $lang['choice_one_db']?>:
                </td>
                <td bgcolor="#EEEEF6">&nbsp;
                  <input type="text" name="dbname" value="<?php echo $dbname?>" style="width:200px">
                </td>
            </tr>
<?php
		}
?>
           </table>
         </td>
       </tr>
       <tr>
	   <td align="center">
	     <br>
	     <input type="hidden" name="action" value="environment">
	     <input type="submit" name="submit" value="<?php echo $lang['save_config']?>" style="height: 25">
	     <input type="button" name="exit" value="<?php echo $lang['exit']?>" style="height: 25" onclick="javascript: window.close();">
	   </td>
	 </tr>
	 </form>
<?php

	show_footer();
}
elseif($action == 'environment')
{
	$step = 4;
	
	show_header();
	
	if(is_writeable($configfile))
	{

		$dbname = ($_POST['type'] == 1) ? $_POST['dbname'] : $_POST['dbnameselect'];
		$dbname = setconfig($dbname);

		$fp = fopen($configfile, 'r');
		$configcontent = fread($fp, filesize($configfile));
		fclose($fp);

		$configcontent = preg_replace("/[$]dbname\s*\=\s*[\"'].*?[\"'];/is", "\$dbname = '$dbname';", $configcontent);

		$fp = fopen($configfile, 'w');
		fwrite($fp, trim($configcontent));
		fclose($fp);

	}

	include $configfile;
	include CYASK_ROOT.'./include/db_mysql.php';
	$db = new db_sql;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$msg = '';
	$quit = FALSE;

	$curr_os = PHP_OS;

	$curr_php_version = PHP_VERSION;
	if($curr_php_version < '4.3.5')
	{
		$msg .= "<font color=\"#FF0000\">$lang[php_version_435]</font>\t";
		$quit = TRUE;
	}

	if(@ini_get(file_uploads))
	{
		$max_size = @ini_get(upload_max_filesize);
		$curr_upload_status = $lang['attach_enabled'].$max_size;
		$msg .= $lang['attach_enabled_info'].$max_size."\t";
	}
	else
	{
		$curr_upload_status = $lang['attach_disabled'];
		$msg .= "<font color=\"#FF0000\">$lang[attach_disabled_info]</font>\t";
	}

	$query = $db->query("SELECT VERSION()");
	$curr_mysql_version = $db->result($query, 0);
	if($curr_mysql_version < '3.23')
	{
		$msg .= "<font color=\"#FF0000\">$lang[mysql_version_323]</font>\t";
		$quit = TRUE;
	}

	$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

	if(dir_writeable(CYASK_ROOT.'./templates'))
	{
		$curr_tpl_writeable = $lang['writeable'];
	}
	else
	{
		$curr_tpl_writeable = $lang['unwriteable'];
		$msg .= "<font color=\"#FF0000\">$lang[unwriteable_template]</font>\t";
	}

	if(dir_writeable(CYASK_ROOT.'./askdata'))
	{
		$curr_data_writeable = $lang['writeable'];
	}
	else
	{
		$curr_data_writeable = $lang['unwriteable'];
		$msg .= "<font color=\"#FF0000\">$lang[unwriteable_askdata]</font>\t";
	}

	if(dir_writeable(CYASK_ROOT.'./askdata/templates'))
	{
		$curr_template_writeable = $lang['writeable'];
	}
	else
	{
		$curr_template_writeable = $lang['unwriteable'];
		$msg .= "<font color=\"#FF0000\">$lang[unwriteable_askdata_template]</font>\t";
		$quit = TRUE;
	}

	if(dir_writeable(CYASK_ROOT.'./askdata/cache'))
	{
		$curr_cache_writeable = $lang['writeable'];
	}
	else
	{
		$curr_cache_writeable = $lang['unwriteable'];
		$msg .= "<font color=\"#FF0000\">$lang[unwriteable_askdata_cache]</font>\t";
		$quit = TRUE;
	}

	if(strstr($tablepre, '.'))
	{
		$msg .= "<font color=\"#FF0000\">$lang[tablepre_invalid]</font>\t";
		$quit = TRUE;
	}

	$db->select_db($dbname);
	if($db->error())
	{
		if($db->version() > '4.1')
		{
			$db->query("CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET $dbcharset");
		}
		else
		{
			$db->query("CREATE DATABASE IF NOT EXISTS $dbname");
		}
		if($db->error())
		{
			$msg .= "<font color=\"#FF0000\">$lang[db_invalid]</font>\t";
			$quit = TRUE;
		}
		else
		{
			$db->select_db($dbname);
			$msg .= "$lang[db_auto_created]\t";
		}
	}

	$query = $db->query("CREATE TABLE cyask_test (test TINYINT (3) UNSIGNED)", 'SILENT');
	if($db->error())
	{
		$dbpriv_createtable = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	}
	else
	{
		$dbpriv_createtable = $lang['yes'];
	}
	$query = $db->query("INSERT INTO cyask_test (test) VALUES (1)", 'SILENT');
	if($db->error()) 
	{
		$dbpriv_insert = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	} 
	else 
	{
		$dbpriv_insert = $lang['yes'];
	}
	$query = $db->query("SELECT * FROM cyask_test", 'SILENT');
	if($db->error())
	{
		$dbpriv_select = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	}
	else
	{
		$dbpriv_select = $lang['yes'];
	}
	$query = $db->query("UPDATE cyask_test SET test='2' WHERE test='1'", 'SILENT');
	if($db->error())
	{
		$dbpriv_update = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	} else {
		$dbpriv_update = $lang['yes'];
	}
	$query = $db->query("DELETE FROM cyask_test WHERE test='2'", 'SILENT');
	if($db->error())
	{
		$dbpriv_delete = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	}
	else
	{
		$dbpriv_delete = $lang['yes'];
	}
	$query = $db->query("DROP TABLE cyask_test", 'SILENT');
	if($db->error())
	{
		$dbpriv_droptable = '<font color="#FF0000">'.$lang['no'].'</font>';
		$quit = TRUE;
	}
	else
	{
		$dbpriv_droptable = $lang['yes'];
	}

	$query = $db->query("SELECT COUNT(*) FROM cyask_set", 'SILENT');
	if(!$db->error())
	{
		$msg .= "<font color=\"#FF0000\">$lang[db_not_null]</font>\t";
		$alert = " onSubmit=\"return confirm('$lang[db_drop_table_confirm]');\"";
	}
	else
	{
		$alert = '';
	}

	if($quit)
	{
		$msg .= "<font color=\"#FF0000\">$lang[install_abort]</font>";
	}
	else
	{
		$msg .= $lang['install_process'];
	}

?>
        <tr>
          <td><strong><?php echo $lang['current_process'];?> <?php echo $lang['check_env'];?></strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><strong><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['check_user_and_pass']?></font></strong></td>
        </tr>
        <tr>
          <td>
            <br>
            <table width="50%" cellspacing="1" bgcolor="#000000" border="0" align="center">
              <tr bgcolor="#3A4273">
                <td align="center" style="color: #FFFFFF"><?php echo $lang['permission']?></td>
                <td align="center" style="color: #FFFFFF"><?php echo $lang['status']?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">CREATE TABLE</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_createtable?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">INSERT</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_insert?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">SELECT</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_select?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">UPDATE</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_update?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">DELETE</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_delete?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">DROP TABLE</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $dbpriv_droptable?></td>
              </tr>
            </table>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['compare_env']?></font></b></td>
        </tr>
        <tr>
          <td>
            <br>
            <table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
              <tr bgcolor="#3A4273">
                <td align="center"></td>
                <td align="center" style="color: #FFFFFF"><?php echo $lang['env_required']?></td>
                <td align="center" style="color: #FFFFFF"><?php echo $lang['env_best']?></td>
                <td align="center" style="color: #FFFFFF"><?php echo $lang['env_current']?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['env_os']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $lang['unlimited']?></td>
                <td bgcolor="#E3E3EA" align="center">UNIX/Linux/FreeBSD</td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $curr_os?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['env_php']?></td>
                <td bgcolor="#EEEEF6" align="center">4.3.5+</td>
                <td bgcolor="#E3E3EA" align="center">5.2.0+</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_php_version?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['env_attach']?></td>
                <td bgcolor="#EEEEF6" align="center"3><?php echo $lang['unlimited']?></td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['enabled']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_upload_status?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['env_mysql']?></td>
                <td bgcolor="#EEEEF6" align="center">4.0+</td>
                <td bgcolor="#E3E3EA" align="center">4.1+</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_mysql_version?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['env_diskspace']?></td>
                <td bgcolor="#EEEEF6" align="center">2M+</td>
                <td bgcolor="#E3E3EA" align="center">50M+</td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_disk_space?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">/templates <?php echo $lang['env_dir_writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $lang['unlimited']?></td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_tpl_writeable?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">/askdata <?php echo $lang['env_dir_writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $lang['unlimited']?></td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_data_writeable?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">/askdata/templates <?php echo $lang['env_dir_writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_template_writeable?></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" align="center">/askdata/cache <?php echo $lang['env_dir_writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#E3E3EA" align="center"><?php echo $lang['writeable']?></td>
                <td bgcolor="#EEEEF6" align="center"><?php echo $curr_cache_writeable?></td>
              </tr>
            </table>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['confirm_preparation']?></font></b></td>
        </tr>
        <tr>
          <td>
            <br>
            <ol><?php echo $lang['preparation']?></ol>
          </td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['install_note']?></font></b></td>
        </tr>
        <tr>
          <td>
            <br>
            <ol>
<?php

	foreach(explode("\t", $msg) as $message)
	{
		echo "              <li>$message</li>\n";
	}
	echo"            </ol>\n";

	if($quit)
	{

		print<<<END
            <center>
            <input type="button" name="refresh" value="$lang[recheck_config]" style="height: 25" onclick="javascript: window.location=(\"?language=$language&action=environment\");" />&nbsp;
            <input type="button" name="exit" value="$lang[exit]" style="height: 25" onclick="javascript: window.close();" />
            </center>
END;

	}
	else
	{

		print<<<END
        <form method="post" action="?language=$language" $alert >
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><strong><font color="#FF0000">&gt;</font><font color="#000000"> $lang[config_admin]</font></strong></td>
        </tr>
        <tr>
          <td align="center">
			<table width="600" cellspacing="1" border="0">
			 <tr><td align="left"> $lang[config_admin_comment]</td></tr>
			</table>
        </td>
        </tr>
        
        <tr>
          <td align="center">
            <br>
            <table width="600" cellspacing="1" bgcolor="#000000" border="0" align="center">
              <tr>
                <td bgcolor="#E3E3EA" width="35%">&nbsp; $lang[username]</td>
                <td bgcolor="#EEEEF6" width="65%"><input type="text" name="username" value="" size="30"></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" width="35%">&nbsp; $lang[admin_email]</td>
                <td bgcolor="#EEEEF6" width="65%"><input type="text" name="email" value="" size="30"></td>
              </tr>
              <tr>
                <td bgcolor="#E3E3EA" width="35%">&nbsp; $lang[password]</td>
                <td bgcolor="#EEEEF6" width="65%"><input type="password" name="password" size="30"></td>
              </tr>
            </table>
            <br />
            <input type="hidden" name="action" value="install" />
            <input type="submit" name="submit" value="$lang[start_install]" style="height: 25" />&nbsp;
            <input type="button" name="exit" value="$lang[exit]" style="height: 25" onclick="javascript: window.close();" />
          </td>
        </tr>
        </form>
END;

	}
	show_footer();
}
elseif($action == 'install')
{
	$step = 5;
	
	@include_once $configfile;
	@include_once CYASK_ROOT.'./include/db_mysql.php';
	@include_once(CYASK_ROOT.'./uc_client/client.php');
	
	$username = trim($_POST['username']);
	$password = $_POST['password'];

	list($uid,$uname,$email)= uc_get_user($username,0);
	
	if($uid<=0)
	{
		$email = trim($_POST['email']);
		$uid = uc_user_register($username,$password,$email);
	}	

	switch($uid)
	{
		case -1:
		case -2:
		case -4:
		case -5:
		case -6:
			$msg=$lang['admin_username_invalid'.abs($uid)];
		break;
		default:
		break;
	}
	
	show_header();

?>
        <tr>
          <td><strong><?php echo $lang['current_process']?> <?php echo $lang['installing']?></strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['check_admin']?></font></b></td>
        </tr>
        <tr>
          <td><?php echo $lang['check_admin_validity']?>
<?php
	if($msg)
	{
		show_msg($msg);
	}
	else
	{
		uc_user_addprotected($username);
		
		echo result(1, 0)."</td>\n";
		echo"        </tr>\n";
	}

?>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td><b><font color="#FF0000">&gt;</font><font color="#000000"> <?php echo $lang['select_db']?></font></b></td>
        </tr>
<?php

	$db = new db_sql;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);


echo"        <tr>\n";
echo"          <td>$lang[select_db] $dbname ".result(1, 0)."</td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td>\n";
echo"            <hr noshade align=\"center\" width=\"100%\" size=\"1\">\n";
echo"          </td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td><b><font color=\"#FF0000\">&gt;</font><font color=\"#000000\"> $lang[create_table]</font></b></td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td>\n";

	$fp = fopen($sqlfile, 'rb');
	$sqlcontent = fread($fp, 2048000);
	$sqlcontent = str_replace("\r\n", "\n", $sqlcontent);
	fclose($fp);
	
	run_query($sqlcontent);

	$db->query("DELETE FROM {$tablepre}member");
	$db->query("INSERT INTO {$tablepre}member SET uid='$uid',username='$username',password='".md5($password)."',email='$email',adminid=1,attachopen=1");

	$db->query("DELETE FROM {$tablepre}admin");
	$db->query("INSERT INTO {$tablepre}admin SET uid='$uid',adminid=1,sid='all'");

echo"          </td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td>\n";
echo"            <hr noshade align=\"center\" width=\"100%\" size=\"1\">\n";
echo"          </td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td><b><font color=\"#FF0000\">&gt;</font><font color=\"#000000\"> $lang[init_file]</font></b></td>\n";
echo"        </tr>\n";
echo"        <tr>\n";
echo"          <td>\n";

loginit('adminlog');
loginit('errorlog');

if(@$fp = fopen($lockfile, 'w')) 
{
	fwrite($fp, 'Cyask for UCenter');
	fclose($fp);
}

dir_clear(CYASK_ROOT.'./askdata/templates');

?>
          </td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr>
          <td align="center">
            <font color="#FF0000"><b><?php echo $lang['install_succeed']?></font><br>
            <?php echo $lang['username']?></b> <?php echo $username?><b> &nbsp; <?php echo $lang['password']?></b> <?php echo $password?>
            <br><br>
            <a href="../admin.php">进入管理页面</a>
            <br>
            <a href="../index.php" target="_blank">进入Cyask首页</a>
          </td>
        </tr>
<?php
	show_footer();
}

/////////////////安装过程所需函数库/////////////////////////////////////////////////

function loginit($logfile)
{
	global $lang;

	echo $lang['init_log'].' '.$logfile;
	$fp = @fopen(CYASK_ROOT.'./askdata/'.$logfile.'.php', 'w');
	@fwrite($fp, "<?PHP exit(\"Access Denied\"); ?>\n");
	@fclose($fp);
	result();
}

function run_query($sql)
{
	global $lang, $dbcharset, $tablepre, $db;

	//$sql = str_replace("\r", "\n", str_replace(' '.ORIG_TABLEPRE, ' '.$tablepre, $sql));
	$sql = str_replace("\r", "\n", str_replace(' cyask_', ' '.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query)
	{
		$queries = explode("\n", trim($query));
		foreach($queries as $query)
		{
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query)
	{
		$query = trim($query);
		if($query)
		{
			if(substr($query, 0, 12) == 'CREATE TABLE')
			{
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				echo $lang['create_table'].' '.$name.' ... <font color="#0000EE">'.$lang['succeed'].'</font><br>';
				$db->query(createtable($query, $dbcharset));
			}
			else
			{
				$db->query($query);
			}
		}
	}
}

function result($result = 1, $output = 1)
{
	global $lang;

	if($result)
	{
		$text = '... <font color="#0000EE">'.$lang['succeed'].'</font><br>';
		if(!$output)
		{
			return $text;
		}
		echo $text;
	}
	else
	{
		$text = '... <font color="#FF0000">'.$lang['fail'].'</font><br>';
		if(!$output)
		{
			return $text;
		}
		echo $text;
	}
}

function dir_writeable($dir)
{
	if(!is_dir($dir))
	{
		@mkdir($dir, 0777);
	}
	if(is_dir($dir))
	{
		if($fp = @fopen("$dir/test.test", 'w'))
		{
			@fclose($fp);
			@unlink("$dir/test.test");
			$writeable = 1;
		}
		else
		{
			$writeable = 0;
		}
	}
	return $writeable;
}

function dir_clear($dir)
{
	global $lang;

	echo $lang['clear_dir'].' '.$dir;
	$directory = dir($dir);
	while($entry = $directory->read())
	{
		$filename = $dir.'/'.$entry;
		if(is_file($filename))
		{
			@unlink($filename);
		}
	}
	$directory->close();
	result();
}

function createtable($sql, $dbcharset)
{
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function setconfig($string)
{
	if(!get_magic_quotes_gpc())
	{
		$string = str_replace('\'', '\\\'', $string);
	}
	else
	{
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}

function strexists($haystack, $needle)
{
	return !(strpos($haystack, $needle) === FALSE);
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE)
{
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].(isset($matches['query']) && $matches['query'] ? '?'.$matches['query'] : '') : '/';
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

function show_msg($message)
{
	global $lang,$version;
	
	print<<<END
		<tr>
          <td><strong>$lang[current_process] $lang[install_interrupt]</strong></td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
		<tr>
		<td>
            ... <font color="#FF0000">$lang[fail_reason] $message</font></td>
        </tr>
        <tr>
          <td align="center">
            <br>
            <input type="button" name="back" value="$lang[go_back]" style="height: 25" onClick="javascript: history.go(-1);">
          </td>
        </tr>
END;
	show_footer();
}

function show_header()
{
	global $lang, $action, $step;

	if(empty($action))
	{
		$content = '<tr><td align="center"><strong>'.$lang['welcome'].'</strong></td></tr>';
	}
	else
	{
		$step = $step-1;
		$step = $step<0 ? 1:$step;
		$config_array =array($lang[config_ucenter],$lang[config_cyaskdb],$lang[create_cyaskdb],$lang[check_env],$lang[finish_install]);
		$step_array = array();
		foreach($config_array as $key=>$val)
		{
			if($key == $step)
			{
				$step_array[] = '<font color="red">'.$val.'</font>';
			}
			else
			{
				$step_array[] = $val;
			}
		}
		
		$step_str = count($step_array) ? implode('&nbsp;&nbsp;=>&nbsp;&nbsp;',$step_array) : '';
		
		$content = '<tr><td align="center"><strong>'.$step_str.'</strong></td></tr>';
	}
	
	print<<<END
<html>
<head>
<title>$lang[install_wizard]</title>
<style>
A:visited	{COLOR: #3A4273; TEXT-DECORATION: none}
A:link		{COLOR: #3A4273; TEXT-DECORATION: none}
A:hover		{COLOR: #3A4273; TEXT-DECORATION: underline}
body,table,td	{COLOR: #3A4273; FONT-FAMILY: Tahoma, Verdana, Arial; FONT-SIZE: 12px; LINE-HEIGHT: 20px; scrollbar-base-color: #E3E3EA; scrollbar-arrow-color: #5C5C8D}
input		{COLOR: #085878; FONT-FAMILY: Tahoma, Verdana, Arial; FONT-SIZE: 12px; background-color: #3A4273; color: #FFFFFF; scrollbar-base-color: #E3E3EA; scrollbar-arrow-color: #5C5C8D}
.install	{FONT-FAMILY: Arial, Verdana; FONT-SIZE: 20px; FONT-WEIGHT: bold; COLOR: #000000}
</style>
</head>
<body bgcolor="#3A4273" text="#000000">
<table width="95%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
  <tr>
    <td>
      <table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
          <td class="install" height="30" valign="bottom"><font color="#FF0000">&gt;&gt;</font>
            $lang[install_wizard]</td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
		<tr>
          <td align="center">
           $content
          </td>
        </tr>
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
END;
}

function show_footer()
{
	global $version;
	
	print<<<END
        <tr>
          <td>
            <hr noshade align="center" width="100%" size="1">
          </td>
        </tr>
        <tr valign="middle">
          <td height="50" align="center">
            <strong style="font-size: 11px">Powered by <a href="http://www.cyask.com" target="_blank">Cyask $version </a> , &nbsp; Copyright &copy; Cyask 2005-2008</strong>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br /><br />
</body>
</html>
END;

	exit();
}

?>