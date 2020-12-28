<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="<?php echo $web_path;?>"><?php echo $site_name;?></a> &gt;&gt; <?php echo $toplink;?></div>
<div id="left2">
<div class="t3 bcg"><div class="t3t bgg">
<?php if($ques_status==1) { ?>
待解决问题
<?php } else { ?>
未知问题
<?php } ?>
</div></div>
<div class="b3 bcg mb12">
<div class="w100">
<div class="f14b"><?php echo $ques_title;?></div>
<div>
<?php if($ques_score) { ?>
<img width="16" height="16" src="<?php echo $web_path;?><?php echo $styledir;?>/money.gif" align="absmiddle" /> <font color="red">悬赏<?php echo $ques_score;?>分</font>
<?php } ?>
&nbsp;
<?php if($left_time) { ?>
(离问题结束还有<font color="red"><?php echo $left_day;?>天<?php echo $left_hour;?>小时</font>)
<?php } ?>
</div>
<div class="f14"><?php echo $ques_supplement;?></div>
<div class="f13" align="right">提问者: <?php echo $ques_user;?> &nbsp;提问时间: <?php echo $ques_asktime;?> &nbsp;</div>
<div>
<form action="" method="post">
<?php if($ques_allowanswer) { ?>
<input name="answer" type="button" onclick="location.href='#reply'" value="我要回答" class="bnsrh" />&nbsp;&nbsp;
<?php } ?>
</form>
</div>
<?php if($ques_allowhandle) { ?>
<br />
<div style="padding-bottom: 3px"><form name="handleform1" action="<?php echo $web_path;?>handle.php" method="post">
<input type="submit" name="submit" value="问题补充" class="bnsrh" />
<input type="hidden" name="command" value="ques_supply" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
可以对您的提问补充细节，以得到更准确的答案;</form></div>
<div style="padding-bottom: 3px"><form name="handleform2" action="<?php echo $web_path;?>handle.php" method="post">
<input type="submit" name="submit" value="提高悬赏" class="bnsrh" />
<input type="hidden" name="command" value="ques_addscore" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
提高悬赏分，以提高问题的关注度，并获得额外5天问题有效期;</form></div>
<?php if($ques_allowsetvote) { ?>
<div style="padding-bottom: 3px"><form name="handleform3" action="<?php echo $web_path;?>handle.php" method="post">
<input type="submit" name="submit" value="发起投票" class="bnsrh" />
<input type="hidden" name="command" value="ques_vote" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
不知道哪个回答最好时，可让网友投票来选出最佳答案;</form></div>
<?php } if($ques_allowclose) { ?>
<div><form name="handleform4" action="<?php echo $web_path;?>handle.php" method="post">
<input type="submit" name="submit" value="结束问题" class="bnsrh" />
<input type="hidden" name="command" value="ques_close" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
没有满意的回答，还可直接结束提问，关闭问题。</form></div>
<?php } } ?>
</div>
</div>
<?php if($answer_count) { ?>
<div class="t3 bcg"><div class="t3t bgg">问题答案 ( <?php echo $answer_count;?> )</div></div>
<div class="b3 bcg mb12">
<div class="w100">
<?php if(is_array($answer_list)) { foreach($answer_list as $answer) { ?>
<div><font class="f14"><a href="<?php echo $web_path;?>member.php?uid=<?php echo $answer['uid'];?>" target="_blank"><u><?php echo $answer['username'];?>的答案</u></a></div>
<br />
<div class="f14"><?php echo $answer['answer'];?></div>
<div class="f13" align="right"><a href="<?php echo $web_path;?>response.php?aid=<?php echo $answer['aid'];?>#response"><?php echo $answer['response'];?>&nbsp;评论</a>&nbsp;&nbsp;回答时间：<?php echo $answer['time'];?>&nbsp;</div>
<?php if($ques_allowhandle) { ?>
<div>
<form name="adopt<?php echo $answer['aid'];?>" action="<?php echo $web_path;?>handle.php" method="post">
<input type="hidden" name="command" value="answer_adopt" />
<input type="hidden" name="aid" value="<?php echo $answer['aid'];?>" />
<input type="hidden" name="qid" value="<?php echo $answer['qid'];?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type=submit name="submit" value="采纳为答案" class="bnsrh" />
</form>
</div>
<?php } ?>
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
if(cyask_user==f.askername.value)
{
document.getElementById("msgtip").innerHTML = "<font size=\"2\" color=\"red\">提示：您不必回答自己的问题！</font>";
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
if(!limit_words(f.content,des,limit_len)) return false;
}
-->
</script>
<form name="answerForm" action="<?php echo $web_path;?>answer.php" method="post" enctype="multipart/form-data" onsubmit="return check_answer(this,'回答内容',10000,10);">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<tr><td class="f14" width="15%">&nbsp;</td><td width="85%"><span id="msgtip"></span></td></tr>
<tr valign="top"><td class="f14" width="15%" align="right"><a name="reply"></a>我要回答：&nbsp;</td>
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
<span id="tip2">回答字数在10000字以内</span><br /><br /></td></tr>
<tr><td class="f14" width="15%">&nbsp;</td>
<td width="85%">
<input type="hidden" name="command" value="ques_answer" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
<input type="hidden" name="askername" value="<?php echo $askername;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="submit" name="dosubmit" value="好了，提交回答" onclick="cyaskeditorsubmit()" class="bnsrh" /></td></tr>
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
