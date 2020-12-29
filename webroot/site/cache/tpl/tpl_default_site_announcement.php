<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?>
</div><!--header end-->

<div id="nav">
<div class="main_nav">
<ul>
<?php if(empty($_SCONFIG['defaultchannel'])) { ?>
<li><a href="<?=S_URL?>/">首页</a></li>
<?php } ?>
<?php if(is_array($channels['menus'])) { foreach($channels['menus'] as $key => $value) { ?>
<li><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
</div><!--nav end-->

<div class="column">
<div class="col1">

<div class="global_module margin_bot10 bg_fff">
<div class="global_module3_caption"><h3>
你的位置：<a href="<?=S_URL?>/">首页</a>
&gt;&gt; <?=$title?></h3></div>
<ul class="news_list">
<?php if(is_array($listvalue)) { foreach($listvalue as $value) { ?>
<li>
<h4><a href="<?=$value['url']?>"><?=$value['subject']?></a></h4>
<p class="news_list_caption">发布人: <?=$value['author']?>&nbsp;&nbsp;开始时间: <?=$value['starttime']?>&nbsp;&nbsp;结束时间: <?=$value['endtime']?></p>
<p><?=$value['message']?></p>
</li>
<?php } } ?>
</ul>
<?php if(!$id) { ?>
<?=$multipage?>
<?php } else { ?>
<div class="more_notice"><a href="<?php echo geturl("action/announcement"); ?>">更多公告</a></div>
<?php } ?>
</div>

</div><!--col1 end-->

<div class="col2"><?php block("announcement", "order/displayorder/limit/0,7/cachetime/96400/subjectlen/30/cachename/announce/tpl/data"); ?><div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>最新公告</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['announce'])) { foreach($_SBLOCK['announce'] as $value) { ?>
<li><a href="<?=$value['url']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>

</div><!--col2 end-->
</div><!--column end-->
<?php include template('footer'); ?>