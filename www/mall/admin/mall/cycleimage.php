<?php

/**
 * ECMall: ���λ���������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Id: cycleimage.php 6076 2008-11-18 10:31:58Z Garbin $
*/

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

include(ROOT_PATH. '/includes/cls.xml.php');
include(ROOT_PATH. '/includes/cls.uploader.php');
include_once(ROOT_PATH . '/includes/cls.validator.php');

class CycleImageController extends ControllerBackend
{
    function __construct($act)
    {
        $this->CycleImageController($act);
    }

    function CycleImageController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        parent::ControllerBackend($act);
    }

    /**
     * �鿴�ֲ�ͼƬ�б�
     *
     * @author  scottye
     * @return  void
     */
    function view()
    {
        $this->logger = false;
        $doc = $this->_get_document();
        $data = array();

        for ($i = 0; $i < count($doc->children); $i++)
        {
            $tmp = $doc->children[$i]->attributes;
            $data[] = $tmp;
        }

        $this->assign('data', $data);
        $this->assign('cycleimage_stats',  $this->str_format('cycleimage_stats', count($data)));
        $this->display('cycle_image.view.html', 'mall');
    }


    /**
     * ��ȡXML�ĵ�
     *
     * @author  scottye
     * @return  void
     */
    function _get_document()
    {
        /* �����ݿ��ȡ */
        $xml = new XmlLib_xmlParser();
        $xml_pre = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $str = $this->conf('mall_cycle_image');
        if (empty($str))
        {
            $str = "<bcaster></bcaster>";
        }
        $xml->loadFromString($xml_pre . $str);

        $doc = $xml->getDocument();

        return $doc;
    }

    /**
     * ����ID
     *
     * @author  scottye
     * @return  void
     */
    function _create_id()
    {
        $doc = $this->_get_document();
        $id = 0;
        for ($i = 0; $i < count($doc->children); $i++)
        {
            if (intval($doc->children[$i]->attributes['id']) > $id)
            {
                $id = intval($doc->children[$i]->attributes['id']);
            }
        }

        return ($id + 1);
    }

    /**
     * ����ֲ�ͼƬ
     *
     * @author  liupeng
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $this->display('cycle_image.detail.html', 'mall');
        }
        else
        {
            $node = new Xmllib_Node('item');
            $node->attributes['id'] = $this->_create_id();

            /* ������ӵ�ַ */
            if (!Validator::is_url($_POST['link']))
            {
                $this->show_warning('link_invalid');
                return;
            }
            else
            {
                $node->attributes['link']       = $_POST['link'];
            }

            /* ����ͼƬ�ĵ�ַ */
            $path = '';
            if ($_POST['file_radio'] == '1')
            {
                if (($path = $this->upload_image($_FILES['file'])) === false)
                {
                    $this->show_warning($this->err);
                    $this->err = '';
                    return;
                }
            }
            else
            {
                if (($path = $this->image_path($_POST['file'])) === false)
                {
                    $this->show_warning('not_local_url');
                    return;
                }
            }

            $node->attributes['item_url'] = $path;

            $doc = $this->_get_document();
            $doc->appendChild($node);

            $this->_save($doc->toString());

            $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
            $this->show_message('add_succeed', 'back_list', $location);
            return;
        }
    }

    /**
     * �༭�ֲ�ͼƬ
     *
     * @author  liupeng
     * @return  void
     */
    function edit()
    {
        if (empty($_REQUEST["id"]))
        {
            $this->redirect("admin.php?app=cycleimage");
        }
        $id = intval($_REQUEST["id"]);
        $this->log_item = $id;
        $doc = $this->_get_document();
        $node = @$doc->getElementById($id);

        if ($node->attributes['id'] == NULL)
        {
            $this->show_warning('image_not_exist');
            return;
        }

        $node_info = array_merge($node->attributes, $node->toArray());

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;

            $this->assign('info', $node_info);

            $this->display('cycle_image.detail.html', 'mall');
        }
        else
        {
            /* ����ͼƬ */
            if ($_POST['file_radio'] == '1')
            {
                if (($path = $this->upload_image($_FILES['file'])) === false)
                {
                    $this->show_warning($this->err);
                    $this->err = '';
                    return;
                }
                else
                {
                    if (strpos($node_info['image'], 'data/images') !== false)
                    {
                        _at('unlink', ROOT_PATH . '/' . $node_info['image']);
                    }
                }
            }
            else
            {
                if (($path = $this->image_path($_POST['file'])) === false)
                {
                    $this->show_warning('not_local_url');

                    return;
                }
            }
            $node->attributes['item_url'] = $path;

            /* �������ӵ�ַ */
            if (!Validator::is_url($_POST['link']))
            {
                $this->show_warning('link_invalid');
                return;
            }
            else
            {
                $node->attributes['link'] = $_POST['link'];
            }

            $this->_save($doc->toString());

            $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
            $this->show_message('edit_succeed', 'back_list', $location);

            return;
        }
    }

    /**
     * ɾ���ֲ�ͼƬ����
     *
     * @author  scottye
     * @return  void
     */
    function drop()
    {
        if (empty($_GET['id']))
        {
            $this->redirect("admin.php?app=cycleimage");
            return;
        }

        $id = $_GET['id'];
        $this->log_item = $id;

        $doc = $this->_get_document();

        $node =& $doc->getElementById($id);

        if ($node == NULL)
        {
            $this->show_warning('image_not_exist');
            return;
        }

        $node_info = array_merge($node->attributes, $node->toArray());

        $doc->removeById($id);

        $this->_save($doc->toString());

        if (strpos($node_info['item_url'], 'data/images') !== false &&
            is_file(ROOT_PATH . '/' . $node_info['item_url']))
        {
             unlink(ROOT_PATH . '/' . $node_info['item_url']);
        }

        $location = preg_replace('/act=\w+/i', 'act=view', $_SERVER['REQUEST_URI']);
        $this->show_message('delete_succeed', 'back_list', $location);
        return;
    }

    /**
     * ����
     *
     * @author  scottye
     * @param   string  $content
     * @return  void
     */
    function _save($content)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.conf.php');
        $mng_conf = new ConfManager(0);
        $mng_conf->set_conf('mall_cycle_image', $content);
    }

    /**
     * ����ͼƬ��url��ַ��������xml�ڵ�
     *
     * @author  weberliu
     * @param   string  $file   �ļ�·��
     * @return  string|false
     */
    function image_path($file)
    {
        $site_url = site_url();

        if ((strpos($file, 'http://') !== false || strpos($file, 'https://') !== false) &&
            strpos($file, $site_url) === false)
        {
            return false;
        }
        else
        {
            return str_replace($site_url.'/', '', $file);
        }
    }

    /**
     * �����ϴ���ͼƬ��������·��
     *
     * @author  weberliu
     * @param   array   $uploaded   �ϴ����ļ���Ϣ������
     * @return  string|false
     */
    function upload_image($uploaded)
    {
        $retval = false;
        $uploader = new Uploader('data/images/', 'image', $this->conf('mall_max_file'));

        if (!empty($uploaded['name']))
        {
            if ($uploader->upload_files($uploaded))
            {
                $files = $uploader->success_list;
                $retval = $files[0]['target'];
            }
            else
            {
                $this->err = $uploader->err;
            }
        }
        else
        {
            $this->err = 'not_file';
        }

        return $retval;
    }

};
?>
