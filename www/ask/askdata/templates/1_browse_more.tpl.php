<?php if(!defined('IN_CYASK')) exit('Access Denied'); include template('header'); ?>
<div id="middle">
<div id="path"><a href="<?php echo $web_path;?>"><?php echo $site_name;?></a> &gt&gt
<?php if($type==0) { ?>
全部问题
<?php } elseif($type==1) { ?>
待解决问题
<?php } elseif($type==2) { ?>
已解决问题
<?php } elseif($type==3) { ?>
投票中问题
<?php } elseif($type==5) { ?>
高分问题
<?php } elseif($type==6) { ?>
推荐的问题
<?php } elseif($type==7) { ?>
知识分享
<?php } ?>
</div>
<div id="left2">
<div class="w100">
<div id="subline"></div>
<div id="sub">
<?php if($type==0) { ?>
<span>全部问题</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php"><b>全部问题</b></a>
<?php } if($type==2) { ?>
<span>已解决问题</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php?type=2"><b>已解决问题</b></a>
<?php } if($type==1) { ?>
<span>待解决问题</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php?type=1"><b>待解决问题</b></a>
<?php } if($type==3) { ?>
<span>投票中问题</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php?type=3"><b>投票中问题</b></a>
<?php } if($type==5) { ?>
<span>高分问题</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php?type=5"><b>高分问题</b></a>
<?php } if($type==7) { ?>
<span>知识分享</span>
<?php } else { ?>
<a href="<?php echo $web_path;?>browse.php?type=7"><b>知识分享</b></a>
<?php } ?>
</div>
<div class="subt bgg">共有相关条目 <?php echo $quescount;?> 项</div>
<?php if($quescount) { ?>
<script type="text/javascript">
function disQstate(s)
{ 
switch (s)
{
case 1:var op='<img src="<?php echo $web_path;?><?php echo $styledir;?>/icn_time.gif" alt="待解决问题">';break;
case 2:var op='<img src="<?php echo $web_path;?><?php echo $styledir;?>/icn_ok.gif"  alt ="已解决问题">';break;
case 3:var op='<img src="<?php echo $web_path;?><?php echo $styledir;?>/icn_vote.gif" alt="投票中问题">';break;
case 4:var op='<img src="<?php echo $web_path;?><?php echo $styledir;?>/icn_cancel.gif" alt="已关闭问题">';break;
case 7:var op='<img src="<?php echo $web_path;?><?php echo $styledir;?>/icn_share.gif" alt="知识分享">';break;
default: var op='未知问题';
}
document.write(op);
}
</script>
<table class="tlp4" id="tl" cellspacing="0" width="100%" border="0">
<tr><td class="nl" height="30">标题</td>
<td class="nl" align="center" width="10%">回答数</td>
<td class="nl" align="center" width="10%">状态</td>
<td class="nl" align="center" width="10%">提问时间</td></tr>
<?php if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td>
<?php if($question['score']) { ?>
<img width="12" height="11" align="absmiddle" alt="悬赏<?php echo $ques_score;?>分" src="<?php echo $web_path;?><?php echo $styledir;?>/s_money.gif" /><span class="red" title="悬赏<?php echo $ques_score;?>分"><?php echo $question['score'];?></span>
<?php } ?>
<span class="f14"><a href="<?php echo $web_path;?>question.php?qid=<?php echo $question['qid'];?>" target="_blank"><?php echo $question['title'];?></a> [<a class="lgy" href="<?php echo $web_path;?>browse.php?sortid=<?php echo $question['sid'];?>&type=<?php echo $type;?>"><?php echo $question['sort'];?></a>]</span></td>
<td align="center"><?php echo $question['answercount'];?></td>
<td align="center"><script type="text/javascript">disQstate(<?php echo $question['status'];?>);</script></td>
<td align="center"><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
</table>
<?php } else { ?>
<table class="tlp4" id="tl" cellspacing="0" width="100%" border="0">
<tr><td height="30" align="center">对不起，本目录下暂时没有问题，欢迎您成为提出此类问题的第一人！</td></tr>
</table>
<?php } ?>
<div id="pg">
<?php if($page>1) { ?>
<a href="<?php echo $web_path;?>browse.php?type=<?php echo $type;?>&page=1">[首页]</a>                                                                                                          
<a href="<?php echo $web_path;?>browse.php?type=<?php echo $type;?>&page=<?php echo $page_front;?>">前一页</a>
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="<?php echo $web_path;?>browse.php?type=<?php echo $type;?>&page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="<?php echo $web_path;?>browse.php?type=<?php echo $type;?>&page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
                     
</div>
</div>
</div>
<div id="right2">
<div class="t3 bcb"><div class="t3t bgb">热点问题</div></div>
<div class="b3 bcb mb12">
<div class="w100">
<?php if(is_array($hotques_list)) { foreach($hotques_list as $hq) { ?>
&#8226; <span class="f13"><a class="lq" href="<?php echo $web_path;?>question.php?qid=<?php echo $hq['qid'];?>" target="_blank" title="<?php echo $hq['title'];?>"><?php echo $hq['stitle'];?></a></span><br />
<?php } } ?>
</div>
</div>
</div>
</div>
<br />
<?php include template('footer'); ?>
