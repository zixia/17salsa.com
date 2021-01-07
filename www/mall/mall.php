<?php

/**
 * ECMALL: ��վ��ҳ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mall.php 6076 2008-11-18 10:31:58Z Garbin $
 */

class MallController extends ControllerFrontend
{
    var $_allowed_actions = array('view', 'jslang', 'cycleimage');

    function __construct($act)
    {
        $this->MallController($act);
    }
    function MallController($act)
    {
        !$act && $act = 'view';
        parent::ControllerFrontend($act);
    }

    /**
     * ��վ��ҳ
     *
     * @author  scottye
     */
    function view()
    {
        $cache_id = 'mall';
        if (!$this->is_cached($cache_id))
        {
            /* ȡ���Ƽ�Ʒ�ƣ�20���� */
            $brand_list = $this->get_recommended_brand(20);
            $no_logo_list = array();
            $logo_list = array();
            foreach ($brand_list as $brand)
            {
                if ($brand['brand_logo'])
                {
                    $logo_list[] = $brand;
                }
                else
                {
                    $no_logo_list[] = $brand;
                }
            }
            $this->assign('recommended_logo_brand', $logo_list);
            $this->assign('recommended_no_logo_brand', $no_logo_list);

            /* ȡ���Ź���Ʒ */
            $this->assign('group_buy_goods', $this->group_buy_goods(10));

            /* ȡ����Ʒ���� */
            $this->assign('goods_category', $this->get_goods_category());

            /* ȡ���Ƽ����̣�10���� */
            $this->assign('recommended_store', $this->get_recommend_store(10));

            /* ȡ��վ�ڿ�Ѷ��7���� */
            $this->assign('site_news', $this->get_site_news(7));

            /* ȡ��������(5��) */
            $this->assign('latest_wanted', $this->get_latest_wanted(5));

            /* ȡ�����³ɽ���4���� */
            $this->assign('latest_sold', $this->get_latest_sold(4));

            /* �����Ƽ� */
            $this->assign('rc_goods', $this->get_rc_goods());

            /* ȡ����������(10��) */
            $this->assign('partner_list', $this->get_partner(100));

            /* feed */
            $this->assign('feed_url', 'index.php?app=feed');

            /* description */
            $this->assign('meta_description', $this->conf('mall_description'));

            /*crontab code*/
            $this->assign('crontab_code', '<iframe style="display:none" height="0" src="' .site_url(). '/index.php?app=crontab"></iframe>');
        }

        /* ����pageview */
        $this->update_pageview(0);

        $this->display('homepage', 'mall', $cache_id);
    }

    /**
     * ����Ƽ�����
     *
     * @author    wj
     * @param   int     $num
     * @return  void
     */
    function get_recommend_store($num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');

        $store_mng  = new StoreManager();
        $store_info = $store_mng->get_list(1, array('is_recommend' => 1, 'store_is_open' => 1), $num);
        $rec_store  = $store_info['data'];

        foreach ($rec_store AS $key=>$val)
        {
            $rec_store[$key]['avatar']      = UC_API . '/avatar.php?uid=' . $val['store_id'] . '&amp;size=small';
            $rec_store[$key]['uchome_url']  = uc_home_url($val['store_id']);
        }

        return $rec_store;
    }

    /**
     * ��ȡ�Ź���Ʒ
     *
     * @author  scottye
     * @param   int   $count  ȡ������
     * @return  array
     */
    function group_buy_goods($count)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.groupbuy.php');
        $gb_mng = new GroupBuyManager(0, 0);

        return $gb_mng->get_list(1, array('underway' => 1, 'store_is_open' => 1), $count);
    }

    /**
     * ȡ���Ƽ�Ʒ��
     */
    function get_recommended_brand($num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
        $brand_mng = new BrandManager(0);
        /* Ʒ�ư���Ʒ�������� */
        $protect_key = array('sort'=>'goods_count', 'order'=>'desc');
        $protect_item = array();
        foreach($protect_key as $k=>$v)
        {
            if (isset($_GET[$k]))
            {
                $protect_item[$k] = $_GET['k'];
                $_GET['k'] = $v;
            }
        }
        $recommended_brand = $brand_mng->get_list(1, array('is_promote' => 1, 'store_is_open' => 1), $num);
        foreach ($protect_item as $k=>$v)
        {
            $_GET[$k] = $v;
        }

        return $recommended_brand['data'];
    }

    /**
     * ȡ��վ�ڿ�Ѷ
     */
    function get_site_news($num)
    {
        $_GET['sort'] = 'is_top';
        $_GET['order'] = 'asc';
        include_once(ROOT_PATH . '/includes/manager/mng.article.php');
        $article_mng = new ArticleManager(0);
        $condition = array('cate_id' => ARC_NEWS, 'if_show' => 1);

        return $article_mng->get_list(1, $condition, $num);
    }

    /**
     *    ȡ��������
     *
     *    @author    Garbin
     *    @param     int $num
     *    @return    array
     */
    function get_latest_wanted($num)
    {
        $tmp = $_GET;
        $_GET = array();
        include_once(ROOT_PATH . '/includes/manager/mng.wanted.php');
        $mng = new WantedManager();
        $latest_wanted = $mng->get_list(1, null, $num);
        $_GET = $tmp;
        return $latest_wanted['data'];
    }

    /**
     * ȡ�����³ɽ�
     */
    function get_latest_sold($num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $order_mng = new OrderManager(0);
        $latest_sold = $order_mng->list_recent_deal($num);

        return $latest_sold;
    }

    /**
     * ȡ����Ʒ����:ÿ3��һ������Ϊһ��
     */
    function get_goods_category()
    {
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate_mod = new Category();
        $cate_list = $cate_mod->list_child(3);

        $list = array();
        foreach ($cate_list as $cate)
        {
            switch ($cate['level'])
            {
                case 1 :
                    $list[$cate['cate_id']] = $cate;
                    break;

                case 2 :
                    $list[$cate['parent_id']]['children'][$cate['cate_id']] = $cate;
                    break;
                case 3 :
                    $list[$cate_list[$cate['parent_id']]['parent_id']]['children'][$cate['parent_id']]['children'][$cate['cate_id']] = $cate;
            }

        }

        return $list;
    }

    /**
     * ȡ����������
     */
    function get_partner($num)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.partner.php');
        $partner_mng = new PartnerManager(0);
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
     * ��ȡĳ��������Ʒ
     * @param void
     *
     * @return array
     */
    function get_rc_goods()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mg = new GoodsManager(0);
        $data = array();
        $tmp = $mg->get_list(1, array(), 4);
        $data['image_goods'] = $tmp['data'];
        $tmp = $mg->get_list(1, array(), 4);
        $data['word_goods'] = $tmp['data'];
        $tmp = $mg->get_list(1, array(), 8);
        $data['hot_goods'] = $tmp['data'];

        return $data;
    }

    /**
     *  ȡ���ֲ�ͼƬ�б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function cycleimage()
    {
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        echo $this->conf('mall_cycle_image');
    }
}

?>
