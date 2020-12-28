var LayoutSet = {
  item: [],
  add: function(layout){
    this.item.push(layout);
  },
  getLayoutById: function(id) {
    for(var i=0; i<this.item.length; i++) {
      if(this.item[i].id == id) return this.item[i];
    }
  },
  update: function(){
    for(var i=0; i<this.item.length; i++) {
      if(this.item[i].update)this.item[i].update();
    }
  },
  getMaxLayout: function(){
    var elem = false;
    for(var i=0; i<this.item.length; i++) {
      if (!elem)
        elem = this.item[i].elem;
      else {
        if (this.item[i].elem.offsetWidth > elem.offsetWidth){
          elem = this.item[i].elem;
        }
      }
    }
    return elem;
  }
};

var ModuleSet = {
  item: {},
  add: function(key, value){
    if (key=="add"||key=="item") throw '';
    this[key] = value;
  }
};

function Layout(elem) {
  this.elem = elem;
  this.id = this.elem.id;
  this.curPos = Element.getPosition(this.elem);
  if (elem.tagName.toUpperCase() == "DIV") this.setHeight(elem);
}

Layout.prototype = {
  comprise: function(X, Y, block) {
    this.left = this.curPos.left;
    this.top  = this.curPos.top;
    this.right = this.left + this.elem.offsetWidth;
    this.bottom = this.top + this.elem.offsetHeight;

    if ((this.left < X && this.right > X) && (this.top < Y && this.bottom > Y)) {
      return true;
    } else {
      return false;
    }
  },
  //获取容器下的所有模块
  getChildren: function() {
    if (!this._children) {
      this._children = new Array();
      var children = this.elem.children;
      for (var i=0; i < children.length; i++) {
        if (children[i].getAttribute) {
          if(ModuleSet[children[i].id]) {
            this._children.push(children[i]);
          }
        }
      }
    }
    return this._children;
  },
  insertModule: function(elem, old) {
    if (old) {
      if (Element.next(elem) == old) {
        return;
      }
      this.elem.insertBefore(elem, old);
    } else {
      this.elem.appendChild(elem);
    }
  },
  update: function() {
    this._children = false;
    if (this.getChildren().length == 0 && this.elem.offsetHeight < 10) {
      this.elem.style.height = "100px";
    } else {
      this.elem.style.height = "";
    }
    var elem = this.elem;
    if (elem.tagName.toUpperCase() == "DIV") this.setHeight(elem);
    this.curPos = Element.getPosition(this.elem);
    this.lastWidth = this.elem.offsetWidth;
  },
  setHeight: function(elem) {
    if (elem.tagName.toUpperCase() == "DIV") {
      if (elem.parentNode.tagName.toUpperCase() == "TD"){
        var height = elem.parentNode.offsetHeight;
        var tmp = elem;
        while ((tmp = Element.prev(tmp)) != null) {
          height -= tmp.offsetHeight;
        }
        elem.style.height = height + "px";
      } else {
        if (elem.children.length == 0) {
          elem.style.height = "100px";
        }
      }
    }  
  }
};

function DragModule(elem) {
  this.module= elem;
  this.module.style.position = "relative";

  this.closeBtn = $ce("IMG");
  this.closeBtn.className = "close_btn";
  this.closeBtn.src='js/tools.template/images/btn_close.gif';
  this.module.appendChild(this.closeBtn);
  var self = this;
  this.closeBtn.onmousedown = function(event) {
    Event.stopObserving(document, "mousemove", self.delegate.processDrag);
    Event.stopObserving(document, "mouseup", self.delegate.endDrag);
    Element.remove(self.module);
    self.onClose();
  };

  this.update(); // 刷新
  this.module.setAttribute("dragAllowable", "true");
  this.delegate  = new Object(); //定义委托
  this.delegate.onDrag      = this.onDrag.bind(this);
  this.delegate.processDrag = this.processDrag.bind(this);
  this.delegate.endDrag     = this.endDrag.bind(this);
  if (this.module.children[0].tagName == "H3" && Element.getStyle(this.module.children[0], "display") != "none") {
    Event.observe(this.module.children[0], "mousedown", this.delegate.onDrag);
    this.module.children[0].style.cursor = "move";
  } else {
    Event.observe(this.module, "mousedown", this.delegate.onDrag);
    this.module.style.cursor = "move";
  }
}

Object.extend(DragModule.prototype, (function(){
  var _lastLayout = false;
  var _moduleInfoCache = new Object();
  return {
    onClose : function(){},
    virtualBox: false,
    update: function() {
      this.layout = LayoutSet.getLayoutById(this.module.parentNode.id);
    },
    onDrag: function(event) {
      if (event.srcElement == this.closeBtn) return;
      event.returnValue = false;
      ui.effect._setOpacity(this.module, 50);
      if (!this.virtualBox) {
        this.virtualBox = $ce("div");
        this.virtualBox.style.position = "absolute";
        this.virtualBox.style.border = "2px #ccc solid";
        this.virtualBox.style.filter='alpha(opacity=80)';
        this.virtualBox.style.cursor = "move";
        this.virtualBox.style.height = "35px";
        this.virtualBox.style.width = "100px";
        document.body.appendChild(this.virtualBox);
      } else {
        this.virtualBox.style.display = "";
      }
      this.virtualBox.style.left =  (Event.pointerX(event)-50) + "px";
      this.virtualBox.style.top = (Event.pointerY(event)-10) + "px";
      this.rx = Event.pointerX(event) - this.virtualBox.offsetLeft;
      this.ry = Event.pointerY(event) - this.virtualBox.offsetTop;

      Event.observe(document, "mousemove", this.delegate.processDrag);
      Event.observe(document, "mouseup", this.delegate.endDrag);
      var pos = Element.getPosition(this.module);
      this.top = pos.top;
    },

    processDrag:function(event) {
      event.returnValue = false;
      var x = Event.pointerX(event), y = Event.pointerY(event);
      if (y-5 <= Math.max(document.documentElement.scrollTop,document.body.scrollTop)) {
        (document.documentElement || document.body).scrollTop -= 15;
      }
      
      bottom = Math.min(document.documentElement.clientHeight, document.body.clientHeight);
      if ((y-(document.documentElement.scrollTop || document.body.scrollTop)) >= (bottom-5)) {
        (document.documentElement || document.body).scrollTop += 15;
      }
      this.virtualBox.style.left = (Event.pointerX(event) - this.rx) + "px";
      this.virtualBox.style.top  = (Event.pointerY(event) - this.ry) + "px";
      this.Y = y;

      this.inLayout = false;

      if (_lastLayout) {
        if (_lastLayout.comprise(x, y)) {
          this.drop(_lastLayout);
          this.inLayout = true;
          this.virtualBox.style.cursor = "move";
          return;
        } else 
        _moduleInfoCache = new Object();
      }

      for (var i = 0; i < LayoutSet.item.length; i++) {
        if(LayoutSet.item[i].comprise(x, y)) {
          if (this.layout != LayoutSet.item[i])
            ui.effect.blink(LayoutSet.item[i].elem, 3);
          this.virtualBox.style.cursor = "move";
          _lastLayout = LayoutSet.item[i];
          this.drop(LayoutSet.item[i]);
          this.inLayout = true;
        } else {
          this.virtualBox.style.cursor = "not-allowed";
          if (spliter.parentNode) {
             Element.remove(spliter);
             spliter.layout = false;
          }
        }
      }
    },

    drop: function(layout) {
      var newdate = new Date();
      if (this.lastDragTime) {
        if((newdate.getTime()-this.lastDragTime) < 40)
          return;
      }
      this.lastDragTime = newdate.getTime();

      var items = layout.getChildren();
      spliter.layout = layout;
      var inserted = false;
      if (items.length == 0) {
        spliter.show();
        layout.insertModule(spliter);
        return;
      }
      
      var Y = parseInt(this.virtualBox.style.top);
      for (var i=0; i < items.length; i++) {
        if (items[i] == this.module) continue;
        if (!_moduleInfoCache[i]) {
          _moduleInfoCache[i] = {};
          _moduleInfoCache[i].pos = Element.getPosition(items[i]);
          _moduleInfoCache[i].prev = Element.prev(items[i]);
          _moduleInfoCache[i].next = Element.next(items[i]);
          var tmp = Element.getStyle(items[i], "marginBottom");
          if (tmp != null) tmp = parseInt(tmp);
          else tmp = 0;
          _moduleInfoCache[i].marginBottom = tmp;

          tmp = Element.getStyle(items[i], "marginTop");
          if (tmp != null)
            tmp = parseInt(tmp);
          else
            tmp = 0;
          _moduleInfoCache[i].pos.top += tmp;
        }
        var info = _moduleInfoCache[i];
        var pos = info.pos;

        var bottom = 0;
        var height = items[i].offsetHeight;
        
        // 如果取不到高度
        if (height == 0) {
          if(info.next != null) {
            var nextElem = info.next;
            var nPos = Element.getPosition(nextElem);
            bottom = nPos.top;
          }else{
            bottom = layout.bottom;
          }
          height = bottom - pos.top;
        } else {
          bottom = items[i].offsetHeight + pos.top;
          if (_moduleInfoCache[i].marginBottom!=null) {
            bottom += _moduleInfoCache[i].marginBottom;
          }
        }

        if ((Y <= bottom) && Y >= pos.top) {
          if ((Y - pos.top) >= parseInt(height/2)) {
            // spliter插入到该模块的下面
            var next = info.next;
            if (next != this.module) {
              inserted = true;
              layout.insertModule(spliter,next ? next: false);
              break;
            }
          } else {
            // spliter插入到该模块的上面
            if (info.prev != this.module)  {
              inserted = true;
              layout.insertModule(spliter, items[i]);
              break;
            }
          }
        }
      }
      
      if (!inserted && items[0] != this.module) {
        if(Y <= Element.getPosition(items[0]).top){
          layout.insertModule(spliter, items[0]);
          inserted = true;
        } else if(items[items.length-1] != this.module){
          if (Y >= Element.getPosition(items[items.length-1]).top){
            layout.insertModule(spliter);
            inserted = true;
          }
        }
      }
      if (inserted)spliter.show();
      else spliter.hide();
    },
    endDrag: function(event) {
      if(this.inLayout) {
        try {
          if(spliter.layout && spliter.display)
            spliter.layout.insertModule(this.module, spliter);
            LayoutSet.update();
        } catch(ex) {
          // alert(ex.description);
        }
      }
      if(spliter.parentNode)Element.remove(spliter);
      ui.effect._setOpacity(this.module, 100);
      _lastLayout = false;
      _moduleInfoCache = new Object();
      this.update();
      Event.stopObserving(document, "mousemove", this.delegate.processDrag);
      Event.stopObserving(document, "mouseup", this.delegate.endDrag);
      this.virtualBox.style.display = "none";
      //_template_update();
    }
  };

})());

var spliter = $ce("DIV");
spliter.style.height     = "2px";
spliter.style.display    = "block";
spliter.style.fontSize   = "0px";
spliter.style.background = "blue";
spliter.id = "ESC_spliter";
spliter.layout = false;
spliter.display = false;
spliter.hide = function() {
  if (spliter.display){
    Element.hide(this);
    spliter.layout = false;
    spliter.display = false;
  }
};
spliter.show = function() {
  if (!spliter.display){
    spliter.display = true;
    Element.show(this);
  }
};