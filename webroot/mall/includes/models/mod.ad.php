<?php

/**
 * ECMALL: 广告模型
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 构造函数
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
     * 构造函数
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
    * 获取广告信息
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
    * 获取广告代码
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
            /* 图片广告 */
            case 0 :
            $img = (strpos(strtolower($res['ad_code']), 'http://') === 0) ? $res['ad_code'] : site_url() . '/' . $res['ad_code'] ;
                $html .= "<a href='$url&amp;url=". urlencode($res[ad_link]) . "&amp;id=$res[ad_id]' target='_blank'><img src='{$img}' /></a>";
            break;
            /* FLASH广告 */
            case 1 :
                $html .= $this->_create_flash_code($res['ad_code'], $res['width'], $res['height']);
            break;
            /* 代码广告 */
            case 2 :
                $html .= $res['ad_code'];
            break;
            /* 代码广告 */
            case 3 :
                $html .= "<a href='$url&url=". urlencode($res[ad_link]) . "&id=$res[ad_id]' target='_blank'>$res[ad_code]</a>";
            break;
        }

        return $html;
   }

    /**
    * 生成FLASH广告代码
    *
    * @param string $url flash文件URL
    * @param string $url flash文件URL
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
    * 计算点击次数
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
