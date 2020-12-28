<?php

/**
 * ECMALL: ��Աʵ�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.userbase.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class UserBase extends Model
{
    /**
     * ���캯��
     */
    function __construct($user_id, $store_id=0)
    {
        $this->UserBase($user_id, $store_id);
    }

    function UserBase($user_id, $store_id=0)
    {
        parent::Model($user_id, $store_id);
    }
    /**
     * �û��ǳ�
     *
     * @author wj
     * @return  void
     */
    function logout()
    {
        $GLOBALS['sess']->destroy_session();
        $_SESSION = array();
    }


    /**
     *  ��ȡ�û���ϸ��Ϣ
     *
     *  @access public
     *  @param  none
     *  @return array
     */
    function get_user_detail()
    {
        if (!$this->_id)
        {
            return;
        }
        $sql = "SELECT * FROM `ecm_users` WHERE user_id={$this->_id}";

        return $GLOBALS['db']->getRow($sql);
    }


    /**
     *  ���ص�¼,����UC��ȥ��֤
     *
     *  @access public
     *  @param  int $user_id        �û�ID
     *  @param  string $password    ��������
     *  @param  string $model       ADMINΪ��̨�û���¼,USERΪǰ̨�û���¼
     *  @return mixed
     */

    function local_login($user_id, $password)
    {
        $user_id = intval($user_id);
        if ($this->_model == 'ADMIN')
        {
            $sql = "SELECT u.user_name,u.password,au.* FROM `ecm_users` u LEFT JOIN `ecm_admin_user` au ON u.user_id = au.user_id WHERE u.user_id={$user_id} AND u.password='{$password}'";
        }
        else
        {
            $sql = "SELECT * FROM `ecm_users` WHERE user_id={$user_id} AND password='{$password}'";
        }
        $row = $GLOBALS['db']->getRow($sql);

        if ($row['user_id'] > 0 )
        {
            if ($this->_model == 'ADMIN')
            {
                $this->_id = $row['user_id'];
                $this->_update_last_login();

                /* ����session */
                $_SESSION['admin_id']   = $row['user_id'];
                $_SESSION['admin_name'] = $row['real_name'];
                $_SESSION['store_id']   = $row['store_id'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];
            }
            else
            {
                $this->_id  = $row['uid'];
                $this->_update_last_login();

                /* ����session */
                $_SESSION['user_id']    = $row['user_id'];
                $_SESSION['user_name']  = $row['user_name'];
                $_SESSION['last_login'] = $row['last_login'];
                $_SESSION['last_ip']    = $row['last_ip'];
            }

            return $row;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * ��Ucenter��ȡͬ����¼����
     *
     * @author  weberliu
     * @return  string
     */
    function sync_login($uid=null)
    {
        $res = uc_call('uc_user_synlogin', array($uid));
        return $this->_sync_ucenter('uc_user_synlogin', $uid);
    }

    /**
     * ��Ucenter��ȡͬ���˳�����
     *
     * @author  weberliu
     * @return  string
     */
    function sync_logout($uid=null)
    {
        return $this->_sync_ucenter('uc_user_synlogout', $uid);
    }

    /**
     * ��Ucenter���ͬ������ǳ��Ĵ���
     *
     * @author  weberliu
     * @param   string  $func
     * @param   int     $uid
     * @return  string
     */
    function _sync_ucenter($func, $uid=null)
    {
        if ($uid === null)
        {
            $uid = $this->_id;
        }

        $res = uc_call($func, array($uid));

        if (preg_match('/<script/i', $res))
        {
            return $res;
        }
        else
        {
            return '';
        }
    }

    /**
     * ���������
     *
     * @param   int     $len
     *
     * @return  string
     */
    function generate_code($len=4)
    {
        $chars = '23457acefhkmprtvwxy';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++)
        {
            $arr[$i] = $chars[$i];
        }

        mt_srand((double) microtime() * 1000000);
        shuffle($arr);

        $code = substr(implode('', $arr), 5, $len);

        return $code;
    }

    /**
     * ��������¼ʱ���IP��ַ
     *
     * @return  void
     */
    function _update_last_login()
    {
        $sql = "UPDATE $this->_table SET ".
                "last_login='" .gmtime(). "',".
                "last_ip='" .real_ip(). "' ".
                "WHERE $this->_key='$this->_id'";
        $GLOBALS['db']->query($sql);
    }

    /**
     * �������������
     *
     *
     *  @return array goods_id
     */
    function generate_password()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        $hash = '';
        for($i = 0; $i < 10; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }

        return md5($hash);
    }

    /**
     * ������û�
     * @authore scottye
     *
     * @param array base_info
     *
     * @return array user_info
     */
    function activate($info)
    {
        $row = array();
        $row['user_id'] = $info['uid'];
        $row['user_name'] = addslashes($info['username']);
        $row['email'] = $info['email'];
        $row['last_login'] = gmtime();
        $row['reg_time'] = gmtime();
        $row['last_ip'] = real_ip();
        $row['visit_count'] = 1;
        $row['store_id'] = -1;
        $row['default_feed'] = 15;
        $row['password'] = $this->generate_password();
        $GLOBALS['db']->autoExecute('`ecm_users`', $row, 'INSERT');

        return $this->get_local_user_info($info['uid']);
    }

    /**
     *  �����������û���
     *
     *  @author Garbin
     *  @return void
     */
    function update_seller_credit()
    {
        $this->update_credit('seller');
    }

    /**
     *  ����������û���
     *
     *  @author Garbin
     *  @return void
     */
    function update_buyer_credit()
    {
        $this->update_credit('buyer');
    }

    /**
     *  �������û���
     *
     *  @author Garbin
     *  @param  string $mode    Ҫ���µ����û������ƣ���ѡֵ[seller:]
     *  @return void
     */
    function update_credit($mode)
    {
        if ($mode != 'seller' && $mode != 'buyer')
        {
            return;
        }
        $user_field = $mode == 'seller' ? 'store_id' : 'user_id';
        $sql = "SELECT SUM({$mode}_credit) as s_c ".
               "FROM `ecm_order_info` WHERE {$user_field}={$this->_id} AND {$mode}_evaluation_invalid=0";
        $v   = $GLOBALS['db']->getOne($sql);
        $v   = floatval($v);

        $usql = "UPDATE `ecm_users` SET {$mode}_credit = {$v} WHERE user_id={$this->_id}";
        $GLOBALS['db']->query($usql);
    }

    /**
     * ��ȡ��Ӧ�õ��û�����
     * @author  wj
     * @param   int     $uid     �û�id
     *
     * @return void
     */
    function get_local_user_info($uid)
    {
        $sql = "SELECT u.*, IFNULL(au.store_id, -1) AS store_id FROM `ecm_users` AS u ".
                    "LEFT JOIN `ecm_admin_user` AS au ON au.user_id=u.user_id WHERE u.user_id='$uid'";

        return  $GLOBALS['db']->getRow($sql);
    }
}
?>
