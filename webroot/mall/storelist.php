<?php

/**
 * ECMALL: �����б�
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: storelist.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class StoreListController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'check_store_name');

    /**
     * ���캯��
     *
     * @author  scottye
     * @param   stirng      $act    Ĭ�ϲ���
     * @return  void
     */
    function __construct($act)
    {
        $this->StoreListController($act);
    }

    /**
     * ���캯��
     *
     * @author  scottye
     * @param   stirng      $act    Ĭ�ϲ���
     * @return  void
     */
    function StoreListController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * �����б�ҳ��
     *
     * @author  scottye
     * @return  void
     */
    function view()
    {
        /* ���� */
        $cate_id  = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        $keywords = empty($_GET['keywords']) ? '' : trim($_GET['keywords']);
        if (empty($_GET['sort']) || !in_array(strtolower($_GET['sort']), array('store_name', 'add_time', 'goods_count')))
        {
            $_GET['sort'] = 'seller_credit';
        }
        if (empty($_GET['order']) || !in_array(strtolower($_GET['order']), array('asc', 'desc')))
        {
            $_GET['order'] = 'asc';
        }

        /* ֻΪû�йؼ��ֵĵ�һҳ������ */
        if ($keywords == '' && $this->get_page() == 1)
        {
            $need_cache = true;
            $cache_id   = 'cate_' . $cate_id . '_' . $_GET['sort'] . '_' . $_GET['order'];
        }
        else
        {
            $need_cache = false;
        }

        /* ����Ҫ�������û�л���ʱ��ѯ */
        if (!$need_cache || !$this->is_cached($cache_id))
        {
            /* ��ǰλ�� */
            $this->set_title(array($this->lang('store_list')));;
            $location_data = array(array('name' => $this->lang('store_list')));
            $this->assign('location_data', $location_data);

            /* ����ʽ */
            $url = 'index.php?app=storelist&cate_id=' . $cate_id . '&keywords=' . $keywords . '&';
            $sort_list = array(
                $url . 'sort=add_time&order=asc'       => $this->lang('store_sort.time_asc'),
                $url . 'sort=add_time&order=desc'        => $this->lang('store_sort.time_desc'),
                $url . 'sort=store_name&order=asc'     => $this->lang('store_sort.name_asc'),
                $url . 'sort=store_name&order=desc'      => $this->lang('store_sort.name_desc'),
                $url . 'sort=goods_count&order=asc'    => $this->lang('store_sort.goods_asc'),
                $url . 'sort=goods_count&order=desc'     => $this->lang('store_sort.goods_desc'),
                $url . 'sort=seller_credit&order=asc'  => $this->lang('store_sort.credit_asc'),
                $url . 'sort=seller_credit&order=desc'   => $this->lang('store_sort.credit_desc'),
            );
            $this->assign('sort_list', $sort_list);
            $this->assign('curr_sort', htmlspecialchars($url . 'sort=' . $_GET['sort'] . '&order=' . $_GET['order']));

            /* ȡ�����������ĵ��� */
            if ($cate_id > 0)
            {
                include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
                $mng_goods = new GoodsManager(0);
                $condition = array(
                    'mall_cate_id'  => $cate_id,
                    'is_on_sale'    => 1
                );
                $store_ids = $mng_goods->get_store_ids($condition);
            }
            else
            {
                $store_ids = array();
            }

            include_once(ROOT_PATH . '/includes/manager/mng.store.php');
            $mng_store = new StoreManager();
            $condition = array(
                'keywords'      => $keywords,
                'store_is_open' => 1,
                'store_ids'     => $store_ids
            );
            $store_list = $mng_store->get_list($this->get_page(), $condition, 20);
            include_once(ROOT_PATH . '/includes/models/mod.user.php');
            foreach ($store_list['data'] as $key => $val)
            {
                $store_list['data'][$key]['avatar']      = UC_API . '/avatar.php?uid=' . $val['store_id'] . '&amp;size=small';
                $store_list['data'][$key]['uchome_url']  = uc_home_url($val['store_id']);
                $store_list['data'][$key]['formated_add_time'] = local_date($this->conf('mall_time_format_simple'), $val['add_time']);
                $store_list['data'][$key]['seller_grade'] = User::score_to_grade($val['seller_credit'], $this->conf('mall_value_of_heart'));
            }
            $this->assign('store_list', $store_list);

            /* ȡ�����ŵ��� */
            $condition = array('store_is_open' => 1, 'hot' => 1);
            $_GET['sort'] = 'order_count';
            $store_list = $mng_store->get_list(1, $condition, 10);
            $this->assign('hot_store_list', $store_list);

            /* ȡ�����µ��� */
            $condition = array('store_is_open' => 1);
            $_GET['sort'] = 'add_time';
            $_GET['order'] = 'desc';
            $store_list = $mng_store->get_list(1, $condition, 10);
            $this->assign('new_store_list', $store_list);
        }

        $this->display('store_list', 'mall', $cache_id);
    }
}

?>