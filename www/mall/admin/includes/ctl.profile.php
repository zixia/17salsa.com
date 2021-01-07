<?php

/**
 * ECMALL: �������ĸ������Ϲ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctl.profile.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ProfileController extends ControllerBackend
{
    function __construct($act)
    {
        $this->ProfileController($act);
    }

    function ProfileController($act)
    {
        if (empty($act))
        {
            $act = 'login';
        }
        parent::__construct($act);
    }
    /**
     * ����Ա��¼����
     *
     * @return  void
     */
    function login()
    {
        $need_captcha = $this->conf('mall_captcha_status') & 8;
        if (defined('IS_AJAX') && IS_AJAX)
        {
            $this->json_error('missing_session');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            include_once(ROOT_PATH. '/includes/cls.image.php');

            $this->assign('captcha', $need_captcha);
            $this->display('profile.login.html');
        }
        else
        {
            $username = trim($_POST['username']);
            if (empty($username) || empty($_POST['password']))
            {
                $this->show_warning('login_empty');
                return;
            }
            else
            {
                if (! empty($_SESSION['captcha']) && $need_captcha && base64_encode(trim(strtolower($_POST['captcha']))) != $_SESSION['captcha'])
                {
                    $this->show_warning('incorrect_captcha');
                    return;
                }
                else
                {
                    $user = new AdminUser(0, 0);
                    $user->login($username, $_POST['password']);

                    if (empty($user->err))
                    {
                        require_once(ROOT_PATH. '/includes/models/mod.user.php');
                        $user1 = new User(0, 0);
                        $user1->login($username, $_POST['password']);//��½ǰ̨

                        $msg = $this->str_format('login_successfully', $username) . $user->sync_login();

                        $this->show_message($msg, 'enter_control_panel', 'admin.php');
                        return;
                    }
                    else
                    {
                        $this->show_warning($user->err);
                        return;
                    }
                }
            }
        }

        return;
    }
    /**
     * ����Ա�ǳ�
     *
     * @return  void
     */
    function logout()
    {
        UserBase::logout();

        $this->redirect('admin.php');

        return;
        //$this->redirect('admin.php');
    }
    /**
     * ��ù���Ա����ϸ��Ϣ
     *
     * @return  void
     */
    function get_info()
    {
        static $user_info = null;

        if ($user_info === null)
        {
            $user_info = parent::get_info();
        }

        return $user_info;
    }

    /**
     *
     *
     *  @access public
     *  @params none
     *  @return void
     */
    function welcome()
    {
         $this->redirect('admin.php');
    }

    /**
     * ������֤��ͼƬ
     *
     * @return  void
     */
    function captcha()
    {
        $this->logger = false;
        include_once(ROOT_PATH. '/includes/cls.captcha.php');
        $word = UserBase::generate_code();
        $_SESSION['captcha'] = base64_encode($word);

        $code = new Captcha();
        $code->code = $word;
        $code->width = 80;
        $code->height = 18;
        $code->display();
    }

};
?>