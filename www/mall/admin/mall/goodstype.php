<?php

/**
 * ECMALL: 商品类型控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: goodstype.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.goodstype.php');
include_once(ROOT_PATH . '/includes/models/mod.goodstype.php');

class GoodsTypeController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->GoodsTypeController($act);
    }

    function GoodsTypeController($act)
    {
        $this->_mng = new GoodsTypeManager($_SESSION['store_id']);

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

        /* 取得列表 */
        $list = $this->_mng->get_list($this->get_page());
        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('goodstype_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* 显示模版 */
        $this->display('mall/goods_type.view.html');
    }

    /**
     * ajax编辑
     */
    function modify()
    {
        /* 参数 */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('type_name')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        /* 检查名称是否重复 */
        $id = intval($_GET['id']);
        $value = trim($_GET['value']);
        $this->log_item = $id;
        $type_id = $this->_mng->get_id($value);
        if ($type_id != $id && $type_id != 0)
        {
            $this->json_error('name_exist');
            return;
        }

        $this->_modify('GoodsType', $_GET);
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
            $this->show_warning('no_goodstype_selected');
            return;
        }
        $this->log_item = $ids;

        /* 根据param做相应处理 */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), 'back_goodstype', 'admin.php?app=goodstype&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_goodstype_deleted');
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
     * 删除商品类型
     */
    function drop()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new GoodsType($id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        if ($info['attr_count'] > 0)
        {
            $this->show_message('drop_not_allowed');
            return;
        }
        $this->log_item = $id;
        /* 删除 */
        if ($mod->drop())
        {
            $this->log_item = $id;
            $this->show_message('drop_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
        }
    }

    /**
     * 添加
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            /* 参数 */
            $info = array(
                'store_id'    => $_SESSION['store_id']
            );

            $info['type_name'] = empty($_POST['type_name']) ? '' : trim($_POST['type_name']);
            if (empty($info['type_name']))
            {
                $this->show_warning('name_required');
                return;
            }

            if ($this->_mng->get_id($info['type_name']) > 0)
            {
                $this->show_warning('name_exist');
                return;
            }

            /* 插入 */
            $this->log_item = $this->_mng->add($info);

            $this->show_message('add_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
            return;
        }
    }

    /**
     * 编辑
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new GoodsType($id, $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $this->assign('info', $info);

            $this->display('mall/goods_type.detail.html');
        }
        else
        {
            /* 参数 */
            $info = array(
                'store_id'    => $_SESSION['store_id']
            );

            $info['type_id'] = empty($_POST['id']) ? 0 : intval($_POST['id']);
            if ($info['type_id'] <= 0)
            {
                $this->show_warning('Hacking Attempt: id');
                return;
            }

            $mod = new GoodsType($info['type_id'], $_SESSION['store_id']);
            $arr = $mod->get_info();
            if (empty($arr))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $info['type_name'] = empty($_POST['type_name']) ? '' : trim($_POST['type_name']);
            if (empty($info['type_name']))
            {
                $this->show_warning('name_required');
                return;
            }

            $type_id = $this->_mng->get_id($info['type_name']);
            if ($type_id != 0 && $type_id != $info['type_id'])
            {
                $this->show_warning('name_exist');
                return;
            }

            /* 更新 */
            $mod->update($info);
            $this->log_item = $info['type_id'];

            $this->show_message('edit_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
            return;
        }
    }

    /**
     * 判断名称是否重复
     */
    function duplicate_name()
    {
        /* 参数 */
        $id   = empty($_POST['id']) ? 0 : intval($_POST['id']);
        $name = empty($_POST['name']) ? '' : trim($_POST['name']);
        if ($name == '')
        {
            $this->json_error('name_required');
            return;
        }

        /* 查询 */
        $type_id = $this->_mng->get_id($name);
        if ($type_id != $id && $type_id != 0)
        {
            $this->json_error('name_exist');
            return;
        }
        else
        {
            $this->json_result();
            return;
        }
    }
}

?>