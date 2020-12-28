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
</div><!--nav end--><?php block("friendlink", "order/displayorder/limit/0,100/cachetime/11600/namelen/32/cachename/friendlink/tpl/data"); ?><div class="column global_module bg_fff">
<div class="global_module3_caption"><h3>你的位置：<a href="<?php echo geturl("action/site/type/link"); ?>">链接</a></h3></div><?php $imglink=$txtlink="";; ?>
<?php if(is_array($_SBLOCK['friendlink'])) { foreach($_SBLOCK['friendlink'] as $value) { ?>
<?php if($value['logo']) { ?>
<?php $imglink .= "<a href=\"".$value['url']."\" target=\"_blank\" title=\"".$value['description']."\"><img src=\"".$value['logo']."\" alt=\"".$value['description']."\"  border=\"0\" /></a>\n";; ?>
<?php } else { ?>
<?php $txtlink .= "<li><a href=\"".$value['url']."\" title=\"".$value['description']."\" target=\"_blank\">".$value['name']."</a></li>\n";; ?>
<?php } ?>
<?php } } ?>
<?php if(!empty($imglink)) { ?>
<div class="links_img">
<?=$imglink?>
</div>
<?php } ?>

<?php if(!empty($txtlink)) { ?>
<div class="links_tx">
<ul class="s_clear">
<?=$txtlink?>
</ul>
</div>
<?php } ?>
</div>
<?php include template('footer'); ?>