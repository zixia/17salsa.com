<?php

//-------------------------------------------------------------
function traceHttp()
{
    $query_string = "";
    if ($_POST) {
        $kv = array();
        foreach ($_POST as $key => $value) {
            $kv[] = "$key=$value";
        }
        $query_string = "[_POST]".join("&", $kv);
    }
    else {
        $query_string = $_SERVER['QUERY_STRING'];
    }

    logger("REMOTE_ADDR: ".$_SERVER["REMOTE_ADDR"].((strpos($_SERVER["REMOTE_ADDR"], "101.226"))?" From Weixin":" Unknown IP"));
    logger("QUERY_STRING: ".$query_string);
}

function logger($content)
{
    file_put_contents("log.html", date('Y-m-d H:i:s ').$content."<br>", FILE_APPEND);
}

?>
