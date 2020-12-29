<?php

/**
 * ECMALL: 数据调用模型
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 构造函数
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
     * 构造函数
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
    * 获取数据调用信息
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