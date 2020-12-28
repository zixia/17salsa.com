<?php

/**
 * ECMALL: �ļ�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: cls.filecache.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class filecache
{
    var $cache_dir = null; //�����ļ�Ŀ¼
    var $expiry = null; //�������ʱ��

    /**
     * ���캯��
     *
     * @author  wj
     * @param  string       $cache_dir      //�����ļ�Ŀ¼
     * @param  int          $expiry         //����ʱ��,Ĭ��һ��Сʱ
     *
     * @return  void
     */
    function __construct($cache_dir, $expiry = 3600)
    {
        $this->filecache($cache_dir, $expiry);
    }

    /**
     * ���캯��
     *
     * @author  wj
     * @param  string       $cache_dir      //�����ļ�Ŀ¼
     * @param  int          $expiry         //����ʱ��,Ĭ��һ��Сʱ
     *
     * @return  void
     */
    function filecache($cache_dir, $expiry=3600)
    {
        $this->cache_dir = ROOT_PATH . '/temp/' . $cache_dir;
        $this->expiry = $expiry;
    }

    /**
     * ��ȡָ��key������
     *
     * @author  wj
     * @param  string       $key      //���ݼ�ֵ
     *
     * @return  mix
     */
    function get($key)
    {
        $result = false;
        $hash_key = $this->_get_hash_key($key);
        $file_name = $this->cache_dir . '/' . $hash_key . '.php';
        if (is_file($file_name) && ($data = file_get_contents($file_name)) && isset($data{23}))
        {
            $filetime = substr($data, 13, 10);
            $data     = substr($data, 23);

            if ($filetime < (time() - $this->expiry))
            {
                unlink($file_name); //�������
            }
            else
            {
                $result = unserialize($data);
            }
        }

        return $result;
    }

    /**
     * ���캯��
     *
     * @author  wj
     * @param  string       $key      ����keyֵ
     * @param  mix          $data     ��������
     *
     * @return  void
     */
    function set($key, $data)
    {
        $hash_key = $this->_get_hash_key($key);
        $file_name = $this->cache_dir . '/' . $hash_key . '.php';
        file_put_contents($file_name, '<?php exit;?>' . time() . serialize($data)); //д�뻺��
        clearstatcache();
    }

    /**
     * Ϊkeyֵ����Ψһ������
     *
     * @author  wj
     * @param  string       $key        keyֵ
     *
     * @return  string      Ψһ����
     */
    function _get_hash_key($key)
    {
        return abs(crc32($key)) . '_' .md5($key);
    }

}
?>