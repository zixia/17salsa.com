<?php

/**
 * ECMALL: �ʼ�ģ�������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
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
     * ȡ���ʼ�ģ��
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
     *  �б��ʼ�ģ��
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
     *  �༭ģ��
     *
     *  @access public
     *  @param  string $t_code  �ʼ�ģ��Ψһ��ʶ
     *  @param  array  $t_info  �ʼ�ģ��Ψһ��ʶ
     *  @return bool
     */

    function update($t_code, $t_info)
    {
        $sql = "UPDATE `ecm_mail_templates` SET subject='{$t_info['subject']}',content='{$t_info['content']}' WHERE template_code='{$t_code}'";

        return $GLOBALS['db']->query($sql);
    }
};

?>