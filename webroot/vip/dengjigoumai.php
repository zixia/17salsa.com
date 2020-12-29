<?php
$title = "电话购买";
$tab_class_home = "actived";

include "header.inc.php";
?>
<script type="text/javascript" src="js/checkinput.js"></script>
 <!--中间-->
<div id="main_content"><div class="lm_name"><img src="images/djgm.gif" alt="" width="98" height="27" /></div>
   <div class="qyblock_a">
      <div class="rts"><img src="images/qyclub_pic01.png" width="914" height="16" /></div>
	  <div class="content_left">
	    <div class="content_right">


<?php
$hasSubmitted=false;
$currTime=time();
$prevSubmitTime=0;
session_name("Private");
session_start();
if( isset( $_SESSION[prevSubmitTime] ) )
{
	$prevSubmitTime = $_SESSION[prevSubmitTime];
	if( $currTime-$prevSubmitTime<5*60 )
	{
		$hasSubmitted = true;
	}
	else
	{
		$hasSubmitted = false;
		unset( $_SESSION[prevSubmitTime] );
	}
}
session_write_close();

if( $hasSubmitted )
{
	$leftTime = $currTime-$prevSubmitTime;
	$leftTime = 5*60 - $leftTime;
	$leftMin=floor($leftTime/60);
	$leftSec=$leftTime-$leftMin*60;
	echo <<<EOL
  <div align="center"> 对不起，您需要等待 $leftMin 分 $leftSec 秒 才能再次提交。 </div>
EOL;
}

if(isset($_POST[name]) && $_POST[name] != "" )
{	
	session_name("Private");
	session_start();
	$_SESSION[prevSubmitTime] = time();
	session_write_close();
	
	$to      = 'salsa@17salsa.com';
	$subject = '=?UTF-8?B?' 
				. base64_encode('17salsa VIP卡购买登记表')
				. '?='
				;
	$from	= isset($_POST[email])?$_POST[email]:'abu@17salsa.org';
	$message = <<<EOL
	您好，我想购买VIP卡。
	我的姓名是 $_POST[name] 
	我的电话是 $_POST[phone]
	我的邮箱是 $_POST[email]


	一起salsa网VIP网站： http://vip.17salsa.com
	一起salsa网客服热线：400-633-1733

EOL;
	$headers = "From: $from" . "\r\n" .
	'Content-Type: text/plain; charset=UTF-8' . "\r\n" . 
    'X-Mailer: PHP/' . phpversion() . "\r\n";

	if ( isset($_POST[email]) )
	{
		$headers .= "Cc: $_POST[email]" . "\r\n";
	}

//	mail($to, $subject, $message, $headers);

	$logfile = "/tmp/17salsa.vip.log";

	$fh = fopen($logfile, 'a');
	fputs($fh, date("Y-m-d H:i:s", time()));
	fputs($fh, " IP:" . $_SERVER["HTTP_X_FORWARDED_FOR"]);
	fputs($fh, "\r\n"); 
	fputs($fh, $message);
	fputs($fh, "\r\n"); 
	fclose($fh);

/*
	if ( isset($_POST[email]) )
	{
		mail($$_POST[email], $subject, $message, $headers);
	}
*/

	echo <<<EOL
    <div class="dhgm_txt1">$_POST[name]您好：<br /><br />
	您成功登记了如下信息：<br />
	<br />
	<blockquote>
		姓名：$_POST[name]<br />
		电话：$_POST[phone]<br />
		邮箱：$_POST[email]<br />
	</blockquote>
	</div>
     <div class="dhgm_txt1">
我们的客服专员会尽快联系你。
如有任何疑问敬请拨打客服热线：400-633-1733，谢谢！</div>
     <p><a href='woyaogouka.php'><img border="0" title="我要购卡" src="images/btn_a.png"/></a></p>
EOL;

}
else
{
?>

<div class="dhgm_txt1">请留下您的联系方式，我们客服人员会主动与您联系：</div>

<div class="djgm_txt1">
<form id="registerBuyForm" name="registerBuyForm" method="post" action="dengjigoumai.php">
  <div class="Inputbox_a">姓 名<strong>:</strong>
    <input type="text" id="name" name="name"/></div>
	<div class="Inputbox_a">电 话<strong>:</strong>
      <input type="text" id="phone" name="phone"/></div>
	<div class="Inputbox_a">邮 箱<strong>:</strong>
      <input type="text" id="email" name="email"/></div>

	<br />  
	  <input type="image" name="submit" id="submit" src="images/btn_b.gif" alt="提交登记" width="131" height="30" onClick="return checkInput(this.form);" <?php if($hasSubmitted) echo "disabled" ?> />
</form>
</div>
<?php
}
?>

	    </div>
	    
	  </div>
	  <div class="bot"><img src="images/qyclub_pic02.png" width="914" height="22" /></div>
   </div>
</div>



<!--底部-->
<?php
include "footer.inc.php";
?>
