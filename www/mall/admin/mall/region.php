<?php

/**
 * ECMALL: 地区设置控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: region.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.region.php');

class RegionController extends ControllerBackend
{
    var $_mng = null;

    function __construct($act)
    {
        $this->RegionController($act);
    }

    function RegionController($act)
    {
        $this->_mng = new RegionManager($_SESSION['store_id']);

        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看地区列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false; // 不记日志
        $this->assign('region_list', $this->_mng->get_regions_list());
        $this->assign('top_regions', $this->_mng->get_options(0));
        $this->display('region.view.html', 'mall'); // 显示模版
    }

    /**
     * 添加一个地区
     *
     * @return  void
     */
    function add()
    {
        /* 参数 */
        $region_name = empty($_POST['region_name']) ? '' : trim($_POST['region_name']);
        $parent_id   = empty($_POST['parent_id']) ? 0 : intval($_POST['parent_id']);

        /* 检查参数 */
        $parent  = $this->_mng->get_region($parent_id);
        if (empty($parent))
        {
            $parent_id = 0;
        }

        if ($region_name == '')
        {
            $this->show_warning('name_is_empty');

            return;
        }

        if ($this->_mng->region_name_exist($region_name, $parent_id))
        {
            $this->show_warning('name_exists');

            return;
        }
        /* 插入 */
        $this->log_item = $this->_mng->add($region_name, $parent_id);
        $this->show_message('add_ok', 'go_back', 'admin.php?app=region&act=view&pid=' . $parent_id);
        return;
    }

    /**
     * 修改地区名称（ajax）
     *
     * @return  void
     */
    function update()
    {
        /* 参数 */
        $region_id   = empty($_GET['region_id']) ? 0 : intval($_GET['region_id']);
        $region_name = empty($_GET['region_name']) ? '' : trim($_GET['region_name']);

        /* 检查 */
        $region = $this->_mng->get_region($region_id);
        if (empty($region))
        {
            $this->json_error('record_not_exist', $region['region_name']);
            return;
        }
        if ($region_name == '')
        {
            $this->json_error('name_is_empty', $region['region_name']);
            return;
        }
        if ($this->_mng->region_name_exist($region_name, $region_id))
        {
            $this->json_error('name_exists', $region['region_name']);
            return;
        }

        /* 更新 */
        $this->_mng->update($region_id, $region_name);
        $this->json_result();
        return;
    }

    /**
     * 删除地区
     *
     * @return void
     */
    function drop()
    {
        /* 参数 */
        $region_id = empty($_GET['region_id']) ? 0 : intval($_GET['region_id']);
        $region = $this->_mng->get_region($region_id);
        if (empty($region))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* 检查是否有下级地区 */
        $children = $this->_mng->get_list($region_id);
        if (!empty($children))
        {
            $this->show_warning('region_has_children');
            return;
        }

        /* 删除 */
        if ($this->_mng->drop($region_id))
        {
            $this->log_item = $region_id; // 日志
            $this->show_message('drop_ok', 'go_back', 'admin.php?app=region&act=view&pid=' . $region['parent_id']);
            return;
        }
    }
};

?>