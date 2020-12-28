<?php

/**
 * ECMALL: ��洦�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ads.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/models/mod.ad.php');

class AdsController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'jump');

    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->AdsController($act);
    }

    function AdsController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * ��ʾ���
     *
     * @return  void
     */
    function view()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $ad = new Ad($id);

        $info = $ad->get_info();
        $code = '';
        if ($info['position_id'] == 0)
        {
            $code .= "document.write(\"";
            $code .= addcslashes($ad->get_code(), "\"");
            $code .= "\");";

            $code = ecm_iconv(CHARSET, $_GET['encoding'], $code);
        }

        echo $code;
    }

    /**
     * �����ת����
     *
     * @return  void
     */
    function jump()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $ad = new Ad($id);
        $ad->click_count();
        $this->redirect($_GET['url']);
    }
}
?>