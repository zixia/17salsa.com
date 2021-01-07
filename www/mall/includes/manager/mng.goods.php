<?php

/**
 * ECMALL: goods ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.goods.php 6099 2008-11-21 03:17:53Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}
include_once(ROOT_PATH . '/includes/manager/mng.base.php');
include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
include_once(ROOT_PATH . '/includes/models/mod.brand.php');

class GoodsManager extends Manager
{
    var $allow_file_size = 0;
    var $_store_id = 0;
    var $_use_default_spec = true;

    function __construct($store_id = 0, $allow_file_size  = 0)
    {
        $this->GoodsManager($store_id, $allow_file_size);
    }

    function GoodsManager($store_id = 0, $allow_file_size  = 0)
    {
        $this->_store_id = intval(trim($store_id));
        $this->allow_file_size = $allow_file_size;
    }

    /**
     * ���һ����Ʒ
     *
     * @author  wj
     * @param   array           $data       ��Ʒ����
     * @return  boolen
     */
    function add ($data)
    {
        $data = $this->_filter_data($data);
        if ($data)
        {
            //add default time
            $data['ecm_goods']['add_time'] = gmtime();
            $data['ecm_goods']['sort_weighing'] = 999;
            $data['ecm_goods']['last_update'] = $data['ecm_goods']['add_time'];
            $data['ecm_goods']['store_id'] = $this->_store_id;
            //set default spec
            if (!isset($data['other']['default_spec'])) $data['other']['default_spec'] = 0;

            $GLOBALS['db']->autoExecute('`ecm_goods`', $data['ecm_goods'], 'INSERT');
            $goods_id = $GLOBALS['db']->insert_id();
            //spec
            $default_spec = '';
            for ($i=0; $i<count($data['`ecm_goods_spec`']); $i++)
            {
                $data['`ecm_goods_spec`'][$i]['goods_id'] = $goods_id;
                unset($data['`ecm_goods_spec`'][$i]['spec_id']);
                if (empty($data['`ecm_goods_spec`'][$i]['sku'])) $data['`ecm_goods_spec`'][$i]['sku'] = $this->generate_sku($goods_id);
                $GLOBALS['db']->autoExecute('`ecm_goods_spec`', $data['`ecm_goods_spec`'][$i], 'INSERT');
                //update goods default spec
                if ($data['other']['default_spec'] == $i)
                {
                    $default_spec = $GLOBALS['db']->insert_id();
                    $sql = "UPDATE `ecm_goods` SET default_spec = '$default_spec' WHERE goods_id = '$goods_id'";
                    $GLOBALS['db']->query($sql);
                }
            }
            // attr
            if (isset($data['ecm_goods_attr']))
            {
                foreach ($data['ecm_goods_attr'] as $val)
                {
                    $val['goods_id'] = $goods_id;
                    $GLOBALS['db']->autoExecute('`ecm_goods_attr`', $val, 'INSERT');
                }
            }

            //�����ϴ���Ʒ
            $imgaes_ids = $this->_upload_images($goods_id, $data['thumb']);
            //����spec��default_image
            $this->_update_default_image($goods_id);

            //������������͵��̵���Ʒ����
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
            $new_category = new Category(0, $this->_store_id);
            $mng_brand = new BrandManager($this->_store_id);
            $new_category->alter_goods_num(1, $data['ecm_goods']['mall_cate_id']); //������վ��������
            $new_category->alter_goods_num(1, $data['ecm_goods']['store_cate_id']); //���µ��̷�������Ʒ
            $mng_brand->alter_goods_num(1, $data['ecm_goods']['brand_id']); //����Ʒ����Ʒ����
            $this->update_goods_count();

            return $goods_id;
        }
        else
        {
            return false;
        }
    }

    /**
     * ������Ʒ
     *
     * @author  wj
     * @param   int         $goods_id   ��Ʒid
     * @param   int         $data       ��Ʒ��������
     *
     * @return  bool
     */
    function update ($goods_id, $data)
    {
        $data = $this->_filter_data($data);
        if ($data)
        {
            $old_data = $GLOBALS['db']->getRow("SELECT mall_cate_id, store_cate_id, brand_id FROM `ecm_goods` WHERE goods_id = '$goods_id'");
            //add default time
            $data['ecm_goods']['last_update'] = gmtime();
            //set default spec
            if (!isset($data['other']['default_spec'])) $data['other']['default_spec'] = 0;

            $GLOBALS['db']->autoExecute('`ecm_goods`', $data['ecm_goods'], 'UPDATE', "goods_id='{$goods_id}'");
            //spec
            for ($i=0; $i<count($data['`ecm_goods_spec`']); $i++)
            {
                $data['`ecm_goods_spec`'][$i]['goods_id'] = $goods_id;
                $spec_id = $data['`ecm_goods_spec`'][$i]['spec_id'];
                unset($data['`ecm_goods_spec`'][$i]['spec_id']);
                if (empty($data['`ecm_goods_spec`'][$i]['sku'])) $data['`ecm_goods_spec`'][$i]['sku'] = $this->generate_sku($goods_id);
                if ($spec_id > 0)
                {
                    $GLOBALS['db']->autoExecute('`ecm_goods_spec`', $data['`ecm_goods_spec`'][$i], 'UPDATE', "spec_id='{$spec_id}'");
                }
                else
                {
                    $GLOBALS['db']->autoExecute('`ecm_goods_spec`', $data['`ecm_goods_spec`'][$i], 'INSERT');
                    $spec_id = $GLOBALS['db']->insert_id();
                }
                //update goods default spec
                if ($data['other']['default_spec'] == $i)
                {
                    $sql = "UPDATE `ecm_goods` SET default_spec = '$spec_id' WHERE goods_id = '$goods_id'";
                    $GLOBALS['db']->query($sql);
                }
            }
            // attr
            if (isset($data['ecm_goods_attr']))
            {
                $sql = "DELETE FROM `ecm_goods_attr` WHERE goods_id='{$goods_id}'";
                $GLOBALS['db']->query($sql);
                foreach ($data['ecm_goods_attr'] as $val)
                {
                    $val['goods_id'] = $goods_id;
                    $GLOBALS['db']->autoExecute('`ecm_goods_attr`', $val, 'INSERT');
                }
            }

            //������Ʒ�������ɫ�������ֶ�
            if (isset($data['ecm_upload_files']))
            {
                for ($i=0; $i<count($data['ecm_upload_files']); $i++)
                {
                    $file_id = $data['ecm_upload_files'][$i]['file_id'];
                    unset($data['ecm_upload_files'][$i]['file_id']);
                    $GLOBALS['db']->autoExecute('`ecm_upload_files`', $data['ecm_upload_files'][$i], 'UPDATE', "file_id='$file_id'");
                }
            }

            $imgaes_ids = $this->_upload_images($goods_id, $data['thumb']);
            //����spec��default_image
            $this->_update_default_image($goods_id);

            //���·�������Ʒ����
            include_once(ROOT_PATH . '/includes/models/mod.category.php');
            include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
            $new_category = new Category(0, $this->_store_id);
            $mng_brand = new BrandManager($this->_store_id);
            if ($old_data['store_cate_id'] != $data['ecm_goods']['store_cate_id'])
            {
                $new_category->alter_goods_num(1, $data['ecm_goods']['store_cate_id']);
                $new_category->alter_goods_num(-1, $old_data['store_cate_id']);
            }
            if ($old_data['mall_cate_id'] != $data['ecm_goods']['mall_cate_id'])
            {
                $new_category->alter_goods_num(1, $data['ecm_goods']['mall_cate_id']);
                $new_category->alter_goods_num(-1, $old_data['mall_cate_id']);
            }
            if ($old_data['brand_id'] != $data['ecm_goods']['brand_id'])
            {
                $mng_brand->alter_goods_num(1, $data['ecm_goods']['brand_id']);
                $mng_brand->alter_goods_num(-1, $old_data['brand_id']);
            }

            // ���µ�����Ʒ����
            $this->update_goods_count();

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * �ϴ���Ʒ��ͼƬ
     *
     * @author  wj
     * @param   int         $goods_id   ��Ʒid
     * @param   array       $thumb      ��Ʒͼ������
     * @return  bool|array
     */
    function _upload_images($goods_id, $thumb)
    {

        $manage = $this->_thumb($goods_id);
        $images = $manage->add($thumb['image'], $thumb['color_name'], $thumb['sort_order']);
        $this->err = $manage->err;
        if (!$images)
        {
            if ($manage->err == 'e_noupload_files')
            {
                return 'e_noupload_files';
            }
            else
            {
                $this->err = $manage->err;
                return false;
            }
        }
        else
        {
            return $images;
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
    function &_thumb ($goods_id)
    {
        static $thumbs = null;

        if (!isset($thumbs[$goods_id]))
        {
            include_once(ROOT_PATH . '/includes/manager/mng.file.php');
            $thumb = new FileManager($this->_store_id);
            $thumb->item_id = $goods_id;
            $thumb->allow_size = $this->allow_file_size;
            $thumb->file_type = 'image';
            $thumb->item_type = 'album';
            $thumb->water_mark = false;
            //todo: ˮӡ
            //$thumb->water_mark = ROOT_PATH . '/data/watermark.gif';

            $thumb->manager->thumb_status = true;
            //todo: ���ͼƬ�߶��Լ�����
            $thumb->manager->thumb_width = 500;
            $thumb->manager->thumb_height = 300;
            $thumb->manager->thumb_quality = 90;
            $thumbs[$goods_id] = $thumb;
        }


        return $thumbs[$goods_id];
    }

    /**
     * ����ָ�����id��Ʒ��Ϣ
     *
     * @access  public
     * @param   $spec_ids
     *
     * @return void
     */
    function get_goods ($spec_ids)
    {
        $sql = "SELECT s.*, g.* ".
               " FROM `ecm_goods_spec` AS s".
               " LEFT JOIN `ecm_goods` AS g ON s.goods_id = g.goods_id ".
               " WHERE s.spec_id " . db_create_in($spec_ids);
        $arr = $GLOBALS['db']->getAll($sql);
        $data = array();
        foreach ($arr as $spec)
        {
            $data[$spec['spec_id']] = $spec;
        }
        return $data;
    }

    /**
     * ���ָ����Ʒ��ļ۸�������
     *
     * @author  wj
     * @param   array      condition   ��������
     * @return  array       array('min_price' => $min, 'max_price' => $max)
     */
    function get_limit_price($condition)
    {
        $where = $this->_make_condition($condition);
        $sql = "SELECT MIN(s.store_price) AS min_price, MAX(s.store_price) AS max_price".
               " FROM `ecm_goods` AS g ".
               " LEFT JOIN `ecm_goods_spec` AS s ON g.goods_id=s.goods_id".
               " WHERE " . $where;

        return $GLOBALS['db']->getRow($sql);
    }


    /**
     * ��ȡָ�������е�Ʒ���б�����ҳ����ʹ�ã�
     *
     * @author  wj
     * @param array $condition
     * @param int $limit
     * @return array
     */
    function get_brand_list($condition, $limit)
    {
        $where = $this->_make_condition($condition);
        $sql = "SELECT b.brand_id, b.brand_name, COUNT(g.goods_id) AS num".
               " FROM `ecm_brand` AS b".
               " LEFT JOIN `ecm_goods` AS g  ON g.brand_id=b.brand_id";
         if (!$this->_use_default_spec)
         {
             $sql .= " LEFT JOIN `ecm_goods_spec` AS s ON g.goods_id = s.goods_id";
         }

         $sql .=  " WHERE  "  . $where . " GROUP BY b.brand_id ORDER BY num DESC";

        if ($limit > 0)
        {
            $sql .= " LIMIT " . intval($limit);
        }

        return $GLOBALS['db']->getAll($sql);
    }


    /**
     * ��ȡָ����������Ʒ��ɫ�б�
     *
     * @author  wj
     * @param array $condition
     * @return array
     */
    function get_color_list($condition)
    {
        $where = $this->_make_condition($condition);
        $sql = "SELECT DISTINCT color_rgb".
               " FROM `ecm_goods_spec` AS s".
               " LEFT JOIN `ecm_goods` AS g ON g.goods_id=s.goods_id ".
               " WHERE  " . $where . " AND color_rgb > '' ";
        $data = $GLOBALS['db']->getAll($sql);
        $count = count($data);
        for ($i=0; $i < $count; $i++)
        {
            $data[$i]['color_rgb_trim'] = trim($data[$i]['color_rgb'], '#');
        }
        return $data;
    }


    /**
     * ���ط�����������Ʒ�б�
     *
     * @author  wj
     * @param   int     $page       ��ǰ��ҳ
     * @param   array   $condition  ����
     * @param   int     $pagesize   ��ҳ��С
     *
     * @return array
     */
    function get_list ($page, $condition=null, $pagesize = 0, $is_full=false)
    {
        $arg = $this->query_params($page, $condition, 'goods_id', $pagesize);
        $arg['sort'] = $this->get_pre_sort($arg['sort']);

        $sql = "SELECT g.goods_id, g.store_id, g.goods_name, g.mall_cate_id,".
               " g.store_cate_id, g.click_count, g.brand_id, g.is_deny, ".
               " g.goods_brief, g.is_real, g.extension_code, g.is_on_sale, g.is_alone_sale, ".
               " g.give_points, g.max_use_points, g.add_time, ".
               " g.type_id, g.new_level, g.sort_weighing,".
               " g.is_mi_best, g.is_mw_best, g.is_m_hot,".
               " g.is_s_new, g.is_s_best, g.sales_volume, g.last_update,".
               " s.spec_id, s.color_name, s.color_rgb, s.spec_name, s.sku,".
               " s.stock, s.market_price, s.store_price, s.default_image, ".
               " st.store_name, st.is_certified, b.brand_name,".
               " sm.cate_name AS mall_cate_name, ss.cate_name AS store_cate_name ";

        if ($is_full)
        {
            $sql .= ', g.goods_desc ';
        }

        $sql .=" FROM `ecm_goods` AS g ";
        if ($this->_use_default_spec)
        {
            $sql .= " LEFT JOIN `ecm_goods_spec` AS s ON g.default_spec = s.spec_id";
        }
        else
        {
            $sql .= " LEFT JOIN `ecm_goods_spec` AS s ON g.goods_id = s.goods_id";
        }

        $sql .=" LEFT JOIN `ecm_brand` AS b ON g.brand_id = b.brand_id ".
               " LEFT JOIN `ecm_category` AS sm ON g.mall_cate_id = sm.cate_id ".
               " LEFT JOIN `ecm_category` AS ss ON g.store_cate_id = ss.cate_id ".
               " LEFT JOIN `ecm_store` AS st ON st.store_id = g.store_id".
               " WHERE $arg[where]";
        if (!$this->_use_default_spec)
        {
            $sql .= " GROUP BY g.goods_id";
        }

        $sql .= " ORDER BY {$arg[sort]} $arg[order] LIMIT $arg[start], $arg[number]";

        $data = $GLOBALS['db']->getAll($sql);

        return array('data' => $data, 'info' => $arg['info']);
    }

    /**
     * ����goods_id �õ���Ʒ
     * @param string ids
     *
     * @return array goods_list
     */
    function get_list_by_ids($ids)
    {
        if (empty($ids)) return array();
        $data = $this->get_list(1, array('goods_ids'=>$ids), 1000);

        return $data['data'];
    }

    /**
     *  ���ɲ�ѯ����
     *
     * @author  wj
     * @param   array   $condition      ��������
     *
     * @return  string  ���ɲ�ѯ����
     */
    function _make_condition ($condition)
    {
        static $data = null;
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $key = crc32(var_export($condition, true));
        if (!isset($data[$key]))
        {
            $this->_use_default_spec = true; //ÿ�ζ���ԭ
            $where  = '1';
            if ($this->_store_id > 0) $condition['store_id'] = $this->_store_id;
            foreach ($condition as $k => $v)
            {
                switch ($k)
                {
                    case "store_id":
                    case "is_mi_best":
                    case "is_mw_best":
                    case "is_m_hot":
                    case "is_s_new":
                    case "is_s_best":
                    case "brand_id":
                    case "is_on_sale":
                        $where .= " AND g.{$k}='$v'";
                        break;
                    case "is_m_best":
                        $where .= " AND (g.is_mi_best = 1 OR g.is_mw_best = 1)";
                        break;
                    case "stock":
                        //ת��Ϊgoods id �Ĳ�ѯ
                        $sql = "SELECT DISTINCT(goods_id) FROM `ecm_goods_spec` WHERE stock = 0";
                        $where .= " AND g.goods_id " . db_create_in($GLOBALS['db']->getCol($sql));
                        break;
                    case "tag_words":
                        $v = trim($v);
                        $where .= " AND g.keywords LIKE '%" . $v . "%'";
                        break;
                    case "keywords":
                        $v = trim($v);
                        $word_list = explode(' ', $v);

                        foreach ($word_list as $word)
                        {
                            $where .= " AND (g.goods_name LIKE '%" . trim($word) . "%' OR g.keywords LIKE '%" . trim($word) . "%')";
                        }
                        break;
                    case "store_cate_id":
                        $category = new Category($v, $this->_store_id);
                        $where .= " AND g.{$k} " . db_create_in($category->list_child_id());
                        break;
                    case "mall_cate_id":
                        $category = new Category($v, 0);
                        $where .= " AND g.{$k} " . db_create_in($category->list_child_id());
                        break;
                    case "brand_name":
                        $brand_manager = new BrandManager($this->_store_id);
                        $brand_info = $brand_manager->get_brand_info($v);
                        if ($brand_info)
                        {
                            $where .= " AND g.brand_id = '" . $brand_info['brand_id'] . "'";
                        }
                        break;
                    case "brand_id":
                        $where .= " AND g.brand_id = '" . $v . "'";
                        break;
                    case "goods_ids":
                        $where .= " AND g.goods_id " . db_create_in($v);
                        break;
                    case "except_goods_ids":
                        $where .= " AND g.goods_id NOT " . db_create_in($v);
                        break;
                    case "new_level":
                        if ($v >= 10)
                        {
                            $where .= " AND g.new_level = 10 ";
                        }
                        else
                        {
                            $where .= " AND g.new_level < 10 ";
                        }
                        break;
                    case "store_is_open":
                        $where .= " AND g.store_id " . db_create_in($this->get_open_store());
                        break;
                    case 'min_price':
                        $min_price = intval($v);
                        if ($min_price > 0)
                        {
                            $where .= " AND s.store_price >= $min_price";
                            $this->_use_default_spec = false;
                        }
                        break;
                    case 'max_price':
                        $max_price = intval($v);
                        if ($max_price > 0)
                        {
                            $where .= " AND s.store_price <= $max_price";
                            $this->_use_default_spec = false;
                        }

                        break;
                    case 'color_rgb':
                        $where .= " AND s.color_rgb='$v'";
                        $this->_use_default_spec = false;
                        break;
                    case 'sell_able':
                        $region = isset($condition['region_id']) ? intval($condition['region_id']) : 0;
                        $where .= " AND g.store_id " . db_create_in($this->get_open_store(null, $region)) .
                                  " AND g.is_on_sale = 1" .
                                  " AND g.is_deny = 0 ";
                    default:

                }
            }

            $data[$key] = $where;
        }

        return $data[$key];
    }

    /**
     * ���ط���������Ʒ����
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_count ($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql = "SELECT COUNT(*) FROM `ecm_goods` AS g ";
        if (!$this->_use_default_spec)
        {
            $sql .= " LEFT JOIN `ecm_goods_spec`  AS s ON g.goods_id=s.goods_id WHERE " . $where . " GROUP BY g.goods_id";
        }
        else
        {
            $sql .= " WHERE " . $where;
        }

        $rec_count  = $GLOBALS['db']->getOne($sql);
        return $rec_count;
    }

    /**
     * �����ύ����Ʒ����
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function _filter_data ($data)
    {
        $result = array();
        foreach ($data as $key=>$val)
        {
            switch ($key)
            {
                case 'goods_name':
                case 'goods_brief':
                case 'goods_desc':
                case 'seller_note':
                    $result['ecm_goods'][$key] = trim($val);
                    break;
                case 'store_id':
                case 'mall_cate_id':
                case 'store_cate_id':
                case 'give_points':
                case 'max_use_points':
                case 'type_id':
                case 'new_level':
                case 'is_deny':
                case 'editor_type':
                case 'sort_weighing':
                    $result['ecm_goods'][$key] = intval($val);
                    break;
                case 'goods_weight':
                    $result['ecm_goods'][$key] = floatval($val);
                    break;
                case 'is_real':
                case 'is_on_sale':
                case 'is_alone_sale':
                case 'is_s_best':
                case 'is_s_new':
                case 'is_mi_best':
                case 'is_mw_best':
                case 'is_m_hot':
                    $result['ecm_goods'][$key] = empty($val) ? 0 : 1;
                    break;
                case 'brand_name':
                    if (empty($val))
                    {
                        $result['ecm_goods']['brand_id'] = 0;
                    }
                    else
                    {
                        $brand_manager = new BrandManager($this->_store_id);
                        $brand_info = $brand_manager->get_brand_info($val);
                        if ($brand_info)
                        {
                            if (($brand_info['store_id'] != 0) && ($brand_info['store_id'] != $this->_store_id))
                            {
                                $brand = new Brand($brand_info['brand_id'], 0);
                                $brand->set_public();
                            }
                        }
                        else
                        {
                            $brand_info = array('brand_name'=>$val,'store_id'=>$this->_store_id);
                            $brand_info['brand_id'] = $brand_manager->add($brand_info);
                        }
                        $result['ecm_goods']['brand_id'] = $brand_info['brand_id'];
                    }
                    break;
                case 'color_name':
                case 'spec_name':
                case 'sku':
                    foreach ($val as $i=>$v)
                    {
                       $result['`ecm_goods_spec`'][$i][$key] = trim($v);
                    }
                    break;
                case 'stock':
                case 'spec_id':
                    foreach ($val as $i=>$v)
                    {
                       $result['`ecm_goods_spec`'][$i][$key] = intval($v);
                    }
                    break;
                case 'store_price':
                case 'market_price':
                    foreach ($val as $i=>$v)
                    {
                       $result['`ecm_goods_spec`'][$i][$key] = floatval($v);
                    }
                    break;
                case 'color_rgb':
                    foreach ($val as $i=>$v)
                    {
                        $v = trim($v);
                        $result['`ecm_goods_spec`'][$i][$key] = preg_match('/^#[0-9A-F][0-9A-F][0-9A-F][0-9A-F][0-9A-F][0-9A-F]$/', $v) ? $v : '';
                    }
                    break;
                case 'image':
                    $result['thumb']['image'] = $val;
                    break;
                case 'image_color':
                    $result['thumb']['color_name'] = $val;
                    break;
                case 'image_sort':
                    $result['thumb']['sort_order'] = $val;
                    break;
                case 'post_file_id':
                    foreach ($val as $i=>$v)
                    {
                        $result['ecm_upload_files'][$i]['file_id'] = intval($v);
                    }
                    break;
                case 'post_image_color':
                    foreach ($val as $i=>$v)
                    {
                        $result['ecm_upload_files'][$i]['color'] = trim($v);
                    }
                    break;
                case 'post_image_sort':
                    foreach ($val as $i=>$v)
                    {
                        $result['ecm_upload_files'][$i]['sort_order'] = intval($v);
                    }
                    break;
                case 'attr':
                    foreach ($val as $i=>$v)
                    {
                        $result['ecm_goods_attr'][] = array('attr_id'=>intval($i), 'attr_value'=>trim($v));
                    }
                    break;
                case 'default_spec':
                    $result['other']['default_spec'] = empty($val) ? 0 : intval($val);
                    break;
                case 'goods_desc_attachments':
                    foreach ($val as $i=>$v)
                    {
                        $result['other']['attachments'][] = intval($v);
                    }
                    break;
                case 'keywords':
                    if (!empty($val))
                    {
                        $tags = preg_split('/[\s|,]+/', $val);
                        $tags = array_unique($tags);
                        $val = implode(' ', $tags);
                    }

                    $result['ecm_goods'][$key] = $val;
                    break;

                default:
            }
        }

        return $result;
    }

    /**
     * �Զ�����sku
     *
     * @author   wj
     * @param    int    $goods_id       //��Ʒid
     *
     * @return  stirng
     */
    function generate_sku ($goods_id)
    {
        static $skus = null;
        if (!isset($skus[$goods_id]))
        {
            $sql = "SELECT sku FROM `ecm_goods_spec` WHERE goods_id = '$goods_id'";
            $skus[$goods_id] = $GLOBALS['db']->getCol($sql);
        }
        $count = count($skus[$goods_id]);
        if ($count == 0)
        {
            // by zixia 2009-01-05 $pre_sku = 'ECM' . str_repeat('0', 5 - strlen($goods_id)) . $goods_id;
            $pre_sku = 'SALSA' . str_repeat('0', 5 - strlen($goods_id)) . $goods_id;
            $sku =  $pre_sku . '-01';
            $skus[$goods_id][] = $sku;
        }
        else
        {
            $pos = strrpos($skus[$goods_id][0], '-');
            if ($pos > 0)
            {
                $pre_sku = substr($skus[$goods_id][0], 0, $pos) . '-';
            }
            else
            {
                $pre_sku =  $skus[$goods_id][0] . '-';
            }

            //ֱ���ҵ����ظ���ǰ׺������
            while (1)
            {
                $sql = "SELECT COUNT(*) FROM `ecm_goods_spec` WHERE sku LIKE '$pre_sku%' AND goods_id <> '$goods_id'";
                if ($GLOBALS['db']->getOne($sql) == 0)
                {
                    break;
                }
                $pre_sku .= '-ext-';
            }
            //ֱ���ҵ����ظ�sku������ѭ��
            while(1)
            {
                if ($count < 10) $pre_sku .= '0';
                $sku = $pre_sku . ($count + 1);
                if (array_search($sku, $skus[$goods_id]) === false)
                {
                    $skus[$goods_id][] = $sku;
                    break;
                }
                $count ++;
            }
        }

        return $sku;
    }

    /**
     * ��������ѡ���ĵ���
     *
     * @author  weberliu
     * @param   string   $type  set_top, set_show, drop
     * @param   string   $in
     * @return  bool
     */
    function batch($type, $in)
    {
        $sql = "UPDATE `ecm_goods` SET";
        switch ($type)
        {
            case 'drop':
                return $this->drop($in);
            break;
            case 'set_sale':
                $sql .= ' is_on_sale = 1 - is_on_sale';
            break;
            case 'set_best':
                $sql .= ' is_s_best = 1 - is_s_best';
            break;
            default:
                $this->err = 'Unknow batch processor';
                return false;
        }
        $sql .= " WHERE goods_id " .db_create_in($in);

        if ($this->_store_id>0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }
        return $GLOBALS['db']->query($sql);
    }

    /**
     * ��ָ����Ʒת�Ƶ�ָ������
     *
     * @author  weberliu
     * @param  string   $store_cate_id      �̵����id
     * @param  string   $in                 Ҫ�ı����Ʒid
     *
     * @return  bool
     */
    function move_to_store_cate($store_cate_id, $in)
    {
        $query_in = db_create_in($in, 'goods_id');

        /* ������������Ʒ���� */
        $sql = "SELECT store_cate_id, COUNT(store_cate_id) AS num FROM `ecm_goods` WHERE $query_in ";
        if ($this->_store_id > 0) $sql .= " AND store_id = " . $this->_store_id;
        $sql .= " GROUP BY store_cate_id";

        $cate_info = $GLOBALS['db']->getAll($sql);
        $goods_count = 0;

        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate = new Category(0, $this->_store_id);
        foreach ($cate_info as $row)
        {
            if ($row['store_cate_id'] > 0)
            {
                $cate->alter_goods_num(0-$row['num'], $row['store_cate_id']);
            }
            $goods_count += $row['num'];
        }
        $cate->alter_goods_num($goods_count, $store_cate_id);

        /*�޸���Ʒ����*/
        $sql = "UPDATE `ecm_goods` SET store_cate_id = '$store_cate_id' WHERE $query_in ";
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }
        $GLOBALS['db']->query($sql);
    }


    /**
     * ��ָ����Ʒת�Ƶ�ָ������
     *
     * @author  wj
     * @param  string   $store_cate_id      �̵����id
     * @param  string   $in                 Ҫ�ı����Ʒid
     *
     * @return  bool
     */
    function move_to_mall_cate($mall_cate_id, $in)
    {
        $query_in = db_create_in($in, 'goods_id');

        /* ������������Ʒ���� */
        $sql = "SELECT mall_cate_id, COUNT(mall_cate_id) AS num FROM `ecm_goods` WHERE $query_in ";
        if ($this->_store_id > 0) $sql .= " AND store_id = " . $this->_store_id;
        $sql .= " GROUP BY mall_cate_id";

        $cate_info = $GLOBALS['db']->getAll($sql);
        $goods_count = 0;

        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate = new Category(0, 0);
        foreach ($cate_info as $row)
        {
            if ($row['mall_cate_id'] > 0)
            {
                $cate->alter_goods_num(0-$row['num'], $row['mall_cate_id']);
            }
            $goods_count += $row['num'];
        }
        $cate->alter_goods_num($goods_count, $mall_cate_id);

        /*�޸���Ʒ����*/
        $sql = "UPDATE `ecm_goods` SET mall_cate_id = '$mall_cate_id' WHERE $query_in ";
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }

        $GLOBALS['db']->query($sql);
    }

   /**
    * ɾ����Ʒ
    *
    * @author   wj
    * @param    $in     ��Ʒid
    * @return   bool
    */
   function drop ($in)
   {
       $query_in = db_create_in($in);

        /*����û��Ȩ�޵���Ʒid*/
        if ($this->_store_id > 0)
        {
            $sql = "SELECT goods_id FROM `ecm_goods` WHERE goods_id $query_in AND store_id='{$this->_store_id}'";
            $col = $GLOBALS['db']->getCol($sql);
            if (empty($col))
            {
                return true;
            }
            $in = implode(',', $col);
        }

        /*�������ڽ��е��Ź�*/
        $cur_time = gmtime();
        $sql = "SELECT goods_id FROM `ecm_goods_activity` where goods_id $query_in AND act_type = '" .ACT_GROUPBUY. "' AND is_finished = 0 AND end_time > '$cur_time'";
        $col = $GLOBALS['db']->getCol($sql);

        if ($col)
        {
            $in = explode(',', $in);
            $in = array_diff($in, $col);
            if (empty($in))
            {
                $this->err = 'groupbuy_is_active';

                return false;
            }

            $query_in = db_create_in($in);
        }

        /* �������̷�������Ʒ���� */
        $sql = "SELECT store_cate_id, COUNT(store_cate_id) AS num FROM `ecm_goods` WHERE goods_id $query_in GROUP BY store_cate_id";
        $cate_info = $GLOBALS['db']->getAll($sql);
        $goods_count = 0;
        include_once(ROOT_PATH . '/includes/models/mod.category.php');
        $cate = new Category(0, $this->_store_id);
        foreach ($cate_info as $row)
        {
            if ($row['store_cate_id'] > 0)
            {
                $cate->alter_goods_num(0-$row['num'], $row['store_cate_id']);
            }
            $goods_count += $row['num'];
        }
        /*������վ�������Ʒ����*/
        $sql = "SELECT mall_cate_id, COUNT(mall_cate_id) AS num FROM `ecm_goods` WHERE goods_id $query_in  GROUP BY mall_cate_id";
        $cate_info = $GLOBALS['db']->getAll($sql);
        $cate = new Category(0, 0);
        foreach ($cate_info as $row)
        {
            if ($row['mall_cate_id'] > 0)
            {
                $cate->alter_goods_num(0-$row['num'], $row['mall_cate_id']);
            }
        }
        //�޸ĵ��̵���Ʒ����
        if ($this->_store_id == 0)
        {
            //�����Ƕ�����̵�
            $sql = "SELECT store_id, COUNT(goods_id) AS goods_count FROM `ecm_goods` WHERE goods_id $query_in GROUP BY store_id";
            $res = $GLOBALS['db']->query($sql);
            while ($row = $GLOBALS['db']->fetchRow($res))
            {
                $GLOBALS['db']->query("UPDATE `ecm_store` SET goods_count = IF((goods_count - $row[goods_count]) > 0, goods_count - $row[goods_count], 0 ) WHERE store_id='$row[store_id]'" );
            }
        }
        //����Ʒ������Ʒ����
        include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
        $sql = " SELECT brand_id, COUNT(brand_id) AS num FROM `ecm_goods` WHERE goods_id $query_in AND brand_id > 0 GROUP BY brand_id";
        $brand_info = $GLOBALS['db']->getAll($sql);
        $mng_brand = new BrandManager($this->_store_id);
        foreach ($brand_info as $row)
        {
            $mng_brand->alter_goods_num(0-$row['num'], $row['brand_id']);
        }

        /* ��ʼɾ����Ʒ */
        $sql = "DELETE FROM `ecm_goods_spec`  WHERE goods_id $query_in";
        $GLOBALS['db']->query($sql);
        /* ɾ������ */
        $sql = "DELETE FROM `ecm_goods_attr` WHERE goods_id $query_in";
        $GLOBALS['db']->query($sql);
        /* ɾ��������Ʒ */
        $sql = "DELETE FROM `ecm_related_goods` WHERE goods_id $query_in or related_goods_id $query_in";
        $GLOBALS['db']->query($sql);
        /* ɾ����Ʒ */
        $sql = "DELETE FROM `ecm_goods`  WHERE goods_id $query_in";
        $GLOBALS['db']->query($sql);
        $del_count = $GLOBALS['db']->affected_rows();
        /* ɾ���ղؼ� */
        $sql = "DELETE FROM `ecm_collect_goods` WHERE goods_id $query_in";
        $GLOBALS['db']->query($sql);
        /*ɾ��ͼƬ*/
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $mng_file = new FileManager($this->_store_id);
        $mng_file->drop_by_item($in);

        //�޸ĵ��̵���Ʒ����
        if ($this->_store_id > 0)
        {
            $this->update_goods_count();
        }

        return $del_count;
    }

    /**
     * ����sku��ȡ��Ʒid
     *
     * @access  public
     * @param   stirng      $sku
     *
     * @return void
     */
    function get_spec_id_by_sku ($sku)
    {
        $sql = "SELECT spec_id FROM `ecm_goods_spec`".
               " WHERE sku='$sku' LIMIT 1";

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ȡ��id��name����
     */
    function get_idname_list($page, $condition = null, $pagesize = 20)
    {
        $arg = $this->query_params($page, $condition, 'goods_id', $pagesize);
        if ($this->_store_id > 0)
        {
            $sql = "SELECT g.goods_id AS id, g.goods_name AS name ".
                   " FROM `ecm_goods` AS g ".
                   " WHERE $arg[where] ORDER BY {$arg[sort]} $arg[order] LIMIT $arg[start], $arg[number]";
        }
        else
        {
            $sql = "SELECT g.goods_id AS id, g.goods_name AS name ".
                   " FROM `ecm_goods` AS g ".
                   " WHERE $arg[where] ORDER BY {$arg[sort]} $arg[order] LIMIT $arg[start], $arg[number]";
        }

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * ����������Ʒ����
     *
     * @param   int     $id1    ��Ʒid1
     * @param   int     $id2    ��Ʒid2
     * @param   string  $type   ���ͣ�s���� c����
     */
    function set_related($id1, $id2, $type)
    {
        $sql = "INSERT INTO `ecm_related_goods` (goods_id, relation, related_goods_id) " .
                "VALUES ('$id1', '$type', '$id2')";
        $GLOBALS['db']->query($sql, 'SILENT');

        $sql = "INSERT INTO `ecm_related_goods` (goods_id, relation, related_goods_id) " .
                "VALUES ('$id2', '$type', '$id1')";
        $GLOBALS['db']->query($sql, 'SILENT');
    }

    /**
     * ���������Ʒ����
     *
     * @param   int     $id1    ��Ʒid1
     * @param   int     $id2    ��Ʒid2
     * @param   string  $type   ���ͣ�s���� c����
     */
    function unset_related($id1, $id2, $type)
    {
        $sql = "DELETE FROM `ecm_related_goods` " .
                "WHERE goods_id = '$id1' " .
                "AND relation = '$type' " .
                "AND related_goods_id = '$id2'";
        $GLOBALS['db']->query($sql, 'SILENT');

        $sql = "DELETE FROM `ecm_related_goods` " .
                "WHERE goods_id = '$id2' " .
                "AND relation = '$type' " .
                "AND related_goods_id = '$id1'";
        $GLOBALS['db']->query($sql, 'SILENT');
    }

    /**
     * ��ȡָ��������Ʒ����
     *
     * @param   array     $spec    ����б�
     * @return void
     */
    function get_total_weight($spec)
    {
        $spec_id = array_keys($spec);
        $sql = "SELECT s.spec_id, g.goods_weight".
               " FROM `ecm_goods_spec` AS s ".
               " LEFT JOIN `ecm_goods` AS g ON s.goods_id = g.goods_id ".
               " WHERE s.spec_id " . db_create_in($spec_id);
        $res = $GLOBALS['db']->query($sql);
        $total = 0;
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $total += floatval($row['goods_weight']) * $spec[$row['spec_id']];
        }

        return $total;
    }

    /**
     * �������ֶμ���ǰ׺
     *
     * @author  wj
     * @param   string      $sort   �����ֶ���
     * @return  string
     */
    function get_pre_sort($sort)
    {
        $map = array('goods_id'=>'g', 'store_id'=>'g', 'goods_name'=>'g', 'mall_cate_id'=>'g', 'store_cate_id'=>'g',
        'brand_id'=>'g','is_real'=>'g', 'give_points'=>'g', 'max_use_points'=>'g', 'add_time'=>'g', 'last_update'=>'g',
        'is_best'=>'g', 'is_new'=>'g', 'is_hot'=>'g', 'spec_id'=>'s', 'color_name'=>'s', 'spec_name'=>'s', 'stock'=>'s',
        'market_price'=>'s', 'store_price'=>'s', 'sort_weight'=>'g', 'is_mi_best'=>'g', 'is_mw_best'=>'g', 'is_m_hot'=>'g',
        'is_s_best'=>'g', 'is_s_hot'=>'g', 'is_s_new'=>'g', 'brand_name'=>'b', 'store_cate_name'=>'', 'mall_cate_name'=>'',
        'store_name'=>'', 'sort_weighing'=>'g', 'is_on_sale'=>'g', 'is_deny'=>'g'
        );
        if (isset($map[$sort]))
        {
            $sort = empty($map[$sort]) ? $sort : $map[$sort] . '.' . $sort;
        }
        else
        {
            $sort = 'g.goods_id';
        }

        return $sort;
    }

    /**
     * ������Ʒ����
     *
     */
    function update_goods_count()
    {
        if ($this->_store_id > 0)
        {
            $sql = "SELECT COUNT(*) FROM `ecm_goods` WHERE store_id = '{$this->_store_id}' AND is_on_sale = 1 AND is_deny = 0 ";
            $store_goods_count = $GLOBALS['db']->getOne($sql);
            include_once(ROOT_PATH . '/includes/models/mod.store.php');
            $new_store = new Store($this->_store_id);
            $new_store->update(array('goods_count'=>$store_goods_count));
        }
    }

    /**
     * ��ȡ��Ʒ����ʾ�Ĺ�����ɫ�͵������ڵ�
     *
     * @author      wj
     * @param       array       $goods_ids      ��Ʒid
     *
     * @return      array
     */
    function get_extra_info($goods_ids)
    {
        $data = array();
        /* ��ȡ������ɫ */
        $sql = "SELECT * FROM `ecm_goods_spec` WHERE goods_id " . db_create_in($goods_ids);
        $query = $GLOBALS['db']->query($sql);
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            if (!empty($res['spec_name']))
            {
                $data[$res['goods_id']]['spec_names'][$res['spec_name']] = $res['spec_id'];
            }
            if (!empty($res['color_name']))
            {
                $data[$res['goods_id']]['color_names'][$res['color_name']] = $res['spec_id'];
            }
            /* ��¼rgbֵ */
            if (!empty($res['color_rgb']))
            {
                $data[$res['goods_id']]['color_rgb'][$res['color_rgb']] = $res;
            }
        }

        /* ��ȡ�������ڵ� */
        $sql = "SELECT r.region_name AS store_location, g.goods_id FROM `ecm_goods` AS g LEFT JOIN `ecm_store` AS s ON g.store_id=s.store_id LEFT JOIN `ecm_regions` AS r ON r.region_id = s.store_location WHERE g.goods_id " . db_create_in($goods_ids);
        $query = $GLOBALS['db']->query($sql);
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            $data[$res['goods_id']]['region'] = $res['store_location'];
        }

        /* ��ȡ��Ʒ�����۵Ĵ��� */
        $sql = "SELECT COUNT(*) AS messages, goods_id FROM `ecm_message` WHERE goods_id" . db_create_in($goods_ids) . " AND if_show='1' GROUP BY goods_id";
        $query = $GLOBALS['db']->query($sql);
        while ($res = $GLOBALS['db']->fetch_array($query))
        {
            $data[$res['goods_id']]['messages'] = $res['messages'];
        }
        return $data;
    }

    /**
     * ����Ʒ����ת�Ƶ�ĳƷ����
     *
     * @author  weberliu
     * @param   int     $brand_id   Ʒ��ID
     * @param   string  $in         ʹ�ö��ŷָ�����ƷID
     * @return bool
     */
    function move_to_brand($brand_id, $in)
    {
        $query_in = db_create_in($in);

        /* ������������Ʒ���� */
        $sql = "SELECT brand_id, COUNT(brand_id) AS num FROM `ecm_goods` WHERE goods_id $query_in ";
        if ($this->_store_id > 0) $sql .= " AND store_id = " . $this->_store_id;
        $sql .= " GROUP BY brand_id";

        $brand_info = $GLOBALS['db']->getAll($sql);
        $brand_count = 0;

        include_once(ROOT_PATH . '/includes/manager/mng.brand.php');
        $mng_brand = new BrandManager($this->_store_id);
        foreach ($brand_info as $row)
        {
            if ($row['brand_id'] > 0)
            {
                $mng_brand->alter_goods_num(0 - $row['num'], $row['brand_id']);
                $brand_count += $row['num'];
            }
        }

        $mng_brand->alter_goods_num($brand_count, $brand_id);

        /*�޸���Ʒ����*/
        $sql = "UPDATE `ecm_goods` SET brand_id = '$brand_id' WHERE goods_id $query_in ";
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }
        $GLOBALS['db']->query($sql);
    }

    /**
     * ȡ��������������Ʒ�ĵ���
     *
     * @author  scottye
     * @param   array   $condition  ��������
     * @return  array
     */
    function get_store_ids($condition)
    {
        $sql = "SELECT DISTINCT store_id " .
                "FROM `ecm_goods` AS g " .
                "WHERE " . $this->_make_condition($condition);
        return $GLOBALS['db']->getCol($sql);
    }

    /**
     * update goods spec defalut image
     *
     * @author      wj
     * @param       int         $goods_id
     *
     * @return      void
     */
    function _update_default_image($goods_id)
    {
        $sql = "SELECT DISTINCT color_name FROM `ecm_goods_spec` WHERE goods_id='$goods_id'";
        $col = $GLOBALS['db']->getCol($sql);
        if ($col)
        {
            $thumb = $this->_thumb($goods_id);
            $color_map = array();
            foreach ($col as $color)
            {
                $color_map[$color] = $thumb->get_goods_image(addslashes($color));
            }

            $sql = "SELECT spec_id, color_name FROM `ecm_goods_spec` WHERE goods_id='$goods_id'";
            $specs = $GLOBALS['db']->getAll($sql);
            foreach ($specs as $spec)
            {
                $sql = "UPDATE `ecm_goods_spec` SET default_image = '" . $color_map[$spec['color_name']] . "' WHERE spec_id = '" . $spec['spec_id'] . "'";
                $GLOBALS['db']->query($sql);
            }
        }
        else
        {
            $default_color = intval($thumb->get_goods_image(''));
            $sql = "UPDATE `ecm_goods_spec` SET default_image = '{$default_color}' WHERE goods_id='$goods_id'";
            $GLOBALS['db']->query($sql);
        }
    }

    /**
     *  ���ӽ�����
     *
     *  @author Garbin
     *  @param  array $goods_ids
     *  @return void
     */
    function add_order_volumn($goods_ids)
    {
        $sql = "UPDATE `ecm_goods` SET order_volumn=order_volumn+1 WHERE goods_id " . db_create_in($goods_ids);
        $GLOBALS['db']->query($sql);
    }

    /**
     * ��ȡ���ʹ�õ�Ʒ������
     * @author wj
     * @param int $limit
     * @return array
     */
    function get_last_brand_name($limit)
    {
        $sql = "SELECT b.brand_name, b.brand_id, MAX(g.add_time) AS brand_order FROM `ecm_goods` AS g".
                " LEFT JOIN `ecm_brand` AS b ON g.brand_id = b.brand_id ".
                " WHERE g.brand_id > 0 ".
                " GROUP BY b.brand_id".
                " ORDER BY brand_order DESC ".
                " LIMIT " . intval($limit);
        return $GLOBALS['db']->getCol($sql);
    }
}

?>
