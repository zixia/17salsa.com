<?php

/**
 * ECMALL: 多语言支持类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: cls.language.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class Language
{

    /**
     * 取得语言项
     *
     * @author  weberliu
     * @params  string  $key
     * @return  mixed
     */
    function &get($key = '')
    {
        $lang =& Language::load_lang();
        if (!empty($key))
        {
            $args = explode('.', $key);

            if (isset($lang[$args[0]]))
            {
                $num    = count($args);
                $item   = $lang[$args[0]];
                if ($num > 1)
                {
                    for ($i = 1; $i < $num; $i++)
                    {
                        if (isset($item[$args[$i]]))
                        {
                            $item = $item[$args[$i]];
                        }
                        else
                        {
                            $item = $args[$i]; //获取key并退出
                            break;
                        }
                    }
                }

                return $item;
            }
            else
            {
                return $key;
            }
        }
        else
        {
            return $lang;
        }
    }

    /**
     *  判断语言包是否被加载过
     *
     *  @access public
     *  @params mixed $packet_name
     *  @return BOOL
     */

    function is_loaded($packet_name)
    {
        return Language::load_lang($packet_name, TRUE);
    }


    /**
     *  加载语言包
     *  note:当指定了$files数组且is_detect为FALSE是，该方法不加载语言包，仅用于检测指定的files包是否有被加载过
     *  @access public
     *  @params mixed $files, bool $is_detect
     *  @return mixed
     */

    function &load_lang($files = array(), $is_detect = FALSE)
    {

        static $lang_wrapper = NULL;

        /* 记录所加载过的语言文件 */
        static $lang_packets   = array();

        if (!empty($files))
        {
            $lang_wrapper === NULL && $lang_wrapper = array();
            if ($is_detect)
            {
                /* 检测是否已加载指定的包,全部包都已加载时才会返回TRUE */
                $result = FALSE;
                !is_array($files) && $files = array($files);
                foreach ($files as $_k => $_v)
                {
                    if (array_search($_v, $lang_packets) !== FALSE)
                    {
                        $result = TRUE;
                    }
                    else
                    {
                        $result = FALSE;
                    }
                }

                return $result;
            }
            else
            {
                /* 加载语言包 */
                !is_array($files) && $files = array($files);
                foreach ($files as $_k => $_v)
                {
                    include($_v);
                    $jslang = array();
                    if (isset($lang['js']) && isset($lang_wrapper['js']) &&
                    is_array($lang['js']) && is_array($lang_wrapper['js']))
                    {
                        $jslang = array_merge($lang_wrapper['js'], $lang['js']);
                    }
                    is_array($lang) && $lang_wrapper = array_merge($lang_wrapper, $lang);
                    !empty($jslang) && $lang_wrapper['js'] = $jslang;
                    unset($lang);
                    $lang_packets[] = basename($_v, '.php');
                }
            }
        }

        if (isset($lang_wrapper['js']['validator']) && (!isset($lang_wrapper['js']['validator']['hack']))) $lang_wrapper['js']['validator']['hack'] = array('note'=>'hack for smarty');
        if (isset($lang_wrapper['js']['listname']) && (!isset($lang_wrapper['js']['listname']['hack']))) $lang_wrapper['js']['listname']['hack'] = array('note'=>'hack for smarty');
        return $lang_wrapper;
    }
}

function lang_file($file_name)
{
    return ROOT_PATH . '/languages/' . LANG . '/' . $file_name . '.php';
}
?>