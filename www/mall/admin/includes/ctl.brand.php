<?php

/**
 * ECMALL: 网站品牌管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.brand.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
include_once(ROOT_PATH . '/includes/models/mod.brand.php');

class BrandController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->BrandController($act);
    }

    function BrandController($act)
    {
        $this->_mng = new BrandManager($_SESSION['store_id']);

        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看列表
     */
    function view()
    {
        $this->logger = false; // 不记日志

        /* 参数 */
        $keywords = empty($_GET['keywords']) ? '' : trim($_GET['keywords']);

        /* 取得列表 */
        $list = $this->_mng->get_list($this->get_page(), array('keywords' => $keywords));
        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('brand_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* 显示模版 */

        $this->assign('store_id', $_SESSION['store_id']);
        $this->display('brand.view.html');
    }

    /**
     * ajax编辑
     */
    function modify()
    {
        /* 参数 */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('brand_name', 'website', 'is_promote')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        /* 检查名称是否重复 */
        if ($field_name == 'brand_name')
        {
            $id = intval($_GET['id']);
            $value = trim($_GET['value']);
            $brand_id = $this->_mng->get_id($value);
            if ($brand_id > 0 && $brand_id != $id)
            {
                $this->json_error('brand_name_exist');
                return;
            }
        }

        $this->_modify('Brand', $_GET);
    }

    /**
     * 批量处理
     */
    function batch()
    {
        /* 参数 */
        $param = empty($_GET['param']) ? '' : trim($_GET['param']);
        $ids   = empty($_GET['ids']) ? '' : trim($_GET['ids']);

        if (empty($ids))
        {
            $this->show_warning('no_brand_selected');
            return;
        }

        /* 根据param做相应处理 */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), $this->lang('back_brands'), 'admin.php?app=brand&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_brand_deleted');
                return;
            }
        }
        else
        {
            $this->show_warning('Hacking Attemp');
            return;
        }
    }

    /**
     * 添加品牌
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 初始化 */
            $brand = array(
                'brand_id' => 0,
                'website'  => 'http://'
            );
            $this->assign('brand', $brand);

            $this->display('brand.detail.html');
        }
        else
        {
            /* 参数 */
            $brand = array(
                'store_id'    => $_SESSION['store_id'],
                'if_show'     => 1,
                'goods_count' => 0
            );

            $brand['brand_name'] = empty($_POST['brand_name']) ? '' : trim($_POST['brand_name']);
            if (empty($brand['brand_name']))
            {
                $this->show_warning('brand_name_required');
                return;
            }

            if ($this->_mng->get_id($brand['brand_name']) > 0)
            {
                $this->show_warning('brand_name_exist');
                return;
            }

//            $brand['website'] = empty($_POST['website']) ? '' : trim($_POST['website']);

            if (!empty($_FILES['brand_logo']['name']))
            {
                include_once(ROOT_PATH . '/includes/cls.uploader.php');
                $uploader = new Uploader('data/user_files/brand', 'image', $this->conf('mall_max_file'));
                if ($uploader->upload_files($_FILES['brand_logo']))
                {
                    $brand['brand_logo'] = $uploader->success_list[0]['target'];
                }
                else
                {
                    $this->show_warning($uploader->err);
                    return;
                }
            }

            /* 插入 */
            $this->log_item = $this->_mng->add($brand);
            // todo: 链接问题
            $this->show_message('add_ok',
                'continue_add', 'admin.php?app=brand&act=add',
                'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * 编辑品牌
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $brand_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($brand_id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new Brand($brand_id, $_SESSION['store_id']);
            $brand = $mod->get_info();
            if (empty($brand))
            {
                $this->show_warning('record_not_exist');
                return;
            }
/*
            if (empty($brand['website']))
            {
                $brand['website'] = 'http://';
            }
*/
            $this->assign('brand', $brand);

            $this->display('brand.detail.html');
        }
        else
        {
            /* 参数 */
            $brand = array(
                'store_id'    => $_SESSION['store_id'],
                'if_show'     => 1,
                'goods_count' => 0
            );

            $brand['brand_id'] = empty($_POST['brand_id']) ? 0 : intval($_POST['brand_id']);
            $mod = new Brand($brand['brand_id'], $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $brand['brand_name'] = empty($_POST['brand_name']) ? '' : trim($_POST['brand_name']);
            if (empty($brand['brand_name']))
            {
                $this->show_warning('brand_name_required');
                return;
            }

            $brand_id = $this->_mng->get_id($brand['brand_name']);
            if ($brand_id > 0 && $brand_id != $brand['brand_id'])
            {
                $this->show_warning('brand_name_exist');
                return;
            }

//            $brand['website'] = empty($_POST['website']) ? '' : trim($_POST['website']);

            if (!empty($_FILES['brand_logo']['name']))
            {
                include_once(ROOT_PATH . '/includes/cls.uploader.php');
                $uploader = new Uploader('data/user_files/brand', 'image', $this->conf('mall_max_file'));
                if ($uploader->upload_files($_FILES['brand_logo']))
                {
                    $brand['brand_logo'] = $uploader->success_list[0]['target'];
                }
                else
                {
                    $this->show_warning($uploader->err);
                    return;
                }
            }

            // 删除原来的图片
            if (isset($brand['brand_logo']) && !empty($info['brand_logo']) && is_file(ROOT_PATH . '/' . $info['brand_logo']))
            {
                @unlink(ROOT_PATH . '/' . $info['brand_logo']);
            }

            /* 更新 */
            $mod->update($brand);
            $this->log_item = $brand['brand_id'];
            // todo: 链接问题
            $this->show_message('edit_ok', 'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * 判断名称是否重复
     */
    function duplicate_name()
    {
        /* 参数 */
        $brand_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $brand_name = empty($_GET['name']) ? '' : trim($_GET['name']);
        if ($brand_name == '')
        {
            $this->json_error('brand_name_required');
            return;
        }

        /* 查询 */
        $id = $this->_mng->get_id($brand_name);
        if ($id > 0 && $id != $brand_id)
        {
            $this->json_error('brand_name_exist');
            return;
        }
        else
        {
            $this->json_result();
            return;
        }
    }

    /**
     * 删除品牌
     */
    function drop()
    {
        /* 参数 */
        $brand_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new Brand($brand_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }
        if ($info['goods_count'] > 0)
        {
            $this->show_warning('cannot_drop');
            return;
        }

        /* 删除 */
        if ($mod->drop())
        {
            $this->log_item = $brand_id;
            $this->show_message('drop_ok', 'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * 更新品牌数量
     *
     * @author liupeng
     * @return void
     **/
    function update_goods_count()
    {
       $manager = new BrandManager($_SESSION['store_id']);
       $manager->update_goods_count();
       $this->json_result('', 'ok');
    }
}

?>