/* 收获地址 */
document.require("js/ui.formbox.js");
document.require("js/ui.formbox/style.css", null, "css");

function autoFilled(index){
  index = index -1;
  formData = addressData[index];
  var region_id = 0;
  if (formData.address_id)
  {
    if (typeof(region) != 'undefined')
    {
      region_id = formData.region1;
      if (formData.region2 > 0) {
        region_id = formData.region2;
      }
      region.selected(region_id);
    }
    for (k in formData)
    {
      if ($(k))
      {
        switch (k)
        {
        case 'region1':
        case 'region2':
          break;
        default:
          $(k).value = formData[k];
          break;
        }
      }
    }
    setRegion();
  }
}
function setRegion(){
  $('region_id').value = '';
  for (var i = 2; i > 0 ; i-- )
  {
    objName = 'address_region' + i;
    if ($(objName).value > 0)
    {
      $('region_id').value = $(objName).value;
      break;
    }
  }
}

function addAddress() {
  /*
  $('consignee').value = '';
  $('address_region1').value = 0;
  $('address_region2').options.length = 1;
  $('address').value = '';
  $('zipcode').value = '';
  $('email').value = '';
  $('mobile_phone').value = '';
  $('home_phone').value = '';
  $('best_time').value = '';
  $('op').value = 'add';
  */
  if (($('op').value == 'add') && ($('address_display_box').style.display == "")){
    $('address_display_box').style.display = "none";
  }else{
    $('address_display_box').style.display = "";
  }
  $('op').value = 'add';
  document.forms['theForm'].reset();
  $('address_form_title').innerHTML = '<span>' + lang.add_address + '</span>';
}
function editAddress(index) {
  $('address_display_box').style.display = "";
  autoFilled(index);
  $('address_form_title').innerHTML = '<span>' + lang.edit_address+ '</span>';
  $('op').value = 'edit';
}

var cur_address_id = 0;
function confirm_drop_address(address_id) {
  cur_address_id = address_id;
  var d = new Dialog(DIALOG_CONFIRM);
  d.onOK = function () {
    d.close();
    var url = 'index.php?app=member&act=address&op=drop&address_id='+cur_address_id;
    Ajax.call(url, function(s) {
      if(s.done) {
          var obj = $('address_'+cur_address_id);
          Element.remove($('address_'+cur_address_id));
          if($('add_address_button'))$('add_address_button').innerHTML = '<a href="javascript:addAddress();">'+ lang.add_address + '</a>';
      }
    });
  };
  d.setContent(lang.confirm, lang.drop_address_confirm);
  d.show();
}

/* 收藏夹 */
function rm_favorite(goods_id) {
  Ajax.call('index.php?app=member&act=favorite&op=delete&goods_id=' + goods_id, function (s) {
    alert(s.msg);
    if(s.done) {
        $('favorite_' + goods_id).style.display = "none";
    }
  });
}

/* 订单列表 */
function delivered(orderId, storeId){
  if (!orderId)
  {
    alert('error order id');
    return;
  }
  currOrderId = orderId;
  currStoreId = storeId;
  dialogContent =  '<div>'+lang.delivered_dialog_content+'</div>';
  dialogContent += '<div class="dialog_form_line">'+lang.delivered_dialog_title+'<br /><textarea id="order_comment" style="border-style:none;border:1px #dadada solid;background:#fafafa;" cols="30" rows="5" name="comment"></textarea></div>';
  dialogContent += '<div class="dialog_form_line"><label for="poor" class="dialog_label"><input id="poor" type="radio" name="rank_radio" value="1" onclick="$(\'rank\').value=this.value;" />'+lang.poor_button+'</label><label for="common" class="dialog_label"><input id="common" type="radio" name="rank_radio" value="2" onclick="$(\'rank\').value=this.value;" />'+lang.common_button+'</label><label for="good" class="dialog_label"><input type="radio" name="rank_radio" onclick="$(\'rank\').value=this.value;" value="3" id="good" />'+lang.good_button+'</label></div>';
  dialogContent += '<div class="dialog_form_line"><label for="is_add_friend" class="dialog_label"><input type="checkbox" name="is_add_friend" id="is_add_friend" value="1" />'+lang.is_add_friend+'<input type="hidden" name="rank" id="rank" /></label></div>';
  var d = new Dialog(DIALOG_CONFIRM);
  d.onOK = function () {
    Ajax.addVal('order_id', currOrderId);
    Ajax.addVal('comment', $('order_comment').value);
    Ajax.addVal('rank', $('rank').value);
    Ajax.addVal('is_add_friend', ($('is_add_friend').checked ? 1 : 0));
    Ajax.call('index.php?app=shopping&act=evaluation', function (response) {
      if (!response.done)
      {
        alert(response.msg);
        return;
      }
      Ajax.call('index.php?app=mail');
      window.location.reload(1);
    }, 'post');
  };
  d.setSize(350);
  d.setContent(lang.delivered_dialog_title, dialogContent);
  d.okBtnName = lang.delivered;
  d.show();
}

var currOrderId = null;
var currStoreId = null;
function cancelConfirm(order_id){
  var d = new Dialog(DIALOG_CONFIRM);
  d.onOK = function () {
    Ajax.call('index.php?app=member&act=cancel_order&order_id=' + order_id, function (response) {
      if (!response.done) {
        d.close();
        alert(response.msg);
        return;
      }
      Element.remove($('cancel_order_button_' + order_id));
      if ($('pay_order_button_' + order_id)) {
        Element.remove($('pay_order_button_' + order_id));
      }
      $('order_status_' + order_id).innerHTML = lang.order_status_rejected;
      d.close();
    });
  };
  d.setContent(lang.cancel_order_dialog_title, lang.cancel_order_confirm);
  d.show();
}

function editAvatar() {
  var d = new Dialog(DIALOG_USERDEF);
  d.setContent(lang.edit_avatar, $('avatar-edit').innerHTML);
  d.setSize(480);
  d.components.icon  = null;
  d.onClose = function () {
    window.location.reload(-1);
    return true;
  };
  d.show();
}
