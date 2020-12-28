<?php

/**
 * ECMALL: 团购活动参与人员管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.groupbuy_actor.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class GroupBuyActorManager extends Manager
{
    var $_act_id    = 0;
    var $_store_id  = 0;

    /**
     *  构造函数
     *
     *  @params int $store_id
     *
     *  @return void
     */
    function __construct($act_id, $store_id=0)
    {
        $this->GroupBuyActorManager($act_id, $store_id);
    }

    function GroupBuyActorManager($act_id, $store_id=0)
    {
        $this->_act_id      = $act_id;
        $this->_store_id    = $store_id;
    }

    /**
     * 获得参与者列表
     *
     * @param   int     $page
     * @param   array   $condition
     *
     * @return  array
     */
    function get_list($page, $condition=array())
    {
        $arg = $this->query_params($page, $condition, 'log_id');

        $sql = "SELECT a.* , u.user_name as login_user_name".
               " FROM `ecm_group_buy` AS a ".
               " LEFT JOIN `ecm_users` AS u ON a.user_id=u.user_id".
               " WHERE act_id=$this->_act_id " .
                "ORDER BY $arg[sort] $arg[order] LIMIT $arg[start], $arg[number]";
        $res = $GLOBALS['db']->getAll($sql);

        return array('data' => $res, 'info' => $arg['info']);
    }

    /**
     * 获得符合条件的记录总数
     *
     * @return  int
     */
    function get_count($condition=array())
    {
        $sql    = "SELECT COUNT(*) FROM `ecm_group_buy` WHERE act_id=$this->_act_id";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }
    /**
     * 添加团购参与人员
     *
     * @param   array    $arr
     *
     * @return  boolean
     */
    function add($arr)
    {
        $arr['store_id'] = $this->_store_id;
        $arr['act_id'] = $this->_act_id;

        return $GLOBALS['db']->autoExecute('`ecm_group_buy`', $arr);
    }

    /**
     * 删除指定的记录
     *
     * @param   int     $log_id
     *
     * @return  boolean
     */
    function drop($log_id)
    {
        $sql = "DELETE FROM `ecm_group_buy` WHERE log_id ='$log_id' AND act_id='$this->_act_id' AND store_id='$this->_store_id'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 改变记录的状态
     *
     * @param   int     $status
     *
     * @return  boolean
     */
    function update_status()
    {
        $sql = "UPDATE `ecm_group_buy` SET status='$status' WHERE log_id='$log_id' AND act_id='$this->_act_id' AND store_id='$this->_store_id'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 根据log id 获取用户信息
     *
     * @param   int     $status
     *
     * @return  boolean
     */
    function get_info_by_id($ids)
    {
        $sql = "SELECT * FROM `ecm_group_buy` WHERE log_id  " . db_create_in($ids);
        $res = $GLOBALS['db']->query($sql);
        $result = array();
        while ($row = $GLOBALS['db']->FetchRow($res))
        {
            $result[$row['log_id']] = $row;
        }

        return $result;
    }

    function update($log_id, $data)
    {
        return $GLOBALS['db']->autoExecute('`ecm_group_buy`', $data, 'UPDATE', "log_id = '$log_id'");
    }
    /**
     * 检查用户是否已经参加过改团购了
     *
     * @author wj
     * @param int $user_id  用户id
     * @return boolen
     */
    function had_join($user_id)
    {
        $sql = "SELECT COUNT(*) FROM `ecm_group_buy` WHERE act_id='$this->_act_id' AND user_id = '$user_id'";

        return $GLOBALS['db']->getOne($sql);
    }
};

 ?>
