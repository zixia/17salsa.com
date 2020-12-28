<?php

/**
 * ECMALL: 友情链接控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.partner.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.partner.php');
include_once(ROOT_PATH . '/includes/models/mod.partner.php');
include_once(ROOT_PATH . '/includes/cls.validator.php');

class PartnerController extends ControllerBackend
{
    var $_manager = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->PartnerController($act);
    }

    function PartnerController($act)
    {
        $this->_manager = new PartnerManager($_SESSION['store_id']);

        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看列表
     */
    function view()
    {
        $this->logger=false;
        $condition = isset($_GET['keywords']) ? array('partner_name' => trim($_GET['keywords'])) : array();
        $list = $this->_manager->get_list($this->get_page(), $condition);

        $this->assign('list', $list);
        $this->assign('stats', $this->str_format('partner_stats', $list['info']['rec_count'], $list['info']['page_count']));
        $this->display('partner.view.html');
    }

    /**
     * AJAX更新链接
     *
     * @author liupeng
     * @return void
     */
    function modify()
    {
        $idx = intval($_GET['id']);
        $col = trim($_GET['column']);
        $val = trim($_GET['value']);
        $this->log_item = $idx;

        if ($col == 'partner_website' && !Validator::is_url($val))
        {
            $this->json_error('not_url');
            return;
        }
        if ($col == 'partner_name' && $this->_manager->get_partner_id($val, $idx))
        {
            $this->json_error($this->str_format("partner_exists", $val));
            return;
        }

        $this->_modify('Partner', $_GET, 'edit_partner_failed');
    }

    /**
     * 添加链接
     *
     * @author liupeng
     * @return void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $partner = array('partner_website' => 'http://', 'sort_order' => 0);

            $this->assign('partner', $partner);
            $this->display('partner.detail.html');
        }
        else
        {
            $post = $this->_post_filter($_POST);

            if(is_string($post))
            {
                $this->show_warning($post);
                return;
            }

            if ($this->_manager->get_partner_id($post['partner_name']) > 0)
            {
                $this->show_warning($this->str_format('partner_exists', stripslashes($post['partner_name'])));
                return;
            }

            if (!Validator::is_url($post['partner_website']))
            {
                $this->show_warning("not_url");
                return;
            }

            if ($this->_manager->add($post))
            {
                $this->log_item = $GLOBALS['db']->insert_id();
                $this->show_message('add_partner_successful', 'back_list', 'admin.php?app=partner&amp;act=view', 'go_back', 'admin.php?app=partner&amp;act=add');
                return;
            }
            else
            {
                $this->show_warning('add_partner_failed');
                return;
            }
        }
    }

    /**
     * 编辑链接
     *
     * @author liupeng
     * @return void
     */
    function edit()
    {
        $parent_id  = intval($_GET['partner_id']);
        $partner    = new Partner($parent_id, $_SESSION['store_id']);
        $info       = $partner->get_info();
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->assign('partner', $info);
            $this->display('partner.detail.html');
        }
        else
        {
            $post = $this->_post_filter($_POST);
            if (is_string($post))
            {
                $this->show_warning($post);
                return;
            }
            if ($this->_manager->get_partner_id($post['partner_name'], $parent_id) > 0)
            {
                $this->show_warning($this->str_format('partner_exists', stripslashes($post['partner_name'])));
                return;
            }

            if (!Validator::is_url($post['partner_website']))
            {
                $this->show_warning("not_url");
                return;
            }

            if (isset($post['partner_logo']) && is_file($info['partner_logo']))
            {
                @unlink($info['partner_logo']);
            }
            if ($partner->update($post))
            {
                $this->log_item = $post['partner_id'];
                $this->show_message('edit_partner_successful', 'back_list', 'admin.php?app=partner&amp;act=view', 'go_back');
            }
            else
            {
                $this->show_warning('edit_partner_failed');
                return;
            }
        }
    }
    /**
     * 判断名称是否重复
     */
    function duplicate_name()
    {
        $this->logger = false;
        $id = intval($_POST['partner_id']);
        $name = trim($_POST['partner_name']);

        $res = $this->_manager->get_partner_id($name);

        if ($res && $res != $id)
        {
            $msg = $this->str_format('partner_exists', $name);
            $this->json_error($msg);
            return;
        }
        else
        {
            $this->json_result();
            return;
        }
    }

    /**
     * 删除链接
     *
     * @author liupeng
     * @return void
     */
    function drop()
    {
        $this->log_item = $_GET['partner_id'];
        $mod = new Partner(intval($_GET['partner_id']), $_SESSION['store_id']);
        $mod->drop();
        $this->show_message('drop_partner_successful', 'back_list', 'admin.php?app=partner&amp;act=view');
        return;
    }

    /**
     * 用户提交的数据的过滤器
     *
     * @param  array    $post
     *
     * @return  array
     */
    function _post_filter($post)
    {
        $arr = array();
        $arr['partner_name']    = sub_str(trim($post['partner_name']), 120);
        $arr['partner_website'] = sub_str(trim($post['partner_website']), 255);
        $arr['sort_order']      = intval($post['sort_order']);

        if (!empty($_FILES['partner_logo_local']['tmp_name']))
        {
            include_once(ROOT_PATH. '/includes/cls.uploader.php');
            $uploader = new Uploader('data/images/partner_logo/', 'image', $this->conf('mall_max_file'));

            $res = $uploader->upload_files($_FILES['partner_logo_local']);
            if ($res === true)
            {
                $arr['partner_logo'] = $uploader->success_list[0]['target'];
            }
            else
            {
                return $uploader->err;
            }
        }

        return $arr;
    }

    /**
     * 批量处理
     * @author redstone
     *
     * @return boolean
     */
    function batch()
    {
        $ids = trim($_GET['ids']);
        if (empty($ids))
        {
            $this->show_warning('undefined');
            return;
        }
        include_once(ROOT_PATH . '/includes/models/mod.partner.php');
        $arr_id = explode(',', $ids);
        foreach ($arr_id as $key=>$val)
        {
            if ($val = intval(trim($val)))
            {
                $mod = new Partner($val, $_SESSION['store_id']);
                $mod->drop();
            }
        }
        $this->show_message('batch_drop_partner_success', 'back_list', 'admin.php?app=partner&amp;act=view');
        return true;
    }

    /**
     * 删除上传图片
     * @author liupeng
     *
     * @return void
     */
    function delete_picture()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $partner = new Partner($id, $_SESSION['store_id']);
        $info = $partner->get_info();

        if ($info)
        {
            $filename = ROOT_PATH . '/' .$info['partner_logo'];
            if (is_file($filename))
            {
                @unlink($filename);
            }

            $data['partner_logo'] = '';

            $partner->update($data);

            $this->json_result('', 'ok');
        }
    }

}

?>