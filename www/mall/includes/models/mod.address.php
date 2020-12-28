<?php

/**
 * ECMALL: ��ַ��Ϣʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.address.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Address extends Model
{
    /**
     * ���캯��
     */
    function __construct($id, $store_id = 0)
    {
        $this->Address($id, $store_id);
    }

    function Address($id, $store_id = 0)
    {
        $this->_table = '`ecm_user_address`';
        $this->_key   = 'address_id';
        parent::Model($id, $store_id);
    }

    /**
     * �ӵ�ַ��Ϣ��ȡ�����¼��� region_id
     *
     * @param   array   $address    ��ַ��Ϣ
     */
    function get_region_id($address)
    {
        if ($address['region4'] > 0)
        {
            return $address['region4'];
        }
        elseif ($address['region3'] > 0)
        {
            return $address['region3'];
        }
        elseif ($address['region2'] > 0)
        {
            return $address['region2'];
        }
        else
        {
            return $address['region1'];
        }
    }
}

?>