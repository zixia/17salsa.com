<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: news.func.php 12536 2009-07-06 06:22:57Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function freshcookie($itemid) {
	global $_SC, $_SGLOBAL;

	$isupdate = 1;
	$old = empty($_COOKIE[$_SC['cookiepre'].'supe_refresh_items'])?0:trim($_COOKIE[$_SC['cookiepre'].'supe_refresh_items']);
	$itemidarr = explode('_', $old);
	if(in_array($itemid, $itemidarr)) {
		$isupdate = 0;
	} else {
		$itemidarr[] = trim($itemid);
		ssetcookie('supe_refresh_items', implode('_', $itemidarr));
	}
	if(empty($_COOKIE)) $isupdate = 0;

	return $isupdate;
}

function updateviewnum($itemid) {
	global $_SGLOBAL;

	$logfile = S_ROOT.'./log/viewcount.log';
	if(@$fp = fopen($logfile, 'a+')) {
		fwrite($fp, $itemid."\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+1 WHERE itemid=\''.$itemid.'\'');
	}
}

function sjammer($str) {
	global $_SGLOBAL, $_SCONFIG;

	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<span style="display:none">'.$_SCONFIG['sitename'].$randomstr.'</span>'.$str :
		$str.'<span style="display:none">'.$randomstr.$_SGLOBAL['supe_uid'].'</span>';
}

function formatcomment($comment, $repeatids = array(), $style=0) {
	global $_SCONFIG, $lang;

	$searcharr = $replacearr = array();
	$comment['message'] = snl2br($comment['message']);

	if(empty($comment['author'])) $comment['author'] = 'Guest';
	$comment['hideauthor'] = (!empty($comment['hideauthor']) && !empty($_SCONFIG['commanonymous'])) ? 1 : 0;
	$comment['hideip'] = (!empty($comment['hideip']) && !empty($_SCONFIG['commhideip'])) ? 1 : 0;
	$comment['hidelocation'] = (!empty($comment['hidelocation']) && !empty($_SCONFIG['commhidelocation'])) ? 1 : 0;
	$comment['iplocation'] = str_replace(array('-', ' '), '', convertip($comment['ip']));
	$comment['ip'] = preg_replace("/^(\d{1,3})\.(\d{1,3})\.\d{1,3}\.\d{1,3}$/", "\$1.\$2.*.*", $comment['ip']);
	
	$_SCONFIG['commfloornum'] = intval($_SCONFIG['commfloornum']);
	$comment['floornum'] = intval($comment['floornum']);
	if(!$style) {

		if(!empty($_SCONFIG['commfloornum'])) {
			//削楼功能
			if($_SCONFIG['commfloornum'] < $comment['floornum']) {
				$cutfloor = $comment['floornum'] - $_SCONFIG['commfloornum'];
				$searchstr = "/\<div id=\"cid_{$comment['cid']}_$cutfloor\".*?\<div id=\"cid_{$comment['cid']}_".($cutfloor+1)."_title\"/is";
				$replacestr = "<div id=\"cid_{$comment['cid']}_".($cutfloor+1)."_title\"";
				$comment['message'] = preg_replace($searchstr, $replacestr, $comment['message']);
			}
			
		} else {
			//高层电梯
			if($comment['floornum'] > 49) {
				$elevatordetail = <<<EOF
						<div id="cid_{$comment['cid']}_elevator" class="floor_op">
							<div class="old_title "><span class="author">$lang[comment_elevator]</span><span class="color_red">$lang[comment_floor_hide]</span></div>
							<p class="detail "><span><a class="color_red" href="javascript:;" onclick="elevator($comment[cid], 2);" title="$lang[comment_floor_up_title]">[{$lang['comment_floor_up']}]</a>
							<a class="color_red" href="javascript:;" onclick="elevator($comment[cid], 1);" title="$lang[comment_floor_down_title]">[{$lang['comment_floor_down']}]</a></span>
							$lang[comment_floor_total]{$comment['floornum']}$lang[comment_floor_total_2]</p>
							<input type="hidden" id="cid_{$comment['cid']}_elevatornum" value="40">
							<input type="hidden" id="cid_{$comment['cid']}_floornum" value="$comment[floornum]">
						</div>
EOF;
				$searcharr[] = '<div id="cid_'.$comment['cid'].'_'.($comment['floornum']-8).'_title"';
				$replacearr[] = $elevatordetail.'<div id="cid_'.$comment['cid'].'_'.($comment['floornum']-8).'_title"';
				if(!in_array($comment['firstcid'], $repeatids)) {
					for ($i=41; $i < $comment['floornum']-8; $i++) {
						$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}\" class=\"old\"";
						$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}_title\" class=\"old_title\"";
						$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}_detail\" class=\"detail\"";
						$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}\" class=\"hideold\"";
						$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}_title\" class=\"hideelement\"";
						$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}_detail\" class=\"hideelement\"";
					}
				}
			}
			
		}
	
		//隐藏重复盖楼
		if(!empty($_SCONFIG['commhidefloor']) && in_array($comment['firstcid'], $repeatids)) {
			$tipdetail = "<p id=\"cid_{$comment['cid']}_tip_detail\" class=\"hidetip\">$lang[comment_floor_repeat] <a class=\"color_red\" href=\"javascript:;\" onclick=\"operatefloor({$comment['cid']});\">[{$lang['comment_floor_view_repeat']}]</a><p></div>";
			$searcharr[] = 'class="old"';
			$searcharr[] = 'class="old_title"';
			$searcharr[] = 'class="detail"';
			$searcharr[] = 'class="floor_op"';
			$searcharr[] = '_1" class="hideold"';
			$searcharr[] = '_tip" class="hideold"';
			$searcharr[] = '_1_title" class="hideelement"';
			$searcharr[] = '_1_detail" class="hideelement"';
			$searcharr[] = '<div class="new"';
			
			$replacearr[] = 'class="hideold"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = '_1" class="old"';
			$replacearr[] = '_tip" class="old"';
			$replacearr[] = '_1_title" class="old_title"';
			$replacearr[] = '_1_detail" class="detail"';
			$replacearr[] = $tipdetail.'<div class="new"';
			
			$comment['message'] = "<div id=\"cid_{$comment['cid']}_tip\" class=\"old\">".$comment['message'];
	
		}
	
		$comment['message'] = str_replace($searcharr, $replacearr, $comment['message']);

	} else {
		
		preg_match_all ("/\<div class=\"new\">(.+)?\<\/div\>/is", $comment['message'], $currentmessage, PREG_SET_ORDER);
		if(!empty($currentmessage)) $comment['message'] = $currentmessage[0][0];
		$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote.+?\<\/blockquote\>\<\/div\>/is", '',$comment['message']);
		
	}
	
	return $comment;
	
}


function getcheckboxstr($var, $optionarray, $value='', $other='') {
	$html = '<table><tr>';
	$i=0;
	foreach ($optionarray as $okey => $ovalue) {
		$html .= '<td style="border:0"><input name="'.$var.'[]" type="checkbox" value="'.$okey.'"'.$other.' />'.$ovalue.'</td>';
		if($i%5==4) $html .= '</tr><tr>';
		$i++;
	}
	$html .= '</tr></table>';

	$valuearr = array();
	if(!empty($value)) {
		if(is_array($value)) {
			$valuearr = $value;
		} else {
			$valuearr = explode(',', $value);
		}
	}

	if(!empty($valuearr)) {
		foreach ($valuearr as $ovalue) {
			$html = str_replace('value="'.$ovalue.'"', 'value="'.$ovalue.'" checked', $html);
		}
	}

	return $html;
}

function printjs() {
print <<<EOF
	<script type="text/javascript">
		function settitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objsubject.style.color = objfontcolor.value;
			objfontcolor.style.backgroundColor = objfontcolor.value;
			objsubject.style.fontSize = objfontsize.value;
			objsubject.style.width = 500;
			if(objem.checked == true) {
				objsubject.style.fontStyle = "italic";
			} else {
				objsubject.style.fontStyle = "";
			}
			if(objstrong.checked == true) {
				objsubject.style.fontWeight = "bold";
			} else {
				objsubject.style.fontWeight = "";
			}
			if(objunderline.checked == true) {
				objsubject.style.textDecoration = "underline";
			} else {
				objsubject.style.textDecoration = "none";
			}
		}
		function loadtitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objfontcolor.style.backgroundColor = objsubject.style.color;
			objfontcolor.value = objsubject.style.color;
			var colorstr = objsubject.style.color;
			if(isFirefox=navigator.userAgent.indexOf("Firefox")>0 && colorstr != ""){
				colorstr = rgbToHex(colorstr);
			}
			if(colorstr != "") {
				objfontcolor.options.selectedIndex = getbyid(colorstr).index;
				objfontcolor.options.selected = true;
			}
			objfontsize.value = objsubject.style.fontSize;
			if(objsubject.style.fontWeight == "bold") {
				objstrong.checked = true;
			} else {
				objstrong.checked = false;
			}
			if(objsubject.style.fontStyle == "italic") {
				objem.checked = true;
			} else {
				objem.checked = false;
			}
			if(objsubject.style.textDecoration == "underline") {
				objunderline.checked = true;
			} else {
				objunderline.checked = false;
			}		
		}
		function makeselectcolor(selectname){
			subcat = new Array('00','33','66','99','CC','FF');
			var length = subcat.length;
			var RED = subcat;
			var GREEN = subcat;
			var BLUE = subcat;
			var b,r,g;
			var objsubject=document.getElementById(selectname);
			for(r=0;r < length;r++){
				for(g=0;g < length;g++){
					for(b=0;b < length;b++){
						var oOption = document.createElement("option");
						oOption.style.backgroundColor="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.style.color="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.value="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.text="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.id="#"+RED[r]+GREEN[g]+BLUE[b];
						objsubject.appendChild(oOption);
						}
					}
				}
		}
		function rgbToHex(color) {
			color=color.replace("rgb(","")
			color=color.replace(")","")
			color=color.split(",")
			
			r=parseInt(color[0]);
			g=parseInt(color[1]);
			b=parseInt(color[2]);
			
			r = r.toString(16);
			if (r.length == 1) {
				r = '0' + r;
			}
			g = g.toString(16);
			if (g.length == 1) {
				g = '0' + g;
			}
			b = b.toString(16);
			if (b.length == 1) {
				b = '0' + b;
			}
			return ("#" + r + g + b).toUpperCase();
		}
			
	</script>
EOF;

}
function addurlhttp($m) {
	if (preg_grep("/^http\:/", array($m[2])) || preg_grep("/^\//", array($m[2]))) {
		return 'src="'.$m[2].'.'.$m[3];
	} else {
		return 'src="'.S_URL_ALL.'/'.$m[2].'.'.$m[3];
	}
		
}

//显示扩充信息选择列表
function prefieldhtml($thevalue, $prefieldarr, $var, $input=1, $size='20', $isarray=0) {
	global $alang;

	if($isarray) {
		$optionstr = '';
		foreach ($prefieldarr as $nakey => $navalue) {
			$optionstr .= '<option value="'.$nakey.'">'.$navalue.'</option>';
		}
	} else {
		if(empty($prefieldarr[$var])) {
			$vararr = array();
		} else {
			$vararr = $prefieldarr[$var];
		}
		$optionstr = '';
		foreach ($vararr as $navalue) {
			$optionstr .= '<option value="'.$navalue['value'].'">'.$navalue['value'].'</option>';
			if(empty($thevalue[$var]) && !empty($navalue['isdefault'])) {
				$thevalue[$var] = $navalue['value'];
			}
		}
	}
	$varstr = '';
	if($input) {
		if(empty($thevalue[$var])) $thevalue[$var] = '';
		$varstr .= '<input name="'.$var.'" type="text" id="'.$var.'" size="'.$size.'" value="'.$thevalue[$var].'" />';
		$varstr .= ' <select name="varop" onchange="changevalue(\''.$var.'\', this.value)">';
		$varstr .= '<option value="">'.$alang['prefield_option_'.$var].'</option>';
	} else {
		$varstr .= '<select name="'.$var.'">';
		if(!empty($optionstr)) {
			$optionstr = str_replace('value="'.$thevalue[$var].'"', 'value="'.$thevalue[$var].'" selected', $optionstr);
		}
	}

	$varstr .= $optionstr;
	$varstr .= '</select>';
	return $varstr;
}

//获取相关TAG
function postgetincludetags($message, $tagnamearr) {
	global $_SGLOBAL;
	
	$postincludetags = '';
	if(!file_exists(S_ROOT.'./data/system/tag.cache.php')) {
		include_once(S_ROOT.'./include/cron/tagcontent.php');
	}
	@include_once(S_ROOT.'./data/system/tag.cache.php');
	if(empty($_SGLOBAL['tagcontent'])) $_SGLOBAL['tagcontent'] = '';
	$tagtext = implode('|', $tagnamearr).'|'.$_SGLOBAL['tagcontent'];
	$postincludetags = getincluetags($message, $tagtext);
	return $postincludetags;
}

//获取内容中包含的TAG
function getincluetags($text, $tagtext) {
	$resultarr = array();
	$tagtext = str_replace('/', '\/', $tagtext);
	preg_match_all("/($tagtext)/", $text, $matches);
	if(!empty($matches[1]) && is_array($matches[1])) {
		foreach ($matches[1] as $value) {
			if(strlen($value)>2) $resultarr[$value] = $value;
		}
	}
	return implode("\t", $resultarr);
}

//信息TAG关联处理
function postspacetag($op, $type, $itemid, $tagarr) {
	global $_SGLOBAL;

	$colnumname = "spacenewsnum";
	$deletetagidarr = $addtagidarr = $spacetagidarr = array();
	if($op == 'add') {
		if(!empty($tagarr['existsid'])) {
			$addtagidarr = $tagarr['existsid'];
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($tagarr['existsid']).')');
		}
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacetags').' WHERE itemid=\''.$itemid.'\'');
		while ($spacetag = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($tagarr['existsid']) && in_array($spacetag['tagid'], $tagarr['existsid'])) {
				$spacetagidarr[] = $spacetag['tagid'];
			} else {
				$deletetagidarr[] = $spacetag['tagid'];
			}
		}
		foreach ($tagarr['existsid'] as $etagid) {
			if(!empty($spacetagidarr) && in_array($etagid, $spacetagidarr)) {
			} else {
				$addtagidarr[] = $etagid;
			}
		}
		if(!empty($deletetagidarr)) {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE itemid='.$itemid.' AND tagid IN ('.simplode($deletetagidarr).')');
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET  '.$colnumname.'='.$colnumname.'-1 WHERE tagid IN ('.simplode($deletetagidarr).')');
		}
		if(!empty($addtagidarr)) {
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($addtagidarr).')');
		}
	}
	//TAG
	if(!empty($tagarr['nonename'])) {
		foreach ($tagarr['nonename'] as $posttagname) {
			$insertsqlarr = array(
				'tagname' => $posttagname,
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'dateline' => $_SGLOBAL['timestamp'],
				$colnumname => 1
			);
			$addtagidarr[] = inserttable('tags', $insertsqlarr, 1);			
		}
	}
	if(!empty($addtagidarr)) {
		$insertstr = $comma = '';
		foreach ($addtagidarr as $tagid) {
			$insertstr .= $comma.'(\''.$itemid.'\',\''.$tagid.'\',\''.$_SGLOBAL['timestamp'].'\',\''.$type.'\')';
			$comma = ',';
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('spacetags').' (itemid, tagid, dateline, type) VALUES '.$insertstr);
	}
}

//获取相关信息ID
function getrelativeitemids($itemid, $typearr=array(), $num=10) {
	global $_SGLOBAL;

	$tagidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('spacetags')." WHERE itemid='$itemid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagidarr[] = $value['tagid'];
	}
	if(empty($tagidarr)) return '';
	
	$sqlplus = '';
	if(!empty($typearr)) $sqlplus = "AND type IN (".simplode($typearr).")";
	$itemidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spacetags')." WHERE tagid IN (".simplode($tagidarr).") AND itemid<>'$itemid' $sqlplus ORDER BY itemid DESC LIMIT 0, $num");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemidarr[] = $value['itemid'];
	}
	return implode(',', $itemidarr);
	
}
?>