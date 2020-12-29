<?php

class control extends base{
	var $pluginid;
	function control(& $get,& $post){
		$this->base( & $get,& $post);
		$this->load('plugin');
		$this->load('setting');
		$this->loadplugin('jswizard');
		$this->view->setlang('zh','back');
		$plugin=$_ENV['plugin']->get_plugin_by_identifier('jswizard');
		$this->pluginid=$plugin['pluginid'];
	}
	function dodefault(){
		$jswizardlist=$_ENV['jswizard']->get_all_list();
		$jsstatus=$this->plugin[jswizard][vars][jsstatus];
		$this->view->assign('pluginid',$this->pluginid);
		$this->view->assign('jsstatus',$jsstatus);
		$this->view->assign('jswizardlist',$jswizardlist);
		$this->view->display('file://plugins/jswizard/view/admin_jswizard');
	}

	function dodoc(){
		if(!isset($this->post['jssubmit'])){
			if(isset($this->get[3])){
				$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
				$js_list=unserialize($jslist[value]);
				$js_name=$jslist[variable];
				$type=1;
				$inputs=htmlentities($js_list['js_wizard']);
				$previewcon=$_ENV['jswizard']->preview_content($jslist[value]);
				$this->view->assign('previewcon',$previewcon);
			}else{
				$js_name="doc_".util::random(3);
			}
			if($js_list['js_code']==''){
				$js_list['js_code']="[title]<br />";
			}
			$this->view->assign('type',$type);
			$this->view->assign('js_name',$js_name);
			$this->view->assign('js_list',$js_list);
			$this->view->assign('inputs',$inputs);
			$this->view->assign('pluginid',$this->pluginid);
			$this->view->display('file://plugins/jswizard/view/admin_jswizard_doc');
		}else{
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_title=strpos($jswizard['js_code'],'[title]');
			$js_author=strpos($jswizard['js_code'],'[author]');
			$js_sum=strpos($jswizard['js_code'],'[summary]');
			$js_time=strpos($jswizard['js_code'],'[time]');
			$js_category=strpos($jswizard['js_code'],'[category]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($jswizard['js_doc_long']!='' && !is_numeric($jswizard['js_doc_long'])){
				$this->message('词条名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_author_long']!='' && !is_numeric($jswizard['js_author_long'])){
				$this->message('作者名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_sum_long']!='' && !is_numeric($jswizard['js_sum_long'])){
				$this->message('摘要长度不正确，请重新填写！','BACK');
			}			
					
			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-1-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if($jswizard['js_code']==''){
				$jswizard['js_code']="[title]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_content($value);
			//保存
			if($this->get[2]!='preview'){
				if((bool)$_ENV['jswizard']->check_jsname($jswizard['js_name'])){
					$this->message('调用名称已存在，请重新输入名称！','index.php?plugin-jswizard-admin_jswizard-doc');
				}
				$_ENV['jswizard']->add_jswizard($jswizard['js_name'],$value,1);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('添加成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				unset($jswizard['js_name']);
				//生成预览
				
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_doc');
			}
		}
	}
	function doeditdoc(){
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_title=strpos($jswizard['js_code'],'[title]');
			$js_author=strpos($jswizard['js_code'],'[author]');
			$js_sum=strpos($jswizard['js_code'],'[summary]');
			$js_time=strpos($jswizard['js_code'],'[time]');
			$js_category=strpos($jswizard['js_code'],'[category]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($jswizard['js_doc_long']!='' && !is_numeric($jswizard['js_doc_long'])){
				$this->message('词条名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_author_long']!='' && !is_numeric($jswizard['js_author_long'])){
				$this->message('作者名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_sum_long']!='' && !is_numeric($jswizard['js_sum_long'])){
				$this->message('摘要长度不正确，请重新填写！','BACK');
			}			

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-1-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if($jswizard['js_code']==''){
				$jswizard['js_code']="[title]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_content($value);
			//编辑
			if($this->get[2]!='preview'){
				$_ENV['jswizard']->eidt_jswizard($jswizard['js_name'],$value,1);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('修改成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_doc');
			}
	}
	function douser(){
		if(!isset($this->post['jssubmit'])){
			if(isset($this->get[3])){
				$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
				$js_list=unserialize($jslist[value]);
				$js_name=$jslist[variable];
				$type=1;
				$inputs=htmlentities($js_list['js_wizard']);
				$previewcon=$_ENV['jswizard']->preview_user_content($jslist[value]);
				$this->view->assign('previewcon',$previewcon);
			}else{
				$js_name="user_".util::random(3);
			}
			if($js_list['js_code']==''){
				$js_list['js_code']="[username]<br />";
			}
			$this->view->assign('type',$type);
			$this->view->assign('js_name',$js_name);
			$this->view->assign('js_list',$js_list);
			$this->view->assign('inputs',$inputs);
			$this->view->assign('pluginid',$this->pluginid);
			$this->view->display('file://plugins/jswizard/view/admin_jswizard_user');
		}else{
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_username=strpos($jswizard['js_code'],'[username]');
			$js_usergroup=strpos($jswizard['js_code'],'[usergroup]');
			$js_sex=strpos($jswizard['js_code'],'[sex]');
			$js_regtime=strpos($jswizard['js_code'],'[regtime]');
			$js_signature=strpos($jswizard['js_code'],'[signature]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($jswizard['js_user_long']!='' && !is_numeric($jswizard['js_user_long'])){
				$this->message('用户名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_des_long']!='' && !is_numeric($jswizard['js_des_long'])){
				$this->message('签名长度不正确，请重新填写！','BACK');
			}			
					
			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-2-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if($jswizard['js_code']==''){
				$jswizard['js_code']="[username]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_user_content($value);
			//保存
			if($this->get[2]!='preview'){
				if((bool)$_ENV['jswizard']->check_jsname($jswizard['js_name'])){
					$this->message('调用名称已存在，请重新输入名称！','index.php?plugin-jswizard-admin_jswizard-user');
				}
				$_ENV['jswizard']->add_jswizard($jswizard['js_name'],$value,2);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('添加成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				unset($jswizard['js_name']);
				//生成预览
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_user');
			}
		}
	}
	function doedituser(){
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_username=strpos($jswizard['js_code'],'[username]');
			$js_usergroup=strpos($jswizard['js_code'],'[usergroup]');
			$js_sex=strpos($jswizard['js_code'],'[sex]');
			$js_regtime=strpos($jswizard['js_code'],'[regtime]');
			$js_signature=strpos($jswizard['js_code'],'[signature]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($jswizard['js_user_long']!='' && !is_numeric($jswizard['js_user_long'])){
				$this->message('用户名称长度不正确，请重新填写！','BACK');
			}
			if($jswizard['js_des_long']!='' && !is_numeric($jswizard['js_des_long'])){
				$this->message('签名长度不正确，请重新填写！','BACK');
			}			

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-2-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if($jswizard['js_code']==''){
				$jswizard['js_code']="[username]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_user_content($value);
			//编辑
			if($this->get[2]!='preview'){
				$_ENV['jswizard']->eidt_jswizard($jswizard['js_name'],$value,2);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('修改成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_user');
			}
	}

	function docate(){
		if(!isset($this->post['jssubmit'])){
			if(isset($this->get[3])){
				$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
				$js_list=unserialize($jslist[value]);
				$js_name=$jslist[variable];
				$type=1;
				$inputs=htmlentities($js_list['js_wizard']);
				$previewcon=$_ENV['jswizard']->preview_cate_content($jslist[value]);
				$this->view->assign('previewcon',$previewcon);
			}else{
				$js_name="cate_".util::random(3);
			}
			if(''==$js_list['js_code']){
				$js_list['js_code']="[catename]<br />";
			}
			$this->view->assign('type',$type);
			$this->view->assign('js_name',$js_name);
			$this->view->assign('js_list',$js_list);
			$this->view->assign('inputs',$inputs);
			$this->view->assign('pluginid',$this->pluginid);
			$this->view->display('file://plugins/jswizard/view/admin_jswizard_cate');
		}else{
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_catename=strpos($jswizard['js_code'],'[catename]');
			$js_docnum=strpos($jswizard['js_code'],'[docnum]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($js_catename!==false && $jswizard['js_cate_long']!='' && !is_numeric($jswizard['js_cate_long'])){
				$this->message('分类名长度不正确，请重新填写！','BACK');
			}			
					
			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-3-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if(''==$jswizard['js_code']){
				$jswizard['js_code']="[catename]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_cate_content($value);
			//保存
			if($this->get[2]!='preview'){
				if((bool)$_ENV['jswizard']->check_jsname($jswizard['js_name'])){
					$this->message('调用名称已存在，请重新输入名称！','index.php?plugin-jswizard-admin_jswizard-cate');
				}
				$_ENV['jswizard']->add_jswizard($jswizard['js_name'],$value,3);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('添加成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				unset($jswizard['js_name']);
				//生成预览
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_cate');
			}
		}
	}
	function doeditcate(){
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_catename=strpos($jswizard['js_code'],'[catename]');
			$js_docnum=strpos($jswizard['js_code'],'[docnum]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($js_catename!==false && $jswizard['js_cate_long']!='' && !is_numeric($jswizard['js_cate_long'])){
				$this->message('分类名长度不正确，请重新填写！','BACK');
			}			

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-3-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if(''==$jswizard['js_code']){
				$jswizard['js_code']="[catename]<br />";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_cate_content($value);
			//编辑
			if($this->get[2]!='preview'){
				$_ENV['jswizard']->eidt_jswizard($jswizard['js_name'],$value,3);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('修改成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_cate');
			}
	}

	function dosearch(){
		if(!isset($this->post['jssubmit'])){
			if(isset($this->get[3])){
				$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
				$js_list=unserialize($jslist[value]);
				$js_name=$jslist[variable];
				$type=1;
				$inputs=htmlentities($js_list['js_wizard']);
				$previewcon=$_ENV['jswizard']->preview_search_content($jslist[value]);
				$this->view->assign('previewcon',$previewcon);
			}else{
				$js_name="search_".util::random(3);
			}
			$this->view->assign('type',$type);
			$this->view->assign('js_name',$js_name);
			$this->view->assign('js_list',$js_list);
			$this->view->assign('inputs',$inputs);
			$this->view->assign('pluginid',$this->pluginid);
			$this->view->display('file://plugins/jswizard/view/admin_jswizard_search');
		}else{
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-4-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_search_content($value);
			$jswizard['js_code']=$previewcon;
	  /*	if($jswizard['js_code']==''){
				$jswizard['js_code']=$previewcon;
				$value=serialize($jswizard);
			}
		*/	
			//保存
			if($this->get[2]!='preview'){
				if((bool)$_ENV['jswizard']->check_jsname($jswizard['js_name'])){
					$this->message('调用名称已存在，请重新输入名称！','index.php?plugin-jswizard-admin_jswizard-search');
				}
				$_ENV['jswizard']->add_jswizard($jswizard['js_name'],$value,4);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('添加成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				unset($jswizard['js_name']);
				//生成预览
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_search');
			}
		}
	}
	function doeditsearch(){
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-4-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';

			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_search_content($value);
			$jswizard['js_code']=$previewcon;
			//编辑
			if($this->get[2]!='preview'){
				$_ENV['jswizard']->eidt_jswizard($jswizard['js_name'],$value,4);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('修改成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_search');
			}
	}	
	function dotag(){
		if(!isset($this->post['jssubmit'])){
			if(isset($this->get[3])){
				$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
				$js_list=unserialize($jslist[value]);
				$js_name=$jslist[variable];
				$type=1;
				$inputs=htmlentities($js_list['js_wizard']);
				$previewcon=$_ENV['jswizard']->preview_tag_content($jslist[value]);
				$this->view->assign('previewcon',$previewcon);
			}else{
				$js_name="tag_".util::random(3);
			}
			if(''==$js_list['js_code']){
				$js_list['js_code']="[tagname]&nbsp;";
			}
			$this->view->assign('type',$type);
			$this->view->assign('js_name',$js_name);
			$this->view->assign('js_list',$js_list);
			$this->view->assign('inputs',$inputs);
			$this->view->assign('pluginid',$this->pluginid);
			$this->view->display('file://plugins/jswizard/view/admin_jswizard_tag');
		}else{
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_tagname=strpos($jswizard['js_code'],'[tagname]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($js_tagname!==false && $jswizard['js_tag_long']!='' && !is_numeric($jswizard['js_tag_long'])){
				$this->message('标签名长度不正确，请重新填写！','BACK');
			}			
					
			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-5-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if(''==$jswizard['js_code']){
				$jswizard['js_code']="[tagname]&nbsp;";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_tag_content($value);
			//保存
			if($this->get[2]!='preview'){
				if((bool)$_ENV['jswizard']->check_jsname($jswizard['js_name'])){
					$this->message('调用名称已存在，请重新输入名称！','index.php?plugin-jswizard-admin_jswizard-tag');
				}
				$_ENV['jswizard']->add_jswizard($jswizard['js_name'],$value,5);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('添加成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				unset($jswizard['js_name']);
				//生成预览
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_tag');
			}
		}
	}
	function doedittag(){
			$jswizard=$this->post;
			$jswizard['js_code']=stripslashes($jswizard['js_code']);
			$js_tagname=strpos($jswizard['js_code'],'[tagname]');
			
			if($jswizard['js_num']!='' && !is_numeric($jswizard['js_num'])){
				$this->message('显示数量不正确，请重新填写！','BACK');
			}			
			if($js_tagname!==false && $jswizard['js_tag_long']!='' && !is_numeric($jswizard['js_tag_long'])){
				$this->message('标签名长度不正确，请重新填写！','BACK');
			}			

			$jswizard['js_wizard']='<script type="text/javascript" src="'.$this->setting['site_url'].'/'.$this->setting['seo_prefix'].'plugin-jswizard-javascript-default-5-'.$jswizard['js_name'].$this->setting['seo_suffix'].'"></script>';
			if(''==$jswizard['js_code']){
				$jswizard['js_code']="[tagname]&nbsp;";
			}
			$inputs=htmlentities($jswizard['js_wizard']);
			unset($jswizard['jssubmit']);
			$value=serialize($jswizard);
			$previewcon=$_ENV['jswizard']->preview_tag_content($value);
			//编辑
			if($this->get[2]!='preview'){
				$_ENV['jswizard']->eidt_jswizard($jswizard['js_name'],$value,5);
				//生成缓存文件
				$this->cache->writecache('jswizard_'.$jswizard['js_name'],$previewcon);
				$this->message('修改成功！','index.php?admin_plugin-manage-'.$this->pluginid);
			}else{
				//预览
				$type=1;
				$js_name=$jswizard['js_name'];
				$this->view->assign('previewcon',$previewcon);
				$this->view->assign('js_list',$jswizard);
				$this->view->assign('js_name',$js_name);
				$this->view->assign('type',$type);
				$this->view->assign('inputs',$inputs);
				$this->view->assign('pluginid',$this->pluginid);
				$this->view->display('file://plugins/jswizard/view/admin_jswizard_tag');
			}
	}

	function doremove(){
		if(!empty($this->post['jswizard'])){
			$_ENV['jswizard']->remove_request($this->post['jswizard']);
			file::cleardir(HDWIKI_ROOT.'/data/cache');
			$this->message('删除成功！','index.php?plugin-jswizard-admin_jswizard');
		}else{
			$this->message('请选择您要删除的项！','BACK');
		}
	}
}
?>