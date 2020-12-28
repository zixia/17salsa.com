<?php

/**
 * ECMALL: 商品管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: goods.php 6025 2008-11-03 06:56:33Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/manager/mng.goods.php');
require_once(ROOT_PATH . '/includes/models/mod.goods.php');
require_once(ROOT_PATH . '/includes/models/mod.category.php');
class GoodsController extends ControllerBackend
{
    function __construct($act)
    {
        $this->GoodsController($act);
    }

    function GoodsController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }
    /**
     * 查看商品
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false; // 不记日志
        $mall_category = new Category(0, $_SESSION['store_id']);
        $this->assign('mall_cate_options', $mall_category->get_options());

        $condition = array();
        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] = trim($_GET['keywords']);
        }
        if (!empty($_GET['mall_cate_id']))
        {
            $condition['mall_cate_id'] = intval($_GET['mall_cate_id']);
        }
        if (!empty($_GET['store_id']))
        {
            $condition['store_id'] = intval($_GET['store_id']);
        }

        $this->assign('condition', $condition);
        $manager = new GoodsManager($_SESSION['store_id']);
        $list = $manager->get_list($this->get_page(), $condition);

        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $new_store = new StoreManager();
        unset($_GET['sort']);
        unset($_GET['order']);
        $store_list = $new_store->get_list(1, array(), 1000);
        $store_options = array();
        foreach ($store_list['data'] as $val)
        {
            $store_options[$val['store_id']] = $val['store_name'];
        }
        $this->assign('store_options', $store_options);
        $this->assign('stat_info',  $this->str_format('mall_stat_info', $new_store->get_count(1), $list['info']['rec_count'], $list['info']['page_count']));
        $this->assign('list',       $list);
        $this->display('goods.view.html', 'mall');
    }

    /**
     * 编辑商品
     *
     * @author  wj
     * @return  void
     */
    function edit()
    {
      $goods_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($goods_id <= 0) trigger_error('goods is invalid!', E_USER_ERROR);
        $goods = new Goods($goods_id);
        $info = $goods->get_goods_detail();

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $info['spec'] = $goods->get_spec();

            $this->build_editor('goods_desc', $info['goods_desc'], '80%', '300', 'rich_editor', 'true', $attachments, $info['editor_type']);
            /*商品分类*/
            $mall_category  = new Category(0,   0);
            $mall_cate_index = $mall_category->get_index();
            $this->assign('mall_cate_index', ecm_json_encode($mall_cate_index));
            $mall_cate1 = new Category($info['mall_cate_id'], 0);
            $mall_cate_info = $mall_cate1->get_info();
            $info['mall_cate_dir'] = $mall_cate_info['dir'] . '/' . $info['mall_cate_id'];

            $this->assign('goods', $info);
            $this->display('goods.detail.html', 'mall');
        }
        else
        {
            $goods_id = empty($_POST['goods_id']) ? 0 : intval($_POST['goods_id']);

            if ($goods_id <= 0) trigger_error("error goods id [{$goods_id}]", E_USER_ERROR);
            $this->log_item = $goods_id;
            $data = $_POST;
            $data['goods_desc'] = $this->get_editor_content($data['goods_desc'], $info['editor_type']);
            $opt_arr = array('is_on_sale', 'is_deny', 'is_mi_best', 'is_mw_best', 'is_m_hot');
            foreach($opt_arr as $opt)
            {
                if (isset($data[$opt]))
                {
                    $data[$opt] = 1;
                }
                else
                {
                    $data[$opt] = 0;
                }
            }

            $manager = new GoodsManager(0, $this->conf('mall_max_file'));
            $manager->update($goods_id, $data);

            $this->show_message('edit_ok', 'back_list', 'admin.php?app=goods&amp;act=view');

            return;
        }
    }

    /**
     * AJAX更新字段值
     *
     * @return  void
     */
    function modify()
    {
        $id     = intval($_GET['id']);
        $column = trim($_GET['column']);
        $value  = trim($_GET['value']);

        if ($id < 0) trigger_error('id is undefined!', E_USER_ERROR);
        $this->log_item = $id;

        $data   = array($column=>$value);
        $goods  = new Goods($id, $_SESSION['store_id']);

        if($goods->update($data))
        {
            $this->json_result($value);
            return;
        }
        else
        {
            $this->json_error("There is no record had been updated!");
            return;
        }
   }

    /**
     * 批量处理
     * @author  wj
     * @return  void
     */
    function batch()
    {
        $type   = trim($_GET['param']);
        $in     = trim($_GET['ids']);

        $this->log_item = $in;
        if (empty($in))
        {
            $this->show_warning('batch_not_selected');
            return;
        }
        else
        {
            if (strpos($type, ','))
            {
                /* $type包含多个参数的情况 */
               $param = explode(',', $type);
               if ($param[0] == 'move_to_store_cate')
               {
                   $target_store_cate = intval($param[1]);
                   $manager = new GoodsManager($_SESSION['store_id']);
                   $res = $manager->move_to_store_cate($target_store_cate,$in);
               }
            }
            else
            {
                /*只传递一个参数的*/
                $manager    = new GoodsManager($_SESSION['store_id']);
                $res        = $manager->batch($type, $in);
            }

            if ($goods->err)
            {
                $this->show_message($goods->err);
                return;
            }
            $patterns[]     = '/act=\w+/i';
            $patterns[]     = '/[&|\?]?param=[\w|,]+/i';
            $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
            $replacement[]  = 'act=view';
            $replacement[]  = '';
            $replacement[]  = '';
            $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
            if ($type == 'drop')
            {
                $this->show_message($this->str_format('batch_drop_successfully', intval($res)), $this->lang('back_list'), $location);
            }
            else
            {
                $this->show_message('batch_successfully', $this->lang('back_list'), $location);
            }
            return;
        }
    }

    /**
     * 删除一个商品
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function drop ()
    {
        $goods_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($goods_id <= 0) trigger_error('goods is invalid!', E_USER_ERROR);
        $goods = new Goods($goods_id, $_SESSION['store_id']);
        $this->log_item = $goods_id;
        $goods->drop();
        if ($goods->err)
        {
            $this->show_message($goods->err);
            return;
        }
        $patterns[]     = '/act=\w+/i';
        $patterns[]     = '/[&|\?]?param=\w+/i';
        $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
        $replacement[]  = 'act=view';
        $replacement[]  = '';
        $replacement[]  = '';
        $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed', 'back_list', $location);
        return;
    }
};
?>