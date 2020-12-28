<?php

/**
 * ECMALL: 团购活动管理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: groupbuy.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require(ROOT_PATH . '/includes/manager/mng.groupbuy.php');
require(ROOT_PATH . '/includes/models/mod.groupbuy.php');

class GroupBuyController extends ControllerBackend
{
    var $manager = null;

    function __construct($act)
    {
        $this->GroupBuyController($act);
    }

    function GroupBuyController($act)
    {
        $this->manager = new GroupBuyManager($_SESSION['store_id']);
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * 查看团购活动列表
     *
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $today  = gmstr2time('today');
        $list   = $this->manager->get_list($this->get_page());

        deep_local_date($list['data'], 'start_time', 'Y-m-d');
        deep_local_date($list['data'], 'end_time', 'Y-m-d');

        foreach ($list['data'] as $key=>$val)
        {
            if ($val['is_finished'] == GROUPBUY_CANCEL)
            {
                $list['data'][$key]['act_status'] = $this->lang('canceled');
            }
            else
            {
                $cur_date = local_date('Y-m-d');
                if ($val['start_time'] > $cur_date)
                {
                    $list['data'][$key]['act_status'] = $this->lang('not_start');
                }
                elseif ($val['end_time'] >= $cur_date)
                {
                    $list['data'][$key]['act_status'] = $this->lang('active');
                }
                else
                {
                    $list['data'][$key]['act_status'] = $this->lang('end');
                }
            }
        }

        $this->assign('list',   $list);

        /* 统计信息 */
        $cond_actived   = array('start_time' => $today, 'end_time'  => $end_time);
        $this->assign('stats',  sprintf($this->lang('group_buy_stats'),
        $this->manager->get_count(), $this->manager->get_count(array('underway'))));

        $this->display('groupbuy.view.html', 'store');
    }

    /**
     * 添加团购活动
     *
     * @author  Xzf
     * @return  void
     */
    function add()
    {
        include_once(ROOT_PATH. '/includes/models/mod.goods.php');
        $this->logger = false;

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $goods_id   = intval($_GET['goods_id']);
            $mod_goods  = new Goods($goods_id, $_SESSION['store_id']);
            $goods_info = $mod_goods->get_info();

            if (empty($goods_info))
            {
                $this->show_warning('goods_not_exists');
                return;
            }
            else
            {
                $arr    = $mod_goods->get_spec();
                $spec   = $this->_get_spec($arr);

                $group_buy['goods_id']          = $goods_id;
                $group_buy['act_name']          = $goods_info['goods_name'];
                $group_buy['goods_name']        = $goods_info['goods_name'];
                $group_buy['limit']             = 0;
                $group_buy['start_time']        = local_date("Y-m-d");
                $group_buy['end_time']          = local_date("Y-m-d", gmstr2time("+1 week"));
                $group_buy['goods_spec']        = array($goods_info['default_spec']);

                $this->assign('group_buy', $group_buy);
                $this->assign('spec_list', $spec);
                $this->display('groupbuy.detail.html', 'store');
            }
        }
        else
        {
            $post = $this->_post_filter($_POST);

            /* 检查是否选定了商品规格 */
            if (empty($post['spec_id']))
            {
                $this->show_warning('no_goods_spec');
                return;
            }

            /* 检查商品是否属于店主 */
            $mod_goods  = new Goods($post['goods_id'], $_SESSION['store_id']);
            $goods_info = $mod_goods->get_info();

            if (empty($goods_info))
            {
                $this->show_warning('goods_not_exists');
                return;
            }
            else
            {
                $post['goods_name'] = addslashes($goods_info['goods_name']);
                $this->logger = true;
                $this->log_item = $this->manager->add($post);
                $this->show_message('add_groupbuy_successful', 'back_list', 'admin.php?app=groupbuy&amp;act=view');
                return;
            }
        }
    }

    /**
     * 编辑团购活动内容
     *
     * @return  void
     */
    function edit()
    {
        include_once(ROOT_PATH. '/includes/models/mod.goods.php');

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 编辑团购活动界面 */
            $act_id = intval($_GET['act_id']);

            if ($act_id <= 0)
            {
                $this->show_warning('no_specifically_act');
                return;
            }

            /* 获得活动信息 */
            $instance   = new GroupBuy($act_id, $_SESSION['store_id']);
            $act_info   = $instance->get_info();

            if (empty($act_info))
            {
                $this->show_warning('no_specifically_act');
                return;
            }

            /* 获得参加活动的商品的所有规格 */
            $goods  = new Goods($act_info['goods_id']);
            $spec   = $this->_get_spec($goods->get_spec());

            $this->assign('group_buy',  $act_info);
            $this->assign('spec_list',  $spec);
            $this->display('groupbuy.detail.html',  'store');
        }
        else
        {
            $post = $this->_post_filter($_POST);

            /* 检查是否选定了商品规格 */
            if (empty($post['spec_id']))
            {
                $this->show_warning('no_goods_spec');
                return;
            }

            /* 检查商品是否属于店主 */
            $mod_goods  = new Goods($post['goods_id'], $_SESSION['store_id']);
            $goods_info = $mod_goods->get_info();

            if (empty($goods_info))
            {
                $this->show_warning('goods_not_exists');
                return;
            }
            else
            {
                $instance = new GroupBuy($post['act_id'], $_SESSION['store_id']);
                if ($instance->update($post))
                {
                    $this->log_item = $post['act_id'];
                    $this->show_message('update_successfully', 'back_list', 'admin.php?app=groupbuy');
                    return;
                }
                else
                {
                    $this->show_warning('update_failed');
                    return;
                }
            }
        }
    }

    /**
     * 查看团购活动的参与情况
     *
     * @author  wj
     * @return  void
     */
    function view_log()
    {
        $this->logger = false;
        include_once(ROOT_PATH. '/includes/manager/mng.groupbuy_actor.php');
        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');

        $act_id = intval($_GET['act_id']);
        $instance = new GroupBuy($act_id);
        $info = $instance->get_info(); //团购信息

        $total = $instance->get_total_info();

        /* 获取商品信息 */
        $goods = new Goods($info['goods_id']);
        $goods_info = $goods->get_info();
        if (!empty($info['spec_id']))
        {
            $spec_ids = explode(',', $info['spec_id']);
            $specs = $goods->get_spec();
            foreach ($specs as $val)
            {
                if (in_array($val['spec_id'], $spec_ids))
                {
                    $goods_info['spec'][$val['spec_id']] = $val;
                }
            }
        }
        $manager   = new GroupBuyActorManager($act_id);
        $actor_list = $manager->get_list(1, array(), 100000); //无需分页
        foreach ($actor_list['data'] as $key=>$val)
        {
            $actor_list['data'][$key]['remarks'] = str_replace(array("\r", "\n", "\l"), array('', '<br />', ''), $val['remarks']);
            $actor_list['data'][$key]['spec_name'] = isset($goods_info['spec'][$val['spec_id']]) ? $goods_info['spec'][$val['spec_id']]['color_name'] . ' ' . $goods_info['spec'][$val['spec_id']]['spec_name'] : $val['spec_id'];
        }

        //判断状态
        if ($info['is_finished'] == GROUPBUY_CANCEL)
        {
            $this->assign('act_status', 'cancle');
        }
        else
        {
            $cur_date = local_date("Y-m-d");
            if ($info['start_time'] > $cur_date)
            {
                //活动未开始
                $this->assign('act_status', 'not_start');
            }
            else
            {
                if ($info['end_time'] >= $cur_date)
                {
                    //活动正在进行中
                    $this->assign('act_status', 'active');
                }
                else
                {
                    //活动已经结束
                    $this->assign('act_status', 'end');
                }
            }
        }

        $this->assign('act_id', $act_id);
        $this->assign('list',   $actor_list);
        $this->assign('stats',  $this->str_format('actor_stats', $total['actor_num'], $total['goods_num']));
        $this->display('groupbuy.view_log.html', 'store');
    }

    /**
     * 过滤、在处理表单提交的数据
     *
     * @param  array    $arr
     *
     * @return  array
     */
    function _post_filter($arr)
    {
        $post['act_id']             = !empty($arr['act_id']) ? intval($arr['act_id']) : 0;
        $post['goods_id']           = $arr['goods_id'];
        $post['spec_id']            = empty($arr['goods_spec']) ? '' : implode(',',$arr['goods_spec']);
        $post['start_time']         = gmstr2time($arr['start_time']);
        $post['end_time']           = gmstr2time($arr['end_time']);
        $post['act_desc']           = $arr['act_desc'];
        $post['ext_info']           = serialize(array('price' => floatval($arr['groupbuy_price']), 'limit' => intval($arr['groupbuy_limit'])));
        $post['act_name']               =$arr['act_name'];

        return $post;
    }

    /**
     * 格式化规格列表
     *
     * @param  array    $spec_arr
     *
     * @return  array
     */
    function _get_spec($spec_arr)
    {
        $spec = array();

        foreach ($spec_arr AS $val)
        {
            $spec[$val['spec_id']] = $val['color_name']. ' ' .$val['spec_name']. ' ' .$val['sku'];
        }

        return $spec;
    }

    /**
     * 结束当前团购
     *
     * @author  weberliu
     * @return  array
     */
    function end_activity()
    {
        $act_id = intval($_GET['act_id']);
        $pre_time = gmstr2time('today') - 86400;

        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        $mod = new GroupBuy($act_id, $_SESSION['store_id']);
        $info = $mod->get_info();
 ;
        if ($info['end_time'] > local_date('Y-m-d',$pre_time))
        {
            $mod->update(array('end_time'=>$pre_time));
        }
        $this->redirect('admin.php?app=groupbuy&act=view_log&act_id=' . $act_id);
    }

    /**
     * 取消一个团购
     *
     * @param  void
     *
     * @return  array
     */
    function cancel_activity()
    {
        $act_id = intval($_GET['act_id']);

        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        $mod = new GroupBuy($act_id, $_SESSION['store_id']);
        $mod->update(array('is_finished'=>GROUPBUY_CANCEL)); //将团购状态改为取消状态

        $this->redirect('admin.php?app=groupbuy&act=view_log&act_id=' . $act_id);
    }

    /**
     * 激活一个团购
     *
     * @param  void
     *
     * @return  array
     */
    function reopen_activity()
    {
        $act_id = intval($_GET['act_id']);

        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        $mod = new GroupBuy($act_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        $mg = new Goods($info['goods_id']);
        if (!$mg->get_info())
        {
            $this->show_message('goods_not_exist');
            return;
        }
        $mod->update(array('is_finished'=>0)); //将团购状态改为取消状态

        $this->redirect('admin.php?app=groupbuy&act=view_log&act_id=' . $act_id);
    }

    /**
     * 发送团购链接
     *
     * @author wj
     * @param  void
     *
     * @return  void
     */
    function pm_buy_link()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
            $act_id = intval($_GET['act_id']);
            $ids = trim($_GET['ids']);
            $pm_count = substr_count($ids, ',') + 1;
            $groupbuy = new GroupBuy($act_id, $_SESSION['store_id']);
            $info = $groupbuy->get_info();

            $this->assign('pm_title', $this->str_format('groupbuy_title', $info['goods_name']));
            $this->assign('pm_content', $this->str_format('pm_template', $info['goods_name']));
            $this->assign('pm_intro', $this->str_format('pm_intro', $pm_count));
            $this->assign('act_id', $act_id);
            $this->assign('ids', $ids);
            $this->display('groupbuy.buy_link.html', 'store');
        }
        else
        {
            $pm_content = trim($_POST['pm_content']);
            $pm_title = trim($_POST['pm_title']);
            $ids = trim($_POST['ids']);
            $this->log_item = $ids;
            $act_id = intval($_POST['act_id']);
            include_once(ROOT_PATH . '/includes/manager/mng.groupbuy_actor.php');
            include_once(ROOT_PATH . '/includes/cls.mail_queue.php');
            $actors = new GroupBuyActorManager($act_id, $_SESSION['store_id']);
            $list = $actors->get_info_by_id($ids);
            $url = site_url() . '/index.php?app=groupcheckout&act=transfer&id=';
            $mail_que = array();
            foreach ($list as $val)
            {
                $content = str_replace('$url', $url . $val['log_id'], $pm_content); //生成实际内容
                $msg_id = uc_call('uc_pm_send', array(0, intval($val['user_id']), $pm_title, $content));
                $mail_que[] = array($val['email'], $pm_title, $content);
                if ($msg_id > 0)
                {
                    $actors->update($val['log_id'], array('notify'=>1,'status'=>1));
                }
            }
            include_once(ROOT_PATH . '/includes/lib.editor.php');
            foreach ($mail_que as $_m)
            {
                $this->send_mail($_m[0], '', '', $_m[1], stripslashes(Editor::parse($_m[2])));
            }
            $this->show_message('groupbuy_ok', 'group_buy_viewlog', 'admin.php?app=groupbuy&amp;act=view_log&amp;act_id='.$act_id);

            return;
        }
    }

    /**
     * ajax 修改数量
     *
     */
    function modify()
    {
        $allowed_column = array('act_name', 'start_time', 'end_time'); // 允许编辑的字段名

        if (!in_array($_GET['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }

        if ($_GET['column'] == 'start_time')
        {
            $_GET['value'] = local_strtotime($_GET['value']);
        }

        if ($_GET['column'] == 'end_time')
        {
            $_GET['value'] = local_strtotime($_GET['value']);
        }

        $this->_modify('groupbuy', $_GET, 'error');
    }

    /**
     * 修改用户日志
     *
     * @author  wj
     * @param void
     * @return  void
     */
    function modify_log()
    {
        $allowed_column = array('number');
        if (!in_array($_GET['column'], $allowed_column))
        {
            $this->json_error($this->lang('deny_edit'));
            return;
        }
        include_once(ROOT_PATH. '/includes/models/mod.groupbuy_actor.php');
        $this->_modify('GroupBuyActor', $_GET, 'error');
    }

    /**
     * 删参与者信息
     *
     */
    function drop_actor()
    {
        $log_id = intval($_GET['log_id']);
        $act_id = intval($_GET['act_id']);
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy_actor.php');
        $new_actor = new GroupBuyActorManager($act_id, $_SESSION['store_id']);
        $new_actor->drop($log_id);
        $this->redirect('admin.php?app=groupbuy&act=view_log&act_id=' . $act_id);
    }

    /**
     * 删除团购
     */
    function drop()
    {
        $act_id = intval($_GET['act_id']);
        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        $mg = new GroupBuy($act_id);
        $mg->drop();

        $patterns[]     = '/act=\w+/i';
        $patterns[]     = '/[&|\?]?param=\w+/i';
        $patterns[]     = '/[&|\?]?ids=[\w,]+/i';
        $replacement[]  = 'act=view';
        $replacement[]  = '';
        $replacement[]  = '';
        $location = preg_replace($patterns, $replacement, $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed','back_list', $location);
        return;
    }
};
?>
