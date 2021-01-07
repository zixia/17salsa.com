<?php
/**
 * ECMALL: ���¹�����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.article.php 6062 2008-11-13 10:00:35Z Scottye $
 */

include_once(ROOT_PATH . '/includes/manager/mng.base.php');

class ArticleManager extends Manager
{
    var $_store_id = 0;

    /**
     *  ���캯��
     *  @param int $store_id
     *  @return none
     */

    function __construct($store_id)
    {
        $this->ArticleManager($store_id);
    }

    function ArticleManager($store_id)
    {
       $this->_store_id = $store_id;
       parent::__construct($store_id);
    }

    /**
     *  ��ȡ�����б�
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_list($page, $condition = null, $pagesize = 0)
    {
        $arg = $this->query_params($page, $condition, 'add_time', $pagesize);

        $sql = "SELECT art.*,cate.cate_name FROM `ecm_article` AS art " .
                "LEFT JOIN `ecm_article_cate` AS cate ON art.cate_id=cate.cate_id ".
                "WHERE $arg[where] ORDER BY $arg[sort] $arg[order] , add_time DESC LIMIT $arg[start], $arg[number]";

        $list = array();
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $row['formated_add_time'] = local_date('Y-m-d H:i', $row['add_time']);
            $list[] = $row;
        }
        return array('data' => $list, 'info' => $arg['info']);
    }

    /**
     *  ��ȡ�����б�
     *  @param int $start_id, int $page_size
     *  @return Array
     */
    function get_count($condition)
    {
        $where      = $this->_make_condition($condition);
        $sql        = "SELECT COUNT(*) FROM `ecm_article` AS art ".
                        "LEFT JOIN `ecm_article_cate` AS cate ON art.cate_id=cate.cate_id ".
                        "WHERE $where";
        $rec_count  = $GLOBALS['db']->getOne($sql);

        return $rec_count;
    }

    /**
     *  �������
     *  @param  array  $info
     *  @return int
     */
    function add($info)
    {
        $info['store_id'] = $this->_store_id;
        return $GLOBALS['db']->autoExecute('`ecm_article`', $info);
    }

    /**
     *  ��������Ƿ����
     *  @param  string  $title
     *  @param  int     $article_id
     *  @return int
     */
    function get_article_id($title)
    {
        $sql = "SELECT count(*) FROM `ecm_article` WHERE title='$title'";
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id='{$this->_store_id}'";
        }

        $count = $GLOBALS['db']->getOne($sql);
        return $count > 0 ? true : false;
    }

    /**
     * ��������
     *
     * @author  weberliu
     * @param   mixed   $article_id
     * @return  bool
     */
    function update($ids, $info)
    {
        $set_arr = array();
        foreach($info AS $k => $val)
        {
            $set_arr[] = "$k='$val'";
        }

        $set_str = join(',', $set_arr);
        $sql = "UPDATE `ecm_article` SET $set_str WHERE " .db_create_in($ids, 'article_id');
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }

        return $GLOBALS['db']->query($sql);
    }

    /**
     * ��������ѡ���ĵ���
     *
     * @author  weberliu
     * @param   string  $type  set_top, set_show, drop
     * @param   string  $in
     *
     * @return  bool
     */
    function batch($type, $in)
    {
        $sql = "UPDATE `ecm_article` SET";
        switch ($type)
        {
            case 'set_top':
                $sql .= ' is_top = 1-is_top';
            break;
            case 'set_show':
                $sql .= ' if_show = 1-if_show';
            break;
            case 'drop':
                return $this->drop($in);
            break;
            default:
                $this->err = 'Unknow batch processor';
                return false;
        }
        $sql .= " WHERE " .db_create_in($in, 'article_id');

        if ($this->_store_id>0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }
        return $GLOBALS['db']->query($sql);
    }


    /**
     *  ɾ������
     *
     * @author  scottye
     * @param   string      $ids
     * @return  void
     */
    function drop($ids)
    {
        $sql = "DELETE FROM `ecm_article` WHERE " .db_create_in($ids, 'article_id');
        if ($this->_store_id > 0)
        {
            $sql .= " AND store_id = " . $this->_store_id;
        }
        return $GLOBALS['db']->query($sql);
    }

    /**
     *  ��������ʽ��$conditionsת����SQL��WHERE�������
     *  @param mixed $conditions
     *  @return string
     */
    function _make_condition($condition)
    {
        $where = '1';

        if ($this->_store_id > 0)
        {
            $where .= " AND art.store_id = " . $this->_store_id;
        }

        if (!empty($condition['if_show']))
        {
            $where .= " AND if_show=1";
        }

        if (!empty($condition['cate_id']))
        {
            $where .= " AND art.cate_id=$condition[cate_id]";
        }

        if (!empty($condition['keywords']))
        {
            $where .= " AND title LIKE '%$condition[keywords]%'";
        }

        if (isset($condition['store_id']))
        {
            $where .= " AND art.store_id = '" . $condition['store_id'] . "'";
        }

        $where .= (isset($condition['built_in'])) ? " AND art.code > ''" : " AND art.code = ''";

        return $where;
    }

    /**
     * ��ȡ��������
     *
     * @author  weberliu
     * @return  array
     */
    function get_store_article()
    {
        $sql = "SELECT article_id, title FROM `ecm_article` WHERE store_id = {$this->_store_id} ORDER BY sort_order ASC";

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     *    ������±����Ƿ����
     *
     *    @author    Garbin
     *    @param     string $title
     *    @param     int $art_id
     *    @return    bool
     */
    function exists($title, $art_id = 0)
    {
        if (!$title)
        {
            return false;
        }
        $store_limit = ' AND store_id=' . $this->_store_id;
        if (!$art_id)
        {
            $sql = "SELECT article_id FROM `ecm_article` WHERE title='{$title}'{$store_limit}";

            return $GLOBALS['db']->getOne($sql);
        }
        else
        {
            $art_id = intval($art_id);
            $sql = "SELECT title FROM `ecm_article` WHERE article_id={$art_id}{$store_limit}";
            if ($GLOBALS['db']->getOne($sql) == $title)
            {
                return false;
            }
            else
            {
                return $this->exists($title, 0);
            }
        }
    }
}

?>
