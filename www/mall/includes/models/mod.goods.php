<?php

/**
 * ECMALL: ��Ʒģ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.goods.php 6055 2008-11-13 09:00:40Z Scottye $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class Goods extends Model
{

    /**
     * ���캯��
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function __construct ($goods_id, $store_id=0)
    {
        $this->Goods($goods_id, $store_id);
    }

    /**
     * ���캯��
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function Goods($goods_id = 0, $store_id=0)
    {
        $this->_table = '`ecm_goods`';
        $this->_key   = 'goods_id';
        parent::Model($goods_id, $store_id);
    }

    /**
     * ��ȡ��Ʒ��Ϣ
     *
     * @author  wj
     * @param  void
     * @return array
     */
    function get_info ()
    {
        static $infos = null;
        if (!isset($infos[$this->_id]))
        {
            $sql = "SELECT g.*, b.brand_name ".
                " FROM `ecm_goods` as g".
                " LEFT JOIN `ecm_brand` AS b ON g.brand_id = b.brand_id ".
                " WHERE g.goods_id = '{$this->_id}'" . $this->_get_store_limit('g');
            $infos[$this->_id] = $GLOBALS['db']->getRow($sql);
        }

        return $infos[$this->_id];
    }

    /**
     *  ��ȡ��Ʒ��ϸ��Ϣ
     *
     * @author  wj
     * @param   int     spec_id     ���idΪ0ʱ��ȡĬ�Ϲ�񣬴���
     *
     * @return array
     */
    function get_goods_detail($spec_id=0)
    {
        $goods_info = $this->get_info();
        if (empty($goods_info))
        {
            $this->err = 'goods[' . $this->_id . '] is not found';
            return array();
        }

        if ($spec_id == 0)
        {
            $spec_id = $goods_info['default_spec'];
        }

        $spec_info = $this->get_spec_info($spec_id);

        return array_merge($goods_info, $spec_info);
    }

    /**
     *  ��ȡ��Ʒ����
     *
     * @author  wj
     * @param void
     *
     * @return array
     */
    function get_attribute ()
    {
        $sql = "SELECT ga.attr_value, ga.attr_id, a.attr_name, a.sort_order ".
               " FROM `ecm_goods_attr` AS ga ".
               " LEFT JOIN `ecm_attribute` AS a ON ga.attr_id=a.attr_id".
               " WHERE ga.goods_id = '{$this->_id}'".
               " ORDER BY a.sort_order ASC";
        $res = $GLOBALS['db']->query($sql);
        $list = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $list[$row['attr_id']] = $row;
        }

        return $list;
    }


    /**
     * ��ȡ������Ʒ
     *
     * @access  public
     * @param   void
     *
     * @return array
     */
    function get_similar_goods ()
    {
        return $this->get_related_goods('s');
    }

    /**
     *  ��ȡ������Ʒ
     *
     * @access  public
     * @param    void
     *
     * @return array
     */
    function get_compatible_goods ()
    {
        return $this->get_related_goods('c');
    }


    /**
     * ��ȡ��Ʒ������ɫ
     *
     * @access  public
     * @param    void
     *
     * @return array
     */
    function get_colors ($spec_name=null)
    {
        $spec = $this->get_cache_spec();
        $result = array();
        foreach ($spec as $val)
        {
            if (empty($val['color_name'])) continue;
            if (isset($result[$val['color_name']]))
            {
                $result[$val['color_name']]['spec'][$val['spec_name']] = $val;
                if (empty($result[$val['color_name']]['color_rgb']) && (!empty($val['color_rgb']))) $result[$val['color_name']]=$val['color_rgb'];
                if (isset($spec_name) && $val['spec_name']== $spec_name)
                {
                    $result[$val['color_name']]['spec_id'] = $val['spec_id'];
                }
            }
            else
            {
                $result[$val['color_name']] = array('color_name'=>$val['color_name'], 'color_rgb'=>$val['color_rgb'], 'spec'=>array($val['spec_name']=>$val));
                $result[$val['color_name']]['spec_id'] = $val['spec_id'];
            }
        }

        return $result;
    }

    /**
     * ��ȡ���еĹ��ţ�����ɫ�޹صģ���
     *
     * @author      wj
     * @param       string      $color_name     ��ɫ����
     *
     * @return      array
     */
    function get_specs ($color_name=null)
    {
        $spec = $this->get_cache_spec();
        $result = array();
        foreach ($spec as $val)
        {

            if (empty($val['spec_name'])) continue;
            if (isset($result[$val['spec_name']]))
            {
                if (isset($color_name) && $val['color_name'] == $color_name)
                {
                    $result[$val['spec_name']] = array('spec_id'=>$val['spec_id'], 'spec_name'=>$val['spec_name'], 'active'=> true);
                }
            }
            else
            {
                $result[$val['spec_name']] = array('spec_id'=>$val['spec_id'], 'spec_name'=>$val['spec_name'], 'active'=> (isset($color_name) && $val['color_name']== $color_name ));
            }
        }

        return $result;
      }

    /**
     * ��ȡ��Ʒ���й�񣬼�¼�����棬���ҹ��idΪ��ֵ
     *
     * @access  public
     * @param    void
     *
     * @return array
     */
    function get_cache_spec()
    {
        static $cache = null;
        if (!isset($cache[$this->_id]))
        {
            $all_spec = $this->get_spec();
            $spec = array();
            foreach ($all_spec as $row)
            {
                $spec[$row['spec_id']] = $row;
            }
            $cache[$this->_id] = $spec;
        }

        return $cache[$this->_id];
    }

    /**
     * �����Ʒ�����й��
     *
     * @access  public
     * @param   void
     *
     * @return array
     */
    function get_spec ()
    {
        $sql = "SELECT * FROM `ecm_goods_spec` WHERE goods_id ='{$this->_id}' ORDER BY color_name, spec_name, sort_order";

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * ���µ������
     *
     * @access  public
     * @param   int      $count  ���ӵĵ������
     *
     * @return void
     */
    function update_click($count = 1)
    {
        $count = intval($count);
        $sql = "UPDATE {$this->_table} SET click_count = click_count + $count WHERE goods_id='{$this->_id}'";
        $GLOBALS['db']->query($sql);
    }

    /**
     * ������Ʒ������
     *
     * @param int num
     * @return void
     */
    function update_sales_volume($num)
    {
        $num = intval($num);
        $sql = "UPDATE {$this->_table} SET sales_volume=sales_volume+'$num' WHERE goods_id='{$this->_id}'";
        $GLOBALS['db']->query($sql);
    }

    /**
     * ɾ��һ������
     *
     * @access  public
     * @param   int     $spec_id ����id
     *
     * @return int
     */
    function drop_spec ($spec_id)
    {
        /* ���Ȩ�� */
        if ($this->_store_id > 0)
        {
            $sql = "SELECT g.store_id ".
                   " FROM `ecm_goods_spec` AS s".
                   " LEFT JOIN `ecm_goods` AS g ON g.goods_id = s.goods_id ".
                   " WHERE spec_id = '$spec_id'";
            $store_id = $GLOBALS['db']->getOne($sql);
            if ($store_id != $this->_store_id)
            {
                //��֤Ȩ��
                $this->err = 'No permission to drop! The goods is not belong to your store';

                return 0;
            }
        }

        $sql = "DELETE FROM `ecm_goods_spec` WHERE spec_id='$spec_id' AND goods_id='{$this->_id}'";
        $GLOBALS['db']->query($sql);
        return $GLOBALS['db']->affected_rows();
    }

   /**
    * ɾ��һ��ͼƬ
    *
    * @access  public
    * @param    int     $image_id   ��ƷͼƬid
    *
    * @return bool
    */
    function drop_image ($image_id)
    {
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $thumb = new FileManager($this->_store_id);
        $thumb->item_id = $this->_id;
        $result = $thumb->drop_by_ids($image_id);
        if (!$result)
        {
            $this->err[] = $thumb->err;
        }

        return $result;
    }

    /**
     * ɾ������Ʒ
     *
     * @access  public
     * @param   void
     *
     * @return boolen
     */
    function drop ()
    {
        /* ���Ȩ�� */
        if ($this->_store_id > 0)
        {
            $sql = "SELECT store_id FROM `ecm_goods` WHERE goods_id = '{$this->_id}'";
            $store_id = $GLOBALS['db']->getOne($sql);
            if (empty($store_id))
            {
                $this->err = "No permission to drop! The goods is not belong to your store!";

                return 0;
            }
        }

        include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
        $mng = new GoodsManager($this->_store_id);
        $mng->drop($this->_id);
        $this->err = $mng->err;

        return $mng->drop($this->_id);
    }

    /**
    *  ��ȡ������Ʒ
    *
    * @access   public
    * @param    stirng      $type   'c':������Ʒ��'s':������Ʒ
    * @param    bool        $full   �Ƿ�ȫ����Ϣ
    *
    * @return array $list
    */
   function get_related_goods($type, $full = true)
   {
       static $related_goods = null;
       if ($related_goods === null)
       {
            if ($full)
            {
               $sql = "SELECT r.related_goods_id, r.relation, g.*, s.* ".
                      " FROM `ecm_related_goods` AS r".
                      " LEFT JOIN `ecm_goods` AS g ON r.related_goods_id=g.goods_id".
                      " LEFT JOIN `ecm_goods_spec` as s ON g.default_spec=s.spec_id ".
                      " WHERE r.goods_id='{$this->_id}'".
                      " ORDER BY r.relation, r.sort_order";
            }
            else
            {
               $sql = "SELECT r.related_goods_id AS id, g.goods_name AS name, r.relation ".
                      " FROM `ecm_related_goods` AS r".
                      " LEFT JOIN `ecm_goods` AS g ON r.related_goods_id=g.goods_id ".
                      " WHERE r.goods_id='{$this->_id}'".
                      " ORDER BY r.relation, r.sort_order";
            }
           $res = $GLOBALS['db']->query($sql);
           $related_goods = array('c'=>array(), 's'=>array());
           while ($row = $GLOBALS['db']->fetchRow($res))
           {
               $related_goods[$row['relation']][] = $row;
           }
       }

       return $related_goods[$type];
   }

   /**
    * ��ȡָ�������
    *
    * @author   wj
    * @param    int     $spec_id    ��Ʒspec_id
    *
    * @return int
    */
   function get_stock ($spec_id)
   {
        if ($spec_id == 0)
        {
            $goods_info = $this->get_info();
            $spec_id = $goods_info['spec_id'];
        }

        $spec_info = $this->get_spec_info($spec_id);

        return intval($spec_info['stock']);
   }

   /**
    * ����ָ�������
    *
    * @access  public
    * @param    int     $spec_id        ���id
    * @param    int     $count          Ҫ�����Ŀ����
    *
    * @return void
    */
   function set_stock($spec_id, $count)
   {
       $count = intval($count);
       $sql = "UPDATE `ecm_goods_spec` SET stock = stock + $count WHERE spec_id = '$spec_id' ";

       return $GLOBALS['db']->query($sql);
   }


    /**
     * ��������
     *
     * @param    int     $rgoods_id  ������Ʒid
     * @param    string  $type       ���ͣ�s���� c����
     */
    function set_related($rgoods_id, $type = 's')
    {
        $type = strtolower($type);
        if ($type != 's')
        {
            $type = 'c';
        }

        $mod = new Goods($rgoods_id, $this->_store_id);
        $rgoods = $mod->get_info();
        if ($rgoods)
        {
            $arr = array(
                'goods_id'          => $this->_id,
                'relation'          => $type,
                'related_goods_id'  => $rgoods_id,
                'sort_order'        => 0
            );
            $GLOBALS['db']->autoExecute('`ecm_related_goods`', $arr, 'INSERT');

            $arr = array(
                'goods_id'          => $rgoods_id,
                'relation'          => $type,
                'related_goods_id'  => $this->_id,
                'sort_order'        => 0
            );
            $GLOBALS['db']->autoExecute('`ecm_related_goods`', $arr, 'INSERT');
        }
    }

    /**
     * �������
     *
     * @param    int     $rgoods_id  ������Ʒid
     * @param    string  $type       ���ͣ�s���� c����
     */
    function set_unrelated($rgoods_id, $type = 's')
    {
        $type = strtolower($type);
        if ($type != 's')
        {
            $type = 'c';
        }

        $mod = new Goods($rgoods_id, $this->_store_id);
        $rgoods = $mod->get_info();
        if ($rgoods)
        {
            $sql = "DELETE FROM `ecm_related_goods` " .
                    "WHERE goods_id = '" . $this->_id . "' " .
                    "AND relation = '$type' " .
                    "AND related_goods_id = '$rgoods_id'";
            $GLOBALS['db']->query($sql);

            $sql = "DELETE FROM `ecm_related_goods` " .
                    "WHERE goods_id = '" . $rgoods_id . "' " .
                    "AND relation = '$type' " .
                    "AND related_goods_id = '" . $this->_id . "'";
            $GLOBALS['db']->query($sql);
        }
    }

    /**
     * ȡ�ù��������Ʒ���û�
     *
     *  @author Garbin
     *  @param  $limit
     *  @return   array   user_id����
     */
    function get_users_who_bought ($limit = 0)
    {
        $sql = "SELECT oi.user_id, u.user_name, oi.add_time, oi.is_anonymous " .
                "FROM `ecm_order_info` AS oi, `ecm_order_goods` AS og, `ecm_users` AS u " .
                "WHERE oi.order_id = og.order_id ANd oi.user_id = u.user_id " .
                " AND og.goods_id = '" . $this->_id . "' AND oi.user_id > 0";
        if ($limit > 0) $sql .= " LIMIT " . $limit;
        $res = $GLOBALS['db']->query($sql);
        $list = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $list[$row['user_id']] = $row;
        }

        return $list;
    }

    /**
     * ��ȡ������ʷ
     *
     * @return array
     */
     function get_bought_history()
     {
        $sql = "SELECT og.goods_name, og.goods_price, og.goods_number, oi.add_time, oi.is_anonymous, u.user_id, u.user_name ".
               " FROM `ecm_order_goods` AS og ".
               " LEFT JOIN `ecm_order_info` AS oi ON og.order_id=oi.order_id ".
               " LEFT JOIN `ecm_users` AS u ON oi.user_id=u.user_id ".
               " WHERE og.goods_id={$this->_id} AND oi.order_status " . db_create_in(array(ORDER_STATUS_SHIPPED, ORDER_STATUS_DELIVERED)) .
               " Order By oi.add_time DESC LIMIT 20 ";

        return $GLOBALS['db']->getAll($sql);
     }

     /**
      * ��ָ��spec_id����Ϣ
      *
      * @author wj
      * @param int  $spec_id     ��Ʒspec_id
      * @return  array
      */
     function get_spec_info($spec_id)
     {
        static $specs_info = null;

        if (!isset($specs_info[$spec_id]))
        {
            $sql = "SELECT * FROM `ecm_goods_spec` WHERE spec_id='$spec_id'";
            $specs_info[$spec_id] = $GLOBALS['db']->getRow($sql);
        }

        return $specs_info[$spec_id];
     }

     /**
      * ������Ʒ����
      */
     function update($arr)
     {
         
         if (parent::update($arr))
         {
             $info = $this->get_info();
             include_once(ROOT_PATH . '/includes/manager/mng.goods.php');
             $mng_goods = new GoodsManager($info['store_id']);
             $mng_goods->update_goods_count();

             return true;
         }
         else
         {
             return false;
         }
     }
}

//goods ������
class GoodsFactory
{
    /**
     *  ���ݹ��id����һ��goods����
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function build ($spec_id, $store_id=0)
    {
        $sql = "SELECT goods_id FROM `ecm_goods_spec` WHERE spec_id='$spec_id'";
        $goods_id = intval($GLOBALS['db']->getOne($sql));
        if ($goods_id <= 0)
        {
            $this->err = "spec id [$spec_id] is not exist!";

            return null;
        }
        return new Goods($goods_id, $store_id);
    }
}
?>
