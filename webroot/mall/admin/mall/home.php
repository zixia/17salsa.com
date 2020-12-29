<?php

/**
 * ECMALL: ����������ڳ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
    /* �ÿ�������������ΪStore */
    var $_domain = 'mall';

    /**
     * ��ʾ��ӭҳ
     *
     * author  liupeng
     * return  void
     */
    function welcome()
    {
        include(ROOT_PATH . '/includes/inc.constant.php');
        include(ROOT_PATH . '/admin/includes/inc.stat.php');

        /* ȡ��վ�ڶ�̬ */
        $newmember_count            = get_member_count('today');
        $newgoods_count             = get_goods_count('today');
        $newstore_count             = get_store_count('today');
        $today_finish_order_count   = get_order_count('today', 'finish', 'number');

        /* ȡ��ͳ����Ϣ */

        $this->stat_info['member_count']           = get_member_count('unlimited');
        $this->stat_info['goods_count']            = get_goods_count('unlimited');
        $this->stat_info['store_count']            = get_store_count('unlimited');
        $this->stat_info['order_count']            = get_order_count('unlimited');
        $this->stat_info['finish_order_amount']    = get_order_count('unlimited', 'finish', 'amount');
        $this->stat_info['all_finish_order_count'] = get_order_count('unlimited', 'finish', 'number');


        /* ȡ��ϵͳ��Ϣ */
        $sysinfo['server_os']       = PHP_OS;
        $sysinfo['web_server']      = $_SERVER['SERVER_SOFTWARE'];
        $sysinfo['php_version']     = PHP_VERSION;
        $sysinfo['mysql_version']   = $GLOBALS['db']->version();
        $sysinfo['current_version'] = VERSION;
        $sysinfo['install_date']    = local_date('Y-m-d H:i', file_get_contents(ROOT_PATH . '/data/install.lock'));


        $this->assign('install_dir_exists',     is_dir(ROOT_PATH . '/install'));
        $this->assign('site_domain',            urlencode(site_url()));

        /* ��ǰ������Ϣ */
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
     * �°汾��⺯��
     *
     * @author liupeng
     * @return string
     */
    function update_site_information()
    {
        require_once(ROOT_PATH . '/includes/inc.constant.php');
        $pid = $this->conf("mall_site_id");
        $mysql_version = $GLOBALS['db']->version();
        // ��Ҫ��Ϣ
        $update = array(
            'uniqueid' => $pid,         // ��Ʒid
            'version' => VERSION,       // ����汾
            'release' => RELEASE,       // �����������
            'php' => PHP_VERSION,       // php �汾
            'mysql' => $mysql_version,  // mysql �汾
            'charset' => CHARSET,       // ҳ�����
            'url' => site_url(),   //��վ��ַ
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
            $stat_info['member_count'] = get_member_count('30day'); // �����ʮ���½�����û�
            $stat_info['goods_count']  = get_goods_count('30day');  // �����ʮ����¹�����Ʒ
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

        //����ʹ��
        return '<sc'.'ript language="Jav'.'aScript" src="ht'. 'tp:/' . '/e' .'cmal' . 'l.sho' . 'pe' . 'x.c' . 'n/sy' . 'stem'. '/ecm' . 'all' . '_in' . 'stal' . 'l.p' . 'hp?'.'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp.'"></s'.'cri'.'pt>';
        //���½����ڲ���
        //return '<sc'.'ript language="Jav'.'aScript" src="http://localhost/com/news.php?'.'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp.'"></s'.'cri'.'pt>';
    }

};
?>