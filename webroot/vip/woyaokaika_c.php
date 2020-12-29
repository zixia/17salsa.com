<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>17Salsa VIP卡--17Salsa网--全球华人最大休闲拉丁舞(Salsa莎莎)爱好者聚集地</title>

<link href="css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php
if( checkPersonalInfoSimply( $_POST[name], $_POST[phone],  $_POST[email] ) )
{
	session_name("Private");
	session_start();
	$_SESSION[name]=$_POST[name];
	$_SESSION[phone]=$_POST[phone];
	$_SESSION[email]=$_POST[email];
	session_write_close();

	echo <<<EOL
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>
   <ul>
    <li class="finish">30%</li>
	<li class="finish">60%</li>
	<li>100%</li>
   </ul>

 <form id="websiteAccountForm" name="websiteAccountForm" method="post" action="woyaokaika_d.php">
   <div class="num1">请绑定您的17salsa社区账号完成开卡操作：</div>
   <div class="num2">
     <div class="Inputbox_b" >用户名<strong>:</strong> 
       <input name="user" id="user" type="text" /></div>
	 <div class="Inputbox_b"><span style="letter-spacing:7px;*letter-spacing:14px;_letter-spacing:14px;">密码</span><strong>:</strong> 
       <input name="pwd" id="pwd" type="password" />
	 </div>
	 <div class="act">
	   <p>如果您还不是17salsa社区用户，请按以下步骤进行绑定</p>
	   <p> 1、新窗口打开<a href="http://17salsa.com/home/do.php?ac=311b2f4efb591deb147c18221c851157" target="_blank"> 17salsa社区注册页面</a>。</p>
	   <p> 2、完成17alsa社区注册。</p>
	   <p> 3、接下来，将您的用户名密码填入本页上方输入框。</p>
	   <p> 4、最后，点击完成开卡按钮。 </p>
	 </div>
	
	 <p style="text-align:center;"><input type="image" name="submit" id="submit" src="images/btn_d.gif" width="129" height="30" border="0" /></p>
  </div>
 </form>
	 
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
     <p><strong>个人信息格式有误，请重新输入。</strong></p>
     <p style="text-align:center;padding-top:15px;"><a href='woyaokaika_a.php' onclick="javascript:history.go(-1);return false;"><img src="images/back.gif" border="0" /></a></p>
   </div>
   </div>
EOL;
}
?>
<?php
function checkPersonalInfoSimply( $name, $phone, $email )
{
	if(!preg_match( '/@/', $email ))
		return false;
	return true;
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
