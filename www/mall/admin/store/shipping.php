<?php

/**
 * ECMALL: 配送方式控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id$
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.shipping.php');
include_once(ROOT_PATH . '/includes/models/mod.shipping.php');
include_once(ROOT_PATH . '/includes/models/mod.shippingfee.php');

class ShippingController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->ShippingController($act);
    }

    function ShippingController($act)
    {
        $this->_mng = new ShippingManager($_SESSION['store_id']);

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
        $list = $this->_mng->get_list();
        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('shipping_stats', $list['info']['rec_count'], $list['info']['enabled']));

        /* 显示模版 */
        $this->display('store/shipping.view.html');
    }

    /**
     * 卸载
     */
    function drop()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($this->_mng->drop($id))
        {
            $this->show_message('drop_ok', 'back_shipping', 'admin.php?app=shipping&act=view');
            return;
        }
        else
        {
            $this->show_warning('record_not_exist');
            return;
        }
    }

    /**
     * 安装
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            $this->assign('is_enable_radio', array(1 => $this->lang('yes'),
                                                   0 => $this->lang('no')));
            $this->assign('info', array('shipping_fee' => '10.00',
                                        'surcharge'    => '0.00',
                                        'enabled'      => 1));
            $this->assign('is_add', 1);

            $this->display('store/shipping.detail.html');
        }
        else
        {

            /* 提交信息 */
            $info = $_POST['shipping'];

            /* 插入 */
            $id = $this->_mng->add($info);
            if (!$id)
            {
                $this->show_warning($this->_mng->err);

                return;
            }
            $this->log_item = $id;

            $this->show_message('add_ok', 'back_shipping', 'admin.php?app=shipping&act=view');
            return;
        }
    }

    /**
     *  编辑
     *
     *  @param  none
     *  @return void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            $mod = new Shipping($id, $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }
            include_once(ROOT_PATH . '/includes/manager/mng.region.php');
            $region_mng = new RegionManager(0);
            $regions = $region_mng->get_regions($info['cod_regions']);

            $this->assign('is_enable_radio', array(1 => $this->lang('yes'),
                                                   0 => $this->lang('no')));
            $this->assign('info', $info);
            $this->assign('regions', $regions);
            $this->display('store/shipping.detail.html');
        }
        else
        {
            /* 参数 */
            $id = empty($_POST['id']) ? 0 : intval($_POST['id']);
            $this->log_item = $id;
            $affected_rows = $this->_mng->update($_POST['shipping'], $id);
            if ($affected_rows === FALSE)
            {
                $this->show_warning($this->_mng->err);

                return;
            }

            $this->show_message('edit_ok', 'back_shipping', 'admin.php?app=shipping&act=view');
            return;
        }
    }

    /**
     *  Ajax编辑
     *
     *  @author liupeng
     *  @return void
     */
    function modify()
    {
        $id = intval($_GET['id']);
        $column = trim($_GET['column']);
        $value = trim($_GET['value']);
        $enable_columns = array('shipping_name', 'shipping_fee', 'surcharge', 'enabled');
        if (!in_array($column, $enable_columns))
        {
            $this->json_error('not_editable');

            return;
        }

        if ($id < 0)
        {
            $this->json_error('id is undefined!');
            return;
        }
        $this->log_item = $id;
        $data = array($column=>$value);
        if($this->_mng->update($data, $id))
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
}

?>
