<?php

/**
 * ECMALL: 支付方式实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.payment.php 6058 2008-11-13 09:25:33Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Payment extends Model
{
    /* 当前支付的订单对象 */

    var $order = NULL;
    var $_ext_note = '';
    var $currency  = 'all';

    /**
     * 构造函数
     */
    function __construct($id, $store_id)
    {
        $this->Payment($id, $store_id);
    }

    function Payment($id, $store_id)
    {
        $this->_table = '`ecm_payment`';
        $this->_key   = 'pay_id';
        parent::Model($id, $store_id);
    }

    /**
     * 取得信息
     */
    function get_info()
    {
        $sql = "SELECT * FROM `ecm_payment` " .
                "WHERE pay_id = '" . $this->_id . "' " .
                "AND store_id = '" . $this->_store_id . "' " .
                "AND enabled = 1";
        $info = $GLOBALS['db']->getRow($sql);
        if (isset($info['config']))
        {
            $info['config'] = unserialize($info['config']);
        }

        return $info;
    }

    /**
     * 更新
     */
    function update($data)
    {
        if (isset($data['config']))
        {
            $data['config'] = serialize($data['config']);
        }

        return parent::update($data);
    }

    /**
     * 卸载
     */
    function drop()
    {
        $sql = "UPDATE `ecm_payment` " .
                "SET enabled = 0 " .
                "WHERE pay_id = '" . $this->_id . "'" . $this->_get_store_limit();

        return $GLOBALS['db']->query($sql);
    }

    function get_config($cfg = '')
    {
        if (!$cfg)
        {
            $sql = "SELECT config FROM `ecm_payment` " .
                    "WHERE pay_id = '" . $this->_id . "' " .
                    "AND store_id = '" . $this->_store_id . "' " .
                    "AND enabled = 1";
            $info = $GLOBALS['db']->getRow($sql);
            $cfg = $info['config'];
        }
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
        {
            $config = array();

            foreach ($arr AS $key => $val)
            {
                $config[$key] = $val['value'];
            }

            return $config;
        }
        else
        {
            return false;
        }
    }

    /**
     *  保存配置
     *
     *  @author Garbin
     *  @param  array $config
     *  @return void
     */
    function save_config($config)
    {
        $config = serialize($config);
        $sql = "UPDATE `ecm_payment` ".
               "SET config='{$config}' ".
               "WHERE store_id='{$this->_store_id}' AND pay_id='{$this->_id}'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     *  获取结果响应地址
     *
     *  @access public
     *  @param  string $code
     *  @return string
     */
    function get_respond_url($code)
    {
        return site_url() . '/index.php?app=respond&pay_id=' . $this->_id . '&store_id=' . $this->_store_id . '&code=' . $code;
    }

    /**
     *  判断是否已支付
     *
     *  @author Garbin
     *  @param  string $log_id
     *  @return bool
     */
    function is_paid($log_id)
    {
        $sql = "SELECT is_paid FROM `ecm_pay_log` WHERE log_id='{$log_id}'";
        $is_paid = $GLOBALS['db']->getOne($sql);

        return $is_paid;
    }
    /**
     *  通过订单号取得订单对象
     *
     *  @access public
     *  @param  string $log_id
     *  @return void
     */
    function init_order($log_id)
    {
        if ($this->order === NULL)
        {
            include_once(ROOT_PATH . '/includes/models/mod.order.php');
            $this->order  =   new Order();
            $this->order->init_by_pay_log($log_id);
        }
    }

    /**
     *  付款成功后，将订单状态改变为已接受
     *
     *  @access
     *  @param
     *  @return
     */
    function order_paid()
    {
        /* 将支付记录置为已支付状态 */
        $sql = "UPDATE `ecm_pay_log` SET is_paid=1 WHERE log_id = '{$this->order->_order_data['log_id']}'";
        $GLOBALS['db']->query($sql);

        $this->order->change_status(array(  'code'  => ORDER_STATUS_ACCEPTTED,
                                            'paid'  => $this->total_fee),
                                    gmtime(),
                                    'pay');

        /* 如果是续租订单,延长店铺租期 */
        $order_info = $this->order->get_info();
        if ($order_info['extension_code'] == 'STORERELET')
        {
            $scheme_info = unserialize($order_info['seller_comment']);
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $mod_store = new Store($order_info['user_id']);
            $store_info = $mod_store->get_info();
            $arr = array(
                'goods_limit'   => $scheme_info['allowed_goods'],
                'file_limit'    => $scheme_info['allowed_file'],
                'end_time'      => strtotime('+' . $scheme_info['allowed_month'] . ' month', max($store_info['end_time'], gmtime()))
            );
            $mod_store->update($arr);
        }
    }

    /**
     *  支付表单结构信息
     *
     *  @access public
     *  @param  string $action      支付网关中用于处理支付请求的URL地址
     *  @param  string $method      表单提交方式,GET, POST
     *  @param  array  $fields      要提交过去的数据
     *  @param  string $button_name 支付按钮名称
     *  @return
     */

    function form_info($action, $method, $fields, $button_name = 'pay_button')
    {
        return array(
                    'action' => $action,
                    'method' => $method,
                    'fields' => $fields,
                    'button_name' => Language::get($button_name)
        );
    }

    /**
     *  获取附加信息
     *
     *  @author Garbin
     *  @return string
     */
    function get_ext_note()
    {
        return $this->_ext_note;
    }
}

?>