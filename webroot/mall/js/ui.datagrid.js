/**
 * ECMall: Data Grid
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.datagrid.js 6009 2008-10-31 01:55:52Z Garbin $
 */
ui.DataGrid = function(table) {
  this.table = table;
  this.init();
  var arr = document.location.href.split("?");
  var tmp = arr[1].split("&");
  var _self = this;
  this.url = arr[0];
  for (var i=0; i<tmp.length; i++){
   var item = tmp[i].split("=");
   if(item[0].toLowerCase() == "app") this.url += "?app=" + item[1];
  }
  this.action = "modify";

  var checkAll = $class("checkAll", this.table, "INPUT")[0];

  if (checkAll) {
    checkAll.onclick = function() {
      checkboxs = _self.table.getElementsByTagName("INPUT");
      for(var j=0; j<checkboxs.length; j++){
        if (this.checked) {
          if (!checkboxs[j].disabled)
            checkboxs[j].checked = true;
        } else
          checkboxs[j].checked = false;
      }
    };
  }

  if (this.table) {
    var tmp = $class('batch-link', this.table, 'li');
    for (var i=0; i < tmp.length; i++)
    {
      if (tmp[i].getAttribute("action") == null)
        break;

      tmp[i].onclick = function(e) {
        var evt = fixEvent(e);
        var action = this.getAttribute("action");
        var param = this.getAttribute("param");
        if (param == 'drop') {
          if (!confirm(lang.delete_confirm)) {
            return false;
          }
        }
        _self.batch(action, param);
        evt.cancelBubble = true;
      };
    }
    table.onmousemove = function(e) {
      var row = _self.currRow(e);
      if (row != null) row.style.backgroundColor = "#EAF5FB";
    };

    table.onmouseout = function(e) {
      var row = _self.currRow(e);
      if (row != null) row.style.backgroundColor = "";
    };
  }
};

ui.DataGrid.prototype = {
  init : function(){
    this.columns = new Array();
    var tableHead = this.table.rows[0];
    for (var i=0; i<tableHead.children.length; i++) {
      var column = tableHead.children[i];
      this.columns[i]= {
       dataType: column.getAttribute("dataType"),
       columnName: column.getAttribute("columnName"),
       readOnly: column.getAttribute("readOnly") == "false" ? false : true,
       dropDownList: column.getAttribute("dropDownList") != null ? $(column.getAttribute("dropDownList")) : false,
       required : column.getAttribute("required") == "false" ? false : true
      }
    }

    var self = this;
    for (var j=1; j<this.table.rows.length; j++) {
      var row = this.table.rows[j];
      if (row.getAttribute('readOnly')=='true') continue;
      if (row.cells.length != this.columns.length) break;
      for (var k=0; k<row.cells.length; k++) {
        if (!this.columns[k].readOnly) {
          if (this.columns[k].dataType == "bool") {
            var elems = row.cells[k].getElementsByTagName("IMG");
            if (elems.length > 0){
              elems[0].onclick = (function(key) {
                var cell = row.cells[key];
                return function() {
                  var attribute = self.columns[key];
                  Object.extend(cell, attribute);
                  self.click(cell, this);
                }
              })(k);
            }
          } else {
            var elems = row.cells[k].getElementsByTagName("span");
            row.cells[k].onclick = (function(key){
              return function(e) {
                if (fixEvent(e).srcElement.tagName.toLowerCase() != "span")
                  return ;
                var attribute = self.columns[key];
                Object.extend(this, attribute);
                self.edit(this);
              }
            })(k);
          }
        }
      }
    }
  },
  click : function(cell, e) {
    var value = parseInt(e.getAttribute("value"));
    value = value == 0 ? 1 : 0;
    if (res = this.update(cell.parentNode.id, cell.columnName, value)) {
      if (res.done == false) {
        alert(res.msg);
        return;
      }
      var path = e.src.substring(0, e.src.lastIndexOf("/")+1);
      e.src = path + (value == 1 ? "yes.gif" : "no.gif");
      e.setAttribute("value", value);
    }
  },
  edit : function(cell)
  {
    var b = $class('editbox', cell);
    if (b.length > 0)
      return;
    var span = cell.getElementsByTagName("span");
        span = span[0];
    var org = cell.innerHTML;
    var text = span.innerText;
    var input = null;
    // 如果不是下拉列表
    if (!cell.dropDownList) {
      input = $ce("INPUT");
      switch(cell.dataType)
      {
        case "string" :
          input.type = "text";
        break;
        case "int" :
          input.type = "text";
          input.checker = new dt_int();
          input.value = parseFloat(input.value);
        break;
        case "date" :
          input.type = "text";
          input.checker = new dt_date();
        break;
      }
    } else {
      input = cell.dropDownList;
      try {
        Element.show(input);
        for (var i=0; i< input.options.length; i++) {
          if(input.options[i].text == text) {
            input.options[i].selected = true;
            text = input.options[i].value;
          }
        }
      }
      catch(ex)
      {
        alert(ex.description);
      }
    }
    input.className = "editbox";
    input.onkeypress = function(event) {
      event = fixEvent(event);
      if (event.keyCode == 13) {
        this.blur();
      } else if (event.keyCode == 27) {
        try {
          cell.innerHTML = org;
        }
        catch(ex) {
          alert(ex.description);
        }
      }
    };

    var self = this;
    function blur() {
      if (input == null) return;
      try {
        if (cell.required&&input.value == "") {
          throw {type:2, message:lang.required};
        }
        if (text == input.value) {
          throw {type:2, message:'no change'};
        }
        if(input.checker) {
          if(!input.checker.check(input.value)) {
            throw {type:1, message:input.checker.errorMsg()};
          }
        }
        var res = self.update(cell.parentNode.id, cell.columnName, input.value);

        if (!res.done)
          throw {type:1, message:res.msg};
        if (input.tagName.toLowerCase() == "select")
          text = input.options[input.selectedIndex].text;
        else
          text = input.value.replace(/<.*?>/g,"");
      }
      catch(ex) {
        if (ex.type == 1)
          alert(ex.message);
      } finally {
        if (input.calendar) {
            input.calendar.dispose();
        }
        if (input.tagName.toLowerCase() == "select") {
          text = input.options[input.selectedIndex].text;
          Element.hide(input);
          document.body.appendChild(input);
        } else {
           input = null;
        }
        span.innerHTML = text;
      }

    }
    if (input.tagName.toLowerCase() != "select")
      input.style.width = (span.offsetWidth < 10 ? 40 : span.offsetWidth + 10) + "px";
    input.value = text;
    span.innerHTML = "";
    span.appendChild(input);

    if (input.select)
        input.select();
    if (cell.dataType == "date") {
      input.calendar = new ui.calendar(input, input);
      //如果是日期列进行特殊处理
      input.calendar.onSelect = blur;
      input.calendar.onStateChanged = function() {
        if (this.box.style.display == "none")
          blur();
      };
    }
    else
      input.onblur = blur;
    input.focus();

  },
  update : function(id, columnName, value) {
    Ajax.addVal("id", id);
    Ajax.addVal("column", columnName);
    Ajax.addVal("value", value.toString().replace(/<.*?>/g,""));

    var res = Ajax.call(this.url + "&act="+this.action, null, "GET", false);
    return res;
  },
  batch : function(action, param) {
    var obj = this.table.getElementsByTagName("INPUT");
    if (obj) {
      var checked = new Array();
      for (var i=0; i<obj.length; i++) {
        if (obj[i].type == "checkbox" && obj[i].name=="id" && obj[i].checked) {
          checked.push(obj[i].value);
      }}
      if (checked.isEmpty()) {
        alert(lang.selected_nothing);
      } else {
        var uri = location.href.replace(/act=\w+/, 'act='+action);
        location.href=uri + "&param=" + param + "&ids=" + checked.join(',');
      }
    }
  },
  currRow : function(e) {
    var elem = fixEvent(e).srcElement;
    do{
      if (elem.tagName == 'TR' && elem.firstChild.tagName != "TH") {
        var lastIdx = elem.parentNode.children.length - 1;
        if (elem.parentNode.children[lastIdx] != elem){
          return elem;
        }
      }
    } while((elem = elem.parentNode)!=this.table)
  }
};

