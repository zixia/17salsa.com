<?php

/**
 * ECMALL: �༭���������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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