var $ = function (id) {
 return "string" == typeof id ? document.getElementById(id) : id;
};
var Extend = function(destination, source) {
 for (var property in source) {
  destination[property] = source[property];
 }
 return destination;
}
var CurrentStyle = function(element){
 return element.currentStyle || document.defaultView.getComputedStyle(element, null);
}
var Bind = function(object, fun) {
 var args = Array.prototype.slice.call(arguments).slice(2);
 return function() {
  return fun.apply(object, args.concat(Array.prototype.slice.call(arguments)));
 }
}
var Tween = {
 Quart: {
  easeOut: function(t,b,c,d){
   return -c * ((t=t/d-1)*t*t*t - 1) + b;
  }
 },
 Back: {
  easeOut: function(t,b,c,d,s){
   if (s == undefined) s = 1.70158;
   return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
  }
 },
 Bounce: {
  easeOut: function(t,b,c,d){
   if ((t/=d) < (1/2.75)) {
    return c*(7.5625*t*t) + b;
   } else if (t < (2/2.75)) {
    return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
   } else if (t < (2.5/2.75)) {
    return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
   } else {
    return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
   }
  }
 }
}
//ÈÝÆ÷¶ÔÏó,»¬¶¯¶ÔÏó,ÇÐ»»ÊýÁ¿
var SlideTrans = function(container, slider, count, options) {
 this._slider = $(slider);
 this._container = $(container);//ÈÝÆ÷¶ÔÏó
 this._timer = null;//¶¨Ê±Æ÷
 this._count = Math.abs(count);//ÇÐ»»ÊýÁ¿
 this._target = 0;//Ä¿±êÖµ
 this._t = this._b = this._c = 0;//tween²ÎÊý
 
 this.Index = 0;//µ±Ç°Ë÷Òý
 
 this.SetOptions(options);
 
 this.Auto = !!this.options.Auto;
 this.Duration = Math.abs(this.options.Duration);
 this.Time = Math.abs(this.options.Time);
 this.Pause = Math.abs(this.options.Pause);
 this.Tween = this.options.Tween;
 this.onStart = this.options.onStart;
 this.onFinish = this.options.onFinish;
 
 var bVertical = !!this.options.Vertical;
 this._css = bVertical ? "top" : "left";//·½Ïò
 
 //ÑùÊ½ÉèÖÃ
 var p = CurrentStyle(this._container).position;
 p == "relative" || p == "absolute" || (this._container.style.position = "relative");
 this._container.style.overflow = "hidden";
 this._slider.style.position = "absolute";
 
 this.Change = this.options.Change ? this.options.Change :
  this._slider[bVertical ? "offsetHeight" : "offsetWidth"] / this._count;
};
SlideTrans.prototype = {
  //ÉèÖÃÄ¬ÈÏÊôÐÔ
  SetOptions: function(options) {
 this.options = {//Ä¬ÈÏÖµ
  Vertical: true,//ÊÇ·ñ´¹Ö±·½Ïò£¨·½Ïò²»ÄÜ¸Ä£©
  Auto:  true,//ÊÇ·ñ×Ô¶¯
  Change:  0,//¸Ä±äÁ¿
  Duration: 50,//»¬¶¯³ÖÐøÊ±¼ä
  Time:  10,//»¬¶¯ÑÓÊ±
  Pause:  4000,//Í£¶ÙÊ±¼ä(AutoÎªtrueÊ±ÓÐÐ§)
  onStart: function(){},//¿ªÊ¼×ª»»Ê±Ö´ÐÐ
  onFinish: function(){},//Íê³É×ª»»Ê±Ö´ÐÐ
  Tween:  Tween.Quart.easeOut//tweenËã×Ó
 };
 Extend(this.options, options || {});
  },
  //¿ªÊ¼ÇÐ»»
  Run: function(index) {
 //ÐÞÕýindex
 index == undefined && (index = this.Index);
 index < 0 && (index = this._count - 1) || index >= this._count && (index = 0);
 //ÉèÖÃ²ÎÊý
 this._target = -Math.abs(this.Change) * (this.Index = index);
 this._t = 0;
 this._b = parseInt(CurrentStyle(this._slider)[this.options.Vertical ? "top" : "left"]);
 this._c = this._target - this._b;
 
 this.onStart();
 this.Move();
  },
  //ÒÆ¶¯
  Move: function() {
 clearTimeout(this._timer);
 //Î´µ½´ïÄ¿±ê¼ÌÐøÒÆ¶¯·ñÔò½øÐÐÏÂÒ»´Î»¬¶¯
 if (this._c && this._t < this.Duration) {
  this.MoveTo(Math.round(this.Tween(this._t++, this._b, this._c, this.Duration)));
  this._timer = setTimeout(Bind(this, this.Move), this.Time);
 }else{
  this.MoveTo(this._target);
  this.Auto && (this._timer = setTimeout(Bind(this, this.Next), this.Pause));
 }
  },
  //ÒÆ¶¯µ½
  MoveTo: function(i) {
 this._slider.style[this._css] = i + "px";
  },
  //ÏÂÒ»¸ö
  Next: function() {
 this.Run(++this.Index);
  },
  //ÉÏÒ»¸ö
  Previous: function() {
 this.Run(--this.Index);
  },
  //Í£Ö¹
  Stop: function() {
 clearTimeout(this._timer); this.MoveTo(this._target);
  }
};
//new SlideTrans("idContainer", "idSlider", 3).Run();
///////////////////////////////////////////////////////////
var forEach = function(array, callback, thisObject){
 if(array.forEach){
  array.forEach(callback, thisObject);
 }else{
  for (var i = 0, len = array.length; i < len; i++) { callback.call(thisObject, array[i], i, array); }
 }
}
var st = new SlideTrans("idContainer2", "idSlider2", 4, { Vertical: false });
var nums = [];
//²åÈëÊý×Ö
for(var i = 0, n = st._count - 1; i <= n;){
 (nums[i] = $("idNum").appendChild(document.createElement("li"))).innerHTML = ++i;
}
forEach(nums, function(o, i){
 o.onclick = function(){ o.className = "on"; st.Auto = false; st.Run(i); }
 o.onmouseout = function(){ o.className = ""; st.Auto = true; st.Run(); }
})
//ÉèÖÃ°´Å¥ÑùÊ½
st.onStart = function(){
 forEach(nums, function(o, i){ o.className = st.Index == i ? "on" : ""; })
}
//$("idAuto").onclick = function(){
 //if(st.Auto){
  //st.Auto = false; st.Stop(); this.value = "×Ô¶¯";
 //}else{
  //st.Auto = true; st.Run(); this.value = "Í£Ö¹";
 //}
//}
//$("idNext").onclick = function(){ st.Next(); }
//$("idPre").onclick = function(){ st.Previous(); }
//$("idTween").onchange = function(){
 //switch (parseInt(this.value)){
  //case 2 :
   //st.Tween = Tween.Bounce.easeOut; break;
  //case 1 :
   //st.Tween = Tween.Back.easeOut; break;
  //default :
   //st.Tween = Tween.Quart.easeOut;
 //}
//}
st.Run();
