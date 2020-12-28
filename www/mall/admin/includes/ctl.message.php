<?php

/**
 * ECMALL: ���Թ��������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ���캯��
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
        $this->assign('stats', $this->str_format('message_stats', $list['info']['rec_count'], $list['info']['page_count']));

        /* ��ʾģ�� */
        $this->display('message.view.html');
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
            $this->show_warning('no_message_selected');
            return;
        }

        /* ����param����Ӧ���� */
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
     * �༭����
     *
     * @author  wj
     * @return  void
     */
    function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            /* ���� */
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
            /* ���� */
            $message = array(
                'store_id'    => $_SESSION['store_id'],
                'reply'     => trim($_POST['reply']),
                'if_show'     => empty($_POST['if_show']) ? 0 : 1,
                'message_id'  => intval($_POST['message_id'])
            );
            if (empty($message['reply'])) unset($message['reply']); //reply Ϊ��ʱ,������reply�����²���.
            $mod = new Messages($message['message_id'], $_SESSION['store_id']);
            $info = $mod->get_info();
            if (empty($info))
            {
                $this->show_warning('record_not_exist');
                return;
            }

            /* ���� */
            $mod->update($message);
            //���ҳ������Ϊ��ʾ�������Ƿ�Ҫ����feed
            if ($message['if_show'])
            {
                $this->send_feed($message['message_id']);
            }
            $this->log_item = $message['message_id'];
            // todo: ��������
            $this->show_message('edit_ok', 'back_messages', 'admin.php?app=message&act=view');
            return;
        }
    }

    /**
     * ɾ������
     */
    function drop()
    {
        /* ���� */
        $message_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $mod = new Messages($message_id, $_SESSION['store_id']);
        $info = $mod->get_info();
        if (empty($info))
        {
            $this->show_warning('record_not_exist');
            return;
        }

        /* ɾ�� */
        if ($mod->drop())
        {
            $this->log_item = $message_id;
            $this->show_message('drop_ok', 'back_messages', 'admin.php?app=message&act=view');
            return;
        }
    }

    /**
     * ajax �༭�л��Ƿ���ʾ����
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
            //���ҳ������Ϊ��ʾ�������Ƿ�Ҫ����feed
            if ($value)
            {
                $this->send_feed($id);
            }
            //��־id
            $this->log_item = $id;
            $this->json_result($value);
        }
        else
        {
            $this->logger = false;
        }
    }

    /**
     * �Զ�����Ƿ���Ҫ��feed����Ҫ�ͷ���feed
     *
     * @author  wj
     * @param   int     $message_id     ��Ϣid
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
            $mod->update(array('need_send_feed'=>0)); //����feed������Ϊ��Ҫ�ٷ�feed�ˡ�
        }

    }
}

?>