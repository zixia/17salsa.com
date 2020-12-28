<?php

/**
 * ECMALL: ����ģ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.category.php 6051 2008-11-12 09:27:51Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class Category extends Model
{
    var $tree = null;
    var $cache_path = null;

    /**
     * ���캯��
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function __construct ($cate_id =0, $store_id=0)
    {
        $this->Category($cate_id, $store_id);
    }

    /**
     * ���캯��
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function Category($cate_id = 0, $store_id=0)
    {
        $this->_table = '`ecm_category`';
        $this->_key = 'cate_id';
        $this->cache_path = ROOT_PATH . '/temp/query_caches/cache_category.php';
        parent::Model($cate_id, $store_id);
    }

    /**
     * ������Ʒ����
     *
     * @author liupeng
     *
     * @return void
     */
    function update_goods_count()
    {
        $field = 'mall_cate_id';
        $where = '';
        if ($this->_store_id > 0)
        {
            $field = 'store_cate_id';
            $wehre = "WHERE store_id = '$this->_store_id'";
        }
        $sql = "SELECT $field, count(*) AS goods_num FROM `ecm_goods` $where GROUP BY $field";

        $results = $GLOBALS['db']->query($sql);

        $data = array();
        while (($item = $GLOBALS['db']->fetchRow($results)))
        {
            $data[$item[$field]] = $item['goods_num'];
        }

        $this->_init_tree();
        foreach($this->tree AS $key => $value)
        {
            $data[$key] = isset($data[$key]) ? $data[$key] : 0;
            if ($value['goods_count'] != $data[$key])
            {
                $sql = "UPDATE `ecm_category` SET goods_count='{$data[$key]}' WHERE cate_id='$key'";

                $GLOBALS['db']->query($sql);
            }
        }
        $this->_clean_cache();
    }


    /**
     * ��ȡָ�������ӷ���Array(cate_id=>array(cate_name, level, ����)
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function list_child ($level=0)
    {
        $list = $this->_get_child($level);

        return $list;
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_options ($level=0)
    {
        $arr = $this->list_child($level);
        foreach ($arr as $val)
        {
             $options[$val['cate_id']] = str_repeat('&nbsp;&nbsp;', $val['level']) . $val['cate_name'];
        }
        return $options;
    }

    /**
     * �������������ķ�������
     *
     * @param int $level ����ļ���
     * @return array
     */
    function get_index($level  =0)
    {
        $arr = $this->list_child($level);
        $index = array();
        foreach ($arr as $val)
        {
            $index[$val['parent_id']][] = array('cate_id'=>$val['cate_id'], 'cate_name'=>$val['cate_name'], 'dir'=>$val['dir'],'level'=>$val['level']);
        }

        return $index;
    }
    /**
     * ��ȡ����Ļ�����Ϣ
     *
     * @access  public
     * @param
     *     * @return void
     */
    function get_info()
    {
        if ($this->_id <= 0)
        {
            $this->err = "cate_id [" . $this->_id . "] is not support the method!";

            return array();
        }

        if ($this->tree === null)
        {
            $this->_init_tree();
        }

        if (isset($this->tree[$this->_id]))
        {
            return $this->tree[$this->_id];
        }
        else
        {
            $this->err = 'cate_id [' . $this->_id . '] is not found';
            return array();
        }
    }

    /**
     * ��ȡ��ǰ���༰���ӷ������id������
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function list_child_id ($level = 0)
    {
        return array_keys($this->_get_child($level));
    }

    /**
     * ���һ������
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function add ($data)
    {
        $data['store_id'] = $this->_store_id;
        if ($data['cate_id'])
        {
            unset($data['cate_id']);
        }
        $GLOBALS['db']->autoExecute('`ecm_category`', $data, 'INSERT');
        $this->_clean_cache();

        return $GLOBALS['db']->insert_id();
    }

    /**
     * ���·���
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function update ($data)
    {
        if ($data['store_id'])
        {
            unset($data['store_id']);
        }
        if ($data['cate_id'])
        {
            unset($data['cate_id']);
        }

        if (isset($data['parent_id']))
        {
            $list = $this->list_child_id();

            if (in_array($data['parent_id'], $list))
            {
                $this->err[] = 'e_parent_id_invalid';
                return false;
            }
        };

        $GLOBALS['db']->autoExecute('`ecm_category`', $data, 'UPDATE', "cate_id = '{$this->_id}' AND store_id='{$this->_store_id}'");
        $this->_clean_cache();

        return true;
    }

    /**
     * ��ȡ������Ʒ��
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function list_brand ()
    {
        return array(array('brand_id'=>1, 'brand_name'=>'Nokia'), array('brand_id'=>1, 'brand_name'=>'Motorala'));
    }

    /**
    * ��ȡ��Ʒ�������и���������ϸ��Ϣ����ά���飬��������
    *
    * @access  public
    * @param
    *
    * @return void
    */
   function list_parent ()
   {
       if ($this->_id == 0)
       {
           return array();
       }
       if (!$this->tree)
       {
           $this->_init_tree();
       }
       if ($this->tree[$this->_id]['level'] == 1)
       {
           return array();
       }
       $data = explode('/', $this->tree[$this->_id]['dir']);
       $result = array();
       for ($i=1; $i<count($data);$i++)
       {
           $result[] = $this->tree[$data[$i]];
       }
       return $result;
   }

   /**
    * �жϷ����Ƿ�����
    * @param void
    *
    * @return void
    */
   function cate_name_exist($cate_name, $parent_id=null)
   {
       if (isset($parent_id))
       {
            $parent_id = intval($parent_id);
            $sql = "SELECT COUNT(*) FROM `ecm_category` WHERE parent_id='$parent_id' AND cate_name='$cate_name' AND store_id='{$this->_store_id}'";
       }
       else
       {
           $info = $this->get_info();
           $sql = "SELECT COUNT(*) FROM `ecm_category` WHERE parent_id='" .$info['parent_id']. "' AND cate_name='$cate_name' AND cate_id!='{$this->_id}' AND store_id='{$this->_store_id}'";
       }

       return $GLOBALS['db']->getOne($sql);
   }

    /**
    * ɾ��һ������
    *
    * @access  public
    * @param
    *
    * @return void
    */
   function drop ()
   {
       $sql = "SELECT COUNT(*) FROM `ecm_category` WHERE parent_id ='{$this->_id}'";
       if ($GLOBALS['db']->getOne($sql) > 0)
       {
           $this->err[] = 'e_is_not_empty';

           return false;
       }
       // �����Ʒ
       $sql = "SELECT COUNT(*) FROM `ecm_goods` WHERE mall_cate_id = '{$this->_id}' OR store_cate_id = '{$this->_id}'";
       if ($GLOBALS['db']->getOne($sql) > 0)
       {
           $this->err[] = 'e_is_not_empty';

           return;
       }

       $sql = "DELETE FROM `ecm_category` WHERE cate_id='{$this->_id}'" .  $this->_get_store_limit();
       $GLOBALS['db']->query($sql);
       if ($GLOBALS['db']->affected_rows() > 0)
       {
           $this->_clean_cache();

           return true;
       }
       else
       {
           # code...
           $this->err[] = 'no_such_category';

           return false;
       }
   }

   /**
   * ��ȡָ������ķ�������
   *
   * @access  public
   * @param
   *
   * @return void
   */
  function _get_child ($level)
  {
      if ($this->tree === null){$this->_init_tree();}
      if ($this->_id == 0 && $level == 0)
      {
          return $this->tree;
      }

      if ($this->_id == 0)
      {
          $param = array('cate_id'=>0, 'level'=>0, 'dir'=>'0');
      }
      else
      {
          $param = $this->tree[$this->_id];
      }
      $param['add_level'] = $level;
      category_filter(array(),$param);
      return  array_filter($this->tree, "category_filter");
  }

   /**
    * ��ʼ����Ʒ����������
    *
    * @author   wj
    * @param    void
    *
    * @return void
    */
    function _init_tree ()
    {
        static $cate_data = null;
        if (isset($cate_data[$this->_store_id]))
        {
            $this->tree = &$cate_data[$this->_store_id];
        }
        else
        {
            if ($this->_store_id == 0 && is_file($this->cache_path) && ((filemtime($this->cache_path) + 3600) > time()))
            {
                //��ȡ����
                include($this->cache_path);
            }
            else
            {
                $sql = "SELECT * FROM `ecm_category` WHERE store_id='{$this->_store_id}'";
                $arr = $GLOBALS['db']->getAll($sql);
                $count = count($arr);
                $data = array();
                for($i=0; $i < $count; $i++)
                {
                    $data[$arr[$i]['cate_id']] = $arr[$i];
                }
                foreach ($data as $val)
                {
                    $this->_build($val['cate_id'], $data);
                }
                //����
                category_sort(array(), array(), $data);
                uasort($data, "category_sort");

                $this->tree = $data;
                if ($this->_store_id == 0)
                {
                    //д����
                    file_put_contents($this->cache_path, "<?php\n\$this->tree=" . var_export($this->tree, true) . ";\n?>", LOCK_EX);
                }
            }
            $cate_data[$this->_store_id] = $this->tree;
        }
   }

   /**
    * �������
    *
    * @return void
    */
   function _clean_cache()
   {
       if (is_file($this->cache_path))
       {
           unlink($this->cache_path);
       }
   }

    /**
    * ������༶����
    *
    * @access  public
    * @param
    *
    * @return void
    */
    function _build ($cate_id, &$data)
    {
        if (!isset($data[$cate_id])) return;
        //������
        if ($data[$cate_id]['parent_id'] == $cate_id)
        {
            $this->err = 'The category "' . $data[$cate_id]['cate_name'] . '" is invalid! It\'s cate_id eq parent_id';
            return ;
        }

        if (!isset($data[$cate_id]['child'])) $data[$cate_id]['child'] = 0;
        if (isset($data[$cate_id]['total']))
        {
            $data[$cate_id]['total'] += $data[$cate_id]['goods_count'];
        }
        else
        {
            $data[$cate_id]['total'] = $data[$cate_id]['goods_count'];
        }

        $parents = array();
        $parent_id = $data[$cate_id]['parent_id'];
        $total = $data[$cate_id]['goods_count'];
        $level = 1;

        while (isset($data[$parent_id]) && (!in_array($parent_id, $parents)))
        {
            if (isset($data[$parent_id]['child']))
            {
                $data[$parent_id]['child'] ++;
            }
            else
            {
                $data[$parent_id]['child'] = 1;
            }

            if (isset($data[$parent_id]['total']))
            {
                $data[$parent_id]['total'] += $total;
            }
            else
            {
                $data[$parent_id]['total'] = $total;
            }

            $parents[] = $parent_id;
            $level ++;
            $parent_id = $data[$parent_id]['parent_id'];
        }
        $parents[] = 0;
        $parents = array_reverse($parents);
        $data[$cate_id]['dir'] = implode('/', $parents);
        $data[$cate_id]['level'] = $level;
    }

    /**
    * �޸ķ�������Ʒ����
    *
    * @author scottye
    * @param
    *
    * @return void
    */
    function alter_goods_num($goods_num, $cate_id=0)
    {
        $cate_id = $cate_id > 0 ? intval($cate_id) : $this->_id;

        $goods_num = intval($goods_num);
        if ($goods_num > 0)
        {
            $sql = "UPDATE {$this->_table} SET goods_count = goods_count + {$goods_num} WHERE cate_id = '$cate_id'";
        }
        else
        {
            $goods_num = 0 - $goods_num;
            $sql = "UPDATE {$this->_table} SET goods_count = IF(goods_count > $goods_num, goods_count - $goods_num, 0) WHERE cate_id = '$cate_id'";
        }

        $GLOBALS['db']->query($sql);

        $this->_clean_cache();
    }
}

/**
 * ����������
 *
 * @access  public
 * @param
 *
 * @return void
 */
function category_sort ($a, $b, $add_data=null)
{
    static $data = null;
    if (isset($add_data))
    {
        $data = $add_data;
        return true;
    }

    if ($a['dir'] == $b['dir'])
    {
        if ($a['sort_order'] == $b['sort_order'])
        {
            return $a['cate_id'] < $b['cate_id'] ? -1 : 1;
        }
        return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
    }
    else
    {
        $count = min($a['level'], $b['level']);
        $tmp_a = explode('/', $a['dir']);
        $tmp_b = explode('/', $b['dir']);

        for ($i=0; $i< $count; $i++)
        {
            if ($tmp_a[$i] != $tmp_b[$i])
            {
                if ($data[$tmp_a[$i]]['sort_order'] == $data[$tmp_b[$i]]['sort_order'])
                {
                    return $data[$tmp_a[$i]]['cate_id'] < $data[$tmp_b[$i]]['cate_id'] ? -1 : 1;
                }
                return $data[$tmp_a[$i]]['sort_order'] < $data[$tmp_b[$i]]['sort_order'] ? -1 : 1;
            }
        }
        if ($a['level'] < $b['level'])
        {
            if ($tmp_b[$count] == $a['cate_id'])
            {
                return -1;
            }
            if ($data[$a['cate_id']]['sort_order'] == $data[$tmp_b[$count]]['sort_order'])
            {
                return  $a['cate_id'] < $tmp_b[$count] ? -1 : 1;
            }
            return $data[$a['cate_id']]['sort_order'] < $data[$tmp_b[$count]]['sort_order'] ? -1 : 1;
        }
        else
        {
            if ($tmp_a[$count] == $b['cate_id'])
            {
                return 1;
            }
            if ($data[$b['cate_id']]['sort_order'] == $data[$tmp_a[$count]]['sort_order'])
            {
                return  $tmp_a[$count] < $b['cate_id'] ? -1 : 1;
            }
            return $data[$tmp_a[$count]]['sort_order'] < $data[$b['cate_id']]['sort_order'] ? -1 : 1;
        }
    }
}

/**
 * ������˺���
 *
 * @access  public
 * @param
 *
 * @return void
 */
function category_filter ($val, $add_param=null)
{
    static $param = 0;
    if (isset($add_param))
    {
        $param = $add_param;
        if ($param['cate_id'] > 0)
        {
           $param['parent_dir'] = $param['dir'] . '/' . $param['cate_id'];
           $param['parent_dir_ext'] = $param['dir'] . '/' . $param['cate_id'] . '/';
        }
        else
        {
            $param['parent_dir'] = '0';
            $param['parent_dir_ext'] = '0/';
        }
        $param['last_level'] =  $param['level'] + $param['add_level'];
        $param['parent_dir_ext_length'] = strlen($param['parent_dir_ext']);

        return true;
    }
    if ($val['cate_id'] == $param['cate_id'])
    {
        return true;
    }

    if ($val['level'] > $param['level'])
    {
        if ($param['add_level'] > 0 && ($val['level'] > $param['last_level']))
        {
            return false;
        }
        if ($val['dir'] == $param['parent_dir'] ||  strncmp($val['dir'], $param['parent_dir_ext'], $param['parent_dir_ext_length']) == 0)
        {
            return true;
        }
        return false;
    }

}
?>
