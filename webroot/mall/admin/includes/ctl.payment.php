<?php

/**
 * ECMALL: ֧����ʽ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * �鿴�б�
     */
    function view()
    {
        $this->logger = false; // ������־

        /* ȡ���б� */
        $list = $this->_mng->get_list();

        if($list['data'] && isset($list['data']['tenpay']))
        {
            $arr = array('tenpay' => $list['data']['tenpay']);
            unset($list['data']['tenpay']);
            $list['data'] = array_merge($arr, $list['data']);
        }

        $this->assign('list', $list);

        /* ͳ����Ϣ */
        $this->assign('stats', $this->str_format('payment_stats', $list['info']['rec_count'], $list['info']['installed']));

        /* ��ʾģ�� */
        $this->display('store/payment.view.html');
    }

    /**
     * ajax�༭
     */
    function modify()
    {
        /* ���� */
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

        /* ���� */
        $mod = new Payment($id, $_SESSION['store_id']);
        $mod->update(array($field_name => $value));
        $this->log_item = $id;
        $this->json_result();
        return;
    }

    /**
     * ɾ��
     */
    function drop()
    {
        /* ���� */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $this->log_item = $id;
        $mod = new Payment($id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* ж�� */
        $mod->drop();
        $this->log_item = $id;
        $this->show_message('drop_ok', 'back_payment', 'admin.php?app=payment&act=view');
        return;
    }

    /**
     *  ��װ
     *  @author Garbin
     *  @return void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ���� */
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

            /* ��ʼ�� */
            $this->assign('info', $built_in);
            $this->display('store/payment.detail.html');
        }
        else
        {
            /* ���� */
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

            /* �ύ���� */
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

            /* ���� */
            $pay_id         = $this->_mng->add($info);
            $this->log_item = $pay_id;

            /* ֧��������ʾ��ҪǩԼ */
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
     * �༭
     *  @author Garbin
     *  return void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ���� */
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
            /* ���� */
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

            /* �ύ���� */
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

            /* ���� */
            $mod->update(addslashes_deep($info));
            $this->log_item = $id;
            /* ֧��������ʾ��ҪǩԼ */
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
     *  ֧�����ͻ�ǩԼ
     *
     *  @author Garbin
     *  @return void
     */
    function alipay_sign()
    {
        $mode = $_GET['do'];
        include_once(ROOT_PATH . '/includes/manager/mng.payment.php');
        include_once(ROOT_PATH . '/includes/payment/alipay.php');

        /* �ж��Ƿ����� */
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
     *  �����֧�ֵ�ǰ�̳ǵĻ��ң����Զ�ж��
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