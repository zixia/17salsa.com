<?php

/**
 * ECMALL: 敏感词设置
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: badwords.php 6009 2008-10-31 01:55:52Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/manager/mng.badwords.php');
class BadwordsController extends ControllerBackend
{
    var $mng = null;

    function __construct($act)
    {
        $this->BadwordsController($act);
    }
    function BadwordsController($act)
    {
        if (empty($act))
        {
            $act = 'view';
        }
        $this->mng = new BadwordsManager();
        parent::__construct($act);
    }

    function view()
    {
        $this->logger = false;
        $base = 3;
        $list = $this->mng->get_list();
        $count = count($list);
        if (($dis = $count % $base) > 0)
        {
            $count += ($base - $dis);
        }

        $badwrods_list = array();

        for ($i=0; $i < $count; $i++)
        {
            $badwords_list[floor($i/$base)][] = isset($list[$i]) ? $list[$i] : '';
        }

        $this->assign('badwords_list', $badwords_list);
        $this->display('badwords.view.html', 'mall');
    }

    function add()
    {
        $this->logger = false;
        $words = isset($_POST['badwords_name']) ? trim($_POST['badwords_name']) : '';

        if (!$words)
        {
            $this->show_warning('please_input_words');
            return;
        }

        if ($this->mng->is_duplicate($words))
        {
            $this->show_warning('words_is_exist');
            return;
        }

        if ($words_id = $this->mng->add($words))
        {
            $this->logger = true;
            $this->log_item = $words_id;
            $this->show_message('add_ok', 'badwords_view', 'admin.php?app=badwords&amp;act=view');
        }
        else
        {
            $this->show_warning($this->err);
        }
    }

    function update()
    {
        $words_id = isset($_POST['words_id']) ? intval($_POST['words_id']) : 0;
        $words = isset($_POST['words']) ? trim($_POST['words']) : '';
        $old_words = $this->mng->get_words($words_id);
        if (!$words)
        {
            $this->json_error('please_input_words', $old_words);
        }
        elseif ($this->mng->is_duplicate($words, $words_id))
        {
          $this->json_error('words_is_exist', $old_words);
        }
        else
        {
          $this->log_item = $words_id;

          $this->mng->update($words_id, $words);
          $this->json_result($words, 'ok');
        }
    }

    function drop()
    {
       $words_ids = isset($_POST['words_ids']) ? $_POST['words_ids'] : '';
       if ($words_ids)
       {
           $this->log_item = implode(',', $words_ids);
           $this->mng->drop($words_ids);
       }
       $this->show_message('drop_ok', 'badwords_view', 'admin.php?app=badwords&amp;act=view');
    }
}

?>