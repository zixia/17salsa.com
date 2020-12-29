/**
 * ECMall: Module Editor
 * ============================================================================
 * (C) 2005-2008 ShopEx Inc., all rights reserve.
 * Homepage:  http://www.shopex.cn
 * ============================================================================
 * $Id: moduleeditor.js 6035 2008-11-04 05:32:58Z Garbin $
 */
var ModuleEditor = {
  appendColorSelector : function(elem) {
    var color = $ce("span");
    color.className = "colorBlock";
    color.style.cssText="margin-right:5px;padding-bottom:1px !important;padding-bottom:0px;";
    color.innerHTML = "<img src=\"admin/images/color_selecter.gif\" width=\"21\" height=\"18\" alt=\"selecter\">";
    color.children[0].onclick = function() {
      if (this.cs) {
        this.cs.show();
        return;
      }
      this.cs = new ui.ColorSelector(color, "");
      this.cs.show();
      this.cs.setValue = function(value) {
        elem.value = value;
        color.style.backgroundColor = value;
      };
    };
    elem.parentNode.insertBefore(color, elem);
  },
  create: function() {
    var self = this;
    var _editor = null;

    _editor = new ui.FormBox(lang.create_module);
    _editor.width = "400px";

    _editor.addItem("textbox", "module_name" ,{label: lang.module_name});
    _editor.addItem('select' ,'cate',{style:{width:'auto'},label:lang.cate});
    _editor.addItem("textbox", "module_id" ,{style:{display:'none'},value:0});
    if (storeId == 0) {
      _editor.addItem("textbox", "img_count" ,{label: lang.img_count,style:{width:'30px'},value:0});
      _editor.addItem("textbox", "word_count" ,{label: lang.word_count,style:{width:'30px'},value:0});
      _editor.addItem("textbox", "hot_count" ,{label: lang.hot_count,style:{width:'30px'},value:0});


      _editor.addLine();
      _editor.addItem("text", "" ,{value:lang.title_style});
      _editor.addItem("textbox", "title_backgroundcolor" ,{label: lang.background_color, style:{width:'60px', marginRight:'20px'}});
      _editor.addItem("textbox", "title_fontcolor" ,{label: lang.font_color,inline: true,style:{width:'60px'}});
      _editor.addItem('file', 'title_backgroundimage',{style:{width:'auto',height:'20px'},label:lang.background_img});
      _editor.addItem("text", "" ,{value:lang.content_style});

      _editor.addItem("textbox", "content_backgroundcolor" ,{label: lang.background_color, style:{width:'60px', marginRight:'20px'}});
      _editor.addItem("textbox", "content_fontcolor" ,{label: lang.font_color,inline: true,style:{width:'60px'}});

      _editor.addItem('file', 'content_backgroundimage',{style:{width:'auto',height:'20px'},label:lang.background_img});

      _editor.addItem("text", "" ,{value:lang.bottom_style});
      _editor.addItem("textbox", "bottom_backgroundcolor" ,{label: lang.background_color, style:{width:'60px', marginRight:'20px'}});
      _editor.addItem("textbox", "bottom_fontcolor" ,{label: lang.font_color,inline: true,style:{width:'60px'}});

      _editor.addItem('file', 'bottom_backgroundimage',{style:{width:'auto',height:'20px'},label:lang.background_img});
    }
    _editor.addItem('button', 'submitBtn',{style:{width:'100px',margin:'auto',height:'20px'},value: lang.save});
    _editor.addItem('button', 'updateBtn',{style:{width:'100px',margin:'auto',height:'20px'},value: lang.update,inline: true});
    _editor.items['submitBtn'].parentNode.style.textAlign = "center";
    _editor.frame = $ce("div");
    _editor.frame.innerHTML = "<iframe name='blankiframe'></iframe>"
    Element.hide(_editor.frame);
    document.body.appendChild(_editor.frame);
    _editor.frame = _editor.frame.children[0];

    _editor.form.target = "blankiframe";
    _editor.form.action = "admin.php?app=template&act=add_module";
    _editor.form.method = "POST";
    _editor.form.encoding = "multipart/form-data";
    if (storeId == 0) {
      this.appendColorSelector(_editor.items['title_backgroundcolor']);
      this.appendColorSelector(_editor.items['title_fontcolor']);
      this.appendColorSelector(_editor.items['content_backgroundcolor']);
      this.appendColorSelector(_editor.items['content_fontcolor']);
      this.appendColorSelector(_editor.items['bottom_backgroundcolor']);
      this.appendColorSelector(_editor.items['bottom_fontcolor']);
    }

    _editor.showTip(lang.load_category);
    Ajax.call('admin.php?app=template&act=get_cate_tree', (function(res) {
      _editor.items["cate"].options[0] = new Option(lang.all_cate, 0)
      if (res.done) {
        var tree = res.retval.parseJSON();
        for (key in tree) {
          if (typeof(tree[key]) != "function") {
            if (tree[key].level > 1) {
              tree[key].cate_name = "-" + tree[key].cate_name;
            }
            _editor.items["cate"].options[_editor.items["cate"].options.length] = new Option(tree[key].cate_name, tree[key].cate_id);
          }
        }
        _editor.hideTip();
      }
    }).bind(this));
    _editor.items["cate"].onchange = function() {
        var catName = this.options[this.selectedIndex].text;

        if(catName[0] == "-") {
            catName = catName.substring(1, catName.length);
        }
        _editor.items["module_name"].value = catName;
    }
    _editor.box.style.top = "40px";
    _editor.onShow = function(id) {
      var width = Math.min(document.body.scrollWidth, self.innerWidth||document.body.clientWidth);
      _editor.box.style.left = (width / 2 - 200) + "px";
      _editor.locker.lock();
    };

    _editor.onClose = function() {
      _editor.locker.unLock();
      var cbs = $class("colorBlock", _editor.box, "SPAN");
      for (var i = 0; i < cbs.length; i++) {
        cbs[i].style.backgroundColor = "";
      }
    };
    _editor.allowDrag = true;
    return _editor;
  }
};
