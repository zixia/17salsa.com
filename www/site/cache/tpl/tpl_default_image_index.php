<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads = getad('system', 'uchimage', '1'); ; ?>
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
            <li style="padding-top:0px;margin-right:0px;"><a href="<?=S_URL?>" style="padding:0 0 0 0;"><img src="<?=S_URL?>/images/logos.gif" alt="<?=$_SCONFIG['sitename']?>" /></a></li>
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

<div class="column"><?php block("uchphoto", "order/updatetime DESC/limit/0,6/cachetime/87480/cachename/uchphoto"); ?><div class="col1 global_module" id="image_focus">
<div id="image_focus_big">
<ul><?php $i=0;; ?>
<?php if(is_array($_SBLOCK['uchphoto'])) { foreach($_SBLOCK['uchphoto'] as $key => $value) { ?>
<li 
<?php if($i == 0) { ?>
class="current"
<?php } ?>
s><a href="<?=$value['url']?>">
<?php if(substr($value['pic'], -10) == '.thumb.jpg') { ?>
<img src="<?php echo substr($value['pic'], 0, -10); ?>" alt="<?=$value['subject']?>" />
<?php } else { ?>
<img src="<?=$value['pic']?>" alt="<?=$value['subject']?>" />
<?php } ?>
</a></li><?php $i++; ?>
<?php } } ?>
</ul>
</div>
<div id="image_focus_small">
<h3>最近更新</h3>
<ul class="global_piclist">
<?php if(is_array($_SBLOCK['uchphoto'])) { foreach($_SBLOCK['uchphoto'] as $key => $bvalue) { ?>
<li><div><a href="<?=$bvalue['url']?>"><img src="<?=$bvalue['pic']?>" alt="<?=$bvalue['subject']?>" /></a></div></li>
<?php } } ?>
</ul>
</div>

</div>
<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?open=1&rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end--><?php block("poll", "order/dateline DESC/limit/0,3/cachetime/80000/cachename/poll"); ?><div class="super_notice margin_bot0">
<h3>调查:</h3>
<ul>
<?php if(empty($_SBLOCK['poll'])) { ?>
<li>暂时没有调查</li>
<?php } else { ?>
<?php if(is_array($_SBLOCK['poll'])) { foreach($_SBLOCK['poll'] as $value) { ?>
<li><a href="<?=$value['url']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } ?>
</ul>
</div><!--调查end-->

</div>
</div><!--column end--><?php block("uchphoto", "updatetime/2592000/order/picnum DESC/limit/0,12/cachetime/87480/subjectlen/12/subjectdot/0/cachename/uchphoto2"); ?><div class="column global_module">
<div class="global_module1_caption"><h3>本月相册达人</h3></div>
<div class="image_user_list">
<?php if(is_array($_SBLOCK['uchphoto2'])) { foreach($_SBLOCK['uchphoto2'] as $key => $value) { ?>
<dl>
<dt><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>&op=uchphoto"><img src="<?=UC_API?>/avatar.php?uid=<?=$value['uid']?>&size=small" alt="" /></a></dt>
<dd>
<p><a href="<?=$value['url']?>"><?=$value['albumname']?></a></p>
<p><a class="color_black" href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>&op=uchphoto"><?=$value['username']?></a></p>
</dd>
</dl>
<?php } } ?>
</div>
</div><!--column end-->
<?php if(!empty($ads['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagecenterad']?></div>
<?php } ?>
<?php block("uchphoto", "order/picnum DESC/limit/0,12/cachetime/87480/subjectlen/12/subjectdot/0/cachename/uchphototop"); ?><div class="column global_module">
<div class="global_module1_caption"><h3>精彩相册</h3><a class="more" href="<?php echo geturl("action/imagelist"); ?>">更多&gt;&gt;</a></div>
<div class="image_gallery_list clearfix">
<?php if(is_array($_SBLOCK['uchphototop'])) { foreach($_SBLOCK['uchphototop'] as $key => $value) { ?>
<dl>
<dt><div><a href="<?=$value['url']?>"><img src="<?=$value['pic']?>" alt="" /></a></div></dt>
<dd>
<h6><a href="<?=$value['url']?>"><?=$value['albumname']?></a></h6>
<p><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>&op=uchphoto"><?=$value['username']?> 的相册</a></p>
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

</div>
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