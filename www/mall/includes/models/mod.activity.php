<?php

/**
 * ECMALL: 活动实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.activity.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Activity extends Model
{
    var $_type  = null;

    function __construct($act_type, $act_id=0, $store_id=0)
    {
        $this->Activity($act_type, $act_id, $store_id);
    }

    function Activity($act_type, $act_id=0, $store_id=0)
    {
        $this->_table = '`ecm_goods_activity`';
        $this->_key   = 'act_id';
        $this->_type = $act_type;

        parent::__construct($act_id, $store_id);
    }

    function get_info()
    {
        $arr = parent::get_info();

        if (!empty($arr))
        {
            /* 重新组合扩展信息 */
            $ext = unserialize($arr['ext_info']);

            foreach ($ext AS $key=>$val)
            {
                $arr[$key] = $val;
            }

            unset($arr['ext_info']);

            /* 转换时间格式 */
            $arr['start_time']  = local_date('Y-m-d', $arr['start_time']);
            $arr['end_time']    = local_date('Y-m-d', $arr['end_time']);
        }

        return $arr;
    }
}

?>
