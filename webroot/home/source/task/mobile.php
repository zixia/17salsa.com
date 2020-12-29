<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: blog.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$mobile = $space['mobile'];

if($mobile) {

	$task['done'] = 1;//活动完成

} else {

	//活动完成向导
	$task['guide'] = '
		<strong>请按照以下的说明来参与本活动：</strong>
		<ul>
		<li>1. <a href="http://17salsa.net/home/cp.php?ac=profile&op=contact" target="_blank">新窗口打开【个人设置-联系方式】页面</a>；</li>
		<li>2. 在新打开的页面中，填写手机号码，然后保存。</li>
		</ul>
		<p>提示：可以设置手机号码只允许好友可见，或设置好友也无法看到！</p>
';

}

?>
