<?php

/**
 * ECMALL: conf 管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 读取配置信息
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
     * 获得修改配置信息项目
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
     * 复制一份配置项
     *
     * @param   int   $store_id     店铺ID
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
     * 设置配置项的值
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