/**
 * ECMall:
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.frontend.js 6009 2008-10-31 01:55:52Z Garbin $
 */

/**
 * 为页面中所有的头像加上菜单处理方法
 */

Event.observe(window, 'load', function() {
  elms = $class("avatar");
  for (var i=0; i<elms.length; i++) {
    elms[i].onmouseover = function() {var mnu = $class("avatar-menu", this);var lnk = $class("avatar-link", this);mnu[0].style.display="block";lnk[0].className = "avatar-hover"; };
    elms[i].onmouseout = function() {var mnu = $class("avatar-menu", this);var lnk = $class("avatar-hover", this);mnu[0].style.display="none"; lnk[0].className = "avatar-link"; };
  }

  check_pm();
  if ($('pm_button')) {
    $('pm_button').onclick = function () {
      pmwin('open');
    };
  }

  if ($('addcart')) {
    $('addcart').onmouseover = showCartStatus;
  }
  cart_status_tip = new Dialog(DIALOG_TIP);
  cart_status_tip.setSize(250);
  cart_status_tip.isSingle = true;
  cart_status_tip.isDraggable = false;
  cart_status_tip.autoCloseTime = 3;
  cart_status_tip.fadeTime      = 0.3;
});

function showCartStatus() {
  if (cart_status_tip) {
    if(!cart_status_tip.isClosed()){
      return;
    }
    var cont = lang.cart_status.replace('[cart_goods_count]', $('cart_goods_count').innerHTML);
    cont = cont.replace('[cart_goods_amount]', priceFormat($('hidden_cart_goods_amount').value));
    cart_status_tip.setContent('', cont);
    cart_status_tip.pointTo($('addcart'));
    cart_status_tip.show();
  }
}

/* 检查PM */
function check_pm() {
  if ($class("check_new_pm", "A").length > 0) {
    Ajax.call("index.php?app=member&act=check_new_pm", function(res){
      if (res.done) {
        var elem = $class("check_new_pm", "A")[0];
        if (!lang.pm) lang.pm = elem.innerHTML;
        if (res.retval == "new_pm") {
          elem.innerHTML = lang.new_pm;
          elem.style.color = "red";
        } else {
          elem.innerHTML = lang.pm;
          elem.style.color = "";
        }
        elem.onclick = function() {
          pmwin('open');
          if (elem.innerHTML == lang.new_pm){
            elem.innerHTML = lang.pm;
            elem.style.color = "";
          }
        }
      }
    });
  }
}

/* 添加好友 */
function add_friend(userId, noAlert)
{
    Ajax.addVal('ret_url', encodeURIComponent(location.href));
    Ajax.addVal("friend_id", userId);
    Ajax.call("index.php?app=member&act=add_friend", function(res){
        if (res.msg == "NO_LOGIN")
        {
            document.location.href = res.retval;
        }
        else
        {
            if (!noAlert)
            {
              var d = new Dialog(DIALOG_MESSAGE);
              d.setContent(lang.system_notice, res.msg);
              d.show();
            }
        }
    }, "GET", false);
}

/* tags 提交 */
function addTag(goods_id)
{
  var tag_words = $('tag_words').value;
  if (tag_words.length == 0){
    alert(lang.tag_empty);
    return false;
  }
  Ajax.addVal('tag_words', tag_words);
  Ajax.call('index.php?app=goods&act=add_tag&id=' + goods_id, addTagResponse);
  return false;
}

function addTagResponse(result)
{
  if (!result.done){
    if (result.msg == 'NO_LOGIN')
    {
      if (confirm(lang.no_login))
      {
        location.href = "index.php?app=member&act=login&ret_url=" + encodeURIComponent(location.href);
      }
      return;
    }
  } else {
    $("tag_content").innerHTML = result.retval;
    $('tag_words').value = "";
  }
  if (result.msg.length > 0){
    alert(result.msg);
  }
}

/* 商品相册 */
function loadNextImage()
{
  if (curImage + 1 > imageUrl.length){
    curImage = 1;
  }else{
    curImage ++;
  }
  gotoImage();
}

function loadPreImage()
{
  if (curImage - 1 < 1){
    curImage = imageUrl.length;
  } else {
    curImage --;
  }
  gotoImage();
}

function gotoImage()
{
  var elem = $class("curPage");
  for (var i=0; i<elem.length; i++ ){
    elem[i].innerHTML = curImage;
  }
  var newImage = imageUrl[curImage -1].replace(/&amp;/g, '&');
  $("previewImage").src = newImage;
}


/* 商品JS */
function addToFavorite(id)
{
  Ajax.call('index.php?app=member&act=add_favorite&id=' + id + '&ret_url=' + encodeURIComponent(location.href) , addToFavoriteRespond);
}

function addToFavoriteRespond(result, result_text){
  if (!result.done){
    if (result.msg == 'NO_LOGIN')
    {
      document.location.href = result.retval;
      return;
    }
  }
  if (result.msg.length > 0){
      alert(result.msg);
  }
}

function viewLargePic(){
  var pic = $("goods_pic").getAttribute("org");
  if (pic) {
    var win = window.open(pic ,'largePicture','width=800,height=600,resizable=yes');
    win.focus();
  }


  return false;
}

function changeGallery(event){
  var e = fixEvent(event);
  var elem = e.srcElement;
  if (elem.tagName == 'IMG' && elem.parentNode.tagName == 'A'){
    if (elem.parentNode.href != location.href){
      var gallerylist = $('gallerylist');
      for (var i=0;i< gallerylist.childNodes.length ;i++ ){
        if (gallerylist.childNodes[i].tagName == 'LI'){
          Element.removeClass(gallerylist.childNodes[i], "selected");
        }
      }

      var thumbImg = elem.parentNode.href;
      var thumb_org = elem.getAttribute("org");
      var goodsPic = $('goods_pic');
      goodsPic.src = thumbImg;
      goodsPic.setAttribute('org', thumb_org);
      if (typeof(_magnifier) == 'object') {_magnifier.setImage(thumb_org);}
      if (goodsPic.parentNode.style.backgroundImage) goodsPic.parentNode.style.backgroundImage = 'url(' + thumbImg + ')';
      Element.addClass(elem.parentNode.parentNode, "selected");
    }
  }
  return false;
}

function addToCart(spec_id, goods_num){
  if (!spec_id)
  {
    alert(lang.invalid_spec_id);
    return;
  }
  goods_num = Number(goods_num);
  if ($('addcart_button').disabled) {
    var d = new Dialog(DIALOG_MESSAGE);
    d.setContent(lang.sys_msg, lang.select_action_first);
    d.show();
    return;
  }
  if (!goods_num || goods_num == 'NaN')
  {
    var d = new Dialog(DIALOG_WARNING);
    d.setContent(lang.sys_msg, lang.invalid_goods_num);
    d.show();

    return;
  }
  $('addcart_button').disabled = true;
  Ajax.call('index.php?app=shopping&act=add_to_cart&goods_number=' + goods_num + '&spec_id=' + spec_id, addToCartMsg);
}

function addToCartMsg(response){
  if (!response.done)
  {
    var d = new Dialog(DIALOG_WARNING);
    d.onOK = function () {
      $('goods_num').select();
      d.close();
    };
    d.setContent(lang.error, response.msg);
    d.show();
  $('addcart_button').disabled = false;
    return;
  }

  var d = new Dialog(DIALOG_TIP);
  d.setContent('', response.retval.msg);
  d.pointTo($('addcart_button'));
  d.addButton(lang.checkout_button_name, function () {
    window.location.href="index.php?app=shopping&act=shopping_cart";
  }, true);
  d.addButton(lang.shopping_button_name, function () {
    d.close();
    $('goods_num').disabled = false;
    $('addcart_button').disabled = false;
    showCartStatus();
  });
  d.show();
  $('goods_num').disabled = true;
  $('hidden_cart_goods_count').value = response.retval.count;
  $('cart_goods_count').innerHTML = Number(response.retval.count);
  $('hidden_cart_goods_amount').value = response.retval.amount;
}

/*密码强度检查*/
function checkIntensity(pwd){
  var Mcolor = "#FFF",Lcolor = "#FFF",Hcolor = "#FFF";
  var m=0;
  var Modes = 0;
  for (i=0; i<pwd.length; i++){
    var charType = 0;
    var t = pwd.charCodeAt(i);
    if (t>=48 && t <=57){
      charType = 1;
    }else if (t>=65 && t <=90){
      charType = 2;
    }else if (t>=97 && t <=122)
      charType = 4;
    else
      charType = 4;
    Modes |= charType;
  }

  for (i=0;i<4;i++){
    if (Modes & 1) m++;
      Modes>>>=1;
  }
  if (pwd.length<=4){
    m = 1;
  }
  switch(m){
    case 1 :
      Lcolor = "2px solid red";
      Mcolor = Hcolor = "2px solid #DADADA";
    break;
    case 2 :
      Mcolor = "2px solid #f90";
      Lcolor = Hcolor = "2px solid #DADADA";
    break;
    case 3 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    case 4 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    default :
      Hcolor = Mcolor = Lcolor = "";
    break;
  }
  $("pwd_lower").style.borderBottom  = Lcolor;
  $("pwd_middle").style.borderBottom = Mcolor;
  $("pwd_high").style.borderBottom   = Hcolor;
}

/* 检查用户名是否合法 */
function checkUserName(username){
    var target = $("checkusername");
    Element.removeClass(target, "error_msg");
    target.innerHTML = "<span class=\"tip_loading\">&nbsp;</span>";

    if (username.length < 2 || (escape(username).indexOf('%u') == -1 && username.length < 3)) {
        Element.addClass(target, "error_msg");
        target.innerHTML = lang.username_short;
    }else{
        Ajax.addVal('app', 'member');
        Ajax.addVal('act', 'check_user');
        Ajax.addVal('username', username);
        Ajax.call('index.php', function(result){
          if (result.done){
            Element.removeClass(target, "error_msg");
            target.innerHTML = "<span class=\"tip_checked\">&nbsp;</span>";
          }else{
            Element.addClass(target, "error_msg");
            target.innerHTML = result.msg;
          }
        }, 'GET');
    }
}

/* 检查用户Email是否合法 */
function checkUserEmail(email){
    var target = $("checkuseremail");
    Element.removeClass(target, "error_msg");
    target.innerHTML = "<span class=\"tip_loading\">&nbsp;</span>";

    if (email.length == 0 || (!email.isEmail())){
        Element.addClass(target, "error_msg");
        target.innerHTML = lang.email_invalid;
    }else{
        Ajax.addVal('app', 'member');
        Ajax.addVal('act', 'check_email');
        Ajax.addVal('email', email);
        Ajax.call('index.php', function(result){
          if (result.done){
            Element.removeClass(target, "error_msg");
            target.innerHTML = "<span class=\"tip_checked\">&nbsp;</span>";
          }else{
            Element.addClass(target, "error_msg");
            target.innerHTML = result.msg;
          }
        }, 'GET');
}}

/* 检查用户重复密码是否正确 */
function checkRepeatPassword(repeatpass){
 var target = $('checkrepeatpassword');
 var pass = $('password_id').value;

 if (pass.length == 0) {
    Element.addClass(target, "error_msg");
    target.innerHTML = lang.input_pass_first;
    return;
 }
 if (repeatpass.length == 0){
    Element.addClass(target, "error_msg");
    target.innerHTML = lang.repeat_pass_empty;
    return;
 }

 if (pass != repeatpass){
    Element.addClass(target, "error_msg");
    target.innerHTML = lang.repeat_pass_invalid;
    return;
 }

 Element.removeClass(target, "error_msg");
 target.innerHTML = "<span class=\"tip_checked\">&nbsp;</span>";

}
function showAskDialog(order_sn){
  var d = new Dialog(DIALOG_USERDEF);
  d.setContent(lang.pay_dialog_title, lang.pay_dialog_content);
  d.setSize(310);
  d.addButton(lang.pay_sucess, function () {
    window.location.href="index.php?app=member&act=order_view";
  }, true);
  d.addButton(lang.pay_faild, function () {
    window.location.href = 'index.php?app=shopping&act=pay_faild_notice&order_sn='+order_sn;
  });
  d.show();
}
/* 格式化货币 */
function priceFormat(s){
  s = Math.ceil(s * 100)/100;
  s = s.toString();
  if(/[^0-9\.\-]/.test(s))
    return "invalid value";
  s=s.replace(/^(\d*)$/,"$1.");
  s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
  s=s.replace(".",",");
  var re=/(\d)(\d{3},)/;
  while(re.test(s)){
      s=s.replace(re,"$1,$2");
  }
  s=s.replace(/,(\d\d)$/,".$1");
  return lang.currency + s.replace(/^\./,"0.")
}

/* show the msn status */
function show_msn_status(result)
{
    var innerFrame = document.getElementById('msn_status');

    var statusIcon = document.createElement('img');
    statusIcon.style.border = 'none';
    statusIcon.src = result.icon.url;
    statusIcon.width = result.icon.width;
    statusIcon.height = result.icon.height;
    statusIcon.alt = result.statusText;
    statusIcon.title = result.statusText;

    var displayName = document.createElement('span');

    if (result.status != "Offline")
    {
        document.require("http://settings.messenger.live.com/controls/1.0/PresenceButton.js");
        document.require("http://messenger.services.live.com/users/" + result.id + "/presence?dt=&mkt=zh-cn&cb=Microsoft_Live_Messenger_PresenceButton_onPresence");
        var show_name = result.displayName.length > 0 ? result.displayName : lang.online;
        displayName.innerHTML = "<a href='javascript:Microsoft_Live_Messenger_PresenceButton_startConversation(\"http://settings.messenger.live.com/Conversation/IMMe.aspx?invitee=" + result.id + "&mkt=zh-cn\");'>" + show_name + "</a>";
    }
    else
    {
        displayName.innerHTML = result.displayName.length > 0 ? result.displayName : lang.offline;
    }

    innerFrame.innerHTML = "";
    innerFrame.appendChild(statusIcon);
    innerFrame.appendChild(displayName);
    }
