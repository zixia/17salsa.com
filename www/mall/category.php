<?php

/**
 * ECMall: ��Ʒ����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: category.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('CTRL_DOMAIN', 'mall');

require_once(ROOT_PATH . '/includes/ctl.searchbase.php');

class CategoryController extends SearchBaseController
{
    var $_allowed_actions = array('view');

    function __construct($act)
    {
        $this->CategoryController($act);
    }

    function CategoryController($act)
    {
        parent::__construct($act);
    }

    /* �鿴�����µ���Ʒ */
    function view()
    {
        $cache_id = '';
        $depand_param = array();
        $_GET['keywords'] = '';
        if (!empty($_GET['page'])) $depand_param['page'] = intval($_GET['page']);
        if (!empty($_GET['sort'])) $depand_param['sort'] = $_GET['sort'] == 'store_price' ? 'store_price' : 'goods_id';
        if (!empty($_GET['order'])) $depand_param['order'] = $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
        if (!empty($_GET['show_type'])) $depand_param['show_type'] = $_GET['show_type'] == 'l_l' ? 'l_l' : 'l_t';
        $cache_id = 'category' . $this->_cate_id . '_' . $this->crc32_code($depand_param);

        if (isset($_GET['color_rgb']) || isset($_GET['min_price']) || isset($_GET['max_price']) || isset($_GET['brand_id'])  || isset($_GET['region']))
        {
            $cache_id = '';
        }

        if (empty($cache_id) || ($cache_id && (!$this->is_cached($cache_id))))
        {
            if (parent::view() == false)
            {
                return;
            }
        }
        $this->assign('feed_url', 'index.php?app=feed&amp;cate_id=' . $this->_cate_id);
        $this->display('goods_list', 'mall', $cache_id);
    }

}

?>
