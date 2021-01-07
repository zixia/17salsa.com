<?php

/**
 * ECMALL: �Ż�ȯʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.coupon.php 6009 2008-10-31 01:55:52Z Garbin $
 */


class Coupon extends Model
{
    var $_info  = NULL;

    /**
     *  ���캯��
     *  @params $store_id, $goods_list
     *  @return
     */

    function __construct($coupon_id=0, $store_id=0)
    {
        $this->Coupon($coupon_id, $store_id);
    }

    function Coupon($coupon_id=0, $store_id=0)
    {
        $this->_table = '`ecm_coupon`';
        $this->_key   = 'coupon_id';
        parent::Model($coupon_id, $store_id);
    }


    /**
     *  ͨ���Ż�ȯ��������ʼ���ö���
     *
     *  @access public
     *  @param  string $sn
     *  @return void
     */
    function init_by_sn($sn)
    {
         return $this->get_info_by_sn($sn, $this->_store_id);
    }

    /**
     *  ͨ���Ż�ȯ�����ȡ�Ż�ȯ��Ϣ
     *
     *  @access public
     *  @param  string $sn
     *  @param  int $store_id
     *  @return array
     */
    function get_info_by_sn($sn, $store_id = 0)
    {
        if ($this->_info === NULL)
        {
            $sql = "SELECT c.*,cs.* FROM `ecm_coupon` c LEFT JOIN `ecm_coupon_sn` cs ON c.coupon_id=cs.coupon_id WHERE cs.coupon_sn='{$sn}'";
            $this->_info = $GLOBALS['db']->getRow($sql);
            $this->_store_id = $this->_info['store_id'];
            if ($store_id && $this->_store_id != $store_id)
            {
                $this->_info = NULL;
                $this->_store_id = NULL;

                return FALSE;
            }

            $this->_id   = $this->_info['coupon_id'];
        }

        return $this->_info;
    }


    /**
     *  �����Ż�ȯ�Ƿ����
     *
     *  @access public
     *  @param  none
     *  @return bool
     */
    function is_usable()
    {
        if ($this->_info == NULL)
        {
            $this->_err = 'no_coupon';

            return FALSE;
        }

        /* ���ʹ�ô����Ƿ��þ� */
        if ($this->_info['usable_times'] == 0)
        {
            $this->_err = 'coupon_no_usable_time';

            return FALSE;
        }

        /* ����Ƿ���� */

        if ($this->is_expire())
        {
            return FALSE;
        }

        return TRUE;
    }


    /**
     *  ����Ƿ����
     *
     *  @return bool
     */

    function is_expire()
    {
        $now = gmtime();

        if ($this->_info['start_time'] >= $now)
        {
            $this->_err = 'coupon_coming_soon';

            return TRUE;
        }

        if($this->_info['end_time'] && $this->_info['end_time'] <= $now)
        {
            $this->_err = 'coupon_expired';

            return TRUE;
        }

        return FALSE;
    }

    /**
     * ����Ż�ȯ�ļ�ֵ
     *
     * @param   string  $coupon_sn
     * @param   int     $store_id
     *
     * @return  int     0 ��ʾ��Ч
     */
    function get_value()
    {
        if ($this->_info == NULL)
        {
            $this->_err = 'no_coupon';

            return FALSE;
        }

        return $this->_info['coupon_value'];
    }

    /**
     *  ���¿�ʹ�ô���
     *
     *  @access public
     *  @param  sign
     *  @return
     */
    function update_usable_times($range)
    {
         if ($this->_info === NULL)
         {
             $this->_err = 'no_coupon';

             return FALSE;
         }
         if ($this->_info['max_times'] == -1)
         {
             return;
         }

         $sql = "UPDATE `ecm_coupon_sn` SET usable_times = usable_times + ($range) WHERE coupon_sn = {$this->_info['coupon_sn']}";

         $GLOBALS['db']->query($sql);
    }
    /**
     * ���ɴ�������
     *
     * @param  int  $num
     *
     * @return  array
     */
    function generate($num)
    {
        $sql    = "SELECT max_times FROM `ecm_coupon` ".
                    "WHERE coupon_id=$this->_id AND store_id='$_SESSION[store_id]'";
        $times  = $GLOBALS['db']->getOne($sql);

        if ($times !== NULL)
        {
            $max = $GLOBALS['db']->getOne('SELECT MAX(coupon_sn) FROM `ecm_coupon_sn`');
            $max = strlen($max) < 12 ? '1000000' : substr($max, 0, 7);

            if ($num > 9999)
            {
                $num = 9999;
            }

            if ($num < 1)
            {
                $num = 1;
            }

            $arr = array();
            for ($i = 1; $i <= $num; $i++)
            {
                $arr[] = ($max + $i) . mt_rand(10000, 99999);
            }

            $times = ($times == 0) ? -1 : $times;   // ���������ʹ�ô�������ô���Ϊ����

            $sql = "INSERT INTO `ecm_coupon_sn` (coupon_sn, coupon_id, usable_times) VALUES ";
            foreach ($arr AS $sn)
            {
                $sql .= "('$sn', $this->_id, $times),";
            }
            $sql = substr($sql, 0, -1);

            if ($GLOBALS['db']->query($sql))
            {
                return $arr;
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

}

?>
