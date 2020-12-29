<?php

/**
 * ECMALL: ��Ʒ����ҳ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: goods.php 6024 2008-11-03 06:29:46Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}
include_once(ROOT_PATH . '/store.php');
class GoodsController extends StoreController
{
    var $_allowed_actions = array('view');

    function __construct($act)
    {
        $this->GoodsController($act);
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function GoodsController ($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �鿴��Ʒ��Ϣ
     *
     * @author  wj
     * @return  void
     */
    function view ()
    {
        include_once(ROOT_PATH . '/includes/models/mod.goods.php');
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        include_once(ROOT_PATH . '/includes/lib.editor.php');
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');

        if (empty($_GET['id']) && empty($_GET['spec_id']))
        {
            $this->show_warning('param_error');

            return;
        }

        /* ��ʼ��goods_id, spec_id �Լ�mod_goods */
        if (isset($_GET['id']))
        {
            $goods_id = intval($_GET['id']);
            $mod_goods = new Goods($goods_id);
            $goods_info = $mod_goods->get_info();
            $spec_id = $goods_info['default_spec'];
        }
        else
        {
            $spec_id = intval($_GET['spec_id']);
            $spec_info = Goods::get_spec_info($spec_id);
            if (empty($spec_info))
            {
                $this->show_warning('no_goods');
                return;
            }
            $goods_id = $spec_info['goods_id'];
            $mod_goods = new Goods($goods_id);
        }

        $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $cache_id = sprintf('goods_%d_%d', $spec_id, $page);

        /* ��ȡ��Ʒ��Ϣ */
        $info = $mod_goods->get_info();

        /* ��֤״̬ */
        if (empty($info))
        {
            $this->show_warning('no_goods');

            return;
        }

        if ($info['is_on_sale'] == '0')
        {
            $this->show_warning('goods_off_sale');

            return;
        }

         /* ��Ʒ������Ա��ֹ */
        if ($info['is_deny'] > 0)
        {
            if ((empty($_SESSION['admin_id']) || !empty($_SESSION['store_id'])))
            {
                $this->show_warning('goods_is_deny', 'back_home', site_url());
                return;
            }
            else
            {
                // todo: ����Ա����ʱ����Ҫ���߹���Ա����Ʒ�Ѿ�������
            }
        }

        if (!$this->is_cached($cache_id))
        {
            $spec_info = $mod_goods->get_spec_info($spec_id); //��ȡ�����Ϣ
            $info = array_merge($info, $spec_info);
            //��Ʒ�����ٴ���
            $info['to_save'] =  ($info['market_price'] > $info['store_price']) ? ($info['market_price'] -$info['store_price']) : 0;
            if ($info['keywords']) $info['tags'] = explode(' ', $info['keywords']);

            if ($info['type_id'] > 0)
            {
                $info['attr']   = $mod_goods->get_attribute(); //����
            }
            $info['lang_new_level'] = $this->lang('level' . $info['new_level']); //�¾ɳ̶�

            $goods_color    = $mod_goods->get_colors(addslashes($info['spec_name'])); //������Щ��ɫ
            $goods_spec     = $mod_goods->get_specs(addslashes($info['color_name'])); //������Щ���

            if (count($goods_color) > 0) $this->assign('goods_color', $goods_color);
            if (count($goods_spec) > 0) $this->assign('goods_spec', $goods_spec);

            $gallery = $this->get_gallery($goods_id, addslashes($info['color_name'])); //��ȡ�������

            if ($info['editor_type'] == 0)
            {
                $info['goods_desc'] = Editor::parse($info['goods_desc']);
            }

            $this->assign('goods',              $info);
            $this->assign('gallery',            $gallery);
            $this->assign('gallery_count',      count($gallery));
            $this->assign('similar_goods',      $mod_goods->get_similar_goods()); //������Ʒ
            $this->assign('compatible_goods',   $mod_goods->get_compatible_goods()); //������Ʒ

            /*  �������� */
            $store_id = $info['store_id'];
            $this->set_store($store_id);
            $store_data = $this->get_store_data($store_id);
            if (!is_array($store_data) && $store_data == -1)
            {

                $this->show_warning('store_closed', $this->lang('close_page'), 'javascript:window.close()', $this->lang('back_home'), 'index.php');
                return;

            }
            foreach ($store_data as $k=>$v)
            {
                $this->assign($k, $v);
            }

            /* ���ñ�ǩ */
            $this->assign('tabs', $this->get_tabs('view', $store_id));
            $url = "app=store&store_id=".$store_id;
            $this->assign('url', $url); //���õ��̵�����

            /* ��ȡ�Ƽ���Ʒ */
            $manager = new GoodsManager($info['store_id']);
            $this->assign('best_goods', $manager->get_list(1, array('is_best'=>1, 'sell_able'=>1)));

            /* ��ȡ��Ʒ�ķ�����Ϣ����Ϊ��ǰλ�� */
            $this->assign('location_data', $this->get_location_data($info['mall_cate_id'], $info['goods_name']));
            //ҳ�����
            $title_meta = array($info['goods_name'], $cate_info['cate_name']);
            $this->set_title($title_meta);

            //��ȡ���������Ʒ���û�
            $this->assign('bought_history', $mod_goods->get_bought_history());

            /*��Ʒtag*/
            if ($info['keywords'])
            {
                $_sp = ' ';
                $arr_keywords = explode($_sp, $info['keywords']);
                $this->assign('tag_relative', $this->_get_tag_relative($arr_keywords));
            }
            /* description */
            $this->assign('meta_description', $info['goods_brief']);
        }
        else
        {
            $this->set_store($info['store_id']);
            $info['stock'] = $mod_goods->get_stock($spec_id); //����������
            $this->assign('goods', $info);
        }

        /* ����pageview */
        $this->update_pageview($this->_store_id);

        /* ��ֵ��Ʒ���� */
        $this->_assign_comment($goods_id);

        if (isset($_SESSION['feed']))
        {
            $this->assign('feed_status', sprintf('%04b', $_SESSION['feed']));
        }

        $has_uch = has_uchome();
        if ($has_uch)
        {
            $myspace_url = uc_home_url($_SESSION['user_id']);
            if (isset($_SESSION['feed']))
            {
                $this->assign('feed_status', sprintf('%04b', $_SESSION['feed']));
            }
            if (!empty($_SESSION['user_id']))
            {
                $this->assign('lang_send_comment_feed', $this->str_format('send_comment_feed', $myspace_url));
            }
        }

        if (is_from_browser())
        {
            $mod_goods->update_click(); //������Ʒ��Ϣ
        }

        $this->display('goods', 'store', $cache_id);
    }

    /**
     * ��ȡtag��ص�����
     *
     * @author  wj
     * @param  array   $tags
     *
     * @return  array
     */
    function _get_tag_relative($tags)
    {
        if (empty($tags))
        {
            return array();
        }

        if (is_array($tags))
        {
            /* ���һ�������tag */
            $rand_key = array_rand($tags);
            $rand_tag = $tags[$rand_key];
        }
        else
        {
            $rand_tag = $tags;
        }

        include_once(ROOT_PATH . '/includes/cls.filecache.php');
        $cache = new filecache('tag_caches', 86400);
        $tag_relative = $cache->get($rand_tag); //��ȡ����

        if ($tag_relative === false)
        {
            $apps = uc_call('uc_app_ls');


            /* ��ȡ��ǰ�Ķ�Ӧ������ */
            if (is_file(ROOT_PATH. '/data/inc.app_setting.php'))
            {
                include_once(ROOT_PATH. '/data/inc.app_setting.php');
            }

            if (empty($app_settings))
            {
                $app_settings = array(); //��ʼ��app_setting
                foreach ($apps AS $key=>$val)
                {
                    if ($val['appid'] != UC_APPID)
                    {
                        $app_settings[$key]['num'] = 5;
                        $app_settings[$key]['tpl'] = isset($apps[$key]['tagtemplate']['template']) ? $apps[$key]['tagtemplate']['template'] : '';
                    }
                }
            }

            $tag_settings = array();
            foreach ($app_settings AS $key=>$val)
            {
                $tag_settings[$key] = $val['num'];
            }

            $tag_relative = uc_call('uc_tag_get', array($rand_tag, $tag_settings));
            if (!is_array($tag_relative)) $tag_relative = array();

            /* ������Ϸ��ص����� */
            foreach ($tag_relative AS $key=>$val)
            {
                $tag_relative[$key]['name'] = $apps[$key]['name'];
                $tag_relative[$key]['url']  = $apps[$key]['url'];
                $tag_relative[$key]['data'] = $this->_build_tag_relative($app_settings[$key]['tpl'], $tag_relative[$key]['data']);
            }

            $cache->set($rand_tag, $tag_relative);
        }


        return $tag_relative;
    }

    /**
     * ��ʽ����õ��������
     *
     * @param   string  $tpl
     * @param   array   $data
     *
     * @return  string
     */
    function _build_tag_relative($tpl, $data)
    {
        $html = array();

        if(!empty($data))foreach ($data AS $key=>$val)
        {
            $find = array();
            $replace = array();
            foreach ($val AS $k=>$v)
            {
                $find[] = "{" .$k. "}";
                $replace[] = ($k == 'dateline') ? local_date($this->conf('mall_time_format_simple'), $v) : $v;
            }

            $html[] = str_replace($find, $replace, $tpl);
        }

        return $html;
    }

    /**
     * ȡ�ö���Ʒ������
     *
     * @author  scottye
     * @param   int $goods_id   ��Ʒid
     */
    function _assign_comment($goods_id)
    {
        /* ��Ʒ���� */
        include_once(ROOT_PATH . '/includes/manager/mng.message.php');
        $msg_mng = new MessageManager(0);
        $msg_cond = array('if_show' => 1, 'goods_id' => $goods_id);
        $msg_list = $msg_mng->get_list($this->get_page(), $msg_cond, 10);
        $open_store = $msg_mng->get_open_store();

        $mod_goods = new Goods($goods_id);
        $users_bought = $mod_goods->get_users_who_bought();
        foreach ($msg_list['data'] as $key => $msg)
        {
            $msg_list['data'][$key]['bought'] = isset($users_bought[$msg['buyer_id']]) ? 1 : 0;
            if (isset($users_bought[$msg['buyer_id']]['is_anonymous']) && $users_bought[$msg['buyer_id']]['is_anonymous'])
            {
                $msg_list['data'][$key]['buyer_name'] = $this->lang('anonymous_buyer');
                $msg_list['data'][$key]['buyer_id']   = 0;
            }

            $msg_list['data'][$key]['user'] = array('user_id'=>$msg['buyer_id'], 'user_name'=>$msg['buyer_name'], 'uchome_url'=>uc_home_url($msg['buyer_id']), 'avatar'=>UC_API . '/avatar.php?uid=' . $msg['buyer_id'] . '&amp;size=small');
            if (in_array($msg['buyer_id'], $open_store))
            {
                $msg_list['data'][$key]['user']['has_store'] = 1;
            }
            else
            {
                $msg_list['data'][$key]['user']['has_store'] = 0;
            }
            $msg_list['data'][$key]['formated_add_time'] = local_date($this->conf('mall_time_format_complete'), $msg_list['add_time']);
        }

        $this->assign('msg_list', $msg_list);

        /* �Ƿ���Ҫ��֤�� */
        $need_captcha = $this->conf('mall_captcha_status');
        $need_captcha = $need_captcha & 4;
        $this->assign('need_captcha', $need_captcha);
    }

    /**
     * ��õ��̵���Ϣ
     *
     * @param   int     $store_id
     *
     * @return  void
     */
    function _get_store_info($store_id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $new_user = new User($store_id);
        $info   = $new_user->get_info();
        if ($info)
        {
            $info['owner_name'] = $info['user_name'];
            $info['store_id'] = $info['user_id'];
            $info['avatar']     = UC_API. '/avatar.php?uid=' .$store_id. '&amp;size=small';
            $info['uchome_url']     = uc_home_url($store_id);
            $info['reg_time']   = local_date($this->conf('mall_time_format_simple'), $info['add_time']);
            //$info['eval'] = $new_user->get_evaluation();
            $info['seller_rate'] = $new_user->get_eval_rate('seller');
            $info['buyer_rate'] = $new_user->get_eval_rate('buyer');
        }

        return $info;
    }

    function get_gallery($goods_id,$color_name)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $file_mng = new FileManager(0);
        $list = $file_mng->get_list_by_item($goods_id, '', $color_name);
        return $list;
    }


    /**
     * ��ȡ��ǰλ������
     *
     * @author wj
     * @param int       $mall_cate_id       ��վĿ¼cate_id
     * @param string    $goods_name         ��Ʒ����
     * @return array
     */
    function get_location_data($mall_cate_id, $goods_name)
    {
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $category       = new Category($mall_cate_id);
        $parent_cate    = $category->list_parent();
        $cate_info      = $category->get_info();;
        $parent_cate[]  = $cate_info;
        $location_data  = array();
        foreach ($parent_cate as $val)
        {
            $location_data[] = array('name'=>$val['cate_name'], 'url'=>'index.php?app=category&cate_id='.$val['cate_id']);
        }
        $location_data[] = array('name'=>$goods_name);

        return $location_data;
    }

};

?>
