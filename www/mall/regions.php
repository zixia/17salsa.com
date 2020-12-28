<?php

/**
 * ECMALL: 地区选择
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: regions.php 6052 2008-11-13 03:24:36Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.region.php');

class RegionsController extends ControllerFrontend
{
    var $_mng = null;
    var $_allowed_actions = array('load', 'get', 'get_all_regions');

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->RegionsController($act);
    }

    function RegionsController($act)
    {
        $this->_mng = new RegionManager(0);
        !method_exists($this, $act) && $act = 'load';
        parent::ControllerFrontend($act);
    }

    function load()
    {
        $this->logger = false; // 不记日志

        /* 参数 */
        $pid   = empty($_GET['parent']) ? 0 : intval($_GET['parent']);
        $level = empty($_GET['level']) ?  0 : intval($_GET['level']);

        /* 取得地区 */
        $arr = array(
            'regions' => ($pid > 0 || $level == 1) ? $this->_mng->get_list($pid) : array(),
            'level'   => $level
        );

        /* 返回 */
        $this->json_result($arr);
        return;
    }

    /**
     *  获取地区数据
     *
     *  @access
     *  @param
     *  @return
     */

    function get()
    {
        $this->logger = false;
        $regions = $this->_mng->get_all();
        $js_data = 'Regions = ' . ecm_json_encode($regions) . ';';
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        echo $js_data;
    }
}

?>
