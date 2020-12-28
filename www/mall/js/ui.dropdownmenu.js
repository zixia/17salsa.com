/**
 * ECMall: 下拉列表类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.dropdownmenu.js 6009 2008-10-31 01:55:52Z Garbin $
 */
function initDropDownMenu (btn, menu) {
  var buttons = $class(btn);
  var menus = $class(menu);

  if (buttons.length == menus.length) {
    for (var i=0; i < buttons.length; i++) {
      new ui.DropDownMenu(buttons[i], menus[i]);
    }
  } else {
    throw "Dropdown menu error.";
  }
}

ui.DropDownMenu = function (btn, mnu, bgColor, dbColor)
{
  this.btn = btn;
  this.box = mnu;
  this.borderColor = (dbColor) ? dbColor : "#a7cedb";
  this.bgColor = (bgColor) ? bgColor : "#FFF";

  var _self = this;
  this.btn.onclick = function(e) {_self.show();};

  this.delegate = new Object();
  this.delegate.autoHide = (function(event){
      var obj = fixEvent(event).srcElement;
      if (!Element.contains(this.btn, obj)&&!Element.contains(this.box, obj))
        this.hide();
  }).bind(this);

  document.body.appendChild(this.box);

  this._line = $ce("div");
  this._line.style.cssText = "font-size:0px;background:"+this.bgColor+";position:absolute;height:3px;display:none;";
  document.body.appendChild(this._line);

  Event.observe(document,'mouseup', this.delegate.autoHide);
};

ui.DropDownMenu.prototype = {
  show : function() {
    this.box.style.display = "block";
    this.btn.style.backgroundColor = this.bgColor;
    this.btn.style.border = " 1px solid " + this.borderColor;
    this._line.style.width = (this.btn.offsetWidth - 2) + "px";

    var ih     = window.innerHeight;
    var iw     = window.innerWidth;
    var pos    = Element.getPosition(this.btn);
    var right  = pos.left + this.box.offsetWidth;
    var bottom = pos.top + this.box.offsetHeight;

    if (bottom > ih) {
      this.box.style.top = (pos.top - this.box.offsetHeight + 1)  + "px";
    } else {
      this.box.style.top = (pos.top + this.btn.offsetHeight) -1  + "px";
    }
    if (right > iw) {
      this.box.style.left = pos.left - this.box.offsetWidth + this.btn.offsetWidth + "px";
    } else {
      this.box.style.left = pos.left+ "px";
    }

    this._line.style.top = (pos.top + this.btn.offsetHeight - 1) + "px";
    this._line.style.left = (pos.left + 1) + "px";
    this._line.style.display = "block";
  },
  hide : function() {
    this.btn.style.border = "none";
    this.btn.style.backgroundColor = "";
    this._line.style.display = "none";
    Element.hide(this.box);
  }
};