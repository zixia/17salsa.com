<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="c90">
<div class="t3 bcb"><div class="t3t bgb">提高悬赏</div></div>
<div class="b3 bcb mb12">
<div class="w100">
<br />
<div class="f14B"><?php echo $ques_title;?></div>
<div class="f13">该问题目前的悬赏分:<font color="red"><?php echo $ques_score;?></font></div>
<div class="f13">&nbsp;</div>
<script language="JavaScript"> 
function check_score()
{
var addscore=Number(addscoreform.addscore.value);
var myscore=Number(addscoreform.myscore.value);
if(addscore > myscore)
{
      	document.getElementById("scoretip").innerHTML = "<font color=\"red\">抱歉，悬赏不能高于您目前的总积分！</font>";
  	addscoreform.addscore.options[0].selected = true;
}
else
{
document.getElementById("scoretip").innerHTML = "";
}
}
</script>
<form name="addscoreform" action="handle.php" method="post">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<tr><td width="18%"><span class="f14">请选择追加的悬赏分:</span></td>
<td width="82%" height="30" class="f14">
<input type="hidden" name="myscore" value="<?php echo $my_score;?>" />
<select name="addscore" onchange="check_score();">
<option value="0" selected>0</option>
<option value="10">10</option>
<option value="20">20</option>
<option value="30">30</option>
<option value="40">40</option>
<option value="50">50</option>
<option value="60">60</option>
<option value="80">80</option>
</select>&nbsp;<span id="scoretip"></span>
</td></tr>
<tr><td width="18%" height="30"><span class="f14">您的总积分:</span></td>
<td class="f14" width="82%" height="30"><?php echo $my_score;?></td></tr>
<tr><td width="18%" height="50">&nbsp;</td>
    <td width="82%" height="50">
<input type="hidden" name="command" value="ques_addscore_submit" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="hidden" name="url" value="<?php echo get_referer($default = './');?>" />
<input type="submit" name="addscoresubmit" value="好了，提交" class="bnsrh" />&nbsp;&nbsp; 
<input type="button" name="Submit2" onClick="history.back();" value="放&nbsp;弃" class="bnsrh" />
</td></tr>
</table>
</form>
<br />
</div>
</div>
</div>
</div>
<?php include template('footer'); ?>
