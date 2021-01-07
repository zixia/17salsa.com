<?php

/**
 * ECMALL: ��Ϣ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.message.php 6009 2008-10-31 01:55:52Z Garbin $
 */
if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

/**
 * Exception Handler
 *
 * error:   �жϳ�����ʾ��Ϣ�г��ֱ���bug�������Լ���¼����־�ļ�
 * warning: �жϳ�����ʾ��Ϣ�г��ֱ���bug�����ӡ�
 * notice:  ���жϳ��򣬴�����Ϣ�����ҳ���ע����
 *---------------------------------------
 * �û����Ĵ�����ʽ��
 *
 * error:   �жϳ�����ʾ��Ϣ�г��ֱ���bug�������Լ���¼����־�ļ�
 * warning: �жϳ�����ʾ��Ϣ�в����������ļ����кŵ���Ϣ
 * notice:  �жϳ�����ʾ��Ϣ�в����������ļ����кŵ���Ϣ�� ��δ������ӵ�ַ�أ�
 *---------------------------------------
 *
 * @param   number  $errno
 * @param   string  $errstr
 * @param   string  $errfile
 * @param   string  $errline
 *
 * @return  void
 */
function exception_handler($errno, $errstr, $errfile, $errline)
{
    if ($errno == 2048)
    {
        return true;
    }

    if ($errno != E_NOTICE)
    {
        $msg        = new Message($errstr, $errno);
        $errfile    = str_replace(ROOT_PATH, '', $errfile);

        if ($errno != E_USER_WARNING && $errno != E_USER_NOTICE)
        {
            $msg->err_file = $errfile;
            $msg->err_line = $errline;
        }

        /* add report link */
        if ($errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_PARSE || $errno == E_WARNING)
        {
            $msg->report_link($errno, $errstr, $errfile, $errline);

            put_log($errno, $errstr, $errfile, $errline); // д�������־

            $msg->display();
            exit;
        }
        else
        {
            $msg->display();
        }
    }
    else if ($errno == E_NOTICE && (defined(DEBUG_MODE) && DEBUGE_MODE > 0) )
    {
        echo "<div style='font: 14px verdana'><b>Notice:</b> $errstr<br/><b>Error File:</b> $errfile: [$errline]</div>";
    }

    return true;
}

/**
 * д�� log �ļ�
 *
 * @param   string  $msg
 * @param   string  $file
 * @param   string  $line
 */
function put_log($err, $msg, $file, $line)
{
    $filename = ROOT_PATH . "/temp/logs/" .date("Ym"). ".log";

    if (!is_dir('temp/logs'))
    {
        ecm_mkdir('temp/logs');
    }

    $handler = null;

    if (($handler = fopen($filename, 'ab+')) !== false)
    {
        fwrite($handler, date('r') . "\t[$err]$msg\t$file\t$line\n");
        fclose($handler);
    }
}

class Message extends MessageBase
{
    var $caption    = '';
    var $icon       = '';
    var $links      = array();
    var $redirect   = '';
    var $err_line   = '';
    var $err_file   = '';

    function __construct($str='', $errno=null)
    {
        $this->Message($str, $errno);
    }
    function Message($str, $errno=null)
    {
        if ($errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_WARNING)
        {
            $this->icon = "error";
        }
        else if ($errno == E_USER_WARNING)
        {
            $this->icon = "warning";
        }
        else
        {
            $this->icon = "notice";
        }

        $this->handle_message($str);
    }
    function handle_message($msg)
    {
        /* decode message */
        $arr = @unserialize($msg);

        if ($arr === false)
        {
            $this->message = nl2br($msg);
        }
        else
        {
            foreach ($arr['links'] AS $key=>$val)
            {
                $this->add_link($val['text'], $val['href']);
            }
            $this->message = nl2br($arr['content']);
        }
    }
    /**
     * ����bug��������
     *
     * @author wj
     * @param string $err  ��������
     * @param string $msg ������Ϣ
     * @param string $file   �����ļ�
     * @param string $line   �����к�
     * @return  void
     */
    function report_link($err, $msg, $file, $line)
    {
        if (strncmp($msg, 'MySQL Error[', 12) == 0)
        {
            $tmp_arr = explode("\n", $msg, 2);
            $tmp_param = strtr($tmp_arr[0], array('MySQL Error['=>'dberrno=', ']: '=>'&dberror='));
            parse_str($tmp_param, $tmp_arr);
            $url = 'http://ecmall.shopex.cn/help/faq.php?type=mysql&dberrno=' . $tmp_arr['dberrno'] . '&dberror=' .  urlencode($tmp_arr['dberror']);

            $this->add_link($this->lang('mysql_error_report'), $url);
        }
        else
        {
            $arr_report = array('err'=>$err, 'msg'=>$msg, 'file'=>$file, 'line'=>$line, 'query_string'=>$_SERVER['QUERY_STRING'], 'occur_date'=>local_date('Y-m-d H:i:s'));
            foreach ($arr_report as $k=>$v)
            {
                $arr_report[$k] = $k . chr(9) . $v;
            }
            $str_report = str_replace('=', '', base64_encode(implode(chr(8), $arr_report)));
            $url = 'index.php?app=issue&data=' . $str_report . '&amp;sign=' . md5($str_report . ECM_KEY);

            $this->add_link($this->lang('report_issue'), $url);
        }

        $this->add_link($this->lang('go_back'));
    }

    /**
     * ���һ�����ӵ���Ϣҳ��
     *
     * @author  weberliu
     * @param   string  $text
     * @param   string  $href
     * @return  void
     */
    function add_link($text, $href='javascript:history.back()')
    {
        $this->links[] = array('text' => $text, 'href' => $href);

        if ($this->icon == 'notice' && $this->redirect == '')
        {
            $this->redirect = (strstr($href, 'javascript:') !== false) ? $href : "location.href='{$href}'";
        }
    }

    /**
     * ��ʾ��Ϣҳ��
     *
     * @author  wj
     * @return  void
     */
    function display()
    {
        $this->message = str_replace(ROOT_PATH, '', $this->message);

        if (defined('IS_AJAX') && IS_AJAX)
        {
            $error_line = empty($this->err_file[$this->err_line]) ? '' : "\n\nFile: $this->err_file[$this->err_line]";
            if ($this->icon == "notice")
            {
                $this->json_result('', $this->message . $error_line);
                return;
            }
            else
            {
                $this->json_error($this->message . $error_line);
                return;
            }
        }
        else
        {
            if ($this->redirect)
            {
                $this->redirect = str_replace('&amp;', '&', $this->redirect); //$this->redirect �Ǹ�jsʹ�õ�,���ܰ���&amp;
            }
            $this->assign('message',    $this->message);
            $this->assign('links',      $this->links);
            $this->assign('icon',       $this->icon);
            $this->assign('err_line',   $this->err_line);
            $this->assign('err_file',   $this->err_file);
            $this->assign('redirect',   $this->redirect);
            restore_error_handler(); //������ʾʱ������׽�ص�,����display����ʱ������ѭ��
            parent::display('message', $this->_message_template_dir);
        }
    }
}
?>