<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?><div id="newslisttab">
<ul>
<li>频道名</li>
<?php if(is_array($channels['types'])) { foreach($channels['types'] as $value) { ?>
<li
<?php if($type == $value['nameid']) { ?>
 class="active"
<?php } ?>
><a href="<?=$theurl?>&type=<?=$value['nameid']?>&folder=<?=$_SGET['folder']?>"><?=$value['name']?></a>
<?php if(in_array($value['nameid'], $_SCONFIG['closechannels'])) { ?>
关闭中
<?php } ?>
</li>
<?php } } ?>

<?php if(is_array($_SCONFIG['hidechannels'])) { foreach($_SCONFIG['hidechannels'] as $value) { ?>
<?php if($value['nameid']=='news' || $value['upnameid']=='news') { ?>
<li
<?php if($type == $value['nameid']) { ?>
 class="active"
<?php } ?>
><a href="<?=$theurl?>&type=<?=$value['nameid']?>&folder=<?=$_SGET['folder']?>"><?=$value['name']?></a></li>
<?php } ?>
<?php } } ?>
</ul>
</div>
<?php if($_GET['op'] != 'add' && $_GET['op'] != 'edit') { ?>
<form method="get" name="listform" id="theform" action="<?=$theurl?>" enctype="multipart/form-data">
<table cellspacing="0" cellpadding="0" width="100%"  class="toptable">
<tr>
<td>itemid:<input type="text" name="searchid" id="searchid" value="" size="5" /> 
标题:<input type="text" name="searchkey" id="searchkey" value="" size="10" /> 
查看:<select name="catid">
<option value="">所有分类</option>
<?php if(is_array($catarr)) { foreach($catarr as $value) { ?>
<option value="<?=$value['catid']?>"
<?php if($_GET['catid']==$value['catid']) { ?>
 selected
<?php } ?>
><?=$value['pre']?><?=$value['name']?></option>
<?php } } ?>
</select>
<select id="digest" name="digest">
<option value="">不限制精华</option>
<option value="1">精华I</option>
<option value="2">精华II</option>
<option value="3">精华III</option>
</select>
排序:<select id="order" name="order">
<option value="" selected>默认排序</option>
<option value="dateline">发布时间</option>
<option value="lastpost">最后回复</option>
<option value="viewnum">查看数</option>
<option value="replynum">回复数</option>
</select>
<select id="sc" name="sc">
<option value="ASC">升序</option>
<option value="DESC" selected>降序</option>
</select>
每页:<input type="text" name="perpage" id="perpage" value="20" size="2" />条
<input type="submit" name="filtersubmit" value="GO">
<input type="hidden" name="type" value="<?=$type?>" />
<input type="hidden" name="action" value="spacenews" />
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />
</td>
</tr>
</table>
</form>
<?php } ?>
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td><h1>资讯</h1></td>
<td class="actions">
<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
<tr>
<td <?=$active['0']?>><a href="<?=$theurl?>&type=<?=$type?>" class="view">发布箱</a></td>
<td <?=$active['1']?>><a href="<?=$theurl?>&type=<?=$type?>&folder=1" class="view">待审箱</a></td>
<td <?=$active['2']?>><a href="<?=$theurl?>&type=<?=$type?>&folder=2" class="view">垃圾箱</a></td>
<td <?=$active['add']?>><a href="<?=$theurl?>&type=<?=$type?>&op=add" class="add">发布信息</a></td>
<?php if($_GET['op'] == 'edit') { ?>
<td <?=$active['edit']?>><a href="<?=$theurl?>&op=edit&itemid=<?=$_GET['itemid']?>">编辑信息</a></td>
<?php } ?>
</tr>
</table>
</td>
</tr>
</table>
<?php if($listarr) { ?>
<script language="javascript">
<!--
function jsop(radionvalue) {
$('divnoop').style.display = "none";
$('divmovefolder').style.display = "none";
$('divcheck').style.display = "none";
$('divmovecat').style.display = "none";
$('divdigest').style.display = "none";
$('divtop').style.display = "none";
$('divallowreply').style.display = "none";
$('divdelete').style.display = "none";
if(radionvalue != 'noop') {
$('div'+radionvalue).style.display = "";
}
}
//-->
</script>
<form method="post" name="listform" id="theform" action="<?=$newurl?>" enctype="multipart/form-data" onSubmit="return listsubmitconfirm(this)">
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />

<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
<tr>
<th width="30">选择</th>
<th width="50">itemid</th>
<th>标题</th>
<th width="70">系统分类</th>
<th width="50">添加人</th>
<th width="120">发布日期</th>
<th width="60">来源</th>
<?php if(!$_SGET['folder']) { ?>
<th width="55">审核级别</th>
<?php } ?>
<th width="60">操作</th>
</tr>
<?php if(is_array($listarr)) { foreach($listarr as $value) { ?>
<?php $class = empty($class) ? ' class="darkrow"': '';; ?><?php $subjectpre = getsubjectpre($value);; ?><tr<?=$class?>>
<td><input name="item[]" type="checkbox" value="<?=$value['itemid']?>" /></td>
<td><?=$value['itemid']?></td>
<td><?=$subjectpre?>
<?php if(!$_SGET['folder']) { ?>
<a href="<?php echo geturl("action/viewnews/itemid/$value[itemid]"); ?>" target="_blank">
<?php } else { ?>
<a href="<?=$theurl?>&amp;op=view&amp;itemid=<?=$value['itemid']?>" target="_blank">
<?php } ?>
<?=$value['subject']?></a></td>
<td align="center"><a href="<?=$theurl?>&type=<?=$type?>&catid=<?=$value['catid']?>">
<?php if($catarr[$value['catid']][name]) { ?>
<?=$catarr[$value['catid']]['name']?>
<?php } ?>
</a></td>
<td align="center">
<?php if(!$value['uid']) { ?>
游客
<?php } else { ?>
<a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>" target="_blank"><?=$value['username']?></a>
<?php } ?>
</td>
<td><?php sdate("Y-m-d H:i", $value[dateline]); ?></td>
<td align="center">
<?php if($value['fromtype'] == 'adminpost' || empty($value['fromtype'])) { ?>
<a href="<?=$theurl?>&type=<?=$type?>&folder=<?=$_SGET['folder']?>&fromtype=adminpost">后台发布</a>
<?php } elseif($value['fromtype'] == 'userpost') { ?>
<a href="<?=$theurl?>&type=<?=$type?>&folder=<?=$_SGET['folder']?>&fromtype=userpost">用户发布</a>
<?php } elseif($value['fromtype'] == 'newspost') { ?>
<a href="<?=$theurl?>&type=<?=$type?>&folder=<?=$_SGET['folder']?>&fromtype=newspost">推送发布</a>
<?php } elseif($value['fromtype'] == 'robotpost') { ?>
<a href="<?=$theurl?>&type=<?=$type?>&folder=<?=$_SGET['folder']?>&fromtype=robotpost">采集发布</a>
<?php } ?>
</td>
<?php if(!$_SGET['folder']) { ?>
<td><?=$gradearr[$value['grade']]?></td>
<?php } ?>
<td align="center">
<img src="<?=S_URL?>/images/base/icon_edit.gif" align="absmiddle"> <a href="<?=$theurl?>&op=edit&folder=<?=$_SGET['folder']?>&itemid=<?=$value['itemid']?>">编辑</a>
</td>
</tr>
<?php } } ?>
</table>
<table cellspacing="0" cellpadding="0" width="100%"  class="btmtable">
<tr>
<th width="12%">批量操作</th>
<th><input type="checkbox" name="chkall" onclick="checkall(this.form, 'item')">全选
<input type="radio" name="operation" value="noop" onClick="jsop(this.value)" checked>不操作
<input type="radio" name="operation" value="movefolder" onClick="jsop(this.value)">审批信息
<input type="radio" name="operation" value="movecat" onClick="jsop(this.value)">移动分类
<?php if(!$_SGET['folder']) { ?>
<input type="radio" name="operation" value="check" onClick="jsop(this.value)">等级审核
<input type="radio" name="operation" value="digest" onClick="jsop(this.value)">精华
<input type="radio" name="operation" value="top" onClick="jsop(this.value)">置顶
<?php } ?>
<input type="radio" name="operation" value="allowreply" onClick="jsop(this.value)">评论
<input type="radio" name="operation" value="delete" onClick="jsop(this.value)">删除
</th>
</tr>
<tr id="divnoop" style="display:none">
<td></td><td></td>
</tr>
<tr id="divmovefolder" style="display:none">
<th>选择文件夹</th>
<td><input name="opfolder" type="radio" value="1" checked />发布箱&nbsp;&nbsp;<input name="opfolder" type="radio" value="2" />待审箱&nbsp;&nbsp;</td>
</tr>

<tr id="divcheck" style="display:none">
<th>审核等级</th>
<td><input name="opcheck" type="radio" value="0" checked /><?=$gradearr['0']?>&nbsp;&nbsp;
<input name="opcheck" type="radio" value="1" /><?=$gradearr['1']?>&nbsp;&nbsp;
<input name="opcheck" type="radio" value="2" /><?=$gradearr['2']?>&nbsp;&nbsp;
<input name="opcheck" type="radio" value="3" /><?=$gradearr['3']?>&nbsp;&nbsp;
<input name="opcheck" type="radio" value="4" /><?=$gradearr['4']?>&nbsp;&nbsp;
<input name="opcheck" type="radio" value="5" /><?=$gradearr['5']?>&nbsp;&nbsp;</td>
</tr>

<tr id="divmovecat" style="display:none">
<th>选择分类</th>
<td><select name="opcatid" id="opcatid">
<?php if(is_array($allcatarr)) { foreach($allcatarr as $key => $cvalue) { ?>
<?php if($channels['types'][$key]['name']) { ?>
<optgroup label="<?=$channels['types'][$key]['name']?>">
<?php if(is_array($cvalue)) { foreach($cvalue as $value) { ?>
<option value="<?=$key?>_<?=$value['catid']?>"><?=$value['pre']?><?=$value['name']?></option>
<?php } } ?>
</optgroup>
<?php } ?>
<?php } } ?>
</select>
</td>
</tr>

<tr id="divdigest" style="display:none">
<th>精华</th>
<td><input name="opdigest" type="radio" value="0" checked />非精华&nbsp;&nbsp;<input name="opdigest" type="radio" value="1" />精华I&nbsp;&nbsp;<input name="opdigest" type="radio" value="2" />精华II&nbsp;&nbsp;<input name="opdigest" type="radio" value="3" />精华III&nbsp;&nbsp;</td>
</tr>

<tr id="divtop" style="display:none">
<th>置顶</th>
<td><input name="optop" type="radio" value="0" checked />非置顶&nbsp;&nbsp;<input name="optop" type="radio" value="1" />置顶I&nbsp;&nbsp;<input name="optop" type="radio" value="2" />置顶II&nbsp;&nbsp;<input name="optop" type="radio" value="3" />置顶III&nbsp;&nbsp;</td>
</tr>

<tr id="divallowreply" style="display:none">
<th>评论</th>
<td><input name="opallowreply" type="radio" value="1" checked />允许评论&nbsp;&nbsp;<input name="opallowreply" type="radio" value="0" />不允许评论&nbsp;&nbsp;</td>
</tr>

<tr id="divdelete" style="display:none">
<th>删除</th>
<td><input name="opdelete" type="radio" value="0" checked />直接删除
<?php if($_SGET['folder'] != 2) { ?>
&nbsp;&nbsp;<input name="opdelete" type="radio" value="1" />放入垃圾箱&nbsp;&nbsp;
<?php } ?>
</td>
</tr>

</table>

<table cellspacing="0" cellpadding="0" width="100%"  class="listpage">
<tr>
<td><?=$multipage?></td>
</tr>
</table>

<div class="buttons">
<input type="submit" name="listsubmit" value="提交保存" class="submit">
<input type="reset" name="listreset" value="重置">
<input name="listvaluesubmit" type="hidden" value="yes" />
</div>

</form>
<?php } elseif(empty($_GET['op']) && !submitcheck('listvaluesubmit') && !submitcheck('valuesubmit') ) { ?>
<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
<tr><td align="center">没有符合条件的信息</td></tr>
</table>
<?php } ?>
<?php if($thevalue) { ?>
<?php if($page == 1) { ?>
<script src="<?=S_URL?>/include/js/selectdate.js"></script>
<script language="javascript">
<!--
function showdivcustomfieldtext() {
var cfindex = $("customfieldid").selectedIndex;
showtbody(cfindex);
}	
function showtbody(id) {
for(i=1;i<=<?=$tbodynum?>;i++){
obj=$("cf_"+i);
if(i == id) {
obj.style.display="";
} else {
obj.style.display="none";
}
}
}
function textCounter(obj, showid, maxlimit) {
var len = strLen(obj.value);
var showobj = $(showid);
if(len < maxlimit) {
showobj.innerHTML = maxlimit - len;
} else {
obj.value = getStrbylen(obj.value, maxlimit);
showobj.innerHTML = "0";
}
}
function strLen(str) {
var charset = is_ie ? document.charset : document.characterSet;
var len = 0;
for(var i = 0; i < str.length; i++) {
len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset.toLowerCase() == "utf-8" ? 3 : 2) : 1;
}
return len;
}
function settitlestyle() {
var objsubject=$('subject');
var objfontcolor=$('fontcolor');
var objfontsize=$('fontsize');
var objem=$('em');
var objstrong=$('strong');
var objunderline=$('underline');
objsubject.style.color = objfontcolor.value;
objfontcolor.style.backgroundColor = objfontcolor.value;
objsubject.style.fontSize = objfontsize.value;
objsubject.style.width = 500;
if(objem.checked == true) {
objsubject.style.fontStyle = "italic";
} else {
objsubject.style.fontStyle = "";
}
if(objstrong.checked == true) {
objsubject.style.fontWeight = "bold";
} else {
objsubject.style.fontWeight = "";
}
if(objunderline.checked == true) {
objsubject.style.textDecoration = "underline";
} else {
objsubject.style.textDecoration = "none";
}
}
function loadtitlestyle() {
var objsubject=$('subject');
var objfontcolor=$('fontcolor');
var objfontsize=$('fontsize');
var objem=$('em');
var objstrong=$('strong');
var objunderline=$('underline');
objfontcolor.style.backgroundColor = objsubject.style.color;
objfontcolor.value = objsubject.style.color;
var colorstr = objsubject.style.color;
if(isFirefox=navigator.userAgent.indexOf("Firefox")>0 && colorstr != ""){
colorstr = rgbToHex(colorstr);
}
if(colorstr != "") {
objfontcolor.options.selectedIndex = getbyid(colorstr).index;
objfontcolor.options.selected = true;
}
objfontsize.value = objsubject.style.fontSize;
if(objsubject.style.fontWeight == "bold") {
objstrong.checked = true;
} else {
objstrong.checked = false;
}
if(objsubject.style.fontStyle == "italic") {
objem.checked = true;
} else {
objem.checked = false;
}
if(objsubject.style.textDecoration == "underline") {
objunderline.checked = true;
} else {
objunderline.checked = false;
}		
}
function makeselectcolor(selectname){
subcat = new Array('00','33','66','99','CC','FF');
var length = subcat.length;
var RED = subcat;
var GREEN = subcat;
var BLUE = subcat;
var b,r,g;
var objsubject=$(selectname);
for(r=0;r < length;r++){
for(g=0;g < length;g++){
for(b=0;b < length;b++){
var oOption = document.createElement("option");
oOption.style.backgroundColor="#"+RED[r]+GREEN[g]+BLUE[b];
oOption.style.color="#"+RED[r]+GREEN[g]+BLUE[b];
oOption.value="#"+RED[r]+GREEN[g]+BLUE[b];
oOption.text="#"+RED[r]+GREEN[g]+BLUE[b];
oOption.id="#"+RED[r]+GREEN[g]+BLUE[b];
objsubject.appendChild(oOption);
}
}
}
}
function rgbToHex(color) {
color=color.replace("rgb(","")
color=color.replace(")","")
color=color.split(",")

r=parseInt(color[0]);
g=parseInt(color[1]);
b=parseInt(color[2]);

r = r.toString(16);
if (r.length == 1) {
r = '0' + r;
}
g = g.toString(16);
if (g.length == 1) {
g = '0' + g;
}
b = b.toString(16);
if (b.length == 1) {
b = '0' + b;
}
return ("#" + r + g + b).toUpperCase();
}
//-->
</script>
<?php } ?>
<form method="post" name="thevalueform" id="theform" action="<?=$newurl?>" enctype="multipart/form-data" onSubmit="return validate(this)">
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />
<div class="colorarea01">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<?php if(checkperm('allowdirectpost')) { ?>
<tr>
<th>提示说明</th>
<td>您目前发布的资讯需要经过审阅之后才能公开发布。<br>在没有通过审阅之前，您发布的资讯将自动放置在待审箱中。
</td>
</tr>
<?php } ?>
<tr id="tr_subject">
<th>标题*</th>
<td>
<?php if($page == 1) { ?>
<input name="subject" type="text" id="subject" onblur="relatekw();" size="60" maxlength="80" value="<?=$thevalue['subject']?>" style="width: 500px;<?=$mktitlestyle?>"  onkeyup="textCounter(this, 'maxlimit', 80);" /><br />
当前可再写长度<strong id="maxlimit">80</strong>字节，最多80个字节
<?php } else { ?>
<strong><?=$thevalue['subject']?></strong>
<?php } ?>
</td>
</tr>
<?php if($page == 1) { ?>
<tr>
<th>标题样式</th>
<td>文字颜色
<select name="fontcolor" id="fontcolor" onChange="settitlestyle();" style="width: 80px;background-color: #000000">
<option value="" selected="selected">default</option>
</select> 
文字大小
<select name="fontsize" id="fontsize" onChange="settitlestyle();">
<option value="" selected="selected">default</option>
<option value="12px">12px</option>
<option value="13px">13px</option>
<option value="14px">14px</option>
<option value="16px">16px</option>
<option value="18px">18px</option>
<option value="24px">24px</option>
<option value="36px">36px</option>
<option value="48px">48px</option>
<option value="72px">72px</option>
</select>
<img src="admin/images/ti.gif" /><input type="checkbox" name="em" id="em" value="italic" onClick="settitlestyle();" />
<img src="admin/images/tb.gif" /><input type="checkbox" name="strong" id="strong" value="bold" onClick="settitlestyle();" />
<img src="admin/images/tu.gif" /><input type="checkbox" name="underline" id="underline" value="underline" onClick="settitlestyle();" />
</td>
</tr>
<tr>
<th>资讯发布日期<p>您可以自己指定当前资讯的发布日期。注意，不能是当前时间以后的日期。</p></th>
<td><input type="text" name="dateline" id="dateline" readonly="readonly" value="<?php sdate("Y-m-d H:i:s", $thevalue[dateline]); ?>" /><img src="<?=S_URL?>/admin/images/time.gif" onClick="getDatePicker('dateline', event, 21)" /></td>
</tr>
<tr id="tr_newsurl">
<th>外部链接URL<p>如果填入外部链接，查看该资讯时，将自动跳转到该链接。</p></th>
<td><input name="newsurl" type="text" id="newsurl" size="60" value="<?=$thevalue['newsurl']?>" /></td>
</tr>
<tr id="tr_catid">
<th>系统分类*<p>请为您的信息正确选择一个系统分类，便于信息被更多的人查看到</p></th>
<td><select name="catid" id="catid">
<?php if(is_array($categorylistarr)) { foreach($categorylistarr as $value) { ?>
<option value="<?=$value['catid']?>"
<?php if($thevalue['catid']==$value['catid']) { ?>
 selected
<?php } ?>
><?=$value['pre']?><?=$value['name']?></option>
<?php } } ?>
</select>
</td>
</tr>
<?php } ?>
<?php if(!$_SGET['folder']) { ?>
<tr id="tr_">
<th>资讯分页<p>您可以为该信息进行分页</p></th>
<td><?=$optext?></td>
</tr>
<?php if($_GET['op'] != 'add') { ?>
<tr id="tr_pageorder">
<th>资讯分页定位</th>
<td>
<select name="pageorder" id="pageorder">
<?php if(is_array($pageorders)) { foreach($pageorders as $key => $value) { ?>
<option value="<?=$key?>"
<?php if($key == $page) { ?>
 selected
<?php } ?>
><?=$value?></option>
<?php } } ?>
</select>
</td>
</tr>
<?php if($multipage) { ?>
<tr id="tr_">
<th>资讯分页信息</th>
<td><?=$multipage?></td>
</tr>
<?php } ?>
<?php } ?>
<?php } ?>
</table>

<table cellspacing="0" cellpadding="0" width="100%"  class="edittable">
<tr><td>
<div id="fulledit" class="editerTextBox"><div id="message" class="editerTextBox"></div></div>
<script type="text/javascript">
function init() {
<?php if(checkperm('allowpostattach')) { ?>
et = new word("message", "<?=$thevalue['message']?>", 0, 1);
<?php } else { ?>
et = new word("message", "<?=$thevalue['message']?>", 0, 3);
<?php } ?>
}
if(window.Event) {
window.onload = init;
} else {
init();
}
</script>
</td></tr>
</table>
</div>
<?php if(checkperm('allowpostattach') && $page == 1) { ?>
<input name="subjectpic" id="subjectpic" type="hidden" value="<?=$thevalue['picid']?>" />
<div class="colorarea02">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr id="tr_upload">
<th>内容附件<p>您可以上传多个附件，其中，列表中的选中图片附件将作为该资讯的封面图片。<font color=red>注意，上传的附件必须要插入到内容中才能正常显示。</font></p></th>
<td>
<div id="uploadbox">
<div class="tabs">
<a id="localuploadtab" href="javascript:;" onclick="hideshowtags('uploadbox', 'localupload');" class="current">本地上传</a><a id="remoteuploadtab" href="javascript:;" onclick="hideshowtags('uploadbox', 'remoteupload');">远程上传</a>
<?php if($thevalue['allowmax'] > 1) { ?>
<a id="batchuploadtab" href="javascript:;" onclick="hideshowtags('uploadbox', 'batchupload');">批量上传</a>
<?php } ?>
</div>
<div id="localupload">
<table cellpadding="0" cellspacing="0">
<tr>
<th>选择文件:</th>
<td><input name="localfile" type="file" id="localfile" size="28" /></td>
<td valign="bottom" rowspan="3" class="upbtntd"><button onclick="return uploadFile(0)">上传</button></td>
</tr>
<tr>
<th>上传说明:</th>
<td><input name="uploadsubject0" type="text" size="40" /></td>
</tr>
</table>
</div>
<div id="remoteupload" style="display: none;">
<table cellpadding="0" cellspacing="0">
<tr>
<th>输入网址:</th>
<td><input type="text" size="40" name="remotefile" value="http://" /></td>
<td valign="bottom" rowspan="2" class="upbtntd"><button onclick="return uploadFile(1)" />上传</button></td>
</tr>
<tr>
<th>上传说明:</th>
<td><input name="uploadsubject1" type="text" size="40" /></td>
</tr>
</table>
</div>
<div id="batchupload" style="display: none;">
<table summary="" cellpadding="0" cellspacing="6" border="0" width="100%">
<tr>
<td><span id="batchdisplay"><input size="28" class="fileinput" id="batch_1" name="batchfile[]" onchange="insertimg(this)" type="file" /></span></td>
<td class="upbtntd" align="right">
<button id="doupfile" onclick="return uploadFile(2)">上传</button>
</td>
<tr>
<td colspan="2">
<div id="batchpreview"></div>
</td>
</tr>
<tr>
<td colspan="2">
<button id="delall" name="delall" onclick="return delpic()" style="background: transparent; border: none; cursor: pointer; color: red; " >全部清空</button>
</td>
</tr>
</table>
</div>

<p class="textmsg" id="divshowuploadmsg" style="display:none"></p>
<p class="textmsg succ" id="divshowuploadmsgok" style="display:none"></p>
<input type="hidden" id="uploadallowmax" name="uploadallowmax" value="<?=$thevalue['allowmax']?>">
<input type="hidden" name="uploadallowtype" value="<?=$thevalue['allowtype']?>">
<input type="hidden" name="thumbwidth" value="<?=$_SCONFIG['thumbarray']['news']['0']?>">
<input type="hidden" name="thumbheight" value="<?=$_SCONFIG['thumbarray']['news']['1']?>">
<input type="hidden" name="noinsert" value="<?=$thevalue['noinsert']?>">
</div>
<div id="divshowupload"><?=$inserthtml?></div>
</td>
</tr>
</table>
</div>
<?php } ?>
<div class="colorarea03">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr>
<th>获取远程资讯<p>填入网址，点击“获取远程资讯”按钮就可获得网址中的资讯信息</p></th>
<td>
<input type="text" name="referurl" id="referurl" size="60" value="" /><br />
<select name="robotlevel">
<option value="1">简单获取</option>
<option value="2" selected="selected">智能获取</option>
</select>
<span id="scharset" name="scharset" style="display:none">
<select name="charset" id="charset">
<option value="">自动分析编码</option>
<option value="GBK">GBK</option>
<option value="GB2312">GB2312</option>
<option value="BIG5">BIG5</option>
<option value="UTF-8">UTF-8</option>
<option value="UNICODE">UNICODE</option>
</select>
</span>
<input type="button"  value="获取远程资讯" onclick="return robotReferUrl('getrobotmsg');" />
<p class="textmsg" id="divshowrobotmsg" style="display:none"></p>
<p class="textmsg succ" id="divshowrobotmsgok" style="display:none"></p>
</td>
</tr>
</table>
</div>
<?php if($page == 1) { ?>
<div class="colorarea01">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr id="tr_tagname">
<th>TAG<p>TAG就是一篇信息的关键字，只能包含汉英数和下划线，长度不超过10个字符。多个TAG之间用半角空格隔开。</p></th>
<td><input name="tagname" type="text" id="tagname" size="30" value="<?=$thevalue['tagname']?>" /><input type="button"  value="可用TAG" onclick="relatekw();return false;" /></td>
</tr>
</table>
</div>
<?php } ?>
<div class="colorarea02">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr id="tr_">
<th>原创作者</th>
<td><?=$newsauthorstr?></td>
</tr>

<tr id="tr_">
<th>信息来源</th>
<td><?=$newsfromstr?></td>
</tr>

<tr id="tr_newsfromurl">
<th>信息来源URL</th>
<td><input name="newsfromurl" type="text" id="newsfromurl" size="60" value="<?=$thevalue['newsfromurl']?>" /></td>
</tr>
</table>
</div>
<?php if($page == 1 && !$_SGET['folder']) { ?>
<div class="colorarea03">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr><th>自定义信息<p>使用本功能，您可以为该条信息添加更多个性字段内容。点击此处可以<a href="admincp.php?action=customfields&type=news"><u>管理</u></a>自定义信息</p></th>
<td>
<select id="customfieldid" name="customfieldid" onchange="showdivcustomfieldtext()">
<?php if(is_array($cfhtmlselect)) { foreach($cfhtmlselect as $key => $value) { ?>
<option value="<?=$key?>"
<?php if($key==$thevalue['customfieldid']) { ?>
 selected
<?php } ?>
><?=$value?></option>
<?php } } ?>
</select>
</td>
</tr>
<?=$cfhtml?>
</table>
</div>
<?php } ?>
<?php if($page == 1) { ?>
<div class="colorarea01">
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">


<tr id="tr_allowreply">
<th>允许评论</th>
<td><input name="allowreply" type="radio" value="1"
<?php if($thevalue['allowreply']) { ?>
 checked
<?php } ?>
 />允许评论&nbsp;&nbsp;
<input name="allowreply" type="radio" value="0"
<?php if(!$thevalue['allowreply']) { ?>
 checked
<?php } ?>
 />不允许评论&nbsp;&nbsp;</td>
</tr>
<?php if(!$_SGET['folder']) { ?>
<?php if(!checkperm('allowdirectpost')) { ?>
<tr id="tr_folder">
<th>文件夹</th>
<td><input name="folder" type="radio" value="0"
<?php if(!$_SGET['folder']) { ?>
 checked
<?php } ?>
 />发布箱&nbsp;&nbsp;
<input name="folder" type="radio" value="1"
<?php if($_SGET['folder']) { ?>
 checked
<?php } ?>
 />待审箱&nbsp;&nbsp;</td>
</tr>
<?php } ?>
<?php if(checkperm('managecheck')) { ?>
<tr id="tr_digest">
<th>精华级别</th>
<td><input name="digest" type="radio" value="0"
<?php if(!$thevalue['digest']) { ?>
 checked
<?php } ?>
 />非精华&nbsp;&nbsp;
<input name="digest" type="radio" value="1"
<?php if($thevalue['digest']==1) { ?>
 checked
<?php } ?>
 />精华I&nbsp;&nbsp;
<input name="digest" type="radio" value="2"
<?php if($thevalue['digest']==2) { ?>
 checked
<?php } ?>
 />精华II&nbsp;&nbsp;
<input name="digest" type="radio" value="3"
<?php if($thevalue['digest']==3) { ?>
 checked
<?php } ?>
 />精华III&nbsp;&nbsp;</td>
</tr>

<tr id="tr_top">
<th>置顶级别</th>
<td><input name="top" type="radio" value="0"
<?php if(!$thevalue['top']) { ?>
 checked
<?php } ?>
 />非置顶&nbsp;&nbsp;
<input name="top" type="radio" value="1"
<?php if($thevalue['top']==1) { ?>
 checked
<?php } ?>
 />置顶I&nbsp;&nbsp;
<input name="top" type="radio" value="2"
<?php if($thevalue['top']==2) { ?>
 checked
<?php } ?>
 />置顶II&nbsp;&nbsp;
<input name="top" type="radio" value="3"
<?php if($thevalue['top']==3) { ?>
 checked
<?php } ?>
 />置顶III&nbsp;&nbsp;</td>
</tr>

<tr id="tr_grade">
<th>审核等级</th>
<td><input name="grade" type="radio" value="0"
<?php if(!$thevalue['grade']) { ?>
 checked
<?php } ?>
 /><?=$gradearr['0']?>&nbsp;&nbsp;
<input name="grade" type="radio" value="1"
<?php if($thevalue['grade']==1) { ?>
 checked
<?php } ?>
 /><?=$gradearr['1']?>&nbsp;&nbsp;
<input name="grade" type="radio" value="2"
<?php if($thevalue['grade']==2) { ?>
 checked
<?php } ?>
 /><?=$gradearr['2']?>&nbsp;&nbsp;
<input name="grade" type="radio" value="3"
<?php if($thevalue['grade']==3) { ?>
 checked
<?php } ?>
 /><?=$gradearr['3']?>&nbsp;&nbsp;
<input name="grade" type="radio" value="4"
<?php if($thevalue['grade']==4) { ?>
 checked
<?php } ?>
 /><?=$gradearr['4']?>&nbsp;&nbsp;
<input name="grade" type="radio" value="5"
<?php if($thevalue['grade']==5) { ?>
 checked
<?php } ?>
 /><?=$gradearr['5']?>&nbsp;&nbsp;</td>
</tr>
<?php } ?>
<?php } ?>
</table>
</div>
<?php if(!$_SGET['folder']) { ?>
<script language="javascript" type="text/javascript">
makeselectcolor('fontcolor');
loadtitlestyle();
var theboj = $('subject');
textCounter(theboj, 'maxlimit', 80);
</script>
<?php if($_SCONFIG['allowfeed'] && !$thevalue['itemid']) { ?>
<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">
<tr><th>加入事件</th>
<td><input type="radio" checked="checked" value="1" name="addfeed"/>是
<input type="radio"  value="0" name="addfeed"/>否
</td>
</tr>
</table>
<?php } ?>
<?php } ?>
<?php } ?>
<div class="buttons">
<input type="submit" name="thevaluesubmit" value="提交保存" onclick="publish_article();" class="submit">
<input type="reset" name="thevaluereset" value="重置">
<input name="itemid" type="hidden" value="<?=$thevalue['itemid']?>" />
<input name="nid" type="hidden" value="<?=$thevalue['nid']?>" />
<input name="oitemid" type="hidden" value="<?=$thevalue['oitemid']?>" />
<input name="page" type="hidden" value="<?=$page?>" />
<input name="listcount" type="hidden" value="<?=$listcount?>" />
<input name="hash" type="hidden" value="<?=$thevalue['hash']?>" />
<input name="tid" type="hidden" value="<?=$thevalue['tid']?>" />
<input name="type" type="hidden" value="<?=$thevalue['type']?>" />
<input name="valuesubmit" type="hidden" value="yes" />
<?php if($page != 1) { ?>
<input name="catid" type="hidden" value="<?=$thevalue['catid']?>" />
<?php } ?>

<?php if($makepageorder == 1) { ?>
<input name="makepageorder" type="hidden" value="yes" />
<?php } ?>
</div>


</form>
<?php } ?>