<?php

/**
 * ECMALL: 设置
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ctl.conf.php 6030 2008-11-03 10:23:41Z Wj $
 */

include_once(ROOT_PATH . '/includes/manager/mng.conf.php');
include_once(ROOT_PATH . '/includes/lib.editor.php');

class ConfController extends ControllerBackend
{
    var $_group_code = '';
    var $_act = '';
    var $_owner = '';
    var $_store_id = '';
    var $manager = null;

    function __construct($act)
    {
        $this->ConfController($act);
    }

    function ConfController($act)
    {
        if (empty($act))
        {
            $act = 'conf';
        }
        $this->_act = $act;
        $this->_store_id = $_SESSION['store_id'];
        $this->_owner = $this->_store_id ? 'store' : 'mall';
        $this->manager = new ConfManager($this->_store_id);
        parent::__construct($act);
    }

    /**
     * 网站系统设置
     *
     * @author  liupeng
     *
     * @return  void
     */
    function setting()
    {
        if ($_POST['value_submit'])
        {
            $group_items = $this->manager->get_item($this->_store_id ? 'store' : 'mall');
            foreach ($group_items as $group_code => $items)
            {
                foreach ($items as $item)
                {
                    if ($item['type'] == 'hidden' || $item['code'] == 'store_msn')
                    {
                        continue;
                    }
                    if ($item['code'] != 'store_intro')
                    {
                        $_POST[$item['code']] = !is_array($_POST[$item['code']]) ? trim($_POST[$item['code']]) : $_POST[$item['code']];
                    }
                    if (!isset($_POST[$item['code']]) && $item['type'] != 'file')
                    {
                        if ($item['required'])
                        {
                            $this->show_warning('conf_data_is_empty');
                            return;
                        }
                    }
                    else
                    {
                        if ($item['type'] == 'int' || $item['type'] == 'float')
                        {
                            $_POST[$item['code']] = $item['type'] == 'int' ? intval($_POST[$item['code']]) : floatval($_POST[$item['code']]);
                            $tmp = explode(',', $item['params']);
                            if ($tmp[0] != '*' && $_POST[$item['code']] < $tmp[0])
                            {
                                $this->show_warning('conf_data_illegal');
                                return;
                            }
                            if ($tmp[1] != '*' && $_POST[$item['code']] > $tmp[1])
                            {
                                $this->show_warning('conf_data_illegal');
                                return;
                            }
                        }
                        elseif ($item['type'] == 'radio' || $item['type'] == 'select' || $item['type'] == 'checkbox')
                        {
                            $val_list = array();
                            $arr_params = explode(',', $item['params']);
                            foreach ($arr_params as $key=>$val)
                            {
                                if (strpos($val, '=') === false)
                                {
                                    $val_list[] = $val;
                                }
                                else
                                {
                                    $tmp = explode('=', $val);
                                    $val_list[] = $tmp[0];
                                }
                            }
                            if ($item['type'] == 'checkbox')
                            {
                                $tmp = '';
                                if (!is_array($_POST[$item['code']]))
                                {
                                    $_POST[$item['code']] = array();
                                }

                                foreach ($_POST[$item['code']] as $key => $val)
                                {
                                    if (!in_array($key, $val_list))
                                    {
                                        $this->show_warning('conf_data_illegal');
                                        return;
                                    }
                                    $tmp += 1 << $key;
                                }
                                $_POST[$item['code']] = $tmp;
                            }
                            else
                            {
                                if (!in_array($_POST[$item['code']], $val_list))
                                {
                                    $this->show_warning('conf_data_illegal');
                                    return;
                                }
                            }
                        }
                        elseif ($item['type'] == 'file')
                        {
                            if (!empty($_FILES[$item['code']]['tmp_name']))
                            {
                                include_once(ROOT_PATH . '/includes/cls.uploader.php');
                                $target_dir = dirname($item['default_value']);
                                $target_dir = empty($target_dir) ? 'data/common/' : $target_dir;
                                $upload = new Uploader($target_dir, 'image', $this->conf('mall_max_file'));
                                if (is_file($this->conf($item['code'], $_SESSION['store_id'])))
                                {
                                    unlink($this->conf($item['code'], $_SESSION['store_id']));
                                }
                                if ($upload->upload_files($_FILES[$item['code']]))
                                {
                                    $_POST[$item['code']] = $upload->success_list[0]['target'];
                                    if ($item['code'] == 'mall_goods_default_img')
                                    {
                                        $_POST[$item['code']] = 'data/common/default_image.jpg';
                                        if (is_file($_POST[$item['code']]))
                                        {
                                            @unlink($_POST[$item['code']]);
                                        }
                                        @rename($upload->success_list[0]['target'], $_POST[$item['code']]);
                                        if ($handle = opendir('data/common/'))
                                        {
                                            while (false !== ($file = readdir($handle)))
                                            {
                                                if ($file != '.' && $file != '..')
                                                {
                                                    if (preg_match('/^default_image_\d+_\d+.jpg$/', $file))
                                                    {
                                                        @unlink('data/common/' . $file);
                                                    }
                                                }
                                            }
                                            closedir($handle);
                                        }
                                    }
                                    if ($item['code'] == 'mall_logo')
                                    {
                                        $_POST[$item['code']] = 'data/common/mall_logo.gif';
                                        if (is_file($_POST[$item['code']]))
                                        {
                                            @unlink($_POST[$item['code']]);
                                        }
                                        @rename($upload->success_list[0]['target'], $_POST[$item['code']]);
                                    }
                                }
                                else
                                {
                                    $this->show_warning($upload->err);
                                    return;
                                }
                            }
                            else
                            {
                                continue;
                            }

                        }
                        elseif ($item['type'] == 'time_zone')
                        {
                            $_POST[$item['code']] = $_POST[$item['code']] > 12 || $_POST[$item['code']] < -12 ? 8 : $_POST[$item['code']];
                        }
                        elseif ($item['type'] == 'read_dir')
                        {
                            if (!is_dir(ROOT_PATH . '/' . $item['params'] . '/' . $_POST[$item['code']]))
                            {
                                continue;
                            }
                        }
                        elseif ($item['type'] == 'html')
                        {
                            $prefix = intval($this->conf('mall_editor_type')) == 0 ? '' : 'html:';
                            $_POST[$item['code']] = $prefix . $this->get_editor_content($_POST[$item['code']]);
                        }
                        elseif ($item['type'] == 'password')
                        {
                            if ($_POST[$item['code']] == '********' || $this->conf($item['code']) == $_POST[$item['code']])
                            {
                                continue;
                            }
                        }
                        else
                        {
                            if (empty($item['required']) && empty($_POST[$item['code']]))
                            {
                                $_POST[$item['code']] = '';
                            }
                            else
                            {
                                if ($item['params'] == 'nohtml')
                                {
                                    $_POST[$item['code']] = strip_tags($_POST[$item['code']]);
                                }
                                elseif (is_numeric($item['params']))
                                {
                                    $_POST[$item['code']] = substr($_POST[$item['code']], 0, $item['params']);
                                }
                                elseif (in_array($item['type'], array('email', 'telnum', 'id_card', 'mobile', 'url')))
                                {
                                    $fun_name = 'is_' . $item['type'];
                                    if (!$fun_name($_POST[$item['code']]))
                                    {
                                        $this->show_warning('conf_data_illegal');
                                        return;
                                    }
                                }
                            }
                        }
                    }
                    $this->manager->set_conf($item['code'], $_POST[$item['code']]);
                }
            }

            if ($this->_store_id)
            {
                require_once(ROOT_PATH. '/includes/manager/mng.file.php');
                $fm = new FileManager($_SESSION['store_id']);

                if (count($_POST['file_id']) > 0)
                {
                    $fm->update_item_id($_POST['file_id'], 'store_intro', $this->_store_id);
                }
            }


            $this->show_message('conf_save_success', 'back_' . $this->_owner . '_conf', 'admin.php?app=' . APPLICATION . '&act=' . $this->_act);
            $this->clean_cache();
            return true;
        }
        elseif ($_POST['test_submit'])
        {
            $this->send_test_email();
        }
        else
        {
            $this->logger = false;
            $values = $this->manager->get_conf();
            $group_items = $this->manager->get_item($this->_owner);

            foreach ($group_items as $group_code => $items)
            {
                $new_items = array();
                foreach ($items as $key => $res)
                {
                    if ($res['type'] == 'hidden')
                    {
                        unset($items[$key]);
                        continue;
                    }
                    if ($res['code'] == 'store_intro')
                    {
                        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
                        $fm = new FileManager($_SESSION['store_id']);
                        $attachments = $fm->get_list_by_item($this->_store_id, 'store_intro');
                        $editor_type = 0;
                        if (strncmp($values['store_intro'], 'html:', 5) == 0)
                        {
                            $values['store_intro'] = substr($values['store_intro'], 5);
                            $editor_type = 1;
                        }
                        $this->build_editor('store_intro', $values['store_intro'], '96%', '300', 'store_intro', 'true', $attachments, $editor_type);
                    }
                    if ($res['type'] == 'read_dir')
                    {
                        $dir = ROOT_PATH . '/' . $res['params'];
                        if ($handle == opendir($dir))
                        {
                            $tmp = array();
                            while (false !== ($file = readdir($handle)))
                            {
                                if (!preg_match("/^\./", $file) && is_dir($dir . '/' . $file))
                                {
                                    $tmp[] = $file;
                                }
                            }
                            $res['type'] = count($tmp) > 3 ? 'select' : 'radio';
                            $res['params'] = implode(',', $tmp);
                            unset($tmp);
                        }
                    }
                    if ($res['type'] == 'radio' || $res['type'] == 'select' || $res['type'] == 'checkbox')
                    {
                        if ($res['type'] == 'select')
                        {
                            $res['input_html'] = '<select name="' . $res['code'] . '">';
                        }

                        $arr_params = explode(',', $res['params']);
                        foreach ($arr_params as $key=>$val)
                        {

                            if (strpos($val, '=') === false) {
                                $value = $val;
                                $lang_key = $val;
                            }
                            else
                            {
                                $tmp = explode('=', $val);
                                $value = $tmp[0];
                                $lang_key = $tmp[1];
                            }
                            if ($res['type'] == 'select')
                            {
                                $selected = '';
                                if ($values[$res['code']] == $value)
                                {
                                    $selected = 'selected="selected"';
                                }
                                $res['input_html'] .= '<option value="' . $value .'" ' .$selected. '>' . $this->lang($lang_key) . '</option>';
                            }
                            else
                            {
                                $checked = '';
                                if ($res['type'] == 'radio')
                                {
                                    if ($values[$res['code']] == $value)
                                    {
                                        $checked = 'checked="checked"';
                                    }


                                    $res['input_html'] .= '<label><input type="radio" name="' . $res['code'] . '" value="' . $value .'" ' .$checked. ' /> ' . $this->lang($lang_key) . '</label>&nbsp;';

                                }
                                else
                                {
                                    if ($values[$res['code']] & 1 << $value)
                                    {
                                        $checked = 'checked="checked"';
                                    }
                                    $res['input_html'] .= '<input type="checkbox" name="' . $res['code'] . '[' . $value . ']" value="1" ' .$checked. ' /> ' . $this->lang($lang_key) . '&nbsp;';
                                }
                            }
                        }
                        if ($res['type'] == 'select')
                        {
                            $res['input_html'] .= '</select>';
                        }
                    }
                    elseif ($res['type'] == 'time_zone')
                    {
                        $res['params'] = array(
                                    '-12'=>'(GMT -12:00) Eniwetok, Kwajalein',
                                    '-11'=>'(GMT -11:00) Midway Island, Samoa',
                                    '-10'=>'(GMT -10:00) Hawaii',
                                    '-9'=>'(GMT -09:00) Alaska',
                                    '-8'=>'(GMT -08:00) Pacific Time (US &amp; Canada), Tijuana',
                                    '-7'=>'(GMT -07:00) Mountain Time (US &amp; Canada), Arizona',
                                    '-6'=>'(GMT -06:00) Central Time (US &amp; Canada), Mexico City',
                                    '-5'=>'(GMT -05:00) Eastern Time (US &amp; Canada), Bogota, Lima, Quito',
                                    '-4'=>'(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz',
                                    '-3.5'=>'(GMT -03:30) Newfoundland',
                                    '-3'=>'(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is',
                                    '-2'=>'(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena',
                                    '-1'=>'(GMT -01:00) Azores, Cape Verde Islands',
                                    '0'=>'(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia',
                                    '1'=>'(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome',
                                    '2'=>'(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa',
                                    '3'=>'(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi',
                                    '3.5'=>'(GMT +03:30) Tehran',
                                    '4'=>'(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi',
                                    '4.5'=>'(GMT +04:30) Kabul',
                                    '5'=>'(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
                                    '5.5'=>'(GMT +05:30) Bombay, Calcutta, Madras, New Delhi',
                                    '5.75'=>'(GMT +05:45) Katmandu',
                                    '6'=>'(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk',
                                    '6.5'=>'(GMT +06:30) Rangoon',
                                    '7'=>'(GMT +07:00) Bangkok, Hanoi, Jakarta',
                                    '8'=>'(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei',
                                    '9'=>'(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
                                    '9.5'=>'(GMT +09:30) Adelaide, Darwin',
                                    '10'=>'(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok',
                                    '11'=>'(GMT +11:00) Magadan, New Caledonia, Solomon Islands',
                                    '12'=>'(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island',
                        );
                    }
                    else
                    {
                        $required = $res['required'] ? ' required="true" ' : '';
                        if ($res['type'] == 'text')
                        {
                            $res['input_html'] = '<textarea name="' . $res['code'] . '" rows="5" cols="50"' . $required . 'dataType="string" test="' . $res['params'] . '">' . $values[$res['code']] . '</textarea>';
                        }
                        elseif ($res['type'] == 'file')
                        {
                            $res['input_html'] = '';
                        }
                        elseif ($res['type'] == 'password')
                        {
                            $res['input_html'] = '<input type="password" name="' . $res['code'] . '" size="40"' . $required . ' value="********" dataType="' . $res['type'] . '" test="' . $res['params'] . '" />';
                        }
                        else
                        {
                            $res['input_html'] = '<input type="text" name="' . $res['code'] . '" size="40"' . $required . ' value="' . $values[$res['code']] . '" dataType="' . $res['type'] . '" test="' . $res['params'] . '" />';
                            if ($res['params'] == 'test')
                            {
                                $res['input_html'] .= '<input type="button" name="test_submit" value="' . $this->lang('test') . '" onclick="test_ajax()" />';
                            }
                        }
                    }
                    $res['value'] = $values[$res['code']];
                    $res['note'] = $res['code'] . '_note';
                    $res['haha'] = 1;
                    $new_items[] = $res;
                }

                $group_items[$group_code] = $new_items;
                unset($new_items);
            }
            $msn_active_url = 'http://settings.messenger.live.com/applications/websignup.aspx'.
                              '?returnurl=' . site_url() . '/admin.php' . urlencode('?app=conf&act=update_store_msn&store_id=' . $_SESSION['store_id']) .
                               '&amp;privacyurl=' . site_url() . '/index.php' . urlencode('?app=article&act=builtin&code=MSN_PRIVACY');

            $this->assign('msn_active_url', $msn_active_url);
            $this->assign('page_title', 'page_title_conf_' . $this->_owner);

            if ($this->_store_id > 0)
            {
                /* 显示出店铺名称 */
                include_once(ROOT_PATH . '/includes/models/mod.store.php');
                $mod_store = new Store($_SESSION['store_id']);
                $store_info = $mod_store->get_info();
                $arr_store_name = array('type' => 'input', 'code' => 'store_name', 'input_html' => $store_info['store_name']);
                $group_items['store_conf'] = array_merge(array($arr_store_name), $group_items['store_conf']);
            }
            $this->assign('group_items', $group_items);

            $this->display('conf.html');
        }
    }

    /**
     * 发送测试邮件
     *
     * @author  weberliu
     */
    function send_test_email()
    {
        include_once ROOT_PATH . '/includes/cls.mail_queue.php';
        $mail_protocol = ($_POST['mall_email_type'] != 'smtp') ? MAIL_PROTOCOL_LOCAL : MAIL_PROTOCOL_SMTP;
        if ($_POST['mall_email_pass'] == '********')
        {
            $_POST['mall_email_pass'] = $this->conf('mall_email_pass');
        }
        $mailer = new MailQueue($this->conf('mall_name'), $_POST['mall_email_addr'], $mail_protocol,
            $_POST['mall_email_host'], $_POST['mall_email_port'], $_POST['mall_email_id'],
            $_POST['mall_email_pass']);

        if ($mailer->test_send($_POST['mall_test_email'], $this->lang('test_mail_subject'), $this->lang('test_mail_body')))
        {
            $this->show_message('test_mail_has_send');
        }
        else
        {
            $this->json_error('test_mail_has_fail', implode("\n", $mailer->mailer->errors));
        }
    }

    /**
     * 更新store_msn 字段
     *
     * @author wj
     */
    function update_store_msn()
    {
        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';

        if ($store_id != $_SESSION['store_id'])
        {
            $this->show_warning('store_id_not_match');
        }
        elseif (empty($id))
        {
            $this->show_warning('url_error');
        }
        else
        {
            $this->manager->set_conf('store_msn', $id);
            $this->clean_cache();
            $this->show_message('active_msn_success', 'close_windows', 'javascript:window.close();');
        }
    }

    /**
     * 删除store_msn 数据
     *
     * @author wj
     */
    function drop_store_msn()
    {
        $this->manager->set_conf('store_msn', '');
        $this->clean_cache();
        $this->show_message('unactive_msn_success');
    }
}
?>
