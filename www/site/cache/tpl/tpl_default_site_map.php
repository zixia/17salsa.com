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

<div class="column global_module bg_fff">
<div class="global_module3_caption">
<h3>你的位置：<a href="<?=S_URL?>/"><?=$_SCONFIG['sitename']?></a>
&gt;&gt; <?=$title?></h3></div>
<div id="site_map">
<?php if(is_array($channels['menus'])) { foreach($channels['menus'] as $key => $value) { ?>
<?php if($key == 'news' || $value['upnameid']=='news') { ?>
<!--资讯根分类 开始--><?php block("category", "type/$key/isroot/1/limit/0,100/order/c.displayorder/cachetime/10800/cachename/category"); ?><div>
<h1><?=$value['name']?></h1>
<ul>
<?php if(is_array($_SBLOCK['category'])) { foreach($_SBLOCK['category'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['name']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<?php } } ?>
<!--模型列表开始-->
<?php if(is_array($modelarr)) { foreach($modelarr as $value) { ?>
<div>
<h1><?=$value['modelalias']?></h1>
<ul>
<?php if(is_array($value['categories'])) { foreach($value['categories'] as $key => $val) { ?>
<li><a href="<?=S_URL?>/m.php?name=<?=$value['modelname']?>&mo_catid=<?=$key?>" title="<?=$val?>"><?=$val?></a></li>
<?php } } ?>
</ul>
</div>
<?php } } ?>
<!--模型列表结束-->
<!--论坛版块列表100个 开始-->
<?php if(!empty($channels['menus']['bbs'])) { ?>
<?php block("bbsforum", "type/forum/limit/0,100/order/f.displayorder/cachetime/21500/cachename/bbsforum/tpl/data"); ?><div>
<h1><?=$channels['menus']['bbs']['name']?></h1>
<ul>
<?php if(is_array($_SBLOCK['bbsforum'])) { foreach($_SBLOCK['bbsforum'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['name']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } ?>

<?php if(!empty($channels['menus']['uchimage'])) { ?>
<div>
<h1><?=$channels['menus']['uchimage']['name']?></h1>
</div>
<?php } ?>

<?php if(!empty($channels['menus']['uchblog'])) { ?>
<div>
<h1><?=$channels['menus']['uchblog']['name']?></h1>
</div>
<?php } ?>
</div>

</div>
<?php include template('footer'); ?>