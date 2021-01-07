<?php
/**
 * ECMall: �ַ���ת����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.iconv.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class Chinese
{
    /**
     * ��� GB <-> UNICODE ���ձ������
     * @��������
     * @����      �ڲ�
     */
    var $unicode_table = array();

    /**
     * �������ķ��򻥻�����ļ�ָ��
     *
     * @��������  ����
     * @����      �ڲ�
     */
    var $ctf;

    /**
     * �ȴ�ת�����ַ���
     * @��������
     * @����      �ڲ�
     */
    var $SourceText = '';

    /**
     * Chinese ����������
     *
     * @��������  ����
     * @����      ����
     */
    var $config = array(
        'codetable_dir'    => '',                // ��Ÿ������Ի������Ŀ¼
        'source_lang'      => '',                // �ַ���ԭ����
        'target_lang'      => '',                // ת����ı���
        'GBtoBIG5_table'   => 'gb-big5.table',   // ��������ת��Ϊ�������ĵĶ��ձ�
        'BIG5toGB_table'   => 'big5-gb.table',   // ��������ת��Ϊ�������ĵĶ��ձ�
        'GBtoUTF8_table'   => 'gb_utf8.php',     // ��������ת��ΪUTF-8�Ķ��ձ�
        'BIG5toUTF8_table' => 'big5_utf8.php'    // ��������ת��ΪUTF-8�Ķ��ձ�
    );

    var $iconv_enabled    = false; // �Ƿ���� ICONV ģ�飬Ĭ��Ϊ��
    var $mbstring_enabled = false; // �Ƿ���� MBSTRING ģ�飬Ĭ��Ϊ��


    /**
     * Chinese ��Ϥ������
     *
     * ��ϸ˵��
     * @�β�      �ַ��� $source_lang Ϊ��Ҫת�����ַ�����ԭ����
     *            �ַ��� $target_lang Ϊת����Ŀ�����
     *            �ַ��� $SourceText Ϊ�ȴ�ת�����ַ���
     * @����      ����
     * @����ֵ    ��
     * @throws
     */
    function Chinese($dir = './')
    {
        $this->config['codetable_dir'] = $dir . "includes/codetable/";

        if (function_exists('iconv'))
        {
            $this->iconv_enabled = true;
        }

        if (PHP_VERSION >= '5.0' && function_exists('mb_convert_encoding') && function_exists('mb_list_encodings'))
        {
            $encodings = mb_list_encodings();

            if (in_array('UTF-8', $encodings) == true && in_array('BIG-5', $encodings) == true && in_array('CP936', $encodings) == true) // CP936 ���� GBK �ַ����ı���
            {
                $this->mbstring_enabled = true;
            }
        }
    }

    function Convert($source_lang, $target_lang, $source_string = '')
    {
        /* ����ַ���Ϊ�ջ����ַ�������Ҫת����ֱ�ӷ��� */
        if ($source_string == '' || preg_match("/[\x80-\xFF]+/", $source_string) == 0)
        {
            return $source_string;
        }

        if ($source_lang)
        {
            $this->config['source_lang'] = $this->_lang($source_lang);
        }

        if ($target_lang)
        {
            $this->config['target_lang'] = $this->_lang($target_lang);
        }

        /* ���������ͬ��ֱ�ӷ��� */
        if ($this->config['source_lang'] == $this->config['target_lang'])
        {
            return $source_string;
        }

        $this->SourceText = $source_string;

        if (($this->iconv_enabled || $this->mbstring_enabled) && !($this->config['source_lang'] == 'GBK' && $this->config['target_lang'] == 'BIG-5'))
        {
            if ($this->config['target_lang'] != 'UNICODE')
            {
                $string = $this->_convert_iconv_mbstring($this->SourceText, $this->config['target_lang'], $this->config['source_lang']);

                /* �����ȷת�� */
                if ($string)
                {
                    return $string;
                }
            }
            else
            {
                $string = '';
                $text = $SourceText;
                while ($text)
                {
                    if (ord(substr($text, 0, 1)) > 127)
                    {
                        if ($this->config['source_lang'] != 'UTF-8')
                        {
                            $char = $this->_convert_iconv_mbstring(substr($text, 0, 2), 'UTF-8', $this->config['source_lang']);
                        }
                        else
                        {
                            $char = substr($text, 0, 3);
                        }
                        /* ���ת������ */
                        if ($char == '')
                        {
                            $string = '';

                            break;
                        }

                        switch (strlen($char))
                        {
                            case 1:
                                $uchar  = ord($char);
                                break;

                            case 2:
                                $uchar  = (ord($char[0]) & 0x3f) << 6;
                                $uchar += ord($char[1])  & 0x3f;
                                break;

                            case 3:
                                $uchar  = (ord($char[0]) & 0x1f) << 12;
                                $uchar += (ord($char[1]) & 0x3f) << 6;
                                $uchar += ord($char[2])  & 0x3f;
                                break;

                            case 4:
                                $uchar  = (ord($char[0]) & 0x0f) << 18;
                                $uchar += (ord($char[1]) & 0x3f) << 12;
                                $uchar += (ord($char[2]) & 0x3f) << 6;
                                $uchar += ord($char[3])  & 0x3f;
                                break;
                        }
                        $string .= '&#x' . dechex($uchar) . ';';

                        if ($this->config['source_lang'] != 'UTF-8')
                        {
                            $text = substr($text, 2);
                        }
                        else
                        {
                            $text = substr($text, 3);
                        }
                    }
                    else
                    {
                        $string .= substr($text, 0, 1);
                        $text    = substr($text, 1);
                    }
                }

                /* �����ȷת�� */
                if ($string)
                {
                    return $string;
                }
            }
        }

        $this->OpenTable();
        // �ж��Ƿ�Ϊ���ķ�����ת��
        if (($this->config['source_lang'] == 'GBK' || $this->config['source_lang'] == 'BIG-5') && ($this->config['target_lang'] == 'GBK' || $this->config['target_lang'] == 'BIG-5'))
        {
            return $this->GBtoBIG5();
        }

        // �ж��Ƿ�Ϊ���塢����������UTF8ת��
        if (($this->config['source_lang'] == 'GBK' || $this->config['source_lang'] == 'BIG-5' || $this->config['source_lang'] == 'UTF-8') && ($this->config['target_lang'] == 'UTF-8' || $this->config['target_lang'] == 'GBK' || $this->config['target_lang'] == 'BIG-5'))
        {
            return $this->CHStoUTF8();
        }

        // �ж��Ƿ�Ϊ���塢����������UNICODEת��
        if (($this->config['source_lang'] == 'GBK' || $this->config['source_lang'] == 'BIG-5') && $this->config['target_lang'] == 'UNICODE')
        {
            return $this->CHStoUNICODE();
        }
    }

    function _lang($lang)
    {
        $lang = strtoupper($lang);

        if (substr($lang, 0, 2) == 'GB')
        {
            return 'GBK';
        }
        else
        {
            switch(substr($lang, 0, 3))
            {
                case 'BIG':
                    return 'BIG-5';

                case 'UTF':
                    return 'UTF-8';

                case 'UNI':
                    return 'UNICODE';

                default:
                    return '';
            }
        }
    }

    function _convert_iconv_mbstring($string, $target_lang, $source_lang)
    {
        if ($this->iconv_enabled)
        {
            $return_string = @iconv($source_lang, $target_lang, $string);
            if ($return_string !== false)
            {
                return $return_string;
            }
        }

        if ($this->mbstring_enabled)
        {
            if ($source_lang == 'GBK')
            {
                $source_lang = 'CP936';
            }
            if ($target_lang == 'GBK')
            {
                $target_lang = 'CP936';
            }

            $return_string = @mb_convert_encoding($string, $target_lang, $source_lang);
            if ($return_string !== false)
            {
                return $return_string;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * �� 16 ����ת��Ϊ 2 �����ַ�
     *
     * ��ϸ˵��
     * @�β�      $hexdata Ϊ16���Ƶı���
     * @����      �ڲ�
     * @����      �ַ���
     * @throws
     */
    function _hex2bin($hexdata)
    {
        $bindata = '';

        for ($i = 0, $count = strlen($hexdata); $i < $count; $i += 2)
        {
            $bindata .= chr(hexdec($hexdata{$i} . $hexdata{$i + 1}));
        }

        return $bindata;
    }

    /**
     * �򿪶��ձ�
     *
     * ��ϸ˵��
     * @�β�
     * @����      �ڲ�
     * @����      ��
     * @throws
     */
    function OpenTable()
    {
        static $gb_utf8_table      = NULL;
        static $gb_unicode_table   = NULL;
        static $utf8_gb_table      = NULL;

        static $big5_utf8_table    = NULL;
        static $big5_unicode_table = NULL;
        static $utf8_big5_table    = NULL;

        // ����ԭ����Ϊ�������ĵĻ�
        if ($this->config['source_lang'] == 'GBK')
        {
            // ����ת��Ŀ�����Ϊ�������ĵĻ�
            if ($this->config['target_lang'] == 'BIG-5')
            {
                $this->ctf = @fopen($this->config['codetable_dir'] . $this->config['GBtoBIG5_table'], 'rb');
                if (is_null($this->ctf))
                {
                    echo '�򿪴�ת�����ļ�ʧ�ܣ�';

                    exit;
                }
            }

            // ����ת��Ŀ�����Ϊ UTF8 �Ļ�
            if ($this->config['target_lang'] == 'UTF-8')
            {
                if ($gb_utf8_table === NULL)
                {
                    require_once($this->config['codetable_dir'] . $this->config['GBtoUTF8_table']);
                }
                $this->unicode_table = $gb_utf8_table;
            }

            // ����ת��Ŀ�����Ϊ UNICODE �Ļ�
            if ($this->config['target_lang'] == 'UNICODE')
            {
                if ($gb_unicode_table === NULL)
                {
                    if (isset($gb_utf8_table) === false)
                    {
                        require_once($this->config['codetable_dir'] . $this->config['GBtoUTF8_table']);
                    }
                    foreach ($gb_utf8_table AS $key => $value)
                    {
                        $gb_unicode_table[$key] = substr($value, 2);
                    }
                }
                $this->unicode_table = $gb_unicode_table;
            }
        }

        // ����ԭ����Ϊ�������ĵĻ�
        if ($this->config['source_lang'] == 'BIG-5')
        {
            // ����ת��Ŀ�����Ϊ�������ĵĻ�
            if ($this->config['target_lang'] == 'GBK')
            {
                $this->ctf = @fopen($this->config['codetable_dir'] . $this->config['BIG5toGB_table'], 'rb');
                if (is_null($this->ctf))
                {
                    echo '�򿪴�ת�����ļ�ʧ�ܣ�';

                    exit;
                }
            }
            // ����ת��Ŀ�����Ϊ UTF8 �Ļ�
            if ($this->config['target_lang'] == 'UTF-8')
            {
                if ($big5_utf8_table === NULL)
                {
                    require_once($this->config['codetable_dir'] . $this->config['BIG5toUTF8_table']);
                }
                $this->unicode_table = $big5_utf8_table;
            }

            // ����ת��Ŀ�����Ϊ UNICODE �Ļ�
            if ($this->config['target_lang'] == 'UNICODE')
            {
                if ($big5_unicode_table === NULL)
                {
                    if (isset($big5_utf8_table) === false)
                    {
                        require_once($this->config['codetable_dir'] . $this->config['BIG5toUTF8_table']);
                    }
                    foreach ($big5_utf8_table AS $key => $value)
                    {
                        $big5_unicode_table[$key] = substr($value, 2);
                    }
                }
                $this->unicode_table = $big5_unicode_table;
            }
        }

        // ����ԭ����Ϊ UTF8 �Ļ�
        if ($this->config['source_lang'] == 'UTF-8')
        {
            // ����ת��Ŀ�����Ϊ GBK �Ļ�
            if ($this->config['target_lang'] == 'GBK')
            {
                if ($utf8_gb_table === NULL)
                {
                    if (isset($gb_utf8_table) === false)
                    {
                        require_once($this->config['codetable_dir'] . $this->config['GBtoUTF8_table']);
                    }
                    foreach ($gb_utf8_table AS $key => $value)
                    {
                        $utf8_gb_table[hexdec($value)] = '0x' . dechex($key);
                    }
                }
                $this->unicode_table = $utf8_gb_table;
            }

            // ����ת��Ŀ�����Ϊ BIG5 �Ļ�
            if ($this->config['target_lang'] == 'BIG-5')
            {
                if ($utf8_big5_table === NULL)
                {
                    if (isset($big5_utf8_table) === false)
                    {
                        require_once($this->config['codetable_dir'] . $this->config['BIG5toUTF8_table']);
                    }
                    foreach ($big5_utf8_table AS $key => $value)
                    {
                        $utf8_big5_table[hexdec($value)] = '0x' . dechex($key);
                    }
                }
                $this->unicode_table = $utf8_big5_table;
            }
        }
    }

    /**
     * �����塢�������ĵ� UNICODE ����ת��Ϊ UTF8 �ַ�
     *
     * ��ϸ˵��
     * @�β�      ���� $c �������ĺ��ֵ�UNICODE�����10����
     * @����      �ڲ�
     * @����      �ַ���
     * @throws
     */
    function CHSUtoUTF8($c)
    {
        $str='';

        if ($c < 0x80)
        {
            $str .= $c;
        }
        elseif ($c < 0x800)
        {
            $str .= (0xC0 | $c >> 6);
            $str .= (0x80 | $c & 0x3F);
        }
        elseif ($c < 0x10000)
        {
            $str .= (0xE0 | $c >> 12);
            $str .= (0x80 | $c >> 6 & 0x3F);
            $str .= (0x80 | $c & 0x3F);
        }
        elseif ($c < 0x200000)
        {
            $str .= (0xF0 | $c >> 18);
            $str .= (0x80 | $c >> 12 & 0x3F);
            $str .= (0x80 | $c >> 6  & 0x3F);
            $str .= (0x80 | $c & 0x3F);
        }

        return $str;
    }

    /**
     * ���塢�������� <-> UTF8 ����ת���ĺ���
     *
     * ��ϸ˵��
     * @�β�
     * @����      �ڲ�
     * @����      �ַ���
     * @throws
     */
    function CHStoUTF8()
    {
        if ($this->config['source_lang'] == 'BIG-5' || $this->config['source_lang'] == 'GBK')
        {
            $ret = '';

            while ($this->SourceText)
            {
                if (ord($this->SourceText{0}) > 127)
                {
                    if ($this->config['source_lang'] == 'BIG-5')
                    {
                        $utf8 = $this->CHSUtoUTF8(hexdec(@$this->unicode_table[hexdec(bin2hex($this->SourceText{0} . $this->SourceText{1}))]));
                    }
                    if ($this->config['source_lang'] == 'GBK')
                    {
                        $utf8 = $this->CHSUtoUTF8(hexdec(@$this->unicode_table[hexdec(bin2hex($this->SourceText{0} . $this->SourceText{1})) - 0x8080]));
                    }
                    for ($i = 0, $count = strlen($utf8); $i < $count; $i += 3)
                    {
                        $ret .= chr(substr($utf8, $i, 3));
                    }

                    $this->SourceText = substr($this->SourceText, 2, strlen($this->SourceText));
                }
                else
                {
                    $ret .= $this->SourceText{0};
                    $this->SourceText = substr($this->SourceText, 1, strlen($this->SourceText));
                }
            }
            $this->unicode_table = array();
            $this->SourceText = '';

            return $ret;
        }

        if ($this->config['source_lang'] == 'UTF-8')
        {
            $i   = 0;
            $out = '';
            $len = strlen($this->SourceText);
            while ($i < $len)
            {
                $c = ord($this->SourceText{$i++});
                switch($c >> 4)
                {
                    case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                        // 0xxxxxxx
                        $out .= $this->SourceText{$i - 1};
                        break;
                    case 12: case 13:
                        // 110x xxxx   10xx xxxx
                        $char2 = ord($this->SourceText{$i++});
                        $char3 = $this->unicode_table[(($c & 0x1F) << 6) | ($char2 & 0x3F)];

                        if ($this->config['target_lang'] == 'GBK')
                        {
                            $out .= $this->_hex2bin(dechex($char3 + 0x8080));
                        }
                        elseif ($this->config['target_lang'] == 'BIG-5')
                        {
                            $out .= $this->_hex2bin(dechex($char3 + 0x0000));
                        }
                        break;
                    case 14:
                        // 1110 xxxx  10xx xxxx  10xx xxxx
                        $char2 = ord($this->SourceText{$i++});
                        $char3 = ord($this->SourceText{$i++});
                        $char4 = @$this->unicode_table[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];

                        if ($this->config['target_lang'] == 'GBK')
                        {
                            $out .= $this->_hex2bin(dechex($char4 + 0x8080));
                        } elseif ($this->config['target_lang'] == 'BIG-5')
                        {
                            $out .= $this->_hex2bin(dechex($char4 + 0x0000));
                        }

                        break;
                }
            }

            // ���ؽ��
            return $out;
        }
    }

    /**
     * ���塢��������ת��Ϊ UNICODE����
     *
     * ��ϸ˵��
     * @�β�
     * @����      �ڲ�
     * @����      �ַ���
     * @throws
     */
    function CHStoUNICODE()
    {
        $utf = '';

        while ($this->SourceText)
        {
            if (ord($this->SourceText{0}) > 127)
            {
                if ($this->config['source_lang'] == 'GBK')
                {
                    $utf .= '&#x' . $this->unicode_table[hexdec(bin2hex($this->SourceText{0} . $this->SourceText{1})) - 0x8080] . ';';
                }
                elseif ($this->config['source_lang'] == 'BIG-5')
                {
                    $utf .= '&#x' . $this->unicode_table[hexdec(bin2hex($this->SourceText{0} . $this->SourceText{1}))] . ';';
                }

                $this->SourceText = substr($this->SourceText, 2, strlen($this->SourceText));
            }
            else
            {
                $utf .= $this->SourceText{0};
                $this->SourceText = substr($this->SourceText, 1, strlen($this->SourceText));
            }
        }

        return $utf;
    }

    /**
     * �������� <-> �������� ����ת���ĺ���
     *
     * ��ϸ˵��
     * @����      �ڲ�
     * @����ֵ    ���������utf8�ַ�
     * @throws
     */
    function GBtoBIG5()
    {
        // ��ȡ�ȴ�ת�����ַ������ܳ���
        $max = strlen($this->SourceText) - 1;

        for ($i = 0; $i < $max; $i++)
        {
            $h = ord($this->SourceText{$i});
            if ($h >= 160)
            {
                $l = ord($this->SourceText{$i + 1});

                if ($h == 161 && $l == 64)
                {
                    $gb = '  ';
                }
                else
                {
                    fseek($this->ctf, ($h - 160) * 510 + ($l - 1) * 2);
                    $gb = fread($this->ctf, 2);
                }

                $this->SourceText{$i}     = $gb{0};
                $this->SourceText{$i + 1} = $gb{1};

                $i++;
            }
        }
        fclose($this->ctf);

        // ��ת����Ľ������ $result;
        $result = $this->SourceText;

        // ��� $thisSourceText
        $this->SourceText = '';

        // ����ת�����
        return $result;
    }
}

?>