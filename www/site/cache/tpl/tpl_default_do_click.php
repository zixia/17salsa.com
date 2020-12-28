<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
</div>
<?php } ?>

<?php if($_GET['op'] == 'show') { ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
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
<div class="global_module3_caption"><h3>你的位置：<a href="<?=S_URL?>"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
&gt;&gt; 表态查看
</h3>
</div>
<div id="click_div">
<?php } ?>
<?php if($idtype == 'models') { ?>
<?php include template('model_click'); ?>
<?php } else { ?>
<?php include template('news_click'); ?>
<?php } ?>

<?php if(empty($_SGLOBAL['inajax'])) { ?>
</div>
</div>
</div><!--col1 end-->

<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end-->
</div><!--col2 end-->
</div><!--column end-->
<?php } ?>
<?php } ?>
<?php include template('footer'); ?>