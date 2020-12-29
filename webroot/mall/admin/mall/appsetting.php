<?php

/**
 * ECMALL: ��ǩ���ù������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: appsetting.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class appSettingController extends ControllerBackend
{
    var $_config_file = '';

    function __construct($act)
    {
        $this->appSettingController($act);
    }

    function appSettingController($act)
    {
        if (empty($act))
        {
            $act = 'conf';
        }

        $this->_config_file = ROOT_PATH. '/data/inc.app_setting.php';

        parent::__construct($act);
    }

    /**
     * ���ñ�ǩ��ѡ��
     *
     * @return  void
     */
    function setting()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET")
        {
            $this->logger = false; //����¼��־
            $this->_setting_form();
        }
        else
        {
            $this->_save_settings($_POST);
        }
    }

    /**
     * ���ñ�ǩѡ��ı�
     *
     * @return  void
     */
    function _setting_form()
    {
        if (is_file($this->_config_file))
        {
            include_once($this->_config_file);
        }
        else
        {
            $app_settings = array();
        }

        $apps = uc_call('uc_app_ls');

        if (count($apps) > 1)
        {
            /* ������� */
            foreach ($apps AS $key=>$val)
            {
                if ($val['appid'] == UC_APPID)
                {
                    unset($apps[$key]);
                    continue;
                }

                $apps[$key]['fields']   = $apps[$key]['tagtemplates']['fields'];
                $apps[$key]['num']      = isset($app_settings[$key]['num']) ? $app_settings[$key]['num'] : 5;
                $apps[$key]['tpl']      = isset($app_settings[$key]['tpl']) ? $app_settings[$key]['tpl'] : $apps[$key]['tagtemplates']['template'];

                unset($apps[$key]['tagtemplates']);
            }

            $this->assign('settings',   $apps);
            $this->display('app_setting.html', 'mall');
        }
        else
        {
            $this->show_warning('no_application');
            return;
        }
    }

    /**
     * ��������
     *
     * @param  array    $post
     *
     * @return  void
     */
    function _save_settings($post)
    {
        $str = "<?php\n";

        foreach ($post['item_num'] AS $key=>$val)
        {
            $str .= "\$app_settings[$key]['num'] = $val;\n";
        }

        foreach ($post['item_template'] AS $key=>$val)
        {
            $str .= "\$app_settings[$key]['tpl'] = \"" .str_replace("\n", "\\n", $val). "\";\n";
        }

        $str .= "?>";

        $res = file_put_contents($this->_config_file, $str, LOCK_EX);

        if ($res > 0)
        {
            $this->show_message('update_setting_success', 'go_back', 'admin.php?app=appsetting&act=setting');
            return;
        }
        else
        {
            $this->show_warning('update_setting_failed');
            return;
        }
    }

};
?>
