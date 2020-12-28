<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads2 = getad('system', 'uchblog', '2'); ; ?>
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
<?php if($key == 'uchblog' ) { ?>
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
<div class="column global_module margin_bot10 bg_fff">
<div class="global_module3_caption"><h3>
<em style="float:right; padding:0 5px 0 0;">
<a href="<?=$_SC['uchurl']?>/network.php?ac=blog" title="转至<?=$channels['menus']['uchblog']['name']?>" class="vote" target="_blank">转至<?=$channels['menus']['uchblog']['name']?></a>
</em>
你的位置：<a href="<?=S_URL?>/"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
<?php if($_SGET['order'] == 'viewnum') { ?>
(以浏览量进行排列)
<?php } elseif($_SGET['order'] == 'replynum') { ?>
(以评论数进行排列)
<?php } else { ?>
(以发布时间进行排列)
<?php } ?>
</h3></div><?php block("uchblog", "perpage/15/order/$order/cachetime/7200/showdetail/1/messagelen/380/messagedot/1/cachename/uchbloghot"); ?><!--uchblog-->
<?php if(is_array($_SBLOCK['uchbloghot'])) { foreach($_SBLOCK['uchbloghot'] as $key => $value) { ?>
<div class="blog_info_list">
<div class="box_l">
<a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><img src="<?=UC_API?>/avatar.php?uid=<?=$value['uid']?>&size=small" alt=""/></a><br/>
<a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a><br/>
<?php if(($_SGLOBAL['timestamp'] - $value['dateline']) > 86400) { ?>
 <?php sdate("Y-m-d", $value[dateline]); ?>
<?php } else { ?>
<?php echo intval(($_SGLOBAL['timestamp'] - $value['dateline']) / 3600 + 1);; ?>小时之前
<?php } ?>
</div>
<div class="box_r">
<h5><a href="<?=$value['url']?>"><?=$value['subject']?></a></h5>
<div class="blog_signtx"><?=$value['message']?></div>
<p class="blog_info">浏览(<?=$value['viewnum']?>)&nbsp;|&nbsp;评论(<?=$value['replynum']?>)</p>
</div>
</div>
<?php } } ?>
<?php if(!empty($_SBLOCK['uchbloghot_multipage'])) { ?>
<?=$_SBLOCK['uchbloghot_multipage']?>
<?php } ?>
</div>


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