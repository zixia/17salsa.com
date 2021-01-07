<?php

/**
 * ECMALL: ����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     *  �������԰�
     *
     *  @access
     */

    var $_common_lang = array('common');

    /**
     *  ʹ�ÿɱ任����ģ������
     *
     *  @access
     */

    var $_use_which_template = 'use_theme';

    /**
     *  ָ����Ϣ����ʱ���õ�ģ��Ŀ¼
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
     * ���캯��
     *
     * @author wj
     * @param string $act
     * @return void
     */
    function ControllerFrontend($act)
    {
        /* ����act */
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

        //����Ƿ�Ҫͬ����¼
        if (!empty($_COOKIE['ECM_USERNAME']) && !empty($_COOKIE['ECM_AUTH']) && strlen($_COOKIE['ECM_AUTH']) == 32)
        {
            $user_info = uc_call('uc_get_user', array($_COOKIE['ECM_USERNAME']));
            $user_info[0] = intval($user_info[0]);
            if ($user_info[0] > 0 && $_COOKIE['ECM_AUTH'] == md5($user_info[0] . ECM_KEY . $user_info[1]))
            {
                //ͬ����¼
                if (!empty($_SESSION['usser_id']))
                {
                    /* ����Ѿ���¼,����ѵ�¼�û���Ϣ */
                    $GLOBALS['sess']->destroy_session();
                    $_SESSION = array();
                }
                include_once(ROOT_PATH . '/includes/models/mod.user.php');
                $mod_user = new User(0);
                $tmp = array('uid'=>$user_info[0], 'username'=>$user_info[1], 'email'=>$user_info[2]);
                $mod_user->set_login($tmp);
            }
        }
        ecm_setcookie('ECM_AUTH', ''); //�����Ƿ�ɹ���¼,������֤��Ĩ��
        parent::ControllerBase($act);
    }

    /**
     * ����ģ�岢��ʾ
     *
     * @author  wj
     * @param string  $template_file  ģ������
     * @param string $template_dir    ģ��·��
     * @param string  $cache_id         ����id
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

        /* ������Ϣ */
        if (!empty($_SESSION['user_id']))
        {
            $this->assign('check_new_pm', 1);
        }

        /* ����ģ������ */
        $this->set_template_path('/' . $template_dir, '/' . $template_dir, TRUE);

        /* ��ظ�ֵ */
        $this->assign('icp_number', $this->conf('mall_icp_number'));
        $this->assign('mall_storeapply', $this->conf('mall_storeapply'));
        $visitor = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';//note ȥ���ο͵�ip��ʾ
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

        /* û����ʱ��ֵ */
        if (empty($cache_id) || ($cache_id && ($this->_is_cached == false)))
        {
            $nav_list = $this->get_nav_list();  // ȡ�õ���


            $this->assign('nav_list',   $nav_list['middle']);
            $this->assign('top_nav',    $nav_list['top']);
            $this->assign('bottom_nav', $nav_list['bottom']);

            /* �������ĵ����� */
            $this->assign('help_center_url', 'index.php?app=article&amp;act=help_center');

            /* ȡ�÷��������б� */
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            $cate_mod = new Category();
            $this->assign('category', $cate_mod->get_options(2));

            /* ȡ�����Źؼ��� */
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
            /* ��� */
            parent::display($template_file, $cache_id);
        }
        else
        {
            header("Content-type:text/html;charset=" . CHARSET, true);
            $this->_template->display_cache();
        }
    }

    /**
     * ���ҳ���ϵĵ�������
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

        /* �жϸ�ѡ˭ */
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
     * ʵ����ģ�����
     *
     * @return  void
     */
    function config_template()
    {
        parent::config_template();

        /* ����ģ���ļ�����Ŀ¼ */
        $this->_template->cache_dir = ROOT_PATH . '/temp/caches';
        $this->_template->template_dir  = ROOT_PATH . '/themes';
        $this->_template->compile_dir   = ROOT_PATH . '/temp/compiled';
        $this->_template->cache_lifetime = $this->conf('mall_cache_life'); //����ʱ��
    }


    /**
     * �жϻ����Ƿ����
     *
     * @param string    $cache_id   ����id
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
                //����Ŀ¼�ɹ����������û���Ŀ¼
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
     * �����ļ���Ŀ¼
     *
     * @author wj
     * @param string    $cache_id    ����id
     * @return string   Ŀ¼��
     */
    function cache_dir($cache_id)
    {
        $cache_path = md5($cache_id);
        $cache_path = '/' . $cache_path{0} . $cache_path{1} . '/' . $cache_path{2} . $cache_path{3} ;

        return $cache_path;
    }

    /**
     * ȡһ�������crc32_codeֵ
     *
     * @param var  $param
     * @return string   �ִ�ֵ
     */
    function crc32_code($param)
    {
        ksort($param);

        return sprintf("%x", crc32(var_export($param, true)));
    }

    /**
     * ���ָ����������
     *
     * @param   string  $key
     *
     * @return  string|array
     */
    function lang($key = '')
    {
        if (!Language::is_loaded($this->_common_lang))
        {
            /* ���ع������԰� */
            $this->_load_common_lang();
        }

        return parent::lang($key);
    }

    /**
     * ����ҳ�����
     *
     * @athor   weberliu
     * @param   void
     * @return  string   ҳ�����     *
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
        //��������
        if ($this->_store_id > 0)
        {
            $this->_page_title[] = $this->conf('store_title', $this->_store_id);
        }
        //�̳�����
        $this->_page_title[] = $this->conf('mall_title');

        foreach ($this->_page_title AS $key=>$val)
        {
            $this->_page_title[$key] = htmlspecialchars($val);
        }

        return implode('-', $this->_page_title);
    }

    /**
     * ���ñ���
     * @param array ��������
     *
     * @return void
     */
    function set_title($meta)
    {
        $this->_page_title = $meta;
    }

    /**
     * ����ҳ����ʴ���
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
     * ����ҳ���keywords
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