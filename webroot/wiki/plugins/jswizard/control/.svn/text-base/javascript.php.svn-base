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
		if($this->plugin[jswizard][vars][jsrefdomains]==''){
			$jsrefdomains=preg_replace("/([^\:]+).*/", "\\1", (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL));
		}else{
			$jsrefdomains=$this->plugin[jswizard][vars][jsrefdomains];
		}
		$REFERER	= 	parse_url($_SERVER['HTTP_REFERER']);
		if($jsrefdomains && (empty($REFERER) || !in_array($REFERER['host'], explode("\r\n", trim($jsrefdomains))))) {
			exit("document.write(\"被禁止调用！\");");
		}
		
		$jsstatus=$this->plugin[jswizard][vars][jsstatus];
		if(0==$jsstatus){
			echo 'document.write("没有开启数据调用！");';
			exit;
		}else{
			$jslist=$_ENV['jswizard']->check_jsname($this->get[3]);
			if(!$jslist){
				echo 'document.write("没有相关信息！");';
				exit;
			}
			$js_list=unserialize($jslist[value]);
			if(''==$js_list[js_cachetime]){
				$cachetime=$this->plugin[jswizard][vars][jscachelife];
			}else{
				$cachetime=$js_list[js_cachetime];
			}
			
			$isvalid=$this->cache->isvalid('jswizard_'.$this->get[3],$cachetime);
			if(!$isvalid){
				//删除过期文件，并生成新的缓存文件
				$this->cache->removecache('jswizard_'.$this->get[3]);
				if(1==$this->get[2]){
					$previewcon=$_ENV['jswizard']->preview_content($jslist[value]);
				}elseif(2==$this->get[2]){
					$previewcon=$_ENV['jswizard']->preview_user_content($jslist[value]);
				}elseif(3==$this->get[2]){
					$previewcon=$_ENV['jswizard']->preview_cate_content($jslist[value]);
				}elseif(5==$this->get[2]){
					$previewcon=$_ENV['jswizard']->preview_tag_content($jslist[value]);
				}elseif(4==$this->get[2]){
					$previewcon=$_ENV['jswizard']->preview_search_content($jslist[value]);
				}
				$this->cache->writecache('jswizard_'.$this->get[3],$previewcon);
			}
			$preview=$this->cache->getcache('jswizard_'.$this->get[3],$cachetime);
			$preview=stripslashes($preview);

			$preview=trim($preview);
			if(4==$this->get[2]){
				$fp=fopen('1.txt',wb);
				fputs($fp,$preview);
				fclose($fp);
			}
			$doclist='document.write("'.addcslashes($preview,'"').'");';
			echo $doclist;
		}
	}
}
?>