<?php

/**
 * ECMALL: 店铺实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.store.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Store extends Model
{
    /**
     * 构造函数
     */
    function __construct($store_id)
    {
        $this->Store($store_id);
    }

    function Store($store_id)
    {
        $this->_table = '`ecm_store`';
        $this->_key   = 'store_id';
        parent::Model($store_id);
    }

    /**
     * 取得店铺信息
     *
     * @return  array
     */
    function get_info()
    {
        $sql = "SELECT s.*, u.user_name, u.email, c.value as self_open FROM `ecm_store` AS s ".
                "LEFT JOIN `ecm_users` AS u ON u.user_id = s.store_id ".
                "LEFT JOIN `ecm_config_value` AS c ON s.store_id = c.store_id AND c.code = 'store_status' ".
                "WHERE s.store_id = '" . $this->_id . "'";

        $res = $GLOBALS['db']->getRow($sql);
        $res['closed_by_admin'] = empty($res['is_open']); // 被管理员关闭
        $res['closed_by_owner'] = empty($res['self_open']); // 被店主关闭（后台可以访问）
        $res['expired']         = $res['end_time'] > 0 && $res['end_time'] < gmtime(); // 已过期
        $res['reletable']       = $res['end_time'] > 0 && $res['end_time'] - STORE_RELET_TIME < gmtime(); // 可续租

        return $res;
    }

    /**
     * 删除店铺，禁用该功能
     *
     * @return  void
     */
    function drop()
    {
        return;
    }

    /**
     * 更新店铺下的商品总数
     *
     * @param  int  $num
     *
     * @return  void
     */
    function update_goods_count($num)
    {
        $arr = array('goods_count', "goods_count+($num)");

        return $this->update($arr);
    }

    /**
     * 更新店铺下的订单总数
     *
     * @param  int  $num
     *
     * @return  void
     */
    function update_order_count($num)
    {
        $sql = "UPDATE {$this->_table} SET order_count = order_count + ($num) WHERE store_id = {$this->_id}";
        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * 取得店铺已有商品数
     *
     * @author  scottye
     * @return  int
     */
    function get_goods_count()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager($this->_id);
        return $mng_goods->get_count(array());
    }

    /**
     * 取得店铺已有的文件数
     *
     * @author  scottye
     * @return  int
     */
    function get_file_count()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $mng_file = new FileManager($this->_id);
        return $mng_file->get_store_file_count($this->_id);
    }
}
?>