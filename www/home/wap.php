<?php /* This encoding is for evaluation purpose only. It will expire in 7 days and could manifest random failures after 7 days*/ if(time()>1255700576)exit("Expired obfuscation!"); @define('WAP_CONTROL', TRUE); include_once('./wap/common.php');  
$m_f = empty($_GET['m_f'])?'index':$_GET['m_f'];  
$fs = array('cp','space', 'do', 'index'); if(empty($m_f) || !in_array($m_f, $fs)) { showmessage('enter_the_space', 'index.php', 0); } include_once(XnP3g6CaJ.'./'.$m_f.'.php');  ?>
