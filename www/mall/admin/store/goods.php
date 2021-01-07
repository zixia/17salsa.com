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
 * $Id: goods.php 6087 2008-11-20 02:12:08Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/manager/mng.goods.php');
require_once(ROOT_PATH . '/includes/models/mod.category.php');
require_once(ROOT_PATH . '/includes/models/mod.goods.php');
require_once(ROOT_PATH . '/includes/manager/mng.goodstype.php');
require_once(ROOT_PATH . '/includes/models/mod.goodstype.php');

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
     * 商品列表
     *
     * @author  wj
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $store_category = new Category(0, $_SESSION['store_id']);
        $mall_categroy = new Category(0, 0);
        $this->assign('store_cate_options', $store_category->get_options());
        $this->assign('mall_cate_options', $mall_categroy->get_options());

        $condition = array();
        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] = trim($_GET['keywords']);
        }
        $cate_type = 'store';
        if (!empty($_GET['store_cate_id']))
        {
            $condition['store_cate_id'] = intval($_GET['store_cate_id']);
        }
        elseif (!empty($_GET['mall_cate_id']))
        {
            $condition['mall_cate_id'] = intval($_GET['mall_cate_id']);
            $cate_type = 'mall';
        }
        if (isset($_GET['stock']))
        {
            $condition['stock'] = 0; //查询库存为空的商品
        }
        $manager = new GoodsManager($_SESSION['store_id']);
        $list = $manager->get_list($this->get_page(), $condition);

        $this->assign('condition',  $condition);
        $this->assign('cate_type', $cate_type);
        $this->assign('stat_info',  $this->str_format('stat_info', $list['info']['rec_count'], $list['info']['page_count']));
        $this->assign('list',       $list);
        $this->display('goods.view.html', 'store');
    }

    /**
     * 添加商品
     *
     * @author  wj
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            /*检查商品数量*/
            $msg = $this->check_goods_count();
            if ($msg)
            {
                $this->show_message($msg, 'back_list', 'admin.php?app=goods&amp;act=view');
                return;
            }

            $mall_category = new Category(0, 0);
            $store_category = new Category(0, $_SESSION['store_id']);
            $mall_cate_index = $mall_category->get_index();
            $this->assign('mall_cate_index', ecm_json_encode($mall_cate_index));
            $this->assign('first_mall_cate_list',      $cate_index[0]);
            $this->assign('store_cate_list',     $store_category->get_options());

            /* 判断商品编辑器模式 */
            $goods_edit_mode = empty($_COOKIE['ecm_config']['goods_edit_mode']) ? 'full' : trim($_COOKIE['ecm_config']['goods_edit_mode']);
            $this->assign('mode', $goods_edit_mode);

            /* 商品类型 */
            $type_manager = new GoodsTypeManager(0);
            $type_list = $type_manager->get_options();
            $this->assign('type_options', $type_list);

            /*商品默认值*/
            $info = array('is_best'=>0, 'give_points'=>0, 'max_use_points'=>0, 'is_s_new'=>1,
                'default_spec'=>0, 'is_on_sale'=>1,'goods_weight'=>0,'new_level'=>10,
                'spec'=>array(array('spec_id'=>0, 'store_price'=>1, 'stock'=>1)), 'type_id'=>$type_id,
                'mall_cate_dir'=>'0/0',
            );

            /* uch */
            $has_uch = has_uchome();
            if ($has_uch)
            {
                $this->assign('lang_goods_send_feed', $this->str_format('is_send_feed', uc_home_url($_SESSION['admin_id'])));
            }

            //商品新旧程度
            $level_range = array();
            for($i=9; $i > 5; $i--)
            {
                $level_range[$i] = $this->lang('level'.$i);
            }
            $this->assign('level_range', $level_range);
            $this->assign('select_level',   array(10=>$this->lang('firsthand'),0=>$this->lang('secondhand')));
            $this->assign('goods',          $info);

            include_once(ROOT_PATH . '/includes/manager/mng.file.php');
            $fm = new FileManager($_SESSION['store_id']);
            $attachs = $fm->get_list_by_item(0);

            $this->build_editor('goods_desc', '', '95%', '300', 'rich_editor', 'true', $attachs);
            $this->assign('default_feed_status', $this->conf('store_feed_default_status', $_SESSION['store_id']));
            $this->display('goods.detail.html', 'store');
        }
        else
        {
            /*检查商品数量*/
            $msg = $this->check_goods_count();
            if ($msg)
            {
                $this->show_message($msg, 'back_list', 'admin.php?app=goods&amp;act=view');
                return;
            }
            /*当上传附件时检查附件数量*/
            if (isset($_FILES['image']) && isset($_FILES['image']['size']) && (!empty($_FILES['image']['size'][0])))
            {
                $msg = $this->check_store_file_count($_SESSION['store_id']);
                if ($msg)
                {
                    $this->show_message($msg);
                    return;
                }
            }
            $this->post_conversion();//单位换算
            $data = $_POST;

            //过滤禁止修改的字段
            $forbid_filed = $this->get_forbid_filed();
            foreach ($forbid_filed as $v)
            {
                if (isset($data[$v])) unset($data[$v]);
            }
            $data['image'] = $_FILES['image'];

            $data['goods_desc'] = $this->get_editor_content($data['goods_desc']);
            $data['is_deny'] = $this->_allowed_sale();
            $data['editor_type'] = $this->conf('mall_editor_type');

            $goods_manager = new GoodsManager($_SESSION['store_id'], $this->conf('mall_max_file'));
            $goods_id = $goods_manager->add($data);

            if ($goods_id)
            {
                $this->log_item = $goods_id;
                if (!empty($_POST['send_feed'])) $this->send_feed($goods_id);    // 发送事件通知

                if ($goods_manager->err == 'e_file_too_big')
                {
                    $this->show_warning('add_file_warning', 're_edit', 'admin.php?app=goods&amp;act=edit&amp;id=' . $goods_id, 'back_list', 'admin.php?app=goods&amp;act=view');
                }
                else
                {
                    $this->show_message('add_ok', 'add_other',
                    'admin.php?app=goods&amp;act=add', $this->lang('back_list'), 'admin.php?app=goods&amp;act=view');
                }

                if (isset($_POST['file_id']) && count($_POST['file_id']) > 0)
                {
                    $fm = new FileManager($_SESSION['store_id']);
                    $fm->update_item_id($_POST['file_id'], 'goods', $goods_id);
                }
            }
            else
            {
               $this->show_warning($goods_manager->err);
               return false;
            }
        }
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
        $goods = new Goods($goods_id, $_SESSION['store_id']);
        $info = $goods->get_goods_detail();
        if (empty($info))
        {
            $this->show_warning('no_such_goods');

            return false;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $info['spec'] = $goods->get_spec();

            /* 判断商品编辑器模式 */
            $goods_edit_mode = empty($_COOKIE['ecm_config']['goods_edit_mode']) ? 'full' : trim($_COOKIE['ecm_config']['goods_edit_mode']);
            if (count($info['spec']) > 1)
            {
                $goods_edit_mode = 'full';
                $this->assign('not_allow_change', 1);
            }
            $this->assign('mode', $goods_edit_mode);

            /* 商品类型 */
            $type_manager = new GoodsTypeManager(0);
            $type = new GoodsType($info['type_id'], 0);
            $attr = $goods->get_attribute();
            $attr_list = $type->get_attr_list();
            for ($i=0; $i < count($attr_list); $i++)
            {
                $attr_list[$i]['value'] = isset($attr[$attr_list[$i]['attr_id']]['attr_value']) ? trim($attr[$attr_list[$i]['attr_id']]['attr_value']) : '';
            }
            $this->assign('attr_list',      $attr_list);
            $this->assign('type_options',   $type_manager->get_options());

            /*商品图片*/
            include_once(ROOT_PATH . '/includes/manager/mng.file.php');
            $thumb = new FileManager($_SESSION['store_id']);
            $thumb->item_type = 'album';
            $this->assign('thumb', $thumb->get_list_by_item($goods_id, 'album'));

            $fm = new FileManager($_SESSION['store_id']);
            $attachments = $fm->get_list_by_item($goods_id, 'goods');

            $this->build_editor('goods_desc', $info['goods_desc'], '80%', '300', 'rich_editor', 'true', $attachments, $info['editor_type']);
            /*商品分类*/
            $mall_category  = new Category(0,   0);
            $store_category = new Category(0,   $_SESSION['store_id']);
            $mall_cate_index = $mall_category->get_index();
            $this->assign('mall_cate_index', ecm_json_encode($mall_cate_index));
            $this->assign('store_cate_list',     $store_category->get_options());
            $mall_cate1 = new Category($info['mall_cate_id'], 0);
            $mall_cate_info = $mall_cate1->get_info();
            $info['mall_cate_dir'] = $mall_cate_info['dir'] . '/' . $info['mall_cate_id'];

            //商品新旧程度
            $level_range = array();
            for($i=9; $i > 5; $i--)
            {
                $level_range[$i] = $this->lang('level'.$i);
            }
            $secondlevel = ($info['new_level'] == '10') ? 0 : intval($info['new_level']);
            $this->assign('level_range', $level_range);
            $this->assign('select_level',   array(10=>$this->lang('firsthand'),$secondlevel=>$this->lang('secondhand')));
            $this->assign('goods', $info);

            $this->display('goods.detail.html', 'store');
        }
        else
        {
            $goods_id = empty($_POST['goods_id']) ? 0 : intval($_POST['goods_id']);
            /*当上传附件时检查附件数量*/
            if (isset($_FILES['image']) && isset($_FILES['image']['size']) && (!empty($_FILES['image']['size'][0])))
            {
                $msg = $this->check_store_file_count($_SESSION['store_id']);
                if ($msg)
                {
                    $this->show_message($msg);
                    return;
                }
            }

            $this->post_conversion();//单位换算
            if ($goods_id <= 0) trigger_error("error goods id [{$goods_id}]", E_USER_ERROR);
            $this->log_item = $goods_id;
            $data = $_POST;
            $data['image'] = $_FILES['image'];

            //过滤禁止修改的字段
            $forbid_filed = $this->get_forbid_filed();
            foreach ($forbid_filed as $v)
            {
                if (isset($data[$v])) unset($data[$v]);
            }

            $data['goods_desc'] = $this->get_editor_content($data['goods_desc']);
            $data['editor_type'] = $this->conf('mall_editor_type');

            $manager = new GoodsManager($_SESSION['store_id'], $this->conf('mall_max_file'));
            $manager->update($goods_id, $data);

            /* 更新编辑器上传的图片 */
            if (count($_POST['file_id']) > 0)
            {
                $fm = new FileManager($_SESSION['store_id']);
                $fm->update_item_id($_POST['file_id'], 'goods', $goods_id);
            }

            if ($manager->err == 'e_file_too_big')
            {
                $this->show_warning('edit_file_warning', 're_edit', 'admin.php?app=goods&amp;act=edit&amp;id=' . $goods_id, 'back_list', 'admin.php?app=goods&amp;act=view');
            }
            else
            {
                $this->show_message('edit_ok', 'back_list', 'admin.php?app=goods&amp;act=view');
            }

            return;
        }
    }

    /**
     * 删除商品
     *
     * @author  liupeng
     * @return  void
     */
    function drop()
    {
        $goods_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($goods_id <= 0) trigger_error('goods is invalid!', E_USER_ERROR);
        $goods = new Goods($goods_id, $_SESSION['store_id']);
        $info  = $goods->get_info();
        if (empty($info))
        {
            $this->show_warning('no_such_goods');
            return;
        }
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
        $this->show_message('delete_succeed','back_list', $location);
        return;
    }

    /**
     * 删除一个规格
     *
     * @author  liupeng
     * @return  void
     */
    function drop_spec()
    {
        $spec_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($spec_id <= 0) trigger_error("The spec id [{$spec_id}] is invalid!", E_USER_ERROR);
        $goods = GoodsFactory::build($spec_id, $_SESSION['store_id']);
        $this->log_item = $spec_id;
        if ($goods->drop_spec($spec_id))
        {
            $this->json_result();
            return;
        }
        else
        {
            $this->json_error($goods->err[0]);
            return;
        }
    }

    /**
     * 删除一张图片
     *
     * @author  liupeng
     * @return  void
     */
    function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        if ($goods_id <= 0) trigger_error("The goods id [{$goods_id}] is invalid!", E_USER_ERROR);
        if ($id <= 0) trigger_error("The image id [{$id}] is invalid!", E_USER_ERROR);
        $goods = new Goods($goods_id, $_SESSION['store_id']);
        $this->log_item = $id;
        if ($goods->drop_image($id))
        {
            $this->json_result();
            return;
        }
        else
        {
            $this->json_error($goods->err[0]);
            return;
        }
    }

    /**
     * 获取品牌信息
     *
     * @author  wj
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
            //为空时去最近使用的几条
            $mng_goods = new GoodsManager($_SESSION['store_id']);
            $brand_name = $mng_goods->get_last_brand_name(10);
        }

        $list = array_map(null, $brand_name, $brand_name);
        $this->json_result($list);
    }

    /**
     * AJAX更新字段值
     *
     * @author  liupeng
     * @return  void
     */
    function modify()
    {
        $id = intval($_GET['id']);
        $column = trim($_GET['column']);
        $value = trim($_GET['value']);
        if ($id < 0) trigger_error('id is undefined!', E_USER_ERROR);
        $this->log_item = $id;
        $data = array($column=>$value);
        $goods = new Goods($id, $_SESSION['store_id']);
        if($goods->update($data))
        {
            $this->json_result($value);
        }
        else
        {
            $this->json_error("There is no record had been updated!");
            return;
        }
   }

    /**
     * 批量处理
     *
     * @author  wj
     * @return  void
     */
    function batch()
    {
        $type   = trim($_GET['param']);
        $in     = trim($_GET['ids']);

        $forbid_filed = $this->get_forbid_filed();
        if (in_array($type, $forbid_filed))
        {
            $this->show_warning('forbid modify the fild!');
            return;
        }

        /* 过滤无效和重复id */
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
               if ($param[0] == 'move_to_cate')
               {
                   $target_cate_id = intval($param[2]);
                   $target_cate_type = trim($param[1]);

                   $manager = new GoodsManager($_SESSION['store_id'], $this->conf('mall_max_file'));
                   if ($target_cate_type == 'store')
                   {
                         $res = $manager->move_to_store_cate($target_cate_id,$in);
                   }
                   else
                   {
                       $res = $manager->move_to_mall_cate($target_cate_id, $in);
                   }

               }
               elseif ($param[0] == 'move_to_brand')
               {
                   $new_brand_name = trim($param[1]);
                   include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
                   $mod_brand = new BrandManager(0);
                   $id = $mod_brand->get_id($new_brand_name);
                   if ($id <=0){
                       $this->show_warning('brand_not_exist');

                       return;
                   }
                   else
                   {
                       $manager = new GoodsManager($_SESSION['store_id']);
                       $res = $manager->move_to_brand($id,$in);
                   }
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
            $replacement[]  = 'act=view&';
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
     * 获取属性列表
     *
     * @author  liupeng
     * @return void
     */
    function get_attribute()
    {
        $this->logger = false;
        $type_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($type_id <= 0) trigger_error("The type id[$type_id] is unexpert!");
        $type = new GoodsType($type_id, 0);
        $this->json_result($type->get_attr_list());
    }

    /**
     * 查看关联商品
     *
     * @author  liupeng
     * @return void
     */
    function relate()
    {
        $this->logger = false;
        /* 参数 */
        $goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($goods_id <= 0) trigger_error('goods is invalid!', E_USER_ERROR);
        $mod = new Goods($goods_id, $_SESSION['store_id']);
        $goods = $mod->get_info();
        $this->assign('goods', $goods);

        $type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 's';
        if ($type != 's')
        {
            $type = 'c';
        }
        $this->assign('type', $type);
        $tmp = $this->lang('whose_related_goods');
        $this->assign('title', sprintf($tmp[$type], $goods['goods_name']));

        /* 取得关联商品 */
        $related_goods = $mod->get_related_goods($type, false);
        $this->assign('related_goods', $related_goods);

        /* 取得分类品牌 */
        $category = new Category(0, $_SESSION['store_id']);
        $this->assign('cate_options', $category->get_options());

        /* 显示模版 */
        $this->display('goods.relate.html', 'store');
    }

    /**
     * ajax方式取得商品
     *
     * @author  liupeng
     * @return void
     */
    function search()
    {
        $this->logger = false;
        /* 搜索条件 */
        $condition = array();
        if (!empty($_GET['cate_id']))
        {
            $condition['store_cate_id'] = intval($_GET['cate_id']);
        }
        if (!empty($_GET['brand_name']))
        {
            $condition['brand_name'] = trim($_GET['brand_name']);
        }
        if (!empty($_GET['keywords']))
        {
            $condition['keywords'] = trim($_GET['keywords']);
        }
        if (!empty($_GET['goods_id']))
        {
            $condition['except_goods_ids'] = trim($_GET['goods_id']);
        }

        /* 输出 */
        $manager = new GoodsManager($_SESSION['store_id']);
        $list = $manager->get_idname_list(1, $condition, 50);
        header("Content-type:text/html;charset=" . CHARSET, true);
        echo ecm_json_encode($list);
    }

    /**
     * ajax方式建立关联
     *
     * @author  liupeng
     * @return void
     */
    function set_related()
    {
        /* 参数 */
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        $type = empty($_GET['type']) ? 's' : strtolower(trim($_GET['type']));
        if ($type != 's')
        {
            $type = 'c';
        }
        $id_list = $_GET['id_list'];
        $this->log_item = $goods_id;
        /* 建立关联 */
        $manager = new GoodsManager($_SESSION['store_id']);
        if ($id_list && is_array($id_list))
        {
            foreach ($id_list as $id)
            {
                $manager->set_related($goods_id, $id, $type);
            }
        }

        /* 返回已关联商品 */
        $mod = new Goods($goods_id, $_SESSION['store_id']);
        $related_goods = $mod->get_related_goods($type, false);
        header("Content-type:text/html;charset=" . CHARSET, true);
        echo ecm_json_encode($related_goods);
    }

    /**
     * ajax方式解除关联
     *
     * @author  liupeng
     * @return void
     */
    function unset_related()
    {
        /* 参数 */
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        $type = empty($_GET['type']) ? 's' : strtolower(trim($_GET['type']));
        if ($type != 's')
        {
            $type = 'c';
        }
        $id_list = $_GET['id_list'];
        $this->log_item = $goods_id;
        /* 解除关联 */
        $manager = new GoodsManager($_SESSION['store_id']);
        if ($id_list && is_array($id_list))
        {
            foreach ($id_list as $id)
            {
                $manager->unset_related($goods_id, $id, $type);
            }
        }

        /* 返回已关联商品 */
        $mod = new Goods($goods_id, $_SESSION['store_id']);
        $related_goods = $mod->get_related_goods($type, false);
        header("Content-type:text/html;charset=" . CHARSET, true);
        echo ecm_json_encode($related_goods);
    }

    /**
     * 检查是否超过商品数量上限
     *
     * @author  liupeng
     * @return string,bool 成功返回true,失败放回错误信息
     */
    function check_goods_count()
    {
        $result = '';
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $new_store = new Store($_SESSION['store_id']);
        $info = $new_store->get_info();

        if (($info['goods_limit'] > 0) && ($new_store->get_goods_count() >= $info['goods_limit']))
        {
            $result = $this->str_format('over_goods_count', $info['goods_count'], $info['goods_limit']);
        }

        return $result;
    }

    /**
     * 对提交的数据进行转换处理
     *
     * @author  liupeng
     * @return  void
     */
    function post_conversion()
    {
        //新旧程度
        if (isset($_POST['select_level']) && $_POST['select_level'] == '10')
        {
            $_POST['new_level'] = 10;
        }
        //新品、精品补充
        $checkbox_item = array('is_s_best', 'is_s_new', 'is_on_sale');
        foreach ($checkbox_item as $v)
        {
            if (!isset($_POST[$v]))
            {
                $_POST[$v] = 0;
            }
        }
        //不能修改的字段
        $deny_item = $this->get_forbid_filed();
        foreach ($deny_item as $v)
        {
            if (isset($_POST[$v])) unset($_POST[$v]);
        }
    }

    /**
     * Ajax方式获取商品类型
     *
     * @author  liupeng
     * @return  void
     */
    function get_goods_type()
    {
        $this->logger = false;
        $cate_id = intval($_GET['cate_id']);
        $new_cate = new Category($cate_id,0);
        $info = $new_cate->get_info();
        $retval = intval($info['type_id']);
        if ($retval > 0)
        {
            $this->json_result($retval);
        }
        else
        {
            $this->json_error('', $retval);
        }
    }

    /**
     * 返回被禁止修改字段
     *
     * @author  liupeng
     * @return  array
     */
    function get_forbid_filed()
    {
        return array('is_mi_best', 'is_mw_best', 'is_m_hot', 'sort_weighing', 'is_deny');
    }

    /**
     * 发送添加商品的事件
     *
     * @author  weberliu
     * @param   int     $goods_id
     * @return  void
     */
    function send_feed($goods_id)
    {
        /* send feed to uc */
        $new_goods = new Goods($goods_id, $_SESSION['store_id']);
        $info = $new_goods->get_goods_detail(0);
        $link_url = site_url() . '/index.php?app=goods&id=' . $goods_id;

        $feed_info['icon']              =   'goods';
        $feed_info['user_id']           =   $_SESSION['store_id'];
        $feed_info['user_name']         =   $_SESSION['admin_name'];
        $feed_info['title']['template'] =   $this->lang('feed_add_goods_title');
        $feed_info['body']['template']  =   $this->lang('feed_add_goods_message');
        $feed_info['body']['data']['subject'] = $info['goods_name'];
        $feed_info['body']['data']['store']   = '<a href="' . site_url() . '/index.php?app=store&store_id=' . $info['store_id'] . '" target="_blank">' . $this->conf('store_name', $info['store_id']) . '</a>';
        $feed_info['body']['data']['price']   = $info['store_price'];
        $feed_info['body']['data']['time']    = local_date($this->conf('mall_time_format_complete'));
        $feed_info['images'][]                =   array('url' => site_url() . '/image.php?file_id=' . $info['default_image'] . '&hash_path=' . md5(ECM_KEY . $info['default_image'] . 100 . 100) . '&width=100&height=100', 'link' => $link_url);

        add_feed($feed_info);
    }

    /**
     * 获得当前的系统设置，商品是否默认允许销售
     *
     * @author  wj
     * @return  int
     */
    function _allowed_sale()
    {
        $conf = intval($this->conf('mall_auto_allow'));

        if ($conf === 2)
        {
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $mod = new Store($_SESSION['store_id']);
            $info = $mod->get_info();

            return (1 - intval($info['is_certified']));
        }
        else
        {
            return (1-$conf);
        }
    }


};

?>
