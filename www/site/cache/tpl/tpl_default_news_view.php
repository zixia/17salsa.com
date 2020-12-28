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
<li><a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php if($total != $i) { ?>
 <?=$dot?> 
<?php } ?>
</li><?php $i++;; ?>
<?php } } ?>
</ul>
</div><!--nav end-->

<div class="column">
<div class="col1">
<div class="global_module margin_bot10 bg_fff">
<div class="global_module3_caption"><h3>你的位置：<a href="<?=S_URL?>"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
&gt;&gt; 详细内容
<a href="<?=S_URL?>/cp.php?ac=news&op=add&type=<?=$channel?>" title="在线投稿" class="btn_capiton_op btn_capiton_op_r40" target="_blank">在线投稿</a>
</h3>
</div>
<div id="article">
<h1><?=$news['subject']?></h1>

<div id="article_extinfo">
<div><span>
<a href="<?php echo geturl("action/top/idtype/hot"); ?>" target="_blank" class="add_top10">排行榜</a> 
<a href="javascript:;" class="add_bookmark" onclick="bookmarksite(document.title, window.location.href);">收藏</a> 
<a href="javascript:doPrint();" class="print">打印</a> 
<a href="javascript:;" class="send_frinend" onclick="showajaxdiv('<?=S_URL?>/batch.common.php?action=emailfriend&amp;itemid=<?=$news['itemid']?>', 400);">发给朋友</a> 
<a href="javascript:;" class="report" onclick="report(<?=$news['itemid']?>);">举报</a>
<script src="<?=S_URL?>/batch.postnews.php?ac=fromss&amp;itemid=<?=$news['itemid']?>"></script>
</span>
<?php if(!empty($news['newsfrom'])) { ?>
来源：
<?php if(!empty($news['newsfromurl'])) { ?>
<a href="<?=$news['newsfromurl']?>" target="_blank" title="<?=$news['newsfrom']?>"><?=$news['newsfrom']?></a>
<?php } else { ?>
<?=$news['newsfrom']?>
<?php } ?>
&nbsp;&nbsp;
<?php } ?>
发布者：<a href="<?=S_URL?>/space.php?uid=<?=$news['uid']?>&op=news"><?=$news['newsauthor']?></a> </div>
<div><span>热度<?=$news['hot']?>票&nbsp;&nbsp;浏览<?=$news['viewnum']?>次
<?php if(!empty($_SCONFIG['commstatus'])) { ?>
 【<a class="color_red" href="<?php echo geturl("action/viewcomment/itemid/$news[itemid]"); ?>" target="_blank" title="点击查看">共<?=$news['replynum']?>条评论</a>】【<a class="color_red" href="<?php echo geturl("action/viewcomment/itemid/$news[itemid]"); ?>">我要评论</a>】
<?php } ?>
</span>
时间：<?php sdate('Y年n月d日 H:i', $news["dateline"]); ?></div>
</div>

<div id="article_body">
<?php if(!empty($news['custom']['name'])) { ?>
<div id="article_summary">
<?php if(is_array($news['custom']['key'])) { foreach($news['custom']['key'] as $ckey => $cvalue) { ?>
<h6><?=$news['custom']['name']?></h6>
<p><?=$cvalue['name']?>:<?=$news['custom']['value'][$ckey]?></p>
<?php } } ?>
</div>
<?php } ?>

<?php if(!empty($ads3['viewinad'])) { ?>
<div class="ad_article">
<?=$ads3['viewinad']?>
</div>
<?php } ?>
<?=$news['message']?>
<?php if(empty($multipage)) { ?>
<?php if(is_array($news['attacharr'])) { foreach($news['attacharr'] as $attach) { ?>
<?php if($attach['isimage']) { ?>
<p class="article_download"><a href="<?=$attach['url']?>" target="_blank"><img src="<?=$attach['thumbpath']?>" alt="<?=$attach['subject']?>" /><span><?=$attach['subject']?></span></a></p>
<?php } else { ?>
<p class="article_download"><a href="<?=$attach['url']?>" target="_blank"><?=$attach['filename']?></a>(<?php echo formatsize($attach['size']);; ?>)</p>
<?php } ?>
<?php } } ?>
<?php } ?>
</div>
</div><!--article end-->
<?php if(!empty($relativetagarr)) { ?>
<div id="article_tag">
<strong>TAG:</strong> 
<?php if(is_array($relativetagarr)) { foreach($relativetagarr as $value) { ?>
<?php $svalue = rawurlencode($value);; ?><a href="<?php echo geturl("action/tag/tagname/$svalue"); ?>"><?=$value?></a>
<?php } } ?>
</div>
<?php } ?>

<?php if($multipage) { ?>
<?=$multipage?>
<?php } ?>
<div id="click_div">
<?php include template('news_click'); ?>
</div>

<div id="article_pn"><a class="box_l" href="<?=S_URL?>/batch.common.php?action=viewnews&amp;op=up&amp;itemid=<?=$news['itemid']?>&amp;catid=<?=$news['catid']?>">上一篇</a> <a class="box_r" href="<?=S_URL?>/batch.common.php?action=viewnews&amp;op=down&amp;itemid=<?=$news['itemid']?>&amp;catid=<?=$news['catid']?>">下一篇</a></div>
<?php if(!empty($_SCONFIG['commstatus'])) { ?>
<?php if(!empty($_SCONFIG['viewspace_pernum'])) { ?>
<div class="comment">
<?php if(!empty($commentlist)) { ?>
<?php if(is_array($commentlist)) { foreach($commentlist as $value) { ?>
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
 | <a href="<?=S_URL?>/cp.php?action=click&op=add&clickid=33&groupid=3&idtype=comments&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_comments_<?=$value['cid']?>_33_3" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="up"><span class="color_red">支持</span><span class="color_gray">(<span id="click_<?=$value['cid']?>_33"><?=$value['click_33']?></span>)</span></a>
 | <a href="<?=S_URL?>/cp.php?action=click&op=add&clickid=34&groupid=3&idtype=comments&id=<?=$value['cid']?>&hash=<?php echo md5($value['authorid']."\t".$value['dateline']);; ?>" id="click_comments_<?=$value['cid']?>_34_3" onclick="ajaxmenu(event, this.id, 2000, 'show_clicknum')" class="down">反对<span class="color_gray">(<span id="click_<?=$value['cid']?>_34"><?=$value['click_34']?></span>)</span></a>
<?php } ?>

<?php if(empty($value['authorid']) && $value['authorid'] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1) { ?>
 | <a href="<?php echo geturl("action/viewcomment/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1"); ?>">删除</a>
<?php } ?>
</div>
</div>
<?php } } ?>
<?php } ?>
</div><!--comment end-->
<?php if(checkperm('allowcomment')) { ?>
<div class="sign_msg">
<a name="sign_msg"></a>
<form  action="<?php echo geturl("action/viewcomment/itemid/$news[itemid]/php/1"); ?>" method="post">
<script language="javascript" type="text/javascript" src="<?=S_URL?>/batch.formhash.php?rand=<?php echo rand(1, 999999); ?>"/></script>
<fieldset>
<legend>发表评论</legend>
<div class="sign_msg_login">

</div>
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
<input type="hidden" value="<?=$news['type']?>" name="type" />
<input type="hidden" value="submit" name="submitcomm" />
<input type="hidden" id="itemid" name="itemid" value="<?=$news['itemid']?>" />
<input type="hidden" id="upcid" name="upcid" value="" size="5" />
<input type="hidden" id="type" name="type" value="<?=$news['type']?>" size="5" />

</div>

</fieldset>
</form>
<p class="sign_tip">网友评论仅供网友表达个人看法，并不表明本网同意其观点或证实其描述。</p>
</div><!--sign_msg end-->
<?php } ?>
<?php } ?>
<div id="comment_op"><a href="<?php echo geturl("action/viewcomment/itemid/$news[itemid]"); ?>" class="view" target="_blank">查看全部回复</a><span>【已有<?=$news['replynum']?>位网友发表了看法】</span></div>
<?php } ?>
</div>
<?php if(!empty($ads3['pagecenterad'])) { ?>
<div class="ad_mainbody"><?=$ads3['pagecenterad']?></div>
<?php } ?>
<!--图文资讯显示--><?php block("spacenews", "type/$channel/haveattach/2/showattach/1/order/i.lastpost DESC/limit/0,12/subjectlen/14/subjectdot/0/cachetime/8000/cachename/picnews"); ?>
<?php if($_SBLOCK['picnews']) { ?>
<div class="global_module margin_bot10">
<div class="global_module1_caption"><h3>图文资讯</h3></div>
<ul class="globalnews_piclist clearfix">
<?php if(is_array($_SBLOCK['picnews'])) { foreach($_SBLOCK['picnews'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><img src="<?=$value['a_thumbpath']?>" alt="<?=$value['subjectall']?>" /></a><span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></span></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
</div><!--col1 end-->

<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end--><?php block("spacenews", "catid/$thecat[subcatid]/order/i.dateline DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/13800/cachename/newnews"); ?>
<?php if(!empty($_SBLOCK['newnews'])) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>最新报道</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['newnews'])) { foreach($_SBLOCK['newnews'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<?php block("spacenews", "type/$channel/dateline/2592000/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,20/cachetime/89877/subjectlen/30/subjectdot/0/cachename/hotnews2"); ?>
<?php if($_SBLOCK['hotnews2']) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>精彩推荐</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['hotnews2'])) { foreach($_SBLOCK['hotnews2'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<!--相关资讯-->
<?php if(!empty($news['relativeitemids'])) { ?>
<?php block("spacenews", "itemid/$news[relativeitemids]/order/i.dateline DESC/limit/0,20/cachetime/17680/cachename/relativeitem/tpl/data"); ?>
<?php if(!empty($_SBLOCK['relativeitem']) ) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>相关资讯</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['relativeitem'])) { foreach($_SBLOCK['relativeitem'] as $ikey => $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<?php } ?>
<?php if(!empty($ads3['siderad'])) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>网络资源</h3></div>
<div class="ad_sidebar">
<?=$ads3['siderad']?>
</div>

</div>
<?php } ?>
</div><!--col2 end-->
</div><!--column end-->
<?php if(!empty($ads3['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads3['pagefootad']?></div>
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
<script language="javascript" type="text/javascript">
<!--
addMediaAction('article_body');
addImgLink("article_body");
//-->
</script>
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
<?php include template('footer'); ?>