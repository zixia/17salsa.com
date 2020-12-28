<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads3 = getad('system', $channel, '3'); ; ?>
<?php if(!empty($ads3['pageheadad']) ) { ?>
<div class="ad_header"><?=$ads3['pageheadad']?></div>
<?php } ?>
</div><!--header end-->

<div id="nav">
<div class="main_nav">
<ul>
<?php if(empty($_SCONFIG['defaultchannel'])) { ?>
<li><a href="<?=S_URL?>/">首页</a></li>
<?php } ?>
<?php if(is_array($channels['menus'])) { foreach($channels['menus'] as $key => $value) { ?>
<li 
<?php if($key == $channel ) { ?>
 class="current"
<?php } ?>
><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div><?php block("category", "type/$channel/isroot/1/order/c.displayorder/limit/0,100/cachetime/80800/cachename/category"); ?><ul class="ext_nav clearfix"><?php $dot = '|'; ?><?php $total = count($_SBLOCK['category']); ?><?php $i = 1;; ?>
<?php if(is_array($_SBLOCK['category'])) { foreach($_SBLOCK['category'] as $value) { ?>
<li><a href="
<?php if($channels['menus'][$type]['type'] == 'model') { ?>
<?=$siteurl?>/m.php?name=<?=$modelsinfoarr['modelname']?>&mo_catid=<?=$value['catid']?>
<?php } else { ?>
<?=$value['url']?>
<?php } ?>
"><?=$value['name']?></a>
<?php if($total != $i) { ?>
 <?=$dot?> 
<?php } ?>
</li><?php $i++;; ?>
<?php } } ?>
</ul>
</div><!--nav end-->
<?php if(!empty($ads3['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads3['pagecenterad']?></div>
<?php } ?>
<script type="text/javascript">
<!--
function clearcommentmsg() {
if($('message').value == '<?=$_SCONFIG['commdefault']?>') $('message').value = '';
}
function addcommentmsg() {
if($('message').value == '') $('message').value = '<?=$_SCONFIG['commdefault']?>';
}
//-->
</script>

<div class="column">
<div class="col1">
<div class="comment_caption">
<ul>
<li
<?php if(!$order) { ?>
 class="current"
<?php } ?>
><a href="<?php echo geturl("action/viewcomment/type/$type/itemid/$item[itemid]"); ?>"><div class="tab_all">全部评论
<?php if(!$order) { ?>
<em>(评论共<span class="color_red"><?=$listcount?></span>条,显示<span class="color_red"><?=$perpage?></span>条)</em>
<?php } ?>
</div></a></li>
<li
<?php if($order==1) { ?>
 class="current"
<?php } ?>
><a href="<?php echo geturl("action/viewcomment/type/$type/itemid/$item[itemid]/order/1"); ?>"><div class="tab_up"><span>最新支持</span>
<?php if($order==1) { ?>
<em>（支持数大于2的评论）</em>
<?php } ?>
</div></a></li>
<li
<?php if($order==2) { ?>
 class="current"
<?php } ?>
><a href="<?php echo geturl("action/viewcomment/type/$type/itemid/$item[itemid]/order/2"); ?>"><div class="tab_up"><span>最多支持</span>
<?php if($order==2) { ?>
<em>（支持数最多的评论）</em>
<?php } ?>
</div></a></li>
<li
<?php if($order==3) { ?>
 class="current"
<?php } ?>
><a href="<?php echo geturl("action/viewcomment/type/$type/itemid/$item[itemid]/order/3"); ?>"><div class="tab_down"><span>最新反对</span>
<?php if($order==3) { ?>
<em>（反对数大于支持数的评论）</em>
<?php } ?>
</div></a></li>
<li
<?php if($order==4) { ?>
 class="current"
<?php } ?>
><a href="<?php echo geturl("action/viewcomment/type/$type/itemid/$item[itemid]/order/4"); ?>"><div class="tab_down"><span>最多反对</span>
<?php if($order==4) { ?>
<em>（反对数最多的评论）</em>
<?php } ?>
</div></a></li>
</ul>
</div><!--comment_caption end-->
<div class="comment_cont">
<div class="arti_title"><h1>评论：<?=$item['subject']?></h1><a class="color_red" href="
<?php if($channels['menus'][$type]['type'] == 'model') { ?>
<?php echo geturl("action/model/name/$modelsinfoarr[modelname]/itemid/$item[itemid]"); ?>
<?php } else { ?>
<?php echo geturl("action/viewnews/itemid/$item[itemid]"); ?>
<?php } ?>
" target="_blank">[查看全文]</a></div>
<div class="arti_summary"><?=$item['message']?></div><?php $gv = $clickgroups['3'];; ?>
<?php if(is_array($iarr)) { foreach($iarr as $value) { ?>
<div class="comm_list">
<div class="title">
<div class ="from_info">
<span class="author"><?=$_SCONFIG['sitename']?>
<?php if(!$value['hidelocation']) { ?>
<?php if($value['iplocation']!='LAN') { ?>
<?=$value['iplocation']?>
<?php } else { ?>
火星
<?php } ?>
<?php } ?>
网友
<?php if(!empty($value['authorid']) && !$value['hideauthor']) { ?>
<a href="<?=S_URL?>/space.php?uid=<?=$value['authorid']?>">[<?=$value['author']?>]</a>
<?php } ?>
</span>
<?php if($_SCONFIG['commshowip']) { ?>
ip:
<?php if($value['hideip']) { ?>
*.*.*.*
<?php } else { ?>
<?=$value['ip']?>
<?php } ?>
<?php } ?>
</div>
<span class="post_time"><?php sdate("Y-m-d H:i:s", $value["dateline"], 1); ?></span>
<a name="cid_<?=$value['cid']?>"></a>
</div>
<div id="cid_<?=$value['cid']?>" class="body">
<?=$value['message']?>
</div>
<div class="comm_op">
<a href="javascript:;" onclick="clearcommentmsg();getQuote(<?=$value['cid']?>);">引用</a>
 | <a href="javascript:;" onclick="clearcommentmsg();$('message').focus();addupcid(<?=$value['cid']?>);" class="replay">回复</a>
 
<?php if($gv['status']) { ?>
 | <a href="<?=S_URL?>/do.php?action=click&op=add&clickid=33&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_<?=$value['cid']?>_33" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="clicknum_<?=$value['cid']?>_33"><?=$value['click_33']?></span>)</span></a>
 | <a href="<?=S_URL?>/do.php?action=click&op=add&clickid=34&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_<?=$value['cid']?>_34" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="clicknum_<?=$value['cid']?>_34"><?=$value['click_34']?></span>)</span></a>
<?php } ?>
</div>
</div>
<?php } } ?>
<?php if($multipage) { ?>
<?=$multipage?>
<?php } ?>

<?php if(checkperm('allowcomment')) { ?>
<div class="sign_msg">
<form  action="<?php echo geturl("action/viewcomment/itemid/$item[itemid]/php/1"); ?>" method="post">
<script language="javascript" type="text/javascript" src="<?=S_URL?>/batch.formhash.php?rand=<?php echo rand(1, 999999); ?>"/></script>
<fieldset>
<legend>发表评论</legend>
<textarea style="background:#F9F9F9 url(<?=S_URL?>/images/comment/<?=$_SCONFIG['commicon']?>) no-repeat 50% 50%;" id="message" cols="60" rows="4" name="message" onclick="clearcommentmsg();hideelement('imgseccode');" onblur="addcommentmsg();" onkeydown="ctlent(event,'postcomm');" /><?=$_SCONFIG['commdefault']?></textarea>
<div class="sign_msg_sub">
<?php if($_SGLOBAL['supe_uid']) { ?>
<?php if(checkperm('allowanonymous')) { ?>
<label for="signcheck_01"><input class="input_checkbox" type="checkbox" id="signcheck_01" name="hideauthor" value="1" />匿名</label>
<?php } ?>

<?php if(checkperm('allowhideip')) { ?>
<label for="signcheck_02"><input class="input_checkbox" type="checkbox" id="signcheck_02" name="hideip" value="1" />隐藏IP</label>
<?php } ?>

<?php if(checkperm('allowhidelocation')) { ?>
<label for="signcheck_03"><input class="input_checkbox" type="checkbox" id="signcheck_03" name="hidelocation" value="1" />隐藏位置</label>
<?php } ?>

<?php if($_SCONFIG['allowfeed']) { ?>
<label for="signcheck_04"><input class="input_checkbox" type="checkbox" id="signcheck_04" name="addfeed" checked="checked">加入事件</label>
<?php } ?>
<?php } ?>
<?php if(empty($_SCONFIG['noseccode'])) { ?>
<span class="authcode_sub"><label style="margin-right:0;" for="seccode">验证码：</label> 
<input type="text" class="input_tx" size="10" id="seccode" name="seccode" maxlength="4" onfocus="showelement('imgseccode')" /> 
<img style="display:none;" id="imgseccode" class="img_code" src="<?=S_URL?>/do.php?action=seccode" onclick="newseccode('imgseccode');" alt="seccode" title="看不清？点击换一张" />
<a class="changcode_txt" title="看不清？点击换一张" href="javascript:showelement('imgseccode');newseccode('imgseccode');">换一张</a>
</span>
<?php } ?>
<input type="submit" value="发表" name="searchbtn" onclick="return submitcheck();" class="input_search"/>
<input type="hidden" value="submit" name="submitcomm" />
<input type="hidden" value="<?=$item['type']?>" name="type" />
<input type="hidden" id="itemid" name="itemid" value="<?=$item['itemid']?>" />
<input type="hidden" id="upcid" name="upcid" value="" size="5" />
<input type="hidden" id="type" name="type" value="<?=$channel?>" size="5" />

</div>

</fieldset>
</form>
<p class="sign_tip">网友评论仅供网友表达个人看法，并不表明本网同意其观点或证实其描述。</p>
</div><!--sign_msg end-->
<?php } ?>
</div><!--comment_cont end-->
</div><!--col1 end-->

<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end--><?php block("spacecomment", "type/$channel/itemid/$item[itemid]/click_33/2/order/click_33 DESC, dateline DESC/limit/0,10/cachetime/900/cachename/hotcomment/tpl/data"); ?>
<?php if($_SBLOCK['hotcomment']) { ?>
<div id="hot_comment">
<h3>热门评论</h3>
<?php if(is_array($_SBLOCK['hotcomment'])) { foreach($_SBLOCK['hotcomment'] as $value) { ?>
<?php $value = formatcomment($value, array(), 1);; ?><div class="comm_list">
<div class="title">
<div class ="from_info"><span class="author">网友
<?php if(!empty($value['authorid']) && !$value['hideauthor']) { ?>
<a href="<?=S_URL?>/space.php?uid=<?=$value['authorid']?>">[<?=$value['author']?>]</a>
<?php } ?>
</span></div>
<span class="post_time"><?php sdate("m-d H:i", $value[dateline], 1); ?></span>
</div>
<div class="body">
<p class="new"><?=$value['message']?></p>
</div>
<div class="comm_op">
 <a href="javascript:;" onclick="clearcommentmsg();$('message').focus();addupcid(<?=$value['cid']?>);" class="replay">回复</a>
 
<?php if($gv['status']) { ?>
 | <a href="<?=S_URL?>/do.php?action=click&op=add&clickid=33&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_<?=$value['cid']?>_33_hot" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="clicknum_<?=$value['cid']?>_33_hot"><?=$value['click_33']?></span>)</span></a>
 | <a href="<?=S_URL?>/do.php?action=click&op=add&clickid=34&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_<?=$value['cid']?>_34_hot" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="clicknum_<?=$value['cid']?>_34_hot"><?=$value['click_34']?></span>)</span></a>
<?php } ?>
</div>
</div>
<?php } } ?>
</div><!--hot_comment end-->
<?php } ?>
</div><!--col2 end-->
</div>
<?php if(!empty($ads3['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads3['pagefootad']?></div>
<?php } ?>

<?php if(!empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])) { ?>
<?php if(!empty($ads3['pagemovead'])) { ?>
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div style="position: absolute; left: 6px; top: 6px;">
<?=$ads3['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
<div style="position: absolute; right: 6px; top: 6px;">
<?=$ads3['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
</div>
<?php } ?>

<?php if(!empty($ads3['pageoutad'])) { ?>
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
<?=$ads3['pageoutad']?>
</div>
</div>
<?php } ?>
<script type="text/javascript" src="<?=S_URL?>/include/js/floatadv.js"></script>
<script type="text/javascript">
<?php if(!empty($ads3['pageoutad'])) { ?>
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<?php } ?>

<?php if(!empty($ads3['pagemovead'])) { ?>
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<?php } ?>
</script>
<?php } ?>
<?php if(!empty($ads3['pageoutindex'])) { ?>
<?=$ads3['pageoutindex']?>
<?php } ?>
<?php include template('footer'); ?>