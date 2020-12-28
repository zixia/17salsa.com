<?php

/**
 * ECMALL: sitemap 设置
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: sitemap.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('HOMEPAGE_PRIORITY',     1);
define('CATEGORY_PRIORITY',     0.8);
define('CONTENT_PRIORITY',      0.5);

class SitemapController extends ControllerBackend
{
    function __construct($act)
    {
        $this->SitemapController($act);
    }
    function SitemapController($act)
    {
        if (empty($act))
        {
            $act = 'setting';
        }
        parent::__construct($act);
    }

    function setting()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            /*------------------------------------------------------ */
            //-- 设置更新频率
            /*------------------------------------------------------ */
            $this->logger = false;
            $config = array(
                'homepage_changefreq' => 'daily',
                'category_changefreq' => 'daily',
                'content_changefreq'  => 'daily',
            );

            $this->assign('config',           $config);
            $this->display('sitemap.html', 'mall');
        }
        else
        {
            /*------------------------------------------------------ */
            //-- 生成站点地图
            /*------------------------------------------------------ */
            include_once(ROOT_PATH . '/includes/cls.google_sitemap.php');

            $domain = site_url() . '/';
            $today  = local_date('Y-m-d');

            $sm     =& new google_sitemap();
            $smi    =& new google_sitemap_item($domain, $today, $_POST['homepage_changefreq'], HOMEPAGE_PRIORITY);
            $sm->add_item($smi);

            /* 商品分类 */
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            $mng_category = new Category();
            $cate_id_list = $mng_category->list_child_id();
            foreach ($cate_id_list as $cate_id)
            {
                $smi =& new google_sitemap_item($domain . 'index.php?app=category&cate_id=' . $cate_id, $today,
                    $_POST['category_changefreq'], CATEGORY_PRIORITY);
                $sm->add_item($smi);
            }

            /* 商品 */
            include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
            $mng_goods = new GoodsManager();
            $goods_list = $mng_goods->get_list(1, array('sell_able' => 1), 100000);
            foreach ($goods_list['data'] as $goods)
            {
                $smi =& new google_sitemap_item($domain . 'index.php?app=goods&id=' . $goods['goods_id'], $today,
                    $_POST['content_changefreq'], CONTENT_PRIORITY);
                $sm->add_item($smi);
            }

            $this->clean_cache();

            $sm_file = ROOT_PATH . '/sitemaps.xml';
            if ($sm->build($sm_file))
            {
                $this->show_message($this->str_format('generate_success', $sm_file));
            }
            else
            {
                $sm_file = ROOT_PATH . '/data/sitemaps.xml';
                if ($sm->build($sm_file))
                {
                    $this->show_message($this->str_format('generate_success', $sm_file));
                }
                else
                {
                    $this->show_message('generate_failed');
                }
            }
        }
    }
}

?>