<?php

/**
 * ECMall: ���ú�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: lib.common.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/**
 * ����MySQL���ݿ����ʵ��
 *
 * @author  wj
 * @return  object
 */
function &db()
{
    include_once(ROOT_PATH. '/includes/cls.mysql.php');
    static $db = false;
    if ($db === false)
    {
        $cfg = parse_url(DB_CONFIG);

        if ($cfg['scheme'] == 'mysql')
        {
            if (empty($cfg['pass']))
            {
                $cfg['pass'] = '';
            }
            else
            {
                $cfg['pass'] = urldecode($cfg['pass']);
            }
            $cfg ['user'] = urldecode($cfg['user']);

            if (empty($cfg['path']))
            {
                trigger_error('Invalid database name.', E_USER_ERROR);
            }
            else
            {
                $cfg['path'] = str_replace('/', '', $cfg['path']);
            }

            $charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
            $db =& new cls_mysql();
            $db->cache_dir = ROOT_PATH. '/temp/query_caches/';
            $db->connect($cfg['host']. ':' .$cfg['port'], $cfg['user'],
                $cfg['pass'], $cfg['path'], $charset);
        }
        else
        {
            trigger_error('Unkown database type.', E_USER_ERROR);
        }
    }

    return $db;
}

/**
 * ��õ�ǰ������
 *
 * @return  string
 */
function get_domain()
{
    /* Э�� */
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

    /* ������IP��ַ */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    elseif (isset($_SERVER['HTTP_HOST']))
    {
        $host = $_SERVER['HTTP_HOST'];
    }
    else
    {
        /* �˿� */
        if (isset($_SERVER['SERVER_PORT']))
        {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
            {
                $port = '';
            }
        }
        else
        {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME']))
        {
            $host = $_SERVER['SERVER_NAME'] . $port;
        }
        elseif (isset($_SERVER['SERVER_ADDR']))
        {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}

/**
 * �����վ��URL��ַ
 *
 * @return  string
 */
function site_url()
{
    return get_domain() . substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
}


/**
 * ��ȡUTF-8�������ַ����ĺ���
 *
 * @param   string      $str        ����ȡ���ַ���
 * @param   int         $length     ��ȡ�ĳ���
 * @param   bool        $append     �Ƿ񸽼�ʡ�Ժ�
 *
 * @return  string
 */
function sub_str($string, $length = 0, $append = true)
{

    if(strlen($string) <= $length) {
        return $string;
    }

    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

    $strcut = '';

    if(strtolower(CHARSET) == 'utf-8') {
        $n = $tn = $noc = 0;
        while($n < strlen($string)) {

            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t < 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }

            if($noc >= $length) {
                break;
            }

        }
        if($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        for($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }

    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

    if ($append && $string != $strcut)
    {
        $strcut .= '...';
    }

    return $strcut;

}

/**
 * ����û�����ʵIP��ַ
 *
 * @return  string
 */
function real_ip()
{
    static $realip = NULL;

    if ($realip !== NULL)
    {
        return $realip;
    }

    if (isset($_SERVER))
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* ȡX-Forwarded-For�е�һ����unknown����ЧIP�ַ��� */
            foreach ($arr AS $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown')
                {
                    $realip = $ip;

                    break;
                }
            }
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            if (isset($_SERVER['REMOTE_ADDR']))
            {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
            else
            {
                $realip = '0.0.0.0';
            }
        }
    }
    else
    {
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_CLIENT_IP'))
        {
            $realip = getenv('HTTP_CLIENT_IP');
        }
        else
        {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

    return $realip;
}

/**
 * ��֤������ʼ���ַ�Ƿ�Ϸ�
 *
 * @param   string      $email      ��Ҫ��֤���ʼ���ַ
 *
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
    {
        if (preg_match($chars, $user_email))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

/**
 * ����Ƿ�Ϊһ���Ϸ���ʱ���ʽ
 *
 * @param   string  $time
 * @return  void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}

/**
 * ��÷������ϵ� GD �汾
 *
 * @return      int         ���ܵ�ֵΪ0��1��2
 */
function gd_version()
{
    include_once(ROOT_PATH . 'includes/cls_image.php');

    return cls_image::gd_version();
}

/**
 * �ݹ鷽ʽ�ĶԱ����е������ַ�����ת��
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 * �������Ա������������������ַ�����ת��
 *
 * @access   public
 * @param    mix        $obj      �����������
 * @author   Xuan Yan
 *
 * @return   mix                  �����������
 */
function addslashes_deep_obj($obj)
{
    if (is_object($obj) == true)
    {
        foreach ($obj AS $key => $val)
        {
            $obj->$key = addslashes_deep($val);
        }
    }
    else
    {
        $obj = addslashes_deep($obj);
    }

    return $obj;
}

/**
 * �ݹ鷽ʽ�ĶԱ����е������ַ�ȥ��ת��
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function stripslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    }
}
/**
 *  ��һ���ִ��к���ȫ�ǵ������ַ�����ĸ���ո��'%+-()'�ַ�ת��Ϊ��Ӧ����ַ�
 *
 * @access  public
 * @param   string       $str         ��ת���ִ�
 *
 * @return  string       $str         ������ִ�
 */
function make_semiangle($str)
{
    $arr = array('��' => '0', '��' => '1', '��' => '2', '��' => '3', '��' => '4',
                 '��' => '5', '��' => '6', '��' => '7', '��' => '8', '��' => '9',
                 '��' => 'A', '��' => 'B', '��' => 'C', '��' => 'D', '��' => 'E',
                 '��' => 'F', '��' => 'G', '��' => 'H', '��' => 'I', '��' => 'J',
                 '��' => 'K', '��' => 'L', '��' => 'M', '��' => 'N', '��' => 'O',
                 '��' => 'P', '��' => 'Q', '��' => 'R', '��' => 'S', '��' => 'T',
                 '��' => 'U', '��' => 'V', '��' => 'W', '��' => 'X', '��' => 'Y',
                 '��' => 'Z', '��' => 'a', '��' => 'b', '��' => 'c', '��' => 'd',
                 '��' => 'e', '��' => 'f', '��' => 'g', '��' => 'h', '��' => 'i',
                 '��' => 'j', '��' => 'k', '��' => 'l', '��' => 'm', '��' => 'n',
                 '��' => 'o', '��' => 'p', '��' => 'q', '��' => 'r', '��' => 's',
                 '��' => 't', '��' => 'u', '��' => 'v', '��' => 'w', '��' => 'x',
                 '��' => 'y', '��' => 'z',
                 '��' => '(', '��' => ')', '��' => '[', '��' => ']', '��' => '[',
                 '��' => ']', '��' => '[', '��' => ']', '��' => '[', '��' => ']',
                 '��' => '[', '��' => ']', '��' => '{', '��' => '}', '��' => '<',
                 '��' => '>',
                 '��' => '%', '��' => '+', '��' => '-', '��' => '-', '��' => '-',
                 '��' => ':', '��' => '.', '��' => ',', '��' => '.', '��' => '.',
                 '��' => ',', '��' => '?', '��' => '!', '��' => '-', '��' => '|',
                 '��' => '"', '��' => '`', '��' => '`', '��' => '|', '��' => '"',
                 '��' => ' ');

    return strtr($str, $arr);
}

/**
 * ��ʽ�����ã������������ֻ�ٷֱȵĵط�
 *
 * @param   string      $fee    ����ķ���
 */
function format_fee($fee)
{
    $fee = make_semiangle($fee);
    if (strpos($fee, '%') === false)
    {
        return floatval($fee);
    }
    else
    {
        return floatval($fee) . '%';
    }
}

/**
 * �����ܽ��ͷ��ʼ������
 *
 * @param     float    $amount    �ܽ��
 * @param     string    $rate    ���ʣ������ǹ̶����ʣ�Ҳ�����ǰٷֱȣ�
 * @param     string    $type    ���ͣ�s ���۷� p ֧�������� i ��Ʊ˰��
 * @return     float    ����
 */
function compute_fee($amount, $rate, $type)
{
    $amount = floatval($amount);
    if (strpos($rate, '%') === false)
    {
        return round(floatval($rate), 2);
    }
    else
    {
        $rate = floatval($rate) / 100;
        if ($type == 's')
        {
            return round($amount * $rate, 2);
        }
        elseif($type == 'p')
        {
            return round($amount * $rate / (1 - $rate), 2);
        }
        else
        {
            return round($amount * $rate, 2);
        }
    }
}

/**
 * ��ȡ��������ip
 *
 * @access      public
 *
 * @return string
 **/
function real_server_ip()
{
    static $serverip = NULL;

    if ($serverip !== NULL)
    {
        return $serverip;
    }

    if (isset($_SERVER))
    {
        if (isset($_SERVER['SERVER_ADDR']))
        {
            $serverip = $_SERVER['SERVER_ADDR'];
        }
        else
        {
            $serverip = '0.0.0.0';
        }
    }
    else
    {
        $serverip = getenv('SERVER_ADDR');
    }

    return $serverip;
}
/**
 * ����û�����ϵͳ�Ļ��з�
 *
 * @access  public
 * @return  string
 */
function get_crlf()
{
/* LF (Line Feed, 0x0A, \N) �� CR(Carriage Return, 0x0D, \R) */
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'Win'))
    {
        $the_crlf = "\r\n";
    }
    elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'Mac'))
    {
        $the_crlf = "\r"; // for old MAC OS
    }
    else
    {
        $the_crlf = "\n";
    }

    return $the_crlf;
}

/**
 * ����ת������
 *
 * @author  wj
 * @param string $source_lang       ��ת������
 * @param string $target_lang         ת�������
 * @param string $source_string      ��Ҫת��������ִ�
 * @return string
 */
function ecm_iconv($source_lang, $target_lang, $source_string = '')
{
    static $chs = NULL;

    /* ����ַ���Ϊ�ջ����ַ�������Ҫת����ֱ�ӷ��� */
    if ($source_lang == $target_lang || $source_string == '' || preg_match("/[\x80-\xFF]+/", $source_string) == 0)
    {
        return $source_string;
    }

    if ($chs === NULL)
    {
        require_once(ROOT_PATH . '/includes/cls.iconv.php');
        $chs = new Chinese(ROOT_PATH . '/');
    }

    return strtolower($target_lang) == 'utf-8' ? addslashes(stripslashes($chs->Convert($source_lang, $target_lang, $source_string))) : $chs->Convert($source_lang, $target_lang, $source_string);
}

function ecm_geoip($ip)
{
    static $fp = NULL, $offset = array(), $index = NULL;

    $ip    = gethostbyname($ip);
    $ipdot = explode('.', $ip);
    $ip    = pack('N', ip2long($ip));

    $ipdot[0] = (int)$ipdot[0];
    $ipdot[1] = (int)$ipdot[1];
    if ($ipdot[0] == 10 || $ipdot[0] == 127 || ($ipdot[0] == 192 && $ipdot[1] == 168) || ($ipdot[0] == 172 && ($ipdot[1] >= 16 && $ipdot[1] <= 31)))
    {
        return 'LAN';
    }

    if ($fp === NULL)
    {
        $fp     = fopen(ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb');
        if ($fp === false)
        {
            return 'Invalid IP data file';
        }
        $offset = unpack('Nlen', fread($fp, 4));
        if ($offset['len'] < 4)
        {
            return 'Invalid IP data file';
        }
        $index  = fread($fp, $offset['len'] - 4);
    }

    $length = $offset['len'] - 1028;
    $start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8)
    {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip)
        {
            $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }

    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    $area = fread($fp, $index_length['len']);

    fclose($fp);
    $fp = NULL;

    return $area;
}

function ecm_json_encode($value)
{
    if (CHARSET == 'utf-8' && function_exists('json_encode'))
    {
        return json_encode($value);
    }

    $props = '';
    if (is_object($value))
    {
        foreach (get_object_vars($value) as $name => $propValue)
        {
            if (isset($propValue))
            {
                $props .= $props ? ','.ecm_json_encode($name)  : ecm_json_encode($name);
                $props .= ':' . ecm_json_encode($propValue);
            }
        }
        return '{' . $props . '}';
    }
    elseif (is_array($value))
    {
        $keys = array_keys($value);
        if (!empty($value) && !empty($value) && ($keys[0] != '0' || $keys != range(0, count($value)-1)))
        {
            foreach ($value as $key => $val)
            {
                $key = (string) $key;
                $props .= $props ? ','.ecm_json_encode($key)  : ecm_json_encode($key);
                $props .= ':' . ecm_json_encode($val);
            }
            return '{' . $props . '}';
        }
        else
        {
            $length = count($value);
            for ($i = 0; $i < $length; $i++)
            {
                $props .= $props ? ','.ecm_json_encode($value[$i])  : ecm_json_encode($value[$i]);
            }
            return '[' . $props . ']';
        }
    }
    elseif (is_string($value))
    {
        $replace  = array('\\' => '\\\\', "\n" => '\n', "\t" => '\t', '/' => '\/',
                        "\r" => '\r', "\b" => '\b', "\f" => '\f',
                        '"' => '\"', chr(0x08) => '\b', chr(0x0C) => '\f'
                        );
        $value  = strtr($value, $replace);
        return '"' . $value . '"';
    }
    elseif (is_bool($value))
    {
        return $value ? 'true' : 'false';
    }
    elseif (empty($value))
    {
        return '""';
    }
    else
    {
        return $value;
    }
}

function ecm_json_decode($value)
{
    if (CHARSET == 'utf-8' && function_exists('json_decode'))
    {
        return json_decode($value);
    }

    if (!class_exists('JSON'))
    {
        include(ROOT_PATH . '/includes/cls.json.php');
    }
    $json = new JSON();
    return $json->decode($value);
}

function file_ext($filename)
{
    return trim(substr(strrchr($filename, '.'), 1, 10));
}

/**
 * �����������Ĳ�ѯ: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      �б�������ַ���,���Ϊ�ַ���ʱ,�ַ���ֻ�������ִ�
 * @param    string   $field_name     �ֶ�����
 * @author   wj
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list))
    {
        return $field_name . " IN ('') ";
    }
    else
    {
        if (!is_array($item_list))
        {
            $item_list = explode(',', $item_list);
            foreach ($item_list as $k=>$v)
            {
                $item_list[$k] = intval($v);
            }
        }

        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item)
        {
            if ($item !== '')
            {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp))
        {
            return $field_name . " IN ('') ";
        }
        else
        {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * �������ģ��������Լ����ӵ�ַ
 *
 * @access      public
 * @param       string      $directory      �����ŵ�Ŀ¼
 * @return      array
 */
function read_modules($directory = '.')
{
    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();

    while (false !== ($file = @readdir($dir)))
    {
        if (preg_match("/^.*?\.php$/", $file))
        {
            include_once($directory. '/' .$file);
        }
    }
    @closedir($dir);
    unset($set_modules);

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    return $modules;
}

/**
 * ��һ��Ⲣ����ÿһ��Ŀ¼
 * @param string $dir_path Ŀ¼·��
 *
 * @return int $mod      Ŀ¼Ȩ��
 */
function ecm_mkdir($dir_path, $mod=0777)
{
    $cur_dir = ROOT_PATH.'/';
    if (is_dir($cur_dir . $dir_path))
    {
        return true;
    }
    $arr_path = explode('/', $dir_path);
    foreach ($arr_path as $val)
    {
        if (!empty($val))
        {
            $cur_dir .= $val;

            if (is_dir($cur_dir))
            {
                $cur_dir .= '/';
                continue;
            }
            if (mkdir($cur_dir, $mod))
            {
                $cur_dir .= '/';
                fclose(fopen($cur_dir.'index.htm', 'w'));
            }
            else
            {
                return false;
            }
        }
    }
    return true;
}

/**
 * ɾ��Ŀ¼,��֧��Ŀ¼�д� ..
 *
 * @param string $dir
 *
 * @return boolen
 */
function ecm_rmdir($dir)
{
    $dir = str_replace(array('..', "\n", "\r"), array('', '', ''), $dir);
    $ret_val = false;
    if (is_dir($dir))
    {
        $d = @dir($dir);
        if($d)
        {
            while (false !== ($entry = $d->read()))
            {
               if($entry!='.' && $entry!='..')
               {
                   $entry = $dir.'/'.$entry;
                   if(is_dir($entry))
                   {
                       ecm_rmdir($entry);
                   }
                   else
                   {
                       @unlink($entry);
                   }
               }
            }
            $d->close();
            $ret_val = rmdir($dir);
         }
    }
    else
    {
        $ret_val = unlink($dir);
    }

    return $ret_val;
}

function price_format($price, $price_format = NULL)
{
    if (empty($price)) $price = '0.00';
    $price = number_format($price, 2);

    if ($price_format === NULL)
    {
        $price_format = Language::get('price_format');
    }

    return sprintf($price_format, $price);
}

/**
 *  ����¼���UC
 *
 *  @access public
 *  @param  array $feed_info ��:array('icon' => 'goods', 'user_id' => 1, 'user_name'=> 'Garbin', 'title'=>array('template'=>'������Ʒ{goods}', 'data'=>array('goods'=>'Dell Vostro 1400')), 'body'=>array('template'=>'{buyer}����һ��{goods}', 'data'=>array('buyer'=>'Garbin', 'goods'=>'Dell Vostro 1400')),'images'=>array(array('url'=>'abc.gif', 'link'=>'ddd')))
 *  @return feed_id
 */
function add_feed($feed_info)
{
    include_once(ROOT_PATH . '/' . UC_PATH . '/client.php');
    return uc_feed_add($feed_info['icon'], $feed_info['user_id'], $feed_info['user_name'], $feed_info['title']['template'], $feed_info['title']['data'], $feed_info['body']['template'], $feed_info['body']['data'], $feed_info['body_general'], $feed_info['target_ids'], $feed_info['images']);
}

/**
 *  ��ȡ����״̬������
 *
 *  @access public
 *  @params int $order_status
 *  @return string
 */
function get_order_status_lang($order_status)
{
    $order_status_table = array (
      0 => 'order_status_temporary',
      1 => 'order_status_pending',
      2 => 'order_status_submitted',
      3 => 'order_status_acceptted',
      4 => 'order_status_processing',
      5 => 'order_status_shipped',
      6 => 'order_status_delivered',
      7 => 'order_status_invalid',
      8 => 'order_status_rejected',
    );

    return Language::get($order_status_table[$order_status]);
}

/**
 *  ��ȡ��������������
 *
 *  @access public
 *  @param  int $evaluation
 *  @return string
 */
function get_evaluation($evaluation)
{
    if ($evaluation === NULL)
    {

        return Language::get('order_evaluation_unevaluated');
    }
    $order_evaluations = array(
        '-1'   =>  'order_evaluation_poor',
        '0'    =>  'order_evaluation_common',
        '1'    =>  'order_evaluation_good'
    );

    return isset($order_evaluations[$evaluation]) ? Language::get($order_evaluations[$evaluation]) : '';
}

/**
 *  ����COOKIE
 *
 *  @access public
 *  @param  string $key     Ҫ���õ�COOKIE����
 *  @param  string $value   ������Ӧ��ֵ
 *  @param  int    $expire  ����ʱ��
 *  @return void
 */
function ecm_setcookie($key, $value, $expire = 0, $cookie_path=COOKIE_PATH, $cookie_domain=COOKIE_DOMAIN)
{
    setcookie($key, $value, $expire, $cookie_path, $cookie_domain);
}

/**
 *  ��ȡCOOKIE��ֵ
 *
 *  @access public
 *  @param  string $key    Ϊ��ʱ����������COOKIE
 *  @return mixed
 */
function ecm_getcookie($key = '')
{
    return isset($_COOKIE[$key]) ? $_COOKIE[$key] : 0;
}

/**
 * ����UCenter�ĺ���
 *
 * @author  weberliu
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function uc_call($func, $params=null)
{
    restore_error_handler();
    if (!function_exists($func))
    {
        include_once(ROOT_PATH . '/' . UC_PATH . '/client.php');
    }

    $res = call_user_func_array($func, $params);

    set_error_handler('exception_handler');

    return $res;
}


/**
 * ��� UCenter Home ���˿ռ�ĵ�ַ
 *
 * @author  wj
 * @param   int     $uid
 *
 * @return  void
 */
function uc_home_url($uid)
{
    static $url_format = null;

    $reval = '';

    if ($url_format === null)
    {
        $has_uch = has_uchome();
        $url_format = $has_uch ? $has_uch['url'] . '/?%d' : '';
    }

    if ($url_format == '')
    {
        $reval = '';
    }
    else
    {
        $reval = sprintf($url_format, $uid);
    }
    return $reval;
}


/**
 * ������ת��
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function ecm_iconv_deep($source_lang, $target_lang, $value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        if (is_array($value))
        {
            foreach ($value as $k=>$v)
            {
                $value[$k] = ecm_iconv_deep($source_lang, $target_lang, $v);
            }
            return $value;
        }
        elseif (is_string($value))
        {
            return ecm_iconv($source_lang, $target_lang, $value);
        }
        else
        {
            return $value;
        }
    }
}

/**
 *  fopen��װ����
 *
 *  @author wj
 *  @param string $url
 *  @param int    $limit
 *  @param string $post
 *  @param string $cookie
 *  @param boolen $bysocket
 *  @param string $ip
 *  @param int    $timeout
 *  @param boolen $block
 *  @return responseText
 */
function ecm_fopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true)
{
    $return = '';
    $matches = parse_url($url);
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if($post)
    {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    }
    else
    {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if(!$fp)
    {
        return '';
    }
    else
    {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if(!$status['timed_out'])
        {
            while (!feof($fp))
            {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n"))
                {
                    break;
                }
            }

            $stop = false;
            while(!feof($fp) && !$stop)
            {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if($limit)
                {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}

/**
 * Σ�� HTML���������
 *
 * @param   string  $html   ��Ҫ���˵�html����
 *
 * @return  string
 */
function html_filter($html)
{
    $filter = array(
        "/\s/",
        "/<(\/?)(script|i?frame|style|html|body|title|link|object|meta|\?|\%)([^>]*?)>/isU",
        "/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU",
        );

    $replace = array(
        " ",
        "&lt;\\1\\2\\3&gt;",
        "\\1\\2",
        );

    $str = preg_replace($filter,$replace,$html);
    return $str;
}

/**
 * ����ϵͳ���б����ļ��������ļ���ģ��ṹ����
 *
 * @author  wj
 * @param   void
 *
 * @return  void
 */
function clean_cache()
{
    /*������*/
    $cache_dirs = array(
        ROOT_PATH . '/temp/caches',
        ROOT_PATH . '/temp/compiled/mall/admin',
        ROOT_PATH . '/temp/compiled/mall/',
        ROOT_PATH . '/temp/compiled/store/admin',
        ROOT_PATH . '/temp/compiled/store',
        ROOT_PATH . '/temp/js',
        ROOT_PATH . '/temp/query_caches',
        ROOT_PATH . '/temp/tag_caches',
        ROOT_PATH . '/temp/style',
    );

    foreach ($cache_dirs as $dir)
    {
        $d = dir($dir);
        if ($d)
        {
            while (false !== ($entry = $d->read()))
            {
                if($entry!='.' && $entry!='..' && $entry != '.svn' && $entry != 'admin' && $entry != 'index.html')
                {
                   ecm_rmdir($dir . '/'. $entry);
                }
            }
            $d->close();
        }
    }

    /*�����໺������*/
    if (is_file(ROOT_PATH . '/temp/query_caches/cache_category.php'))
    {
        unlink(ROOT_PATH . '/temp/query_caches/cache_category.php');
    }

    /*���һ����ǰͼƬ���沢���ն���Ŀ¼*/
    $expiry_time = strtotime('-1 week');
    $path = ROOT_PATH . '/temp/thumb';
    $d = dir($path);
    if ($d)
    {
        while(false !== ($entry = $d->read()))
        {
            if ($entry!='.' && $entry!= '..' && $entry != '.svn' && is_dir(($dir = ($path . '/' . $entry))))
            {
                $sd = dir($dir);
                if ($sd)
                {
                    $left_dir_count = 0;
                    while(false !== ($entry = $sd->read()))
                    {
                        if ($entry!='.' && $entry!= '..' && is_dir(($subdir = ($dir . '/' . $entry))))
                        {
                            $fsd = dir($subdir);
                            $left_file_count = 0;
                            while (false !== ($entry= $fsd->read()))
                            {
                                if ($entry!='.' && $entry!='..' && $entry != 'index.htm' && is_file(($file =$subdir . '/' . $entry)))
                                {
                                    if (filemtime($file) < $expiry_time)
                                    {
                                        unlink($file);
                                    }
                                    else
                                    {
                                        $left_file_count ++;
                                    }
                                }
                            }
                            $fsd->close();
                            if ($left_file_count == 0)
                            {
                                //�����Ŀ¼
                                ecm_rmdir($subdir);
                            }
                            else
                            {
                                $left_dir_count ++;
                            }
                        }
                    }
                    $sd->close();
                    if ($left_dir_count == 0) ecm_rmdir($dir);
                }
            }
        }
        $d->close();
    }

}

/**
 * ���ϵͳ������file_put_contents�����������ú���
 *
 * @author  wj
 * @param   string  $file
 * @param   mix     $data
 * @return  int
 */
if (!function_exists('file_put_contents'))
{
    define('FILE_APPEND', 'FILE_APPEND');
    if (!defined('LOCK_EX'))
    {
        define('LOCK_EX', 'LOCK_EX');
    }

    function file_put_contents($file, $data, $flags = '')
    {
        $contents = (is_array($data)) ? implode('', $data) : $data;

        $mode = ($flags == 'FILE_APPEND') ? 'ab+' : 'wb';

        if (($fp = @fopen($file, $mode)) === false)
        {
            return false;
        }
        else
        {
            $bytes = fwrite($fp, $contents);
            fclose($fp);

            return $bytes;
        }
    }
}


/**
 * ȥ���ַ����Ҳ���ܳ��ֵ�����
 *
 * @author  wj
 * @param   string      $str        �ַ���
 *
 *
 * @return  string
 */
function trim_right($str)
{
    $len = strlen($str);
    /* Ϊ�ջ򵥸��ַ�ֱ�ӷ��� */
    if ($len == 0 || ord($str{$len-1}) < 127)
    {
        return $str;
    }
    /* ��ǰ���ַ���ֱ�Ӱ�ǰ���ַ�ȥ�� */
    if (ord($str{$len-1}) >= 192)
    {
       return substr($str, 0, $len-1);
    }
    /* �зǶ������ַ����ȰѷǶ����ַ�ȥ��������֤�Ƕ������ַ��ǲ���һ���������֣�������ԭ��ǰ���ַ�Ҳ��ȡ�� */
    $r_len = strlen(rtrim($str, "\x80..\xBF"));
    if ($r_len == 0 || ord($str{$r_len-1}) < 127)
    {
        return sub_str($str, 0, $r_len);
    }

    $as_num = ord(~$str{$r_len -1});
    if ($as_num > (1<<(6 + $r_len - $len)))
    {
        return $str;
    }
    else
    {
        return substr($str, 0, $r_len-1);
    }
}

/**
 * ͨ���ú������к����������ƴ���
 *
 * @author  weberliu
 * @param   string      $fun        Ҫ���δ���ĺ�����
 * @return  mix         ����ִ�н��
 */
function _at($fun)
{
    $arg = func_get_args();
    unset($arg[0]);
    restore_error_handler();
    $ret_val = @call_user_func_array($fun, $arg);
    set_error_handler('exception_handler');
    return $ret_val;
}

/**
 * �����Ƿ�װ��uchome
 *
 * @author wj
 * @param  void
 * @return boolen
 */
function has_uchome()
{
    include_once(ROOT_PATH . '/includes/cls.filecache.php');
    $file_cache = new filecache('query_caches', 60);
    $has_uch = $file_cache->get('has_uchome');
    if ($has_uch === false)
    {
        $has_uch = 0;
        $uc_app_list = uc_call('uc_app_ls');
        if($uc_app_list) foreach ($uc_app_list as $_ua)
        {
            if ($_ua['type'] == 'UCHOME')
            {
                $has_uch = $_ua;
            }
        }
        $file_cache->set('has_uchome', $has_uch);
    }

    return $has_uch;
}

/**
 * �����Ƿ���ͨ����������ʵ�ҳ��
 *
 * @author wj
 * @param  void
 * @return boolen
 */
function is_from_browser()
{
    static $ret_val = null;
    if ($ret_val === null)
    {
        $ret_val = false;
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        if ($ua)
        {
            if ((strpos($ua, 'mozilla') !== false) && ((strpos($ua, 'msie') !== false) || (strpos($ua, 'gecko') !== false)))
            {
                $ret_val = true;
            }
            elseif (strpos($ua, 'opera'))
            {
                $ret_val = true;
            }
        }
    }
    return $ret_val;
}

/**
 * ��ȡ�����Զ�������
 *
 * @author  wj
 * @param   int     $store_id
 * @return  stirng
 */
function get_store_custom_url($store_id)
{
    $custom_url = '';
    if (ENABLED_CUSTOM_DOMAIN)
    {
        static $domain_arr = null;
        include_once(ROOT_PATH . '/includes/manager/mng.store.php');
        $custom_data = StoreManager::get_custom_store();
        if (isset($custom_data[$store_id]))
        {
            if ($domain_arr === null)
            {
                $pos1 = strpos(MAIN_DOMAIN, '://') + 3;
                $pos2 = strpos(MAIN_DOMAIN, '.', $pos1);
                $domain_arr[0] = substr(MAIN_DOMAIN, 0, $pos1);
                $domain_arr[1] = '';
                $domain_arr[2] = trim(substr(MAIN_DOMAIN, $pos2), '/') . '/';
            }
            $domain_arr[1] = $custom_data[$store_id];
            $custom_url = implode('', $domain_arr);
        }
    }

    return $custom_url;
}

/**
 * ��ȡ����url
 * @author  wj
 * @param   int     $store_id
 * @return  stirng
 */
function get_store_url($store_id)
{
    $custom_url = get_store_custom_url($store_id);
    if (empty($custom_url))
    {
        $custom_url = site_url() . '/index.php?app=store&amp;store_id=' . $store_id;
    }

    return $custom_url;
}

/**
 * �жϵ�ǰ�Ƿ�ʹ����վ���ַ
 * @author      wj
 * @param       void
 * @return      void
 */
function is_main_site()
{
    $ret_val = true;
    //�������������������
    if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN)
    {
        $url = get_domain();
        //�˶��Ƿ���������
        if (strncmp($url, MAIN_DOMAIN, strlen($url)) !== 0)
        {
            $ret_val = false;
        }
    }

    return $ret_val;
}

?>
