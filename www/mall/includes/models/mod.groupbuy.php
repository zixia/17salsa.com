<?php

/**
 * ECMALL: 团购活动实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.groupbuy.php 6009 2008-10-31 01:55:52Z Garbin $
 */

require_once(ROOT_PATH. '/includes/models/mod.activity.php');
class GroupBuy extends Activity
{
    var $_act_id = 0;

    /**
     *  构造函数
     *  @params $store_id, $goods_list
     *  @return
     */
    function __construct($act_id=0, $store_id=0)
    {
        $this->GroupBuy($act_id, $store_id);
    }

    function GroupBuy($act_id=0, $store_id=0)
    {
        $this->_act_id = $act_id;

        parent::__construct(ACT_GROUPBUY, $act_id, $store_id);
    }

    /**
     *  获取团购已参加人数和购买数量
     *  @params void
     *  @return array
     */
    function get_total_info()
    {
        $sql = "SELECT SUM(number) as goods_num, COUNT(user_id) AS actor_num ".
               " FROM `ecm_group_buy` WHERE act_id = '{$this->_act_id}'";

        $data = $GLOBALS['db']->getRow($sql);

        if (empty($data['goods_num'])) $data['goods_num'] = 0;

        return $data;
    }

    /**
     * 删除团购活动
     */
    function drop()
    {
        $sql = "DELETE FROM `ecm_goods_activity` WHERE act_id = '{$this->_act_id}' AND act_type='" . ACT_GROUPBUY . "'";
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id='{$this->_store_id}'";
        }
        $GLOBALS['db']->query($sql);
        if ($GLOBALS['db']->affected_rows())
        {
            $sql = "DELETE FROM `ecm_group_buy` WHERE act_id='{$this->_act_id}'";
            $GLOBALS['db']->query($sql);
        }
    }
}

?>
