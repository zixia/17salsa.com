<?php

/**
 * ECMALL: �ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.activity.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Activity extends Model
{
    var $_type  = null;

    function __construct($act_type, $act_id=0, $store_id=0)
    {
        $this->Activity($act_type, $act_id, $store_id);
    }

    function Activity($act_type, $act_id=0, $store_id=0)
    {
        $this->_table = '`ecm_goods_activity`';
        $this->_key   = 'act_id';
        $this->_type = $act_type;

        parent::__construct($act_id, $store_id);
    }

    function get_info()
    {
        $arr = parent::get_info();

        if (!empty($arr))
        {
            /* ���������չ��Ϣ */
            $ext = unserialize($arr['ext_info']);

            foreach ($ext AS $key=>$val)
            {
                $arr[$key] = $val;
            }

            unset($arr['ext_info']);

            /* ת��ʱ���ʽ */
            $arr['start_time']  = local_date('Y-m-d', $arr['start_time']);
            $arr['end_time']    = local_date('Y-m-d', $arr['end_time']);
        }

        return $arr;
    }
}

?>
