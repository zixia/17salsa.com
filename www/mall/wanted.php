<?php

/**
 * ECMALL: 求购
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: wanted.php 6155 2008-12-19 02:18:53Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class WantedController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'add', 'reply', 'edit', 'show');

    /**
     * 构造函数
     */
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
        parent::__construct($act);
    }

    /**
     *    列表
     *
     *    @author:    Garbin
     *    @return:    void
     */
    function view()
    {
        $conditions = array();
        if (isset($_GET['cate_id']))
        {
            if ($_GET['cate_id'] != 'other')
            {
                if ($_GET['cate_id'] != 0)
                {
                    $conditions['cate_id'] = intval($_GET['cate_id']);
                }
            }
            else
            {
                $conditions['cate_id'] = 0;
            }
        }
        if (isset($_GET['keywords']) && $_GET['keywords'])
        {
            $conditions['keywords'] = trim($_GET['keywords']);
        }
        if (isset($_GET['region_id']) && $_GET['region_id'])
        {
            $conditions['region_id'] = intval($_GET['region_id']);
        }
        if (isset($_GET['my_post']) && $_GET['my_post'])
        {
            $conditions['user_id'] = $_SESSION['user_id'];
        }
        if (isset($_GET['valid_only']) && $_GET['valid_only'])
        {
            $conditions['valid'] = true;
        }
        if (!isset($conditions['user_id']) && isset($_GET['user_id']) && $_GET['user_id'])
        {
            $conditions['user_id'] = intval($_GET['user_id']);
        }
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;


        $url = 'index.php?app=wanted&user_id=' . $conditions['user_id'] . '&keywords=' . $conditions['keywords'] . '&my_post=' . $conditions['my_post'] . '&region_id=' . $conditions['region_id'] . '&cate_id=' . $conditions['cate_id'] . '&';
        $sort_list = array(
            $url . 'sort=expiry&order=desc'       => $this->lang('wanted_expiry_desc'),
            $url . 'sort=expiry&order=asc'        => $this->lang('wanted_expiry_asc'),
        );
        $this->assign('sort_list', $sort_list);
        $this->assign('curr_sort', htmlspecialchars($url . 'sort=' . $_GET['sort'] . '&order=' . $_GET['order']));

        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $manager = new WantedManager();
        $this->_assign_categories();
        $this->_assign_regions_list();
        $this->assign('location_data', array(array('name' => $this->lang('nav_wanted'))));
        $this->assign('url_format', "index.php?app=wanted&act=view&cate_id={$conditions['cate_id']}&region_id={$conditions['region_id']}&keywords={$conditions['keywords']}&my_post={$_GET['my_post']}" );
        $this->assign('conditions', $conditions);
        $this->assign('wanted', $manager->get_list($page, $conditions));
        $this->display('wanted_list', 'mall');
    }

    /**
     *    查看求购详情
     *
     *    @author:    Garbin
     *    @return:    void
     */
    function show()
    {
        $id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id)
        {
            $this->show_warning('no_this_wanted');

            return false;
        }

        include_once(ROOT_PATH . '/includes/models/mod.wanted.php');
        $wanted = new Wanted($id);
        $info   = $wanted->get_info(true);
        if (empty($info))
        {
            $this->show_warning('no_this_wanted', 'back_to_wanted', 'index.php?app=wanted&amp;act=view');

            return;
        }

        $this->_assign_user_data($info['user_id']);
        $this->_assign_other_wanted($info['user_id']);

        $this->assign('location_data', array(array('name' => $this->lang('nav_wanted'), 'url' => 'index.php?app=wanted'),
                                             array('name' => $this->lang('view_wanted'))));
        $this->assign('is_expire', ($info['expiry'] < gmtime()));
        $this->assign('login_require', $this->str_format('login_to_reply', 'index.php?app=member&amp;act=login&amp;ret_url=' . urlencode('index.php?app=wanted&amp;act=show&amp;id=' . $info['log_id'])));
        $this->assign('info', $info);
        $this->set_title(array($info['subject']));
        $this->display('wanted_detail', 'mall');
    }

    /**
     * 添加求购
     *
     * @author  Garbin
     * @return  void
     */
    function add()
    {
        if (!$this->_check_login())
        {
            return false;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->_assign_categories();
            $this->_assign_regions_list();
            /* 申请开店的界面 */
            $this->display('mc_post_wanted', 'mall');
        }
        else
        {
            $info = array();
            if (!$_POST['expiry'])
            {
                $this->show_warning('wanted_expiry_required');

                return;
            }
            if (!$this->_valid_post())
            {
                return;
            }
            $info['region_id'] = $_POST['region_id'];
            $info['expiry'] = $_POST['expiry'];
            $info['subject'] = $_POST['subject'];
            $info['detail'] = $_POST['detail'];
            $info['cate_id'] = $_POST['cate_id'];
            $info['user_id'] = $_SESSION['user_id'];
            $info['price_start'] = $_POST['price_start'];
            $info['price_end'] = $_POST['price_end'];

            /* 检查是否有敏感字符 */
            if (!$this->_check_badwords($info['subject']))
            {
                return false;
            }
            if (!$this->_check_badwords($info['detail']))
            {
                return false;
            }

            include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
            $manager = new WantedManager();
            $id = $manager->add($info);
            $this->clean_cache();

            $this->show_message('add_wanted_success', 'back_to_wanted' , 'index.php?app=wanted', 'back_home', 'index.php');
        }
    }

    /**
     *    回复
     *
     *    @author:    Garbin
     *    @return:    void
     */
    function reply()
    {
        if (!$this->_check_login())
        {
            return false;
        }

        /* 没有店铺就不让回复 */
        if (!isset($_SESSION['admin_store']) || $_SESSION['admin_store'] < 0)
        {
            $this->show_warning('store_admin_only');

            return false;
        }

        $id = empty($_POST['id']) ? 0 : intval($_POST['id']);
        if (!$id)
        {
            $this->show_warning('Hacking attempt');

            return false;
        }
        include_once(ROOT_PATH . '/includes/models/mod.wanted.php');
        $mod = new Wanted($id);
        $wanted_info = $mod->get_info();
        if (empty($wanted_info))
        {
            $this->show_warning('no_this_wanted');

            return false;
        }

        /* 过期了就不让回复 */
        if ($wanted_info['expiry'] <= gmtime())
        {
            $this->show_warning('wanted_expire');

            return false;
        }

        if ($wanted_info['user_id'] == $_SESSION['store_id'])
        {
            $this->show_warning('reply_self_disabled');

            return false;
        }

        $info = array();
        $info['user_id'] = $_SESSION['user_id'];
        $info['detail']  = trim($_POST['detail']);
        $info['goods_url'] = trim($_POST['goods_url']);

        /* 检查是否有敏感字符 */
        if (!$this->_check_badwords($info['goods_url']))
        {
            return false;
        }
        if (!$this->_check_badwords($info['detail']))
        {
            return false;
        }

        $mod->reply($info);

        $this->show_message('reply_success', 'back_wanted_info', 'index.php?app=wanted&act=show&id=' . $id, 'back_to_wanted', 'index.php?app=wanted');
    }

    /**
     *    编辑
     *
     *    @author:    Garbin
     *    @return:    void
     */
    function edit()
    {
        if (!$this->_check_login())
        {
            return false;
        }
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('illegal_request');

            return false;
        }
        if (!$this->_check_owner($id))
        {
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->_assign_categories();
            $this->_assign_regions_list();

            /* 申请开店的界面 */
            $this->display('mc_post_wanted', 'mall');
        }
        else
        {
            $info = array();
            if (isset($_POST['delay']) && $_POST['delay'])
            {
                $info['expiry'] = intval($_POST['delay']);
            }
            if(!$this->_valid_post())
            {
                return false;
            }
            $info['region_id'] = $_POST['region_id'];
            $info['subject'] = trim($_POST['subject']);
            $info['detail'] = trim($_POST['detail']);
            $info['cate_id'] = $_POST['cate_id'];
            $info['price_start'] = floatval($_POST['price_start']);
            $info['price_end']   = floatval($_POST['price_end']);
            if (!$this->_check_badwords($info['subject']))
            {
                return false;
            }
            if (!$this->_check_badwords($info['detail']))
            {
                return false;
            }
            include_once(ROOT_PATH . '/includes/models/mod.wanted.php');
            $manager = new Wanted($id);
            $manager->update($info);
            $this->clean_cache();

            $this->show_message('edit_wanted_success', 'back_wanted_view' , 'index.php?app=member&act=wanted_view', 'back_home', 'index.php');
        }
    }

    /**
     *    获取分类列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _assign_categories()
    {
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate_mod = new Category();
        $this->assign('wanted_cates', $cate_mod->get_options(1));
    }

    /**
     *    获取分类列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _assign_regions_list()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.region.php');
        $region_manager = new RegionManager();
        $this->assign('wanted_regions', $region_manager->get_regions_list());
    }

    /**
     *    赋值用户信息
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    void
     */
    function _assign_user_data($user_id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $mod_user = new User($user_id);
        $this->assign('seller_rate', $mod_user->get_eval_rate('seller'));
        $this->assign('buyer_rate', $mod_user->get_eval_rate('buyer'));
    }

    /**
     *    获取求购信息
     *
     *    @author    Garbin
     *    @param     int $id
     *    @return    void
     */
    function _assign_wanted_info($id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.wanted.php');
        $wanted = new Wanted($id);
        $info   = $wanted->get_info();
        if (empty($info))
        {
            $this->show_warning('no_this_wanted');

            return false;
        }

        $this->assign('wanted_info', $info);

        return $info;
    }

    /**
     *    获取我的其它求购
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    void
     */
    function _assign_other_wanted($user_id)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $manager = new WantedManager();
        $this->assign('my_other_wanted', $manager->get_list(1, array('user_id' => $user_id)));
    }

    /**
     *    验证输入
     *
     *    @author:    Garbin
     *    @return:    bool
     */
    function _valid_post()
    {
        if (!$_POST['region_id'])
        {
            $this->show_warning('wanted_location_required');

            return false;
        }
        if (!$_POST['subject'])
        {
            $this->show_warning('wanted_subject_required');

            return false;
        }
        if ($_POST['start'] > $_POST['end'])
        {
            $this->show_warning('invalid_price');

            return false;
        }
        if (!$_POST['detail'])
        {
            $this->show_warning('wanted_detail_required');

            return false;
        }

        return true;
    }

    /**
     *    检查是否登录
     *
     *    @author:    Garbin
     *    @param:     param
     *    @return:    void
     */
    function _check_login()
    {
        if (empty($_SESSION['user_id']))
        {
            $this->redirect("index.php?app=member&act=login&ret_url=" . urlencode("index.php?app=wanted&act={$_GET['act']}&id={$_GET['id']}"));

            return false;
        }

        return true;
    }

    /**
     *    检查是否是拥有者
     *
     *    @author    Garbin
     *    @param     int $id
     *    @return    bool
     */
    function _check_owner($id)
    {
        $info = $this->_assign_wanted_info($id);
        if (!$info)
        {
            $this->show_warning('no_this_wanted', 'back_to_wanted', 'index.php?app=wanted');

            return false;
        }
        if ($info['user_id'] != $_SESSION['user_id'])
        {
            $this->show_warning('Hacking attempt');

            return false;
        }

        return $info;
    }

    /**
     *    检查是否有敏感字符
     *
     *    @author:    Garbin
     *    @param:     string $msg
     *    @return:    bool
     */
    function _check_badwords($msg)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.badwords.php');
        $badwords_mng = new BadwordsManager();
        if (!$badwords_mng->check($msg))
        {
            $this->show_warning('has_badwords');

            return false;
        }

        return true;
    }
}

?>
