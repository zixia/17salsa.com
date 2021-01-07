<?php
/**
 * ECMALL: Email Sender
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * $Id: cls.mailer.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

define('SMTP_STATUS_NOT_CONNECTED', 1, true);
define('SMTP_STATUS_CONNECTED',     2, true);


/*
 * Send Mail Class
 * --------------------------------------------
 * Usage:
 *
 * $mailer = new Mailer('Bill', 'name@domain.com', MAIL_PROTOCOL_SMTP, 'smtp.domain.com', '25', 'username', 'password');
 * $mailer->debug = true|false;
 * $res = $mailer->send('who@domain.com,you@domain.com', 'Email Subject', 'Message Body', 'CHARSET', 1);
*/
class Mailer
{
    var $connection, $recipients;
    var $timeout    = 15;
    var $errors     = array();
    var $status     = SMTP_STATUS_NOT_CONNECTED;
    var $protocol   = MAIL_PROTOCOL_LOCAL;
    var $priority   = 3; // 1 = High, 3 = Normal, 5 = low
    var $debug      = false;

    /* PRIVATE  ATTRIBUTES */
    var $_helo, $_auth, $_mail_id;
    var $_crlf = "\r\n";

    function __construct($from, $email, $protocol, $host='', $port='', $user='', $pass='')
    {
        $this->Mailer($from, $email, $protocol, $host, $port, $user, $pass);
    }

    function Mailer($from, $email, $protocol, $host='', $port='', $user='', $pass='')
    {
        $this->protocol = $protocol;
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->pass     = $pass;
        $this->from     = base64_encode($from);
        $this->email    = $email;
        $this->_auth    = !empty($pass);
    }

    function send($mailto, $subject, $content, $charset, $is_html, $receipt=false)
    {
        if ($this->protocol == MAIL_PROTOCOL_LOCAL)
        {
            $res = $this->_local_send($mailto, $subject, $content, $charset, $is_html, $receipt);
        }
        else
        {
            $res = $this->_smtp_send($mailto, $subject, $content, $charset, $is_html, $receipt);
        }

        return $res;
    }

    function _local_send($mailto, $subject, $content, $charset, $is_html, $receipt=false)
    {
        $subject    = $this->_make_subject($charset, $subject);
        $headers    = array();
        $headers[]  = $this->_make_from($charset); // 与dz保持一致，不然在126邮箱下显示不一致
        $headers[]  = $this->_make_content_type($charset, $is_html);
        $headers[]  = ($receipt) ? $this->_make_receipt($charset) : NULL;

        return mail($mailto, $subject, $content, implode($this->_crlf, $headers));
    }

    function _smtp_send($mailto, $subject, $content, $charset, $is_html, $receipt=false)
    {
        $this->_mail_id = md5(uniqid(time()));

        if (!function_exists('fsockopen'))
        {
            $this->errors[] = 'server_disabled_socked';

            return false;
        }

        $body       = base64_encode($content);
        if (!$this->_connect())
        {
            $this->errors[] = 'Connect Error: ' .$this->_gets();

            return false;
        }

        if (!$this->_helo())
        {
            $this->errors[] = 'EHLO/HELO Error: ' .$this->_gets();

            return false;
        }
        while(1) {
            $lastmessage = $this->_gets();
            if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
                break;
            }
        }

        if (!$this->_authorization())
        {
            $this->errors[] = 'Authorization failed: ' .$this->_gets();

            return false;
        }

        if (!$this->_mail_from() || !$this->_repeipt($mailto) || !$this->_data())
        {
            return false;
        }

        $this->_header($mailto, $subject, $content, $charset, $is_html, $receipt);
        $this->_mail_body($content);

        return $this->_quit();
    }
    function _make_content_type($charset, $is_html)
    {
        $content_type = ($is_html == 0) ?
            'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $content_type .= '; format=flowed';

        return $content_type;
    }
    function _make_from($charset)
    {
        $from = "From: \"=?$charset?B?$this->from?=\" <$this->email>";

        return $from;
    }
    function _make_receipt($charset)
    {
        $receipt = "Disposition-Notification-To: =?$charset?B?$this->from?=\" <$this->email>";

        return $receipt;
    }
    function _make_subject($charset, $subject)
    {
        $str = "=?$charset?B?" . base64_encode(str_replace("\r", '', str_replace("\n", '', $subject))) . '?=';

        return $str;
    }
    function _mail_from()
    {
        $str = "MAIL FROM: <$this->email>";

        $this->_puts($str);

        if ($this->_result(3) === '250')
        {
            return true;
        }
        else
        {
            $this->errors[] = $this->_gets();
            return false;
        }
    }
    function _mail_to($to)
    {
        $arr = explode(',', $to);
        $str = 'To: ';
        foreach ($arr AS $val)
        {
            $str .= "<$val>,";
        }

        return substr($str, 0, -1);
    }
    /**
     * smtp连接远程服务器
     *
     * @author  wj
     *
     * @return boolen
     */
    function _connect()
    {
        if (!is_resource($this->connection))
        {
            $this->connection = _at('fsockopen', $this->host, $this->port, $errno, $errstr, $this->timeout);

            if (!$this->connection)
            {
                return false;
            }
            else
            {
                stream_set_blocking($this->connection, true);

                $res = $this->_result(3);

                return ($res == '220');
            }
        }
        else
        {
            return true;
        }
    }
    function _puts($data)
    {
        if (is_resource($this->connection))
        {
            if ($this->debug)
            {
                echo "PUT: $data\r\n";
            }

            return fputs($this->connection, $data . $this->_crlf);
        }
        else
        {
            return false;
        }
    }
    function _gets()
    {
        $res = null;

        if (is_resource($this->connection))
        {
            $res = fgets($this->connection, 512);
            /*
            $line = '';
            while (strpos($res, $this->_crlf) === false || $line{3} !== ' ')
            {
                $line   = fgets($this->connection, 512);
                $res    .= $line;
            }
            */
        }
        if ($this->debug)
        {
            echo "GET: $res\r\n";
        }

        return trim($res);
    }
    function _result($len)
    {
        $res = $this->_gets();

        return ($res !== null) ? substr($res, 0, $len) : '';
    }
    function _helo()
    {
        $cmd = ($this->_auth) ? 'EHLO' : 'HELO';
        $this->_puts($cmd . ' ecmall');

        $res = $this->_result(3);

        return ($res == 220 || $res == 250);
    }
    function _authorization()
    {
        if ($this->_puts('AUTH LOGIN') && $this->_result(3) == '334' &&
            $this->_puts(base64_encode($this->user)) && $this->_result(3) == '334' &&
            $this->_puts(base64_encode($this->pass)) && $this->_result(3) == '235')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function _repeipt($to)
    {
        $arr = explode(',', $to);
        foreach ($arr AS $val)
        {
            $this->_puts('RCPT TO: <' . $val . '>');

            if ($this->_result(2) !== '25')
            {
                $this->errors[] = substr($this->_gets(), 3);

                return false;
            }
        }
        return true;
    }
    function _data()
    {
        if ($this->_puts('DATA') && $this->_result(3) == '354')
        {
            return true;
        }
        else
        {
            $this->errors[] = substr($this->_gets(), 3);

            return false;
        }
    }
    function _header($mailto, $subject, $content, $charset, $is_html, $receipt)
    {
        $headers    = array();
        $headers[]  = sprintf("Message-ID: <%s@%s>", $this->_mail_id, $_SERVER['HTTP_HOST']);
        $headers[]  = $this->_make_from($charset);
        $headers[]  = $this->_mail_to($mailto);
        $headers[]  = 'Subject: ' .$this->_make_subject($charset, $subject);
        $headers[]  = $this->_make_content_type($charset, $is_html);
        $headers[]  = 'Date: ' . date('r');
        $headers[]  = 'Content-Transfer-Encoding: base64';
        $headers[]  = 'Content-Disposition: inline';
        $headers[]  = 'MIME-Version: 1.0';
        $headers[]  = "Sender: $this->email";
        $headers[]  = "X-Priority: $this->priority";
        $headers[]  = "X-Originating-IP: [$_SERVER[REMOTE_ADDR]]";
        $headers[]  = 'X-Mailer: ecmall mailer';
        $headers[]  = '';

        $this->_puts(implode($this->_crlf, $headers));
    }
    function _mail_body($body)
    {
        $body = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $body)))))));
        $this->_puts($body);
        $this->_puts('.');

    }

    /**
     * smtp 发送邮件后退出
     *
     * @author  wj
     * @return boolen
     */
    function _quit()
    {
        $this->_puts('QUIT');

        if ($this->_result(3) == '250')
        {
            $ret_val = true;
        }
        else
        {
            $this->errors[] = $this->_get();
            $ret_val = false;
        }
        _at('fclose', $this->connection);

        return $ret_val;
    }
};

?>