<?php

/**
 * ECMALL: �������������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: nav.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.nav.php');
require(ROOT_PATH . '/includes/models/mod.nav.php');

class NavController extends ControllerBackend
{
    var $manager = null;
    var $mod = null;

    function __construct($act)
    {
        $this->NavController($act);
    }

    function NavController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }

        $this->manager = new NavManager();
        if (!empty($_GET['id']) && intval($_GET['id']))
        {
            $this->mod = new Nav($_GET['id']);
        }

        parent::__construct($act);
    }

    /**
     * �鿴������
     * @author  wj
     * @param void
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $condition = array();
        $self_app_list = $this->manager->get_app_list();
        $uc_app_list = uc_call('uc_app_ls', array());

        /* ͬ��,������,���,ɾ�� */
        foreach ($uc_app_list as $app)
        {
            if ($app['type'] == 'ECMALL')
            {
                continue;
            }
            if (isset($self_app_list[$app['appid']]))
            {
                if ($self_app_list[$app['appid']]['nav_url'] != $app['url'])
                {
                    $mod_nav = new Nav($self_app_list[$app['appid']]['nav_id']);
                    $mod_nav->update(array('nav_url'=>$app['url']));
                }
                unset($self_app_list[$app['appid']]);
            }
            else
            {
                    $nav_info['store_id']       = 0;
                    $nav_info['nav_name']       = $app['name'];
                    $nav_info['nav_url']        = $app['url'];
                    $nav_info['nav_position']   = 'middle';
                    $nav_info['if_show']        = 1;
                    $nav_info['sort_order']     = 0;
                    $nav_info['open_new']       = 1;
                    $nav_info['is_app']         = $app['appid'];
                    $this->manager->add($nav_info);
            }
        }
        foreach ($self_app_list as $app)
        {
            $mod_nav = new Nav($app['nav_id']);
            $mod_nav->drop();
        }
        $list = $this->manager->get_list();
        $this->assign('list',   $list);
        $this->display('nav.view.html', 'mall');
    }

    /**
     * ��ӵ���
     *
     * @return  void
     */
    function add()
    {
        $this->process_nav('add');
    }

    /**
     * �����ύ����������
     * @param array $nav POST����������
     *
     * @return array $nav ����������
     */
    function process_data($nav)
    {
        $nav['nav_name'] = trim($nav['nav_name']);
        $nav['nav_url'] = trim($nav['nav_url']);
        $nav['sort_order'] = trim($nav['sort_order']);
        $nav['if_show'] = empty($nav['if_show']) ? 0 : 1;
        $nav['open_new'] = empty($nav['open_new']) ? 0 : 1;
        $nav['cate_id'] = empty($nav['nav_type']) ? 0 : intval($nav['cate_id']);
        $nav['nav_position'] = in_array($nav['nav_position'], array('top', 'middle', 'bottom')) ? $nav['nav_position'] : 'top';
        if (empty($nav['nav_name']))
        {
            $this->show_warning('nav_name_empty');
            return false;
        }
        if (empty($nav['nav_url']))
        {
            $this->show_warning('nav_url_empty');
            return false;
        }
        $nav['cate_id'] = intval($nav['cate_id']);
        $nav['sort_order'] = empty($nav['sort_order']) ? 0 : intval($nav['sort_order']);
        return $nav;
    }

    /**
     * Ajax��ʽ�޵�������Ϣ
     *
     * @return  void
     */
    function modify()
    {
        $this->_modify("Nav", $_GET, 'error');
        $this->clean_cache();
    }

    /**
     * �༭������Ϣ
     *
     * @return  void
     */
    function edit()
    {
        $this->process_nav('update');
    }

    /**
     * ����༭�������
     * @param string act (add or update)
     *
     * @return boolean
     */
    function process_nav($act)
    {
        if (empty($_POST['value_submit']))
        {
            $this->logger = false; // ����¼��־
            if ($act == 'update')
            {
                $nav = $this->mod ? $this->mod->get_info() : array();
            }
            else
            {
                $nav['open_new'] = 1;
                $nav['if_show'] = 1;
                $nav['nav_position'] = 'top';
            }
            require_once(ROOT_PATH . '/includes/models/mod.category.php');
            $cate_mod = new Category(0, 0);
            $cate_list = $cate_mod->get_options();
            $this->assign('cate_list', $cate_list);
            $this->assign('nav', $nav);
            $this->display('nav.detail.html', 'mall');
        }
        else
        {
            if ($act == 'update' && empty($this->mod))
            {
                $this->show_warning('undefined');
                return false;
            }
            if (!$nav = $this->process_data($_POST['nav']))
            {
                return false;
            }
            $res = $act == 'add' ? $this->manager->add($nav) : $this->mod->update($nav);
            if ($res)
            {
                $this->show_message('operate_success', 'back_to_list', 'admin.php?app=nav&act=view');
                $this->clean_cache();
                return true;
            }
            else
            {
                $error = $act == 'add' ? $this->manager->err : $this->mod->err;
                $this->show_warning($error, 'back_to_list', 'admin.php?app=nav&act=add');
                return false;
            }
        }
    }

    /**
     * ɾ��һ������
     *
     * @return boolean
     */
    function drop()
    {

        if ($this->mod && $this->mod->drop())
        {
            $this->show_message('operate_success', 'back_to_list', 'admin.php?app=nav&act=view');
            $this->clean_cache();
            return true;
        }
        else
        {
            $this->show_warning($this->mod->err);
            return false;
        }
    }

}
?>