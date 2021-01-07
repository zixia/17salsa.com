<?php

/**
 * ECMALL: ��վƷ�ƹ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: inc.stat.php 6022 2008-11-03 02:50:29Z Scottye $
 */

define('STAT_SECONDS', 30*24*3600);

/**
 *  ��ȡ��Աͳ������
 *
 *  @access public
 *  @param  string $time_range      today:���� unlimited:������
 *  @return int
 */

function get_member_count($time_range = 'unlimited', $store_id = 0)
{
    $sql = 'SELECT COUNT(*) as c FROM `ecm_users` WHERE 1';
    if ($store_id)
    {
        $sql .= ' AND store_id = ' . intval($store_id);
    }
    switch ($time_range)
    {
    case 'today':
        $today_timestamp = get_today_timestamp();
        $time_limit = ' AND reg_time >= ' . $today_timestamp;
        break;
    case '30day':
        $today_timestamp = get_today_timestamp();
        $time_limit = ' AND last_login >= ' . ($today_timestamp - STAT_SECONDS);
        break;
    case 'unlimited':
        $time_limit = '';
        break;
    }

    $sql .= $time_limit;

    return $GLOBALS['db']->getOne($sql);
}

/**
 *  ��ȡ��Ʒͳ����Ϣ
 *
 *  @access public
 *  @param  string $time_range      today:���� unlimited:������
 *  @return int
 */

function get_goods_count($time_range = 'unlimited', $condition = '', $store_id = 0)
{
    !$store_id && $store_id = $_SESSION['store_id'];
    switch ($time_range)
    {
    case 'today':
        $today_timestamp = get_today_timestamp();
        $time_limit = ' AND add_time >= ' . $today_timestamp;
        break;
    case '30day':
        $today_timestamp = get_today_timestamp();
        $time_limit = ' AND last_update >= ' . ($today_timestamp - STAT_SECONDS);
        break;
    case 'unlimited':
        $time_limit = '';
        break;
    }

    switch ($condition)
    {
    case 'oos':
        $sql = 'SELECT COUNT(*) as c FROM `ecm_goods` g LEFT JOIN `ecm_goods_spec` s ON g.goods_id=s.goods_id WHERE 1 AND s.stock=0';
    break;
    default:
        $sql = 'SELECT COUNT(*) as c FROM `ecm_goods` WHERE 1';
        break;
    }
    if ($store_id)
    {
        $sql .= ' AND store_id = ' . intval($store_id);
    }
    if ($time_limit)
    {
        $sql .= $time_limit;;
    }

    return $GLOBALS['db']->getOne($sql);
}

/**
 * ��ȡpageview
 *
 * @author liupeng
 * @return int
 */
function get_page_view()
{
    $date = date('Y-m-d');
    $date = strtotime($date) - (3600*24*30);
    $date = date('Y-m-d', $date);
    $sql = "SELECT sum(view_times) as r FROM `ecm_pageview` WHERE view_date >= '$date'";

    $res = $GLOBALS['db']->getOne($sql);
    $res = intval($res);

    return $res;
}

/**
 *  ��ȡ��Ʒͳ����Ϣ
 *
 *  @author liupeng
 *  @param  string $time_range      today:���� unlimited:������ last_month:��һ����
 *  @return int
 */

function get_store_count($time_range = 'unlimited', $store_id = 0)
{
    $sql = 'SELECT COUNT(*) as c FROM `ecm_store` WHERE 1';
    if ($store_id)
    {
        $sql .= ' AND store_id = ' . intval($store_id);
    }
    switch ($time_range)
    {
        case 'today':
            $today_timestamp = get_today_timestamp();
            $time_limit = ' AND add_time >= ' . $today_timestamp;
            break;
        case 'last_month':
            $date = date('Y-m-d');
            $time = strtotime($date) - (3600 * 24 * 30);
            $time_limit = " AND add_time>='$time'";
            break;
        case '30day':
            $time = get_today_timestamp() - STAT_SECONDS;
            $time_limit = " AND add_time>='$time'";
            break;
        case 'unlimited':
            $time_limit = '';
            break;
    }

    $sql .= $time_limit;
    return $GLOBALS['db']->getOne($sql);
}

/**
 *  ��ȡ����ͳ����Ϣ
 *
 *  @author liupeng
 *  @param  string $time_range      today:���� unlimited:������
 *  @param  string $order_type      finish:�����
 *  @param  string $return_type     number:���ض������� amount:�����ܽ��
 *  @return int
 */

function get_order_count($time_range = 'unlimited', $order_type = 'finish', $return_type = 'number', $store_id = 0)
{
    !$store_id && $store_id = $_SESSION['store_id'];
    $sql = "SELECT %s as c FROM `ecm_order_info` WHERE extension_code NOT IN ('STORERELET') ";
    if ($store_id)
    {
        $sql .= ' AND store_id = ' . intval($store_id);
    }

    switch ($time_range)
    {
        case 'today':
            $today_timestamp = get_today_timestamp();
            $time_limit = ' AND add_time >= ' . $today_timestamp;
            break;
        case 'last_month':
            $date = date('Y-m-d');
            $time = strtotime($date) - (3600 * 24 * 30);
            $time_limit = ' AND add_time >= ' . $time;
            break;
        case '30day':
            $time = get_today_timestamp() - STAT_SECONDS;
            $time_limit = ' AND add_time >= ' . $time;
            break;
        case 'unlimited':
            $time_limit = '';
            break;
    }

    switch ($order_type)
    {
        case 'new':
            $type_limit = '';
            break;
        case 'finish':
            $type_limit = ' AND order_status = ' . ORDER_STATUS_DELIVERED;
            break;
        case 'unevalated':
            $type_limit = ' AND order_status = ' . ORDER_STATUS_DELIVERED . ' AND buyer_evaluation = ' . ORDER_EVALUATION_UNEVALUATED;
            break;
        case 'wait_for_ship':
            $type_limit = ' AND order_status IN(' . ORDER_STATUS_PROCESSING . ',' . ORDER_STATUS_ACCEPTTED . ')';
            break;
        default:
            $type_limit = ' AND order_status = ' . ORDER_STATUS_DELIVERED;
            break;
    }

    switch ($return_type)
    {
        case 'number':
            $field = 'COUNT(*)';
            break;
        case 'amount':
            $field = 'SUM(order_amount)';
            break;
        default:
            $field = 'COUNT(*)';
            break;
    }

    $sql = sprintf($sql, $field);

    $sql .= $time_limit . $type_limit;

    return $GLOBALS['db']->getOne($sql);

}


/**
 * ��ȡ����Ա����¼ʱ��
 *
 * @author liupeng
 * @return date
 */
function get_admin_last_login_time()
{
    $sql = "SELECT last_login FROM `ecm_admin_user` WHERE store_id = 0 ORDER BY last_login DESC LIMIT 1";
    $last_login = $GLOBALS['db']->getOne($sql);

    return local_date('Y-m-d h:i:s', $last_login);
}

/**
 *  ��ȡ����0ʱ��ʱ���
 *
 *  @access public
 *  @param  none
 *  @return int
 */

function get_today_timestamp()
{
    return gmstr2time(local_date('Y-m-d 0:0:0'));
}

/**
 *  ��ȡ��������
 *
 *  @access public
 *  @param  none
 *  @return int
 */
function get_store_count2($time_range = 'last_month')
{
    $sql = "SELECT count(*) AS c FROM `ecm_store` WHERE 1";
    switch ($time_range)
    {

    }

    $store_count = $GLOBALS['db']->getOne($sql);

    $store_count = intval($store_count);
    return $store_count;
}

/**
 *  ��ȡȱ������Ʒ����
 *
 *  @access public
 *  @param  none
 *  @return int
 */

function get_oos_count($store_id = 0)
{
    $sql = 'SELECT COUNT(*) as c FROM `ecm_goods` WHERE 1';
    if ($store_id)
    {
        $sql .= ' AND store_id = ' . intval($store_id);
    }
}

/**
 * ���δ�������������
 *
 * @author  weberliu
 * @return  int
 */
function get_new_apply_count()
{
    include_once(ROOT_PATH . '/includes/manager/mng.storeapply.php');

    $mng = new StoreApplyManager();
    $num = $mng->get_count(array('status' => 0));

    return $num;
}


?>
