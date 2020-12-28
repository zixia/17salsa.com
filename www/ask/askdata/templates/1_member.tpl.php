<?php if(!defined('IN_CYASK')) exit('Access Denied'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $username;?> - 个人中心 - Powered By cyask</title>
<meta content="<?php echo $meta_description;?>" name="description" />
<meta content="<?php echo $meta_keywords;?>" name="keywords" />
<link href="<?php echo $styledir;?>/default.css" type="text/css" rel="stylesheet" />
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
<body>
<div id=main align="center">
<table width="100%" align=center border=0>
  <tr height=60><td valign=top width=170 height=69>
    &nbsp;<a href="./"><img src="<?php echo $styledir;?>/1000ask.gif" border=0></a></td>
<td><table cellspacing=0 cellpadding=0 width="99%" border=0>
        <tr>
          <td class="blueBG" height="25" align="left">&nbsp;<b class="p14"><?php echo $username;?></b>&nbsp;<b class="p14">个人中心</b>&nbsp;&nbsp;<a href='./'>返回首页</a></td>
          <td class="blueBG" width="300" height="25" align="right">&nbsp;&nbsp;
          </td>
          </tr></table>
     </td></tr>
</table>
<div id="leftw">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
<?php if($command=='info') { ?>
<tr><td class="blueBG f14" height="22">他(她)的档案</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="member.php?command=info&amp;uid=<?php echo $uid;?>">他(她)的档案</a></td></tr>
<?php } if($command=='score') { ?>
<tr><td class="blueBG f14" height=22>他(她)的积分</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="member.php?command=score&amp;uid=<?php echo $uid;?>">他(她)的积分</a></td></tr>
<?php } if($command=='question') { ?>
<tr><td class="blueBG f14" height=22>他(她)的提问</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="member.php?command=question&amp;uid=<?php echo $uid;?>">他(她)的提问</a></td></tr>
<?php } if($command=='answer') { ?>
<tr><td class="blueBG f14" height=22>他(她)的回答</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="member.php?command=answer&amp;uid=<?php echo $uid;?>">他(她)的回答</a></td></tr>
<?php } if($command=='share') { ?>
<tr><td class="blueBG f14" height=22>他(她)的分享</td></tr>
<?php } else { ?>
<tr><td class="f14" height=22><a href="member.php?command=share&amp;uid=<?php echo $uid;?>">他(她)的分享</a></td></tr>
<?php } ?>
</table>
</div>
<div id=rightw>
<?php if($command=='score') { ?>
<div class="mt mgb"><a href="my.php?command=sendmsg&amp;username=<?php echo $username;?>">给他(她)发信</a></div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=2 cellpadding=3 width="100%" border=0>
<TR><TD vAlign=top width="33%">
      <TABLE cellSpacing=1 cellPadding=3 width=100% bgColor=#dbdbdb border=0>
       <TR><TD class=pad10L noWrap bgColor=#ffffff><DIV align=center>积分明细</DIV></TD></TR>
        <TR bgColor=#ffffff><TD class="pad10L tbPad10" vAlign=center height=60>
            <TABLE cellSpacing=0 cellPadding=3 width="100%" border=0>
               <TR><TD width=100><DIV align=right>总积分:</DIV></TD>
                   <TD width=100><SPAN class=f14><?php echo $totalscore;?></SPAN></TD></TR>
             </TABLE></TD></TR>
       </TABLE>
     </TD>
     <td valign=top width="33%">
       <TABLE cellspacing=1 cellpadding=3 width=100% bgColor=#dbdbdb border=0>
        <TR><TD class=pad10L noWrap bgColor=#ffffff><DIV align=center>回答统计</DIV></TD></TR>
        <TR bgColor=#ffffff><TD class="pad10L tbPad10" valign=center height=60>
            <TABLE cellspacing=0 cellpadding=3 width="100%" border=0>
              <TR><TD noWrap width=100><DIV align=right>回答总数:</DIV></TD>
                   <TD width=100><?php echo $answercount;?></TD></TR>
              <TR><TD noWrap width=100><DIV align=right>回答被采纳数:</DIV></TD>
                  <TD  width=100><?php echo $adoptcount;?></TD></TR>
              <TR><TD noWrap width=100><DIV align=right>回答采纳率:</DIV></TD>
<TD width=100><?php echo $rightvalage;?></TD></TR>
             </TABLE>
            </TD></TR>
        </TABLE>
</td>
    <td valign=top width="33%">
      <TABLE cellSpacing=1 cellPadding=3 width=100% bgColor=#dbdbdb border=0>
        <TR><TD class=pad10L noWrap bgColor=#ffffff><DIV align=center>提问统计</DIV></TD></TR>
        <TR bgColor=#ffffff><TD class="pad10L tbPad10" vAlign=center height=60>
             <TABLE cellSpacing=0 cellPadding=3 width="100%" border=0>
                <TR><TD width=100><DIV align=right>提问总数:</DIV></TD>
                    <TD width=100><?php echo $question_allcount;?></TD></TR>
                <TR><TD noWrap width=100><DIV align=right>已解决问题数:</DIV></TD>
                    <TD width=100><?php echo $questionOK;?></TD></TR>
                <TR><TD noWrap width=100><DIV align=right>待解决问题数:</DIV></TD>
                    <TD width=100><?php echo $questionASK;?></TD></TR>
<TR><TD noWrap width=100><DIV align=right>投票中问题数:</DIV></TD>
                    <TD  width=100><?php echo $questionVOTE;?></TD></TR>
<TR><TD noWrap width=100><DIV align=right>已关闭问题数:</DIV></TD>
                    <TD width=100><?php echo $questionCLOSE;?></TD></TR>
             </TABLE></TD></TR>
       </TABLE>
    </TD>
 </TR>
</TABLE>
</div>
</div>
<?php } elseif($command=='info') { ?>
<div class="mt mgb"><a href="my.php?command=sendmsg&amp;username=<?php echo $username;?>">给他(她)发信</a></div>
<div class="mb mcb">
<div class="w100">
<table cellspacing="2" cellpadding="2" width="100%" height="200" border="0">
<tr>
<td valign=top width="40%">
   <TABLE cellSpacing=1 cellPadding=3 width=100% bgColor=#dbdbdb border=0>
      <tr><td class="pad10L" width="100%" nowrap="nowrap" bgColor="#ffffff" align="center">基本信息</td></tr>
       <TR bgColor="#ffffff"><TD class="pad10L tbPad10" valign="center" height="60">
            <TABLE cellSpacing=0 cellPadding=3 width="260" border=0>
               <TR><TD width=100 align=right>用户名:</TD>
                   <TD width=160><SPAN class=f14><?php echo $member_username;?></SPAN></TD></TR>
               <TR><TD width=100 align=right>电子邮箱:</TD>
                   <TD width=160><SPAN class=f14>
<?php if($member_email) { echo $member_email;?>
<?php } else { ?>
邮箱隐藏
<?php } ?>
</SPAN></TD></TR>
               <TR><TD width=100 align=right>性 别:</TD>
                   <TD width=160><SPAN class=f14>
<?php if($member_gender==1) { ?>
男
<?php } elseif($member_gender==2) { ?>
女
<?php } else { ?>
保密
<?php } ?>
</span></TD></TR>
               <TR><TD width=100 align=right>生 日:</TD>
                   <TD width=160><SPAN class=f14><?php echo $member_bday;?></SPAN></TD></TR>
             </TABLE>
            </TD>
           </TR>
     </TABLE>
</td>
<td valign=top width="60%">
   <TABLE cellspacing="1" cellpadding="3" width=98% bgColor=#dbdbdb border=0>
        <TR><TD class="pad10L" bgcolor="#ffffff" width="100%" align="center">附加信息</TD></TR>
        <TR bgColor="#ffffff"><TD class="pad10L" valign=center height=60>
            <TABLE cellspacing=0 cellpadding=3 width="100%" border=0>
            <TR><TD width="10%" align=right>QQ:</TD>
             <TD width="90%"><SPAN class=f14><?php echo $member_qq;?></SPAN></TD></TR>
              <TR><TD width="10%" align=right>MSN:</TD>
             <TD width="90%"><SPAN class=f14><?php echo $member_msn;?></SPAN></TD></TR>
            <TR><TD noWrap width="10%" align="right" valign=top>个性签名:</TD>
              <TD width="90%" align="left"><div><?php echo $member_signature;?></div></TD></TR>
             </TABLE>
            </TD></TR>
        </TABLE>
</td>
 </TR>
</TABLE>
</div>
</div>
<?php } elseif($command=='question') { ?>
<div class="mt mgb">他(她)的提问 &nbsp;(<?php echo $quescount;?>)</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border=0 valign=top>
<?php if($quescount) { ?>
<TR><TD width="60%" align="center" height="30">标题</TD>
    <TD width="8%" align="center">悬赏分</TD>
<TD width="8%" align="center">回答数</TD>
    <TD width="8%" align="center">状态</TD>
    <TD width="15%" align="center">提问时间</TD>
</TR>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>��<A href="question.php?qid=<?php echo $question['qid'];?>" title="<?php echo $question['title'];?>" target="_blank"><?php echo $question['stitle'];?></A></TD>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=1">[首页]</a>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='answer') { ?>
<div class="mt mgb">他(她)的回答 &nbsp;(<?php echo $answercount;?>)</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border=0>
<?php if($answercount) { ?>
<TR><TD width="54%" align=center height=30>标题</TD>
<TD align=center width="9%">悬赏分</TD>
    <TD align=center width="6%">评论</TD>
    <TD align=center width="7%">状态</TD>
    <TD align=center width="11%">采纳时间</TD>
    <TD align=center width="13%">回答时间</TD></TR>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<TR><TD class="linetop f14" height=25>��<a href="response.php?aid=<?php echo $question['aid'];?>" target=_blank title="<?php echo $question['title'];?>"><?php echo $question['stitle'];?></a></TD>
     <TD class=linetop align=center valign=middle><FONT color=red><?php echo $question['score'];?></FONT></TD>
 <TD class=linetop align=center valign=middle><FONT color=#333333><?php echo $question['response'];?></FONT></TD>
     <TD class=linetop align=center valign=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></TD>
     <TD class=linetop align=center valign=middle>
<?php if($question['adopt']) { ?>
<font color="red">已被采纳</font>
<?php } else { ?>
未被采纳
<?php } ?>
</td>
     <TD class=linetop align=center valign=middle><?php echo $question['answertime'];?></TD></TR> 
<?php } } ?>
<tr><td colspan=6>&nbsp;</td></tr>
<tr><td align=center colspan=6>
<?php if($page>1) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=1">[首页]</a>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } elseif($command=='share') { ?>
<div class="mt mgb">他(她)的分享 &nbsp;(<?php echo $quescount;?>)</div>
<div class="mb mcb">
<div class="w100">
<table cellspacing=0 cellpadding=0 width="100%" border=0 valign=top>
<?php if($quescount) { ?>
<TR><TD width="60%" align="center" height="30">标题</TD>
    <TD width="8%" align="center">悬赏分</TD>
<TD width="8%" align="center">回答数</TD>
    <TD width="8%" align="center">状态</TD>
    <TD width="15%" align="center">提问时间</TD>
</TR>
<?php } if(is_array($ques_list)) { foreach($ques_list as $question) { ?>
<tr><td class="linetop f14" vAlign=center height=25>��<A href="question.php?qid=<?php echo $question['qid'];?>" title="<?php echo $question['title'];?>" target="_blank"><?php echo $question['stitle'];?></A></TD>
    <td class=linetop vAlign=center align=middle><FONT color=red><?php echo $question['score'];?></FONT></td>
<td class=linetop vAlign=center align=middle><FONT color=#333333><?php echo $question['answercount'];?></FONT></td>
    <td class=linetop vAlign=center align=middle><SCRIPT>disQstate(<?php echo $question['status'];?>);</SCRIPT></td>
    <td class=linetop vAlign=center align=middle><?php echo $question['asktime'];?></td></tr>
<?php } } ?>
<tr><td colspan=5>&nbsp;</td></tr>
<tr><td align="center" colspan=5>
<?php if($page>1) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=1">[首页]</a>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_front;?>">前一页</a>                                                                                                                                                
<?php } if($pagecount>1) { echo $pagelinks;?>
<?php } if($page<$pagecount) { ?>
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $page_next;?>">下一页</a>                                                                                                                        
<a href="member.php?command=<?php echo $command;?>&amp;username=<?php echo $username;?>&amp;page=<?php echo $pagecount;?>">[尾页]</a>
<?php } ?>
</td></tr>
</table>
</div>
</div>
<?php } ?>
</div>
<br />
<?php include template('footer'); ?>
