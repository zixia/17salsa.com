<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?>
<?php include template('cp_header'); ?>
<ul class="ext_nav clearfix">
<li><a href="cp.php?ac=credit">积分日志</a></li>
<li><a href="cp.php?ac=credit&op=rule">积分规则</a></li>
<?php if(checkperm('allowtransfer') ) { ?>
<li><a href="cp.php?ac=credit&op=exchange">积分兑换</a></li>
<?php } ?>
</ul>
</div><?php $_TPL['rewardtype'] = array('0' => '消费','1' => '奖励','2' => '惩罚');
$_TPL['cycletype'] = array('0' => '一次性','1' => '每天','2' => '整点','3' => '间隔分钟','4' => '不限周期'); ; ?><div class="column">
<div class="col1" >
<?php if(empty($op)) { ?>
<div class="global_module margin_bot10 bg_fff userpanel">
<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit">积分日志</a></h3></div>
<div class="integral">
<div class="integral_caption"><h2>获得积分历史</h2></div>
<table><tbody>
<tr>
<td width="17%">动作名称</td>
<td width="17%">总次数</td>
<td width="17%">周期次数</td>
<td width="17%">单次积分</td>
<td width="18%">单次经验值</td>
<td>最后奖励时间</td>
</tr>
<?php if($list) { ?>
<?php if(is_array($list)) { foreach($list as $key => $value) { ?>
<tr>
<td><a><?=$value['rulename']?></a></td>
<td><?=$value['total']?></td>
<td><?=$value['cyclenum']?></td>
<td><?=$value['credit']?></td>
<td><?=$value['experience']?></td>
<td><?php sdate('m-d H:i',$value[dateline], 1); ?></td>
</tr>
<?php } } ?>
<?php } else { ?>
<tr>
<td colspan="6" class="user_no_body">暂时没有获得任何积分</td>
</tr>
<?php } ?>
</tbody></table>
</div>
</div>
<?php } elseif($op=='rule') { ?>
<div class="global_module margin_bot10 bg_fff userpanel">
<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit&op=rule">积分规则</a></h3></div>
<div class="personaldata">
<table><tbody>
<?php if($list) { ?>
<tr class="font_weight">
<td>动作名称</td>
<td width="80">奖励周期</td>
<td width="80">奖励次数</td>
<td width="80">奖励方式</td>
<td width="80">获得积分</td>
<td width="80">获得经验值</td>
</tr>
<?php if(is_array($list)) { foreach($list as $value) { ?>
<tr>
<td><?=$value['rulename']?></td>
<td><?=$_TPL['cycletype'][$value['cycletype']]?></td>
<td><?=$value['rewardnum']?></td>
<td
<?php if($value['rewardtype']==1) { ?>
 class="num_add"
<?php } else { ?>
 class="num_reduce"
<?php } ?>
><?=$_TPL['rewardtype'][$value['rewardtype']]?></td>
<td>
<?php if($value['rewardtype']==1) { ?>
+
<?php } else { ?>
-
<?php } ?>
<?=$value['credit']?></td>
<td>
<?php if($value['rewardtype']==2) { ?>
-
<?php } else { ?>
+
<?php } ?>
<?=$value['experience']?></td>
</tr>
<?php } } ?>
<?php } else { ?>
<tr>
<td colspan="5" class="user_no_body">暂无相关积分规则</td>
</tr>
<?php } ?>
<?php if($multi) { ?>
<tr>
<td colspan="6"><div class="pages"><?=$multi?></div></td>
</tr>
<?php } ?>
</tbody></table>
</div>
</div>
<?php } elseif($op == 'exchange') { ?>
<form method="post" action="cp.php?ac=credit&op=exchange">
<input type="hidden" name="formhash" value="<?php echo formhash();; ?>" />
<div class="global_module margin_bot10 bg_fff userpanel">
<div class="global_module3_caption"><h3>你的位置：<a href="cp.php?ac=credit&op=rule">积分兑换</a></h3></div>
<div class="sumup">
<h2>您可以将自己的积分兑换到本站其他的应用（比如论坛）里面。</h2>
<table cellspacing="0" cellpadding="0" class="formtable">
<tr><th width="150">目前您的积分数:</th>
<td><span class="big_red"><?=$_SGLOBAL['member']['credit']?></span></td></tr>
<tr><th><label for="password">密码</label>:</th>
<td><input type="password" name="password" class="t_input" /></td></tr>
<tr><th>支出积分:</th>
<td><input type="text" id="amount" name="amount" value="0" class="t_input" onkeyup="calcredit();" /></td></tr>
<tr><th>兑换成:</th>
<td><input type="text" id="desamount" value="0" class="t_input" disabled />&nbsp;&nbsp;
<select name="tocredits" id="tocredits" onChange="calcredit();">
<?php if(is_array($_CACHE['creditsettings'])) { foreach($_CACHE['creditsettings'] as $id => $ecredits) { ?>
<?php if($ecredits['ratio']) { ?>
<option value="<?=$id?>" unit="<?=$ecredits['unit']?>" title="<?=$ecredits['title']?>" ratio="<?=$ecredits['ratio']?>"><?=$ecredits['title']?></option>
<?php } ?>
<?php } } ?>
</select></td></tr>
<tr><th>兑换比率:</th>
<td><span class="bold">1</span>&nbsp;<span id="orgcreditunit">积分</span>
<span id="orgcredittitle"></span>&nbsp;兑换&nbsp;
<span class="bold" id="descreditamount"></span>&nbsp;
<span id="descreditunit"></span><span id="descredittitle"></span></td></tr>
<tr><th>&nbsp;</th><td><input type="submit" name="exchangesubmit" value="兑换积分" class="submit"></td></tr>
</table>
</div>
</div>
</form>

<script type="text/javascript">
function calcredit() {
tocredit = $('tocredits')[$('tocredits').selectedIndex];
$('descreditunit').innerHTML = tocredit.getAttribute('unit');
$('descredittitle').innerHTML = tocredit.getAttribute('title');
$('descreditamount').innerHTML = Math.round(1/tocredit.getAttribute('ratio') * 100) / 100;
$('amount').value = $('amount').value.toInt();
if($('amount').value != 0) {
$('desamount').value = Math.floor(1/tocredit.getAttribute('ratio') * $('amount').value);
} else {
$('desamount').value = $('amount').value;
}
}
String.prototype.toInt = function() {
var s = parseInt(this);
return isNaN(s) ? 0 : s;
}
calcredit();
</script>
<?php } ?>
</div>

<div class="col2" >
<div id="user_login">
<script src="<?=S_URL?>/batch.panel.php?rand=<?php echo rand(1, 999999); ?>" type="text/javascript" language="javascript"></script>
</div><!--user_login end-->

<div id="contribute" class="global_module bg_fff margin_bot10">
<div class="global_module2_caption"><h3>频道</h3></div>
<ul>
<?php if(is_array($channels['menus'])) { foreach($channels['menus'] as $value) { ?>
<?php if($value['type']=='type' || $value['upnameid']=='news') { ?>
<li
<?php if($value['nameid']==$type) { ?>
 class="current"
<?php } ?>
 onclick="window.location.href='<?=S_URL?>/cp.php?ac=news&op=list&do=<?=$do?>&type=<?=$value['nameid']?>&<?php echo rand(1, 999999); ?>';">
<span>
<?php if($value['nameid']==$type) { ?>
共(<?=$listcount?>)条 当前频道
<?php } else { ?>
浏览
<?php } ?>
</span>
<a><?=$value['name']?></a></li>
<?php } elseif($value['type']=='model') { ?>
<li onclick="window.location.href='<?=S_URL?>/cp.php?ac=models&op=list&do=<?=$do?>&nameid=<?=$value['nameid']?>&<?php echo rand(1, 999999); ?>';">
<span>浏览</span>
<a><?=$value['name']?></a></li>
<?php } ?>
<?php } } ?>
</ul>
</div>
</div><!--col2-->
</div>
<?php include template('cp_footer'); ?>