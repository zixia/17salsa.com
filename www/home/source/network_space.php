<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_space.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');
@include_once(S_ROOT.'./data/data_profilefield.php');
$fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];
	
//初始化
$multi_mode = false;//节约服务器资源，关闭排行榜分页
$cache_time = 5;//5分钟一更新

$gets = $list = array();
$multi = '';

if(!empty($_GET['searchmode'])) {
	
	$now_pos = -1;
	
	//判断是否搜索太快
	$waittime = interval_check('search');
	if($waittime > 0) {
		showmessage('search_short_interval');
	}
	$gets['username'] =  empty($_GET['username'])?'':stripsearchkey($_GET['username']);
	$gets['name'] =  empty($_GET['name'])?'':stripsearchkey($_GET['name']);
	$gets['fieldid'] = empty($_GET['fieldid'])?'':intval($_GET['fieldid']);
	if($gets['fieldid'] && !empty($_SGLOBAL['profield'][$gets['fieldid']])) {
		$gets['fieldname'] = empty($_GET['fieldname'])?'':stripsearchkey($_GET['fieldname']);
	} else {
		$gets['fieldid'] = $gets['fieldname'] = '';
	}
	$gets['tagid'] = empty($_GET['tagid'])?'':intval($_GET['tagid']);
	$gets['blood'] = empty($_GET['blood'])?'':stripsearchkey($_GET['blood']);
	$gets['birthprovince'] = empty($_GET['birthprovince'])?'':stripsearchkey($_GET['birthprovince']);
	$gets['birthcity'] = empty($_GET['birthcity'])?'':stripsearchkey($_GET['birthcity']);
	$gets['resideprovince'] = empty($_GET['resideprovince'])?'':stripsearchkey($_GET['resideprovince']);
	$gets['residecity'] = empty($_GET['residecity'])?'':stripsearchkey($_GET['residecity']);
	$gets['birthyear'] = empty($_GET['birthyear'])?'':intval($_GET['birthyear']);
	$gets['birthmonth'] = empty($_GET['birthmonth'])?'':intval($_GET['birthmonth']);
	$gets['birthday'] = empty($_GET['birthday'])?'':intval($_GET['birthday']);
	$gets['sex'] = empty($_GET['sex'])?'':intval($_GET['sex']);
	$gets['marry'] = empty($_GET['marry'])?'':intval($_GET['marry']);
	$gets['qq'] = empty($_GET['qq'])?'':stripsearchkey($_GET['qq']);
	$gets['msn'] = empty($_GET['msn'])?'':stripsearchkey($_GET['msn']);
	
	$gets['startage'] = empty($_GET['startage'])?'':intval($_GET['startage']);
	$gets['endage'] = empty($_GET['endage'])?'':intval($_GET['endage']);
	
	//搜索积分/不扣积分
	//cksearchcredit($ac);
	
	//开始搜索
	$wherearr = array();
	foreach (array('sex', 'birthyear', 'birthmonth', 'birthday', 'marry', 'blood', 'birthprovince', 'birthcity', 'resideprovince', 'residecity', 'qq', 'msn') as $value) {
		if($gets[$value]) {
			$wherearr[] = "spacefield.$value='$gets[$value]'";
		}
	}
	
	if(empty($gets['birthyear'])) {
		$startage = $endage = 0;
		//转换成实际的年份
		if($gets['endage']) {
			$startage = sgmdate('Y') - $gets['endage'];
		}
		if($gets['startage']) {
			$endage = sgmdate('Y') - $gets['startage'];
		}
		if($startage && $endage && $endage > $startage) {
			$wherearr[] = '(spacefield.birthyear>='.$startage.' AND spacefield.birthyear<='.$endage.')';
		} else if($startage && empty($endage)) {
			$wherearr[] = 'spacefield.birthyear>='.$startage;
		} else if(empty($startage) && $endage) {
			$wherearr[] = 'spacefield.birthyear<='.$endage;
		}
	}
	//自定义
	foreach ($fields as $fkey => $fvalue) {
		if($fvalue['allowsearch']) {
			$gets['field_'.$fkey] = empty($_GET['field_'.$fkey])?'':stripsearchkey($_GET['field_'.$fkey]);
			if($gets['field_'.$fkey]) {
				$wherearr[] = "spacefield.field_$fkey LIKE '%".$gets['field_'.$fkey]."%'";
			}
		}
	}
	
	$next = true;
	if($gets['tagid']) {
		$tagid = $gets['tagid'];
	} elseif($gets['fieldid'] && $gets['fieldname']) {
		$tagid = getcount('mtag', array('tagname'=>$gets['fieldname'], 'fieldid'=>$gets['fieldid']), 'tagid');
		if(empty($tagid)) {
			$next = false;
		}
	}
	if($next) {
		$selectsql = $fromsql = '';
		if($tagid) {
			$selectsql = "tagspace.uid, tagspace.username";
			$wherearr[] = "tagspace.uid = spacefield.uid";
			$wherearr[] = "tagspace.tagid = '$tagid'";
			$fromsql .= ','.tname('tagspace').' tagspace';
		}
		if($gets['username'] || $gets['name']) {
			$selectsql = "space.*";
			$wherearr[] = "space.uid = spacefield.uid";
			if($gets['username']) {
				$wherearr[] = "space.username LIKE '%$gets[username]%'";
			}
			if($gets['name']) {
				$wherearr[] = "space.name LIKE '%$gets[name]%'";
			}
			$fromsql .= ','.tname('space').' space';
		}
		if(empty($selectsql)) {
			$selectsql = "space.*";
			$wherearr[] = "space.uid = spacefield.uid";
			$fromsql .= ','.tname('space').' space';
		}
		if(empty($wherearr)) {
			showmessage('set_the_correct_search_content');
		}
		
		$wheresql = implode(' AND ', $wherearr);
		$sql = "SELECT $selectsql, spacefield.* FROM ".tname('spacefield')." spacefield $fromsql WHERE $wheresql";
		
		$fuids = array();
		$count = 0;
		$query = $_SGLOBAL['db']->query($sql.' LIMIT 0, 100');//最多100条
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$fuids[] = $value['uid'];
			$list[] = $value;
		}
		
		//在线状态
		$ols = array();
		if($fuids) {
			$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$ols[$value['uid']] = $value['lastactivity'];
			}
		}
		
		//更新最后操作时间
		if($_SGLOBAL['supe_uid']) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastsearch='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		}
	}
	
} else {

	//分页
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);

	//普通浏览模式
	$cache_file = '';
	$fuids = array();
	$count = 0;
	$now_pos = 0;
	
	if ($_GET['view'] == 'show') {
		$c_sql = "SELECT COUNT(*) FROM ".tname('show');
		$sql = "SELECT space.*, field.*, main.* FROM ".tname('show')." main
			LEFT JOIN ".tname('space')." space ON space.uid=main.uid
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
			ORDER BY main.credit DESC";
		
		//清理
		if(substr($_SGLOBAL['timestamp'], -1) == '0') {
			$_SGLOBAL['db']->query("DELETE FROM ".tname('show')." WHERE credit<1");//清理小于1的数据
		}
		
		//我的竞价积分
		$space['showcredit'] = getcount('show', array('uid'=>$space['uid']), 'credit');
		$space['showcredit'] = intval($space['showcredit']);
		
		//我的位置
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('show')." WHERE credit>'$space[showcredit]'"), 0);
		$now_pos++;
		
	} elseif ($_GET['view'] == 'mm') {
		if($multi_mode) {
			$c_sql = "SELECT COUNT(*) FROM ".tname('spacefield')." WHERE sex='2'";
		} else {
			$count = 100;
			$cache_file = S_ROOT.'./data/cache_top_mm.txt';
		}
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main, ".tname('spacefield')." field
			WHERE field.sex='2' AND field.uid=main.uid
			ORDER BY main.viewnum DESC";
		
		//我的位置
		if($space['sex']==2) {
			$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE f.uid=s.uid AND f.sex='2' AND s.viewnum>'$space[viewnum]'";
			$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
			$now_pos++;
		} else {
			$now_pos = -1;
		}
			
	} elseif ($_GET['view'] == 'gg') {
		if($multi_mode) {
			$c_sql = "SELECT COUNT(*) FROM ".tname('spacefield')." WHERE sex='1'";
		} else {
			$count = 100;
			$cache_file = S_ROOT.'./data/cache_top_gg.txt';
		}
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main, ".tname('spacefield')." field
			WHERE field.sex='1' AND field.uid=main.uid
			ORDER BY main.viewnum DESC";
		
		//我的位置
		if($space['sex']==1) {
			$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE f.uid=s.uid AND f.sex='1' AND s.viewnum>'$space[viewnum]'";
			$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
			$now_pos++;
		} else {
			$now_pos = -1;
		}
		
	} elseif ($_GET['view'] == 'credit') {
		if($multi_mode) {
			$c_sql = "SELECT COUNT(*) FROM ".tname('space');
		} else {
			$count = 100;
			$cache_file = S_ROOT.'./data/cache_top_credit.txt';
		}
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
			ORDER BY main.credit DESC";
		
		//我的位置
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.credit>'$space[credit]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		
	} elseif ($_GET['view'] == 'friendnum') {
		if($multi_mode) {
			$c_sql = "SELECT COUNT(*) FROM ".tname('space');
		} else {
			$count = 100;
			$cache_file = S_ROOT.'./data/cache_top_friendnum.txt';
		}
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
			ORDER BY main.friendnum DESC";
		
		//我的位置
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.friendnum>'$space[friendnum]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		
	} elseif ($_GET['view'] == 'viewnum') {
		if($multi_mode) {
			$c_sql = "SELECT COUNT(*) FROM ".tname('space');
		} else {
			$count = 100;
			$cache_file = S_ROOT.'./data/cache_top_viewnum.txt';
		}
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
			ORDER BY main.viewnum DESC";
		
		//我的位置
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.viewnum>'$space[viewnum]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		
	} elseif ($_GET['view'] == 'online') {
		$c_sql = "SELECT COUNT(*) FROM ".tname('session');
		$sql = "SELECT field.*, space.*, main.*
			FROM ".tname('session')." main
			LEFT JOIN ".tname('space')." space ON space.uid=main.uid
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid";
		$now_pos = -1;
	} else {
		$c_sql = "SELECT COUNT(*) FROM ".tname('space');
		$sql = "SELECT main.*, field.* FROM ".tname('space')." main USE INDEX (updatetime)
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
			ORDER BY main.updatetime DESC";
		$_GET['view'] = 'all';
		
		$now_pos = -1;
	}
	
	$list = array();
	if(empty($count)) {
		$cache_mode = false;
		$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query($c_sql),0);
		$multi = multi($count, $perpage, $page, $theurl."&view=$_GET[view]");
	} else {
		$cache_mode = true;
		$multi = '';
		$start = 0;
		$perpage = $count;
		
		if($cache_file && file_exists($cache_file) && $_SGLOBAL['timestamp'] - @filemtime($cache_file) < $cache_time*60) {
			$list_cache = sreadfile($cache_file);
			$list = unserialize($list_cache);
		}
	}
	if($count && empty($list)) {
		$query = $_SGLOBAL['db']->query("$sql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$fuids[] = $value['uid'];
			$list[] = $value;
		}
		if($cache_mode && $cache_file) {
			swritefile($cache_file, serialize($list));
		}
	}
	
	//在线状态
	$ols = array();
	if($fuids) {
		$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$ols[$value['uid']] = $value['lastactivity'];
		}
	}
	
	$sub_actives = array($_GET['view'] => ' class="current"');
	
}

//页面显示
//性别
$sexarr = array($gets['sex']=>' selected');

//生日:年
$birthyeayhtml = '';
$nowy = sgmdate('Y');
for ($i=1; $i<80; $i++) {
	$they = $nowy - $i;
	$selectstr = $they == $gets['birthyear']?' selected':'';
	$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
}
//生日:月
$birthmonthhtml = '';
for ($i=1; $i<13; $i++) {
	$selectstr = $i == $gets['birthmonth']?' selected':'';
	$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//生日:日
$birthdayhtml = '';
for ($i=1; $i<32; $i++) {
	$selectstr = $i == $gets['birthday']?' selected':'';
	$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//血型
$bloodhtml = '';
foreach (array('A','B','O','AB') as $value) {
	$selectstr = $value == $gets['blood']?' selected':'';
	$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
}
//婚姻
$marryarr = array($gets['marry']=>' selected');

//群组
$fieldids = array($gets['fieldid']=>' selected');

//自定义
foreach ($fields as $fkey => $fvalue) {
	if($fvalue['allowsearch']) {
		if($fvalue['formtype'] == 'text') {
			$fvalue['html'] = '<input type="text" name="field_'.$fkey.'" value="'.$gets["field_$fkey"].'" class="t_input">';
		} else {
			$fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>";
			$optionarr = explode("\n", $fvalue['choice']);
			foreach ($optionarr as $ov) {
				$ov = trim($ov);
				if($ov) {
					$selectstr = $gets["field_$fkey"]==$ov?' selected':'';
					$fvalue['html'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
				}
			}
			$fvalue['html'] .= "</select>";
		}
		$fields[$fkey] = $fvalue;
	} else {
		unset($fields[$fkey]);
	}
}

realname_get();

$_GET = shtmlspecialchars(sstripslashes($_GET));

?>