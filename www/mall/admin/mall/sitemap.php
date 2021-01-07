<?php

/**
 * ECMALL: sitemap ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
            //-- ���ø���Ƶ��
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
            //-- ����վ���ͼ
            /*------------------------------------------------------ */
            include_once(ROOT_PATH . '/includes/cls.google_sitemap.php');

            $domain = site_url() . '/';
            $today  = local_date('Y-m-d');

            $sm     =& new google_sitemap();
            $smi    =& new google_sitemap_item($domain, $today, $_POST['homepage_changefreq'], HOMEPAGE_PRIORITY);
            $sm->add_item($smi);

            /* ��Ʒ���� */
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            $mng_category = new Category();
            $cate_id_list = $mng_category->list_child_id();
            foreach ($cate_id_list as $cate_id)
            {
                $smi =& new google_sitemap_item($domain . 'index.php?app=category&cate_id=' . $cate_id, $today,
                    $_POST['category_changefreq'], CATEGORY_PRIORITY);
                $sm->add_item($smi);
            }

            /* ��Ʒ */
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