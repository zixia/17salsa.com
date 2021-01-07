<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?><table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>
<td with="150" align="left" nowrap><a href="http://www.17salsa.net/site/"><font color="brown">17Salsa资讯站最新资讯：</font></a></td>
<td width="650" align="left"><marquee scrollamount="5" onmouseover=this.stop() onmouseout=this.start()>
<?php if(is_array($iarr)) { foreach($iarr as $ikey => $value) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?=$value['url']?>" target="_blank" title="<?=$value['subjectall']?>"><?=$value['subject']?></a>
<?php } } ?>
</marquee></td>
</tr></table>