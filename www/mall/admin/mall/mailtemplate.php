<?php

/**
 * ECMALL: �ʼ�ģ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mailtemplate.php 6009 2008-10-31 01:55:52Z Garbin $
 */


include_once(ROOT_PATH . '/includes/manager/mng.mailtemplate.php');

class MailtemplateController extends ControllerBackend
{

    /**
     *  ���캯��
     *
     *  @access public
     *  @param  string $act
     *  @return void
     */

    function __construct($act)
    {
        $this->MailtemplateController($act);
    }

    function MailtemplateController($act)
    {
        if (empty($act))
        {
            $act = 'setting';
        }
        $this->_manager = new MailTemplateManager();
        parent::__construct($act);
    }

    /**
     *  ����
     *
     *  @access public
     *  @param  none
     *  @return void
     */
    function setting()
    {
         if ($_SERVER['REQUEST_METHOD'] == 'GET')
         {
             $this->logger = false;
             $m_t_code    =   trim($_GET['m_t_code']);
             $m_t_list    =   $this->_list_mail_template();
             !$m_t_code && $m_t_code = key($m_t_list);
             $m_info      =   $this->_get_mail_template($m_t_code);
             $this->assign('selected',           $m_t_code);
             $this->assign('mail_template_list', $m_t_list);
             $this->assign('mail',               $m_info);
             $this->build_editor('mail[content]',  $m_info['content'], '80%', '300', 'rich_editor', 'false', array(), 0);
             $this->display('mail_template_setting.html', 'mall');
         }
         elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
         {
             /* ��ʼ������ */
             $mail      = $_POST['mail'];
             $t_code    = $mail['template_code'];
             $mail['content'] =  str_replace(array('%7B', '%7D'),
                                             array('{', '}'),
                                             $mail['content']);
             $t_info    = $mail;
             $this->log_item = $t_code;
             if ($this->_manager->update($t_code, $t_info))
             {
                 $this->show_message('edit_mail_template_sucessfully', $this->lang('go_back'), 'admin.php?app=mailtemplate&amp;m_t_code=' . $t_code);

                 return;
             }
             else
             {
                $this->show_warning('edit_mail_template_faild');

                return;
             }
         }
    }

    /**
     *  ȡ�������ʼ�ģ���б�
     *
     *  @access public
     *  @param  none
     *  @return array
     */
    function _list_mail_template()
    {
        $mts        = $this->_manager->get_list();
        $rtn        = array();
        if ($mts)
        {
            foreach ($mts as $_mt)
            {
                $rtn[$_mt['template_code']] = $this->lang($_mt['template_code']);
            }
        }

        return $rtn;
    }

    /**
     *  ȡ���ʼ�ģ��
     *
     *  @access public
     *  @param  int $t_code �ʼ�ģ���ʶ��
     *  @return array
     */
    function _get_mail_template($t_code)
    {
         return $this->_manager->get_template($t_code);
    }
}
?>
