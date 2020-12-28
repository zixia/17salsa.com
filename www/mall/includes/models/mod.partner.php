<?php

/**
 * ECMALL: 店铺实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.partner.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Partner extends Model
{
    /**
     * 构造函数
     */
    function __construct($id, $store_id = 0)
    {
        $this->Partner($id, $store_id);
    }

    function Partner($id, $store_id = 0)
    {
        $this->_table = '`ecm_partner`';
        $this->_key   = 'partner_id';
        $this->_id    = $id;
        $this->_store_id = $store_id;
    }

    function update($data)
    {
        $info = $this->get_info();

        if ($data['partner_logo'] && is_file($info['partner_logo']))
        {
            unlink($info['partner_logo']);
        }

        return parent::update($data);
    }

    /**
     * @删除记录
     * @author redstone
     *
     * @return void
     */
    function drop()
    {
        $info = $this->get_info();
        if ($info['partner_logo'] && is_file($info['partner_logo']) && is_writable($info['partner_logo']))
        {
            unlink($info['partner_logo']);
        }
        parent::drop();
    }
}
?>