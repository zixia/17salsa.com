<?php

function FSubstr($title,$start,$len="",$magic=true) 
{
  /**
    *    powered by Smartpig
    *    mailto:d.einstein@263.net
    */
  return $title;

  $length = 0;
  if($len == "") $len = strlen($title);

  //判断起始为不正确位置
  if($start > 0)
  {
    $cnum = 0;
    for($i=0;$i<$start;$i++)
    {
     if(ord(substr($title,$i,1)) >= 128) $cnum ++;
    }
    if($cnum%2 != 0) $start--;
  
    unset($cnum);
  }

  if(strlen($title)<=$len) return substr($title,$start,$len);

  $alen     = 0;
  $blen = 0;

  $realnum = 0;

  for($i=$start;$i<strlen($title);$i++)
  {
    $ctype = 0;
    $cstep = 0;
    $cur = substr($title,$i,1);
    if($cur == "&")
    {
     if(substr($title,$i,4) == "&lt;")
     {
      $cstep = 4;
      $length += 4;
      $i += 3;
      $realnum ++;
      if($magic)
      {
       $alen ++;
      }
     }
     else if(substr($title,$i,4) == "&gt;")
     {
      $cstep = 4;
      $length += 4;
      $i += 3;
      $realnum ++;
      if($magic)
      {
       $alen ++;
      }
     }
     else if(substr($title,$i,5) == "&amp;")
     {
      $cstep = 5;
      $length += 5;
      $i += 4;
      $realnum ++;
      if($magic)
      {
       $alen ++;
      }
     }
     else if(substr($title,$i,6) == "&quot;")
     {
      $cstep = 6;
      $length += 6;
      $i += 5;
      $realnum ++;
      if($magic)
      {
       $alen ++;
      }
     }
     else if(substr($title,$i,6) == "&#039;")
     {
      $cstep = 6;
      $length += 6;
      $i += 5;
      $realnum ++;
      if($magic)
      {
       $alen ++;
      }
     }
     else if(preg_match("/&#(\d+);/i",substr($title,$i,8),$match))
     {
      $cstep = strlen($match[0]);
      $length += strlen($match[0]);
      $i += strlen($match[0])-1;
      $realnum ++;
      if($magic)
      {
       $blen ++;
       $ctype = 1;
      }
     }
    }else{
     if(ord($cur)>=128)
     {
      $cstep = 2;
      $length += 2;
      $i += 1;
      $realnum ++;
      if($magic)
      {
       $blen ++;
       $ctype = 1;
      }
     }else{
      $cstep = 1;
      $length +=1;
      $realnum ++;
      if($magic)
      {
       $alen++;
      }
     }
    }
  
    if($magic)
    {
     if(($blen*2+$alen) == ($len*2)) break;
     if(($blen*2+$alen) == ($len*2+1))
     {
      if($ctype == 1)
      {
       $length -= $cstep;
       break;
      }else{
       break;
      }
     }
    }else{
     if($realnum == $len) break;
    }
  }

  unset($cur);
  unset($alen);
  unset($blen);
  unset($realnum);
  unset($ctype);
  unset($cstep);

  return substr($title,$start,$length);
}

function pregstring( $str ){
    $strtemp = trim($str);
    $search = array(
                "|'|Uis",
                "|<script[^>]*?>.*?</script>|Uis", // 去掉 javascript
				"|<[\/\!]*?[^<>]*?>|Uis", // 去掉 HTML 标记
				"'>(quot|#34);'i", // 替换 HTML 实体
                "/\[flash\=?(media|real)*\](.+?)\[\/flash\]/ie", //去除BBCode
                "/\\[mp3\\=?(auto)*\\](.+?)\\[\\/mp3\\]/ie", //去除BBCode
				"'>(amp|#38);'i",
				"|,|Uis",
				"|[\s]{2,}|is",
				"[>nbsp;]isu",
				"|[$]|Uis",
             );
	$replace = array(
				"`",
				"",
				"",
				"",
                "",
                "",
				"",
				"",
				" ",
				" ",
				" dollar ",
              );
    $text = preg_replace($search, $replace, $strtemp);
    return $text;
}

function get_blog($blogid)
{
    $conn=mysql_connect(UC_DBHOST, UC_DBUSER, UC_DBPW);
    mysql_select_db(UC_DBNAME);

    $query=mysql_query("SELECT uid,username,subject,blogid FROM uchome_blog WHERE blogid =".$blogid.";");
    $row=mysql_fetch_row($query);
    
    return $row;
}

function get_thread($threadid)
{
    $conn=mysql_connect(UC_DBHOST, UC_DBUSER, UC_DBPW);
    mysql_select_db(UC_DBNAME);

    $query=mysql_query("SELECT uid,username,subject,tagid FROM uchome_thread WHERE tid =".$threadid.";");
    $row1=mysql_fetch_row($query);
    $tagid = $row1[3];

    $query=mysql_query("SELECT tagid,tagname FROM uchome_mtag WHERE tagid =".$tagid.";");
    $row2=mysql_fetch_row($query);

    $ret = array(
             'uid' => $row1[0],
             'username' => $row1[1],
             'subject' => $row1[2],
             'tagid'  =>  $row1[3],
             'tagname' => $row2[1]
           );

    return $ret;
}

function get_event($eventid)
{
    $conn=mysql_connect(UC_DBHOST, UC_DBUSER, UC_DBPW);
    mysql_select_db(UC_DBNAME);

    $query=mysql_query("SELECT uid, username, title, city FROM uchome_event WHERE eventid =".$eventid.";");
    $row=mysql_fetch_row($query);

    return $row;
}

function get_message($blogid)
{
    $conn=mysql_connect(UC_DBHOST, UC_DBUSER, UC_DBPW);
    mysql_select_db(UC_DBNAME);

    $query=mysql_query("SELECT message FROM uchome_blogfield WHERE blogid =".$blogid.";");
    $row=mysql_fetch_row($query);

    return $row[0];
}

?>

