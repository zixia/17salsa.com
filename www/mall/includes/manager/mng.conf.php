<?php

/**
 * ECMALL: conf ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.conf.php 54 2007-12-27 06:54:41Z redstone $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class ConfManager
{
    var $err = null;
    var $_store_id = 0;

    function __construct($store_id = 0)
    {
        $this->ConfManager($store_id);
    }

    function ConfManager($store_id = 0)
    {
        $this->_store_id = intval(trim($store_id));
    }

    /**
     * ��ȡ������Ϣ
     *
     * @author  redstone
     * @return  string
     */
    function get_conf()
    {
        $conf = array();
        $sql = "SELECT code, value FROM `ecm_config_value` WHERE store_id=" . $this->_store_id;
        $res = $GLOBALS['db']->getAllCached($sql);
        foreach ($res as $val)
        {
            $conf[$val['code']] = $val['value'];
        }

        return $conf;
    }

    /**
     * ����޸�������Ϣ��Ŀ
     *
     * @author  weberliu
     * @param   string  $group_code
     * @return  array   items
     */
    function get_item($owner)
    {
        $tmp = array();
        $sql = "SELECT * FROM `ecm_config_item` WHERE owner='$owner' ORDER BY sort_order";
        $query = $GLOBALS['db']->query($sql);
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            $tmp[$res['group_code']][] = $res;
        }
        return $tmp;
    }

    /**
     * ����һ��������
     *
     * @param   int   $store_id     ����ID
     *
     * @return boolean
     */
    function clone_conf()
    {
        $sql = "INSERT INTO `ecm_config_value` (store_id, code, value) ".
                "SELECT '" . $this->_store_id . "', code, default_value FROM `ecm_config_item` WHERE owner='store'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * �����������ֵ
     * @param   string  $code
     * @param   fix   $value
     *
     * @return void
     */
    function set_conf($code, $value)
    {
        $GLOBALS['db']->query("UPDATE `ecm_config_value` SET value='$value' WHERE code='$code' AND store_id='" . $this->_store_id . "'");
    }
}

?>