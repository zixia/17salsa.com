<?php

/**
 * ECMALL: ������֧����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ȡ��������
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
                            $item = $args[$i]; //��ȡkey���˳�
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
     *  �ж����԰��Ƿ񱻼��ع�
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
     *  �������԰�
     *  note:��ָ����$files������is_detectΪFALSE�ǣ��÷������������԰��������ڼ��ָ����files���Ƿ��б����ع�
     *  @access public
     *  @params mixed $files, bool $is_detect
     *  @return mixed
     */

    function &load_lang($files = array(), $is_detect = FALSE)
    {

        static $lang_wrapper = NULL;

        /* ��¼�����ع��������ļ� */
        static $lang_packets   = array();

        if (!empty($files))
        {
            $lang_wrapper === NULL && $lang_wrapper = array();
            if ($is_detect)
            {
                /* ����Ƿ��Ѽ���ָ���İ�,ȫ�������Ѽ���ʱ�Ż᷵��TRUE */
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
                /* �������԰� */
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