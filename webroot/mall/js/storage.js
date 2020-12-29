/**
 * ECMall: Storage
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.tabview.js 5096 2008-07-04 05:34:29Z Liupeng $
 */

var Storage = new Object();
Object.extend(Storage, (function(){
  var _storage = false;
  if (navigator.isIE()) document.documentElement.addBehavior("#default#userdata");
  if (typeof(sessionStorage) != 'undefined') _storage = sessionStorage;

  if (navigator.isIE()){
    var _add = function(key, value){
      with(document.documentElement)
      try {
        load(key);
        setAttribute("value", value);
        save(key);
        return  getAttribute("value");
      } catch (ex){
        return null;
      }
    };

    var _get = function(key) {
      with(document.documentElement)
      try {
        load(key);
        return  getAttribute("value");
      } catch (ex){
        return null;
      }
    };

    var _remove = function(key) {
      with(document.documentElement)
      try {
        load(key);
        removeAttribute("value");
        save(key);
      } catch(ex){}
    };

  } else if(navigator.isFirefox() && _storage){
    var _add = function(key, value){
      sessionStorage.setItem(key,value);
    };

    var _get = function(key) {
      return sessionStorage.getItem(key)
    };

    var _remove = function(key) {
      var value = undefined;
      sessionStorage.setItem(key,value);
    };
  } else {
    var _add = function(key, value){
      document.setCookie(key, value);
    };

    var _get = function(key) {
      return document.getCookie(key);
    };

    var _remove = function(key) {
      document.removeCookie(key);
    };
  }

  return {
    add: function(key, value) {
      if (typeof(value) != 'string') {
        value = value.toJSONString();
      }
      _add(key, value);
    },
    get: function(key){
      return _get(key);
    },
    remove: function(key){
      _remove(key);
    }
  };
})());

