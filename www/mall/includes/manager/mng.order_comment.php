<?php

/**
 * ECMALL: 订单评价管理类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mng.order_comment.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

class OrderCommentManager extends Manager
{
    var $_id = 0;
    function __construct($id)
    {
        $this->OrderCommentManager($id);
    }
    function OrderCommentManager($id)
    {
        $this->_id = $id;
    }
    function get_list($page = 1, $condition = array('from' => 'all', 'evaluation' => 0), $pagesize = 30)
    {
        $arr = $this->query_params($page, $condition, 'oi.add_time', $pagesize);

        /* 获取作为卖家的评论 */
        $sql = 'SELECT oi.order_sn,oi.user_id, oi.store_id,oi.order_amount,oi.add_time,oi.seller_evaluation,oi.seller_evaluation_invalid,oi.seller_comment,oi.seller_credit,oi.buyer_evaluation,oi.buyer_evaluation_invalid,oi.buyer_comment,oi.buyer_credit,oi.is_anonymous,u.user_name AS buyer_name,su.user_name AS seller_name ' .
               'FROM `ecm_order_info` oi LEFT JOIN `ecm_users` u ON oi.user_id=u.user_id LEFT JOIN `ecm_users` su ON oi.store_id=su.user_id ' .
               "WHERE {$arr['where']} ORDER BY {$arr['sort']} {$arr['order']} LIMIT {$arr['start']},{$arr['number']}";
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res as $key => $value)
        {
            if ($value['user_id'] == $this->_id)
            {
                $res[$key]['from'] = 'seller';
                $res[$key]['evaluation'] =  $value['buyer_evaluation'];
                $res[$key]['display_name'] = $res[$key]['seller_name'];
                $res[$key]['display_evaluation'] =  get_eval_lang($value['buyer_evaluation']);
                $res[$key]['display_comment'] = $value['buyer_comment'];
                $res[$key]['display_credit'] = $value['buyer_credit'];
                $res[$key]['evaluation_invalid'] = $value['buyer_evaluation_invalid'];
            }
            elseif ($value['store_id'] == $this->_id)
            {
                $res[$key]['from'] = 'buyer';
                $res[$key]['evaluation'] =  $value['seller_evaluation'];
                $res[$key]['display_name'] = $res[$key]['buyer_name'];
                $res[$key]['display_evaluation'] = get_eval_lang($value['seller_evaluation']);
                $res[$key]['display_comment'] = $value['seller_comment'];
                $res[$key]['display_credit'] = $value['seller_credit'];
                $res[$key]['evaluation_invalid'] = $value['seller_evaluation_invalid'];
            }
            $res[$key]['avatar'] = UC_API . '/avatar.php?uid=' . $val['user_id'] . '&amp;size=small';
        }

        return array('data' => $res, 'info'=>$arr['info']);
    }

    /**
     * 获得符合条件的记录总数
     *
     * @param   array   $condition
     *
     * @return  int
     */
    function get_count($condition)
    {
        $where  = $this->_make_condition($condition);
        $sql    = 'SELECT COUNT(*) ' .
                  'FROM `ecm_order_info` oi ' .
                  "WHERE {$where}";
        $count  = $GLOBALS['db']->getOne($sql);

        return $count;
    }

    /**
     *    make conditions
     *
     *    @author    Garbin
     *    @param     array $condition
     *    @return    string
     */
    function _make_condition($condition)
    {
        $where = array();
        foreach ($condition as $key => $value)
        {
            switch ($key)
            {
                case 'from':
                    $eval_condition = '> 0';
                    if ($condition['evaluation'] > 0 && $condition['evaluation'] <=3)
                    {
                        $eval_condition = '=' . intval($condition['evaluation']);
                    }
                    $from_buyer = "(oi.store_id={$this->_id} AND seller_evaluation {$eval_condition})";
                    $from_seller= "(oi.user_id={$this->_id} AND buyer_evaluation {$eval_condition})";
                    if ($value == 'buyer')
                    {
                        $where[] = $from_buyer;
                    }
                    elseif ($value == 'seller')
                    {
                        $where[] = $from_seller;
                    }
                    else
                    {
                        $where[] = '(' . $from_buyer . ' OR ' . $from_seller . ')';
                    }
                break;
                case 'date':
                    if ($value == 'one_week')
                    {
                        $where[] = 'oi.add_time >= ' . (gmtime() - 3600 * 24 * 7);
                    }
                    elseif ($value == 'one_month')
                    {
                        $where[] = 'oi.add_time >= ' . (gmtime() - 3600 * 24 * 30);
                    }
                    elseif ($value == 'half_year')
                    {
                        $where[] = 'oi.add_time >= ' . (gmtime() - 3600 * 24 * 180);
                    }
                    elseif ($value == 'half_year_ago')
                    {
                        $where[] = 'oi.add_time < ' . (gmtime() - 3600 * 24 * 180);
                    }
                break;
                default:
                break;
            }
        }

        return implode(' AND ', $where);
    }
}
/**
 *    获取评价语言项
 *
 *    @author    Garbin
 *    @param     int $evaluation
 *    @return    void
 */
function get_eval_lang($evaluation)
{
    switch ($evaluation)
    {
        case 1:
            return Language::get('eval_bad');
        break;
        case 2:
            return Language::get('eval_normal');
        break;
        case 3:
            return Language::get('eval_good');
        break;
    }
}

?>
