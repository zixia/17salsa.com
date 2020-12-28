<?php

/**
 * ECMALL: ��������Ϣ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: wanted.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class WantedController extends ControllerBackend
{
    function __construct($act)
    {
        $this->WantedController($act);
    }
    function WantedController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $this->_manager = new WantedManager();
        parent::__construct($act);
    }

    /**
     *    �б�����Ϣ
     *
     *    @author    Garbin
     *    @return    void
     */
    function view()
    {
        $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $conditions = array();
        if (isset($_GET['keywords']))
        {
            $conditions['keywords'] = trim($_GET['keywords']);
        }
        $wanted_list = $this->_manager->get_list($page, $conditions);
        $this->assign('list', $wanted_list);
        $this->assign('stats', $this->str_format('wanted_stats', $wanted_list['info']['rec_count']));
        $this->display('wanted.view', 'mall');
    }

    /**
     *    ɾ������Ϣ
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        $in = trim($_GET['ids']);
        $rows = $this->_manager->drop($in);
        if (!$rows)
        {
            $this->show_warning('no_records_to_be_drop', 'wanted_view', 'admin.php?app=wanted');

            return;
        }
        $this->clean_cache();
        $this->show_message('drop_success', 'wanted_view', 'admin.php?app=wanted');
    }
}

?>
