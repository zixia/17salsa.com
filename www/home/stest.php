<?php
#define SHORT_URL_LEN 3

$_TPL['URL'] = array( 'U1t'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=496&id=10370',
                      'aB3'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=3723&id=10345',
                      'yV5'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=3987&id=10378',
                      'd4X'=>'http://www.17salsa.com/home/wap/space.php?do=blog&uid=466&id=10504',
                      '11E'=>'http://17salsa.com/home/space-3689-do-thread-id-5306.html',
                      '9Mm'=>'http://17salsa.com/home/space-124-do-thread-id-1393.html',
                      '7Bt'=>'http://17salsa.com/home/space-8-do-thread-id-110.html',
                      '3Gr'=>'http://17salsa.com/site/viewnews-1640-salsa-bachata.html',
                      'BBf'=>'http://17salsa.com/home/space-328-do-thread-id-5428.html'
                      );

$uri = $_SERVER["REQUEST_URI"];
$key = substr($uri, strpos($uri, "?")+1, SHORT_URL_LEN);
$url = $_TPL['URL'][$key];

if($url == '')
{
  $url = "http://www.17salsa.com/home";
}

Header("HTTP/1.1 303 See Other"); 
Header("Location: $url");  
exit;

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
 
  for ($i = 0; $i < $subHexLen; $i++) {
    $subHex = substr ($hex, $i * 8, 8);
    $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
    $out = '';
 
    for ($j = 0; $j < SHORT_URL_LEN + 1; $j++) {
      $val = 0x0000001F & $int;
      $out .= $base32[$val];
      $int = $int >> SHORT_URL_LEN;
    }
 
    $output[] = $out;
  }
 
  return $output;
}

function get_url( $short )
{
    $url = '';

    $conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
    mysql_select_db(MK_DBNAME);

    $result=mysql_query("select count(*),url from short_url where short='$short'");
    $row=mysql_fetch_row($result);
    if($row[0]>=1)
    {
        $url = $row[1];
    }

    mysql_close($conn);
    return $url;
}

function set_url( $url )
{
    $conn=mysql_connect(MK_DBHOST, MK_DBUSER, MK_DBPW);
    mysql_select_db(MK_DBNAME);

    $short = shorturl($url);

    for($i = 0; $i < 4; $i++)
    {
        $result=mysql_query("select count(*) from short_url where short='$short[$i]'");
        $row=mysql_fetch_row($result);
        if($row[0] >= 1)
           continue;

        $result=mysql_query("insert into short_url values(short='$short[$i]', url='$url'");
        $error = mysql_error() ? mysql_error() : 0;
        if( $error )
        {
            echo $error;
            mysql_close($conn);
            return '';
        }
        else
        {
           mysql_close($conn);
           return $short[$i];
        }
    }

    mysql_close($conn);
    return '';
}
?>
