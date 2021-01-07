<?php

/**
 * ECMALL: 标签设置管理程序
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 设置标签的选项
     *
     * @return  void
     */
    function setting()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET")
        {
            $this->logger = false; //不记录日志
            $this->_setting_form();
        }
        else
        {
            $this->_save_settings($_POST);
        }
    }

    /**
     * 设置标签选项的表单
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
            /* 重新组合 */
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
     * 保存设置
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
