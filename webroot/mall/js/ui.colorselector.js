/**
 * ECMall: Color Selector
 * ============================================================================
 * (C) 2005-2008 ShopEx Inc., all rights reserve.
 * Homepage:  http://www.shopex.cn
 * ============================================================================
 * $Id: ui.colorselector.js 6010 2008-10-31 02:18:11Z Garbin $
 */
ui.ColorSelector = function(sender, value)
{
    this.sender = sender;
    this.box = $ce("DIV");
    this.box.className = "color_selector";
    this.box.innerHTML = "<img src='admin/images/color.jpg' style='width:164px;height:164px;'/>";
    this.box.innerHTML = "<img src='admin/images/color.jpg' style='width:164px;height:164px;'/>";

    this.box.style.position = "absolute";
    var pos = Element.getPosition(sender);
    this.box.style.top = (pos.top + sender.offsetHeight) + "px";
    this.box.style.left = pos.left + "px";
    this.box.style.border = "#000 1px solid";
    this.box.style.zIndex = 9999;
    var div = $ce("div");

    this.block = $ce("input");
    this.textbox = $ce("input");
    this.block.style.backgroundColor = value;
    this.block.style.border = "none";
    this.block.style.width = "25px";

    this.textbox.style.width = "60px";
    this.block.style.border = "1px";
    this.textbox.style.height = "14px";
    this.textbox.style.margin = "4px 10px 0 4px";

    div.appendChild(this.block);
    div.appendChild(this.textbox);
    div.style.padding = "4px";
    div.style.backgroundColor = "#fff";

    this.btn = $ce("input");
    this.btn.value = " OK ";
    this.btn.type = "button";

    this.closeBtn = $ce("input");
    this.closeBtn.value = "quxiao";
    this.closeBtn.type = "button";
    div.appendChild(this.btn);
    this.box.appendChild(div);
    this.box.onclick = this.getColor.bind(this);

    this.head = $ce("div");
    this.head.style.height = "15px";
    this.head.style.width = "164px";
    this.head.style.textAlign = "right";
    this.head.style.backgroundColor = "#000";

    this.closeSpan = $ce("span");
    this.closeSpan.style.color = "#fff";
    this.closeSpan.innerHTML = "close";
    this.closeSpan.onclick = this.hide.bind(this);
    this.head.appendChild(this.closeSpan);
    this.box.insertBefore(this.head, this.box.firstChild);
    document.body.appendChild(this.box);
};

ui.ColorSelector.prototype = {
  setValue : function(value) {},
  hide : function() {
    Element.hide(this.box)
  },
  show : function() {
    var pos = Element.getPosition(this.sender);
    this.box.style.top = (pos.top + this.sender.offsetHeight) + "px";
    this.box.style.left = pos.left + "px";

    Element.show(this.box)
  },
  getColor: function(event) {
    event = fixEvent(event);
    var hex = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F'];
    var myTop = parseInt(this.box.style.top);
    var myLeft = parseInt(this.box.style.left);
    var scrollTop = (document.documentElement.scrollTop||document.body.scrollTop);
    if (event.clientX > (myLeft + this.box.offsetWidth) || event.clientX < myLeft || (event.clientY+scrollTop) > (myTop + 164 + 17) || (event.clientY+scrollTop) < myTop + 17)
      return;

    var hsv = new Object();
    var h = 164;
    var y = (event.clientY+scrollTop) - myTop - 17;
    hsv.h = 360 * (event.clientX - myLeft) / 164;
    if (y > h/2) {
      hsv.s = 1.0;
      hsv.v = 2 * (h - y) / h;
    } else {
      hsv.v = 1.0;
      hsv.s = y / (h/2);
    }
    var rgb = this.hsvToRgb(hsv);
    var red   = Math.round(255 * rgb.r);
    var green = Math.round(255 * rgb.g);
    var blue  = Math.round(255 * rgb.b);
    hexstr = '#' + hex[(red - (red % 16)) / 16].toString() + hex[red % 16].toString()
            + hex[(green - (green % 16)) / 16].toString() + hex[green % 16].toString()
            + hex[(blue - (blue % 16)) / 16].toString() + hex[blue % 16].toString();

    this.block.style.background = hexstr;
    this.textbox.value = hexstr;
    this.btn.onclick = (function() {
      this.setValue(hexstr);
      this.hide();
    }).bind(this);
  },

  hsvToRgb : function(hsv) {
    var rgb = new Object();
    var i, f, p, q, t;

    if (hsv.s == 0) {
      rgb.r = rgb.g = rgb.b = hsv.v;
      return rgb;
    }
    hsv.h /= 60;
    i = Math.floor( hsv.h );
    f = hsv.h - i;
    p = hsv.v * ( 1 - hsv.s );
    q = hsv.v * ( 1 - hsv.s * f );
    t = hsv.v * ( 1 - hsv.s * ( 1 - f ) );
    switch( i ) {
      case 0:
          rgb.r = hsv.v;
          rgb.g = t;
          rgb.b = p;
          break;
      case 1:
          rgb.r = q;
          rgb.g = hsv.v;
          rgb.b = p;
          break;
      case 2:
          rgb.r = p;
          rgb.g = hsv.v;
          rgb.b = t;
          break;
      case 3:
          rgb.r = p;
          rgb.g = q;
          rgb.b = hsv.v;
          break;
      case 4:
          rgb.r = t;
          rgb.g = p;
          rgb.b = hsv.v;
          break;
      default:
          rgb.r = hsv.v;
          rgb.g = p;
          rgb.b = q;
          break;
    }
    return rgb;
  }
};