<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="c90">
<div class="t3 bcb"><div class="t3t bgb">问题补充</div></div>
<div class="b3 bcb mb12">
<div class="w100">
<br />
<script type="text/javascript">
function check_supply(f,des,limit_len,min_len)
{
if(f.supplement.value=="")
{
document.getElementById("msgtip").innerHTML = "<font size=\"2\" color=\"red\">请在下面填写您的"+des+"</font>";
return false;
}
if(f.supplement.value.length<min_len)
{
document.getElementById("msgtip").innerHTML = "<font size=\"2\" color=\"red\">您"+des+"少于"+min_len+"个字！</font>";
return false;
}
if(!limit_words(f.supplement,des,limit_len)) return false;
}
</script>
<form name="quesSupplyForm" action="handle.php" onSubmit="return check_supply(this,'问题补充',1000,2);" method="post">
<table cellspacing=0 cellpadding=0 width="100%" border=0>
<tr valign="top">
<td width="15%" height="26"><span class="f14">我的提问:</span></td>
<td width="85%"><span class="f14b"><?php echo $ques_title;?></span></td></tr>
<tr valign="top">
<td width="15%"><SPAN class=f14>问题补充:</SPAN></td>
<td width="85%" height=200>
<span id="msgtip"></span><br />
 <textarea name="supplement" rows="15" cols="70"><?php echo $ques_supplement;?></textarea></td></tr>
</table>
<table cellSpacing=0 cellPadding=0 width="100%" border=0>
<tr>
<td width="15%" height="50" valign="middle">&nbsp;</td>
<td width="85%" height="50" valign="middle" >
<input type="hidden" name="command" value="ques_supply_submit" />
<input type="hidden" name="qid" value="<?php echo $qid;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="hidden" name="url" value="<?php echo get_referer($default = './');?>" />
<input type="submit" value="好了，提交" name="supllysubmit" class="bnsrh" />&nbsp;&nbsp;&nbsp;&nbsp;
<input type=button value="放&nbsp;弃" name="Submit3" onClick="history.back();" class="bnsrh" />
</td></tr>
</table>
</form>
<br />
</div>
</div>
</div>
</div>
<?php include template('footer'); ?>
