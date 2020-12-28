<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads2 = getad('system', 'uchimage', '2'); ; ?>
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
<?php if($key == 'uchimage' ) { ?>
 class="current"
<?php } ?>
><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
</div><!--nav end-->
<?php if(!empty($ads2['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads2['pagecenterad']?></div>
<?php } ?>
<?php block("uchphoto", "albumid/$aid/order/updatetime/cachename/uch_photo"); ?><!--uch_photo-->
<div class="column global_module bg_fff">
<div class="global_module3_caption"><?php $values = $_SBLOCK['uch_photo']['0'];; ?><h3>
<em style="float:right; padding:0 5px 0 0;">
<a href="<?=$_SC['uchurl']?>/space.php?uid=<?=$values['uid']?>&do=album&id=<?=$values['albumid']?>" title="转至<?=$channels['menus']['uchimage']['name']?>" class="vote" target="_blank">转至<?=$channels['menus']['uchimage']['name']?></a>
</em>
您的位置：<a href="<?=S_URL?>/"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
&gt;&gt; <a href="<?=S_URL?>/space.php?uid=<?=$values['uid']?>&op=uchphoto"><?=$values['username']?>的相册</a>
&gt;&gt; 浏览相册
</h3></div>

<div class="image_list_userinfo">
<dl>
<dt><div><img src="<?=$values['pic']?>" alt="<?=$values['albumname']?>"></div></dt>
<dd>
<h2><?=$values['albumname']?></h2>
<h4><a href="<?=S_URL?>/space.php?uid=<?=$values['uid']?>&op=uchphoto">查看 <?=$values['username']?> 的全部相册</a></h4>
<p><?=$values['picnum']?>张照片 | 更新
<?php if(($_SGLOBAL['timestamp'] - $values['updatetime']) > 86400) { ?>
 <?php sdate("Y-m-d", $values[updatetime]); ?>
<?php } else { ?>
<?php echo intval(($_SGLOBAL['timestamp'] - $values['updatetime']) / 3600 + 1);; ?>小时之前
<?php } ?>
</p>
</dd>
</dl>
</div><!--image_list_userinfo end-->

<ul class="global_piclist image_list clearfix">
<?php if($photolist) { ?>
<?php if(is_array($photolist)) { foreach($photolist as $pkey => $pvalue) { ?>
<li><div><a href="<?=$pvalue['url']?>"><img alt="" src="<?=$pvalue['pic']?>"/></a></div></li>
<?php } } ?>
<?php } ?>
</ul>
<?php if(!empty($multipage)) { ?>
<?=$multipage?>
<?php } ?>
</div>
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
<?php if(!empty($ads2['pageoutindex'])) { ?>
<?=$ads2['pageoutindex']?>
<?php } ?>
<?php include template('footer'); ?>