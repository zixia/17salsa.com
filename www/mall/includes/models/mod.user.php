<?php

/**
 * ECMALL: ��Աʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.user.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.userbase.php');

class User extends UserBase
{
    var $_table         = '`ecm_users`';
    var $_key           = 'user_id';

    /**
     * ���캯��
     */
    function __construct($user_id, $store_id=0)
    {
        $this->User($user_id, $store_id);
    }

    function User($user_id, $store_id=0)
    {
        parent::Model($user_id, $store_id);
    }

    /**
     * ȡ���û���Ϣ
     *
     * @author  weberliu
     * @param   int     $value_of_heart ������һ���������
     * @param   string  $timeformat     ʱ���ʽ
     * @return  array
     */
    function get_info($value_of_heart = 0, $timeformat='')
    {
        static $arr = null;

        if (!isset($arr[$this->_id]))
        {
            $info = parent::get_info();
            if ($info)
            {
                if (empty($timeformat)) $timeformat = 'Y-m-d H:i';
                $info['formated_reg_time'] = local_date($timeformat, $info['reg_time']);
                $info['formated_last_login'] = local_date($timeformat, $info['last_login']);

                if ($value_of_heart > 0)
                {
                    /* ���õȼ� */
                    $info['seller_grade'] = $this->score_to_grade($info['seller_credit'], $value_of_heart);
                    $info['buyer_grade']  = $this->score_to_grade($info['buyer_credit'], $value_of_heart);
                }
            }
            $arr[$this->_id] = $info;
        }

        return $arr[$this->_id];
    }

    /**
     * ��uc �ӿڱ���һ��
     *
     * @return  void
     */
    function add($username, $password, $email)
    {
        $uid = uc_call('uc_user_register', array($username, $password, $email));

        if ($uid > 0)
        {
            //������ӵ���վϵͳ
            $cur_time = gmtime();
            $new_user = array('user_id'=>$uid, 'user_name'=>$username, 'email'=>$email, 'default_feed'=>15, 'reg_time' => $cur_time,
                              'last_login'=> $cur_time, 'last_ip'=>real_ip(), 'password'=>$this->generate_password());
            /* ����û��治�� */
            $sql = "SELECT user_id FROM `ecm_users` WHERE user_id = '$uid' LIMIT 1";
            if ($GLOBALS['db']->getOne($sql) > 0)
            {
                $GLOBALS['db']->autoExecute('`ecm_users`', $new_user, 'UPDATE', "user_id ='$uid'");
            }
            else
            {
                $GLOBALS['db']->autoExecute('`ecm_users`', $new_user, 'INSERT');
            }

            /* ����session */
            $_SESSION['user_id']    = $new_user['user_id'];
            $_SESSION['user_name']  = $new_user['user_name'];
            $_SESSION['last_login'] = $new_user['last_login'];
            $_SESSION['last_ip']    = $new_user['last_ip'];
            $_SESSION['admin_store']= -1;
            $_SESSION['feed']       = $new_user['default_feed'];
        }

        return $uid;
    }
    /**
     * �޸ĸ�������
     * @param   array   $user_info
     *
     * @return  boolean true or false
     */
    function update($user_info)
    {
        $arr = '';
        $enable_items = array('email', 'gender', 'birthday', 'msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone', 'password', 'default_feed', 'repwd_code');
        foreach ($user_info as $key => $val)
        {
            if (in_array($key, $enable_items))
            {
                $arr[$key] = $val;
            }
        }
        return parent::update($arr);
    }
    /**
     * �û���¼
     *
     * @author  wj
     * @param   string  $username
     * @param   string  $password
     *
     * @return  array
     */
    function login($username, $password)
    {
        $res = uc_call('uc_user_login', array($username, $password));

        list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email']) = addslashes_deep($res);

        if ($tmp['uid'] > 0)
        {
            $this->set_login($tmp); //���õ�¼״̬
        }

        return $tmp;
    }
    /**
     * ȡ���û�������
     * @param   int     $store_id   ����id
     * @param   bool    $show       �Ƿ���������ʾ�˻���Ϣ
     * @return  int
     */
    function get_points($store_id = 0, $show = FALSE)
    {
        $sql = "SELECT SUM(ua.points) AS points, ua.store_id, s.store_name FROM `ecm_user_account` ua " .
               "LEFT JOIN `ecm_store` s ON ua.store_id=s.store_id " .
               "WHERE ua.user_id = '$this->_id'";
        $sql .= $store_id ? " AND ua.store_id = '" . intval($store_id) . "'" : '';
        $sql .= " GROUP BY ua.store_id";
        if ($store_id)
        {

            return intval($GLOBALS['db']->getOne($sql));
        }
        else
        {
            if ($show)
            {
                return $GLOBALS['db']->getAll($sql);
            }
            $rtn_arr = array();
            $res = $GLOBALS['db']->query($sql);
            while ($row = $GLOBALS['db']->fetchRow($res))
            {
                $rtn_arr[$row['store_id']] = $row['points'];
            }

            return $rtn_arr;
        }
    }

    /**
     * ��ӻ��ֱ䶯��¼
     *
     * @param    int        $store_id    ����id
     * @param    int        $points        ��������������ʾ�ӣ�������ʾ����
     * @param    string    $remark        ˵��
     */
    function add_points($store_id, $points, $remark)
    {
        $arr = array(
            'user_id'    => $this->_id,
            'store_id'    => intval($store_id),
            'points'    => intval($points),
            'remark'    => trim($remark),
            'add_time'    => gmtime()
        );
        $GLOBALS['db']->autoExecute('`ecm_user_account`', $arr, 'INSERT');
    }

    /**
     * ȡ���ջ���ַ
     */
    function get_address_list()
    {
        $sql = "SELECT *,r1.region_name as region1_name,r2.region_name as region2_name " .
                "FROM `ecm_user_address` ua " .
                "LEFT JOIN `ecm_regions` r1 ON ua.region1=r1.region_id " .
                "LEFT JOIN `ecm_regions` r2 ON ua.region2=r2.region_id " .
                "WHERE user_id = '" . $this->_id . "'";
        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * �����ջ���ַ
     *
     * @return boolean
     */
    function update_address($address_id, $data)
    {
        extract($data);
        $sql = "UPDATE `ecm_user_address` SET consignee='$consignee', email='$email', region1='$region1', region2='$region2', address='$address', zipcode='$zipcode', office_phone='$office_phone', home_phone='$home_phone', ".
                "mobile_phone='$mobile_phone', sign_building='$sign_building', best_time='$best_time' " .
                "WHERE user_id='" . $this->_id . "' AND address_id='" . $address_id . "'";
        return $GLOBALS['db']->query($sql);
    }

    /**
     * ɾ��ĳ���ջ���ַ
     *
     * @return void
     */
    function drop_address($address_id)
    {
        $sql = "DELETE FROM `ecm_user_address` WHERE user_id='" . $this->_id . "' AND address_id='" . $address_id . "'";
        return $GLOBALS['db']->query($sql);
    }

    /**
     * ����ջ���ַ
     *
     * @return boolean
     */
    function add_address($data)
    {
        extract($data);
        $sql = "INSERT INTO `ecm_user_address` " .
               "(user_id, consignee, email, region1, region2, address, zipcode, office_phone, home_phone, mobile_phone, sign_building, best_time) " .
               "VALUES ('" . $this->_id . "', '$consignee', '$email', '$region1', '$region2', '$address', '$zipcode', '$office_phone', '$home_phone', '$mobile_phone', '$sign_building', '$best_time')";
        return $GLOBALS['db']->query($sql);
    }

    /**
     * ���Ŀǰ�û�������ջ��ַ��Ŀ
     * @return int
     */
    function get_address_count()
    {
        $sql = "SELECT COUNT(*) FROM `ecm_user_address` WHERE user_id='" . $this->_id . "'";
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ��һ����Ʒ��ӵ��û��ղؼ�
     * @param   int     $goods_id   ����id
     *
     * @return  bool
     */
    function add_favorite($goods_id)
    {
        $sql = "SELECT COUNT(*) FROM `ecm_collect_goods` WHERE user_id ='{$this->_id}' AND goods_id = '$goods_id'";

        if ($GLOBALS['db']->getOne($sql) > 0)
        {
            $this->err = 'goods_already_add';

            return false;
        }
        else
        {
            $data = array('user_id'=>$this->_id, 'goods_id'=>$goods_id, 'add_time'=>gmtime());
            $GLOBALS['db']->autoExecute('`ecm_collect_goods`', $data, 'INSERT');

            return true;
        }

    }

    /**
     * ɾ���ղؼ��е�ĳ������
     * @param int goods_id
     *
     * @return boolean
     */
    function drop_favorite($goods_id)
    {
        $sql = "DELETE FROM `ecm_collect_goods` WHERE goods_id='" . $goods_id . "' AND user_id='" . $this->_id . "'";
        return $GLOBALS['db']->query($sql);
    }

    /**
     * ����û��ղ���Ʒ��id
     *
     *
     *  @return array goods_id
     */
    function get_favorite()
    {
        $goods = array();
        $sql = "SELECT goods_id FROM `ecm_collect_goods` WHERE user_id ='{$this->_id}'";
        $query = $GLOBALS['db']->query($sql);
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            $goods[] = $res['goods_id'];
        }
        return $goods;
    }

    /**
     * ȡ������
     *
     * @return     array
     */
    function get_evaluation()
    {
        $list = array();

        // ��������
        $sql = "SELECT 'seller' AS type, seller_evaluation AS eval, add_time " .
                "FROM `ecm_order_info` " .
                "WHERE store_id = '" . $this->_id . "' " .
                "AND seller_evaluation > 0 " .
                "AND seller_evaluation_invalid = 0 ";
        $list = $GLOBALS['db']->getAll($sql);

        // �������
        $sql = "SELECT 'buyer' AS type, buyer_evaluation AS eval, add_time " .
                "FROM `ecm_order_info` " .
                "WHERE user_id = '" . $this->_id . "' " .
                "AND buyer_evaluation > 0 " .
                'AND buyer_evaluation_invalid = 0 ';
        $list = array_merge($list, $GLOBALS['db']->getAll($sql));

        // ��ʱ������۷ֿ�
        $result         = array();
        $week_ago       = gmtime() - 7 * 86400;
        $month_ago      = gmtime() - 30 * 86400;
        $half_year_ago  = gmtime() - 180 * 86400;
        foreach ($list as $row)
        {
            if ($row['add_time'] > $week_ago)
            {
                /* һ���� */
                $result[$row['type']]['week'][$row['eval']] = empty($result[$row['type']]['week'][$row['eval']]) ? 1 : $result[$row['type']]['week'][$row['eval']] + 1;
                $result[$row['type']]['month'][$row['eval']] = empty($result[$row['type']]['month'][$row['eval']]) ? 1 : $result[$row['type']]['month'][$row['eval']] + 1;
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* �ϼ� */
                $result[$row['type']]['week']['total'] = empty($result[$row['type']]['week']['total']) ? 1 : $result[$row['type']]['week']['total'] + 1;
                $result[$row['type']]['month']['total'] = empty($result[$row['type']]['month']['total']) ? 1 : $result[$row['type']]['month']['total'] + 1;
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            elseif ($row['add_time'] > $month_ago)
            {
                /* һ���� */
                $result[$row['type']]['month'][$row['eval']] = empty($result[$row['type']]['month'][$row['eval']]) ? 1 : $result[$row['type']]['month'][$row['eval']] + 1;
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* �ϼ� */
                $result[$row['type']]['month']['total'] = empty($result[$row['type']]['month']['total']) ? 1 : $result[$row['type']]['month']['total'] + 1;
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            elseif ($row['add_time'] > $half_year_ago)
            {
                /* ������ */
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* �ϼ� */
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            else
            {
                /* ����ǰ */
                $result[$row['type']]['other'][$row['eval']] = empty($result[$row['type']]['other'][$row['eval']]) ? 1 : $result[$row['type']]['other'][$row['eval']] + 1;

                /* �ϼ� */
                $result[$row['type']]['other']['total'] = empty($result[$row['type']]['other']['total']) ? 1 : $result[$row['type']]['other']['total'] + 1;
            }

            /* ���� */
            $result[$row['type']]['total'][$row['eval']] = empty($result[$row['type']]['total'][$row['eval']]) ? 1 : $result[$row['type']]['total'][$row['eval']] + 1;

            /* �ϼ� */
            $result[$row['type']]['total']['total'] = empty($result[$row['type']]['total']['total']) ? 1 : $result[$row['type']]['total']['total'] + 1;
        }

        return $result;
    }

    /**
     * ȡ�ú�����
     *
     * @param   string  $type   ���ͣ�seller buyer
     * @return     array
     */
    function get_eval_rate($type)
    {
        if ($type != 'buyer')
        {
            $type = 'seller';
        }

        /* ��ʼ�� */
        $result = array(
            '1' => 0, // ������
            '2' => 0, // ������
            '3' => 0, // ������
            'total' => 0,   // ������
            'rate'  => 0.00 // ������
        );

        if ($type == 'seller')
        {
            // ��������
            $sql = "SELECT seller_evaluation AS eval, COUNT(*) AS num " .
                    "FROM `ecm_order_info` " .
                    "WHERE seller_evaluation_invalid = 0 AND store_id = '" . $this->_id . "' " .
                    "AND seller_evaluation > 0 " .
                    "GROUP BY seller_evaluation";
        }
        else
        {
            // �������
            $sql = "SELECT buyer_evaluation AS eval, COUNT(*) AS num " .
                    "FROM `ecm_order_info` " .
                    "WHERE buyer_evaluation_invalid=0 AND user_id = '" . $this->_id . "' " .
                    "AND buyer_evaluation > 0 " .
                    "GROUP BY buyer_evaluation";
        }
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if (in_array($row['eval'], array(1,2,3)))
            {
                $result[$row['eval']] += $row['num'];
                $result['total']      += $row['num'];
            }
        }

        if ($result['total'] > 0)
        {
            $result['rate'] = round($result['3'] * 100 / $result['total'], 2);
        }

        return $result;
    }

    function _update_visit_count()
    {
        if ($this->_id > 0)
        {
            $sql = "UPDATE {$this->_table} SET visit_count = visit_count + 1 WHERE user_id={$this->_id}";

            $GLOBALS['db']->query($sql);
        }
    }

    /**
     * �����û��ֻ�������õȼ���0��15�����֣�
     *
     * @author  scottye
     * @param   float       $score          ���û���
     * @param   int         $value_of_heart ��һ���������
     * @return  int         ���õȼ�
     */
    function score_to_grade($score, $value_of_heart)
    {
        $grade = floor($score / $value_of_heart);
        if ($grade < 6)
        {
            return max(0, $grade);
        }
        else
        {
            $grade = floor($grade / 6);
            if ($grade < 6)
            {
                return $grade + 5;
            }
            else
            {
                return min(15, floor($grade / 6) + 10);
            }
        }
    }

    /**
     * ���û�����Ϊ��¼״̬.����û�������,�Զ�����
     *
     * @author redstone
     * @param array $info
     *
     * @return void
     */
    function set_login($info)
    {
        $this->_id  = $info['uid'];

        /* ����Ƿ������ecmall�û���,�����ڼ��� */
        $row = $this->get_local_user_info($info['uid']);

        if (empty($row))
        {
            $row = $this->activate($info);
        }
        else
        {
            if ($row['user_name'] != $info['username'])
            {
                $this->re_user_name($info['username']);
                $row['user_name'] = $info['username'];
            }
            if ($row['email'] != $info['email'])
            {
                $this->update(array('email' => $info['email']));
                $row['email'] = $info['email'];
            }
            $this->_update_last_login();
            $this->_update_visit_count();
        }

        /* ����session */
        $_SESSION['user_id']    = $row['user_id'];
        $_SESSION['user_name']  = $row['user_name'];
        $_SESSION['last_login'] = $row['last_login'];
        $_SESSION['last_ip']    = $row['last_ip'];
        $_SESSION['admin_store']= $row['store_id'];
        $_SESSION['feed']       = $row['default_feed'];

    }

    /**
     * �޸��û���
     * @author redstone
     * @param   string  $user_name
     * @return  void
     */
    function re_user_name($user_name)
    {
        $GLOBALS['db']->query("UPDATE `ecm_users` SET user_name='$user_name' WHERE user_id=" . $this->_id);
    }

    /**
     * ɾ���û�(ucɾ���û�ʱ�����)
     * �ѵ�������Ϊɾ��״̬
     */
    function drop()
    {
//        parent::drop();
        $sql = "DELETE FROM `ecm_users` WHERE user_id = '" . $this->_id . "'";
        $GLOBALS['db']->query($sql);
        $sql = "UPDATE `ecm_store` SET is_open = 2 WHERE store_id = '" . $this->_id . "'";
        $GLOBALS['db']->query($sql);
    }
}
?>
