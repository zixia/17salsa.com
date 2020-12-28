<?php

/**
 * ECMALL: ����ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
     * ȡ�õ�����Ϣ
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
        $res['closed_by_admin'] = empty($res['is_open']); // ������Ա�ر�
        $res['closed_by_owner'] = empty($res['self_open']); // �������رգ���̨���Է��ʣ�
        $res['expired']         = $res['end_time'] > 0 && $res['end_time'] < gmtime(); // �ѹ���
        $res['reletable']       = $res['end_time'] > 0 && $res['end_time'] - STORE_RELET_TIME < gmtime(); // ������

        return $res;
    }

    /**
     * ɾ�����̣����øù���
     *
     * @return  void
     */
    function drop()
    {
        return;
    }

    /**
     * ���µ����µ���Ʒ����
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
     * ���µ����µĶ�������
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
     * ȡ�õ���������Ʒ��
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
     * ȡ�õ������е��ļ���
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