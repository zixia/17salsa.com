<?php
/**
 * ECMall: ��̬���ݺ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: lib.insert.php 6009 2008-10-31 01:55:52Z Garbin $
 */


/**
 * ��ȡ���λ���
 *
 * @access  public
 *
 * @return  string
 */
function insert_ads($par)
{
    if (!class_exists(AdPosition))
    {
        require_once(ROOT_PATH . '/includes/models/mod.ad_position.php');
    }

    $adp = new AdPosition($par['id']);

    return $adp->get_ads($par['template']->edit_mode);
}

/**
 * ����ģ���е�{nocache}
 *
 * @author wj
 * @param  string $param
 * @return string
 */
function insert_nocache($param)
{
    error_reporting(E_ALL ^ E_NOTICE);
    $str = $param['template']->_eval(stripslashes($param['_template']));
    error_reporting($param['template']->_errorlevel);
    return $str;
}

?>