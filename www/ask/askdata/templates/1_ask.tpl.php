<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="./"><?php echo $site_name;?></a> &gt;&gt; 提出问题</div>
<script type="text/javascript">
function check_askform(obj)
{
if(obj.cid.value=="" || obj.cid.value==0)
{
alert("请选择正确的分类,如果您的内容无法归入任何子分类，您可以只选择一级分类。");
return false;
}
if(obj.qtitle.value =="" || obj.qtitle.value.length ==0)
{
alert("请正确写下您的问题！");obj.qtitle.focus();return false;
}
var leftChars = get_left_chars(obj.qtitle,50);
   
    if ( leftChars < 0) 
{
      alert("问题标题字数限定在50字以内，请缩短字数");
      obj.qtitle.focus();
      return false;    
    }
if(leftChars>92)
{
      alert("问题标题不详细，请重新输入");
      obj.qtitle.focus();
      return false;    
}
var qs_length = obj.qsupply.value.length;
  
if(qs_length >3000) 
{   
      alert("问题补充说明字数限定在3000字节以内，请缩短字数");
      obj.qsupply.focus();
      return false;    
    }
}
</script>
<noscript><div class="t3 bcb" align="center"><div class="t3t bgb">&nbsp;</div></div>
<div class="b3 bcb f14b" align="center">更好的使用本站服务，请开启 Javascript 功能</div>
</noscript>
<div id="c90">
<div id="dht">
<div class="t3 bcb"><div class="t3t bgb">提出问题</div></div>
<div class="b3 bcb">

<form name="askform" method="post" action="ask.php" onSubmit="return check_askform(this);">
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr valign="top"><td width="110" class="f14" nowrap>您的问题:</td>
<td><input type="text" name="qtitle" value="<?php echo $ques_title;?>" size="67" /></td>
</tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr valign="top"><td width="110" class="f14" nowrap>问题补充:</td>
<td><textarea name="qsupply" cols="66" rows="8"></textarea> 
<br />可以对您的提问补充细节，以得到更准确的答案;</td></tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr valign="top"><td width="110" nowrap class="f14">问题分类:</td>
<td class="f14">
<span id="classid">
<table cellspacing="0" cellpadding="0" border="0">
<tr><td><select id="ClassLevel1" style="WIDTH: 125px" size="8" name="ClassLevel1"><option selected></option></select></td>
<td width="20"><div align="center"><B>→</B></div></td>
<td><select id="ClassLevel2" style="width: 100px" size="8" name="ClassLevel2"><option selected></option></select></td>
<td width=20><div id="jiantou" align="center"><B>→</B></div></td>
<td><select id="ClassLevel3" style="width: 100px" onchange="getCidValue();" size="8" name="ClassLevel3"><option selected></option></select></td>
</tr>
</table>
<script language="javascript">
function getCidValue()
{
var _cl1 = document.askform.ClassLevel1;
var _cl2 = document.askform.ClassLevel2;
var _cl3 = document.askform.ClassLevel3;
var _cid = document.askform.cid;
if(_cl1.value!=0) _cid.value = _cl1.value;
if(_cl2.value!=0) _cid.value = _cl2.value;
if(_cl3.value!=0) _cid.value = _cl3.value;
}
var g_ClassLevel1;
var g_ClassLevel2;
var g_ClassLevel3;
var class_level_1=new Array(
<?php echo $class1;?>
);
var class_level_2=new Array(
<?php echo $class2;?>
);
var class_level_3=new Array(
<?php echo $class3;?>
);
function FillClassLevel1(ClassLevel1)
{
    ClassLevel1.options[0] = new Option("aa", "0");
    for(i=0; i<class_level_1.length; i++)
    {
        ClassLevel1.options[i] = new Option(class_level_1[i][1], class_level_1[i][0]);
    }
    // ClassLevel1.options[0].selected = true;
    ClassLevel1.length = i;
}
function FillClassLevel2(ClassLevel2, class_level_1_id)
{
    ClassLevel2.options[0] = new Option("不选择", "");
    count = 1;
    for(i=0; i<class_level_2.length; i++){
    if(class_level_2[i][0].toString() == class_level_1_id) {
            ClassLevel2.options[count] = new Option(class_level_2[i][2], class_level_2[i][1]);
            count = count+1;}
    }
    ClassLevel2.options[0].selected = true;
    ClassLevel2.length = count;
}
function FillClassLevel3(ClassLevel3, class_level_2_id)
{
    ClassLevel3.options[0] = new Option("不选择", "");
    count = 1;
    for(i=0; i<class_level_3.length; i++) {
        if(class_level_3[i][0].toString() == class_level_2_id) {
            ClassLevel3.options[count] = new Option(class_level_3[i][2], class_level_3[i][1]);
            count = count+1;}
    }
    ClassLevel3.options[0].selected = true;
    ClassLevel3.length = count;       
}
function ClassLevel2_onchange()
{
    getCidValue();
    FillClassLevel3(g_ClassLevel3, g_ClassLevel2.value); 
    if (g_ClassLevel3.length <= 1) {  
     g_ClassLevel3.style.display = "none";
 document.getElementById("jiantou").style.display = "none";
    }
    else {
     g_ClassLevel3.style.display = "";     
 document.getElementById("jiantou").style.display = "";	 
    }       
}
 
function ClassLevel1_onchange()
{
    getCidValue();
    FillClassLevel2(g_ClassLevel2, g_ClassLevel1.value);
    ClassLevel2_onchange();

}
function InitClassLevelList(ClassLevel1, ClassLevel2, ClassLevel3)
{
    g_ClassLevel1=ClassLevel1;
    g_ClassLevel2=ClassLevel2;
    g_ClassLevel3=ClassLevel3;
    g_ClassLevel1.onchange = Function("ClassLevel1_onchange();");
    g_ClassLevel2.onchange = Function("ClassLevel2_onchange();");
    FillClassLevel1(g_ClassLevel1);
    ClassLevel1_onchange();
}
InitClassLevelList(document.askform.ClassLevel1, document.askform.ClassLevel2, document.askform.ClassLevel3);

var selected_id_list="0"
var blank_pos = selected_id_list.indexOf(" ");
var find_blank = true;
if (blank_pos == -1) {
    find_blank = false;
    blank_pos = selected_id_list.length;
}
var id_str = selected_id_list.substr(0, blank_pos);
g_ClassLevel1.value = id_str;
ClassLevel1_onchange();

if (find_blank == true) {
    selected_id_list = selected_id_list.substr(blank_pos + 1, 
    selected_id_list.length - blank_pos - 1);
    blank_pos = selected_id_list.indexOf(" ");
    if (blank_pos == -1) {
        find_blank = false;
        blank_pos = selected_id_list.length;
    }
    id_str = selected_id_list.substr(0, blank_pos);
    g_ClassLevel2.value = id_str;
    ClassLevel2_onchange();

    if (find_blank == true) {
        selected_id_list = selected_id_list.substr(blank_pos + 1, 
        selected_id_list.length - blank_pos - 1);
        blank_pos = selected_id_list.indexOf(" ");
        if (blank_pos == -1) {
            find_blank = false;
            blank_pos = selected_id_list.length;
        }
        id_str = selected_id_list.substr(0, blank_pos);
        g_ClassLevel3.value = id_str;
    }
}
</script>
</span>
</td></tr>
</table>
<table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr><td width="110">&nbsp;</td><td>如果您的问题无法归入任何子分类，您可以只选择一级分类。</td></tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr><td width="110" valign="top" nowrap class="f14">悬赏分:</td>
<td><div style="height:28px;" class="f14">
<script type="text/javascript">
function check_myscore()
{
var givescore=Number(document.askform.givescore.value);
var myscore=Number(document.askform.myscore.value);
if(givescore > myscore)
{
      	document.getElementById("scoretip").innerHTML = "<font color=red>抱歉，悬赏不能高于您目前的总积分！</font>";
  	document.askform.givescore.options[0].selected = true;
}
else
{
document.getElementById("scoretip").innerHTML = "";
}
}
function check_hid()
{
var myscore=Number(document.askform.myscore.value);
if(myscore<5000)
{
      	document.getElementById("hidtip").innerHTML = "<font color=red>抱歉，您的等级不能隐藏答案！</font>";
  	document.askform.hidanswer.checked = false;
}
else
{
document.getElementById("hidtip").innerHTML = "";
}
}
</script>
<select name="givescore" onchange="check_myscore();"> 
<option value="0">0</option>
<option value="5">5</option>
<option value="10">10</option>
<option value="15">15</option>
<option value="20">20</option>
<option value="30">30</option>
<option value="50">50</option>
<option value="80">80</option>
<option value="100">100</option>
</select>
&nbsp;&nbsp;&nbsp;您的总积分<input type="text" name="myscore" value="<?php echo $my_score;?>" size="10" readonly="true" />
<br /><span id="scoretip">悬赏分越高，您的问题越受关注，更可能得到最佳答案，但不能高于您目前的总积分。</span></div>
</td></tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr> 
<td width="110" valign="top" nowrap="nowrap" class="f14">答案私有:</td>
<td valign="top" class="f14">
<input type="checkbox" name="hidanswer" value="1" onclick="check_hid();" />
&nbsp;<span id="hidtip">隐藏问题的答案</div></td>
</tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr><td width="110" class="f14">&nbsp;</td>
<td valign="top">
<input type="hidden" name="cid" value="0"> 
<input type="hidden" name="command" value="ask" />
<input type="hidden" name="url" value="<?php echo $url;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="submit" name="submit" value="好了，提交" class="bnsrh" />
</td>
</tr>
</table>
</form>
<br />
</div>
</div>
</div>
</div>
<?php include template('footer'); ?>
