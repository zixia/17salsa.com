<?php

/**
 * ECMall: MYSQL �������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.mysql.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class cls_mysql
{
    var $max_cache_time = 300; // ���Ļ���ʱ�䣬����Ϊ��λ
    var $cache_dir      = 'temp/query_caches/';
    var $query_log      = array();
    var $root_path      = '';

    var $error_message  = array();
    var $version        = '';
    var $starttime      = 0;
    var $timeline       = 0;
    var $timezone       = 0;

    /* Private attributes */
    var $_link_id       = NULL;
    var $_settings      = array();
    var $_query_count   = 0;
    var $_dbhash        = '';
    var $_platform      = '';

    var $mysql_config_cache_file_time = 0;
    var $mysql_disable_cache_tables = array(); // ����������ı�������������л���

    function __construct()
    {
        $this->cls_mysql();
    }

    /**
     * db���캯��
     *
     * @author  wj
     *
     * @return  void
     **/
    function cls_mysql()
    {
        if (!defined('DB_PREFIX'))
        {
            $this->ErrorMsg('Have not define DB_PREFIX');
        }
        $this->cache_dir = ROOT_PATH . '/temp/query_caches/';
    }

    /**
     * �������ݿ�
     *
     * author wj
     * param  string  $dbhost   ���ݿ�������
     * param  string  $dbuser   ���ݿ��û���
     * param  string  $dbpw     ���ݿ�����
     * param  string  $charset  ���ݿ��ַ���
     * param  string  $pconnect �־�����,1Ϊ����,0Ϊ�ر�
     * param  string  $quiet    ����ģʽ,1Ϊ����,0Ϊ�ر�
     * return bool
     **/
    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $pconnect = 0, $quiet = 0)
    {
        if ($pconnect)
        {
            if (!($this->_link_id = @mysql_pconnect($dbhost, $dbuser, $dbpw)))
            {
                if (!$quiet)
                {
                    $this->ErrorMsg();
                }

                return false;
            }
        }
        else
        {
            if (PHP_VERSION >= '4.2')
            {
                $this->_link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            }
            else
            {
                $this->_link_id = @mysql_connect($dbhost, $dbuser, $dbpw);

                mt_srand((double)microtime() * 1000000); // �� PHP 4.2 ���µİ汾��������������ĳ�ʼ������
            }

            if (!$this->_link_id)
            {
                if (!$quiet)
                {
                    $this->ErrorMsg();
                }

                return false;
            }
        }

        $this->_dbhash  = md5(ROOT_PATH . $dbhost . $dbuser . $dbpw . $dbname);
        $this->version  = mysql_get_server_info($this->_link_id);

        /* ���mysql �汾�� 4.1+ ���ϣ���Ҫ���ַ������г�ʼ�� */
        if ($this->version > '4.1')
        {
            if ($charset != 'latin1')
            {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->_link_id);
            }
            if ($this->version > '5.0.1')
            {
                mysql_query("SET sql_mode=''", $this->_link_id);
            }
        }

        $sqlcache_config_file = $this->cache_dir . 'config_file_' . $this->_dbhash . '.php';

        if (is_file($sqlcache_config_file))
        {
            include($sqlcache_config_file);
        }

        $this->starttime = time();

        if ($this->max_cache_time && $this->starttime > $this->mysql_config_cache_file_time + $this->max_cache_time)
        {
            if ($dbhost != '.')
            {
                $result = mysql_query("SHOW VARIABLES LIKE 'basedir'", $this->_link_id);
                $row    = mysql_fetch_assoc($result);
                if (!empty($row['Value']{1}) && $row['Value']{1} == ':' && !empty($row['Value']{2}) && $row['Value']{2} == "\\")
                {
                    $this->_platform = 'WINDOWS';
                }
                else
                {
                    $this->_platform = 'OTHER';
                }
            }
            else
            {
                $this->_platform = 'WINDOWS';
            }

            if ($this->_platform == 'OTHER' &&
                ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306') ||
                (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC'))
            {
                $result = mysql_query("SELECT UNIX_TIMESTAMP() AS timeline, UNIX_TIMESTAMP('" . date('Y-m-d H:i:s', $this->starttime) . "') AS timezone", $this->_link_id);
                $row    = mysql_fetch_assoc($result);

                if ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306')
                {
                    $this->timeline = $this->starttime - $row['timeline'];
                }

                if (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC')
                {
                    $this->timezone = $this->starttime - $row['timezone'];
                }
            }

            $content = '<' . "?php\r\n" .
                       '$this->mysql_config_cache_file_time = ' . $this->starttime . ";\r\n" .
                       '$this->timeline = ' . $this->timeline . ";\r\n" .
                       '$this->timezone = ' . $this->timezone . ";\r\n" .
                       '$this->_platform = ' . "'" . $this->_platform . "';\r\n?" . '>';

            @file_put_contents($sqlcache_config_file, $content, LOCK_EX);
        }

        /* ѡ�����ݿ� */
        if ($dbname)
        {
            if (mysql_select_db($dbname, $this->_link_id) === false )
            {
                if (!$quiet)
                {
                    $this->ErrorMsg();
                }

                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }

    function select_database($dbname)
    {
        return mysql_select_db($dbname, $this->_link_id);
    }

    function set_mysql_charset($charset)
    {
        /* ���mysql �汾�� 4.1+ ���ϣ���Ҫ���ַ������г�ʼ�� */
        if ($this->version > '4.1')
        {
            if (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8', 'utf8')))
            {
                $charset = str_replace('-', '', $charset);
            }
            if ($charset != 'latin1')
            {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->_link_id);
            }
        }
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC)
    {
        return mysql_fetch_array($query, $result_type);
    }

    function query($sql, $type = '', $times=0)
    {
        if ($this->_link_id === NULL)
        {
            $this->connect($this->_settings['dbhost'], $this->_settings['dbuser'], $this->_settings['dbpw'], $this->_settings['dbname'], $this->_settings['charset'], $this->_settings['pconnect']);
            $this->_settings = array();
        }

        $this->_query_count++;
        if ($this->_query_count <= 99)
        {
            $this->query_log[] = $sql;
        }

        /* ����ǰ��ʱ��������ʼ��ʱ���ʱ���Զ�ִ�� ping ����Զ��������Ӳ��� */
        if (PHP_VERSION >= '4.3' && time() > $this->starttime + 1)
        {
            mysql_ping($this->_link_id);
        }

        $sql = $this->prefix($sql); // �������ǰ׺

        if (!($query = mysql_query($sql, $this->_link_id)))
        {
            $errno = mysql_errno();
            $error = mysql_error();

            if (($errno == 126 || $errno == 145 || $errno == 1062 || $errno == 1194 || $errno == 1034 || $errno == 1035)
                && $times == 0 && (preg_match('/\'\.?\\\\?([\w_]+)\\\\?([\w_]+)\'/', $error, $match) !== false))
            {
                //echo "REPAIR TABLE $match[2]<br>";
                /* �����������Ϊ���޸��������޸������ */
                if (isset($match[2]))
                {
                    mysql_query("REPAIR TABLE $match[2]");
                    $query = $this->query($sql, $type, 1);
                }
            }
            elseif ($errno == 2006)
            {
                $this->ErrorMsg();exit;
            }
            else
            {
                if ($type != 'SILENT')
                {
                    $trace  = debug_backtrace();
                    $msg    = 'MySQL Error[' .mysql_errno($this->_link_id). ']: ' . mysql_error($this->_link_id). "\nMySQL Query:" .$sql;
                    $msg    .= "\nWrong File:  " .$trace[0]['file']. "[" .$trace[0]['line']. "]";

                    trigger_error($msg, E_USER_ERROR);

                    return false;
                }
            }
        }

        /* ��¼sql log */
        if (defined('DEBUG_MODE') && (DEBUG_MODE & 8) == 8)
        {
            $logfilename = $this->root_path . 'data/mysql_query_' . $this->_dbhash . '_' . date('Y_m_d') . '.log';
            $str = $sql . "\n\n";

            if (PHP_VERSION >= '5.0')
            {
                file_put_contents($logfilename, $str, FILE_APPEND, LOCK_EX);
            }
            else
            {
                $fp = @fopen($logfilename, 'ab+');
                if ($fp)
                {
                    fwrite($fp, $str);
                    fclose($fp);
                }
            }
        }

        return $query;
    }

    function prefix($sql)
    {
        return preg_replace('/\s\`ecm_(.+?)\`([,|\s]?)/', ' ' .DB_PREFIX. '$1$2', $sql);
    }

    function affected_rows()
    {
        return mysql_affected_rows($this->_link_id);
    }

    function error()
    {
        return mysql_error($this->_link_id);
    }

    function errno()
    {
        return mysql_errno($this->_link_id);
    }

    function result($query, $row)
    {
        return @mysql_result($query, $row);
    }

    function num_rows($query)
    {
        return mysql_num_rows($query);
    }

    function num_fields($query)
    {
        return mysql_num_fields($query);
    }

    function free_result($query)
    {
        return mysql_free_result($query);
    }

    function insert_id()
    {
        return mysql_insert_id($this->_link_id);
    }

    function fetchRow($query)
    {
        return mysql_fetch_assoc($query);
    }

    function fetch_fields($query)
    {
        return mysql_fetch_field($query);
    }

    function version()
    {
        return $this->version;
    }

    function ping()
    {
        if (PHP_VERSION >= '4.3')
        {
            return mysql_ping($this->_link_id);
        }
        else
        {
            return false;
        }
    }

    function escape_string($unescaped_string)
    {
        if (PHP_VERSION >= '4.3')
        {
            return mysql_real_escape_string($unescaped_string);
        }
        else
        {
            return mysql_escape_string($unescaped_string);
        }
    }

    function close()
    {
        return mysql_close($this->_link_id);
    }

    function ErrorMsg($message = '', $sql = '')
    {
        if ($message)
        {
            echo "<b>ECMall info</b>: $message\n\n";
        }
        else
        {
            static $last_errno = null;
            $error = mysql_error();
            $error_no = mysql_errno();
            if ($last_errno == $error_no)
            {
                exit;
            }
            if ($last_errno === null)
            {
                $last_errno = $error_no;
            }
            Language::load_lang(lang_file('common'));

            echo "<b>MySQL server error report:</b><br />";
            echo "Error:",$error, "<br />";
            echo "Errno:", $error_no, "<br />";
            echo '<a href="http://ecmall.shopex.cn/help/faq.php?type=mysql&amp;dberrno=' . $error_no . '&amp;dberror=' . urlencode($error) . '" target="_blank">'. Language::get('mysql_error_report') . '</a>';
        }

        exit;
    }

/* ���� Adodb ���� */
    function selectLimit($sql, $num, $start = 0)
    {
        if ($start == 0)
        {
            $sql .= ' LIMIT ' . $num;
        }
        else
        {
            $sql .= ' LIMIT ' . $start . ', ' . $num;
        }

        return $this->query($sql);
    }

    function getOne($sql, $limited = false)
    {
        if ($limited == true)
        {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false)
        {
            $row = mysql_fetch_row($res);

            if ($row !== false)
            {
                return $row[0];
            }
            else
            {
                return '';
            }
        }
        else
        {
            return false;
        }
    }

    function getOneCached($sql, $cached = 'FILEFIRST')
    {
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->_platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getOne($sql, true);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getOne($sql, true);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getAll($sql)
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = mysql_fetch_assoc($res))
            {
                $arr[] = $row;
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }

    function getAllCached($sql, $cached = 'FILEFIRST')
    {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->_platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getAll($sql);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getAll($sql);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getRow($sql, $limited = false)
    {
        if ($limited == true)
        {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false)
        {
            return mysql_fetch_assoc($res);
        }
        else
        {
            return false;
        }
    }

    function getRowCached($sql, $cached = 'FILEFIRST')
    {
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->_platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getRow($sql, true);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getRow($sql, true);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getCol($sql)
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = mysql_fetch_row($res))
            {
                $arr[] = $row[0];
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }

    function getColCached($sql, $cached = 'FILEFIRST')
    {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->_platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getCol($sql);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getCol($sql);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = '')
    {
        $field_names = $this->getCol('DESC ' . $table);

        $sql = '';
        if ($mode == 'INSERT')
        {
            $fields = $values = array();
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }

            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else
        {
            $sets = array();
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
                }
            }

            if (!empty($sets))
            {
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }

    function autoReplace($table, $field_values, $update_values, $where = '', $querymode = '')
    {
        $field_descs = $this->getAll('DESC ' . $table);

        $primary_keys = array();
        foreach ($field_descs AS $value)
        {
            $field_names[] = $value['Field'];
            if ($value['Key'] == 'PRI')
            {
                $primary_keys[] = $value['Field'];
            }
        }

        $fields = $values = array();
        foreach ($field_names AS $value)
        {
            if (array_key_exists($value, $field_values) == true)
            {
                $fields[] = $value;
                $values[] = "'" . $field_values[$value] . "'";
            }
        }

        $sets = array();
        foreach ($update_values AS $key => $value)
        {
            if (array_key_exists($key, $field_values) == true)
            {
                if (is_int($value) || is_float($value))
                {
                    $sets[] = $key . ' = ' . $key . ' + ' . $value;
                }
                else
                {
                    $sets[] = $key . " = '" . $value . "'";
                }
            }
        }

        $sql = '';
        if (empty($primary_keys))
        {
            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else
        {
            if ($this->version() >= '4.1')
            {
                if (!empty($fields))
                {
                    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                    if (!empty($sets))
                    {
                        $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
                    }
                }
            }
            else
            {
                if (empty($where))
                {
                    $where = array();
                    foreach ($primary_keys AS $value)
                    {
                        if (is_numeric($value))
                        {
                            $where[] = $value . ' = ' . $field_values[$value];
                        }
                        else
                        {
                            $where[] = $value . " = '" . $field_values[$value] . "'";
                        }
                    }
                    $where = implode(' AND ', $where);
                }

                if ($where && (!empty($sets) || !empty($fields)))
                {
                    if (intval($this->getOne("SELECT COUNT(*) FROM $table WHERE $where")) > 0)
                    {
                        if (!empty($sets))
                        {
                            $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
                        }
                    }
                    else
                    {
                        if (!empty($fields))
                        {
                            $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                        }
                    }
                }
            }
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }

    function setMaxCacheTime($second)
    {
        $this->max_cache_time = $second;
    }

    function getMaxCacheTime()
    {
        return $this->max_cache_time;
    }

    /**
     * ��ȡ���ݼ�¼������
     *
     * author redstone
     * param  string  $sql     ��ѯ���
     * param  string  $cached  ����ѡ��
     * return array
     **/
    function getSqlCacheData($sql, $cached = '')
    {
        $sql = trim($sql);

        $result = array();
        $result['filename'] = $this->cache_dir . abs(crc32($this->_dbhash . $sql)) . '_' . md5($this->_dbhash . $sql) . '.php';
        if (file_exists($result['filename']) && ($data = file_get_contents($result['filename'])) && isset($data{23}))
        {
            $filetime = substr($data, 13, 10);
            $data     = substr($data, 23);

            if (($cached == 'FILEFIRST' && time() > $filetime + $this->max_cache_time) ||
                ($cached == 'MYSQLFIRST' && $this->table_lastupdate($this->get_table_name($sql)) > $filetime))
            {
                $result['storecache'] = true;
            }
            else
            {
                $result['data'] = @unserialize($data);
                if ($result['data'] === false)
                {
                    $result['storecache'] = true;
                }
                else
                {
                    $result['storecache'] = false;
                }
            }
        }
        else
        {
            $result['storecache'] = true;
        }

        return $result;
    }

    function setSqlCacheData($result, $data)
    {
        if ($result['storecache'] === true && $result['filename'])
        {
            @file_put_contents($result['filename'], '<?php exit;?>' . time() . serialize($data), LOCK_EX);
            clearstatcache();
        }
    }

    /* ��ȡ SQL ����������µı��ʱ�䣬�ж���������£��������µı��ʱ�� */
    function table_lastupdate($tables)
    {
        if ($this->_link_id === NULL)
        {
            $this->connect($this->_settings['dbhost'], $this->_settings['dbuser'], $this->_settings['dbpw'], $this->_settings['dbname'], $this->_settings['charset'], $this->_settings['pconnect']);
            $this->_settings = array();
        }

        $lastupdatetime = '0000-00-00 00:00:00';

        $tables = str_replace('`', '', $tables);
        $this->mysql_disable_cache_tables = str_replace('`', '', $this->mysql_disable_cache_tables);

        foreach ($tables AS $table)
        {
            if (in_array($table, $this->mysql_disable_cache_tables) == true)
            {
                $lastupdatetime = '2037-12-31 23:59:59';

                break;
            }

            if (strpos($table, '.') !== false)
            {
                $tmp = explode('.', $table);
                $sql = 'SHOW TABLE STATUS FROM `' . trim($tmp[0]) . "` LIKE '" . trim($tmp[1]) . "'";
            }
            else
            {
                $sql = "SHOW TABLE STATUS LIKE '" . trim($table) . "'";
            }
            $result = mysql_query($sql, $this->_link_id);

            $row = mysql_fetch_assoc($result);
            if ($row['Update_time'] > $lastupdatetime)
            {
                $lastupdatetime = $row['Update_time'];
            }
        }
        $lastupdatetime = strtotime($lastupdatetime) - $this->timezone + $this->timeline;

        return $lastupdatetime;
    }

    function get_table_name($query_item)
    {
        $query_item = trim($query_item);
        $table_names = array();

        /* �ж�������ǲ��Ǻ��� JOIN */
        if (stristr($query_item, ' JOIN ') == '')
        {
            /* ����һ��� SELECT FROM ��� */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $query_item, $table_names))
            {
                $table_names = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $table_names[1]);

                return preg_split('/\s*,\s*/', $table_names);
            }
        }
        else
        {
            /* �Ժ��� JOIN �������н��� */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $query_item, $table_names))
            {
                $other_table_names = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $query_item, $other_table_names);

                return array_merge(array($table_names[1]), $other_table_names[1]);
            }
        }

        return $table_names;
    }

    /* ���ò�������л���ı� */
    function set_disable_cache_tables($tables)
    {
        if (!is_array($tables))
        {
            $tables = explode(',', $tables);
        }

        foreach ($tables AS $table)
        {
            $this->mysql_disable_cache_tables[] = $table;
        }

        array_unique($this->mysql_disable_cache_tables);
    }
}

?>
