<?php

/**
 * ECMALL: 商品搜索控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: search.php 2369 2008-04-17 10:02:41Z zhaoxiongfei $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('CTRL_DOMAIN', 'mall');

require_once(ROOT_PATH . '/includes/manager/mng.goods.php');
require_once(ROOT_PATH . '/includes/models/mod.category.php');
require_once(ROOT_PATH . '/includes/manager/mng.region.php');

class SearchBaseController extends ControllerFrontend
{
    var $manager = null;
    var $cate_mod = null;
    var $_store_id = 0;
    var $_cate_id = null;
    function __construct($act)
    {
        $this->SearchBaseController($act);
    }

    function SearchBaseController($act)
    {
        $act = empty($act) ? 'view' : $act;
        if (isset($_GET['store_id']))
        {
            $this->_store_id = intval($_GET['store_id']);
        }
        $this->manager = new GoodsManager($this->_store_id);
        $this->cate_mod = new Category(0, $this->_store_id);
        $this->_cate_id = intval($_GET['cate_id']);
        parent::__construct($act);
    }

    /**
     * 商品收索列表页面
     *
     * @author  wj
     * @return  void
     */
    function view()
    {
        $_GET['order'] = isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
        $url_base = 'index.php?app=' . APPLICATION . ($this->_store_id ? '&amp;store_id='.$this->_store_id : '');
        $condition = array('sell_able'=>1);
        $location_data = $filter_data = $url_extra = array();
        $title = $this->conf('mall_name');
        $page_url = '';

        /* 分类 */
        $title_meta = array(); //标题元素
        if ($this->_cate_id)
        {
            $cate_id_name = $this->_store_id ? 'store_cate_id' : 'mall_cate_id';
            $condition[$cate_id_name] = $this->_cate_id;
            $cate_mod = new Category($this->_cate_id, $this->_store_id);
            $parents  = $cate_mod->list_parent();
            if (!empty($parents))
            {
                foreach ($parents as $val)
                {
                    $location_data[] = array('name' => $val['cate_name'], 'url' => 'index.php?app=search&amp;cate_id='.$val['cate_id']);
                    $title_meta[] = $val['cate_name'];
                }
            }
            $cate_info = $cate_mod->get_info();
            if (empty($cate_info))
            {
                $this->show_message('undefined_action');
                return false;
            }
            $location_data[] = array('name' => $cate_info['cate_name'], 'url' => 'index.php?app=search&amp;cate_id='.$cate_info['cate_id']);
            $title_meta[] = $cate_info['cate_name'];

            $filter_data['cate_id'] = array('name' => 'goods_category', 'value' => $cate_info['cate_name'], 'url' => '&amp;cate_id=' . $this->_cate_id);
        }

        /* 关键词 */
        if ($keywords = trim($_GET['keywords']))
        {
            $condition['keywords'] = $keywords;
            $location_data[] = array('name' => $this->str_format('keywrod_result_list', $keywords));
            array_unshift($title_meta, $keywords);

            $encode_keywords = urlencode($keywords);
            $filter_data['keywords'] = array('name' => 'keywords', 'value' => $keywords, 'url' => '&amp;keywords=' . $encode_keywords);
        }
        /* goods tag */
        if (!empty($_GET['tag_words']))
        {
            $tag_words = trim($_GET['tag_words']);
            $encode_tag_words = urlencode($tag_words);
            $location_data[] = array('name'=>$this->lang('tag_list'), 'url'=>'#');
            $location_data[] = array('name'=>$tag_words);

            if ($tag_words)
            {
                $condition['tag_words'] = $tag_words;
                $page_url .= '&amp;tag_words=' . $encode_tag_words;
            }
            $filter_data['tag_words'] = array('name' => 'tag_words', 'value' => $tag_words, 'url' => '&amp;tag_words=' . $encode_tag_words);
        }
        elseif (empty($this->_cate_id))
        {
            $location_data[] = array('name' => $this->lang('all_category'));
        }

        /* 品牌 */
        if (isset($_GET['brand_id']))
        {
            require_once(ROOT_PATH . '/includes/models/mod.brand.php');
            $brand_id = intval($_GET['brand_id']);
            $condition['brand_id'] = $brand_id;

            $brand = new Brand($brand_id, $this->_store_id);
            $brand_info = $brand->get_info();
            $filter_data['brand_id'] = array('name' => 'goods_brand', 'value' => $brand_info['brand_name'], 'url' => '&amp;brand_id=' . $brand_id);
        }

        /* 是否精品 */
        if (isset($_GET['is_best']))
        {
            $condition['is_best'] = 1;

            $filter_data['is_best'] = array('name' => 'is_best', 'value' => $this->lang('yes'), 'url' => '&amp;is_best=1');
        }

                /* 是否精品 */
        if (isset($_GET['is_best']))
        {
            $condition['is_best'] = 1;

            $filter_data['is_best'] = array('name' => 'is_best', 'value' => $this->lang('yes'), 'url' => '&amp;is_best=1');
        }

        /* 颜色值 */
        if (isset($_GET['color_rgb']))
        {
            $color_rgb = trim($_GET['color_rgb']);
            $condition['color_rgb'] = '#'. $color_rgb;

            $encode_color_rgb = urlencode($color_rgb);
            $filter_data['color_rgb'] = array('name' => 'color_rgb', 'value' =>$condition['color_rgb'], 'url' => '&amp;color_rgb=' . $encode_color_rgb);
        }

        /* 地区ID */
        if (isset($_GET['region']))
        {
            $region = trim($_GET['region']);
            $condition['region_id'] = $region;

            $filter_data['region'] = array('name' => 'region', 'value' =>$region, 'url' => '&amp;region=' . $region);
        }

        /* 价格区间 */
        if (isset($_GET['min_price']) || isset($_GET['max_price']))
        {
            $min_price = intval($_GET['min_price']);
            $max_price = intval($_GET['max_price']);
            if ($max_price < $min_price && $max_price > 0)
            {
                $tmp = $min_price;
                $min_price = $max_price;
                $max_price = $tmp;
            }
            if ($max_price > 0 && $min_price < 1)
            {
                $filter_data['price_range'] = array('name'=> 'filter_price', 'value' => $this->str_format('no_rg', $max_price), 'url' => '&amp;max_price=' . $max_price);
                $condition['max_price'] = $max_price;
            }
            elseif ($max_price < 1 && $min_price > 0)
            {
                $filter_data['price_range'] = array('name'=> 'filter_price', 'value' => $this->str_format('no_lg', $min_price), 'url' => '&amp;min_price=' . $min_price);
                $condition['min_price'] = $min_price;
            }
            elseif ($max_price > 0 && $min_price > 0)
            {
                $filter_data['price_range'] = array('name'=> 'filter_price', 'value' => $min_price . $this->lang('monetary_unit') . '-' . $max_price . $this->lang('monetary_unit'), 'url' => '&amp;min_price=' . $min_price . '&amp;max_price=' . $max_price);
                $condition['max_price'] = $max_price;
                $condition['min_price'] = $min_price;
            }
        }

        /* 显示方式和排序方式 */
        $show_type = (isset($_GET['show_type']) && $_GET['show_type'] == 'l_l') ? 'l_l' : 'l_t';
        $url_extra['show_type'] = $show_type == 'l_l' ? '&amp;show_type=l_l' : '';
        $sort = (isset($_GET['sort']) && $_GET['sort'] == 'store_price') ? 'store_price' : 'goods_id';
        $url_extra['sort'] = $sort == 'store_price' ? '&amp;sort=store_price' : '';
        $btn_act = array();
        $btn_act[$show_type] = '_act';
        $btn_act[$sort] = '_act';
        $_GET['order'] = (isset($_GET['order']) && $_GET['order'] == 'ASC') ? 'ASC' : 'DESC';
        $url_extra['order'] = $_GET['order'] == 'ASC' ? '&amp;order=ASC' : '&amp;order=DESC';

        $page = max(1, intval($_GET['page']));
        $page_url .= '&amp;page=' . $page;

        $goods_list = $this->manager->get_list($page, $condition, $this->conf('mall_page_size'));

        if ($page > $goods_list['info']['page_count'])
        {
            $target_url = preg_replace('/page=\d+/', 'page=' . $goods_list['info']['page_count'], $_SERVER['REQUEST_URI']);
            header("Location: $target_url\n");
            exit;
        }

        //获取符合条件的商品id
        $page_goods_ids = array();
        $goods_count = count($goods_list['data']);
        for ($i=0; $i < $goods_count; $i++)
        {
            $page_goods_ids[] = $goods_list['data'][$i]['goods_id'];
        }
        $extra_data = $this->manager->get_extra_info($page_goods_ids);
        for ($i=0; $i < $goods_count; $i++)
        {
            $_goods_id = $goods_list['data'][$i]['goods_id'];
            $goods_list['data'][$i] = array_merge($goods_list['data'][$i], $extra_data[$_goods_id]);
        }


        $url_sort .= '&amp;order=' . ($_GET['order'] == 'ASC' ? 'DESC' : 'ASC') . "&amp;page=$page";

        if (isset($condition['max_price']) || isset($condition['min_price']))
        {
            $price_range = array();
            $this->assign('show_price_range', 1);
        }
        else
        {
            $price_range = $this->get_price_range($condition, 5);
        }

        $brand_arr = $this->manager->get_brand_list($condition, 20);
        $color_arr = $this->manager->get_color_list($condition);

        $filte_url = '';
        foreach ($filter_data as $val)
        {
            $filte_url .= $val['url'];
        }
        $display_url = '';
        foreach ($url_extra as $val)
        {
            $display_url .= $val;
        }

        $region = new RegionManager();
        $regions_list = $region->get_regions_list();

        if (isset($_GET['region']))
        foreach($regions_list AS $key => $val)
        {
            if ($val['region_id'] == trim($_GET['region']))
            {
                $this->assign('region_name', $val['region_name']);
            }
        }
        $this->assign('regions_list', $regions_list);

        //$this->assign('title', $title);
        $this->set_title($title_meta);
        $this->assign('location_data', $location_data);
        $this->assign('category', $this->cate_mod->get_options(2));
        $this->assign('btn_act', $btn_act);
        $this->assign('goods_list', $goods_list);
        $this->assign('title', $title);
        $this->assign('order', $_GET['order'] == 'ASC' ? 'DESC' : 'ASC');
        $this->assign('display_url', $display_url);
        $this->assign('url_sort', $url_sort);
        $this->assign('price_range', $price_range);
        $this->assign('brand_arr', $brand_arr);
        $this->assign('color_arr', $color_arr);
        $this->assign('filter_data' , $filter_data);
        $this->assign('filte_url', $filte_url);
        $this->assign('display_url', $display_url);
        $this->assign('page_url', $page_url);
        $this->assign('url_extra', $url_extra);
        $this->assign('url_base', $url_base);
        $this->assign('url_format', $url_base . $add_url . '&page=%d');
        $this->assign('show_type', $show_type);
        $this->assign('cate_family', $this->_get_family($cate_info['parent_id']));
        $this->assign('cate_info', $cate_info);

        /* 取得最新成交 */
        $this->assign('latest_sold', $this->_get_recent_deal());

        /* 更新pageview */
        $this->update_pageview(0);

        return true;
    }

    /**
     * 获得推荐店铺
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

    /**
     * 获得商品列表的价格区间
     *
     * @param string goods_ids 商品ids
     * @param int num 价格区间个数
     * @return array price_range
     */
    function get_price_range($condition, $num)
    {
        $price_range = array();
        $price_limit = $this->manager->get_limit_price($condition);
        if ($price_limit['max_price'] - $price_limit['min_price'] < 1)
        {
            return array();
        }
        $range = ceil(($price_limit['max_price'] - $price_limit['min_price']) / $num);
        for ($i = 0; $i < $num; $i++)
        {
            $left = floor($price_limit['min_price'] + $i * $range);
            $right = $left + $range;
            $right = $right > $price_limit['max_price'] ? intval($price_limit['max_price']) : $right;
            $price_range[] = array('left' => $left, 'right' => $right);
        }
        return $price_range;
    }

    /**
     *  获取站内快讯
     *
     *  @param  none
     *  @return array
     */
    function _get_site_news()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.article.php');
        $article_mng = new ArticleManager(0);
        $site_news = $article_mng->get_list(1, array('cate_id' => 3));

        return $site_news['data'];
    }

    /**
     *  获取促销商品
     *
     *  @author    wj
     *  @param  none
     *  @return array
     */
    function _get_promoted_goods()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $goods_mng = new GoodsManager(0);
        $promoted_goods = $goods_mng->get_list(1, array('is_promoted' => 1, 'sell_able'=>1));

        return $promoted_goods['data'];
    }


    /**
     *  获取最新成交
     *
     *  @param  none
     *  @return array
     */

    function _get_recent_deal()
    {
        include_once(ROOT_PATH . '/includes/manager/mng.order.php');
        $order_manager = new OrderManager($this->_store_id);
        $latest_sold = $order_manager->list_recent_deal();

        return $latest_sold;
    }

    /**
     * 获取此分类的同级分类、父级分类以及下级分类
     *
     * @author      wj
     * @param       int     $parent_id
     *
     * @return      array
     */
    function _get_family($parent_id)
    {
        if (isset($parent_id))
        {
            $cate_mod = new Category($this->_cate_id);
            $parents = $cate_mod->list_parent();
            $son_cate = $cate_mod->list_child(1);
            unset($son_cate[$this->_cate_id]);
            $brothers = array();
            if ($this->_cate_id)
            {
                $parent_mod = new Category($parent_id);
                $brothers = $parent_mod->list_child(1);
                unset($brothers[$parent_id]);
            }

        }
        else
        {
            //没有选择cate id 的情况,只用获取第一级的分类
            $cate_mod = new Category(0);
            $parents = array();
            $son_cate = array();
            $brothers = $cate_mod->list_child(1);
        }

        return array('parents' => $parents, 'sons' => $son_cate, 'brothers' => $brothers);
    }

}

?>