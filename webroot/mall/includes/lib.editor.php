<?php

/**
 * ECMall: UBB�༭����ط���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: lib.editor.php 4835 2008-06-25 01:28:27Z Garbin $
 */

$GLOBALS['UBB_UPLOADED_ITEM_IDS'] = array();

class Editor
{
    /**
     *  ����UBB�����е������ַ�
     *
     *  @author Garbin
     *  @param  string $str
     *  @return string
     */
    function filter($str)
    {
        preg_match_all('/\[localfile\](\d+)\[\/localfile\]/i', $str, $local_files);
        $str = Editor::parse_upload_file($local_files[1], $str);
        $str = Editor::drop_attachs($str);
        return $str;
    }

    /**
     *  �����ϴ��ļ�
     *
     *  @author  Garbin
     *  @param   array $inserted_que    �Ѳ��뵽�ĵ��еı��ظ����б�
     *  @param   string $str            UBB�ĵ�
     *  @return  string
     */
    function parse_upload_file($inserted_que, $str)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $file_manager = new FileManager($_SESSION['store_id']);
        $src = array();
        $rzt = array();

        foreach ($_FILES as $_k => $f)
        {
            if (ereg('editor_upload_file_[0-9]+', $_k) && is_uploaded_file($f['tmp_name']))
            {
                $_fid = substr($_k, 19);
                $_ur = $file_manager->add($f);
                if (!$_ur)
                {
                    continue;
                }
                else
                {
                    $_uf = current($_ur);
                    $GLOBALS['UBB_UPLOADED_ITEM_IDS'][] = $_uf['file_id'];
                    $attach_tag = '[attach=' . $_uf['file_path'] . ']' . $_uf['file_id'] . '[/attach]';

                    /* �����ڣ�����뵽�ĵ�ĩβ */
                    if (!in_array($_fid, $inserted_que))
                    {
                        $str .= '\n' . $attach_tag;
                    }
                    else
                    {
                        $src[] = '[localfile]' . $_fid . '[/localfile]';
                        $rzt[] = $attach_tag;
                    }
                }
            }
        }

        return str_replace($src, $rzt, $str);
    }

    /**
     *  ����UBB��������ַ�ΪHTML
     *
     *  @author Garbin
     *  @param  string $str
     *  @return string
     */
    function parse($str)
    {
        $str = html_entity_decode($str);
        $patterns = array( '/\[align=(left|center|right)\]/i',
                           '/\[img(=(\d+),(\d+))?\]javascript:(.+?)\[\/img\]/i',
                           '/\[img=(\d+),(\d+)\](.+?)\[\/img\]/i',
                           '/\[img\](.+?)\[\/img\]/i',
                           '/\[hr\](\[\/hr\])?/',
                           '/\[url\]((https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|bctp:\/\/|ed2k:\/\/|thunder:\/\/|synacast:\/\/|\{\$){1}([^\["\']+?))\[\/url\]/i',
                           '/\[url=((https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|bctp:\/\/|ed2k:\/\/|thunder:\/\/|synacast:\/\/|\{\$){1}([^\["\']+?))\]/i',
                           '/\[url\]([^\["\']+?)\[\/url\]/i',
                           '/\[url=([^\["\']+?)\]/i',
                           '/\[\/url\]/',
                           '/\[list=([\d]+)\]/i',
                           '/\[list=([a-z]+)\]/i',
                           '/\[list\]/i',
                           //'/\[\*\](((?!\[\*\]|\[\/list\]).)*)/is',
                           '/\[backcolor=([^\]]+)\]/i',
                           '/\[color=([^\]]+)\]/i',
                           '/\[font=([^\]]+)\]/i',
                           '/\[size=([^\]]+)\]/i',
                           '/\[attach=([^\]]+\.(jpg|png|gif|jpeg))\](\d+)\[\/attach\]/i',
                           '/\[attach=([^\]]+)\](\d+)\[\/attach\]/i',
                           '/\[table=(([%\d]+)((,)([a-z#0-9]+))?)\]/i'
        );
        $replacements = array(
                          '<div style="text-align:\1;">',
                          ' ',
                          '<img src="\3" alt="\3" width="\1" height="\2" />',
                          '<img src="\1" alt="\1" />',
                          '<hr />',
                          '<a href="\1" title="\1" target="_blank">\1</a>',
                          '<a href="\1" title="\1" target="_blank">',
                          '<a href="http://\1" title="\1" target="_blank">\1</a>',
                          '<a href="http://\1" title="\1" target="_blank">',
                          '</a>',
                          '<ul start="\1" style="list-style:decimal;">',
                          '<ul start="\1" style="list-style:lower-alpha;">',
                          '<ul style="list-style:square;">',
                          //'<li>\1</li>',
                          '<span style="background:\1;">',
                          '<font color="\1">',
                          '<span style="font-family:\1;">',
                          '<font size="\1">',
                          '<img src="\1" alt="\1" />',
                          '<a href="\1">attach:\2</a>',
                          '<table width="\2" bgcolor="\5">'
        );

        $str = str_replace(array('[b]',
                                 '[/b]',
                                 '[i]',
                                 '[/i]',
                                 '[u]',
                                 '[/u]',
                                 '[indent]',
                                 '[/indent]',
                                 '[/align]',
                                 '[/backcolor]',
                                 '[/color]',
                                 '[/size]',
                                 '[/list]',
                                 '[tr]',
                                 '[td]',
                                 '[/tr]',
                                 '[/td]',
                                 '[/table]',
                                 '[/font]',
                                 '[*]'),
                           array('<strong>',
                                 '</strong>',
                                 '<i>',
                                 '</i>',
                                 '<u>',
                                 '</u>',
                                 '<blockquote>',
                                 '</blockquote>',
                                 '</div>',
                                 '</span>',
                                 '</font>',
                                 '</font>',
                                 '</ul>',
                                 '<tr>',
                                 '<td>',
                                 '</tr>',
                                 '</td>',
                                 '</table>',
                                 '</span>',
                                 '<li>'),
                           $str);
        $str = preg_replace($patterns, $replacements, $str);

        return nl2br($str);
    }

    /**
     *  ��ȡ�б��HTML����
     *
     *  @param  string $str
     *  @return string
     */
    function get_list_html($str, $start)
    {
        $str = preg_replace('/\[\*\](((?!\[\*\]).)+)/s', is_numeric($start) ? '<li>\1</li>' : '<li type="a">\1</li>', $str);

        return $str;
    }

    /**
     * ��html����ת��Ϊbbcode����
     *
     * @author   wj
     * @param    stirng      $html       html����
     *
     * @return  stirng
     */
    function html2bbcode($html)
    {
        $pattern = array(
            '/<table.*?>/i',
            '/<th.*?>/i',
            '/<tr.*?>/i',
            '/<td.*?>/i',
            '/<ul.*?>/i',
            '/<ol.*?>/i',
            '/<li.*?>/i',
            '/<font.*?color=["|\']?([#A-Z0-9a-z]+).*?>(.*?)<\/font>/i',
            '/<font.*?size=["|\']?([A-Z0-9a-z]+).*?>(.*?)<\/font>/i',
            '/<b.*?>/i',
            '/<strong.*?>/i',
            '/<i.*?>/i',
            '/<u.*?>/i',
        );
        $replace = array(
            '[table=100%,white]',
            '[tr]',
            '[tr]',
            '[td]',
            '[list]',
            '[list=1]',
            '[*]',
            '[color=\1]\2[/color]',
            '[size=\1]\2[/size]',
            '[b]',
            '[b]',
            '[i]',
            '[u]',
        );
        $bbcode = preg_replace($pattern, $replace, $html);
        $pattern = array(
            '</b>',
            '</strong>',
            '</i>',
            '</u>',
            '</table>',
            '</tr>',
            '</th>',
            '</td>',
            '</ul>',
            '</ol>',
            '</li>',
        );
        $replace = array(
            '[/b]',
            '[/b]',
            '[/i]',
            '[/u]',
            '[/table]',
            '[/tr]',
            '[/tr]',
            '[/td]',
            '[/list]',
            '[/list]',
            ''
        );
        $fun = function_exists('str_ireplace') ? 'str_ireplace' : 'str_replace';
        $bbcode = call_user_func($fun, $pattern, $replace, $bbcode);
        $bbcode = strip_tags($bbcode);
        //����һЩhtml��ת����Ϣ
        $tran = array('&quot;'=>'"', '&amp;'=>'&', '&lt;'=>'<', '&gt;'=>'>', '&nbsp;'=>' ');
        $bbcode = strtr($bbcode, $tran);

        return $bbcode;
    }

    /**
     *  ɾ������
     *
     *  @author Garbin
     *  @param  string $str
     *  @return string
     */
    function drop_attachs($str)
    {
        if (isset($_POST['drop_editor_attachs']) && is_array($_POST['drop_editor_attachs']))
        {
            $partterns = array();
            $replacments = array();
            foreach ($_POST['drop_editor_attachs'] as $_k)
            {
                $partterns[] = '/\[attach=([^\]]+)\]' . intval($_k) . '\[\/attach\]/i';
                $replacments[] = '';
            }
            $str = preg_replace($partterns, $replacments, $str);
        }

        return $str;
    }

    /**
     *  ת��html����
     *
     *  @author liupeng
     *  @param  string  $string HTML����
     *  @return string
     */
    function shtmlspecialchars($string)
    {
        if(is_array($string))
        {
            foreach($string as $key => $val)
            {
                $string[$key] = shtmlspecialchars($val);
            }
        }
        else
        {
            $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
            str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
        }
        return $string;
    }

    /**
     *  ����HTML����
     *
     *  @author liupeng
     *  @param  string  $html HTML����
     *  @return string
     */
    function check_html($html)
    {

        $html = stripslashes($html);

        $html = preg_replace("/\<script.*?\>.*?\<\/script\>/is", '', $html); //ȥ��script
        $html = preg_replace("/\<style.*?\>.*?\<\/style\>/is", '', $html);   //ȥ��style
        preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
        $searchs = $replaces = array();
        if($ms[1])
        {
            $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';//����ı�ǩ
            $ms[1] = array_unique($ms[1]);
            foreach ($ms[1] as $value)
            {
                $value = Editor::shtmlspecialchars($value);
                $searchs[] = "&lt;".$value."&gt;";
                $value = str_replace(array('\\','/*'), array('.','/.'), $value);
                $value = preg_replace(array("/(javascript|script|eval|behaviour|expression)/i", "/(\s+|&quot;|')on/i"), array('.', ' .'), $value);
                if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value))
                {
                    $value = '';
                }
                $replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
            }
        }
        $html = Editor::shtmlspecialchars($html);
        if($searchs)
        {
            $html = str_replace($searchs, $replaces, $html);
        }

        $html = preg_replace("/\&amp\;lt\;(.*?)\&amp\;gt\;/is", '&lt;\\1&gt;', $html);//�ָ�<>��ʾ
        //$html = preg_replace("/\<(.*?)width[=|:].*?(\s|\>|\'|\"|;)/is", '<\\1\\2', $html);
        $html = addslashes($html);

        $html = preg_replace("/&amp;gt;/is", '&gt;', $html);   //�滻&gt;
        $html = preg_replace("/&amp;lt;/is", '&lt;', $html);   //�滻&lt;

        return $html;
    }
}

?>
