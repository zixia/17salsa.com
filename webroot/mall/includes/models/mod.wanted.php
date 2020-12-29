<?php

/**
 * ECMALL: 求购信息实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.wanted.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}


class Wanted extends Model
{
    var $_table = '`ecm_wanted`';
    var $_key   = 'log_id';

    /**
     * 构造函数
     */
    function __construct($id, $store_id = 0)
    {
        $this->Wanted($id, $store_id = 0);
    }

    function Wanted($id, $store_id = 0)
    {
        parent::Model($id, $store_id = 0);
    }

    /**
     *    获取求购信息
     *
     *    @author:    Garbin
     *    @return:    array
     */
    function get_info($with_reply = false)
    {
        $info = $GLOBALS['db']->getRow('SELECT al.*,u.user_name,r.region_name FROM `ecm_wanted` al '.
                                       'LEFT JOIN `ecm_users` u ON al.user_id=u.user_id ' .
                                       'LEFT JOIN `ecm_regions` r ON al.region_id=r.region_id '.
                                       "WHERE log_id={$this->_id}");
        if (empty($info))
        {
            return false;
        }
        $info['avatar'] = UC_API . '/avatar.php?uid=' . $info['user_id'] . '&amp;size=small';
        $info['uchome_url'] = uc_home_url($info['user_id']);
        $info['expiry_days'] = floor(($info['expiry'] - $info['add_time']) / (24 * 3600));
        if ($with_reply)
        {
            $info['reply_list'] = $this->get_reply();
        }

        return $info;
    }

    /**
     *    编辑
     *
     *    @author:    Garbin
     *    @param:     array $info
     *    @return:    void
     */
    function update($info)
    {
        if (isset($info['expiry']))
        {
            $info['expiry'] = gmtime() + $info['expiry'] * 24 * 3600;
        }

        parent::update($info);
    }

    /**
     *    删除求购信息
     *
     *    @author:    Garbin
     *    @return:    bool
     */
    function drop()
    {
        parent::drop();
        /* 删除回复 */
        $sql = "DELETE FROM `ecm_wanted_reply` WHERE log_id={$this->_id}";

        return $GLOBALS['db']->query($sql);
    }

    /**
     *    新增回复
     *
     *    @author:    Garbin
     *    @param:     array $reply_info
     *    @return:    int
     */
    function reply($reply_info)
    {
        $user_id = intval($reply_info['user_id']);
        $now     = gmtime();
        $detail  = trim($reply_info['detail']);
        $goods_url = trim($reply_info['goods_url']);
        $sql = 'INSERT INTO `ecm_wanted_reply`(user_id, log_id, add_time, detail, goods_url) ' .
               "VALUES({$user_id}, {$this->_id}, {$now}, '{$detail}', '{$goods_url}')";

        $GLOBALS['db']->query($sql);
        $id = $GLOBALS['db']->insert_id();
        $sql = "UPDATE {$this->_table} SET replies = replies + 1 WHERE {$this->_key} = {$this->_id}";
        $GLOBALS['db']->query($sql);

        return $id;
    }

    /**
     *    获取回复列表
     *
     *    @author:    Garbin
     *    @return:    void
     */
    function get_reply()
    {
        $sql = 'SELECT ar.*,u.user_name,s.store_name,s.store_id FROM `ecm_wanted_reply` ar ' .
               'LEFT JOIN `ecm_users` u ON ar.user_id = u.user_id ' .
               'LEFT JOIN `ecm_admin_user` au ON ar.user_id=au.user_id ' .
               'LEFT JOIN `ecm_store` s ON au.store_id = s.store_id ' .
               'WHERE ar.log_id = ' . $this->_id;
        $replies = $GLOBALS['db']->getAll($sql);
        foreach ($replies as $_k => $_v)
        {
            $_v['avatar'] = UC_API . '/avatar.php?uid=' . $_v['user_id'] . '&amp;size=small';
            $_v['uchome_url'] = uc_home_url($_v['user_id']);
            $replies[$_k] = $_v;
        }

        return $replies;
    }
}

?>
