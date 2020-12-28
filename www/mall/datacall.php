<?php

/**
 * ECMALL: ���ݵ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: datacall.php 5522 2008-08-14 01:52:08Z Scottye $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.datacall.php');
require_once(ROOT_PATH. '/includes/manager/mng.goods.php');

class DataCallController extends ControllerFrontend
{
    var $_allowed_actions = array('view');
    var $_doc_contents = '';

    function __construct($act)
    {
        $this->DataCallController($act);
    }

    function DataCallController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::__construct($act);
    }

    /**
     * ��ȡ�ַ�������
     *
     * @author liupeng
     * @param  int  $val �ַ���id
     * @return string
     */
    function get_charset($val)
    {
        switch (intval($val))
        {
            case 1 :
                $charset = 'utf-8';
            break;
            case 2 :
                $charset = 'gbk';
            break;
            case 3 :
                $charset = 'big5';
            break;
            default :
                $charset = CHARSET;
            break;
        }
        return $charset;
    }

    /**
     * ��ȡ�ַ�������
     *
     * @author liupeng
     * @return string
     */
    function view()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$this->is_cache($id))
        {
            $dc = new DataCall($id);
            $info = $dc->get_info();

            if (empty($info))
            {
                die('Hacking');
            }

            $this->_cache_time     = $info['cache_time'];
            $this->_goods_name_len = $info['goods_name_length'];
            $this->_expires        = time() + $this->_cache_time;

            $this->_charset = $this->get_charset($info['content_charset']);

            $this->_init_template();

            $manager = new GoodsManager($info['store_id']);

            $_GET['sort'] = 'sort_weighing';
            $_GET['order'] = 'ASC';

            $condition = array();
            $condition['sell_able'] = 1;

            if (empty($info['store_id']))
            {
                $condition['mall_cate_id'] = $info['cate_id'];
            }
            else
            {
                $condition['store_cate_id'] = $info['cate_id'];
            }

            if ($info['recommend_option'][0] == 1 && empty($info['store_id']))
            {
                $condition['is_m_best'] = 1;
            }
            elseif ($info['recommend_option'][0] == 1)
            {
                $condition['is_s_best'] = 1;
            }

            if ($info['recommend_option'][1] == 1)
            {
                $condition['is_m_hot'] = 1;
            }

            if (!empty($info['brand_id']))
            {
                $condition['brand_id'] = $info['brand_id'];
            }

            $list = $manager->get_list(1, $condition, $info['goods_number']);

            $this->_doc_write($info['template']['header']);
            $body = $this->_parse_var($info['template']['body']);

            $this->assign('list', $list['data']);

            $body = $this->_template->fetch('str:{foreach from=$list item=goods}'.$body.'{/foreach}');
            $this->_doc_write($body);
            $this->_doc_write($info['template']['footer']);
            $this->_save_cache(intval($_GET['id']));
        }

        $this->doc_output();
    }

    /**
     * ��������
     *
     * @author liupeng
     * @param  string $str ����
     * @return string
     */
    function _parse_var($str)
    {
        $code = str_replace('{goods_name}', '{$goods.goods_name|escape|truncate:'.$this->_goods_name_len.'}', $str);
        $code = str_replace('{goods_img_url}', '{image file=$goods.default_image width=100 height=100 absolute_path=true}', $code);
        $code = str_replace('{goods_price}', '{$goods.store_price}', $code);
        $code = str_replace('{goods_url}', site_url() . '/{url src=index.php?app=goods&amp;id=$goods.goods_id}', $code);

        return $code;
    }

    /**
     * ����Ƿ񻺴�
     *
     * @author liupeng
     * @param  int  $id  ����ID
     * @return bool
     */
    function is_cache($id)
    {
        $file_path = ROOT_PATH . '/temp/js/datacallcache'. $id .'.js';

        if (is_file($file_path))
        {
            $content = file_get_contents($file_path);
            $idx = strpos($content , "%^@#!*");
            $str = substr($content, 0 , $idx);

            $arr = explode('|', $str);

            $this->_chareset = $arr[0];
            $this->_expires  = $arr[1];

            if (time() > $this->_expires)
            {
                return false;
            }
            else
            {
                $this->_doc_contents = substr($content, $idx+6);
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * ���滺��
     *
     * @author liupeng
     * @param  $id  ����ID
     * @return void
     */
    function _save_cache($id)
    {
        $file_path = ROOT_PATH . '/temp/js/datacallcache'. $id .'.js';

        file_put_contents($file_path, "{$this->_charset}|{$this->_expires}%^@#!*" . $this->_doc_contents);
    }

    /**
     * ���JS
     *
     * @author  liupeng
     * @param   string  $str  ����
     * @return  void;
     */
    function _doc_write($str)
    {
        $str = str_replace("\r", "", $str);
        $str = str_replace("\n", "", $str);
        $str = str_replace("'", "\\'", $str);
        $this->_doc_contents .= 'document.write(\''. $str .'\');';
    }

    /**
     * ���JS
     *
     * @author  liupeng
     * @return  void;
     */
    function doc_output()
    {
        header("Content-type:text/html;charset=" . $this->_chareset , true);
        echo ecm_iconv(CHARSET, $this->_chareset, $this->_doc_contents);
    }
}

?>