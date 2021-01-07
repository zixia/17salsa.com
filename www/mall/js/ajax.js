
/**
 * ECMall: Ajax ��
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ:  http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ajax.js 6107 2008-11-24 05:20:47Z Garbin $
 */

var Ajax = {
  /* �Զ����� */
  _onComplete : function () {},
  _onRunning : function () {},
  method : 'GET',

  /* ���÷�����������JSON TEXT XML */
  setReturnType : function (type) {
    if (typeof(type) == 'string' && (type.toUpperCase() == 'JSON' || type.toUpperCase() == 'XML' || type.toUpperCase() == 'TEXT')) {
      this.returnType = type.toUpperCase();
  }},

  /* �����Ƿ����첽��true or false */
  addVal : function (key, val) {
    if (!this.data) this.data = new Object;
    this.data[key] = val;
  },

  /* ����http����url Ϊ�����ַ��callback Ϊ�ص�������Ϊ�գ� ����������Ϊ�������ݣ�����֧�ֶ����ִ����߻��
   * call����Ĭ��ʹ���첽������ֵΪjson��ʽ
   */
  call : function (url, callback, method, asyn) {
    /* �������� */
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

    /* ����XMLHttpRequest */
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

    /* �������� */

    //xhr.setTimeouts(30*1000,30*1000,30*1000,30*60*1000); //����(Resolve, Connect, Send, Receive);
    try {
      if (typeof(this._onRunning) == 'function') this._onRunning();
      xhr.open(method, url, asyn);
      xhr.setRequestHeader('Ajax-Request', "1");
      if (method == 'POST')
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=utf-8');
      /* �첽 */
      if (asyn) {
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (typeof(Ajax._onComplete) == 'function') Ajax._onComplete();
                if (xhr.status == 200) {
                  if (typeof(callback) == 'function') {
                    var result = Ajax.parseResult(xhr, returnType);
                    callback(result, xhr.responseText);
                }}else{
                  //http����
                  throw("An HTTP error " + xhr.status + "occurred. \n" + url);
                }
                xhr = null;
            }
        };
        if (xhr != null) xhr.send(data);
      }else{
        /* ͬ�� */
       /* running */
       xhr.send(data);
       if (typeof(Ajax._onComplete) == 'function') Ajax._onComplete();
       if (xhr.status == 200) {
         var result = this.parseResult(xhr, returnType);

         if (typeof(callback) == 'function') callback(result, xhr.responseText);
         return result;
        }else{
          //http����
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
    if (pre == undefined)  returnVal = returnVal.substr(0, returnVal.length -1); //��ȡ����&
    return returnVal;
  },

  encode : function(str) {
    return encodeURIComponent(str);
  },

  /* ������� */
  parseResult : function (xhr, returnType) {
    var result;
    if (returnType == 'JSON') {
      /*����JSON*/
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
