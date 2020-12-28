<?php if(!defined('IN_CYASK')) exit('Access Denied'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $title;?></title>
<meta name="description" content="<?php echo $meta_description;?>" />
<meta name="keywords" content="<?php echo $meta_keywords;?>" />
<link href="<?php echo $web_path;?><?php echo $styledir;?>/default.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $web_path;?>js/base.js"></script>
<script type="text/javascript" src="<?php echo $web_path;?>include/functions.js"></script>
<script type="text/javascript" src="<?php echo $web_path;?>include/xmlhttp.js"></script>
<script type="text/javascript">
function limit_words(varfield,obj_str,limit_len)
{
    var leftchars = get_left_chars(varfield,limit_len);
    if (leftchars >= 0)
    {   
    	return true;
    }
    else
    {
       ls_str = obj_str + "的长度请限定在" + limit_len + "个汉字以内！";
       window.alert(ls_str);
       return false;     
    } 
    return true;
}
function search_submit()
{
if(document.wordform.word.value=="" || document.wordform.word.value.length<2)
    {
document.wordform.word.focus();
alert("请正确写下您的问题！");
return false;
}
return true;
}
function ask_submit()
{
var word=document.wordform.word.value;
location.href="<?php echo $web_path;?>ask.php?word="+word;
}
function share_submit()
{
var word=document.wordform.word.value;
location.href="<?php echo $web_path;?>share.php?word="+word;
}
function parse_message(data)
{
var did=document.getElementById("newmessagetip");
if(data)
{
if(data >=1)
{
did.innerHTML='&nbsp;&nbsp;<a href="my.php?command=mymessage&amp;newpm"><font size="2" color="red">有新消息</font></a>&nbsp;&nbsp;';

}
else
{
did.innerHTML='';
}
}
else
{
did.innerHTML='<font size="2" color="red">消息检测中...</font>';
}
}
</script>
</head>
<body>
<div id="main">
<div id="usrbar">
<nobr>
<script type="text/javascript">
<!--
var now = new Date();
var hours = now.getHours();
if(hours < 6){document.write("凌晨好!")}
else if (hours < 9){document.write("早上好!")}
else if (hours < 12){document.write("上午好!")}
else if (hours < 14){document.write("中午好!")}
else if (hours < 17){document.write("下午好!")}
else if (hours < 19){document.write("傍晚好!")}
else if (hours < 22){document.write("晚上好!")}
else {document.write("夜里好!")}
var cyask_user='<?php echo $cyask_user;?>';
if(cyask_user)
{
document.write('<span id="newmessagetip"></span>&nbsp;<b>'+cyask_user+'</b>&nbsp;&nbsp;<a href="<?php echo $web_path;?>my.php">个人中心</a>&nbsp;|&nbsp;<a href="<?php echo $web_path;?>login.php?command=logout&url='+StrCode(location.href)+'">退出</a>');
XMLHttp.getR('<?php echo $web_path;?>process/msgcheck.php',parse_message,'text');
var adminid='<?php echo $cyask_adminid;?>';
if(adminid==1)
{
document.write('&nbsp;|&nbsp;<a href="<?php echo $web_path;?>admin.php">系统设置</a>');
}
}
else
{document.write('&nbsp;<a href="<?php echo $web_path;?>login.php?url='+StrCode(location.href)+'">登录</a>&nbsp;|&nbsp;<a href="<?php echo $web_path;?>register.php?url='+StrCode(location.href)+'">注册</a>');}
// -->
</script>
</nobr>
</div>
<div id="head">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td valign="top" width="160"><a href="<?php echo $web_path;?>"><img src="<?php echo $web_path;?><?php echo $styledir;?>/1000ask.gif" border="0" /></a></td>
<td width="390">
<div class="Tit"><span class="B">问题互助</span></div>
<form name="wordform" action="<?php echo $web_path;?>search.php" method="get" onsubmit="return search_submit()">
<input name="word" class="formfont" tabIndex="1" maxLength="50" size="42" />
<br /><br />
<input type="submit" name="search" value="搜索答案" />
<input type="button" name="ask" value="我要提问" onclick="ask_submit();" />
<input type="button" name="share" value="我要分享" onclick="share_submit();" />
</form>
</td>
<td><font size="2"></font></td>
</tr>
</table>
</div>
<br />
