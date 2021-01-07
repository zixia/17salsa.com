<?php

/**
 * ECMALL: 编辑器语言输出
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.base.php 5335 2008-07-29 09:30:41Z Liupeng $
 */

define('ROOT_PATH', str_replace('editor', '', dirname(__FILE__)));
include(ROOT_PATH . '/data/inc.config.php');
include(ROOT_PATH . '/languages/'.LANG.'/editor.php');
define('CHARSET', substr(LANG, strpos(LANG, '-') + 1));

header("Content-type:text/html;charset=" . CHARSET, true);
$contents = "";
foreach ($lang AS $key => $value)
{
    if ($contents != "")
    {
        $contents .= ",";
    }
    $contents .= "$key:\"$value\"";
}

echo "if (!lang){var lang = {};}\r\n editorLanguage={" . $contents . "};Object.extend(lang, editorLanguage);";
?>