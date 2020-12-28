/**
 * ECMall: 日历类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.calendar.js 6009 2008-10-31 01:55:52Z Garbin $
 */
Event.observe(window, "load", function(){
  var curDoc = location.href.split("?")[0];
  document.require("js/ui.effect.js");
  document.require("js/ui.calendar/calendar.css",null, "css");
});

ui.calendar = function(sender, target, year, month, date)
{
  this.textBox = target;
  this.sender = sender;
  var regExp = /^(\d{4})-0?(\d{1,2})-0?(\d{1,2})$/;
  var res = this.textBox.value.match(regExp);
  if (res != null) {
    year = res[1];
    month = parseInt(res[2], 10)>0 ? parseInt(res[2], 10) - 1 : 0;
    date = res[3];
  }
  var today;
  if (year) today = new Date(year, month, date);
  else today = new Date();
  this.year = this.currentYear = today.getFullYear();
  this.month = this.currentMonth = today.getMonth();
  this.currentDay = today.getDate();
  this.date = today.getDate();
  this.day = today.getDay();
  this.currentItem = null;

  this.box = $ce("div");
  this.box.className = "calendar";
  this.box.innerHTML = '<iframe href="return false" frameborder="no" style="width:182px;height:170px;filter:alpha(opacity=0);position:absolute; border:0; visibility:inherit; top:0px; left:0px; z-index:-1;"></iframe>';

  var btnBar = $ce("div");
  btnBar.className = "btnbar";

  this.prevBtn = $ce("SPAN");
  this.prevBtn.className = "calendarprev";
  this.prevBtn.onclick = this.prevMonth.bind(this);
  btnBar.appendChild(this.prevBtn);

  this.yearMonthSpan = $ce("span");
  btnBar.appendChild(this.yearMonthSpan);

  this.nextBtn = $ce("SPAN");
  this.nextBtn.className = "calendarnext";
  this.nextBtn.onclick = this.nextMonth.bind(this);
  btnBar.appendChild(this.nextBtn);

  this.box.appendChild(btnBar);
  this.slideBar = $ce("DIV");
  this.slideBar.className = "slideBar";
  this.box.appendChild(this.slideBar);

  this.group = $ce("DIV");
  this.group.style.width = "9999px";

  this.slideBar.appendChild(this.group);
  this.lang = null;
  var self = this;

  document.body.appendChild(this.box);

  if (typeof(this.box.onselectstart) != "undefined") {
    this.box.onselectstart= function(){return false;};
  } else {
    this.box.style.cssText = "-moz-user-select:none";
  };

  this.slideBar.scrollLeft = 0;
  var pos = Element.getPosition(target);
  this.box.style.left = pos.left + "px";
  this.box.style.top = (pos.top + target.offsetHeight) + "px";
  this.lang = lang;
  this.construct();
  this.yearMonthSpan.className = "yms";
  this.yearMonthSpan.innerHTML = this.lang.dateFormat.replace("$year",this.year).replace("$month",this.month + 1);
  this.yearMonthSpan.onclick = function() {
    if (this.lastChild.tagName != "SELECT"){
      this.innerHTML = "";
      var list = self.getYearMonthList();
      self.yearMonthSpan.appendChild(list);
      list.focus();
    }
  };
  this.tmpFunc = new Object();
  this.tmpFunc.blur = this.blur.bind(self);

  Event.observe(document, "mousedown", self.tmpFunc.blur);
};

ui.calendar.prototype = {
  getYearMonthList : function() {
    var y = 1970;
    var list = $ce("SELECT");
    for (y; y < 2039; y++) {
      for (var m = 1; m <= 12; m++) {
        var idx = list.options.length;
        list.options[idx] = new Option();

        if (this.currentYear == y && (this.currentMonth + 1) == m)
          list.options[idx].selected = true;
        list.options[idx].value = y + "-" + m;
        list.options[idx].text = this.lang.dateFormat.replace("$year", y).replace("$month", m);
      }
    }

    list.onBlur = function() {
      Element.remove(this);
    };
    var self = this;
    list.onchange = list.onblur = function() {
      var date = this.value.split("-");
      text = self.lang.dateFormat.replace("$year", date[0]).replace("$month", date[1]);
      self.currentYear = parseInt(date[0]);
      self.currentMonth = parseInt(date[1]) - 1;
      self.clear();
      self.construct();
      this.parentNode.innerHTML = text;
    };
    return list;
  },
  dispose : function() {
    document.body.removeChild(this.box);
  },
  blur:function(event){
    if(event.srcElement == this.textBox || event.srcElement == this.sender)
      return;
    var elem = event.srcElement;
    if (!Element.contains(this.box, elem))
      this.hide();
  },
  construct : function() {
    var dayNum = this.getMonthDay(this.currentYear, this.currentMonth);
    var week = this.getWeek(this.currentYear, this.currentMonth, 1);
    var d = 1;
    var n = 1;

    var item = $ce("DIV");
    item.className = "item";

    var sb = new Array();

    sb.push("<ul class='weekbar'>");
    for (var j=0; j < 7;j++) {
      sb.push("<li>" + this.lang.week[j] + "</li>");
    }
    sb.push("</ul>");

    sb.push("<UL class='daylist'>");
    var prevMonthDay = false;
    var today = new Date();
    var val = "";
    var className = "";
    for(var i=1; i < 43; i++) {
      sb.push("<li");
      className = "day";
      ulClassName = "";

      if (i > week && d <= dayNum) {
        val = d;
        if (d == today.getDate() && today.getMonth() == this.currentMonth && today.getFullYear() == this.currentYear) {
          className = "today day";
        } else if (d == this.date && this.month == this.currentMonth && this.currentYear == this.year) {
          className = "selected day";
        }
        d++;
      } else {
        if (d==1) {
          var m = this.currentMonth, y = this.currentYear;
          if (m == 0) {
            m = 11; y--;
          } else {
            m--;
          }
          className = "prevMonth";
          if (! prevMonthDay)
            prevMonthDay = this.getMonthDay(y, m);
          var day = prevMonthDay - week + i;
          val = day;
        } else {
          className = "nextMonth";
          val = n;
          n++;
        }
      }
      sb.push(" class='" + className + "'>");
      sb.push(val);
      sb.push("</li>");
      if ((i % 7) == 0 && i < 42) {
        sb.push("</UL><UL class='daylist'>");
      }
    };
    sb.push("</UL>");
    item.innerHTML = sb.join("");
    var currentDay = $class("selected", item, 'li');
    if (currentDay.length > 0)
      Element.addClass(currentDay[0].parentNode,"currentline");
    else {
      currentDay = $class("today",item, 'li');
      if (currentDay.length > 0)
        Element.addClass(currentDay[0].parentNode,"currentline");
    }
    this.currentItem = item;
    item.setAttribute("date", this.lang.dateFormat.replace("$year",this.currentYear).replace("$month",this.currentMonth + 1));
    var self = this;
    item.onclick = function(e) {
      var srcElem = $(fixEvent(e).srcElement);
      if(Element.hasClass(srcElem, "day")) {
        self.select(srcElem.innerHTML);
        self.textBox.value = self.currentYear + "-" + (self.currentMonth + 1) + "-" + srcElem.innerHTML;
        self.hide();
        self.onSelect(e);
      }
      if(Element.hasClass(srcElem,"prevMonth")) {
        self.prevMonth();
        self.select(srcElem.innerHTML);
        return;
      }
      if(Element.hasClass(srcElem,"nextMonth")) {
        self.nextMonth();
        self.select(srcElem.innerHTML);
        return;
      }
    };

    /*
    item.ondbclick = function(e) {
      var srcElem = $(fixEvent(e).srcElement);
      if (Element.hasClass(srcElem, "day")) {
        self.textBox.value = self.currentYear + "-" + (self.currentMonth + 1) + "-" + srcElem.innerHTML;
        self.hide();
      }
      self.onSelect(e);
    };
    */
    if (arguments[0] == "inerst")
      this.group.insertBefore(item, this.group.firstChild);
    else
      this.group.appendChild(item);
  },
  onSelect : function() {},
  onStateChanged : function(){},
  hide : function() {
    Element.hide(this.box);
    this.onStateChanged();
  },
  show : function() {
    Element.show(this.box);
    this.box.focus();
  },
  select : function(day)
  {
    var elems = $class("day", this.currentItem,"li");
    var currentWeekUL = false;
    for (var i=0; i<elems.length; i++) {
      if(elems[i].innerHTML == day) {
        Element.addClass(elems[i], "selected");
        currentWeekUL = elems[i].parentNode;
      }
      else {
        Element.removeClass(elems[i],"selected");
        Element.removeClass(elems[i].parentNode,"currentline");
      }
    }
    if(currentWeekUL)Element.addClass(currentWeekUL, "currentline");
  },
  scrolling : false,
  nextMonth : function() {
    if (this.scrolling) return;

    if (this.currentMonth == 11) {
      this.currentYear++;
      this.currentMonth = 0;
    } else {
      this.currentMonth++;
    }
    if (Element.next(this.currentItem)==null) {
      this.construct();
    } else {
      this.currentItem = Element.next(this.currentItem);
    }
    this.select(0);
    this.yearMonthSpan.innerHTML = this.currentItem.getAttribute("date");
    var self = this;
    this.scrolling = true;
    ui.effect.scroll("left", this.slideBar, 182, 350, function() {
      self.scrolling=false;
      if(self.group.children.length>5) {
        Element.remove(self.group.children[0]);
        self.slideBar.scrollLeft -= 182;
      }
    });
  },
  prevMonth : function() {
    if (this.scrolling) return;
    if (this.currentMonth == 0) {
      this.currentYear--;
      this.currentMonth = 11;
    } else {
      this.currentMonth--;
    }

    if (Element.prev(this.currentItem) == null) {
      this.construct('inerst');
      this.slideBar.scrollLeft += 182;
    } else {
      this.currentItem = Element.prev(this.currentItem);
    }
    this.select(0);
    this.yearMonthSpan.innerHTML = this.currentItem.getAttribute("date");
    this.scrolling = true;
    var self = this;
    ui.effect.scroll("right", this.slideBar, 182, 350,  function() {
      if(self.group.children.length > 5) Element.remove(self.group.children[self.group.children.length - 1]);
      self.scrolling = false;
    });
  },
  getMonthDay:function(year,month){
    var arr = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if ((year % 4 == 0 && year % 100 > 0) || year % 400 == 0)arr[1]=29;
      return arr[month];
  },
  getWeek : function(year, month, day) {
    var d = new Date(year, month, day);
    return d.getDay();
  },
  clear : function() {
    while(this.group.children.length > 0) {
      Element.remove(this.group.children[0]);
    }
    this.slideBar.scrollLeft = 0;
  }
};