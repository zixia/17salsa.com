<?php

/**
 * ECMALL: ֧����ʽ������
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

class PaymentManager extends Manager
{
    var $_store_id = 0;

    function __construct($store_id)
    {
        $this->PaymentManager($store_id);
    }

    function PaymentManager($store_id)
    {
        parent::Manager();
        $this->_store_id = intval($store_id);
    }

    /**
     * ȡ���б�
     *
     * @return  array
     */
    function get_list()
    {
        /* �Ѱ�װ */
        $installed = $this->get_installed();

        /* δ��װ */
        $uninstalled = $this->get_built_in();
        foreach ($uninstalled as $code => $payment)
        {
            if (isset($installed[$code]))
            {
                unset($uninstalled[$code]);
            }
        }

        /* ���е� */
        $all = array_merge($installed, $uninstalled);

        return array('data' => $all, 'info' => array('rec_count' => count($all), 'installed' => count($installed)));
    }

    /**
     * ȡ���Ѱ�װ��֧����ʽ
     *
     * @param   string      $code   ֧������
     * @return  array       �����֧�����룬���ظ�֧����Ϣ�����򣬷�������֧����Ϣ
     */
    function get_installed($code = '')
    {
        if (empty($code))
        {
            $installed = array();
            $sql = "SELECT * FROM `ecm_payment` " .
                    "WHERE store_id = '" . $this->_store_id . "' " .
                    "AND enabled = 1 " .
                    "ORDER BY sort_order";
            $res = $GLOBALS['db']->query($sql);
            while ($row = $GLOBALS['db']->fetchRow($res))
            {
                $installed[$row['pay_code']] = $row;
            }


            return $installed;
        }
        else
        {
            $sql = "SELECT * FROM `ecm_payment` " .
                    "WHERE pay_code = '$code' " .
                    "AND store_id = '" . $this->_store_id . "' " .
                    "AND enabled = 1 ";

            return $GLOBALS['db']->getRow($sql);
        }
    }

    /**
     * ȡ�����õ�֧����ʽ
     *
     * @param   string      $code   ֧������
     * @return  array       �����֧�����룬���ظ�֧����Ϣ�����򣬷�������֧����Ϣ
     */
    function get_built_in($code = '')
    {
        $built_in = array();
        $modules = read_modules(ROOT_PATH . '/includes/payment');
        foreach ($modules as $payment)
        {
            if (is_array($payment['currency']) && !in_array(CURRENCY, $payment['currency']))
            {
                continue;
            }
            elseif (is_string($payment['currency']) && $payment['currency'] != 'all')
            {
                continue;
            }
            if (empty($code) || $code == $payment['code'])
            {

                $config = array();
                if (isset($payment['config']))
                {
                    foreach ($payment['config'] as $conf)
                    {
                        $name = $conf['name'];
                        $conf['name'] = Language::get($name);
                        if ($conf['type'] == 'select')
                        {
                            $conf['range'] = Language::get($name . '_range');
                        }
                        if (Language::get($name . '_desc', 0))
                        {
                        }
                        if ($conf['desc'])
                        {
                            $conf['desc'] = Language::get($name . '_desc');
                        }
                        $config[$name] = $conf;
                    }
                }
                $built_in[$payment['code']] = array(
                    'pay_id'    => 0,
                    'pay_code'  => $payment['code'],
                    'pay_name'  => Language::get($payment['code']),
                    'pay_desc'  => Language::get($payment['desc']),
                    'pay_fee'   => 0,
                    'config'    => $config,
                    'is_cod'    => $payment['is_cod'],
                    'is_online' => $payment['is_online'],
                    'enabled'   => 0,
                    'sort_order'=> '',
                    'author'    => $payment['author'],
                    'website'   => $payment['website'],
                    'version'   => $payment['version'],
                );
            }
        }

        if (empty($code))
        {
            return $built_in;
        }
        else
        {
            return empty($built_in[$code]) ? array() : $built_in[$code];
        }
    }

    /**
     * ��װ
     *
     *  @author Garbin
     *  @param   array       $data       ����
     *  @return  int
     */
    function add($data)
    {
        $data['config'] = serialize($data['config']);
        $sql = "SELECT pay_id FROM `ecm_payment` " .
                "WHERE store_id = '" . $this->_store_id . "' " .
                "AND pay_code = '" . $data['pay_code'] . "'";
        $id = $GLOBALS['db']->getOne($sql);
        if (empty($id))
        {
            $GLOBALS['db']->autoExecute('`ecm_payment`', $data, 'INSERT');

            return $GLOBALS['db']->insert_id();
        }
        else
        {
            $GLOBALS['db']->autoExecute('`ecm_payment`', $data, 'UPDATE', "pay_id = '$id'");

            return $id;
        }
    }

    /**
     * ȡ��id
     *
     * @param   string      $name   ����
     * @return  int         ����id��û�ҵ�����0
     */
    function get_id($name)
    {
        $sql = "SELECT pay_id FROM `ecm_payment` " .
                "WHERE pay_name = '$name' " .
                "AND store_id = '" . $this->_store_id . "' " .
                "AND enabled = 1";
        $id = $GLOBALS['db']->getOne($sql);

        return empty($id) ? 0 : $id;
    }

    /**
     * ȡ��֧����ʽ
     * @author  weberliu
     * @param   bool    $include_cod    �Ƿ������������
     * @param   bool    $include_offline�Ƿ��������֧��
     * @return  string
     */
    function get_payment_list($include_cod = true, $include_offline = true)
    {
        include(ROOT_PATH . "/includes/lib.editor.php");

        $sql = "SELECT * " .
                "FROM `ecm_payment` " .
                "WHERE store_id = '" . $this->_store_id . "' " .
                "AND enabled = 1 ";
        if (!$include_cod)
        {
            $sql .= "AND is_cod = 0 ";
        }
        if (!$include_offline)
        {
            $sql .= "AND is_online = 1 ";
        }
        $sql .= "ORDER BY sort_order ";
        $arr = $GLOBALS['db']->getAll($sql);

        foreach ($arr AS $key=>$val)
        {
            $arr[$key]['pay_desc'] = Editor::parse($val['pay_desc']);
        }

        return $arr;
    }

    /**
     *  �ж�$pay_code��֧����ʽ�Ƿ�����
     *
     *  @access public
     *  @params string $pay_code
     *  @return bool
     */

    function is_enabled($pay_code)
    {
        $sql = 'SELECT pay_id,store_id,pay_name ' .
               'FROM `ecm_payment` ' .
               "WHERE pay_code='{$pay_code}' AND enabled=1";
        $result = $GLOBALS['db']->getRow($sql);
        return $result['pay_id'] ? $result : FALSE;
    }
}
?>