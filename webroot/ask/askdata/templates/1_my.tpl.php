<?php if(!defined('IN_CYASK')) exit('Access Denied'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>">
<title>个人中心 - Powered By cyask</title>
<meta content="<?php echo $meta_description;?>" name=description>
<meta content="<?php echo $meta_keywords;?>" name=keywords>
<link href="<?php echo $styledir;?>/default.css" type=text/css rel=stylesheet>
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
</head>
<body style="background:url(images/mainbg.gif)">
<div id=main align="center">
<table width="100%" align=center border=0>
<tr height="60">
<td valign="top" width="170" height="69">&nbsp;<a href="./"><img src="<?php echo $styledir;?>/1000ask.gif" border=0></a></td>
<td><table cellspacing=0 cellpadding=0 width="99%" border=0>
     <tr><td class=blueBG height=25 align="left">&nbsp;&nbsp;<b class=p14>个人中心</b> &nbsp;<a href='./'>返回首页</a></td>
          <td class=blueBG width=300 height=25 align=right>
          欢迎回来！ <b><?php echo $cyask_user;?></b> &nbsp;&nbsp;<a href="login.php?command=logout">退出</a>&nbsp;&nbsp;
          </td>
      </tr></table>
</td></tr>
</table>
<br />
<div id="leftw">
<table class="pad10L" cellspacing="1" cellpadding="0" width="100%" border="0">
<?php if($command=='mymessage') { ?>
<tr><td class="blueBG f14" height="22">我的消息</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=mymessage">我的消息</a></td></tr>
<?php } if($command=='myask') { ?>
<tr><td class="blueBG f14" height=22>我的问题</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myask">我的问题</a></td></tr>
<?php } if($command=='myanswer') { ?>
<tr><td class="blueBG f14" height=22>我的回答</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myanswer">我的回答</a></td></tr>
<?php } if($command=='myshare') { ?>
<tr><td class="blueBG f14" height=22>我的分享</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myshare">我的分享</a></td></tr>
<?php } if($command=='myoverdue') { ?>
<tr><td class="blueBG f14" height=22>过期问题</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myoverdue">过期问题</a></td></tr>
<?php } if($command=='myscore') { ?>
<tr><td class="blueBG f14" height=22>我的积分</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myscore">我的积分</a></td></tr>
<?php } if($command=='myinfo') { ?>
<tr><td class="blueBG f14" height=22>我的档案</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=myinfo">我的档案</a></td></tr>
<?php } if($command=='upinfo') { ?>
<tr><td class="blueBG f14" height=22>修改档案</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=upinfo">修改档案</a></td></tr>
<?php } if($command=='uppassword') { ?>
<tr><td class="blueBG f14" height=22>修改密码</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="my.php?command=uppassword">修改密码</a></td></tr>
<?php } ?>
</table>
</div>
<div id="rightw">
<?php if($command=='myscore') { ?>
<div class="mt mgb">我的积分</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=3 width="100%" height=160 border=0>
<tr><td valign="top" width="33%">
      <TABLE cellSpacing=1 cellPadding=3 width=100% bgColor="#dbdbdb" border="0">
       <TR><td class="pad10L" bgColor="#ffffff"><div align=center>积分明细</div></td></tr>
        <TR bgColor=#ffffff><td class="pad10L tbPad10" valign=center height=60>
            <TABLE cellSpacing=0 cellPadding=3 width="100%" border=0>
               <TR><td width=100><DIV align=right>总积分:</DIV></td>
                   <td><SPAN class=f14><?php echo $totalscore;?></SPAN></td></tr>
                   <TR><td width=100><DIV align=right>上周积分:</DIV></td>
                   <td><SPAN class=f14><?php echo $lastweekscore;?></SPAN></td></tr>
                   <TR><td width=100><DIV align=right>上月积分:</DIV></td>
                   <td><SPAN class=f14><?php echo $lastmonthscore;?></SPAN></td></tr>
             </TABLE></td></tr>
       </TABLE>
     </td>
     <td valign=top width="33%">
       <TABLE cellspacing=1 cellpadding=3 width=100% bgColor=#dbdbdb border=0>
        <TR><td class=pad10L noWrap bgColor=#ffffff><DIV align=center>回答统计</DIV></td></tr>
        <TR bgColor=#ffffff><td class="pad10L tbPad10" valign=center height=60>
            <TABLE cellspacing=0 cellpadding=3 width="100%" border=0>
              <TR><td noWrap width=100><DIV align=right>回答总数:</DIV></td>
                   <td><?php echo $answercount;?></td></tr>
              <TR><td width=100><DIV align=right>回答被采纳数:</DIV></td>
                  <td><?php echo $adoptcount;?></td></tr>
              <TR><td width=100><DIV align=right>回答采纳率:</DIV></td>
<td><?php echo $rightvalage;?></td></tr>
             </TABLE>
            </td></tr>
        </TABLE>
</td>
    <td valign=top width="33%">
      <TABLE cellSpacing=1 cellPadding=3 width=100% bgColor=#dbdbdb border=0>
        <TR><td class=pad10L noWrap bgColor=#ffffff><DIV align=center>提问统计</DIV></td></tr>
        <TR bgColor=#ffffff><td class="pad10L tbPad10" vAlign=center height=60>
             <TABLE cellSpacing=0 cellPadding=3 width="100%" border=0>
                <TR><td width=100><DIV align=right>提问总数:</DIV></td>
                    <td><?php echo $question_allcount;?></td></tr>
                <TR><td width=100><DIV align=right>已解决问题数:</DIV></td>
                    <td><?php echo $questionOK;?></td></tr>
                <TR><td width=100><DIV align=right>待解决问题数:</DIV></td>
                    <td><?php echo $questionASK;?></td></tr>
<TR><td width=100><DIV align=right>投票中问题数:</DIV></td>
                    <td><?php echo $questionVOTE;?></td></tr>
<TR><td width=100><DIV align=right>已关闭问题数:</DIV></td>
                    <td><?php echo $questionCLOSE;?></td></tr>
             </TABLE></td></tr>
       </TABLE>
    </td>
 </tr>
</table>
</div>
</div>
<?php } elseif($command=='myask') { ?>
<div class="mt mgb">我的问题 &nbsp;&nbsp;( <?php echo $quescount;?> )</div>
<div class="mb mcb">
<table cellspacing=0 cellpadding=0 width="100%" border=0 valign="top">
<?php if($quescount) { ?>
<tr><td width="60%" align=center height=30>标题</td>
    <td width="8%" align=center>悬赏分</td>
<td width="8%" align=center>回答数</td>
    <td width="8%" align=center>状态</td>
    <td width="15%" align=center>提问时间</td>
</tr>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>·<a href="question.php?qid=<?php echo $question['qid'];?>" target="_blank" title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></a></td>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
<?php } elseif($command=='myoverdue') { ?>
<div class="mt mgb">过期问题 &nbsp;&nbsp;( <?php echo $quescount;?> )</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border="0" valign="top">
<?php if($quescount) { ?>
<TR><td width="60%" align=center height=30>标题</td>
    <td width="8%" align=center>悬赏分</td>
<td width="8%" align=center>回答数</td>
    <td width="8%" align=center>状态</td>
    <td width="15%" align=center>提问时间</td>
</tr>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>·<A href="question.php?qid=<?php echo $question['qid'];?>" target="_blank" title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></A></td>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='myanswer') { ?>
<div class="mt mgb">我的回答 &nbsp;&nbsp;( <?php echo $answercount;?> )</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border="0">
<?php if($answercount) { ?>
<TR><td width="54%" align=center height=30>标题</td>
<td align=center width="9%">悬赏分</td>
    <td align=center width="6%">评论</td>
    <td align=center width="7%">状态</td>
    <td align=center width="11%">我的回答</td>
    <td align=center width="13%">回答时间</td></tr>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<TR><td class="linetop f14" height=25>·<a href="response.php?aid=<?php echo $question['aid'];?>" target=_blank title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></A></td>
     <td class=linetop align=center valign=middle><FONT color="red"><?php echo $question['score'];?></FONT></td>
 <td class=linetop align=center valign=middle><FONT color="#333333"><?php echo $question['response'];?></FONT></td>
     <td class=linetop align=center valign=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
     <td class=linetop align=center valign=middle>
<?php if($question['adopttime']) { ?>
<font color="red">已被采纳</font>
<?php } else { ?>
未被采纳
<?php } ?>
</td>
     <td class=linetop align=center valign=middle><?php echo $question['answertime'];?></td></tr> 
<?php } } ?>
<tr><td colspan=6>&nbsp;</td></tr>
<tr><td align=center colspan=6>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='myshare') { ?>
<div class="mt mgb">我的分享 &nbsp;&nbsp;( <?php echo $quescount;?> )</div>
<div class="mb mcb">
<table cellspacing=0 cellpadding=0 width="100%" border=0 valign="top">
<?php if($quescount) { ?>
<tr><td width="60%" align=center height=30>标题</td>
    <td width="8%" align=center>悬赏分</td>
<td width="8%" align=center>回答数</td>
    <td width="8%" align=center>状态</td>
    <td width="15%" align=center>提问时间</td>
</tr>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>·<a href="question.php?qid=<?php echo $question['qid'];?>" target="_blank" title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></a></td>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
<?php } elseif($command=='myoverdue') { ?>
<div class="mt mgb">过期问题 &nbsp;&nbsp;( <?php echo $quescount;?> )</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border="0" valign="top">
<?php if($quescount) { ?>
<TR><td width="60%" align=center height=30>标题</td>
    <td width="8%" align=center>悬赏分</td>
<td width="8%" align=center>回答数</td>
    <td width="8%" align=center>状态</td>
    <td width="15%" align=center>提问时间</td>
</tr>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>·<A href="question.php?qid=<?php echo $question['qid'];?>" target="_blank" title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></A></td>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='mymessage') { ?>
<div class="mt mgb">
<table cellspacing=0 cellpadding=0 width="100%" height=26 border=0>
<tr>
<td width="40%">
&nbsp;
<?php if($boxtype=='inbox') { ?>
<b>收件箱</b>
<?php } else { ?>
<a href="my.php?command=<?php echo $command;?>&amp;boxtype=inbox">收件箱</a>
<?php } ?>
&nbsp;|&nbsp;
<?php if($boxtype=='outbox') { ?>
<b>发件箱</b>
<?php } else { ?>
<a href="my.php?command=<?php echo $command;?>&amp;boxtype=outbox">发件箱</a>
<?php } ?>
</td>
<td align="right" width="60%">
<a href="my.php?command=sendmsg">发送信息</a> &nbsp;&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border="0">
<tr>
<td colspan=5 height=22>&nbsp;&nbsp;(<?php echo $msgcount;?> 封信)&nbsp;&nbsp;
<?php if($boxtype=='inbox') { if($msgtype=='newpm') { ?>
未读消息
<?php } else { ?>
<a href="my.php?command=<?php echo $command;?>&amp;boxtype=inbox&amp;msgtype=newpm">未读消息</a>
<?php } ?>
&nbsp;|&nbsp;
<?php if($msgtype=='systempm') { ?>
系统消息
<?php } else { ?>
<a href="my.php?command=<?php echo $command;?>&amp;boxtype=inbox&amp;msgtype=systempm">系统消息</a>
<?php } ?>
&nbsp;|&nbsp;
<?php if($msgtype=='announcepm') { ?>
公共消息
<?php } else { ?>
<a href="my.php?command=<?php echo $command;?>&amp;boxtype=inbox&amp;msgtype=announcepm">公共消息</a>
<?php } } ?>
</td>
    </tr>
<?php if($msgcount) { ?>
<tr>
<td width="45%" height=25>&nbsp;&nbsp;标题</td>
<td align=center width="15%">发送时间</td>
<td align=center width="10%">发件人</td>
    <td align=center width="10%">状态</td>
    <td align=center width="10%">删除</td>
    </tr>
<?php } if(is_array($msg_list)) { foreach($msg_list as $message) { ?>
<tr>
<td class="linetop f14" height=25>
·<a href="my.php?command=readmsg&amp;pmid=<?php echo $message['pmid'];?>&amp;boxtype=<?php echo $boxtype;?>&amp;page=<?php echo $page;?>"><?php echo $message['title'];?></a></td>
    <td class="linetop" align=center valign=middle><?php echo $message['mdate'];?></td>
    <td class="linetop" align=center valign=middle>
<?php if($boxtype=='inbox') { ?>
<a href="member.php?uid=<?php echo $message['fromuid'];?>" target="_blank"><?php echo $message['fromuser'];?></a>
<?php } else { ?>
--
<?php } ?>
    </td>
<td class="linetop" align=center valign=middle>
<?php if($message['new']==0) { ?>
已读
<?php } else { ?>
<font color=red>未读</font>
<?php } ?>
</td>
    <td class="linetop" align=center valign=middle><a href="my.php?command=delmessage&amp;boxtype=<?php echo $boxtype;?>&amp;pmid=<?php echo $message['pmid'];?>&amp;page=<?php echo $page;?>">删除</a></td></tr>
<?php } } ?>
<tr><td colspan=6>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=1">[首页]</a>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="my.php?command=<?php echo $command;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='myinfo') { ?>
<div class="mt mgb">我的档案</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=2 width="100%" height="60" border="0">
<tr>
<td valign=top width="30%">
      <TABLE cellSpacing=1 cellPadding=3 width="100%" bgColor="#dbdbdb" border=0>
        <TR bgColor=#ffffff>
<td class="pad10L tbPad10">
            <TABLE cellSpacing=0 cellPadding=2 width="100" border=0>
               <TR><td width=200 colspan="2" align="center"><img src="<?php echo $members[avatar];?>" /></td></tr>
   <TR><td width=80 align=right>用户名:</td>
                   <td width=120><SPAN class=f14><?php echo $members[username];?></SPAN></td></tr>
               <TR><td width=80 align=right>性 别:</td>
                   <td width=120><SPAN class=f14>
<?php if($members[gender]==1) { ?>
男
<?php } elseif($members[gender]==2) { ?>
女
<?php } else { ?>
保密
<?php } ?>
</SPAN></td></tr>
               <TR><td width=80 align=right>生 日:</td>
                   <td width=120><SPAN class=f14><?php echo $members[bday];?></SPAN></td></tr>
             </TABLE></td></tr>
    </TABLE>
</td>
<td valign=top width="70%">
      <TABLE cellSpacing=1 cellPadding=3 width="100%" bgColor="#dbdbdb" border=0>
        <TR bgColor=#ffffff>
<td class="pad10L tbPad10" vAlign=center height=60>
            <TABLE cellSpacing=0 cellPadding=3 width="100%" border=0>
               <TR><td width=100 align=right>QQ:</td>
                   <td><SPAN class=f14><?php echo $members[qq];?></SPAN></td></tr>
               <TR><td width=100 align=right>MSN:</td>
                   <td><SPAN class=f14><?php echo $members[msn];?></SPAN></td></tr>
               <TR><td width="100" align="right" valign="top">个性签名:</td>
                   <td><?php echo $members[signature];?></td></tr>
             </TABLE></td></tr>
    </TABLE>
</td>
</tr>
</table>
</div>
</div>
<?php } elseif($command=='upinfo') { ?>
<div class="mt mgb">修改档案</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=3 width="100%" border=0>
<script language="JavaScript">
function check_upinfo(theForm)
{
if(theForm.signature.value.length>500)
{
      	document.getElementById("signaturetip").innerHTML='<font color="red">个性签名500字以内。</font>';
      	return false;
}
else
{
document.getElementById("signaturetip").innerHTML='';
}
}
</script>
<tr>
    <td class="pad10L tbPad10" vAlign=center height=60>
       <table cellspacing=0 cellpadding=3 width="100%" border=0>
       <form name=upinfoForm action="my.php" method="post" onsubmit="return check_upinfo(this);" enctype="multipart/form-data">
         <TR><td width=100 align=right>用户名:</td>
             <td width=90%><SPAN class=f14><?php echo $members[username];?></SPAN></td></tr>
         <TR><td width=100 align=right>性 别:</td>
             <td width=90%><SPAN class=f14><select name="gender"><option value=0 
<?php if($members[gender]==0) { ?>
selected
<?php } ?>
 >保密</option><option value=1 
<?php if($members[gender]==1) { ?>
selected
<?php } ?>
 >男</option><option value=2 
<?php if($members[gender]==2) { ?>
selected
<?php } ?>
 >女</option></select></SPAN></td></tr>
         <TR><td width=100 align=right>生 日</td>
             <td width=90%><SPAN class=f14><input type=text name="bday" size=10 maxlength=10 value=<?php echo $members[bday];?>></SPAN></td></tr>
         <TR><td width=100 align=right>QQ:</td>
             <td width=90%><SPAN class=f14><input type=text name="qq" size=20 maxlength=12 value="<?php echo $members[qq];?>"></SPAN></td></tr>
         <TR><td width=100 align=right>MSN:</td>
             <td width=90%><SPAN class=f14><input type=text name="msn" size=20 maxlength=40 value="<?php echo $members[msn];?>"></SPAN></td></tr>
         <TR><td width=100 align=right>个性签名:</td>
             <td width=90%><SPAN class=f14>
             <textarea name="signature" cols="50" rows="6"><?php echo $members[signature];?></textarea></SPAN></td></tr>
         <TR><td width="100%" height="5" colspan="2">&nbsp;</td></tr>
         <TR><td width=100 align=right>&nbsp;</td>
             <td width=90%><SPAN class=f14>
             <input type="hidden" name="command" value="upinfosubmit" />
             <input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
             <input type="submit" name="upinfosubmit" value="好了，提交" /></SPAN></td></tr>
        </form>
        </table>
  </td>
 </tr>
</TABLE>
</div>
</div>
<?php } elseif($command=='uppassword') { ?>
<div class="mt mgb">修改密码</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=3 width="100%" height="300" border=0>
<script language="JavaScript">
function check_password(theForm)
{
if(theForm.opw.value == "")
{
      		document.getElementById("pwtip").innerHTML = "<font color=red>请输入您的旧密码吧！</font>";
  	theForm.opw.focus();
      		return false;
}
else
{
document.getElementById("pwtip").innerHTML = "";
}
if(theForm.npw.value == "")
{
      		document.getElementById("npwtip").innerHTML = "<font color=red>请输入您的新密码吧！</font>";
  	theForm.npw.focus();
      		return false;
}
else
{
document.getElementById("npwtip").innerHTML = "";
}
}

</script>
<tr>
    <td class="pad10L tbPad10" valign="top" height=60>
     <TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
      <form action="my.php" method="post" onsubmit="return check_password(this);">
       <tr>
      <td width="100" class="pad10L" height=30>用户名:</td>
      <td><?php echo $cyask_user;?></td>
       </tr>
       <tr>
      <td width="100" class="pad10L" height=30>电子邮箱:</td>
      <td><input name="email" type="text" id="email" size="20"></td>
       </tr>
      <tr>
      <td height="30" class="pad10L">旧密码:</td>
      <td><input name="opw" type="password" id="word" size="20">&nbsp;<span id=pwtip></span></td>
</tr>
<tr>
      <td height="30" class="pad10L">新密码:</td>
      <td>
        <input name="npw" type="password" id="npw" size="20">&nbsp;<span id=npwtip></span>
      </td>
    </tr>
    <tr>
      <td valign="top" class="pad10L">&nbsp;</td>
      <td valign="bottom">
        <br />
        <input type="hidden" value="uppwsubmit" name="command" />
<input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
        <input type="submit" name="uppwsubmit" value="好了，提交" />
      </td>
    </tr>
    </form>
    </TABLE>
  </td>
 </tr>
</TABLE>
</div>
</div>
<?php } elseif($command=='sendmsg') { ?>
<div class="mt mgb">发送信息</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=3 width="100%" height=300 border=0>
<script type="text/javascript">
function check_sendmsg(theForm)
{
if(theForm.username.value == '')
{
      	document.getElementById("usertip").innerHTML = '<font color="red">请您填写对方用户名（帐号）</font>';
  	theForm.username.focus();
      	return false;
}
else
{
document.getElementById("usertip").innerHTML = '';
}
if(theForm.title.value == '')
{
      	document.getElementById("titletip").innerHTML = '<font color="red">请您填写信息</font>';
  	theForm.title.focus();
      	return false;
}
else
{
document.getElementById("titletip").innerHTML = '';
}
}

</script>
<tr>
    <td class="pad10L tbPad10" vAlign=center height=60>
       <table cellspacing=0 cellpadding=3 width="100%" border=0>
       <form name="sendmsgForm" action="my.php" method="post" onsubmit="return check_sendmsg(this);">
         <TR><td width=100 align=right>用户名:</td>
             <td width=90%><SPAN class=f14><input type=text name="username" size=30 value="<?php echo $_GET['username'];?>" /></SPAN>&nbsp;<span id=usertip></span></td></tr>
         <TR><td width=100 align=right>主 题:</td>
             <td width=90%><SPAN class=f14><input type=text name="title" size=30 maxlength=48></SPAN>&nbsp;<span id=titletip></span></td></tr>
         <TR><td width=100 align=right>正 文:</td>
             <td width=90%><SPAN class=f14><textarea name="content" rows=10 cols=60></textarea></SPAN></td></tr>
         <TR><td width=100 align=right>&nbsp;</td>
             <td width=90%><SPAN class="f14">
             <input type="hidden" name="command" value="sendmsg" />
             <input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
             <input type="submit" name="submit" value="好了，提交" /></SPAN></td></tr>
        </form>
        </table>
  </td>
 </tr>
</TABLE>
</div>
</div>
<?php } elseif($command=='readmsg') { ?>
<div class="mt mgb">阅读信息</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing="2" cellpadding="3" width="100%" height="30" border="0">
<script language="javascript">
function check_sendmsg(theForm)
{
if(theForm.content.value == "")
{
      	document.getElementById("contentip").innerHTML = "<font color=red>请您填写信息</font>";
  	theForm.content.focus();
      	return false;
}
else
{
document.getElementById("contentip").innerHTML = "";
}
}

</script>
    
<tr>
    <td class="pad10L tbPad10" vAlign="top" height=30>
       <table cellspacing=0 cellpadding=3 width="100%" border=0>
         <tr><td colspan="2"><span class="f14b"><?php echo $msg[subject];?></span></td></tr>
      
         <tr><td width=150><span class=f14><?php echo $msg['msgfrom'];?><br /><?php echo $msg['mdate'];?></td>
             <td width=85%><?php echo $msg['message'];?></td>
         </tr>
         <tr><td colspan="2" height="3">&nbsp;</td></tr>
        </table>
  </td>
 </tr>
</table>
<table cellspacing=2 cellpadding=3 width="95%" height=30 border=0>
 <tr><td valign="center" height=5><hr size=1 color="#cccccc" width="99%"><br />回复信息</td></tr>
 <tr>
    <td class="pad10L tbPad10" vAlign="top" height=60>
       <table cellspacing=0 cellpadding=3 width="100%" border=0>
       <form name="sendmsgForm" action="my.php" method="post" onsubmit="return check_sendmsg(this);">
       <tr>
<td width="100">&nbsp;</td>
            <td width="90%"><span id="contentip"></span></td>
        </tr>
        <tr>
<td width=100 align=right>&nbsp;</td>
             <td width=90%>
             <input type="hidden" name="pmid" value="<?php echo $msg[pmid];?>" />
             <input type="hidden" name="backuid" value="<?php echo $msg[msgfromid];?>" />
             <input type="hidden" name="title" value="<?php echo $msg[subject];?>" />
             <textarea name="content" rows=8 cols=60></textarea></td></tr>
        <tr>
<td width=100 align=right>&nbsp;</td>
             <td width=90%><SPAN class="f14">
             <input type="hidden" name="command" value="replymsg" />
             <input type="hidden" name="boxtype" value="<?php echo $_GET[boxtype];?>" />
             <input type="hidden" name="page" value="<?php echo $_GET[page];?>" />
             <input type="hidden" name="formhash" value="<?php echo form_hash();?>" />
             <input type="submit" name="submit" value="好了，提交" />
             <input type="button" name="close" value="返回" onclick="history.back();" />
             </SPAN></td></tr>
        </form>
        </table>
  </td>
 </tr>
</table>
</div>
</div>
<?php } ?>
</div>
<?php include template('footer'); ?>
