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
</div><!--nav end-->


<div class="column">
<div class="global_module margin_bot10 bg_fff">
<div class="global_module3_caption"><h3>你的位置：搜索</h3></div>

<div id="detail_search">
<?php if(empty($searchname)) { ?>
<?php $searchname = 'subject'; ?>
<?php } ?>
<form action="<?=S_URL?>/batch.search.php" method="post">
<input type="text" class="input_tx" size="50" name="searchkey" value="<?=$searchkey?>" /> <input type="submit" class="input_search" name="authorsearchbtn" value="搜索" />
<div class="search_catalog">
<input id="title" name="searchname" type="radio" value="subject" 
<?php if($searchname == 'subject') { ?>
checked="checked" 
<?php } ?>
/><label for="title">标题</label>
<input id="content" name="searchname" type="radio" value="message" 
<?php if($searchname == 'message') { ?>
checked="checked" 
<?php } ?>
/><label for="content">内容</label>
<input id="author" name="searchname" type="radio" value="author" 
<?php if($searchname == 'author') { ?>
checked="checked" 
<?php } ?>
/><label for="author">作者</label>
<?php if(!empty($channels['menus']['bbs'])) { ?>
<a title="搜索论坛" href="<?=$bbsurl?>/search.php" target="_blank">搜索论坛</a>
<?php } ?>
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />
</div>
</form>
</div><!--search_bar end-->
<?php if(!empty($iarr)) { ?>
<ul id="sarch_list">
<?php if(is_array($iarr)) { foreach($iarr as $value) { ?>
<li><strong>[<?=$channels['menus'][$value['type']]['name']?>]</strong> <a href="<?=$value['url']?>"><?=$value['subject']?></a> (<a href="<?=S_URL?>/space.php?uid=<?=$value['uid']?>"><?=$value['username']?></a>，<?php sdate("Y-n-d H:i:s", $value["dateline"]); ?>)</li>
<?php } } ?>
</ul>
<?php if(!empty($multipage)) { ?>
<?=$multipage?>
<?php } ?>
<?php } ?>
</div>

</div><!--column end-->
<?php include template('footer'); ?>