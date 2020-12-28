<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>17Salsa VIP卡--17Salsa网--全球华人最大休闲拉丁舞(Salsa莎莎)爱好者聚集地</title>

<link href="css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php
session_name("Private");
session_start();
$cardno=$_SESSION[cardno];
$cardcode=$_SESSION[cardcode];
$name=$_SESSION[name];
$phone=$_SESSION[phone];
$email=$_SESSION[email];
session_write_close();


function unescape($str)
{
  $str = rawurldecode($str);
  preg_match_all("/%u.{4}|&#x.{4};|&#\d+;|.+/U",$str,$r);

  $ar = $r[0];
  foreach($ar as $k => $v)
  {
    if(substr($v,0,2) == "%u")
    {
       $ar[$k] = mb_convert_encoding(pack("H4",substr($v,-4)), "utf-8", "UCS-2");
    }
    elseif(substr($v,0,3) == "&#x")
    {
       $ar[$k] = mb_convert_encoding(pack("H4",substr($v,3,-1)), "utf-8", "UCS-2");
    }
    elseif(substr($v,0,2) == "&#")
    {
       $ar[$k] =mb_convert_encoding(pack("n",substr($v,2,-1)), "utf-8", "UCS-2");
    }
  }
  return join("",$ar);
}


function vip_avatar($uid, $size) {

    $type = 'real';
    $var = "avatarfile_{$uid}_{$size}_{$type}";

    $uid = abs(intval($uid));
    $uid = sprintf("%09d", $uid);
    $dir1 = substr($uid, 0, 3);
    $dir2 = substr($uid, 3, 2);
    $dir3 = substr($uid, 5, 2);
    $typeadd = $type == 'real' ? '_real' : '';
    $avatar = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
    $avatar_file = UC_DIR.'/data/avatar/'.$avatar;
    if(file_exists($avatar_file))
    {
        $avatar_url = 'http://17salsa.com/center/data/avatar/'.$avatar;
    }
    else
    {
        $avatar_url = 'http://17salsa.com/center/images/noavatar_middle.gif';
    }
    return $avatar_url;
}


// verify that user&pwd exists in 17salsa_vip
include './config.inc.php';
include './uc_client/client.php';
$user = $_POST[user];
$pass = $_POST[pwd];
list($uid, $username, $password, $uc_email) = uc_user_login($user, $pass);

if( $uid <= 0 )
{
	echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>

   <div class="num3">
     <p><strong>您输入的17SALSA网社区账户或密码有误，请重新输入。</strong></p>
	<br />
	<p>如果您还没有注册17SALSA，请先到<a href='http://17salsa.com/home/do.php?ac=311b2f4efb591deb147c18221c851157'>17SALSA.com</a>注册，谢谢！</p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php' onclick="javascript:history.go(-1);return false;"><img src="images/back.gif" border="0" /></a></p>
   </div>
   </div>
EOL;
}
else 
{ 
	$hasOpenedCard=openCard( $cardno, $cardcode, $name, $phone, $email, $user, $uid );
    $name = unescape($name);
    $phone = unescape($phone);
    $email = unescape($email);

	if( $hasOpenedCard == 0 )
	{
		echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>
   <ul>
    <li class="finish">30%</li>
	<li class="finish">60%</li>
	<li class="finish">100%</li>
   </ul>

   <div class="num3">
     <p style="margin-bottom:15px;"><strong>恭喜您！现在您的17SALSA卡已经开卡成功！</strong></p>
	<p>您的姓名：<strong>$name</strong></p>
	<p>您的电话：<strong>$phone</strong></p>
	<p>您的邮箱：<strong>$email</strong></p>
     <p>您的卡号：<strong>$cardno</strong><br />
     您的17SALSA账号：<strong>$_POST[user]</strong><br />
     您的17SALSA空间：<strong><a href="http://17salsa.com/home/$uid" target="_blank">http://17SALSA.com/home/$uid</a></strong></p>
     <p style="margin-top:15px;"><strong>现在您可以凭卡去任意一家签约学校学习salsa，尽情享受吧！</strong></p>
     <p style="margin-top:40px; margin-left:30px;"><a href="qianyuexuexiao.php" target="_blank">17SALSA签约学校</a><span><a href="qianyuejiuba.php" target="_blank">17SALSA签约酒吧</a></span></p>
   </div>
</div>
EOL;

	$subject = '=?UTF-8?B?' 
				. base64_encode("恭喜17SALSA VIP贵宾${name}开卡成功！")
				. '?='
				;
	$from	= 'salsa@17salsa.com';
	$message = <<<EOL
     17SALSA VIP贵宾${name}您好！
	
		恭喜您！现在您的17SALSA卡已经开卡成功！

		您的17SALSA卡信息如下，请您妥善保管。
		如果您在用卡过程中遇到问题，请拨打17SALSA客服热线：400-633-1733，
		我们将尽最大的努力协助您解决问题，谢谢！

		您登记的信息如下：
			姓名：$name
			电话：$phone
			邮箱：$email
			卡号：$cardno
			17SALSA账号：$_POST[user]
			17SALSA空间：http://17salsa.com/home/$uid

     	现在您可以凭卡去任意一家签约学校学习salsa，尽情享受吧！

		* 17SALSA签约学校 - http://17salsa.com/vip/qianyuexuexiao.php
		* 17SALSA签约酒吧 - http://17salsa.com/vip/qianyuejiuba.php
 

	17SALSA网VIP网站： http://vip.17SALSA.com
	17SALSA网客服热线：400-633-1733

EOL;
	$headers = "From: $from" . "\r\n" .
	'Cc: salsa@17salsa.com' . "\r\n" .
	'Content-Type: text/plain; charset=UTF-8' . "\r\n" . 
    'X-Mailer: PHP/' . phpversion() . "\r\n";

	mail($email, $subject, $message, $headers);

	$logfile = "/tmp/17salsa.vip.log";

	$fh = fopen($logfile, 'a');
	fputs($fh, date("Y-m-d H:i:s", time()));
	fputs($fh, " IP:" . $_SERVER["HTTP_X_FORWARDED_FOR"]);
	fputs($fh, "\r\n"); 
	fputs($fh, $message);
	fputs($fh, "\r\n"); 
	fclose($fh);

	/***
	* add uchome feed
	***/

    require_once './uc_client/client.php';
	//$hasOpenedCard=openCard( $cardno, $cardcode, $name, $phone, $email, $user, $uid );

    $vip_html = "<a href='http://vip.17salsa.com' target='_blank'>17SALSA VIP贵宾卡</a>";
    $user_html = "<a href='http://17salsa.com/home/space.php?uid=".$uid."' target='_self'>";
    $url_end = "</a>";
    $image1 = vip_avatar($uid, 'small');
    $image1_link = "http://17salsa.com/home/space.php?uid=".$uid;

    $feed['icon'] = 'icon_v';
    $feed['uid'] = $uid;
    $feed['username'] = $user;
    $feed['title_template'] = "{userurl}{actor}{urlend} 开卡操作成功";
    $feed['body_template']  = '<b>{userurl}{actor}成为了17Salsa网贵宾用户{urlend}</b><br />   {userurl}{actor}{urlend}的{app}({cardno})开卡成功，成为了<b>VIP贵宾</b>用户！';
    $feed['title_data']     = array('userurl' => $user_html, 'urlend' => $url_end, 'actor' => $user, 'app' => $vip_html, 'cardno' => $cardno);
    $feed['body_data']      = array('userurl' => $user_html, 'urlend' => $url_end, 'actor' => $user, 'app' => $vip_html, 'cardno' => $cardno);
    $feed['images'][]       = array('url' => $image1, 'link' => $image1_link);

    uc_feed_add($feed['icon'], $feed['uid'], $feed['username'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', '', $feed['images']);

	}
	else if( $hasOpenedCard==1 )
	{
		echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>

   <div class="num3">
     <p><strong>对不起，您的17SALSA账号只能绑定一张Vip卡。<br />
     如有任何疑问，请致电客服热线400-633-1733咨询。</strong></p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php'><img src="images/back.gif" border="0"/></a></p>
   </div>
   </div>
EOL;
	}
	else
	{
		echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>

   <div class="num3">
     <p><strong>对不起，开卡失败。如有任何疑问，请致电客服热线400-633-1733咨询。</strong></p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php'><img src="images/back.gif" border="0"/></a></p>
   </div>
   </div>
EOL;
	}
}
?>

<?php
function openCard( $cardno, $cardcode, $name, $phone, $email, $account, $uid )
{
	// 0 for OK, 1 for duplicate-binding( one 17salsa_account multiple vip card ), 2 SQL errors.
	$conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
	mysql_select_db(MK_DBNAME);

	$result=mysql_query("select count(*) from 17salsa_vip where 17salsa_id=$uid");
	$row=mysql_fetch_row($result);
	if($row[0]>=1)
	{
		// this account is already bound to a vip card
		mysql_close($conn);
		return 1;
	}

	$query=sprintf("update 17salsa_vip set name='%s', phone='%s', email='%s', 17salsa_account='%s', 17salsa_id=$uid, active_date=now() where cardno='%s' and cardcode='%s'", 
		mysql_real_escape_string($name), 
		mysql_real_escape_string($phone), 
		mysql_real_escape_string($email), 
		mysql_real_escape_string($account), 
		mysql_real_escape_string($cardno), 
		mysql_real_escape_string($cardcode));
	$result=mysql_query($query);
	$error = mysql_error() ? mysql_error() : 0;
	if( $error ) 
	{
		echo $error;
		mysql_close($conn);
		return 2;
	}

	// add vip
	$result=mysql_query("update uchome_space set groupid=3 where uid=$uid");
	$error = mysql_error() ? mysql_error() : 0;
	if( $error ) 
	{
		echo $error;
		mysql_close($conn);
		return 2;
	}

	mysql_close($conn);
	return 0;
}
?>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-13033101-1");
pageTracker._trackPageview();
} catch(err) {}</script>


</body>

</html>
