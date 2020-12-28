<?php

/**
 * ECMALL: Rich Editor
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: editor.php 6009 2008-10-31 01:55:52Z Garbin $
 */
class RichEditor {
    var $editor_name;
    var $base_path;
    var $width = "100%";
    var $height = '400';
    var $value = '';
    var $toolbar = 'full';
    var $file_manager = "attachment.php";
    var $border_color = "#a7a6aa";
    var $lang = '';
    var $attachments = array();

    function RichEditor($name, $base_path='.') {
        $this->editor_name  = $name;
        $this->base_path    = $base_path;
        $this->file_manager = $this->base_path . '/editor/attachment.php';
    }

    function build($toolbar='', $lang_attach='', $lang_drop_attach='')
    {
        $toolbar = empty($toolbar) ? $this->toolbar : $toolbar;
        $value = htmlspecialchars($this->value);
        $source = "$this->base_path/editor/editor.html?name=$this->editor_name&amp;toolbar=$toolbar&amp;fileManager=$this->file_manager&amp;borderColor=$this->border_color&amp;lang=$this->lang&amp;basePath=$this->basePath";
        $html = "\n<!-- RICH EDITOR -->\n<div id=\"{$this->editor_name}__container\">\n";
        $html .= "<iframe id=\"{$this->editor_name}___frame\" src=\"$source\" width=\"$this->width\" height=\"$this->height\" frameborder=\"0\" scrolling=\"no\" style=\"background:#FFF\"></iframe>\n";
        $html .= "<input type=\"hidden\" name=\"$this->editor_name\" value=\"$value\" id=\"{$this->editor_name}___hidden\"/>\n";
        $html .= "<div style=\"text-align:left;\">\n";
        $html .= "<p style='text-align:left'><strong>$lang_attach</strong></p>\n";
        $html .= "<script type='text/javascript'>\n";
        $html .= "function dropAttachment(lnk, id, path) {var obj=document.getElementById('{$this->editor_name}___frame'); obj.contentWindow.dropAttachment(lnk, id, path);}\n";
        $html .= "</script>\n";
        $html .= "<ul style=\"list-style:disc; margin:auto;padding:auot 12px\" id=\"{$this->editor_name}__attachments\">\n";

        foreach ($this->attachments AS $val)
        {
            $html .= "<li>$val[orig_name]&nbsp;[$val[file_size]]&nbsp;<a href='javascript:;' fileId='$val[file_id]' filePath='$val[file_name]'>$lang_drop_attach</a></li>\n";
        }

        $html .= "</ul></div>\n";
        $html .= "</div>\n<!-- /RICH EDITOR -->\n\n";

        return $html;
    }

    function show($toolbar='') {
        echo $this->build($toolbar);
    }
}

?>
