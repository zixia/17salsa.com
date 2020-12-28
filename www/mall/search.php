<?php

/**
 * ECMALL: ��Ʒ����������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: search.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('CTRL_DOMAIN', 'mall');

require_once(ROOT_PATH . '/includes/ctl.searchbase.php');

class SearchController extends SearchBaseController
{
    var $_allowed_actions = array('view');

    function __construct($act)
    {
        $this->SearchController($act);
    }
    function SearchController($act)
    {
        parent::__construct($act);
    }

    /*
     * ��ʾ�������
     *
     * @author  weberliu
     * @return void
     */
    function view()
    {
        $_GET['keywords'] = trim($_GET['keywords']);
        if (empty($_GET['keywords']) && empty($_GET['tag_words']))
        {
            $_GET['app'] = 'category';

            $arr = array();

            foreach ($_GET AS $key=>$val)
            {
                $arr[] = "{$key}={$val}";
            }

            $target_url = 'index.php?' . implode('&', $arr);
            $this->redirect($target_url);
        }
        else
        {
            if (parent::view() == false)
            {
                return;
            }
            $this->display('goods_list', 'mall');
        }
    }

}

?>