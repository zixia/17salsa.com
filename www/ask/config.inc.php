<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

//Cyask 配置参数

$dbhost = getenv('SALSA17_MYSQL_HOST');	// 数据库服务器
$dbuser = getenv('SALSA17_MYSQL_USER'); // 数据库用户名
$dbpw   = getenv('SALSA17_MYSQL_PASS');	// 数据库密码
$dbname = '17salsa';		// 数据库名

$dbcharset = 'utf8';	// MySQL 字符集
$pconnect = 0;			// 数据库持久连接 0=关闭, 1=打开
$tablepre = 'cy_';   // 表名前缀
$cookiepre = 'cyask_';	// cookie 前缀
$cookiedomain = '';		// cookie 作用域
$cookiepath  = '/';		// cookie 作用路径
$cyask_key = '1234567890';	
$charset = 'utf-8';		// 程序默认字符集
$headercache = 0; 		// 页面缓存开关 0=关闭, 1=打开
$headercharset = 1;		// 强制设置字符集,只乱码时使用
$tplrefresh = 1;		// 模板自动刷新开关 0=关闭, 1=打开
$errorreport = 1;		// 是否报告 PHP 错误, 0=只报告给管理员和版主(更安全), 1=报告给任何人
$onlinehold = 300;		// 在线保持时间
$attachdir = './attachments';	// 附件保存位置 (服务器路径, 属性 777, 必须为 web 可访问到的目录, 不加 "/", 相对目录务必以 "./" 开头)
$attachurl = 'attachments';		// 附件路径 URL 地址 (可为当前 URL 下的相对地址或 http:// 开头的绝对地址, 不加 "/")
$htmlopen = 0; 			// 静态页生成开关 0=关闭, 1=打开


//UCenter 配置参数
define('UC_CONNECT', 'mysql'); 		// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen(), mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
//数据库相关 (mysql 连接时, 并且没有设置 UC_DBLINK 时, 需要配置以下变量)
define('UC_DBHOST', getenv('SALSA17_MYSQL_HOST')); 	// UCenter 数据库主机
define('UC_DBUSER', getenv('SALSA17_MYSQL_USER'));		// UCenter 数据库用户名
define('UC_DBPW', getenv('SALSA17_MYSQL_PASS')); 			// UCenter 数据库密码
define('UC_DBNAME', '17salsa'); 	// UCenter 数据库名称

define('UC_DBCHARSET', 'utf8'); 		// UCenter 数据库字符集
define('UC_DBTABLEPRE', '`17salsa`.uc_'); 	// UCenter 数据库表前缀
define('UC_DBCONNECT', '0'); 		// UCenter 数据库持久连接 0=关闭, 1=打开
//通信相关
define('UC_KEY', '0fW4k4Y0C9Wc2eMcS7Z6B8ubs1ca0aC4cdieQdPc4aV85e78k3rf65paYav3g1kf'); 	// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://17salsa.com/center'); // UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf-8'); 		// UCenter 的字符集
define('UC_IP', '211.99.222.57'); 				// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '7'); 			// 当前应用的 ID
