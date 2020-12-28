<?php

/**
 * ECMALL: ������ҳ
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: store.php 6030 2008-11-03 10:23:41Z Wj $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'article', 'credit', 'comment');
    /**
     * ���캯��
     */
    function __construct($act)
    {
        $this->StoreController($act);
    }

    function StoreController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * ������ҳ
     *
     * @author  liupeng
     * @return  void
     */
    function view()
    {
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        $this->set_store($store_id);

        /* ����pageview */
        $this->update_pageview($store_id);

        // ���ɻ���id
        if (empty($_GET['keywords']))
        {
            $arr = array(
                'store_id'  => intval($_GET['store_id']),
                'page'      => isset($_GET['page']) ? intval($_GET['page']) : 1,
                'cate_id'   => isset($_GET['cate_id']) ? intval($_GET['cate_id']) : -1,
                'show_type' => isset($_GET['show_type']) ? trim($_GET['show_type']) : 'l_t',
                'sort'      => isset($_GET['sort']) ? trim($_GET['sort']) : 'goods_id',
                'order'     => isset($_GET['order']) ? trim($_GET['order']) : 'DESC');

            $cache_id = $this->crc32_code($arr);
            $pagename = 'homepage';

            unset($arr);
        }
        else
        {
            $cache_id = '';
        }

        if (!($cache_id && $this->is_cached($cache_id)))
        {
            $store_data = $this->get_store_data($store_id);

            $url_base  = 'index.php?app=' . APPLICATION . ($store_id ? '&amp;store_id='.$store_id : '');
            $show_type = (isset($_GET['show_type']) && $_GET['show_type'] == 'l_l') ? 'l_l' : 'l_t';

            if ($store_data == -1)
            {
                return;
            }
            foreach ($store_data as $k=>$v)
            {
                $this->assign($k, $v);
            }

            $this->assign('store_name', $this->conf('store_name', $store_id));

            /* ���ñ�ǩ */
            $this->assign('tabs', $this->get_tabs('view', $store_id));
            /* ȡ�õ��̼��*/
            $store_intro = $this->conf('store_intro', $store_id);
            if (strncmp($store_intro, 'html:', 5) == 0)
            {
                $store_intro = substr($store_intro, 5);
            }
            else
            {
                include_once(ROOT_PATH . '/includes/lib.editor.php');
                $store_intro = Editor::parse($this->conf('store_intro', $store_id));
            }
            $this->assign('store_intro', $store_intro);

            include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
            $goods_mng = new GoodsManager($store_id);
            $pagename = 'homepage';

            /* ȡ����Ʒ�б�ÿҳ20���� */
            $cate_id = isset($_GET['cate_id']) ? intval($_GET['cate_id']) : -1;
            $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

            if ($keywords)
            {
                /* ������Ʒ */
                $this->set_title(array($keywords)); //���ñ���
                /* ���� */
                $this->assign('location_data', $this->_get_location_data($store_id, $keywords));
                $condition = array('keywords' => $keywords, 'sell_able'=>1);
                $list_title = $this->lang('search_result');
                $this->assign('keywords', $keywords);

                $goods_list = $goods_mng->get_list($this->get_page(), $condition, $this->conf('store_page_size',$store_id));
                $this->modify_list($goods_list); //����Ʒ�б�����һЩ��Ϣ
                $this->assign('goods_list', $goods_list);
                $pagename = 'goods_list';
            }
            elseif ($cate_id == -1)
            {
                /* ������ҳ */
                $list_title = trim($_GET['cate_name']);
                /* ���� */
                $this->assign('location_data', $this->_get_location_data($store_id));
                //��ȡ�Ƽ���Ʒ��������Ʒ
                $new_goods = $goods_mng->get_list(1, array('is_s_new'=>1, 'sell_able'=>1), 100);
                $recommended_goods = $goods_mng->get_list(1, array('is_s_best'=>1, 'sell_able'=>1), 100);
                $this->assign('new_goods', $new_goods);
                $this->assign('store_recommended_goods', $recommended_goods);

                /* ȡ�ý����е��Ź���Ʒ */
                $group_goods = $this->get_group_goods($store_id, 100);
                $this->assign('group_goods', $group_goods);
            }
            elseif ($cate_id == 0)
            {
                /* ������Ʒ */
                $list_title = trim($_GET['cate_name']);
                /* ���� */
                $this->assign('location_data', $this->_get_location_data($store_id));
                $all_goods = $goods_mng->get_list($this->get_page(), array('sell_able'=>1), $this->conf('store_page_size',$store_id));
                $this->modify_list($all_goods);//����Ʒ�б�����һЩ��Ϣ
                $this->assign('goods_list', $all_goods);

                $pagename = 'goods_list';

            }
            elseif ($cate_id > 0)
            {
                /* ĳ������Ʒ */
                $condition = array('store_cate_id' => $cate_id, 'sell_able'=>1);
                $list_title = trim($_GET['cate_name']);
                $this->set_title(array($list_title));//���ñ���
                /* ���� */
                $location_data = $this->_get_location_data($store_id, true);
                include_once(ROOT_PATH . '/includes/models/mod.category.php');
                $new_category = new Category($cate_id, $store_id);
                $info = $new_category->get_info();
                $custom_url = get_store_custom_url($store_id);
                if ($custom_url)
                {
                    $custom_url .= '/';
                }
                if ($info['parent_id'])
                {
                    $new_category = new Category($info['parent_id'], $store_id);
                    $info1 = $new_category->get_info();
                    $url_data['cate_name'] = urlencode($info['cate_name']);
                    $location_data[] = array('name'=>$info1['cate_name'], 'url'=>$custom_url . 'index.php?' . $url . '&amp;cate_id=' . $info1['cate_id'] . '&amp;cate_name=' . urlencode($info1['cate_name']));
                }
                $location_data[] = array('name' => $info['cate_name']);

                $this->assign('location_data', $location_data);


                $goods_list = $goods_mng->get_list($this->get_page(), $condition, $this->conf('store_page_size',$store_id));
                $this->modify_list($goods_list); //����Ʒ���Ӹ�����Ϣ
                $this->assign('goods_list', $goods_list);
                $pagename = 'goods_list';
            }

            if (!empty($keywords) || $cate_id > -1)
            {
                $btn_act = array();
                $sort = (isset($_GET['sort']) && $_GET['sort'] == 'store_price') ? 'store_price' : 'goods_id';
                $btn_act[$show_type] = '_act';
                $btn_act[$sort] = '_act';

                $page = max(1, intval($_GET['page']));
                $page_url = '&amp;page=' . $page;
                $this->assign('page_url', $page_url);

                $url_base .= "&cate_id=$cate_id";

                if (!empty($keywords))
                {
                    $url_base .= '&keywords=' . urlencode($keywords);
                }
                $sort = (isset($_GET['sort']) && $_GET['sort'] == 'store_price') ? 'store_price' : 'goods_id';
                $url_extra['sort'] = $sort == 'store_price' ? '&amp;sort=store_price' : '';
                $url_extra['order'] = $_GET['order'] == 'ASC' ? '&amp;order=ASC' : '&amp;order=DESC';
                $url_extra['show_type'] = $show_type == 'l_t' ? '&amp;show_type=l_t' : '&amp;show_type=l_l';

                $this->assign('order', $_GET['order'] == 'ASC' ? 'DESC' : 'ASC');
                $this->assign('url_base',  $url_base);
                $this->assign('btn_act',   $btn_act);
                $this->assign('show_type', $show_type);
                $this->assign('sort',      $sort);
                $this->assign('url_extra', $url_extra);
            }

            $this->assign('list_title', $list_title);
            $this->assign('url', $url);
            $this->assign('meta_description', $store_intro);
            $this->assign('feed_url', 'index.php?app=feed&amp;store_id=' . $store_id);
        }

        $this->display($pagename, 'store', $cache_id);
    }

    /**
     * ��������
     *
     * @author  Garbin
     * @return  void
     */
    function article()
    {
        /* ���� */
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        $this->set_store($store_id);

        $art_id = isset($_GET['art_id']) ? intval($_GET['art_id']) : 0;
        $cache_id = 'store_article_' . $store_id . '_' . $art_id;
        if (!$this->is_cached($cache_id))
        {
            $store_data = $this->get_store_data($store_id);
            if ($store_data == -1)
            {
                return;
            }

            foreach ($store_data as $k=>$v)
            {
                $this->assign($k, $v);
            }

            $this->assign('store_name', $this->conf('store_name', $store_id));

            include_once(ROOT_PATH . '/includes/models/mod.article.php');
            $art = new Article($art_id);
            $info = $art->get_info();
            $this->set_title(array($info['title']));
            /* ���� */
            $this->assign('location_data', $this->_get_location_data($store_id, $info['title']));

            include_once(ROOT_PATH . '/includes/lib.editor.php');

            $art_content = $info['content'];
            if ($info['edior_type'] == 0)
            {
                $art_content = Editor::parse($art_content);
            }
            /* �������� */
            $this->assign('art_content', $art_content);

            /* ���ñ�ǩ */
            $this->assign('tabs', $this->get_tabs('article', $store_id));
            $this->assign('url', 'app=store&amp;store_id=' . $store_id);
        }

        $this->display('store_article', 'store', $cache_id);
    }

    /**
     * ��������
     *
     * @author  Garbin
     * @return  void
     */
    function credit()
    {
        /* ���� */
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        $page     = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $this->set_store($store_id);

        /*
        $cache_id = 'store_credit_' . $store_id . '_' . $_GET['from'] . '_' . $page . '_' . $_GET['date'] . '_' . $_GET['evaluation'];
        if (!$this->is_cached($cache_id))
        {
        */
        $store_data = $this->get_store_data($store_id);
        if ($store_data == -1)
        {
            return;
        }

        foreach ($store_data as $k=>$v)
        {
            $this->assign($k, $v);
        }

        /* ���� */
        $this->assign('location_data', $this->_get_location_data($store_id, $this->lang('title_store_credit')));

        /* ȡ���û����� */
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $mod_user = new User($store_id);
        $evaluation = $mod_user->get_evaluation();
        $this->assign('eval', $evaluation);

        /* ȡ�������б� */
        $conditions = array();
        $conditions['date'] = isset($_GET['date']) ? $_GET['date'] : '';
        $conditions['from'] = isset($_GET['from']) ? $_GET['from'] : 'all';
        $conditions['evaluation'] = isset($_GET['evaluation']) ? $_GET['evaluation'] : 0;

        include_once(ROOT_PATH . '/includes/manager/mng.order_comment.php');
        $mng_comment = new OrderCommentManager($store_id);
        $comments    = $mng_comment->get_list($page, $conditions);
        $this->assign('comments', $comments);
        $this->assign('store_name', $this->conf('store_name', $store_id));
        /* ���ñ�ǩ */
        $this->assign('tabs', $this->get_tabs('credit', $store_id));
        $this->assign('url', 'app=store&amp;store_id=' . $store_id);
        //}

        $this->display('store_credit', 'store', $cache_id);
    }

    /**
     * ��������
     *
     * @author  Garbin
     * @return  void
     */
    function comment()
    {
        /* ���� */
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        $this->set_store($store_id);

        $cache_id = 'store_comment_' . $store_id;
        if (!$this->is_cached($cache_id))
        {
            $store_data = $this->get_store_data($store_id);

            if ($store_data == -1)
            {
                return;
            }
            foreach ($store_data as $k=>$v)
            {
                $this->assign($k, $v);
            }

            /* ���� */
            $this->assign('location_data', $this->_get_location_data($store_id, $this->lang('title_store_comment')));
            $this->assign('store_name', $this->conf('store_name', $store_id));

            /* ���ñ�ǩ */
            $this->assign('tabs', $this->get_tabs('comment', $store_id));

            $this->assign('url', 'app=store&amp;store_id=' . $store_id);
        }

        //�Ƿ���Ҫ��֤��
        $need_captcha = $this->conf('mall_captcha_status');
        $need_captcha = $need_captcha & 4;
        $this->assign('need_captcha', $need_captcha);

        include_once(ROOT_PATH . '/includes/manager/mng.message.php');
        $msg_mng = new MessageManager($store_id);
        $cond  = array('goods_id' => 0, 'if_show' => 1);
        $msg_list = $msg_mng->get_list($this->get_page(), $cond, 10);
        $open_store = $msg_mng->get_open_store();
        foreach ($msg_list['data'] as $key => $msg)
        {
            $msg_list['data'][$key]['user'] = array('user_id'=>$msg['buyer_id'], 'user_name'=>$msg['buyer_name'], 'uchome_url'=>uc_home_url($msg['buyer_id']), 'avatar'=>UC_API . '/avatar.php?uid=' . $msg['buyer_id'] . '&amp;size=small');
            if (in_array($msg['buyer_id'], $open_store))
            {
                $msg_list['data'][$key]['user']['has_store'] = 1;
            }
            else
            {
                $msg_list['data'][$key]['user']['has_store'] = 0;
            }
        }
        $this->assign('msg_list', $msg_list);
        $this->assign('store_id', $store_id);

        $this->display('store_comment', 'store', $cache_id);
    }


    /**
     * ȡ����������
     *
     * @author  weberliu
     * @return  array
     */
    function get_partner($store_id, $num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.partner.php');
        $partner_mng = new PartnerManager($store_id);
        $partner_list = $partner_mng->get_list(1, null, $num);

        $arr = array('logo'=>array(), 'text'=>array());

        foreach ($partner_list['data'] AS $key=>$val)
        {
            if (empty($val['partner_logo']))
            {
                $arr['text'][] = $val;
            }
            else
            {
                $arr['logo'][] = $val;
            }
        }

        return $arr;
    }

    /**
     * ȡ�õ�����Ʒ����
     */
    function get_store_category($store_id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate_mod = new Category(0, $store_id);
        $cate_list = $cate_mod->list_child(2);

        $list = array();
        $row  = 0;
        foreach ($cate_list as $cate)
        {
            $pid = $cate['parent_id'];
            if ($pid == 0)
            {
                $cate['children'] = array();
                $list[$row] = $cate;
                $children =& $list[$row]['children'];
                $row++;
            }
            else
            {
                $children[] = $cate;
            }
        }

        return $list;
    }

    //��ȡ���̵�tabҳ��
    function get_tabs($act, $store_id)
    {
        $custom_url = get_store_custom_url($store_id);
        include_once(ROOT_PATH . '/includes/manager/mng.article.php');
        $mng_art = new ArticleManager($store_id);
        $art_list = $mng_art->get_store_article();
        $tabs = array();
        //��ҳ
        $tabs[] = array('class' => $act == 'view' ? 'first selected' : 'first' , 'href' => $custom_url ? $custom_url : 'index.php?app=store&amp;store_id=' . $store_id, 'text' => $this->conf('store_name', $store_id));
        $art_id = isset($_GET['art_id']) ?  intval($_GET['art_id']) : -1;
        //����
        foreach ($art_list as $v)
        {
            $tabs[] = array('class' => $art_id == $v['article_id'] ? 'selected' : '' , 'href' => $custom_url ? $custom_url . 'index.php?act=article&amp;art_id=' . $v['article_id'] : 'index.php?app=store&amp;store_id=' . $store_id . '&amp;act=article&amp;art_id=' . $v['article_id'], 'text' => $v['title']);
        }
        //����
        $tabs[] = array('class' => $act == 'credit' ? 'selected' : '', 'href' => $custom_url ? $custom_url . 'index.php?act=credit' : 'index.php?app=store&amp;store_id=' . $store_id . '&amp;act=credit', 'text' => $this->lang('credit'));
        //����
        $tabs[] = array('class' => $act == 'comment' ? 'last selected' : 'last', 'href' => $custom_url ? $custom_url . 'index.php?act=comment' : 'index.php?app=store&amp;act=comment&amp;store_id=' . $store_id, 'text' => $this->lang('store_comment'));

        return $tabs;
    }

    /**
     * ��ȡ���������Ϣ
     *
     * @author  wj
     * @param   int    store id
     *
     * @return array
     */
    function get_store_data($store_id)
    {
        $data = array();
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $mod_store = new Store($store_id);
        $store = $mod_store->get_info();
        if (empty($store) || $store['closed_by_admin'] || $store['closed_by_owner'] || $store['expired'])
        {
            $this->show_warning('store_closed', $this->lang('close_page'), 'javascript:window.close()', $this->lang('back_home'), 'index.php');
            return -1;
        }

        $data['store'] = $store;
        $data['store_id'] = $store_id;
        $data['store_name'] = $store['store_name'];

        $data['store_logo'] = $this->conf('store_logo', $store_id);

        /* ȡ�û�Ա��Ϣ */
        include_once(ROOT_PATH . '/includes/models/mod.user.php');
        $mod_user = new User($store_id);
        $user = $mod_user->get_info($this->conf('mall_value_of_heart'), $this->conf('mall_time_format_simple'));
        $user['avatar']     = UC_API . '/avatar.php?uid=' . $store_id . '&amp;size=small';
        $user['uchome_url'] = uc_home_url($store_id);
        $data['user'] = $user;

        /* ȡ�ú����� */
        $data['seller_rate'] = $mod_user->get_eval_rate('seller');
        $data['buyer_rate'] = $mod_user->get_eval_rate('buyer');

        /* ȡ�õ��̷��� */
        $data['goods_category'] = $this->get_store_category($store_id);

        /* ȡ����������(10��) */
        $data['partner_list'] = $this->get_partner($store_id, 10);

        $data['store_qq'] = $this->conf('store_qq', $store_id);
        $data['store_ww'] = $this->conf('store_ww', $store_id);
        $data['store_msn'] = $this->conf('store_msn', $store_id);

        return $data;
    }

    /**
     * ȡ�ý����е��Ź���Ʒ
     *
     * @author  scottye
     * @param   int     $store_id   ����id
     * @param   int     $num        ����
     * @return  array
     */
    function get_group_goods($store_id, $num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy.php');
        $mng_group = new GroupBuyManager($store_id);
        $condition = array('underway' => 1);
        return $mng_group->get_list(1, $condition, $num);
    }

    /**
     * ����Ʒ�б����Ӹ�����Ϣ
     *
     * @author  wj
     * @param   array       $goods_list     ��Ʒ�б�
     *
     * @return  void
     */
    function modify_list(&$goods_list, $keywords='')
    {
        //��ȡ������������Ʒid
        $page_goods_ids = array();
        $goods_count = count($goods_list['data']);
        for ($i=0; $i < $goods_count; $i++)
        {
            $page_goods_ids[] = $goods_list['data'][$i]['goods_id'];
        }
        $extra_data = GoodsManager::get_extra_info($page_goods_ids);
        for ($i=0; $i < $goods_count; $i++)
        {
            if (!empty($keywords))
            {
                $word_list = explode(' ', trim($keywords));
                foreach ($word_list as $word)
                {
                    $goods_list['data'][$i]['goods_name'] = str_replace($word, '<span class="high_light">' . $word . '</span>', $goods_list['data'][$i]['goods_name']);
                }
            }
            $_goods_id = $goods_list['data'][$i]['goods_id'];
            $goods_list['data'][$i] = array_merge($goods_list['data'][$i], $extra_data[$_goods_id]);
        }
    }

    /**
     * ȡ�õ�ǰλ��
     *
     * @author  scottye
     * @param   int         $store_id   ����id
     * @param   string/bool $tail       β��
     */
    function _get_location_data($store_id, $tail = false)
    {
        $location_data = array(array('name' => $this->lang('store_list'), 'url' => 'index.php?app=storelist'));
        if ($tail)
        {
            $custom_url = get_store_custom_url($store_id);
            $location_data[] = array('name' => $this->conf('store_name', $store_id), 'url' => $custom_url ? $custom_url : 'index.php?app=store&amp;store_id=' . $store_id);
            if ($tail !== true)
            {
                $location_data[] = array('name' => $tail);
            }
        }
        else
        {
            $location_data[] = array('name' => $this->conf('store_name', $store_id));
        }
        return $location_data;
    }
}
?>
