<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads3 = getad('system', 'uchblog', '3'); ; ?>
<?php if(!empty($ads3['pageheadad']) ) { ?>
<div class="ad_header"><?=$ads3['pageheadad']?></div>
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
<?php if(!empty($ads3['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads3['pagecenterad']?></div>
<?php } ?>
<div class="column" id="blog_detail">
<div class="box_l">
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>作者</h3></div>
<div class="blog_user">
<a href="<?=S_URL?>/space.php?uid=<?=$blogdetail['uid']?>"><img src="<?=UC_API?>/avatar.php?uid=<?=$blogdetail['uid']?>&size=middle" alt="" /></a><br/>
<a href="<?=S_URL?>/space.php?uid=<?=$blogdetail['uid']?>"><?=$blogdetail['username']?></a><br/>
[<a href="<?=S_URL?>/space.php?uid=<?=$blogdetail['uid']?>&op=uchblog">查看TA的全部日志</a>]
</div>
</div><?php block("uchblog", "order/dateline DESC/uid/$blogdetail[uid]/limit/0,10/cachetime/86549/subjectlen/22/subjectdot/0/cachename/blog"); ?><div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>其它日志</h3></div>
<ul class="global_tx_list3">
<?php if(is_array($_SBLOCK['blog'])) { foreach($_SBLOCK['blog'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php if(!empty($ads3['siderad'])) { ?>
<div class="global_module margin_bot10 bg_fff">
<div class="global_module2_caption"><h3>网络资源</h3></div>
<div class="ad_sidebar">
<?=$ads3['siderad']?>
</div>

</div>
<?php } ?>
</div><!--box_l end-->

<div class="box_r global_module bg_fff">
<div class="global_module3_caption"><h3>
<em style="float:right; padding:0 5px 0 0;">
<a href="<?=$_SC['uchurl']?>/space.php?uid=<?=$blogdetail['uid']?>&do=blog&id=<?=$blogdetail['blogid']?>" title="转至<?=$channels['menus']['uchblog']['name']?>" class="vote" target="_blank">转至<?=$channels['menus']['uchblog']['name']?></a>
</em>
你的位置：<a href="<?=S_URL?>/"><?=$_SCONFIG['sitename']?></a>
<?php if(is_array($guidearr)) { foreach($guidearr as $value) { ?>
&gt;&gt; <a href="<?=$value['url']?>"><?=$value['name']?></a>
<?php } } ?>
&gt;&gt; <a href="<?=S_URL?>/space.php?uid=<?=$blogdetail['uid']?>&op=uchblog"><?=$blogdetail['username']?>的日志</a>
&gt;&gt; 详细内容</h3></div>
<div id="blog_article">
<h1><?=$blogdetail['subject']?></h1>
<div class="blog_tipinfo">发布:<?php sdate('Y-n-d H:i', $blogdetail["dateline"]); ?> | 作者:<a href="<?=S_URL?>/space.php?uid=<?=$blogdetail['uid']?>&op=uchblog"><?=$blogdetail['username']?></a> | 来源:本站 | 查看:<?=$blogdetail['viewnum']?>次 | 字号: <a href="javascript:doZoom('12');">小</a> <a href="javascript:doZoom('14');">中</a> <a href="javascript:doZoom('16');">大</a></div>
<div id="blog_body">
<?php if(!empty($ads3['viewinad'])) { ?>
<div class="ad_article">
<?=$ads3['viewinad']?>
</div>
<?php } ?>
<?=$blogdetail['message']?>
</div>
<div class="blog_op">
<a href="javascript:doPrint();">打印</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="bookmarksite(document.title, window.location.href)">收藏此页</a>&nbsp;|&nbsp;
<a href="javascript:;" onclick="showajaxdiv('<?=S_URL?>/batch.common.php?action=emailfriend&amp;uid=<?=$blogdetail['uid']?>&amp;blogid=<?=$blogdetail['blogid']?>', 400);">推荐给好友</a>&nbsp;|&nbsp;<a href="<?=$_SC['uchurl']?>/cp.php?ac=common&op=report&idtype=blogid&id=<?=$blogdetail['blogid']?>" >举报</a>
</div>
</div>	

<div class="comment">
<?php if(!empty($blogcomment)) { ?>
<?php $i=0;; ?>
<?php if(is_array($blogcomment)) { foreach($blogcomment as $value) { ?>
<?php if($i % 2 == 0) { ?>
<div class="comm_list bg_f8">
<?php } else { ?>
<div class="comm_list">
<?php } ?>
<div class="title">
<a href="<?=S_URL?>/space.php?uid=<?=$value['authorid']?>"><img src="<?=UC_API?>/avatar.php?uid=<?=$value['authorid']?>&size=small" alt="" style="width: 30px;" /></a>
<a href="<?=S_URL?>/space.php?uid=<?=$value['authorid']?>"><?=$value['author']?></a><?php sdate('Y-n-d H:i:s', $value["dateline"]); ?></div>
<div class="body">
<?=$value['message']?>
</div>
</div><?php $i++; ?>
<?php } } ?>
<?php } ?>
</div>	

<div id="comment_op"><a href="<?=$_SC['uchurl']?>/space.php?uid=<?=$blogdetail['uid']?>&do=blog&id=<?=$blogdetail['blogid']?>" class="write" target="_blank">我也来说两句</a> <a href="<?=$_SC['uchurl']?>/space.php?uid=<?=$blogdetail['uid']?>&do=blog&id=<?=$blogdetail['blogid']?>" class="view" target="_blank">查看全部回复</a></div>	

</div>
</div>

</div><!--column end-->
<?php if(!empty($ads3['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads3['pagefootad']?></div>
<?php } ?>

<?php if(!empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])) { ?>
<?php if(!empty($ads3['pagemovead'])) { ?>
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div style="position: absolute; left: 6px; top: 6px;">
<?=$ads3['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
<div style="position: absolute; right: 6px; top: 6px;">
<?=$ads3['pagemovead']?>
<br />
<img src="<?=S_URL?>/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
</div>
</div>
<?php } ?>

<?php if(!empty($ads3['pageoutad'])) { ?>
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
<?=$ads3['pageoutad']?>
</div>
</div>
<?php } ?>
<script type="text/javascript" src="<?=S_URL?>/include/js/floatadv.js"></script>
<script type="text/javascript">
<?php if(!empty($ads3['pageoutad'])) { ?>
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<?php } ?>

<?php if(!empty($ads3['pagemovead'])) { ?>
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<?php } ?>
</script>
<?php } ?>
<?php if(!empty($ads3['pageoutindex'])) { ?>
<?=$ads3['pageoutindex']?>
<?php } ?>
<?php include template('footer'); ?>