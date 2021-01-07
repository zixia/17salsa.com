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
 * $Id: mod.article.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class Article extends Model
{
    /**
     * ���캯��
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
     * ���캯��
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
    * ��ȡ������Ϣ
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
    * ���ݴ��������µ�ID
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
     *    ɾ������
     *
     *    @author    Garbin
     *    @return    bool
     */
    function drop()
    {
        $result = parent::drop();

        /* ɾ������ */
        include_once(ROOT_PATH . '/includes/manager/mng.file.php');
        $mng = new FileManager($this->_store_id);
        $mng->item_type = 'article';
        $mng->drop_by_item($this->_id);

        return $result;
    }

}
?>
