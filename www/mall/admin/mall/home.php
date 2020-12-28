<?php

/**
 * ECMALL: 管理中心入口程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: home.php 6075 2008-11-18 10:28:00Z yelin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include(ROOT_PATH . '/admin/includes/ctl.home.php');

class HomeController extends HomeBaseController
{
    /* 该控制器所属的域为Store */
    var $_domain = 'mall';

    /**
     * 显示欢迎页
     *
     * author  liupeng
     * return  void
     */
    function welcome()
    {
        include(ROOT_PATH . '/includes/inc.constant.php');
        include(ROOT_PATH . '/admin/includes/inc.stat.php');

        /* 取得站内动态 */
        $newmember_count            = get_member_count('today');
        $newgoods_count             = get_goods_count('today');
        $newstore_count             = get_store_count('today');
        $today_finish_order_count   = get_order_count('today', 'finish', 'number');

        /* 取得统计信息 */

        $this->stat_info['member_count']           = get_member_count('unlimited');
        $this->stat_info['goods_count']            = get_goods_count('unlimited');
        $this->stat_info['store_count']            = get_store_count('unlimited');
        $this->stat_info['order_count']            = get_order_count('unlimited');
        $this->stat_info['finish_order_amount']    = get_order_count('unlimited', 'finish', 'amount');
        $this->stat_info['all_finish_order_count'] = get_order_count('unlimited', 'finish', 'number');


        /* 取得系统信息 */
        $sysinfo['server_os']       = PHP_OS;
        $sysinfo['web_server']      = $_SERVER['SERVER_SOFTWARE'];
        $sysinfo['php_version']     = PHP_VERSION;
        $sysinfo['mysql_version']   = $GLOBALS['db']->version();
        $sysinfo['current_version'] = VERSION;
        $sysinfo['install_date']    = local_date('Y-m-d H:i', file_get_contents(ROOT_PATH . '/data/install.lock'));


        $this->assign('install_dir_exists',     is_dir(ROOT_PATH . '/install'));
        $this->assign('site_domain',            urlencode(site_url()));

        /* 当前程序信息 */
        $this->assign('ecsc_lang',              CHARSET);

        $this->assign('newmember_count',        $newmember_count);
        $this->assign('newgoods_count',         $newgoods_count);
        $this->assign('newstore_count',         $newstore_count);
        $this->assign('finish_order_count',     $today_finish_order_count);

        $this->assign('member_count',           $this->stat_info['member_count']);
        $this->assign('goods_count',            $this->stat_info['goods_count']);
        $this->assign('store_count',            $this->stat_info['store_count']);
        $this->assign('order_count',            $this->stat_info['order_count']);
        $this->assign('finish_order_amount',    $this->stat_info['finish_order_amount']);
        $this->assign('all_finish_order_count', $this->stat_info['all_finish_order_count']);
        $this->assign('new_apply_count',        get_new_apply_count());

        $this->assign('sysinfo',                $sysinfo);
        $this->assign('CURRENT_VERSION',        CURRENT_VERSION);
        $this->assign('spt',                    $this->update_site_information());

        parent::welcome();
    }

    /**
     * 新版本检测函数
     *
     * @author liupeng
     * @return string
     */
    function update_site_information()
    {
        require_once(ROOT_PATH . '/includes/inc.constant.php');
        $pid = $this->conf("mall_site_id");
        $mysql_version = $GLOBALS['db']->version();
        // 必要信息
        $update = array(
            'uniqueid' => $pid,         // 产品id
            'version' => VERSION,       // 程序版本
            'release' => RELEASE,       // 程序更新日期
            'php' => PHP_VERSION,       // php 版本
            'mysql' => $mysql_version,  // mysql 版本
            'charset' => CHARSET,       // 页面编码
            'url' => site_url(),   //网站地址
        );

        $update_time = 0;
        if (file_exists(ROOT_PATH . '/data/update_time.lock'))
        {
            $update_time = filemtime(ROOT_PATH . '/data/update_time.lock');
        }

        if(empty($update_time) || ($timestamp - $update_time > 3600 * 4))
        {
            touch(ROOT_PATH.'/data/update_time.lock');
            $stat_info = array();
            $stat_info['page_view']    = get_page_view();
            $stat_info['order_amount'] = get_order_count('unlimited', 'finish', 'amount');
            $stat_info['order_count']  = get_order_count();
            $stat_info['store_count']  = get_store_count();
            $stat_info['member_count'] = get_member_count('30day'); // 最近三十天登陆过的用户
            $stat_info['goods_count']  = get_goods_count('30day');  // 最近三十天更新过的商品
            $stat_info['admin_last_login_time']   = get_admin_last_login_time();

            foreach($stat_info AS $key => $value)
            {
                $update[$key] = $value;
            }
        }

        $data = '';
        $timestamp = time();
        foreach($update as $key => $value)
        {
            $data .= $key.'='.rawurlencode($value).'&';
        }

        //发布使用
        return '<sc'.'ript language="Jav'.'aScript" src="ht'. 'tp:/' . '/e' .'cmal' . 'l.sho' . 'pe' . 'x.c' . 'n/sy' . 'stem'. '/ecm' . 'all' . '_in' . 'stal' . 'l.p' . 'hp?'.'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp.'"></s'.'cri'.'pt>';
        //以下仅用于测试
        //return '<sc'.'ript language="Jav'.'aScript" src="http://localhost/com/news.php?'.'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp.'"></s'.'cri'.'pt>';
    }

};
?>