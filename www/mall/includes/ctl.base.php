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
 * $Id: ctl.base.php 6028 2008-11-03 09:14:20Z Wj $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include(ROOT_PATH . '/includes/cls.language.php');

class ControllerBase
{
    /* public attributes */
    var $template   = null;
    var $charset    = 'utf-8';

    /* private attributes */
    var $_template  = NULL;
    var $_action    = '';
    var $_store_id  = 0;

    /**
     *  ָ��Ҫʹ�õ�ģ������
     *
     *  @access
     */

    var $_use_which_template = 'use_template';
    var $_message_template_dir = '';

    /* ������֧�ֲ��� */

    /**
     *  ���԰�����·��
     *
     *  @access
     */

    var $_lang_folder = '/languages/%s/';


    /**
     *  �������԰�
     *
     *  @access
     */

    var $_common_lang = array('common');


    /* public functions */
    function __construct($act)
    {
        $this->ControllerBase($act);
    }
    function ControllerBase($act)
    {
        if ($act != 'edit_template')
        {
            $this->_action  = $act;

            if (!empty($act) && !method_exists($this, $act) ||
                empty($act))
            {
                $this->show_warning('invalid_request');
                exit;
            }

            $this->$act();
        }
        $_SESSION['timezone'] = $this->conf('mall_time_zone');
    }

    /**
     * �ض���ָ����URL
     *
     * @param   string  $url
     *
     * @return  void
     */
    function redirect($url)
    {
        $url = strtr(trim($url), array("\r" => '', "\n" => ''));

        header("Location: $url\n", true);
        exit;
    }

    /**
     *  �������԰�����·��
     *
     *  @access
     *  @params
     *  @return
     */

    function get_lang_folder()
    {
        return ROOT_PATH . sprintf($this->_lang_folder, LANG);
    }

    /**
     *  ���ع������԰�
     *
     *  @access public
     *  @params none
     *  @return void
     */

    function _load_common_lang()
    {
        if (empty($this->_common_lang))
        {
            return;
        }
        $lang_files = array();

        foreach ($this->_common_lang as $lang_file)
        {
            $lang_files[] = $this->get_lang_folder() . $lang_file . '.php';
        }

        !empty($lang_files) && Language::load_lang($lang_files);
    }
    /**
     *  ���ص�ǰӦ�ö�Ӧ�������ļ�
     *
     *  @access public
     *  @params array $lang_wrapper
     *  @return none
     */
    function _load_app_lang()
    {
        /* ����APPLICATION���԰� */
        $app_lang = $this->get_lang_folder() . APPLICATION . '.php';
        Language::load_lang(array($app_lang));
    }

    /**
     * ���ָ����������
     *
     * @author  weberliu
     * @param   string  $key
     * @return  string|array
     */
    function lang($key = '')
    {
        return Language::get($key);
    }

    /**
     * ���ָ����������
     *
     * @param   string  $key
     * @param   string  $store_id
     *
     * @return  string
     */
    function conf($key = '', $store_id = 0)
    {
        static $conf = null;

        $store_id = empty($store_id) ? 0 : intval($store_id);

        if (empty($conf[$store_id]))
        {
            include_once(ROOT_PATH . '/includes/manager/mng.conf.php');
            $conf_manager = new ConfManager($store_id);
            $conf[$store_id] = $conf_manager->get_conf();
            if ($store_id > 0)
            {
                include_once(ROOT_PATH . '/includes/models/mod.store.php');
                $mod_store = new Store($store_id);
                $info_store = $mod_store->get_info();
                if ($info_store)
                {
                    $conf[$store_id]['store_name'] = $info_store['store_name'];
                }
            }
        }

        if (!empty($key))
        {
            if (isset($conf[$store_id][$key]))
            {
                return $conf[$store_id][$key];
            }
            else
            {
                trigger_error('Undefined configure key: ' .$key, E_USER_ERROR);
                return false;
            }
        }
        else
        {
            return $conf[$store_id];
        }
    }

    /**
     * ����һ��������ģ�����
     *
     * @param  string   $key
     * @param  mixed    $val
     *
     * @return  void
     */
    function assign($key, $val)
    {
        $this->_init_template();
        $this->_template->assign($key, $val);
    }

    /**
     *  ���
     *
     *  @access
     *  @params
     *  @return
     */

    function display($template_file, $cache_id = '')
    {
        /* ��� */
        $this->_init_template();
        header("Content-type:text/html;charset=" . CHARSET, true);

        $this->_template->display($template_file, $cache_id);
        // ob_end_flush();
    }

    function fetch($template_file, $cache_id = '')
    {
        /* ��� */
        $this->_init_template();
        return $this->_template->fetch($template_file, $cache_id);

    }

    function is_cached($template_file, $cache_id = '')
    {
        /* ��� */
        $this->_init_template();

        return $this->_template->is_cached($template_file, $cache_id);
    }

    /**
     *  ��ʼ��ģ������
     *
     *  @access
     *  @params
     *  @return
     */
    function _init_template()
    {
        if ($this->_template === NULL)
        {
            $use_template_func = $this->_use_which_template;
            method_exists($this, $use_template_func) && $this->$use_template_func();
        }
    }

    /**
     * ʵ����ģ�����
     *
     * @return  void
     */
    function use_template()
    {
        if ($this->_template === NULL)
        {
            include_once(ROOT_PATH . '/includes/cls.template.php');
            $this->_template = new ecsTemplate;

            /* ���ø����� */
            $this->config_template();
        }
    }

    /**
     *  ʹ�ÿɱ任����ģ������
     *
     *  @access public
     *  @params none
     *  @return void
     */
    function use_theme()
    {
        if ($this->_template === NULL)
        {
            include_once(ROOT_PATH . '/includes/cls.template.php');
            include_once(ROOT_PATH . '/includes/cls.themes.php');
            $this->_template = new Theme();
            $this->_template->cache_dir = ROOT_PATH . '/temp/caches';

            /* ����ģ������ */
            $this->config_template();
        }
    }

    function set_store($store_id)
    {
        $this->_init_template();
        $this->_store_id = $store_id;
        $this->_template->store_id = $store_id ? $store_id : 0;
    }

    /**
     *  ����ģ��
     *
     *  @access public
     *  @params none
     *  @return void
     */
    function config_template()
    {
        $options = array();
        $options['mall_time_format_simple'] = $this->conf('mall_time_format_simple');
        $options['mall_skin'] = $this->conf('mall_skin');
        $options['controller'] = get_class($this);

        $this->_template->options        = $options;
        $this->_template->caching       = ((DEBUG_MODE & 1) == 0);  // �Ƿ񻺴�
        $this->_template->force_compile = ((DEBUG_MODE & 2) == 2);  // �Ƿ���Ҫǿ�Ʊ���
        $this->_template->direct_output = ((DEBUG_MODE & 4) == 4);  // �Ƿ�ֱ�����
        $this->_template->gzip          = $this->gzip_enabled();
    }

    /**
     *  ����Դģ�弰����·��
     *
     *  @access public
     *  @params string $src_path[, string $compiled_path[, Bool $superaddition]]
     *  @return void
     */
    function set_template_path($src_path, $compile_path = NULL, $superaddition = FALSE)
    {
        if (!$superaddition)
        {
            $this->_template->template_dir = $src_path;
            if ($compile_path !== NULL)
            {
                $this->_template->compile_dir = $compile_path;
            }
        }
        else
        {
            $this->_template->template_dir .= $src_path;
            if ($compile_path !== NULL)
            {
                $this->_template->compile_dir .= $compile_path;
            }
        }
    }

    /**
     *  ����ģ�����滺��
     *
     *  @access public
     *  @params Bool $caching
     *  @return void
     */
    function enable_cache()
    {
        $this->_init_template();
        $this->_template->caching = TRUE;
    }

    /**
     * ��ʽ��һ���ַ���
     *
     * @param   string  $key
     *
     * @return  string
     */
    function str_format($key)
    {
        $arr = func_get_args();
        $arr[0] = $this->lang($key);

        return call_user_func_array('sprintf', $arr);
    }

    /**
     * send a system notice message
     *
     * @author wj
     * @param string $msg
     * @return void
     */
    function show_message ($msg)
    {
        $a = $this->_trigger_message(func_get_args());

        $this->_message(serialize($a), E_USER_NOTICE);
    }

    /**
     * send a system warning message
     *
     * @param string $msg
     */
    function show_warning ($msg)
    {
        $this->logger = false;
        $a = $this->_trigger_message(func_get_args());

        $this->_message(serialize($a), E_USER_WARNING);
    }

    /**
     * send a system message
     *
     * @author  weberliu
     * @param   string  $msg
     * @param   int     $type
     */
    function _message($msg, $type)
    {
        $msg = new Message($msg, $type);
        $msg->display();
    }

    /**
     * Make a error message by JSON format
     *
     * @param   string  $msg
     *
     * @return  void
     */
    function json_error ($msg='', $retval=null)
    {
        $this->logger = false;
        if (!empty($msg))
        {
            $msg = $this->lang($msg);
        }
        $result = array('done' => false , 'msg' => $msg);
        if (isset($retval)) $result['retval'] = $retval;

        $this->json_header();
        echo ecm_json_encode($result);
    }

    /**
     * Make a successfully message
     *
     * @param   mixed   $retval
     * @param   string  $msg
     *
     * @return  void
     */
    function json_result ($retval = '', $msg = '')
    {
        if (!empty($msg))
        {
            $msg = $this->lang($msg);
        }

        $this->json_header();
        echo ecm_json_encode(array('done' => true , 'msg' => $msg , 'retval' => $retval));
    }

    /**
     * Send a Header
     *
     * @author weberliu
     *
     * @return  void
     */
    function json_header()
    {
        if ($this->gzip_enabled())
        {
             ob_start('ob_gzhandler');
        }

        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }

    /**
     * get page number for current request
     *
     * @return  int
     */
    function get_page()
    {
        $page = 0;
        if (isset($_GET['page']))
        {
            $page = intval($_GET['page']);
        }

        if ($page <= 0)
        {
            $page = 1;
        }

        return $page;
    }

    /**
     *  UBB�༭��
     *
     *  @author liupeng
     *  @param  string  $content     �ֶ���
     *  @param  string  $editor_type �༭������
     *  @return void
    */
    function get_editor_content($content, $editor_type = null)
    {
        include_once(ROOT_PATH . '/includes/lib.editor.php');

        $content = trim($content);
        $editor_type = $editor_type === null ? intval($this->conf('mall_editor_type')) : intval($editor_type);

        if ($editor_type === 0)
        {
            $content = htmlspecialchars($content);
        }
        else
        {
            $content= Editor::check_html($content);
        }
        return $content;
    }

    /**
     *  �����༭��
     *
     *  @author liupeng
     *  @param  string  $field_name     �ֶ���
     *  @param  string  $value          ����
     *  @param  int     $width          ���
     *  @param  int     $height         �߶�
     *  @param  string  $php_assign_key ģ�������
     *  @param  string  $editor_type    �༭������ 0ΪbbCode 1ΪHTML
     *  @return void
     */
    function build_editor($field_name, $value = '', $width = '500', $height = '400', $php_assign_key = 'rich_editor', $allow_attach = 'true', $attachs = array(), $editor_type = null)
    {
        $width = strpos($width, '%') === false ? $width.'px' : $width;
        $height = strpos($height, '%') === false ? $height.'px' : $height;
        $code = "";
        if ($attachs)
        {
            $attachs_json = ecm_json_encode($attachs);
            $code = "\r\nvar files = $attachs_json;";
            $code .= "\r\nfor(var i=0;i<files.length; i++) {";
            $code .= "addFileItem(files[i]);}";
        }

        //ϵͳĬ��ֵ
        $bbcode = intval($this->conf('mall_editor_type')) == 0 ? 'true' : 'false';
        $need_change = 'false';
        
        if ($editor_type !== null)
        {
            $editor_type = intval($editor_type);
            //���������ò�һ����Ҫת��
            if ($editor_type != intval($this->conf('mall_editor_type')))
            {
                $need_change = 'true';
            }
        }
        
        //laopeng add it, maybe usefull
        if ($bbcode == 'false')
        {
            $value = str_replace('&', '&amp;', $value);
        }

        /*
        $editor_type = $editor_type === null ? intval($this->conf('mall_editor_type')) : intval($editor_type);
        if ($editor_type === 0)
        {
            $bbcode = 'true';
        }
        elseif ($editor_type == 1)
        {
            $bbcode = 'false';
            $value = str_replace('&', '&amp;', $value);
        }
        elseif ($this->conf('mall_editor_type') == 0)
        {
            $bbcode = 'true';
        }
        */
        $editor_html_code = '<script type="text/javascript" src="editor/lang.php"></script><script type="text/javascript" src="editor/niceditor.js"></script><script type="text/javascript" src="editor/bbcode.js"></script><script>Event.observe(window, "load", function() { try{new nicEditor({fullPanel : true, bbCode: '.$bbcode.',allowUpload:'.$allow_attach.',needChange:'.$need_change.'}).panelInstance("'. $field_name .'");}catch(ex){var str = "";for(key in ex){str += key +"="+ex[key]+"\r\n";}alert(str);}'.$code.'});</script><textarea id="'.$field_name.'" name="'.$field_name.'" style="width:'. $width .';height:'. $height .';">'.$value.'</textarea>';

        $this->assign($php_assign_key, $editor_html_code);
    }

    /**
     * build a system message
     *
     * @param array $arr
     * @return array
     */
    function _trigger_message ($arr)
    {
        if (count($arr) < 2) {
            $arr[] = $this->lang('go_back');
        }
        if (count($arr) < 3) {
            $arr[] = 'javascript:history.back()';
        }

        $m = (empty($arr[0])) ? '' : $this->lang($arr[0]);
        $a = array('content' => $m, 'links' => array());
        $n = count($arr);
        for ($i = 1; $i < $n; $i += 2) {
            $href = (($i + 1) >= $n) ? 'javascript:history.back()' : $arr[$i + 1];
            //$redirect = (($i + 2) >= $n) ? false : $arr[$i + 2];
            $a['links'][] = array('href' => $href , 'text' => $this->lang($arr[$i]));
        }

        return $a;
    }

    /**
     * respond js language for this controller
     *
     * @access  public
     *
     * @return void
     */
    function jslang()
    {
        $jslang = $this->lang('js');
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        header("Expires: " .date(DATE_RFC822, strtotime("+1 hour")). "\n");
        if (is_array($jslang))
        {
            die('var lang = ' . ecm_json_encode($jslang) . ';');
        }
        else
        {
            die('var lang = null;');
        }
    }

    /**
     * destory this object
     *
     * @return  void
     */
    function destory()
    {}

    /**
     * assign query info
     *
     * @author wj
     * @param   void
     * @return  void
     */
    function _assign_query_info()
    {
        if (defined('PAGE_STARTED'))
        {
            if (PHP_VERSION >= '5.0.0')
            {
                $query_time = microtime(true) - PAGE_STARTED;
            }
            else
            {
                list($now_usec, $now_sec)     = explode(' ', microtime());
                list($start_usec, $start_sec) = explode(' ', PAGE_STARTED);
                $query_time = ($now_sec - $start_sec) + ($now_usec - $start_usec);
            }
        }
        else
        {
            $query_time = 0.005;
        }

        $this->assign('query_time', $query_time);
        $this->assign('query_count', $GLOBALS['db']->_query_count);
        $this->assign('query_user_count', is_object($GLOBALS['sess']) ? $GLOBALS['sess']->get_users_count() : 0);
        /* �ڴ�ռ����� */
        if (function_exists('memory_get_usage'))
        {
            $this->assign('memory_info', memory_get_usage() / 1048576);
        }

        $this->assign('gzip_enabled', $this->gzip_enabled());

        $this->assign('site_domain', urlencode(get_domain())); // ��ǰ����
        $this->assign('ecm_version', VERSION . ' ' . RELEASE); // ��ǰ����
    }

    /**
     * �����ʼ�����
     * @param string $to Ŀ��email
     * @param string $mail_template �ʼ�ģ��
     * @param array  $values �ʼ�����
     * @param string $subject �ʼ�����
     * @param string $message �ʼ�����
     *
     * @return void
     */
    function send_mail($to, $mail_template = '', $values = array(), $subject = '', $message = '')
    {
        /* �����ʼ����� */
        include_once(ROOT_PATH. '/includes/cls.mail_queue.php');
        $mail_protocol = ($this->conf('mall_email_type') != 'smtp') ? MAIL_PROTOCOL_LOCAL : MAIL_PROTOCOL_SMTP;
        $mailer = new MailQueue($this->conf('mall_name'), $this->conf('mall_email_addr'), $mail_protocol,
            $this->conf('mall_email_host'), $this->conf('mall_email_port'), $this->conf('mall_email_id'),
            $this->conf('mall_email_pass'));

        if (empty($subject) && empty($message))
        {
            /* ��÷�����ӹ���Ա�ʼ�ģ�� */
            include_once(ROOT_PATH. '/includes/manager/mng.mailtemplate.php');
            $manager    = new MailTemplateManager();
            $template   = $manager->get_template($mail_template);

            if ($template == false)
            {
                $this->show_warning('missing_mail_template');
                return false;
            }

            /* ����һ���µ�ģ���� */
            include_once(ROOT_PATH. '/includes/cls.template.php');
            $tpl = new ecsTemplate();
            $tpl->direct_output = true;

            foreach ($values as $key => $value)
            {
                $tpl->assign($key, $value);
            }
            if (!isset($values['site_url']))
            {
                $tpl->assign('site_url', site_url());
            }
            if (!isset($values['mall_name']))
            {
                $tpl->assign('mall_name', $this->conf('mall_name'));
            }
            if (!isset($values['sent_date']))
            {
                $tpl->assign('sent_date', local_date('Y-m-d H:i'));
            }

            include_once(ROOT_PATH . '/includes/lib.editor.php');
            $template['content'] = Editor::parse($template['content']);
            $message = $tpl->fetch("str:$template[content]");
            $subject = $tpl->fetch("str:$template[subject]");
        }

        $mailer->add($to, CHARSET, $subject, $message, MAIL_PRIORITY_LOW);

        //��ҳ���м��봥������
        if (!defined('SEND_MAIL'))
        {
            define('SEND_MAIL', 1);
        }

        return true;
    }

    /**
     * �ж��Ƿ�����gzipѹ��
     *
     * @return boolean
     */
    function gzip_enabled()
    {
        static $enabled_gzip = NULL;

        if ($enabled_gzip === NULL)
        {
            $enabled_gzip = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1 && function_exists('ob_gzhandler'));
        }

        return $enabled_gzip;
    }

    /**
     *    �����ѯ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function clean_cache()
    {
        //�����ݸı�Ҫ������ļ�
        $cache_dirs = array(ROOT_PATH . '/temp/caches', ROOT_PATH . '/temp/query_caches');
        foreach ($cache_dirs as $cache_dir)
        {
            $d = @dir($cache_dir);
            if($d)
            {
                while (false !== ($entry = $d->read()))
                {
                    if($entry!='.' && $entry!='..' && $entry != '.svn' && $entry != 'index.html')
                    {
                       $entry = $cache_dir.'/'.$entry;
                       ecm_rmdir($entry);
                    }
                }
                $d->close();
             }
        }
    }
};

?>
