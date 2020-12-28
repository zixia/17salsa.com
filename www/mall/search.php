<?php

/**
 * ECMALL: 商品搜索控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: search.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('CTRL_DOMAIN', 'mall');

require_once(ROOT_PATH . '/includes/ctl.searchbase.php');

class SearchController extends SearchBaseController
{
    var $_allowed_actions = array('view');

    function __construct($act)
    {
        $this->SearchController($act);
    }
    function SearchController($act)
    {
        parent::__construct($act);
    }

    /*
     * 显示搜索结果
     *
     * @author  weberliu
     * @return void
     */
    function view()
    {
        $_GET['keywords'] = trim($_GET['keywords']);
        if (empty($_GET['keywords']) && empty($_GET['tag_words']))
        {
            $_GET['app'] = 'category';

            $arr = array();

            foreach ($_GET AS $key=>$val)
            {
                $arr[] = "{$key}={$val}";
            }

            $target_url = 'index.php?' . implode('&', $arr);
            $this->redirect($target_url);
        }
        else
        {
            if (parent::view() == false)
            {
                return;
            }
            $this->display('goods_list', 'mall');
        }
    }

}

?>