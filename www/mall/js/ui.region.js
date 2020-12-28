/**
 * ECMall: 地区列表类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.region.js 6017 2008-10-31 08:20:19Z Garbin $
 */

/**
 * @param string name 下列列表的名称
 * @param
 */

var Regions = [];
ui.region = function (selName){
  this.selName = selName;

  /* 加载一级地区 */
  this.loadRegions(0, 1);
};

/**
 * 返回当前选择的地区
 * @return obj('id' => '', 'name' => '')
 */
ui.region.prototype.getRegion = function()
{
  var obj  = new Object;
  obj.id   = 0;
  obj.name = '';

  for (var i = 2; i > 0; i--)
  {
    var sel = $(this.selName + i);
    if (sel != 'undefined')
    {
      if (sel.options[sel.selectedIndex].value > 0)
      {
        obj.id   = sel.options[sel.selectedIndex].value;
        obj.name = sel.options[sel.selectedIndex].text;
        break;
      }
    }
  }

  return obj;
};
ui.region.prototype.getRegions = function (parent){
  var rtn = [];
  for (var i=0; i < Regions.length; i++)
  {
    if (Regions[i]['parent_id'] == parent)
    {
      rtn.push(Regions[i]);
    }
  }

  return rtn;
};
ui.region.prototype.changed = function(obj, level, callback)
{
  level = parseInt(level);
  if (level > 2)
  {
    alert('invalid param: level ' + level);
    return;
  }
  this.clearChildrens(level);
  var parent = parseInt(obj.options[obj.selectedIndex].value);
  if (parent)
  {
    this.loadRegions(parent, level);
  }
  if (callback)
  {
      callback(obj.value);
  }
};
ui.region.prototype.loadRegions = function (parent, level){
    var regions = this.getRegions(parent);

    /* 获取需要改变的select控件 */
    var sel     = $(this.selName + level);

    sel.length = 1;
    sel.selectedIndex = 0;
    //Event.observe(sel, 'change', this.);
    if (regions)
    {
      for (i = 0; i < regions.length; i++)
      {
        var opt = document.createElement("OPTION");
        opt.value = regions[i].region_id;
        opt.text  = regions[i].region_name;
        sel.options.add(opt);
      }
    }
};
ui.region.prototype.clearChildrens = function (level){
   for (var i=level; i<=2; i++)
   {
     var obj = $(this.selName + i);
     obj.options.length = 1;
   }
};
ui.region.prototype.selected = function (region_id){
  if(!region_id){
    return;
  }
  var parents = this.usort(this.getParents(region_id));
  parents.push(region_id);
  $(this.selName + '1').value = parents[0];
  this.changed($(this.selName + '1'), 2);
  $(this.selName + '2').value = parents[1];
  /*
  for (var i=1; i < parents.length; i++)
  {
    if (parents[i])
    {
      var level = i +1;
      $(this.selName + level).value = parents[i];
      if (level < 4)
      {
        this.changed($(this.selName + level), level+1);
      }
    }
  }
  */
};
ui.region.prototype.getParents = function (region_id){
  var rtn = [];
  parent_id = this.getParent(region_id);
  if (parent_id)
  {
    rtn.push(parent_id);
    //rtn = rtn.concat(this.getParents(parent_id));
  }

  return rtn;
};
ui.region.prototype.getParent = function (region_id){
  for (var i=0; i < Regions.length; i++)
  {
    if (Regions[i]['region_id'] == region_id)
    {
        return Regions[i]['parent_id'];
    }
  }
};
ui.region.prototype.usort    = function (arr){
  var tmp = [];
  for (var i=arr.length; i >= 0; i--)
  {
    if (Number(arr[i]) > 0)
    {
      tmp.push(arr[i]);
    }
  }
  return tmp;
};
