<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php if($_SGLOBAL['member']['groupid'] == 1) { ?>
<div class="bdrcontent">
<div class="title"><h3>欢迎光临管理平台</h3></div>
<p>通过登录管理平台，您可以对站点的参数进行设置，并可以及时获取官方的更新动态和重要补丁通告。</p>
</div>
<br />

<div class="bdrcontent">
<div class="title">
<h3>官方最新动态</h3>
<p>官方新版本的发布与重要补丁的升级等动态，都会在这里显示。</p>
</div>
<div id="customerinfor" style="line-height:1.5em;"></div>
<br />
<div class="title">
<h3>技术支持服务</h3>
<p>如果你在使用中遇到问题，可以访问以下SupeSite站点需求帮助</p>
</div>
<ul class="listcol list2col">
<li><a href="http://www.discuz.net/forum-75-1.html" target="_blank">SupeSite官方论坛</a></li>
<li><a href="http://www.comsenz.com/purchase/supesite" target="_blank">Comsenz商业支持服务</a></li>
</ul>
</div>
<br />

<div class="bdrcontent">
<div class="title">
<h3>站点数据统计</h3>
<p>通过站点统计，您可以整体把握站点的发展状况。</p>
</div>
<ul class="listcol list2col">
<li>全部资讯数: <?=$statistics['spaceitemnum']?></li>
<li>全部评论数: <?=$statistics['spacecommentnum']?></li>
<li>全部用户组数: <?=$statistics['usergroupnum']?></li>
<li>全部举报数: <?=$statistics['reportnum']?></li>
<li>全部广告数: <?=$statistics['adnum']?></li>
<li>全部公告数: <?=$statistics['announcementnum']?></li>
<li>全部附件数: <?=$statistics['attachmentnum']?></li>
<li>全部聚合论坛版块数: <?=$statistics['forumnum']?></li>
<li>全部资讯分类数: <?=$statistics['categorynum']?></li>
<li>全部频道数: <?=$statistics['channelnum']?></li>
<li>全部友情链接数: <?=$statistics['friendlinknum']?></li>
<li>全部用户数: <?=$statistics['membernum']?></li>
<li>全部模型数: <?=$statistics['modelnum']?></li>
<li>全部投票数: <?=$statistics['pollnum']?></li>
<li>全部TAG数: <?=$statistics['tagnum']?></li>
<li>全部采集器数: <?=$statistics['robotnum']?></li>
</ul>
</div>
<br />

<div class="bdrcontent">
<div class="title"><h3>程序数据库/版本</h3></div>
<ul>
<li>操作系统: <?=$os?></li>
<li>数据库版本: <?=$statistics['mysql']?></li>
<li>上传许可: <?=$fileupload?></li>
<li>数据库尺寸: <?=$dbsize?></li>
<li>附件尺寸: <?=$attachsize?></li>
<li>当前程序版本: SupeSite <?=$statistics['version']?> ( <?=$statistics['release']?> )</li>
<li>UCenter 程序版本: UCenter <?=UC_CLIENT_VERSION?> Release <?=UC_CLIENT_RELEASE?></li>
</ul>
</div>
<br />

<div class="bdrcontent">
<div class="title">
<h3>开发团队</h3>
</div>
<table>
<tr><td>版权所有</td><td><a  href="http://www.comsenz.com/" target="_blank">康盛创想(北京)科技有限公司 (Comsenz Inc.)</a> 产品著作权号:2006SR12090</td></tr>
<tr><td>总策划</td><td><a  href="http://www.discuz.net/space.php?uid=1" target="_blank">Kevin 'Crossday' Day</a>, <a  href="http://www.discuz.net/space.php?uid=174393" target="_blank">Guode 'Sup' Li</a></td></tr>
<tr><td>开发与支持团队</td><td><a  href="http://www.discuz.net/space.php?uid=322293" target="_blank">Qingpeng 'Andy' Zheng</a>, <a  href="http://www.discuz.net/space.php?uid=248739" target="_blank">Jing 'Qiezi' Zou</a>, <a  href="http://www.discuz.net/space.php?uid=672953" target="_blank">Fei 'Fengshu' Zhao</a>, <a  href="http://www.discuz.net/space.php?uid=906359" target="_blank">Peng 'Dingusxp' Xu</a></td></tr>
<tr><td>美工设计</td><td><a  href="http://www.discuz.net/space.php?uid=294092" target="_blank">Fangming 'Lushnis' Li</a>, <a  href="http://www.discuz.net/space.php?uid=371830" target="_blank">Yulong 'Dragonlicn' Li</a>, <a  href="http://www.discuz.net/space.php?uid=981306" target="_blank">Jianwei 'Marmotsun' Sun</a></td></tr>
<tr><td>公司网站</td><td><a href=http://www.comsenz.com target="_blank">http://www.comsenz.com</a></td></tr>
<tr><td>产品网站</td><td><a href=http://x.discuz.net target="_blank">http://x.discuz.net</a></td></tr>
</table>
</div>
<?php if($statistics['update']) { ?>
<script language="javascript" src="http://x.discuz.net/customer/update.php?get=<?=$statistics['update']?>"></script>
<?php } ?>
<?php } elseif(!in_array('1', $menus['0']) && !in_array('1', $menus['1']) && !in_array('1', $menus['2']) && !in_array('1', $menus['3'])) { ?>
<div class="bdrcontent">
<div class="title"><h3>欢迎光临管理平台</h3></div>
<p>您不具备任何管理权限。</p>
</div>
<?php } else { ?>
<div class="bdrcontent">
<div class="title"><h3>欢迎光临管理平台</h3></div>
<p>通过管理平台操作，你可以对发布的信息进行批量管理。</p>
</div>
<?php } ?>