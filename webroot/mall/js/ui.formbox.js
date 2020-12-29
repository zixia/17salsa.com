/**
 * ECMall: Formbox
 * ============================================================================
 * (C) 2005-2008 ShopEx Inc., all rights reserve.
 * Homepage:  http://www.shopex.cn
 * ============================================================================
 * $Id: ui.formbox.js 6098 2008-11-21 02:37:10Z Garbin $
 */
ui.FormBox = function (name)
{
  this.box = $ce("DIV");
  this.form = $ce("FORM");

  this.form.setAttribute("AUTOCOMPLETE", "off");
  this.closeBtn = $ce("span");
  this.closeBtn.innerHTML = "close";
  this.closeBtn.className = "closeBtn";
  this.closeBtn.onclick = this.close.bind(this);

  this.box.className = "formbox";
  this.box.style.position = "absolute";
  this.box.style.zIndex = 9999;
  this.list = $ce("dl");
  this.head = $ce("dt");
  this.head.innerHTML = name;
  this.head.appendChild(this.closeBtn);

  this.tip = $ce("dd");
  Element.addClass(this.tip, 'tip');

  this.tipSpan = $ce("span");
  this.closeTipBtn = $ce("img");
  this.closeTipBtn.className = "closeTipBtn";
  this.closeTipBtn.src = "js/ui.formbox/images/btn_delete.gif";
  this.closeTipBtn.onclick = (function(){
    this.hideTip();
  }).bind(this);
  this.list.appendChild(this.head);
  this.list.appendChild(this.tip);
  this.tip.appendChild(this.closeTipBtn);
  this.tip.appendChild(this.tipSpan);

  this.delegate  = new Object(); //¶¨ÒåÎ¯ÍÐ
  this.delegate.onDrag      = this.onDrag.bind(this);
  this.delegate.processDrag = this.processDrag.bind(this);
  this.delegate.endDrag     = this.endDrag.bind(this);
  this.items = new Object();
  this.allowDrag = false;
  var self = this;

  Element.hide(this.tip);
  Event.observe(this.head, "mousedown", this.delegate.onDrag);

  ui.FormBox._formSet[name] = this;
};
ui.FormBox._formSet = {};

ui.FormBox.getForm = function(name)
{
  return ui.FormBox._formSet[name];
};

ui.FormBox.prototype = {
  onDrag: function(event) {
    if (!this.allowDrag) return;
    this.rx = Event.pointerX(event) - this.box.offsetLeft;
    this.ry = Event.pointerY(event) - this.box.offsetTop;
    Event.observe(document, "mousemove", this.delegate.processDrag);
    Event.observe(document, "mouseup", this.delegate.endDrag);
  },
  processDrag: function(event) {
    this.left = (Event.pointerX(event) - this.rx);
    this.top = (Event.pointerY(event) - this.ry);
    this.box.style.top = this.top + "px";
    this.box.style.left = this.left + "px";
  },
  endDrag: function() {
    Event.stopObserving(document, "mousemove", this.delegate.processDrag);
    Event.stopObserving(document, "mouseup", this.delegate.endDrag);
  },
  addLine : function() {
    this.lastdd = $ce("dd");
    this.lastdd.innerHTML = "<hr>";
    this.list.appendChild(this.lastdd);
    this.lastdd = $ce("dd");
  },
  addItem : function(type, name, options) {
    var e = this._createElement(type);

    e.name = name;
    if (!options.inline) {
      this.lastdd = $ce("dd");
      this.list.appendChild(this.lastdd);
    }

    if (type =='element') {
        this.lastdd.appendChild(name);
        return;
    }

    if (options.style) {
        Object.extend(e.style, options.style);
    }

    if (options.className) {
        e.className = options.className;
    }
    if (options.label || options.rightLabel) {
      var label = $ce("label");
      if (options.label)
        label.appendChild(document.createTextNode(options.label));

      label.appendChild(e);

      if (options.rightLabel)
        label.appendChild(document.createTextNode(options.rightLabel));
      this.lastdd.appendChild(label);
    }

    if (options.value) {
      e.value = options.value;
    }

    if (options.checked) {
      e.checked = options.checked;
    }

    if (type == 'text') {
      e.innerHTML = options.value;
      e.className = "textElem";
    }

    if(options.label || options.rightLabel) {
      this.lastdd.appendChild(label);
    } else {
      this.lastdd.appendChild(e);
    }

    if (this.items[name]) {
      if (this.items[name].constructor != window.Array) {
        var temp = this.items[name];
        this.items[name] = new Array();
        this.items[name].push(temp);
      }
      this.items[name].push(e);

    } else {
      this.items[name] = e;
    }
  },
  _createElement : function(type){
    var e;
    switch(type){
      case "textbox":
        e=$ce("input");
        e.type = "text";
      break;
      case "radio":
      case "button":
      case "file":
      case "checkbox":
        e=$ce("input");
        e.type = type;
      break;
      case "text":
        e=$ce("label");
        break;
      case "textarea":
        e=$ce("textarea");
      break;
      case "select":
        e=$ce("select");
      break;
    }
    return e;
  },
  onShow: function(){},
  show: function() {
    if (this.width) {
      this.box.style.width = this.width;
    }
    if (!this.box.parentNode) {
      this.box.appendChild(this.form);
      this.form.appendChild(this.list);
      document.body.appendChild(this.box);
    } else {
      Element.show(this.box);
    }
    this.onShow();
  },
  close: function(){
    Element.hide(this.box);
    this.onClose();
  },
  onClose: function(){},
  showTip: function(text){
    Element.show(this.tip);
    this.tipSpan.innerHTML = text;
  },
  setTipText: function(text){
    this.tipSpan.innerHTML = text;
  },
  hideTip: function(delay) {
    delay = delay ? delay : 1;
    window.setTimeout((function(){
      Element.hide(this.tip);
    }).bind(this), delay);
  },
  reset: function() {
    for(var key in this.items) {
      if (typeof(this.items[key]) != "function") {
        if (typeof(this.items[key].tagName) != "undefined") {
          if(this.items[key].tagName.toLowerCase() == "input") {
            if (this.items[key].type == "text") {
              this.items[key].value = "";
            }
            if (this.items[key].type == "file") {
              this._resetFile(this.items[key]);
            }
          }

          if(this.items[key].tagName.toLowerCase() == "select") {
              this.items[key].selectedIndex = 0;
          }
        }
      }
    }
    this.hideTip();
  },
  _resetFile: function(elem)
  {
    var nxt = Element.next(elem);
    var parent = elem.parentNode;

    var tmpForm = $ce("FORM");
    tmpForm.appendChild(elem);
    document.body.appendChild(tmpForm);
    tmpForm.reset();
    if (nxt != null) {
      parent.inserBefore(elem,nxt);
    } else {
      parent.appendChild(elem);
    }
    Element.remove(tmpForm);
    delete tmpForm;
  }

};
