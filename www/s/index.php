<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


$s = $_REQUEST['s'];

header("Location: http://goo.gl/$s",TRUE,302);
?>
