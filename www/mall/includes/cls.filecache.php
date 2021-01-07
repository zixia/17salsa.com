<?php

/**
 * ECMALL: 文件缓存
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: cls.filecache.php 6009 2008-10-31 01:55:52Z Garbin $
 */

class filecache
{
    var $cache_dir = null; //缓存文件目录
    var $expiry = null; //缓存过期时间

    /**
     * 构造函数
     *
     * @author  wj
     * @param  string       $cache_dir      //缓存文件目录
     * @param  int          $expiry         //缓存时间,默认一个小时
     *
     * @return  void
     */
    function __construct($cache_dir, $expiry = 3600)
    {
        $this->filecache($cache_dir, $expiry);
    }

    /**
     * 构造函数
     *
     * @author  wj
     * @param  string       $cache_dir      //缓存文件目录
     * @param  int          $expiry         //缓存时间,默认一个小时
     *
     * @return  void
     */
    function filecache($cache_dir, $expiry=3600)
    {
        $this->cache_dir = ROOT_PATH . '/temp/' . $cache_dir;
        $this->expiry = $expiry;
    }

    /**
     * 获取指定key的数据
     *
     * @author  wj
     * @param  string       $key      //数据键值
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
                unlink($file_name); //清除缓存
            }
            else
            {
                $result = unserialize($data);
            }
        }

        return $result;
    }

    /**
     * 构造函数
     *
     * @author  wj
     * @param  string       $key      缓存key值
     * @param  mix          $data     缓存数据
     *
     * @return  void
     */
    function set($key, $data)
    {
        $hash_key = $this->_get_hash_key($key);
        $file_name = $this->cache_dir . '/' . $hash_key . '.php';
        file_put_contents($file_name, '<?php exit;?>' . time() . serialize($data)); //写入缓存
        clearstatcache();
    }

    /**
     * 为key值生成唯一的索引
     *
     * @author  wj
     * @param  string       $key        key值
     *
     * @return  string      唯一索引
     */
    function _get_hash_key($key)
    {
        return abs(crc32($key)) . '_' .md5($key);
    }

}
?>