/**
 * ECMall: 购物流程工具函数
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: shopping.js 6080 2008-11-19 07:50:31Z Garbin $
 */

/* 购物车 */
var __not_in_dialog = false;
var d = null;
function useCouponDialog(storeId, couponSN) {
  couponSN = $('store'+storeId+'_coupon_sn').value;
  var tip = lang.use_coupon_dialog_tip;
  if (couponSN) {
    var tip = lang.use_coupon_dialog_old_coupon_sn.replace('[coupon_sn]', couponSN + '&nbsp;[<a href="javascript:;" onclick="useCoupon(\'\', '+storeId+');" title="'+lang.cancel_use_coupon+'">'+lang.cancel+'</a>]') + '<br />' + tip;
  }
  d = new Dialog(DIALOG_PROMPT);
  d.setSize(350);
  d.setContent(lang.use_coupon_dialog_title, lang.use_coupon_dialog_content);
  d.inputSize = '30';
  d.summary = tip;
  d.okBtnName = lang.use_coupon_dialog_ok;
  d.cancelBtnName = lang.close;
  d.onOK    = function () {
    var c_n = d.value;
    if (!c_n) {
      alert(lang.invalid_coupon_sn);
      d.focus();
      return;
    }
    useCoupon(d.value, storeId);
  };
  d.show();
}
function useCoupon(coupon_sn, store_id, not_in_dialog){
  __not_in_dialog = not_in_dialog;
  Ajax.call('index.php?app=shopping&act=validate_coupon_sn&coupon_sn='+coupon_sn+'&store_id='+store_id,useCouponResponse);
}
function useCouponResponse(response){
  if (!response.done)
  {
    if(d)d.focus();
    if (response.retval.is_cancel) {
      $('store'+response.retval.store_id+'_coupon').style.display = 'none';
      $('store'+response.retval.store_id+'_coupon_sn').value = '';
    }
    //$('store'+response.retval.store_id+'_coupon').style.display = 'none';
    if (response.retval.close) {
      if(d)d.close();
    }
    var d2 = new Dialog(DIALOG_MESSAGE);
    d2.setContent(lang.sys_msg, response.msg);
    d2.autoCloseTime = 3;
    d2.show();
    return;
  }
  $('store'+response.retval.store_id+'_coupon').style.display = '';
  $('store'+response.retval.store_id+'_coupon_value').innerHTML = response.msg;
  var tip = new ui.tip($('store'+response.retval.store_id+'_coupon_value'));
  tip.setText(lang.tip, lang.coupon_has_value);
  tip.height = 50;
  tip.width = 180;
  tip.show();
  tip.hide(30000);
  if (!__not_in_dialog) {
    $('store'+response.retval.store_id+'_coupon_sn').value = d.value;
    d.close();
  }
}
function closeDialog(){
  Dialog.close();
}
/*
function loginRequest () {
  var f = Dialog.getLoginForm();
  username = f.username.value;
  password = f.password.value;
  if (!username)
  {
    alert(lang.invalid_username);
    return;
  }
  if (!password)
  {
    alert(lang.invalid_password);
    return;
  }
  Ajax.setMethod('post');
  Ajax.addVal('user_name', username);
  Ajax.addVal('password', password);
  Ajax.call('index.php?app=member&act=login', loginResult);
}
function loginResult(response){
  if (response.done)
  {
    Dialog.close();
    Dialog.show({content:response.msg,onOK:function(){window.location.reload(1);}});
  }
  else
  {
    Dialog.close();
    Dialog.show({type:DIALOG_WARNING,content:response.msg});
  }
}
*/
function goCheckout(store_id, d){
  switch (d)
  {
  case 'shipping_and_payment':
    document.setCookie('store_id', store_id);
    $('store' + store_id + '_go_checkout').value=1;
    updateCart(store_id);
  break;
  case 'order_review':
    $('store' + store_id + '_go_checkout').value=2;
    updateCart(store_id);
  break;
  }
}
function updateCart(store_id) {
    document.forms['cart_store' + store_id].submit();
}
function updateGoodsSubTotal(spec_id, num_input, store_id) {
  var goodsStock = Number($('goods:'+spec_id+':stock').value);
  var goodsPrice = Number($('goods:'+spec_id+':price').value);
  var cartGoodsCount = Number($('cart_goods_count').innerHTML);
  var oldValue   = Number($('goods' + spec_id + '_old_num').value);
  var num        = Number(num_input.value);
  var oldAmount  = Number($('store' + store_id + '_old_amount').value);
  if (num <= 0 || isNaN(num)) {
    alert(lang.error_goods_number);
    num_input.value = oldValue;
    return;
  }
  if (num > goodsStock) {
    alert(lang.goods_not_enough + goodsStock);
    num_input.value = oldValue;
    return;
  }
  var subTotal = priceFormat(goodsPrice * num);
  $('goods' + spec_id + '_subtotal').innerHTML = subTotal;
  $('goods' + spec_id + '_old_num').value = num;
  var _g_c = cartGoodsCount + (num - oldValue);
  $('cart_goods_count').innerHTML = _g_c;
  $('hidden_cart_goods_count').value = _g_c;
  $('hidden_cart_goods_amount').value = Number($('hidden_cart_goods_amount').value) + (num - oldValue) * goodsPrice;
  var newAmount = oldAmount + (num - oldValue) * goodsPrice;
  $('store'+ store_id +'_goods_amount').innerHTML = priceFormat(newAmount);
  $('store' + store_id + '_old_amount').value = newAmount;
  ajaxUpdateCart(store_id, spec_id, num);
}
function ajaxUpdateCart(store_id, spec_id, num){
    var goods_number = new Array();
    goods_number[spec_id] = num;
    Ajax.addVal('goods_number', goods_number);
    Ajax.addVal('go_checkout', 3);
    Ajax.call('index.php?app=shopping&act=update_cart&store_id=' + store_id, null, 'post');
}
/* 配送支付 */
function autoFilled(index){
  index = index -1;
  formData = addressData[index];
  for (var i = 2; i > 0 ; i-- )
  {
    objName = 'region' + i;
    if (formData[objName] > 0)
    {
      $('region_id').value = formData[objName];
      break;
    }
  }
  if (use_new_address == 1)
  {
    if (formData.address_id)
    {
      if (typeof(region) != 'undefined')
      {
          region.selected($('region_id').value);
      }
      for (k in formData)
      {
        if ($(k))
        {
          switch (k)
          {
          case 'region1':
          case 'region2':
          case 'region_id':
            break;
          default:
            $(k).value = formData[k];
            break;
          }
        }
      }
    }
  }
  $('selected_address_id').value = formData.address_id;
  updatePaymentList();
}
function setRegion(){
  $('region_id').value = '';
  for (var i = 2; i > 0 ; i-- )
  {
    objName = 'region' + i;
    if ($(objName).value > 0)
    {
      $('region_id').value = $(objName).value;
      break;
    }
  }
  updatePaymentList();
}
function updatePaymentList(){
  if ($('region_id').value && $('shipping_id').value)
  {
    Ajax.call('index.php?app=shopping&act=get_shipping_cod_surport&region_id=' + $('region_id').value + '&shipping_id=' + $('shipping_id').value, updatePaymentListResult);
  }
}
function updatePaymentListResult(response){
  if (!$('cod_payment'))
  {
    return;
  }
  if (!response.done)
  {
    $('cod_payment').disabled = true;
    if ($('cod_payment').checked)
    {
      $('cod_payment').checked  = false;
      setPayment();
    }
    return;
  }
  $('cod_payment').disabled = false;

}
function setShipping(shipping_id, shipping_name){
  $('shipping_name').value = shipping_name;
  $('shipping_id').value   = shipping_id;
  updatePaymentList();
}
function setPayment(pay_id, pay_name){
  $('pay_name').value = pay_name;
  $('pay_id').value   = pay_id;
}
function useNewAddress(obj, isCallBack){
  if ($('add_address').style.display == 'none')
  {
    /* select address */
    if(obj)obj.innerHTML= lang.select_address;
    //$('address_list').style.display='none';
    $('add_address').style.display='';
    Validator.preSubmit = function(){
      mobile = new dt_mobile();
      phone  = new dt_tel_num();
      if (!$('mobile_phone').value && !$('home_phone').value)
      {
        alert(lang.phone_required);

        return false;
      }
      if ($('mobile_phone').value && !mobile.check($('mobile_phone').value))
      {
        alert(lang.mobile_phone_format_invalid);

        return false;
      }
      if ($('home_phone').value && !phone.check($('home_phone').value))
      {
        alert(lang.phone_format_invalid);

        return false;
      }
      return checkForm(true);
    };
    Validator.run('theForm');
    if (region == null)
    {
      region = new ui.region('region');
    }
    if (!isCallBack)
    {
      use_new_address = 1;
      document.setCookie('use_new_address', 1);
    }
  }
  else
  {
    /* use new address */
    if(obj)obj.innerHTML= lang.use_new_address;
    //$('address_list').style.display='';
    $('add_address').style.display='none';
    document.forms['theForm'].onsubmit = function () {
      return checkForm();
    };
    if (!isCallBack)
    {
      use_new_address = 0;
      document.setCookie('use_new_address', 0);
    }
  }
}
function checkForm(n) {
  if (!$('shipping_id').value) {
    alert(lang.shipping_method_required);
    return false;
  }
  if (!$('pay_id').value) {
    alert(lang.payment_method_required);
    return false;
  }
  if (!$('selected_address_id').value && !n) {
    alert(lang.address_id_required);
    return false;
  }
  return true;
}
/*
function invForm(v){
  if (v)
  {
    $('inv_need').checked = true;
    $('inv_form').style.display = '';
    document.setCookie('need_inv', 1);
  }
  else
  {
    $('inv_unneeded').checked = true;
    $('inv_form').style.display = 'none';
    document.setCookie('need_inv', 0);
  }
}
*/
function invCheck(invSel) {
  if (invSel.value == '' ) {
    $('inv_payee').disabled = true;
  }
  else {
    $('inv_payee').disabled = false;
  }
}
