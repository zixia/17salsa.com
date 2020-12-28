<?php

/**
 * ECMALL: 数据调用管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.datacall.php 5477 2008-08-07 07:25:23Z Liupeng $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.category.php');
require_once(ROOT_PATH. '/includes/models/mod.datacall.php');
require_once(ROOT_PATH. '/includes/manager/mng.datacall.php');

class DataCallController extends ControllerBackend
{
    var $template_dir = '';
    var $app_name     = 'datacall';

    function __construct($act)
    {
        $this->DataCallController($act);
    }

    function DataCallController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }
    /**
     * 查看调用列表
     *
     * @author  liupeng
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $mng = new DataCallManager($_SESSION['store_id']);
        $page = $this->get_page();
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];

        $res  = $mng->get_list($page, $condition);

        $res['data'] = deep_local_date($res['data'], 'add_time', $this->conf('mall_time_format_complete'));

        $this->assign('cate_list',      $cate_list);
        $this->assign('list',           $res);
        $this->assign('condition',      $condition);
        $this->assign('data_call_stats',  $this->str_format('data_call_stats', $res['info']['rec_count'], $res['info']['page_count']));
        $this->assign('url_format',     "admin.php?app={$this->app_name}&amp;act=view&amp;page=%d");

        $this->display('data_call.view.html');
    }


    /**
     * 添加数据调用
     *
     * @author  liupeng
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            $category = new Category(0, $_SESSION['store_id']);
            $this->assign('cate_options', $category->get_options());
            $this->assign('store_id', $_SESSION['store_id']);

            $this->display('data_call.detail.html');
        }
        else
        {
            $info = $this->_post_handler();
            $manager = new DataCallManager($_SESSION['store_id']);
            $res = $manager->add($info);

            if ($res)
            {
               $id = $GLOBALS['db']->insert_id();
               $this->log_item = $id; //日志
               $this->show_message('add_succeed', 'back_view_data_call', 'admin.php?app=' . $this->app_name . '&amp;act=view', $this->lang('gen_js'), 'admin.php?app=' . $this->app_name . '&amp;act=view#gen_js.'.$id);
            }
            else
            {
                $str = join("\n", $this->err);
                $this->show_warning($str);
                return;
            }
        }
    }

    /**
     * 编辑数据调用
     *
     * @author  liupeng
     * @return  void
     */
    function edit()
    {
        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app={$this->app_name}");
        }

        $id = intval($_REQUEST["id"]);
        $this->log_item = $id; //日志
        $dc = new DataCall($id, $_SESSION['store_id']);
        $info = $dc->get_info();

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $this->assign('info',           $info);
            $category = new Category(0, $_SESSION['store_id']);
            $this->assign('cate_options', $category->get_options());
            $this->assign('store_id', $_SESSION['store_id']);
            $this->display('data_call.detail.html', $this->template_dir);
        }
        else
        {
            $data = $this->_post_handler();
            if ($dc->update($data))
            {
                $this->show_message($this->str_format('edit_succeed', $info['title']), $this->lang('back_view_data_call'), 'admin.php?app=' . $this->app_name . '&amp;act=view');
                $cache_file = ROOT_PATH . '/temp/js/datacallcache'. $id . '.js';
                if (is_file($cache_file))
                {
                    @unlink($cache_file);
                }
            }
        }
    }

    /**
     * 删除数据调用
     *
     * @author  liupeng
     * @return  void
     */
    function drop()
    {
        if (empty($_GET['id']))
        {
            $this->redirect("admin.php?app={$this->app_name}");
            return;
        }
        $id = $_GET['id'];
        $this->log_item = $id;

        $art = new DataCall($id, $_SESSION['store_id']);
        $art->drop();
        $patterns[]     = '/act=\w+/i';
        $patterns[]     = '/[&|\?]?param=\w+/i';
        $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
        $replacement[]  = 'act=view';
        $replacement[]  = '';
        $replacement[]  = '';
        $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed', 'back_list', $location);
    }

    /**
     * 获取品牌信息
     *
     * @author  liupeng
     * @return  void
     */
    function get_brand_list()
    {
        $this->logger = false;
        $q = empty($_GET['q']) ? '' : trim($_GET['q']);
        include_once(ROOT_PATH . '/includes/models/mod.brand.php');
        if ($q)
        {
            $brand = new Brand(0, $_SESSION['store_id']);
            $brand_name = $brand->get_brand_name($q, 10);
        }
        else
        {
            include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
            //为空时去最近使用的几条
            $mng_goods = new GoodsManager($_SESSION['store_id']);
            $brand_name = $mng_goods->get_last_brand_name(10);
        }

        $list = array_map(null, $brand_name, $brand_name);
        $this->json_result($list);
    }

    /**
     * 处理POST数据
     *
     * @author  liupeng
     * @return  array
     */
    function _post_handler()
    {
        require_once(ROOT_PATH. '/includes/manager/mng.brand.php');
        require_once(ROOT_PATH. '/includes/lib.editor.php');

        $data = array();

        $data['call_desc'] = trim($_POST['desc']);
        $data['cache_time'] = isset($_POST['cache_time']) && intval($_POST['cache_time']) > 60 ? intval($_POST['cache_time']) : 60;
        $data['cate_id'] = isset($_POST['cate_id']) ? intval($_POST['cate_id']) : 0;
        $data['content_charset'] = isset($_POST['content_charset']) ? intval($_POST['content_charset']) : 0;
        $data['brand_id'] = !empty($_POST['brand_name']) ? intval(BrandManager::get_id($_POST['brand_name'])) : 0;
        $data['goods_name_length'] = isset($_POST['goods_name_length']) ? intval($_POST['goods_name_length']) : 4;
        $data['goods_number'] = intval($_POST['goods_number']) > 1 ? intval($_POST['goods_number']) : 1;
        $data['store_id'] = intval($_SESSION['store_id']);

        $data['template'] = array(
            'header' => stripcslashes(Editor::check_html($_POST['template_header'])),
            'body' => stripcslashes(Editor::check_html($_POST['template_body'])),
            'footer' => stripcslashes(Editor::check_html($_POST['template_footer'])),
        );
        $data['template'] = $GLOBALS['db']->escape_string(serialize($data['template']));
        $data['recommend_option'] = intval($_POST['recommend_options'][0]);
        $data['recommend_option'] .= intval($_SESSION['store_id']) == 0 ? ','.intval($_POST['recommend_options'][1]) : '';

        return $data;
    }
};
?>