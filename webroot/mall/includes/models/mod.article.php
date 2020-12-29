<?php

/**
 * ECMALL: 文章模型
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.article.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class Article extends Model
{
    /**
     * 构造函数
     *
     * @access  public
     * @param   int     article_id
     * @param   int     store_id
     * @param   string  code
     *
     * @return void
     */
    function __construct ($article_id, $store_id = 0, $code='')
    {
        $this->Article($article_id, $store_id, $code);
    }

    /**
     * 构造函数
     *
     * @access  public
     * @author  Weber Liu
     * @param   int     article_id
     * @param   int     store_id
     * @param   string  code
     *
     * @return void
     */
    function Article($article_id, $store_id = 0, $code='')
    {
        $this->_table = '`ecm_article`';
        $this->_key   = 'article_id';

        if (!empty($code))
        {
            $article_id = $this->get_id_by_code($code);
        }

        parent::Model($article_id, $store_id);
    }

    /**
    * 获取文章信息
    *
    * @access  public
    * @param
    *
    * @return void
    */
   function get_info()
   {
        $sql = "SELECT a.*, c.cate_name " .
                "FROM `ecm_article` AS a " .
                "   LEFT JOIN `ecm_article_cate` AS c ON a.cate_id = c.cate_id " .
                " WHERE a.article_id = " . $this->_id;
        if ($this->_store_id > 0)
        {
            $sql .= " AND a.store_id = " . $this->_store_id;
        }
        $res = $GLOBALS['db']->getRow($sql);
        return $res;
   }

   /**
    * 根据代码获得文章的ID
    *
    * @param  string    $code
    *
    * @return  int
    */
   function get_id_by_code($code)
   {
       $sql = "SELECT article_id FROM {$this->_table} WHERE code='$code' AND store_id=0";

       return $GLOBALS['db']->getOne($sql);
   }

    /**
     *    删除文章
     *
     *    @author    Garbin
     *    @return    bool
     */
    function drop()
    {
        $result = parent::drop();

        /* 删除附件 */
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $mng = new FileManager($this->_store_id);
        $mng->item_type = 'article';
        $mng->drop_by_item($this->_id);

        return $result;
    }

}
?>
