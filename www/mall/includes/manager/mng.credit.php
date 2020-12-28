<?php

/**
 * ECMALL: ���û��ֹ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.credit.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/models/mod.adminuser.php');
require_once(ROOT_PATH . '/includes/models/mod.user.php');
require_once(ROOT_PATH . '/includes/manager/mng.order.php');

class CreditManager extends Manager
{

    /**
     *  Ҫ����Ķ�����Ϣ
     */

    var $_order_info    = array();
    var $_order_goods   = array();
    var $_to            = '';       //��˭�����û���
    var $_order_manager = NULL;
    var $_body          = NULL;
    var $_is_invalid      = FALSE;
    var $mall_store_repeat_limit = 0;
    var $mall_goods_repeat_limit = 0;
    var $mall_min_goods_amount   = 0;
    var $mall_max_goods_amount   = 0;

    /**
     *  ����Ҫ����Ķ�����Ϣ
     *
     *  @author Garbin
     *  @param  Order   $order   ������Ϣ
     *  @param  string  $to      ��˭�����û���
     *  @return void
     */
    function init($order, $to, $rank)
    {
        $this->_to         = $to;
        $this->_order      = $order;
        $this->_order_info = $this->_order->_order_data ? $this->_order->_order_data : $this->_order->get_info_with_payment();
        $this->_rank       = $rank;
        $this->init_order_manager();
    }

    /**
     *  ����������Ϣ
     *
     *  @param
     *  @return
     */
    function set_conf($array)
    {
        foreach ($array as $_k => $_v)
        {
            $this->$_k = $_v;
        }
    }

    /**
     *  ��ʼ������������
     *
     *  @author Garbin
     *  @return void
     */
    function init_order_manager()
    {
        if (!$this->_order_info['store_id'])
        {
            return;
        }
        $this->_order_manager = new OrderManager($this->_order_info['store_id']);
    }

    /**
     *  ��ʼ������ʵ��
     *
     *  @author Garbin
     *  @return void
     */
    function init_body()
    {
        if (!$this->_order_info['store_id'])
        {
            return;
        }
        if ($this->_to == 'seller')
        {
            $this->_body      =   new AdminUser($this->_order_info['store_id']);
        }
        elseif ($this->_to == 'buyer')
        {
            $this->_body      =   new User($this->_order_info['user_id']);
        }
    }

    /**
     *  ��ȡ���ܻ��ֵ��������
     *
     *  @author Garbin
     *  @return Object
     */
    function & get_body()
    {
        return $this->_body;
    }

    /**
     *  �ж��Ƿ���һ�����Լ������û��ֵĶ���
     *
     *  @author Garbin
     *  @return void
     */
    function is_valid_order()
    {
        return ($this->_order_info['is_online'] && $this->_order_info['is_paid'] && $this->_rank != ORDER_EVALUATION_COMMON);
    }

    /**
     *  �ж��Ƿ��ǺϷ���Ч��IP
     *
     *  @author Garbin
     *  @return void
     */
    function is_valid_ip()
    {
        $this->init_body();
        if ($this->_to == 'seller')
        {
            $recent_ip = $this->_body->get_recent_ip();

            /* ���IP */
            if (in_array($this->_order_info['user_ip'], $recent_ip))
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }

        }
        else
        {
            return TRUE;
        }
    }

    /**
     *  �ж��Ƿ񳬹��¶�������
     *
     *  @author Garbin
     *  @return void
     */
    function is_notover_times()
    {
        if ($this->_to == 'seller')
        {
            /* ����Ȼ�µĿ�ʼʱ�� */
            $m_timestamp = call_user_func_array('mktime', explode(',', date('0,0,0,m,1,Y', gmtime())));

            /* ��ұ����ڸõ��µĳ�Ч�Ķ������� */
            $order_num = $this->_order_manager->get_order_num($this->_order_info['user_id'], 'oi.add_time > ' . $m_timestamp . ' AND oi.order_status=' . ORDER_STATUS_DELIVERED);

            /* ���� */
            if ($this->mall_store_repeat_limit > 0 && $order_num >= $this->mall_store_repeat_limit)
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        else
        {
            return TRUE;
        }
    }

    /**
     *  �ж��Ƿ���һ����Ч����Ҫ�������û��ֵĶ���
     *
     *  @author Garbin
     *  @return Bool
     */
    function is_invalid()
    {
        $r1 = $this->is_valid_order();
        if (!$r1)
        {
            $this->_is_invalid = TRUE;
            return TRUE;
        }
        $r2 = $this->is_valid_ip();
        if (!$r2)
        {
            $this->_is_invalid = TRUE;
            return TRUE;
        }
        $r3 = $this->is_notover_times();
        if (!$r3)
        {
            $this->_is_invalid = TRUE;
            return TRUE;
        }

        return FALSE;
    }
    /**
     *  ��ȡӦ��(��)�����û�����
     *
     *  @author Garbin
     *  @return int
     */
    function get_credits()
    {
        if ($this->_is_invalid)
        {
            return 0;
        }
        $order_goods = $this->_order->list_goods();
        if ($this->_to == 'seller')
        {
            $offset =   $this->mall_goods_repeat_limit * 3600 * 24;         //ʱ����
            $bought_goods = $this->_order_manager->get_bought_goods('oi.add_time > ' . $this->_order_info['add_time'] - $offset . ' AND oi.order_status='.ORDER_STATUS_DELIVERED); //��һ����Ч�ɽ��Ķ���
            foreach ($bought_goods as $_g)
            {
                if (isset($order_goods[$_g['spec_id']]))
                {
                    unset($order_goods[$_g['spec_id']]);
                }
            }
        }

        $credits = 0;
        /* �������û��� */
        foreach ($order_goods as $_g)
        {
            if ($_g['subtotal'] <= $this->mall_min_goods_amount)
            {
                continue;
            }
            $credit_base = 1;
            $increatment = round($_g['subtotal'] / $this->mall_max_goods_amount, 1);
            if ($increatment > 1)
            {
                $increatment = 1;
            }
            $credits += $credit_base + $increatment;
        }
        if ($this->_rank == ORDER_EVALUATION_POOR)
        {
            $mark = '-';
        }
        elseif ($this->_rank == ORDER_EVALUATION_GOOD)
        {
            $mark = '+';
        }

        return intval($mark .  $credits);
    }
}

?>
