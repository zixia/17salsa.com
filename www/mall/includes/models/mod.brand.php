<?php

/**
 * ECMALL: Ʒ��ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.brand.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class Brand extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id)
    {
        $this->Brand($id, $store_id);
    }

    function Brand($id, $store_id)
    {
        $this->_table = '`ecm_brand`';
        $this->_key   = 'brand_id';
        parent::Model($id, $store_id);
    }

    /**
     * ��Ϊ����Ʒ�ƣ���������ʱstore��0��
     */
    function set_public()
    {
        if ($this->_store_id == 0)
        {
            return $this->update(array('store_id' => 0));
        }
        else
        {
            $this->err = '';
            return false;
        }
    }

    /*
     * ���ݸ�����ID���Ʒ�Ƶ�����
     *
     * @author  weberliu
     * @param   string      $keywords   ��ѯ�ؼ���
     * @param   string      $limit      ���صļ�¼����
     * @return  array
     */
    function get_brand_name($keywords, $limit)
    {
        $sql = "SELECT brand_name FROM {$this->_table} ".
               " WHERE store_id IN (0, {$this->_store_id}) AND brand_name LIKE '$keywords%'".
               " LIMIT $limit";
        return $GLOBALS['db']->getCol($sql);
    }
}
?>