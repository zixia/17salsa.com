<?php

/**
 * ECMALL: ���ͷ�ʽ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id$
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class ShippingManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->ShippingManager($store_id);
    }

    function ShippingManager($store_id)
    {
        parent::Manager();
        $this->_store_id = intval($store_id);
        if ($this->_store_id)
        {
            $this->_store_limit = ' AND s.store_id=' . $this->_store_id;
        }
    }

    /**
     *  ����һ�����ͷ�ʽ
     *
     *  @param  $data ����
     *  @return int
     */
    function add($data)
    {
        $data['store_id'] = $this->_store_id;
        $data = $this->filter($data);
        if (!$data)
        {
            return FALSE;
        }
        $fields = $this->get_set_fields($data);
        $sql = "INSERT INTO `ecm_shipping` SET {$fields}";
        $GLOBALS['db']->query($sql);

        $id = $GLOBALS['db']->insert_id();

        return $id;
    }

    /**
     *  �޸�һ�����ͷ�ʽ
     *
     *  @param  array $data Ҫ�޸ĵ�����
     *  @return bool
     */
    function update($data, $shipping_id)
    {
        $data = $this->filter($data, $shipping_id);
        if (!$data)
        {
            return FALSE;
        }

        $fields = $this->get_set_fields($data);

        $sql = "UPDATE `ecm_shipping` s SET {$fields} WHERE s.shipping_id={$shipping_id}{$this->_store_limit}";
        if (!$GLOBALS['db']->query($sql))
        {
            $this->err = 'record_not_exist';

            return FALSE;
        }
        $result = $GLOBALS['db']->affected_rows();

        return $result;
    }

    /**
     *  ��ȡ���ͷ�ʽ�б�
     *
     *  @param  none
     *  @return array
     */
    function get_list()
    {
        $sql = "SELECT * FROM `ecm_shipping` s WHERE 1{$this->_store_limit}";
        $shipping_list= $GLOBALS['db']->getAll($sql);

        return array('data' => $shipping_list,
                     'info' => array('rec_count' => count($shipping_list)));
    }

    /**
     *  ��ȡ�����õ����ͷ�ʽ����
     *
     *  @param  none
     *  @return int
     */
    function get_enabled()
    {
        $sql = "SELECT * FROM `ecm_shipping` s WHERE enabled=1{$this->_store_limit}";
        $list = $GLOBALS['db']->getAll($sql);

        return array('data' => $list,
                     'info' => array(
                                'rec_count' => count($list)
                        ));
    }

    /**
     *  ɾ�����ͷ�ʽ
     *
     *  @param  $id     ��ɾ�������ͷ�ʽID
     *  @return int
     */
    function drop($id)
    {
        include_once(ROOT_PATH . '/includes/models/mod.shipping.php');
        $shipping = new Shipping($id, $this->_store_id);

        return $shipping->drop();
    }

    /**
     *  ������ӵ����ͷ�ʽ����Ϣ
     *
     *  @param  $data   ����
     *  @return array   �������������
     */
    function filter($data, $shipping_id = 0)
    {
        if($data)foreach ($data as $_k => $_v)
        {
            switch ($_k)
            {
            case 'store_id':
                $_v = intval($_v);
                break;
            case 'shipping_name':
                if (!$_v)
                {
                    $this->err = 'name_is_empty';

                    return FALSE;
                }
                if (strlen($_v) > 120)
                {
                    $this->err = 'name_is_too_long';

                    return FALSE;
                }
                if ($this->_exists($_v, $shipping_id))
                {
                    $this->err = 'name_exists';

                    return FALSE;
                }
                $_v = trim($_v);
                break;
            case 'shipping_desc':
                if (strlen($_v) > 500)
                {
                    $this->err = 'desc_is_too_long';

                    return FALSE;
                }
                $_v = trim($_v);
                break;
            case 'cod_regions':
                array_walk($_v, create_function('&$item, $key', '$item = intval($item);'));
                $_v = implode(',', $_v);
                break;
            case 'shipping_fee':
            case 'surcharge':
                $_v = floatval($_v);
                break;
            case 'enabled':
                $_v = intval($_v) > 0 ? 1 : 0;
                break;
            default:
                unset($data[$_k]);
                break;
            }
            isset($data[$_k]) && $data[$_k] = $_v;
        }

        return $data;
    }

    /**
     *  ����Ҫ������ݵ�SQL�ַ���
     *
     *  @param  array $data ����
     *  @return string
     */
    function get_set_fields($data)
    {
        if ($data)
        {
            $arr = array();
            foreach ($data as $_k => $_v)
            {
                $arr[] = "{$_k}='{$_v}'";
            }

            return implode(',', $arr);
        }

        return '';
    }

    /**
     *    ����������Ƿ����
     *
     *    @author    Garbin
     *    @param     string $name
     *    @return    bool
     */
    function _exists($name, $shipping_id = 0)
    {
        if (!$name)
        {
            return false;
        }
        if (!$shipping_id)
        {
            $sql = "SELECT s.shipping_id FROM `ecm_shipping` s WHERE s.shipping_name='{$name}'{$this->_store_limit}";

            return $GLOBALS['db']->getOne($sql);
        }
        else
        {
            $sql = "SELECT s.shipping_name FROM `ecm_shipping` s WHERE s.shipping_id={$shipping_id}{$this->_store_limit}";
            if ($GLOBALS['db']->getOne($sql) == $name)
            {
                return 0;
            }
            else
            {
                return $this->_exists($name, 0);
            }
        }
    }
}

?>
