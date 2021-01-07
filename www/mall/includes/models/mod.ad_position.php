<?php

/**
 * ECMALL: ���λģ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.ad_position.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.base.php');
require_once(ROOT_PATH. '/includes/manager/mng.ad.php');
require_once(ROOT_PATH. '/includes/models/mod.ad.php');

class AdPosition extends Model
{
    var $_table = '`ecm_ad_position`';
    var $_key   = 'position_id';

    /**
     * ���캯��
     *
     * @access  public
     * @param   int article_id
     *
     * @return void
     */
    function __construct($position_id, $store_id = 0)
    {
        $this->AdPosition($position_id, $store_id);
    }

    /**
     * ���캯��
     *
     * @access  public
     * @param   int article_id
     *
     * @return void
     */
    function AdPosition($position_id, $store_id = 0)
    {
        $this->_id = $position_id;
        $this->_store_id = $store_id;
    }

    /**
    * ��ȡ���
    *
    * @access  public
    * @param bool $is_edit_mode
    *
    * @return void
    */
    function get_ads($is_edit_mode = false)
    {
        $info = $this->get_info();
        if ($is_edit_mode)
        {
            return "<div id=\"adp_$this->_id\" class=\"ECM_adposition\" style='width:$info[width]px;height:$info[height]px;background:#ccc;'><div class=\"posname\">$info[position_name]</div></div>";
        }
        else
        {
            $ad = new Ad(null, $this->_id);
            $html = "<div id=\"adp_$this->_id\" class=\"ECM_adposition\" style=\"width:$info[width]px;height:$info[height]px;overflow:hidden;\">" . $ad->get_code() . "</div>";
            return $html;
        }
    }

}
?>