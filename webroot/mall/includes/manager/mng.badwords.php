<?php

/**
 * ECMALL: conf 管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
     * 构造函数
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
     * 添加一个敏感词
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
     * 删除一个敏感词
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
     * 更新一个敏感词
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
     * 获取一个敏感词
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
     * 判断一个敏感词是否重复
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
     * 获取所以关键字
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
     * 字串中是否含有检查关键字
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
