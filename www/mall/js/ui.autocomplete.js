/**
 * ECMall: 自动补全类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.autocomplete.js 6009 2008-10-31 01:55:52Z Garbin $
 */

ui.autoComplete = function (input, uri, callback){
  var ac = this;
  this.input = $(input);
  if (typeof(callback) == 'function') this.callback = callback;
  this.db = new ui.autoCompleteDB(uri);
  input = this.input;
  input.onkeydown = function (event) {return ac.onkeydown(this, event);};
  input.onkeyup = function (event) {ac.onkeyup(this, event);};
  input.onblur = function () {ac.hidePopup();ac.db.cancel();};
  input.setAttribute('autoComplete', 'Off');
};

ui.autoComplete.prototype.onkeydown = function (input, e) {
  e = fixEvent(e);
  switch (e.keyCode){
  case 40: // down arrow
    this.selectDown();
    return false;
  case 38: // up arrow
    this.selectUp();
    return false;
  case 13: // enter
    e.cancelBubble = true;
    return true;
  default : // all other keys
    return true;
}};

ui.autoComplete.prototype.onkeyup = function (input, e){
  e = fixEvent(e);
  switch (e.keyCode){
    case 16: // shift
    case 17: // ctrl
    case 18: // alt
    case 20: // caps lock
    case 33: // page up
    case 34: // page down
    case 35: // end
    case 36: // home
    case 37: // left arrow
    case 38: // up arrow
    case 39: // right arrow
    case 40: // down arrow
      return true;

    case 13: // enter
    case 9:  // tab
    case 27: // esc
      this.hidePopup(e.keyCode);
      return true;

    default: // all other keys
      if (input.value.length > 0){
        this.populatePopup();
      }else{
        this.hidePopup(e.keyCode);
      }
      return true;
}};

/**
 * Puts the currently highlighted suggestion into the autocomplete field
 */
ui.autoComplete.prototype.select = function (node) {
  this.input.value = node.autocompleteValue;
};

/**
 * Highlights the next suggestion
 */
 ui.autoComplete.prototype.selectDown = function (){
  if (this.selected && this.selected.nextSibling){
    this.highlight(this.selected.nextSibling);
  } else {
    if (!this.popup || (this.popup && this.popup.innerHTML.isEmpty())) this.populatePopup();
    if (this.popup && (!this.popup.innerHTML.isEmpty())) {
      var lis = this.popup.getElementsByTagName('li');
      if (lis.length > 0) this.highlight(lis[0]);
 }}};
/**
 * Highlights the previous suggestion
 */
 ui.autoComplete.prototype.selectUp = function () {
  if (this.selected && this.selected.previousSibling) this.highlight(this.selected.previousSibling);
 };

/**
 * Highlights a suggestion
 */
 ui.autoComplete.prototype.highlight = function (node) {
  if (this.selected) this.selected.className = '';
  node.className = 'selected';
  this.selected = node;
 };

 /**
 * Unhighlights a suggestion
 */
 ui.autoComplete.prototype.unhighlight = function (node) {
  node.className = '';
  this.selected = false;
 };

 /**
 * Hides the autocomplete suggestions
 */
ui.autoComplete.prototype.hidePopup = function (keycode) {
  // Select item if the right key or mousebutton was pressed
  if (this.selected && ((keycode && keycode != 46 && keycode != 8 && keycode != 27) || !keycode)){
    this.input.value = this.selected.autocompleteValue;
    if (this.callback) this.callback(this.input, this.selected.hideValue);
    }
  // Hide popup
  var popup = this.popup;
  if (popup) {
    this.popup = null;
    Element.remove(popup);
  }
  this.selected = false;
};

/**
 * Positions the suggestions popup and starts a search
 */
 ui.autoComplete.prototype.populatePopup = function (){
  // Show popup
  if (this.popup) Element.remove(this.popup);
  this.selected = false;
  this.popup = $ce('DIV');
  this.popup.id = 'searchMenu';
  this.popup.owner = this;
  var pos = Element.getPosition(this.input);
  this.popup.style.top = (pos.top + this.input.offsetHeight) + 'px';
  this.popup.style.left = (pos.left) + 'px';
  //this.popup.style.marginLeft = this.input.offsetWidth + 'px';
  //this.popup.style.width = (this.input.offsetWidth) + 'px';
  Element.hide(this.popup);
  document.body.appendChild(this.popup);

  // Do search
  this.db.owner = this;

  this.db.search(this.input.value);
 };

 /**
 * Fills the suggestion popup with any matches received
 */
 ui.autoComplete.prototype.found = function (matches) {
  // If no value in the textfield, do not show the popup.
  //if (!this.input.value.length) return false;
  // Prepare matches
  var ul = $ce('ul');
  var ac = this;
  for (key in matches) {
    if (typeof(matches[key][0]) !='string') continue;
    var li = $ce('LI');
    li.innerHTML = matches[key][1];
    li.onmousedown = function () {ac.select(this);};
    li.onmouseover = function () {ac.highlight(this);};
    li.onmouseout = function () {ac.unhighlight(this)};
    li.autocompleteValue = matches[key][0];
    if (matches[key][2]) li.hideValue = matches[key][2];
    ul.appendChild(li);
  }
  // Show popup with matches, if any
  if (this.popup) {
    this.popup.innerHTML = '';
    if (ul.childNodes.length > 0) {
      this.popup.appendChild(ul);
      Element.show(this.popup);
      if (this.popup.offsetWidth < this.input.offsetWidth){
        this.popup.style.width = (this.input.offsetWidth) + 'px';
    }} else {
      this.hidePopup();
 }}};

 ui.autoComplete.prototype.setStatus = function (status) {
  switch (status) {
  case 'begin':
    this.input.className = 'throbbing';
    break;
  case 'cancel':
  case 'error':
  case 'found':
    this.input.className ='';
    break;

 }};

/**
 * An AutoComplete DataBase object
 */
ui.autoCompleteDB = function (uri) {
  this.uri = uri;
  this.delay = 300;
  this.cache = {};
};

/**
 * Performs a cached and delayed search
 */
 ui.autoCompleteDB.prototype.search = function (searchString) {
  var db = this;
  this.searchString = searchString;

  // See if this key has been searched for before
  if (this.cache[searchString]) {
    return this.owner.found(this.cache[searchString]);
  };

  // Initiate delayed search
  if (this.timer) clearTimeout(this.timer);
  this.timer = setTimeout(function () {
    db.owner.setStatus('begin');
    // Ajax GET request for autocompletion
    Ajax.addVal('q', searchString);
    Ajax.call(db.uri, function (result) {
      if (result.msg && result.msg.length > 0) alert(result.msg);
      if (result.done) {
        db.cache[searchString] = result.retval;
        // Verify if these are still the matches the user wants to see
        if (db.searchString == searchString) db.owner.found(result.retval);
      }
      db.owner.setStatus('found');
    }, 'get');
  }, this.delay);
 };

/**
 * Cancels the current autocomplete request
 */
 ui.autoCompleteDB.prototype.cancel = function () {
  if (this.owner) this.owner.setStatus('cancel');
  if (this.timer) clearTimeout(this.timer);
  this.searchString = '';
 };