<?php
/**
 * ECMall ����֧�������Ӧ����ҳ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2007 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: respond.php 4774 2008-06-23 08:31:38Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.payment.php');

class AlipayController extends ControllerFrontend
{
    var $_allowed_actions = array('respond');

    function __construct($act)
    {
        $this->AlipayController($act);
    }
    function AlipayController($act)
    {
        $act = 'respond';
        parent::__construct($act);
    }

    /**
     *  ���մ�֧����վ���ص�֧�������Ϣ
     *
     *  @author Garbin
     *  @return void
     */

    function respond()
    {
        include(ROOT_PATH . '/includes/payment/alipay.php');
        $_mng = new PaymentManager(0);
        $alipay = new alipay((int)$_GET['pay_id'], (int)$_GET['store_id']);
        if($alipay->respond())
        {
            $this->show_message('alipay_sign_sucess', 'store_entrance', 'admin.php');
        }
        else
        {
            $this->show_warning('alipay_sign_faild');
        }
    }

}
?>