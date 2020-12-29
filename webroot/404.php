<?php
/*
 * 判断 avatar 头像如果不存在，则返回缺省
 */
$request_uri = $_SERVER['REQUEST_URI']; // => /center/data/avatar/000/00/29/81_real_avatar_middle.jpg
if ( preg_match('#/center/data/avatar/\d+/\d+/\d+/.+_avatar_([^\.]+).jpg#', $request_uri, $matches) ){
    $redir_url = "http://17salsa.com/center/images/noavatar_" . $matches[1] . ".gif";
    header("Location: $redir_url");
    die();
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<title>一起SALSA网</title>

<meta name="author" content="Li Zhuohuan, zixia, blues, bruce, 李卓桓" />
<meta name="copyright" content="copyright 2008 @17salsa.ncom" />
<meta name="description" content="首页动态 - 阿布 - 一起Salsa网是中国最专业的Salsa拉丁风情舞爱好者、Salsa俱乐部交流社区。在这里你能找到最好的Salsa舞者，最新的Salsa培训资料，最权威的Salsa夜店活动。快来注册，拥有属于你自己的Salsa空间吧！ " />
<meta name="keywords" content="一起Salsa社区, 一起Salsa网, Salsa爱好者社区, Salsa爱好者网, Salsa俱乐部, Salsa培训, Salsa教练, Salsa教学, 跳舞, 风情拉丁舞蹈社区, bachata, merengue, chachacha, chacha" />

<script language="javascript" type="text/javascript" src="/home/source/script_common.js"></script>
<script language="javascript" type="text/javascript" src="/home/source/script_menu.js"></script>

<script language="javascript" type="text/javascript" src="/home/source/script_ajax.js"></script>
<script language="javascript" type="text/javascript" src="/home/source/script_face.js"></script>
<script language="javascript" type="text/javascript" src="/home/source/script_manage.js"></script>
<style type="text/css">
@import url(/home/template/default/style.css);
</style>
<link rel="shortcut icon" href="/home/image/favicon.ico" />
<link rel="edituri" type="application/rsd+xml" title="rsd" href="/home/xmlrpc.php?rsd=1" />
</head>
<body>
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>


<div id="header">

<center>
<div style="width:970px; background:#FF8E00; text-align:left;">
<nobr>

<script type="text/javascript"><!--
google_ad_client = "pub-8383497624729613";
/* 728x90, created 12/19/08 */
google_ad_slot = "5540738089";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<script type="text/javascript"><!--
google_ad_client = "pub-8383497624729613";
/* 200x90, created 12/19/08 */
google_ad_slot = "0985571996";
google_ad_width = 200;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

</nobr>
</div>
</center>



<div class="headerwarp">
<h1 class="logo"><a href="index.php"><img src="/home/image/logo_17salsa.gif" alt="一起Salsa网" /></a></h1>
<ul class="menu">
<li><a href="/">首页</a></li>
<li><a href="/home/space.html">个人主页</a></li>
<li><a href="/home/space-friend.html">好友</a></li>
<li><a href="/home/network.html">随便看看</a></li>


<li class="ucmenu" onmouseover="subop(this)" onmouseout="subop(this)">
<a href="/home/link.php?url=http://17salsa.com" title="一起Salsa网" target="_blank">一起SALSA网</a>

<ul id="ucmenu" style="display: none;">
<li><a href="/home/space-mtag-tagid-12.html" title="Salsa论坛BBS" target="_blank">论坛BBS</a></li>
<li><a href="/home/network-blog.html" title="Salsa博客blog" target="_blank">日志Blog</a></li>
<li><a href="/home/network-album.html" title="Salsa相册照片" target="_blank">相册照片</a></li>
<li><a href="/home/space-tag.html" title="Salsa标签" target="_blank">标签分类</a></li>
<li><a href="/home/search.html" title="Salsa搜索" target="_blank">站内搜索</a></li>
</ul>
</li>


</ul>

</div>
</div>


<script >
<!--
function subop(obj){

var tLeft = obj.offsetLeft;
var tTop = obj.offsetTop + 38;
while(obj=obj.offsetParent) {
tLeft+=obj.offsetLeft;
tTop+=obj.offsetTop;
}
if($('ucmenu').style.display == 'none'){
$('ucmenu').style.cssText = 'display:block; left:' + tLeft + 'px; top:' + tTop + 'px;';
}
else{
$('ucmenu').style.cssText = 'display:none;';
}
}
-->
</script>

<div id="wrap">


<?php
$url = $_SERVER['REQUEST_URI'];
//die(var_dump($_SERVER));
//die($url);

if ( preg_match("@^/[^/]+$@",$url) 
		|| preg_match("@^/attachment/@",$url)
)
{
	header("Location: /home$url",TRUE,302);
	die();
}

header ('HTTP/1.1 404 Not Found');
?>

<font size="+1" color="#888">非常抱歉，您要查看的页面不存在。</font>

<br />
<br />
<br />

<script type="text/javascript">
  var GOOG_FIXURL_LANG = 'zh-CN';
  var GOOG_FIXURL_SITE = 'http://17salsa.com/';
</script>
<script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>


<br />
<br />
<br />


<div id="footer" title="">

<p class="r_option">
<a href="javascript:;" onclick="window.scrollTo(0,0);" id="a_top" title="TOP">TOP</a>
</p>


<p>
一起Salsa网<!-- a href="mailto:salsa@17salsa.org">联系我们</a -->
            <!-- - < a href="http://zh.17salsa.org/Home/about-us" target="_blank">关于17SALSA</a -->
            - <a href="/home/link.php?url=http://sites.google.com%2Fa%2F17salsa.org%2Fteam-zh%2FHome%2Fabout-us" target="_blank">关于17SALSA</a>

            <!-- a href="mailto:salsa@17salsa.org">联系我们</a -->
            - <a href="/home/link.php?url=http://sites.google.com%2Fa%2F17salsa.org%2Fteam-zh%2FHome%2Fcontact-us" target="_blank">联系我们</a>
            <!-- a href="http://zh.17salsa.org/Home/salsa-links" target="_blank">友情链接</a -->
            - <a href="/home/link.php?url=http://sites.google.com%2Fa%2F17salsa.org%2Fteam-zh%2FHome%2Fsalsa-links" target="_blank">友情链接</a>
 - <a  href="http://www.miibeian.gov.cn" target="_blank">京ICP证020015号</a></p>

</div>


</div>
<!--/wrap-->


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


