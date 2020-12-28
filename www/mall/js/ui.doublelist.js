/**
 * ECMall: double list
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ:  http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: ui.doublelist.js 6009 2008-10-31 01:55:52Z Garbin $
 */

ui.doublelist = function()
{
  this.selAll     = arguments[0] ? arguments[0] : null;  // ��ѡ��Ŀ�б�
  this.selChecked = arguments[1] ? arguments[1] : null;  // ��ѡ��Ŀ�б�

  this.addFunc    = arguments[2] ? arguments[2] : null;  // ���뺯��
  this.dropFunc   = arguments[3] ? arguments[3] : null;  // �Ƴ�����

  var _self = this;

  /**
   * ������
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
   * ���ѡ��
   * @param   boolean  all         �Ƿ�ȫ��
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
   * ɾ��ѡ����
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
   * ���������صĺ���
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
   * ΪselectԪ�ش���options
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