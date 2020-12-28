<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('header'); ?><?php $ads = getad('system', 'top', '1'); ; ?>
<?php if(!empty($ads['pageheadad']) ) { ?>
<div class="ad_header"><?=$ads['pageheadad']?></div>
<?php } ?>
</div>

<div id="nav">
<div class="main_nav">
<ul>
<?php if(empty($_SCONFIG['defaultchannel'])) { ?>
<li><a href="<?=S_URL?>/index.php">首页</a></li>
<?php } ?>
<?php if(is_array($channels['menus'])) { foreach($channels['menus'] as $key => $value) { ?>
<li
<?php if($key == $channel ) { ?>
 class="current"
<?php } ?>
><a href="<?=$value['url']?>"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
</div>
</div><!--nav end-->
<?php if(!empty($ads['pagecenterad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagecenterad']?></div>
<?php } ?>
<div class="column">
<?php if($groupid) { ?>
<div id="mood_banner">
<?php if($clickgroup['icon']) { ?>
<img src="images/click/<?=$clickgroup['icon']?>" alt="<?=$clickgroup['grouptitle']?>排行榜">
<div class="show_toplist">
<em><a href="javascript:contributeop('top_op');">&gt;&gt;查看其他排行榜</a></em>
<div style="display: none;" id="top_op">
<a href="<?php echo geturl("action/top/"); ?>">热度排行榜</a>
<?php if(is_array($clickgroups)) { foreach($clickgroups as $value) { ?>
<a href="<?php echo geturl("action/top/groupid/$value[groupid]"); ?>"><?=$value['grouptitle']?>排行榜</a>
<?php } } ?>
</div>
</div>
<?php } else { ?>
<div id="top_rank_caption">
<h3><?=$clickgroup['grouptitle']?>排行榜</h3>
<div class="other_top">
<em><a href="javascript:contributeop('top_op');">查看其他排行榜</a></em>
<div style="display: none;" id="top_op">
<a href="<?php echo geturl("action/top/"); ?>">热度排行榜</a>
<?php if(is_array($clickgroups)) { foreach($clickgroups as $value) { ?>
<a href="<?php echo geturl("action/top/groupid/$value[groupid]"); ?>"><?=$value['grouptitle']?>排行榜</a>
<?php } } ?>
</div>
</div>
</div>
<?php } ?>
</div>

<div id="mood_top">
<?php if(is_array($click)) { foreach($click as $v) { ?>
<?php if($clickgroup['block']=='spacenews') { ?>
<?php block("$clickgroup[block]", "dateline/86400/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } elseif($clickgroup['block']=='model') { ?>
<?php block("model", "name/$modelarr[modelname]/dateline/86400/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } else { ?>
<?php block("$clickgroup[block]", "dateline/86400/order/click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } ?>
<div class="global_module">
<div class="global_module2_caption">
<?php if($v['icon']) { ?>
<img src="images/click/<?=$v['icon']?>" alt="<?=$v['name']?>">
<?php } ?>
<h3><?=$v['name']?></h3><span class="rank_catalog">日排行</span></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<?php if($clickgroup['block']=='spacecomment') { ?>
<?php $value = formatcomment($value, array(), 1);; ?><?php $value['subject'] = $value['message'];; ?>
<?php } ?>
<li><span class="box_r">
<?php echo $value['click_'.$v['clickid']];; ?>
票</span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php if($clickgroup['block']=='spacenews') { ?>
<?php block("$clickgroup[block]", "dateline/604800/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } elseif($clickgroup['block']=='model') { ?>
<?php block("model", "name/$modelarr[modelname]/dateline/604800/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } else { ?>
<?php block("$clickgroup[block]", "dateline/604800/order/click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php } ?>
<div class="global_module right_fix">
<div class="global_module2_caption">
<?php if($v['icon']) { ?>
<img src="images/click/<?=$v['icon']?>" alt="<?=$v['name']?>">
<?php } ?>
<h3><?=$v['name']?></h3><span class="rank_catalog">周排行</span></div>
<ul class="global_tx_list1">
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<?php if($clickgroup['block']=='spacecomment') { ?>
<?php $value = formatcomment($value, array(), 1);; ?><?php $value['subject'] = $value['message'];; ?>
<?php } ?>
<li><span class="box_r">
<?php echo $value['click_'.$v['clickid']];; ?>
票</span><a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
</ul>
</div>
<?php } } ?>
</div><!--mood_top end-->
<?php } else { ?>
<div id="top_rank">
<div id="top_rank_caption">
<h3>热度排行榜</h3>
<ul>
<li<?=$timearr['0']?>><a href="<?php echo geturl("action/top"); ?>"><span>全部</span></a></li>
<li<?=$timearr['2']?>><a href="<?php echo geturl("action/top/time/2"); ?>"><span>2小时内</span></a></li>
<li<?=$timearr['4']?>><a href="<?php echo geturl("action/top/time/4"); ?>"><span>4小时内</span></a></li>
<li<?=$timearr['8']?>><a href="<?php echo geturl("action/top/time/8"); ?>"><span>8小时内</span></a></li>
<li<?=$timearr['24']?>><a href="<?php echo geturl("action/top/time/24"); ?>"><span>24小时内</span></a></li>
<li<?=$timearr['168']?>><a href="<?php echo geturl("action/top/time/168"); ?>"><span>一周内</span></a></li>
</ul>
<div class="other_top">
<em><a href="javascript:contributeop('top_op');">查看其他排行榜</a></em>
<div style="display: none;" id="top_op">
<a href="<?php echo geturl("action/top/"); ?>">热度排行榜</a>
<?php if(is_array($clickgroups)) { foreach($clickgroups as $value) { ?>
<a href="<?php echo geturl("action/top/groupid/$value[groupid]"); ?>"><?=$value['grouptitle']?>排行榜</a>
<?php } } ?>
</div>
</div>
</div>
<table>
<tbody>
<tr class="top_rank_2caption">
<td width="40">排名</td>
<td width="510">标题</td>
<td width="100">类别</td>
<td width="130">时间</td>
<td>热度</td>
</tr>
<?php if($list) { ?>
<?php if(is_array($list)) { foreach($list as $value) { ?>
<tr>
<td><?=$value['i']?></td>
<td><a href="<?php echo geturl("action/viewnews/itemid/$value[itemid]"); ?>"><?=$value['subject']?></a></td>
<td class="color_gray">
[<a href="<?php echo geturl("action/$value[type]"); ?>" class="color_gray"><?=$channels['menus'][$value['type']]['name']?></a>] 
<a href="<?php echo geturl("action/category/catid/$value[catid]"); ?>"><?=$_SGLOBAL['category'][$value['catid']]['name']?></a></td>
<td class="color_gray"><?php sdate('Y-m-d H:i', $value[dateline]); ?></td>
<td class="color_brown"><?=$value['hot']?>票</td>
</tr>
<?php } } ?>
<?php } else { ?>
<tr><td colspan="5"><div class="user_no_body">没有符合条件的信息</div></td></tr>
<?php } ?>
</tbody>
</table>
</div><!--top_rank end-->
<?php } ?>
</div>
<?php if(!empty($ads['pagefootad'])) { ?>
<div class="ad_pagebody"><?=$ads['pagefootad']?></div>
<?php } ?>
<script type="text/javascript">
function contributeop(id) {
if($(id).style.display != 'block') {
$(id).style.display = 'block';
} else {
$(id).style.display = 'none';
}	
}
</script>
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