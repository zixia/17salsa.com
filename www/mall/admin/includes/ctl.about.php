<?php

/**
 * ECMALL: ��ʾ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctl.about.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class AboutController extends ControllerBackend
{
    function __construct($act)
    {
        $this->AboutController($act);
    }

    function AboutController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     *  ����
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function view()
    {
        $this->logger = false; //����¼��־
        $this->display('about.html', 'mall');
    }
};
?>