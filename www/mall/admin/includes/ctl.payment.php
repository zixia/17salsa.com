<?php

/**
 * ECMALL: 支付方式控制器类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.payment.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
include_once(ROOT_PATH . '/includes/models/mod.payment.php');

class PaymentController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->PaymentController($act);
    }

    function PaymentController($act)
    {
        $this->_mng = new PaymentManager($_SESSION['store_id']);

        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看列表
     */
    function view()
    {
        $this->logger = false; // 不记日志

        /* 取得列表 */
        $list = $this->_mng->get_list();

        if($list['data'] && isset($list['data']['tenpay']))
        {
            $arr = array('tenpay' => $list['data']['tenpay']);
            unset($list['data']['tenpay']);
            $list['data'] = array_merge($arr, $list['data']);
        }

        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('payment_stats', $list['info']['rec_count'], $list['info']['installed']));

        /* 显示模版 */
        $this->display('store/payment.view.html');
    }

    /**
     * ajax编辑
     */
    function modify()
    {
        /* 参数 */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('pay_fee', 'sort_order')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        $id    = intval($_GET['id']);
        $value = trim($_GET['value']);
        $this->log_item = $id;

        if ($field_name == 'pay_fee')
        {
            $value = format_fee($value);
        }

        /* 更新 */
        $mod = new Payment($id, $_SESSION['store_id']);
        $mod->update(array($field_name => $value));
        $this->log_item = $id;
        $this->json_result();
        return;
    }

    /**
     * 删除
     */
    function drop()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $this->log_item = $id;
        $mod = new Payment($id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* 卸载 */
        $mod->drop();
        $this->log_item = $id;
        $this->show_message('drop_ok', 'back_payment', 'admin.php?app=payment&act=view');
        return;
    }

    /**
     *  安装
     *  @author Garbin
     *  @return void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $code = empty($_GET['code']) ? '' : trim($_GET['code']);
            $built_in = $this->_mng->get_built_in($code);
            if (empty($built_in))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $installed = $this->_mng->get_installed($code);
            if (!empty($installed))
            {
                $this->show_warning('already_installed');
                return;
            }

            /* 初始化 */
            $this->assign('info', $built_in);
            $this->display('store/payment.detail.html');
        }
        else
        {
            /* 参数 */
            $code = empty($_POST['pay_code']) ? '' : trim($_POST['pay_code']);
            $built_in = $this->_mng->get_built_in($code);
            if (empty($built_in))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $installed = $this->_mng->get_installed($code);
            if (!empty($installed))
            {
                $this->show_warning('already_installed');
                return;
            }

            /* 提交数据 */
            $config = $built_in['config'];
            if (isset($_POST['config_name']))
            {
                foreach ($_POST['config_name'] as $key => $name)
                {
                    $config[$name]['value'] = $_POST['config_value'][$key];
                }
            }

            $info = array(
                'store_id'  => $_SESSION['store_id'],
                'pay_code'  => $code,
                'pay_name'  => $built_in['pay_name'],
                'pay_desc'  => empty($_POST['pay_desc']) ? '' : trim($_POST['pay_desc']),
                'pay_fee'   => empty($_POST['pay_fee']) ? 0 : format_fee(trim($_POST['pay_fee'])),
                'config'    => $config,
                'is_cod'    => $built_in['is_cod'],
                'is_online' => $built_in['is_online'],
                'enabled'   => 1,
                'sort_order'=> 0,
                'author'    => $built_in['author'],
                'website'   => $built_in['website'],
                'version'   => $built_in['version']
            );

            /* 插入 */
            $pay_id         = $this->_mng->add($info);
            $this->log_item = $pay_id;

            /* 支付宝则提示他要签约 */
            if ($code == 'alipay' && ($config['alipay_real_method']['value'] == '0' || $config['alipay_virtual_method']['value'] == '0'))
            {
                $this->show_message('alipay_need_sign_to_use',
                                    'to_edit_payment', 'admin.php?app=payment&act=edit&id=' . $pay_id,
                                    'back_payment', 'admin.php?app=payment&amp;act=view');
            }
            else
            {
                $this->show_message('add_ok',
                                    'back_payment', 'admin.php?app=payment&amp;act=view');

            }
            return;
        }
    }

    /**
     * 编辑
     *  @author Garbin
     *  return void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
            $mod    = new Payment($id, $_SESSION['store_id']);
            $info   = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }
            if(!$this->_drop_if_invalid($info['pay_code'], $mod))
            {
                return;
            }

            $this->assign('info', $info);
            $this->display('store/payment.detail.html');
        }
        else
        {
            /* 参数 */
            $id     = empty($_POST['id']) ? 0 : intval($_POST['id']);
            $mod    = new Payment($id, $_SESSION['store_id']);
            $info   = $mod->get_info();

            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }


            if(!$this->_drop_if_invalid($info['pay_code'], $mod))
            {
                return;
            }

            /* 提交数据 */
            $config = $info['config'];
            if (isset($_POST['config_name']))
            {
                foreach ($_POST['config_name'] as $key => $name)
                {
                    if ($name == 'alipay_account')
                    {
                        if ($_POST['config_value'][$key] != $info['config']['alipay_account']['value'])
                        {
                            unset($config['customer_code']);
                        }
                    }
                    $config[$name]['value'] = $_POST['config_value'][$key];
                }
            }

            $info = array(
                'pay_desc'  => empty($_POST['pay_desc']) ? '' : trim($_POST['pay_desc']),
                'pay_fee'   => empty($_POST['pay_fee']) ? 0 : format_fee(trim($_POST['pay_fee'])),
                'config'    => $config
            );

            /* 更新 */
            $mod->update(addslashes_deep($info));
            $this->log_item = $id;
            /* 支付宝则提示他要签约 */
            if ($_POST['pay_code'] == 'alipay' && ($config['alipay_real_method']['value'] == '0' || $config['alipay_virtual_method']['value'] == '0') && !$config['customer_code'])
            {
                $this->show_message('alipay_need_sign_to_use',
                                    'to_edit_payment', 'admin.php?app=payment&act=edit&id=' . $id,
                                    'back_payment', 'admin.php?app=payment&amp;act=view');
            }
            else
            {
                $this->show_message('edit_ok', 'back_payment', 'admin.php?app=payment&act=view');

            }

            return;
        }
    }

    /**
     *  支付宝客户签约
     *
     *  @author Garbin
     *  @return void
     */
    function alipay_sign()
    {
        $mode = $_GET['do'];
        include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
        include_once(ROOT_PATH . '/includes/payment/alipay.php');

        /* 判断是否启用 */
        $payment_manager = new PaymentManager($_SESSION['store_id']);
        $pay_info = $payment_manager->get_installed('alipay');
        if (!$pay_info)
        {
            $this->show_warning('enable_first');
            return;
        }
        $alipay = new alipay($pay_info['pay_id'], $pay_info['store_id']);
        if ($mode == 'sign')
        {
            $url = $alipay->_get_alipay_sign_url();
            $this->redirect($url);
        }
        else
        {
            $url = $alipay->_get_alipay_unsign_url();
            $alipay->unsign();
            $this->show_message($this->lang('alipay_unsign_sucess') . '<iframe style="display:none;" src="' . $url['host'] . $url['pars'] . '"></iframe>');
        }
    }

    /**
     *  如果不支持当前商城的货币，则自动卸载
     *
     *  @author Garbin
     *  @param  string $code
     *  @return bool
     */
    function _drop_if_invalid($pay_code, $mod)
    {
        $mod_info = $this->_mng->get_built_in($pay_code);
        if (empty($mod_info))
        {
            $mod->drop();
            $this->show_warning('record_not_exist');

            return;
        }

        return true;
    }
}

?>