<?php

/**
 * ECMALL: ��귽�����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: rentscheme.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.rentscheme.php');
include_once(ROOT_PATH . '/includes/models/mod.rentscheme.php');

class RentSchemeController extends ControllerBackend
{
    var $_mng = null;

    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->RentSchemeController($act);
    }

    function RentSchemeController($act)
    {
        $this->_mng = new RentSchemeManager();

        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �鿴�б�
     */
    function view()
    {
        $this->logger = false; // ������־

        /* ȡ���б� */
        $list = $this->_mng->get_list($this->get_page());
        $this->assign('list', $list);

        /* ͳ����Ϣ */
        $this->assign('stats', $this->str_format('rentscheme_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* ��ʾģ�� */
        $this->display('rentscheme.view.html', 'mall');
    }

    /**
     * ajax�༭
     */
    function modify()
    {
        /* ���� */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('allowed_goods', 'allowed_file', 'allowed_month', 'price')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }
        if (($field_name == 'allowed_goods' || $field_name == 'allowed_file') && intval($_GET['value']) < 0)
        {
            $this->json_error('allowed_goods_note');
            return;
        }
        if ($field_name == 'allowed_month' && intval($_GET['value']) < 1)
        {
            $this->json_error('allowed_month_note');
            return;
        }
        if ($field_name == 'price' && floatval($_GET['value']) < 1)
        {
            $this->json_error('price_note');
            return;
        }

        $this->_modify('RentScheme', $_GET);
    }

    /**
     * ��������
     */
    function batch()
    {
        /* ���� */
        $param = empty($_GET['param']) ? '' : trim($_GET['param']);
        $ids   = empty($_GET['ids']) ? '' : trim($_GET['ids']);

        if (empty($ids))
        {
            $this->show_warning('no_rentscheme_selected');
            return;
        }

        /* ����param����Ӧ���� */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), $this->lang('back_rentschemes'), 'admin.php?app=rentscheme&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_rentscheme_deleted');
                return;
            }
        }
        else
        {
            $this->show_warning('Hacking Attemp');
            return;
        }
    }

    /**
     * ���
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ��ʼ�� */
            $rentscheme = array();
            $this->assign('rentscheme', $rentscheme);

            $this->display('rentscheme.detail.html', 'mall');
        }
        else
        {
            /* ���� */
            $rentscheme = array(
                'allowed_goods'     => max(0, intval($_POST['allowed_goods'])),
                'allowed_file'      => max(0, intval($_POST['allowed_file'])),
                'allowed_month'     => max(1, intval($_POST['allowed_month'])),
                'price'             => max(1, round(floatval($_POST['price']), 2)),
            );

            /* ���� */
            $this->log_item = $this->_mng->add($rentscheme);
            $this->show_message('add_ok',
                'continue_add', 'admin.php?app=rentscheme&act=add',
                'back_rentschemes', 'admin.php?app=rentscheme&act=view');
            return;
        }
    }

    /**
     * �༭
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ���� */
            $scheme_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($scheme_id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new RentScheme($scheme_id);
            $rentscheme = $mod->get_info();
            if (empty($rentscheme))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $this->assign('rentscheme', $rentscheme);
            $this->display('rentscheme.detail.html', 'mall');
        }
        else
        {
            /* ���� */
            $rentscheme = array(
                'allowed_goods'     => max(0, intval($_POST['allowed_goods'])),
                'allowed_file'      => max(0, intval($_POST['allowed_file'])),
                'allowed_month'     => max(1, intval($_POST['allowed_month'])),
                'price'             => max(1, round(floatval($_POST['price']), 2)),
            );

            $rentscheme['scheme_id'] = empty($_POST['scheme_id']) ? 0 : intval($_POST['scheme_id']);
            $mod = new RentScheme($rentscheme['scheme_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            /* ���� */
            $mod->update($rentscheme);
            $this->log_item = $rentscheme['scheme_id'];
            $this->show_message('edit_ok', 'back_rentschemes', 'admin.php?app=rentscheme&act=view');
            return;
        }
    }

    /**
     * ɾ��
     */
    function drop()
    {
        /* ���� */
        $scheme_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new RentScheme($scheme_id);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* ɾ�� */
        if ($mod->drop())
        {
            $this->log_item = $scheme_id;
            $this->show_message('drop_ok', 'back_rentschemes', 'admin.php?app=rentscheme&act=view');
            return;
        }
    }
}

?>