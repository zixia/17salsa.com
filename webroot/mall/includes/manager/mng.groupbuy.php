<?php

/**
 * ECMall: :团购活动管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.groupbuy.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include(ROOT_PATH. '/includes/manager/mng.activity.php');

class GroupBuyManager extends ActivityManager
{
    function __construct($store_id=0)
    {
        $this->GroupBuyManager($store_id);
    }

    function GroupBuyManager($store_id=0)
    {
        parent::__construct(ACT_GROUPBUY, $store_id);
    }

    function get_list($page, $condition=array(), $pagesize = 0)
    {
        $list = parent::get_list($page, $condition, $pagesize);
        //对扩展信息反序列化
        $func = create_function('&$arr', '$arr[\'ext\']=unserialize($arr[\'ext_info\']);');
        array_walk($list['data'], $func);

        return $list;

    }
};
?>