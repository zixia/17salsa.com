/**
 * ECMall: 图片预览类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.imgpreviewer.js 6071 2008-11-14 10:42:21Z Garbin $
 */

function initImgPreviewer (cName, picIcon) {
  if (typeof cName == "undefined") cName = "icon-picture";
  if (typeof picIcon == "undefined") picIcon = "admin/images/icon_picture.gif";

  var icons = $class(cName);
  for (var i=0; i<icons.length; i++) {
    if (icons[i].src != document.location && icons[i].src != "") {
      var o = new ui.imgPreviewer(icons[i], picIcon);
      if (icons[i].getAttribute('enable_drop') != 'disabled') {
        o.onDrop = function() {dropPicture(this.src, this.img);}
      }
    }
  }
}

ui.imgPreviewer = function(img, icon) {
  var tmp_img = new Image();
  tmp_img.src = img.src;
  img.orig_w = tmp_img.width;
  img.orig_h = tmp_img.height;
  this.img = img;
  this.src = img.src;
  this.frame = null;
  this.width = (img.offsetWidth > 400) ? 400 : img.offsetWidth;
  this.height = img.offsetHeight / img.offsetWidth * this.width;
  this.picture = null;
  this.dropIcon = null;
  this.onDrop = null;
  var self = this;

  with (img) {
    src = icon;
    style.visibility = "visible";
    width = 16;
    height = 16;
    cursor = "pointer";
  }
  img.onclick = this.show.bind(this);
  Event.observe(document, 'click', this.hide.bind(this));
  //Event.observe(img, 'error', this.hide.bind(this));
};

ui.imgPreviewer.prototype.show = function(e) {
  if (this.frame == null) {
    this.frame = $ce("div");
    this.picture = $ce("img");
    document.body.appendChild(this.frame);

    var pos = Element.getPosition(this.img);
    this.picture.src = this.src;
    this.picture.style.width = "0px";
    this.picture.style.height = "0px";
    this.frame.appendChild(this.picture);
    if (this.img.getAttribute('enable_drop') != 'disabled') {
        this.dropIcon = new Image();
        this.dropIcon.src = "admin/images/icon_drop.gif";
        this.dropIcon.onclick = (function() {
          if (typeof this.onDrop == "function") { this.onDrop(); }
        }).bind(this);

        with (this.dropIcon.style) {
          margin = "1px";
          cursor = "pointer";
          position = "absolute";
          right = "0px";
          top = "0px";
          display = "none";
        }
        this.frame.appendChild(this.dropIcon);
        this.tmpToggleDropIcon = this.toggleDropIcon.bind(this);
        Event.observe(document, 'mousemove', this.tmpToggleDropIcon);
    }
    with (this.frame.style) {
      position = "absolute";
      left = (pos.left) + "px";
      top = (pos.top) + "px";
      display = "none";
      border = "5px solid #CCC";
    }
  }
  if (this.frame.style.display == "none") {
    this.frame.style.display = "";
    this.picture.style.width = "0px";
    this.picture.style.height = "0px";
    if (this.width == 0 || this.height == 0)
    {
       if(this.img.orig_h > this.img.orig_w) {
           h = 200;
           w = (this.img.orig_w * h) / this.img.orig_h;
       } else {
           w = 200;
           h = (this.img.orig_h * w) / this.img.orig_w;
       }
       ui.effect.ResizeTo(this.picture, w, h, 200);
    }
    else
    {
      ui.effect.ResizeTo(this.picture, this.width, this.height, 200);
    }
    ui.effect.FadeTo(this.frame, 0, 100, 200);
  }
};

ui.imgPreviewer.prototype.hide = function(e) {
  var evt = fixEvent(e);
  if (this.frame != null && evt.srcElement != this.img) this.frame.style.display = "none";

  //Event.stopObserving(document, 'mousemove', this.tmpToggleDropIcon);
};

ui.imgPreviewer.prototype.toggleDropIcon = function(e) {
  var evt = fixEvent(e);

  if (Element.contains(this.frame,evt.srcElement)) {
    if (this.dropIcon.style.display == "none") {
      this.dropIcon.style.display = "inline";
      ui.effect.FadeTo(this.dropIcon, 0, 80, 500);
    }
      //alert(this.dropIcon.style.display);
  } else {

    this.dropIcon.style.display = "none";
  }
};
