<?php
$domain = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'17salsa.com' ;
//echo $domain . "<br>";

// 17salsa VIP card
if ( preg_match('/^vip\.17salsa/i',$domain) 
	|| preg_match('/^www\.vip\.17salsa/i',$domain) 
	|| preg_match('/4006331733/',$domain)
	)
{
	header("Location: http://17salsa.com/vip/",TRUE,302);
	die();
}

if ( preg_match('/^hksf2011\.17salsa/i',$domain)
    || preg_match('/^www\.hksf2011\.17salsa/i',$domain)
    )
{
    header("Location: http://17salsa.com/home/space-124-do-thread-id-5785.html",TRUE,302);
    die();
}

// 如果是自定义域名，则需要到 /home
if ( !preg_match('/^17salsa/i',$domain) 
	&& !preg_match('/^www\.17salsa/i',$domain) )
{
	// 不能用 http:// ，否则自定义域名会失效
	header("Location: /home/",TRUE,302);
	die();
}

header("Location: http://17salsa.com/home/",TRUE,302);
die();
	

/*
if ( preg_match('/com$/i',$domain) )
{
	header("Location: http://17salsa.net/site/",TRUE,302);
	die();
}
*/


?>
