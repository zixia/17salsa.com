<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="<?php echo $web_path;?>"><?php echo $site_name;?></a> &gt;&gt; <?php echo $toplink;?></div>
<div id="left2">
<div class="t3 bcb"><div class="t3t bgb">
<?php if($ques_status==1) { ?>
待解决问题
<?php } elseif($ques_status==2) { ?>
已解决问题
<?php } elseif($ques_status==3) { ?>
投票中问题
<?php } elseif($ques_status==4) { ?>
已关闭问题
<?php } else { ?>
未知问题
<?php } ?>
</div></div>
<div class="b3 bcb mb12">
<div class="f14B"><br /><?php echo $ques_title;?></div>
<div>
<?php if($ques_score) { ?>
<img width="16" height="16" src="<?php echo $web_path;?><?php echo $styledir;?>/money.gif" align="absmiddle" /> <font color="red">悬赏<?php echo $ques_score;?>分</font>
<?php } ?>
&nbsp;
<?php if($left_time) { ?>
(离问题结束还有<font color="red"><?php echo $left_day;?>天<?php echo $left_hour;?>小时</font>)
<?php } ?>
</div>
<div class="f13" align="right">提问者:<a href="<?php echo $web_path;?>member.php?uid=<?php echo $ques_uid;?>" target="_blank"><?php echo $ques_user;?></a> &nbsp;提问时间:<?php echo $ques_asktime;?>&nbsp;</div>
<div class="f14">
<form name="collectForm" action="<?php echo $web_path;?>collect.php" method="post">
<?php if($cyask_user) { ?>
<input type="submit" name="submit" value="我要收藏" class="bnsrh" />
<?php } ?>
&nbsp;<a href="question.php?qid=<?php echo $qid;?>"><u>其他答案</u></a>
</form>
</div>
<div class="f12">&nbsp;</div>
<div class="f14"><?php echo $answer['answer'];?></div>
<div class="f13" align="right">回答者:<a href="<?php echo $web_path;?>member.php?uid=<?php echo $answer['uid'];?>" target="_blank"><?php echo $answer['username'];?></a> &nbsp;回答时间:<?php echo $answer['time'];?>&nbsp;</div>
<div class="f14b"><a name="response"></a>此答案得到<?php echo $response_count;?>次评论</div>
<?php if(is_array($response_list)) { foreach($response_list as $response) { ?>
<div class="f14"><?php echo $response['content'];?></div>
<div class="f13" align="right">评论者:<a href="<?php echo $web_path;?><?php echo $response['userlink'];?>"><?php echo $response['username'];?></a> &nbsp;评论时间:<?php echo $response['time'];?>&nbsp;</div>
<div class="f12"><hr size="1" width="99%" color="#cccccc"></div>
<?php } } ?>
</div>

<div class="b4 bcb mb12">
<div class="w100">
<script type="text/javascript">
<!--
function check_comment(f,des,limit_len,min_len)
{
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
if(!limit_words(f.content,des,limit_len)) return false;
}
-->
</script>
<form name="commentForm" action="<?php echo $web_path;?>answer.php" method="post" enctype="multipart/form-data" onsubmit="return check_comment(this,'��Ӧ����',5000,5);">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<tr><td class="f14" width="15%">&nbsp;</td><td width="85%"><span id="msgtip"></span></td></tr>
<tr valign="top"><td class="f14" width="15%" align="right"><a name="comment"></a>我要评论&nbsp;</td>
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
<span id="tip2">评论字数在5000字以内</span><br /><br /></td></tr>
<tr><td class="f14" width="15%">&nbsp;</td>
<td width="85%">
<input type="hidden" name="command" value="answer_response" />
<input type="hidden" name="aid" value="<?php echo $aid;?>" />
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
&#8226; <span class="f13"><a class="question" href="<?php echo $web_path;?>question.php?qid=<?php echo $hotquestion['qid'];?>" target="_blank" title="<?php echo $hotquestion['title'];?>"><?php echo $hotquestion['stitle'];?></a></span><br />
<?php } } ?>
</div>
</div>
</div>
</div>
<?php include template('footer'); ?>
