<?php
/**
 * ECMall 在线支付结果响应处理页面
 * ============================================================================
 * 版权所有 (C) 2005-2007 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
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
     *  接收从支付网站返回的支付结果信息
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