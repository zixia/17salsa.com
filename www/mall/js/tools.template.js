/**
 * ECMall: 模板管理Javascript文件
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: tools.template.js 6009 2008-10-31 01:55:52Z Garbin $
 */
var ecmtcp = null;
var skinChanged = false;
var customModuleChanged = false;
Event.observe(window, 'load', function() {

  if (!config) return;

  for (var key in config) {
    if (typeof(config[key]) == "function") continue;
    try {
      var layout = new Layout($(key), config[key]);
      LayoutSet.add(layout);
      for (var i=0; i < config[key].length; i++) {
        if(!ModuleSet[config[key][i]]) {
          var elem = $(config[key][i]);
          if(elem) {
            registerDargModule(elem);
          }
        }
      }
    } catch(ex) {}
  }
  document.require("js/ui.formbox/style.css", null, "css");

  document.require("js/tools.template/controlpanel.js", function(){

    ecmtcp = new ControlPanel();
  });
});

// 注册可拖拽模块
function registerDargModule(elem) {
  var dm = new DragModule(elem);
  dm.name = elem.id;
  dm.onClose = function() {
    var buttons = $class("btn", document.body);
    for (var i=0; i<buttons.length; i++) {
      if (buttons[i].getAttribute("module") == this.name) {
        var path = "js/tools.template/images/btn_add.gif";
        buttons[i].src = path;
        buttons[i].title = lang.add;
      }
    }
    this.layout.update();
  };
  ModuleSet.add(elem.id, dm);
}

// 检测登录状态
function checkLogin(data) {
  if (typeof(data) != "object") {
    return true;
  }
  if (!data.done) {
    var d = new Dialog(DIALOG_MESSAGE);
    d.setContent(lang.sys_msg, data.msg);
    d.closeBtnName = 'close';
    d.onOK = function () {
      d.close();
      document.location.href = document.location.href.split("#")[0];
      document.location.reload();
    };
    d.show();
    return false;
  }
  return true;
}

function _getLayoutConfig() {
  LayoutSet.update();
  var data = {};
  for (var i=0; i < LayoutSet.item.length; i++) {
    var layout = LayoutSet.item[i];
    var children = layout.getChildren();
    data[layout.id] = [];
    for(var j=0; j < children.length; j++) {
      data[layout.id].push(children[j].id);
    }
  }

  return data;
}

var _templateUpdating = false;
function _templateUpdate(noalert, force) {
  if (_templateUpdating) return;
  var d = new Dialog(DIALOG_MESSAGE);
  d.setContent(lang.sys_msg, lang.save_succeed);
  d.closeBtnName = 'close';

  var data = _getLayoutConfig();
  Ajax.addVal('file', filename);
  Ajax.addVal('pagename', pagename);
  if(config.toJSONString() == data.toJSONString() && !skinChanged && !force && !customModuleChanged) {
    if (!noalert) d.show();
    return;
  } else {
    if (config.toJSONString() != data.toJSONString() || customModuleChanged)
      Ajax.addVal('config', data);
    if (skinChanged)
      Ajax.addVal('skin', skin);
  }
  var tip = null;
  if (!$("ECM_tip")) {
    tip = $ce("div");
    tip.id = "ECM_tip";
    tip.innerHTML = lang.save_setting;
    document.body.appendChild(tip);
  } else {
    tip = $("ECM_tip");
    Element.show(tip);
  }
  var scrollTop = (document.body.scrollTop || document.documentElement.scrollTop);
  tip.style.top = (window.innerHeight - tip.offsetHeight - 10 + scrollTop) + "px";
  _templateUpdating = true;
  Ajax.call("admin.php?app=template&act=update_template", function(res){
    if (!checkLogin(res)) return;
    config = data;
    skinChanged = false;
    customModuleChanged = false;
    _templateUpdating = false;
    Element.hide(tip);
    if (!noalert) d.show();

  }, "POST");
}
