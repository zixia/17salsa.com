<?php

/**
 * ECMALL: 导航实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.nav.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Nav extends Model
{
    /**
     * 构造函数
     */
    function __construct($nav_id)
    {
        $this->Nav($nav_id);
    }
    function Nav($nav_id)
    {
        $this->_table = '`ecm_nav`';
        $this->_key   = 'nav_id';
        parent::Model($nav_id);
    }

    /**
     * 更新导航菜单项
     *
     * @author  liupeng
     * @param   array  $arr 菜单项信息
     * @return  bool
     */
    function update($arr)
    {
        foreach ($arr as $key => $value)
        {
            if ($key == 'nav_name')
            {
                if (empty($value))
                {
                    $this->err = 'nav_name_empty';
                    return false;
                }

                if (($id = $this->get_by_name($value)) && $id != $this->_id)
                {
                    $this->err = 'nav_name_duplication';
                    return false;
                }
            }
        }
        return parent::update($arr);
    }

    function get_by_name($name)
    {
        $sql = "SELECT * FROM `ecm_nav` WHERE nav_name='$name'";
        return $GLOBALS['db']->getOne($sql);
    }
}
?>