<?php

/**
 * ECMALL: 页面浏览管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.pageview.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class PageviewManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id = 0)
    {
        $this->PageviewManager($store_id);
    }

    function PageviewManager($store_id = 0)
    {
        $this->_store_id = intval($store_id);
        parent::__construct();
    }

    /**
     * 更新pageview
     *
     * @author  scottye
     *
     * @param   void
     */
    function update_pageview()
    {
        $view_date = local_date('Y-m-d', gmtime());
        $sql = "SELECT COUNT(*) FROM `ecm_pageview` " .
                "WHERE store_id = '" . $this->_store_id . "' AND view_date = '" . $view_date . "'";
        if ($GLOBALS['db']->getOne($sql) > 0)
        {
            $sql = "UPDATE `ecm_pageview` SET view_times = view_times + 1 " .
                    "WHERE store_id = '" . $this->_store_id . "' AND view_date = '" . $view_date . "'";
            $GLOBALS['db']->query($sql);
        }
        else
        {
            $sql = "INSERT INTO `ecm_pageview` (store_id, view_date, view_times) " .
                    "VALUES ('" . $this->_store_id . "', '" . $view_date . "', 1)";
            $GLOBALS['db']->query($sql);
        }
    }

    /**
     * 取得某时间段内的流量统计
     *
     * @author  scottye
     *
     * @param   string  $type       统计类型 year month day
     * @param   int     $year       年份
     * @param   int     $month      月份
     * @return  array | false
     */
    function get_data($type = 'year', $year = 0, $month = 0)
    {
        if (!in_array($type, array('year', 'month', 'day')))
        {
            return false;
        }
        $year  = intval($year);
        $month = intval($month);
        switch ($type)
        {
            case 'year' :
                $date_prefix = '';
                $date_format = '%Y';
                $min         = date('Y');
                $max         = date('Y');
                break;
            case 'month':
                $date_prefix = $year;
                $date_format = '%c';
                $min         = 1;
                $max         = 12;
                break;
            case 'day'  :
                $date_prefix = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $date_format = '%e';
                $min         = 1;
                $max         = date('t', strtotime($date_prefix . '-01'));
                break;
        }

        /* 初始化 */
        $data = array();
        for ($i = $min; $i <= $max; $i++)
        {
            $data[$i] = array('label' => $i, 'value' => 0);
        }

        $where = " 1 ";
        if ($this->_store_id > 0)
        {
            $where .= " AND store_id = '" . $this->_store_id . "' ";
        }
        if (!empty($date_prefix))
        {
            $where .= " AND view_date LIKE '" . $date_prefix . "%' ";
        }
        $sql = "SELECT DATE_FORMAT(view_date, '$date_format') AS label, SUM(view_times) AS value " .
                "FROM `ecm_pageview` " .
                "WHERE " . $where .
                "GROUP BY label ORDER BY label";
        $res  = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $data[$row['label']] = $row;
        }

        return $data;
    }
}

?>