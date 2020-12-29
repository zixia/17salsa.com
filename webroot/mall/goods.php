<?php

/**
 * ECMALL: 商品详情页面
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 查看商品信息
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

        /* 初始化goods_id, spec_id 以及mod_goods */
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

        /* 获取商品信息 */
        $info = $mod_goods->get_info();

        /* 验证状态 */
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

         /* 商品被管理员禁止 */
        if ($info['is_deny'] > 0)
        {
            if ((empty($_SESSION['admin_id']) || !empty($_SESSION['store_id'])))
            {
                $this->show_warning('goods_is_deny', 'back_home', site_url());
                return;
            }
            else
            {
                // todo: 管理员访问时，需要告诉管理员该商品已经被禁售
            }
        }

        if (!$this->is_cached($cache_id))
        {
            $spec_info = $mod_goods->get_spec_info($spec_id); //获取规格信息
            $info = array_merge($info, $spec_info);
            //商品数据再处理
            $info['to_save'] =  ($info['market_price'] > $info['store_price']) ? ($info['market_price'] -$info['store_price']) : 0;
            if ($info['keywords']) $info['tags'] = explode(' ', $info['keywords']);

            if ($info['type_id'] > 0)
            {
                $info['attr']   = $mod_goods->get_attribute(); //属性
            }
            $info['lang_new_level'] = $this->lang('level' . $info['new_level']); //新旧程度

            $goods_color    = $mod_goods->get_colors(addslashes($info['spec_name'])); //共有那些颜色
            $goods_spec     = $mod_goods->get_specs(addslashes($info['color_name'])); //共有那些规格

            if (count($goods_color) > 0) $this->assign('goods_color', $goods_color);
            if (count($goods_spec) > 0) $this->assign('goods_spec', $goods_spec);

            $gallery = $this->get_gallery($goods_id, addslashes($info['color_name'])); //获取相册数据

            if ($info['editor_type'] == 0)
            {
                $info['goods_desc'] = Editor::parse($info['goods_desc']);
            }

            $this->assign('goods',              $info);
            $this->assign('gallery',            $gallery);
            $this->assign('gallery_count',      count($gallery));
            $this->assign('similar_goods',      $mod_goods->get_similar_goods()); //相似商品
            $this->assign('compatible_goods',   $mod_goods->get_compatible_goods()); //适配商品

            /*  店铺数据 */
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

            /* 设置标签 */
            $this->assign('tabs', $this->get_tabs('view', $store_id));
            $url = "app=store&store_id=".$store_id;
            $this->assign('url', $url); //设置店铺的链接

            /* 获取推荐商品 */
            $manager = new GoodsManager($info['store_id']);
            $this->assign('best_goods', $manager->get_list(1, array('is_best'=>1, 'sell_able'=>1)));

            /* 获取商品的分类信息，作为当前位置 */
            $this->assign('location_data', $this->get_location_data($info['mall_cate_id'], $info['goods_name']));
            //页面标题
            $title_meta = array($info['goods_name'], $cate_info['cate_name']);
            $this->set_title($title_meta);

            //获取购买这个商品的用户
            $this->assign('bought_history', $mod_goods->get_bought_history());

            /*商品tag*/
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
            $info['stock'] = $mod_goods->get_stock($spec_id); //不缓存数据
            $this->assign('goods', $info);
        }

        /* 更新pageview */
        $this->update_pageview($this->_store_id);

        /* 赋值商品评论 */
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
            $mod_goods->update_click(); //更新商品信息
        }

        $this->display('goods', 'store', $cache_id);
    }

    /**
     * 获取tag相关的内容
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
            /* 获得一个随机的tag */
            $rand_key = array_rand($tags);
            $rand_tag = $tags[$rand_key];
        }
        else
        {
            $rand_tag = $tags;
        }

        include_once(ROOT_PATH . '/includes/cls.filecache.php');
        $cache = new filecache('tag_caches', 86400);
        $tag_relative = $cache->get($rand_tag); //获取缓存

        if ($tag_relative === false)
        {
            $apps = uc_call('uc_app_ls');


            /* 获取当前的多应用设置 */
            if (is_file(ROOT_PATH. '/data/inc.app_setting.php'))
            {
                include_once(ROOT_PATH. '/data/inc.app_setting.php');
            }

            if (empty($app_settings))
            {
                $app_settings = array(); //初始化app_setting
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

            /* 重新组合返回的数组 */
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
     * 格式化获得的相关内容
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
     * 取得对商品的评论
     *
     * @author  scottye
     * @param   int $goods_id   商品id
     */
    function _assign_comment($goods_id)
    {
        /* 商品评论 */
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

        /* 是否需要验证码 */
        $need_captcha = $this->conf('mall_captcha_status');
        $need_captcha = $need_captcha & 4;
        $this->assign('need_captcha', $need_captcha);
    }

    /**
     * 获得店铺的信息
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
     * 获取当前位置数组
     *
     * @author wj
     * @param int       $mall_cate_id       网站目录cate_id
     * @param string    $goods_name         商品名称
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
