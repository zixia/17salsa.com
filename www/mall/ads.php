<?php

/**
 * ECMALL: 广告处理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ads.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/models/mod.ad.php');

class AdsController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'jump');

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->AdsController($act);
    }

    function AdsController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 显示广告
     *
     * @return  void
     */
    function view()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $ad = new Ad($id);

        $info = $ad->get_info();
        $code = '';
        if ($info['position_id'] == 0)
        {
            $code .= "document.write(\"";
            $code .= addcslashes($ad->get_code(), "\"");
            $code .= "\");";

            $code = ecm_iconv(CHARSET, $_GET['encoding'], $code);
        }

        echo $code;
    }

    /**
     * 广告跳转处理
     *
     * @return  void
     */
    function jump()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $ad = new Ad($id);
        $ad->click_count();
        $this->redirect($_GET['url']);
    }
}
?>