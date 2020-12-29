<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads = getad('system', $channel, '1'); ; ?>
<?php if(!empty($ads['pageheadad']) ) { ?>
<div class="ad_header"><?=$ads['pageheadad']?></div>
<?php } ?>
</div><!--header end-->

<div id="nav">
<div class="main_nav">
<ul>
<?php if(empty($_SCONFIG['defaultchannel'])) { ?>
<li><a href="<?=S_URL?>/">首页</a></li>
<?php } ?>
            <li style="padding-top:0px;margin-right:1px;"><a href="<?=S_URL?>" style="padding:0 0 0 0;"><img src="<?=S_URL?>/images/logos.gif" alt="<?=$_SCONFIG['sitename']?>" /></a></li>
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
</div><!--nav end--><?php block("spacenews", "type/$channel/haveattach/2/showattach/1/showdetail/1/order/i.dateline DESC/limit/0,11/subjectlen/48/subjectdot/0/messagelen/170/messagedot/1/cachetime/11930/cachename/picnews"); ?><div class="column">
<div class="col1">
<div class="clearfix">
<div class="col3">

<div id="focus_turn">
<?php if(!empty($_SBLOCK['picnews'])) { ?>
<?php $picnews = @array_slice($_SBLOCK['picnews'], 0, 5);; ?><ul id="focus_pic"><?php $j = 0; ?>
<?php if(is_array($picnews)) { foreach($picnews as $pkey => $pvalue) { ?>
<?php $pcurrent = ($j == 0 ? 'current' : 'normal');; ?><li class="<?=$pcurrent?>"><a href="<?php echo geturl("action/viewnews/itemid/$pvalue[itemid]"); ?>"><img src="<?=$pvalue['a_filepath']?>" alt="<?=$pvalue['subject']?>" /></a></li><?php $j++; ?>
<?php } } ?>
</ul>
<ul id="focus_tx"><?php $i = 0; ?>
<?php if(is_array($picnews)) { foreach($picnews as $key => $value) { ?>
<?php $current = ($i == 0 ? 'current' : 'normal');; ?><li class="<?=$current?>"><a href="<?=$value['url']?>"><?=$value['subject']?></a></li><?php $i++; ?>
<?php } } ?>
</ul>
<div id="focus_opacity"></div>
<?php } ?>
</div><!--focus_turn end-->

</div><!--col3 end-->
<!--最新资讯--><?php block("spacenews", "type/news/grade/1/catid/3,4,6,8/order/i.dateline DESC/limit/0,9/cachetime/85400/subjectlen/40/subjectdot/0/cachename/newnews1"); ?><div class="col4" id="hot_news">
<h3>最新资讯</h3>
<?php if(is_array($_SBLOCK['newnews1'])) { foreach($_SBLOCK['newnews1'] as $value) { ?>
<div class="hot_news_list">
<h4><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></h4>
</div>
<?php } } ?>
</div><!--col4 end-->
</div>

<!--图文资讯显示-->
<?php if(!empty($_SBLOCK['picnews'])) { ?>
<?php $picnews2 = @array_slice($_SBLOCK['picnews'], 5, 11);; ?>
<?php } ?>
<div class="global_module margin_bot10">
<div class="global_module1_caption"><h3>图文资讯</h3></div>
<ul class="globalnews_piclist clearfix">
<?php if(is_array($picnews2)) { foreach($picnews2 as $value) { ?>
<li><a href="<?=$value['url']?>"><img src="<?=$value['a_thumbpath']?>" alt="<?=$value['subjectall']?>"/></a><span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></span></li>
<?php } } ?>
</ul>
</div>
<?php if(!empty($ads['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagecenterad']?></div>
<?php } ?>
<div class="catalog_list clearfix">
<!--各分类最新资讯列表--><?php $i = 1;; ?>
<?php if(is_array($_SBLOCK['category'])) { foreach($_SBLOCK['category'] as $ckey => $cat) { ?>
<?php $cachetime = 1800+30*$ckey;; ?><?php block("spacenews", "catid/$cat[subcatid]/order/i.dateline DESC/limit/0,6/cachetime/$cachetime/subjectlen/36/subjectdot/0/cachename/newslist"); ?> 
<?php if(($i % 2) == 0) { ?>
<div class="global_module box_r">
<?php } else { ?>
<div class="global_module">
<?php } ?>
<div class="global_module1_caption"><h3><?=$cat['name']?></h3><a href="<?php echo geturl("action/category/catid/$cat[catid]"); ?>" class="more">更多&gt;&gt;</a></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['newslist'])) { foreach($_SBLOCK['newslist'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[dateline]); ?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div><?php $i++;; ?>
<?php } } ?>
</div>


</div><!--col1 end-->

<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end--><?php block("tag", "order/spacenewsnum DESC/limit/0,30/cachetime/88008/cachename/hottag/tpl/data"); ?>        <div class="global_module margin_bot10 bg_fff"><!--magzine -->
            <!-- a border="0" href="http://www.17salsa.com/dl/mag/2010/10/index.html"><img width="250" height="337" src="http://17salsa.com/dl/mag/img/cover_201010_medium.gif"></a-->
<div class="global_module2_caption"><h3>热门标签</h3></div>
<ul class="tag_list clearfix">
<?php if(is_array($_SBLOCK['hottag'])) { foreach($_SBLOCK['hottag'] as $value) { ?>
<li><a href="<?=$value['url']?>"><?=$value['tagname']?></a>(<?=$value['spacenewsnum']?>)</li>
<?php } } ?>
</ul>
</div>

<!--最新评论资讯显示--><?php block("spacenews", "type/$channel/order/i.lastpost DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/7500/cachename/newnews"); ?><div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>最新评论</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['newnews'])) { foreach($_SBLOCK['newnews'] as $value) { ?>
<li><span class="box_r"><?php sdate('m-d', $value[lastpost]); ?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>

<!--月度评论热点--><?php block("spacenews", "type/$channel/lastpost/2592000/order/i.replynum DESC/limit/0,10/cachetime/75400/subjectlen/26/subjectdot/0/cachename/replyhot"); ?><div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>月度评论</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['replyhot'])) { foreach($_SBLOCK['replyhot'] as $value) { ?>
<li><span class="box_r"><?=$value['replynum']?></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>

</div><!--col2 end-->
</div><!--column end-->
<?php if(!empty($ads['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagefootad']?></div>
<?php } ?>

<?php if(!empty($ads['pagemovead']) || !empty($ads['pageoutad'])) { ?>
<?php if(!empty($ads['pagemovead'])) { ?>
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div style="position: absolute; left: 6px; top: 6px;">
<?=$ads['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
<div style="position: absolute; right: 6px; top: 6px;">
<?=$ads['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
</div>
<?php } ?>

<?php if(!empty($ads['pageoutad'])) { ?>
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
<?=$ads['pageoutad']?>
</div>
</div>
<?php } ?>
<script type="text/javascript" src="<?=S_URL?>/include/js/floatadv.js"></script>
<script type="text/javascript">
<?php if(!empty($ads['pageoutad'])) { ?>
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<?php } ?>

<?php if(!empty($ads['pagemovead'])) { ?>
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<?php } ?>
</script>
<?php } ?>
<?php if(!empty($ads['pageoutindex'])) { ?>
<?=$ads['pageoutindex']?>
<?php } ?>
<?php include template('footer'); ?>