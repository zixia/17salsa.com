<?php

/**
 * ECMALL: google sitemap
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.google_sitemap.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class google_sitemap
{
    var $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    var $charset = "UTF-8";
    var $footer = "\t</urlset>\n";
    var $items = array();

    /**
     * ����һ���µ�����
     *@access   public
     *@param    google_sitemap  item    $new_item
     */
    function add_item($new_item)
    {
        $this->items[] = $new_item;
    }

    /**
     * ����XML�ĵ�
     *@access    public
     *@param     string  $file_name  ����ṩ���ļ����������ļ������򷵻��ַ���.
     *@return [void|string]
     */
    function build( $file_name = null )
    {
        $map = $this->header . "\n";

        foreach ($this->items AS $item)
        {
            $item->loc = htmlentities($item->loc, ENT_QUOTES);
            $map .= "\t\t<url>\n\t\t\t<loc>$item->loc</loc>\n";

            // lastmod
            if ( !empty( $item->lastmod ) )
                $map .= "\t\t\t<lastmod>$item->lastmod</lastmod>\n";

            // changefreq
            if ( !empty( $item->changefreq ) )
                $map .= "\t\t\t<changefreq>$item->changefreq</changefreq>\n";

            // priority
            if ( !empty( $item->priority ) )
                $map .= "\t\t\t<priority>$item->priority</priority>\n";

            $map .= "\t\t</url>\n\n";
        }

        $map .= $this->footer . "\n";

        if (!is_null($file_name))
        {
            return _at('file_put_contents',$file_name, $map, LOCK_EX);
        }
        else
        {
            return $map;
        }
    }

}

class google_sitemap_item
{
    /**
     *@access   public
     *@param    string  $loc        λ��
     *@param    string  $lastmod    ���ڸ�ʽ YYYY-MM-DD
     *@param    string  $changefreq ����Ƶ�ʵĵ�λ (always, hourly, daily, weekly, monthly, yearly, never)
     *@param    string  $priority   ���ȼ� 0-1
     */
    function google_sitemap_item($loc, $lastmod = '', $changefreq = '', $priority = '')
    {
        $this->loc = $loc;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
    }
}

?>