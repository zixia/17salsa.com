<?php

/**
 * ECMall: UBB编辑器相关方法
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: lib.editor.php 4835 2008-06-25 01:28:27Z Garbin $
 */

$GLOBALS['UBB_UPLOADED_ITEM_IDS'] = array();

class Editor
{
    /**
     *  过滤UBB代码中的特殊字符
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
     *  处理上传文件
     *
     *  @author  Garbin
     *  @param   array $inserted_que    已插入到文档中的本地附件列表
     *  @param   string $str            UBB文档
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

                    /* 不存在，则插入到文档末尾 */
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
     *  解释UBB编码过的字符为HTML
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
     *  获取列表的HTML代码
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
     * 将html代码转换为bbcode代码
     *
     * @author   wj
     * @param    stirng      $html       html代码
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
        //处理一些html的转义信息
        $tran = array('&quot;'=>'"', '&amp;'=>'&', '&lt;'=>'<', '&gt;'=>'>', '&nbsp;'=>' ');
        $bbcode = strtr($bbcode, $tran);

        return $bbcode;
    }

    /**
     *  删除附件
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
     *  转义html代码
     *
     *  @author liupeng
     *  @param  string  $string HTML代码
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
     *  处理HTML代码
     *
     *  @author liupeng
     *  @param  string  $html HTML代码
     *  @return string
     */
    function check_html($html)
    {

        $html = stripslashes($html);

        $html = preg_replace("/\<script.*?\>.*?\<\/script\>/is", '', $html); //去掉script
        $html = preg_replace("/\<style.*?\>.*?\<\/style\>/is", '', $html);   //去掉style
        preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
        $searchs = $replaces = array();
        if($ms[1])
        {
            $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';//允许的标签
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

        $html = preg_replace("/\&amp\;lt\;(.*?)\&amp\;gt\;/is", '&lt;\\1&gt;', $html);//恢复<>显示
        //$html = preg_replace("/\<(.*?)width[=|:].*?(\s|\>|\'|\"|;)/is", '<\\1\\2', $html);
        $html = addslashes($html);

        $html = preg_replace("/&amp;gt;/is", '&gt;', $html);   //替换&gt;
        $html = preg_replace("/&amp;lt;/is", '&lt;', $html);   //替换&lt;

        return $html;
    }
}

?>
