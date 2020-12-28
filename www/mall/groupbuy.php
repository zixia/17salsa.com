<?php

/**
 * ECMALL: �Ź�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: groupbuy.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('CTRL_DOMAIN', 'mall');


include_once(ROOT_PATH . '/store.php');

class GroupbuyController extends StoreController
{
    var $_allowed_actions = array('view', 'detail', 'join_in');

    function __construct($act)
    {
        $this->GroupbuyController($act);
    }
    function GroupbuyController($act)
    {
        if (empty($act)) $act = 'view';

        if (isset($_GET['store_id']))
        {
            $this->_store_id = intval($_GET['store_id']);
        }
        parent::__construct($act);
    }

    /**
     * �Ź��б�ҳ��
     *
     * @author  wj
     * @return  void
     */
    function view()
    {
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        if ($store_id < 0) $store_id = 0;
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy.php');
        $mng_groupbuy = new GroupBuyManager($store_id);
        $url_extra = "index.php?app=groupbuy&amp;act=view";
        $list = $mng_groupbuy->get_list($this->get_page(), array('actual'=> 1));
        $this->list_modify($list); //��list������Ϣ
        $this->assign('url_extra', $url_extra);
        $this->assign('location_data', array(array('name'=>$this->lang('groupbuy_list'))));
        $this->assign('groupbuy_list', $list);
        $this->assign('recommended_store', $this->get_recommend_store());
        $this->display('groupbuy_list', 'mall');
    }

    /**
     * �Ź�����ҳ��
     *
     * @author  liupeng
     * @return  void
     */
    function detail()
    {
        $act_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($act_id <= 0)
        {
            $this->show_warning('param_error');

            return;
        }

        include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy_actor.php');
        $instance = new GroupBuy($act_id);
        $info = $instance->get_info();
        if (empty($info))
        {
            $this->show_warning('no_goods');

            return;
        }
        $store_id = $info['store_id'];

        $this->set_store($store_id);
        $store_data = $this->get_store_data($store_id);
        if (!is_array($store_data))
        {
            if ($store_data == -1)
            {
                $this->show_warning('store_closed');
                return;
            }
        }
        foreach ($store_data as $k=>$v)
        {
            $this->assign($k, $v);
        }
        $url = "app=store&store_id=".$store_id;
        $this->assign('url', $url); //���õ��̵�����

        /* ���ñ�ǩ */
        $this->assign('tabs', $this->get_tabs('view', $store_id));
        $info['add_url'] = 'index.php?app=groupbuy&amp;act=join_in&amp;id=' . $act_id;
        $total_info = $instance->get_total_info(); //��ȡ������Ϣ
        $info['goods_num'] = $total_info['goods_num'];
        $info['compare_num'] = $info['limit'] - $info['goods_num'];
        $info['status'] = $this->groupbuy_status($info);
        $info['lang_status'] = $this->lang('gb_' . $info['status']);
        $this->assign('info', $info);
        //�Ź���ʾ��Ϣ
        $limit_intro = '';
        if ($info['status'] == 'active')
        {
            if ($info['limit'] > 0)
            {
                if ($info['compare_num'] > 0)
                {
                    $limit_intro = $this->str_format('not_enough_actor', $info['compare_num']);
                }
                else
                {
                    $limit_intro = $this->lang('enough_actor');
                }
            }
        }
        else
        {
            $limit_intro = $this->lang('gb_' . $info['status']);
        }
        $this->assign('limit_intro', $limit_intro);

        $goods = new Goods($info['goods_id']);
        $goods_info = $goods->get_goods_detail();

        if (empty($goods_info))
        {
            $this->show_warning('no_goods');

            return;
        }

         /* ��Ʒ������Ա��ֹ */
        if ($goods_info['is_deny'] > 0)
        {
            $this->show_warning('goods_is_deny');

            return;
        }

        if (!empty($info['spec_id']))
        {
            $spec_ids = explode(',', $info['spec_id']);
            $specs = $goods->get_spec();
            foreach ($specs as $val)
            {
                if (in_array($val['spec_id'], $spec_ids))
                {
                    $goods_info['spec'][] = $val;
                }
            }
        }

        $this->set_title(array($info['act_name'], $this->lang('group_buy')));

        $goods_info['attr'] = $goods->get_attribute();

        /* ����UBB���� */
        include_once(ROOT_PATH . '/includes/lib.editor.php');
        if ($goods_info['editor_type'] == 0)
        {
            $goods_info['goods_desc'] = Editor::parse($goods_info['goods_desc']);
        }
        $this->assign('goods', $goods_info);

        $gallery = $this->get_gallery($info['goods_id']);

        $spec_color = array();
        foreach ($goods_info['spec'] AS $val)
        {
            $spec_color[] = $val['color_name'];
        }

        foreach ($gallery AS $key => $value)
        {
            if ($value['color'] && (!in_array($value['color'], $spec_color)))
            {
                unset($gallery[$key]);
            }
        }

        $gallery = array_values($gallery);
        $this->assign('gallery_count', count($gallery));
        $this->assign('gallery', $gallery);

        /* ��ȡ��������Ϣ */
        $mng_actor = new GroupBuyActorManager($act_id);
        $this->assign('groupbuy_log', $mng_actor->get_list(1));

        //����
        $location_data = array(array('name'=>$this->lang('groupbuy_list'), 'url'=>'index.php?app=groupbuy&amp;act=view'), array('name'=>$info['goods_name']));
        $this->assign('location_data', $location_data);
        $this->assign('act_type',   $this->lang('groupbuy'));
        $this->assign('title', $info['goods_name'] . '-' . $this->lang('groupbuy_list'));

        $this->display('groupbuy_detail', 'store');
    }

    /**
     * �������
     *
     * @author  wj
     * @param   void
     *
     * @return void
     */
    function join_in()
    {
        $act_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if ($act_id <= 0)
        {
            $this->show_warning('param_error');

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            if (empty($_SESSION['user_id']))
            {
                //δ��¼��ת����¼ҳ��
                $this->redirect('index.php?app=member&act=login&ret_url=' . urlencode('index.php?app=groupbuy&act=join_in&id=' .$act_id));
            }
            /* ��Ʒ��Ϣ */
            include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
            include_once(ROOT_PATH . '/includes/models/mod.goods.php');

            $instance = new GroupBuy($act_id);
            $info = $instance->get_info(); //�Ź���Ϣ
            if ($info['store_id'] == $_SESSION['user_id'])
            {
                $this->show_warning('e_groupbuy_self');
                return;
            }

            if ($this->is_had_join($act_id, $_SESSION['user_id']))
            {
                $this->show_warning('had_join');
                return;
            }
            /* ��ȡ��Ʒ��Ϣ */
            $goods = new Goods($info['goods_id']);
            $goods_info = $goods->get_info();

            if (!empty($info['spec_id']))
            {
                $spec_ids = explode(',', $info['spec_id']);
                $goods_info['spec'] = array();
                $specs = $goods->get_spec();
                foreach ($specs as $val)
                {
                    if (in_array($val['spec_id'], $spec_ids))
                    {
                        $goods_info['spec'][] = $val;
                    }
                }

                if (count($goods_info['spec']) ==1 && empty($goods_info['spec'][0]['color_name']) && empty($goods_info['spec'][0]['spec_name']))
                {
                    //Ĭ��ֻ��һ�ֹ��ʱ, ����Ϊ��,��ʱӦ�����
                    unset($goods_info['spec'][0]);
                }
            }
            $store_id = $info['store_id'];
            $this->set_store($store_id);
            $store_data = $this->get_store_data($store_id);
            if (!is_array($store_data))
            {
                if ($store_data == -1)
                {
                    $this->show_warning('store_closed');
                    return;
                }
            }

            foreach ($store_data as $k=>$v)
            {
                $this->assign($k, $v);
            }
            /* ���ñ�ǩ */
            $this->assign('tabs', $this->get_tabs('view', $store_id));
            $url = "app=store&store_id=".$store_id;
            $this->assign('url', $url); //���õ��̵�����

            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            $user = new User($_SESSION['user_id']);
            $user_info = $user->get_info();
            $user_info['telephone'] = empty($user_info['office_phone']) ? $user_info['home_phone'] : $user_info['office_phone'];

            //����
            $location_data = array(array('name'=>$this->lang('groupbuy_list'), 'url'=>'index.php?app=groupbuy&amp;act=view'), array('name'=>$info['goods_name']));
            $this->assign('location_data', $location_data);
            $this->assign('goods', $goods_info);
            $this->assign('info',  $info);
            $this->assign('act_user', $user_info);
            $this->assign('act_type',   $this->lang('groupbuy'));
            $this->assign('title', $info['goods_name']);

            $this->display('groupbuy_join', 'store');
        }
        else
        {
            if (empty($_SESSION['user_id']))
            {
                //û��½��ʾ
                $this->show_warning('no_login', 'groupbuy', 'index.php?app=groupbuy&amp;act=detail&amp;id=' . $act_id);

                return;
            }
            //����Ƿ��Ѿ�������Ź�
            if ($this->is_had_join($act_id, $_SESSION['user_id']))
            {
                $this->show_warning('had_join');
                return;
            }
            /*��������*/
            $data = array();
            $data['act_id'] = $act_id;
            $data['spec_id'] = empty($_POST['spec_id']) ? 0 : intval($_POST['spec_id']);
            $data['number'] = empty($_POST['number']) ? 0 : intval($_POST['number']);
            $data['email'] = empty($_POST['email']) ? '' : trim($_POST['email']);
            $data['telephone'] = empty($_POST['telephone']) ? '' : trim($_POST['telephone']);
            $data['mobile'] = empty($_POST['mobile']) ? '' : trim($_POST['mobile']);
            $data['remarks'] = empty($_POST['remarks']) ? '' : trim($_POST['remarks']);
            $data['user_name'] = empty($_POST['user_name']) ? '' : trim($_POST['user_name']);

            if ($data['number'] <= 0)
            {
                $this->show_warning('groupbuy_num_error');

                return;
            }

            if ($data['email'] == '' || (!is_email($data['email'])))
            {
                $this->show_warning('groupbuy_email_error');

                return;
            }

            if ($data['telephone'] == '' && $data['mobile'] == '')
            {
                $this->show_warning('groupbuy_telephone_error');

                return;
            }

            /* ��ȡ�Ź���Ϣ */
            include_once(ROOT_PATH . '/includes/models/mod.groupbuy.php');
            include_once(ROOT_PATH . '/includes/manager/mng.groupbuy_actor.php');
            $instance = new GroupBuy($act_id);
            $info = $instance->get_info();
            $status = $this->groupbuy_status($info);

            //����Ź����ǻ�У���ʾ�˳�
            if ($status != 'active')
            {
                $this->show_warning('gb_' . $status);

                return;
            }

            if (!isset($info['store_id']))
            {
                $this->show_warning('no_activity', 'home', 'index.php');

                return;
            }

            //���spec_idΪ0,��ʹ��Ĭ��spec_id
            if ($data['spec_id'] <= 0)
            {
               include_once (ROOT_PATH . '/includes/models/mod.goods.php');
               $mod_goods = new Goods($info['goods_id']);
               $goods_info = $mod_goods->get_info();
               $data['spec_id'] = $goods_info['default_spec'];
            }

            //��ȫʣ����Ϣ
            $data['goods_id'] = $info['goods_id'];
            $data['user_id'] = $_SESSION['user_id'];
            $data['add_time'] = gmtime();

            $mng_actor = new GroupBuyActorManager($act_id, $info['store_id']);

            $mng_actor->add($data);
            $this->show_message('groupbuy_add_ok', 'back_groupbuy', 'index.php?app=groupbuy&amp;act=detail&amp;id=' . $act_id);
            return;
        }
    }

    /**
     * ��ȡ�Ź�״̬
     *
     * @param   int     $store_id
     *
     * @return  void
     */
    function groupbuy_status(&$info)
    {
         //�ж�״̬
        if ($info['is_finished'] == GROUPBUY_CANCEL)
        {
            return 'cancel';
        }
        else
        {
            $cur_date = local_date("Y-m-d");
            if ($info['start_time'] > $cur_date)
            {
                //�δ��ʼ
                return 'not_start';
            }
            else
            {
                if ($info['end_time'] >= $cur_date)
                {
                    //����ڽ�����
                    return 'active';
                }
                else
                {
                    //��Ѿ�����
                    return 'end';
                }
            }
        }
    }


    /**
     * ����Ƽ�����
     *
     * @return  void
     */
    function get_recommend_store()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');

        $store_mng  = new StoreManager();
        $store_info = $store_mng->get_list(1, array('is_recommend' => 1), 3);
        $rec_store  = $store_info['data'];

        foreach ($rec_store AS $key=>$val)
        {
            $rec_store[$key]['avatar']      = UC_API . '/avatar.php?uid=' . $val['store_id'] . '&amp;size=small';
            $rec_store[$key]['uchome_url']  = uc_home_url($val['store_id']);
        }

        return $rec_store;
    }

    function get_gallery($goods_id)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $file_mng = new FileManager(0);
        $list = $file_mng->get_list_by_item($goods_id);
        return $list;
    }

    /**
     * ���б����ݵ�����
     *
     * @author  wj
     * @param   array   $list
     *
     * @return  void
     */
    function list_modify(&$list)
    {
        $start_time = gmstr2time('today'); //��ȡ�������ʱ��
        foreach ($list['data'] as $k=>$v)
        {
            $list['data'][$k]['status'] = $v['end_time'] > $start_time ? 'active' : 'end';
        }
    }

    /**
     * ����û��Ƿ������Ź�
     *
     * @author wj
     * @param int   $act_id     �id
     * @param int   $user_id    �û�id
     */
    function is_had_join($act_id, $user_id)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy_actor.php');
        $actors = new GroupBuyActorManager($act_id);
        $num = $actors->had_join($user_id);

        return $num;
    }
 }

?>
