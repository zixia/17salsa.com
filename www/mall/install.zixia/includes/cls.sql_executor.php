<?php

/**
 * ECMall SQL���ִ���ࡣ�ڵ��ø��෽��֮ǰ����ο���Ӧ������˵����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2007 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.ecmall.com
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: cls.sql_executor.php 6047 2008-11-10 09:13:02Z Garbin $
 */

if (!defined('IN_ECM'))
{
    die('Hacking attempt');
}

class sql_executor
{
    /**
     * ��¼����ִ�й�����������������������Ϣ
     *
     * @access  public
     * @var     string       $error
     */
    var $error = '';

    /**
     * �洢�������ԵĴ���ţ���Щ���󲻻��¼��$error�����У�
     * ����Ȼ���¼�ڴ�����־�ļ����С�
     *
     * @access  private
     * @var     array       $ignored_errors
     */
    var $ignored_errors = array();

    /**
     * MySQL����
     *
     * @access  private
     * @var     object      $db
     */
    var $db = '';

    /**
     * ���ݿ��ַ�����
     *
     * @access   private
     * @var      string     $charset
     */
    var $db_charset = '';

    /**
     * �滻ǰ��ǰ׺
     *
     * @access  private
     * @var     string      $source_prefix
     */
    var $source_prefix = '';

    /**
     * �滻���ǰ׺
     *
     * @access  private
     * @var     string      $target_prefix
     */
    var $target_prefix = '';

    /**
     * ����������ʱ�����򽫰���־��¼�ڸ�ָ�����ļ���
     *
     * @access  private
     * @var     string       $log_path
     */
    var $log_path = '';

    /**
     * ������ѡ��󣬳��򽫽������ܻ��ز�ѯ��������ʹ�ظ����б�����Ҳ�����������ݿ�Ĳ�ѯ��ͻ������������
     * �ͷ�����֮�����ͨѶʱ�Ƿǳ��б�Ҫ�ģ���Ϊ������п�������������䷢���жϡ������������õ��˴���������
     * ���ʽ��������ѡ��󽫷ǳ��ķѷ���������Դ��
     *
     * @access  private
     * @var     boolean      $auto_match
     */
    var $auto_match = false;

    /**
     * ��¼��ǰ����ִ�е�SQL�ļ���
     *
     * @access  private
     * @var     string       $current_file
     */
    var $current_file = 'Not a file, but a string.';

    /**
     * ���캯��
     *
     * @access  public
     * @param   mysql       $db             mysql�����
     * @param   string      $charset        �ַ���
     * @param   string      $sprefix        �滻ǰ��ǰ׺
     * @param   string      $tprefix        �滻���ǰ׺
     * @param   string      $log_path       ��־·��
     * @param   boolean     $auto_match     �Ƿ�������ܻ���ѯ
     * @param   array       $ignored_errors ���ԵĴ��������
     * @return  void
     */
    function __construct($db, $charset = 'utf8', $sprefix = 'ecm_', $tprefix = 'ecm_', $log_path = '', $auto_match = false, $ignored_errors = array())
    {

        $this->sql_executor($db, $charset, $sprefix, $tprefix, $log_path, $auto_match, $ignored_errors);
    }

    /**
     * ���캯��
     *
     * @access  public
     * @param   mysql       $db             mysql�����
     * @param   string      $charset        �ַ���
     * @param   string      $sprefix        �滻ǰ��ǰ׺
     * @param   string      $tprefix        �滻���ǰ׺
     * @param   string      $log_path       ��־·��
     * @param   boolean     $auto_match     �Ƿ�������ܻ���ѯ
     * @param   array       $ignored_errors ���ԵĴ��������
     * @return  void
     */
    function sql_executor($db, $charset = 'utf8', $sprefix = 'ecm_', $tprefix = 'ecm_', $log_path = '', $auto_match = false, $ignored_errors = array())
    {
        $this->db = $db;
        $this->db_charset = $charset;
        $this->source_prefix = $sprefix;
        $this->target_prefix = $tprefix;
        $this->log_path = $log_path;
        $this->auto_match = $auto_match;
        $this->ignored_errors = $ignored_errors;
    }

    /**
     * ִ������SQL�ļ������е�SQL���
     *
     * @access  public
     * @param   array       $sql_files     �ļ�����·����ɵ�һά����
     * @return  boolean     ִ�гɹ�����true��ʧ�ܷ���false��
     */
    function run_all($sql_files)
    {
        if (!is_array($sql_files))
        {
            return false;
        }


        foreach ($sql_files AS $sql_file)
        {
            $query_items = $this->parse_sql_file($sql_file);

            /* �������ʧ�ܣ������� */
            if (!$query_items)
            {
                continue;
            }

            foreach ($query_items AS $query_item)
            {
                /* �����ѯ��Ϊ�գ������� */
                if (!$query_item)
                {
                    echo $query_item;
                    continue;
                }
                if (!$this->query($query_item))
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * ִ���Ѽ��ص�SQL
     *
     * @autho   liupeng
     * @param   array       $sql_files     �ļ�����·����ɵ�һά����
     * @return  boolean     ִ�гɹ�����true��ʧ�ܷ���false��
     */
    function run()
    {
        //$sql = $this->replace_prefix($this->_sql);
        $sql = $this->_sql;
        /* ������ѯ�� */
        $sql = str_replace("\r", '', $sql);
        $query_items = explode(";\n", $sql);

        foreach ($query_items AS $query_item)
        {
            $query_item = $this->remove_comment($query_item);

            /* �����ѯ��Ϊ�գ������� */
            if (!$query_item)
            {
                continue;
            }
            if (!$this->query($query_item))
            {
                echo $query_item;
                return false;
            }
        }

        return true;
    }

    /**
     * ����SQL
     *
     * @access  public
     * @param   string      $file_path      �ļ��ľ���·��
     * @return  mixed       �����ɹ����ط�ɢ�Ĳ�ѯ�����飬ʧ�ܷ���false��
     */
    function load_string($sql)
    {
        $this->_sql = $sql;
    }

    /**
     * ��÷�ɢ�Ĳ�ѯ��
     *
     * @access  public
     * @param   string      $file_path      �ļ��ľ���·��
     * @return  mixed       �����ɹ����ط�ɢ�Ĳ�ѯ�����飬ʧ�ܷ���false��
     */
    function parse_sql_file($file_path)
    {
        /* ���SQL�ļ��������򷵻�false */
        if (!file_exists($file_path))
        {
            return false;
        }

        /* ��¼��ǰ�������е�SQL�ļ� */
        $this->current_file = $file_path;

        /* ��ȡSQL�ļ� */
        $sql = implode('', file($file_path));

        /* ɾ��SQLע�ͣ�����ִ�е���replace���������Բ���Ҫ���м�⡣��ͬ�� */
        $sql = $this->remove_comment($sql);

        /* ɾ��SQL����β�Ŀհ׷� */
        $sql = trim($sql);

        /* ���SQL�ļ���û�в�ѯ����򷵻�false */
        if (!$sql)
        {
            return false;
        }

        /* �滻��ǰ׺ */
        //$sql = $this->replace_prefix($sql);

        /* ������ѯ�� */
        $sql = str_replace("\r", '', $sql);
        $query_items = explode(";\n", $sql);

        return $query_items;
    }

    /**
     * ִ��ĳһ����ѯ��
     *
     * @access  public
     * @param   string      $query_item      ��ѯ��
     * @return  boolean     �ɹ�����true��ʧ�ܷ���false��
     */
    function query($query_item)
    {
        /* ɾ����ѯ����β�Ŀհ׷� */
        $query_item = trim($query_item);

        /* �����ѯ��Ϊ���򷵻�false */
        if (!$query_item)
        {
            return false;
        }

        /* ��������� */
        if (preg_match('/^\s*CREATE\s+TABLE\s*(.+?)\s/i', $query_item, $arr))
        {
            $res = $this->create_table($query_item);

            if (function_exists('show_js_message'))
            {
                $table_name = str_replace('`', '', $arr[1]);
                $str = $GLOBALS['lang']['create_data_table'] . $table_name;
                if (!$res)
                {
                    $str .= ' ' . $GLOBALS['lang']['failed'];
                }
                else
                {
                    $str .= ' ' . $GLOBALS['lang']['succeed'];
                }

                show_js_message($str);
            }


            if (!$res)
                return false;
        }
        /* ����ALTER TABLE��䣬��ʱ���򽫶Ա�Ľṹ�����޸� */
        elseif ($this->auto_match && preg_match('/^\s*ALTER\s+TABLE\s*/i', $query_item))
        {
            if (!$this->alter_table($query_item))
            {
                return false;
            }
        }
        /* ���������޸Ĳ�������������ӡ����¡�ɾ���� */
        else
        {
            if (!$this->do_other($query_item))
            {
                echo $query_item;
                return false;
            }
        }

        return true;
    }

    /**
     * ����SQL��ѯ���е�ע�͡��÷���ֻ����SQL�ļ��ж�ռһ�л�һ�����Щע�͡�
     *
     * @access  public
     * @param   string      $sql        SQL��ѯ��
     * @return  string      �����ѹ��˵�ע�͵�SQL��ѯ����
     */
    function remove_comment($sql)
    {
        /* ɾ��SQL��ע�ͣ���ע�Ͳ�ƥ�任�з� */
        $sql = preg_replace('/^\s*(?:--|#).*/m', '', $sql);

        /* ɾ��SQL��ע�ͣ�ƥ�任�з�����Ϊ��̰��ƥ�� */
        //$sql = preg_replace('/^\s*\/\*(?:.|\n)*\*\//m', '', $sql);
        $sql = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql);

        return $sql;
    }

    /**
     * �滻��ѯ�������ݱ��ǰ׺���÷���ֻ�����в�ѯ��Ч��CREATE TABLE,
     * DROP TABLE, ALTER TABLE, UPDATE, REPLACE INTO, INSERT INTO
     *
     * @access  public
     * @param   string      $sql        SQL��ѯ��
     * @return  string      �������滻��ǰ׺��SQL��ѯ����
     */
    function replace_prefix($sql)
    {
        $keywords = 'CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|'
                  . 'DROP\s+TABLE(?:\s+IF\s+EXISTS)?|'
                  . 'ALTER\s+TABLE|'
                  . 'UPDATE|'
                  . 'REPLACE\s+INTO|'
                  . 'INSERT\s+INTO';

        $pattern = '/(' . $keywords . ')(\s*)`?' . $this->source_prefix . '(\w+)`?(\s*)/i';
        $replacement = '\1\2`' . $this->target_prefix . '\3`\4';

        $sql = preg_replace($pattern, $replacement, $sql);
        $pattern = '/(UPDATE.*?WHERE)(\s*)`?' . $this->source_prefix . '(\w+)`?(\s*\.)/i';
        $replacement = '\1\2`' . $this->target_prefix . '\3`\4';
        $sql = preg_replace($pattern, $replacement, $sql);

        return $sql;
    }

    /**
     * ��ȡ������֡��÷���ֻ�����в�ѯ��Ч��CREATE TABLE,
     * DROP TABLE, ALTER TABLE, UPDATE, REPLACE INTO, INSERT INTO
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $query_type     ��ѯ����
     * @return  mixed       �ɹ����ر�����֣�ʧ�ܷ���false��
     */
    function get_table_name($query_item, $query_type = '')
    {
        $pattern = '';
        $matches = array();
        $table_name = '';

        /* ���ûָ��$query_type�����Զ���ȡ */
        if (!$query_type && preg_match('/^\s*(\w+)/', $query_item, $matches))
        {
            $query_type = $matches[1];
        }

        /* ��ȡ��Ӧ��������ʽ */
        $query_type = strtoupper($query_type);
        switch ($query_type)
        {
        case 'ALTER' :
            $pattern = '/^\s*ALTER\s+TABLE\s*`?(\w+)/i';
            break;
        case 'CREATE' :
            $pattern = '/^\s*CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s*`?(\w+)/i';
            break;
        case 'DROP' :
            $pattern = '/^\s*DROP\s+TABLE(?:\s+IF\s+EXISTS)?\s*`?(\w+)/i';
            break;
        case 'INSERT' :
            $pattern = '/^\s*INSERT\s+INTO\s*`?(\w+)/i';
            break;
        case 'REPLACE' :
            $pattern = '/^\s*REPLACE\s+INTO\s*`?(\w+)/i';
            break;
        case 'UPDATE' :
            $pattern = '/^\s*UPDATE\s*`?(\w+)/i';
            break;
        default :
            return false;
        }

        if (!preg_match($pattern, $query_item, $matches))
        {
            return false;
        }
        $table_name = $matches[1];

        return $table_name;
    }

    /**
     *   ���SQL�ļ���ָ���Ĳ�ѯ��
     *
     * @access  public
     * @param   string    $file_path       SQL��ѯ��
     * @param   int       $pos             ��ѯ���������
     * @return  mixed     �ɹ����ظò�ѯ�ʧ�ܷ���false��
     */
    function get_spec_query_item($file_path, $pos)
    {
        $query_items = $this->parse_sql_file($file_path);

        if (empty($query_items)
                || empty($query_items[$pos]))
        {
            return false;
        }

        return $query_items[$pos];
    }

    /**
     * �ž�MYSQL�汾���������ݱ�
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @return  boolean     �ɹ�����true��ʧ�ܷ���false��
     */
    function create_table($query_item)
    {
        /* ��ȡ�������崮�Լ��������������������ִ�Сд��ƥ�任�з�����Ϊ̰��ƥ�� */
        $pattern = '/^\s*(CREATE\s+TABLE[^(]+\(.*\))(.*)$/is';
        if (!preg_match($pattern, $query_item, $matches))
        {
            return false;
        }
        $main = $matches[1];
        $postfix = $matches[2];

        /* �ӱ������������в��ұ������ */
        $pattern = '/.*(?:ENGINE|TYPE)\s*=\s*([a-z]+).*$/is';
        $type = preg_match($pattern, $postfix, $matches) ? $matches[1] : 'MYISAM';

        /* �ӱ������������в���������� */
        $pattern = '/.*(AUTO_INCREMENT\s*=\s*\d+).*$/is';
        $auto_incr = preg_match($pattern, $postfix, $matches) ? $matches[1] : '';

        /* �������ñ����������� */
        $postfix = $this->db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARACTER SET " . $this->db_charset
                                                : " TYPE=$type";
        $postfix .= ' ' . $auto_incr;

        /* ���¹��콨����� */
        $sql = $main . $postfix;

        /* ��ʼ������ */
        if (!$this->db->query($sql, 'SILENT'))
        {
            $this->handle_error($sql);
            return false;
        }

        return true;
    }

    /**
     * �޸����ݱ�ķ������㷨���˼·��
     * 1. �Ƚ����ֶ��޸Ĳ�����CHANGE
     * 2. Ȼ������ֶ��Ƴ�������DROP [COLUMN]
     * 3. ���Ž����ֶ���Ӳ�����ADD [COLUMN]
     * 4. ���������Ƴ�������DROP INDEX
     * 5. ����������Ӳ�����ADD INDEX
     * 6. ����������������
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @return  boolean     �޸ĳɹ�����true�����򷵻�false
     */
    function alter_table($query_item)
    {
        /* ��ȡ���� */
        $table_name = $this->get_table_name($query_item, 'ALTER');
        if (!$table_name)
        {
            return false;
        }

        /* �Ȱ�CHANGE������ȡ����ִ�У��ٹ��˵����� */
        $result = $this->parse_change_query($query_item, $table_name);
        if ($result[0] && !$this->db->query($result[0], 'SILENT'))
        {
            $this->handle_error($result[0]);
            return false;
        }
        if (!$result[1])
        {
            return true;
        }

        /* ��DROP [COLUMN]��ȡ����ִ�У��ٹ��˵����� */
        $result = $this->parse_drop_column_query($result[1], $table_name);
        if ($result[0] && !$this->db->query($result[0], 'SILENT'))
        {
            $this->handle_error($result[0]);
            return false;
        }
        if (!$result[1])
        {
            return true;
        }

        /* ��ADD [COLUMN]��ȡ����ִ�У��ٹ��˵����� */
        $result = $this->parse_add_column_query($result[1], $table_name);
        if ($result[0] && !$this->db->query($result[0], 'SILENT'))
        {
            $this->handle_error($result[0]);
            return false;
        }
        if (!$result[1])
        {
            return true;
        }

        /* ��DROP INDEX��ȡ����ִ�У��ٹ��˵����� */
        $result = $this->parse_drop_index_query($result[1], $table_name);
        if ($result[0] && !$this->db->query($result[0], 'SILENT'))
        {
            $this->handle_error($result[0]);
            return false;
        }
        if (!$result[1])
        {
            return true;
        }

        /* ��ADD INDEX��ȡ����ִ�У��ٹ��˵����� */
        $result = $this->parse_add_index_query($result[1], $table_name);
        if ($result[0] && !$this->db->query($result[0], 'SILENT'))
        {
            $this->handle_error($result[0]);
            return false;
        }
        /* ִ���������޸Ĳ��� */
        if ($result[1] && !$this->db->query($result[1], 'SILENT'))
        {
            $this->handle_error($result[1]);
            return false;
        }

        return true;
    }

    /**
     * ������CHANGE����
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $table_name     ����
     * @return  array       ����һ����CHANGE��������������������ɵ�����
     */
    function parse_change_query($query_item, $table_name = '')
    {
        $result = array('', $query_item);

        if (!$table_name)
        {
            $table_name = $this->get_table_name($query_item, 'ALTER');
        }

        $matches = array();
        /* ��1����ģʽƥ��old_col_name����2����ģʽƥ��column_definition����3����ģʽƥ��new_col_name */
        $pattern = '/\s*CHANGE\s*`?(\w+)`?\s*`?(\w+)`?([^,(]+\([^,]+?(?:,[^,)]+)*\)[^,]+|[^,;]+)\s*,?/i';
        if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER))
        {
            $fields = $this->get_fields($table_name);
            $num = count($matches);
            $sql = '';
            for ($i = 0; $i < $num; $i++)
            {
                /* ������д���ԭ���� */
                if (in_array($matches[$i][1], $fields))
                {
                    $sql .= $matches[$i][0];
                }
                /* ������д��������� */
                elseif (in_array($matches[$i][2], $fields))
                {
                    $sql .= 'CHANGE ' . $matches[$i][2] . ' ' . $matches[$i][2] . ' ' . $matches[$i][3] . ',';
                }
                else /* ������������������� */
                {
                    $sql .= 'ADD ' . $matches[$i][2] . ' ' . $matches[$i][3] . ',';
                    $sql = preg_replace('/(\s+AUTO_INCREMENT)/i', '\1 PRIMARY KEY', $sql);
                }
            }
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
            $result[0] = preg_replace('/\s*,\s*$/', '', $sql);//�洢CHANGE�������ѹ���ĩβ�Ķ���
            $result[0] = $this->insert_charset($result[0]);//�����ַ�������
            $result[1] = preg_replace($pattern, '', $query_item);//�洢��������
            $result[1] = $this->has_other_query($result[1]) ? $result[1]: '';
        }

        return $result;
    }

    /**
     * ������DROP COLUMN����
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $table_name     ����
     * @return  array       ����һ����DROP COLUMN����������������ɵ�����
     */
    function parse_drop_column_query($query_item, $table_name = '')
    {
        $result = array('', $query_item);

        if (!$table_name)
        {
            $table_name = $this->get_table_name($query_item, 'ALTER');
        }

        $matches = array();
        /* ��ģʽ�洢���� */
        $pattern = '/\s*DROP(?:\s+COLUMN)?(?!\s+(?:INDEX|PRIMARY))\s*`?(\w+)`?\s*,?/i';
        if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER))
        {
            $fields = $this->get_fields($table_name);
            $num = count($matches);
            $sql = '';
            for ($i = 0; $i < $num; $i++)
            {
                if (in_array($matches[$i][1], $fields))
                {
                    $sql .= 'DROP ' . $matches[$i][1] . ',';
                }
            }
            if ($sql)
            {
                $sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
                $result[0] = preg_replace('/\s*,\s*$/', '', $sql);//����ĩβ�Ķ���
            }
            $result[1] = preg_replace($pattern, '', $query_item);//����DROP COLUMN����
            $result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
        }

        return $result;
    }

    /**
     * ������ADD [COLUMN]����
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $table_name     ����
     * @return  array       ����һ����ADD [COLUMN]����������������ɵ�����
     */
    function parse_add_column_query($query_item, $table_name = '')
    {
        $result = array('', $query_item);

        if (!$table_name)
        {
            $table_name = $this->get_table_name($query_item, 'ALTER');
        }

        $matches = array();
        /* ��1����ģʽ�洢�ж��壬��2����ģʽ�洢���� */
        $pattern = '/\s*ADD(?:\s+COLUMN)?(?!\s+(?:INDEX|UNIQUE|PRIMARY))\s*(`?(\w+)`?(?:[^,(]+\([^,]+?(?:,[^,)]+)*\)[^,]+|[^,;]+))\s*,?/i';
        if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER))
        {
            $fields = $this->get_fields($table_name);
            $mysql_ver = $this->db->version();
            $num = count($matches);
            $sql = '';
            for ($i = 0; $i < $num; $i++)
            {
                if (in_array($matches[$i][2], $fields))
                {
                    /* ���Ϊ�Ͱ汾MYSQL����ѷǷ��ؼ��ֹ��˵� */
                    if  ($mysql_ver < '4.0.1' )
                    {
                        $matches[$i][1] = preg_replace('/\s*(?:AFTER|FIRST)\s*.*$/i', '', $matches[$i][1]);
                    }
                    $sql .= 'CHANGE ' . $matches[$i][2] . ' ' . $matches[$i][1] . ',';
                }
                else
                {
                    $sql .= 'ADD ' . $matches[$i][1] . ',';
                }
            }
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
            $result[0] = preg_replace('/\s*,\s*$/', '', $sql);//����ĩβ�Ķ���
            $result[0] = $this->insert_charset($result[0]);//�����ַ�������
            $result[1] = preg_replace($pattern, '', $query_item);//����ADD COLUMN����
            $result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
        }

        return $result;
    }

    /**
     * ������DROP INDEX����
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $table_name     ����
     * @return  array       ����һ����DROP INDEX����������������ɵ�����
     */
    function parse_drop_index_query($query_item, $table_name = '')
    {
        $result = array('', $query_item);

        if (!$table_name)
        {
            $table_name = $this->get_table_name($query_item, 'ALTER');
        }

        /* ��ģʽ�洢���� */
        $pattern = '/\s*DROP\s+(?:PRIMARY\s+KEY|INDEX\s*`?(\w+)`?)\s*,?/i';
        if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER))
        {
            $indexes = $this->get_indexes($table_name);
            $num = count($matches);
            $sql = '';
            for ($i = 0; $i < $num; $i++)
            {
                /* �����ģʽΪ�գ�ɾ������ */
                if (empty($matches[$i][1]))
                {
                    $sql .= 'DROP PRIMARY KEY,';
                }
                /* ����ɾ������ */
                elseif (in_array($matches[$i][1], $indexes))
                {
                    $sql .= 'DROP INDEX ' . $matches[$i][1] . ',';
                }
            }
            if ($sql)
            {
                $sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
                $result[0] = preg_replace('/\s*,\s*$/', '', $sql);//�洢DROP INDEX�������ѹ���ĩβ�Ķ���
            }
            $result[1] = preg_replace($pattern, '', $query_item);//�洢��������
            $result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
        }

        return $result;
    }

    /**
     * ������ADD INDEX����
     *
     * @access  public
     * @param   string      $query_item     SQL��ѯ��
     * @param   string      $table_name     ����
     * @return  array       ����һ����ADD INDEX����������������ɵ�����
     */
    function parse_add_index_query($query_item, $table_name = '')
    {
        $result = array('', $query_item);

        if (!$table_name)
        {
            $table_name = $this->get_table_name($query_item, 'ALTER');
        }

        /* ��1����ģʽ�洢�������壬��2����ģʽ�洢"PRIMARY KEY"����3����ģʽ�洢��������4����ģʽ�洢���� */
        $pattern = '/\s*ADD\s+((?:INDEX|UNIQUE|(PRIMARY\s+KEY))\s*(?:`?(\w+)`?)?\s*\(\s*`?(\w+)`?\s*(?:,[^,)]+)*\))\s*,?/i';
        if (preg_match_all($pattern, $query_item, $matches, PREG_SET_ORDER))
        {
            $indexes = $this->get_indexes($table_name);
            $num = count($matches);
            $sql = '';
            for ($i = 0; $i < $num; $i++)
            {
                $index = !empty($matches[$i][3]) ? $matches[$i][3] : $matches[$i][4];
                if (!empty($matches[$i][2]) && in_array('PRIMARY', $indexes))
                {
                    $sql .= 'DROP PRIMARY KEY,';
                }
                elseif (in_array($index, $indexes))
                {
                    $sql .= 'DROP INDEX ' . $index . ',';
                }
                $sql .= 'ADD ' . $matches[$i][1] . ',';
            }
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $sql;
            $result[0] = preg_replace('/\s*,\s*$/', '', $sql);//�洢ADD INDEX�������ѹ���ĩβ�Ķ���
            $result[1] = preg_replace($pattern, '', $query_item);//�洢�����Ĳ���
            $result[1] = $this->has_other_query($result[1]) ? $result[1] : '';
        }

        return $result;
    }

    /**
     * ��ȡ���е�indexes
     *
     * @access  public
     * @param   string      $table_name      ���ݱ���
     * @return  array
     */
    function get_indexes($table_name)
    {
        $indexes = array();

        $result = $this->db->query("SHOW INDEX FROM $table_name", 'SILENT');

        if ($result)
        {
            while ($row = $this->db->fetchRow($result))
            {
                $indexes[] = $row['Key_name'];
            }
        }

        return $indexes;
    }

    /**
     * ��ȡ���е�fields
     *
     * @access  public
     * @param   string      $table_name      ���ݱ���
     * @return  array
     */
    function get_fields($table_name)
    {
        $fields = array();

        $result = $this->db->query("SHOW FIELDS FROM $table_name", 'SILENT');

        if ($result)
        {
            while ($row = $this->db->fetchRow($result))
            {
                $fields[] = $row['Field'];
            }
        }

        return $fields;
    }

    /**
     * �ж��Ƿ��������Ĳ�ѯ
     *
     * @access  private
     * @param   string      $sql_string     SQL��ѯ��
     * @return  boolean     �з���true�����򷵻�false
     */
    function has_other_query($sql_string)
    {
        return preg_match('/^\s*ALTER\s+TABLE\s*`\w+`\s*\w+/i', $sql_string);
    }

    /**
     * �ڲ�ѯ���м����ַ�������
     *
     * @access  private
     * @param  string      $sql_string     SQL��ѯ��
     * @return  string     �����ַ������õ�SQL��ѯ��
     */
    function insert_charset($sql_string)
    {
        if ($this->db->version() > '4.1')
        {
            $sql_string = preg_replace('/(TEXT|CHAR\(.*?\)|VARCHAR\(.*?\))\s+/i',
                    '\1 CHARACTER SET ' . $this->db_charset . ' ',
                    $sql_string);
        }

        return $sql_string;
    }

    /**
     * �������������ݿ����
     *
     * @access  private
     * @param   string      $query_item     SQL��ѯ��
     * @return  boolean     �ɹ�����true��ʧ�ܷ���false��
     */
    function do_other($query_item)
    {
        if (!$this->db->query($query_item, 'SILENT'))
        {
            $this->handle_error($query_item);
            return false;
        }

        return true;
    }

    /**
     * ���������Ϣ
     *
     * @access  private
     * @param   string      $query_item     SQL��ѯ��
     * @return  boolean     �ɹ�����true��ʧ�ܷ���false��
     */
    function handle_error($query_item)
    {
        $mysql_error = 'ERROR NO: ' . $this->db->errno()
                    . "\r\nERROR MSG: " . $this->db->error();

        $error_str = "SQL Error:\r\n " . $mysql_error
                . "\r\n\r\n"
                . "Query String:\r\n ". $query_item
                . "\r\n\r\n"
                . "File Path:\r\n ". $this->current_file
                . "\r\n\r\n\r\n\r\n";

        /* ����һЩ���� */
        if (!in_array($this->db->errno(), $this->ignored_errors))
        {
            $this->error = $error_str;
        }

        if ($this->log_path)
        {
            $f = @fopen($this->log_path, 'ab+');
            if (!$f)
            {
                return false;
            }
            if (!@fwrite($f, $error_str))
            {
                return false;
            }
        }

        return true;
    }
}

?>
