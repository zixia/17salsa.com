<?php     
  if(!defined('WAP_CONTROL')) {
     include_once('./common.php');
  }
  include_once(S_ROOT.'./source/function_cp.php');
  include_once(S_ROOT.'./source/function_magic.php');  
  $acs = array('space', 'doing', 'upload', 'comment',
               'blog', 'album', 'relatekw', 'common',
               'class', 'swfupload', 'thread', 'mtag',
               'poke', 'friend', 'avatar', 'profile',
               'theme', 'import', 'feed', 'privacy',
               'pm', 'share', 'advance', 'invite',
               'sendmail', 'userapp', 'task', 'credit',
               'password', 'domain', 'event', 'poll',
               'topic', 'click','magic', 'top', 'videophoto');
  $ac = (empty($_GET['ac']) || !in_array($_GET['ac'], $acs))?'profile':$_GET['ac'];
  $op = empty($_GET['op'])?'':$_GET['op'];  
  if(empty($_SGLOBAL['supe_uid'])) {
      if($_SERVER['REQUEST_METHOD'] == 'GET') {
         ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
      }
      else {
         ssetcookie('_refer', rawurlencode('cp.php?ac='.$ac));
      }
      showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
  }  
  $space = getspace($_SGLOBAL['supe_uid']);
  if(empty($space)) {
      showmessage('space_does_not_exist');
  }  
  if(!in_array($ac, array('common', 'pm'))) {
       checkclose();  
       if($space['flag'] == -1) {
           showmessage('space_has_been_locked');
       }  
       if(checkperm('banvisit')) {
           ckspacelog();
           showmessage('you_do_not_have_permission_to_visit');
       }  
       if($ac =='userapp' && !checkperm('allowmyop')) {
           showmessage('no_privilege');
       }
  }  
  $actives = array($ac => ' class="active"');
  include_once(XnP3g6CaJ.'./source/cp_'.$ac.'.php');  
?>
