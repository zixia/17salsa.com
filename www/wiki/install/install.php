<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('IN_HDWIKI', TRUE);
define('HDWIKI_ROOT', '../');

$lang_name=$_COOKIE['lang_name'];
if(isset($_REQUEST['lang'])){
	$lang_name = $_REQUEST['lang'];
	setcookie('lang_name',$lang_name);
}
if(!$lang_name){
	$lang_name='zh';
}

require HDWIKI_ROOT."/lang/$lang_name/install.php";
require HDWIKI_ROOT.'/version.php';
require HDWIKI_ROOT.'/model/base.class.php';
$step = (isset ($_GET['step'])) ? $_GET['step'] : $_POST['step'];

if (file_exists(HDWIKI_ROOT.'/data/install.lock') && $step != '7') {
	echo "<font color='red'>{$lang['tipAlreadyInstall']}</font>";
	exit();
}

if(!ini_get('short_open_tag')) {
	echo "<font color='red'>{$lang['shortOpenTagInvalid']}</font>";
	exit();
}

$dbcharset = $lang['commonDBCharset'];
header("Content-Type: text/html; charset={$lang['commonCharset']}");
$installfile = basename(__FILE__);
$configfile = HDWIKI_ROOT.'/config.php';
$logofile = HDWIKI_ROOT.'/style/default/logo.gif';

$sqlfile = HDWIKI_ROOT.'/install/hdwiki.sql';
if (!is_readable($sqlfile)) {
	exit ($strDBNoExists);
}

require HDWIKI_ROOT.'/install/install_func.php';
if (''==$step)
	$step = 1;
$arrTitle = array (
	"",
	$lang['commonLicenseInfo'],
	$lang['commonSystemCheck'],
	$lang['commonDatabaseSetup'],
	$lang['commonAdministratorSetup'],
	$lang['commonInstallComplete']
);
$arrStep = range(0, 5);

$nextStep = $step +1;
$prevStep = $step -1;
if($step==3){
	$nextStep=$step;
	$prevStep=$step;
}
$nextAccess = 1;

$uploadsDir = HDWIKI_ROOT.'/uploads';
$userfaceDir = HDWIKI_ROOT.'/uploads/userface';
$dataDir = HDWIKI_ROOT.'/data';
$pluginDir =HDWIKI_ROOT.'/plugins';
$site_url="http://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-20);
			
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $lang['commonInstallTitle']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['commonCharset']?>">
<meta content="noindex, nofollow" name="robots">
<link rel="stylesheet" href="images/install.css" type="text/css" media="screen,projection" />
<script language="JavaScript" type="text/javascript">
	function selectlang(lang){
		var selectlang = document.getElementById(lang);
		var curstep = <?php echo $step?>;
		var langvalue = selectlang.options[selectlang.selectedIndex].value;
		window.location = "install.php?step="+curstep+"&lang="+langvalue;
	}
</script>
</head>
<body>
<div id="container">
<div id="header">
<div id="logo"></div>
<div id="topheader">
<p><strong>HDWiki V<?php echo HDWIKI_VERSION?> Release <?php echo HDWIKI_RELEASE?></strong></p>
<p><?php echo $lang['commonSetupLanguage'] ?>
<select id="lang" name="lang" onchange="selectlang('lang');">
	<option value="zh"<?php  if('zh' == $lang_name) { ?> selected="selected"<?php } ?>> <?php echo $lang['zh']?></option>
</select>
</p>
</div>
</div>
<div id="content-wrap">
<div id="menu">
<ul class="sidemenu">
<li class="navtitle"><?php echo $lang['commonSetupNavigate']?></li>
<?php
$steptotal = count($arrTitle);
for ($i = 1; $i < $steptotal; $i++) {
	if ($step >= $arrStep[$i]) {
		if($step==$i) {
			$href1 = "<li class=\"sidemenubg\">";
			$href2 = "</li>";
		}else{
			$href1 = "<li><a href='$installfile?step=" . $arrStep[$i] . "'>";
			$href2 = "</a></li>";
		}

	} else {
		$href1 = "<li><a>";
		$href2 = "</a></li>";
	}
?>
                    <?php echo $href1.$i.". ".$arrTitle[$i].$href2?>
					<?php } ?>
					</ul>
					<p class="lbox"> <?php echo $lang['tipLeftHelp']?></p>
</div>
<div id="main">
       	<?php if($step!=6){?><form name="settingsform" method="post" action="<?php echo $installfile; ?>"><?php }?>
      	<?php switch ($step) {
		case 1 :
			if ($msg) {
				$str = "<p>" . $msg . "</p>";
			}
			if ($nextAccess == 1)
				$str = "<div id=\"tips\"><div class=\"log\">{$lang['step1ReadLicense']}</div><div class=\"mes\"><div align=\"center\"><textarea style=\"width: 94%; height: 300px;\">" . $lang['step1LicenseInfo'] . "</textarea></div><br /><div align=\"center\"><input type=\"submit\" value=\"{$lang['step1Agree']}\" class=\"inbut1\">    <input type=\"button\" value=\"{$lang['step1Disagree']}\" class=\"inbut\" onclick=\"javascript:window.close();\"></div></div>";
			break;
		case 2 :

			$fileConfigAccess = file_writeable($configfile);
			$filelogoAccess=file_writeable($logofile);
			
			$dirUploadsAccess = file_writeable($uploadsDir);
			$dirUserfaceAccess = file_writeable($userfaceDir);
			$dirDataAccess = file_writeable($dataDir);
			$dirPluginAccess = file_writeable($pluginDir);

			if(@ini_get("file_uploads")) {
				$max_size = @ini_get(upload_max_filesize);
				$curr_upload_status = "<font class=\"s4_color\">{$lang['step2AttachAllowSize']}: $max_size</font>";
			} else {
				$curr_upload_status = "<font color='red'>{$lang['step2AttachDisabled']}</font>";
				$msg .= "<span class='err'>{$lang['step2AttachDisabledTip']}</span><br>";
				$nextAccess=0;
			}

			$curr_php_version = PHP_VERSION;

			if ($curr_php_version < '4.1.0') {
				$curr_php_version = "$curr_php_version <font color='red'>{$$lang['step2PHPVersionTooLowTip']}</font>";
				$nextAccess = 0;
			}

			if (!function_exists('mysql_connect')) {
				$MySQLVersion = "<font color='s3_color'>{$lang['commonUnsupport']}</font>";
				$nextAccess = 0;
			} else {
				$MySQLVersion = "<font class='s2_color'>{$lang['commonSupport']}</font>";;
			}

			$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

			$os = strtoupper(substr(PHP_OS, 0, 3));
			$curOs = PHP_OS;
			if ($fileConfigAccess) {
				$fileConfigAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$fileConfigAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			if ($filelogoAccess) {
				$filelogoAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$filelogoAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			if ($dirUploadsAccess) {
				$dirUploadsAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$dirUploadsAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			if ($dirUserfaceAccess) {
				$dirUserfaceAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$dirUserfaceAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			if ($dirDataAccess ) {
				$dirDataAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$dirDataAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			if ($dirPluginAccess ) {
				$dirPluginAccessTip = "<font class='s1_color'>{$lang['commonWriteable']}</font>";
			}else{
				$dirPluginAccessTip = "<font class='s3_color'>{$lang['commonNotWriteable']}</font>";
				$nextAccess = 0;
			}
			$str = $str."<div id=\"tips\">{$lang['step2Tip']}</div>";

			$str = $str."<div id=\"wrapper\">
  <table class=\"table_nav\">
    <tr class=\"nav_bar\">
      <td></td>
      <td>HDWiki {$lang['commonConfigRequire']}</td>
      <td>HDWiki {$lang['commonConfigOptimized']}</td>
      <td>{$lang['commonConfigCurrent']}</td>
    </tr>
    <tr>
      <td>{$lang['commonOS']}</td>
      <td>{$lang['commonUnlimited']}</td>
      <td class=\"s1_color\">UNIX/Linux/FreeBSD </td>
      <td class=\"s4_color\">$curOs</td>
    </tr>
    <tr>
      <td>PHP {$lang['commonVersion']}</td>
      <td>4.1.0+ </td>
      <td class=\"s1_color\">5.2.2+</td>
      <td class=\"s2_color\">$curr_php_version</td>
    </tr>
    <tr>
      <td>{$lang['commonAttachUpload']}</td>
      <td>{$lang['commonUnlimited']}</td>
      <td class=\"s1_color\">{$lang['commonAllow']}</td>
      <td >$curr_upload_status</td>
    </tr>
    <tr>
      <td>MySQL {$lang['commonSupport']}</td>
      <td>3.23+</td>
      <td class=\"s1_color\">{$lang['commonSupport']}</td>
      <td>$MySQLVersion</td>
    </tr>
    <tr>
      <td>{$lang['commonDiskSpace']}</td>
      <td>10M+</td>
      <td class=\"s1_color\">{$lang['commonUnlimited']}</td>
      <td class=\"s4_color\">$curr_disk_space</td>
    </tr>
  </table>
</div>";

$str = $str."<div id=\"wrapper1\">
						<table class=\"table_nav\">
    <tr class=\"nav_bar\">
      <td>{$lang['commonDirName']}</td>
      <td>{$lang['commonDirDescribe']}</td>
      <td>{$lang['commonStateOptimized']}</td>
      <td>{$lang['commonStateCurrent']}</td>
    </tr>
    <tr>
      <td>./uploads</td>
      <td>{$lang['commonDirAttach']}</td>
      <td class=\"s1_color\">{$lang['commonDirPower']} {$lang['commonWriteable']}</td>
      <td>$dirUploadsAccessTip</td>
    </tr>
    <tr>
      <td>./uploads/userface</td>
      <td>{$lang['commonDirUserface']}</td>
      <td class=\"s1_color\">{$lang['commonDirPower']} {$lang['commonWriteable']}</td>
      <td>$dirUserfaceAccessTip</td>
    </tr>
    <tr>
      <td>./data</td>
      <td>{$lang['commonDirSysData']}</td>
      <td class=\"s1_color\">{$lang['commonDirPower']} {$lang['commonWriteable']}</td>
      <td>$dirDataAccessTip</td>
    </tr>
    <tr>
      <td>./plugins</td>
      <td>{$lang['commonDirSysPlugin']}</td>
      <td class=\"s1_color\">{$lang['commonDirPower']} {$lang['commonWriteable']}</td>
      <td>$dirPluginAccessTip</td>
    </tr>
    <tr>
      <td>./config.php</td>
      <td>{$lang['commonFileConfig']}</td>
      <td class=\"s1_color\">{$lang['commonFilePower']} {$lang['commonWriteable']}</td>
      <td>$fileConfigAccessTip</td>
    </tr>
    <tr>
      <td>./style/default/logo.gif</td>
      <td>{$lang['commonFileLogo']}</td>
      <td class=\"s1_color\">{$lang['commonFilePower']} {$lang['commonWriteable']}</td>
      <td>$filelogoAccessTip</td>
    </tr>
  </table></div>";
			break;
		case 3 :
			$saveconfig=$_REQUEST['saveconfig'];
			if($saveconfig=='1'){
				//db parameter
				$dbhost = trim($_POST['dbhost']);
				$dbuser = trim($_POST['dbuser']);
				$dbpassword = trim($_POST['dbpassword']);
				$dbname = trim($_POST['dbname']);
				$table_prefix = trim($_POST['table_prefix']);

				if ($dbhost == "" or $dbuser == "" or $dbname == "" or $table_prefix == "") {
					$msg .= "<SPAN class=err>{$lang['step3IsNull']}</span><br />";
					$nextAccess = 0;
				}

				if (strstr($table_prefix, '.') and $nextAccess == 1) {
					$msg .= "<SPAN class=err>{$lang['step3DBPrefix']}</span><br />";
					$nextAccess = 0;
				}

				if ($nextAccess == 1) {
					if(!@mysql_connect($dbhost, $dbuser, $dbpassword)) {
						$msg .= '<SPAN class=err>'.$lang['step3NoConnDB'].'</span>';
						$nextAccess = 0;
					} else {
						if(mysql_get_server_info() > '4.1') {
							mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET $dbcharset");
						} else {
							mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`");
						}
						if(mysql_errno()) {
							$msg .= "<SPAN class=err>{$lang['step3DBNoPower']}</span><br />";
							$nextAccess = 0;
						}

						mysql_close();
					}
				}

				if ($nextAccess == 1) {
					if (is_writeable($configfile) || (!file_exists($configfile))) {
$configcontent = "<?php
		define('DB_HOST', '".$dbhost."');
		define('DB_USER', '".$dbuser."');
		define('DB_PW', '".$dbpassword."');
		define('DB_NAME', '".$dbname."');
		define('DB_CHARSET', '".$dbcharset."');
		define('DB_TABLEPRE', '".$table_prefix."');
		define('DB_CONNECT', 0);
		define('WIKI_CHARSET', '".$lang['commonCharset']."');
?>";
						$fp1 = fopen($configfile, 'wbt');
						$bytes=fwrite($fp1, $configcontent);
						@ fclose($fp1);
					} else {
						if (!file_exists($configfile)) {
							$msg .= "<SPAN class=err>{$lang['step3DBConfigWriteErrorTip']}</span><br />";
							$nextAccess = 0;
						}else{
							$msg .= "<SPAN class=err>{$lang['step3DBConfigNotWriteTip']}</span><br />";
							$nextAccess = 0;
						}
					}
				}

				if ($nextAccess == 0) {
					$msg .= "<br /><SPAN class=err>{$lang['tipGenErrInfo']}</span><br /><br />";
					$msg .= "</p>\n";
				} else {
					echo   "<script>window.location=\"{$_SERVER['PHP_SELF']}?step=4\";</script>";
				}
				$str=$str.$msg;
			}else{
				if (PHP_VERSION < '4.0.6') {
					$msg .= "<SPAN class=err>{$lang['step2PHPVersionTooLowTip']}</span><br /><br />";
					$nextAccess = 0;

				}
				if (!function_exists('mysql_connect')) {
					$msg .= "<SPAN class=err>{$lang['step3MySQLExtErrorTip']}</span><br /><br />";
					$nextAccess = 0;
				}
				if ($msg) {
					$str = "<p>" . $msg . "</p>";
				}

				if ($nextAccess == 1) {
					$str = "<div id=\"tips\">{$lang['step3Tip']}</div>";

					$str .= "<div id=\"wrapper\">
	<div class=\"col\">
	<h3>{$lang['commonSetupOption']}        {$lang['commonSetupParameterValue']}        {$lang['commonSetupComment']}</h3>
	<p><span class=\"red\">{$lang['step3MySqlHost']}: </span> <input name=\"dbhost\" value=\"localhost:3306\" type=\"text\" size=\"20\" />    {$lang['step3MySqlHostComment']}</p>
	<p>{$lang['step3MySqlUser']}: <input name=\"dbuser\" value=\"\" type=\"text\" size=\"20\" />    {$lang['step3MySqlUserComment']}</p>
	<p>{$lang['step3MySqlPass']}:&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"dbpassword\" value=\"\" type=\"password\" size=\"20\" />    {$lang['step3MySqlPassComment']}</p>
	<p>{$lang['step3MySqlDBName']}:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"dbname\" value=\"wiki\" type=\"text\" size=\"20\" />    {$lang['step3MySqlDBNameComment']}</p>
	<p><span class=\"red\">{$lang['step3MySqlDBTablePrefix']}:</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"table_prefix\" value=\"wiki_\" type=\"text\" size=\"20\" />    {$lang['step3MySqlDBTablePrefixComment']}</p>
	</div></div><input type='hidden' name='saveconfig' value='1'/>";
				}
				$prevStep=$prevStep-1;
			}

			break;
		case 4 :
				require_once HDWIKI_ROOT.'/config.php';
				if(!@mysql_connect(DB_HOST, DB_USER, DB_PW)) {
					$msg .= '<SPAN class=err>'.$lang['step3NoConnDB'].'</span><br/>';
					$nextAccess = 0;
				} else {
					$curr_mysql_version = mysql_get_server_info();
					if($curr_mysql_version < '3.23') {
						$msg .= '<SPAN class=err>'.$lang['step3MySqlVersionToLowTip'].'</span><br/>';
						$nextAccess = 0;
					}
					$islink=mysql_select_db(DB_NAME);
					if($islink){
						$result = mysql_query("SELECT COUNT(*) FROM ".DB_TABLEPRE."setting");
						if($result) {
							$msg .= '<SPAN class=err>'.$lang['step3DBAlreadyExist'].'</span><br/>';
							$alert = " onClick=\"return confirm('{$lang['step3DBDropTableConfirm']}');\"";
						}
					}
				}

				$str = "<div id=\"tips\">" .
							"<div class=\"log\">{$lang['commonInfotip']}</div><div class=\"mes\"><p>{$lang['step4Tip']}<br/>$msg</p></div>
							</div>";
				$str =$str."<div id=\"wrapper\"><div class=\"col\">" .
	"<h3>{$lang['commonSetupOption']}{$lang['commonSetupParameterValue']}{$lang['commonSetupComment']}</h3>
	<p><span class=\"red\">{$lang['step4AdministratorNick']}:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input name=\"admin_master\" value=\"\" type=\"text\" size=\"20\" />{$lang['step4AdministratorNickComment']}</p>
	<p><span class=\"red\">{$lang['step4AdministratorEmail']}:&nbsp;</span><input name=\"admin_email\" value=\"\" type=\"text\" size=\"20\" />{$lang['step4AdministratorEmailComment']}</p>
	<p><span class=\"red\">{$lang['step4AdministratorPass']}:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input name=\"admin_pw\" value=\"\" type=\"password\" size=\"20\" />{$lang['step4AdministratorPassComment']}</p>
	<p><span class=\"red\">{$lang['step4AdministratorRePass']}:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input name=\"admin_pw2\" value=\"\" type=\"password\" size=\"20\" />{$lang['step4AdministratorRePassComment']}</p>
	</div></div>";
			 
			break;
		case 5 :
			$admin_pw = encode($_POST['admin_pw']);
			$admin_pw2 = encode($_POST['admin_pw2']);
			$admin_email = encode(trim($_POST['admin_email']));
			$admin_master = encode(trim($_POST['admin_master']));
			$site_icp = "";

			if ($admin_pw == "" or $admin_pw2 == "" or $admin_email == "" or $admin_master == "") {
				$str = "<SPAN class=err>{$lang['step3IsNull']}</span>";
				$nextAccess = 0;
			}
			elseif (strlen($admin_pw) < 6) {
				$str = "<SPAN class=err>{$lang['step4AdministratorPassTooShortTip']}</span>";
				$nextAccess = 0;
			}
			elseif ($admin_pw != $admin_pw2) {
				$str = "<SPAN class=err>{$lang['step4AdministratorPassNotSame']}</span>";
				$nextAccess = 0;
			}
			elseif (check_email($admin_email) == 0) {
				$str = "<SPAN class=err>{$lang['step4AdministratorEmailInvalid']}</span>";
				$nextAccess = 0;
			} else {
				if ($nextAccess == 1) {

					require_once HDWIKI_ROOT.'/config.php';
					require_once HDWIKI_ROOT.'/lib/hddb.class.php';

					$db = new hddb(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET);
 
					$fp = fopen($sqlfile, 'rb');
					$sql = fread($fp, filesize($sqlfile));
					fclose($fp);
					$strcretip=runquery($sql);

 
					if($nextAccess==1) $msg .= "{$lang['step4ImportDefaultData']} <br />";

					$admin_email = strtolower($admin_email);
					$adminpwd = md5($admin_pw);
					$regtime=time();
					$site_name = $lang['step4DefaultSiteName'];



$installsql = <<<EOT

INSERT INTO wiki_usergroup (`groupid`, `grouptitle`, `regulars`, `default`, `type`, `creditslower`, `creditshigher`, `stars`, `color`, `groupavatar`) VALUES
(1, '匿名用户', 'index-default|index-setstyle|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-getpass|user-code|user-space|user-clearcookies|synonym-view', 'index-default|index-setstyle|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-getpass|user-code|user-space|user-clearcookies|synonym-view', 1, 0, 0, 0, '', ''),
(3, '词条管理员', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|doc-remove|comment-delete|comment-add|comment-edit|edition-remove|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|admin_main-logout|admin_main-mainframe|admin_main-update|admin_doc-default|admin_doc-search|admin_doc-audit|admin_doc-recommend|admin_doc-lock|admin_doc-unlock|admin_doc-remove|admin_doc-move|admin_doc-rename|admin_comment-default|admin_comment-search|admin_comment-delete|admin_attachment-default|admin_attachment-search|admin_attachment-remove|admin_attachment-download|admin_focus-focuslist|admin_focus-remove|admin_focus-reorder|admin_focus-edit|admin_focus-updateimg|admin_focus-numset|admin_tag-hottag|admin_word-default|admin_synonym-default|admin_synonym-search|admin_synonym-delete|admin_synonym-save|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|doc-remove|comment-delete|comment-add|comment-edit|edition-remove|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|admin_main-logout|admin_main-mainframe|admin_main-update|admin_doc-default|admin_doc-search|admin_doc-audit|admin_doc-recommend|admin_doc-lock|admin_doc-unlock|admin_doc-remove|admin_doc-move|admin_doc-rename|admin_comment-default|admin_comment-search|admin_comment-delete|admin_attachment-default|admin_attachment-search|admin_attachment-remove|admin_attachment-download|admin_focus-focuslist|admin_focus-remove|admin_focus-reorder|admin_focus-edit|admin_focus-updateimg|admin_focus-numset|admin_tag-hottag|admin_word-default|admin_synonym-default|admin_synonym-search|admin_synonym-delete|admin_synonym-save|exchange-default', 1, 0, 0, 2, '', ''),
(4, '超级管理员', '', '', 1, 0, 0, 3, '', ''),
(5, '白丁', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|doc-edit|doc-sandbox|comment-add|synonym-view|synonym-savesynonym|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|doc-edit|doc-sandbox|comment-add|synonym-view|synonym-savesynonym|exchange-default', 2, -999999, 0, 0, '', ''),
(2, '书童', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 2, 0, 100, 1, '', ''),
(7, '秀才', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 2, 100, 300, 4, '', ''),
(8, '举人', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|exchange-default', 2, 300, 600, 5, '', ''),
(9, '进士', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 2, 600, 1000, 8, '', ''),
(10, '状元', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 2, 1000, 1500, 16, '', ''),
(11, '翰林', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|comment-add|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 2, 1500, 2100, 18, '', ''),
(12, '太傅', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-changename|doc-lock|doc-unlock|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-changename|doc-lock|doc-unlock|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 2, 2100, 2800, 24, '', ''),
(13, '圣贤', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 2, 2800, 999999999, 33, '', ''),
(14, '荣誉宰相', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-changename|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-changename|doc-audit|comment-add|comment-edit|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|exchange-default', 0, 0, 0, 5, '', ''),
(15, '管理员', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|doc-remove|comment-delete|comment-add|comment-edit|edition-remove|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|admin_banned-default|admin_friendlink-default|admin_friendlink-add|admin_friendlink-edit|admin_friendlink-remove|admin_friendlink-updateorder|admin_main-login|admin_main-default|admin_main-logout|admin_main-mainframe|admin_main-update|admin_setting-base|admin_setting-logo|admin_setting-credit|admin_setting-seo|admin_setting-cache|admin_setting-updatecache|admin_setting-removecache|admin_setting-attachment|admin_setting-mail|admin_task-default|admin_task-taskstatus|admin_task-edittask|admin_task-run|admin_log-default|admin_setting-notice|admin_plugin-list|admin_plugin-default|admin_plugin-manage|admin_plugin-install|admin_plugin-uninstall|admin_plugin-start|admin_plugin-stop|admin_plugin-setvar|admin_plugin-hook|admin_doc-default|admin_doc-search|admin_doc-audit|admin_doc-recommend|admin_doc-lock|admin_doc-unlock|admin_doc-remove|admin_doc-move|admin_doc-rename|admin_comment-default|admin_comment-search|admin_comment-delete|admin_attachment-default|admin_attachment-search|admin_attachment-remove|admin_attachment-download|admin_focus-focuslist|admin_focus-remove|admin_focus-reorder|admin_focus-edit|admin_focus-updateimg|admin_focus-numset|admin_tag-hottag|admin_word-default|admin_synonym-default|admin_synonym-search|admin_synonym-delete|admin_synonym-save|admin_user-default|admin_user-list|admin_user-add|admin_user-edit|admin_usergroup-default|admin_usergroup-list|admin_category-default|admin_category-list|admin_category-add|admin_category-edit|admin_category-reorder|admin_statistics-stand|admin_statistics-cat_toplist|admin_statistics-doc_toplist|admin_statistics-edit_toplist|admin_statistics-credit_toplist|admin_statistics-admin_team|exchange-default', 'index-default|index-setstyle|attachment-download|category-default|category-ajax|category-view|category-letter|list-letter|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-editimage|user-getpass|user-code|user-space|user-clearcookies|user-cutoutimage|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|attachment-uploadimg|attachment-remove|doc-create|doc-verify|doc-edit|doc-editsection|doc-refresheditlock|doc-unseteditlock|doc-sandbox|doc-setfocus|doc-getcategroytree|doc-changecategory|doc-changename|doc-lock|doc-unlock|doc-audit|doc-remove|comment-delete|comment-add|comment-edit|edition-remove|edition-excellent|edition-unexcellent|edition-copy|synonym-removesynonym|synonym-view|synonym-savesynonym|doc-immunity|admin_banned-default|admin_friendlink-default|admin_friendlink-add|admin_friendlink-edit|admin_friendlink-remove|admin_friendlink-updateorder|admin_main-login|admin_main-default|admin_main-logout|admin_main-mainframe|admin_main-update|admin_setting-base|admin_setting-logo|admin_setting-credit|admin_setting-seo|admin_setting-cache|admin_setting-updatecache|admin_setting-removecache|admin_setting-attachment|admin_setting-mail|admin_task-default|admin_task-taskstatus|admin_task-edittask|admin_task-run|admin_log-default|admin_setting-notice|admin_plugin-list|admin_plugin-default|admin_plugin-manage|admin_plugin-install|admin_plugin-uninstall|admin_plugin-start|admin_plugin-stop|admin_plugin-setvar|admin_plugin-hook|admin_doc-default|admin_doc-search|admin_doc-audit|admin_doc-recommend|admin_doc-lock|admin_doc-unlock|admin_doc-remove|admin_doc-move|admin_doc-rename|admin_comment-default|admin_comment-search|admin_comment-delete|admin_attachment-default|admin_attachment-search|admin_attachment-remove|admin_attachment-download|admin_focus-focuslist|admin_focus-remove|admin_focus-reorder|admin_focus-edit|admin_focus-updateimg|admin_focus-numset|admin_tag-hottag|admin_word-default|admin_synonym-default|admin_synonym-search|admin_synonym-delete|admin_synonym-save|admin_user-default|admin_user-list|admin_user-add|admin_user-edit|admin_usergroup-default|admin_usergroup-list|admin_category-default|admin_category-list|admin_category-add|admin_category-edit|admin_category-reorder|admin_statistics-stand|admin_statistics-cat_toplist|admin_statistics-doc_toplist|admin_statistics-edit_toplist|admin_statistics-credit_toplist|admin_statistics-admin_team|exchange-default', 1, 0, 0, 2, '', ''),
(16, '被删除', 'index-default|index-setstyle|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-getpass|user-code|user-space|user-clearcookies|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|synonym-view', 'index-default|index-setstyle|category-default|category-ajax|category-view|category-letter|list-letter|list-default|list-recentupdate|list-popularity|list-focus|doc-view|doc-innerlink|doc-summary|doc-editor|comment-view|edition-list|edition-view|edition-compare|search-default|search-fulltext|search-tag|list-weekuserlist|list-allcredit|list-rss|doc-random|doc-vote|user-register|user-login|user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail|user-logout|user-profile|user-editprofile|user-editpass|user-getpass|user-code|user-space|user-clearcookies|pms-default|pms-box|pms-setread|pms-remove|pms-sendmessage|pms-checkrecipient|pms-blacklist|synonym-view', 1, 0, 0, 1, '', '');

REPLACE INTO wiki_setting(variable,value) VALUES
	('site_name', '{$site_name}'),
	('site_url', '{$site_url}'),
	('site_icp', ''),
	('cookie_domain', ''),
	('cookie_pre', 'hd_'),
	('app_url', 'http://kaiyuan.hudong.com'),

	('verify_doc', '0'),
	('cat_intro_set','0'),
	('time_offset','8'),
	('time_diff','0'),
	('time_format',''),
	('date_format','m-d'),
	('style_user_select','1'),

	('credit_create', '5'),
	('credit_edit', '3'),
	('credit_upload', '2'),
	('credit_register', '20'),
	('credit_login', '1'),
	('credit_pms','1'),
	('credit_comment','2'),
	('list_prepage', '20'),
	
	('list_focus', '10'),
	('list_recentupdate', '10'),
	('list_weekuser', '10'),
	('list_allcredit', '10'),
	('list_popularity', '10'),
	('list_letter', '10'),
	('category_view', '10'),
	('category_letter', '10'),
	('index_commend', '5'),
	('index_recentupdate', '15'),
	('index_weekuser', '10'),
	('index_topuser', '10'),
	('index_recentcomment', '8'),

	('seo_prefix', 'index.php?'),
	('seo_separator', '-'),
	('seo_suffix', ''),
	('seo_title', ''),
	('seo_keywords', ''),
	('seo_description', ''),
	('seo_headers', ''),
	('seo_type', '0'),

	('attachment_size', '2048'),
	('attachment_open', '0'),
	('attachment_type', 'jpg|jpeg|bmp|gif|png|gz|bz2|zip|rar|doc|ppt|mp3|xls|txt|swf|flv|php'),
		
	('index_cache_time', '300'),
	('list_cache_time', '300'),
	('doc_cache_time', '300'),
	('tpl_name', 'default'),
	('style_name','default'),
	('lang_name','zh'),
	('auto_picture','0'),
	('checkcode','3'),
	('sandbox_id',''),
	('site_notice','本站是由<span style="color:#FF0000">1</span>位网民共同撰写的百科全书，目前已收录词条<span style="color:#FF0000"> 0</span>个'),

	
	('search_time', '1'),
	('search_tip_switch', '1'),
	('close_register_reason', '对不起，网站暂停注册！给您带来的不便还请谅解。'),
	('error_names', '管理员'),
	('register_check', '0'),
	('name_min_length', '3'),
	('name_max_length', '15'),
	('register_least_minute', '30'),
	('allow_register', '1'),
	('close_website', '0'),
	('close_website_reason', '网站暂时关闭，马上就会恢复，请稍候关注，谢谢。');
				 
INSERT INTO wiki_category(`name`,`navigation`,`docs`) VALUES ('Default','a:1:{i:0;a:2:{s:3:"cid";s:1:"1";s:4:"name";s:7:"Default";}}',0);
INSERT INTO wiki_user(email,username,`password`,`lastip`,groupid,credits,regtime) VALUES ('{$admin_email}', '{$admin_master}', '$adminpwd', '{$_SERVER[REMOTE_ADDR]}',4,20,'$regtime') ;
INSERT INTO wiki_creditdetail(`uid`,`operation`,`credit`,`time`) VALUES (1,'user-register',20,$regtime);


INSERT INTO wiki_regular (`id`, `name`, `regular`, `type`, `regulargroupid`) VALUES
	(1, '﻿访问首页', 'index-default', 0, 18),
	(2, '更改风格', 'index-setstyle', 0, 18),
	(3, '图片上传', 'attachment-uploadimg', 0, 20),
	(4, '附件下载（附件）', 'attachment-download', 0, 18),
	(5, '删除附件（附件）', 'attachment-remove', 0, 20),
	(6, '浏览分类', 'category-default|category-ajax', 0, 18),
	(7, '浏览具体分类', 'category-view', 0, 18),
	(8, '分类下字母顺序浏览', 'category-letter', 0, 18),
	(9, '按字母顺序浏览（排行榜）', 'list-letter', 0, 18),
	(10, '最近更新词条（排行榜）', 'list-default|list-recentupdate', 0, 18),
	(13, '用户人气词条列表(排行榜)', 'list-popularity', 0, 18),
	(14, '推荐词条列表(排行榜)', 'list-focus', 0, 18),
	(15, '浏览词条', 'doc-view', 0, 18),
	(16, '创建词条', 'doc-create', 0, 20),
	(17, '验证词条是否存在', 'doc-verify', 0, 20),
	(18, '编辑词条', 'doc-edit', 0, 20),
	(19, '分段编辑词条', 'doc-editsection', 0, 20),
	(20, '刷新编辑锁', 'doc-refresheditlock', 0, 20),
	(21, '取消编辑锁', 'doc-unseteditlock', 0, 20),
	(22, '浏览词条内链', 'doc-innerlink', 0, 18),
	(23, '浏览词条摘要', 'doc-summary', 0, 18),
	(24, '浏览词条贡献者', 'doc-editor', 0, 18),
	(25, '沙盒', 'doc-sandbox', 0, 20),
	(26, '设置推荐词条（前台词条管理）', 'doc-setfocus', 0, 20),
	(27, '移动词条分类（前台词条管理）', 'doc-getcategroytree|doc-changecategory', 0, 20),
	(28, '更改词条名（前台词条管理）', 'doc-changename', 0, 20),
	(29, '锁定词条（前台词条管理）', 'doc-lock', 0, 20),
	(30, '解除词条锁定（前台词条管理）', 'doc-unlock', 0, 20),
	(31, '审核词条（前台词条管理）', 'doc-audit', 0, 20),
	(32, '删除词条（前台词条管理）', 'doc-remove', 0, 20),
	(33, '查看评论', 'comment-view', 0, 18),
	(34, '删除评论（前台评论管理）', 'comment-delete', 0, 20),
	(35, '添加评论（前台评论管理）', 'comment-add', 0, 20),
	(36, '编辑评论（前台评论管理）', 'comment-edit', 0, 20),
	(37, '版本列表（历史版本）', 'edition-list', 0, 18),
	(38, '浏览版本（历史版本）', 'edition-view', 0, 18),
	(39, '版本对比（历史版本）', 'edition-compare', 0, 18),
	(40, '删除版本（历史版本）', 'edition-remove', 0, 20),
	(41, '优秀版本（历史版本）', 'edition-excellent', 0, 20),
	(42, '取消优秀（历史版本）', 'edition-unexcellent', 0, 20),
	(43, '复制版本（历史版本）', 'edition-copy', 0, 20),
	(44, '进入词条（搜索）', 'search-default', 0, 18),
	(45, '全文搜索（搜索）', 'search-fulltext', 0, 18),
	(46, 'TAG搜索（搜索）', 'search-tag', 0, 18),
	(47, '用户注册（用户）', 'user-register', 0, 19),
	(48, '用户登录（用户）', 'user-login', 0, 19),
	(49, '检测用户（用户）', 'user-check|user-checkusername|user-checkcode|user-checkpassword|user-checkoldpass|user-checkemail', 0, 19),
	(50, '用户注销（用户）', 'user-logout', 0, 19),
	(51, '个人信息（用户）', 'user-profile', 0, 19),
	(52, '个人信息设置（用户）', 'user-editprofile', 0, 19),
	(53, '修改密码（用户）', 'user-editpass', 0, 19),
	(54, '修改头像（用户）', 'user-editimage', 0, 19),
	(55, '找回密码（用户）', 'user-getpass', 0, 19),
	(56, '显示验证码（用户）', 'user-code', 0, 19),
	(57, '个人空间（用户）', 'user-space', 0, 19),
	(58, '清除cookies（用户）', 'user-clearcookies', 0, 19),
	(59, 'IP禁止', 'admin_banned-default', 1, 21),
	(60, '分类管理列表（分类管理）', 'admin_category-default|admin_category-list', 1, 25),
	(61, '添加分类（分类管理）', 'admin_category-add', 1, 25),
	(62, '编辑分类（分类管理）', 'admin_category-edit', 1, 25),
	(63, '删除分类（分类管理）', 'admin_category-remove', 1, 25),
	(64, '分类排序（分类管理）', 'admin_category-reorder', 1, 25),
	(65, '分类合并（分类管理）', 'admin_category-merge', 1, 25),
	(66, '数据库备份（数据库管理）', 'admin_db-backup', 1, 27),
	(67, '数据库还原（数据库管理）', 'admin_db-import', 1, 27),
	(68, '删除数据文件（数据库管理）', 'admin_db-remove', 1, 27),
	(69, '数据库列表（数据库管理）', 'admin_db-tablelist', 1, 27),
	(70, '数据库优化（数据库管理）', 'admin_db-optimize', 1, 27),
	(71, '数据库修复（数据库管理）', 'admin_db-repair', 1, 27),
	(72, '下载数据文件（数据库管理）', 'admin_db-downloadfile', 1, 27),
	(73, '词条列表（管理词条）', 'admin_doc-default', 1, 23),
	(74, '搜索词条（管理词条）', 'admin_doc-search', 1, 23),
	(75, '审核词条（管理词条）', 'admin_doc-audit', 1, 23),
	(76, '推荐词条（管理词条）', 'admin_doc-recommend', 1, 23),
	(77, '锁定词条（管理词条）', 'admin_doc-lock', 1, 23),
	(78, '解锁词条（管理词条）', 'admin_doc-unlock', 1, 23),
	(79, '删除词条（管理词条）', 'admin_doc-remove', 1, 23),
	(80, '移动词条（管理词条）', 'admin_doc-move', 1, 23),
	(81, '重命名词条（管理词条）', 'admin_doc-rename', 1, 23),
	(82, '搜索评论（后台管理评论）', 'admin_comment-default|admin_comment-search', 1, 23),
	(83, '删除评论（后台管理评论）', 'admin_comment-delete', 1, 23),
	(84, '搜索附件（后台管理附件）', 'admin_attachment-default|admin_attachment-search', 1, 23),
	(85, '删除附件（后台管理附件）', 'admin_attachment-remove', 1, 23),
	(86, '下载附件（后台管理附件）', 'admin_attachment-download', 1, 23),
	(87, '推荐词条列表（推荐词条）', 'admin_focus-focuslist', 1, 23),
	(88, '删除推荐词条（推荐词条）', 'admin_focus-remove', 1, 23),
	(89, '更改推荐词条顺序（推荐词条）', 'admin_focus-reorder', 1, 23),
	(90, '编辑推荐词条（推荐词条）', 'admin_focus-edit', 1, 23),
	(91, '更新图片（推荐词条）', 'admin_focus-updateimg', 1, 23),
	(92, '词条显示数量设置（推荐词条）', 'admin_focus-numset', 1, 23),
	(93, '友情链接列表（友情链接）', 'admin_friendlink-default', 1, 21),
	(94, '添加友情链接（友情链接）', 'admin_friendlink-add', 1, 21),
	(95, '编辑友情链接（友情链接）', 'admin_friendlink-edit', 1, 21),
	(96, '删除友情链接（友情链接）', 'admin_friendlink-remove', 1, 21),
	(97, '更新友情链接顺序（友情链接）', 'admin_friendlink-updateorder', 1, 21),
	(98, '语言列表（语言）', 'admin_language-default', 1, 26),
	(99, '添加语言（语言）', 'admin_language-addlanguage', 1, 26),
	(100, '删除语言（语言）', 'admin_language-removelanguage', 1, 26),
	(101, '更新语言（语言）', 'admin_language-updatelanguage', 1, 26),
	(102, '设置默认语言（语言）', 'admin_language-setdefaultlanguage', 1, 26),
	(103, '管理员登录（后台登录）', 'admin_main-login|admin_main-default', 1, 21),
	(104, '管理员退出（后台登录）', 'admin_main-logout', 1, 21),
	(105, '后台框架（后台登录）', 'admin_main-mainframe', 1, 21),
	(106, '后台新版本提示（后台登录）', 'admin_main-update', 1, 21),
	(107, '插件列表（插件管理）', 'admin_plugin-list|admin_plugin-default|admin_plugin-manage', 1, 22),
	(108, '安装插件（插件管理）', 'admin_plugin-install', 1, 22),
	(109, '卸载插件（插件管理）', 'admin_plugin-uninstall', 1, 22),
	(110, '启用插件（插件管理）', 'admin_plugin-start', 1, 22),
	(111, '停用插件（插件管理）', 'admin_plugin-stop', 1, 22),
	(112, '插件变量（插件管理）', 'admin_plugin-setvar', 1, 22),
	(113, '插件钩子（插件管理）', 'admin_plugin-hook', 1, 22),
	(114, '规则列表(管理权限)', 'admin_regular-list|admin_regular-default', 1, 24),
	(115, '添加规则(管理权限)', 'admin_regular-add', 1, 24),
	(116, '编辑规则(管理权限)', 'admin_regular-edit', 1, 24),
	(117, '删除规则(管理权限)', 'admin_regular-remove', 1, 24),
	(118, '基本设置(网站管理)', 'admin_setting-base', 1, 21),
	(119, '上传logo(网站管理)', 'admin_setting-logo', 1, 21),
	(120, '积分设置(网站管理)', 'admin_setting-credit', 1, 21),
	(121, 'seo设置(网站管理)', 'admin_setting-seo', 1, 21),
	(122, '缓存页面(网站管理)', 'admin_setting-cache', 1, 21),
	(123, '更新缓存设置(网站管理)', 'admin_setting-updatecache', 1, 21),
	(124, '清除缓存(网站管理)', 'admin_setting-removecache', 1, 21),
	(125, '附件设置(网站管理)', 'admin_setting-attachment', 1, 21),
	(126, '邮件设置(网站管理)', 'admin_setting-mail', 1, 21),
	(127, '风格列表（风格）', 'admin_style-default', 1, 26),
	(128, '创建模版风格页面（风格）', 'admin_style-create', 1, 26),
	(129, '删除风格（风格）', 'admin_style-removestyle', 1, 26),
	(131, '设置默认风格（风格）', 'admin_style-setdefaultstyle', 1, 26),
	(132, '热门标签设置（热门标签）', 'admin_tag-hottag', 1, 23),
	(133, '列表|添加|删除（定时任务）', 'admin_task-default', 1, 21),
	(134, '启用|停用（定时任务）', 'admin_task-taskstatus', 1, 21),
	(135, '编辑定时任务（定时任务）', 'admin_task-edittask', 1, 21),
	(136, '执行定时任务（定时任务）', 'admin_task-run', 1, 21),
	(137, '用户列表（管理用户）', 'admin_user-default|admin_user-list', 1, 24),
	(138, '添加用户（管理用户）', 'admin_user-add', 1, 24),
	(139, '编辑用户（管理用户）', 'admin_user-edit', 1, 24),
	(140, '删除用户（管理用户）', 'admin_user-remove', 1, 24),
	(141, '用户组列表（管理用户组）', 'admin_usergroup-default|admin_usergroup-list', 1, 24),
	(142, '添加用户组（管理用户组）', 'admin_usergroup-add', 1, 24),
	(143, '编辑用户组（管理用户组）', 'admin_usergroup-edit', 1, 24),
	(144, '删除用户组（管理用户组）', 'admin_usergroup-remove', 1, 24),
	(145, '关键词过滤(词语过滤)', 'admin_word-default', 1, 23),
	(146, '裁剪图片', 'user-cutoutimage', 0, 19),
	(147, '上周贡献榜', 'list-weekuserlist', 0, 18),
	(148, '总贡献榜', 'list-allcredit', 0, 18),
	(149, '修改用户组(管理用户组)', 'admin_usergroup-change', 1, 24),
	(150, 'Rss订阅', 'list-rss', 0, 18),
	(151, '后台操作记录(网站管理)', 'admin_log-default', 1, 21),
	(152, '查收短消息', 'pms-default|pms-box|pms-setread', 0, 19),
	(153, '删除短消息', 'pms-remove', 0, 19),
	(154, '发送短消息', 'pms-sendmessage|pms-checkrecipient', 0, 19),
	(155, '忽略列表', 'pms-blacklist', 0, 19),
	(156, '站内公告(网站管理)', 'admin_setting-notice', 1, 21),
	(157, '删除同义词(前台同义词管理)', 'synonym-removesynonym', 0, 20),
	(158, '查看同义词(前台同义词管理)', 'synonym-view', 0, 20),
	(159, '编辑同义词(前台同义词管理)', 'synonym-savesynonym', 0, 20),
	(160, '同义词列表(后台同义词管理)', 'admin_synonym-default', 1, 23),
	(161, '搜索同义词(后台同义词管理)', 'admin_synonym-search', 1, 23),
	(162, '删除同义词(后台同义词管理)', 'admin_synonym-delete', 1, 23),
	(163, '编辑同义词(后台同义词管理)', 'admin_synonym-save', 1, 23),
	(164, '基本概况统计(后台统计)', 'admin_statistics-stand', 1, 28),
	(165, '分类排行榜(后台统计)', 'admin_statistics-cat_toplist', 1, 28),
	(166, '词条排行榜(后台统计)', 'admin_statistics-doc_toplist', 1, 28),
	(167, '编辑排行榜(后台统计)', 'admin_statistics-edit_toplist', 1, 28),
	(168, '积分排行榜(后台统计)', 'admin_statistics-credit_toplist', 1, 28),
	(169, '管理团队(后台统计)', 'admin_statistics-admin_team', 1, 28),
	(170, 'UC积分兑换', 'exchange-default', 2, 19),
	(174, '词条免检', 'doc-immunity', 0, 20),
	(176, '编辑模版文件（风格）', 'admin_style-editxml', 1, 26),
	(177, '编辑模版描述文件（风格）', 'admin_style-edit', 1, 26),
	(178, '读取模版文件（风格）', 'admin_style-readfile', 1, 26),
	(179, '保存模版文件（风格）', 'admin_style-savefile', 1, 26),
	(181, '卸载模版（风格）', 'admin_style-removestyle', 1, 26),
	(183, '可安装模版列表（风格）', 'admin_style-list', 1, 26),
	(184, '安装模版（风格）', 'admin_style-install', 1, 26),
	(185, '显示广告列表', 'admin_adv-default', 0, 21),
	(186, '设置广告加载方式', 'admin_adv-config', 0, 21),
	(187, '搜索广告(后台)', 'admin_adv-search', 0, 21),
	(188, '添加广告', 'admin_adv-add', 0, 21),
	(189, '编辑广告', 'admin_adv-edit', 0, 21),
	(190, '删除广告', 'admin_adv-remove', 0, 21),
	(191, '审核用户', 'admin_user-checkup', 0, 19),
	(192, '用户审核(取消)', 'admin_user-uncheckeduser', 0, 19),
	(193, '注册控制', 'admin_setting-baseregister', 0, 21),
	(201, '随便看看', 'doc-random', 0, 18),
	(202, '此词条对我有用', 'doc-vote', 0, 18),
	(203, '创建新风格页面', 'admin_style-add', 0, 26),
	(204, '创建新风格', 'admin_style-createstyle', 0, 26),
	(206, '频道列表（频道）', 'admin_channel-default', 1, 21),
	(207, '添加频道（频道）', 'admin_channel-add', 1, 21),
	(208, '编辑频道（频道）', 'admin_channel-edit', 1, 21),
	(209, '删除频道（频道）', 'admin_channel-remove', 1, 21),
	(210, '修改频道显示顺序（频道）', 'admin_channel-updateorder', 1, 21),
	(211, '频道列表（频道）', 'admin_channel-default', 1, 21),
	(212, '添加频道（频道）', 'admin_channel-add', 1, 21),
	(213, '编辑频道（频道）', 'admin_channel-edit', 1, 21),
	(214, '删除频道（频道）', 'admin_channel-remove', 1, 21),
	(215, '修改频道显示顺序（频道）', 'admin_channel-updateorder', 1, 21);


	
	
INSERT INTO wiki_language (`name`, `available`, `path`, `copyright`) VALUES 
	('简体中文', 1, 'zh', 'hudong.com');

INSERT INTO wiki_style (`name`, `available`, `path`, `copyright`, `css`) VALUES
	('默认风格', 1, 'default', 'hudong.com', 'a:18:{s:8:"bg_color";s:11:"transparent";s:14:"left_framcolor";s:7:"#e6e6e6";s:16:"leftitle_bgcolor";s:7:"#f7f7f8";s:18:"leftitle_framcolor";s:7:"#efefef";s:16:"middle_framcolor";s:7:"#eaf1f6";s:19:"middletitle_bgcolor";s:7:"#eaf6fd";s:21:"middletitle_framcolor";s:7:"#c4d2db";s:15:"right_framcolor";s:7:"#cef2e0";s:17:"rightitle_bgcolor";s:7:"#cef2e0";s:19:"rightitle_framcolor";s:7:"#a3bfb1";s:13:"nav_framcolor";s:7:"#cdcdcd";s:11:"nav_bgcolor";s:7:"#aaaeb1";s:13:"nav_linkcolor";s:4:"#fff";s:13:"nav_overcolor";s:4:"#ff0";s:8:"nav_size";s:4:"14px";s:10:"bg_imgname";s:11:"html_bg.jpg";s:13:"titbg_imgname";s:10:"col_bg.jpg";s:4:"path";s:7:"default";}');


INSERT INTO wiki_regulargroup (`id`, `title`, `size`, `type`) VALUES
	(18, '页面浏览', 0, 0),
	(19, '用户操作', 0, 0),
	(20, '词条管理', 0, 0),
	(21, '网站管理', 0, 1),
	(22, '插件管理', 0, 1),
	(23, '内容管理', 0, 1),
	(24, '用户管理', 0, 1),
	(25, '分类管理', 0, 1),
	(26, '语言/风格', 0, 1),
	(27, '数据库管理', 0, 1),
	(28, '站内统计', 0, 1);

INSERT INTO wiki_regular_relation (`idleft`, `idright`) VALUES
	(5, 3),
	(5, 18),
	(19, 18),
	(21, 29),
	(21, 20),
	(30, 29),
	(34, 36),
	(40, 43),
	(40, 41),
	(40, 42),
	(48, 50),
	(52, 51),
	(53, 51),
	(54, 51),
	(62, 60),
	(63, 64),
	(63, 65),
	(63, 60),
	(63, 61),
	(63, 62),
	(65, 64),
	(67, 70),
	(67, 69),
	(67, 71),
	(67, 72),
	(67, 68),
	(67, 66),
	(68, 72),
	(68, 71),
	(68, 70),
	(68, 69),
	(68, 66),
	(71, 69),
	(79, 162),
	(79, 83),
	(79, 80),
	(79, 78),
	(79, 77),
	(79, 75),
	(79, 85),
	(79, 81),
	(79, 73),
	(88, 87),
	(88, 89),
	(88, 90),
	(88, 92),
	(88, 91),
	(96, 95),
	(96, 93),
	(96, 94),
	(96, 97),
	(100, 98),
	(100, 99),
	(100, 101),
	(100, 102),
	(102, 101),
	(109, 108),
	(109, 113),
	(109, 112),
	(109, 111),
	(109, 110),
	(109, 107),
	(117, 114),
	(117, 116),
	(117, 115),
	(129, 127),
	(129, 131),
	(129, 130),
	(129, 128),
	(131, 130),
	(140, 137),
	(140, 138),
	(140, 139),
	(144, 143),
	(144, 142),
	(144, 141),
	(152, 51),
	(153, 51),
	(153, 154),
	(153, 155),
	(153, 152),
	(154, 51),
	(155, 51),
	(157, 159),
	(157, 158),
	(162, 161),
	(162, 160),
	(162, 163);

EOT;

 	$strtip=runquery($installsql);

/* 
	$pluginbase = new pluginbase($db);
	$pluginbase->install('hdapi');
 	$pluginbase->install('ucenter');
*/	
					if (mysql_error()) {
						$str = "<SPAN class=err>" . $strtip . ' ' . mysql_error() . "</span>";
						$nextAccess = 0;
					}

					if($nextAccess==1){
						$str = "<div id=\"tips\">{$lang['stepSetupDelInstallDirTip']}</div>";
						 $str .="<div id=\"wrapper_1\"><div class=\"col\"><br />$strcretip $msg<br /></div></div>";

					}

					if ($nextAccess == 1) {
	                    @cleardir(HDWIKI_ROOT.'/data/view');
	                    @cleardir(HDWIKI_ROOT.'/data/cache');
	                    @forceMkdir(HDWIKI_ROOT.'/data/attachment');
	                    @forceMkdir(HDWIKI_ROOT.'/data/backup');
	                    @forceMkdir(HDWIKI_ROOT.'/data/cache');
						@forceMkdir(HDWIKI_ROOT.'/data/db_backup');
	                    @forceMkdir(HDWIKI_ROOT.'/data/logs');
						@forceMkdir(HDWIKI_ROOT.'/data/tmp');
	                    @forceMkdir(HDWIKI_ROOT.'/data/view');
	                    @forceMkdir(HDWIKI_ROOT.'/data/momo');
					}
				}
			}

			break;
			
			case 6:
			@touch(HDWIKI_ROOT.'/data/install.lock');
			$info['type']=1;
			$info['sitedomain']=$_SERVER['SERVER_NAME'];
			$info['siteaddress']=$site_url;
			$info['version']=HDWIKI_VERSION.HDWIKI_RELEASE.$lang['commonCharset'];
			$info = base64_encode(serialize($info));
			
			//install count
			require_once HDWIKI_ROOT.'/config.php';
			require_once HDWIKI_ROOT.'/lib/util.class.php';
			@util::hfopen('http://kaiyuan.hudong.com/count2/in.php?action=install', 0, 'info='.urlencode($info));


			$str = "<div id=\"wrapper1\"><span style=\"color:red\">{$lang['stepSetupSuccessTip']}</span></div>";
			$str .= '<iframe id="count" name="count" src="http://kaiyuan.hudong.com/count2/interface.php?info='.$info.'" scrolling="no" width="455" style="height:370px" frameborder="0"></iframe>';
			break;

			case 7:
				require_once HDWIKI_ROOT.'/config.php';
				require_once HDWIKI_ROOT.'/lib/hddb.class.php';
				require_once HDWIKI_ROOT.'/lib/util.class.php';
				require_once HDWIKI_ROOT.'/lib/string.class.php';
				
				$db = new hddb(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET);
				//install 
				$pluginbase = new pluginbase($db);
				
				$plugin = $pluginbase->model->get_plugin_by_identifier('hdapi');
				
				if ($plugin){
					echo "<span style='font-size:20px;'>百科联盟开通成功.</span><a href='../'>进入首页</a>";
					break;
				}else {
					$pluginbase->install('hdapi');
				}
				
				//update info
				$data = $_GET['info'];
				$data = str_replace(' ', '+', $data);
				$info = base64_decode($data);
				
				if ($info){
					$obj = unserialize($info);
					if(is_array($obj)){
						$url2 = 'http://kaiyuan.hudong.com/count2/in.php?action=update&sitedomain='.$_SERVER['SERVER_NAME'].'&info='.$data;
						$data = util::hfopen($url2);
						//if gbk then toutf8
						if ($lang['commonCharset'] == 'GBK'){
							$obj['sitenick'] = string::hiconv($obj['sitenick'], 'gbk', 'utf-8');
						}
						
						$arr = array('usernick'=>$obj['sitenick'], 'sitenick'=>$obj['sitenick'], 'sitekey'=>$obj['sitekey']);
						
						$plugin = $pluginbase->model->get_plugin_by_identifier('hdapi');
						
						if ($plugin){
							$pluginbase->model->update_pluginvar($arr, $plugin['pluginid']);
						} 
						
						echo "<span style='font-size:20px;'>百科联盟开通成功！</span><a href='../'>进入首页</a>";
					}else{
						echo "<span style='font-size:20px;'>百科联盟开通失败，请登录管理后台开通！</span><a href='../'>进入首页</a>";
					}
				} else {
					echo "<span style='font-size:20px;'>百科联盟开通失败，请登录管理后台开通！</span><a href='../'>进入首页</a>";
				}
			break;
	}

	if ($nextAccess == 0) {
		$str .= "<br /><br /><input class=\"inbut\" type=\"button\" value=\"{$lang['commonPrevStep']}\" onclick=\"javascript: window.location=('$installfile?step=$prevStep');\">\n";
	}elseif($step>1&&$nextAccess&&$step<6){
		$str .= "<div id=\"wrapper2\"><input onclick=\"window.location='install.php?step=$prevStep';\" type=\"button\" value=\"{$lang['commonPrevStep']}\" class=\"inbut\"/>  <input type=\"submit\" value=\"{$lang['commonNextStep']}\" class=\"inbut1\"/ $alert></div>";
	}

	echo $str;
?>
<?php if($step!=6){?>
<INPUT type=hidden value=<?php echo $nextStep?> name="step">
</form><?php }?>
</div>
</div>
<div class="clear"></div>
<div id="footer">
<p>Powered by <a class="footlink" href="http://kaiyuan.hudong.com">HDWiki</a> V<?php echo HDWIKI_VERSION?>| &copy;2005-2008 <strong>Hudong</strong></p>
</div>
</div>
</body>
</html>