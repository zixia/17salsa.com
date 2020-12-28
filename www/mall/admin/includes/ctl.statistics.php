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
 * $Id: ctl.statistics.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH. '/includes/cls.xml.php');
include_once(ROOT_PATH . '/includes/manager/mng.statistics.php');

class StatisticsController extends ControllerBackend
{

    var $mng = null;

    function __construct($act)
    {
        $this->StatisticsController($act);
    }

    function StatisticsController($act)
    {
        if (empty($act))
        {
            $act = '';
        }
        $this->mng = new StatisticsManager($_SESSION['store_id']);
        parent::__construct($act);
    }

    /**
     * 查看客户统计
     *
     * @author  redstone
     *
     * @return  void
     */
    function guest_stats()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.user.php');
        $mng_user = new UserManager();
        $stats_data['user_total'] = $mng_user->get_count(array());
        $stats_data['order_total'] = $this->mng->get_order_count('', $this->get_store_id());
        $stats_data['user_order_total'] = $this->mng->get_order_count('user', $this->get_store_id());

        $stats_data['have_order'] = $this->mng->get_have_order_user($this->get_store_id());
        $stats_data['guest_sale_rate'] = $this->str_format('%0.2f', $stats_data['have_order'] * 100 / $stats_data['user_total']);

        if ($stats_data['user_order_amount'] = $this->mng->get_order_amount('user', $this->get_store_id()))
        {
            $stats_data['user_average_order'] = $this->str_format('%0.3f', $stats_data['user_order_total'] / $stats_data['user_total']);
            $stats_data['user_average_amount'] = $this->str_format('%0.3f', $stats_data['user_order_amount'] / $stats_data['user_total']);
        }
        else
        {
            $stats_data['user_order_amount'] = $stats_data['user_average_order'] = $stats_data['user_average_amount'] = '-';
        }

        if ($stats_data['guest_order_total'] = $stats_data['order_total'] - $stats_data['user_order_total'])
        {
            $stats_data['guest_order_amount'] = $this->mng->get_order_amount('guest', $this->get_store_id());
            $stats_data['guest_average_amount'] = $this->str_format('%0.3f', $stats_data['guest_order_amount'] / $stats_data['guest_order_total']);
        }
        else
        {
            $stats_data['guest_order_total'] = $stats_data['guest_order_amount'] = $stats_data['guest_average_amount'] = '-';
        }

        if ($_GET['do'] == 'downxls')
        {
            $values = array();
            $values[] = array($this->lang('guest_sale_rate'));
            $values[] = array($this->lang('member_count'), $stats_data['user_total'], $this->lang('have_order'), $stats_data['have_order']);
            $values[] = array($this->lang('order_count'), $stats_data['order_total'], $this->lang('guest_sale_rate'), $stats_data['guest_sale_rate'] . '%');

            $values[] = array($this->lang('user_average_order_amount'));
            $values[] = array($this->lang('user_order_amount'), price_format($stats_data['user_order_amount']), $this->lang('user_average_order'), $stats_data['user_average_order']);
            $values[] = array($this->lang('user_average_amount'), price_format($stats_data['user_average_amount']));

            $values[] = array($this->lang('guest_average_order_amount'));
            $values[] = array($this->lang('guest_order_amount'), price_format($stats_data['guest_order_amount']), $this->lang('guest_order_count'), $stats_data['guest_order_total']);
            $values[] = array($this->lang('guest_average_amount'), price_format($stats_data['guest_average_amount']));

            $this->out_xls($values, 'guest_stats');
        }
        else
        {
            $this->assign('stats_data', $stats_data);
            $this->display('stats_guest.html');
        }
    }

    /**
     * 查看销售明细
     *
     * @author  redstone
     *
     * @return  void
     */
    function sale_list()
    {
        $condition['start_time'] = $this->handle_date($_REQUEST['start_date'], -7);
        $condition['end_time'] = $this->handle_date($_REQUEST['end_date'], 1) + 86399;
        $condition['store_id'] = $this->get_store_id();
        $page = max(1, intval($_GET['page']));
        $pagesize = $_GET['do'] == 'downxls' ? 0 : 20;
        $list = $this->mng->get_sale_list($page, $condition, $pagesize);
        if ($_GET['do'] == 'downxls')
        {
            $outdata[] = array(
                    $this->lang('goods_name'),
                    $this->lang('order_sn'),
                    $this->lang('goods_number'),
                    $this->lang('goods_price'),
                    $this->lang('sale_date'),
                    );
            foreach ($list['data'] as $key => $val)
            {
                $val['goods_price'] = price_format($val['goods_price']);
                $val['add_time'] = local_date($this->conf('mall_time_format_simple'), $val['add_time']);
                $outdata[] = array(
                        $val['goods_name'],
                        $val['order_sn'],
                        $val['goods_number'],
                        $val['goods_price'],
                        $val['add_time'],
                        );
            }
            $this->out_xls($outdata, 'sale_list', $condition);
        }
        else
        {
            $this->select_store();
            $this->assign('condition', $condition);
            $this->assign('sale_list_stats',  $this->str_format('sale_list_stats', $list['info']['rec_count'], $list['info']['page_count']));
            $this->assign('list', $list);
            $this->assign('url_format', 'admin.php?app=statistics&act=sale_list');
            $this->display('stats_sale_list.html');
        }
    }

    /**
     * 查看销售排行
     *
     * @author  redstone
     *
     * @return  void
     */
    function sale_order()
    {
        $condition['start_time'] = $this->handle_date($_REQUEST['start_date'], -7);
        $condition['end_time'] = $this->handle_date($_REQUEST['end_date'], 1) + 86399;
        $condition['store_id'] = $this->get_store_id();
        $page = max(1, intval($_GET['page']));
        $pagesize = $_GET['do'] == 'downxls' ? 0 : 20;
        $list = $this->mng->get_sale_order($page, $condition, $pagesize);
        if ($_GET['do'] == 'downxls')
        {
            $outdata[] = array(
                    $this->lang('order_num'),
                    $this->lang('goods_name'),
                    $this->lang('goods_sku'),
                    $this->lang('sales_volume'),
                    $this->lang('sales_amount'),
                    $this->lang('avg_price'),
                    );
            foreach ($list['data'] as $key => $val)
            {
                $val['avg_price'] = price_format($val['avg_price']);
                $outdata[] = array(
                        ($page - 1) * $pagesize + $key + 1,
                        $val['goods_name'],
                        $val['sku'],
                        $val['sales_volume'],
                        price_format($val['sales_amount']),
                        $val['avg_price'],
                        );
            }
            $this->out_xls($outdata, 'sale_order', $condition);
        }
        else
        {
            foreach ($list['data'] as $key => $val)
            {
                $val['order_num'] = ($page - 1) * $pagesize + $key + 1;
                $list['data'][$key] = $val;
            }
            $this->select_store();
            $this->assign('condition', $condition);
            $this->assign('sale_order_stats',  $this->str_format('sale_list_stats', $list['info']['rec_count'], $list['info']['page_count']));
            $this->assign('list', $list);
            $this->assign('url_format', 'admin.php?app=statistics&act=sale_order');
            $this->display('stats_sale_order.html');
        }
    }

    /**
     * 查看会员排行
     *
     * @author  redstone
     *
     * @return  void
     */
    function user_order()
    {
        if ($_SESSION['store_id'])
        {
            $this->show_warning('not_allow_operate');
            return;
        }

        $condition['start_time'] = $this->handle_date($_REQUEST['start_date'], -7);
        $condition['end_time'] = $this->handle_date($_REQUEST['end_date'], 1) + 86399;
        $page = max(1, intval($_GET['page']));
        $pagesize = $_GET['do'] == 'downxls' ? 0 : 20;
        $list = $this->mng->get_user_order($page, $condition, $pagesize);
        if ($_GET['do'] == 'downxls')
        {
            $outdata[] = array(
                    $this->lang('order_num'),
                    $this->lang('user_name'),
                    $this->lang('order_total'),
                    $this->lang('order_amount'),
                    );
            foreach ($list['data'] as $key => $val)
            {
                $outdata[] = array(
                        ($page - 1) * $pagesize + $key + 1,
                        $val['user_name'],
                        $val['order_total'],
                        price_format($val['order_amount']),
                        );
            }
            $this->out_xls($outdata, 'user_order', $condition);
        }
        else
        {
            foreach ($list['data'] as $key => $val)
            {
                $val['order_num'] = ($page - 1) * $pagesize + $key + 1;
                $val['uchome_url'] = uc_home_url($val['user_id']);
                $list['data'][$key] = $val;
            }
            $this->assign('condition', $condition);
            $this->assign('user_order_stats',  $this->str_format('sale_list_stats', $list['info']['rec_count'], $list['info']['page_count']));
            $this->assign('list', $list);
            $this->assign('url_format', 'admin.php?app=statistics&act=user_order');
            $this->display('stats_user_order.html');
        }
    }

    /**
     * 查看访问购买率
     *
     * @author  redstone
     *
     * @return  void
     */
    function visit_sold()
    {
        $condition['store_id'] = $this->get_store_id();
        $page = max(1, intval($_GET['page']));
        $pagesize = $_GET['do'] == 'downxls' ? 0 : 20;
        $list = $this->mng->get_visit_sold($page, $condition, $pagesize);
        if ($_GET['do'] == 'downxls')
        {
            $outdata[] = array(
                    $this->lang('goods_name'),
                    $this->lang('sales_volume'),
                    $this->lang('pageview'),
                    $this->lang('sales_click_rate'),
                    $this->lang('order_volumn'),
                    $this->lang('cart_volumn'),
                    $this->lang('order_cate_rate'),
                    );
            foreach ($list['data'] as $key => $val)
            {
                $outdata[] = array(
                        $val['goods_name'],
                        $val['sales_volume'],
                        $val['click_count'],
                        $val['click_count'] ? $this->str_format('%0.2f', $val['sales_volume'] * 1000 / $val['click_count']) . '&permil;' : '-',
                        $val['order_volumn'],
                        $val['cart_volumn'],
                        $val['cart_volumn'] ? $this->str_format('%0.2f', $val['order_volumn'] * 100 / $val['cart_volumn']) . '%' : '-',
                        );
            }
            $this->out_xls($outdata, 'visit_sold', $condition);
        }
        else
        {
            foreach ($list['data'] as $key => $val)
            {
                $val['sales_click_rate'] = $val['click_count'] ? $this->str_format('%0.2f', $val['sales_volume'] * 1000 / $val['click_count']) . '&permil;' : '-';
                $val['order_cart_rate'] = $val['cart_volumn'] ? $this->str_format('%0.2f', $val['order_volumn'] * 100 / $val['cart_volumn']) . '%' : '-';
                $list['data'][$key] = $val;
            }
            $this->select_store();
            $this->assign('condition', $condition);
            $this->assign('visit_sold_stats',  $this->str_format('sale_list_stats', $list['info']['rec_count'], $list['info']['page_count']));
            $this->assign('list', $list);
            $this->assign('url_format', 'admin.php?app=statistics&act=visit_sold');
            $this->display('stats_visit_sold.html');
        }
    }

    /**
     * 查看店铺的统计
     *
     * @author  redstone
     *
     * @return  void
     */
    function store_order()
    {
        if ($_SESSION['store_id'])
        {
            $this->show_warning('not_allow_operate');
            return;
        }

        $condition['start_time'] = $this->handle_date($_REQUEST['start_date'], -30);
        $condition['end_time'] = $this->handle_date($_REQUEST['end_date'], 1) + 86399;
        $page = max(1, intval($_GET['page']));
        $pagesize = $_GET['do'] == 'downxls' ? 0 : 20;
        $list = $this->mng->get_store_order($page, $condition, $pagesize);
        if ($_GET['do'] == 'downxls')
        {
            $outdata[] = array(
                    $this->lang('order_num'),
                    $this->lang('store_name'),
                    $this->lang('order_count'),
                    $this->lang('order_amount'),
                    $this->lang('open_time'),
                    );
            foreach ($list['data'] as $key => $val)
            {
                $outdata[] = array(
                        ($page - 1) * $pagesize + $key + 1,
                        $val['store_name'],
                        $val['order_count'],
                        price_format($val['order_amount']),
                        local_date('Y-m-d', $val['add_time']),
                        );
            }
            $this->out_xls($outdata, 'store_order', $condition);
        }
        else
        {
            foreach ($list['data'] as $key => $val)
            {
                $val['order_num'] = ($page - 1) * $pagesize + $key + 1;
                $list['data'][$key] = $val;
            }
            $this->assign('condition', $condition);
            $this->assign('store_order_stats',  $this->str_format('sale_list_stats', $list['info']['rec_count'], $list['info']['page_count']));
            $this->assign('list', $list);
            $this->assign('url_format', 'admin.php?app=statistics&act=store_order');
            $this->display('stats_store_order.html');
        }
    }

    /**
     * 获得查询的store_id
     * @author  redstone
     */
    function get_store_id()
    {
        if ($_SESSION['store_id'])
        {
            return $_SESSION['store_id'];
        }
        else
        {
            if ($_GET['store_id'])
            {
                return intval($_GET['store_id']);
            }
            elseif ($_POST['store_id'])
            {
                return intval($_POST['store_id']);
            }
            else
            {
                return 0;
            }
        }
    }

    /**
     * 查询店铺，为模板中下拉菜单使用
     * @author  redstone
     */
    function select_store()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $new_store = new StoreManager();
        $tmp = $_GET;
        unset($_GET['sort']);
        unset($_GET['order']);
        $store_list = $new_store->get_list(1, array(), 100);
        $store_options = array();
        foreach ($store_list['data'] as $val)
        {
            $store_options[$val['store_id']] = $val['store_name'];
        }
        $this->assign('store_options', $store_options);
        $_GET = $tmp;
    }

    /**
     * 处理提交上来的时间
     *
     * @author  redstone
     * @param   date Y-m-d
     * @param   int  默认的日期 小于零代表过去几天，大于零代表未来几天
     *
     * @return  int timestamp
     */
    function handle_date($date, $days)
    {
        if (empty($date))
        {
            return local_strtotime(local_date('Y-m-d')) + $days * 86400;
        }
        else
        {
            return local_strtotime($date);
        }
    }

    /**
     * 将统计报表输出为 xls 文档
     *
     * @author  redstone
     * @param   fix     $values     数据
     * @param   string  $file_name  文件名称
     * @param   fix     $condition  条件
     *
     * @return  void
     */
    function out_xls($values, $file_name, $condition = null)
    {
        include_once('includes/cls.excelwriter.php');

        $file_name = $this->lang($file_name);
        if ($condition !== null)
        {
            if ($condition['store_id'])
            {
                $file_name .= '_' . $this->conf('store_name', $condition['store_id']);
            }
            else
            {
                $file_name .= '_' . $this->conf('mall_name', 0);
            }
            if ($condition['start_time'] && $condition['end_time'])
            {
                $file_name .= '_' . local_date('Ymd', $condition['start_time']) . '-' . local_date('Ymd', $condition['end_time']);
            }
        }

        $xls = new ExcelWriter(CHARSET, $file_name);
        $xls->add_array($values);
        $xls->output();
    }

    /**
     * 流量统计
     *
     * @author  scottye
     *
     * @return  void
     */
    function view_flow()
    {
        /* 无参数表示页面请求，有参数表示ajax请求和导出请求 */
        $year   = empty($_GET['year'])  ? 0  : intval($_GET['year']);
        $month  = empty($_GET['month']) ? 0  : intval($_GET['month']);
        $type   = empty($_GET['type'])  ? '' : trim($_GET['type']);
        if (   ($year != 0 && ($year < 2000 || $year > 2020))
            || ($month != 0 && ($month < 1 || $month > 12))
            || !in_array($type, array('', 'xml', 'csv'))
            || ($year != 0 && $type == ''))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        /* 根据参数做相应处理 */
        if (empty($year))
        {
            if (empty($type))
            {
                /* 页面请求：显示当前月的日流量、当前年的月流量、所有年的年流量数据 */
                $cur_time  = gmtime();
                $cur_year  = local_date('Y', $cur_time);
                $cur_month = local_date('n', $cur_time);
                $this->assign('year_data',  $this->_flow_get_data('year'));
                $this->assign('month_data', $this->_flow_get_data('month', $cur_year));
                $this->assign('day_data',   $this->_flow_get_data('day',   $cur_year, $cur_month));
                $this->display('stats_flow.html');
            }
            else
            {
                /* 导出所有年的年流量数据 */
                $this->_flow_export('year');
            }
        }
        else
        {
            if (empty($month))
            {
                if ($type == 'xml')
                {
                    /* ajax请求指定年的月流量数据 */
                    $this->json_result($this->_flow_get_data('month', $year));
                }
                else
                {
                    /* 导出指定年的月流量数据 */
                    $this->_flow_export('month', $year);
                }
            }
            else
            {
                if ($type == 'xml')
                {
                    /* ajax请求指定年、月的日流量数据 */
                    $this->json_result($this->_flow_get_data('day', $year, $month));
                }
                else
                {
                    /* 导出指定年、月的日流量数据 */
                    $this->_flow_export('day', $year, $month);
                }
            }
        }
    }

    /**
     * 销售统计
     *
     * @author  scottye
     */
    function view_sale()
    {
        /* 参数 */
        if (empty($_GET['type']))
        {
            /* 首次访问默认参数 */
            $type        = 'day';
            $inc_shipped = true;
            $time_field  = 'add_time';
            $year        = date('Y', time());
            $month       = date('n', time());
            $compare     = false;
            $format      = 'xml';
        }
        else
        {
            $type        = trim($_GET['type']);
            $inc_shipped = empty($_GET['inc_shipped']) ? false : true;
            $time_field  = empty($_GET['time_field']) ? 'add_time' : trim($_GET['time_field']);
            $year        = empty($_GET['year']) ? 0 : intval($_GET['year']);
            $month       = empty($_GET['month']) ? 0 : intval($_GET['month']);
            $compare     = empty($_GET['compare']) ? false : true;
            $format      = empty($_GET['format']) ? 'xml' : trim($_GET['format']);
        }

        if (   !in_array($type, array('year', 'quarter', 'month', 'day', 'store'))
            || ($type == 'store' && $_SESSION['store_id'] > 0)
            || !in_array($time_field, array('add_time', 'ship_time'))
            || !in_array($format, array('xml', 'csv')))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if ($type == 'year')
        {
            $year = 0;
        }
        if (in_array($type, array('year', 'quarter', 'month')))
        {
            $month = 0;
        }
        if ($year == 0)
        {
            $month = 0;
        }

        /* 缺参数，提示 */
        if (in_array($type, array('quarter', 'month', 'day')) && $year <= 0)
        {
            $this->show_warning('pls_select_year');
            return;
        }
        if ($type == 'day' && $month <= 0)
        {
            $this->show_warning('pls_select_month');
            return;
        }

        /* 处理请求 */
        if ($format == 'xml')
        {
            /* 显示页面 */
            $types = $this->lang('sale_type');
            if ($_SESSION['store_id'] > 0)
            {
                unset($types['store']);
            }
            $this->assign('types', $types);
            $this->assign('years', $this->_get_years());
            $this->assign('months', $this->_get_months());
            $this->assign('cur_type', $type);
            $this->assign('cur_inc_shipped', $inc_shipped);
            $this->assign('cur_time_field', $time_field);
            $this->assign('cur_year', $year);
            $this->assign('cur_month', $month);
            $this->assign('cur_compare', $compare);
            $this->assign('data', $this->_sale_get_data($type, $inc_shipped, $time_field, $year, $month, $compare));
            $this->display('stats_sale.html');
        }
        else
        {
            /* 导出数据 */
            $this->_sale_export($type, $inc_shipped, $time_field, $year, $month);
        }
    }

    /**
     * 订单统计
     *
     * @author  scottye
     */
    function view_order()
    {
        /* 参数 */
        if (empty($_GET['type']))
        {
            $type      = 'status';
            $condition = array('inc_shipped' => true);
            $format    = 'xml';
        }
        else
        {
            $type   = trim($_GET['type']);
            $format = empty($_GET['format']) ? 'xml' : 'csv';
            if (!in_array($type, array('status', 'shipping', 'payment', 'region')))
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $condition = array();
            if ($type == 'status' && !empty($_GET['inc_shipped']))
            {
                $condition['inc_shipped'] = true;
            }
            if ($type == 'region' && !empty($_GET['if_top']))
            {
                $condition['if_top'] = true;
            }
        }

        /* 处理请求 */
        if ($format == 'xml')
        {
            /* 显示页面 */
            $types = $this->lang('order_type');
            if ($_SESSION['store_id'] == 0)
            {
                unset($types['shipping']);
            }
            $this->assign('types', $types);
            $this->assign('cur_type', $type);
            $this->assign('condition', $condition);
            $this->assign('data', $this->_order_get_data($type, $condition));
            $this->display('stats_order.html');
        }
        else
        {
            /* 导出数据 */
            $this->_order_export($type, $condition);
        }
    }

    /**
     * 取得订单统计数据
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   array   $condition
     * @return  array
     */
    function _order_get_data($type, $condition)
    {
        $setting = $this->_order_get_setting($type);
        $data    = $this->mng->get_order_data($type, $condition, 10);

        /* 语言项 */
        $temp = array();
        foreach ($data as $label => $value)
        {
            if (substr($label, 0, 6) == 'label_')
            {
                $label = $this->lang($label);
            }
            $temp[$label] = $value;
        }
        $data = $temp;

        $xml     = $this->_order_data_to_xml($setting, $data);
        $link    = $this->_order_get_link($type, $condition);

        return array('type' => $type, 'xml' => $xml, 'link' => $link);
    }

    /**
     * 导出订单统计数据
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   array   $condition
     * @return  void
     */
    function _order_export($type, $condition)
    {
        $title = array(
            array($this->lang('order_title.' . $type)),
            array($this->lang('order_name.'. $type), $this->lang('order_total'))
        );
        $data  = $this->mng->get_order_data($type, $condition);

        /* 语言项 */
        $temp = array();
        foreach ($data as $label => $value)
        {
            if (substr($label, 0, 6) == 'label_')
            {
                $label = $this->lang($label);
            }
            $temp[] = array($label, $value);
        }
        $data = $temp;

        $this->out_xls(array_merge($title, $data), $this->lang('order_title.' . $type));
    }

    /**
     * 生成订单统计xml设置
     *
     * @author  scottye
     *
     * @param   string  $type
     * @return  array
     */
    function _order_get_setting($type)
    {
        $setting = array(
            'caption'         => $this->lang('order_title.' . $type),
            'baseFontSize'    => 12,
            'rotateValues'    => 1,
        );

        return $setting;
    }

    /**
     * 订单统计数据转换成xml
     *
     * @author  scottye
     *
     * @param   array   $setting
     * @param   array   $data
     * @return  string
     */
    function _order_data_to_xml($setting, $data)
    {
        $xml = new XmlLib_Node('chart');
        foreach ($setting as $name => $value)
        {
            $xml->setAttribute($name, $value);
        }

        foreach ($data as $label => $value)
        {
            $node =& $xml->createChild('set');
            $node->setAttribute('label', $label);
            $node->setAttribute('value', $value);
        }

        return str_replace("\"", "'", $xml->toString());
    }

    /**
     * 取得订单统计下载链接
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   array   $condition
     * @return  string
     */
    function _order_get_link($type, $condition)
    {
        $url = "admin.php?app=statistics&amp;act=view_order&amp;format=csv&amp;type=" . $type;
        if (!empty($condition['inc_shipped']))
        {
            $url .= "&amp;inc_shipped=1";
        }
        if (!empty($condition['if_top']))
        {
            $url .= "&amp;if_top=1";
        }

        return "<a href='$url'>" . $this->lang('downxls') . "</a>";
    }

    /**
     * 取得流量数据，包括xml格式的数据和导出链接
     *
     * @author  scottye
     *
     * @param   string  $type   类型 year month day
     * @param   int     $year   年份 $type = month | day 时需要
     * @param   int     $month  月份 $type = day 时需要
     * @return  array('xml' => '', 'link' => '')
     */
    function _flow_get_data($type, $year = 0, $month = 0)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.pageview.php');
        $mng_pageview = new PageviewManager($_SESSION['store_id']);

        $setting = $this->_flow_get_setting($type, $year, $month);
        $data    = $mng_pageview->get_data($type, $year, $month);
        if ($type == 'year' || $type == 'month')
        {
            /* 加上更新链接 */
            $url = ($type == 'year') ? 'javascript:update_flow(%s);' : 'javascript:update_flow(' . $year . ', %s);';
            foreach ($data as $key => $item)
            {
                if ($item['value'] > 0)
                {
                    $data[$key]['link'] = $this->str_format($url, $key);
                }
            }
        }
        $xml     = $this->_flow_data_to_xml($setting, $data);
        $link    = $this->_flow_get_link($year, $month);

        return array('type' => $type, 'xml' => $xml, 'link' => $link);
    }

    /**
     * 导出流量数据
     *
     * @author  scottye
     *
     * @param   string  $type   类型 year month day
     * @param   int     $year   年份 $type = month | day 时需要
     * @param   int     $month  月份 $type = day 时需要
     */
    function _flow_export($type, $year = 0, $month = 0)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.pageview.php');
        $mng_pageview = new PageviewManager($_SESSION['store_id']);

        $title = array(
            array($this->str_format($type . '_flow', $year, $month)),
            array($this->lang($type), $this->lang('pageview'))
        );
        $data  = $mng_pageview->get_data($type, $year, $month);

        $this->out_xls(array_merge($title, $data), $this->str_format($type . '_flow', $year, $month));
    }

    /**
     * 取得流量统计的设置
     *
     * @author  scottye
     *
     * @param   string  $type   类型：year month day
     * @param   int     $year   年
     * @param   int     $month  月
     * @return  array
     */
    function _flow_get_setting($type, $year = 0, $month = 0)
    {
        $setting = array(
            'caption'         => $this->str_format($type . '_flow', $year, $month),
            'baseFontSize'    => 12,
            'rotateValues'    => 1,
        );

        return $setting;
    }

    /**
     * 取得导出url
     *
     * @author  scottye
     *
     * @param   int     $year   年份
     * @param   int     $month  月份
     * @return  string
     */
    function _flow_get_link($year = 0, $month = 0)
    {
        $url = "admin.php?app=statistics&amp;act=view_flow&amp;type=csv";
        if (!empty($year))
        {
            $url .= "&amp;year=" . $year;
        }
        if (!empty($month))
        {
            $url .= "&amp;month=" . $month;
        }

        return "<a href='$url'>" . $this->lang('downxls') . "</a>";
    }

    /**
     * 取得销售统计数据 xml格式
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   bool    $inc_shipped
     * @param   string  $time_field
     * @param   int     $year
     * @param   int     $month
     * @param   bool    $compare
     * @return  array
     */
    function _sale_get_data($type, $inc_shipped, $time_field, $year, $month, $compare = false)
    {
        $setting    = $this->_sale_get_setting($type, $year, $month);
        $datasets   = array();
        $data       = $this->mng->get_sale_data($type, $inc_shipped, $time_field, $year, $month, array('limit' => 10));
        $datasets[] = array('name' => $this->_sale_get_series_name($year, $month, 'quantity'), 'data' => $data['quantity'], 'secondary' => 1);
        $datasets[] = array('name' => $this->_sale_get_series_name($year, $month, 'revenue'),  'data' => $data['revenue']);
        if ($type == 'store')
        {
            $store_ids  = array_keys($data['quantity']);
            $categories = $this->mng->get_sale_store($store_ids);
        }
        else
        {
            $categories = array_keys($data['quantity']);
        }

        if ($compare && $year > 0)
        {
            /* 上期数据 */
            if ($month > 0)
            {
                if ($month == 1)
                {
                    $new_year  = $year - 1;
                    $new_month = 12;
                }
                else
                {
                    $new_year  = $year;
                    $new_month = $month - 1;
                }
            }
            else
            {
                $new_year  = $year - 1;
                $new_month = 0;
            }
            $new_data   = $this->mng->get_sale_data($type, $inc_shipped, $time_field, $new_year, $new_month, array('store_ids' => $store_ids));
            $datasets[] = array('name' => $this->_sale_get_series_name($new_year, $new_month, 'quantity'), 'data' => $new_data['quantity'], 'secondary' => 1);
            $datasets[] = array('name' => $this->_sale_get_series_name($new_year, $new_month, 'revenue'),  'data' => $new_data['revenue']);
        }

        $xml        = $this->_sale_data_to_xml($setting, $categories, $datasets);
        $link       = $this->_sale_get_link($type, $inc_shipped, $time_field, $year, $month);

        return array('xml' => $xml, 'link' => $link);
    }

    /**
     * 导出销售统计数据
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   bool    $inc_shipped
     * @param   string  $time_field
     * @param   int     $year
     * @param   int     $month
     * @return  void
     */
    function _sale_export($type, $inc_shipped, $time_field, $year, $month)
    {
        $caption = $this->_sale_get_caption($type, $year, $month);
        $title = array(
            array($caption),
            array($this->lang($type), $this->lang('order_total'), $this->lang('sales_amount'))
        );

        $data  = array();
        $temp  = $this->mng->get_sale_data($type, $inc_shipped, $time_field, $year, $month);
        if (count($temp['quantity']) > 0)
        {
            if ($type == 'store')
            {
                $store_ids = array_keys($temp['quantity']);
                $stores    = $this->mng->get_sale_store($store_ids);
                foreach ($stores as $key => $store_name)
                {
                    $data[] = array($store_name, $temp['quantity'][$key], $temp['revenue'][$key]);
                }
            }
            else
            {
                foreach ($temp['quantity'] as $key => $quantity)
                {
                    $data[] = array($key, $quantity, $temp['revenue'][$key]);
                }
            }
        }

        $this->out_xls(array_merge($title, $data), $caption);
    }

    /**
     * 生成图标系列名称
     *
     * @author  scottye
     *
     * @param   int     $year
     * @param   int     $month
     * @param   string  $datatype   数据 quantity | revenue
     * @return  string
     */
    function _sale_get_series_name($year, $month, $datatype)
    {
        $name = ($datatype == 'quantity') ? $this->lang('order_total') : $this->lang('sales_amount');
        if ($year > 0)
        {
            if ($month > 0)
            {
                $name = $year . $this->lang('year') . str_pad($month, 2, '0', STR_PAD_LEFT) . $this->lang('month') . $name;
            }
            else
            {
                $name = $year . $this->lang('year') . $name;
            }
        }

        return $name;
    }

    /**
     * 生成销售统计标题
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   int     $year
     * @param   int     $month
     * @return  string
     */
    function _sale_get_caption($type, $year, $month)
    {
        if ($type == 'store')
        {
            if ($year > 0)
            {
                if ($month > 0)
                {
                    $caption = $this->str_format('store_month_sale', $year, $month);
                }
                else
                {
                    $caption = $this->str_format('store_year_sale', $year);
                }
            }
            else
            {
                $caption = $this->lang('store_sale');
            }
        }
        else
        {
            $caption = $this->str_format($type . '_sale', $year, $month);
        }

        return $caption;
    }

    /**
     * 返回销售统计图表设置
     *
     * @author  scottye
     *
     * @param   string      $type
     * @param   int         $year
     * @param   int         $month
     * @return  array
     */
    function _sale_get_setting($type, $year, $month)
    {
        $setting = array(
            'caption'       => $this->_sale_get_caption($type, $year, $month),
            'palette'       => 2,
            'baseFontSize'  => 12,
            'labelDisplay'  => WRAP,
            'showvalues'    => 0,
            'PYAxisName'    => $this->lang('sales_amount'),
            'SYAxisName'    => $this->lang('order_total'),
            'rotateYAxisName'   => 0,
            'PYAxisNameWidth'   => 20,
            'SYAxisNameWidth'   => 20,
            'maxColWidth'   => 40,
        );

        return $setting;
    }

    /**
     * 生成销售统计下载链接
     *
     * @author  scottye
     *
     * @param   string  $type
     * @param   bool    $inc_shipped
     * @param   string  $time_field
     * @param   int     $year
     * @param   int     $month
     * @return  string
     */
    function _sale_get_link($type, $inc_shipped, $time_field, $year, $month)
    {
        $url = "admin.php?app=statistics&amp;act=view_sale&amp;format=csv&amp;type=" . $type . "&amp;time_field=" . $time_field;
        if ($inc_shipped)
        {
            $url .= "&amp;inc_shipped=1";
        }
        if (!empty($year))
        {
            $url .= "&amp;year=" . $year;
        }
        if (!empty($month))
        {
            $url .= "&amp;month=" . $month;
        }

        return "<a href='$url'>" . $this->lang('downxls') . "</a>";
    }

    /**
     * 生成到当前时间为止的年份数组
     *
     * @author  scottye
     *
     * @param   int     $start  开始年份
     * @return  array
     */
    function _get_years($start = 2008)
    {
        $years = array(0 => $this->lang('all'));
        for ($i = $start; $i <= date('Y', time()); $i++)
        {
            $years[$i] = $i;
        }
        return $years;
    }

    /**
     * 生成月份数组
     *
     * @author  scottye
     *
     * @return  array
     */
    function _get_months()
    {
        $months = array(0 => $this->lang('all'));
        for ($i = 1; $i <= 12; $i++)
        {
            $months[$i] = $i;
        }
        return $months;
    }

    /**
     * 转换销售统计数据为xml格式
     *
     * @author  scottye
     *
     * @param   array   $setting
     * @param   array   $categories
     * @param   array   $datasets
     * @return  string
     */
    function _sale_data_to_xml($setting, $categories, $datasets)
    {
        $xml = new XmlLib_Node('chart');
        foreach ($setting as $name => $value)
        {
            $xml->setAttribute($name, $value);
        }

        $categories_node =& $xml->createChild('categories');
        foreach ($categories as $label)
        {
            $node =& $categories_node->createChild('category');
            $node->setAttribute('label', $label);
        }

        foreach ($datasets as $dataset)
        {
            $dataset_node =& $xml->createChild('dataset');
            $dataset_node->setAttribute('seriesName', $dataset['name']);
            if (isset($dataset['secondary']))
            {
                $dataset_node->setAttribute('parentYAxis', 'S');
            }
            foreach ($dataset['data'] as $value)
            {
                $node =& $dataset_node->createChild('set');
                $node->setAttribute('value', $value);
            }
        }

        return str_replace("\"", "'", $xml->toString());
    }

    /**
     * 把流量统计数据转换成xml
     *
     * @author  scottye
     *
     * @param   array   $setting    图表设置
     * @param   array   $data       数据
     * @return  string  xml
     */
    function _flow_data_to_xml($setting, $data)
    {
        $xml = new XmlLib_Node('chart');
        foreach ($setting as $name => $value)
        {
            $xml->setAttribute($name, $value);
        }

        foreach ($data as $item)
        {
            $node =& $xml->createChild('set');
            $node->setAttribute('label', $item['label']);
            $node->setAttribute('value', $item['value']);
            if (!empty($item['link']))
            {
                $node->setAttribute('link',  $item['link']);
            }
        }

        return str_replace("\"", "'", $xml->toString());
    }
}

?>
