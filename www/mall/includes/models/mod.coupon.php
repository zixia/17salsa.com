<?php

/**
 * ECMALL: 优惠券实体类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.coupon.php 6009 2008-10-31 01:55:52Z Garbin $
 */


class Coupon extends Model
{
    var $_info  = NULL;

    /**
     *  构造函数
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
     *  通过优惠券号码来初始化该对象
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
     *  通过优惠券号码获取优惠券信息
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
     *  检查该优惠券是否可用
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

        /* 检查使用次数是否用尽 */
        if ($this->_info['usable_times'] == 0)
        {
            $this->_err = 'coupon_no_usable_time';

            return FALSE;
        }

        /* 检查是否过期 */

        if ($this->is_expire())
        {
            return FALSE;
        }

        return TRUE;
    }


    /**
     *  检查是否过期
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
     * 获得优惠券的价值
     *
     * @param   string  $coupon_sn
     * @param   int     $store_id
     *
     * @return  int     0 表示无效
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
     *  更新可使用次数
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
     * 生成促销代码
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

            $times = ($times == 0) ? -1 : $times;   // 如果不限制使用次数则可用次数为负数

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
