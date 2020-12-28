<?php
/**
  * wechat php test
  */

require_once 'logger.php';
require_once 'uc_query.php';

//define your token
define("TOKEN", "17SALSAofN4ObxjY0mkPZdHAFsS");
traceHttp();
$wechatObj = new wechatCallbackapi();
logger("test0");
$wechatObj->validInput();

class wechatCallbackapi
{

/*    const textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%d</FuncFlag>
</xml>";

    const imageTpl="<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[image]]></MsgType>
 <Image>
 <MediaId><![CDATA[%d]]></MediaId>
 </Image>
 </xml>";

    const voiceTpl = "";
    const videoTpl = "";
    const locationTpl = "";
    const linkTpl = "";
*/
    public function initValid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    //valid signature and response to input message
    public function validInput()
    {
logger("test01");
        //valid signature , option
        if($this->checkSignature()){
logger("test02");
            $this->responseMsg();
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        logger("R ".$postStr);

        //extract post data
        if (!empty($postStr)){
                
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $rx_type = trim($postObj->MsgType);

                switch($rx_type)
                {
                    case "text":
                        $resultStr = $this->receiveText($postObj);
                        break;

                    case "event":
                        $resultStr = $this->receiveEvent($postObj);
                        break;

                    case "image":
                        $resultStr = $this->receivePicture($postObj);
                        break;

                    case "voice":
                        $resultStr = "Unsupported msg type: ".$rx_type;
                        break;

                    case "video":
                        $resultStr = "Unsupported msg type: ".$rx_type;
                        break;

                    case "location":
                        $resultStr = "Unsupported msg type: ".$rx_type;
                        break;

                    case "link":
                        $resultStr = "Unsupported msg type: ".$rx_type;
                        break;

                    default:
                        $resultStr = "Unknown msg type: ".$rx_type;
                        break;
                }
                logger("T ".$resultStr);
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }
        
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    private function receiveText($object)
    {
        $funcFlag = 0;
        $keyword = trim($object->Content);
        $resultStr = "";
        $cityArray = array();
        $contentStr = "";
        $needArray = false;
        $illegal = false;
        $saytome = false;
        
        if ($keyword == "blog"){
            $testuc = new UcHome();
            $resultStr = $testuc->retrieve_blog_list($object);
        }
        else {
            $contentStr = "received a text from ".trim($object->FromUserName).", Thanks!";//translate($keyword);
            $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        }
        //Content ..........2048.............
        return $resultStr;
    }
    
    private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "Welcom to 17salsa.com!";
                break;
            case "unsubscribe":
                $contentStr = "";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "V1001_TODAY_PARTY":
                       $testuc = new UcHome();
                       $resultStr = $testuc->retrieve_party_list($object);  
                        break;
                    case "V1001_TODAY_BLOG":
logger("test30");
                        $testuc = new UcHome();
                        $resultStr = $testuc->retrieve_blog_list($object);                
                        break;
                    case "V1001_GOOD":
                        $contentStr = "Thanks!";
                        $resultStr = $this->transmitText($object, $contentStr);
                        break;
                    default:
                        $contentStr = "receive an eventkey: ".$object->EventKey;
                        $resultStr = $this->transmitText($object, $contentStr);
                        break;
                }
                break;
            default:
                $contentStr = "receive a new event: ".$object->Event;
                $resultStr = $this->transmitText($object, $contentStr);
                break;
        }
        return $resultStr;
    }
    
    private function receivePicture($object)
    {
        $imageTpl ="";
    }

    private function transmitText($object, $content, $flag = 0)
    {
      $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%d</FuncFlag>
</xml>";

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
}

?>
