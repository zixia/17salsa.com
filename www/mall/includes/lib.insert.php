<?php
/**
 * ECMall: 动态内容函数库
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: lib.insert.php 6009 2008-10-31 01:55:52Z Garbin $
 */


/**
 * 获取广告位广告
 *
 * @access  public
 *
 * @return  string
 */
function insert_ads($par)
{
    if (!class_exists(AdPosition))
    {
        require_once(ROOT_PATH . '/includes/models/mod.ad_position.php');
    }

    $adp = new AdPosition($par['id']);

    return $adp->get_ads($par['template']->edit_mode);
}

/**
 * 解析模板中的{nocache}
 *
 * @author wj
 * @param  string $param
 * @return string
 */
function insert_nocache($param)
{
    error_reporting(E_ALL ^ E_NOTICE);
    $str = $param['template']->_eval(stripslashes($param['_template']));
    error_reporting($param['template']->_errorlevel);
    return $str;
}

?>