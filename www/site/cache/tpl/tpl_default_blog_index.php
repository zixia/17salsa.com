<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads = getad('system', 'uchblog', '1'); ; ?>
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
<?php if($key == 'uchblog' ) { ?>
 class="current"
<?php } ?>
><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
</div><!--nav end-->

<div class="column">
<div class="col1"><?php block("uchblog", "picflag/2/dateline/604800/order/viewnum DESC/limit/0,5/cachetime/286528/subjectlen/48/subjectdot/0/cachename/blogpic"); ?><div class="col3">
<div id="focus_turn">
<?php if(!empty($_SBLOCK['blogpic'])) { ?>
<ul id="focus_pic"><?php $j = 0; ?>
<?php if(is_array($_SBLOCK['blogpic'])) { foreach($_SBLOCK['blogpic'] as $key => $value) { ?>
<?php $pcurrent = ($j == 0 ? 'current' : 'normal');; ?><li class="<?=$pcurrent?>"><a href="<?=$value['url']?>">
<?php if(substr($value['pic'], -10) == '.thumb.jpg') { ?>
<img src="<?php echo substr($value['pic'], 0, -10); ?>" alt="<?=$value['subject']?>" />
<?php } else { ?>
<img src="<?=$value['pic']?>" alt="<?=$value['subject']?>" />
<?php } ?>
</a></li><?php $j++; ?>
<?php } } ?>
</ul>
<ul id="focus_tx"><?php $i = 0; ?>
<?php if(is_array($_SBLOCK['blogpic'])) { foreach($_SBLOCK['blogpic'] as $bvalue) { ?>
<?php $current = ($i == 0 ? 'current' : 'normal');; ?><li class="<?=$current?>"><a href="<?=$value['url']?>"><?=$bvalue['subject']?></a></li><?php $i++;; ?>
<?php } } ?>
</ul>
<div id="focus_opacity"></div>
<?php } ?>
</div><!--focus_turn end--><?php block("uchblog", "picflag/2/order/viewnum DESC/limit/0,3/cachetime/286528/subjectlen/16/subjectdot/0/cachename/blog2pic"); ?><div id="blog_pic_story">
<h3>图片故事</h3>
<ul class="globalnews_piclist">
<?php if(is_array($_SBLOCK['blog2pic'])) { foreach($_SBLOCK['blog2pic'] as $key => $value) { ?>
<li><a href="<?=$value['url']?>">
<?php if(substr($value['pic'], -10) == '.thumb.jpg') { ?>
<img src="<?php echo substr($value['pic'], 0, -10); ?>" alt="<?=$value['subject']?>" />
<?php } else { ?>
<img src="<?=$value['pic']?>" alt="<?=$value['subject']?>" />
<?php } ?>
</a><span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></span></li>
<?php } } ?>
</ul>
</div>
</div><!--col3 end--><?php block("uchspace", "order/updatetime DESC/limit/0,10/cachetime/86475/cachename/uchspace"); ?><div class="col4" id="blog_new">
<h3>最近更新</h3>
<ul class="global_avatar_list clearfix">
<?php if(is_array($_SBLOCK['uchspace'])) { foreach($_SBLOCK['uchspace'] as $value) { ?>
<li><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><img src="<?=UC_API?>/avatar.php?uid=<?=$value['uid']?>&size=small" alt="" /></a><span><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span></li>
<?php } } ?>
</ul><?php block("uchblog", "dateline/604800/order/viewnum DESC,dateline DESC/limit/0,9/cachetime/124200/subjectlen/42/subjectdot/0/cachename/blogreply"); ?><ul class="txt">
<?php if(is_array($_SBLOCK['blogreply'])) { foreach($_SBLOCK['blogreply'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div><!--col4 end-->
</div><!--col1 end-->
<div class="col2">
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?open=1&rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end--><?php block("uchblog", "dateline/2592000/order/viewnum DESC,dateline DESC/limit/0,13/cachetime/97643/subjectlen/26/subjectdot/0/cachename/bloghottop"); ?>
<?php if(!empty($_SBLOCK['bloghottop'])) { ?>
<?php $bloghottop = @array_slice($_SBLOCK['bloghottop'], 0, 3);; ?>
<?php } ?>
<div id="blog_top">
<h3>日志之星</h3>
<?php if(is_array($bloghottop)) { foreach($bloghottop as $value) { ?>
<dl>
<dt><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><img src="<?=UC_API?>/avatar.php?uid=<?=$value['uid']?>&size=small" alt="" /></a><span><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span></dt>
<dd>
<a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a><br />
浏览(<?=$value['viewnum']?>) | 评论(<?=$value['replynum']?>)
</dd>
</dl>
<?php } } ?>
</div><!--blog_top end-->
</div><!--col2 end-->
</div><!--column end-->
<?php if(!empty($ads['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagecenterad']?></div>
<?php } ?>
<?php block("uchspace", "order/viewnum DESC,friendnum DESC/limit/0,15/cachetime/87530/cachename/hotspace"); ?>
<?php if(!empty($_SBLOCK['hotspace'])) { ?>
<div class="column global_module">
<div class="global_module1_caption"><h3>热门空间</h3></div>
<ul class="global_avatar_list blog_hot_avatar">
<?php if(is_array($_SBLOCK['hotspace'])) { foreach($_SBLOCK['hotspace'] as $value) { ?>
<li><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><img alt="" src="<?=UC_API?>/avatar.php?uid=<?=$value['uid']?>&size=small"/></a><span><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span></li>
<?php } } ?>
</ul>
</div>
<?php } ?>
<div class="column">
<div class="col1 blog_margin_hack"><?php block("uchblog", "order/dateline DESC/limit/0,12/cachetime/6980/subjectlen/38/subjectdot/0/showdetail/1/messagelen/80/messagedot/1/cachename/newblog"); ?><div class="global_module margin_bot10">
<?php if(!empty($_SBLOCK['newblog'])) { ?>
<div class="global_module1_caption"><h3>最新日志</h3><a class="more" href="<?php echo geturl("action/bloglist/order/dateline"); ?>">更多&gt;&gt;</a></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['newblog'])) { foreach($_SBLOCK['newblog'] as $value) { ?>
<li><span class="box_r"><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
<?php } ?>
</div><?php block("uchblog", "dateline/604800/order/viewnum DESC,dateline DESC/limit/0,12/cachetime/124200/subjectlen/38/subjectdot/0/showdetail/1/messagelen/80/messagedot/1/cachename/blogreply"); ?><div class="global_module margin_bot10">
<div class="global_module1_caption"><h3>本周热门日志</h3><a class="more" href="<?php echo geturl("action/bloglist/order/viewnum"); ?>">更多&gt;&gt;</a></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['blogreply'])) { foreach($_SBLOCK['blogreply'] as $value) { ?>
<li><span class="box_r"><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div><?php block("uchblog", "dateline/604800/order/replynum DESC,dateline DESC/limit/0,12/cachetime/97643/subjectlen/38/subjectdot/0/showdetail/1/messagelen/380/messagedot/1/cachename/bloghot"); ?><div class="global_module margin_bot10">
<div class="global_module1_caption"><h3>本周热评日志</h3><a class="more" href="<?php echo geturl("action/bloglist/order/replynum"); ?>">更多&gt;&gt;</a></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['bloghot'])) { foreach($_SBLOCK['bloghot'] as $value) { ?>
<li><span class="box_r"><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>			

</div><!--col1 end-->

<div class="col2"><?php block("uchblog", "dateline/2592000/order/replynum DESC,dateline DESC/limit/0,10/cachetime/1264200/subjectlen/24/subjectdot/0/cachename/bloghotreply"); ?><div class="global_module global_tx_list6">
<div class="global_module2_caption"><h3>本月热评</h3></div>
<ul>
<?php if(is_array($_SBLOCK['bloghotreply'])) { foreach($_SBLOCK['bloghotreply'] as $value) { ?>
<li><span class="box_l"><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></span><span class="box_r"><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span></li>
<?php } } ?>
</ul>
</div>
<?php if(!empty($_SBLOCK['bloghottop'])) { ?>
<?php $bloghottop2 = @array_slice($_SBLOCK['bloghottop'], 3, 13);; ?>
<?php } ?>
<div class="global_module global_tx_list6">
<div class="global_module2_caption"><h3>本月点击排行</h3></div>
<ul>
<?php if(is_array($bloghottop2)) { foreach($bloghottop2 as $value) { ?>
<li><span class="box_l"><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></span><span class="box_r"><a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a></span></li>
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