<?php if(!defined('IN_CYASK')) exit('Access Denied'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $site_name;?> - Powered by Cyask</title>
<link href="<?php echo $styledir;?>/default.css" type=text/css rel=stylesheet />
</head>
<body>
<div id="main" style="height:100%">
<div align="left">
<table width="100%" align="center" border=0>
<tr><td valign="top" width="160">&nbsp;&nbsp;<a href="./"><img src="<?php echo $styledir;?>/1000ask.gif" border=0></a></td>
    <td class="f14">&nbsp;&nbsp;<b><a href="./"><?php echo $lang['back_home'];?></a></b></td>
</tr>
</table>
</div>
<div id="c90"">
<div class="t3 bcb"><div class="t3t bgb"><?php echo $lang['action_message'];?></div></div>
<div class="b3 bcb mb12">
<div class="w100">
<table cellspacing=0 cellpadding=0 width=100% height=310 valgin=top border=0>
<tr><td class=f12 height=50 align=center></td></tr>
<tr><td class=f14 height=30 align=center><?php echo $signal_message;?></td></tr>
<tr><td class=f14 height=30 align=center>
<a class="question" href="<?php echo $url;?>"><?php echo $lang['go_back'];?></a>&nbsp;&nbsp;
<a class="question" href='./my.php?command=myask'><?php echo $lang['my_question'];?></a>&nbsp;&nbsp;
<a class="question" href='./'><?php echo $lang['back_home'];?></a>
</td></tr>
<tr><td class=f12 height=150 align=center></td></tr>
</table>
</div>
</div>
</div>
<br />
<div id="ft">
<hr width="99%" size="1" color="#d6e0ef" />
<a href="mailto:<?php echo $admin_email;?>"><?php echo $lang['contact_us'];?></a> - <?php echo $site_name;?> &nbsp;&nbsp;Powered by <a href="http://www.cyask.com" target="_blank">cyask 3.1</a> &copy; 2008. 
<br />
</div>
</div>
</body>
</html>