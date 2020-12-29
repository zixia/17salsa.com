/**
 * ECMall: ControlPanel
 * ============================================================================
 * (C) 2005-2008 ShopEx Inc., all rights reserve.
 * Homepage:  http://www.shopex.cn
 * ============================================================================
 * $Id: ui.formbox.js 2788 2008-05-07 08:19:27Z Liupeng $
 */

var ControlPanel = function() {
  document.require("js/tools.template/moduleeditor.js");

  this.cp = $("ECM_ControlPanel");
  this.panel = $class('panel',this.cp)[0];
  this.toggleBtn = $class('switch_on',this.cp)[0];
  this.toggleBtn.innerHTML = lang.hidden_panel;
  this.toggleBtn.onclick = this.toggle.bind(this);

  if ($class("no_goods_module", document.body, "div").length>0)
    Element.remove($class("no_goods_module", document.body, "div").item(0));

  $class("btn_save", this.cp).item(0).onclick = function() {
    _templateUpdate(false);
  };
  this.init();

};

Object.extend(ControlPanel.prototype, (function(){
  /* private member */
  var _prevBtn, _nextBtn;
  var _moduleList = false;
  var _adList = false;
  var _customModuleList = false;
  var _loading = false;
  var _panelgb = false;
  var _moduleListSide = false;
  var _mainSide = false;
  var _locker = false;
  var _loader = false;
  var _bulidModuleBtn = false;
  var _self = false;
  var _cmEditor = false;
  var _skinList = false;
  var _layoutList = false;

  function _pager(list, act, side, icon) {
    if (!list.pageSize)
      list.pageSize = 5;
    var f = function(){
      for(var i=0; i<list.children.length; i++){
        var p = list.page - 1;
        if (i > (p*list.pageSize - 1) && i < (p*list.pageSize)+list.pageSize ) {
          Element.show(list.children[i]);
        } else {
          Element.hide(list.children[i]);
        }
      }
    };

    if (list.pageCount == 0)
      list.pageCount = 1;
    _showListPager("list_pager", list.listname, list, icon);
    if (act == "next") {
      if ((list.page + 1) <= list.pageCount) {
        list.page++;
        if ($class("prevdisable", side).length)
          $class("prevdisable", side).item(0).className = "prev";
      } else {
        return;
      }

      if (list.page == list.pageCount && $class("next", side).item(0)){
        $class("next", side).item(0).className = "nextdisable";
      }
    } else if(act == "prev") {
      if ((list.page - 1) >= 1) {
        _nextBtn.className = "next";
        list.page--;
      } else {
        return;
      }
      if (list.page == 1) _prevBtn.className = "prevdisable";
    } else {
      if (list.page < list.pageCount && $class("nextdisable", side).length>0) {
        $class("nextdisable", side).item(0).className = "next";
        list.page = 1;
      }
      if (list.page > 1 && $class("prevdisable", side).length>0){
        $class("prevdisable", side).item(0).className = "prev";
      }
      f();
      return;
    }
    ui.effect.FadeTo(list, 100, 0, 50, function(){
        f();
        ui.effect.FadeTo(list, 0, 100, 50);
      }
    );
  }

  function _showListPager(type, text, list, icon) {
    var listPager = $class("list_pager", this.cp).item(0);
    if ($class("prev", listPager).length>0)
       _prevBtn = $class("prev", listPager).item(0);
    else
       _prevBtn = $class("prevdisable", listPager).item(0);

    if ($class("next", listPager).length>0)
       _nextBtn = $class("next", listPager).item(0);
    else
       _nextBtn = $class("nextdisable", listPager).item(0);

    if (type == "pane_tab") {
      Element.show($class("pane_tab", this.cp).item(0));
      Element.hide(listPager);
    } else {
      Element.show(listPager);
      Element.hide($class("pane_tab", this.cp).item(0));
      var h3 = listPager.getElementsByTagName("H3")[0];
      h3.innerHTML = text;
      h3.className = icon;
    }

    if (list) {
      if (list.page==1) _prevBtn.className = "prevdisable";
      if (list.page==list.pageCount) _nextBtn.className = "nextdisable";
    }

    _prevBtn.onclick = function() {
      _pager(list, "prev", listPager, icon);
    };
    _nextBtn.onclick = function() {
      _pager(list, "next", listPager, icon);
    };
  }

  function _buildCustomModule(id) {
    if (!_cmEditor) {
      _cmEditor = ModuleEditor.create();
      if (!_locker) _locker = new ui.utils.locker();
      _cmEditor.locker = _locker;
      _cmEditor.items["submitBtn"].onclick = (function() {
        _cmEditor.showTip(lang.saving);
        if (_cmEditor.items["module_name"].value == "") {
          _cmEditor.setTipText(lang.module_name_empty);
          return;
        }
        if (storeId == 0) {
            var img_count  = _cmEditor.items["img_count"].value;
            var word_count = _cmEditor.items["word_count"].value;
            var hot_count  = _cmEditor.items["hot_count"].value;
            if ((img_count == "" || img_count == "0") && (word_count == "" || word_count == "0") && (hot_count == "" || hot_count == "0")){
              _cmEditor.showTip(lang.goods_num_empty);
              return;
            }
        }
        /* post info */
        _cmEditor.form.submit();
      }).bind(this);

      _cmEditor.items["updateBtn"].onclick = function(){
        customModuleChanged = true;
        _cmEditor.items["submitBtn"].onclick();
      };
      Event.observe(_cmEditor.frame, "load", (function() {
        var jstr = _cmEditor.frame.contentWindow.document.body.innerHTML.replace(/<.*?>/ig,'');
        if (jstr == "") return;
        var json = jstr.parseJSON();

        if (json.done) {
          _cmEditor.setTipText(lang.save_succeed);
          _customModuleList = false;
          _self.customModuleList(false, json.retval.parseJSON());
          _cmEditor.close();
        } else {
          alert(json.msg)
        }
        if (_cmEditor.items["module_id"].value != "" && $("cm_"+_cmEditor.items["module_id"].value)) {
          _buildModule({
            moduleName : 'cm_'+_cmEditor.items["module_id"].value,
            customModule: true,
            info : {id : _cmEditor.items["module_id"].value}
            },function(html) {
                var tmp = $ce("div");
                tmp.innerHTML = html;
                if (tmp.firstChild) {
                  var elem = tmp.children[0];
                  var id = "cm_" + _cmEditor.items["module_id"].value;
                  $(id).className = elem.className;
                  $(id).innerHTML = elem.innerHTML;
                  registerDargModule($(id), id);
                }
              }
          );
        }
        }));
    }
    _cmEditor.reset();
    if (storeId == 0) {
        _cmEditor.items['img_count'].value = "4";
        _cmEditor.items['word_count'].value = "4";
        _cmEditor.items['hot_count'].value = "10";
        _cmEditor.items['img_count'].onblur = _cmEditor.items['word_count'].onblur = _cmEditor.items['hot_count'].onblur = function(){
          var exp = /^[0-9]{1,20}$/;
          if (!(exp.test(this.value)))
            this.value = "0";
        }
    }
    _cmEditor.show();
    if (id) {
      _loadModuleInfo(id);
      Element.show(_cmEditor.items['updateBtn']);
      Element.hide(_cmEditor.items['submitBtn']);
    } else {
      Element.hide(_cmEditor.items['updateBtn']);
      Element.show(_cmEditor.items['submitBtn']);
    }
  }

  function _loadModuleInfo(id) {
    _ajaxRequest("admin.php?app=template&act=get_module_info&id="+id, function(res){
      var o = {"ic":"img_count",
              "wc":"word_count",
              "hc":"hot_count",
              "tbgc":"title_backgroundcolor",
              "tfc":"title_fontcolor",
              "cbgc":"content_backgroundcolor",
              "cfc":"content_fontcolor",
              "bbgc":"bottom_backgroundcolor",
              "bfc":"bottom_fontcolor"
              };

      var data = res.retval.parseJSON();
      _cmEditor.items["module_name"].value = data['name'];
      _cmEditor.items["module_id"].value = id;
      for(key in data.config){
        var k = key;
        if (o[k]) {
          k = o[k];
        }
        if (_cmEditor.items[k]){
          _cmEditor.items[k].value = data.config[key];
        }
        if (key == "c") {
          for(var i=0;i<_cmEditor.items['cate'].options.length; i++){
            if (_cmEditor.items['cate'].options[i].value == data.config[key]){
              _cmEditor.items['cate'].options[i].selected = true;
            }
          }
        }
      }
      var colorBlocks = $class("colorBlock", _cmEditor.box, 'span');
      for(var i=0; i< colorBlocks.length; i++) {
        colorBlocks[i].style.background = Element.next(colorBlocks[i]).value;
      }
    }, 'GET');
  }

  function _ajaxRequest(url, callback, method) {
    _showLoading();
    Ajax.addVal("pagename", pagename);
    Ajax.addVal("filename", filename);
    Ajax.call(url, function(res) {
      _hideLoading();
      if (!checkLogin(res)) return;
      callback(res);
    }, "POST");
  }

  function _showLoading() {
    if (!_loading) _loading = $class("loading", this.cp).item(0);
    if (!_panelgb) _panelgb = $class("panel_gb", this.cp).item(0);
    Element.show(_loading);
    Element.hide(_panelgb);
  }

  function _hideLoading() {
    Element.hide(_loading);
    Element.show(_panelgb);
  }

  function _setSelectedTabItem(elem) {
    var p = elem.parentNode;
    for(var i=0;i<p.children.length;i++)
      Element.removeClass(p.children[i], "selected");
    Element.addClass(elem, "selected");
  }

  function _hideAllList() {
    if (_bulidModuleBtn) Element.hide(_bulidModuleBtn);
    var allList = $class("data_list", _self.cp, "UL");
    for(var i=0; i<allList.length;i++)
      Element.hide(allList[i]);
  }

  function _createRow(text) {
    var elem = $ce("li");
    var span = $ce("span");
    span.className = "btnlist";
    var addrmBtn = $ce("img");
    addrmBtn.className = "btn";
    span.appendChild(addrmBtn);
    span.appendChild(document.createTextNode(" "));
    elem.appendChild(span);
    elem.appendChild(document.createTextNode(text));
    elem.onmousemove = function() {
      this.className = "heightlight";
    };

    elem.onmouseout = function() {
      this.className = ""
    };
    return elem;
  }

  function _addRemoveModule(opt) {
    if ($(opt.moduleName)) {
      opt.sender.src = "js/tools.template/images/btn_add.gif";
      Element.remove($(opt.moduleName));
    } else {
      if (ModuleSet[opt.moduleName]) {
        $(opt.layout).appendChild(ModuleSet[opt.moduleName].module);
        opt.sender.src = "js/tools.template/images/btn_remove.gif";
      } else {
        _buildModule(opt);
      }
    }
  }

  function _addRemoveAdModule(opt) {
    var id = "adp_" + opt.info.position_id;
    if ($(id)) {
      opt.sender.src = "js/tools.template/images/btn_add.gif";
      Element.remove($(id));
    } else {
      if (ModuleSet[id]) {
        ModuleSet[id].layout.insertModule(ModuleSet[id].module);
      } else {
        _buildAdModule(opt);
      }
      opt.sender.src = "js/tools.template/images/btn_remove.gif";
    }
  }

  function _addRemoveCustomModule(opt) {
    var info = opt.info;
    var id = "cm_" + info.id;
    opt.customModule = true;
    if (!$(id)) {
      if (ModuleSet[id]) {
        ModuleSet[id].layout.insertModule(ModuleSet[id].module);
        opt.sender.src = "js/tools.template/images/btn_remove.gif";
      } else
        _buildModule(opt);
    } else {
      Element.remove($(id));
      opt.sender.src = "js/tools.template/images/btn_add.gif";
    }
  }

  function _deleteCustomModule(id, confirmed){
    if (!confirmed && $("cm_"+id)) {
      var d = new Dialog(DIALOG_CONFIRM);
      d.setContent(lang.sys_msg, lang.confirm_delete);
      d.onOK = function () {
        _deleteCustomModule(id, true)
        d.close();
      };
      d.show();
      return;
    }

    if ($("cm_"+id)) {
      Element.remove($("cm_"+id));
    }
    var data = _getLayoutConfig();
    Ajax.addVal("config", data);
    Ajax.addVal("pagename", pagename);
    Ajax.call("admin.php?app=template&act=delete_module&id="+id, function(res){
      if (!checkLogin(res)) return;
        if (res.done) {
          _customModuleList = false;
          _self.customModuleList(false, res.retval.parseJSON());
        }
      }, "POST");
  }

  function _buildAdModule(options) {
    var info = options.info;
    var mod = $ce("div");
    mod.style.width  = info.width + "px";
    mod.style.height = info.height + "px";
    mod.style.backgroundColor = "#ccc";
    mod.innerHTML = info.position_name;
    mod.id = "adp_" + info.position_id;

    LayoutSet.item[0].elem.appendChild(mod);
    registerDargModule(mod, mod.id);
  }

  function _showLoaderBox(text) {
    if (!_loader) {
      if (!_locker) _locker = new ui.utils.locker();
      _loader = $ce("div");
      _loader.className = "loaderBox";
      _loader.style.cssText = "position:absolute;background:#fff;width:300px; height:80px;z-index:9999;";
      _loader.style.left = (window.innerWidth / 2 - 150)   + "px";
      _loader.style.top = (window.innerHeight / 2 - 50) + "px";
      _loader.className = "ECM_loaderBox";
      var loader = $ce("IMG");
      loader.src = "js/tools.template/images/loadingbar.gif";

      var loaderMessage = $ce("div");
      _loader.appendChild(loader);
      _loader.appendChild(loaderMessage);
      document.body.appendChild(_loader);

    }
    _locker.lock();
    _loader.getElementsByTagName("div")[0].innerHTML = text ? text : lang.bulid_module;
    Element.show(_loader);
  }

  function _setLoaderMessage(text) {
    _loader.getElementsByTagName("div")[0].innerHTML = text;
  }

  function _hideLoaderBox() {
    Element.hide(_loader);
    _locker.unLock();
  }

  function _buildModule(options, callback) {
    Ajax.addVal("file", filename);
    Ajax.addVal("pagename", pagename);
    Ajax.addVal("module", options.moduleName);
    if (!options.customModule) {
    } else {
      Ajax.addVal("module_id", options.info.id);
      Ajax.addVal("custom_module", 1);
      options.moduleName = "cm_" + options.info.id;
    }
    if (!_locker) _locker = new ui.utils.locker();
    _showLoaderBox();
    _locker.lock();
    Ajax.call("admin.php?app=template&act=create_module", function(json){
      _hideLoaderBox();
      _locker.unLock();
       if (callback) {
        callback(json.retval);
        return;
      }

      if (!$("_tmpContainer")) {
        var t = $ce("div");
        t.id = "_tmpContainer";
        Element.hide(t);
        document.body.appendChild(t);
      }

      $("_tmpContainer").innerHTML = json.retval;
      var elem = $(options.moduleName);
      if (!options.layout) {
        var layout = LayoutSet.getMaxLayout();
        if (!layout)
          layout = $class("grid1col")[0];
        if (layout)
          layout.appendChild(elem);
        else
          LayoutSet.item[0].insertModule(elem);
      } else {
        $(options.layout).appendChild(elem);
      }

      registerDargModule(elem, options.moduleName);
      options.sender.src = "js/tools.template/images/btn_remove.gif";
    }, "POST");
  }

  /* set skin */
  function _setSkin(newskin) {
    if (newskin == skin) return;
    var list = _skinList.getElementsByTagName('IMG');
    for (var i= 0; i<list.length; i++) {
      if (list[i].getAttribute("skin") == newskin) {
        Element.addClass(list[i].parentNode, "selected");
      } else {
        Element.removeClass(list[i].parentNode, "selected");
      }
    }

    _showLoaderBox(lang.loading_style);
    var first = true;
    var url = "style.php?";

    if (storeId>0){
      url += "mall_skin=" + mallSkin + "&store_skin=" + newskin;
    } else {
      url += "mall_skin=" + newskin;
    }
    url += "&app="+app;

    var link = document.getElementsByTagName("LINK");
    for (var i=0; i < link.length; i++) {
      var href = link[i].href;
      if (href.indexOf("style.php")>=0) {
        Element.remove(link[i]);
      }
    }
    document.require(url, function(){
       if (!first) return;
       first = false;
        var imgs = document.getElementsByTagName("IMG");
        for (var j=0; j < imgs.length; j++) {
          if (typeof(mallSkin) != "undefined"){
            if (imgs[j].src.indexOf("mall/skin/"+mallSkin+"/images") > 0)
              continue;
          }
          if (imgs[j].src.indexOf(skin+"/images") > 0) {
            imgs[j].src = imgs[j].src.replace(skin+"/images", newskin+"/images");
          }
        }

        var inputs = document.getElementsByTagName("INPUT");
        for (var k=0; k < inputs.length; k++) {
          if (typeof(mallSkin) != "undefined"){
            if (storeId > 0)
            {
              if (inputs[k].src.indexOf("mall/skin/"+mallSkin+"/images") > 0)
              continue;
            }

          }
          if (inputs[k].src.indexOf(skin+"/images") > 0 && inputs[k].type.toUpperCase() == "IMAGE") {
            inputs[k].src = inputs[k].src.replace(skin+"/images", newskin+"/images");
          }
        }
        skin = newskin;
        skinChanged = true;
        _hideLoaderBox();
    }, "css");
  }

  function _setLayout(layout) {
    if (filename == layout)return;
      var newName = layout;

      var d = new Dialog(DIALOG_CONFIRM);
      d.setContent(lang.sys_msg, lang.confirm_change_layout);
      d.onOK = function () {
        Ajax.addVal('file', newName.split(".")[0]);
        Ajax.addVal('pagename', pagename);
        _showLoaderBox(lang.save_setting);
        Ajax.call("admin.php?app=template&act=set_layout", function(res){
          if (!checkLogin(res)) return;
          var d2 = new Dialog(DIALOG_MESSAGE);
          d2.setContent(lang.sys_msg, lang.change_layout_done);
          d2.closeBtnName = 'close';
          d2.onOK = function () {
            document.location.reload();
          }
          d2.show();
          _hideLoaderBox();
        }, "POST");
        d.close();
      };

      d.show();
  }

  return {
    init: function(){
      _self = this;
      if (!_bulidModuleBtn) {
        _bulidModuleBtn = $class("bulid_module_btn").item(0);
        _bulidModuleBtn.onclick = function(){
          _buildCustomModule();
        };
      }
      Element.show($class("panel_gb", this.cp).item(0));
      Element.hide($class("loading", this.cp).item(0));
      /* refresh position */
      if (window.location.href.indexOf("#") > 0) {
        method = window.location.href.split("#")[1];
        if (this[window.location.href.split("#")[1]]){
          this[window.location.href.split("#")[1]].call(this);
        }
      }
    },
    back: function(){},
    toggle: function() {
      if (Element.hasClass(this.toggleBtn,'switch_off')) {
         Element.show(this.panel);
         Element.removeClass(this.toggleBtn,'switch_off');
         Element.addClass(this.toggleBtn,'switch_on');
         this.toggleBtn.innerHTML = lang.hidden_panel;
      } else {
        Element.hide(this.panel);

        Element.removeClass(this.toggleBtn,'switch_on');
        Element.addClass(this.toggleBtn,'switch_off');
        this.toggleBtn.innerHTML = lang.show_panel;
      }
    },
    showContentMenu: function(){
      //_showListPager("pane_tab");
      Element.hide($class("list_pager", this.cp).item(0));
      Element.show($class("pane_tab", this.cp).item(0));
      _hideAllList();
      _setSelectedTabItem($class("pane_content", this.cp, "li").item(0));
      Element.show($class("content_menu", this.cp).item(0));
      document.location.href = document.location.href.split("#")[0] + "#showContentMenu";
    },

    showStyleMenu: function(event){
      Element.hide($class("list_pager", this.cp).item(0));
      Element.show($class("pane_tab", this.cp).item(0));
      _hideAllList();
      _setSelectedTabItem($class("pane_style", this.cp, "li").item(0));
      Element.show($class("style_menu", this.cp).item(0));
      document.location.href = document.location.href.split("#")[0] + "#showStyleMenu";
    },

    moduleList: function(sender) {
      var act = "";
      _hideAllList();
      if (!_moduleList) {
        var self = this;
        _moduleListSide = $class("module_list_side", this.cp).item(0);
        _moduleList = $ce("UL");
        Element.addClass(_moduleList, "text_list module_list data_list");
        Ajax.addVal('file', filename);
        Ajax.addVal('pagename', pagename);
        _ajaxRequest("admin.php?app=template&act=get_modules", function(res) {
          if (res.done == false) return;
          res.retval = res.retval.parseJSON();
          Element.show(_moduleList);

          for(var key in res.retval) {
            if (typeof(res.retval[key]) == "function"){continue;}
            if (res.retval[key]) {
              for(var k in res.retval[key]) {
                var obj = res.retval[key];
                if (typeof(obj[k]) == "function"){continue;}
                var elem = _createRow(obj[k]);
                var addrmBtn = $class("btn", elem).item(0);
                var path = "js/tools.template/images/";
                addrmBtn.src = path + ($(k) ? "btn_remove.gif" : "btn_add.gif");
                addrmBtn.setAttribute("module", k);
                addrmBtn.onclick = (function(opt) {
                  return function() {
                    _addRemoveModule(opt); }
                })({moduleName:k, layout:key, sender:addrmBtn});
                _moduleList.appendChild(elem);
              }
            }
          }
          _moduleList.listname = lang.listname.module;
          _moduleList.page = 1;
          _moduleList.pageCount = parseInt(_moduleList.children.length/5) + ((_moduleList.children.length % 5) > 0 ? 1 : 0);
          _pager(_moduleList, act, _moduleListSide, "icon_content");

          var colRight = $class("col_right", this.cp, "div").item(0);
          colRight.insertBefore(_moduleList, colRight.firstChild);
          self.back = function(){
            self.showContentMenu();
          };
        },"POST");
      } else {
        Element.show(_moduleList);
        _moduleList.page = 1;
        if (sender) act = sender.className;
        _pager(_moduleList, act, _moduleListSide, "icon_content");
      }
    },
    adList: function(sender){
      var act = "";
      var self = this;
      _hideAllList();
      if (!_adList) {
        _ajaxRequest("admin.php?app=template&act=get_ads", function(res){
          _adList = $ce("UL");
          Element.addClass(_adList, "text_list ad_list data_list");
          for(var i=0; i < res.retval.length; i++) {
            var posName = res.retval[i].position_name;
            var id = "adp_" + res.retval[i].position_id;
            var elem = _createRow(posName);
            var addrmBtn = $class("btn", elem).item(0);
            addrmBtn.setAttribute("module", "adp_" + res.retval[i].position_id);
            addrmBtn.onclick = (function(opt) {
                  return function() {
                    _addRemoveAdModule(opt); }
            })({sender:addrmBtn, info: res.retval[i]});

            var path = "js/tools.template/images/";
            addrmBtn.src = path + ($(id) ? "btn_remove.gif" : "btn_add.gif");
            _adList.appendChild(elem);
          }

          var colRight = $class("col_right", this.cp, "div").item(0);
          colRight.insertBefore(_adList, colRight.firstChild);

          _adList.listname = lang.listname.ads;
          _adList.page = 1;
          _adList.pageCount = parseInt(_adList.children.length/5) + ((_adList.children.length % 5) > 0 ? 1 : 0);
          _pager(_adList, act, _moduleListSide, "icon_content");
          self.back = self.showContentMenu;
        }, "POST");
      } else {
        Element.show(_adList);
        if (sender) act = sender.className;
        _pager(_adList, act, _moduleListSide, "icon_content");
      }
    },
    customModuleList: function(sender, data) {
      var act = "";
      var self = this;
      _hideAllList();
      if (_bulidModuleBtn) Element.show(_bulidModuleBtn);
      if (!_customModuleList) {
        function myCallBack(data, page){
          if ($class("custom_module_list", this.cp).length == 0){
            _customModuleList = $ce("UL");
            Element.addClass(_customModuleList, "text_list custom_module_list data_list");
          } else {
            _customModuleList = $class("custom_module_list", this.cp).item(0);
            _customModuleList.innerHTML = "";
            Element.show(_customModuleList);
          }

          for(var i=0; i < data.length; i++) {
            var obj = data[i];
            var id = "cm_" + obj.id;
            var elem = _createRow(obj.name);
            var addrmBtn = $class("btn", elem).item(0);
            addrmBtn.setAttribute("module", id);
            addrmBtn.onclick = (function(opt) {
              return function() {
                _addRemoveCustomModule(opt); }
              })({sender:addrmBtn, info: obj});

            var path = "js/tools.template/images/";
            addrmBtn.src = path + ($(id) ? "btn_remove.gif" : "btn_add.gif");

            var delBtn = $ce("IMG");
            delBtn.src = path + "btn_del.gif";
            delBtn.onclick = (function(id){
              return function(){
                _deleteCustomModule(id);
              };
            })(obj.id);

            var editBtn = $ce("IMG");
            editBtn.src = path + "btn_edit.gif";
            editBtn.onclick = (function(id){
              return function(){
                _buildCustomModule(id);
              };
            })(obj.id);

            elem.firstChild.appendChild(editBtn);
            elem.firstChild.appendChild(document.createTextNode(" "));
            elem.firstChild.appendChild(delBtn);
            _customModuleList.appendChild(elem);
          }

          var colRight = $class("col_right", this.cp, "div").item(0);
          colRight.insertBefore(_customModuleList, colRight.firstChild);

          _customModuleList.listname = lang.listname.goods_module;
          _customModuleList.page = page ? page : 1;
          _customModuleList.pageCount = parseInt(data.length / 5) + ((data.length % 5) > 0 ? 1 : 0);
          _pager(_customModuleList, act, _moduleListSide, "icon_content");
          self.back = self.showContentMenu;
        };

        if (data) {
          myCallBack(data, parseInt(data.length/5) + ((data.length % 5) > 0 ? 1 : 0));
          return;
        }
        _ajaxRequest("admin.php?app=template&act=get_custom_modules", function(res) {
          myCallBack(res.retval.parseJSON());
        }, "POST");
      } else {
        Element.show(_customModuleList);
        if (sender) act = sender.className;
        _pager(_customModuleList, act, _moduleListSide, "icon_content");
      }
    },
    skinList: function(sender) {
      var act = "";
      _hideAllList();
      if (!_skinList) {
        var curPage = 1;
        _ajaxRequest("admin.php?app=template&act=get_skins", function(res){
          _skinList = $ce("UL");
          Element.addClass(_skinList, "thumb_list skin_list data_list");
          res.retval = res.retval.parseJSON();
          for(var i = 0; i < res.retval.length; i++) {
            var li = $ce("li");
            if (res.retval[i].name == skin) {
                Element.addClass(li, "selected");
                curPage = parseInt((i+1) / 3) + (((i+1) % 3) > 0 ? 1 : 0)
            }
            li.innerHTML = "<img src='" + res.retval[i].image +"' skin='"+ res.retval[i].name +"'><div style='visibility:hidden;'>1</div><span style='visibility:hidden;'>"+ res.retval[i].name +"</span>";
            li.onclick = (function(skin) {
              return function(){
                _setSkin(skin);
              };
            })(res.retval[i].name);

            li.onmousemove = function(){
              var span, div;
              for (var i=0; i<this.children.length; i++) {
                if (this.children[i].tagName.toLowerCase() != "img") {
                  this.children[i].style.visibility = "";
                  if (this.children[i].tagName.toLowerCase() == "div")
                    div = this.children[i];
                  if (this.children[i].tagName.toLowerCase() == "span")
                    span = this.children[i];
                }
              }
              div.style.width = span.offsetWidth + "px";
              div.style.lineHeight = "12px";
              div.style.height = span.offsetHeight + "px";
              var center = parseInt(this.offsetWidth / 2);
              span.style.left = (center - parseInt(span.offsetWidth/2)) + "px";
              div.style.left = (center - parseInt(div.offsetWidth/2)) + "px";
              Element.addClass(this, "heightlight");
            };

            li.onmouseout = function() {
              for (var i=0; i<this.children.length; i++) {
                if (this.children[i].tagName.toLowerCase() != "img") {

                  this.children[i].style.visibility = "hidden";
                }
              }
              Element.removeClass(this, "heightlight");
            };
            _skinList.appendChild(li);
          }
          var colRight = $class("col_right", this.cp, "div").item(0);
          colRight.insertBefore(_skinList, colRight.firstChild);
          _skinList.listname = lang.listname.skin;
          _skinList.page = curPage;
          _skinList.pageSize = 3;
          _skinList.pageCount = parseInt(_skinList.children.length/3) + ((_skinList.children.length % 3) > 0 ? 1 : 0);
          _pager(_skinList, act, _moduleListSide, "icon_style");
          _self.back = _self.showStyleMenu;
        },"POST");
      } else {
        Element.show(_skinList);
        if (sender) act = sender.className;
        _pager(_skinList, act, _moduleListSide, "icon_style");
      }
    },
    layoutList: function(sender) {
      var act = "";
      _hideAllList();
      if (!_layoutList) {
        Ajax.addVal('file', filename);
        Ajax.addVal('pagename', pagename);
        _ajaxRequest("admin.php?app=template&act=get_layouts", function(res){
          _layoutList = $ce("UL");
          Element.addClass(_layoutList, "thumb_list skin_list data_list");
          res.retval = res.retval.parseJSON();
          for(var i = 0; i < res.retval.length; i++) {
            var li = $ce("li");
            if (res.retval[i].filename == filename) {
                Element.addClass(li, "selected");
            }
            li.innerHTML = "<img src='" + res.retval[i].image +"' filename='"+ res.retval[i].filename +"'>";
            li.onclick = (function(layout) {
              return function(){
                _setLayout(layout);
              };
            })(res.retval[i].filename);
            _layoutList.appendChild(li);
          }
          var colRight = $class("col_right", this.cp, "div").item(0);
          colRight.insertBefore(_layoutList, colRight.firstChild);

          _layoutList.listname = lang.listname.layout;
          _layoutList.page = 1;
          _layoutList.pageSize = 3;
          _layoutList.pageCount = parseInt(_layoutList.children.length/3) + ((_layoutList.children.length % 3) > 0 ? 1 : 0);
          _pager(_layoutList, act, _moduleListSide, "icon_style");
          _self.back = _self.showStyleMenu;
        },"POST");
      } else {
        Element.show(_layoutList);
        if (sender) act = sender.className;
        _pager(_layoutList, act, _moduleListSide, "icon_style");
      }
    },
    exit : function() {
      document.location.href= document.location.href.replace(/edit_mode=1/i, "");
    },
    restore : function() {
      var dialog = new Dialog(DIALOG_CONFIRM);
      dialog.setContent(lang.sys_msg, lang.confirm_change_layout);
      dialog.onOK = function () {
        _ajaxRequest("admin.php?app=template&act=restore", function(){
          document.location.reload();
        })
      };
      dialog.show();
    }
  };
})());
