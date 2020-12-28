<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>17Salsa VIP卡--17Salsa网--全球华人最大休闲拉丁舞(Salsa莎莎)爱好者聚集地</title>
<script type="text/javascript" src="js/checkinput.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php
$verifyResult = verifyVipCode();
if( $verifyResult == 0 )
{
	// store info into private session
	session_name('Private');
	session_start();
	$_SESSION[cardno]=$_POST[cardno];
	$_SESSION[cardcode]=$_POST[cardcode];
	session_write_close();

	echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>
   <ul>
    <li class="finish">30%</li>
	<li>60%</li>
	<li>100%</li>
   </ul>

<form id="peronalInfoForm" name="personalInfoForm" method="post" action="woyaokaika_c.php">
   <div class="num1">请登记您的个人资料进行开卡操作：</div>
   <div class="num2">
     <div class="Inputbox_b" ><span style="letter-spacing:7px;*letter-spacing:11px;_letter-spacing:11px;">姓名</span><strong>:</strong>
         <input name="name" id="name" type="text" />
     </div>
     <div class="Inputbox_b"><span style="letter-spacing:7px;*letter-spacing:11px;_letter-spacing:11px;">手机</span><strong>:</strong>
         <input name="phone" id="phone" type="text" />
     </div>
     <div class="Inputbox_b">E-mail<strong>:</strong>
         <input name="email" id="email" type="text" />
     </div>
     
     <p style="text-align:center;padding-top:15px;"><input type="image" name="submit" id="submit" src="images/btn_c.gif" width="129" height="30" border="0" onClick="return checkInput(this.form);" /></p>
   </div>
</form>
</div>
EOL;
}
else if( $verifyResult == 1 )
{
	echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>

   <div class="num3">
     <p><strong>您的17salsa VIP卡已经处于开卡成功状态，不需要重复开卡操作。如有任何疑问，请致电客服热线400-633-1733咨询。</strong></p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php'><img src="images/back.gif" border="0" /></a></p>
   </div>
   </div>
EOL;
}
else if( $verifyResult == 2 )
{
	echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>

   <div class="num3">
     <p><strong>卡号验证码输入错误，请重新输入。</strong></p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php'><img src="images/back.gif" border="0"/></a></p>
   </div>
   </div>
EOL;
}
?>
<?php
function verifyVipCode()
{
	// 0 for OK, 1 for duplicate-registering, 2 for incorrect card
	if( isset( $_POST[cardno]) && isset( $_POST[cardcode]) && isset( $_POST[agreementSigned] ) )
	{
		include 'config.inc.php';
		$conn = mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
		mysql_select_db(MK_DBNAME, $conn);
		$query=sprintf("select 17salsa_account from 17salsa_vip where cardno='%s' and cardcode='%s'", 
						mysql_real_escape_string($_POST[cardno]), 
						mysql_real_escape_string($_POST[cardcode]));
		$result=mysql_query( $query );
		while( $row = mysql_fetch_array( $result ) )
		{
			if( is_null($row[0]) )
			{
				return 0;
			}
			else 
			{
				// duplicate registering
				return 1;
			}
		}
		echo mysql_error();
		mysql_close($conn);
	}
	return 2; // incorrect cardno or cardcode
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
