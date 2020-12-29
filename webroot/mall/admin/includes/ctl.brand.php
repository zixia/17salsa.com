<?php

/**
 * ECMALL: ��վƷ�ƹ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctl.brand.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
include_once(ROOT_PATH . '/includes/models/mod.brand.php');

class BrandController extends ControllerBackend
{
    var $_mng = null;

    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->BrandController($act);
    }

    function BrandController($act)
    {
        $this->_mng = new BrandManager($_SESSION['store_id']);

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

        /* ���� */
        $keywords = empty($_GET['keywords']) ? '' : trim($_GET['keywords']);

        /* ȡ���б� */
        $list = $this->_mng->get_list($this->get_page(), array('keywords' => $keywords));
        $this->assign('list', $list);

        /* ͳ����Ϣ */
        $this->assign('stats', $this->str_format('brand_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* ��ʾģ�� */

        $this->assign('store_id', $_SESSION['store_id']);
        $this->display('brand.view.html');
    }

    /**
     * ajax�༭
     */
    function modify()
    {
        /* ���� */
        $field_name  = trim($_GET['column']);
        if (!in_array($field_name, array('brand_name', 'website', 'is_promote')))
        {
            $this->json_error('Hacking Attemp: field_name');
            return;
        }

        /* ��������Ƿ��ظ� */
        if ($field_name == 'brand_name')
        {
            $id = intval($_GET['id']);
            $value = trim($_GET['value']);
            $brand_id = $this->_mng->get_id($value);
            if ($brand_id > 0 && $brand_id != $id)
            {
                $this->json_error('brand_name_exist');
                return;
            }
        }

        $this->_modify('Brand', $_GET);
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
            $this->show_warning('no_brand_selected');
            return;
        }

        /* ����param����Ӧ���� */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), $this->lang('back_brands'), 'admin.php?app=brand&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_brand_deleted');
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
     * ���Ʒ��
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ��ʼ�� */
            $brand = array(
                'brand_id' => 0,
                'website'  => 'http://'
            );
            $this->assign('brand', $brand);

            $this->display('brand.detail.html');
        }
        else
        {
            /* ���� */
            $brand = array(
                'store_id'    => $_SESSION['store_id'],
                'if_show'     => 1,
                'goods_count' => 0
            );

            $brand['brand_name'] = empty($_POST['brand_name']) ? '' : trim($_POST['brand_name']);
            if (empty($brand['brand_name']))
            {
                $this->show_warning('brand_name_required');
                return;
            }

            if ($this->_mng->get_id($brand['brand_name']) > 0)
            {
                $this->show_warning('brand_name_exist');
                return;
            }

//            $brand['website'] = empty($_POST['website']) ? '' : trim($_POST['website']);

            if (!empty($_FILES['brand_logo']['name']))
            {
                include_once(ROOT_PATH . '/includes/cls.uploader.php');
                $uploader = new Uploader('data/user_files/brand', 'image', $this->conf('mall_max_file'));
                if ($uploader->upload_files($_FILES['brand_logo']))
                {
                    $brand['brand_logo'] = $uploader->success_list[0]['target'];
                }
                else
                {
                    $this->show_warning($uploader->err);
                    return;
                }
            }

            /* ���� */
            $this->log_item = $this->_mng->add($brand);
            // todo: ��������
            $this->show_message('add_ok',
                'continue_add', 'admin.php?app=brand&act=add',
                'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * �༭Ʒ��
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ���� */
            $brand_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($brand_id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new Brand($brand_id, $_SESSION['store_id']);
            $brand = $mod->get_info();
            if (empty($brand))
            {
                $this->show_warning('record_not_exist');
                return;
            }
/*
            if (empty($brand['website']))
            {
                $brand['website'] = 'http://';
            }
*/
            $this->assign('brand', $brand);

            $this->display('brand.detail.html');
        }
        else
        {
            /* ���� */
            $brand = array(
                'store_id'    => $_SESSION['store_id'],
                'if_show'     => 1,
                'goods_count' => 0
            );

            $brand['brand_id'] = empty($_POST['brand_id']) ? 0 : intval($_POST['brand_id']);
            $mod = new Brand($brand['brand_id'], $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $brand['brand_name'] = empty($_POST['brand_name']) ? '' : trim($_POST['brand_name']);
            if (empty($brand['brand_name']))
            {
                $this->show_warning('brand_name_required');
                return;
            }

            $brand_id = $this->_mng->get_id($brand['brand_name']);
            if ($brand_id > 0 && $brand_id != $brand['brand_id'])
            {
                $this->show_warning('brand_name_exist');
                return;
            }

//            $brand['website'] = empty($_POST['website']) ? '' : trim($_POST['website']);

            if (!empty($_FILES['brand_logo']['name']))
            {
                include_once(ROOT_PATH . '/includes/cls.uploader.php');
                $uploader = new Uploader('data/user_files/brand', 'image', $this->conf('mall_max_file'));
                if ($uploader->upload_files($_FILES['brand_logo']))
                {
                    $brand['brand_logo'] = $uploader->success_list[0]['target'];
                }
                else
                {
                    $this->show_warning($uploader->err);
                    return;
                }
            }

            // ɾ��ԭ����ͼƬ
            if (isset($brand['brand_logo']) && !empty($info['brand_logo']) && is_file(ROOT_PATH . '/' . $info['brand_logo']))
            {
                @unlink(ROOT_PATH . '/' . $info['brand_logo']);
            }

            /* ���� */
            $mod->update($brand);
            $this->log_item = $brand['brand_id'];
            // todo: ��������
            $this->show_message('edit_ok', 'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * �ж������Ƿ��ظ�
     */
    function duplicate_name()
    {
        /* ���� */
        $brand_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $brand_name = empty($_GET['name']) ? '' : trim($_GET['name']);
        if ($brand_name == '')
        {
            $this->json_error('brand_name_required');
            return;
        }

        /* ��ѯ */
        $id = $this->_mng->get_id($brand_name);
        if ($id > 0 && $id != $brand_id)
        {
            $this->json_error('brand_name_exist');
            return;
        }
        else
        {
            $this->json_result();
            return;
        }
    }

    /**
     * ɾ��Ʒ��
     */
    function drop()
    {
        /* ���� */
        $brand_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new Brand($brand_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }
        if ($info['goods_count'] > 0)
        {
            $this->show_warning('cannot_drop');
            return;
        }

        /* ɾ�� */
        if ($mod->drop())
        {
            $this->log_item = $brand_id;
            $this->show_message('drop_ok', 'back_brands', 'admin.php?app=brand&act=view');
            return;
        }
    }

    /**
     * ����Ʒ������
     *
     * @author liupeng
     * @return void
     **/
    function update_goods_count()
    {
       $manager = new BrandManager($_SESSION['store_id']);
       $manager->update_goods_count();
       $this->json_result('', 'ok');
    }
}

?>