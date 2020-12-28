<?php

/**
 * ECMALL: 订单管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.order.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class OrderManager extends Manager
{
    var $_store_limit = '';
    var $_extension_limit = " AND oi.extension_code NOT IN ('STORERELET') "; // 店铺续租订单
    /**
     *  构造函数
     *  @param int $store_id
     *  @return none
     */

    function __construct($store_id = 0)
    {
        $this->OrderManager($store_id);
    }

    function OrderManager($store_id = 0)
    {
        parent::__construct($store_id);
        $this->_store_id = $store_id;
        if ($this->_store_id)
        {
            $this->_store_limit = ' AND oi.store_id = ' . $this->_store_id;
        }
        !class_exists('Order') && include_once(ROOT_PATH . '/includes/models/mod.order.php');
    }

    /**
     *  添加订单
     *
     *  @access public
     *  @param array $info
     *  @return mixed
     */

    function add($info)
    {
        $order      =   new Order(NULL, $this->_store_id);

        $order->set_user_id($info['user_id']);
        $order->set('referer', $info['referer']);

        /* 写入收货人信息 */
        $order->write_consignee_info($info['consignee_info']);

        /* 写入配送方式信息 */
        $order->write_shipping_info($info['shipping_info']);

        /* 写入支付方式信息 */
        $order->write_payment_info($info['payment_info']);

        /* 写入发票联信息 */
        $order->write_inv_info($info['inv_info']);

        /* 费用信息 */
        $order->write_charge_info($info['charge_info']);

        /* 生成有效的商品列表 */
        $order->set_goods($info['goods_list']);             //使用自定义价格写入商品信息

        /* 提交新订单请求 */
        if(!$order->submit())
        {
            /* 将信息保存到会话，并返回错误 */
            $order->save_session();
            return $order->err;
        }
        $order->clear_session();
        $this->_update_count('+1');

        return TRUE;
    }

    /**
     *  更新单一订单
     *
     *  @access public
     *  @param array    $info       要修改的字段数组
     *  @param int      $order_id   订单ID
     *  @return
     */

    function update($info, $order_id)
    {
        $order      =   new Order($order_id, $this->_store_id);


        $order->set('user_id', $info['user_id']);
        $order->set('invoice_no', $info['invoice_no']);
        isset($info['order_status']) && $order->set('order_status',$info['order_status']);

        /* 写入收货人信息 */
        $order->write_consignee_info($info['consignee_info']);

        /* 写入配送方式信息 */
        $order->write_shipping_info($info['shipping_info']);

        /* 写入支付方式信息 */
        $order->write_payment_info($info['payment_info']);

        /* 写入发票联信息 */
        $order->write_inv_info($info['inv_info']);

        /* 费用信息 */
        $order->write_charge_info($info['charge_info']);

        /* 生成有效的商品列表 */
        $order->set_goods($info['goods_list']);             //使用自定义价格写入商品信息

        /* 提交新订单请求 */
        //echo $order->submit();exit('here');
        if(!$order->submit())
        {

            return $order->err;
        }

        return TRUE;

    }

    /**
     *  列表订单
     *  @param int      $page           列表第几页
     *  @param mixed    $conditions     条件查询字段
     *  @param string   $orderby        排序字段
     *  @param string   $fields         要取得的字段
     *  @return Array
     */

    function get_list($page = 0, $conditions = NULL, $orderby = NULL, $fields = NULL, $pagesize = 10)
    {
        $orderby === NULL && $orderby = 'add_time';
        $fields === NULL && $fields = 'oi.*,(oi.order_amount - oi.discount - oi.points_value - oi.coupon_value - oi.money_paid) AS payable,u.user_name,s.store_name,p.is_cod';
        $pagesize <= 0 && $pagesize = 10;
        $pars = $this->query_params($page, $conditions, $orderby, $pagesize);
        $fields = $this->_get_fields($fields);
        $sql = "SELECT {$fields} FROM `ecm_order_info` oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id LEFT JOIN `ecm_store` s ON oi.store_id = s.store_id LEFT JOIN `ecm_payment` AS p ON oi.pay_id=p.pay_id WHERE 1 {$pars['where']} ORDER BY {$pars['sort']} {$pars['order']} LIMIT {$pars['start']},{$pars['number']}";
        return array('data'=>$GLOBALS['db']->getAll($sql),
                     'info'=>$this->_page_info($page, $pars['count']));
    }

    /**
     *  根据指定的ID获取订单信息
     *
     *  @param  string $ids     由','号连接而成的订单字符串
     *  @return array
     */
    function get_list_by_ids($ids)
    {
        if (!$ids)
        {
            $this->err = 'no_ids';

            return array();
        }
        $sql = "SELECT oi.*,u.user_name FROM `ecm_order_info` oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id LEFT JOIN `ecm_store` s ON oi.store_id = s.store_id LEFT JOIN `ecm_payment` AS p ON oi.pay_id=p.pay_id WHERE oi.order_id IN($ids)";
        $orders = $GLOBALS['db']->getAll($sql);
        if (!$orders)
        {
            $this->err = 'no_orders';

            return array();
        }
        foreach ($orders as $_k => $order)
        {
            $get_goods_sql ="SELECT og.*,g.give_points,og.goods_price as store_price,gs.spec_name,gs.color_name " .
                            "FROM `ecm_order_goods` og ".
                            "LEFT JOIN `ecm_goods_spec` gs ON og.spec_id=gs.spec_id " .
                            "LEFT JOIN `ecm_goods` g ON gs.goods_id=g.goods_id " .
                            "WHERE og.order_id = {$order['order_id']}";
            $orders[$_k]['goods'] = $GLOBALS['db']->getAll($get_goods_sql);
            $orders[$_k]['payable'] = get_payable($order);
            $orders[$_k]['inv_amount'] = get_inv_amount($order);
        }

        return $orders;
    }

    /**
     *  删除一个或多个订单
     *  @param mixed $order_id      可以是数组也可以是一个整数
     *  @return Int
     */

    function drop($order_id)
    {
        is_string($order_id) && $order_id = explode(',', $order_id);
        $drop_ids = $this->getDroppable($order_id);
        if (empty($drop_ids))
        {
            $this->err = 'no_droppable_order';
            return 0;
        }
        $drop_ids = db_create_in($drop_ids);

        /* 删除订单信息 */
        $GLOBALS['db']->query('DELETE FROM `ecm_order_info` WHERE order_id ' . $drop_ids);
        $affected_rows  =   $GLOBALS['db']->affected_rows();

        /* 删除商品数据 */
        $GLOBALS['db']->query('DELETE FROM `ecm_order_goods` WHERE order_id ' . $drop_ids);

        /* 删除订单操作日志 */

        $GLOBALS['db']->query("DELETE FROM `ecm_order_action` WHERE order_id " . $drop_ids);

        $this->_update_count('-' . $affected_rows);
        return $affected_rows;
    }

    /**
     *  获取可删除的订单
     *
     *  @access public
     *  @params mixed $order_id
     *  @return array
     */

    function getDroppable($order_id)
    {
        $drop_ids = array();
        !is_array($order_id) ? $drop_ids[] = $order_id: $drop_ids   = $order_id;
        $drop_ids = db_create_in($drop_ids);
        $droppable = $GLOBALS['db']->getAll('SELECT order_id FROM `ecm_order_info` WHERE order_status = 7 AND order_id ' . $drop_ids);
        $rtn = array();
        foreach ($droppable as $_k => $_v)
        {
            $rtn[] = $_v['order_id'];
        }

        return $rtn;
    }

    /**
     *  更新店铺的订单总数
     *
     *  @access public
     *  @params string $range       如+1
     *  @return
     */

    function _update_count($range)
    {
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $store = new Store($this->_store_id);
        $store->update_order_count($range);
    }

    /**
     *  批量修改订单信息
     *  @param mixed $order_id  可以是数组，也可以是一个整数
     *  @param array $info      要更新的字段
     *  @return Int
     */

    function bupdate($order_ids, $info)
    {
        if(!is_array($info))
        {
            $this->err = 'invalid_update_info';

            return FALSE;
        }
        $ids        = array();
        is_array($order_ids) ? $ids[] = $order_ids : $ids = $order_ids;
        $ids        = implode(',', $order_ids);
        $tmp_order  = new Order();
        foreach($info as $_k => $_v)
        {
            $info[$_k] = $tmp_order->set($_k, $_v, TRUE);
            if(!$info[$_k])
            {
                $this->err =& $tmp_order->err;

                return 0;
            }
        }
        $set_fields = $tmp_order->get_set_fields($info);
        $sql = "UPDATE `ecm_order_info` SET {$set_fields} WHERE order_id IN($ids)";
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }


    /**
     *  批量操作选中的订单
     *
     *  @access public
     *  @param string $type     支持的批量操作有confirm,cancel,drop
     *  @param string $in       一串由主键ID所组成的字符串
     *  @return bool
     */


    function batch($type, $in)
    {
        switch ($type)
        {
            case 'confirm':
                return $this->set_status($in, ORDER_STATUS_ACCEPTTED, 'AND order_status = ' . ORDER_STATUS_SUBMITTED);
            break;
            case 'cancel':
                return $this->set_status($in, ORDER_STATUS_REJECTED, 'AND order_status <> ' . ORDER_STATUS_DELIVERED . ' AND order_status <> ' . ORDER_STATUS_SHIPPED);
            break;
            case 'invalid':
                return $this->set_status($in, ORDER_STATUS_INVALID, 'AND order_status < ' . ORDER_STATUS_ACCEPTTED);
                break;
            case 'drop':
                return $this->drop($in);
            break;
            default:
                $this->err = 'unknow_batch_type';
        }
    }

    /**
     *  批量修改订单状态
     *  @param int $order_id        订单ID
     *  @param int $status_no       状态码
     *  @param mixed $conditions    条件字串，可以是字符串，也可以是数组
     *  @return int
     */

    function set_status($order_id, $status_no, $conditions = '')
    {
        $order_ids = array();
        !is_array($order_id) ? $order_ids[] = $order_id: $order_ids = $order_id;
        $order_ids  =   db_create_in($order_ids);

        /* 取得符合条件的ID数组 */
        $affected_ids = $GLOBALS['db']->getAll("SELECT order_id,user_id,points,coupon_sn FROM `ecm_order_info` oi WHERE order_id {$order_ids} {$conditions}{$this->_store_limit}{$this->_extension_limit}");

        /* 取得商品列表 */
        $goods_list = $GLOBALS['db']->getAll("SELECT goods_id, spec_id, goods_number FROM `ecm_order_goods` og LEFT JOIN `ecm_order_info` oi ON og.order_id = oi.order_id WHERE og.order_id {$order_ids} {$conditions}{$this->_store_limit}{$this->_extension_limit}");

        $order_ids && $GLOBALS['db']->query("UPDATE `ecm_order_info` oi SET order_status={$status_no} WHERE order_id {$order_ids} {$conditions}{$this->_store_limit}{$this->_extension_limit}");

        switch ($status_no)
        {
        case ORDER_STATUS_INVALID:
        case ORDER_STATUS_REJECTED:
            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
            foreach ($affected_ids as $_v)
            {
                /*
                if ($_v['points'] && $_v['user_id'])
                {
                   include_once(ROOT_PATH . '/includes/models/mod.user.php');
                   $user = new User($_v['user_id']);
                   $user->add_points($this->_store_id, $_v['points'], Language::get('cancel_order_return'));
                }
                */
                if ($_v['coupon_sn'])
                {
                   /* 取消订单后返还被扣除的使用次数 */
                   include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
                   $coupon = new Coupon(0, $this->_store_id);
                   $coupon->init_by_sn($_v['coupon_sn']);
                   $coupon->update_usable_times(1);
                }
            }
            /* 加回商品库存 */
            include_once(ROOT_PATH . '/includes/models/mod.goods.php');
            foreach ($goods_list as $_g)
            {
                $goods = new Goods($_g['goods_id'], $this->_store_id);
                $goods->set_stock($_g['spec_id'], $_g['goods_number']);
            }
            break;
        }
        return $affected_ids;
    }

    /**
     *  获取总数
     *
     *  @access public
     *  @param  mixed $conditions
     *  @return int
     */

    function get_count($conditions)
    {

        $count = $GLOBALS['db']->getRow("SELECT count(*) AS rec_count FROM `ecm_order_info` oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id LEFT JOIN `ecm_store` s ON oi.store_id = s.store_id WHERE 1 " . $this->_make_condition($conditions));

        return $count['rec_count'];
    }

    /**
     *  取得SQL语句中SELECT与FROM中间的句段
     *  @param mixed $fields
     *  @return string
     */

    function _get_fields($fields)
    {
        if(empty($fields))
        {

            return '*';
        }
        if(is_string($fields))
        {

            return $fields;
        }
        elseif(is_array($fields))
        {

            return implode(',', $fields);
        }
    }

    /**
     *  将数组形式的$conditions转换成SQL的WHERE部分语句
     *  @param mixed $conditions
     *  @return string
     */

    function _make_condition($conditions)
    {
        $where = '(';
        if(is_numeric($conditions))
        {
            $where .= 'oi.order_id = ' . $conditions;
        }
        elseif (is_string($conditions))
        {
            $where .= $conditions;
        }
        elseif(is_array($conditions))
        {
            if ($conditions['end_date'])
            {
                $conditions['end_date'] += 24 * 3600;
            }
            $where_array = array();
            foreach ($conditions as $_k => $_v)
            {
                switch ($_k)
                {
                case 'order_sn':
                case 'address':
                case 'consignee':
                    $where_array['OR'][] = "oi.{$_k} LIKE '%{$_v}%'";
                    break;
                case 'start_date':
                    $where_array['AND'][] = 'oi.add_time >= ' . $_v;
                    break;
                case 'end_date':
                    $where_array['AND'][] = 'oi.add_time <= ' . $_v;
                    break;
                case 'order_amount':
                case 'goods_amount':
                    $_v['start'] && $where_array['AND'][] = 'oi.' . $_k. ' >= ' . $_v['start'] . '';
                    $_v['end']   && $where_array['AND'][] = 'oi.' . $_k. ' <= ' . $_v['end'] . '';
                    break;
                case 'order_status':
                    if (is_array($_v))
                    {
                        $where_array['AND'][] = 'oi.order_status ' . db_create_in($_v);
                    }
                    else
                    {
                        $where_array['AND'][] = 'oi.order_status = '.$_v;
                    }
                    break;
                case 'store_name':
                    $where_array['AND'][] = "s.store_name = '{$_v}'";
                    break;
                case 'store_id':
                    $where_array['AND'][] = "oi.store_id = {$_v}";
                    break;
                case 'user_id':
                    $where_array['AND'][] = "oi.user_id = {$_v}";
                    break;
                case 'unevaluated':
                    $where_array['AND'][] = "oi.{$_v}_evaluation = 0";
                    break;
                }
            }
            $and = '';
            $or = '';
            $where_array['AND'] && $and = implode(' AND ', $where_array['AND']);
            $where_array['OR'] && $or  = implode(' OR ', $where_array['OR']);
            if ($and && $or)
            {
                $where = $and . ' AND (' . $or . ')';
            }
            else
            {
                $where = $and . $or;
            }
        }
        if ($where)
        {
            $where = ' AND ( ' . $where . ')';
        }
        $where .= $this->_store_limit;
        if (isset($conditions['extension_code']))
        {
            $where .= " AND oi.extension_code = '$conditions[extension_code]' ";
        }
        else
        {
            $where .= $this->_extension_limit;
        }

        return $where;
    }

    /**
     *  获得指定用户的最后一个订单
     *
     *  @access public
     *  @param  int $user_id
     *  @return array
     */

    function get_last_order_by_user($user_id)
    {
        if (!$user_id)
        {
            $this->err = 'invalid_user_id';
            return FALSE;
        }
        $sql = 'SELECT oi.* FROM `ecm_order_info` oi WHERE oi.user_id=' . $user_id . $this->_store_limit . $this->_extension_limit . ' ORDER BY add_time DESC LIMIT 1';

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     *  获取最新成交商品
     *
     *  @author Garbin
     *  @param Int $rec_num 要获取的条数
     *  @return Array
     */

    function list_recent_deal($rec_num = 10)
    {
        $rec_num = empty($rec_num) ? 10 : intval($rec_num);
        $sql = "SELECT DISTINCT og.goods_id " .
               "FROM `ecm_order_info` oi LEFT JOIN `ecm_order_goods` og ON oi.order_id = og.order_id " .
               "WHERE 1{$this->_extension_limit} " .
               "ORDER BY og.rec_id DESC " .
               "LIMIT 0,{$rec_num}";
        $goods_ids = $GLOBALS['db']->getCol($sql);

        $sql = "SELECT og.goods_name,og.goods_id, og.goods_price ".
               "FROM `ecm_order_goods` og ".
               "WHERE 1 " .
               "AND og.goods_id " . db_create_in($goods_ids) .
               "ORDER BY og.rec_id DESC " .
               "LIMIT " . count($goods_ids);

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     *  获取通过通过活动生成的订单
     *
     *  @access public
     *  @param  string $activity_code GROUPBUY为团购
     *  @param  int    $activity_id   活动ID
     *  @param  int    $user_id       用户ID
     *  @return array
     */

    function get_avtivity_order($activity_code, $activity_id, $user_id)
    {
        $sql = "SELECT * FROM `ecm_order_info` WHERE extension_code='{$activity_code}' AND extension_id='{$activity_id}' AND user_id='{$user_id}'";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     *  将指定时间内还未确认收货但已发货的订单自动置为已收货状态
     *
     *  @author Garbin
     *  @param  string $evaluation_value
     *  @param  int $time_range
     *  @return void
     */
    function auto_delivered($evaluation_value, $time_range = 1209600)
    {
        $time_range = intval($time_range);
        $now        = gmtime();
        $ssql = 'SELECT oi.order_id '.
                'FROM `ecm_order_info` oi WHERE order_status=' . ORDER_STATUS_SHIPPED .
                " AND ship_time < {$now} - {$time_range}";
        $orders = $GLOBALS['db']->getAll($ssql);
        /* 记录操作日志 */

        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        include_once(ROOT_PATH . '/includes/manager/mng.credit.php');

        $order_logger = new OrderLogger($this->_store_id);
        foreach ($orders as $_k => $_o)
        {
            $order_logger->write(array('action_user' => '0',
                                       'order_id'    => $_o['order_id'],
                                       'order_status'=> ORDER_STATUS_DELIVERED,
//                                       'action_note' => $this->lang('system_auto_delivered'),
                                       'action_time' => $now));

            /* 计算信用积分 */
            $order  =   new Order($_o['order_id']);
            $credit_manager = new CreditManager();
            $credit_manager->init($order, 'seller', $evaluation_value);
            $credit_manager->set_conf($this->_sys_conf);

            $is_invalid = $credit_manager->is_invalid();
            $credits    = $credit_manager->get_credits();
            $order->add_seller_credit($credits);
            $order->set_status(ORDER_STATUS_DELIVERED);
            $order->seller_evaluation($evaluation_value);
//            $order->seller_comment($this->lang('auto_delivered_comment'));
            $order->filter_data();
            $order->submit();
            if (!$is_invalid && $credits != 0)
            {
                $seller =& $credit_manager->get_body();

                /* 刷新卖家的总信用积分 */
                $seller->update_seller_credit();
            }

            /* 清理内存 */
            unset($orders[$_k], $order, $credit_manager, $seller);
        }
    }

    /**
     *  将指定时间内还未评价的订单自动给买家好评
     *
     *  @author redstone
     *  @param  int    $evaluation_value
     *  @param  string $time_range
     *  @return void
     */
    function auto_evaluation($evaluation_value, $time_range = 604800)
    {
        $evaluation_value = in_array($evaluation_value, array(1, 2, 3)) ? $evaluation_value : 3;
        $time_range = intval($time_range);
        $now        = gmtime();
        $ssql = 'SELECT oi.order_id '.
                'FROM `ecm_order_info` oi '.
                'LEFT JOIN `ecm_order_action` oa ON oi.order_id=oa.order_id '.
                'WHERE oi.buyer_evaluation=' . ORDER_EVALUATION_UNEVALUATED . ' AND oa.order_status = ' . ORDER_STATUS_DELIVERED . " AND oa.action_time < {$now} - {$time_range} ";
        $orders =   $GLOBALS['db']->getAll($ssql);

        /*
        $sql = 'UPDATE `ecm_order_info` oi LEFT JOIN `ecm_order_action` oa ON oi.order_id=oa.order_id SET buyer_evaluation=' . ORDER_EVALUATION_GOOD . ' WHERE oa.order_status = ' . ORDER_STATUS_DELIVERED . " AND action_time < {$now} - {$time_range}{$this->_store_limit}{$this->_extension_limit} ";
        */
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        include_once(ROOT_PATH . '/includes/manager/mng.credit.php');
        foreach ($orders as $_k => $_o)
        {
            /* 计算信用积分 */
            $order  =   new Order($_o['order_id']);
            $credit_manager = new CreditManager();
            $credit_manager->init($order, 'buyer', $evaluation_value);
            $credit_manager->set_conf($this->_sys_conf);

            $is_invalid = $credit_manager->is_invalid();
            $credits    = $credit_manager->get_credits();
            $order->add_buyer_credit($credits);
            $order->buyer_evaluation($evaluation_value);
            $order->buyer_comment(Language::get('auto_evaluation_comment'));
            $order->filter_data();
            $order->submit();
            if (!$is_invalid && $credits != 0)
            {
                $buyer =& $credit_manager->get_body();

                /* 刷新卖家的总信用积分 */
                $buyer->update_buyer_credit();
            }

            /* 清理内存 */
            unset($orders[$_k], $order, $credit_manager, $buyer);
        }

        //$GLOBALS['db']->query($sql);
    }

    /**
     *  传入系统配置信息
     *
     *  @author Garbin
     *  @param  array $conf 配置信息
     *  @return void
     */
    function set_conf($conf)
    {
        $this->_sys_conf    =   $conf;
    }

    /**
     *  获取数字所对应的相关状态代码
     *
     *  @access public
     *  @param  int $status_no  状态码
     *  @return array
     */

    function get_status_code($status_no)
    {
        switch($status_no)
        {
            case ORDER_STATUS_TEMPORARY:
                return 'order_status_temporary';
                break;
            case ORDER_STATUS_PENDING:
                return 'order_status_pending';
                break;
            case ORDER_STATUS_SUBMITTED:
                return 'order_status_submitted';
                break;
            case ORDER_STATUS_ACCEPTTED:
                return 'order_status_acceptted';
                break;
            case ORDER_STATUS_PROCESSING:
                return 'order_status_processing';
                break;
            case ORDER_STATUS_SHIPPED:
                return 'order_status_shipped';
                break;
            case ORDER_STATUS_DELIVERED:
                return 'order_status_delivered';
                break;
            case ORDER_STATUS_INVALID:
                return 'order_status_invalid';
                break;
            case ORDER_STATUS_REJECTED:
                return 'order_status_rejected';
                break;
        }
    }

    /**
     *  获取用户在指定条件下的订单数
     *
     *  @author Garbin
     *  @param  int     $user_id    用户ID
     *  @param  string  $where      条件字串
     *  @return
     */
    function get_order_num($user_id, $where)
    {
        if ($where)
        {
            $where = ' AND ' . $where;
        }
        $sql = 'SELECT COUNT(*) as order_num '.
               'FROM `ecm_order_info` oi '.
               "WHERE 1{$where}{$this->_store_limit}{$this->_extension_limit}";

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     *  获取指定时间内用户所购买过的商品
     *
     *  @author Garbin
     *  @param  string $where   条件字串
     *  @return array
     */
    function get_bought_goods($where)
    {
        if ($where)
        {
            $where = ' AND ' . $where;
        }
        $sql = 'SELECT og.spec_id,og.goods_number,oi.order_id,oi.store_id '.
               'FROM `ecm_order_goods` og '.
               'LEFT JOIN `ecm_order_info` oi ON og.order_id=oi.order_id '.
               'LEFT JOIN `ecm_pay_log` pl ON oi.order_id=pl.order_id AND pl.is_paid=1 '.
               "WHERE 1{$where}{$this->_store_limit}{$this->_extension_limit}";

        return $GLOBALS['db']->getAll($sql);
    }
}

?>
