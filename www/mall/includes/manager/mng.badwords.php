<?php

/**
 * ECMALL: conf ������
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: mng.badwords.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

class BadwordsManager
{
    /**
     * ���캯��
     *
     * @author    wj
     * @param    void
     * @return    void
     */
    function __construct()
    {

    }

    function BadwordsManager()
    {

    }

    /**
     * ���һ�����д�
     *
     * @author    wj
     * @param    string    $words
     * @retrun    boolen
     */
    function add($words)
    {
        $GLOBALS['db']->query("INSERT INTO `ecm_badwords` (words) VALUES ('$words')");
        return $GLOBALS['db']->Insert_Id();
    }

    /**
     * ɾ��һ�����д�
     *
     * @author    wj
     * @param    int        $words_id
     * @return    int
     */
    function drop($words_id)
    {
        if (is_array($words_id))
        {
            $sql = "DELETE FROM `ecm_badwords` WHERE words_id " . db_create_in($words_id);
        }
        else
        {
            $sql = "DELETE FROM `ecm_badword` WHERE words_id = '" . intval($words_id) . "'";
        }

        $GLOBALS['db']->query($sql);

        return $GLOBALS['db']->affected_rows();
    }

    /**
     * ����һ�����д�
     *
     * @author    wj
     * @param    int        $words_id
     * @param    string    $words
     * @return    boolen
     */
    function update($words_id, $words)
    {
        $sql = "UPDATE `ecm_badwords` SET words='$words' WHERE words_id ='" . intval($words_id) . "'";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * ��ȡһ�����д�
     *
     * @author     wj
     * @param      int     $words_id
     * @return     boolen
     */
    function get_words($words_id)
    {
       $words = $GLOBALS['db']->getOne("SELECT words FROM `ecm_badwords` WHERE words_id='$words_id'");

       return $words;
    }

    /**
     * �ж�һ�����д��Ƿ��ظ�
     *
     * @author    wj
     * @param    string    $words
     * @param   int     $words_id
     * @return    void
     */
    function is_duplicate($words, $words_id=0)
    {
        $sql = "SELECT COUNT(*) FROM `ecm_badwords` WHERE words='$words'";
        if ($words_id > 0)
        {
            $sql .= " AND words_id <> '$words_id'";
        }

        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * ��ȡ���Թؼ���
     *
     * @author    wj
     * @param    void
     * @return    array
     */
    function get_list()
    {
        $sql = "SELECT words_id, words FROM `ecm_badwords`";

        return $GLOBALS['db']->getAll($sql);
    }


    /**
     * �ִ����Ƿ��м��ؼ���
     *
     * @author    wj
     * @param    string    $str
     * @return    boolen
     */
    function check($str)
    {
        $sql = "SELECT words FROM `ecm_badwords`";
        $words = $GLOBALS['db']->getCol($sql);

        $ret_val = true;
        if ($words)
        {
            array_walk($words, create_function('&$item, $key', '$item = addcslashes($item, \'*+?^$.{}()[]\/\');'));
            $pattern = '/(?:' . implode(')|(?:', $words) . ')/i';

            if(strpos($pattern, ' '))
            {
                $pattern = preg_replace('/\s+/', '[\s|\r|\n|\_|\-|\.|\,|\~|\`|\!|\@|\#|\$|\%|\^|\&|\*]*', $pattern);
            }
            if (preg_match($pattern, $str))
            {
                $ret_val = false;
            }
        }

        return $ret_val;
    }


}
