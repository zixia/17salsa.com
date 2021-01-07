<?php

/**
 * ECMALL: 页面模型
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: mod.ad.php 3615 2008-05-21 08:06:31Z Liupeng $
 */

if (! defined('IN_ECM'))
{
    trigger_error('Hack attempt.', E_USER_ERROR);
}

require_once(ROOT_PATH . '/includes/cls.xml.php');
require_once(ROOT_PATH. '/includes/models/mod.base.php');

class Page extends Model
{
    var $_store_id       = 0;
    var $layout_path     = '';
    var $pagename        = '';
    var $filename        = '';
    var $_data           = array();
    var $document        = null;
    var $_currentNode    = null;
    var $_currentLibrary = null;
    var $current_skin    = '';
    var $mall_skin       = '';
    var $editable        = false;
    var $type            = 'mall';
    var $controller_name = null;

    function __construct ($pagename, $store_id = 0, $editable = false)
    {
        $this->Page($pagename, $store_id, $editable);
    }

    function Page($pagename, $store_id = 0, $editable = false)
    {
        $this->pagename = $pagename;
        $this->store_id = $store_id;
        $this->editable = $editable;

        $this->layout_path = ROOT_PATH . "/themes/";

        if ($store_id > 0)
        {
            $this->layout_path .= "store/layout/" . $this->pagename;
            $this->type = 'store';
        }
        else
        {
            $this->layout_path .= "mall/layout/" . $this->pagename;
        }

        $sql = "SELECT config, filename, hash_code FROM `ecm_templates` WHERE pagename='$pagename' and store_id='$store_id'";
        $data = $GLOBALS['db']->getRow($sql);

        $xml = null;

        if (!empty($data))
        {
            $this->config = @unserialize($data['config']);
            $this->filename = $this->layout_path . '/' . $data['filename'];
            $this->hash_code = $data['hash_code'];
            $this->_data = $data;

            if (!is_array($this->config))
            {
                $this->config = array();
                $this->reset();
                $this->Page($pagename, $store_id);
                return;
            }
        }
        else
        {
            $xml = $this->_init_page();
        }

        if (empty($xml))
        {
            $this->current_file = $this->filename;
            $xml = new XmlLib_xmlParser();

            $content = file_get_contents($this->current_file);
            $hash_code = md5($content);

            if($this->hash_code != $hash_code)
            {
                $this->reset();
                $this->Page($pagename, $store_id);
                return;
            }
            $content = preg_replace("/\<item.+?\/\>/", "", $content);
            $xml->loadFromString($content);
        }

        $this->layout = $xml->getDocument();
    }

    /**
     * 获取预编译代码
     *
     * author  liupeng
     * return  void
     */
    function get_html()
    {
        $html = & $this->_create_dom();
        $body = & $this->_create_body($this->layout);

        $html->appendChild($body);

        $html_code = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\r\n  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n";
        $html_code .= $html->toString(true, '');
        return $html_code;
    }

    /**
     * 生成bom
     *
     * author  wj
     * return  void
     */
    function & _create_dom()
    {
        $html = new Xmllib_Node('html');
        $head = &$html->createChild('head');

        /* 生成title */
        $title = & $head->createChild('title');
        $text  = $title->createTextNode('{$title}');
        $title->appendChild($text);

        /* 生成meta */
        $content_type = &$head->createChild('meta');
        $content_type->attributes = array('http-equiv' => 'Content-Type', 'content' => 'text/html;charset='. CHARSET .'');

        /*自定义头*/
        $generator = &$head->createChild('meta');
        $generator->attributes = array('name'=>'Generator', 'content'=> 'ECMall' . VERSION);

        //开启二级域名时开启设置base属性
        if (defined('ENABLED_CUSTOM_DOMAIN') && ENABLED_CUSTOM_DOMAIN && (!is_main_site()))
        {
            /*base*/
            $generator = &$head->createChild('base');
            $generator->attributes = array('href'=> trim(MAIN_DOMAIN, '/') . '/');
        }

        /* 生成keywords */
        $keywords = &$head->createChild('meta');
        $keywords->attributes = array('name'=>'Keywords', 'content'=>'{$meta_keywords}');
        /* 生成Description */
        $keywords = &$head->createChild('meta');
        $keywords->attributes = array('name'=>'description', 'content'=>'{$meta_description|escape}');

        $link = &$head->createChild('link');
        $link->attributes = array('rel'=>'alternate', 'type'=>'application/rss+xml', 'title'=>'RSS 2.0', 'href' => 'index.php?app=feed');
        $href = "style.php?mall_skin={$this->mall_skin}";

        if ($this->store_id > 0)
        {
            $href .= "&store_skin={$this->current_skin}";
        }

        $app = str_replace('controller', '', strtolower($this->controller_name));
        $href .= '&app=' . $app;

        $flag = '';
        if ($this->editable)
        {
            $flag = " title='pagecss'";
        }
        $link_tag = $head->createTextNode("<link$flag type='text/css' rel='stylesheet' href='$href'></link>");
        $head->appendChild($link_tag);

        $link = &$head->createChild('link');
        $link->attributes['rel']    = 'shortcut icon';
        $link->attributes['href']   = 'favicon.ico';
        $script_tag = $head->createTextNode('{script src=\'ecmall,ajax,json,ui.effect,ui.dialog,ui.utils,uc,ui.frontend\'}');
        $head->appendChild($script_tag);

        /* 如果是编辑模式 */
        if ($this->editable)
        {
            /* 加载模板管理语言项 */
            $srcript = & $head->createTextNode('{script src=\'tools.template,tools.draganddrop,ui.formbox,ui.colorselector,ui.tip\'}');
            $head->appendChild($srcript);

            $link2 = & $head->createChild('link');

            $link2->attributes['href'] = "js/tools.template/style.css";
            $link2->attributes['rel'] = "stylesheet";
            $link2->attributes['type'] = "text/css";

            $filename = basename($this->filename);

            /* 输出布局和模块的配置信息 */
            $tmp = array();
            foreach($this->config AS $key => $value)
            {
                if(empty($value['denyEdit']))
                {
                    $tmp[$key] = array();
                    foreach($value['children'] AS $k => $v)
                    {
                        $tmp[$key][] = $k;
                    }
                }
            }

            $filename = basename($this->filename);
            $str = "var config = " . ecm_json_encode($tmp) . ";\r\n".
                   "var filename='$filename';\r\n" .
                   "var pagename='$this->pagename';\r\n" .
                   "var skin = '$this->current_skin';\r\n" .
                   "var mallSkin = '$this->mall_skin';\r\n" .
                   "var storeId = '$this->store_id';\r\n".
                   "var app = '$app'";

            $script =& $head->createChild('script');
            $script->attributes['type'] = "text/javascript";
            $js_code = $script->createTextNode($str);
            $script->appendChild($js_code);
        }

        /* 添加skin下的JS文件 */
        $js_dir = "themes/$this->type/skin/$this->current_skin/js";
        if (is_dir($js_dir))
        {
            $dir = dir($js_dir);
            while (false !== ($entry = $dir->read()))
            {
                if(file_ext($entry) == 'js')
                {
                    $script =& $head->createChild('script');
                    $script->attributes['src']  = "themes/$this->type/skin/$this->current_skin/js/$entry";
                    $script->attributes['type'] = 'text/javascript';
                }
            }
            $dir->close();
        }

        $js_language_script = & $head->createChild('script');
        $js_language_script->attributes['type'] = "text/javascript";

        $lang = 'var lang =' . ecm_json_encode(Language::get("js")) . ';';

        $textnode = & $js_language_script->createTextNode($lang);
        $js_language_script->appendChild($textnode);
        return $html;
    }

    /**
     * 生成body元素
     *
     * author  liupeng
     * param  xmlnode $doc xml
     * return  void
     */
    function _create_body($doc)
    {
        if (empty($this->_currentNode))
        {
            $node = new XmlLib_Node("body");
            if ($this->editable)
            {
                $template = file_get_contents(ROOT_PATH . "/js/tools.template/template.html");
                $tn = & $doc->createTextNode($template);
                $node->appendChild($tn);
            }
        }
        else
        {
            $node = & $this->_currentNode;
        }

        for($i = 0; $i < count($doc->children); $i++)
        {
            $first = @$doc->children[$i]->firstChild();
            if(!empty($first) && $first->nodeName() == "col")
            {
                /* 如果是发现该节点下包含COL标签则生成TABLE */
                $table =& $node->createChild("table");
                $table->attributes['cellspacing'] = $table->attributes['cellpadding']= "0";
                $new_node = & $table->createChild("tr");
            }
            elseif (!empty($first) && $first->nodeName() == "library")
            {
                $new_node =& $node->createChild($node->nodeName() == "tr" ? "td" : "div");
            }
            elseif ($doc->children[$i]->nodeName() == "col")
            {
                $new_node =& $node->createChild("td");
            }
            elseif ($doc->children[$i]->nodeName() == "library")
            {
                $this->_currentLibrary =& $node;
                $this->_add_modules();
                break;
            }
            elseif ($doc->children[$i]->nodeNameNS() != "#text")
            {
                $new_node = &$node->createChild("div");
            }
            else
            {
                continue;
            }

            /* COPY属性 */
            $attr = & $doc->children[$i]->attributes;
            $allow_attr = array('id', 'style', 'class');
            foreach($attr AS $key => $value)
            {
                if (in_array($key, $allow_attr))
                {
                    if ($new_node->nodeName() == "tr")
                    {
                        $new_node->parent->attributes[$key] = $value;
                    }
                    else
                    {
                        $new_node->attributes[$key] = $value;
                    }
                }
            }
            $this->_currentNode = & $new_node;
            $this->_create_body($doc->children[$i]);
        }
        return $node;
    }

    /**
    * 清空模版设置
    *
    * author  liupeng
    * return  void
    **/
    function reset()
    {
        $sql = "DELETE FROM `ecm_templates` WHERE pagename='$this->pagename' and store_id='$this->store_id'";
        $GLOBALS['db']->query($sql);

        $this->_init_page();
    }

    /**
    * 初始化模版设置
    *
    * author  liupeng
    * return  void
    **/
    function _init_page()
    {
        $this->filename = $this->layout_path . '/default.layout';
        $xml = new XmlLib_xmlParser();
        $content = file_get_contents($this->filename);
        $hash_code = md5($content);

        $xml->loadFromString($content);
        $config = $this->get_library_item($xml->getDocument());

        $this->config = $config;

        $info['filename'] = 'default.layout';
        $info['pagename'] = $this->pagename;
        $config = serialize($config);
        $info['config']   = $GLOBALS['db']->escape_string($config);
        $info['store_id'] = $this->store_id;
        $info['hash_code'] = $hash_code;
        $filestat = @stat($filename);
        $info['last_modify_time'] = $filestat['mtime'];
        $this->last_modify_time = $filestat['mtime'];
        $GLOBALS['db']->autoExecute('`ecm_templates`', $info);

        return $xml;
    }

    /**
     * 插入模块
     *
     * author  liupeng
     * return  void
     */
    function _add_modules()
    {
        $parent = & $this->_currentLibrary;
        $parent_id = $parent->attributes['id'];
        $config = $this->config;

        $conf = isset($config[$parent_id]) ? $config[$parent_id] : null;

        if (isset($conf))
        {
            foreach($conf['children'] AS $key => $value)
            {
                $type = isset($value['mtype']) ? $value['mtype'] : '';
                if ($type == 'ad')
                {
                    $position_id = substr($key, 4);

                    $str = "{insert name='ads' id='$position_id'}";
                    $text = & $parent->createTextNode($str);
                    $parent->appendChild($text);
                }
                elseif ($type == 'cm')
                {
                    $id  = substr($key, 3);
                    $str = $this->_get_custom_module($id);

                    $text = $parent->createTextNode($str);
                    $parent->appendChild($text);
                }
                else
                {
                    $src = $value['src'];
                    $str = "{include file='$src'}";
                    $text =& $parent->createTextNode($str);
                    $parent->appendChild($parent->createTextNode($str));
                }
                unset($text);
            }
        }
    }

    /**
    * 获取模版默认设置
    *
    * author  liupeng
    * return  void
    **/
    function get_default_config()
    {
        $filename = $this->filename;
        $xml = new XmlLib_xmlParser();
        $content = file_get_contents($filename);
        $this->hash_code = md5($content);

        $xml->loadFromString($content);

        $config = $this->get_library_item($xml->getDocument());
        return $config;
    }

    /**
    * 更新模块数据
    *
    * author  liupeng
    * return  void
    **/
    function update()
    {
        $arr = array();
        if ($this->_data['config'] != $this->config)
        {
            $arr['config'] = $GLOBALS['db']->escape_string(serialize($this->config));
        }

        if ($this->_data['filename'] != basename($this->filename))
        {
            $arr['filename'] = basename($this->filename);
        }

        if ($this->_data['hash_code'] != $this->hash_code)
        {
            $arr['hash_code'] = $this->hash_code;
        }

        $GLOBALS['db']->autoExecute('`ecm_templates`', $arr, 'UPDATE', "store_id='$this->store_id' AND pagename='$this->pagename'");
    }

    /**
     * 获取文档中所有布局和模块信息
     *
     * author   liupeng
     * @param   xmlnode  $doc  XML文档
     * @return  array
     */
    function get_library_item($doc)
    {
        static $config = array();
        for ($i = 0; $i < count($doc->children); $i++)
        {
            if ($doc->children[$i]->nodeName() == 'library')
            {
                $parent = $doc->children[$i]->parent;
                $attr   = $parent->attributes;
                $id     = $attr['id'];

                if (!empty($id))
                {
                    $config[$id] = array();
                    $config[$id]['children'] = array();
                    if (isset($attr['allowEdit']) && $attr['allowEdit'] == 'false')
                    {
                        $config[$id]['denyEdit'] = 1;
                    }

                    $item = $doc->children[$i]->children;
                    for ($j = 0; $j < count($item); $j++)
                    {
                        $item_id = $item[$j]->attributes['id'];
                        $config[$id]['children'][$item_id] = $item[$j]->attributes;
                    }
                }
            }
            if ($doc->children[$i]->hasChildren())
            {
                Page::get_library_item($doc->children[$i]);
            }
        }
        return $config;
    }

    /**
     * 获取自定义模块
     *
     * @author  liupeng
     * @return  string
    */
    function _get_custom_module($id)
    {
        $filename = ROOT_PATH. "/themes/$this->type/resource/custom_module.html";
        $html = file_get_contents($filename);
        $html = str_replace('{index}', $id, $html);

        return $html;
    }
}

?>