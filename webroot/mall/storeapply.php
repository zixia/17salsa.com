<?php

/**
 * ECMALL: 开店申请页面
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: storeapply.php 6063 2008-11-13 10:18:48Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreApplyController extends ControllerFrontend
{
    var $_allowed_actions = array('apply', 'check_store_name', 'check_custom');
    function __construct($act)
    {
        $this->StoreApplyController($act);
    }
    function StoreApplyController ($act)
    {
        if (empty($act))
        {
            $act = 'apply';
        }
        parent::__construct($act);
    }

    /**
     * 申请开店
     *
     * @author  wj
     */
    function apply()
    {
        /* 站长是否允许申请开店 */
        if (!$this->conf('mall_storeapply'))
        {
            $this->show_warning('storeapply_deny');
            return;
        }

        /* 是否登陆 */
        if (empty($_SESSION['user_id']))
        {
            $this->redirect("index.php?app=member&act=login&ret_url=" . urlencode("index.php?app=storeapply&just_login"));
        }

        /* 检查用户是否已经是管理员（网站或店铺的） */
        include_once(ROOT_PATH . '/includes/models/mod.adminuser.php');
        $mod_adminuser = new AdminUser($_SESSION['user_id']);
        $adminuser_info = $mod_adminuser->get_info();
        if ($adminuser_info)
        {
            if (isset($_GET['just_login']))
            {
                /* 如果是刚登录，跳转到首页 */
                $this->redirect("index.php");
            }
            else
            {
                $this->show_warning('u_cannt_setup_shop');
                return;
            }
        }

        /* 检查该用户是否已经提交过未处理的申请 */
        include_once(ROOT_PATH . '/includes/manager/mng.storeapply.php');
        $manager = new StoreApplyManager();
        if (($num = $manager->get_count(array('user_id'=>$_SESSION['user_id'], 'status'=>APPLY_RAW))) > 0)
        {
            $this->show_warning('u_had_applied', 'back_home', 'index.php');
            return;
        }
        else
        {
            if ($_SERVER['REQUEST_METHOD'] == 'GET')
            {
                /* 申请开店的界面 */
                $this->display('mc_storeapply', 'mall');
            }
            else
            {
                $arr = $this->filter($_POST);
                if (empty($arr['owner_name']) || empty($arr['owner_idcard']) || empty($arr['owner_phone']) || empty($arr['owner_address']) ||
                    empty($arr['owner_zipcode']) || empty($arr['store_name']) || empty($arr['store_location']) || empty($arr['apply_reason']))
                {
                    $this->show_warning('');
                    return;
                }

                if (!empty($_FILES['paper_image']['tmp_name']))
                {
                    include_once(ROOT_PATH. '/includes/cls.uploader.php');
                    $uploader = new Uploader('data/images/', 'image', $this->conf('mall_max_file'));

                    $res = $uploader->upload_files($_FILES['paper_image']);
                    if ($res === true)
                    {
                        $arr['paper_image'] = $uploader->success_list[0]['target'];
                    }
                    else
                    {
                        $this->show_warning($uploader->err);
                        return;
                    }
                }
                if ($this->conf('mall_need_paper') && empty($arr['paper_image']))
                {
                    $this->show_warning('upload_paper_pls');
                    return;
                }

                $id = $manager->add($arr);
                include_once(ROOT_PATH . '/includes/manager/mng.admin.php');
                $mng_admin = new AdminManager(0);
                $admin_id = $mng_admin->get_owner_id();
                uc_call('uc_pm_send', array(0, $admin_id, $this->lang('storeapply_notice'), $this->lang('new_storeapply_arrived')));

                $this->show_message('apply_setup_shop_success', 'back_home', 'index.php');
            }
        }
    }

    /**
     * 检查店铺名称是否存在
     *
     * @author  weberliu
     * @return  void
     */
    function check_store_name()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $manager = new StoreManager();
        $id = $manager->get_store_id($_POST['store_name']);

        if ($id === 0)
        {
            $this->json_result();
        }
        else
        {
            $this->json_error('store_exists');
        }
    }

    /**
     * 检查店铺二级域名是否重名
     *
     * @author  wj
     * @param   void
     * @param   void
     */
    function check_custom()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        if (StoreManager::exists_custom($_POST['custom']))
        {
            $this->json_error($this->lang('custom_exists'));
        }
        else
        {
            $this->json_result(1);
        }
    }

    /**
     * 过滤提交的数据
     *
     * @author  weberliu
     * @param   array       $post   POST提交的数据
     * @return  array
     */
    function filter($post)
    {
        $arr = array();
        $arr['owner_name']      = trim($post['owner_name']);
        $arr['owner_idcard']    = trim($post['owner_idcard']);
        $arr['owner_phone']     = trim($post['owner_phone']);
        $arr['owner_address']   = trim($post['owner_address']);
        $arr['owner_zipcode']   = trim($post['owner_zipcode']);
        $arr['apply_reason']    = trim($post['apply_reason']);
        $arr['store_name']      = trim($post['store_name']);
        $arr['store_location']  = trim($post['store_location']);
        $arr['custom']          = trim($post['custom']);
        $arr['user_id']         = $_SESSION['user_id'];

        return $arr;
    }

};

?>