<?php

/**
 * ECMALL: ��Ʒ���Ϳ�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: goodstype.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.goodstype.php');
include_once(ROOT_PATH . '/includes/models/mod.goodstype.php');

class GoodsTypeController extends ControllerBackend
{
    var $_mng = null;

    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->GoodsTypeController($act);
    }

    function GoodsTypeController($act)
    {
        $this->_mng = new GoodsTypeManager($_SESSION['store_id']);

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
        $this->assign('stats', $this->str_format('goodstype_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* ��ʾģ�� */
        $this->display('mall/goods_type.view.html');
    }

    /**
     * ajax�༭
     */
    function modify()
    {
        /* ���� */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('type_name')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        /* ��������Ƿ��ظ� */
        $id = intval($_GET['id']);
        $value = trim($_GET['value']);
        $this->log_item = $id;
        $type_id = $this->_mng->get_id($value);
        if ($type_id != $id && $type_id != 0)
        {
            $this->json_error('name_exist');
            return;
        }

        $this->_modify('GoodsType', $_GET);
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
            $this->show_warning('no_goodstype_selected');
            return;
        }
        $this->log_item = $ids;

        /* ����param����Ӧ���� */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), 'back_goodstype', 'admin.php?app=goodstype&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_goodstype_deleted');
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
     * ɾ����Ʒ����
     */
    function drop()
    {
        /* ���� */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new GoodsType($id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        if ($info['attr_count'] > 0)
        {
            $this->show_message('drop_not_allowed');
            return;
        }
        $this->log_item = $id;
        /* ɾ�� */
        if ($mod->drop())
        {
            $this->log_item = $id;
            $this->show_message('drop_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
        }
    }

    /**
     * ���
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            /* ���� */
            $info = array(
                'store_id'    => $_SESSION['store_id']
            );

            $info['type_name'] = empty($_POST['type_name']) ? '' : trim($_POST['type_name']);
            if (empty($info['type_name']))
            {
                $this->show_warning('name_required');
                return;
            }

            if ($this->_mng->get_id($info['type_name']) > 0)
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $this->log_item = $this->_mng->add($info);

            $this->show_message('add_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
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
            $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new GoodsType($id, $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $this->assign('info', $info);

            $this->display('mall/goods_type.detail.html');
        }
        else
        {
            /* ���� */
            $info = array(
                'store_id'    => $_SESSION['store_id']
            );

            $info['type_id'] = empty($_POST['id']) ? 0 : intval($_POST['id']);
            if ($info['type_id'] <= 0)
            {
                $this->show_warning('Hacking Attempt: id');
                return;
            }

            $mod = new GoodsType($info['type_id'], $_SESSION['store_id']);
            $arr = $mod->get_info();
            if (empty($arr))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $info['type_name'] = empty($_POST['type_name']) ? '' : trim($_POST['type_name']);
            if (empty($info['type_name']))
            {
                $this->show_warning('name_required');
                return;
            }

            $type_id = $this->_mng->get_id($info['type_name']);
            if ($type_id != 0 && $type_id != $info['type_id'])
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $mod->update($info);
            $this->log_item = $info['type_id'];

            $this->show_message('edit_ok', 'back_goodstype', 'admin.php?app=goodstype&act=view');
            return;
        }
    }

    /**
     * �ж������Ƿ��ظ�
     */
    function duplicate_name()
    {
        /* ���� */
        $id   = empty($_POST['id']) ? 0 : intval($_POST['id']);
        $name = empty($_POST['name']) ? '' : trim($_POST['name']);
        if ($name == '')
        {
            $this->json_error('name_required');
            return;
        }

        /* ��ѯ */
        $type_id = $this->_mng->get_id($name);
        if ($type_id != $id && $type_id != 0)
        {
            $this->json_error('name_exist');
            return;
        }
        else
        {
            $this->json_result();
            return;
        }
    }
}

?>