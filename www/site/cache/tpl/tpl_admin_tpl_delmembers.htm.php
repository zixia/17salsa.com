<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?><table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td><h1>恢复被禁用户</h1></td>
</tr>
</table>
<div class="colorarea01">
<table cellspacing="2" cellpadding="2" class="helptable"><tr><td>
<ul><li>用户恢复后，用户发布的相关信息将不能被恢复。</li></ul>
</td></tr></table>
</div>
<table cellspacing="0" cellpadding="0" class="listtable">
<tr><th>UID</th><th>用户名</th><th style="width:15em;">操作</th></tr>
<?php if(is_array($list)) { foreach($list as $value) { ?>
<tr>
<td><?=$value['uid']?></td>
<td><?=$value['username']?></td>
<td width="100">
<a href="<?=S_URL?>/admincp.php?action=delmembers&op=reuse&uid=<?=$value['uid']?>">恢复</a>
</td>
</tr>
<?php } } ?>
</table>