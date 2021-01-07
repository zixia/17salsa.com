<?php

/**
 * ECMALL: rss feed
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: feed.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class FeedController extends ControllerFrontend
{
    var $_allowed_actions = array('view');

    function __construct($act)
    {
        $this->FeedController($act);
    }
    function FeedController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    function view()
    {
        $ver = isset($_GET['ver']) ? $_GET['ver'] : '2.00';
        $condition = array('sell_able' => 1);
        if (isset($_GET['cate_id']) && intval($_GET['cate_id']) > 0)
        {
            $condition['mall_cate_id'] = intval($_GET['cate_id']);
        }
        if (isset($_GET['store_id']) && intval($_GET['store_id']) > 0)
        {
            $condition['store_id'] = intval($_GET['store_id']);
        }

        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng_goods = new GoodsManager();
        $goods_list = $mng_goods->get_list(1, $condition, 20);
        $goods_list = $goods_list['data'];

        include_once(ROOT_PATH . '/includes/cls.rss.php');
        $uri = site_url() . '/';
        $rss = new RSSBuilder(CHARSET, $uri, htmlspecialchars($this->conf('mall_name')), '', $uri . 'data/images/animated_favicon.gif');
        $rss->addDCdata('', 'http://www.shopex.cn', date('r'));
        foreach ($goods_list as $goods)
        {
            $item_url = 'index.php?app=goods&amp;id=' . $goods['goods_id'];
            $about    = $uri . $item_url;
            $title    = htmlspecialchars($goods['goods_name']);
            $link     = $uri . $item_url . '&amp;from=rss';
            $desc     = htmlspecialchars($goods['goods_brief']);
            $subject  = htmlspecialchars($goods['mall_cate_name']);
            $date     = local_date($this->conf('mall_time_zone'), $goods['last_update']);

            $rss->addItem($about, $title, $link, $desc, $subject, $date);
        }

        header('Content-Type: application/xml; charset=' . CHARSET);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
        header('Last-Modified: ' . date('r'));
        header('Pragma: no-cache');
        $rss->outputRSS($ver);
    }
}

?>