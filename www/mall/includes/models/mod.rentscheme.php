<?php

/**
 * ECMALL: ���ⷽ��ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.rentscheme.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


class RentScheme extends Model
{
    /**
     * ���캯��
     */
    function __construct($id)
    {
        $this->RentScheme($id);
    }

    function RentScheme($id)
    {
        $this->_table = '`ecm_rent_scheme`';
        $this->_key   = 'scheme_id';
        parent::Model($id);
    }
}
?>