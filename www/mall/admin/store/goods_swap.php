<?php

/**
 * ECMALL: 商品导入导出控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: goods_swap.php 6106 2008-11-24 02:43:33Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/lib.editor.php');

class Goods_SwapController extends ControllerBackend
{
    function __construct($act)
    {
        $this->Goods_SwapController($act);
    }

    function Goods_SwapController($act)
    {
        if (empty($act))
        {
            $act = 'import';
        }
        parent::__construct($act);
    }

    /**
     * 商品数据导入
     *
     * @author   wj
     * @param    void
     *
     * @return void
     */
    function import ()
    {
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $mall_cate = new Category(0);
        $store_cate = new Category(0, $_SESSION['store_id']);

        $this->assign('mall_cate_option', $mall_cate->get_options());
        $this->assign('store_cate_option', $store_cate->get_options());

        $this->display('goods.import.detail.html', 'store');
    }

    /**
     * 导入数据预览
     *
     * @author      wj
     *
     * @return void
     */
    function preview ()
    {
        $this->logger = false; //不记录日志

        _at('set_time_limit', 300);  //设置超时时间

        $file_type = empty($_POST['file_type']) ? '' : trim($_POST['file_type']);
        $mall_cate_id = empty($_POST['mall_cate_id']) ? 0 : intval($_POST['mall_cate_id']);
        $store_cate_id = empty($_POST['store_cate_id']) ? 0 : intval($_POST['store_cate_id']);
        if (!in_array($file_type, array('taobao', 'paipai'))) trigger_error('Unkonw file type "' . $file_type . '"', E_USER_ERROR);
        /*检查上传是否成功*/
        $msg = $this->check_upload($_FILES['csv_file']);
        if ($msg !== true)
        {
            $this->show_message($msg);
            return;
        }
        /*检查是否能被读取*/
        $data_file_name = $this->get_data_file($_FILES['csv_file']['tmp_name']);
        if ($data_file_name == false)
        {
            $this->show_message('e_deny_read_file');
            return;
        }

        $type_class = $file_type . 'DT';
        $type_convert = new $type_class(array());
        define('EDITOR_TYPE', $this->conf('mall_editor_type'));
        $data = $type_convert->get_data($data_file_name);
        if (empty($data))
        {
            $this->show_message('e_error_file');
            return;
        }

        /*如果上传文件不在临时目录，则手动删除*/
        if (!is_uploaded_file($data_file_name))
        {
            unlink($data_file_name);
        }

        if (isset($data[0]))
        {
            $header = array();
            foreach ($data[0] as $k=>$v)
            {
                $header[$k] = $this->lang($k);
            }
            $goods_num = count($data);

            /* 检查商品数量是否允许 */
            if (!$this->_check_goods_count($goods_num))
            {
                $this->show_warning('over_goods_count');

                return;
            }

            $this->assign('header', $header);
            $this->assign('data', $data);
        }
        $this->assign('mall_cate_id', $mall_cate_id);
        $this->assign('store_cate_id', $store_cate_id);
        $this->display('goods.import.preview.html', 'store');
    }

    /**
     * 完成导入
     *
     * @author  wj
     * @param   void
     *
     * @return void
     */
    function complete ()
    {
        $mall_cate_id = empty($_POST['mall_cate_id']) ? 0 : intval($_POST['mall_cate_id']);
        $store_cate_id = empty($_POST['store_cate_id']) ? 0 : intval($_POST['store_cate_id']);
        $is_deny = $this->_allowed_sale(); //获取是否允许销售
        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng = new GoodsManager($_SESSION['store_id']);
        $spec = array('stock', 'store_price'); //属性表字段

        $filed = array_keys($_POST['data']);
        $filed_size = count($filed);
        $size = count($_POST['data']['goods_name']);

        /* 检查商品数量是否允许 */
        if (!$this->_check_goods_count($size))
        {
            $this->show_warning('over_goods_count');

            return;
        }

        foreach ($_POST['id'] as $i)
        {
            $i--;
            $goods = array('mall_cate_id'=>$mall_cate_id, 'store_cate_id'=>$store_cate_id, 'is_deny'=>$is_deny);
            for($j=0; $j < $filed_size; $j++)
            {
                if (in_array($filed[$j], $spec))
                {
                    $goods[$filed[$j]][] = $_POST['data'][$filed[$j]][$i];
                }
                else
                {
                    $goods[$filed[$j]] = $_POST['data'][$filed[$j]][$i];
                }
            }
            $goods['editor_type'] = $this->conf('mall_editor_type');
            $goods['sort_weighing'] = 999;
            $mng->add($goods);

        }

        $this->show_message('import_ok', $this->lang('goods_list'), 'admin.php?app=goods&amp;act=view');
        return;
    }

    /**
     * 数据导出
     *
     * @author  weberliu
     * @param   void
     *
     * @return void
     */
    function export ()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->logger = false; //不记录日志
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            $category = new Category(0, $_SESSION['store_id']);

            $this->assign('cate_options', $category->get_options());
            $this->display('goods.export.detail.html', 'store');
        }
        else
        {
            $page_size = 100;
            $export_type = trim($_POST['export_type']);
            if (!in_array($export_type, array('ecmall', 'paipai', 'taobao'))) trigger_error("Unknow export Type[$export_type]");
            $data = $_POST[$export_type];
            $url_param = '';
            foreach ($data as $key=>$val)
            {
                $url_param .= '&amp;' . $key . '=' . $val;
            }
            $cate_id = intval($data['cate_id']);
            $this->log_item = $cate_id;
            $condition = array();
            if ($cate_id > 0) $condition['store_cate_id'] = $cate_id;
            include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
            $mng = new GoodsManager($_SESSION['store_id']);

            $param = $mng->query_params(1, $condition, 'goods_id', $page_size);
            if ($param['info']['page_count'] > 1)
            {
                $range = array();
                $last_page = $param['info']['page_count'] -1;
                for($i=0; $i<$param['info']['page_count']; $i++)
                {
                    if ($i == $last_page)
                    {
                        $range[] = array('title'=>$this->str_format('goods_from_to', ($page_size * $i)+1, $param['info']['rec_count']),
                        'url'=>"admin.php?app=goods_swap&amp;act=download&amp;export_type=$export_type&amp;page=" . ($i+1) . $url_param);
                    }
                    else
                    {
                        $range[] = array('title'=>$this->str_format('goods_from_to', ($page_size * $i)+1, ($page_size * $i)+$page_size),
                        'url'=>"admin.php?app=goods_swap&amp;act=download&amp;export_type=$export_type&amp;page=" . ($i+1) . $url_param);
                    }
                }
                $main_title = $this->lang('main_title');
                $this->assign('url_param',      $url_param);
                $this->assign('main_title',     $main_title[$export_type]);
                $this->assign('export_type',    $export_type);
                $this->assign('desc',           $this->str_format('goods_number', $param['count'], $page_size, $param['info']['page_count']));
                $this->assign('range',          $range);
                $this->display('goods.export.download.html', 'store');
            }
            else
            {
                $url = "admin.php?app=goods_swap&amp;act=download&amp;export_type=$export_type&amp;page=0" . $url_param;
                $this->redirect(str_replace('&amp;', '&', $url));
            }
        }
    }

    /**
    * 文件下载
    *
    * @author   weberliu
    * @param    void
    *
    * @return void
    */
   function download ()
   {
        $this->logger = false; //不记录日志
        $page_size = 100;
        $store_cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        $export_type    = trim($_GET['export_type']);
        $page           = empty($_GET['page']) ? 1 : intval($_GET['page']);

        if (!in_array($export_type, array('ecmall', 'paipai', 'taobao'))) trigger_error("Unknow export Type[$export_type]");

        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');

        $mng            = new GoodsManager($_SESSION['store_id']);
        $data           = $mng->get_list($page, array('store_cate_id'=>$store_cate_id), $page_size, true);
        $export_class   =  $export_type . 'DT';
        $type_convert   = new $export_class($_GET);
        $this->lang(); //加载语言项目
        $file_name = "goods_list_for_" . $export_type;
        $file_name = (isset($_GET['page']) && $_GET['page'] == 0) ? $file_name . '.csv' : $file_name . '_' . $page . '.csv';
        header("Content-Disposition: attachment; filename=" . $file_name);
        header("Content-Type: application/unknown");

        echo $type_convert->headline();
        foreach ($data['data'] as $val)
        {
            echo $type_convert->convert($val);
        }
   }

   /**
    * 检查文件是否上传成功，出错则返回错误信息
    *
    * @author       wj
    * @param        array       $file       上传文件数组
    *
    * @return       string,bool
    */
    function check_upload($file)
    {
        $msg = '';
        if (PHP_VERSION > '4.2')
        {
            switch ($file['error'])
            {
                case  UPLOAD_ERR_OK :
                    $msg = true;
                    break;
                case UPLOAD_ERR_INI_SIZE :
                case UPLOAD_ERR_FORM_SIZE :
                    $msg = 'e_upload_big_file';
                    break;
                default:
                    $msg = 'e_upload_unknown_error';

            }
        }
        else
        {
            if (!empty($file['tmp_name']))
            {
                $msg = true;
            }
            else
            {
                $msg = 'e_upload_unknown_error';
            }
        }

        return $msg;
    }

    /**
     * 判断文件是否能读取到，不能则移动到新目录
     *
     * @author      wj
     * @param       string      $file_name
     *
     * @return      stirng
     */
     function get_data_file($file_name)
     {
        $can_read = false;
        $target_name = false;
        if (is_file($file_name))
        {
            $fp = fopen($file_name, 'r');
            if ($fp)
            {
                $can_read = true;
                $target_name = $file_name;
                fclose($fp);
            }
        }
        if (!$can_read)
        {
            $target_name = ROOT_PATH . '/temp/caches/import_csv_' . $_SESSION['store_id'] . '.csv';
            if (move_uploaded_file($file_name, $target_name) || copy($file_name, $target_name))
            {

            }
            else
            {
                $target_name = false;
            }
        }

        return $target_name;
     }

    /**
     * 获得当前的系统设置，商品是否默认允许销售
     *
     * @author  wj
     * @return  int
     */
    function _allowed_sale()
    {
        $conf = intval($this->conf('mall_auto_allow'));

        if ($conf === 2)
        {
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $mod = new Store($_SESSION['store_id']);
            $info = $mod->get_info();

            return (1 - intval($info['is_certified']));
        }
        else
        {
            return (1-$conf);
        }
    }

    /**
     *    检查商品总量
     *
     *    @author    Garbin
     *    @param     int $count
     *    @return    bool
     */
    function _check_goods_count($count)
    {
        include_once(ROOT_PATH . '/includes/models/mod.store.php');
        $new_store = new Store($_SESSION['store_id']);
        $info = $new_store->get_info();

        if (($info['goods_limit'] > 0) && ($new_store->get_goods_count() + $count > $info['goods_limit']))
        {
            return false;
        }

        return true;
    }
}

class taobaoDT
{
    var $field = array();
    var $swap = array();

    function  __construct($data)
    {
        $this->taobaoDT($data);
    }

    function taobaoDT ($data)
    {
        $this->field =  array('goods_name'=>'""', 'goods_class'=>0, 'shop_class'=>0, 'new_level'=>5, 'province'=>"", 'city'=>"", 'sell_type'=>'"b"', 'store_price'=>0, 'add_price'=>0, 'stock'=>0, 'die_day'=>14, 'load_type'=>1, 'post_express'=>0, 'ems'=>0, 'express'=>0, 'pay_type'=>2, 'allow_alipay'=>1, 'invoice'=>0, 'repair'=>0, 'resend'=>1, 'is_store'=>0, 'window'=>0, 'add_time'=>'"1980-1-1  0:00:00"', 'story'=>'""', 'goods_desc'=>'""', 'goods_img'=>'""', 'goods_attr'=>'""', 'group_buy'=>0, 'group_buy_num'=>0, 'template'=>0, 'discount'=>0, 'modify_time'=>'""', 'upload_status'=>100, 'img_status'=>0);
        $this->swap = array('goods_name'=>1, 'store_price'=>0, 'stock'=>0, 'goods_desc'=>1);
        if (!empty($data['post_fee'])) $this->field['post_express'] = floatval($data['post_fee']);
        if (!empty($data['express_fee'])) $this->field['express'] = floatval($data['express_fee']);
        if (!empty($data['ems_fee'])) $this->field['ems'] = floatval($data['ems_fee']);
    }

    /**
     * 生成导出文件的头部
     *
     * @author      wj
     * @param       void
     * @return      stirng
     */
    function headline()
    {
        $str = "";
        foreach ($this->field as $key=>$val)
        {
            $str .= Language::get('taobao.' . $key) . "\t";
        }
        $str = preg_replace("/\t$/", "\n", $str);
        if (CHARSET != 'utf-8') $str = ecm_iconv(CHARSET, 'UTF8', $str);
        return "\xFF\xFE" . utf2uni($str);
    }

    /**
     * 将ecmall商品数据数组转换为文件格式
     *
     * @author      wj
     * @param       array       $data
     * @return      stirng
     */
    function convert($data)
    {
        $result = $this->field;
        foreach ($this->swap as $key=>$val)
        {
             if ($key == 'goods_desc' && $data[$key] && ($data['editor_type'] == 0))
             {
                 $data[$key] = Editor::parse($data[$key]);
             }

             $result[$key] = $val ? '"' . str_replace('"', '""', $data[$key]) . '"' : $data[$key];

        }

        if (CHARSET == 'utf-8')
        {
            return utf2uni(implode("\t", $result) . "\n");
        }
        else
        {
            return utf2uni(ecm_iconv(CHARSET, 'UTF8', implode("\t", $result) . "\n"));
        }
    }

    /**
     * 从文件中读取ecmall商品数据
     *
     * @author      wj
     * @param       string      $file_name  文件名称
     * @return      array
     */
    function get_data($file_name)
    {
        $str = file_get_contents($file_name);
        if($str{0} != "\xFF" || $str{1} != "\xFE")
        {
            //没有utf-8的头部,说明不是淘宝助理导出的数据
            return array();
        }

        $str = uni2utf(substr($str, 2));
        db_ping();
        if (CHARSET != 'utf-8')
        {
            $str = ecm_iconv('UTF8', CHARSET, $str); //转码
            db_ping();
        }

        $str = preg_replace('/\t\"([^\t]+?)\"\t/es', "'\t\"' . stripslashes(str_replace(\"\n\", \"\", '\\1')) . '\"\t'", $str);
        $arr = explode("\n", $str);
        unset($arr[count($arr) -1]);
        unset($arr[0]);
        $data = array();
        $swap = $this->swap;
        $arr_key = array_keys($this->field);
        foreach ($swap as $key=>$val)
        {
            $swap[$key] = array_search($key, $arr_key);
        }

        foreach ($arr as $val)
        {
            $tmp = explode("\t", $val);
            $row = array();
            foreach ($swap as $k=>$v)
            {
                $row[$k] = $this->swap[$k] ? str_replace('""', '"',trim($tmp[$v], '"')) : floatval($tmp[$v]);
                if ($k = 'goods_desc')
                {
                    if (EDITOR_TYPE)
                    {
                        $row[$k] = stripslashes(Editor::check_html(addslashes($row[$k])));
                    }
                    else
                    {
                        $row[$k] = Editor::html2bbcode($row[$k]);
                    }
                }
                db_ping();
            }

            $data[] = $row;
        }

        return $data;
    }
}

class paipaiDT
{
    var $field = array();
    var $swap = array();

    function  __construct($data)
    {
        $this->paipaiDT($data);
    }

    function paipaiDT($data)
    {
        $this->field = array('id'=> -1,'tree_node_id'=> -1,'old_tree_node_id'=> -1,'goods_name'=> '""','id_in_web'=> '""','auctionType'=> '"b"','category'=> 0,'shopCategoryId'=> '""','pictURL'=> '""','stock'=> 0,'duration'=> 14,'startDate'=> '""','stuffStatus'=> 5,'store_price'=> 0,'increment'=> 0,'prov'=> '""','city'=> '""','shippingOption'=> 1,'ordinaryPostFee'=> 0,'fastPostFee'=> 0,'paymentRequire'=> 0,'paymentOption'=> 5,'haveInvoice'=> 0,'haveGuarantee'=> 0,'secureTradeAgree'=> 1,'autoRepost'=> 1,'shopWindow'=> 0,'failed_reason'=> '""','pic_size'=> 0,'pic_filename'=> '""','pic'=> '""','goods_desc'=> '""','story'=> '""','putStore'=> 0,'pic_width'=> 80,'pic_height'=> 80,'skin'=> 0,'prop'=> '""');
        $this->swap = array('goods_name'=>1, 'store_price'=>0, 'stock'=>0, 'goods_desc'=>1);
        if (!empty($data['post_fee'])) $this->field['ordinaryPostFee'] = floatval($data['post_fee']);
        if (!empty($data['express_fee'])) $this->field['fastPostFee'] = floatval($data['express_fee']);

    }

    /**
     * 生成导出文件的头部
     *
     * @author      wj
     * @param       void
     * @return      stirng
     */
    function headline()
    {
        $str = "";
        foreach ($this->field as $key=>$val)
        {
            $str .= '"' . Language::get('paipai.' . $key) . '",';
        }
        $str = preg_replace("/,$/", "\n", $str);
        if (CHARSET != 'gbk') $str = ecm_iconv(CHARSET, 'GBK', $str);
        return $str;
    }

    /**
     * 将ecmall商品数据数组转换为文件格式
     *
     * @author      wj
     * @param       array       $data
     * @return      stirng
     */
    function convert($data)
    {
        $result = $this->field;
        foreach ($this->swap as $key=>$val)
        {
            if ($key=='goods_desc' && $data[$key] && ($data['editor_type'] == 0))
            {
                $data[$key] = Editor::parse($data[$key]);
            }
            $result[$key] = $val ? '"' . str_replace('"', '""', $data[$key]) . '"' : $data[$key];
        }

        if (CHARSET == 'gbk')
        {
            return implode(",", $result) . "\n";
        }
        else
        {
            return ecm_iconv(CHARSET, 'GBK', implode(",", $result) . "\n");
        }
    }

    /**
     * 从文件中读取ecmall商品数据
     *
     * @author      wj
     * @param       string      $file_name  文件名称
     * @return      array
     */
    function get_data($file_name)
    {
        $str = file_get_contents($file_name);
        if (CHARSET != 'gbk')
        {
            $str = ecm_iconv('GBK', CHARSET, $str); //转码
            db_ping();
        }

        //将分行的\n 替换为%e~c!m@a#l$l%
        $str = preg_replace("/,[^,]*?\r\n-?\d+,/", '%e~c!m@a#l$l%' . '-1,', $str);
        $arr = explode('%e~c!m@a#l$l%', $str);

        unset($arr[count($arr) -1]);
        unset($arr[0]);
        $data = array();
        $swap = $this->swap;
        $arr_key = array_keys($this->field);
        foreach ($swap as $key=>$val)
        {
            $swap[$key] = array_search($key, $arr_key);
        }

        foreach ($arr as $val)
        {

            /* 商品描述中可能有'，'分割可能会有问题，需要修正 */
            $tmp = explode(',', $val);
            $tmp_len = count($tmp);

            if ($tmp_len > 36)
            {
                $tmp_f = array_slice($tmp, 0, 31);
                $tmp_m = array_slice($tmp, 31, $tmp_len - 37 + 1);
                $tmp_b = array_slice($tmp, -5);
                $goods_desc = array(implode(',', $tmp_m));

                $tmp = array_merge($tmp_f, $goods_desc, $tmp_b);
                unset($tmp_f);unset($tmp_m);unset($tmp_b);unset($goods_desc);
            }

            $row = array();
            foreach ($swap as $k=>$v)
            {
                $row[$k] = $this->swap[$k] ? trim($tmp[$v], '"') : floatval($tmp[$v]);
                if ($k = 'goods_desc')
                {
                    if (EDITOR_TYPE)
                    {
                        $row[$k] = stripslashes(Editor::check_html(addslashes($row[$k])));
                    }
                    else
                    {
                        $row[$k] = Editor::html2bbcode($row[$k]);
                    }
                }
                db_ping();
            }
            $data[] = $row;
        }

        return $data;
    }
};

/**
 * ut8转码为unicode
 *
 * @access  public
 * @param
 *
 * @return void
 */
function utf2uni ($str)
{
    return preg_replace('/([\x00-\x7F]|(?:[\xC0-\xDF][\x80-\xBF])|(?:[\xE0-\xEF][\x80-\xBF][\x80-\xBF])|(?:[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF])|(?:[\xF8-\xFB][\x80-\xBF][\x80-\xBF][\x80-\xBF][\x80-\xBF])|(?:[\xFC-\xFD][\x80-\xBF][\x80-\xBF][\x80-\xBF][\x80-\xBF][\x80-\xBF]))/e',
    "en_unicode('\\1')", $str);
}


function en_unicode($macth)
{
    $str = '';
    $macth = stripslashes($macth); //正则会对特殊字符转义
    switch(strlen($macth))
    {
        case 1:
            $str =  pack('v', ord($macth));
            break;
        case 2:
            $str = pack('v', ((ord($macth{0}) & 0x1F) << 6) + (ord($macth{1}) & 0x3f));
            break;
        case 3:
            $str = pack('v', ((ord($macth{0}) & 0x1F) << 12) + ((ord($macth{1}) & 0x3f) << 6) + (ord($macth{2}) & 0x3f));
            break;
        default :
    }
    return $str;
}

/**
 * unicode转为utf8
 *
 * @access  public
 * @param
 *
 * @return void
 */
function uni2utf ($source)
{
    $length = strlen($source);
    if ($length & 1)
    {
        trigger_error('fail to translate');
    }

    $str = '';
    for ($i=0; $i < $length; $i+=2)
    {
        $low = ord($source{$i});
        $high = ord($source{$i+1});
        if($high > 8)
        {
            $str .= chr(0xE0 | ($high>>4)) . chr(0x80 | (($high & 0xF) << 2) | ($low >> 6)) . chr(0x80 | ($low & 0x3F));
        }
        else if ($high == 0 && $low < 128)
        {
            $str .= chr(0x7F & $low);
        }
        else
        {
            $str .= chr(0xC0 | (($high & 3) << 2) | ($low >> 6)) . chr(0x80 | ($low & 0x3F));
        }
    }
    return $str;
}

function db_ping()
{
    static $ping_time = null;
    if ($ping_time === null) $ping_time = time();

    if ((time() - $ping_time) >= 1)
    {
        $ping_time = time();
        $GLOBALS['db']->ping();
    }
}

?>
