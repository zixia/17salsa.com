<?php

/**
 * ECMall: : ����ȯ�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: coupon.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.coupon.php');
require(ROOT_PATH . '/includes/models/mod.coupon.php');

class CouponController extends ControllerBackend
{
    var $manager = null;

    function __construct($act)
    {
        $this->CouponController($act);
    }

    function CouponController($act)
    {
        $this->manager = new CouponManager($_SESSION['store_id']);
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �鿴����ȯ�б�
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false; //����¼��־
        $condition = array('start_time' => gmstr2time('today'),
                            'end_time'  => gmstr2time('today'));

        $list = $this->manager->get_list($this->get_page());

        deep_local_date($list['data'], 'start_time', 'Y-m-d');
        deep_local_date($list['data'], 'end_time', 'Y-m-d');

        $this->assign('list',   $list);
        $this->assign('stats',  $this->str_format('coupon_stats',
            $this->manager->get_count(), $this->manager->get_count($condition)));
        $this->display('coupon.view.html', 'store');
    }

    /**
     * ��Ӵ���ȯ
     *
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //����¼��־
            $coupon['min_amount']   = 0;
            $coupon['max_times']    = 1;
            $coupon['start_time']   = local_date("Y-m-d");

            $this->assign('coupon', $coupon);
            $this->display('coupon.detail.html', 'store');
        }
        else
        {
            $post = array();
            $post['coupon_name']    = sub_str(trim($_POST['coupon_name']), 120);
            $post['coupon_value']   = floatval($_POST['coupon_value']);
            $post['max_times']      = intval($_POST['max_times']);
            $post['start_time']     = gmstr2time($_POST['start_time']);
            $post['end_time']       = empty($_POST['end_time']) ? 0 : gmstr2time($_POST['end_time']);
            $post['min_amount']     = floatval($_POST['min_amount']);

            if (!$this->_check_coupon_time($post['start_time'], $post['end_time']))
            {
                $this->show_warning('invalid_coupon_time');
                return;
            }

            if ($this->manager->add($post))
            {
                $this->log_item = $GLOBALS['db']->insert_id();
                $this->show_message('add_coupon_successfully',
                    'back_list', 'admin.php?app=coupon&amp;act=view',
                   'add_continue', 'admin.php?app=coupon&amp;act=add');
                return;
            }
            else
            {
                $this->show_warning('add_coupon_failed');
                return;
            }
        }
    }

    /**
     * ͨ��ajax��ʽ�༭����ȯ��Ϣ
     *
     * @return  void
     */
    function modify()
    {
        if ($_GET['column'] == 'start_time' || $_GET['column'] == 'end_time')
        {
            $_GET['value'] = local_strtotime($_GET['value']);
        }
        if (!empty($_GET['id'])) $this->log_item = $_GET['id'];
        $this->_modify('Coupon', $_GET, 'edit_coupon_failed');
    }

    /**
     * ���Ŵ���ȯ
     *
     * @author  weberliu
     */
    function sent()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //����¼��־
            $this->display('coupon.sent.html', 'store');
        }
        else
        {
            include_once(ROOT_PATH. '/includes/manager/mng.user.php');

            $users = $this->_send_by_username(str_replace("\r", '', str_replace("\n", ' ', trim($_POST['specified_users']))));
            $model = intval($_POST['send_model']);

            if (!empty($users))
            {
                $mod    = new Coupon(intval($_POST['coupon_id']), $_SESSION['store_id']);
                $coupon = $mod->get_info();
                $users  = $this->_assign_to_user($mod, $users);

                if ($model < 2)
                {
                    $this->_pm_to_user($users, $coupon);
                }

                if ($model != 1)
                {
                    $this->_mail_to_user($users, $coupon);
                }
                $this->show_message('send_coupon_successful');
                return;
            }
            else
            {
                $this->show_warning('not_found_user');
                return;
            }
        }
    }

    /**
     * ��������ȯ����
     *
     * @return  void
     */
    function export()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->display('coupon.export.html', 'store');
        }
        else
        {
            $num = intval($_POST['export_num']);
            $idx = intval($_POST['coupon_id']);
            $coupon = new Coupon($idx);
            $info = $coupon->get_info();

            header('Content-type: application/txt');
            header('Content-Disposition: attachment; filename="coupon_' .date('Ymd'). '_' .$info['coupon_name'].'.txt"');

            $this->log_item = $idx;

            $arr = $coupon->generate($num);

            $crlf = get_crlf();
            foreach ($arr AS $sn)
            {
                echo $sn.$crlf;
            }
        }
    }

    /**
     * ����û��б�
     *
     * @return  void
     */
    function get_userlist()
    {
        $this->logger = false; // ����¼��־

        include_once(ROOT_PATH. '/includes/manager/mng.user.php');
        $manager = new UserManager();
        $username = trim($_GET['q']);

        $arr = $manager->get_users_by_name($username);
        if (!empty($arr))
        {
            foreach ($arr AS $val)
            {
                $row[] = array($val['user_name'], $val['user_name'], $val['user_id']);
            }
            $this->json_result($row);
            return;
        }
        else
        {
            $this->json_error();
            return;
        }
    }

    /**
     * ���û����ַ��Ŵ���ȯ
     *
     * @param   int     $points
     *
     * @return  void
     */
    function _send_by_points($points)
    {
        $manager    = new UserManager($_SESSION['store_id']);
        $users      = $manager->get_list(1, array('user_points' => $points));

        return ($users ? $users['data'] : array());
    }

    /**
     * ���û������ܶ��
     *
     * @param   int     $amount
     *
     * @return  void
     */
    function _send_by_amount($amount)
    {
        $manager    = new UserManager(0);
        $users      = $manager->get_list(1, array('shopping_amount' => $amount));

        return ($users ? $users['data'] : array());
    }

    /**
     * ��ָ�����û�������
     *
     * @param   string  $names
     *
     * @return  array
     */
    function _send_by_username($names)
    {
        $manager    = new UserManager(0);
        $manager->set_pagesize(30);
        $users      = $manager->get_list(1, array('name_array' => $names));

        return ($users ? $users['data'] : array());
    }

    /**
     * ����������ÿһ���û�
     *
     * @param  object   $coupon_id
     * @param  array    $users
     *
     * @return  array
     */
    function _assign_to_user($coupon, $users)
    {
        $arr        = array();
        $coupons    = $coupon->generate(count($users));

        if (!empty($coupons))
        {
            for ($i=0; $i < count($users); $i++)
            {
                $arr[$i] = array(
                                'user_id'   => $users[$i]['user_id'],
                                'username'  => $users[$i]['user_name'],
                                'email'     => $users[$i]['email'],
                                'coupon'    => $coupons[$i]
                    );
            }
        }

        return $arr;
    }

    /**
     * Send message to user
     *
     * @author  Garbin
     * @param   array   $users
     * @param   array   $coupon
     *
     * @return  void
     */
    function _pm_to_user($users, $coupon)
    {
        $store_name = $this->_store_name();

        foreach ($users AS $val)
        {
            $url = site_url() . "/index.php?app=store&store=".$_SESSION['store_id'];
            $msg = $this->str_format('send_coupon_message', $store_name, $coupon['coupon_value'],
                $val['coupon'], $coupon['max_times'], local_date('Y-m-d', $coupon['start_time']), local_date('Y-m-d', $coupon['end_time']), $coupon['min_amount']);

            uc_call('uc_pm_send', array(0, $val['user_id'], $this->lang('send_coupon_subject'), $msg, 1));
        }
    }

    /**
     * �������������ʼ����Ÿ�ָ�����û�
     *
     * @author  wj
     * @param  array    $users
     * @param  array    $coupon
     *
     * @return  void
     */
    function _mail_to_user($users, $coupon)
    {
        $store_name = $this->_store_name();

        foreach ($users AS $val)
        {
            $values = array();
            $values['user_name'] = $val['username'];
            $values['mall_name'] = $this->conf('mall_name');
            $values['coupon_sn'] = $val['coupon'];
            $values['store_name'] = $store_name;
            $values['store_url'] = site_url() . "/?store_id=".$_SESSION['store_id'];
            $values['coupon_value'] = $coupon['coupon_value'];
            $values['send_date'] = local_date($this->conf('mall_time_format_complete'));
            $values['start_time']= local_date($this->conf('mall_time_format_complete'), $coupon['start_time']);
            $values['end_time']= local_date($this->conf('mall_time_format_complete'), $coupon['end_time']);
            $values['max_times']= $coupon['max_times'];
            $values['min_amount']= $coupon['min_amount'];

            $this->send_mail($val['email'], 'send_coupon', $values);
        }

        //todo: ���������ʼ��Ķ���
    }

    /**
     * ������ȯ��ʱ���Ƿ���Ч
     *
     * @param   integer     $start      ��ʼʱ��
     * @param   integer     $end        ����ʱ��
     *
     * @return  boolean
     */
    function _check_coupon_time($start, $end)
    {
        return ($end ===0 || ($start < $end));
    }


    /**
     * ��õ��̵�����
     *
     * @return  void
     */
    function _store_name()
    {
        include_once(ROOT_PATH. '/includes/models/mod.store.php');
        $mod    = new Store($_SESSION['store_id']);
        $store  = $mod->get_info();

        if ($store)
        {
            return $store['store_name'];
        }
        else
        {
            return false;
        }
    }

};
?>
