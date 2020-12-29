
/**
 * ECMall: Ajax 类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ajax.js 6107 2008-11-24 05:20:47Z Garbin $
 */

var Ajax = {
  /* 自动触发 */
  _onComplete : function () {},
  _onRunning : function () {},
  method : 'GET',

  /* 设置返回数据类型JSON TEXT XML */
  setReturnType : function (type) {
    if (typeof(type) == 'string' && (type.toUpperCase() == 'JSON' || type.toUpperCase() == 'XML' || type.toUpperCase() == 'TEXT')) {
      this.returnType = type.toUpperCase();
  }},

  /* 设置是否是异步，true or false */
  addVal : function (key, val) {
    if (!this.data) this.data = new Object;
    this.data[key] = val;
  },

  /* 发送http请求，url 为请求地址，callback 为回调函数可为空， 第三个参数为发送数据，可以支持对象、字串或者混合
   * call函数默认使用异步，返回值为json格式
   */
  call : function (url, callback, method, asyn) {
    /* 参数处理 */
    if (typeof(method) == 'string' && (method.toUpperCase() == 'GET' || method.toUpperCase() == 'POST'))
      method = method.toUpperCase();
    else{
      if (data && data.length>0)
        method = 'POST';
      else
        method = 'GET';
    }
    if (url.indexOf('http') != 0)
    {
        url = location.href.substr(0,location.href.lastIndexOf('/')) + '/' + url;
    }
    this.method = method;

    var data = '';
    if (this.data) {
      data += this.joinData(this.data);
      delete(this.data);
    }

    var returnType = '';
    if (this.returnType) {
      returnType = this.returnType;
      delete(this.returnType);
    }else
      returnType = 'JSON';

    if (asyn != undefined)
      asyn = asyn ? true : false;
    else
      asyn = true;


    if (method == "GET"){
      url += url.indexOf("?") >= 0 ? "&" : "?";
      if (data && data.length > 0) {url += data; data =''}
    }

    /* 创建XMLHttpRequest */
    if (window.XMLHttpRequest) {
        var xhr = new XMLHttpRequest();
    }else{
        var MSXML = ['MSXML2.XMLHTTP.6.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP'];
        for(var n = 0; n < MSXML.length; n ++) {
            try {
                var xhr = new ActiveXObject(MSXML[n]);
                break;
            } catch(e) {}
    }}

    /* 发送数据 */

    //xhr.setTimeouts(30*1000,30*1000,30*1000,30*60*1000); //参数(Resolve, Connect, Send, Receive);
    try {
      if (typeof(this._onRunning) == 'function') this._onRunning();
      xhr.open(method, url, asyn);
      xhr.setRequestHeader('Ajax-Request', "1");
      if (method == 'POST')
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=utf-8');
      /* 异步 */
      if (asyn) {
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (typeof(Ajax._onComplete) == 'function') Ajax._onComplete();
                if (xhr.status == 200) {
                  if (typeof(callback) == 'function') {
                    var result = Ajax.parseResult(xhr, returnType);
                    callback(result, xhr.responseText);
                }}else{
                  //http错误
                  throw("An HTTP error " + xhr.status + "occurred. \n" + url);
                }
                xhr = null;
            }
        };
        if (xhr != null) xhr.send(data);
      }else{
        /* 同步 */
       /* running */
       xhr.send(data);
       if (typeof(Ajax._onComplete) == 'function') Ajax._onComplete();
       if (xhr.status == 200) {
         var result = this.parseResult(xhr, returnType);

         if (typeof(callback) == 'function') callback(result, xhr.responseText);
         return result;
        }else{
          //http错误
          throw("An HTTP error " + xhr.status + "occurred. \n" + url);
      }}
    }catch(e){alert(e);}
  },

  joinData : function (param, pre){
    var returnVal = '';
    if (typeof(param) == 'string') {
        var pos = param.indexOf('=');
        if (pos > 0)
          returnVal += this.encode(param.substr(0, pos)) + '=' + this.encode(param.substr(pos+1)) + '&';
        else
          returnVal += 'noindex[]=' + this.encode(param) + '&';
    }
    else if (typeof(param) == 'object') {
      for (n in param) {
        switch (typeof(param[n])) {
          case 'string':
            if (pre == undefined) {
              returnVal += n + '=' + this.encode(param[n]) + '&';
            } else {
              returnVal += pre + '[' + n + ']=' + this.encode(param[n]) + '&';
            }
            break;

          case 'number':
            if (pre == undefined) {
              returnVal += n + '=' + param[n] + '&';
            } else {
              returnVal += pre + '[' + n + ']=' + param[n] + '&';
            }
            break;

          case 'boolean':
            var val = param[i] ? 1 : 0;
            if (pre == undefined) {
              returnVal += n + '=' + val + '&';
            } else {
              returnVal += pre + '[' + n + ']=' + val + '&';
            }
            break;

          case 'object':
            if(param[n].length == 0){
              if (pre == undefined) {
                returnVal += n + '=&';
              } else {
                returnVal += pre + '[' + n + ']=&';
            }}else{
              if (pre == undefined) {
                returnVal += this.joinData(param[n], n);
              } else {
                returnVal += this.joinData(param[n], pre + '[' + n + ']');
              }
            }
            break;

          default:
        }
      }
    }
    if (pre == undefined)  returnVal = returnVal.substr(0, returnVal.length -1); //截取最后的&
    return returnVal;
  },

  encode : function(str) {
    return encodeURIComponent(str);
  },

  /* 解析结果 */
  parseResult : function (xhr, returnType) {
    var result;
    if (returnType == 'JSON') {
      /*解析JSON*/
      result = Ajax.parseJSON(xhr.responseText);
      if (!result) result = {};
    }
    else if (returnType == 'TEXT') {
      result = xhr.responseText;
    }
    else if (returnType == 'XML') {
      result = xhr.responseXML;
    }

    return result;
  },

  parseJSON : function (filter) {
    try {
        if (/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.
                test(filter)) {
            var j = eval('(' + filter + ')');
            if (typeof filter === 'function') {

                function walk(k, v) {
                    if (v && typeof v === 'object') {
                        for (var i in v) {
                            if (v.hasOwnProperty(i)) {
                                v[i] = walk(i, v[i]);
                    }}}
                    return filter(k, v);
                }
                j = walk('', j);
            }
            return j;
        }
    } catch (e) {
    // Fall through if the regexp test fails.
}}};
