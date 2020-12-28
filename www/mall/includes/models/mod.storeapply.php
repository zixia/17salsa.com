<?php

/**
 * ECMALL: ��������ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.storeapply.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreApply extends Model
{
    /**
     * ���캯��
     */
    function __construct($apply_id)
    {
        $this->StoreApply($apply_id);
    }

    function StoreApply($apply_id)
    {
        $this->_table = '`ecm_store_apply`';
        $this->_key   = 'apply_id';
        parent::Model($apply_id);
    }

    /**
     * ȡ�ÿ����������Ϣ
     *
     * @author  weberliu
     * @return  array
     */
    function get_info()
    {
        $sql = "SELECT a.*, u.user_name, r.region_name FROM $this->_table AS a ".
                "LEFT JOIN `ecm_users` AS u ON u.user_id=a.user_id ".
                "LEFT JOIN `ecm_regions` AS r ON r.region_id=a.store_location ".
                "WHERE a.$this->_key='$this->_id'";
        $res = $GLOBALS['db']->getRow($sql);

        return $res;
    }

}
?>