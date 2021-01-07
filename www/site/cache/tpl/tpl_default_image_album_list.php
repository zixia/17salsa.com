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
<div class="column global_module bg_fff">
<div class="global_module1_caption"><h3>精彩相册</h3></div>
<div class="image_gallery_list clearfix"><?php block("uchphoto", "order/updatetime DESC/perpage/21/subjectlen/20/subjectdot/0/cachetime/8200/cachename/uchimage"); ?><!--uchimage-->
<?php if(is_array($_SBLOCK['uchimage'])) { foreach($_SBLOCK['uchimage'] as $key => $value) { ?>
<dl>
<dt><div><a href="<?=$value['url']?>"><img src="<?=$value['pic']?>" alt="" /></a></div></dt>
<dd>
<h6><a href="<?=$value['url']?>"><?=$value['albumname']?></a></h6>
<p><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></p>
<p><?=$value['picnum']?>张照片</p>
<p>更新:
<?php if(($_SGLOBAL['timestamp'] - $value['updatetime']) > 86400) { ?>
 <?php sdate("Y-m-d", $value[updatetime]); ?>
<?php } else { ?>
<?php echo intval(($_SGLOBAL['timestamp'] - $value['updatetime']) / 3600 + 1);; ?>小时之前
<?php } ?>
</p>
</dd>
</dl>
<?php } } ?>
</div>
<?php if($_SBLOCK['uchimage_multipage']) { ?>
<?=$_SBLOCK['uchimage_multipage']?>
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
<?php include template('footer'); ?>