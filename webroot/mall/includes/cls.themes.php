<?php

/**
 * ECMALL: ģ����չ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.themes.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class Theme extends ecsTemplate
{
    var $_currentNode    = null;
    var $_currentLibrary = null;
    var $config          = null;
    var $doc_type        = '';
    var $store_id        = 0;
    var $edit_mode       = false;

    function __construct()
    {
        $this->Theme();
    }

    function Theme()
    {
        parent::__construct();
    }

    /**
     * ��⵱ǰģ��༭״̬
     *
     * author  liupeng
     * return  void
     */
    function check_edit_mode()
    {
        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0 && isset($_GET['edit_mode']))
        {
            require_once(ROOT_PATH . '/includes/models/mod.adminuser.php');

            $admin = new AdminUser($_SESSION['admin_id'], $_SESSION['store_id']);
            /* ���û��Ȩ�� */
            if (!$admin->check_priv('template', 'edit'))
            {
                return false;
            }

            $mall_allowed  = array('homepage', 'goods_list');
            $store_allowed = array('homepage', 'goods', 'goods_list');

            /* ��������̹���Ա��¼ */
            if ($_SESSION['store_id'] > 0 && $_SESSION['store_id'] == $this->store_id)
            {
                if (basename($this->template_dir) == 'store' && in_array($this->pagename, $store_allowed))
                {
                    return true;
                }
            }
            /* �������վ����Ա��¼ */
            else if(!empty($_SESSION['admin_id']) && empty($_SESSION['store_id']))
            {
                if (basename($this->template_dir) == 'mall' && in_array($this->pagename, $mall_allowed))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * ��ʾģ��
     *
     * author  liupeng
     * param   string  $filename   �ļ���
     * return  void
     */
    function display($pagename, $cache_id = '')
    {
        include_once(ROOT_PATH . '/includes/models/mod.page.php');
        $this->pagename = $pagename;
        $editable = false;
        if ($this->check_edit_mode())
        {
            $this->direct_output = true;
            $this->force_compile = true;
            $this->caching = false;
            $editable = true;

            $lang = Language::load_lang(lang_file("admin/template"));
            $this->assign('lang', $lang);

            $this->assign('editing', true);
            $this->assign('pagename', $this->pagename);

            $template_data_file = ROOT_PATH . '/temp/tdata/'. $this->pagename . '_' . intval($_SESSION['store_id']) .'.php';

            if (!is_dir(ROOT_PATH . '/temp/tdata/'))
            {
                ecm_mkdir('/temp/tdata');
            }
            $str = "<?php\r\n";
            $str .= '$tempate_data = ' . var_export($this->_var , true);
            $str .= "\r\n?>";
            file_put_contents($template_data_file, $str, LOCK_EX);
            $this->edit_mode = true;
        }

        $this->_page = new Page($pagename, $this->store_id, $editable);

        $this->_page->current_skin = $this->skin;
        $this->_page->mall_skin    = $this->options['mall_skin'];
        $this->_page->controller_name = $this->options['controller'];

        $this->_current_file   = $this->_page->filename;

        /* ���������ҳ�������ñ�����Ŀ¼ */
        if (!empty($this->store_id))
        {
            $this->compile_sub_dir .= $this->compile_dir . '/' . md5($pagename . $this->_page->filename . $this->skin);
        }
        $this->assign('store_id', $this->store_id);
        $this->assign_custom_data();

        $out = $this->fetch($pagename, $cache_id);

        if (strpos($out, $this->_echash) !== false)
        {
            $k = explode($this->_echash, $out);
            foreach ($k AS $key => $val)
            {
                if (($key % 2) == 1)
                {
                    $k[$key] = $this->insert_mod($val);
                }
            }
            $out = implode('', $k);
        }

        error_reporting($this->_errorlevel);
        $this->_seterror--;

        echo $out;
    }

    /**
     * �����Զ���ģ������
     *
     * author  liupeng
     * return  void
     */
    function assign_custom_data()
    {
        $data = array();
        foreach($this->_page->config AS $key => $value)
        {
            if(empty($value['denyEdit']) && empty($value['integrate']))
            {
                foreach($value['children'] AS $k => $v)
                {
                    if (isset($v['type']) && $v['mtype'] == 'cm')
                    {
                        $id = str_replace('cm_', '', $k);
                        $data[$id] = $this->get_module_data($v);
                        $data[$id]['name'] = $v['name'];
                    }
                }
            }
        }

        $this->assign('custom_module', $data);
    }

    /**
     * ��ȡԤ�������
     *
     * author  liupeng
     * return  void
     */
    function _get_code()
    {
        return $this->_page->get_html();
    }

    /**
     * �������ļ�
     *
     * @param   string      $pagename
     * @param   sting       $cache_id
     *
     * @return  sring
     */
    function fetch($pagename, $cache_id = '')
    {
        if(strpos($pagename, "resource/") !== false || $pagename == '')
        {
            return parent::fetch($pagename);
        }

        if (!$this->_seterror)
        {
            error_reporting(E_ALL ^ E_NOTICE);
        }

        $this->_seterror++;

        if (strncmp($pagename,'str:', 4) == 0)
        {
            $out = parent::_eval(parent::fetch_str(substr($pagename, 4)));
        }
        else
        {
            if ($this->direct_output)
            {
                $code = $this->_get_code();
                $out = parent::_eval(parent::fetch_str($code));
            }
            else
            {
                if (!in_array($filename, $this->template))
                {
                    $this->template[] = $this->_current_file;
                }

                $out = $this->make_compiled($pagename);

                if ($cache_id)
                {
                    if ($this->appoint_cache_id)
                    {
                        $cachename = $cache_id;
                    }
                    else
                    {
                       $cachename = $pagename .'_'. $this->store_id . '_' . $cache_id;
                    }

                    $data = serialize(array('template' => $this->template, 'expires' => $this->_nowtime + $this->cache_lifetime, 'maketime' => $this->_nowtime));

                    $out = str_replace("\r", '', $out);

                    while (strpos($out, "\n\n") !== false)
                    {
                        $out = str_replace("\n\n", "\n", $out);
                    }

                    if (file_put_contents($this->cache_dir . '/' . $cachename . '.php', '<?php exit;?>' . $data . $out, LOCK_EX) === false)
                    {
                        trigger_error('can\'t write:' . $this->cache_dir . '/' . $cachename . '.php');
                    }
                    $this->template = array();
                }
            }
        }

        $this->_seterror--;
        if (!$this->_seterror)
        {
            error_reporting($this->_errorlevel);
        }

        return $out; // ����html����
    }

    /**
     * ����ģ�庯��
     *
     * author  liupeng
     * param   string  $pagename  ҳ������
     * return  string  ������ļ���ַ
     */
    function make_compiled($pagename)
    {
        /* �����resouceĿ¼�µ��ļ��ͽ������������� */
        if(strpos($pagename, "resource/")!==false)
        {
            return parent::make_compiled($pagename);
        }

        $name  = isset($this->compile_sub_dir) ? $this->compile_sub_dir : $this->compile_dir;
        $name .= '/' . $pagename . '.layout.php';

        $exists = is_file($name);

        if ($this->_expires)
        {
            $expires = $this->_expires - $this->cache_lifetime;
        }
        else
        {
            if ($exists)
            {
                $filestat = @stat($name);
                $expires  = $filestat['mtime'];
            }
            else
            {
                $expires = 0;
            }
        }

        $filestat = @stat($this->_current_file);

        if ($filestat['mtime'] <= $expires && !$this->force_compile)
        {
            if (is_file($name))
            {
                $source = $this->_require($name);

                if ($source == '')
                {
                    $expires = 0;
                }
            }
            else
            {
                $source = '';
                $expires = 0;
            }
        }

        if ($this->force_compile || $filestat['mtime'] > $expires)
        {
            $content = $this->_get_code();

            $source = $this->fetch_str($content);

            if (!empty($this->compile_sub_dir))
            {
                $dirname = str_replace(ROOT_PATH, '', $this->compile_sub_dir);
                $name = $this->compile_sub_dir . "/" . basename($name);
                ecm_mkdir($dirname);
            }

            if (file_put_contents($name, $source, LOCK_EX) === false)
            {
                trigger_error('can\'t write:' . $name);
            }

            $source = $this->_eval($source);
        }

        return $source;
    }

    /**
     * ��ȡ�Զ���ģ������
     *
     * @author      wj
     * @param       string      $type           ����
     * @param       array       $config         ������Ϣ
     *
     * @return  string
     */
    function get_module_data($info)
    {
        require_once(ROOT_PATH. '/includes/manager/mng.goods.php');

        $type = $info['type'];
        $config = $info['conf'];
        switch($type)
        {
            case '0' :
                $gm = new GoodsManager();
                $res = array();
                //��Ȩ������
                $_GET['sort'] = 'sort_weighing';
                $_GET['order'] = 'ASC';
                $img_count = isset($config['ic']) ? intval($config['ic']) : 0;
                $word_count = isset($config['wc']) ? intval($config['wc']) : 0;
                $hot_count = isset($config['hc']) ? intval($config['hc']) : 0;
                if ($img_count > 0)
                {
                    $image_goods = $gm->get_list(1, array('mall_cate_id'=> $config['c'], 'is_mi_best'=>1, 'sell_able'=>1), $img_count);
                    $res['image_goods'] = $image_goods['data'];
                }

                if ($word_count > 0)
                {
                    $word_goods = $gm->get_list(1, array('mall_cate_id'=> $config['c'], 'is_mw_best'=>1, 'sell_able'=>1), $word_count);
                    $res['word_goods'] = $word_goods['data'];
                }

                if ($hot_count > 0)
                {
                    $hot_goods = $gm->get_list(1, array('mall_cate_id'=> $config['c'], 'is_m_hot'=>1, 'sell_able'=>1), $hot_count);
                    $res['hot_goods'] = $hot_goods['data'];
                }

                //�������
                unset($_GET['sort']);
                unset($_GET['order']);
                $res['title_fontcolor']       = $config['tfc'];
                $res['title_backgroundcolor'] = $config['tbgc'];
                $res['title_backgroundimage'] = $config['tbgimg'];

                $res['content_fontcolor']       = $config['cfc'];
                $res['content_backgroundcolor'] = $config['cbgc'];
                $res['content_backgroundimage'] = $config['cbgimg'];
                $res['bottom_fontcolor']       = $config['bfc'];
                $res['bottom_backgroundcolor'] = $config['bbgc'];
                $res['bottom_backgroundimage'] = $config['bbgimg'];
            break;

            case '1' :
                $store_id = intval($info['store_id']);
                $gm = new GoodsManager($store_id);
                $res = $gm->get_list(1, array('store_cate_id'=> $config['c'], 'is_s_best' => 1, 'sell_able' => 1), 4);
            break;
        }
        return $res;
    }

}

?>
