<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
<div id="footer">
<div id="footer_top">
<p class="good_link">
<a href="<?=S_URL?>/index.php"><?=$_SCONFIG['sitename']?></a> | 
<a href="<?php echo geturl("action/site/type/map"); ?>">站点地图</a> | 
<a href="<?php echo geturl("action/site/type/link"); ?>">友情链接</a> | 
<a href="mailto:<?=$_SCONFIG['adminemail']?>">联系我们</a>
</p>
<form action="<?=S_URL?>/batch.search.php" method="post">
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />
<input type="hidden" name="searchname" id="searchname" value="subject" />
<p class="footer_search">
<select name="searchtxt" id="searchtxt" onchange="changetype();">
<option value="标题">标题</option>
<option value="内容">内容</option>
<option value="作者">作者</option>
</select>
<input class="input_tx" type="text" value="" name="searchkey" size="30"/>
<input class="input_search" type="submit" value="搜索" name="searchbtn"/>
</p>
</form>
</div>
<div class="copyright">
<p><?php debuginfo();; ?></p>
</div>
</div><!--footer end-->
<script language="javascript">
function changetype() {
if($('searchtxt').value == '标题') {
$('searchname').value = 'subject';
}else if($('searchtxt').value == '内容') {
$('searchname').value = 'message';
}else if($('searchtxt').value == '作者') {
$('searchname').value = 'author';
}
}
</script>

</body>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-13033101-1");
pageTracker._trackPageview();
} catch(err) {}</script>

<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F746c5f5ff0c40a4b769d52603854aaac' type='text/javascript'%3E%3C/script%3E"));
</script>

</html>
<?php } ?>