<?php
  if(!defined('WAP_CONTROL')) {
     include_once('./common.php');
  }  
  $ac = empty($_GET['ac'])?'':$_GET['ac'];  
  if($ac == $_SCONFIG['login_action']) {
     $ac = 'login';
  }
  elseif($ac == 'login') {
     $ac = '';
  }
  if($ac == $_SCONFIG['register_action']) {
     $ac = 'register';
  }
  elseif($ac == 'register') {
     $ac = '';
  }  
  $acs = array('login', 'register', 'lostpasswd', 'swfupload',
               'inputpwd', 'ajax', 'seccode', 'sendmail', 
               'stat', 'emailcheck');
  if(empty($ac) || !in_array($ac, $acs)) { 
     showmessage('enter_the_space', 'index.php', 0);
  }  
  $theurl = 'do.php?ac='.$ac;
  include_once(XnP3g6CaJ.'./source/do_'.$ac.'.php');  
?>
