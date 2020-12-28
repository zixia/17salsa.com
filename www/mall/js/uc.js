/**
 * ECMall: Javascript Framework
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ:  http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: uc.js 6009 2008-10-31 01:55:52Z Garbin $
 */

var hiddenobj = new Array();
var pmwinposition = new Array();
function pmwin(action, param) {
  var objs = document.getElementsByTagName("OBJECT");
  if(action == 'open') {
    for(i = 0;i < objs.length; i ++) {
      if(objs[i].style.visibility != 'hidden') {
        objs[i].setAttribute("oldvisibility", objs[i].style.visibility);
        objs[i].style.visibility = 'hidden';
      }
    }
    var clientWidth = document.body.clientWidth;
    var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    var pmwidth = 800;
    var pmheight = clientHeight * 0.9;
    if(!$('pmlayer')) {
      div = $ce('div');div.id = 'pmlayer';
      div.style.width = pmwidth + 'px';
      div.style.height = pmheight + 'px';
      div.style.left = ((clientWidth - pmwidth) / 2) + 'px';
      div.style.position = 'absolute';
      div.style.zIndex = '999';
      document.body.appendChild(div);

      var code = "";

      if (navigator.isIE()) {
        code = '<iframe href="return false" style="width:' + pmwidth + 'px;height:'+pmheight+'px; position: absolute;  left:0px;top:0px; z-Index=-1;filter:alpha(opacity=0);" frameborder="0"></iframe>';
      }
      $('pmlayer').style.visibility = 'hidden';
      $('pmlayer').innerHTML = '<div style="width: 800px; background: #666666; margin: 5px auto; text-align: left">' +
        '<div style="width: 800px; height: ' + pmheight + 'px; padding: 1px; background: #FFFFFF; border: 1px solid #7597B8; position: relative; left: -6px; top: -3px;">' +
        '<div id="_pm_header_" style="cursor: move; position: relative; left: 0px; top: 0px; width: 800px; height: 30px; margin-bottom: -30px;"></div>' +
        '<a href="javascript:;" onclick="pmwin(\'close\')"><img style="position: absolute; right: 20px; top: 15px" src="data/images/close.gif" alt="' + lang.close + '" title="' + lang.close + '" /></a>' +
        '<div id="pmwinmask" style="margin-top: 30px; position: absolute; width: 100%; height: '+pmheight+'px; display: none"></div><iframe scrolling="yes" id="pmframe" name="pmframe" style="width:' + pmwidth + 'px;height:100%; overflow-x:hidden" allowTransparency="true" frameborder="0"></iframe>'+code+'</div></div>';
      new ui.Draggable(div, $('_pm_header_'), $('pmwinmask'));
      $('pmlayer').style.height = $('pmframe').clientHeight;
      $('pmlayer').style.visibility = 'visible';
    }
    $('pmlayer').style.display = '';
    $('pmlayer').style.top = ((clientHeight - pmheight) / 2 + scrollTop) + 'px';
    if(!param) {
      pmframe.location = 'index.php?app=pm';
    } else {
      pmframe.location = 'index.php?app=pm' + param;
    }
  } else if(action == 'close') {
    for(i = 0;i < objs.length; i ++) {
      if(objs[i].attributes['oldvisibility']) {
        objs[i].style.visibility = objs[i].attributes['oldvisibility'].nodeValue;
        objs[i].removeAttribute('oldvisibility');
      }
    }
    hiddenobj = new Array();
    $('pmlayer').style.display = 'none';
  }
}