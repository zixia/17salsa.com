function hidden_filter(span, obj_id) {
  var obj = $(obj_id);
  if(obj.style.display == 'none') {
    span.className = 'collapse_open';
    obj.style.display = '';
  } else {
    span.className = 'collapse_close';
    obj.style.display = 'none';
  }
}

function submit_price(cur_url) {
    var price_left = price_right = null;
    price_left = Number($('price_left').value);
    price_right = Number($('price_right').value);
    if(!price_left && !price_right) {
        alert(lang.undifine_price_range);
        return;
    }
    if(price_left) {
        cur_url = cur_url + '&min_price=' + price_left;
    }
    if(price_right) {
        cur_url = cur_url + '&max_price=' + price_right;
    }
    self.location.href = cur_url;
}