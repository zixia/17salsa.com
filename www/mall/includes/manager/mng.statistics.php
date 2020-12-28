<?php

/**
 * ECMALL:
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.statistics.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StatisticsManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id = 0)
    {
        $this->StatisticsManager($store_id);
    }

    function StatisticsManager($store_id = 0)
    {
        $this->_store_id = intval($store_id);
    }

    /**
     * 得到有订单的会员数
     *
     * @author redstone
     *
     * @return int
     */
    function get_have_order_user($store_id)
    {
        if ($store_id)
        {
            $where = " AND store_id='$store_id'";
        }
        $sql = "SELECT COUNT(DISTINCT user_id) FROM `ecm_order_info` WHERE user_id>0 AND order_status " . db_create_in(array(ORDER_STATUS_DELIVERED, ORDER_STATUS_SHIPPED)) . $where;
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 得到所有订单的总金额
     *
     * @author  redstone
     * @param   string      $type 取值范围(user, guest, null)
     * @param   int         $store_id   店铺id
     *
     * @return  int
     */
    function get_order_amount($type = '', $store_id = 0)
    {
        $where = "order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ")";
        if ($type == 'user')
        {
            $where .= ' AND user_id>0';
        }
        elseif ($type == 'guest')
        {
            $where .= ' AND user_id=0';
        }
        if ($store_id)
        {
            $where .= " AND store_id='$store_id'";
        }
        $sql = "SELECT SUM(order_amount) FROM `ecm_order_info` WHERE $where";
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 得到所有订单数
     *
     * @author  redstone
     * @param   string      $type 取值范围(user, guest, null)
     * @param   int         $store_id   店铺id
     *
     * @return  int
     */
    function get_order_count($type = '', $store_id = 0)
    {
        $where = "order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ")";
        if ($type == 'user')
        {
            $where .= ' AND user_id>0';
        }
        elseif ($type == 'guest')
        {
            $where .= ' AND user_id=0';
        }
        if ($store_id)
        {
            $where .= " AND store_id='$store_id'";
        }
        $sql = "SELECT COUNT(*) FROM `ecm_order_info` WHERE $where";
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 得到订单商品的列表
     *
     * @author  redstone
     * @param   int     $page       当前页
     * @param   fix     $condition  查询及排序条件 默认为空
     * @param   int     $pagesize   每页显示条数 默认0 返回全部
     *
     * @return int
     */
    function get_sale_list ($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->sale_list_query_params($page, $condition, $pagesize);
        if ($pagesize == 0)
        {
            $arg['limit'] = $arg['start'] = $arg['number'] = '';
        }
        else
        {
            $arg['start'] .= ',';
            $arg['limit'] = 'LIMIT';
        }
        $sql = "SELECT og.*, o.order_sn, o.add_time FROM `ecm_order_goods` og " .
               " LEFT JOIN `ecm_order_info` o" .
               " ON og.order_id=o.order_id" .
               " WHERE $arg[where]" .
               " ORDER BY $arg[sort] $arg[order] $arg[limit] $arg[start] $arg[number]";
        $data = $GLOBALS['db']->getAll($sql);
        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * 得到商品销售排行的列表
     *
     * @author  redstone
     * @param   int     $page       当前页
     * @param   fix     $condition  查询及排序条件 默认为空
     * @param   int     $pagesize   每页显示条数 默认0 返回全部
     *
     * @return int
     */
    function get_sale_order ($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->sale_order_query_params($page, $condition, $pagesize);
        if ($pagesize == 0)
        {
            $arg['limit'] = $arg['start'] = $arg['number'] = '';
        }
        else
        {
            $arg['start'] .= ',';
            $arg['limit'] = 'LIMIT';
        }
        $sql = "SELECT SUM(og.goods_number) AS sales_volume, SUM(og.goods_price*goods_number) AS sales_amount, SUM(og.goods_price*goods_number)/SUM(og.goods_number) AS avg_price, og.sku,  og.spec_id, og.goods_name" .
               " FROM `ecm_order_goods` og " .
               " LEFT JOIN `ecm_order_info` o" .
               " ON og.order_id=o.order_id" .
               " WHERE $arg[where]" .
               " GROUP BY spec_id" .
               " ORDER BY $arg[sort] $arg[order] $arg[limit] $arg[start] $arg[number]";
        $data = $GLOBALS['db']->getAll($sql);
        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * 得到会员排行的列表
     *
     * @author  redstone
     * @param   int     $page       当前页
     * @param   fix     $condition  查询及排序条件 默认为空
     * @param   int     $pagesize   每页显示条数 默认0 返回全部
     *
     * @return int
     */
    function get_user_order ($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->user_order_query_params($page, $condition, $pagesize);
        if ($pagesize == 0)
        {
            $arg['limit'] = $arg['start'] = $arg['number'] = '';
        }
        else
        {
            $arg['start'] .= ',';
            $arg['limit'] = 'LIMIT';
        }
        $sql = "SELECT SUM(o.goods_amount) AS order_amount, u.user_name, COUNT(o.order_id) AS order_total" .
               " FROM `ecm_order_info` o" .
               " LEFT JOIN `ecm_users` u" .
               " ON o.user_id=u.user_id" .
               " WHERE $arg[where]" .
               " GROUP BY o.user_id" .
               " ORDER BY $arg[sort] $arg[order] $arg[limit] $arg[start] $arg[number]";
        $data = $GLOBALS['db']->getAll($sql);
        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * 得到店铺排行的列表
     *
     * @author  redstone
     * @param   int     $page       当前页
     * @param   fix     $condition  查询及排序条件 默认为空
     * @param   int     $pagesize   每页显示条数 默认0 返回全部
     *
     * @return int
     */
    function get_store_order ($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->store_order_query_params($page, $condition, $pagesize);
        if ($pagesize == 0)
        {
            $arg['limit'] = $arg['start'] = $arg['number'] = '';
        }
        else
        {
            $arg['start'] .= ',';
            $arg['limit'] = 'LIMIT';
        }
        $sql = "SELECT s.store_id, s.store_name, s.add_time AS open_time, COUNT(o.order_id) AS order_count, SUM(o.order_amount) AS order_amount" .
               " FROM `ecm_store` s" .
               " LEFT JOIN `ecm_order_info` o" .
               " ON s.store_id=o.store_id" .
               " WHERE $arg[where]" .
               " GROUP BY s.store_id" .
               " ORDER BY $arg[sort] $arg[order] $arg[limit] $arg[start] $arg[number]";
        $data = $GLOBALS['db']->getAll($sql);
        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * 得到访问购买率的列表
     *
     * @author  redstone
     * @param   int     $page       当前页
     * @param   fix     $condition  查询及排序条件 默认为空
     * @param   int     $pagesize   每页显示条数 默认0 返回全部
     *
     * @return int
     */
    function get_visit_sold ($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->visit_sold_query_params($page, $condition, $pagesize);
        if ($pagesize == 0)
        {
            $arg['limit'] = $arg['start'] = $arg['number'] = '';
        }
        else
        {
            $arg['start'] .= ',';
            $arg['limit'] = 'LIMIT';
        }
        $sql = "SELECT goods_id, goods_name, click_count, sales_volume, cart_volumn, order_volumn" .
               " FROM `ecm_goods`" .
               " WHERE $arg[where]" .
               " ORDER BY $arg[sort] $arg[order] $arg[limit] $arg[start] $arg[number]";
        $data = $GLOBALS['db']->getAll($sql);
        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * 处理查询条件以及分页的函数 专为 get_visit_sold 服务
     *
     * @author      redstone
     * @param       int     $page       当前页
     * @param       array   $condition  条件
     * @pagesize    int     $pagesize   每页显示的记录数
     *
     */
    function visit_sold_query_params($page, $condition, $pagesize)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arg['number']  = $this->_get_pagesize();
        $arg['start'] = ($page - 1) * $pagesize;
        $arg['where'] = ($condition['store_id'] ? "store_id=$condition[store_id] AND " : '') . "click_count>0";
        $sql = "SELECT COUNT(*)" .
               " FROM `ecm_goods`" .
               " WHERE $arg[where]";
        $arg['count'] = $GLOBALS['db']->getOne($sql);
        $arg['info'] = $this->_page_info($page, $arg['count']);
        $arg['sort'] = isset($_GET['sort']) && in_array($_GET['sort'], array('click_count', 'order_volumn', 'cart_volumn')) ? $_GET['sort'] : 'sales_volume';
        $arg['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        return $arg;
    }

    /**
     * 处理查询条件以及分页的函数 专为 get_user_order 服务
     *
     * @author      redstone
     * @param       int     $page       当前页
     * @param       array   $condition  条件
     * @pagesize    int     $pagesize   每页显示的记录数
     *
     */
    function user_order_query_params($page, $condition, $pagesize)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arg['number']  = $this->_get_pagesize();
        $arg['start'] = ($page - 1) * $pagesize;
        $arg['where'] = "o.order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ") AND o.add_time>$condition[start_time] AND o.add_time<$condition[end_time] AND o.user_id>0";
        $sql = "SELECT COUNT(DISTINCT o.user_id)" .
               " FROM `ecm_order_info` o" .
               " WHERE $arg[where]";
        $arg['count'] = $GLOBALS['db']->getOne($sql);
        $arg['info'] = $this->_page_info($page, $arg['count']);
        $arg['sort'] = isset($_GET['sort']) && $_GET['sort'] == 'order_amount' ? 'order_amount' : 'order_total';
        $arg['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        return $arg;
    }

    /**
     * 处理查询条件以及分页的函数 专为 get_store_order 服务
     *
     * @author      redstone
     * @param       int     $page       当前页
     * @param       array   $condition  条件
     * @pagesize    int     $pagesize   每页显示的记录数
     *
     */
    function store_order_query_params($page, $condition, $pagesize)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arg['number']  = $this->_get_pagesize();
        $arg['start'] = ($page - 1) * $pagesize;
        $arg['where'] = "o.order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ") AND o.add_time>$condition[start_time] AND o.add_time<$condition[end_time]";
        $sql = "SELECT COUNT(DISTINCT store_id)" .
               " FROM `ecm_order_info` o" .
               " WHERE $arg[where]";
        $arg['count'] = $GLOBALS['db']->getOne($sql);
        $arg['info'] = $this->_page_info($page, $arg['count']);
        $arg['sort'] = isset($_GET['sort']) && $_GET['sort'] == 'order_amount' ? 'order_amount' : 'order_count';
        $arg['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        return $arg;
    }

    /**
     * 处理查询条件以及分页的函数 专为 get_sale_order 服务
     *
     * @author      redstone
     * @param       int     $page       当前页
     * @param       array   $condition  条件
     * @pagesize    int     $pagesize   每页显示的记录数
     *
     */
    function sale_order_query_params($page, $condition, $pagesize)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arg['number']  = $this->_get_pagesize();
        $arg['start'] = ($page - 1) * $pagesize;
        $arg['where'] = ($condition['store_id'] ? "o.store_id=$condition[store_id] AND " : '') . "o.order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ") AND o.add_time>$condition[start_time] AND o.add_time<$condition[end_time]";
        $sql = "SELECT COUNT(DISTINCT og.spec_id)" .
               " FROM `ecm_order_goods` og " .
               " LEFT JOIN `ecm_order_info` o" .
               " ON og.order_id=o.order_id" .
               " WHERE $arg[where]";
        $arg['count'] = $GLOBALS['db']->getOne($sql);
        $arg['info'] = $this->_page_info($page, $arg['count']);
        $arg['sort'] = isset($_GET['sort']) && in_array($_GET['sort'], array('sales_amount', 'avg_price')) ? $_GET['sort'] : 'sales_volume';
        $arg['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        return $arg;
    }

    /**
     * 处理查询条件以及分页的函数 专为 get_sale_list 服务
     *
     * @author      redstone
     * @param       int     $page       当前页
     * @param       array   $condition  条件
     * @pagesize    int     $pagesize   每页显示的记录数
     *
     */
    function sale_list_query_params($page, $condition, $pagesize)
    {
        if ($pagesize > 0) $this->set_pagesize($pagesize);
        $arg['number']  = $this->_get_pagesize();
        $arg['start'] = ($page - 1) * $pagesize;
        $arg['where'] = ($condition['store_id'] ? "o.store_id=$condition[store_id] AND " : '') . "o.order_status IN (" . ORDER_STATUS_DELIVERED . ", " . ORDER_STATUS_SHIPPED . ") AND o.add_time>$condition[start_time] AND o.add_time<$condition[end_time]";
        $sql = "SELECT COUNT(*) FROM `ecm_order_goods` og " .
               " LEFT JOIN `ecm_order_info` o" .
               " ON og.order_id=o.order_id" .
               " WHERE $arg[where]";
        $arg['count'] = $GLOBALS['db']->getOne($sql);
        $arg['info'] = $this->_page_info($page, $arg['count']);
        $arg['sort'] = isset($_GET['sort']) && in_array($_GET['sort'], array('order_sn', 'goods_number', 'goods_price', 'addtime')) ? $_GET['sort'] : 'add_time';
        $arg['sort'] = in_array($arg['sort'], array('order_sn', 'add_time')) ? 'o.' . $arg['sort'] : 'og.' . $arg['sort'];
        $arg['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        return $arg;
    }

    /**
     * 取得销售统计数据
     *
     * @author  scottye
     *
     * @param   string  $type           类型 year quarter month day store
     * @param   bool    $inc_shipped    是否包括状态为 shipped 的订单
     * @param   string  $time_field     时间字段 add_time | ship_time
     * @param   int     $year           年
     * @param   int     $month          月
     * @param   array   $condition      限制条件
     * @return  array
     */
    function get_sale_data($type, $inc_shipped, $time_field, $year, $month, $condition = array())
    {
        if ($type == 'store')
        {
            return $this->_get_sale_data_by_store($inc_shipped, $time_field, $year, $month, $condition);
        }
        else
        {
            return $this->_get_sale_data_by_time($type, $inc_shipped, $time_field, $year, $month);
        }
    }

    /**
     * 取得按时间分组统计的销售数据
     *
     * @param   string  $type           时间类型 year | quarter | month | day
     * @param   bool    $inc_shipped    是否包括状态为 shipped 的订单
     * @param   string  $time_field     时间字段
     * @param   int     $year           年
     * @param   int     $month          月
     * @return  array('quantity' => , 'revenue' => )
     */
    function _get_sale_data_by_time($type, $inc_shipped, $time_field, $year, $month)
    {
        switch ($type)
        {
            case 'year':
                $date_format = '%Y';
                $max         = 0;
                break;
            case 'quarter':
                $date_format = '%c';
                $max         = 4;
                break;
            case 'month':
                $date_format = '%c';
                $max         = 12;
                break;
            case 'day':
                $date_format = '%e';
                $max         = date('t', strtotime($year . '-' . $month . '-1'));
                break;
        }

        /* 初始化 */
        $quantity = array();
        $revenue  = array();
        for ($i = 1; $i <= $max; $i++)
        {
            $quantity[$i] = 0;
            $revenue[$i]  = 0;
        }

        $where = $this->_make_sale_condition($inc_shipped, $time_field, $year, $month);
        $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(" . $time_field . "), '$date_format') AS label, " .
                "COUNT(*) AS quantity, SUM(order_amount) AS revenue " .
                "FROM `ecm_order_info` WHERE " . $where .
                "GROUP BY label ORDER BY label";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if ($type == 'quarter')
            {
                $row['label'] = ceil($row['label'] / 3); // 月转换成季
            }
            $quantity[$row['label']] += $row['quantity'];
            $revenue[$row['label']]  += $row['revenue'];
        }

        return array('quantity' => $quantity, 'revenue' => $revenue);
    }

    /**
     * 取得按店铺分组统计的销售数据
     *
     * @param   bool    $inc_shipped    状态为 shipped 的订单是否包含在内
     * @param   string  $time_field     时间字段
     * @param   int     $year           年
     * @param   int     $month          月
     * @param   array   $condition      限制条件 store_ids | limit
     * @param   array   $store_ids      店铺ids
     * @return  array('store' => , 'quantity' => , 'revenue' =>)
     */
    function _get_sale_data_by_store($inc_shipped, $time_field, $year, $month, $condition)
    {
        $where = $this->_make_sale_condition($inc_shipped, $time_field, $year, $month);
        $sql = "SELECT store_id, COUNT(*) AS quantity, SUM(order_amount) AS revenue " .
                "FROM `ecm_order_info` WHERE " . $where .
                "GROUP BY store_id ORDER BY revenue DESC";
        $quantity = array();
        $revenue  = array();
        if (isset($condition['store_ids']))
        {
            foreach ($condition['store_ids'] as $store_id)
            {
                $quantity[$store_id] = 0;
                $revenue[$store_id]  = 0;
            }
        }
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $quantity[$row['store_id']] = $row['quantity'];
            $revenue[$row['store_id']]  = $row['revenue'];
        }

        /* 限制条件之外的记入其他 */
        if (isset($condition['limit']))
        {
            $limit = intval($limit);
            if ($limit > 0 && count($quantity) > $limit)
            {
                $temp_quantity = array('label_other' => 0);
                $temp_revenue  = array('label_other' => 0);
                $i    = 1;
                foreach ($quantity as $key => $value)
                {
                    if ($i > $limit)
                    {
                        $temp_quantity['label_other'] += $value;
                        $temp_revenue['label_other']  += $value;
                    }
                    else
                    {
                        $temp_quantity[$key] = $value;
                        $temp_revenue[$key]  = $value;
                    }
                    $i++;
                }
                $quantity = $temp_quantity;
                $revenue  = $temp_revenue;
            }
        }
        elseif (isset($condition['store_ids']))
        {
            $store_ids = $condition['store_ids'];
            $temp_quantity = array('label_other' => 0);
            $temp_revenue  = array('label_other' => 0);
            foreach ($quantity as $key => $value)
            {
                if (in_array($key, $store_ids))
                {
                    $temp_quantity[$key] = $value;
                    $temp_revenue[$key]  = $value;
                }
                else
                {
                    $temp_quantity['label_other'] += $value;
                    $temp_revenue['label_other']  += $value;
                }
            }
            if (empty($temp_quantity['label_other']) && empty($temp_revenue['label_other']))
            {
                unset($temp_quantity['label_other']);
                unset($temp_revenue['label_other']);
            }
            $quantity = $temp_quantity;
            $revenue  = $temp_revenue;
        }

        return array('quantity' => $quantity, 'revenue' => $revenue);
    }

    /**
     * 取得销售统计店铺
     *
     * @param   array   $store_ids
     * @return  array(store_id => store_name)
     */
    function get_sale_store($store_ids)
    {
        $store = array();
        $sql = "SELECT store_id, store_name " .
                "FROM `ecm_store` " .
                "WHERE store_id " . db_create_in($store_ids);
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $store[$row['store_id']] = $row['store_name'];
        }

        return $store;
    }

    /**
     * 返回销售统计的条件语句
     *
     * @author  scottye
     *
     * @param   bool    $inc_shipped    是否包含状态为 shipped 的订单
     * @param   string  $time_field     时间字段
     * @param   int     $year           年
     * @param   int     $month          月
     * @return  string
     */
    function _make_sale_condition($inc_shipped, $time_field, $year, $month)
    {
        $where = $inc_shipped ?
            " order_status " . db_create_in(array(ORDER_STATUS_SHIPPED, ORDER_STATUS_DELIVERED)) :
            " order_status = " . ORDER_STATUS_DELIVERED;

        if ($this->_store_id > 0)
        {
            $where .= " AND store_id = '" . $this->_store_id . "'";
        }

        if ($year > 0)
        {
            if ($month > 0)
            {
                $start_time  = gmmktime(0, 0, 0, $month, 1, $year);
                $end_time    = gmmktime(23, 59, 59, $month, date('t', strtotime($year . '-' . $month . '-1')), $year);
            }
            else
            {
                $start_time  = gmmktime(0, 0, 0, 1, 1, $year);
                $end_time    = gmmktime(23, 59, 59, 12, 31, $year);
            }
            $where .= " AND " . $time_field . " >= '$start_time' AND " . $time_field . " <= '$end_time'";
        }

        return $where . " ";
    }

    /**
     * 取得订单统计数据
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   array   $condition
     * @param   int     $limit
     * @return  array
     */
    function get_order_data($type, $condition, $limit = 0)
    {
        $where = ($this->_store_id > 0) ? " WHERE store_id = '" . $this->_store_id . "' " : " WHERE 1 ";
        switch ($type)
        {
            case 'status':
                $inc_shipped = empty($condition['inc_shipped']) ? false : true;
                $data = $this->_get_order_data_by_status($where, $inc_shipped);
                break;
            case 'shipping':
                $data = $this->_get_order_data_by_shipping($where);
                break;
            case 'payment':
                $data = $this->_get_order_data_by_payment($where);
                break;
            case 'region':
                $if_top = empty($condition['if_top']) ? false : true;
                $data = $this->_get_order_data_by_region($where, $if_top);
                break;
            default:
                $data = array();
                break;
        }

        /* 超过limit的累计入其他 */
        if ($limit > 0 && in_array($type, array('shipping', 'payment', 'region')) && count($data) > $limit)
        {
            $temp = array('label_other' => 0);
            $i    = 1;
            foreach ($data as $key => $value)
            {
                if ($i > $limit)
                {
                    $temp['label_other'] += $value;
                }
                else
                {
                    $temp[$key] = $value;
                }
                $i++;
            }
            $data = $temp;
        }

        return $data;
    }

    /**
     *  取得按状态统计的数据
     *
     * @author  scottye
     *
     * @param   string  $where
     * @param   bool    $inc_shipped
     * @return  array
     */
    function _get_order_data_by_status($where, $inc_shipped)
    {
        $data = array(
            'label_unfinished' => 0,
            'label_finished'   => 0,
            'label_invalid'    => 0,
            'label_rejected'   => 0,
        );

        $sql = "SELECT order_status AS label, COUNT(*) AS value " .
                "FROM `ecm_order_info` " . $where .
                "GROUP BY label";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            switch ($row['label'])
            {
                case ORDER_STATUS_TEMPORARY:
                case ORDER_STATUS_PENDING:
                case ORDER_STATUS_SUBMITTED:
                case ORDER_STATUS_ACCEPTTED:
                case ORDER_STATUS_PROCESSING:
                    $data['label_unfinished']     += $row['value'];
                    break;
                case ORDER_STATUS_SHIPPED:
                    if ($inc_shipped)
                    {
                        $data['label_finished']   += $row['value'];
                    }
                    else
                    {
                        $data['label_unfinished'] += $row['value'];
                    }
                    break;
                case ORDER_STATUS_DELIVERED:
                    $data['label_finished']       += $row['value'];
                    break;
                case ORDER_STATUS_INVALID:
                    $data['label_invalid']        += $row['value'];
                    break;
                case ORDER_STATUS_REJECTED:
                    $data['label_rejected']       += $row['value'];
                    break;
                default:
                    $data['label_other']          += $row['value'];
                    break;
            }
        }

        return $data;
    }

    /**
     * 取得按配送方式统计的数据
     *
     * @author  scottye
     *
     * @param   string  $where
     * @return  array
     */
    function _get_order_data_by_shipping($where)
    {
        $data = array();
        $sql = "SELECT shipping_name AS label, COUNT(*) AS value " .
                "FROM `ecm_order_info` " . $where .
                "GROUP BY label ORDER BY value DESC ";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $data[$row['label']] = $row['value'];
        }

        return $data;
    }

    /**
     * 取得按支付统计的数据
     *
     * @author  scottye
     *
     * @param   string  $where
     * @return  array
     */
    function _get_order_data_by_payment($where)
    {
        $data = array();
        $sql = "SELECT pay_name AS label, COUNT(*) AS value " .
                "FROM `ecm_order_info` " . $where .
                "GROUP BY label ORDER BY value DESC ";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $data[$row['label']] = $row['value'];
        }

        return $data;
    }

    /**
     * 取得按收货人地区统计的数据
     *
     * @author  scottye
     *
     * @param   string  $where
     * @param   bool    $if_top
     * @return  array
     */
    function _get_order_data_by_region($where, $if_top)
    {
        $regions = $this->_get_order_region();

        $data = array();
        $sql = "SELECT region_id, COUNT(*) AS value " .
                "FROM `ecm_order_info` " . $where .
                "GROUP BY region_id ORDER BY value DESC ";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $region_id = $row['region_id'];
            $region_name = $regions[$region_id]['region_name'];
            while ($regions[$region_id]['parent_id'] > 0)
            {
                $region_id = $regions[$region_id]['parent_id'];
                $region_name = $if_top ? $regions[$region_id]['region_name'] : $regions[$region_id]['region_name'] . $region_name;
            }
            $data[$region_name] = $row['value'];
        }

        return $data;
    }

    /**
     * 取得地区数据
     *
     * @author  scottye
     *
     * @return  array
     */
    function _get_order_region()
    {
        $regions = array();
        $sql = "SELECT region_id, parent_id, region_name FROM `ecm_regions`";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $regions[$row['region_id']] = $row;
        }

        return $regions;
    }
}

?>