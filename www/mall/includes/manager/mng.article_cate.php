<?php

/**
 * ECMALL: 文章分类管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.article_cate.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ArticleCateManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->ArticleCateManager($store_id);
    }

    function ArticleCateManager($store_id)
    {
        $this->_store_id = $store_id;
    }

    /**
     * 获取文章分类列表
     *
     * @param   int  $page
     * @param   array  $condition
     *
     * @return  array
     */
    function get_list($page = 0, $condition = null)
    {
        $start  = ($page - 1) * $number;
        $where  = $this->_make_condition($condition);

        $sql .= "SELECT * FROM `ecm_article_cate` WHERE $where LIMIT $start, $number";
        $res = $GLOBALS['db']->getAll($sql);
        return $res;
    }

    /**
     * 构造查询条件
     *
     * @param   array  $condition
     *
     * @return  array
     */
    function _make_condition($condition)
    {
        $where = '1';

        if ($this->_store_id > 0)
        {
            $where .= " AND store_id = " . $this->_store_id;
        }
        return $where;
    }

    /**
     * 获取文章分类列表
     *
     * @param   string  $app
     * @param   string  $act
     * @param   int     $item_id
     *
     * @return  array
     */
    function get_options($selected_value = 0)
    {
        $where = $this->_make_condition(null);

        $sql = "SELECT cate_id, cate_name FROM `ecm_article_cate` WHERE $where";
        $res = $GLOBALS['db']->getAll($sql);

        $arr = array();

        foreach($res AS $key => $val)
        {
            $arr[$val['cate_id']] = $val['cate_name'];
        }
        return $arr;
    }
};
?>