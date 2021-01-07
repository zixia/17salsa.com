<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?><?php $cgid = 2;; ?><?php $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);; ?><?php $counts = $clickcounts[$cgid]; ?>
<?php if($gv['status']) { ?>
<?php if($gv['allowtop']) { ?>
<div id="article_state"><div class="box_r"><a href="<?php echo geturl("action/top/idtype/items/groupid/$cgid"); ?>" target="_blank">[<?=$gv['grouptitle']?>排行榜]</a></div></div>
<?php } ?>
<div id="article_op" class="clearfix"><?php $value = $clicks[$cgid];; ?><a class="aop_up" href="<?=S_URL?>/do.php?action=click&op=add&clickid=9&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_9" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><em>顶:</em><?=$value['9']['clicknum']?></a>
<a class="aop_down" href="<?=S_URL?>/do.php?action=click&op=add&clickid=10&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_10" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><em>踩:</em><?=$value['10']['clicknum']?></a>
</div>
<?php if($gv['allowspread']) { ?>
<div id="article_state">
<ul class="state_newstop clearfix">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $v) { ?>
<?php block("spacenews", "dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<li>[<?=$v['name']?>最多的] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } } ?>
</ul>
</div>
<?php } ?>
<?php } ?>
<div id="article_mark">
<div class="dashed_botline">
<table width="100%"><tbody><?php $cgid = 4;; ?><?php $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);; ?><?php $counts = $clickcounts[$cgid]; ?>
<?php if($gv['status']) { ?>
<tr><td colspan="2">
<?php if($gv['allowtop']) { ?>
<div class="box_r"><a href="<?php echo geturl("action/top/idtype/items/groupid/$cgid"); ?>" target="_blank">[<?=$gv['grouptitle']?>排行榜]</a></div>
<?php } ?>
对本文中的事件或人物打分:</td></tr>
<tr>
<td style="width:370px">
<div class="rating">
<ul class="rating_bad">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $value) { ?>
<?php if($value['score'] == 0) { ?>
</ul>
<ul class="rating_normal">
<?php } ?>
<li class="rating<?=$value['name']?>"><a href="
<?php if($value['score']) { ?>
<?=S_URL?>/do.php?action=click&op=add&clickid=<?=$value['clickid']?>&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_<?=$value['clickid']?>
<?php } else { ?>
javascript:;
<?php } ?>
" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><?=$value['name']?></a><em><?=$value['score']?></em></li>
<?php if($value['score'] == 0) { ?>
</ul>
<ul class="rating_good">
<?php } ?>
<?php } } ?>
</div>
</td>
<td style="width:190px">当前平均分：<span
<?php if($counts['average']) { ?>
 class="color_red"
<?php } ?>
><?=$counts['average']?></span> （<?=$counts['clicknum']?>次打分）</td>
</tr>
<?php if($gv['allowspread']) { ?>
<tr>
<td colspan="2">
<div id="article_state" style="margin: 0;">
<ul class="state_newstop clearfix">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $v) { ?>
<?php block("spacenews", "dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<li>[给<?=$v['name']?>最多的] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } } ?>
</ul>
</div>
</td>
</tr>
<?php } ?>
<?php } ?>
<?php $cgid = 5;; ?><?php $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);; ?><?php $counts = $clickcounts[$cgid]; ?>
<?php if($gv['status']) { ?>
<tr><td style="padding-top:20px;" colspan="2">
<?php if($gv['allowtop']) { ?>
<div class="box_r"><a href="<?php echo geturl("action/top/idtype/items/groupid/$cgid"); ?>" target="_blank">[<?=$gv['grouptitle']?>排行榜]</a></div>
<?php } ?>
对本篇资讯内容的质量打分:</td></tr>
<tr>
<td style="width:370px">
<div class="rating">
<ul class="rating_bad">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $value) { ?>
<?php if($value['score'] == 0) { ?>
</ul>
<ul class="rating_normal">
<?php } ?>
<li class="rating<?=$value['name']?>"><a href="
<?php if($value['score']) { ?>
<?=S_URL?>/do.php?action=click&op=add&clickid=<?=$value['clickid']?>&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_<?=$value['clickid']?>
<?php } else { ?>
javascript:;
<?php } ?>
" onclick="ajaxmenu(event, this.id, 2000, 'show_click')"><?=$value['name']?></a><em><?=$value['score']?></em></li>
<?php if($value['score'] == 0) { ?>
</ul>
<ul class="rating_good">
<?php } ?>
<?php } } ?>
</div></td>
<td style="width:190px">当前平均分：<span
<?php if($counts['average']) { ?>
 class="color_red"
<?php } ?>
><?=$counts['average']?></span> （<?=$counts['clicknum']?>次打分）</td>
</tr>
<?php if($gv['allowspread']) { ?>
<tr>
<td colspan="2">
<div id="article_state" style="margin: 0;">
<ul class="state_newstop clearfix">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $v) { ?>
<?php block("spacenews", "dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/0/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<li>[给<?=$v['name']?>最多的] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } } ?>
</ul>
</div>
</td>
</tr>
<?php } ?>
<?php } ?>
</tbody></table>
</div>
</div><!--article_mark end--><?php $cgid = 1;; ?><?php $gv = $clickgroups[$cgid]; unset($clickgroups[$cgid]);; ?><?php $counts = $clickcounts[$cgid];; ?>
<?php if($gv['status']) { ?>
<div id="article_state">
<div class="dashed_botline">
<div class="clearfix">
<?php if($gv['allowtop']) { ?>
<div class="box_r"><a href="<?php echo geturl("action/top/idtype/items/groupid/$cgid"); ?>" target="_blank">[<?=$gv['grouptitle']?>排行榜]</a></div>
<?php } ?>
<em>【已经有<span class="color_red"><?=$counts['clicknum']?></span>人表态】</em>
</div>
<div class="state_value clearfix">
<table><tbody><tr>
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $value) { ?>
<?php $value['height'] = $counts['maxclicknum']?intval($value['clicknum']*80/$counts['maxclicknum']):0;; ?><td valign="bottom">
<?php if($value['clicknum']) { ?>
<div class="
<?php if($value['clicknum'] == $counts['maxclicknum']) { ?>
max_value
<?php } ?>
" style="height:<?=$value['height']?>px;"><em><?=$value['clicknum']?>票</em></div>
<?php } ?>
<a href="<?=S_URL?>/do.php?action=click&op=add&clickid=<?=$value['clickid']?>&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_<?=$value['clickid']?>" onclick="ajaxmenu(event, this.id, 2000, 'show_click')">
<?php if($value['icon']) { ?>
<img src="<?=S_URL?>/images/click/<?=$value['icon']?>" alt="" />
<?php } ?>
<span><?=$value['name']?></span></a></td>
<?php } } ?>
</tr></tbody></table>
</div>
</div>
<?php if($gv['allowspread']) { ?>
<ul class="state_newstop clearfix">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $v) { ?>
<?php block("spacenews", "dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/3600/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<li>[<?=$v['name']?>最多的] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } } ?>
</ul>
<?php } ?>
</div>
<?php } ?>
<!--自定义表态部分-->
<?php if(is_array($clickgroups)) { foreach($clickgroups as $cgid => $gv) { ?>
<?php $counts = $clickcounts[$cgid];; ?>
<?php if($gv['status']) { ?>
<div id="article_state">
<div class="dashed_botline">
<div class="clearfix">
<?php if($gv['allowtop']) { ?>
<div class="box_r"><a href="<?php echo geturl("action/top/idtype/items/groupid/$cgid"); ?>" target="_blank">[<?=$gv['grouptitle']?>排行榜]</a></div>
<?php } ?>
<em>【已经有<span class="color_red"><?=$counts['clicknum']?></span>人表态】</em>
</div>
<div class="state_value clearfix">
<table><tbody><tr>
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $value) { ?>
<?php $value['height'] = $counts['maxclicknum']?intval($value['clicknum']*80/$counts['maxclicknum']):0;; ?><td valign="bottom">
<?php if($value['clicknum']) { ?>
<div class="
<?php if($value['clicknum'] == $counts['maxclicknum']) { ?>
max_value
<?php } ?>
" style="height:<?=$value['height']?>px;"><em><?=$value['clicknum']?>票</em></div>
<?php } ?>
<a href="<?=S_URL?>/do.php?action=click&op=add&clickid=<?=$value['clickid']?>&id=<?=$itemid?>&hash=<?=$hash?>" id="click_<?=$itemid?>_<?=$value['clickid']?>" onclick="ajaxmenu(event, this.id, 2000, 'show_click')">
<?php if($value['icon']) { ?>
<img src="<?=S_URL?>/images/click/<?=$value['icon']?>" alt="" />
<?php } ?>
<span><?=$value['name']?></span></a></td>
<?php } } ?>
</tr></tbody></table>
</div>
</div>
<?php if($gv['allowspread']) { ?>
<ul class="state_newstop clearfix">
<?php if(is_array($clicks[$cgid])) { foreach($clicks[$cgid] as $v) { ?>
<?php block("spacenews", "dateline/$gv[spreadtime]/order/i.click_$v[clickid] DESC/limit/0,1/cachetime/3600/subjectlen/40/subjectdot/0/cachename/click_$v[clickid]"); ?>
<?php if(is_array($_SBLOCK['click_'.$v['clickid']])) { foreach($_SBLOCK['click_'.$v['clickid']] as $value) { ?>
<li>[<?=$v['name']?>最多的] <a href="<?=$value['url']?>" title="<?=$value['subjectall']?>"><?=$value['subject']?></a></li>
<?php } } ?>
<?php } } ?>
</ul>
<?php } ?>
</div>
<?php } ?>
<?php } } ?>