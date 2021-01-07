<?php

/**
 * ECMALL: 出租方案实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.rentscheme.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class RentScheme extends Model
{
    /**
     * 构造函数
     */
    function __construct($id)
    {
        $this->RentScheme($id);
    }

    function RentScheme($id)
    {
        $this->_table = '`ecm_rent_scheme`';
        $this->_key   = 'scheme_id';
        parent::Model($id);
    }
}
?>