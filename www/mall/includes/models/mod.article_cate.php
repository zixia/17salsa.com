<?php

/**
 * ECMALL: ��������ʵ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mod.article_cate.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class ArticleCate extends Model
{
    /**
     * ���캯��
     */
    function __construct ($cate_id, $store_id = 0)
    {
        $this->ArticleCate($cate_id, $store_id);
    }

    function ArticleCate($cate_id, $store_id = 0)
    {
        $this->_table = '`ecm_article_cate`';
        $this->_key   = 'cate_id';
        parent::Model($cate_id, $store_id);
    }
}

?>