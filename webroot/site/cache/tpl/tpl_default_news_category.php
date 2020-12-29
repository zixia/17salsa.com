<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads2 = getad('system', $channel, '2'); ; ?>
<?php if(!empty($ads2['pageheadad']) ) { ?>
<div class="ad_header"><?=$ads2['pageheadad']?></div>
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
<div class="global_module3_caption"><h3>
你的位置：<a href="<?=S_URL?>"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
&nbsp;<a href="<?php echo geturl("action/rss/catid/$thecat[subcatid]"); ?>"><img src="<?=S_URL?>/templates/<?=$_SCONFIG['template']?>/images/icon_rss_ext.gif"></a>
<?php if(checkperm('allowpost')) { ?>
<a href="<?=S_URL?>/cp.php?ac=news&op=add&type=<?=$channel?>" title="在线投稿" class="btn_capiton_op" target="_blank">在线投稿</a>
<?php } ?>
</h3>
</div>
<!--根分类最新日志列表-->
<?php if($_SGET['page'] < 2 || empty($_SGET['mode'])) { ?>
<?php block("spacenews", "perpage/20/catid/$thecat[subcatid]/order/i.dateline DESC/cachename/newlist"); ?>
<?php if($_SBLOCK['newlist']) { ?>
<ul class="global_tx_list4">
<?php if(is_array($_SBLOCK['newlist'])) { foreach($_SBLOCK['newlist'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SBLOCK['newlist_multipage']) { ?>
<?=$_SBLOCK['newlist_multipage']?>
<?php } ?>
<?php } ?>
</div>
<!--论坛资源列表-->
<?php if(!empty($thecat['bbsmodel'])) { ?>
<?php if($_SGET['page']<2 || !empty($_SGET['mode'])) { ?>
<?php $_SGET['mode']='bbs';; ?><?php block("bbsthread", "perpage/20/$thecat[blockparameter]/cachename/bbsthreadlist/tpl/data"); ?>
<?php if($_SBLOCK['bbsthreadlist']) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module1_caption"><h3>论坛资源</h3></div>
<ul class="global_tx_list4">
<?php if(is_array($_SBLOCK['bbsthreadlist'])) { foreach($_SBLOCK['bbsthreadlist'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
<?php if($_SBLOCK['bbsthreadlist_multipage']) { ?>
<?=$_SBLOCK['bbsthreadlist_multipage']?>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
<?php } ?>
<?php block("category", "upid/$thecat[catid]/order/c.displayorder/limit/0,100/cachetime/10900/cachename/subarr/tpl/data"); ?>
<?php if($_SGET['page']<2) { ?>
<?php $i = 1;; ?>
<?php if(!empty($_SBLOCK['subarr'])) { ?>
<div class="catalog_list clearfix">
<?php if(is_array($_SBLOCK['subarr'])) { foreach($_SBLOCK['subarr'] as $ckey => $cat) { ?>
<?php $ctime=1800+30*$ckey;; ?><?php block("spacenews", "catid/$cat[subcatid]/order/i.dateline DESC/limit/0,6/subjectlen/36/subjectdot/0/cachetime/$ctime/cachename/subnewlist/tpl/data"); ?>
<?php if($_SBLOCK['subnewlist']) { ?>
<?php if(($i % 2) == 0) { ?>
<div class="global_module box_r">
<?php } else { ?>
<div class="global_module">
<?php } ?>
<div class="global_module1_caption"><h3><?=$cat['name']?></h3><a href="<?php echo geturl("action/category/catid/$cat[catid]"); ?>" class="more">更多&gt;&gt;</a></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['subnewlist'])) { foreach($_SBLOCK['subnewlist'] as $value) { ?>
<li><span class="box_r"><?php sdate("m-d", $value["dateline"]); ?></span><a href="<?=$value['url']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div><?php $i++;; ?>
<?php } } ?>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
<?php if(!empty($ads2['pagecenterad'])) { ?>
<div class="ad_mainbody"><?=$ads2['pagecenterad']?></div>
<?php } ?>

<?php if($_SGET['page']< 2) { ?>
<!--图文资讯显示--><?php block("spacenews", "catid/$thecat[subcatid]/haveattach/2/showattach/1/order/i.lastpost DESC/limit/0,12/subjectlen/14/subjectdot/0/cachetime/8000/cachename/picnews"); ?>
<?php if($_SBLOCK['picnews']) { ?>
<div class="global_module margin_bot10">
<div class="global_module1_caption"><h3>图文资讯</h3></div>
<ul class="globalnews_piclist clearfix">
<?php if(is_array($_SBLOCK['picnews'])) { foreach($_SBLOCK['picnews'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><img src="<?=$value['a_thumbpath']?>" alt="<?=$value['subjectall']?>" /></a><span><a href="<?=$value['url']?>"><?=$value['subject']?></a></span></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<?php } ?>
</div><!--col1 end-->
<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end-->
<?php if($_SBLOCK['subarr']) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>子分类</h3></div>
<ul class="special_activity clearfix">
<?php if(is_array($_SBLOCK['subarr'])) { foreach($_SBLOCK['subarr'] as $value) { ?>
<li><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
       
<?php if($thecat['thumb'] || $thecat['note']) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3><?=$thecat['name']?></h3></div>
<div class="sidebar_album_info">
<?php if($thecat['thumb']) { ?>
<p><img src="<?=A_URL?>/<?=$thecat['thumb']?>" alt="" /></p>
<?php } ?>

<?php if($thecat['note']) { ?>
<p><?=$thecat['note']?></p>
<?php } ?>
</div>
</div>
<?php } ?>
<?php block("spacenews", "catid/$thecat[subcatid]/order/i.dateline DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/13800/cachename/newnews"); ?><div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>最新评论</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['newnews'])) { foreach($_SBLOCK['newnews'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div><?php block("spacenews", "catid/$thecat[subcatid]/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,10/cachetime/89877/subjectlen/26/subjectdot/0/cachename/hotnews2"); ?>
<?php if($_SBLOCK['hotnews2']) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>精华推荐</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['hotnews2'])) { foreach($_SBLOCK['hotnews2'] as $value) { ?>
<li><span class="box_r"><?=$value['viewnum']?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>

<?php if(!empty($ads2['siderad'])) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>网络资源</h3></div>
<div class="ad_sidebar">
<?=$ads2['siderad']?>
</div>

</div>
<?php } ?>
</div><!--col2 end-->
</div><!--column end-->
<?php if(!empty($ads2['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads2['pagefootad']?></div>
<?php } ?>

<?php if(!empty($ads2['pagemovead']) || !empty($ads2['pageoutad'])) { ?>
<?php if(!empty($ads2['pagemovead'])) { ?>
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div style="position: absolute; left: 6px; top: 6px;">
<?=$ads2['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
<div style="position: absolute; right: 6px; top: 6px;">
<?=$ads2['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
</div>
<?php } ?>

<?php if(!empty($ads2['pageoutad'])) { ?>
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
<?=$ads2['pageoutad']?>
</div>
</div>
<?php } ?>
<script type="text/javascript" src="<?=S_URL?>/include/js/floatadv.js"></script>
<script type="text/javascript">
<?php if(!empty($ads2['pageoutad'])) { ?>
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<?php } ?>

<?php if(!empty($ads2['pagemovead'])) { ?>
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<?php } ?>
</script>
<?php } ?>
<?php include template('footer'); ?>