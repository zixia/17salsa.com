<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class stylemodel {

	var $db;
	var $base;

	function stylemodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	function get_all_list($start=0,$limit=10){
		$stylelist=array();
		$query=$this->db->query('SELECT path FROM '.DB_TABLEPRE.'style where path!="'.$this->base->setting['style_name'].'" ORDER BY  id ASC limit '.$start.','.$limit);
		while($allstyle=$this->db->fetch_array($query)){
			if($allstyle['path']!=$this->base->setting['style_name']){
				$style=$this->read_xml($allstyle['path']);
				$style['tag']=explode(' ',$style['tag']);
				$style['charset']=explode(' ',$style['charset']);
				$stylelist[]=$style;
			}
		}
		return $stylelist;
	}
	
	function get_all_list_num(){
		$stylelist=array();
		$query=$this->db->query('SELECT path FROM '.DB_TABLEPRE.'style where path!="'.$this->base->setting['style_name'].'" ORDER BY  id ASC ');
		while($allstyle=$this->db->fetch_array($query)){
			if($allstyle['path']!=$this->base->setting['style_name']){
				$stylelist[]=$style;
			}
		}
		return $stylelist;
	}	
	function get_path_list(){
		$pathlist=array();
		$query=$this->db->query('SELECT path FROM '.DB_TABLEPRE.'style  ORDER BY  id ASC ');
		while($style=$this->db->fetch_array($query)){
			$pathlist[]=$style['path'];
		}
		return $pathlist;
	}
	
	function add_style($style){
		$this->db->query("INSERT INTO ".DB_TABLEPRE."style (name,path,available,css,copyright) VALUES ('$style[name]','$style[path]','1','','$style[copyright]')");
	}
	
	function read_xml($filenames){
		$xmlarray=array();
		$values=array();
		$tags=array();
		$filedir=HDWIKI_ROOT.'/style/'.$filenames.'/';
		if(file_exists($filedir."desc.xml")){
			$data = implode("",file($filedir."desc.xml"));
			$parser = xml_parser_create();
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($parser, $data, $values, $tags);
			xml_parser_free($parser);
			$xmlcounts=count($values);
			$xmlarray['path']=$filenames;
			for($x=0;$x<$xmlcounts;$x++){
				if($values[$x]['level']==2){
					$values[$x]['value']=string::hiconv($values[$x]['value']);
					$xmlarray[$values[$x]['tag']]=$values[$x]['value'];
				}
			}
			$filedir=HDWIKI_ROOT."/style/".$filenames.'/';
			file::forcemkdir($filedir);
			if(is_file($filedir."screenshot.jpg")){
				$xmlarray['img']=1;
			}
		}
		return $xmlarray;
	}
	
	function write_xml($style){
		$xml="<?xml version=\"1.0\" encoding=\"".WIKI_CHARSET."\"?>\n".
			"<theme name=\"default\" version=\"1.0.1\" active=\"true\">\n".
			"<author><![CDATA[".$style['author']."]]></author>\n".
			"<authorurl><![CDATA[".$style['authorurl']."]]></authorurl>\n".
			"<name><![CDATA[".$style['name']."]]></name>\n".
			"<tag><![CDATA[".$style['tag']."]]></tag>\n".
			"<desc><![CDATA[".$style['desc']."]]></desc>\n".
			"<sitename><![CDATA[".$style['sitename']."]]></sitename>\n".
			"<weburl><![CDATA[".$style['weburl']."]]></weburl>\n".
			"<version><![CDATA[".$style['version']."]]></version>\n".
			"<hdversion><![CDATA[".$style['hdversion']."]]></hdversion>\n".
			"<copyright><![CDATA[".$style['copyright']."]]></copyright>\n".
			"<charset><![CDATA[".$style['charset']."]]></charset>\n".
			"</theme>";
		$filedir=HDWIKI_ROOT.'/style/'.$style['path'].'/';
		file::forcemkdir($filedir);
		$bytes=file::writetofile($filedir.'desc.xml',$xml);
		return ($bytes>0);
	}
	
	function add_check_style($path){
		$style=$this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."style WHERE path = '$path'");
		return $style;
	}
	
	function update_style($style){
		$this->db->query("UPDATE ".DB_TABLEPRE."style set name='$style[name]',copyright='$style[copyright]',available='1' where path='$style[path]'");
	}
	
	function update_stylecss($style,$path){
		$this->db->query("UPDATE ".DB_TABLEPRE."style set css='$style' where path='$path'");
	}
	
	function default_style($path){
		$this->db->query("UPDATE ".DB_TABLEPRE."setting SET value = '$path' WHERE variable = 'style_name' or variable = 'tpl_name'");
	}
	
	function remove_style($path){
		$this->db->query("DELETE FROM ".DB_TABLEPRE."style WHERE path='$path'");
	}
	
	function choose_style_name($path){
		$style=$this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."style WHERE path='$path'");
		$xmlstyle=$this->read_xml($style['path']);
		$xmlstyle[tag]=explode(" ",$xmlstyle['tag']);
		$xmlstyle[charset]=explode(" ",$xmlstyle['charset']);
		return $xmlstyle;
	}
	
	function get_style_list($type){
		$toaddlist=array();
		$filedir=HDWIKI_ROOT.'/style/';
		file::forcemkdir($filedir);
		$handle=opendir($filedir);
		while($filename=readdir($handle)){
			if (is_dir($filedir.$filename) && '.'!=$filename && '..'!=$filename){
				if($type==1){
					if(file_exists($filedir.$filename."/desc.xml")){
						$toaddlist[]=$filename;
					}
				}else{
					$toaddlist[]=$filename;
				}
			}
		}
		closedir($handle);
		return $toaddlist;
	}
	
	function upload_img($uploadimg,$filename){
		$counts=count($uploadimg);
		if($counts!=0){
			for($i=0;$i<$counts;$i++){
				$imgname=$uploadimg[$i]['name'];
				$extname=file::extname($imgname);
				$destfile=HDWIKI_ROOT.'/style/'.$filename.'/'.$uploadimg[$i]['rename'].".".$extname;
				$result = file::uploadfile($uploadimg[$i],$destfile);
				if($result['result'] && $uploadimg[$i]['rename']=='screenshot'){
					util::image_compress($destfile,NULL,158,118);
				}
				$success++;
			}
		}
		return $success;
	}
	
	function get_style_name($ziplist){
		if(!is_array($ziplist)){
			return false;
		}
		foreach($ziplist as $list){
			if(false!==strpos($list['filename'],'desc.xml')){
				$style_name=$list['filename'];
				break;
			}
		}
		$style_name=substr($style_name,strpos($style_name,'style/')+6,-9);
		return $style_name;
	}
	
	function style_charset($path){
		$filedir=HDWIKI_ROOT.'/view/'.$path.'/';
		if(is_dir($filedir)){
			$list=array();
			file::forcemkdir($filedir);
			$handle=opendir($filedir);
			while($filename=readdir($handle)){
				if (!is_dir($filedir.$filename) && '.'!=$filename && '..'!=$filename){
					$list[]=$filename;
				}
			}
			if($list==null)$styletag=1;
		}else{
			$styletag=1;
		}
		if($styletag==1){
			if(WIKI_CHARSET=='UTF-8'){
				$charset=WIKI_CHARSET.' GBK';
			}else{
				$charset=WIKI_CHARSET.' UTF-8';
			}	
		}else{
			$charset=WIKI_CHARSET;
		}
		return $charset;
	}
	
	function write_css($style){
			$data="#html{background:".$style['bg_color']." url(".$style['bg_imgname'].") repeat-x left top;}\n".
				"#html body{width:950px;}\n".
				"#html .bor_b-ccc,#html .col-h2{border-bottom:1px ".$style['title_framcolor']." solid;}\n".
				"#html .bor-ccc,#html .columns,#html .bor-c_dl dl{border:1px ".$style['nav_framcolor']." solid;}\n".
				"#html .inp_txt{border:1px ".$style['input_bgcolor']." solid;color:".$style['input_color'].";}\n".
				"#html a{color:".$style['link_color'].";}\n".
				"#html a:hover{color:".$style['link_hovercolor'].";}\n".
				"#html .link_orange a{color:".$style['link_difcolor']."; text-decoration:none;}\n".
				"#html .link_orange a:hover{color:".$style['link_difcolor'].";text-decoration:underline;}\n".
				"#html .col-h2{height:21px;line-height:21px;background:".$style['titlebg_color']." url(".$style['titbg_imgname'].") repeat-x left top;}";
		$filedir=HDWIKI_ROOT.'/style/'.$style['path'].'/';
		file::forcemkdir($filedir);
		$bytes=file::writetofile($filedir.'mix_color.css',$data);
		return ($bytes>0);
	}
}
?>