<?php

/**
 * ECMALL: ���ͷ�ʽʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.shipping.php 6064 2008-11-13 10:19:42Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Shipping extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id)
    {
        $this->Shipping($id, $store_id);
    }

    function Shipping($id, $store_id)
    {
        $this->_table = '`ecm_shipping`';
        $this->_key   = 'shipping_id';
        parent::Model($id, $store_id);
    }

    /**
     *  ��ȡ���ͷ�ʽ����Ϣ
     *
     *  @param
     *  @return
     */
    function get_info()
    {
        $info = parent::get_info();
        $info['cod_regions_array'] = explode(',', $info['cod_regions']);

        return $info;
    }

    /**
     *  ����
     *
     *  @param  int $status 0Ϊ���ã�1Ϊ����
     *  @return int
     */
    function enable($status = 1)
    {
        $status = intval($status) ? 1 : 0;
        $sql = "UPDATE `ecm_shipping` SET enabled={$status} WHERE shipping_id={$this->_id}" . $this->_get_store_limit();

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }
}

?>