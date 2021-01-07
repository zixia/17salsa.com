<?php

/**
 * ECMALL: ����Ա��־������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.admin_logs.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/* ������־��¼������ */
include_once(ROOT_PATH . '/includes/manager/mng.logger.php');

class AdminLogManager extends Logger
{
    /**
     *  ��¼�����ű���
     *
     *  @access
     */

    var $_table = '`ecm_admin_log`';

    /**
     *  Ҫ��¼����Ϣ�ֶ�
     *
     *  @access
     */

    var $_fields = array('username',
                         'application',
                         'action',
                         'item_id',
                         'store_id',
                         'execution_time',
                         'ip_address');

    /**
     *  ����ֶα�ʾ�¼�������ʱ��
     *
     *  @access
     */

    var $_time_field_name = 'execution_time';

    /**
     *  ����ֶ����ʾ��¼��Ψһ��ʶ
     *
     *  @access
     */

    var $_primary_key = 'log_id';

    /**
     *  ������־
     *
     *  @access
     *  @params
     *  @return
     */

    function add($username, $app, $act, $item_id=0)
    {
        $info = array('username'        => $username,
                      'application'     => $app,
                      'action'          => $act,
                      'item_id'         => $item_id,
                      'ip_address'      => real_ip(),
                      'execution_time'  => gmtime(),
                      'store_id'        => $this->_store_id);

        return $this->write($info);
    }

    /**
     * ������ѯ�������
     *
     * @param   array   $condition
     *
     * @return  string
     */

    function _make_condition($condition)
    {
        $where  = "store_id = '" . $this->_store_id . "'";
        if (!empty($condition))
        {
            $where = "username LIKE '%$condition[username]%'";
        }

        return $where;
    }
};
?>