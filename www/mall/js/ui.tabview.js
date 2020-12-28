/**
 * ECMall: UI.TabView
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.tabview.js 6009 2008-10-31 01:55:52Z Garbin $
 */
  function tabView (bar, labels, pages, uid) {
    this.sess = "tabs" + document.getCookie('ECM_ID') + "" + uid;
    this.bar = bar;
    this.labels = labels;
    this.wrapper = pages;
    this.currIndex = -1;
    this.items = new Array();
    this.labWidth = 126;
    this.closeImage = "admin/images/toptab_close.gif";
    this.closeImageHover = "admin/images/toptab_close_hover.gif";
    var _self = this;
    //Storage.setItem("aa","aa");

    Element.setSelectable(bar, false);
    Event.observe(window, "resize", function(){_self.resize();});

    this.init = function(txt, app, act) {
      try {
        var cookies = document.getCookie(this.sess);
        var data = (Storage.get("tabsStatus"));

        if (data != null) {
          try
          {
            data = (eval("("+data+")"));
            cookies = data[this.sess];
          }
          catch(ex)
          {
            cookies = null;
          }

        }

        if (cookies != null) {
          var pos = cookies.indexOf('|');
          var curr = cookies.substr(0, pos);
          var tabs = cookies.substr(pos + 1);
          var arr = tabs.parseJSON();
          for (var i=0; i<arr.length; i++) {
            this.open(arr[i].text, arr[i].key, arr[i].act, arr[i].params);
          }
          this.show(curr);
          this.currIndex = curr;
          this.saveTabs();
        } else {
          this.open(txt, app, act);
          this.currIndex = 0;
        }
      } catch(e) {
      } finally {
        this.items[0].closeButton.style.visibility = "hidden";
        this.items[0].closeButton.onclick = function() {return;};
        this.resize();
        this.show(this.currIndex);
        this.toggleMenuItem(this.items[this.currIndex].key);
      }
    };
    this.open = function(txt, app, act, params, reload) {
      var i = this.tabIndex(app);
      if (i == -1) {
        var item = this.addTab(txt, app, act, params);
        item.page.src = this._buildUrl(app, act, params);
        if (this.currIndex > -1) this.items[this.currIndex].hidden();
        this.currIndex = this.items.length - 1;
        this.resize();
        this.saveTabs();
        this.show(this.currIndex);
      } else {
        this.items[i].page.contentWindow.location.replace(this._buildUrl(app, act, params));
        this.items[i].act = act;
        this.items[i].params = params;
        this.show(i);
      }
    };
    this.tabIndex = function(key) {
      var reval = -1;
      for (var i=0; i<this.items.length; i++) {
        if (this.items[i].key == key) {
          reval = i;
          break;
      }}
      return reval;
    };
    this.addTab = function(txt, app, act, params) {
      var item = new tabItem(txt, app, act, params);
      item.create();
      item.closeButton.src = this.closeImage;
      item.closeButton.onclick = function() { _self.close(app) };
      item.closeButton.onmouseover = function() {this.src="admin/images/toptab_close_hover.gif";};
      item.closeButton.onmouseout = function() {this.src="admin/images/toptab_close.gif";};
      item.label.onclick = function() {_self.active(app);};
      item.page.onload = function() {
        var url = item.page.contentWindow.location.href.split("?");
        if (url.length <= 1) return;

        var tmp = url[1].split("&");
        var arg = "";

        for (var i=0; i<tmp.length; i++) {
          var _a = tmp[i].split("=");
          if (_a[0] == "act")
            item.act = _a[1];
          else if (_a[0] != 'app')
            arg += tmp[i] + "&";
        };
        item.params = arg.substr(0, arg.length-1);
        _self.saveTabs();
      };

      this.labels.appendChild(item.label);
      this.labels.style.width = (this.bar.scrollWidth + this.labWidth) + "px";
      this.wrapper.appendChild(item.page);
      this.items.push(item);

      return item;
    };
    this.close = function(key) {
      var i = this.tabIndex(key);
      var next = -1;
      if (i > -1) {
        this.labels.removeChild(this.items[i].label);
        this.wrapper.removeChild(this.items[i].page);

        this.items.splice(i, 1);

        if (this.currIndex != i) {
          next = this.currIndex - 1;
        } else if (this.currIndex<this.items.length) {
          next = this.currIndex;
          this.currIndex = 0;
        } else if (this.currIndex>0) {
          next = this.currIndex - 1;
        }

        this.show((next >= 0 ? next : 0));
        this.saveTabs();
      }
      this.relocate();
    };
    this.active = function(key) {
      this.toggleMenuItem(key);
      var i = this.tabIndex(key);
      if (i > -1) {
        if (i != this.currIndex) this.show(i);
        this.saveTabs();
    }};
    this.toggleMenuItem = function(key)
    {
      var groups = $("slide-menu").getElementsByTagName("dl");
      for (var i=0; i<groups.length; i++)
      {
        if (Element.hasClass(groups[i], "menu-group-actived"))
        {
          Element.removeClass(groups[i], "menu-group-actived");
          Element.addClass(groups[i], "menu-group");
        }
      }

      if ($class(key).length>0) {
        $class(key)[0].parentNode.parentNode.className = "menu-group-actived";

        if (currGroup) currGroup = $class(key)[0].parentNode.parentNode;
      }
    };
    this.resize = function() {
      var pos = Element.getPosition(this.wrapper);
      var oHeight = pos.top;
      iHeight = window.innerHeight - oHeight;
      iHeight -= (navigator.isIE()) ? 2 : 4;
      iWidth = (Math.floor((window.innerWidth) / this.labWidth) - 2) * this.labWidth;
      this.bar.style.width = iWidth + "px";
      this.wrapper.style.height = iHeight + "px";
      this.wrapper.style.display = "block";
      this.relocate();
    };
    this.reload = function() {
      this.items[this.currIndex].page.contentWindow.location.reload();
    };
    this.relocate = function() {
      var w = this.bar.offsetWidth - 2;
      var n = Math.floor(w / this.labWidth);

    };
    this.show = function(i) {
      if (i != this.currIndex) {
        this.items[i].show();
        if (this.items[this.currIndex]) this.items[this.currIndex].hidden();
        this.currIndex = i;
      }

      var scrollLeft = this.bar.scrollLeft;
      var offsetLeft = this.items[i].label.offsetLeft;

      var width = this.items[i].label.offsetWidth;
      if(offsetLeft - scrollLeft + width > this.bar.offsetWidth){
        this.bar.scrollLeft = (offsetLeft + width - this.bar.offsetWidth);
        if (Element.next(this.items[i].label)!=null) {
          this.bar.scrollLeft += 128;
        }
      }

      if(offsetLeft<scrollLeft){
        this.bar.scrollLeft = this.items[this.currIndex].label.offsetLeft;
      }

    };
    this.saveTabs = function() {
      if (this.items.length > 0) {
        var arr = new Array();
        for (var i = 0; i<this.items.length; i++) {
          var obj = {"text":this.items[i].text, "key":this.items[i].key, "act":this.items[i].act, 'params':this.items[i].params};
          arr.push(obj);
        }

        var str = this.currIndex + "|" + arr.toJSONString();
        var data = new Object();
        data[this.sess] = str;
        Storage.add("tabsStatus", data.toJSONString());
      }
    };
    this._buildUrl = function(app, act, params) {
      var uri = "admin.php?app=" +app+ "&act=" + act;
      if (params != undefined) uri += "&" + params.replace('%26', '&');

      return uri;
    };
  };
  function tabItem (txt, key, act, params) {
    this.text = txt;
    this.key = key;
    this.act = act;
    this.params = params;
    this.closeButton = $ce("img");
    this.label = null;
    this.page = null;

    this.create = function() {
      this.label = this.createLabel(this.text);
      this.page = this.createPage();
    };
    this.createLabel = function(txt) {
      var lab = $ce("LI");
      lab.id = "_tab_" + this.key;
      lab.className = "actived";
      lab.setAttribute("action", this.act, 0);
      lab.setAttribute("params", this.params, 0);
      lab.appendChild(this.closeButton);

      var tag = $ce("a");
      tag.innerHTML = txt;
      lab.appendChild(tag);

      return lab;
    };
    this.createPage = function() {
      var frm = $ce("iframe");
      with (frm) {
        id = "_frame_" + this.key;
        width = "100%";
        height = "100%";
        setAttribute("frameBorder", "0", 0);
      }

      return frm;
    };
    this.hidden = function() {
      this.label.className = "";
      this.page.style.display = "none";
    };
    this.show = function() {
      this.label.className = "actived";
      this.page.style.display = "";
    };
  }
