/**
 * ECMall: double list
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:  http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: ui.doublelist.js 6009 2008-10-31 01:55:52Z Garbin $
 */

ui.doublelist = function()
{
  this.selAll     = arguments[0] ? arguments[0] : null;  // 可选项目列表
  this.selChecked = arguments[1] ? arguments[1] : null;  // 已选项目列表

  this.addFunc    = arguments[2] ? arguments[2] : null;  // 加入函数
  this.dropFunc   = arguments[3] ? arguments[3] : null;  // 移除函数

  var _self = this;

  /**
   * 检查对象
   * @return boolean
   */
  this.check = function()
  {
    /* source select */
    if ( ! this.selAll)
    {
      alert('selAll undefined');
      return false;
    }
    else
    {
      if (this.selAll.nodeName != 'SELECT')
      {
        alert('selAll is not SELECT');
        return false;
      }
    }

    /* target select */
    if ( ! this.selChecked)
    {
      alert('selChecked undefined');
      return false;
    }
    else
    {
      if (this.selChecked.nodeName != 'SELECT')
      {
        alert('selChecked is not SELECT');
        return false;
      }
    }

    return true;
  };

  /**
   * 添加选项
   * @param   boolean  all         是否全部
   */
  this.addItem = function(all)
  {
    if (!this.check())
    {
      return;
    }

    var selOpt  = new Array();

    for (var i = 0; i < this.selAll.length; i++)
    {
      if (!this.selAll.options[i].selected && all == false) continue;

      if (this.selChecked.length > 0)
      {
        var exsits = false;
        for (var j = 0; j < this.selChecked.length; j++)
        {
          if (this.selChecked.options[j].value == this.selAll.options[i].value)
          {
            exsits = true;

            break;
          }
        }

        if (!exsits)
        {
          selOpt[selOpt.length] = this.selAll.options[i].value;
        }
      }
      else
      {
        selOpt[selOpt.length] = this.selAll.options[i].value;
      }
    }

    if (selOpt.length > 0)
    {
      if (this.addFunc)
      {
        this.addFunc(selOpt);
      }
      else
      {
        _self.createOptions(_self.selChecked, selOpt);
      }
    }
  };

  /**
   * 删除选中项
   * @param   boolean    all
   * @param   string     act
   */
  this.dropItem = function(all, act)
  {
    if (!this.check())
    {
      return;
    }

    var arr = new Array();

    for (var i = this.selChecked.length - 1; i >= 0; i--)
    {
      if (this.selChecked.options[i].selected || all)
      {
        arr[arr.length] = this.selChecked.options[i].value;
      }
    }

    if (arr.length > 0)
    {
      if (_self.dropFunc)
      {
        _self.dropFunc(arr);
      }
      else
      {
        _self.createOptions(_self.selChecked, arr);
      }
    }
  };

  /**
   * 处理添加项返回的函数
   */
  this.addRemoveItemResponse = function(result,txt)
  {
    if (!result.error)
    {
      _self.createOptions(_self.selChecked, result.content);
    }

    if (result.message.length > 0)
    {
      alert(result.message);
    }
  };

  /**
   * 为select元素创建options
   */
  this.createOptions = function(sel, arr)
  {
    sel.length = 0;

    for (var i=0; i < arr.length; i++)
    {
      var opt   = document.createElement("OPTION");
      opt.value = arr[i].id;
      opt.text  = arr[i].name;

      sel.options.add(opt);
    }
  };
};