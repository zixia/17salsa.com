<?php

/**
 * ECMALL: ����������ڳ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctl.home.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class HomeBaseController extends ControllerBackend
{
    function __construct($act)
    {
        $this->HomeBaseController($act);
    }

    function HomeBaseController($act)
    {
        if (empty($act))
        {
            $act = 'home';
        }
        parent::__construct($act);
    }
    /**
     * �������Ŀ�ܽṹ
     *
     * @return  void
     */
    function home()
    {
        $this->logger = false;

        require(ROOT_PATH.'/admin/' . $this->_domain . '/inc.menu.php');
        $menu_data = $this->permission($menu_data);
        $this->assign('menu_data', $menu_data);

        if ($_SESSION['store_id'] > 0)
        {
            $this->assign('store_url', get_store_url($_SESSION['store_id']));
        }

        $this->assign('store_id', $_SESSION['store_id']);
        $this->assign("menu_shortcut", $this->get_shortcut_menu());
        $this->display('home.html');
    }
    /**
     * ��õ�ǰ�û���Ȩ�ޣ�����Ȩ���ʵĲ˵��ų���ȥ
     * @param array $menu_data ȫ���˵�����������
     *
     * @return array $menu_data �����Ĳ˵���������
     */
    function permission($menu_data)
    {
        /**
         * ��ʱ���Դ����ִ���
         */
        return $menu_data;
    }
    /**
     * ��ʾ��ӭҳ
     *
     * @return  void
     */
    function welcome()
    {
        $this->logger = false;

        $admin = new AdminUser($_SESSION['admin_id']);

        /* ��ȡ�����Ϣ */
        $info = $admin->get_user_detail();

        /* ����Ƿ����¶��� */
        $this->assign('new_pm', uc_call('uc_pm_checknew', array($_SESSION['admin_id'])));

        $this->assign('ai', $info);
        $this->assign('welcome',    $this->str_format('welcome', $_SESSION['admin_name']));
        $this->assign('last_login', $_SESSION['last_login'] ? local_date($this->conf('mall_time_format_complete'), $_SESSION['last_login']) : $this->lang('nerver'));
        $this->assign('last_ip',    $_SESSION['last_ip'] ? $_SESSION['last_ip'] : '0.0.0.0');
        $this->display('welcome.html', $this->_domain);
    }

    /**
     *  ��ʾ��¼ҳ
     *
     *  @param
     *  @return
     */
    function login()
    {
        $this->redirect('admin.php?app=profile&act=login');
    }
    /**
     * ��ù���Ա�ĳ��ò���
     *
     * @return  array
     */
    function get_shortcut_menu()
    {
        $admin_user = new AdminUser($_SESSION['admin_id']);
        $admin_info = $admin_user->get_info();
        $admin_menu = empty($admin_info['nav_list']) ? null : unserialize($admin_info['nav_list']);

        if (!empty($admin_menu))
        {
            $func = create_function('$a, $b', 'return $a[\'times\']<$b[\'times\'] ? 1:-1;');
            uasort($admin_menu, $func);
        }

        return $admin_menu;
    }

    /**
     * �������
     */
    function mall_clean_cache()
    {
        clean_cache();
        $this->json_result(1,$this->lang('clean_cache_ok'));
        return;
    }

};
?>