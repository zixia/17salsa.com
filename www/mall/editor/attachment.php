<?php
define('IN_ECM',    true);
define('IS_BACKEND', true);
define('ROOT_PATH', preg_replace('/[\/|\\\]editor/', '', dirname(__FILE__))); // 取得ecmall所在的根目录
/* 定义PHP_SELF常量 */
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF',  $php_self);
define('ROOT_DIR',  substr(PHP_SELF, 0, strstr(PHP_SELF, 'editor/')));

require(ROOT_PATH. '/includes/manager/mng.base.php');
require(ROOT_PATH. '/includes/models/mod.base.php');
require(ROOT_PATH. '/includes/ctl.backend.php');
require(ROOT_PATH. '/includes/inc.init.php');
require(ROOT_PATH. '/includes/manager/mng.file.php');

restore_error_handler();

$manager = new FileManager($_SESSION['store_id']);
$manager->water_mark = false;
$manager->thum_status = false;

if (isset($_GET['ajax']) && !empty($_GET['aid']))
{
    $res['done']    = $manager->drop_by_ids($_GET['aid']);
    $res['msg']     = (string)$manager->err;

    die(ecm_json_encode($res));
}
else
{
    /* upload attachments */
    $err = null;
    $img = '';
    //ie需要发送编码类型，不然有中文时时乱码。
    if ($_SERVER["HTTP_USER_AGENT"] && (strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE') !== false))
    {
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }
    if ($_SESSION['admin_id'] <= 0)
    {
        $err = 'Hacking';
    }
    else
    {
        Language::load_lang(lang_file('admin/common'));
        if (!empty($_FILES['upload_files']['tmp_name']))
        {
            $img = $manager->add($_FILES['upload_files']);

            if (!$img)
            {
                $err = Language::get($manager->err);
            }
            else
            {
                foreach ($img AS $key=>$val)
                {
                    $img[$key]['file_size'] = (ceil($val['file_size']/1024*100)/100). 'KB';
                }
            }
        }
    }
    $json = array();
    if (isset($_GET['callback']))
    {
        echo "<script>parent." . $_GET['callback'] . "(";
    }

    if ($err !== null)
    {
        $json['done']   = false;
        $json['msg'] = $err;
        echo ecm_json_encode($json);
    } else {
        $json['done']   = true;
        $json['retval'] = $img[0];
        $json['msg'] =  Language::get('upload_succeed');
        echo ecm_json_encode($json);
    }
    
    if (isset($_GET['callback']))
    {
        echo ");</script>";
    }
}
?>