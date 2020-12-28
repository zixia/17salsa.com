Event.observe(window, 'load', function(){
  showGoods("recommended_sort");
  showGoods("group_buy");
});

function showGoods(id)
{
  var elem = $(id);
  if (!elem)
  {
    return;
  }
  var last = false;
  var elems = elem.getElementsByTagName("LI");
  
  for (var i=0; i < elems.length; i++)
  {
    if (i == 0) {
      try
      {
        Element.addClass(elems[i], "selected");
      }
      catch(ex)
      {
        document.title = ex.description + "aaaaa";
      }
      last = elems[i];
    }
    
    Event.observe(elems[i], "mouseover", (function(idx) {
      
      return function(){
        if (last != elems[idx]) {
          var elem = elems[idx];
          Element.removeClass(last, "selected");
          Element.addClass(elem, "selected");
          last = elem;
        };
      }
    })(i));
    
  }
}