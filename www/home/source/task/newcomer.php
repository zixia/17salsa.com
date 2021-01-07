<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: blog.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$blogcount = getcount('post', array(	 'uid'		=> $space['uid']
										,'tagid'	=> 167
										,'isthread'	=> 1
									));
if($blogcount) {

	$task['done'] = 1;//活动完成

} else {

	//活动完成向导
	$task['guide'] = '
		<strong>请按照以下的说明来参与本活动：</strong>
		<ul>
		<li>1. <a href="space-mtag-tagid-167.html" target="_blank">新窗口打开【Salsa新人报道】群组页面</a>；</li>
		<li>2. 在新打开的页面中，根据群组模板要求，书写自己的Salsa新人报道贴，并进行发布。</li>
		</ul>';

}

?>
