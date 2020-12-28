<?php

/**
 * ECMALL: 后台控制器基类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.backend.php 6069 2008-11-14 10:14:25Z yelin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/ctl.base.php');
require_once(ROOT_PATH. '/includes/models/mod.adminuser.php');

class ControllerBackend extends ControllerBase
{
    /* private attributes */
    var $logger     = true;
    var $log_item   = 0;

    /* 多语言支持部分 */

    /**
     *  语言包所在路径
     *
     *  @access
     */

    var $_lang_folder = '/languages/%s/admin/';

    /**
     *  公共语言包
     *
     *  @access
     */

    var $_common_lang = array('common');

    /**
     *  使用哪个模板引擎
     *  默认为使用不可变换风格的模板引擎template
     *  若要使用可变换风格的模板引擎请将该值改为use_theme
     *  @access
     */
    var $_use_which_template = 'use_template';
    var $_message_template_dir = '';

    /* public functions */
    function __construct($act)
    {
        $this->ControllerBackend($act);
    }

    function ControllerBackend($act)
    {
        /* 获取COOKIE中存储的登录信息,实现自动登录 */
        if (!isset($_SESSION['admin_id']))
        {
            $auth_string = '';
            if ($auth_string)
            {
                include_once(ROOT_PATH . '/includes/models/mod.adminuser.php');
                list($user_id, $password) = explode("\t", $auth_string);
                $user = new AdminUser(0, 0);
                $act = $user->local_login($user_id, $password) ? 'welcome' : 'login';
            }
        }
        else
        {
            /* 取得app */
            $app_ctrl = strtolower(get_class($this));
            $app = substr($app_ctrl, 0, strpos($app_ctrl, 'controller'));

            /* 判断权限 */
            include_once(ROOT_PATH . '/includes/models/mod.adminuser.php');
            $admin_mod = new AdminUser($_SESSION['admin_id'], $_SESSION['store_id']);
            if (!$admin_mod->check_priv(APPLICATION, $act))
            {
                $this->show_warning('not_allow_operate');
                return false;
            }
            /* 店铺状态的判断 */
            if ($_SESSION['store_id'] && !in_array($app . '-' . $act, array('profile-logout', 'home-home', 'home-welcome', $app . '-jslang', 'about-view', 'storerelet-add', 'storerelet-view', 'storerelet-pay', 'storerelet-do_pay')))
            {
                include_once(ROOT_PATH . '/includes/models/mod.store.php');
                $store_mod = new Store($_SESSION['store_id']);
                $store_info = $store_mod->get_info();
                if ($store_info['closed_by_admin'])
                {
                    $this->show_warning('store_closed', 'goto_logout', 'admin.php?app=profile&act=logout');
                    return false;
                }
                if ($store_info['expired'])
                {
                    $this->show_warning('store_time_illegal', 'goto_logout', 'admin.php?app=profile&act=logout');
                    return false;
                }
            }
        }

        $this->log_oprate($act); // 记录常用操作
        /* 构造 */
        parent::ControllerBase($act);
    }

    /**
     * 调用模板并显示
     *
     * @author  wj
     * @param   string  $template_file
     * @param   string  $template_dir
     *
     * @return  void
     */
    function display($template_file, $template_dir = '')
    {
        if (strpos($template_file, '.html') === FALSE)
        {
            $template_file .= '.html';
        }
        $this->_init_template();
        /* 配置源模板路径 */
        $this->set_template_path('/' . $template_dir, NULL, TRUE);

        /* 相关赋值 */
        $this->assign('store_id',   isset($_SESSION['store_id']) ? $_SESSION['store_id'] : 0);
        $this->assign('charset',    CHARSET);
        $this->assign('ecm_ver',    VERSION);
        $this->assign('lang',       $this->lang());
        $this->_assign_query_info();

        $template_dir = empty($template_dir) ? $template_dir : $template_dir . '/';

        $template_file = 'admin/templates/' . $template_dir . $template_file;

        /* send mail*/
        if (defined('SEND_MAIL'))
        {
            $send_mail_code = '<script type="text/javascript" src="index.php?app=mail&t=' . time()  . '" ></script>';
            $this->assign('send_mail_code', $send_mail_code);
        }
        /* 输出 */
        parent::display($template_file);
    }

    /**
     * 通过ajax方式修改实例的信息
     *
     * @param  string   $cls
     * @param  array    $get
     * @param  string   $msg_failed
     *
     * @return  bool
     */
    function _modify($cls, $get, $msg_failed='')
    {
        $idx = intval($get['id']);
        $col = trim($get['column']);
        $val = trim($get['value']);

        $obj = new $cls($idx, $_SESSION['store_id']);
        $arr = array($col=>$val);

        if ($obj->update($arr))
        {
            $this->log_item = $idx;
            $retval = empty($obj->err) ? '' : $obj->err;
            $this->json_result($retval);
            return;
        }
        else
        {
            $msg_failed = empty($obj->err) ? $msg_failed : $obj->err;
            $this->json_error($msg_failed);
            return;
        }
    }

    /* private functions */
    /**
     * 实例化模板对象
     *
     * @return  void
     */
    function config_template()
    {
        /* 位置不可调换 */
        parent::config_template();

        /* 设置模板文件所在目录 */
        if (isset($_SESSION['store_id']) && $_SESSION['store_id'] > 0)
        {
            $this->_template->cache_dir     = ROOT_PATH . '/temp/caches/store/admin';
            $this->_template->compile_dir   = ROOT_PATH . '/temp/compiled/store/admin';
        }
        else
        {
            $this->_template->cache_dir     = ROOT_PATH . '/temp/caches/mall/admin';
            $this->_template->compile_dir   = ROOT_PATH . '/temp/compiled/mall/admin';
        }
        $this->_template->template_dir  = ROOT_PATH. '/admin/templates';
    }

    /**
     * destory this object
     *
     * @author  wj
     * @return  void
     */
    function destory()
    {
        if ($this->logger === true)
        {
            include_once(ROOT_PATH . '/includes/manager/mng.admin_logs.php');
            $mng = new AdminLogManager($_SESSION['store_id']);
            $mng->add(addslashes($_SESSION['admin_name']), APPLICATION, $this->_action, $this->log_item);
        }
        $clean_action = array('add','drop','edit','update','batch','conf', 'modify', 'set_related', 'unset_related', 'change_status'); //需要清除缓存的操作
        if (in_array($this->_action, $clean_action))
        {
            $this->clean_cache(); //清除缓存
        }
    }

    function log_oprate($act)
    {
        if ($_SESSION['store_id'] === "0")
        {
            include(ROOT_PATH.'/admin/mall/inc.menu.php');
        }
        else
        {
            include(ROOT_PATH.'/admin/store/inc.menu.php');
        }
        foreach ($menu_data AS $group)
        {
            foreach ($group AS $key=>$menu)
            {
                if (APPLICATION == $menu['app'] && $act == $menu['act'])
                {
                    $user = new AdminUser($_SESSION['admin_id']);
                    $user->update_nav($key, $menu);

                    break;
                }
            }
        }
    }

    /**
     * 获得指定的语言项
     *
     * @author  weberliu
     * @param   string  $key
     * @return  string|array
     */
    function lang($key='')
    {
        if (!Language::is_loaded($this->_common_lang))
        {
            /* 加载公用语言包 */
            $this->_load_common_lang();
        }

        if (defined("APPLICATION") && !Language::is_loaded(APPLICATION))
        {
            /* 加载应用对应的语言包 */
            $this->_load_app_lang();
        }

        return parent::lang($key);
    }

    /**
     * 检查编辑器和商品有无上传过附件
     *
     * @author  wj
     * @param   void
     * @retuan  boolen
     */
    function has_uploaded_file()
    {
        $result = false;
        if (!empty($_FILES))
        {
            foreach ($_FILES as $key=>$val)
            {
                if ((strncmp($key, 'editor_upload_file', 18) == 0) && isset($val['size']) && $val['size'] > 0)
                {
                    //编辑器文件
                    $result = true;
                    break;
                }
                elseif($key=='size')
                {
                    if (is_array($val))
                    {
                        //检查商品图片
                        foreach ($val as $v)
                        {
                            if ($v > 0)
                            {
                                $result = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 检查店铺文件是否超过数量
     * @param   int     $store_id   店铺id
     * @return  string
     */
    function check_store_file_count($store_id)
    {
        $result = '';
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $new_store = new Store($store_id);
        $info = $new_store->get_info();

        if ($info['file_limit'] > 0 && $new_store->get_file_count() >= $info['file_limit'])
        {
            $result = $this->str_format('over_file_count', $file_count, $info['file_limit']);
        }

        return $result;
    }
};


class MessageBase extends ControllerBackend {};

?>
