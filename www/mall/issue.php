<?php

/**
 * ECMALL: 错误收集处理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: issue.php 6009 2008-10-31 01:55:52Z Garbin $
 */

define('IN_ECM', true);
define('BUG_EMAIL', 'bugs.ecmall@shopex.cn');

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class IssueController extends ControllerFrontend
{
    var $_allowed_actions = array('send');

    /**
     * 构造函数
     * @author  wj
     * @param  string   $act
     *
     * @return  void
     */
    function __construct($act)
    {
        $this->IssueController($act);
    }

    /**
     * 构造函数
     * @author  wj
     * @param  string   $act
     *
     * @return  void
     */
    function IssueController($act)
    {
        if (empty($act))
        {
            $act = 'send';
        }
        parent::__construct($act);
    }

    /**
     *  发送邮件
     *  @author  wj
     *  @return  void
     */
    function send()
    {
        $data = empty($_GET['data']) ? '' : trim($_GET['data']);
        $sign = empty($_GET['sign']) ? '' : trim($_GET['sign']);

        if (empty($data) || empty($sign) || $sign != md5($data . ECM_KEY))
        {
            $this->show_warning('Hacking attempt'); //错误链接
            return;
        }

        /* 收集回传信息 */
        $tmp = explode(chr(8), base64_decode($data));
        $data = array('-'=>'---Bug info----');
        foreach ($tmp as $v)
        {
            $tmp1 = explode(chr(9), $v);
            $data[$tmp1[0]] = $tmp1[1];
        }
        $data['--'] = '---Server info----';
        $data['server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $data['php_version'] = PHP_VERSION;
        $data['db_version'] = $GLOBALS['db']->version();
        $data['---'] = '--Link info ----';
        $data['region'] = $conf['mall_region_id'];
        $data['mall_address'] = $conf['mall_address'];
        $data['mall_post_code'] = $conf['mall_post_code'];
        $data['mall_tel_num'] = $conf['mall_tel_num'];
        $data['mall_email'] = $conf['mall_email'];
        $data['----'] = '--Other info ----';
        $data['site_url'] = site_url();
        $data['site_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $data['http_user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data['http_user_ip'] = real_ip();

        //邮件
        $subject = 'ISSUE:' . 'Bugs for ecmall ' . VERSION;
        $body = '';
        foreach ($data as $k=>$v)
        {
            if ($k{0} == '-'){
                $body .= "<br />\n" . $k . $v . "<br />\n";
            }
            else
            {
                $body .= $k . ' : ' . $v . "<br />\n";
            }
        }

        $this->send_mail(BUG_EMAIL , '', '', $subject, $body);

        $this->show_message('issue_ok', 'back_home', 'index.php');
    }
};

?>
