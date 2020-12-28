<?php

/**
 * ECMALL: 用户中心
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: member.php 6117 2008-12-05 06:23:50Z Garbin $
 */

require_once(ROOT_PATH . '/includes/models/mod.user.php');

class MemberController extends ControllerFrontend
{
    var $user = null;
    var $_allowed_actions = array('view', 'login', 'register', 'logout', 'home', 'profile', 'order_view',
        'cancel_order', 'address', 'order_detail', 'favorite', 'add_favorite', 'message', 'credit',
        'add_friend', 'active_admin', 'getpwd', 'repwd', 'check_user', 'check_email', 'check_new_pm', 'wanted_view');

    function __construct($act)
    {
        $this->MemberController($act);
    }

    /**
     * 控制器构造函数
     *
     * @author   wj
     * @param    $act        执行操作
     * @return   void
     */
    function MemberController($act)
    {
        if (empty($act)) $act = 'home';
        $not_login_act = array('login', 'register', 'logout', 'credit', 'getpwd', 'repwd', 'check_user', 'check_email'); //不需要登录的act

        if (empty($_SESSION['user_id']) && (!in_array($act, $not_login_act)))
        {
            $ret_url = '?' . $_SERVER['QUERY_STRING'];
            //跳转到登录页面
            if (IS_AJAX == 1)
            {
                //如果是ajax请求，直接提示错误
                $this->json_error('NO_LOGIN', 'index.php?app=member&act=login&ret_url='. urlencode($ret_url));

                return;
            }
            else
            {
                $this->redirect('index.php?app=member&act=login&ret_url='. urlencode($ret_url));
            }
        }
        if ($_SESSION['user_id'] && in_array($act, array('login', 'register')))
        {
            $this->show_warning('you_have_login', 'logout_now', 'index.php?app=member&amp;act=logout', 'go_to_index', 'index.php');
            return;
        }
        $this->user = new User($_SESSION['user_id'], 0);
        parent::__construct($act);
    }

    /**
     * 用户登录入口
     *
     * @author  weberliu
     * @return  void
     */
    function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            include_once(ROOT_PATH . '/includes/models/mod.order.php');
            Language::load_lang(lang_file('shopping')); // 用户信息
            $ecm_username = ecm_getcookie('ECM_USERNAME');
            $this->assign('ret_url', $this->get_ret_url());
            $this->assign('login_captcha', $this->login_captcha()); //验证码
            $this->assign('user_name', $ecm_username ? $ecm_username : '');
            $from = 0;

            if ($_GET['from'] == 'shopping')
            {
                $this->assign('allow_anonymous_buy', $this->conf('mall_allow_guest_buy'));
                $from = 1;
            }
            $this->assign('from', $from);
            $this->display('mc_login', 'mall');
        }
        else
        {
            if ($_SESSION['user_id'])
            {
                $this->show_warning('you_have_login', 'logout_now', 'index.php?app=member&amp;act=logout', 'go_to_index', 'index.php');
                return;
            }
            $user_name  = trim($_POST['user_name']);
            $password   = $_POST['password']; //密码允许使用空格
            $captcha    = empty($_POST['captcha']) ? '' : trim($_POST['captcha']); //验证码
            $cookie_expire = intval($_POST['expire']);

            /* 验证码 */
            if (!$this->login_captcha($captcha))
            {
                $this->show_warning('captcha_invalid', 'relogin', 'index.php?app=member&act=login&ret_url=' . urlencode($this->get_ret_url()));

                return;
            }

            $user = new User(0, 0);
            $row = $user->login($user_name, $password);
            if ($row['uid'] > 0)
            {
                unset($_SESSION['ERROR_LOGIN']); //清除登录失败错误次数
                ecm_setcookie('ECM_USERNAME', $row['username']); //记录登录用户名
                $user_synlogin = $user->sync_login();

                $_SESSION['timezone'] = $this->conf('mall_time_zone');

                $login_sucess_message = $this->str_format('welcome_to_login', $_SESSION['user_name'], $this->conf('mall_title'));
                $this->show_message($login_sucess_message . $user_synlogin, 'auto_link', $this->get_ret_url());
                return;
            }
            else
            {
                //记录登录失败次数
                if (isset($_SESSION['ERROR_LOGIN']))
                {
                    $_SESSION['ERROR_LOGIN'] ++;
                }
                else
                {
                    $_SESSION['ERROR_LOGIN'] = 1;
                }
                if ($row['uid'] == -1)
                {
                    $msg_error_login = 'login_user_no_exists';
                }
                else
                {
                    $msg_error_login = 'login_pass_error';
                }
                $this->show_warning ($msg_error_login, 'relogin', 'index.php?app=member&act=login&ret_url=' . urlencode($this->get_ret_url()), 'forget_pwd', 'index.php?app=member&act=getpwd');
                return;
            }
        }
    }

    /**
     *  新用户注册
     *
     *  @author wj
     *  @return void
     */
    function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            include_once(ROOT_PATH . '/includes/models/mod.order.php');
            Language::load_lang(lang_file('shopping')); // 用户信息

            /* 判断需不需要验证码 */
            $is_need_captcha = $this->conf('mall_captcha_status');
            $is_need_captcha &= 2;

            $this->assign('register_captcha', $is_need_captcha);
            $this->assign('ret_url', $this->get_ret_url());

            $this->display('mc_register', 'mall');
        }
        else
        {
            $user_name = empty($_POST['user_name']) ? '' : trim($_POST['user_name']);
            $password = empty($_POST['password']) ? '' : $_POST['password']; //password 允许为空
            $repeat_password = empty($_POST['repeat_password']) ? '' : $_POST['repeat_password'];
            $email = empty($_POST['email']) ? '' : trim($_POST['email']);
            $captcha = empty($_POST['captcha']) ? '' : trim($_POST['captcha']);

            /* 定义错误类型 */
            $err_msg = array();
            //uc error_msg
            $err_msg[-1] = 'username_invalid';
            $err_msg[-2] = 'username_forbid';
            $err_msg[-3] = 'username_exist';
            $err_msg[-4] = 'email_invalid';
            $err_msg[-5] = 'email_forbid';
            $err_msg[-6] = 'email_exist';

            //ec error_msg
            $err_msg[1] = 'password_empty';
            $err_msg[2] = 'captcha_invalid';
            $err_msg[3] = 'repeat_pass_invalid';

            $error_no = 0;
            if (empty($user_name)) $error_no = -1;
            if (empty($password)) $error_no = 1;
            if ($password != $repeat_password) $error_no = 3;
            if (empty($email)) $error_no = -4;

            /* 判断需不需要验证码 */
            $is_need_captcha = $this->conf('mall_captcha_status');
            $is_need_captcha &= 2;
            if ($is_need_captcha > 0 && $_SESSION['captcha'] != base64_encode(strtolower($captcha)))
            {
                $error_no = 2;
            }

            if ($error_no != 0)
            {
                $this->show_warning($err_msg[$error_no], 'go_back', 'javascript:history.go(-1);', 'back_to_ret_url', $this->get_ret_url());

                return;
            }

            $user = new User(0, 0);
            $uid = $user->add($user_name, $password, $email);
            if ($uid > 0)
            {
                $user_synlogin = $user->sync_login($uid);   // 获得同步登录代码

                $_SESSION['timezone'] = $this->conf('mall_time_zone');
                if ($_POST['ret_url'])
                {
                    $ret_url = urldecode($this->get_ret_url());
                    $this->show_message($this->lang('register_ok') . $user_synlogin, 'go_back', $ret_url, 'back_home', 'index.php');
                }
                else
                {
                    $this->show_message($this->lang('register_ok') . $user_synlogin , 'back_home', 'index.php');
                }
                return;
            }
            else
            {
                $this->show_message(isset($err_msg[$uid]) ? $err_msg[$uid] : 'register_error', 're_register', 'index.php?app=member&act=register&ret_url=' . urlencode($this->get_ret_url()));
                return;
            }
        }
    }

    function logout()
    {
        $syn_logout_code = '';

        /* 退出 */
        if (!empty($_SESSION['user_id']))
        {
            $user               = new User(0, 0);
            $syn_logout_code    = $user->sync_logout($_SESSION['user_id']);
            $user->logout();
        }

        $this->show_message($this->lang('logout_ok') . $syn_logout_code, 'auto_link', $this->get_ret_url() , 'go_to_index', 'index.php' );
    }

    /**
     * 欢迎页面
     *
     * @author  weberliu
     * @return  void
     */
    function home()
    {
        $this->assign('mc_selected', 'welcome');
        $this->assign('seller_rate', $this->user->get_eval_rate('seller'));
        $this->assign('buyer_rate', $this->user->get_eval_rate('buyer'));
        $this->assign('user_info', $this->user->get_info($this->conf('mall_value_of_heart'), $this->conf('mall_time_format_complete')));

        /* 取得最近几条订单信息 */
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $mng_order = new OrderManager();
        $res_order = $mng_order->get_list(1, array('user_id' => $_SESSION['user_id']), NULL, NULL, 3);

        /* 取得最近的几条求购信息 */
        $this->_assign_recent_wanted();

        $this->assign('recent_order', $res_order['data']);

        /* 取得最新留言 */
        include_once(ROOT_PATH . '/includes/manager/mng.message.php');
        $msg_mng = new MessageManager(0);
        $msg_list = $msg_mng->get_list(1, array('buyer_id' => $_SESSION['user_id']), 3);
        foreach ($msg_list['data'] as $key => $msg)
        {
            if ($msg['goods_id'] > 0)
            {
                include_once(ROOT_PATH . '/includes/models/mod.goods.php');
                $mod  = new Goods($msg['goods_id']);
                $info = $mod->get_info();
                $msg_list['data'][$key]['msg_for'] = $this->str_format('message_for_goods', $info['goods_name']);
            }
            else
            {
                include_once(ROOT_PATH . '/includes/models/mod.store.php');
                $mod  = new Store($msg['seller_id']);
                $info = $mod->get_info();
                $msg_list['data'][$key]['msg_for'] = $this->str_format('message_for_store', $info['store_name']);
            }
        }
        $this->assign('msg_list', $msg_list);

        /* 取得最后收藏的几个商品 */
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager();
        $favorite_goods = $this->user->get_favorite();
        $favorite_list = $mng_goods->get_list_by_ids(join(',', $favorite_goods));
        $this->assign('favorite_list', $favorite_list);

        $this->assign('avatar_set_html', uc_call('uc_avatar', array($_SESSION['user_id'])));
        $this->display('mc_home', 'mall');
    }

    /**
     * 个人帐户信息
     *
     * @author  wj
     * @return  void
     */
    function profile()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $user_info = $this->user->get_info();

            if(empty($_POST['new_info']))
            {
                $_POST['new_info'] = array();
            }
            //如果$_POST['new_info']存在，必须为数组，不然后面用户可恶意构造字符串修改其他信息 (add by wj)
            if (!is_array($_POST['new_info']))
            {
                $this->show_warning('POST DATA IS INVALID');
                return;
            }

            if ($_POST['base_info_submit'])
            {
                if ($this->user->update($this->_post_handle($_POST['new_info'])))
                {
                    $this->show_message('update_base_info_success', 'back_mc_profile', 'index.php?app=member&act=profile', 'back_mc_home', 'index.php?app=member&act=home');
                    return true;
                }
                else
                {
                    $this->show_warning($this->user->err);
                    return false;
                }
            }
            elseif ($_POST['password_submit'])
            {
                $orig_pass = trim($_POST['new_info']['orig_pass']);
                if ($new_pass = trim($_POST['new_info']['new_pass']))
                {
                    $confirm_pass = trim($_POST['new_info']['confirm_pass']);
                    if ($new_pass != $confirm_pass)
                    {
                        $this->show_warning('pass_no_match');
                        return false;
                    }
                }
                else
                {
                    $this->show_warning('password_invalid');
                    return;
                }
                $ucresult = uc_call('uc_user_edit', array($user_info['user_name'], $orig_pass, $new_pass, ''));
                if ($ucresult == -1)
                {
                    $this->show_warning('orig_pass_wrong');
                    return false;
                }
                elseif($ucresult == -4)
                {
                    $this->show_warning('email_format_wrong');
                    return false;
                }
                elseif($ucresult == -5)
                {
                    $this->show_warning('email_not_allow');
                    return false;
                }
                elseif($ucresult == -6)
                {
                    $this->show_warning('email_has_used');
                    return false;
                }
                else
                {
                    $this->show_message('update_pass_success', 'back_mc_profile', 'index.php?app=member&act=profile', 'back_mc_home', 'index.php?app=member&act=home');
                    return true;
                }
            }
            elseif ($_POST['email_submit'])
            {
                $login_pass = trim($_POST['new_info']['login_pass']);
                if (empty($login_pass))
                {
                    $this->show_warning('update_email_need_orig_pass');
                    return;
                }
                $ucresult = uc_call('uc_user_edit', array($user_info['user_name'], $login_pass, '', $_POST['new_info']['email']));
                if ($ucresult == -1)
                {
                    $this->show_warning('orig_pass_wrong');
                    return false;
                }
                elseif($ucresult == -4)
                {
                    $this->show_warning('email_format_wrong');
                    return false;
                }
                elseif($ucresult == -5)
                {
                    $this->show_warning('email_not_allow');
                    return false;
                }
                elseif($ucresult == -6)
                {
                    $this->show_warning('email_has_used');
                    return false;
                }
                else
                {
                    $this->user->update(array('email' => $_POST['new_info']['email']));
                    $this->show_message('update_email_success', 'back_mc_profile', 'index.php?app=member&act=profile', 'back_mc_home', 'index.php?app=member&act=home');
                    return true;
                }

            }
            elseif ($_POST['feed_submit'])
            {
                if ($_POST['feed_submit'])
                {
                    $new_info['default_feed'] = bindec(intval($_POST['new_info']['seed_feed']['favorite']) .
                                                            intval($_POST['new_info']['seed_feed']['message']) .
                                                            intval($_POST['new_info']['seed_feed']['friend']) .
                                                            intval($_POST['new_info']['seed_feed']['shopping']));
                }

                if ($this->user->update($new_info))
                {
                    $_SESSION['feed'] = $new_info['default_feed'];
                    $this->show_message('update_feed_default_success', 'back_mc_profile', 'index.php?app=member&act=profile', 'back_mc_home', 'index.php?app=member&act=home');
                    return true;
                }
                else
                {
                    $this->show_warning($this->user->err);
                    return false;
                }
            }
        }
        else
        {
            /* 判断是否有UCHOME */
            $has_uchome = has_uchome();

            $user_info = $this->user->get_info();
            $this->assign('mc_selected', 'profile');
            $this->assign('gender_options', array(0 => $this->lang('secret'), 1 => $this->lang('male'), 2 => $this->lang('female')));
            $this->assign('user_info', $user_info);

            $this->assign('feed_status', sprintf('%04b', $user_info['default_feed']));
            $this->assign('has_uchome', $has_uchome);
            $this->assign('avatar_set_html', uc_call('uc_avatar', array($_SESSION['user_id'])));

            $this->display('mc_profile', 'mall');
        }
    }

    /**
     * 订单
     *
     * @author  scottye
     * @return  void
     */
    function order_view()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        Language::load_lang(lang_file('shopping')); // 载入订单页面所需要的语言项目

        /* 获取订单信息 */
        $mng = new OrderManager(0);
        $condition = array('user_id' => $_SESSION['user_id']);
        $os_actived = 'all';
        if (isset($_GET['order_status']))
        {
            switch ($_GET['order_status'])
            {
            case ORDER_STATUS_PENDING:
                $_GET['os'] = 'pending';
                break;
            case ORDER_STATUS_SHIPPED:
                $_GET['os'] = 'shipped';
                break;
            case ORDER_STATUS_DELIVERED:
                $_GET['os'] = 'delivered';
                break;
            }
        }
        if (isset($_GET['os']))
        {
            if ($_GET['os'] == 'pending')
            {
                $condition['order_status'] = ORDER_STATUS_PENDING;
                $os_actived = 'pending';
            }
            elseif ($_GET['os'] == 'shipped')
            {
                $condition['order_status'] = ORDER_STATUS_SHIPPED;
                $os_actived = 'shipped';
            }
            elseif ($_GET['os'] == 'delivered')
            {
                $condition['order_status'] = ORDER_STATUS_DELIVERED;
                $os_actived = 'delivered';
            }
        }
        $order_list = $mng->get_list($this->get_page(), $condition);
        $open_store = $mng->get_open_store();
        foreach ($order_list['data'] as $key => $order)
        {
            if (!in_array($order['store_id'], $open_store))
            {
                $order_list['data'][$key]['store_is_closed'] = 1;
            }
        }

        $this->assign('os_actived', $os_actived);
        $this->assign('mc_selected', 'order_view');
        $this->assign('order_list', $order_list);

        $this->display('mc_order_view', 'mall');
    }


    /**
     *   查看求购列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function wanted_view()
    {
        $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $manager = new WantedManager();
        $list = $manager->get_list($page, array('user_id' => $_SESSION['user_id']));

        $this->assign('mc_selected', 'wanted_view');
        $this->assign('url_format', 'index.php?app=member&amp;act=wanted_view');
        $this->assign('list', $list);
        $this->display('mc_wanted_view', 'mall');
    }

    /**
     *  取消订单
     *
     *  @param  none
     *  @return void
     */
    function cancel_order()
    {
        /* 初始化变量 */
        $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
        if (!$order_id)
        {
            $this->show_warning('invalid_order_id');

            return;
        }

        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        $order  =   new Order($order_id);
        $order_info = $order->get_info();
        if ($order_info['user_id'] != $_SESSION['user_id'])
        {
            $this->show_warning('not_your_order');

            return;
        }
        if (!$order->cancel())
        {
            $this->show_warning($order->err);

            return;
        }

        /* 记录订单日志 */
        include_once(ROOT_PATH . '/includes/manager/mng.order_logs.php');
        $order_logger = new OrderLogger($order_info['store_id']);
        $order_logger->write(array('action_user' => $_SESSION['user_name'],
                                   'order_id'    => $order_info['order_id'],
                                   'order_status'=> ORDER_STATUS_REJECTED,
                                   'action_note' => $this->lang('buyer_cancelled'),
                                   'action_time' => gmtime()));

        $this->show_message('cancel_order_sucessfully');
        return;
    }

    /**
     * 收货地址
     *
     * @author  scottye
     * @return  void
     */
    function address()
    {
        /* 参数 */
        $op = empty($_REQUEST['op']) ? '' : trim($_REQUEST['op']);
        if (!$op)
        {

            $best_time_options[$this->lang('best_time_all')]= $this->lang('best_time_all');
            $best_time_options[$this->lang('best_time_workday')]= $this->lang('best_time_workday');
            $best_time_options[$this->lang('best_time_offday')]= $this->lang('best_time_offday');

            /* 取得收获地址 */
            $address_list = $this->user->get_address_list();
            $this->assign('best_time_options', $best_time_options);
            $this->assign('address_list', $address_list);
            $this->assign('address_total', count($address_list));
            $this->assign('mall_max_address_num', $this->conf('mall_max_address_num'));
            $this->assign('mc_selected', 'address');
            $this->assign('address_data', 'var addressData = ' . ecm_json_encode($address_list) . ';');
            $this->display('mc_address', 'mall');

        }
        elseif ($op == 'drop')
        {
            $address_id = empty($_GET['address_id']) ? 0 : intval($_GET['address_id']);
            $this->user->drop_address($address_id);
            $this->json_result('drop_address_success');
            return;
        }
        elseif ($op == 'add')
        {
            $address_count = $this->user->get_address_count();
            if ($address_count >= $this->conf('mall_max_address_num'))
            {
                $this->show_warning('add_address_faild');

                return;
            }
            if ($this->_check_address($_POST))
            {
                $this->user->add_address($_POST);
                $this->show_message('add_new_address_success', 'go_on_manage_address', 'index.php?app=member&amp;act=address');
                return;
            }
        }
        elseif ($op == 'edit')
        {
            if ($this->_check_address($_POST))
            {
                $this->user->update_address(intval($_POST['address_id']), $_POST);
                $this->show_message('edit_address_success', 'go_on_manage_address', 'index.php?app=member&amp;act=address');
                return;
            }
        }
    }

    function _check_address($address)
    {
        if (!$address['consignee'])
        {
            $this->show_warning('consignee_required');
            return FALSE;
        }
        if (!$address['region_id'])
        {
            $this->show_warning('region_id_required');
            return FALSE;
        }
        if (!$address['address'])
        {
            $this->show_warning('address_required');
            return FALSE;
        }
        if (!$address['zipcode'])
        {
            $this->show_warning('zipcode_required');
            return FALSE;
        }
        if (!$address['email'] || !is_email($address['email']))
        {
            $this->show_warning('email_empty_or_illegal');
            return FALSE;
        }
        if (!$address['mobile_phone'] && !$address['home_phone'] && !$address['office_phone'])
        {
            $this->show_warning('phone_number_required');
            return FALSE;
        }
        if (intval($_POST['address_id']) == 0)
        {
            $address_count = $this->user->get_address_count();
            if ($address_count >= $this->conf('mall_max_address_num'))
            {
                $this->show_warning('address_is_allow_limit');
                return false;
            }
        }
        return true;
    }

    /**
     * 订单详情
     *
     * @author  Garbin
     * @return  void
     */
    function order_detail()
    {
        include_once(ROOT_PATH . '/includes/models/mod.order.php');
        Language::load_lang(lang_file('shopping')); // 载入订单页面所需要的语言项目

        $order_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($order_id <= 0) {
            $this->show_warning('invalid_order_id');

            return;
        }

        $mod = new Order($order_id, 0);
        $this->assign('mc_selected', 'order_view');
        $order = $mod->get_info_with_payment();
        if ($order['user_id'] != $_SESSION['user_id'])
        {
            $this->show_warning('not_your_order');

            return;
        }
        $order['pay_desc'] = str_replace("\n", "<br />", str_replace("\r", "", $order['pay_desc']));
        $this->assign('order', $order);
        $this->assign('goods', $mod->list_goods());

        /* 检查店铺是否关闭 */
        include_once(ROOT_PATH . '/includes/manager/mng.base.php');
        $mng = new Manager();
        $ids = $mng->get_open_store(array($order['store_id']));
        $ids = array_diff($ids, array(0));
        if (empty($ids))
        {
            $this->assign('store_closed', 1);
        }

        /* 生成支付代码 */
        /*
        $payment_code = $order['pay_code'];
        include_once(ROOT_PATH . '/includes/payment/' . $payment_code . '.php');
        $payment  = new $payment_code($order['pay_id'], $order['store_id']);
        $payment_form = $payment->get_code($mod);
        $this->assign('payment_form', $payment_form);
        */

        $this->display('mc_order_detail', 'mall');
    }

    /**
     * 收藏
     *
     * @author  scottye
     * @return  void
     */
    function favorite()
    {
        if ($_GET['op'] == 'delete' && $goods_id = intval($_GET['goods_id']))
        {
            if ($this->user->drop_favorite($goods_id))
            {
                $this->show_message('drop_favorite_success');
                return;
            }
            else
            {
                $this->show_warning($this->user->err);
                return;
            }
        }
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager();
        $favorite_goods = $this->user->get_favorite();
        $favorite_list = $mng_goods->get_list_by_ids(implode(',', $favorite_goods));
        $this->assign('mc_selected', 'favorite');
        $this->assign('favorite_list', $favorite_list);

        $this->display('mc_favorite', 'mall');
    }

    /*添加收藏商品*/
    function add_favorite()
    {
        $goods_id = empty($_GET['id']) ? 0 :intval($_GET['id']);

        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        $goods = new Goods($goods_id);
        if (!$goods_info = $goods->get_info())
        {
            $this->show_warning('undefined_action');
            return;
        }

        if ($this->user->add_favorite($goods_id))
        {
            $user_info = $this->user->get_info();
            $feed_status = sprintf('%04b', $user_info['default_feed']);
            if ($feed_status{0})
            {
                /* send feed to uc */
                $goods_url = site_url() . '/index.php?app=goods&id=' . $goods_id;
                $store_url = site_url() . '/index.php?app=store&store_id=' . $goods_info['store_id'];
                $feed_info['icon']              =   'goods';
                $feed_info['user_id']           =   $_SESSION['user_id'];
                $feed_info['user_name']         =   $_SESSION['user_name'];
                $feed_info['title']['template'] =   $this->lang('feed_add_favorite_title');
                $feed_info['title']['data']     =   array('store' => '<a href="' . $store_url . '" target="_blank">' . $this->conf('store_name', $goods_info['store_id']) . '</a>',
                                                          'goods' => '<a href="' . $goods_url . '" target="_blank">' . $goods_info['goods_name'] . '</a>');
                add_feed($feed_info);
            }

            $this->show_message('add_favorite_ok', 'back_to_ret_url', $this->get_ret_url());

            return true;
        }
        else
        {
            $this->show_warning($this->user->err, 'back_to_ret_url', $this->get_ret_url());

            return false;
        }
    }

    /* 判断是否要登录验证码和验证是否合法 */
    function login_captcha($captcha = null)
    {
        $is_need_captcha = $this->conf('mall_captcha_status'); //获取配置
        $is_need_captcha &= 1;

        if (isset($captcha))
        {
            //验证验证码
            if ($is_need_captcha == 0) return true; //不需要验证

            $error_login = $this->conf('mall_captcha_error_login');
            if ($error_login > 0 && (empty($_SESSION['ERROR_LOGIN']) || $_SESSION['ERROR_LOGIN'] < $error_login)) return true; //没达到验证条件
            if (empty($captcha)) return false; //验证码不能为空
            if ($_SESSION['captcha'] == base64_encode(strtolower($captcha)))
            {
                return true; //验证成功
            }
            else
            {
                return false;
            }
        }
        else
        {
            //判断是否需要验证码
            if ($is_need_captcha == 0) return false; //不需要验证
            $error_login = $this->conf('mall_captcha_error_login');
            if ($error_login > 0 && (empty($_SESSION['ERROR_LOGIN']) || $_SESSION['ERROR_LOGIN'] < $error_login))
            {
                return false; //没满足条件，不显示验证码
            }
            else
            {
                return true;
            }
        }
    }

    /**
     * 留言评论
     *
     * @author  scottye
     * @return  void
     */
    function message()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.message.php');
        $msg_mng = new MessageManager(0);
        $cond = array('buyer_id' => $this->user->_id);
        $msg_list = $msg_mng->get_list($this->get_page(), $cond);
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        foreach ($msg_list['data'] as $key => $msg)
        {
            if ($msg['goods_id'] > 0)
            {
                $mod  = new Goods($msg['goods_id']);
                $info = $mod->get_info();
                $msg_list['data'][$key]['msg_for'] = $this->str_format('message_for_goods', $info['goods_name']);
            }
            else
            {
                $mod  = new Store($msg['seller_id']);
                $info = $mod->get_info();
                $msg_list['data'][$key]['msg_for'] = $this->str_format('message_for_store', $info['store_name']);
            }
        }
        $this->assign('mc_selected', 'my_message');
        $this->assign('msg_list', $msg_list);
        $this->assign('msg_title', $this->lang('my_message'));

        $this->display('mc_message', 'mall');
    }

    /* 信用评价 */
    function credit()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? $_SESSION['user_id'] : intval($_GET['id']);
        $mod_user = new User($id);
        $user = $mod_user->get_info();
        if (empty($user))
        {
            die('hacking');
        }
        $user['avatar']     = UC_API . '/avatar.php?uid=' . $id . '&amp;size=small';
        $user['uchome_url'] = uc_home_url($id);
        $this->assign('user', $user);

        $this->assign('credit_title', $this->str_format('credit_title', $user['user_name']));

        /* 取得用户评价 */
        $evaluation = $mod_user->get_evaluation();
        $this->assign('eval', $evaluation);

        /* 取得好评率 */
        $this->assign('seller_rate', $mod_user->get_eval_rate('seller'));
        $this->assign('buyer_rate', $mod_user->get_eval_rate('buyer'));
        $this->assign('username', $user['user_name']);

        $this->display('mc_credit', 'mall');
    }

    /* 添加好友 */
    function add_friend()
    {
        $friend_id = intval($_GET['friend_id']);

        if ($friend_id == $_SESSION['user_id'])
        {
            $this->json_error('add_friend_error');
            return;
        }

        $value = uc_call('uc_friend_add', array($_SESSION['user_id'], $friend_id));

        if ($value == '-1')
        {
            $this->show_warning('friend_exists', $this->lang('close_page'), 'javascript:window.close()', $this->lang('back_home'), 'index.php');
            return;
        }
        else
        {
            $this->show_message('add_friend_succeed', $this->lang('back_to_ret_url'), urldecode($this->get_ret_url()), $this->lang('back_home'), 'index.php');
        }
    }

    /* 激活管理员 */
    function active_admin()
    {
        $user_id    = intval($_GET['user_id']);
        $store_id   = intval($_GET['store_id']);
        $time       = intval($_GET['time']);
        $real_name  = trim($_GET['real_name']);
        $auth_key   = trim($_GET['auth_key']);

        if (empty($user_id) || $time < (time() - 7 * 86400) || empty($real_name) || empty($auth_key)
            || $auth_key != md5($user_id . $store_id . $time . $real_name . ECM_KEY))
        {
            $this->show_warning('url_illegal');
            return false;
        }
        else
        {
            include_once(ROOT_PATH . '/includes/manager/mng.admin.php');
            if (AdminManager::get_by_id($user_id))
            {
                $this->show_warning('you_is_admin', 'close_page', 'javascript:window.close()');
                return;
            }
            $admin_mng = new AdminManager($store_id);
            if ($admin_mng->add($user_id, $real_name))
            {
                $this->show_message('active_admin_success', 'close_page', 'javascript:window.close()');
                return true;
            }
            else
            {
                $this->show_warning($admin_mng->err, 'close_page', 'javascript:window.close()');
                return false;
            }
        }
    }

    /* 取回密码 */
    function getpwd()
    {
        if (empty($_POST['value_submit']))
        {
            $this->display('mc_getpwd', 'mall');
        }
        else
        {
            $_POST['user_name'] = trim($_POST['user_name']);
            $_POST['email'] = trim($_POST['email']);
            if (empty($_POST['user_name']))
            {
                $this->show_warning('user_name_empty');
                return;
            }

            require_once(ROOT_PATH . '/includes/manager/mng.user.php');
            $mng_user = new UserManager();;
            $user_id = $mng_user->get_id_by_name($_POST['user_name']);
            if (empty($user_id))
            {
                $this->show_warning('user_not_exists');
                return;
            }
            $mod_user = new User($user_id, 0);
            $user_info = $mod_user->get_info();
            if ($user_info['email'] != $_POST['email'])
            {
                $this->show_warning('user_name_email_no_match');
                return;
            }

            $cur_time = gmtime();
            $auth_str = md5($user_id . $cur_time . ECM_KEY . $user_info['password']);
            $values = array();
            $values['user_name']    = $user_info['user_name'];
            $values['cur_date']     = local_date($this->conf('mall_time_format_complete'), $cur_time);
            $values['expire_date']  = local_date($this->conf('mall_time_format_complete'), $cur_time + 86400 * 7);
            $values['repwd_url']    = site_url() . '/index.php?app=member&act=repwd&user_id=' . $user_id . '&time=' . $cur_time . '&auth_str=' . $auth_str;

            $mod_user->update(array('repwd_code' => $auth_str));

            if ($this->send_mail($user_info['email'], 'get_pwd', $values))
            {
                $this->show_message('get_pwd_success');
                return;
            }
        }
    }

    /* 重置密码 */
    function repwd()
    {
        /* 合法性判断 */
        $cur_time = gmtime();
        $auth_str = trim($_GET['auth_str']);
        $time = intval($_GET['time']);
        $user_id = intval($_GET['user_id']);
        if (empty($auth_str) || empty($time) || empty($user_id))
        {
            $this->show_warning('url_illegal');
            return;
        }
        if ($cur_time > $time + 86400 * 7)
        {
            $this->show_warning('url_expire');
            return;
        }
        $mod_user = new User($user_id, 0);
        if (!$user_info = $mod_user->get_info())
        {
            $this->show_warning('user_not_exists');
            return;
        }
        if ($auth_str != md5($user_id . $time . ECM_KEY . $user_info['password']))
        {
            $this->show_warning('url_illegal');
            return;
        }
        if (empty($user_info['repwd_code']) || $user_info['repwd_code'] != $auth_str)
        {
            $this->show_message('url_is_used');
            return;
        }

        if (empty($_POST['value_submit']))
        {
            $this->assign('user_info', $user_info);
            $this->display('mc_repwd', 'mall');
        }
        else
        {
            $password = trim($_POST['password']);
            $repeat_password = trim($_POST['repeat_password']);

            if (empty($password))
            {
                $this->show_warning('password_invalid');
                return;
            }
            if ($password != $repeat_password)
            {
                $this->show_warning('pass_no_match');
                return;
            }

            $res = uc_call('uc_user_edit', array($user_info['user_name'], $password, $repeat_password, $member['email'], 1));
        if ($res == 1)
        {
            $mod_user->update(array('repwd_code' => ''));
            $this->show_message('repwd_success', 'back_home', 'index.php');
            return;
          }
          else
          {
              $this->show_warning('repwd_faild');
              return;
          }
        }
    }

    /* 检查用户名是否已经存在 */
    function check_user()
    {
        $username = trim($_GET['username']);
        if (empty($username))
        {
            echo 'Username is empty!';
            return;
        }
        $res = uc_call('uc_user_checkname', array($username));
        if ($res < 0)
        {
            $err_msg[-1] = 'username_invalid';
            $err_msg[-2] = 'username_forbid';
            $err_msg[-3] = 'username_exist';

            $this->json_error($this->lang($err_msg[$res]));
            return;
        }
        else
        {
            $this->json_result($res, 'ok');
            return;
        }
    }

    /* 检查email是否已经被使用 */
    function check_email()
    {
        $email = trim($_GET['email']);
        if (empty($email))
        {
            echo 'Email is empty!';
            return;
        }

        $res = uc_call('uc_user_checkemail', array($email));
        if ($res < 0)
        {
            $err_msg[-4] = 'email_invalid';
            $err_msg[-5] = 'email_forbid';
            $err_msg[-6] = 'email_exist';

            $this->json_error($this->lang($err_msg[$res]));
            return;
        }
        else
        {
            $this->json_result($res, 'ok');
            return;
        }
    }

    /**
     * 获得此操作的前一个页面地址
     *
     * @author      wj
     * @return      stirng
     */
    function get_ret_url()
    {
        static $ret_url = null;
        if ($ret_url === null)
        {
            $ret_url = empty($_REQUEST['ret_url']) ? (empty($_SESSION['user_id']) ? 'index.php?' .  $_SERVER['QUERY_STRING'] : $_SERVER['HTTP_REFERER']) : $_REQUEST['ret_url'];
            $ret_url = (empty($ret_url) || preg_match('/(act=login)|(act=register)|(act=logout)|(act=getpwd)(act=repwd)/', $ret_url)) ? 'index.php' : $ret_url;
        }

        return $ret_url;
    }

    /* 过滤post数据 */
    function _post_handle($post)
    {
        $data = array();
        $data['gender'] = in_array($post['gender'], array(0, 1, 2)) ? $post['gender'] : 0;
        $data['birthday'] = $post['birthday'];
        $data['msn'] = is_email($post['msn']) ? $post['msn'] : '';
        $data['qq'] = intval($post['qq']);
        $data['office_phone'] = trim($post['office_phone']);
        $data['home_phone'] = trim($post['home_phone']);
        $data['mobile_phone'] = trim($post['mobile_phone']);
        return $data;
    }

    /**
     * 检测是否有新PM
     *
     * author liupeng
     * retrun void
     */
    function check_new_pm()
    {
        if (!empty($_SESSION['user_id']))
        {
            $res = uc_call('uc_pm_checknew', array($_SESSION['user_id']));
            if ($res == 1)
            {
                $this->json_result("new_pm", "$res");
            }
            else
            {
                $this->json_result("no_pm", "$res");
            }
        }
        else
        {
            $this->json_error("no_login", "ok");
        }
    }
    /**
     * 查看会员信息
     *
     * @author  scottye
     */
    function view()
    {
        /* 参数 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($id <= 0)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $mod_user = new User($id);
        $user_info = $mod_user->get_info();
        if (empty($user_info))
        {
            $this->show_message('user_not_exist');
            return;
        }
    }

    /**
     *    获取最近的几条求购信息
     *
     *    @author    Garbin
     *    @return    void
     */
    function _assign_recent_wanted()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $manager = new WantedManager();
        $list    = $manager->get_list(1, array('user_id' => $_SESSION['user_id']), 3);
        $this->assign('recent_wanted', $list['data']);
    }
}

?>
