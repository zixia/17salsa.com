<?php

/**
 * ECMALL: 友情链接管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.partner.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class PartnerManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->PartnerManager($store_id);
    }

    function PartnerManager($store_id)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * 获得友情链接列表
     *
     * @return  array
     */
    function get_list($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'sort_order', $pagesize);

        $sql = "SELECT * FROM `ecm_partner` WHERE " .$arg['where'].
               " ORDER BY sort_order LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);

        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     * 获得符合条件的记录总数
     *
     * @param   array   $condition
     *
     * @return  int
     */
    function get_count($condition)
    {
        $where  = $this->_make_condition($condition);
        $sql    = "SELECT COUNT(*) FROM `ecm_partner` WHERE $where";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }
    /**
     * 新增一个友情链接
     *
     * @param  array    $post
     *
     * @return  int
     */
    function add($post)
    {
        $post['store_id']   = $this->_store_id;

        $res = $GLOBALS['db']->autoExecute('`ecm_partner`', $post);

        return $res;
    }
    /**
     * 根据给定的友情链接名称获得友情链接的ID
     *
     * @author  weberliu
     * @param   string      $name       友情链接名称
     * @param   string      $not_ids    需要排除的友情链接的ID，使用逗号分隔
     * @return  int
     */
    function get_partner_id($name, $not_ids = '0')
    {
        $sql = "SELECT partner_id FROM `ecm_partner` WHERE partner_name='$name' AND partner_id NOT " .db_create_in($not_ids). " AND store_id=" . $this->_store_id;
        $res = $GLOBALS['db']->getOne($sql);

        return ($res) ? $res : 0;
    }

    /**
     * 创建查询条件
     *
     * @param   array   $condition
     *
     * @return  string
     */
    function _make_condition($condition)
    {
        $where = " store_id = '" . $this->_store_id . "'";

        if (!empty($condition['is_recommend']))
        {
            $where .= " AND is_recommend=1";
        }

        if (!empty($condition['is_certified']))
        {
            $where .= " AND is_certified=1";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND store_name LIKE '%$condition[keywords]%'";
        }

        return $where;
    }

    /**
     * 删除友情链接
     *
     * @author  weberliu
     * @params  string      $ids    需要删除的友情链接的ID，使用逗号分隔
     * @return  bool
     */
    function drop($ids)
    {
        if (empty($ids))
        {
            $this->err = 'undefined';
            return false;
        }
        $tmp = array();
        $arr_id = explode(',', $ids);
        foreach ($arr_id as $key=>$val)
        {
            if ($val = intval(trim($val)))
            {
                $tmp[] = $val;
            }
        }
        $ids = implode(',', $tmp);
        if (empty($ids))
        {
            $this->err = 'data_illegal';
            return false;
        }
        $sql = "DELETE FROM `ecm_partner` WHERE partner_id IN ($ids) AND store_id='" . $this->_store_id . "'";
        $GLOBALS['db']->query($sql);
        return true;
    }

};
?>