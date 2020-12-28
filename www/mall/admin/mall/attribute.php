<?php

/**
 * ECMALL: 属性控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: attribute.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.attribute.php');
include_once(ROOT_PATH . '/includes/models/mod.attribute.php');

class AttributeController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->AttributeController($act);
    }

    function AttributeController($act)
    {
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
        $type_id = empty($_GET['type_id']) ? 0 : intval($_GET['type_id']);
        if ($type_id == 0)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->assign('type_id', $type_id);

        include_once(ROOT_PATH . '/includes/models/mod.goodstype.php');
        $mod = new GoodsType($type_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning($this->lang('Hacking Attempt', 'back', 'admin.php?app=goodstype&act=view'));
            return;
        }

        /* 取得列表 */
        $this->_mng = new AttributeManager($_SESSION['store_id'], $type_id);
        $list = $this->_mng->get_list($this->get_page());
        foreach ($list['data'] as $key => $row)
        {
            $list['data'][$key]['value_range'] = str_replace("\n", "; ", $row['value_range']);
        }
        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('attribute_stats', $info['type_name'], $list['info']['rec_count']));

        /* 显示模版 */
        $this->display('mall/attribute.view.html');
    }

    /**
     * ajax编辑
     */
    function modify()
    {
        /* 参数 */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('attr_name', 'sort_order')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        /* 检查名称是否重复 */
        $id = intval($_GET['id']);
        $value = trim($_GET['value']);
        $this->log_item = $id;
        $mod = new Attribute($id);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->json_error('record_not_exist');
            return;
        }

        $this->_mng = new AttributeManager($_SESSION['store_id'], $info['type_id']);
        $attr_id = $this->_mng->get_id($value);
        if ($attr_id != $id && $attr_id != 0)
        {
            $this->json_error('name_exist');
            return;
        }

        $this->_modify('Attribute', $_GET);
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
            $this->show_warning('no_record_selected');
            return;
        }
        $this->log_item = $ids;
        $arr = explode('|', $param);
        $param = $arr[0];
        $type_id = intval($arr[1]);
        $this->_mng = new AttributeManager($_SESSION['store_id'], $type_id);

        /* 根据param做相应处理 */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), 'back_attribute', 'admin.php?app=attribute&amp;act=view&amp;type_id=' . $type_id);
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_record_deleted');
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
     * 删除属性
     */
    function drop()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $this->log_item = $id;
        $mod = new Attribute($id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* 删除 */
        if ($mod->drop())
        {
            $this->log_item = $id;
            $this->show_message('drop_ok', 'back_attribute', 'admin.php?app=attribute&act=view&type_id=' . $info['type_id']);
            return;
        }
    }

    /**
     * 添加
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            $type_id = empty($_GET['type_id']) ? 0 : intval($_GET['type_id']);
            if ($type_id == 0)
            {
                $this->show_warning('Hacking Attempt', '', 'admin.php?app=goodstype&amp;act=view');
                return;
            }

            require_once(ROOT_PATH . '/includes/models/mod.goodstype.php');
            $goods_type = new GoodsType($type_id, $_SESSION['store_id']);
            $goods_type_info = $goods_type->get_info();
            if (empty($goods_type_info))
            {
                $this->show_warning('Hacking Attempt', '', 'admin.php?app=goodstype&amp;act=view');
                return;
            }

            /* 初始化 */
            $info = array(
                'attr_id'     => 0,
                'type_id'     => $type_id,
                'type_name'   => $goods_type_info['type_name'],
                'input_type'  => 'text',
                'search_type' => 'no',
                'if_link'     => 0
            );
            $this->assign('info', $info);

            if ($goods_type_info['attr_group'])
            {
                $attr_group_list = explode("\r", $goods_type_info['attr_group']);
                $this->assign('attr_group_list', $attr_group_list);
            }

            $this->display('mall/attribute.detail.html');
        }
        else
        {
            /* 参数 */
            $info = $this->_check_param(0);
            if ($info === false)
            {
                return;
            }

            /* 插入 */
            $this->log_item = $this->_mng->add($info);

            $this->show_message('add_ok',
                'continue_add', 'admin.php?app=attribute&act=add&type_id=' . $info['type_id'],
                'back_attribute', 'admin.php?app=attribute&act=view&type_id=' . $info['type_id']);
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

            $mod = new Attribute($id, $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $this->assign('info', $info);

            $this->display('mall/attribute.detail.html');
        }
        else
        {
            /* 参数 */
            $id = empty($_POST['id']) ? 0 : intval($_POST['id']);
            $mod = new Attribute($id, $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('Attribute not exist: ' . $id);
                return;
            }

            $info = $this->_check_param($id);
            if ($info === false)
            {
                return;
            }

            /* 更新 */
            $mod->update($info);
            $this->log_item = $info['attr_id'];

            $this->show_message('edit_ok',
                'back_attribute', 'admin.php?app=attribute&act=view&type_id=' . $info['type_id']);
        }
    }

    /**
     * 判断名称是否重复
     */
    function duplicate_name()
    {
        /* 参数 */
        $id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $name = empty($_GET['name']) ? '' : trim($_GET['name']);
        $type_id = empty($_GET['type_id']) ? 0 : intval($_GET['type_id']);

        /* 查询 */
        $this->_mng = new AttributeManager($_SESSION['store_id'], $type_id);
        if (empty($this->_mng->goods_type))
        {
            $this->show_warning('Goods_type not exist: ' . $type_id);
            return;
        }
        $attr_id = $this->_mng->get_id($name);
        if ($attr_id != $id && $attr_id != 0)
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

    /**
     * 处理 post 参数
     *
     * @param   int     $id     记录id
     * @return  array/bool      成功返回数组，失败返回false
     */
    function _check_param($id)
    {
        $info = array(
            'attr_id' => $id
        );

        $info['type_id'] = empty($_POST['type_id']) ? 0 : intval($_POST['type_id']);
        $this->_mng = new AttributeManager($_SESSION['store_id'], $info['type_id']);
        if (empty($this->_mng->goods_type))
        {
            $this->show_warning('Goods_type not exist: ' . $info['type_id']);
            return false;
        }

        $info['attr_name'] = empty($_POST['attr_name']) ? '' : trim($_POST['attr_name']);
        if (empty($info['attr_name']))
        {
            $this->show_warning('name_required');
            return false;
        }

        $attr_id = $this->_mng->get_id($info['attr_name']);
        if ($attr_id != 0 && $attr_id != $id)
        {
            $this->show_warning('name_exist');
            return false;
        }

        $info['input_type'] = empty($_POST['input_type']) ? '' : $_POST['input_type'];
        if (!in_array($info['input_type'], array_keys($this->lang('input_type_list'))))
        {
            $this->show_warning('Hacking Attempt: input_type');
            return false;
        }

        $info['value_range'] = empty($_POST['value_range']) ? '' : trim($_POST['value_range']);
        $info['sort_order'] = empty($_POST['sort_order']) ? 0 : intval($_POST['sort_order']);

//        $info['search_type'] = empty($_POST['search_type']) ? '' : $_POST['search_type'];
//        if (!in_array($info['search_type'], array_keys($this->lang('search_type_list'))))
//        {
//            $this->show_warning('Hacking Attempt: search_type');
//            return false;
//        }
//
//        $info['if_link'] = empty($_POST['if_link']) ? '' : $_POST['if_link'];
//        if (!in_array($info['if_link'], array_keys($this->lang('if_link_list'))))
//        {
//            $this->show_warning('Hacking Attempt: if_link');
//            return false;
//        }
//
//        $info['attr_group'] = empty($_POST['attr_group']) ? 0 : intval($_POST['attr_group']);

        return $info;
    }
}

?>