<?php

!defined('IN_HDWIKI') && exit('Access Denied');
 
class control extends base{

	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('user');
		$this->load('plugin');
		$this->loadplugin('ucenter');
	}
	function dodefault() {
		$this->doexchange();
	}
	function doexchange() {
		if(!isset($this->post['exchangesubmit'])){
			 $this->view->assign('user',$this->user);
			 $outextcredits=unserialize($this->setting["outextcredits"]);
			 $option=array();
			 foreach($outextcredits as $key=>$change){
			 	$option[$change[creditsrc]][$key]=$change;
			 }
		 	 $this->view->assign('outextcredits',$outextcredits);
		 	 $this->view->assign('option',$option);
	         $this->view->display('file://plugins/ucenter/view/exchange');
		}else{
			$netamount = $tocredits = 0;
			$outextcredits=unserialize($this->setting["outextcredits"]);
			$fromcredit = $this->post['fromcredit'];
			$tocredit = $this->post['tocredit_'.$fromcredit];
			$outexange = $outextcredits[$tocredit];
			if(!$outexange || !$outexange['ratio']) {
				$this->message('credits_exchange_invalid!','BACK',0);
			}
			$amount = intval($this->post['amount']);
			$credit1=$credit2=0;
			if($outexange[creditsrc]==0){
				$credits='credit2';
				$credit2=-$amount;
			}else{
				$credits='credit1';
				$credit1=-$amount;
			}
			
			
			if($amount <= 0 || $amount >$this->user[$credits]) {
				$this->message('积分提交无效！','BACK',0);
			}
			
			require_once(HDWIKI_ROOT."/plugins/ucenter/ucconfig.inc.php");
			require_once(HDWIKI_ROOT."/plugins/ucenter/uc_client/client.php");
			
			$ucresult = uc_user_login($this->user['username'], $this->post['password']);
			list($tmp['uid']) = $ucresult;
			if($tmp['uid'] <= 0) {
				$this->message('输入的密码错误！','BACK',0);
			}
			$netamount = floor($amount * 1/$outexange['ratio']);
			
			$ucresult = uc_credit_exchange_request($this->user['uid'], $outexange['creditsrc'], $outexange['creditdesc'], $outexange['appiddesc'], $netamount);
			if(!$ucresult) {
				$this->message('积分转换失败！','BACK',0);
			}
			
			$_ENV['user']->add_credit($this->user['uid'],'syn_credit',$credit2,$credit1);
			$this->message('积分兑换成功!',$this->setting['seo_prefix']."user-profile".$this->setting['seo_suffix'],0);
		}
	}
	function doupdateuser(){
		#包含uc相应文件。
		$url=HDWIKI_ROOT."/plugins/ucenter/ucconfig.inc.php";
		if(!file_exists($url)){
 			$this->message('您还没有设置过UCenter!','BACK');
 		}else{
			include($url);
		}
		require_once(HDWIKI_ROOT."/plugins/ucenter/uc_client/client.php");
		require_once(HDWIKI_ROOT."/plugins/ucenter/uc_client/lib/db.class.php");
		#
		$ucuserlist='';
		$usercount=$this->db->result_first('select count(*) from '.DB_TABLEPRE.'user');
		for($i=0;$i<$usercount;$i=$i+1){
			$sql="select * from ".DB_TABLEPRE."user order by uid limit $i,1";
			$temUser=$this->db->fetch_first($sql);
			if($data = uc_get_user($temUser['username'])) {
				if($data[0]!=$temUser['uid']){
					if($usernum=$this->db->result_first('select count(*) from '.DB_TABLEPRE.'user where uid=$data[0]')){
						$maxuid=$this->db->result_first('select max(uid) from '.DB_TABLEPRE.'user');
						$maxuid+=1;
						$this->db->query("update ".DB_TABLEPRE."user set uid=$maxuid where uid=$data[0]");
						$_ENV["ucenter"]->update_field($data[0],$maxuid);
					}
					$this->db->query("update ".DB_TABLEPRE."user set uid=$data[0] where uid=$temUser[uid]");
					$_ENV["ucenter"]->update_field($temUser['uid'],$data[0]);
				}
			}
		}
		$plugin=$_ENV['plugin']->get_plugin_by_identifier('ucenter');
		$pluginid=$plugin['pluginid'];
		$this->message('更新用户成功!','index.php?admin_plugin-setvar-'.$pluginid);
		exit();
	}
}
?>