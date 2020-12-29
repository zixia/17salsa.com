<?php
define('MK_DBHOST', 'localhost');
define('MK_DBUSER', '17salsa');
define('MK_DBPW', '13911833788');
define('MK_DBNAME', 'test');

define('SHORTLEN', 5);
define('BASEURL', 'http://17salsa.com/s.p?');
define('MESSAGETEMPLATE', "Your url is shorted:<br/>original URL:&nbsp;%s<br/>&nbsp;shorted URL:&nbsp;<a href='%s%s'><b>%s%s</b></a>");

$_TPL['URL'] = array( 'U1t'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=496&id=10370',
                      'aB3'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=3723&id=10345',
                      'yV5'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=3987&id=10378',
                      'd4X'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=466&id=10504',
                      '11E'=>'http://17salsa.com/home/space-3689-do-thread-id-5306.html',
                      '9Mm'=>'http://17salsa.com/home/space-124-do-thread-id-1393.html',
                      '7Bt'=>'http://17salsa.com/home/space-8-do-thread-id-110.html',
                      '3Gr'=>'http://17salsa.com/site/viewnews-1640-salsa-bachata.html',
                      'BBf'=>'http://17salsa.com/home/space-328-do-thread-id-5428.html',
                      'tlF'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=3095&id=10740',
                      'lJe'=>'http://17salsa.com/home/space-115-do-thread-id-5446.html',
                      'dAN'=>'http://17salsa.com/home/space-4117-do-thread-id-5402.html',
                      'fX0'=>'http://17salsa.com/home/space-37-do-thread-id-5430.html',
                      'ne4'=>'http://17salsa.com/home/space-3689-do-blog-id-10732.html',
                      'nTo'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=8&id=11133.html',
                      'sD2'=>'http://17salsa.com/home/space.php?uid=227&do=thread&id=357',
                      'wwY'=>'http://17salsa.net/home/space-189-do-thread-id-4140.html',
                      'CsT'=>'http://17salsa.net/home/space-525-do-thread-id-5459.html',
                      'y1t'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=150&id=11065.html',
                      'fVv'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=3689&id=11153.html',
                      'a3a'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=124&id=10857.html',
                      'x7R'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=759&id=10770.html',
                      'ssC'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=3731&id=11101.html',
                      '1Z5'=>'http://17salsa.com/home/wap/space.php?do=blog&uid=1620&id=11152.html'
                   );
if( $_GET['ac'] == 'admin')
{
  $inputurl = $_POST["inputurl"];
  $inputwap = $_POST["inputwap"];
  $inputtail = $_POST["inputtail"];

  if ($inputwap != '')
  {
    $waproot = 'http://17salsa.com/home/wap/';
    $spacestr = 'space.php?do=blog&uid=';
    $threadstr = 'space.php?do=thread&uid=';
    $eventstr = 'space.php?do=event&uid=';

    if(ereg("space-([0-9]+)-",$inputwap,$regs1))
      $uid = $regs1[1];
    else if(ereg("uid\=([0-9]+)\&",$inputwap,$regs2))
      $uid = $regs2[1];
    else
      $uid = 0;

    if(ereg("do-blog-id-([0-9]+)",$inputwap,$regb1))
    {
      $blogid = $regb1[1];
      $inputurl = $waproot.$spacestr.$uid.'&id='.$blogid;
    }
    else if(ereg("do\=blog\&id\=([0-9]+)",$inputwap,$regb2))
    {
      $blogid = $regb2[1];
      $inputurl = $waproot.$spacestr.$uid.'&id='.$blogid;
    }
    else
      $blogid = 0;

    if(ereg("do-thread-id-([0-9]+)",$inputwap,$regt1))
    {
      $threadid = $regt1[1];
      $inputurl = $waproot.$threadstr.$uid.'&id='.$threadid;
    }
    else if(ereg("do\=thread\&id\=([0-9]+)",$inputwap,$regt2))
    {
      $threadid = $regt2[1];
      $inputurl = $waproot.$threadstr.$uid.'&id='.$threadid;
    }
    else
      $threadid = 0;

  }

  $message = '';
  if ($inputurl == '')
  {
     //print "admin";
  }
  else
  {
     if($inputtail != '')
     {
        $inputurl = $inputurl.$inputtail;
     }

     $prevshort = get_short($inputurl);
     if ($prevshort != '')
     {
        $message = sprintf(MESSAGETEMPLATE,
                           $inputurl, BASEURL, $prevshort, BASEURL, $prevshort);
     }
     else
     {
        $shortarray = shorturl($inputurl);
        for($i = 0; $i < count($shortarray); $i++)
        {
           if (get_url($shortarray[$i]) == '')
           {
              set_url($shortarray[$i], $inputurl);
              $message = sprintf(MESSAGETEMPLATE,
                                  $inputurl, BASEURL, $shortarray[$i], BASEURL, $shortarray[$i]);
              break;
           }
        }
     }
     if($message == '')
     {
        $message == "Shorten your URL failed!";
     }
  }
}
else
{
   $uri = $_SERVER["REQUEST_URI"];
   $key = substr($uri, strpos($uri, "?")+1, 3);
   $url = $_TPL['URL'][$key];
   if ($url == '')
   {
     $key = substr($uri, strpos($uri, "?")+1, SHORTLEN);
     $url = get_url($key);
   }

   if($url == '')
   {
     $url = "http://www.17salsa.com/home";
   }

   Header("HTTP/1.1 303 See Other"); 
   Header("Location: $url");  
   exit;
}


function get_short( $url )
{
    $short = '';

    $conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
    mysql_select_db(MK_DBNAME);

    $result=mysql_query("select short from short_url where url='$url'");
    $row=mysql_fetch_row($result);
    if(mysql_num_rows($result)>0)
    {
       $short = $row[0];
    }

    mysql_close($conn);
    return $short;
}

function get_url( $short )
{
    $url = '';

    $conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
    mysql_select_db(MK_DBNAME);

    $result=mysql_query("select url from short_url where short='$short'");
    $row=mysql_fetch_row($result);
    if(mysql_num_rows($result)>0)
    {
       $url = $row[0];
    }

    mysql_close($conn);
    return $url;
}

function shorturl($input) {
   $base32 = array (
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
    'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
    'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
    'y', 'z', '0', '1', '2', '3', '4', '5'
    );

   $hex = md5($input);
   $hexLen = strlen($hex);
   $subHexLen = $hexLen / 8;
   $output = array();

   for ($i = 0; $i < $subHexLen; $i++) 
   {
      $subHex = substr ($hex, $i * 8, 8);
      $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
      $out = '';

      for ($j = 0; $j < SHORTLEN; $j++) 
      {
        $val = 0x0000001F & $int;
        $out .= $base32[$val];
        $int = $int >> (SHORTLEN-1);
      }

      $output[] = $out;
  }

  return $output;
}

function set_url( $short, $url )
{

    $conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
    mysql_select_db(MK_DBNAME);

    $query=sprintf("insert into short_url (short, url) values ('%s', '%s');", $short, $url);

    $result=mysql_query($query);
    $error = mysql_error() ? mysql_error() : 0;
    if( $error )
    {
        echo $error;
        mysql_close($conn);
        return 2;
    }
}
?>
<html>
<form id="inputform" name="inputform" method="post" action="s.p?ac=admin">
Direct short: <input id="inputurl" name="inputurl" type="text" size="25" /><br />
WAP short:&nbsp;&nbsp;<input id="inputwap" name="inputwap" type="text" size="25" /><br />
Analytics:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="inputtail" name="inputtail" type="text" size="25" value="<?=$inputtail?>"/><br />
<input type="submit" name="btnSubmit" id="btnSubmit" value="Short It!" /> 
</form>
<div id="message" name="message">
<p><?=$message?></p>
</div>
</html>
