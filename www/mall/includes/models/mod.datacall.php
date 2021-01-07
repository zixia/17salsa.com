<?php

/**
 * ECMALL: ���ݵ���ģ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.datacall.php 5239 2008-07-15 06:28:35Z Liupeng $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.base.php');

class DataCall extends Model
{
    var $_table = '`ecm_data_call`';
    var $_key   = 'id';

    /**
     * ���캯��
     *
     * @access  public
     * @param   int article_id
     *
     * @return void
     */
    function __construct ($id)
    {
        $this->DataCall($id, $position_id);
    }

    /**
     * ���캯��
     *
     * @param   int $id
     *
     * @return void
     */
    function DataCall($id)
    {
        $this->_id = $id;
    }

    /**
    * ��ȡ���ݵ�����Ϣ
    *
    * @author liupeng
    *
    * @return void
    */
   function get_info()
   {
        $sql = "SELECT dc.*,brand_name FROM `ecm_data_call` AS dc " .
                "LEFT JOIN `ecm_brand` AS b ON b.brand_id = dc.brand_id ".
                "WHERE id = '$this->_id'";

        $info = $GLOBALS['db']->getRow($sql);

        if ($info)
        {
            $info['template'] = @unserialize($info['template']);
            $info['recommend_option'] = split(',', $info['recommend_option']);
        }

        return $info;
   }

}

?>