<?php
include_once('./config.php');
include_once('./common.php');
include_once('.source/function_common.php');
include_once('./magzine_function.php');

$_TPL['col_title'] = array( 0=>'精彩日志', '热门话题', '动听音乐', '经典视频', '学术论坛', '培训活动');
$_TPL['col_more'] = array( 0=>'日志', '话题', '音乐', '视频', '学术', '活动');
$_TPL['col_url'] = array( 0=>'space-blog-view-all.html',
                          'space-mtag.html',
                          'http://17salsa.com/site/category-2-salsa-bachata.html',
                          'http://17salsa.com/site/category-1-salsa-bachata.html',
                          'http://17salsa.com/site/category-10-salsa-bachata.html',
                          'space-event-view-recommend.html');
$_TPL['col_type'] = array( 0=>1,2,2,1,3,1 );  //版块类型：1=>有推荐双栏;2=>有推荐单栏;3=>无推荐单栏;4=>无推荐双栏
$_TPL['title_len'] = array(1=>18,30,18,30);
$_TPL['ul_title_len'] = 18;
$_TPL['message_len'] = 60;
$_TPL['username_len'] = 6;
$_TPL['user_url_base'] = 'space.php?uid=';
$_TPL['club_url_base'] = 'space-mtag-tagid-';

include_once('./magzine_data.php');

//mysql_query("set names gb2312");//设置显示字体为简体中文 
header("Content-type: text/html; charset=utf-8");
include_once template("magzine_header");

foreach($urllist as $key=>$col)
{
   $_TPL['title'] = $_TPL['col_title'][$key];  
   $_TPL['more'] = $_TPL['col_more'][$key];

   $list = array();
   $url_count = 0;
   foreach($col as $url)
   {
      if(ereg("space-([0-9]+)-",$url,$regs))
      	$uid = $regs[1];
      else
        $uid = 0;

      if(ereg("event-id-([0-9]+)",$url,$rege))
      {
        $eventid = $rege[1];
        $event = get_event($eventid);
        $uid = $event[0];
        $user_name = FSubstr($event[1],0,$_TPL['username_len']);
        $title = FSubstr($event[2],0,$_TPL['title_len'][$_TPL['col_type'][$key]]);
        $city = $event[3];
        $city_url = '/home/';
      }

      if(ereg("blog-id-([0-9]+)",$url, $regt))
      {
    	$blogid = $regt[1];
        $blogfield = get_blog($blogid);
        $message = '';
        if($_TPL['col_type'][$key]<3 && $url_count<2)
        {
           $message = FSubstr(htmlspecialchars(pregstring(get_message($blogid))),0,$_TPL['message_len']);
        }
        $user_name = FSubstr($blogfield[1],0,$_TPL['username_len']);
        $title = FSubstr($blogfield[2],0,$_TPL['title_len'][$_TPL['col_type'][$key]]);
      }

      if(ereg("thread-id-([0-9]+)",$url, $regt))
      {
        $threadid = $regt[1];
        $thread = get_thread($threadid);
        $message = '';
        /*if($_TPL['col_type'][$key]<3 && $url_count<2)
        {
           $message = get_message($threadid);
        }*/
        $user_name = FSubstr($thread['username'],0,$_TPL['username_len']);
        $title = FSubstr($thread['subject'],0,$_TPL['title_len'][$_TPL['col_type'][$key]]);
        $club = $thread['tagname'];
        $club_url = $_TPL['club_url_base'].$thread['tagid'].'.html'.$_TPL['analytic'];
      }
      else
      {
         $club = 'Salsa';$club_url = '/home/';
      }

      if(strlen($titlelist[$key][$url_count]) > 0)
      {
         $title = $titlelist[$key][$url_count];
      }
      $user_url = $_TPL['user_url_base'].$uid.$_TPL['analytic'];
      $title_url = $url.$_TPL['analytic'];
      $avatar = avatar($uid,'small',TRUE); 
      //$club = 'Salsa';$club_url = '/home/';
      //$city = '北京';$city_url = '/home/';
      $listitem = array(
				 $url_count => array(
	                  'title' => $title,
    	              'title_url' => $title_url,
        	          'user'  => $user_name,
            	      'user_avatar' => $avatar,
                	  'user_url' => $user_url,
                      'message' => $message,
                      'club' => $club,
                      'club_url' => $club_url,
                      'city' => $city,
                      'city_url' => $city_url
                     )
                  );
      if($_TPL['col_type'][$key]<3 && $url_count==0)
      {
        $listitem[$url_count]['title'] = $title = FSubstr($listitem[$url_count]['title'],0,$_TPL['ul_title_len']);
        $ul1 = $listitem[$url_count];
      }
      else if($_TPL['col_type'][$key]<3 && $url_count==1)
      {
        $listitem[$url_count]['title'] = $title = FSubstr($listitem[$url_count]['title'],0,$_TPL['ul_title_len']);
        $ul2 = $listitem[$url_count];
      }
      else
      {
      	$list = $list + $listitem;
      }
      $url_count += 1;   
   }

   include template("magzine_col".$_TPL['col_type'][$key]);
   include template("magzine_ads");
}
 
//$_TPL['css'] = 'network';
include_once template("magzine_footer");

?>

