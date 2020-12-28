<?php

/**
 * ECMALL: 订单管理控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: order.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/models/mod.order.php');     //加载order模型
include_once(ROOT_PATH . '/includes/models/mod.goods.php');     //加载goods模型
include_once(ROOT_PATH . '/includes/manager/mng.order.php');    //加载order管理模型
include_once(ROOT_PATH . '/includes/manager/mng.goods.php');    //加载goods管理模型
include_once(ROOT_PATH . '/includes/manager/mng.region.php');    //加载地区管理模型
include_once(ROOT_PATH . '/includes/manager/mng.payment.php');   //加载支付方式管理模型
include_once(ROOT_PATH . '/includes/manager/mng.shipping.php');  //加载配送方式管理模型
include_once(ROOT_PATH . '/includes/lib.shopping.php');          //购物流程函数库


class OrderController extends ControllerBackend
{
    var $order_manager = NULL;
    var $_order_status_list = array (
          1 => 'order_status_pending',
          2 => 'order_status_submitted',
          3 => 'order_status_acceptted',
          4 => 'order_status_processing',
          5 => 'order_status_shipped',
          6 => 'order_status_delivered',
          7 => 'order_status_invalid',
          8 => 'order_status_rejected',
        );
    function __construct($act)
    {
        $this->OrderController($act);
    }

    function OrderController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看店铺列表
     *
     *  @access public

     * @return  void
     */
    function view()
    {
        $this->logger = false; //不记录日志
        $conditions = array();

        /* 搜索 */

        foreach ($_GET as $_k => $_v)
        {
            switch($_k)
            {
            case 'start_date':
            case 'end_date':
                if ($_v)
                {
                    $conditions[$_k] = local_strtotime($_v);
                }
                break;
            case 'order_amount_start':
                if (!empty($_v))
                {
                    $conditions['order_amount']['start'] = $_v;
                }
                break;
            case 'order_amount_end':
                if (!empty($_v))
                {
                    $conditions['order_amount']['end'] = $_v;
                }
                break;
            case 'goods_amount_start':
                if (!empty($_v))
                {
                    $conditions['goods_amount']['start'] = $_v;
                }
                break;
            case 'goods_amount_end':
                if (!empty($_v))
                {
                    $conditions['goods_amount']['end'] = $_v;
                }
                break;
            case 'order_status':
                if ($_v != '-1')
                {
                    if (strpos($_v, ',') !== false)
                    {
                        $_ss = explode(',', $_v);
                        array_walk($_ss, create_function('&$item, $key', '$item=intval($item);'));
                        $conditions[$_k] = $_ss;
                    }
                    else
                    {
                        $conditions[$_k] = (int)$_v;
                    }
                }
                break;
            case 'keywords':
                if ($_v)
                {
                    $conditions['consignee'] =  $_v;
                    $conditions['address']   =  $_v;
                    $conditions['order_sn']  =  $_v;
                }
                break;
            case 'store_name':
                if ($_v)
                {
                    $conditions[$_k] = trim($_v);
                }
                break;
            case 'user_id':
                if ($_v)
                {
                    $conditions[$_k] = intval($_v);
                }
                break;
            case 'unevaluated':
                if ($_v)
                {
                    $conditions['unevaluated'] = 'buyer';
                }
                break;
            }
        }
        $user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        if ($user_id)
        {
            $conditions['user_id']  = $user_id;
        }

        $order_manager  =   new OrderManager($_SESSION['store_id']);
        $orders         = $order_manager->get_list($this->get_page(), $conditions);

        foreach ($orders['data'] as $_k => $_v)
        {
            $orders['data'][$_k]['status'] = $order_manager->get_status_code($_v['order_status']);
        }

        $orders['data'] = deep_local_date($orders['data'], 'add_time', 'm-d H:i');
        $orders['data'] = addslashes_deep($orders['data']);
        $order_status   = $this->_order_status_list;
        foreach ($order_status as $_k => $_v)
        {
            $order_status[$_k] = $this->lang($_v);
        }

        /* 统计信息部分 */
        include_once(ROOT_PATH . '/admin/includes/inc.stat.php');

        /* 订单搜索部分 */
        $this->assign('search_by_status',   $conditions['order_status'] ? $conditions['order_status']:-1);

        /* 订单列表部分 */
        $this->assign('order_status',   $order_status);
        $this->assign('orders',         $orders['data']);
        $this->assign('page_info',      $orders['info']);
        $this->assign('special',        intval($_GET['special']));
        $this->assign('url_format',     "admin.php?app=order&amp;act=view&amp;page=%d&amp;conditions[order_status]={$_GET['order_status']}&amp;conditions[keywords]={$_GET['keywords']}&amp;conditions[start_date]={$_GET['start_date']}&amp;conditions[end_date]={$_GET['end_date']}&amp;conditions[order_amount][start]={$_GET['order_amount_start']}&amp;conditions[order_amount][end]={$_GET['order_amount_end']}");
        $this->assign('order_stats',    $this->str_format('order_stats', $orders['info']['rec_count'], $orders['info']['page_count'], get_order_count('unlimitted', 'wait_for_ship', 'number', $_SESSION['store_id']), date('Y-m-d',get_today_timestamp()), get_order_count('today', 'new', 'number', $_SESSION['store_id'])));
        $this->display('order.view.html', 'store');
    }

    /**
     *  待发货的订单
     *
     *  @param
     *  @return
     */
    function processing_order()
    {
        $this->redirect('admin.php?app=order&act=view&special=1&order_status=' . ORDER_STATUS_ACCEPTTED . ',' . ORDER_STATUS_PROCESSING);
    }

    /**
     *  订单日志查看
     *
     *  @access
     *  @params
     *  @return
     */

    function view_logs()
    {
        $this->logger = false; //不记录日志
        $order_sn       = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $action_time    = empty($_REQUEST['action_time']) ? 0 : local_strtotime($_REQUEST['action_time']);
        $remark         = empty($_REQUEST['remark'])  ? '' : trim($_REQUEST['remark']);

        if ($order_sn)
        {
            $conditions['order_sn'] = $order_sn;
        }
        if ($action_time)
        {
            $conditions['action_time'] = $action_time;
        }
        if ($remark)
        {
            $conditions['remark']   = $remark;
        }

        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');

        $order_status = $this->_order_status_list;
        foreach ($order_status as $_k => $_v)
        {
            $order_status[$_k] = $this->lang($_v);
        }

        $order_log_manager  = new OrderLogger($_SESSION['store_id']);
        $logs               = $order_log_manager->get_list($this->get_page(), $conditions);
        $logs['data']       = deep_local_date($logs['data'], 'action_time', $this->conf('mall_time_format_complete'));

        $this->assign('logs',           $logs);
        $this->assign('order_status',   $order_status);
        $this->assign('logs_stat',      $this->str_format('logs_stat', $logs['info']['rec_count'], $logs['info']['page_count']));
        $this->display('order.view_logs.html', 'store');
    }
    /**
     *  批量删除
     *
     *  @access public
     *  @param
     *  @return
     */

    function drop()
    {
        if (!is_array($_REQUEST['order_ids']))
        {
            $order_ids = empty($_REQUEST['order_ids']) ? 0 : intval($_REQUEST['order_ids']);
        }
        else
        {
            $order_ids = $_REQUEST['order_ids'];
            if (empty($order_ids))
            {

                return;
            }
        }
        $this->log_item = $order_ids;
        $order_manager  = new OrderManager($_SESSION['store_id']);
        $del_num        = $order_manager->drop($order_ids);

        if (!$del_num)
        {
            $this->show_warning($order_manager->err);

            return;
        }

        $this->show_message($this->str_format('drop_order_sucessful', $del_num), 'view_order', 'admin.php?app=order&amp;act=view');
        return;
    }

    /**
     *  批量操作
     *
     *  @access
     *  @params
     *  @return
     */

    function batch()
    {
        $type   = trim($_GET['param']);
        $in     = explode(',', trim($_GET['ids']));
        $this->log_item = implode(',', $in);
        if (empty($in))
        {
            $this->show_warning('batch_not_selected');
            return;
        }
        else
        {
            $manager    = new OrderManager($_SESSION['store_id']);
            $rzt        = $manager->batch($type, $in);
            $affected_rows = is_array($rzt) ? count($rzt) : $rzt;

            if ($affected_rows)
            {
                $patterns[]     = '/act=\w+/i';
                $patterns[]     = '/[&|\?]?param=\w+/i';
                $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
                $replacement[]  = 'act=view';
                $replacement[]  = '';
                $replacement[]  = '';
                $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);

                /* 记录order action日志 */
                if ($type == 'invalid')
                {
                    $order_status = ORDER_STATUS_INVALID;
                    $action_note  = 'invalid_order';
                    $action_time  = gmtime();
                    foreach ($rzt as $_id)
                    {
                        $this->_order_action_log(addslashes($_SESSION['admin_name']), $_id['order_id'], $order_status, $this->lang($action_note), $action_time);
                    }
                }

                $this->show_message($this->str_format('batch_sucessfully', $affected_rows), $this->lang('view_order'), $location);
                return;
            }
            else
            {
                $this->show_warning('batch_faild', $this->lang('back_list'));
                return;
            }
        }
    }

    /**
     *  批量打印发货单
     *
     *  @author Garbin
     *  @param
     *  @return
     */
    function print_order()
    {
        $in     = trim($_GET['ids']);
        $do     = $_GET['do'] == 'yes' ? 1 : 0;
        $this->log_item = $in;
        if (empty($in))
        {
            $this->show_warning('batch_not_selected');
            return;
        }
        else
        {
            if (!$do)
            {
                $this->assign('ids', $in);
                $this->display('order.print_pop.html', 'store');
                return;
            }
            $manager    = new OrderManager($_SESSION['store_id']);
            $orders     = $manager->get_list_by_ids($in);
            if (!$orders)
            {
                $this->show_warning('no_records');

                return;
            }
            $this->assign('order_title', $this->conf('store_name', $_SESSION['store_id']));
            $this->assign('now', date('Y-m-d H:i', time()));
            $this->assign('orders', $orders);
            $this->display('order.print.html', 'store');
        }

    }

    /**
     *  编辑/查看订单
     *
     *  @author Garbin
     *  @return void
     */

    function edit()
    {
        $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        if (!$order_id)
        {
            $this->show_warning('invalid_order_id');
            return;
        }
        $this->log_item = $order_id; //日志
        $order = new Order($order_id, $_SESSION['store_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //不记录日志
            /* 显示编辑页面 */

            /* 如果是在线支付的订单且订单状态不为待付款,则我们就不允许他编辑该订单 */
            $order_info = $order->get_info_with_payment();

            if ($order_info['order_status'] < ORDER_STATUS_ACCEPTTED)
            {
                $this->assign('inv_enable', $this->conf('store_inv_enable', $_SESSION['store_id']));
                $this->assign('order', $order_info);
                $this->display('order.edit.html', 'store');
            }
            else
            {
                $this->show_warning('edit_order_disabled');

                return FALSE;
            }
        }
        else
        {
            /* 处理编辑请求 */
            /* 初始化数据 */
            $charge_info =   array();

            $order_info = $order->get_info();

            if ($order_info['order_status'] >= ORDER_STATUS_ACCEPTTED && $order_info['order_status'] <= ORDER_STATUS_DELIVERED)
            {
                $this->show_warning('edit_order_disabled');

                return;
            }

            $discount       =   abs(floatval($_POST['discount']));
            $shipping_fee   =   abs(floatval($_POST['shipping_fee']));
            $inv_fee        =   abs(floatval($_POST['inv_fee']));
            if(!$_POST['edit_pay_fee'])
            {
                /* 重新计算支付手续费 */
                $tmp_amount =   $order_info['goods_amount'] + $shipping_fee + $inv_fee - $discount - $order_info['money_paid'] - $order_info['coupon_value'] - $order_info['points_value'];
                $pay_fee    =   get_pay_fee($order_info['store_id'], $order_info['pay_id'], $tmp_amount);
            }
            else
            {
                $pay_fee    =   abs(floatval($_POST['pay_fee']));
            }
            $remark         =   trim($_POST['remark']);
            if ($order_info['goods_amount'] + $shipping_fee + $pay_fee + $inv_fee - $discount - $order_info['coupon_value'] - $order_info['points_value'] - $order_info['money_paid'] < 0)
            {
                $this->show_warning('invalid_charge_info');

                return;
            }

            if (strlen($remark) > 255)
            {
                $this->show_warning('remark_is_too_long');

                return;
            }

            !$remark && $remark = $this->lang('edit_charge_info_remark');

            $charge_info['discount']        = $discount;
            $charge_info['shipping_fee']    = $shipping_fee;
            $charge_info['pay_fee']         = $pay_fee;
            $charge_info['inv_fee']         = $inv_fee;
            $charge_info['goods_amount']    = $order_info['goods_amount'];
            $charge_info['coupon_value']    = $order_info['coupon_value'];
            $charge_info['points_value']    = $order_info['points_value'];
            $charge_info['order_amount']    = get_order_amount($charge_info);

            $order->write_charge_info($charge_info);
            /* 过滤从数据库中取得的数据 */
            $order->filter_data();
            $rzt = $order->submit();

            if (!$rzt)
            {
                $this->show_warning($order->err);

                return;
            }

            /* 记录order action日志 */
            $this->_order_action_log(addslashes($_SESSION['admin_name']), $order_info['order_id'], $order_info['order_status'], $remark, gmtime());

            /* 成功 */
            $this->show_message('edit_order_sucessful', 'back_list', "admin.php?app=order&act=view", 'process_order', 'admin.php?app=order&amp;act=change_status&amp;order_id=' . $order_id);
            return;
        }
    }

    /**
     *  改变订单状态
     *
     *  @author wj
     *  @return void
     */
    function change_status()
    {
        $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        $this->log_item = $order_id;
        if (!$order_id)
        {
            $this->show_warning('invalid_order_id');
            return;
        }
        $order = new Order($order_id, $_SESSION['store_id']);
        $order_info = $order->get_info_with_payment();
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            if ($order->err)
            {
                $this->show_warning($order->err);
                return;
            }
            $order_info['is_cod'] ? $type = 'cod_order' : $type = '';

            /* 获取操作日志 */
            include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
            $order_logger = new OrderLogger($_SESSION['store_id']);
            $action_logs  = $order_logger->get_list(1, array('order_sn' => $order_info['order_sn']));
            foreach ($action_logs['data'] as $_k => $_v)
            {
                $action_logs['data'][$_k]['order_status'] = $this->_order_status_list[$_v['order_status']];
            }


            /* 根据不同的情况计算出当前可执行的操作 */
            switch ($order_info['order_status'])
            {
                case ORDER_STATUS_PENDING:
                    $order_status_options = array(
                        ORDER_STATUS_ACCEPTTED => $this->lang('order_action_pay'),
                        ORDER_STATUS_INVALID   => $this->lang('order_action_invalid'),
                        ORDER_STATUS_REJECTED  => $this->lang('order_action_reject')
                    );
                    $status_comment = $this->lang('status_comment_pending');
                    break;
                case ORDER_STATUS_SUBMITTED:
                    $order_status_options = array(
                        ORDER_STATUS_ACCEPTTED => $this->lang('order_action_accept'),
                        ORDER_STATUS_INVALID   => $this->lang('order_action_invalid')
                    );
                    $order_status_options[ORDER_STATUS_REJECTED] = $this->lang('order_action_reject');
                    $status_comment = $this->lang('status_comment_submitted');
                    break;
                case ORDER_STATUS_ACCEPTTED:
                    if ($order_info['is_cod'])
                    {
                        $order_status_options = array(
                            ORDER_STATUS_SUBMITTED => $this->lang('order_action_unconfirmed')
                        );
                    }
                    $order_status_options[ORDER_STATUS_PROCESSING] = $this->lang('order_action_processing');
                    $order_status_options[ORDER_STATUS_SHIPPED] = $this->lang('order_action_ship');
                    $order_status_options[ORDER_STATUS_REJECTED] = $this->lang('order_action_reject');
                    $status_comment = $this->lang('status_comment_acceptted');
                    break;
                case ORDER_STATUS_PROCESSING:
                    $order_status_options[ORDER_STATUS_SHIPPED] = $this->lang('order_action_ship');
                    $order_status_options[ORDER_STATUS_REJECTED] = $this->lang('order_action_reject');
                    $status_comment = $this->lang('status_comment_processing');
                    break;
                case ORDER_STATUS_SHIPPED:
                    if ($order_info['is_online'])
                    {
                        $order_status_options = array( ORDER_STATUS_PROCESSING => $this->lang('order_action_unfilled'));
                    }
                    else
                    {
                        if ($order_info['is_cod'])
                        {
                            $order_status_options = array(
                                    ORDER_STATUS_PROCESSING => $this->lang('order_action_unfilled')
                            );
                        }
                        else
                        {
                            $order_status_options = array();
                        }
                    }
                    $order_status_options[ORDER_STATUS_REJECTED] = $this->lang('order_action_reject');
                    $status_comment = $this->lang('status_comment_shipped');
                    break;
                case ORDER_STATUS_DELIVERED:
                    $order_status_options = array();
                    $status_comment = $this->lang('status_comment_delivered');
                    break;
                case ORDER_STATUS_REJECTED:

                    $status_comment = $this->lang('status_comment_rejected');
                    break;
            }
            $to_status = intval($_REQUEST['to_status']);
            if ($to_status && !isset($order_status_options[$to_status]))
            {
                $this->show_warning('error');
                return;
            }
            $action_logs['data'] = deep_local_date($action_logs['data'], 'action_time', $this->conf('mall_time_format_complete'));

            $this->assign('status_comment', $status_comment);
            $this->assign('to_status', $to_status);
            $this->assign('curr_status', $this->_order_status_list[$order_info['order_status']]);
            $this->assign('order_status_options', $order_status_options);
            $this->assign('action_logs', $action_logs);
            $this->assign('goods_list', $order->list_goods());
            $this->assign('order', $order_info);
            header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            $last_date = gmdate('D, d M Y H:i:s');
            header('Last-Modified: '. $last_date . ' GMT'); //last modified
            ecm_setcookie('lastModified', $last_date); //to compale
            $this->assign('display_seller_eval', $this->_get_display_eval($order_info['seller_evaluation']));
            $this->assign('display_buyer_eval', $this->_get_display_eval($order_info['buyer_evaluation']));
            $this->display('order.change_status.html', 'store');
        }
        else
        {
            $status_code = empty($_REQUEST['order_status']) ? -1 : intval($_REQUEST['order_status']);
            $remark      = empty($_POST['remark'])       ? '' : trim($_POST['remark']);
            $invoice_no  = empty($_POST['invoice_no'])    ? '' : trim($_POST['invoice_no']);
            if ($status_code == -1)
            {
                $this->show_warning('invalid_order_status');
                return;
            }

            if (!$remark)
            {
                $remark = $this->lang('no_remark');
                //$this->show_warning('remark_required');
                //return;
            }

            /* 是否是货到付款 */
            $is_cod = $order_info['is_cod'];
            $is_online = $order_info['is_online'];
            $is_paid = $order_info['is_paid'];

            /* 如果是在线付款，则不允许往回走 */
            if ($is_online && $order_info['order_status'] > $status_code && $order_info['order_status'] != ORDER_STATUS_INVALID && $order_info['order_status'] != ORDER_STATUS_SHIPPED && $status_code != ORDER_STATUS_PROCESSING)
            {
                if ($status_code <= ORDER_STATUS_DELIVERED)
                {
                    $this->show_warning('not_allow_back');

                    return;
                }
            }

            /* 当前时间 */

            $time = gmtime();
            $send_mail = FALSE;

            $is_rollback = FALSE;
            $update_volume = NULL;

            switch ($status_code)
            {
                case ORDER_STATUS_PENDING:
                    /*  */
                    $order->set('money_paid', 0);
                    break;
                case ORDER_STATUS_ACCEPTTED:
                    if (!$is_cod)
                    {
                        $order->set('pay_time', $time);
                        $order->set('money_paid', $order_info['payable']);
                    }
                    $send_mail = TRUE;
                    $mail = array(
                        'event' => 'order_acceptted',
                        'buyer_email' => $order_info['email'],
                        'order_info'  => $order_info
                    );
                    break;
                case ORDER_STATUS_PROCESSING:
                    if ($order_info['ship_time'] > 0)
                    {
                        $order->set('ship_time', 0);
                        $order->set('invoice_no', '');
                        $is_rollback = TRUE;
                        $update_volume = '-';
                    }
                    break;
                case ORDER_STATUS_SHIPPED:
                    if (!$invoice_no)
                    {
                        $this->show_warning('invalid_invoice_no');

                        return;
                    }
                    if (!$order->set_invoice($invoice_no))
                    {
                        $this->show_warning($order->err[0]);

                        return;
                    }

                    $update_volume = '+';

                    /*
                    if ($order_info['user_id'])
                    {
                        include_once(ROOT_PATH . '/includes/models/mod.user.php');
                        $user = new User($order_info['user_id']);
                        $give_points = $order->get_give_points();
                        $user->add_points($_SESSION['store_id'], $give_points, $this->lang('by_shopping'));
                    }
                    */

                    $order->set('ship_time', $time);
                    if ($order_info['user_id'])
                    {
                        $send_mail = TRUE;

                        $mail = array(
                            'event' => 'shipping_notice',
                            'buyer_email' => $order_info['email'],
                            'order_info'  => $order_info
                        );
                    }

                    break;
                case ORDER_STATUS_DELIVERED:
                    $this->show_warning('delivered_by_user_only');

                    return;
                    $order->set('pay_time', $time);
                    $order->set('money_paid', $order_info['payable']);

                    break;
                case ORDER_STATUS_INVALID:
                    /* 若订单状态是已接受之后的,则不允许置为无效 */
                    if ($order_info['order_status'] > 2)
                    {
                        $this->show_warning('not_allow_invalid');

                        return;
                    }
                case ORDER_STATUS_REJECTED:
                    $this->_add_coupon_usable_times($order_info['coupon_sn'], 1);

                    /* 修改商品库存 */
                    $this->_change_goods_stock($order);
                    $send_mail = TRUE;
                    $mail = array(
                        'event' => 'order_cancel',
                        'buyer_email' => $order_info['email'],
                        'order_info'  => $order_info
                    );

                    break;
                default:
                    break;
            }

            $order->set_status($status_code);
            /* 过滤从数据库中取得的数据 */
            $order->filter_data();
            $order->submit();
            if ($order->has_err())
            {
                $this->show_warning($order->err[0]);
                return;
            }

            /* 若是通过线上付款且发了货的订单，则增加商品的销量 */
            if ($update_volume !== NULL)
            {
                $goods_list =   $order->list_goods();
                include_once(ROOT_PATH . '/includes/models/mod.goods.php');
                foreach ($goods_list as $_g)
                {
                    $goods = new Goods($_g['goods_id']);
                    $goods->update_sales_volume($update_volume . $_g['goods_number']);
                }
            }

            /* 记录order action日志 */
            $this->_order_action_log(addslashes($_SESSION['admin_name']), $order_id, $status_code, $remark, $time);
            /* 发送Email给买家 */

            if ($send_mail)
            {
                $this->_mail_to_buyer($mail['event'], $mail['buyer_email'], $mail['order_info']);
            }
            ecm_setcookie('lastModified', gmdate('D, d M Y H:i:s'));
            $this->show_message('change_status_sucessful', $this->lang('view_order'), 'admin.php?app=order', $this->lang('go_back'), 'admin.php?app=order&amp;act=change_status&amp;order_id=' . $order_id);


            return;

        }
    }

    /**
     *  评价
     *  @author wj
     *  @access public
     *  @param  none
     *  @return void
     */
    function evaluation()
    {
        $order_id = intval($_GET['order_id']);
        $rank     = intval($_GET['rank']);
        $comment  = trim($_POST['comment']);

        if (!$order_id)
        {
            $this->show_warning('invalid_order_id');

            return;
        }
        if ($rank < 1 || $rank > 3)
        {
            $this->show_warning('out_of_range');

            return;
        }

        $order = new Order($order_id);
        $order_info = $order->get_info_with_payment();
        if ($order_info['order_status'] != ORDER_STATUS_DELIVERED || $order_info['buyer_evaluation'])
        {
            $this->show_warning('not_evaluable_order');

            return;
        }

        include_once(ROOT_PATH . '/includes/manager/mng.credit.php');
        $credit_manager = new CreditManager();
        $credit_manager->init($order, 'buyer', $rank);
        $credit_manager->set_conf(array( 'mall_store_repeat_limit' => $this->conf('mall_store_repeat_limit'),
                                         'mall_goods_repeat_limit' => $this->conf('mall_goods_repeat_limit'),
                                         'mall_min_goods_amount'   => $this->conf('mall_min_goods_amount'),
                                         'mall_max_goods_amount'   => $this->conf('mall_max_goods_amount')));

        $is_invalid = $credit_manager->is_invalid();
        $credits    = $credit_manager->get_credits();
        $order->add_buyer_credit($credits);
        $order->buyer_evaluation($rank);
        $order->buyer_comment($comment);
        $order->submit();


        /* 若有计算信用积分 */
        if (!$is_invalid && $credits != 0)
        {
            $buyer =& $credit_manager->get_body();

            /* 刷新买家的总信用积分 */
            $buyer->update_buyer_credit();
        }

        $this->log_item = $order_id;

        /* 记录订单操作日志 */
        $this->_order_action_log(addslashes($_SESSION['admin_name']), $order_id, ORDER_STATUS_DELIVERED, $comment, gmtime());

        /* 发送PM给买家 */
        uc_call('uc_pm_send', array(0, $order_info['user_id'], $this->lang('to_buyer_evaluation_notify'), $this->str_format('to_buyer_evaluation_notify_contents', $_SESSION['admin_name'], $order_info['order_sn']), 1));

        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $store = new Store($order_info['store_id']);
        $store_info = $store->get_info();
        $this->send_mail($order_info['email'], 'to_buyer_evaluation_notify', array( 'order'=>$order_info,
                                                                                    'store'      => $store_info,
                                                                                    'seller' => $store_info['store_name'],
                                                                                    'buyer'=> $order_info['user_name']));

        $this->show_message('evaluate_sucess', $this->lang('go_back'), 'admin.php?app=order&amp;act=change_status&amp;order_id=' . $order_id);
        return;
    }

    /**
     *  发送邮件给买家
     *
     *  @author wj
     *  @param  string $event       事件:shipping_notice 发货通知, order_cancel 订单取消通知
     *  @param  string $buyer_email 买家的联系EMAIL
     *  @param  array  $order_info  订单信息
     *  @return void
     */

    function _mail_to_buyer($event, $buyer_email, $order_info)
    {
        $values = array();
        $values['order'] = $order_info;
        $values['confirm_url'] = site_url() . "/index.php?app=member&act=order_view&order_status=" . ORDER_STATUS_SHIPPED;
        $values['boss'] = $_SESSION['admin_name'];
        $values['store_url'] = site_url() . "/index.php?app=store&store_id=".$_SESSION['store_id'];
        $values['send_date'] = local_date($this->conf('mall_time_format_complete'));
        $this->send_mail($buyer_email, $event, $values);

        //todo: 启动发送邮件的队列
    }

    /**
     *  增加优惠券使用次数
     *
     *  @param
     *  @return
     */
    function _add_coupon_usable_times($coupon_sn, $times = 1)
    {
        if ($coupon_sn)
        {
           /* 取消订单后返还被扣除的使用次数 */
           include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
           $coupon = new Coupon(0, $_SESSION['store_id']);
           $coupon->init_by_sn($coupon_sn);
           $coupon->update_usable_times($times);
        }
    }

    /**
     *  还原商品库存
     *
     *  @param  Object $order 订单对象
     *  @return
     */
    function _change_goods_stock($order, $opreater = '')
    {
        $goods_list = $order->list_goods();
        if (empty($goods_list))
        {
            return;
        }
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        foreach ($goods_list as $_g)
        {
            $goods = new Goods($_g['goods_id'], $_SESSION['store_id']);
            $goods->set_stock($_g['spec_id'], $opreater . $_g['goods_number']);
        }
    }

    /**
     *  记录订单操作日志
     *
     *  @param
     *  @return
     */
    function _order_action_log($action_user, $order_id, $order_status, $action_note, $action_time)
    {
        /* 记录操作日志 */
        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
        $order_logger = new OrderLogger($_SESSION['store_id']);
        $order_logger->write(array('action_user' => $action_user,
                                   'order_id'    => $order_id,
                                   'order_status'=> $order_status,
                                   'action_note' => $action_note,
                                   'action_time' => $action_time));
    }


    /**
     *    获取评价语言项
     *
     *    @author    Garbin
     *    @param     int $evalid
     *    @return    void
     */
    function _get_display_eval($evalid)
    {
        switch ($evalid)
        {
            case 1:
                return $this->lang('order_evaluation_poor');
            break;
            case 2:
                return $this->lang('order_evaluation_common');
            break;
            case 3:
                return $this->lang('order_evaluation_good');
            break;
            default:
                return $this->lang('order_evaluation_unevaluated');
            break;
        }
    }
}

?>
