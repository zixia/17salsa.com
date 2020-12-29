<?php

/**
 * ECMALL: 管理中心入口程序基类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 管理中心框架结构
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
     * 获得当前用户的权限，把无权访问的菜单排除出去
     * @param array $menu_data 全部菜单的数据数组
     *
     * @return array $menu_data 处理后的菜单数据数组
     */
    function permission($menu_data)
    {
        /**
         * 暂时忽略处理部分代码
         */
        return $menu_data;
    }
    /**
     * 显示欢迎页
     *
     * @return  void
     */
    function welcome()
    {
        $this->logger = false;

        $admin = new AdminUser($_SESSION['admin_id']);

        /* 获取身份信息 */
        $info = $admin->get_user_detail();

        /* 检测是否有新短信 */
        $this->assign('new_pm', uc_call('uc_pm_checknew', array($_SESSION['admin_id'])));

        $this->assign('ai', $info);
        $this->assign('welcome',    $this->str_format('welcome', $_SESSION['admin_name']));
        $this->assign('last_login', $_SESSION['last_login'] ? local_date($this->conf('mall_time_format_complete'), $_SESSION['last_login']) : $this->lang('nerver'));
        $this->assign('last_ip',    $_SESSION['last_ip'] ? $_SESSION['last_ip'] : '0.0.0.0');
        $this->display('welcome.html', $this->_domain);
    }

    /**
     *  显示登录页
     *
     *  @param
     *  @return
     */
    function login()
    {
        $this->redirect('admin.php?app=profile&act=login');
    }
    /**
     * 获得管理员的常用操作
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
     * 清除缓存
     */
    function mall_clean_cache()
    {
        clean_cache();
        $this->json_result(1,$this->lang('clean_cache_ok'));
        return;
    }

};
?>