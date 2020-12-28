<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>17Salsa VIP卡--17Salsa网--全球华人最大休闲拉丁舞(Salsa莎莎)爱好者聚集地</title>

<link href="css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body onload="document.cardNoForm.cardno.focus();">
<div class="lineTop">
   <div class="line"></div>
   <div class="zzkk_title"><img src="images/zzkk.gif" /></div>
   <ul>
    <li>30%</li>
	<li>60%</li>
	<li>100%</li>
   </ul>
<form id="cardNoForm" name="cardNoForm" method="post" action="woyaokaika_b.php" onkeypress="if(event.keyCode=='13') return false;">
   <div class="num1">请输入您的卡号和验证码：</div>
   <div class="num2">
     <div class="Inputbox_b" ><span style="letter-spacing:7px;*letter-spacing:14px;_letter-spacing:14px;">卡号</span><strong>:</strong> <input name="cardno" id="cardno" type="text" /></div>
	 <div class="Inputbox_b">验证码<strong>:</strong> <input name="cardcode" id="cardcode" type="text" /></div>
	 <p>＊验证码是您卡片背面签名条上的四位数字（<a href="card/17salsa_vip_card_code.jpg" target="_blank">点击查看图示</a>）</p>
	 <p>
	   <label>
	   <input type="checkbox" name="agreementSigned" id="agreementSigned" value="checkbox" />
	   </label>
     我已阅读，<a href="yonghuxieyi.php" target="_blank">并同意17salsa 卡服务说明</a></p>
	 <p style="text-align:center;"><input type="image" name="submit" id="submit" src="images/btn_c.gif" width="129" height="30" border="0" onclick="return promptCheck();" /></p>
  </div>
</form>
</div>

<script language="javascript" type="text/javascript">
function promptCheck()
{
	if ( !document.getElementById("agreementSigned").checked )
	{
		alert("您需要选中阅读17salsa VIP卡服务说明并同意才能继续！");
		return false;
	}
	else
	{
        var cardnum = document.getElementById("cardno").value;
        var cardcod = document.getElementById("cardcode").value;
        
        document.getElementById("cardno").value = escape(cardnum.substr(0,30));
        document.getElementById("cardcode").value = escape(cardcod.substr(0,10));
		return true;
	}
}
</script>

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
