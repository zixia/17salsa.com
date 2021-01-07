<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$_SC['charset']?>" />
<title>
<?php if($ac=='news' || $ac=='models') { ?>
文章投稿
<?php } elseif($ac=='profile') { ?>
个人中心
<?php } elseif($ac=='credit') { ?>
我的积分
<?php } ?>
 <?=$_SCONFIG['seotitle']?> - </title>
<meta name="keywords" content="<?=$keywords?> <?=$_SCONFIG['seokeywords']?>" />
<meta name="description" content="<?=$description?> <?=$_SCONFIG['seodescription']?>" />
<meta name="generator" content="SupeSite 7.5" />
<meta name="author" content="SupeSite Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2008 Comsenz Inc." />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link rel="stylesheet" type="text/css" href="<?=S_URL?>/templates/<?=$_SCONFIG['template']?>/css/common.css" />
<link rel="stylesheet" type="text/css" href="<?=S_URL?>/templates/<?=$_SCONFIG['template']?>/css/cp_style.css" />
<?=$_SCONFIG['seohead']?>
<script type="text/javascript" src="<?=S_URL?>/templates/<?=$_SCONFIG['template']?>/js/common.js"></script>
<script type="text/javascript">
var siteUrl = "<?=S_URL?>";
</script>
<script src="<?=S_URL?>/include/js/menu.js" type="text/javascript" language="javascript"></script>
<script src="<?=S_URL?>/include/js/ajax.js" type="text/javascript" language="javascript"></script>
<script src="<?=S_URL?>/include/js/common.js" type="text/javascript" language="javascript"></script>
<script src="<?=S_URL?>/include/js/admin.js" type="text/javascript" language="javascript"></script>
<script language="javascript" type="text/javascript" src="images/edit/edit.js"></script>
<link href="images/edit/edit.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div id="header">
<h2><a href="<?=S_URL?>"><img src="<?=S_URL?>/images/logo.gif" alt="<?=$_SCONFIG['sitename']?>" /></a></h2>
</div>
<div id="nav">
<div class="main_nav">
<ul>
<li><a href="<?=S_URL?>/">首页</a></li>
<?php if($_SGLOBAL['supe_uid']) { ?>
<li 
<?php if($ac=='profile') { ?>
 class="current"
<?php } ?>
><a href="cp.php?ac=profile">个人中心</a></li>
<?php } ?>
<li 
<?php if($ac=='news' || $ac=='models') { ?>
 class="current"
<?php } ?>
><a href="cp.php?ac=news">我的投稿</a></li>
<?php if($_SGLOBAL['supe_uid']) { ?>
<li 
<?php if($ac=='credit') { ?>
 class="current"
<?php } ?>
><a href="cp.php?ac=credit">我的积分</a></li>
<?php } ?>
</ul>
</div>