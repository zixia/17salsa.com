<?php

/**
 * ECMALL: ��Ʒ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ctl.category.php 6051 2008-11-12 09:27:51Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class categoryController extends ControllerBackend
{
    function __construct($act)
    {
        $this->categoryController($act);
    }

    function categoryController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        !class_exists('Category') && include(ROOT_PATH . '/includes/models/mod.category.php');
        parent::__construct($act);
    }

    /**
     * �鿴����
     *
     * @return  void
     */
    function view()
    {
        $this->logger=false;
        if (!isset($_COOKIE['ecm_config']['category_fold']))
        {
            ecm_setcookie('ecm_config[category_fold]', 1, time() + 3600 * 24 * 30);
            $_COOKIE['ecm_config']['category_fold'] = 1;
        }
        $fold = empty($_COOKIE['ecm_config']['category_fold']) ? 0 : 1;
        $category = new Category(0, $_SESSION['store_id']);
        $cate_infos = $category->list_child();
        $this->assign('cate_infos', $cate_infos);
        $this->assign('stat_info', $this->str_format('stat_info', count($cate_infos)));
        $this->assign('fold',   $fold);
        $this->display('category.view.html');

    }

    /**
     * ���·�����Ʒ����
     *
     * @author  liupeng
     * @return  void
     */
    function update_goods_count()
    {
        $category = new Category(0, $_SESSION['store_id']);
        $category->update_goods_count();

        $this->json_result('', 'ok');
    }

    /**
     * ��ӷ���
     *
     * @return  void
     */
    function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            $category = new Category(0, $_SESSION['store_id']);
            include_once(ROOT_PATH . '/includes/manager/mng.goodstype.php');
            $type_manager = new GoodsTypeManager($_SESSION['store_id']);
            $cate = array('sort_order'=>0, 'cate_id'=>0, 'parent_id'=>0);
            $this->assign('cate',           $cate);
            $this->assign('cate_options',   $category->get_options());
            $this->assign('type_options',    $type_manager->get_options());
            $this->display('category.detail.html');
        }
        else
        {
            $cate_name = trim($_POST['cate_name']);
            $category = new Category(0, $_SESSION['store_id']);
            //�ж��Ƿ�ͬ����������
            if ($category->cate_name_exist($cate_name, $_POST['parent_id']))
            {
                $this->show_warning('cate_name_exist');

                return;
            }

            $cate_id = $category->add($_POST);

            $this->log_item = $cate_id;
            $this->show_message('add_ok', 'add_new', 'admin.php?app=category&amp;act=add', 'return_view', 'admin.php?app=category&amp;act=view');
            return;
        }
    }

    /**
     * ��������
     *
     * @author  wj
     *
     * @return void
     */
    function edit ()
    {
        $cate_id = empty($_REQUEST['cate_id']) ? 0 : intval($_REQUEST['cate_id']);
        $category = new Category($cate_id, $_SESSION['store_id']);
        $info = $category->get_info();
        if (empty($info))
        {
            $this->show_warning('no_such_category');

            return false;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false;
            if ($cate_id <=0 ) trigger_error('Category ID is Invalid!');
            $child_id = $category->list_child_id();
            $shop_category = new Category(0, $_SESSION['store_id']);
            $cate_options = $shop_category->get_options();
            //�޳���ǰ�����ӷ���
            foreach ($child_id as $key)
            {
                unset($cate_options[$key]);
            }

            $this->assign('cate',           $info);
            $this->assign('cate_options',   $cate_options);
            include_once(ROOT_PATH . '/includes/manager/mng.goodstype.php');
            $type_manager = new GoodsTypeManager($_SESSION['store_id']);
            $this->assign('type_options',  $type_manager->get_options());
            $this->display('category.detail.html');
        }
        else
        {
            $parent_id = empty($_POST['parent_id']) ? 0 : intval($_POST['parent_id']);
            $cate_name = empty($_POST['cate_name']) ? '' : trim($_POST['cate_name']);
            if ($cate_id <=0 ) trigger_error('Category ID is Invalid!', E_USER_ERROR);
            $this->log_item = $cate_id;
            if ($info)
            {
                if ($info['parent_id'] == $parent_id)
                {
                    //û�ı丸Ŀ¼ʱ,���ͬ���Ƿ�������
                    if ($category->cate_name_exist($_POST['cate_name']))
                    {
                        $this->show_warning($this->lang('cate_name_exist'));

                        return;
                    }
                }
                else
                {
                    //�ж��Ƿ�ͬ����������
                    if ($category->cate_name_exist($cate_name, $parent_id))
                    {
                        $this->show_warning('cate_name_exist');

                        return;
                    }
                }
            }
            if($category->update($_POST))
            {
                $this->show_message('edit_ok',  'return_view', 'admin.php?app=category&amp;act=view');
                return;
            }
            else
            {
                $this->show_warning($category->err[0]);
                return;
            }
        }
    }

    /**
    *
    *
    * @access  public
    * @param
    *
    * @return void
    */
   function drop ()
   {
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id <=0 ) trigger_error('Category ID is Invalid!');
        $this->log_item = $cate_id;
        $category = new Category($cate_id, $_SESSION['store_id']);
        if ($category->drop())
        {
            $this->redirect('admin.php?app=category&act=view');
        }
        else
        {
            $this->show_warning($category->err[0]);
            return;
        }
   }

   /**
    *
    *
    * @access  public
    * @param
    *
    * @return void
    */
    function ajax_update ()
    {
        $id = intval($_GET['id']);
        $column = trim($_GET['column']);
        $value = trim($_GET['value']);
        if ($id < 0) trigger_error('id is undefined!', E_USER_ERROR);
        $this->log_item = $id;
        $data = array($column=>$value);
        $category = new Category($id, $_SESSION['store_id']);
        if ($column == 'cate_name')
        {
            //�ж��Ƿ�ͬ����������
            if ($category->cate_name_exist($value))
            {
                $this->json_error($this->lang('cate_name_exist'));

                return;
            }
        }
        if($category->update($data))
        {
            $this->json_result($value);
            return;
        }
        else
        {
            $this->json_error("There is no record had been updated!");
            return;
        }
    }
};
?>
