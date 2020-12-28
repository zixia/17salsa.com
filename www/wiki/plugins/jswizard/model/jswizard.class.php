<?php

class jswizardmodel {
	
	var $db;
	var $base;
	
    function jswizardmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
    }
    function install(){
    	$sqls="
		CREATE TABLE ".DB_TABLEPRE."request (
		  variable varchar(32) NOT NULL DEFAULT '',
		  `value` mediumtext NOT NULL,
		  `type` tinyint(1) NOT NULL,
		  PRIMARY KEY (variable),
		  KEY `type` (`type`)
		)TYPE=MyISAM DEFAULT CHARSET=".DB_CHARSET; 
		$this->db->query($sqls);
		$this->db->query("INSERT INTO `".DB_TABLEPRE."regular` (`name`,`regular`,`type`) VALUES ('数据调用(插件)','javascript-default','2')");
		$this->db->query("UPDATE `".DB_TABLEPRE."usergroup` SET regulars =  CONCAT(regulars,'|javascript-default') where groupid!=4");

		$plugin=array(
		'name'=>'数据调用',
		'identifier'=>'jswizard',
		'description'=>'数据调用',
		'datatables'=>'request',
		'type'=>'0',
		'copyright'=>'hudong.com',
		'homepage'=>'http://kaiyuan.hudong.com',
		'version'=>'1.0',
		'suit'=>'4.0.3,4.0.4',
		'modules'=>''
		);
		$var=array(
			array('displayorder'=>"0",
			'title'=>'是否开启',
			'description'=>'',
			'variable'=>'jsstatus',
			'type'=>'radio',
			'value'=>'0',
			'extra'=>''),
			array('displayorder'=>"1",
			'title'=>'数据调用缓存时间(秒) ',
			'description'=>'数据调用缓存时间(秒)',
			'variable'=>'jscachelife',
			'type'=>'text',
			'value'=>'1800',
			'extra'=>''),
			array('displayorder'=>"2",
			'title'=>'外部调用数据来路限制',
			'description'=>'为了避免其他网站非法调用论坛数据，加重您的服务器负担，您可以设置允许调用论坛数据的来路域名列表，只有在列表中的域名和网站，才能调用您论坛的信息。每个域名一行，不支持通配符，请勿包含 http:// 或其他非域名内容，留空为不限制来路，即任何网站均可调用',
			'variable'=>'jsrefdomains',
			'type'=>'textarea',
			'value'=>'',
			'extra'=>'')
		);
		$plugin['hooks']=$hook;
		$plugin['vars']=$var;
		return $plugin;
	}
	function update($vars){
		$jscachelife=$vars['jscachelife'];
		if(!is_numeric($vars['jscachelife'])){
			return('缓存时间必须为数字！');
		}
		$jsrefdomains=$vars['jsrefdomains'];
		return true;
	}
	
	function uninstall(){
		$this->db->query("DROP TABLE IF EXISTS ".DB_TABLEPRE."request;");
		$this->db->query("DELETE from ".DB_TABLEPRE."regular where regular='javascript-default' and type=2");
		$this->db->query("update ".DB_TABLEPRE."usergroup set regulars=replace(regulars,'|javascript-default','')");
	}
	
	function get_all_list(){
		$query=$this->db->query('select * from '.DB_TABLEPRE.'request');
		while($jswizard=$this->db->fetch_array($query)){
			$jswizardlist[]=$jswizard;
		}
		return @$jswizardlist;
	}
	
	function remove_request($js){
		$jssql ='\''.implode($js,'\',\'').'\'';
		$this->db->query("delete from  ".DB_TABLEPRE."request where variable in ($jssql) ");
	}
	
	function add_jswizard($variable,$value,$type){
		$value=addslashes($value);
		$this->db->query("insert into ".DB_TABLEPRE."request (variable,value,type) values ('$variable','$value','$type')");
	}
	
	function eidt_jswizard($variable,$value,$type){
		$value=addslashes($value);
		$this->db->query("update ".DB_TABLEPRE."request set value='$value',type='$type' where variable='$variable'");
	}
	
	function check_jsname($jsname){
		return $this->db->fetch_first("select * from ".DB_TABLEPRE."request where variable='$jsname'");
	}
	
	function preview_content($value){
		$str='';
		$doc=unserialize($value);
		
		$js_title=strpos($doc['js_code'],'[title]');
		$js_author=strpos($doc['js_code'],'[author]');
		$js_sum=strpos($doc['js_code'],'[summary]');
		$js_time=strpos($doc['js_code'],'[time]');
		$js_category=strpos($doc['js_code'],'[category]');
		
		//js_cateid
		$cidarray=@explode("；",$doc['js_cateid']);		
		$counts=count($cidarray);
		for($i=0;$i<$counts;$i++){
			if($cidarray[$i]==''){
				unset($cidarray[$i]);
			}else if(!is_numeric($cidarray[$i])){
				unset($cidarray[$i]);
			}
		}
		$cid=implode(",",$cidarray);
		$sql="select c.name,d.title,d.cid,d.author,d.did,d.time,d.summary from ".DB_TABLEPRE."doc as d left join ".DB_TABLEPRE."category as c on d.cid=c.cid left join ".DB_TABLEPRE."focus as f on d.did=f.did where 1=1";
		if($cid!=''){
			$sql=$sql." and d.cid in (".$cid.")";
		}
		if(0==$doc['js_type']){
			$sql=$sql." and d.did=f.did";
		}
		
		//js_type
		$order=array();;
		if(1==$doc['js_type']){
			$order[]="d.views desc";
		}elseif(2==$doc['js_type']){
			$order[]="d.time desc";
		}
		//js_order
		if(0==$doc['js_order']){
			$order[]="d.lastedit desc";
		}else if(1==$doc['js_order']){
			$order[]="f.time desc";
		}else{
			$order[]="d.views desc";
		}
		$sqlorder=implode(",",$order);
		$sql=$sql." order by ".$sqlorder;
		
		//js_num
		$limit=10;
		if($doc['js_num'] && is_numeric($doc['js_num'])){
			$limit=$doc['js_num'];
		}
		$sql=$sql." limit ".$limit;
		
		$weburl=explode("plugin-jswizard",$doc['js_wizard']);
		$url=explode('src="',$weburl[0]);
		$query=$this->db->query($sql);
		while($preview=$this->db->fetch_array($query)){
			//title
			if($js_title!==false && is_numeric($doc['js_doc_long'])){
				$preview['title']=(strlen($preview['title'])>$doc['js_doc_long'])?string::substring($preview['title'],0,$doc['js_doc_long'])."...":$preview['title'];
			}
			$title='';
			$title.='<a href="'.$url[1].'doc-view-'.$preview['did'].$this->base->setting['seo_suffix'].'"';
			if(0==$doc['js_target']){
				$title.='target="_self">';
			}else{
				$title.='target="_blank">';
			}
			$title.=$preview['title'].'</a>';	
			//category
			if($js_category!==false){
				$category=$preview['name'];
			}
					
			//author
			if($js_author!==false){
				if(is_numeric($doc['js_author_long'])){
					$author=(strlen($preview['author'])>$doc['js_author_long'])?string::substring($preview['author'],0,$doc['js_author_long'])."...":$preview['author'];
				}else{
					$author=$preview['author'];
				}
			}
			
			//time
			if($js_time!==false){
				$time = date("Y-m-d",$preview['time']);                         
			}
			//summary
			if($js_sum!==false && is_numeric($doc['js_sum_long'])){
				$summary=(strlen($preview['summary'])>$doc['js_sum_long'])?string::substring($preview['summary'],0,$doc['js_sum_long'])."...":$preview['summary'];
			}else{
				$summary=(strlen($preview['summary'])>20)?string::substring($preview['summary'],0,20)."...":$preview['summary'];
			}
			$str=str_replace("[title]",$title,$doc['js_code']);
			$str=str_replace("[author]",$author,$str);
			$str=str_replace("[summary]",$summary,$str);
			$str=str_replace("[time]",$time,$str);
			$str=str_replace("[category]",$category,$str);
			$string.=$str;
		}
		return $string;
	}
	
	function preview_user_content($value){
		$str='';
		$user=unserialize($value);
		
		$js_username=strpos($user['js_code'],'[username]');
		$js_usergroup=strpos($user['js_code'],'[usergroup]');
		$js_sex=strpos($user['js_code'],'[sex]');
		$js_regtime=strpos($user['js_code'],'[regtime]');
		$js_signature=strpos($user['js_code'],'[signature]');

		$sql="select u.username,u.regtime,u.uid,u.gender,u.signature,g.grouptitle from ".DB_TABLEPRE."user as u left join ".DB_TABLEPRE."usergroup as g on u.groupid=g.groupid where 1=1";

		//js_order
		if(0==$user['js_order']){
			$order=" order by u.regtime desc";
		}else if(1==$user['js_order']){
			$order=" order by u.credits desc";
		}

		$sql=$sql.$order;
		
		//js_num
		$limit=10;
		if($user['js_num'] && is_numeric($user['js_num'])){
			$limit=$user['js_num'];
		}
		$sql=$sql." limit ".$limit;
		
		$weburl=explode("plugin-jswizard",$user['js_wizard']);
		$url=explode('src="',$weburl[0]);
		$query=$this->db->query($sql);
		while($preview=$this->db->fetch_array($query)){
			//js_username
			if($js_username!==false && is_numeric($user['js_user_long'])){
				$preview['username']=(strlen($preview['username'])>$user['js_user_long'])?string::substring($preview['username'],0,$user['js_user_long'])."...":$preview['username'];
			}
			$name='';
			$name.='<a href="'.$url[1].'user-space-'.$preview['uid'].$this->base->setting['seo_suffix'].'"';
			if(0==$user['js_target']){
				$name.='target="_self">';
			}else{
				$name.='target="_blank">';
			}
			$name.=$preview['username'].'</a>';	
			//sex
			if($js_sex!==false){
				if(0==$preview['gender']){
					$sex="男";
				}else{
					$sex="女";
				}
			}
			//usergroup
			if($js_usergroup!==false){
				$usergroup=$preview['grouptitle'];
			}
			//js_regtime
			if($js_regtime!==false){
				$regtime=date("Y-m-d",$preview['regtime']);                         
			}
			//js_signature
			if($js_signature!==false && is_numeric($user['js_des_long'])){
				$signature=(strlen($preview['signature'])>$user['js_des_long'])?string::substring($preview['signature'],0,$user['js_des_long'])."...":$preview['signature'];
			}else{
				$signature=$preview['signature'];
			}
			$str=str_replace("[username]",$name,$user['js_code']);
			$str=str_replace("[usergroup]",$usergroup,$str);
			$str=str_replace("[signature]",$signature,$str);
			$str=str_replace("[sex]",$sex,$str);
			$str=str_replace("[regtime]",$regtime,$str);
			$string.=$str;
		}
		return $string;
	}
	function preview_cate_content($value){
		$str='';
		$cate=unserialize($value);
		
		$js_catename=strpos($cate['js_code'],'[catename]');
		$js_docnum=strpos($cate['js_code'],'[docnum]');

		$sql="select * from ".DB_TABLEPRE."category where 1=1";
		//js_cateid
		$cidarray=@explode("；",$cate['js_cid']);		
		$counts=count($cidarray);
		for($i=0;$i<$counts;$i++){
			if($cidarray[$i]==''){
				unset($cidarray[$i]);
			}else if(!is_numeric($cidarray[$i])){
				unset($cidarray[$i]);
			}
		}
		$cid=implode(",",$cidarray);
		if($cid!=''){
			$sql=$sql." and pid in (".$cid.")";
		}else{
			$sql=$sql." and pid in (0)";
		}
		//js_order
		$order=" order by cid desc";
		$sql=$sql.$order;
		
		//js_num
		$limit=10;
		if($cate['js_num'] && is_numeric($cate['js_num'])){
			$limit=$cate['js_num'];
		}
		$sql=$sql." limit ".$limit;
		
		
		$weburl=explode("plugin-jswizard",$cate['js_wizard']);
		$url=explode('src="',$weburl[0]);
		$query=$this->db->query($sql);
		while($preview=$this->db->fetch_array($query)){
			//js_catename
			if($js_catename!==false && is_numeric($cate['js_cate_long'])){
				$preview['name']=(strlen($preview['name'])>$cate['js_cate_long'])?string::substring($preview['name'],0,$cate['js_cate_long'])."...":$preview['name'];
			}
			$name='';
			$name.='<a href="'.$url[1].'category-view-'.$preview['cid'].$this->base->setting['seo_suffix'].'"';
			if(0==$cate['js_target']){
				$name.='target="_self">';
			}else{
				$name.='target="_blank">';
			}
			$name.=$preview['name'].'</a>';	
			$str=str_replace("[catename]",$name,$cate['js_code']);
			$str=str_replace("[docnum]",$preview['docs'],$str);
			$string.=$str;
		}
		return $string;
	}
	function preview_tag_content($value){
		$str='';
		$tag=unserialize($value);
		$tagarray=unserialize($this->base->setting['hottag']);
		$counts=count($tagarray);
		if($tag['js_num'] && is_numeric($tag['js_num']) && $tag['js_num']<$counts){
			$counts=$tag['js_num'];
		}
		$weburl=explode("plugin-jswizard",$tag['js_wizard']);
		$url=explode('src="',$weburl[0]);
		
		for($i=0;$i<$counts;$i++){
			$name='';
			$name.='<a href="'.$url[1].'search-tag-'.urlencode($tagarray[$i][tagname]).$this->base->setting['seo_suffix'].'"';
			if($tag['js_target']==0){
				$name.='target="_self">';
			}else{
				$name.='target="_blank">';
			}
			if($tag['js_tag_long']!='' && is_numeric($tag['js_tag_long'])){
				$tagarray[$i][tagname]=(strlen($tagarray[$i][tagname])>$tag['js_tag_long'])?string::substring($tagarray[$i][tagname],0,$tag['js_tag_long'])."...":$tagarray[$i][tagname];
			}
			$name.=$tagarray[$i][tagname].'</a>';	
			$str=str_replace("[tagname]",$name,$tag['js_code']);
			$string.=$str;			
		}
		return $string;		
	}
	function preview_search_content($value){
		$search=unserialize($value);
		$weburl=explode("plugin-jswizard",$search['js_wizard']);
		$url=explode('src="',$weburl[0]);
		if($search[js_code]==''){
			$string='<form';
			if(1==$search['js_target']){
				$string.=' target="_blank"';
			}
			$string.=' name="searchform" method="post" action="'.$url[1].'search-default'.$this->base->setting['seo_suffix'].'"';
			$string.='><input name="searchtext" type="text" maxlength="80" size="20"  value=""/><input name="default" type="submit" value="进入词条" tabindex="1" /><input name="full" type="submit" value="搜索" tabindex="2" onclick="document.searchform.action=\''.$url[1].'search-fulltext'.$this->base->setting['seo_suffix'].'\';"/><a href="'.$url[1].'search-fulltext'.$this->base->setting['seo_suffix'].'" ';
			if(1==$search['js_target']){
				$string.='target="_blank"';
			}
			$string.='>高级搜索</a></form>';
			if(0==$search['js_target']){
				$string=str_replace('target="_blank"','',$string);
			}
		}else{
			$string=$search[js_code];
			if(1==$search['js_target']){
				$string=str_replace('target="_blank"','',$string);
				$a=explode('<form',$string);
				$string=$a[0].'<form'.' target="_blank"'.$a[1];
				$b=explode('href="'.$url[1].'search-fulltext'.$this->base->setting['seo_suffix'].'" ',$string);
				$string=$b[0].'href="'.$url[1].'search-fulltext'.$this->base->setting['seo_suffix'].'" '.' target="_blank"'.$b[1];
			}else{
				$string=str_replace('target="_blank"','',$string);
			}
		}
		return $string;
	}
}
?>