<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="./"><?php echo $site_name;?></a> &gt;&gt; 知识分享</div>
<script type="text/javascript">
function check_askform(obj)
{
if(obj.cid.value=="" || obj.cid.value==0)
{
alert("请选择正确的分类,如果您的内容无法归入任何子分类，您可以只选择一级分类。");return false;
}
if(obj.share_title.value =="" || obj.share_title.value.length ==0)
{
alert("请正确填写分享主题！");
obj.share_title.focus();
return false;
}
if(obj.share_content.value =="")
{
      alert("请正确填写分享内容！");
      obj.share_content.focus();
      return false;    
}
}
</script>
<noscript>
<div class="t3 bcb" align="center"><div class="t3t bgb">&nbsp;</div></div>
<div class="b3 bcb f14b" align="center">更好的使用本站服务，请开启 Javascript 功能</div>
</noscript>
<div id="c90">

<div class="t3 bcb"><div class="t3t bgb">知识分享</div></div>
<div class="b3 bcb">
<form name="askform" method="post" action="share.php" onSubmit="return check_askform(this);">
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr valign="top"><td width="110" class="f14" nowrap>分享主题:</td>
<td><input type="text" name="share_title" value="<?php echo $share_title;?>" size="78" /></td>
</tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr valign="top"><td width="110" class="f14" nowrap>分享内容:</td>
<td>
<input type="hidden" name="share_content" value="">
<script type="text/javascript" src="cyaskeditor/CyaskEditor.js"></script>
<script type="text/javascript">
<!--
var editor = new CyaskEditor("editor");
editor.hiddenName = "share_content";
editor.editorType = "simple";
editor.editorWidth = "500px";
editor.editorHeight = "200px";
editor.show();
function cyaskeditorsubmit(){editor.data();}
-->
</script>
</td></tr>
</table>
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr valign="top"><td width="110" nowrap class="f14">分享类别:</td>
<td class="f14">
<span id="classid">
<table cellspacing="0" cellpadding="0" border="0">
<tr><td><select id="ClassLevel1" style="WIDTH: 125px" size="5" name="ClassLevel1"><option selected></option></select></td>
<td width="20"><div align="center"><B>→</B></div></td>
<td><select id="ClassLevel2" style="width: 100px" size="5" name="ClassLevel2"><option selected></option></select></td>
<td width=20><div id="jiantou" align="center"><B>→</B></div></td>
<td><select id="ClassLevel3" style="width: 100px" onchange="getCidValue();" size="5" name="ClassLevel3"><option selected></option></select></td>
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

    if (find_blank == true)
    {
        selected_id_list = selected_id_list.substr(blank_pos + 1, 
        selected_id_list.length - blank_pos - 1);
        blank_pos = selected_id_list.indexOf(" ");
        if (blank_pos == -1) 
        {
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
<br />
<table width="80%" border="0" cellpadding="0" cellspacing="0" >
<tr><td width="110" class="f14">&nbsp;</td>
<td valign="top">
<input type="hidden" name="cid" value="0"> 
<input type="hidden" name="command" value="share" />
<input type="hidden" name="url" value="<?php echo $url;?>" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
<input type="submit" name="submit" value="好了，提交" onclick="cyaskeditorsubmit()" class="bnsrh" />
</td>
</tr>
</table>
</form>
<br />
</div>
</div>
</div>
<?php include template('footer'); ?>
