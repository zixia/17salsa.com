<?php

require_once 'logger.php';
//require_once 'msg_template.php';

define('IN_UCHOME', TRUE);
define('S_ROOT', substr(dirname(__FILE__), 0, -2));

require_once S_ROOT.'./config.php';

//local config
$_SGLOBAL['cache']['network_blog'] = 24*60*60;
$_SGLOBAL['cache']['wx_ad'] = 24*60*60;


printf("test0\n");
$uctest = new UcHome();
$obj  = array('url' => "", 'picurl' => "", 'message' => "", 'subject' => "");
printf("test1\n");
$ret = $uctest->retrieve_party_list($obj);
printf($ret."\n");
printf("test2\n");
$ad = $uctest->retrieveAds($obj);
printf($ad['subject']."\n");
printf("test3\n");

//include_once(S_ROOT.'./data/data_network.php');

class UcHome {

    public function retrieve_party_list($object) {
        $resultStr = "Today's party list.";
        return $resultStr;
    }

    public function retrieve_blog_list($object) {
        $cachefile = S_ROOT.'./data/cache_network_blog.txt';
        if(1==1){   //check_network_cache('blog')) {
            $file_content = $this->sreadfile($cachefile);
            $bloglist = unserialize($file_content);
        } else {

/*    $sqlarr = mk_network_sql('blog',
        array('blogid', 'uid'),
        array('hot','viewnum','replynum'),
        array('dateline'),
        array('dateline','viewnum','replynum','hot')
    );
    extract($sqlarr);

    //ÒþË½
    $wherearr[] = "main.friend='0'";

    //ÏÔÊ¾ÊýÁ¿
    $shownum = 6;

    $query = $_SGLOBAL['db']->query("SELECT main.*, field.*
        FROM ".tname('blog')." main
        LEFT JOIN ".tname('blogfield')." field ON field.blogid=main.blogid
        WHERE ".implode(' AND ', $wherearr)."
        ORDER BY main.{$order} $sc LIMIT 0,$shownum");
    $bloglist = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $value['message'] = getstr($value['message'], 86, 0, 0, 0, 0, -1);
        $value['subject'] = getstr($value['subject'], 50, 0, 0, 0, 0, -1);
        $bloglist[] = $value;
    }
    if($_SGLOBAL['network']['blog']['cache']) {
        swritefile($cachefile, serialize($bloglist));
    } */
        }

        foreach($bloglist as $key => $value) {
            //realname_set($value['uid'], $value['username']);
            $value[url] = "http://17salsa.com/home/wap/space.php?do=blog&uid=".$value[uid]."&id=".$value[blogid];
            $value[picurl] = "";
            $bloglist[$key] = $value;
        }

        $ads = $this->retrieveAds($value);
        //$ads[url] = "http://17salsa.com/home/space-event-id-2298.html";
        //$ads[picurl] = "http://17salsa.com/home/attachment/201312/30/37_1388414463SS2M.jpg";
        //$ads[subject] = "17Salsa 5 Years Party";
        //$ads[message] = "17Salsa aniversary party";
        if ($ads) {
            array_unshift($bloglist, $ads);
        }

        $resultStr = $this->transmitPicText($object, $bloglist);
printf("\nresult: ".$resultStr);
        return $resultStr;
    }

    private function dbconnect() {
        global $_SGLOBAL, $_SC;

        include_once(S_ROOT.'./source/class_mysql.php');

        if(empty($_SGLOBAL['db'])) {
            $_SGLOBAL['db'] = new dbstuff;
            $_SGLOBAL['db']->charset = $_SC['dbcharset'];
            $_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']);
        }
    }

    public function retrieveAds($object)
    {
      global $_SGLOBAL, $_SC;
      $cachefile = S_ROOT.'./data/cache_wx_ad.txt';
printf("test2.01\n");
      if($this->check_cache('wx_ad')) {
      printf("test2.02\n");
            $file_content = $this->sreadfile($cachefile);
            $object = unserialize($file_content);
      } else {



     printf("test2.1\n");
        $ads = $object;
     printf("test2.2\n");
     printf("\nHost:".$_SC['dbhost']);
     printf("\nUser:".$_SC['dbuser']);
     printf("\nPW:".$_SC['dbpw']);
     if(empty($_SGLOBAL['db'])) {printf("\nEmpty...\n");}
         $this->dbconnect();
      printf("test2.3\n");
     $querystring = "SELECT title, adcode FROM uchome_ad WHERE system='0' AND available='1' AND title LIKE 'wx-%' ORDER BY adid DESC limit 1";
printf("Query: ".$querystring."\n");
        $query = $_SGLOBAL['db']->query($querystring);
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            printf("Value: title->".$value[title]." adcode->".$value[adcode]."\n");

            $wxads = unserialize($value[adcode]);
            $object[url] = $wxads[imageurl];
            $object[picurl] = $wxads[imagesrc];
            $object[subject] = substr($value[title], 3);  //"wx-...."
            $object[message] = $wxads[imagealt];

            $this->swritefile($cachefile, serialize($object));
        }
    }
printf("test2.5\n");
    return $object;
}

    private function retrieveAd()
    {
        
        $ads = $object;
        $ads[url] = "http://17salsa.com/home/space-event-id-2298.html";
        $ads[picurl] = "http://17salsa.com/home/attachment/201312/30/37_1388414463SS2M.jpg";
        $ads[subject] = "17Salsa 5 Years Party";
        $ads[message] = "17Salsa aniversary party";
        //array_unshift($bloglist, $ads);
        return $ads;
    }

    private function transmitPicText($object, $datalist)
    {
        $picTextTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%d</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%d</ArticleCount>
<Articles>
%s
</Articles>
</xml>";

        $articleTpl = "<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>";

        $content = "";
        $article_count = 0;

        foreach($datalist as $key => $value) {
printf("test3333\n");
            $article = sprintf($articleTpl, $value[subject], $value[message], $value['picurl'], $value['url']);
            $content = $content.$article;
            $article_count = $article_count + 1;
        }
        $resultStr = sprintf($picTextTpl, $object->FromUserName, $object->ToUserName, time(), $article_count, $content);

        return $resultStr;
    }


    //t2.5\n");ì²é»º´æ
    private function check_cache($type) {
        global $_SGLOBAL;
        if($_SGLOBAL['cache'][$type]) {
            $cachefile = S_ROOT.'./data/cache_'.$type.'.txt';
            if(file_exists($cachefile)) {
                $ftime = filemtime($cachefile);
                if(time() - $ftime < $_SGLOBAL['cache'][$type]) {
                    return true;
                }
            }
        }
        return false;
    }

    //»ñÈ¡ÎÄ¼þÄÚÈÝ
    private function sreadfile($filename) {
        $content = '';
        if(function_exists('file_get_contents')) {
            @$content = file_get_contents($filename);
        } else {
            if(@$fp = fopen($filename, 'r')) {
                @$content = fread($fp, filesize($filename));
                @fclose($fp);
            }
        }
        return $content;
    }

    //Ð´ÈëÎÄ¼þ
    private function swritefile($filename, $writetext, $openmod='w') {
        if(@$fp = fopen($filename, $openmod)) {
            flock($fp, 2);
            fwrite($fp, $writetext);
            fclose($fp);
            return true;
        } else {
            runlog('error', "File: $filename write error.");
            return false;
        }
    }
}

?>
