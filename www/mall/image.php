<?php

/**
 * ECMALL: ����ͼ��̬������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: image.php 6009 2008-10-31 01:55:52Z Garbin $
 */

/**
 * ͼƬ����ͼ��������
 * image.php?file=image_path&width=xxx&height=xxx

 * image_path Ϊ��Ը�Ŀ¼�� ԭͼƬ·��
 * width Ϊ����ͼ��ȣ�����
 * height Ϊ����ͼ�߶ȣ�ѡ�� ����ָ��heightʱ����ͼ�ȱ������ŵ����Ϳ�������width
 */

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
    (time() - strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < 3600))
{
    header('HTTP/1.1 304 Not Modified');
    exit;
}

header("Last-Modified:" . date('r'));

if ((!$width = intval($_GET['width'])) || empty($_GET['hash_path']) || !preg_match("/^[0-9a-f]+$/", $_GET['hash_path']))
{
    trigger_error('Hacking attempt(hash_illegal_or_no_width)', E_USER_ERROR);
}

define('ROOT_PATH',     dirname(__FILE__)); //ȡ��ecmall���ڵĸ�Ŀ¼

$file_id    = !empty($_GET['file_id']) ? intval($_GET['file_id']) : 0;
$height     = isset($_GET['height']) ? intval($_GET['height']) : 0;
$hash_path  = trim($_GET['hash_path']);

if ($file_id)
{
    $thumb_url = 'temp/thumb/' . $hash_path{0} . $hash_path{1} . '/' . $hash_path{2} . $hash_path{3} . '/' . $hash_path . $file_id . '.jpg';
    $thumb_path = ROOT_PATH . '/' . $thumb_url;
}
else
{
    $thumb_url = 'data/common/default_image_' . $width . '_' . $height . '.jpg';
    $thumb_path = ROOT_PATH . '/' . $thumb_url;
}

if (!file_exists($thumb_path))
{
    define('IN_ECM', true);
    define('APPLICATION', 'image');

    require_once(ROOT_PATH . '/includes/models/mod.base.php');
    require_once(ROOT_PATH . '/includes/ctl.frontend.php');
    require_once(ROOT_PATH . '/includes/manager/mng.base.php');
    require_once(ROOT_PATH . '/includes/inc.init.php');
    require_once(ROOT_PATH . '/includes/manager/mng.file.php');
    require_once(ROOT_PATH . '/includes/lib.image.php');

    class ImageController extends ControllerFrontend
    {
        var $_file_id       = null;
        var $_width         = null;
        var $_height        = null;
        var $_thumb_path    = null;
        var $_allowed_actions = array('view');

        function __construct()
        {
            $this->ImageController();
        }

        function ImageController()
        {
            $this->_file_id     = $GLOBALS['file_id'];
            $this->_width       = $GLOBALS['width'];
            $this->_height      = $GLOBALS['height'];
            $this->_thumb_path  = $GLOBALS['thumb_path'];

            //parent::ControllerBase('view');
        }

        function view()
        {
            if ($this->_file_id)
            {
                $mng_file = new FileManager();
                $res = $mng_file->get_list_by_ids($this->_file_id);
                $file_name = ROOT_PATH . '/' . $res[0]['file_name'];
                if (!is_file($file_name))
                {
                    $real_hash = md5(ECM_KEY . '0' . $this->_width . $this->_height);

                    $url = 'image.php?file_id=0&hash_path=' . $real_hash . '&width=' . $this->_width . '&height=' . $this->_height;
                    $this->redirect("$url");
                }
            }
            else
            {
                $file_name = ROOT_PATH . '/data/common/default_image.jpg';
            }

            make_thumb($file_name, $this->_thumb_path, $this->_width, $this->_height, $this->conf('mall_thumb_quality'));
            return true;
        }
    }

    $real_hash = md5(ECM_KEY . $file_id . $width . $height);
    if ($hash_path != $real_hash)
    {
        trigger_error('Hacking attempt(hash_illegal)', E_USER_ERROR);
    }

    $controller = new ImageController();
    if (!$controller->view())
    {
        $controller->destory();
        exit;
    }
    else
    {
        $controller->destory();
    }
}

header("Location: $thumb_url");
exit;

?>