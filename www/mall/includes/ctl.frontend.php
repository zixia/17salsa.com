<?php

/**
 * ECMALL: 控制器基类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.frontend.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/ctl.base.php');

class ControllerFrontend extends ControllerBase
{
    /* private attributes */
    var $_log       = true;
    var $_log_item  = 0;
    var $_is_cached = false;
    var $_page_title = array();
    var $_allowed_actions = array();

    /**
     *  公共语言包
     *
     *  @access
     */

    var $_common_lang = array('common');

    /**
     *  使用可变换风格的模板引擎
     *
     *  @access
     */

    var $_use_which_template = 'use_theme';

    /**
     *  指定消息处理时调用的模板目录
     *
     *  @access
     */

    var $_message_template_dir = 'mall';

    /* public functions */
    function __construct($act)
    {
        $this->ControllerFrontend($act);
    }

    /**
     * 构造函数
     *
     * @author wj
     * @param string $act
     * @return void
     */
    function ControllerFrontend($act)
    {
        /* 过滤act */
        if (!in_array($act, $this->_allowed_actions))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $class_name = get_class($this);

        if (!$this->conf('mall_status'))
        {
            $mall_closed_reason = $this->conf('mall_closed_reason');
            if (!$mall_closed_reason)
            {
                $mall_closed_reason = 'mall_closed';
            }
            $this->show_warning($mall_closed_reason, 'close_window', 'javascript:window.close();');
            return false;
        }

        //检查是否要同步登录
        if (!empty($_COOKIE['ECM_USERNAME']) && !empty($_COOKIE['ECM_AUTH']) && strlen($_COOKIE['ECM_AUTH']) == 32)
        {
            $user_info = uc_call('uc_get_user', array($_COOKIE['ECM_USERNAME']));
            $user_info[0] = intval($user_info[0]);
            if ($user_info[0] > 0 && $_COOKIE['ECM_AUTH'] == md5($user_info[0] . ECM_KEY . $user_info[1]))
            {
                //同步登录
                if (!empty($_SESSION['usser_id']))
                {
                    /* 如果已经登录,清除已登录用户信息 */
                    $GLOBALS['sess']->destroy_session();
                    $_SESSION = array();
                }
                include_once(ROOT_PATH . '/includes/models/mod.user.php');
                $mod_user = new User(0);
                $tmp = array('uid'=>$user_info[0], 'username'=>$user_info[1], 'email'=>$user_info[2]);
                $mod_user->set_login($tmp);
            }
        }
        ecm_setcookie('ECM_AUTH', ''); //无论是否成功登录,都把验证串抹掉
        parent::ControllerBase($act);
    }

    /**
     * 调用模板并显示
     *
     * @author  wj
     * @param string  $template_file  模板名称
     * @param string $template_dir    模板路径
     * @param string  $cache_id         缓存id
     * @return  void
     */
    function display($template_file, $template_dir = '',$cache_id='')
    {
        $this->_init_template();
        if (empty($template_dir))
        {
            $template_dir = 'mall';
        }

        if (empty($this->_template->skin))
        {
            $skin_conf_key = $this->_template->store_id == 0 ? 'mall_skin' : 'store_skin';
            $skin = $this->conf($skin_conf_key, $this->_template->store_id);

            $this->_template->skin = empty($skin) ? 'default' : $skin;
        }
        define('URL_REWRITE', $this->conf('mall_url_rewrite'));

        /* 检测短消息 */
        if (!empty($_SESSION['user_id']))
        {
            $this->assign('check_new_pm', 1);
        }

        /* 配置模板引擎 */
        $this->set_template_path('/' . $template_dir, '/' . $template_dir, TRUE);

        /* 相关赋值 */
        $this->assign('icp_number', $this->conf('mall_icp_number'));
        $this->assign('mall_storeapply', $this->conf('mall_storeapply'));
        $visitor = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';//note 去掉游客的ip显示
        $this->assign('visitor',    $visitor);
        isset($_SESSION['last_ip']) && $this->assign('last_ip',    $_SESSION['last_ip']);
        isset($_SESSION['last_login']) && $this->assign('last_login', $_SESSION['last_login']);
        $cart_goods_count = 0;
        $cart_goods_amount= 0;
        $cart_type_count  = 0;
        if (!isset($_SESSION['cart_stats']))
        {
            include_once(ROOT_PATH . '/includes/models/mod.supercart.php');
            $super_cart = new SuperCart();
            $super_cart->recount();
        }

        list($cart_goods_count, $cart_goods_amount, $cart_type_count) = explode(',', $_SESSION['cart_stats']);
        $this->assign('cart_goods_count',   $cart_goods_count);
        $this->assign('cart_goods_amount',  $cart_goods_amount);
        $this->assign('cart_type_count',    $cart_type_count);

        $this->assign('charset',        CHARSET);
        $this->assign('uc_path',        UC_API);
        $this->assign('mall_copyright', $this->conf('mall_copyright'));
        $this->_assign_query_info();

        /* 没缓存时赋值 */
        if (empty($cache_id) || ($cache_id && ($this->_is_cached == false)))
        {
            $nav_list = $this->get_nav_list();  // 取得导航


            $this->assign('nav_list',   $nav_list['middle']);
            $this->assign('top_nav',    $nav_list['top']);
            $this->assign('bottom_nav', $nav_list['bottom']);

            /* 帮助中心的链接 */
            $this->assign('help_center_url', 'index.php?app=article&amp;act=help_center');

            /* 取得分类下拉列表 */
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            $cate_mod = new Category();
            $this->assign('category', $cate_mod->get_options(2));

            /* 取得热门关键字 */
            $hot_keywords = $this->conf('mall_hot_search');
            if ($hot_keywords)
            {
                $hot_keywords = preg_split('/\s+/', $hot_keywords);
                $this->assign('hot_keywords', $hot_keywords);
            }

            /*title*/
            $this->assign('title', $this->make_title());

            /* meta_keywords */
            $this->assign('meta_keywords', $this->get_meta_keywords());
            $this->assign('lang',           $this->lang());

            /* show goods volumn */
            $this->assign('mall_display_volumn', $this->conf('mall_display_volumn'));

            /* send mail*/
            if (defined('SEND_MAIL'))
            {
                $send_mail_code = '<script type="text/javascript" src="index.php?app=mail&t=' . time()  . '" ></script>';
                $this->assign('send_mail_code', $send_mail_code);
            }
            /* 输出 */
            parent::display($template_file, $cache_id);
        }
        else
        {
            header("Content-type:text/html;charset=" . CHARSET, true);
            $this->_template->display_cache();
        }
    }

    /**
     * 获得页面上的导航设置
     *
     * @author  wj
     */
    function get_nav_list()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.nav.php');
        $nav_mng    = new NavManager(0);
        $nav_list   = $nav_mng->get_list(true);
        $links      = array();
        $links[]    = array('text' => $this->lang('home'), 'href' => 'index.php', null);
        $cate_list  = array();

        if (!isset($nav_list['top']))
        {
            $nav_list['top'] = array();
        }

        if (!isset($nav_list['bottom']))
        {
            $nav_list['bottom'] = array();
        }

        if (isset($nav_list['middle']))
        {
            foreach ($nav_list['middle'] as $key=>$nav)
            {
                if ($nav['if_show'])
                {
                    if ($nav['cate_id'] > 0)
                    {
                        $cate_list[$nav['cate_id']] = $key + 1;
                    }

                    $links[] = array(
                        'text'      => $nav['nav_name'],
                        'href'      => $nav['nav_url'],
                        'open_new'  => $nav['open_new']
                    );
                }
            }

        }

        /* 判断该选谁 */
        $select_index = 0;
        $selected_cate_id = isset($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;
        if ($selected_cate_id > 0 && $cate_list)
        {
            if (isset($cate_list[$selected_cate_id]))
            {
                $select_index = $cate_list[$selected_cate_id];
            }
            else
            {
                include_once(ROOT_PATH . '/includes/models/mod.category.php');
                $cate_mod = new Category($selected_cate_id);
                $info = $cate_mod->get_info();
                if (isset($info['dir']))
                {
                    $dir_list = explode('/', $info['dir']);
                    $count = count($dir_list) - 1;
                    $cate_list_key = array_keys($cate_list);
                    for ($i = count($dir_list) - 1; $i > 0; $i--)
                    {
                        if (in_array($dir_list[$i], $cate_list_key))
                        {
                            $select_index = $cate_list[$dir_list[$i]];
                            break;
                        }
                    }
                }
            }
        }

        if ($select_index > 0 || APPLICATION == 'mall')
        {
            $links[$select_index]['selected'] = 1;
        }

        $nav_list['middle'] = $links;
        return $nav_list;
    }

    function fetch($template_file, $cache_id = '')
    {
        $this->assign('lang', $this->lang());
        return parent::fetch($template_file, $cache_id);
    }

    /* private functions */
    /**
     * 实例化模板对象
     *
     * @return  void
     */
    function config_template()
    {
        parent::config_template();

        /* 设置模板文件所在目录 */
        $this->_template->cache_dir = ROOT_PATH . '/temp/caches';
        $this->_template->template_dir  = ROOT_PATH . '/themes';
        $this->_template->compile_dir   = ROOT_PATH . '/temp/compiled';
        $this->_template->cache_lifetime = $this->conf('mall_cache_life'); //缓存时间
    }


    /**
     * 判断缓存是否存在
     *
     * @param string    $cache_id   缓存id
     * @return boolen
     */
    function is_cached($cache_id)
    {
        if (DEBUG_MODE & 1 == 1 || isset($_GET['edit_mode']))
        {
            return false;
        }
        else
        {
            $this->_init_template();
            $this->_template->appoint_cache_id = true;
            $cache_dir = $this->cache_dir($cache_id);

            if (is_dir(ROOT_PATH . '/temp/caches' . $cache_dir) ||
                ecm_mkdir('/temp/caches' . $cache_dir))
            {
                //创建目录成功，重新设置缓存目录
                $this->_template->cache_dir = ROOT_PATH . '/temp/caches' . $cache_dir ;
            }

            if ($this->_template->is_cached('',$cache_id))
            {
                $this->_is_cached = true;
            }

            return $this->_is_cached;
        }
    }

    /**
     * 缓存文件分目录
     *
     * @author wj
     * @param string    $cache_id    缓存id
     * @return string   目录名
     */
    function cache_dir($cache_id)
    {
        $cache_path = md5($cache_id);
        $cache_path = '/' . $cache_path{0} . $cache_path{1} . '/' . $cache_path{2} . $cache_path{3} ;

        return $cache_path;
    }

    /**
     * 取一般变量的crc32_code值
     *
     * @param var  $param
     * @return string   字串值
     */
    function crc32_code($param)
    {
        ksort($param);

        return sprintf("%x", crc32(var_export($param, true)));
    }

    /**
     * 获得指定的语言项
     *
     * @param   string  $key
     *
     * @return  string|array
     */
    function lang($key = '')
    {
        if (!Language::is_loaded($this->_common_lang))
        {
            /* 加载公用语言包 */
            $this->_load_common_lang();
        }

        return parent::lang($key);
    }

    /**
     * 生成页面标题
     *
     * @athor   weberliu
     * @param   void
     * @return  string   页面标题     *
     */
    function make_title()
    {
        if ($this->_action == '')
        {
            $this->_page_title[] = $this->lang('title_sys_msg');
        }
        else
        {
            $lang_key = 'title_' . APPLICATION . '_' . $this->_action;
            if ($this->lang($lang_key) != $lang_key)
            {
                $this->_page_title[] = $this->lang($lang_key);
            }
        }
        //店铺名称
        if ($this->_store_id > 0)
        {
            $this->_page_title[] = $this->conf('store_title', $this->_store_id);
        }
        //商场名称
        $this->_page_title[] = $this->conf('mall_title');

        foreach ($this->_page_title AS $key=>$val)
        {
            $this->_page_title[$key] = htmlspecialchars($val);
        }

        return implode('-', $this->_page_title);
    }

    /**
     * 设置标题
     * @param array 标题数组
     *
     * @return void
     */
    function set_title($meta)
    {
        $this->_page_title = $meta;
    }

    /**
     * 更新页面访问次数
     *
     * @author  scottye
     *
     * @param   int     $store_id
     * @return  void
     */
    function update_pageview($store_id = 0)
    {
        if (is_from_browser())
        {
            include_once(ROOT_PATH . '/includes/manager/mng.pageview.php');
            $mng_pageview = new PageviewManager($store_id);
            $mng_pageview->update_pageview();
        }
    }

    /**
     * 返回页面的keywords
     *
     * @author  wj
     * @param void
     * @return string
     */
    function get_meta_keywords()
    {
        if ($this->_store_id > 0)
        {
            $keywords = $this->conf('store_keywords', $this->_store_id);
        }
        else
        {
            $keywords = $this->conf('mall_keywords');
        }

        if (empty($keywords))
        {
            $keywords = 'ECMALL';
        }

        return $keywords;
    }
};

class MessageBase extends ControllerFrontend {};

?>