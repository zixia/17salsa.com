/**
 * ECMall: 颜色选取类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.colorselector.js 2512 2008-04-25 09:36:43Z Liupeng $
 */
ui.simpleColorSelector = function(sender, value)
{
    this.sender = sender;
    this.lang = new Object;
    if (lang) if (lang.colorindex) this.lang = lang.colorindex;
    this.selected_elem = false;
    if (value) value = value.toUpperCase();
    this.box = $ce("DIV");
    this.box.id = "simpleColorSelector";
    this.ul = $ce("UL");
    for (var i=0;i<this.colorSet.length;i++){
      var li = $ce("LI");
      var span = $ce("SPAN");
      span.innerHTML = this.lang[this.colorSet[i]] ? this.lang[this.colorSet[i]] : this.colorSet[i];
      span.style.backgroundColor = this.colorSet[i];
      li.appendChild(span);
      if (this.colorSet[i] == value)
      {
        this.selected_elem = li;
        Element.addClass(li, "selected");
      }
      this.ul.appendChild(li);
    }
    this.last_elem = false;
    this.ul.onmouseover = this.over.bind(this);
    this.ul.onclick = this.click.bind(this);
    this.box.appendChild(this.ul);
    Event.observe(document, 'mousedown', this.mousedown.bind(this))

    this.box.style.position = "absolute";
    var pos = Element.getPosition(sender);
    this.box.style.top = (pos.top + sender.offsetHeight) + "px";
    this.box.style.left = pos.left + "px";
    this.box.style.border = "#000 1px solid";
    this.box.style.zIndex = 9999;
    var div = $ce("div")

    this.head = $ce("div");
    this.head.style.height = "15px";
    this.head.style.width = "175px";
    this.head.style.textAlign = "center";
    this.head.style.backgroundColor = "#FFF";
    this.head.innerHTML = this.lang.auto ? this.lang.auto : "auto";

    this.box.insertBefore(this.head, this.box.firstChild);
    document.body.appendChild(this.box);
};

ui.simpleColorSelector.prototype = {
  setValue : function(rgb, color_name) {},
  hide : function() {
    Element.hide(this.box)
    Event.stopObserving(document, 'mousedown', this.mousedown.bind(this));
  },
  show : function() {
    var pos = Element.getPosition(this.sender);
    this.box.style.top = (pos.top + this.sender.offsetHeight) + "px";
    this.box.style.left = pos.left + "px";
    Element.show(this.box)
    Event.observe(document, 'mousedown', this.mousedown.bind(this));
  },
  over : function (e) {
  var e = fixEvent(e);
  var elem = e.srcElement;
  if (elem.tagName.toUpperCase() == "SPAN")
  {
         elem = elem.parentNode;
         e.cancelBubble = true;
     if (this.last_elem) {Element.removeClass(this.last_elem, "actived")};
         this.head.innerHTML = elem.firstChild.innerHTML;
     Element.addClass(elem, "actived");
     this.last_elem = elem;
    }
  },
  click : function (e){
    var e = fixEvent(e);
    var elem = e.srcElement;
    if (elem.tagName.toUpperCase() == "LI") elem = elem.firstChild;
    if (elem.tagName.toUpperCase() == "SPAN"){
      if (this.selected_elem) Element.removeClass(this.selected_elem, "selected");
      this.selected_elem = elem.parentNode;
      Element.addClass(this.selected_elem, "selected");
      this.setValue(this.color2hex(elem.style.backgroundColor), elem.innerHTML);
      this.hide();
    }

  },
  mousedown : function (e){
    var e = fixEvent(e);
    if(!Element.contains(this.box, e.srcElement))
    {
      this.hide();
    }
  },
  color2hex : function (color)
  {
    var rgb_color = '';
    if (color.indexOf("rgb") > -1){
      var arr = color.match(/\d+/g);
      if (arr.length == 3){
        var tmp_var = "";
        var rgb = ['#'];
        for(var i=0; i < 3; i++)
        {
          tmp_var = parseInt(arr[i]).toString(16);
          if (tmp_var.length < 2) tmp_var = '0' + tmp_var;
          rgb.push(tmp_var.toUpperCase());
        }
        rgb_color = rgb.join('');
      }
    }else{
      rgb_color =  color.toUpperCase();
    }
    return rgb_color;
  },
  colorSet:["#000000","#993300","#333300","#003300","#003366","#000080","#333399","#333333","#800000","#FF6600","#808000","#008000","#008080","#0000FF","#666699","#808080","#FF0000","#FF9900","#99CC00","#339966","#33CCCC","#3366FF","#800080","#999999","#FF00FF","#FFCC00","#FFFF00","#00FF00","#00FFFF","#00CCFF","#993366","#C0C0C0","#FF99CC","#FFCC99","#FFFF99","#CCFFCC","#CCFFFF","#99CCFF","#CC99FF","#FFFFFF"]
}