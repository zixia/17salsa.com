<?php

/**
 * ECMALL: 邮件模板管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.mailtemplate.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class MailTemplateManager extends Manager
{
    var $_store_id = 0;

    function _construct($store_id=0)
    {
        $this->MailTemplateManager($store_id);
    }

    function MailTemplateManager($store_id=0)
    {
        parent::Manager();
        $this->_store_id = intval($store_id);
    }

    /**
     * 取得邮件模板
     *
     * @param   string  $code
     * @return  string
     */
    function get_template($code)
    {
        $sql = "SELECT subject, content FROM `ecm_mail_templates` WHERE template_code='$code'";
        $row = $GLOBALS['db']->getRow($sql);

        return $row;
    }

    /**
     *  列表邮件模板
     *
     *  @access public
     *  @param  none
     *  @return array
     */
    function get_list()
    {
        $sql = "SELECT * FROM `ecm_mail_templates`";

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     *  编辑模板
     *
     *  @access public
     *  @param  string $t_code  邮件模板唯一标识
     *  @param  array  $t_info  邮件模板唯一标识
     *  @return bool
     */

    function update($t_code, $t_info)
    {
        $sql = "UPDATE `ecm_mail_templates` SET subject='{$t_info['subject']}',content='{$t_info['content']}' WHERE template_code='{$t_code}'";

        return $GLOBALS['db']->query($sql);
    }
};

?>