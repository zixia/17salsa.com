<?php

/**
 * ECMALL: 会员实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 构造函数
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
     * 取得用户信息
     *
     * @author  weberliu
     * @param   int     $value_of_heart 信用升一级所需积分
     * @param   string  $timeformat     时间格式
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
                    /* 信用等级 */
                    $info['seller_grade'] = $this->score_to_grade($info['seller_credit'], $value_of_heart);
                    $info['buyer_grade']  = $this->score_to_grade($info['buyer_credit'], $value_of_heart);
                }
            }
            $arr[$this->_id] = $info;
        }

        return $arr[$this->_id];
    }

    /**
     * 和uc 接口保持一致
     *
     * @return  void
     */
    function add($username, $password, $email)
    {
        $uid = uc_call('uc_user_register', array($username, $password, $email));

        if ($uid > 0)
        {
            //将用添加到网站系统
            $cur_time = gmtime();
            $new_user = array('user_id'=>$uid, 'user_name'=>$username, 'email'=>$email, 'default_feed'=>15, 'reg_time' => $cur_time,
                              'last_login'=> $cur_time, 'last_ip'=>real_ip(), 'password'=>$this->generate_password());
            /* 检查用户存不在 */
            $sql = "SELECT user_id FROM `ecm_users` WHERE user_id = '$uid' LIMIT 1";
            if ($GLOBALS['db']->getOne($sql) > 0)
            {
                $GLOBALS['db']->autoExecute('`ecm_users`', $new_user, 'UPDATE', "user_id ='$uid'");
            }
            else
            {
                $GLOBALS['db']->autoExecute('`ecm_users`', $new_user, 'INSERT');
            }

            /* 保存session */
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
     * 修改个人资料
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
     * 用户登录
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
            $this->set_login($tmp); //设置登录状态
        }

        return $tmp;
    }
    /**
     * 取得用户积分数
     * @param   int     $store_id   店铺id
     * @param   bool    $show       是否是用于显示账户信息
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
     * 添加积分变动记录
     *
     * @param    int        $store_id    店铺id
     * @param    int        $points        积分数（正数表示加，负数表示减）
     * @param    string    $remark        说明
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
     * 取得收货地址
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
     * 更新收货地址
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
     * 删除某条收货地址
     *
     * @return void
     */
    function drop_address($address_id)
    {
        $sql = "DELETE FROM `ecm_user_address` WHERE user_id='" . $this->_id . "' AND address_id='" . $address_id . "'";
        return $GLOBALS['db']->query($sql);
    }

    /**
     * 添加收货地址
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
     * 获得目前用户保存的收获地址数目
     * @return int
     */
    function get_address_count()
    {
        $sql = "SELECT COUNT(*) FROM `ecm_user_address` WHERE user_id='" . $this->_id . "'";
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 将一个商品添加到用户收藏夹
     * @param   int     $goods_id   店铺id
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
     * 删除收藏夹中的某条内容
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
     * 获得用户收藏商品的id
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
     * 取得评价
     *
     * @return     array
     */
    function get_evaluation()
    {
        $list = array();

        // 卖家评价
        $sql = "SELECT 'seller' AS type, seller_evaluation AS eval, add_time " .
                "FROM `ecm_order_info` " .
                "WHERE store_id = '" . $this->_id . "' " .
                "AND seller_evaluation > 0 " .
                "AND seller_evaluation_invalid = 0 ";
        $list = $GLOBALS['db']->getAll($sql);

        // 买家评价
        $sql = "SELECT 'buyer' AS type, buyer_evaluation AS eval, add_time " .
                "FROM `ecm_order_info` " .
                "WHERE user_id = '" . $this->_id . "' " .
                "AND buyer_evaluation > 0 " .
                'AND buyer_evaluation_invalid = 0 ';
        $list = array_merge($list, $GLOBALS['db']->getAll($sql));

        // 按时间和评价分开
        $result         = array();
        $week_ago       = gmtime() - 7 * 86400;
        $month_ago      = gmtime() - 30 * 86400;
        $half_year_ago  = gmtime() - 180 * 86400;
        foreach ($list as $row)
        {
            if ($row['add_time'] > $week_ago)
            {
                /* 一周内 */
                $result[$row['type']]['week'][$row['eval']] = empty($result[$row['type']]['week'][$row['eval']]) ? 1 : $result[$row['type']]['week'][$row['eval']] + 1;
                $result[$row['type']]['month'][$row['eval']] = empty($result[$row['type']]['month'][$row['eval']]) ? 1 : $result[$row['type']]['month'][$row['eval']] + 1;
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* 合计 */
                $result[$row['type']]['week']['total'] = empty($result[$row['type']]['week']['total']) ? 1 : $result[$row['type']]['week']['total'] + 1;
                $result[$row['type']]['month']['total'] = empty($result[$row['type']]['month']['total']) ? 1 : $result[$row['type']]['month']['total'] + 1;
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            elseif ($row['add_time'] > $month_ago)
            {
                /* 一月内 */
                $result[$row['type']]['month'][$row['eval']] = empty($result[$row['type']]['month'][$row['eval']]) ? 1 : $result[$row['type']]['month'][$row['eval']] + 1;
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* 合计 */
                $result[$row['type']]['month']['total'] = empty($result[$row['type']]['month']['total']) ? 1 : $result[$row['type']]['month']['total'] + 1;
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            elseif ($row['add_time'] > $half_year_ago)
            {
                /* 半年内 */
                $result[$row['type']]['half_year'][$row['eval']] = empty($result[$row['type']]['half_year'][$row['eval']]) ? 1 : $result[$row['type']]['half_year'][$row['eval']] + 1;

                /* 合计 */
                $result[$row['type']]['half_year']['total'] = empty($result[$row['type']]['half_year']['total']) ? 1 : $result[$row['type']]['half_year']['total'] + 1;
            }
            else
            {
                /* 半年前 */
                $result[$row['type']]['other'][$row['eval']] = empty($result[$row['type']]['other'][$row['eval']]) ? 1 : $result[$row['type']]['other'][$row['eval']] + 1;

                /* 合计 */
                $result[$row['type']]['other']['total'] = empty($result[$row['type']]['other']['total']) ? 1 : $result[$row['type']]['other']['total'] + 1;
            }

            /* 总数 */
            $result[$row['type']]['total'][$row['eval']] = empty($result[$row['type']]['total'][$row['eval']]) ? 1 : $result[$row['type']]['total'][$row['eval']] + 1;

            /* 合计 */
            $result[$row['type']]['total']['total'] = empty($result[$row['type']]['total']['total']) ? 1 : $result[$row['type']]['total']['total'] + 1;
        }

        return $result;
    }

    /**
     * 取得好评率
     *
     * @param   string  $type   类型：seller buyer
     * @return     array
     */
    function get_eval_rate($type)
    {
        if ($type != 'buyer')
        {
            $type = 'seller';
        }

        /* 初始化 */
        $result = array(
            '1' => 0, // 差评数
            '2' => 0, // 中评数
            '3' => 0, // 好评数
            'total' => 0,   // 总评数
            'rate'  => 0.00 // 好评率
        );

        if ($type == 'seller')
        {
            // 卖家评价
            $sql = "SELECT seller_evaluation AS eval, COUNT(*) AS num " .
                    "FROM `ecm_order_info` " .
                    "WHERE seller_evaluation_invalid = 0 AND store_id = '" . $this->_id . "' " .
                    "AND seller_evaluation > 0 " .
                    "GROUP BY seller_evaluation";
        }
        else
        {
            // 买家评价
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
     * 把信用积分换算成信用等级（0到15的数字）
     *
     * @author  scottye
     * @param   float       $score          信用积分
     * @param   int         $value_of_heart 升一级所需积分
     * @return  int         信用等级
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
     * 将用户设置为登录状态.如果用户不存在,自动激活
     *
     * @author redstone
     * @param array $info
     *
     * @return void
     */
    function set_login($info)
    {
        $this->_id  = $info['uid'];

        /* 检查是否存在于ecmall用户表,不存在激活 */
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

        /* 保存session */
        $_SESSION['user_id']    = $row['user_id'];
        $_SESSION['user_name']  = $row['user_name'];
        $_SESSION['last_login'] = $row['last_login'];
        $_SESSION['last_ip']    = $row['last_ip'];
        $_SESSION['admin_store']= $row['store_id'];
        $_SESSION['feed']       = $row['default_feed'];

    }

    /**
     * 修改用户名
     * @author redstone
     * @param   string  $user_name
     * @return  void
     */
    function re_user_name($user_name)
    {
        $GLOBALS['db']->query("UPDATE `ecm_users` SET user_name='$user_name' WHERE user_id=" . $this->_id);
    }

    /**
     * 删除用户(uc删除用户时会调用)
     * 把店铺设置为删除状态
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
