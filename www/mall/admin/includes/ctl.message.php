<?php

/**
 * ECMALL: 留言管理控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include_once(ROOT_PATH . '/includes/manager/mng.message.php');
include_once(ROOT_PATH . '/includes/models/mod.message.php');

class MessageController extends ControllerBackend
{
    var $_mng = null;

    /**
     * 构造函数
     */
    function __construct($act)
    {
        $this->MessageController($act);
    }

    function MessageController($act)
    {
        $this->_mng = new MessageManager($_SESSION['store_id']);

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
        $this->logger = false; // 不记日志

        /* 参数 */
        $keywords = empty($_GET['keywords']) ? '' : trim($_GET['keywords']);

        /* 取得列表 */
        $list = $this->_mng->get_list($this->get_page(), array('keywords' => $keywords));
        $this->assign('list', $list);

        /* 统计信息 */
        $this->assign('stats', $this->str_format('message_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* 显示模版 */
        $this->display('message.view.html');
    }

    /**
     * 批量处理
     */
    function batch()
    {
        /* 参数 */
        $param = empty($_GET['param']) ? '' : trim($_GET['param']);
        $ids   = empty($_GET['ids']) ? '' : trim($_GET['ids']);

        if (empty($ids))
        {
            $this->show_warning('no_message_selected');
            return;
        }

        /* 根据param做相应处理 */
        if ($param == 'drop')
        {
            $rows = $this->_mng->batch_drop($ids);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_drop_ok', $rows), $this->lang('back_messages'), 'admin.php?app=message&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_message_deleted');
                return;
            }
        }
        elseif ($param == 'show')
        {
            $rows = $this->_mng->batch_update($ids, 'if_show', 1);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_update_ok', $rows), $this->lang('back_messages'), 'admin.php?app=message&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_message_updated');
                return;
            }
        }
        elseif ($param == 'hide')
        {
            $rows = $this->_mng->batch_update($ids, 'if_show', 0);
            if ($rows > 0)
            {
                $this->show_message($this->str_format('batch_update_ok', $rows), $this->lang('back_messages'), 'admin.php?app=message&act=view');
                return;
            }
            else
            {
                $this->logger = false;
                $this->show_message('no_message_updated');
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
     * 编辑留言
     *
     * @author  wj
     * @return  void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* 参数 */
            $message_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            if ($message_id <= 0)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $mod = new Messages($message_id, $_SESSION['store_id']);
            $message = $mod->get_info();
            if (empty($message))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            $this->assign('message', $message);

            $this->display('message.detail.html');
        }
        else
        {
            /* 参数 */
            $message = array(
                'store_id'    => $_SESSION['store_id'],
                'reply'     => trim($_POST['reply']),
                'if_show'     => empty($_POST['if_show']) ? 0 : 1,
                'message_id'  => intval($_POST['message_id'])
            );
            if (empty($message['reply'])) unset($message['reply']); //reply 为空时,将不对reply做更新操作.
            $mod = new Messages($message['message_id'], $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            /* 更新 */
            $mod->update($message);
            //如果页面设置为显示，则检查是否要发送feed
            if ($message['if_show'])
            {
                $this->send_feed($message['message_id']);
            }
            $this->log_item = $message['message_id'];
            // todo: 链接问题
            $this->show_message('edit_ok', 'back_messages', 'admin.php?app=message&act=view');
            return;
        }
    }

    /**
     * 删除留言
     */
    function drop()
    {
        /* 参数 */
        $message_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new Messages($message_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* 删除 */
        if ($mod->drop())
        {
            $this->log_item = $message_id;
            $this->show_message('drop_ok', 'back_messages', 'admin.php?app=message&act=view');
            return;
        }
    }

    /**
     * ajax 编辑切换是否显示留言
     *
     * @author   wj
     *
     * @return  void
     */
    function modify()
    {
        $id = intval($_GET['id']);
        $column = trim($_GET['column']);
        $value = trim($_GET['value']);
        $mod_message = new Messages($id, $_SESSION['store_id']);
        if (!$mod_message->get_info())
        {
            $this->json_error('Hacking Attempt');
            return;
        }
        if ($column != 'if_show')
        {
            $this->json_error('Hacking Attempt');
            return;
        }
        if ($mod_message->update(array('if_show' => $value)))
        {
            //如果页面设置为显示，则检查是否要发送feed
            if ($value)
            {
                $this->send_feed($id);
            }
            //日志id
            $this->log_item = $id;
            $this->json_result($value);
        }
        else
        {
            $this->logger = false;
        }
    }

    /**
     * 自动检查是否需要发feed，需要就发送feed
     *
     * @author  wj
     * @param   int     $message_id     消息id
     * @return  void
     */

    function send_feed($message_id)
    {
        $mod = new Messages($message_id, $_SESSION['store_id']);
        $message = $mod->get_info();

        if ($message['need_send_feed'] && $message['goods_id'])
        {
            include_once(ROOT_PATH . '/includes/models/mod.goods.php');
            $mod_goods = new Goods($message['goods_id']);
            $info = $mod_goods->get_info();
            /* send feed to uc */
            $goods_url = site_url() . '/index.php?app=goods&id=' . $message['goods_id'];
            $store_url = site_url() . '/index.php?app=store&store=' . $message['seller_id'];
            $feed_info['icon']              =   'goods';
            $feed_info['user_id']           =   $message['buyer_id'];
            $feed_info['user_name']         =   $message['buyer_name'];
            $feed_info['title']['template'] =   $this->lang('feed_add_comment_title');
            $feed_info['title']['data']     =   array('store' => '<a href="' . $store_url . '" target="_blank">' . $this->conf('store_name', $message['seller_id']) . '</a>',
                                                      'goods' => '<a href="' . $goods_url . '" target="_blank">' . $info['goods_name'] . '</a>');
            add_feed($feed_info);
            $mod->update(array('need_send_feed'=>0)); //发送feed后设置为不要再发feed了。
        }

    }
}

?>