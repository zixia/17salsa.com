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
<div class="global_module3_caption"><h3>你的位置：<a href="<?=S_URL?>/"><?=$_SCONFIG['sitename']?></a>
&gt;&gt; <?=$title?></h3></div>
<?php if($tag['spacenewsnum']) { ?>
<?php block("spacetag", "tagid/$tag[tagid]/order/st.dateline DESC/perpage/20/cachetime/86400/cachename/news/tpl/data"); ?><ul class="global_tx_list4">
<?php if(is_array($_SBLOCK['news'])) { foreach($_SBLOCK['news'] as $value) { ?>
<li><span class="box_r"><a href="<?php echo geturl("uid/$value[uid]"); ?>"><?=$value['username']?></a></span>[<?=$channels['menus'][$value['type']]['name']?>] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SBLOCK['news_multipage']) { ?>
 <?=$_SBLOCK['news_multipage']?>
<?php } ?>
</div>

</div><!--col1 end-->
<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end-->

<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>相关TAG</h3></div>
<ul class="tag_list clearfix">
<?php if($tag['relativetags']) { ?>
<?php if(is_array($tag['relativetags'])) { foreach($tag['relativetags'] as $key => $value) { ?>
<a href="<?php echo geturl("action/tag/tagname/$key"); ?>"><?=$value?></a>
<?php } } ?>
<?php } else { ?>
暂无相关TAG
<?php } ?>
</ul>
</div>
</div><!--col2 end-->
</div><!--column end-->
<?php include template('footer'); ?>