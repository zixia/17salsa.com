<?php

/**
 * ECMALL: ���ģ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.ad.php 6116 2008-12-05 06:18:58Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

require_once(ROOT_PATH. '/includes/models/mod.base.php');

class Ad extends Model
{
    var $_table = '`ecm_ad`';
    var $_key   = 'ad_id';
    var $_position_id = 0;
    var $is_edit_mode = false;

    /**
     * ���캯��
     *
     * @access  public
     * @param   int article_id
     *
     * @return void
     */
    function __construct ($id, $position_id = null)
    {
        $this->Ad($id, $position_id);
    }

    /**
     * ���캯��
     *
     * @param   int article_id
     *
     * @return void
     */
    function Ad($id, $position_id = null)
    {
        $this->_id = $id;
        $this->_position_id = $position_id;
    }

    /**
    * ��ȡ�����Ϣ
    *
    * @param
    *
    * @return void
    */
   function get_info()
   {
        $now = gmtime();
        $sql = "SELECT ad.*, adp.position_name, adp.width, adp.height, rand() AS rnd FROM `ecm_ad` AS ad LEFT JOIN `ecm_ad_position` AS adp ON adp.position_id = ad.position_id WHERE 1";

        if (!defined('IS_BACKEND'))
        {
            $sql .= " AND ad.enabled = 1 AND (ad.start_time<'$now' AND ad.end_time>'$now')";
        }

        if ($this->_id == null && $this->_position_id !=null)
        {

            $sql .= " AND adp.position_id = '$this->_position_id'";
        }
        else
        {
            $sql .= " AND ad.ad_id = '$this->_id'";
        }

        $sql .= " ORDER BY rnd LIMIT 1";

        $res = $GLOBALS['db']->getRow($sql);

        return $res;
   }

    /**
    * ��ȡ������
    *
    * @param
    *
    * @return void
    */
   function get_code()
   {
        $html = "";
        $url = site_url() . '/index.php?app=ads&amp;act=jump';

        $res = $this->get_info();

        $now = gmtime();
        if ($res['enabled'] == '0' || ($now > $res['end_time'] || $now < $res['start_time']))
        {
            return $html;
        }

        switch (intval($res['ad_type']))
        {
            /* ͼƬ��� */
            case 0 :
            $img = (strpos(strtolower($res['ad_code']), 'http://') === 0) ? $res['ad_code'] : site_url() . '/' . $res['ad_code'] ;
                $html .= "<a href='$url&amp;url=". urlencode($res[ad_link]) . "&amp;id=$res[ad_id]' target='_blank'><img src='{$img}' /></a>";
            break;
            /* FLASH��� */
            case 1 :
                $html .= $this->_create_flash_code($res['ad_code'], $res['width'], $res['height']);
            break;
            /* ������ */
            case 2 :
                $html .= $res['ad_code'];
            break;
            /* ������ */
            case 3 :
                $html .= "<a href='$url&url=". urlencode($res[ad_link]) . "&id=$res[ad_id]' target='_blank'>$res[ad_code]</a>";
            break;
        }

        return $html;
   }

    /**
    * ����FLASH������
    *
    * @param string $url flash�ļ�URL
    * @param string $url flash�ļ�URL
    *
    * @return void
    */
   function _create_flash_code($url, $width, $height)
   {
        $url = (strpos(strtolower($url), 'http://') === 0) ? $url : site_url() . '/' . $url ;
        $code .= "<object width='$width' height='$height' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0'>";
        $code .= "<param name='movie' value='$url'><param name='quality' value='high'>";
        $code .= "<param name='menu' value='false'><param name='wmode' value='opaque'>";

        $code .= "<embed src='$url' width='$width' height='$height' wmode='opaque' menu='false' quality='high' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />";
        $code .= "</object>";

        return $code;
   }

    /**
    * ����������
    *
    * @return void
    */
   function click_count()
   {
        $sql = "UPDATE $this->_table SET click_count=click_count+1 WHERE ad_id='$this->_id'";

        $GLOBALS['db']->query($sql);
   }
}

?>
