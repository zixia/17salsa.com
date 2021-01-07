<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="<?php echo $web_path;?>"><?php echo $site_name;?></a> &gt;&gt; <?php echo $toplink;?></div>
<div id="left2">
<div class="t3 bcg"><div class="t3t bgg">知识分享</div></div>
<div class="b3 bcg mb12">
<div class="w100">
<div class="f14b"><?php echo $ques_title;?></div>
<br />
<div class="f14"><?php echo $ques_supplement;?></div>
<div class="f13" align="right">分享者: <?php echo $ques_user;?> &nbsp;分享时间: <?php echo $ques_asktime;?> &nbsp;</div>
</div>
</div>
<?php if($answer_count) { ?>
<div class="t3 bcg"><div class="t3t bgg">分享讨论 ( <?php echo $answer_count;?> )</div></div>
<div class="b3 bcg mb12">
<div class="w100">
<?php if(is_array($answer_list)) { foreach($answer_list as $answer) { ?>
<div class="f14"><?php echo $answer['answer'];?></div>
<div class="f13" align="right"><a href="<?php echo $web_path;?>member.php?uid=<?php echo $answer['uid'];?>" target="_blank"><u><?php echo $answer['username'];?></u></a>&nbsp;&nbsp;评论时间：<?php echo $answer['time'];?>&nbsp;</div>
<div class="f12"><hr size=1 color="#cccccc" width="99%"></div>
<?php } } ?>
</div>
</div>
<?php } ?>
<div class="b4 bcg mb12">
<div class="w100">
<script type="text/javascript">
<!--
function check_answer(f,des,limit_len,min_len)
{
if(!cyask_user)
{
document.getElementById("msgtip").innerHTML ="<font size=\"2\" color=\"red\">提示：您还没有登录，点此 <a href=\"login.php?url="+escape(location.href)+"\">登录</a></font>";
return false;
}
if(f.content.value=="")
{
document.getElementById("msgtip").innerHTML = "<font size=\"2\" color=\"red\">请在下面填写您的"+des+"</font>";
return false;
}
if(f.content.value.length<min_len)
{
document.getElementById("msgtip").innerHTML = "<font size=\"2\" color=\"red\">您"+des+"少于"+min_len+"个字！</font>";
return false;
}
}
-->
</script>
<form name="answerForm" action="<?php echo $web_path;?>comment.php" method="post" enctype="multipart/form-data" onsubmit="return check_answer(this,'回答内容',10000,2);">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<tr><td class="f14" width="15%">&nbsp;</td><td width="85%"><span id="msgtip"></span></td></tr>
<tr valign="top"><td class="f14" width="15%" align="right"><a name="reply"></a>我要评论：&nbsp;</td>
<td width="85%" align="left">
<input type="hidden" name="content" value="">
<script type="text/javascript" src="cyaskeditor/CyaskEditor.js"></script>
<script type="text/javascript">
<!--
var editor = new CyaskEditor("editor");
editor.hiddenName = "content";
editor.editorType = "simple";
editor.editorWidth = "500px";
editor.editorHeight = "200px";
editor.show();
function cyaskeditorsubmit(){editor.data();}
-->
</script>
<span id="tip2"></span><br /><br /></td></tr>
<tr><td class="f14" width="15%">&nbsp;</td>
<td width="85%">
<input type="hidden" name="command" value="share_comment" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
<input type="hidden" name="askername" value="<?php echo $askername;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="submit" name="dosubmit" value="好了，提交评论" onclick="cyaskeditorsubmit()" class="bnsrh" /></td></tr>
</table>
</form>
<br />
</div>
</div>
</div>
<div id="right2">
<div class="t3 bcb"><div class="t3t bgb">热点问题</div></div>
<div class="b3 bcb mb12">
<div class="w100">
<?php if(is_array($hotques_list)) { foreach($hotques_list as $hotquestion) { ?>
&#8226; <span class="f13"><a class="ql" href="<?php echo $web_path;?><?php echo $hotquestion['qid'];?>" target="_blank" title="<?php echo $hotquestion['title'];?>"><?php echo $hotquestion['stitle'];?></a></span><br />
<?php } } ?>
</div>
</div>
</div>
</div>
<?php include template('footer'); ?>
