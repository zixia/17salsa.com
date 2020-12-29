<?php

/**
 * ECMALL: ����ѡ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: regions.php 6052 2008-11-13 03:24:36Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.region.php');

class RegionsController extends ControllerFrontend
{
    var $_mng = null;
    var $_allowed_actions = array('load', 'get', 'get_all_regions');

    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->RegionsController($act);
    }

    function RegionsController($act)
    {
        $this->_mng = new RegionManager(0);
        !method_exists($this, $act) && $act = 'load';
        parent::ControllerFrontend($act);
    }

    function load()
    {
        $this->logger = false; // ������־

        /* ���� */
        $pid   = empty($_GET['parent']) ? 0 : intval($_GET['parent']);
        $level = empty($_GET['level']) ?  0 : intval($_GET['level']);

        /* ȡ�õ��� */
        $arr = array(
            'regions' => ($pid > 0 || $level == 1) ? $this->_mng->get_list($pid) : array(),
            'level'   => $level
        );

        /* ���� */
        $this->json_result($arr);
        return;
    }

    /**
     *  ��ȡ��������
     *
     *  @access
     *  @param
     *  @return
     */

    function get()
    {
        $this->logger = false;
        $regions = $this->_mng->get_all();
        $js_data = 'Regions = ' . ecm_json_encode($regions) . ';';
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        echo $js_data;
    }
}

?>
