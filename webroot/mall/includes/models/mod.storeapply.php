<?php

/**
 * ECMALL: 开店申请实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.storeapply.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreApply extends Model
{
    /**
     * 构造函数
     */
    function __construct($apply_id)
    {
        $this->StoreApply($apply_id);
    }

    function StoreApply($apply_id)
    {
        $this->_table = '`ecm_store_apply`';
        $this->_key   = 'apply_id';
        parent::Model($apply_id);
    }

    /**
     * 取得开店申请的信息
     *
     * @author  weberliu
     * @return  array
     */
    function get_info()
    {
        $sql = "SELECT a.*, u.user_name, r.region_name FROM $this->_table AS a ".
                "LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id ".
                "LEFT JOIN `ecm_regions` AS r ON r.region_id=a.store_location ".
                "WHERE a.$this->_key='$this->_id'";
        $res = $GLOBALS['db']->getRow($sql);

        return $res;
    }

}
?>