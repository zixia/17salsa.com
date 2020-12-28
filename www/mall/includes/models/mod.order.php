<?php

/**
 * ECMALL: ����ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.order.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/*
����һ�Ŷ�����ʾ������
    $order  =   new Order();
    //���ﳵҳ
    $order->use_points(5, 50);
    $order->use_coupon('sdfeewr1q434', 50);
    $order->save_session();
    //�ջ�����Ϣ������ҳ
    $order->write_consignee_info($_POST['consignee_info']);
    $order->write_shipping_info($_POST['shipping_info']);
    $order->write_pay_info($_POST['pay_info']);
    $order->save_session();
    //����ȷ��ҳ������ű�
    $order->set_goods($_POST['order_goods']);
    $order->submit();                       //�ύ����һ�Ŷ���
�޸Ķ���ʾ�����룺
    $order  =   new Order(1);           //�޸�IDΪ1�Ķ���
    //�����޸ı�����ҳ
    $order->get_info();
    $order->write_consignee_info($_POST['consignee_info']);
    $order->write_consignee_info($_POST['shipping_info']);
    $order->write_consignee_info($_POST['pay_info']);
    if (!$order->submit())
    {
        $this->show_message($order->err);
    }
AJAX�޸Ķ���ĳ�ֶε�ֵ:
    $order  =   new Order(1);
    $order->set('consignee', 'abc');//���ջ����޸�Ϊabc
    if (!$order->submit())
    {
        $this->show_message($order->err);
    }
*/

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/includes/lib.shopping.php');

class Order extends Model
{

    /**
     *  ���ڴ�Ų�����¼�ı���
     *
     *  @access private
     *
     */

    var $_order_data = array();


    /**
     * ��Ʒ����
     */

    var $_goods_data = array();


    /**
     *  ���Ӳ����������磺������Աȡ������ʱ����Ҫ�������еĻ��֣��Ż�ȯ������
     *
     *  @access
     *  @param
     *  @return
     */

    var $_attach_event = array();


    /**
     *  �µ���
     *  ֵΪ0ʱ��ʾ�ö�������������
     *  @access
     */

    var $_user_id = 0;


    /**
     *  ָ������ID
     *
     */

    var $_id = NULL;

    /* ָ�������� */

    var $_order_sn = NULL;


    /**
     *  ��������
     *
     *  @access
     */

    var $_table = '`ecm_order_info`';


    /**
     * ����
     *
     * @access
     */

    var $_key = 'order_id';

    /**
     * ����ID
     */

    var $_store_id = NULL;

    /**
     * �����޶�SQL��ѯ��Χ,��order_id��store_idӰ��
     */

    var $_sql_where= '';

    /**
     *  ���������г��ֵĴ���
     *
     */

    var $err    = NULL;

    /**
     *  ���캯��-PHP5
     *
     *  @access public
     *  @param  int $order_id
     *  @param  string $flag_type order_idΪ����ID,order_snΪ������
     *  @return
     */
    function __construct($order_id = NULL, $store_id = NULL, $flag_type = 'order_id')
    {
        $this->Order($order_id, $store_id, $flag_type);
    }

    function Order($order_id = NULL, $store_id = NULL, $flag_type = 'order_id')
    {
        $this->_store_id = empty($store_id) ? 0 : intval($store_id);
        switch ($flag_type)
        {
        case 'order_id':
            $this->_id = empty($order_id) ? NULL : intval($order_id);
            break;
        case 'order_sn':
            $this->_order_sn = empty($order_id) ? NULL : trim($order_id);
            break;
        }

        $this->_sql_where = $this->_get_sql_where();


    }

    /**
     *    ��ȡָ����SQL����
     *
     *    @author    Garbin
     *    @param     string $ft
     *    @param     string $alias
     *    @return    string
     */
    function _get_sql_where($alias = 'oi')
    {
        $where = array();
        $alias = $alias ? $alias . '.' : '';
        $this->_id && $where[] = $alias . 'order_id =' . $this->_id;
        $this->_order_sn && $where[] = $alias . 'order_sn =\'' . $this->_order_sn . '\'';
        $this->_store_id && $where[] = $alias . 'store_id =' . $this->_store_id;

        $sql = '';
        if(!empty($where))
        {
            $sql = implode(' AND ', $where);
        }

        return $sql;
    }
    /**
     *  ͨ������������ʼ���ö�������
     *
     *  @access public
     *  @param  string $order_sn     ������
     *  @return bool
     */
    function init_by_order_sn($order_sn)
    {
        $order_sn = trim($order_sn);
        if (!$order_sn)
        {
            return false;
        }
        $sql = $this->get_info_sql("oi.order_sn='{$order_sn}'");
        $order_info = $GLOBALS['db']->getRow($sql);
        $order_info['payable']  =   get_payable($order_info);
        $this->Order($order_info['order_id']);
        $this->_order_data =& $order_info;
    }

    /**
     *  ͨ��֧����¼ID����ʼ���ö�������
     *
     *  @access public
     *  @param  string $order_sn     ������
     *  @return bool
     */
    function init_by_pay_log($log_id)
    {
        $log_id = trim($log_id);
        if (!$log_id)
        {
            return false;
        }
        $sql = $this->get_info_sql("pl.log_id='{$log_id}'");

        $order_info = $GLOBALS['db']->getRow($sql);
        $order_info['payable']  =   get_payable($order_info);
        $this->Order($order_info['order_id'], NULL);
        $this->_order_data =& $order_info;
    }

    /**
     *  ָ���ö������µ���
     *
     *  @access public
     *  @param Int $user_id         �û�ID
     *  @return void
     */
    function set_user_id($user_id)
    {
        $this->_user_id = intval($user_id);
    }


    /**
     *  ���ö�����Դ
     *
     *  @access public
     *  @param  string $referer
     *  @return void
     */
    function set_referer($referer)
    {
         $this->set('referer', $referer);
    }

    /**
     *  ��ö�����Ϣ
     *
     *  @access public
     *  @param  string $fields  Ҫ��ȡ���ֶ�
     *  @return array
     */
    function & get_info()
    {
        if (!$this->is_new())
        {
            /* ȡ�ö�������ϸ��Ϣ */
            $tmp = $GLOBALS['db']->getRow($this->get_info_sql($this->_sql_where));
        }
        else
        {   //�����SESSION�ж�ȡ
            $tmp = $_SESSION['_order_info'];
        }
        (is_array($this->_order_data) && is_array($tmp)) ? $this->_order_data += $tmp : $this->_order_data = $tmp;

        $this->_order_data['payable']   =   get_payable($this->_order_data);
        if (!$this->_id)
        {
            $this->_id = $this->_order_data['order_id'];
        }

        return $this->_order_data;
    }


    /**
     *  ��������������ĳ�ʼ���������ݵ�SQL��ѯ���
     *
     *  @access public
     *  @param  $string $where
     *  @return string
     */

    function get_info_sql($where, $info_type = 'normal')
    {
        switch ($info_type)
        {
        case 'normal':
            $sql = "SELECT oi.*,u.user_name,pl.log_id,pl.is_paid " .
                   "FROM {$this->_table} oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id ".
                   "LEFT JOIN `ecm_pay_log` pl ON oi.order_id=pl.order_id AND pl.order_type=".SHOPPING_ORDER . ' ' .
                   "WHERE {$where}";
            break;
        case 'with_payment':
            $sql = "SELECT oi.*,u.user_name,pl.log_id,pl.is_paid,p.pay_code,p.pay_name,p.is_cod,p.is_online,p.enabled,p.pay_desc " .
                   "FROM {$this->_table} oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id ".
                   "LEFT JOIN `ecm_pay_log` pl ON oi.order_id=pl.order_id AND pl.order_type=".SHOPPING_ORDER . ' ' .
                   "LEFT JOIN `ecm_payment` p ON oi.pay_id=p.pay_id " .
                   "WHERE {$where}";
            break;
        }

        return $sql;
    }

    /**
     *  ��ȡ������Ϣ��������ѡ֧����ʽ����Ϣ
     *
     *  @access public
     *  @param  none
     *  @return array
     */
    function & get_info_with_payment()
    {
        if ($this->is_new())
        {

            return FALSE;
        }
        $sql = $this->get_info_sql($this->_sql_where, 'with_payment');

        $order_info = $GLOBALS['db']->getRow($sql);
        $order_info['payable'] = get_payable($order_info);
        include_once(ROOT_PATH . '/includes/lib.editor.php');
        $order_info['pay_desc']= Editor::parse($order_info['pay_desc']);

        return $order_info;
    }


    /**
     *  ��ȡ��������Ҫ֧���Ľ��
     *
     *  @access public
     *  @param  none
     *  @return int
     */
    function get_amount()
    {
        if (!$this->_order_data && !$this->is_new())
        {
            $this->get_info();
        }

        return $this->_order_data['order_amount'];
    }

    /**
     *  ��ȡ��������Ҫ֧���Ľ��
     *
     *  @access public
     *  @param  none
     *  @return int
     */
    function get_payable()
    {
        if (!$this->_order_data && !$this->is_new())
        {
            $this->get_info();
        }

        return $this->_order_data['payable'];
    }

    /**
     *  ��ȡӦ�����͵Ļ�����
     *
     *  @access public
     *  @param  none
     *  @return int
     */
    function get_give_points()
    {
         if (!$this->is_new())
         {
            $goods_list = $this->list_goods();
            $rtn = 0 ;
            foreach ($goods_list as $_g)
            {
                $rtn += $_g['give_points'] * $_g['goods_number'];
            }

            return $rtn;
         }
    }

    /**
     *  ��ȡ����״̬
     *
     *  @access public
     *  @param none
     *  @return int
     */
    function get_order_status()
    {
        if (!$this->is_new())
        {
            return $GLOBALS['db']->getOne("SELECT oi.order_status FROM {$this->_table} oi WHERE {$this->_sql_where}");
        }
    }

    /**
     *  �б���Ʒ
     *
     *  @author Garbin
     *  @return array
     */
    function list_goods()
    {
        if (!$this->is_new())
        {
            $goods = $GLOBALS['db']->getAll( "SELECT og.*,gs.default_image,g.give_points,g.is_deny,og.goods_price as store_price,gs.spec_name,gs.color_name " .
                                        "FROM `ecm_order_goods` og ".
                                        "LEFT JOIN `ecm_order_info` oi ON og.order_id=oi.order_id " .
                                        "LEFT JOIN `ecm_goods_spec` gs ON og.spec_id=gs.spec_id " .
                                        "LEFT JOIN `ecm_goods` g ON gs.goods_id=g.goods_id " .
                                        "WHERE {$this->_sql_where}");

        }
        else
        {
            $goods = $_SESSION['_goods_data'];
        }
        if (empty($goods))
        {
            return ;
        }
        $tmp_arr = array();
        foreach ($goods as $_k => $_v)
        {
            $_v['goods_price'] = $_v['store_price'];
            $_v['subtotal'] = $_v['goods_price'] * $_v['goods_number'];
            $tmp_arr[$_v['spec_id']] = $_v;
            /*
            if (!$_v['is_gift'])
            {
               $rtn_arr['goods'][$_v['spec_id']] = $_v;
            }
            else
            {
               $rtn_arr['gifts'][$_v['spec_id']] = $_v;
            }
            */
        }
        unset($goods);

        return $tmp_arr;
    }

    /**
     *  ����һ���������
     *
     *  @access public
     *  @param  none
     *  @return string
     */
    function create_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     *  ����һ�����׺�
     *
     *  @access public
     *  @param  none
     *  @return string
     */
    function create_pay_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);

        return $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     *  �ж��Ƿ�ָ���˶���ID
     *
     *  @access public
     *  @param  none
     *  @return Bool
     */
    function is_new()
    {

        return (is_null($this->_id) && is_null($this->_order_sn));
    }

    /**
     *  ���¶�����Ϣ
     *
     *  @access public
     *  @param  array $info     Ҫ���µ��ֶ�ֵ
     *  @return Bool
     */
    function update($info)
    {
        if (!is_array($info))
        {

            return FALSE;
        }
        foreach ((array)$info as $_k => $_v)
        {
            $this->set($_k, $_v);
        }

        return TRUE;
    }

    /**
     *  ɾ��һ�Ŷ���
     *
     *  @access public
     *  @param
     *  @return
     */
    function drop()
    {
        if ($this->is_new())
        {
            $this->err[] = 'invalid_order';

            return FALSE;
        }
        $sql_where = $this->_get_sql_where('');
        $GLOBALS['db']->query("DELETE FROM `ecm_order_info` WHERE {$sql_where} AND order_status = 7");
        if (!$GLOBALS['db']->affected_rows())
        {
            $this->err[] = 'not_allow_drop';
            return FALSE;
        }
        $this->drop_goods();
        $this->drop_action_log();
    }

    /**
     *  ɾ����������Ʒ
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function drop_goods()
    {
        $sql = "DELETE FROM `ecm_order_goods` WHERE order_id={$this->_id}";

        $GLOBALS['db']->query($sql);
    }

    /**
     *  ɾ������������־
     *
     *  @access
     *  @param  none
     *  @return void
     */

    function drop_action_log()
    {
        $sql = "DELETE FROM `ecm_order_action` WHERE order_id={$this->_id}";
        $GLOBALS['db']->query($sql);
    }

    /**
     *  д���ջ�����Ϣ
     *
     *  @access public
     *  @param  array $info     Ҫд�����Ϣ
     *  @return bool
     */
    function write_consignee_info($info)
    {
        $this->set('consignee', $info['consignee']);
        $this->set('region', $info['region']);
        $this->set('region_id', $info['region_id']);
        $this->set('address', $info['address']);
        $this->set('zipcode', $info['zipcode']);
        $this->set('office_phone', $info['office_phone']);
        $this->set('home_phone', $info['home_phone']);
        $this->set('mobile_phone', $info['mobile_phone']);
        $this->set('email', $info['email']);
        $this->set('sign_building', $info['sign_building']);
        $this->set('best_time', $info['best_time']);
        $this->set('post_script', $info['post_script']);

        return $this->err;
    }

    /**
     *  ����������Ϣ
     *
     *  @access public
     *  @param  array $info     Ҫд�����Ϣ����
     *  @return Bool
     */
    function write_shipping_info($info)
    {
        $this->set('shipping_id', $info['shipping_id']);
        $this->set('shipping_name', $info['shipping_name']);

        return $this->err;
    }

    /**
     *  ����֧����ʽ
     *
     *  @access public
     *  @param  array $info     Ҫд�����Ϣ����
     *  @return Bool
     */
    function write_payment_info($info)
    {
        $this->set('pay_id', $info['pay_id']);
        $this->set('pay_name', $info['pay_name']);

        return $this->err;
    }

    /**
     *  ָ�����Ʒ������
     *
     *  @access public
     *  @param  array $goods_list Ҫд�����Ϣ����
     *  @return Bool
     */
    function set_goods($goods_list)
    {
        $this->_goods_data = $goods_list;
    }

    /**
     *  ��ӵ�һ��Ʒ������
     *
     *  @access public
     *  @param  int $spec_id    ���ID
     *  @param  array $goods_info   ��Ʒ��Ϣ
     *  @return
     */
    function add_goods($spec_id, $goods_info)
    {
        $this->_goods_data[$spec_id] = $goods_info;
    }

    /**
     *  ʹ�û���
     *
     *  @access public
     *  @param  int $points         ������
     *  @param  double $points_value���ּ�ֵ
     *  @return Bool
     */
    function use_points($points, $points_value)
    {
        $this->set('points', $points);
        $this->set('points_value', $points_value);

        return $this->err;
    }

    /**
     *  ʹ���Ż�ȯ
     *
     *  @access public
     *  @param string $coupon_sn        �Ż�ȯ��
     *  @param double $coupon_value     �Ż�ȯ��ֵ
     *  @return mixed
     */
    function use_coupon($coupon_sn, $coupon_value)
    {
        $this->set('coupon_sn', $coupon_sn);
        $this->set('coupon_value', $coupon_value);

        return $this->err;
    }

    /**
     *  ���ö���״̬
     *
     *  @access public
     *  @param int $status_no   ״̬��
     *  @return Bool
     */
    function set_status($status_no)
    {
        $this->set('order_status', $status_no);
        return $this->err;
    }

    /**
     *  �޸Ķ���״̬
     *
     *  @access public
     *  @param int $status_no
     *  @return int
     */

    function change_status($status_info, $time = 0, $time_type = NULL)
    {
        if ($this->is_new())
        {
            $this->err[] = 'new_order_disabled';
            return FALSE;
        }
        $status_no = is_array($status_info) ? intval($status_info['code']) : intval($status_info);
        $time_field = '';
        $money_field= '';
        if ($time_type == 'pay' || $time_type == 'ship')
        {
            switch ($time_type)
            {
            case 'pay':
                $time_field  = ',pay_time=' . $time;
                $money_field = ',money_paid=money_paid+(' . $status_info['paid'] . ')';
                break;
            case 'ship':
                $time_field = ',ship_time=' . $time;
                break;
            }
        }
        $sql = "UPDATE `ecm_order_info` oi SET order_status={$status_no}{$time_field}{$money_field} WHERE {$this->_sql_where}";
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     *  ���ý��
     *
     *  @access public
     *  @param  array $info     Ҫд�����Ϣ����
     *  @return Bool
     */
    function write_charge_info($info)
    {
        isset($info['shipping_fee']) && $this->set('shipping_fee',    $info['shipping_fee']);        //���ͷ���
        isset($info['insure_fee']) && $this->set('insure_fee',      $info['insure_fee']);          //���۷���
        isset($info['pay_fee']) && $this->set('pay_fee',         $info['pay_fee']);             //֧������
        isset($info['discount']) && $this->set('discount',        $info['discount']);            //�ֽ��ۿ�
        isset($info['money_paid']) && $this->set('money_paid',      $info['money_paid']);          //�Ѹ���
        isset($info['order_amount']) && $this->set('order_amount',    $info['order_amount']);        //��Ʒ�ܽ��
        isset($info['goods_amount']) && $this->set('goods_amount',    $info['goods_amount']);        //��Ʒ�ܽ��
        isset($info['inv_fee']) && $this->set('inv_fee', $info['inv_fee']);                         //��Ʊ����

        return $this->err;
    }

    /**
     *  ���÷�Ʊ����Ϣ
     *
     *  @access public
     *  @param  arran $info Ҫд�����Ϣ����
     *  @return void
     */
    function write_inv_info($info)
    {
        isset($info['inv_payee']) && $this->set('inv_payee',     $info['inv_payee']);        //��Ʊ̧ͷ
        isset($info['inv_fee']) && $this->set('inv_fee',       $info['inv_fee']);          //��Ʊ˰��
        isset($info['inv_content']) && $this->set('inv_content',   $info['inv_content']);      //��Ʊ����
        isset($info['inv_type']) && $this->set('inv_type',      $info['inv_type']);         //��Ʊ����
    }

    /**
     *  д��ȷ��ʱ��
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function set_confirm_time()
    {
        $time   =   gmtime();
        $this->set('confirm_time', $time);
        $this->submit();
    }

    /**
     *  д��֧��ʱ��
     *
     *  @access public
     *  @param  none
     *  @return bool
     */
    function set_pay_time()
    {
        $time   =   gmtime();
        $this->set('pay_time', $time);
        $this->submit();
    }

    /**
     *  д�뷢��ʱ��
     *
     *  @access public
     *  @param  none
     *  @return bool
     */
    function set_shipping_time()
    {
        $time   =   gmtime();
        $this->set('shipping_time', $time);
        $this->submit();
    }

    /**
     *  д�뷢������
     *
     *  @access public
     *  @param  string $invoice_no      ��������
     *  @return bool
     */
    function set_invoice($invoice_no)
    {
        return $this->set('invoice_no', $invoice_no);
    }

    /**
     *  ������չ��Ϣ�����������ʱ�õ�
     *
     *  @access public
     *  @param  string $extension_code GROUPBUYΪ�Ź�
     *  @param  int    $extension_id   �ID
     *  @return
     */

    function set_extension_info($extension_code, $extension_id)
    {
        $this->set('extension_code', $extension_code);
        $this->set('extension_id',   $extension_id);
    }

    /**
     *  ���ö�������
     *
     *  @author Garbin
     *  @param  string $field        Ҫ���õ��ֶ���
     *  @param  mixed $value         Ҫ���õ��ֶ�ֵ
     *  @param  boolean $is_valid    �Ƿ��������֤��������Ҫ�������ݿ⣬Ĭ��ֵΪFalse
     *  @return
     */
    function set($field, $value, $is_valid = FALSE)
    {
        switch($field)
        {
            case 'store_id':        //����ID
                if (!$value)
                {
                    $this->err[] = 'invalid_store_id';
                    return FALSE;
                }
                break;
            case 'user_id':         //�û�ID
                $value && $value = intval($value);
                break;
            case 'order_status':    //״̬
                $value && $value = intval($value);
                break;
            case 'consignee':       //�ջ�����Ϣ(����)
                if (!$value)
                {
                    $this->err[] = 'invalid_consignee_name';
                    return FALSE;
                }
                break;
            case 'region_id':
                $value = intval($value);
                break;
            case 'region':              //���ڵ���(����)
                if (!$value)
                {
                    $this->err[] = 'invalid_region';
                    return FALSE;
                }
                break;
            case 'address':
                if (!$value)
                {
                    return FALSE;
                }
                break;
            case 'zipcode':
            case 'coupon_sn':
            case 'user_ip':
                $value  =   trim($value);
                break;
            case 'office_phone':
                $value  = trim($value);
                break;
            case 'home_phone':
                $value  = trim($value);
                break;
            case 'mobile_phone':
                $value  = trim($value);
                break;
            case 'email':
                if (!is_email($value))
                {
                    $this->err[] = 'invalid_email';

                    return FALSE;
                }
                break;
            case 'best_time':
            case 'sign_building':
                break;
            case 'postscript':
                if (!$value)
                {

                    return;
                }
                break;
            case 'shipping_id':
                if (!$value)
                {
                    $this->err[] = 'invalid_shipping_id';

                    return FALSE;
                }
                break;
            case 'shipping_name':
                break;
            case 'pay_id':
                if (!(int)$value)
                {
                    $this->err[] = 'invalid_pay_id';

                    return FALSE;
                }
                break;
            case 'pay_name':
                $value = trim($value);
                break;
            case 'inv_payee':
                break;
            case 'inv_content':
                break;
            case 'inv_type':
                break;
            case 'inv_fee':
            case 'money_paid':
            case 'discount':
            case 'pay_fee':
            case 'insure_fee':
            case 'shipping_fee':
            //case 'points_value':
            case 'coupon_value':
            case 'goods_amount':
            case 'order_amount':
                $value = doubleval($value); //����PHP4<4.20
                break;
            case 'points':
                $value = intval($value);
                break;
            case 'extension_code':
                $value = trim($value);
                break;
            case 'extension_id':
                $value = intval($value);
                break;
            case 'add_time':
            case 'invoice_no':
            case 'referer':
            case 'post_script':
                break;
            case 'pay_time':
            case 'ship_time':
                $value = intval($value);
                break;
            case 'seller_evaluation':
            case 'buyer_evaluation':
                $value = intval($value);
                if ($value < 1 || $value > 3)
                {
                    $this->err[] = 'out_of_range';

                    return FALSE;
                }
                break;
            case 'seller_credit':
            case 'buyer_credit':
            case 'seller_evaluation_invalid':
            case 'buyer_evaluation_invalid':
                break;
            case 'seller_comment':
            case 'buyer_comment':
                $value = trim($value);
                break;
            case 'is_anonymous':
                $value = $value ? 1 : 0;
                break;
            default:
                $this->err[] = 'no_this_field';

                return FALSE;
                break;
        }
        if ($is_valid)
        {

            return $value;
        }
        else
        {
            $this->_order_data[$field]  = $value;
        }

        return TRUE;
    }


    /**
     *  �޸���Ʒ�б���Ϣ
     *
     *  @access public
     *  @param int      $spec_id         ��Ʒ���ID
     *  @param string   $key             Ҫ�޸ĵ��ֶ���
     *  @param mixed    $value           Ҫ�޸ĳɵ�ֵ
     *  @return bool
     */

    function set_goods_info($spec_id, $key, $value)
    {
        $set_field = NULL;
        $sql = NULL;
        $action = NULL;
        switch ($key)
        {
        case 'goods_number':
            $value = intval($value);
            if (!$value)
            {
                $sql = "DELETE FROM `ecm_order_goods` oi WHERE oi.spec_id={$value} AND {$this->_sql_where}";
            }
            else
            {
                $set_field = "{$key}={$value}";
            }
            $action = 'fix_amount';
        case 'goods_price':
            $value = floatval($value);
            $set_field = "{$key}={$value}";
            $action = 'fix_amount';
            break;
        case 'sku':
            $value = trim($value);
            $set_field = "{$key}='{$value}'";
            break;
        }
        $set_field && $sql = "UPDATE `ecm_order_goods` SET {$set_field}";
        $sql && $GLOBALS['db']->query($sql);
        $action && $this->$action();
        return TRUE;
    }

    /**
     *  ����order_amount��goods_amount
     *
     *  @access public
     *  @param  none
     *  @return bool
     */

    function fix_amount()
    {
        $goods_list = $this->list_goods();
        if (!empty($goods_list))
        {
            $goods_amount = 0;
            foreach ($goods_list as $goods)
            {
                $goods_amount = $goods['goods_price'] * $goods['goods_number'];
            }
            $sql = "UPDATE {$this->_table} oi SET goods_amount={$goods_amount},order_amount={$goods_amount}+(order_amount-goods_amount) WHERE {$this->_sql_where}";
            $GLOBALS['db']->query($sql);
            return TRUE;
        }
        return FALSE;
    }

    /**
     *  ȡ��SQL��SET���沿�ֵ����ݣ��������ɴ����$info���鹹��
     *
     *  @access public
     *  @param  array $info Ҫ�޸ĵ��ֶ�����
     *  @return string      ����email='abc@abc.com',zipcode='234567'���ַ���
     */

    function get_set_fields($info)
    {
        $field_arr = array();
        foreach ($this->_order_data as $_k => $_v)
        {
            /* �ų�����order_info������� */
            if (in_array($_k, array('goods', 'order_id', 'user_name', 'payable', 'log_id', 'is_paid', 'pay_fee_formated')))
            {
                continue;
            }
            $field_arr[] = "{$_k}='{$_v}'";
        }

        return implode(',', $field_arr);
    }

    /**
     *  ����ʱ���ݱ�����Session��
     *
     *  @access public
     *  @param  none
     *  @return Bool
     */

    function save_session()
    {
        $_SESSION['_order_info'] = is_array($_SESSION['_order_info']) ? array_merge($_SESSION['_order_info'], $this->_order_data) : $this->_order_data;
        $_SESSION['_goods_data'] = is_array($_SESSION['_goods_data']) ? $_SESSION['_goods_data'] + $this->_goods_data : $this->_goods_data;
    }

    /**
     *  ���������Session�е�����
     *
     *  @access public
     *  @params none
     *  @return void
     */

    function clear_session()
    {
        unset($_SESSION['_order_info']);
        unset($_SESSION['_goods_data']);
    }

    /**
     *  ��ô���
     *
     *  @access public
     *  @param  none
     *  @return bool
     */

    function has_err()
    {
        if (count($this->err)>0)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     *  ��֪�ö����е���Ʒ�Ƿ����ʵ����Ʒ
     *
     *  @access public
     *  @param  none
     *  @return int
     */

    function has_real_goods()
    {
        $goods_list =& $this->list_goods();
        $result = 0;
        if (empty($goods_list))
        {
            return $result;
        }

        foreach ($goods_list as $goods)
        {
            if ($goods['is_real'])
            {
                $result++;
            }
        }

        return $result;
    }

    /**
     *  �ύ��Ʒ��Ϣ
     *
     *  @author Garbin
     *  @param  bool    $new_order_flag     ��ʶ�Ƿ�����������
     *  @return void
     */

    function submit_goods_info($new_order_flag)
    {
        /* ��Ʒ���� */
        if (!$new_order_flag && !empty($this->_goods_data))    //�����¶������򰴴˷�ʽ����
        {   //������Ʒ����

            /* ȡ�þɵ���Ʒ�б� */
            $old_goods_list = $this->list_goods();

            /* �����µ���Ʒ�б� */
            foreach ($this->_goods_data as $_k=>$n_g)
            {
                foreach ($old_goods_list as $_o_k => $o_g)
                {
                    if ($o_g['spec_id'] ==  $n_g['spec_id'])
                    {
                        //���Ѵ����ھɶ���������¾ɼ�¼����Ϣ
                        if ($o_g['goods_number'] != $n_g['goods_number'] || $o_g['store_price'] != $n_g['store_price'])
                        {
                            $GLOBALS['db']->query("UPDATE `ecm_order_goods` ".
                                                  "SET goods_number={$n_g['goods_number']},goods_price={$n_g['store_price']} ".
                                                  "WHERE spec_id={$n_g['spec_id']} AND {$this->_sql_where}");
                        }
                        unset($this->_goods_data[$_k]); //����Ʒ�б���ȥ���������ظ����
                        unset($old_goods_list[$_o_k]);  //�Ӿ���Ʒ�б���ȥ�����Եó���Ҫɾ������Ʒ
                        break;
                    }
                }
            }
        }

        /* �Ӿ���Ʒ�б���ɾ����Ʒ����Ʒ�б���û�е���Ʒ */
        if (!empty($old_goods_list))
        {
            $drop_ids = array();
            foreach ($old_goods_list as $_k => $_v)
            {
                $drop_ids[] = $_v['spec_id'];
            }
            $drop_ids = db_create_in($drop_ids);
            $GLOBALS['db']->query("DELETE FROM `ecm_order_goods` WHERE spec_id {$drop_ids}");
        }

        /* ������Ʒ����Ʒ�б� */
        if (!empty($this->_goods_data))
        {
            $this->_goods_data = addslashes_deep($this->_goods_data);
            $sql = 'INSERT INTO `ecm_order_goods`(order_id,goods_id,spec_id,goods_name,sku,goods_number,market_price,goods_price,is_real,extension_code) VALUES';
            $values = array();
            foreach ($this->_goods_data as $_v)
            {
                $values[] = "({$this->_id},{$_v['goods_id']},{$_v['spec_id']},'{$_v['goods_name']}','{$_v['sku']}',{$_v['goods_number']},'{$_v['market_price']}','{$_v['store_price']}','{$_v['is_real']}','{$_v['extension_code']}')";
            }
            $sql .= implode(',', $values);

            /* ����Ʒ������� */
            $GLOBALS['db']->query($sql);
        }
    }

    /**
     *  �ύ
     *
     *  @access public
     *  @param  bool $update_goods �Ƿ�����������Ʒ����
     *  @return bool
     */

    function submit($update_goods = TRUE)
    {
        if ($this->err !== NULL)
        {
            return FALSE;
        }
        $new_order_flag = FALSE;    //�Ƿ����¼Ӷ�����ʶ
        if (!empty($this->_order_data))
        {
            if (!$this->is_new())
            {   //��ָ����order_id�����޸�order_id������¼
                $fields = $this->get_set_fields($this->_order_data);
                $sql = "UPDATE {$this->_table} oi SET {$fields} WHERE {$this->_sql_where}";
            }
            else
            {   //���򣬲���һ���¼�¼

                /* ������ */
                $required_arr = array('consignee',
                                      'region',
                                      'region_id',
                                      'address',
                                      'zipcode',
                                      'email',
                                      'home_phone',
                                      'mobile_phone',
                                      'shipping_id',
                                      'shipping_name',
                                      'pay_id',
                                      'pay_name',
                                      'goods_amount',
                                      'order_amount');
                $has_phone_number = FALSE; //�Ƿ���д����ϵ�绰
                foreach ($required_arr as $required)
                {
                    switch ($required)
                    {
                    case 'home_phone':
                    case 'mobile_phone':
                        if ($this->_order_data[$required])
                        {
                            $has_phone_number = TRUE;
                        }
                        break;
                    default:
                        if (!$this->_order_data[$required])
                        {
                            $this->err[] = $required . '_required';
                            return FALSE;
                        }
                        break;
                    }
                }
                if (!$has_phone_number)
                {
                    $this->err[] = 'phone_number_required';

                    return FALSE;
                }
                if (!$this->_order_data['order_status'])
                {
                    $this->_order_data['order_status'] = ORDER_STATUS_PENDING;
                }
                $fields = $this->get_set_fields($this->_order_data);
                if (empty($this->_goods_data))
                {
                    $this->err[] = 'no_goods';
                    return FALSE;
                }
                $order_sn = $this->create_sn();
                $this->_add_time = gmtime();
                $fields .= ',store_id=' . $this->_store_id;             //�������ID
                $fields .= ',order_sn=\'' . $order_sn . '\'';  //���붩����
                $fields .= ',user_id=' . $this->_user_id;
                $fields .= ',add_time=' . $this->_add_time;                     //����ʱ��
                $sql = "INSERT INTO {$this->_table} SET {$fields}";
                $new_order_flag = TRUE;
            }

            /* ����������� */
            $GLOBALS['db']->query($sql);


            /* ȡ���²���Ķ���ID */
            if ($this->is_new())
            {
                $this->_id = $GLOBALS['db']->insert_id();
                $this->_order_sn = $order_sn;

                /* ���������֧����������һ��PayLog */
                //$this->insert_pay_log($this->_id, SHOPPING_ORDER);
            }
        }

        /* �ύ��Ʒ��Ϣ */
        $update_goods && $this->submit_goods_info($new_order_flag);

        return $this->_id;
    }

    /**
     *  ���˴����ݿ���ȡ�õ�����
     *
     *  @author Garbin
     *  @return void
     */
    function filter_data()
    {
        $this->_order_data = addslashes_deep($this->_order_data);
    }

    /**
     *  ȡ������
     *
     *  @author Garbin
     *  @return void
     */
    function cancel()
    {
        if ($this->is_new())
        {
            $this->err = 'this_is_new_order';

            return FALSE;
        }
        if (empty($this->_order_data))
        {
            $this->get_info();
        }
        if (empty($this->_order_data))
        {
            $this->err = 'invalid_order';

            return FALSE;
        }

        if ($this->_order_data['order_status'] >= ORDER_STATUS_DELIVERED)
        {
            $this->err = 'not_allow_cancel';

            return FALSE;
        }

        $this->set_status(ORDER_STATUS_REJECTED);
        if ($this->_order_data['coupon_sn'])
        {
            /* �����Ż�ȯʹ�ô��� */
            include_once(ROOT_PATH . '/includes/models/mod.coupon.php');
            $coupon = new Coupon(0, $this->_order_data['store_id']);
            $coupon->init_by_sn($this->_order_data['coupon_sn']);
            $coupon->update_usable_times(1);
        }
        $goods_list = $this->list_goods();
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        foreach ($goods_list as $_g)
        {
            $goods = new Goods($_g['goods_id']);
            $goods->set_stock($_g['spec_id'], $_g['goods_number']);
        }
        $this->filter_data();

        return $this->submit(FALSE);
    }


    /**
     *  ����֧����¼
     *
     *  @access public
     *  @param  string $order_id
     *  @param  string $order_type
     *  @param  float  $order_amount
     *  @return void
     */
    function insert_pay_log($order_id, $order_type)
    {
        $log_id = $this->create_pay_sn();
        $sql = "REPLACE INTO `ecm_pay_log`(log_id, order_id, order_type) VALUES('{$log_id}', $order_id, '{$order_type}')";
        $GLOBALS['db']->query($sql);
    }

    /**
     *  ��ȡ�ⲿ���׺�
     *
     *  @param
     *  @return
     */
    function get_pay_log()
    {
        if ($this->is_new())
        {
            return FALSE;
        }
        if ($this->_order_data['is_paid'] > 0)
        {

            return FALSE;
        }
        $log_id = $this->create_pay_sn();
        $sql = "REPLACE INTO `ecm_pay_log`(log_id, order_id, order_type) VALUES('{$log_id}', $this->_id, ".SHOPPING_ORDER.")";
        $GLOBALS['db']->query($sql);

        return $log_id;
    }

    /**
     *  ����������
     *
     *  @access public
     *  @param  int $evaluation_code
     *  @return void
     */
    function seller_evaluation($evaluation_code)
    {
        $this->set('seller_evaluation', $evaluation_code);
    }

    /**
     *  ��������������
     *
     *  @author Garbin
     *  @param  string $comment
     *  @return void
     */
    function seller_comment($comment)
    {
        $this->set('seller_comment', $comment);
    }

    /**
     *  �����һ���
     *
     *  @author Garbin
     *  @param  int $num
     *  @return void
     */
    function add_seller_credit($num)
    {
        $this->set('seller_credit', $num);
    }

    /**
     *  �������
     *
     *  @access public
     *  @param  int $evaluation_code
     *  @return void
     */
    function buyer_evaluation($evaluation_code)
    {
        $this->set('buyer_evaluation', $evaluation_code);
    }

    /**
     *  �������������
     *
     *  @author Garbin
     *  @param  string $comment
     *  @return void
     */
    function buyer_comment($comment)
    {
        $this->set('buyer_comment', $comment);
    }

    /**
     *  ����һ���
     *
     *  @author Garbin
     *  @param  int $num
     *  @return void
     */
    function add_buyer_credit($num)
    {
        $this->set('buyer_credit', $num);
    }

    /**
     *  �б������õ�״̬
     *
     *  @access public
     *  @param  string   $type          ���add,��������cod_order,
     *  @param  int      $order_status  ״̬��
     *  @return array
     */

    function list_status($type = 'add', $order_status = '')
    {
        switch ($type)
        {
        case 'add':
            return array(
                    ORDER_STATUS_PENDING=>'order_status_pending',
                    ORDER_STATUS_SUBMITTED=>'order_status_submitted',
                    ORDER_STATUS_ACCEPTTED=>'order_status_acceptted',
                    ORDER_STATUS_PROCESSING=>'order_status_processing',
                    ORDER_STATUS_SHIPPED=>'order_status_shipped',
                    ORDER_STATUS_DELIVERED=>'order_status_delivered'
            );
        case 'cod_order':
            return array(
                    ORDER_STATUS_SUBMITTED=>'order_status_submitted',
                    ORDER_STATUS_ACCEPTTED=>'order_status_acceptted',
                    ORDER_STATUS_PROCESSING=>'order_status_processing',
                    ORDER_STATUS_SHIPPED=>'order_status_shipped',
                    ORDER_STATUS_DELIVERED=>'order_status_delivered',
            );
        default:
            $status_list = array(
                    ORDER_STATUS_PENDING=>'order_status_pending',
                    ORDER_STATUS_ACCEPTTED=>'order_status_acceptted',
                    ORDER_STATUS_PROCESSING=>'order_status_processing',
                    ORDER_STATUS_SHIPPED=>'order_status_shipped',
                    ORDER_STATUS_DELIVERED=>'order_status_delivered',
            );
            if(isset($status_list[$order_status]))
            {
                unset($status_list[$order_status]);
            }

            return $status_list;
        }
    }

    /**
     *    ���ö���������Ч
     *
     *    @author    Garbin
     *    @param     string $body
     *    @return    bool
     */
    function evaluation_invalid($body)
    {
        if (!$body)
        {
            return false;
        }
        switch ($body)
        {
            case 'seller':
                $this->set('seller_evaluation_invalid', 1);
            break;
            case 'buyer':
                $this->set('buyer_evaluation_invalid', 1);
            break;
        }
        $this->filter_data();

        return $this->submit(FALSE);
    }
}

?>
