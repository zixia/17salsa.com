<?php
/**
 * ECMALL: 模板管理控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.template.php 6036 2008-11-04 10:13:21Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.ad_position.php');
include_once(ROOT_PATH . '/includes/models/mod.page.php');

class TemplateController extends ControllerBackend
{
    var $pagename = null;

    function __construct($act)
    {
        //sleep(3);
        $this->TemplateController($act);
    }

    function TemplateController($act)
    {
        if (strpos($_POST['pagename'], '..') !== false || strpos($_POST['skin'], '..') !== false  || strpos($_POST['file'], '..') !== false)
        {
            $this->json_error('hacking');
            return;
        }

        $this->store_id = isset($_SESSION['store_id']) ? intval($_SESSION['store_id']) : 0;

        $this->_template_dir = ROOT_PATH . '/themes/' . ($this->store_id == 0 ? 'mall' : 'store');

        if (isset($_POST['pagename']))
        {
            $this->pagename = trim($_POST['pagename']);
        }

        if (empty($act))
        {
            $act = 'view';
        }

        if ($act != "edit" && $act != "view")
        {
            if (isset($_POST['pagename']))
            {
                $pagename = trim($_POST['pagename']);
                $this->_page = new Page($pagename, $this->store_id);
            }
            $skin_conf_key   = $this->store_id == 0 ? 'mall_skin' : 'store_skin';
            $skin            = $this->conf($skin_conf_key, $this->store_id);
            $this->skin      = empty($skin) ? 'default' : $skin;
        }

        parent::__construct($act);
    }


    /**
     * 查看模板列表
     *
     * author  liupeng
     * return  void
     */
    function view()
    {
        $this->logger = false;
        $page_list = $this->_get_page();
        $this->assign('list', $page_list);
        $this->display('template.view.html');
    }

    /**
     * 获取所有可编辑页面
     *
     * author Garbin
     * return  void
    */
    function _get_page()
    {
        if ($this->store_id == 0)
        {
            $page_list = array('homepage', 'goods_list');
        }
        else
        {
            $page_list = array('homepage', 'goods', 'goods_list');
        }

        $sql = "SELECT filename, pagename FROM `ecm_templates` WHERE " .
               db_create_in($page_list, 'pagename') . " AND store_id = '$this->store_id'";

        $page_list = array_flip($page_list);
        foreach($page_list AS $key => $value)
        {
            $page_list[$key] = array('src' => 'themes/'. basename($this->_template_dir) . '/layout/'.$key.'/default.layout.gif');
        }

        $res = $GLOBALS['db']->query($sql);
        while (($row = $GLOBALS['db']->fetchRow($res)) !== false)
        {
            $dir = str_replace(ROOT_PATH . '/', '', $this->_template_dir).'/layout/'.$row['pagename'].'/'.$row['filename'] . '.gif';

            $page_list[$row['pagename']] = array('src' => $dir);
        }

        foreach ($page_list as $key => $value)
        {
            $page_list[$key]['edit_url'] = $this->_get_edit_url($key);
        }

        return $page_list;
    }

    /**
     * 跳转到编辑状态
     *
     * author  Garbin
     * return  string
     */
    function _get_edit_url($page)
    {
        if (empty($_SESSION['store_id']))
        {
            switch($page)
            {
                case 'homepage':
                    return 'index.php?app=mall&edit_mode=1';
                break;

                case 'goods_list':
                    $cate_id = $GLOBALS['db']->getOne("SELECT cate_id FROM `ecm_category` WHERE store_id=0 LIMIT 1");
                    return 'index.php?app=category&edit_mode=1&mall_cate_id=' . $cate_id;
                break;
            }
        }
        else
        {
            switch($page)
            {
                case 'homepage':
                    return 'index.php?app=store&edit_mode=1&store_id='.$this->store_id;
                break;

                case 'goods_list':
                    $cate_id = $GLOBALS['db']->getOne("SELECT cate_id FROM `ecm_category` WHERE store_id='$this->store_id' LIMIT 1");
                    return 'index.php?app=store&edit_mode=1&cate_id='.$cate_id . '&store_id='.$this->store_id;
                break;

                case 'goods':
                    $goods_id = $GLOBALS['db']->getOne("SELECT goods_id FROM `ecm_goods` WHERE is_on_sale=1 AND store_id='$this->store_id' LIMIT 1");

                    return 'index.php?app=goods&edit_mode=1&id='.$goods_id;
                break;
            }
        }

        return '';
    }

    /**
    * 更新数据
    *
    * author  liupeng
    * return  void
    */
    function _update()
    {
        $this->_page->update();

        $skin_conf_key   = $this->store_id == 0 ? 'mall_skin' : 'store_skin';
        $skin            = $this->conf($skin_conf_key, $this->store_id);

        if ($skin != $this->skin)
        {
            $sql = "UPDATE `ecm_config_value` SET value='$this->skin' WHERE code='$skin_conf_key' AND store_id='$this->store_id'";
            $GLOBALS['db']->query($sql);
        }

        clean_cache();
    }

    /**
    * 更新页面模块位置
    *
    * @author  liupeng
    * @return  void
    */
    function update_template($no_json = false)
    {
        if (isset($_POST['config']))
        {
            $config = $_POST['config'];
            // 布局文件里的默认设置
            $new_config = $def_config = $this->_page->get_default_config();

            foreach($config AS $key => $children)
            {
                $children = is_array($children) ? $children : array();
                $newChildren = array();
                foreach($children AS $name)
                {
                    if (strpos($name ,'adp_') !== false)
                    {
                        $newChildren[$name] = array('id' => $name,
                                                   'mtype' => 'ad',);
                    }
                    elseif (strpos($name ,'cm_') !== false)
                    {
                        $id  = substr($name, 3);
                        $sql = "SELECT type, name, config, store_id FROM `ecm_custom_modules` WHERE id='$id'";
                        $res = $GLOBALS['db']->getRow($sql);

                        $arr = array('id'      => $name,
                                    'mtype'    => 'cm',
                                    'type'     => $res['type'],
                                    'name'     => $res['name'],
                                    'store_id' => $res['store_id'],
                                    'conf'     => @unserialize($res['config'])
                                    );

                        $newChildren[$name] = $arr;
                    }
                    else
                    {
                        $newChildren[$name] = $this->_find_child($name, $def_config);
                    }
                }

                $new_config[$key]['children'] = $newChildren;
            }

            unset($def_config);
            $this->_page->config = $new_config;
        }

        if (isset($_POST['skin']))
        {
            $this->skin = trim($_POST['skin']);
        }

        $this->_update();
        if (!$no_json)
        {
            $this->json_result('succeed', 'ok');
        }
    }

    /**
    * 查找配置中的模块配置项
    *
    * author  liupeng
    * return  array
    */
    function _find_child($name, $config)
    {
        foreach($config AS $key => $children)
        {
            if (isset($config[$key]['children'][$name]))
            {
                $temp = $config[$key]['children'][$name];
                $temp['parent'] = $key;
                return $temp;
            }
        }

        return null;
    }

    /**
    * 获取自定义模块
    *
    * author  liupeng
    * return  array
    */
    function get_custom_modules()
    {
        $sql = "SELECT id, name FROM `ecm_custom_modules` WHERE store_id='$this->store_id'";
        $res = $GLOBALS['db']->getAll($sql);
        $this->json_result(ecm_json_encode($res), 'ok');
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
        header("Content-type:text/html;charset=" . CHARSET, true);
    }

    /**
    * 获取模块列表
    *
    * author  liupeng
    * return  void
    */
    function get_modules()
    {
        Language::load_lang(lang_file('common'));

        $config  = $this->_page->get_default_config();

        foreach($config AS $key => $value)
        {
            if(empty($value['denyEdit']) && empty($value['integrate']))
            {
                $tmp[$key] = array();

                foreach($value['children'] AS $k => $v)
                {
                    $name = $this->lang($k);
                    if ($k == 'no_goods_module')
                    {
                        continue;
                    }
                    $tmp[$key][$k] = $name;
                }
            }
        }
        $this->json_result(ecm_json_encode($tmp), 'ok');
    }


    /**
    * 获取广告位列表
    *
    * @author  liupeng
    * @return  void
    */
    function get_ads()
    {
        $this->logger = false;
        $mng = new AdPositionManager();
        $list = $mng->get_list(1, null, $mng->get_count(null));

        $this->json_result($list['data'], 'ok');
    }

    /**
     * 创建模块
     *
     * @author  liupeng
     * @return  void
    */
    function create_module()
    {
        require_once(ROOT_PATH. "/includes/cls.template.php");
        require_once(ROOT_PATH. "/includes/cls.themes.php");
        require_once(ROOT_PATH. "/includes/lib.common.php");

        Language::load_lang(lang_file('common'));

        $template = new ecsTemplate();

        $module = $_POST['module'];
        $dir =  $this->store_id == 0 ? 'mall' : 'store';
        if (isset($_POST['custom_module']))
        {
            $this->_init_template();
            $id = intval($_POST['module_id']);
            $html = $this->_template->get_custom_module($id, $dir);

            $sql = "SELECT store_id, type, name, config FROM `ecm_custom_modules` WHERE id='$id'";
            $res = $GLOBALS['db']->getRow($sql);
            $res['conf'] = @unserialize($res['config']);

            $module_data = Theme::get_module_data($res);
            $tmp[$id] = $module_data;
            $tmp[$id]['name'] = $res['name'];
            $template->assign('custom_module', $tmp);
        }
        else
        {
            $def = $this->_page->get_default_config();
            $res = ($this->_find_child($module, $def));
            $src = $res['src'];

            $data = trim($_POST['data']);
            $template_data_file = ROOT_PATH . "/temp/tdata/" . $this->pagename .'_'. $this->store_id . '.php';

            if (is_file($template_data_file))
            {
                require($template_data_file);
                $template->_var = $tempate_data;
            }

            $filename = ROOT_PATH. '/' .$src;
            $html = file_get_contents($filename);
        }

        $template->skin = $this->skin;
        $template->template_dir  = $this->_template_dir;
        $template->direct_output = true;
        $source = $template->fetch("str:$html");
        $source = $template->smarty_prefilter_preCompile($source);

        header("Content-type:text/html;charset=" . CHARSET, true);

        $this->json_result($source, 'ok');
    }

    /**
    * 获取布局列表
    *
    * @author  liupeng
    * @return  void
    */
    function get_layouts()
    {
        $this->logger = false;
        $pagename = $_POST['pagename'];

        $dir_path .= $this->_template_dir . "/layout/$pagename";

        $layout_list = array();
        $dir = 'themes/'. basename($this->_template_dir) . '/layout/'.$pagename;
        if (is_dir($dir_path))
        {
            $d = dir($dir_path);
            while (false !== ($entry = $d->read()))
            {
                if(file_ext($entry) == 'layout')
                {
                    $layout_list[] = array('filename'=>$entry, 'image'=> $dir .'/'.$entry.'.gif');
                }
            }
            $d->close();
            $this->json_result(ecm_json_encode($layout_list), 'ok');
        }
        else
        {
            $this->json_error('no layout');
        }
    }

    /**
    * 设置布局
    *
    * @author  liupeng
    * @return  void
    */
    function set_layout()
    {
        if (isset($_POST['file']))
        {
            $filename = trim($_POST['file']);
            $filename = $this->_page->layout_path . '/' . str_replace('.', '', $filename) . '.layout';
            if (!is_file($filename))
            {
                $this->json_error('file not found!');
                return;
            }
            $this->_page->filename = $filename;
            $this->_page->config   = $this->_page->get_default_config();
            $this->_update();
            $this->json_result('', 'ok');
        }
    }

    /**
    * 获取皮肤列表
    *
    * @author  liupeng
    * @return  void
    */
    function get_skins()
    {
        $dir_path .= $this->_template_dir . "/skin/";

        $handle = opendir($dir_path);
        $arr = array();

        while ($dir = readdir($handle))
        {
            $f_dir = $dir_path . '/' . $dir;
            if ($dir != '.' && $dir != '..' && (strpos($dir, '.') === false) && is_dir($f_dir))  //是否还有下级目录
            {
                $arr[] = array('name' => $dir, 'image' => "themes/". basename($this->_template_dir) ."/skin/$dir/$dir.gif");
            }
        }

        closedir($handle);

        $this->json_result(ecm_json_encode($arr), 'ok');
    }

    /**
    * 获取商品分类皮肤
    *
    * @author  liupeng
    * @return  void
    */
    function get_cate_tree()
    {
       require_once(ROOT_PATH . '/includes/models/mod.category.php');
       $cat = new Category(0, $this->store_id);

       $this->json_result(ecm_json_encode($cat->list_child(2)), 'ok');
    }

    /**
     * 获取模块信息
     *
     * @author  liupeng
     * @param   string $return_type
     * @return  mix
    **/
    function get_module_info($return_type = 'JSON')
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $sql = "SELECT * FROM `ecm_custom_modules` WHERE id = '$id'";

        $info = $GLOBALS['db']->getRow($sql);

        if (!empty($info))
        {
            $info['config'] = unserialize($info['config']);
        } else {
            $this->json_error('not found');
            return;
        }

        if ($return_type == 'JSON')
        {
            $this->json_result(ecm_json_encode($info), 'ok');
        }
        else
        {
            return $info;
        }
    }

    /**
     * 添加编辑模块
     *
     * @author  liupeng
     * @return  void
    **/
    function add_module()
    {
        $module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;

        if ($module_id > 0)
        {
            $_GET['id'] = $module_id;
            $info = $this->get_module_info("return");
            $old_config = $info['config'];
        }

        require_once(ROOT_PATH. '/includes/cls.uploader.php');
        $uploader = new Uploader('data/user_files', 'image', $this->conf('mall_max_file'));

        $info['name']   = isset($_POST['module_name']) ? trim($_POST['module_name']) : '';
        $config['c']    = isset($_POST['cate']) ? intval($_POST['cate']) : 0;

        if (isset($_POST['img_count']))
        {
            $config['ic'] = trim($_POST['img_count']); // 图片推荐显示总数
        }

        if (isset($_POST['word_count']))
        {
            $config['wc'] = trim($_POST['word_count']); // 标题背景颜色
        }

        if (isset($_POST['hot_count']))
        {
            $config['hc'] = trim($_POST['hot_count']); // 标题背景颜色
        }

        if (isset($_POST['title_backgroundcolor']))
        {
            $config['tbgc'] = trim($_POST['title_backgroundcolor']); // 标题背景颜色
        }

        if (isset($_POST['title_fontcolor']))
        {
            $config['tfc'] = trim($_POST['title_fontcolor']); // 标题字体颜色
        }

        if (isset($_POST['content_backgroundcolor']))
        {
            $config['cbgc'] = trim($_POST['content_backgroundcolor']); // 内容背景颜色
        }

        if (isset($_POST['content_fontcolor']))
        {
            $config['cfc'] = trim($_POST['content_fontcolor']); // 内容字体颜色
        }

        if (isset($_POST['bottom_backgroundcolor']))
        {
            $config['bbgc'] = trim($_POST['bottom_backgroundcolor']); // 底部背景颜色
        }

        if (isset($_POST['bottom_fontcolor']))
        {
            $config['bfc'] = trim($_POST['bottom_fontcolor']); // 底部字体颜色
        }

        /* 上传标题背景 */
        if (!empty($_FILES['title_backgroundimage']['name']))
        {
            if ($uploader->upload_files($_FILES['title_backgroundimage']))
            {
                $files = $uploader->success_list;
                $config['tbgimg'] = $files[0]['target'];

                if (isset($old_config['tbgimg']) && is_file($old_config['tbgimg']))
                {
                    @unlink(ROOT_PATH . '/' . $old_config['tbgimg']);
                }
            }
            else
            {
                $this->json_error($uploader->err);
                return;
            }
        }
        else
        {
            $config['tbgimg'] = $old_config['tbgimg'];
        }

        /* 上传内容背景 */
        if (!empty($_FILES['content_backgroundimage']['name']))
        {
            if ($uploader->upload_files($_FILES['content_backgroundimage']))
            {
                $files = $uploader->success_list;
                $config['cbgimg'] = $files[0]['target'];

                if (isset($old_config['cbgimg']) && is_file($old_config['cbgimg']))
                {
                    @unlink(ROOT_PATH . '/' . $old_config['cbgimg']);
                }
            }
            else
            {
                $this->json_error($uploader->err);
                return;
            }
        }
        else
        {
            $config['cbgimg'] = $old_config['cbgimg'];
        }

        /* 上传底部背景 */
        if (!empty($_FILES['bottom_backgroundimage']['name']))
        {
            if ($uploader->upload_files($_FILES['bottom_backgroundimage']))
            {
                $files = $uploader->success_list;
                $config['bbgimg'] = $files[0]['target'];

                if (isset($old_config['bbgimg']) && is_file($old_config['bbgimg']))
                {
                    @unlink(ROOT_PATH . '/' . $old_config['bbgimg']);
                }
            }
            else
            {
                $this->json_error($uploader->err);
                return;
            }
        }
        else
        {
            $config['bbgimg'] = $old_config['bbgimg'];
        }

        $info['config']   = $GLOBALS['db']->escape_string(serialize($config));
        $info['store_id'] = $this->store_id;
        $info['type']     = $this->store_id > 0 ? 1 : 0; // 模块类型0为商铺自定义模块,1为店铺自定义模块

        $action = "INSERT";
        $where = "";
        if ($module_id>0)
        {
            $action = "UPDATE";
            $where = "id='$module_id'";
        }
        if ($GLOBALS['db']->autoExecute('`ecm_custom_modules`', $info, $action, $where))
        {
            $this->get_custom_modules();
        }
    }

    /**
    * 删除模块
    *
    * author  liupeng
    * return  void
    **/
    function delete_module()
    {
        $id   = intval($_GET['id']);
        $type = isset($_GET['type']) ? intval($_GET['type']) : 0;

        $sql = "SELECT * FROM `ecm_custom_modules` WHERE id='$id' AND store_id = '$this->store_id'";
        $row = $GLOBALS['db']->getRow($sql);

        $config = @unserialize($row['config']);

        /* 删除自定义模块调用的文件 */
        if (isset($config['tbgimg']) && is_file($config['tbgimg']))
        {
            @unlink(ROOT_PATH . '/' . $config['tbgimg']);
        }
        if (isset($config['cbgimg']) && is_file($config['cbgimg']))
        {
            @unlink(ROOT_PATH . '/' . $config['cbgimg']);
        }
        if (isset($config['bbgimg']) && is_file($config['bbgimg']))
        {
            @unlink(ROOT_PATH . '/' . $config['bbgimg']);
        }

        $sql = "DELETE FROM `ecm_custom_modules` WHERE id = '$id'";
        if ($GLOBALS['db']->query($sql))
        {
            $this->update_template(true);
            $this->get_custom_modules();
        }
    }

    /**
    * 还原模版设置
    *
    * @author  liupeng
    * @return  void
    **/
    function restore()
    {
        $pagename = isset($_POST["pagename"]) ? trim($_POST["pagename"]) : "";

        $sql = "DELETE FROM `ecm_templates` WHERE pagename = '$pagename' AND store_id='$this->store_id'";
        if ($GLOBALS['db']->query($sql))
        {
            $this->json_result('','ok');
        }
    }

    /**
    * 获取并打包CSS文件
    *
    * @author  liupeng
    * @return  void
    **/
    function get_css()
    {
        $pagename = isset($_GET["pagename"]) ? trim($_GET["pagename"]) : '';
        $skin = isset($_GET["skin"])  ? trim($_GET["skin"]) : '';

        if (strpos($pagename, '.') !== false || strpos($skin, '.'))
        {
            return;
        }

        $arr = array();

        if ($this->store_id > 0)
        {
            $mall_skin = $this->conf('mall_skin');
            $arr[] = "themes/mall/skin/$mall_skin/global.css";
            $arr[] = "themes/store/skin/$skin/store.css";
        }
        else
        {
            $arr[] = "themes/mall/skin/$skin/global.css";
            $arr[] = "themes/mall/skin/$skin/mall.css";
        }

        $pagecss = "themes/skin/$skin/$pagename.css";
        if (is_file($pagecss))
        {
            $arr[] = $pagecss;
        }
        $contents = '';
        foreach($arr AS $file)
        {
            $skin_img = dirname(ROOT_DIR . '/'. $file) . '/images';

            $content = file_get_contents($file);
            $content = preg_replace('/url\(images/i', "url($skin_img", $content);
            $contents .= $content;
        }
        header("Content-Type: text/css\n");
        echo $contents;
    }
}

?>
