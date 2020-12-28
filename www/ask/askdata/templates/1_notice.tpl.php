<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div class="path">您现在的位置：<a href=""><?php echo $site_name;?></a> &gt;&gt; 站内公告</div>
<br />
<div id=center2>
<div class="t3 bcb"><div class="t3t bgb">站内公告</div></div>
<div class="b3 bcb mb12 w100">
<table cellspacing=0 cellpadding="0" width="100%" border="0">
<tr><td class="f14b" height="35" align="center"><?php echo $notice['title'];?></td></tr>
<tr><td class="f13" height="35" align="center"><?php echo $notice['author'];?>&nbsp;&nbsp;<?php echo $notice['time'];?></td></tr>
<tr><td height="200" align="left" valign="top"><div><?php echo $notice['content'];?></div></td></tr>
</table>
</div>
</div>
<br />
<?php include template('footer'); ?>
