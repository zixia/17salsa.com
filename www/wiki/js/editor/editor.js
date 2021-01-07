var thcTextareaDiv = document.getElementById("thcTextareaDiv");
var thcIframeDiv = document.getElementById("thcIframeDiv");
var thcIframe = document.getElementById("thcIframe");
var thcFontSelect =	document.getElementById("thcFont");
var thcFontSizeSelect = document.getElementById("thcFontSize");
var thcTextCssSelect = document.getElementById("thcTextCss");
var thcView = document.getElementById('thcView');
var thcIframeDoc = "";
var thcSelect = '';
var thcRange = '';
var thcRangeText = '';
var thcHighFlag = '';
var thcHighTableFlag = '';
var thcElement = '';
var isSection = 0;
var browser = "";

var tbStyleTable= '';
var tbbody = '';
var tbtr = '';
var tbtd = '';
var tbindex = '';
var nexttr='';
var nexttd='';
var thcFont = new Array();
var thcTableFlag = 0;

init = function(html){ 
    var i = document.getElementById("bgiframe");
    i.style.display = "block";
    i.style.opacity = "0.5";
	i.style.backgroundColor = "#AFAFAF";
	i.style.filter = "progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=50)";
	i.style.top = "0";
	i.style.left = "0";
	i.style.width = (window.screen.availWidth)+ "px";
    i.style.height = document.body.clientHeight+ "px";
	var fdiv = document.getElementById("float_window");
    fdiv.innerHTML = html;
    fdiv.style.display = "block";
    fdiv.style.left = (document.body.clientWidth - fdiv.clientWidth) / 2 + "px";
    fdiv.style.top = (document.body.clientHeight - fdiv.clientHeight) / 2 + "px";
}

ThcGetBrowser = function(){
	var agentInfo = navigator.userAgent.toLowerCase();
   if(agentInfo.indexOf("msie")>0) return 'IE';
   else if(agentInfo.indexOf("firefox")>0) return 'FF';
   else if(agentInfo.indexOf("opera")>0) return 'OP';
	else return 0;
}

ThcGetEvent = function(){
	if(document.all) return window.event;
	func=ThcGetEvent.caller;
	while(func!=null){
		 var arg0=func.arguments[0];
		 if(arg0){
			  if((arg0.constructor==Event||arg0.constructor==MouseEvent)||(typeof(arg0)=="object"&&arg0.preventDefault&&arg0.stopPropagation)){
					return arg0;
			  }
		 }
		 func=func.caller;
	}
	return null;
}

var browser = ThcGetBrowser();

changeTitleBg=function(ebc){
	var ttid=document.getElementById('tags_tt');
	var ttname=ttid.getElementsByTagName('a');
	for(i=0;i<ttname.length;i++){
		ttname[i].onfocus=function(){
			this.blur();
		}
		ttname[i].className="";
		ttname[ebc].className="adr_c";
	}
}

ThcMenuSwitch = function(thcCase){
	ThcViewNone();
	UnloadConfirm.clear();
	switch(thcCase){
		case "base":
			document.getElementById('thcBase').style.display = 'block';
			document.getElementById('thcHigh').style.display = 'none';
			document.getElementById('thcHighTable').style.display = 'none';
			thcTextCssSelect = document.getElementById("thcTextCss");
			if(thcIframeDiv.style.display == 'none'){
				thcIframeDiv.style.display = "block";
				daxiao();
				thcIframeDoc.body.focus();
			}
			daxiao();
			changeTitleBg(0);
			break;
		case "high":
			document.getElementById('thcBase').style.display = 'none';
			document.getElementById('thcHigh').style.display = 'block';
			document.getElementById('thcHighTable').style.display = 'none';
			thcTextCssSelect = document.getElementById("thcTextCss_m");
			daxiao();
			changeTitleBg(1);
			break;
		case "table":
			document.getElementById('thcBase').style.display = 'none';
			document.getElementById('thcHigh').style.display = 'none';
			document.getElementById('thcHighTable').style.display = 'block';
			if(thcIframeDiv.style.display == 'none'){
				thcIframeDiv.style.display = "block";
				daxiao();
				thcIframeDoc.body.focus();
			}
			daxiao();
			changeTitleBg(2);
			break;
	}
}

function ThcReplaceLink(str){
	str = str.replace(/%C2%A0/g,'');
	return str;
}

function insertbefore(p,d){
	if(browser == 'IE'){
		p.insertAdjacentElement("beforeBegin", d);
	}else{
		p.parentNode.insertBefore(d,p);
	}
}

function ThcFilterStyle(element){
	if(element){
		var codeNode = element.childNodes;
	}else{
		var codeNode = thcIframeDoc.body.childNodes;
	}
	var num= codeNode.length;
	if(num>0){
		for(var i=0;i<num;i++){
			if (codeNode[i]&&codeNode[i].nodeType&&codeNode[i].nodeType == 1) {
				switch (codeNode[i].tagName.toLowerCase()) {
					case "input":
						codeNode[i].parentNode.removeChild(codeNode[i]);
						break;
					case "img":
					case "table":
						if(codeNode[i]&&codeNode[i].style){
							codeNode[i].removeAttribute('style',0);
						}
						break;
					case "p":
						if (codeNode[i].innerHTML == "") {
							codeNode[i].parentNode.removeChild(codeNode[i]);
						}
						break;
				}
				ThcFilterStyle(codeNode[i]);
			}
		}
	}	
}

function ThcFilterCode(){
	ThcFilterStyle();
	var html = thcIframeDoc.body.innerHTML;
	html = html.replace(/<textarea (.*)>/gi,'');
	html = html.replace(/<\/textarea>/gi,'');
	thcIframeDoc.body.innerHTML = html;
}
ThcButtonClick = function(cmd){
	ThcViewNone();
	if(thcIframeDiv.style.display == 'none'){
		alert(HD_LANG['switchwysiwyg']);
		return;
	}
	switch(cmd){
		case 'undo':
			thcIframeDoc.body.focus();
			thcIframeDoc.execCommand('undo', false, null);
			break;
		case 'redo':
			thcIframeDoc.body.focus();
			thcIframeDoc.execCommand('redo', false, null);
			break;
		case 'paste':
			if(browser == 'IE'){
				thcIframeDoc.body.focus();
				thcIframeDoc.execCommand('paste', false, null);
			}else{alert(HD_LANG['usepaste']);}
			break;
		case 'cut':
			if(browser == 'IE'){
				thcIframeDoc.body.focus();
				thcIframeDoc.execCommand('cut', false, null);
			}else{alert(HD_LANG['usecut']);}
			break;
		case 'copy':
			if(browser == 'IE'){
				thcIframeDoc.body.focus();
				thcIframeDoc.execCommand('copy', false, null);
			}else{alert(HD_LANG['usecopy']);}
			break;
		case 'section':
			thcIframeDoc.body.focus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var flag = false;
			if (browser == 'IE') {
				if((thcRangeText.split('innerlink').length)!=(thcRange.htmlText.split('innerlink').length)){
					return;
				}
				flag = thcRange.htmlText.indexOf("hdwiki_tmml")!=-1;
			} else {
				var p = thcRange.commonAncestorContainer;
				if (!thcRange.collapsed && thcRange.startContainer == thcRange.endContainer && thcRange.startOffset - thcRange.endOffset <= 1 && thcRange.startContainer.hasChildNodes())
					p = thcRange.startContainer.childNodes[thcRange.startOffset];
					while (p.nodeType == 3){
						p = p.parentNode;
					}
				if(p.className == "hdwiki_tmml"){
					flag = true;
				}
			}
			if(!flag){
				if(thcRangeText!=""){
					var element = document.createElement("DIV");
					element.setAttribute("class","hdwiki_tmml");
					element.innerHTML = ThcHtmlReplace(thcRangeText);
					ThcInsertHTML(element);
					var objs = thcIframeDoc.getElementsByTagName("DIV");
					for(var i=0;i<objs.length;i++){
						if(objs[i].nextSibling){
							if(objs[i].nextSibling.tagName=="P"&&objs[i].nextSibling.innerHTML==""){
								thcIframeDoc.body.removeChild(objs[i].nextSibling);
							}
						}
						if(objs[i].previousSibling){
							if(objs[i].previousSibling.tagName=="P"&&objs[i].previousSibling.innerHTML==""){
								thcIframeDoc.body.removeChild(objs[i].previousSibling);
							}
						}
					}
					if(element == thcIframeDoc.body.lastChild){
						var br = thcIframeDoc.createElement('br');
						insertAfter(br,insertNode);	
					}
				}else{return;}
			}
			ThcFilterStyle();
			break;
		case 'removesection':
			thcElement.parentNode.insertBefore(thcElement.firstChild, thcElement);
			thcElement.parentNode.removeChild(thcElement);
			break;
		case 'thcTextCss':
			thcIframeDoc.body.focus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var thcTextCss = thcTextCssSelect.value;
			if(thcElement==null||!thcElement.tagName){return;}
			if(thcElement.tagName.toLowerCase()=='span'){
				thcElement.className=null;
				thcElement.className=thcTextCss;
			}else{
				var element = document.createElement("span");
				element.setAttribute("class",thcTextCss);
				element.innerHTML = ThcHtmlReplace(thcRangeText);
				ThcInsertHTML(element);
			}
			thcElement=null;
			break;
		case 'picture':
			thcfocus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var html = '<div id="insetimg">'
                +'<div id="upload">'
                    +'<fieldset>'
                        +'<ol>'
                            +'<li>'+HD_LANG['selectpic']+'：<input type="file" class="adr_ptheight" value="'+HD_LANG['view']+'" name="photofile" id="f_file"/></li>'
                            +'<li>'+HD_LANG['picsize']+' <input type="radio" value="1" checked="checked" name="picWidthHeight"/>'+HD_LANG['bigimg']+' <input type="radio" value="2" name="picWidthHeight"/>'+HD_LANG['smallimg']+'</li>'
                            +'<li>'+HD_LANG['align']+' <input type="radio" value="left" name="picAlign"/>'+HD_LANG['left']+'<input type="radio" value="right" checked="checked" name="picAlign"/>'+HD_LANG['right']+'</li>'
                        +'</ol>'
                    +'</fieldset>'
                +'</div>'
                +'<div class="adr_imgplt">'
                    +'<fieldset>'
                        +'<legend>'
                            +HD_LANG['pictitle']
                        +'</legend>'
                        +'<textarea name="picAlt" rows="3" style="width:97%">'
                        +'</textarea>'
                    +'</fieldset>'
                +'</div>'
                +'<div style="padding: 5px 0pt; width: 380px; text-align: center;">'
                    +'<span><input type="submit" value="'+HD_LANG['confirm']+'" id="picSubmit"/></span><span><input type="button" onclick="ThcViewNone();" value="'+HD_LANG['cancel']+'"/></span>'
                +'</div>'
            +'</div>';
			init(html);
			break;
		case 'basetable' :
			thcIframeDoc.body.focus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var html = '<div id="insetable">'
            +'<fieldset><legend>'+HD_LANG['table']+'</legend>'  
            +'<div style="margin:10px">'
            +HD_LANG['row']+'：<input type="text" size="5"  maxlength="3" maxlength="2" id="tableRows" value = "4"/>'
            +HD_LANG['col']+'：<input type="text" size="5" maxlength="3" maxlength="2" id="tableCols" value = "2"/></div>' 
            +'</fieldset> '
            +'<div style=" float:left;width:100%;padding-top:5px; text-align:center">' 
            +'<span><input type="button" value="'+HD_LANG['confirm']+'" onclick="ThcInsertTable();" /><input type="button" value="'+HD_LANG['cancel']+'"  onclick = "ThcViewNone();"/>' 
            +'</span></div>'
            +'<div style="clear: both"></div>' 
            +'</div>';
			init(html);
			document.getElementById('tableCols').focus();
			break;
		case 'innerlink' :
			ThcSelectHtml();thcIframeDoc.body.focus();
			if(isSection){
				return;
			}
			var flag = false;
			if (browser == 'IE') {
				if(thcElement.tagName.toLowerCase()=="a"){
					flag = true;
				}else{
					flag = thcRange.htmlText.indexOf("/wiki/")!=-1;
				}
			} else {
				var p = thcRange.commonAncestorContainer;
				if (!thcRange.collapsed && thcRange.startContainer == thcRange.endContainer && thcRange.startOffset - thcRange.endOffset <= 1 && thcRange.startContainer.hasChildNodes())
					p = thcRange.startContainer.childNodes[thcRange.startOffset];
					while (p.nodeType == 3){
						p = p.parentNode;
					}
				if((thcElement.tagName=='A'||p.tagName=='A')){
					flag = true;
				}
			}

			if((thcRangeText=='')||ThcReplaceLink(encodeURI(thcRangeText))=="") return;
			if(flag == true){
				thcIframeDoc.execCommand('unlink', false, null);
			}else{
				var element = document.createElement("A");
				var url= "index.php?doc-innerlink-"+ThcReplaceLink(encodeURI(thcRangeText));
				element.setAttribute("href",url);
				element.setAttribute("class","innerlink");
				element.setAttribute("title",thcRangeText);
				element.innerHTML = ThcHtmlReplace(thcRangeText);
				ThcInsertHTML(element);
			}
			break;
		case 'weblink' :
			thcfocus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var html ='<div id="link">'
			+'<fieldset>'
			+'<legend>'+HD_LANG['linkaddress']+'</legend>'
	        +'<p style="padding:5px"><span>URL</span><input type="text" size="25" id="linkUrl" value="http://" />'
	        +'</p>'
			+'</fieldset>'
			+'<div style="text-align:center">'
			+'<span class="adr_btn_pos"><input type="button" value="'+HD_LANG['confirm']+'" id="webLinkSubmit" onclick = "ThcWebLink();" /><input type="button" value="'+HD_LANG['cancel']+'" onclick = "ThcViewNone();" /></span></div>'
			+'<div style="clear: both"></div>'
			+'</div>';
			init(html);
			document.getElementById('linkUrl').focus();
			break;
		case 'unlink':
			thcIframeDoc.body.focus();
			if(browser=="IE"){
				thcIframeDoc.execCommand('unlink', false, null);
			}else{
				thcRemoveLink();
			}
			break;
		case 'bold':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('bold', false, null);
			break;
		case 'italic':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('italic', false, null);
			break;
		case 'underline':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('underline', false, null);
			break;
		case 'justifyleft':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('justifyleft', false, null);
			break;
		case 'justifycenter':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('justifycenter', false, null);
			break;
		case 'justifyright':
			thcfocus();
			if(isSection){
				return;
			}
			thcIframeDoc.execCommand('justifyright', false, null);
			break;
		case 'media':
			thcfocus();ThcSelectHtml();
			if(isSection){
				return;
			}
			var html = '<div id="meadia">'
			+'<fieldset>'
			+'<legend>'+HD_LANG['fileNetaddress']+'</legend>'
			+'<input type="text" value = "" id="mediaUrl"/><br/><span>'+HD_LANG['supprtformat']+'</span>'
			+'</fieldset>'
			+'<fieldset>'
			+'<legend>'+HD_LANG['filedisplaysize']+'</legend>'
			+HD_LANG['width']+'<input type="text" id="mediaWidth" style="width: 80px;" value="320"/>&nbsp; '
			+''+HD_LANG['height']+'<input type="text" id="mediaHeight" style="width: 80px;" value="240"/> '
			+'</fieldset>'
			+'<p style="width: 280px; text-align: center; padding: 5px 0">'
			+'<span><input type="button" value="'+HD_LANG['confirm']+'" onclick="ThcInsertMedia();"/></span><span><input type="button" value="'+HD_LANG['cancel']+'"  onclick = "ThcViewNone();"/></span></p>'
			+'</div>';
			init(html);
			document.getElementById('mediaWidth').focus();
			break;
		case 'selectall':
			thcfocus();
			thcIframeDoc.execCommand('selectall', false, null);
			break;
	}
}

ThcTableButtonView = function(cmd){
	if(thcTableFlag == 1){nexttr='';nexttd='';}
	ThcViewNone();
	ThcSelectHtml();
	if(thcIframeDiv.style.display == 'none'){
		alert(HD_LANG['switch']);
		return;
	}
	if(thcElement == ''){return;}//thcElement.tagName.toLowerCase() == 'table'||thcElement.tagName.toLowerCase() == 'td'
	if(thcElement.tagName.toLowerCase() == 'table'||thcElement.tagName.toLowerCase() == 'tr'||thcElement.tagName.toLowerCase() == 'td'){
		switch(cmd){
			case 'tableprop':
			return;
			if(thcElement.tagName.toLowerCase() == 'table'||thcElement.tagName.toLowerCase() == 'td'){
				thcIframeDoc.body.focus();ThcSelectHtml();
				var html = 
				'<div id="discrp">'
				+'<fieldset>'
					+'<legend>'+HD_LANG['describe']+'</legend>'
						+'<div class="pd5"><span>'+HD_LANG['tabletitle']+'</span><input type="text" id="tbCaption"/></div>'
						+'<div class="pd5"><span>'+HD_LANG['describe']+'</span><input type="text" id="tbDesc" /></div>'
					+'</fieldset>'
				+'<fieldset>'
					+'<legend>'+HD_LANG['layouts']+'</legend>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['floatelement']+'</span>'
						+'<select id="tbCssFloat">'
							+'<option value="left">'+HD_LANG['lftfloat']+'</option>'
							+'<option value="right">'+HD_LANG['rightfloat']+'</option>'
							+'<option selected="selected">'+HD_LANG['none']+'</option>'
						+'</select>'
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['height']+'</span>'
						+'<input id="tbHeight" type="text" size="5" />'
						+'<select id="tbHeightS">'
							+'<option value="%" selected="selected">'+HD_LANG['percent']+'</option>'
							+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select>'
						+'<span>'+HD_LANG['pixel']+'</span>'
						+'<select id="tbTextAlign">'
							+'<option value="left" selected="selected">'+HD_LANG['left']+'</option>'
							+'<option value="center">'+HD_LANG['center']+'</option>'
							+'<option value="right">'+HD_LANG['right']+'</option>'
						+'</select>'
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['width']+'</span>'
						+'<input type="text" size="5" id="tbWidth" />'
						+'<select id="tbWidthS">'
							+'<option value="%" selected="selected">'+HD_LANG['percenter']+'</option>'
							+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select>'
						+'<span>'+HD_LANG['verticalalign']+'</span>'
						+'<select id="tbVerticalAlign">'
							+'<option value="top">'+HD_LANG['top']+'</option>'
							+'<option value="center">'+HD_LANG['centersingle']+'</option>'
							+'<option value="bottom">'+HD_LANG['bottom']+'</option>'
							+'<option value="baseline" selected="selected">'+HD_LANG['bottom']+'</option>'
						+'</select>'
					+'</div>'
				+'</fieldset>'
				+'<fieldset>'
					+'<legend>'+HD_LANG['margin']+'</legend>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['margin']+'</span><input type="text" size="15" id="tbCellSpacing"/>'
						+'<span>'+HD_LANG['fill']+'</span><input type="text" size="15" id="tbCellPadding"/>'
					+'</div>'
				+'</fieldset>'
				+'<fieldset>'
					+'<legend>'+HD_LANG['border']+'</legend>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['border']+'</span><input type="text" size="5" id="tbBorder" /><span>'+HD_LANG['pixel']+'</span>'
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['frame']+'</span>'
						+'<select id="tbFrame">'
							+'<option value="void">'+HD_LANG['noneframe']+'</option>'
							+'<option value="above">'+HD_LANG['upframe']+'</option>'
							+'<option value="below">'+HD_LANG['bottomframe']+'</option>'
							+'<option value="hsides">'+HD_LANG['upandbottomframe']+'</option>'
							+'<option value="vsides">'+HD_LANG['leftrightframe']+'</option>'
							+'<option value="lhs">'+HD_LANG['leftframe']+'</option>'
							+'<option value="rhs">'+HD_LANG['rightframe']+'</option>'
							+'<option value="box" selected="selected">'+HD_LANG['allframe']+'</option>'
						+'</select>'
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['boundary']+'</span>'
						+'<select id="tbRules">'
							+'<option value="none">'+HD_LANG['noneboundary']+'</option>'
							+'<option value="rows">'+HD_LANG['onlyrowboundary']+'</option>'
							+'<option value="cols">'+HD_LANG['onlycolboundary']+'</option>'
							+'<option value="all" selected="selected">'+HD_LANG['rowcolboundary']+'</option>'
						+'</select>'
					+'</div>'
				+'</fieldset>'
				+'<div>'
					+'<span class="btn_pos">'
						+'<input type="button" value="'+HD_LANG['confirm']+'" onclick = "ThcTableStyle();" />'
						+'<input type="button" value="'+HD_LANG['cancel']+'" onclick = "ThcViewNone();" />'
					+'</span>'
				+'</div>'
					+'<div style="clear: both"></div>'
				+'</div>';
				init(html);
				tbStyleTable = thcElement;
				while(tbStyleTable.tagName.toLowerCase() != 'table'){
					tbStyleTable = tbStyleTable.parentNode;
				}
				ThcTbcssBeFore();
				document.getElementById('tbCaption').focus();
				}else{alert(HD_LANG['selecttable']);return;}
				break;
			case 'rowprop':
			if(thcElement.tagName.toLowerCase() == 'td'){
				thcIframeDoc.body.focus();
				ThcSelectHtml();
				var html = '<div id="layouts">'
				+'<fieldset>'
					+'<legend>'+HD_LANG['rowproperty']+'</legend>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['height']+'</span><input id="trHeight" type="text" size="5" />'
						+'<select id="trHeightS">'
						+'<option value="%" selected="selected">'+HD_LANG['percenter']+'</option>'
						+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select> '
						+'<span>'+HD_LANG['textalign']+'</span>'
						+'<select id="trTextAlign">'
						+'<option value="left" selected="selected">'+HD_LANG['left']+'</option>'
						+'<option value="center">'+HD_LANG['center']+'</option>'
						+'<option value="right">'+HD_LANG['right']+'</option>'
						+'</select> '
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['width']+'</span><input id="trWidth" type="text" size="5" />'
						+'<select id="trWidthS">'
						+'<option value="%" selected="selected">'+HD_LANG['percenter']+'</option>'
						+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select> '
						+'<span>'+HD_LANG['verticalalign']+'</span>'
						+'<select id="trVerticalAlign">'
						+'<option value="top">'+HD_LANG['top']+'</option>'
						+'<option value="center">'+HD_LANG['centersingle']+'</option>'
						+'<option value="bottom">'+HD_LANG['bottom']+'</option>'
						+'<option value="baseline" selected="selected">'+HD_LANG['baseline']+'</option>'
						+'</select>'
					+'</div>'
				+'</fieldset>'
				+'<div>'
				+'<span class="btn_pos"><input type="button" value="'+HD_LANG['confirm']+'" onclick = "ThcStyle(tbtr,\'tr\');" /><input type="button" value="'+HD_LANG['cancel']+'" onclick = "ThcViewNone();" />'
				+'</span></div>'
				+'<div style="clear: both"></div>';
				init(html);			
				tbtr = thcElement.parentNode;//editor.getClosest("tr");
				ThcStyleBefore(tbtr,'tr');}else{alert(HD_LANG['operaterow']);return;}
				break;
			case 'rowinabove':
				nexttr == ''?(tbtr=thcElement.parentNode):(tbtr=nexttr);
				if(tbtr.tagName.toLowerCase()!='tr'){alert(HD_LANG['operaterow']);return;}
				if(tbtr.rowIndex!=''){tbindex = tbtr.rowIndex;}
				if(tbindex == 0){
					var newtr = tbtr.parentNode.insertRow(0);
				}else{var newtr = tbtr.parentNode.insertRow(tbindex);}
				for(var i=0;i<tbtr.childNodes.length;i++){
					var newcell = newtr.insertCell(i);
					newcell.innerHTML = (browser=="IE")?"":"&nbsp;";
				}
				break;
			case 'rowinunder':
				nexttr == ''?(tbtr=thcElement.parentNode):(tbtr=nexttr);
				if(tbtr.tagName.toLowerCase()!='tr'){alert(HD_LANG['operaterow']);return;}
				if(tbtr.rowIndex!=''){tbindex = tbtr.rowIndex;}
				var newtr = tbtr.parentNode.insertRow(++tbindex);
				for(var i=0;i<tbtr.childNodes.length;i++){
					var newcell = newtr.insertCell(i);
					newcell.innerHTML = (browser=="IE")?"":"&nbsp;";
				}
				break;
			case 'rowdelete':
				nexttr == ''?(deltr=thcElement.parentNode):(deltr=nexttr);
				nexttr = (deltr.nextSibling)?deltr.nextSibling:(deltr.previousSibling)?deltr.previousSibling:'';
				if(deltr.tagName.toLowerCase()!='tr'){alert(HD_LANG['selectdelrow']);return;}
				deltr.parentNode.deleteRow(deltr.rowIndex);
				thcTableFlag=0
				break;
			case 'colinbefore':
				nexttd == ''?(tbtd = thcElement):(tbtd=nexttd);
				if(tbtd.tagName.toLowerCase()!='td'){alert(HD_LANG['selectoperatecol']);return;}
				if(tbtd.cellIndex!=''){tbindex = tbtd.cellIndex;}
				tbindex = (tbindex==0)?0:(tbindex);
				tbbody = tbtd.parentNode.parentNode;
				for(var i=0;i<tbbody.childNodes.length;i++){
					var tr = tbbody.childNodes[i];
					if(tr.tagName.toLowerCase() == "tr"){
						var newcell = tr.insertCell(tbindex);
						newcell.innerHTML = (browser=="IE")?"":"&nbsp;";
					}
				}
				break;
			case 'colinafter':
				nexttd == ''?(tbtd = thcElement):(tbtd=nexttd);
				if(tbtd.tagName.toLowerCase()!='td'){alert(HD_LANG['selectoperatecol']);return;}
				if(tbtd.cellIndex!=''){tbindex = tbtd.cellIndex;}
				tbindex++;
				tbbody = tbtd.parentNode.parentNode;
				for(var i=0;i<tbbody.childNodes.length;i++){
					var tr = tbbody.childNodes[i];
					if(tr.tagName.toLowerCase() == "tr"){
						var newcell = tr.insertCell(tbindex);
						newcell.innerHTML = (browser=="IE")?"":"&nbsp;";
					}
				}
				break;
			case 'coldelete':
				var deltd = (nexttd=='')?thcElement:nexttd;
				nexttd = (deltd.nextSibling)?deltd.nextSibling:(deltd.previousSibling)?deltd.previousSibling:'';
				if(deltd.tagName.toLowerCase()!='td'){alert(HD_LANG['selectdelcol']);return;}
				var index = deltd.cellIndex;
				tbody = deltd;table = deltd;
				while(tbody.tagName.toLowerCase() != 'tbody'){
					tbody = tbody.parentNode;
				}
				while(table.tagName.toLowerCase() != 'table'){
					table = table.parentNode;
				}
				for(var i=0;i<tbody.childNodes.length;i++){
					var tr = tbody.childNodes[i];
					if(tr.tagName.toLowerCase() == "tr"){
						var newcell = tr.deleteCell(index);
					}
				}
				if(tr.childNodes.length == 0){
					table.parentNode.removeChild(table);
				}
				thcTableFlag=0;
				break;
			case 'cellprop':
			if(thcElement.tagName.toLowerCase() == 'td'){
				thcIframeDoc.body.focus();
				ThcSelectHtml();
				var html ='<div id="layouts">'
				+'<fieldset>'
					+'<legend>'+HD_LANG['cellproperty']+'</legend>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['height']+'</span><input id="tdHeight" type="text" size="5" />'
						+'<select id="tdHeightS">'
						+'<option value="%" selected="selected">'+HD_LANG['percenter']+'</option>'
						+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select> '
						+'<span>'+HD_LANG['textalign']+'</span>'
						+'<select id="tdTextAlign">'
						+'<option value="left" selected="selected">'+HD_LANG['left']+'</option>'
						+'<option value="center">'+HD_LANG['center']+'</option>'
						+'<option value="right">'+HD_LANG['right']+'</option>'
						+'</select> '
					+'</div>'
					+'<div class="pd5">'
						+'<span>'+HD_LANG['width']+'</span><input id="tdWidth" type="text" size="5" />'
						+'<select id="tdWidthS">'
						+'<option value="%" selected="selected">'+HD_LANG['percenter']+'</option>'
						+'<option value="px" >'+HD_LANG['pixel']+'</option>'
						+'</select> '
						+'<span>'+HD_LANG['verticalalign']+'</span>'
						+'<select id="tdVerticalAlign">'
						+'<option value="top">'+HD_LANG['top']+'</option>'
						+'<option value="center">'+HD_LANG['centersingle']+'</option>'
						+'<option value="bottom">'+HD_LANG['bottom']+'</option>'
						+'<option value="baseline" selected="selected">'+HD_LANG['baseline']+'</option>'
						+'</select>'
					+'</div>'
				+'</fieldset>'
				+'<div>'
				+'<span class="btn_pos"><input type="button" value="'+HD_LANG['confirm']+'" onclick = "ThcStyle(tbtd,\'td\');" /><input type="button" value="'+HD_LANG['cancel']+'" onclick = "ThcViewNone();" />'
				+'</span></div>'
				+'<div style="clear: both"></div>';
				init(html);
				tbtd = thcElement;
				ThcStyleBefore(tbtd,'td');}else{alert(HD_LANG['selectoperatecell']);return;}
				break;
			case 'cellinbefore':
				nexttd == ''?(tbtd = thcElement):(tbtd=nexttd);
				if(tbtd.tagName.toLowerCase()!='td'){alert(HD_LANG['selectoperatecell']);return;}
				if(tbtd.cellIndex!=''){tbindex = tbtd.cellIndex;}
				var tr = tbtd.parentNode;
				if(tbtd.cellIndex == 0){
					var newtd = tr.insertCell(0);
					newtd.innerHTML = (browser=="IE")?"":"&nbsp;";
				}else {
					var newtd = tr.insertCell(tbindex);
					newtd.innerHTML = (browser=="IE")?"":"&nbsp;";
				}
				break;
			case 'cellinafter':
				nexttd == ''?(tbtd = thcElement):(tbtd=nexttd);
				if(tbtd.tagName.toLowerCase()!='td'){alert(HD_LANG['selectoperatecell']);return;}
				if(tbtd.cellIndex!=''){tbindex = tbtd.cellIndex;}
				var tr = tbtd.parentNode;
				var newtd = tr.insertCell(++tbindex);
				newtd.innerHTML = (browser=="IE")?"":"&nbsp;";
				break;
			case 'celldelete':
				nexttd == ''?(td=thcElement):(td=nexttd);
				nexttd = (td.nextSibling)?td.nextSibling:(td.previousSibling)?td.previousSibling:'';
				if(td.tagName.toLowerCase()!='td'){alert(HD_LANG['selectdelcell']);return;}
				table = tbody = td;
				while(tbody.tagName.toLowerCase() != 'tbody'){
					tbody = tbody.parentNode;
				}
				while(table.tagName.toLowerCase() != 'table'){
					table = table.parentNode;
				}
				var tr = td.parentNode;
				td.parentNode.removeChild(td);
				if(tr.childNodes.length == 0){
					tr.parentNode.removeChild(tr);
				}
				if(tbody.childNodes.length == 0){
					table.parentNode.removeChild(table);
				}
				thcTableFlag=0;
				break;
		}
	}else{
		alert(HD_LANG['selecttable']);return;
	}
}


function ThcReplaceLink(str){
	str = str.replace(/%C2%A0/g,'');
	//str = str.replace(/%20/g,'');
	return str;
}

function ThcInsertTable(){
	var element = thcIframeDoc.createElement('div');
	var thcTableRows = document.getElementById('tableRows').value;
	var thcTableCols = document.getElementById('tableCols').value;
	var t = thcIframeDoc.createElement("table");
	t.className = "table";
	for (var i = 0; i < thcTableRows; i++) {
		var rowElement = t.insertRow(i);
		for (var j = 0; j < thcTableCols; j++) {
			var cellElement = rowElement.insertCell(j);
			cellElement.innerHTML = (browser=="IE")?"":"&nbsp";
		}
	}
	element.appendChild(t);
	ThcViewNone();
	ThcInsertHTML(element);
}

function insertAfter(newElement, targetElement){
  var parent = targetElement.parentNode;
  if(parent.lastChild == targetElement){
   parent.appendChild(newElement);
  }else{
   parent.insertBefore(newElement, targetElement.nextSibling);
  }
}

function ThcWebLink(){
	if(ThcReplaceLink(encodeURI(thcRangeText))!=""){
		var thclink = document.getElementById('linkUrl').value;
		if((thclink == "")||(thclink.indexOf("http://")==-1)){alert(HD_LANG['addressnotnone']);return;}
		var element = document.createElement("a");
		element.setAttribute("href",thclink);
		element.setAttribute("target","_blank");
		element.innerHTML = ThcHtmlReplace(thcRangeText);
		ThcInsertHTML(element);
	}else{
		alert(HD_LANG['selecttext']);ThcViewNone();
	}
}

function ThcInsertPicOrder(picsrc){
	picsrc_o = picsrc.replace(/s_/g,'');
	var i = 0;
    while (document.formpic.picAlign[i]) {
        if (document.formpic.picAlign[i].checked) {
            var picalign = document.formpic.picAlign[i].value;
            break;
        }else 
            i++;
    }
    var picalt = (document.formpic.picAlt.value == "") ? document.getElementById('title').value : document.formpic.picAlt.value;
	var thcPicString = '<A title="'+HD_LANG['originalpic']+'" href="' + picsrc_o + '" target=_blank>'
		+'<img title="'+HD_LANG['prefiximglink'] + picalt + '" alt="'+HD_LANG['prefiximglink'] + picalt + '" src="' + picsrc + '" />'
		+'</A>';
	var div = document.createElement('div');
	if(picalign == "right"){
		div.className = "img img_r";	
	}else{
		div.className = "img img_l";
	}
	div.innerHTML = thcPicString;
	var strong = document.createElement('strong');
	strong.innerHTML = picalt;	
	div.appendChild(strong);
	setTimeout(function(){strong.style.width = div.getElementsByTagName("img")[0].offsetWidth+"px";},3000);

    if ((thcElement != null) &&(thcElement.tagName)&&(thcElement.tagName.toLowerCase() == 'img')) {
        var pnode = thcElement.parentNode;
		var ppnode = thcElement.parentNode.parentNode;
		pnode_tag = pnode.tagName.toLowerCase();
		ppnode_tag = ppnode.tagName.toLowerCase();
		switch(ppnode_tag){
			case "div":
			break;
			case "td":
				var p = ppnode.parentNode;
				while(p.tagName.toLowerCase()!="table"){
					p = p.parentNode;
				}
				var l = p.getElementsByTagName("img").length;
				if(l>1){
					insertbefore(pnode,div);
        			ppnode.removeChild(pnode);
					insertbefore(div,div.firstChild);
					div.parentNode.removeChild(div);
					ThcViewNone();
					return;
				}else{
					ppnode = p;
				}	
			break;
		}
		insertbefore(ppnode,div);
		ppnode.parentNode.removeChild(ppnode);
		
        ThcViewNone();
        return;
    }
    ThcInsertHTML(div);
}

ThcInsertMedia = function(){
	var ThcMediaUrl = document.getElementById('mediaUrl').value;
   	var thcMediaFormats = document.getElementsByName('mediaFormat');
   	for (var i=0; i<thcMediaFormats.length; i++){
		if(thcMediaFormats[i].checked){
			var thcMediaFormat = thcMediaFormats[i].value;
  		}
	}
	var width = document.getElementById('mediaWidth').value;
	var height = document.getElementById('mediaHeight').value;
	ThcMediaUrl = ThcMediaUrl.replace(/(^\s+)|(\s+$)/,"");
	var mediatype = ThcMediaUrl.substring(ThcMediaUrl.lastIndexOf('.')+1,ThcMediaUrl.length);
	if(mediatype == 'rm'||mediatype == 'rmvb'){
		var string = '<OBJECT ID=video1 CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" HEIGHT='+height+' WIDTH='+width+'>\
				<param name="_ExtentX" value="9313">\
				<param name="_ExtentY" value="7620">\
				<param name="AUTOSTART" value="0">\
				<param name="SHUFFLE" value="0">\
				<param name="PREFETCH" value="0">\
				<param name="NOLABELS" value="0">\
				<param name="SRC" value="'+ThcMediaUrl+'">\
				<param name="CONTROLS" value="ImageWindow">\
				<param name="CONSOLE" value="Clip1">\
				<param name="LOOP" value="0">\
				<param name="NUMLOOP" value="0">\
				<param name="CENTER" value="0">\
				<param name="MAINTAINASPECT" value="0">\
				<param name="BACKGROUNDCOLOR" value="#000000">\
				<embed SRC type="audio/x-pn-realaudio-plugin" CONSOLE="Clip1" CONTROLS="ImageWindow" HEIGHT="'+height+'" WIDTH="'+width+'" AUTOSTART="false">\
				</OBJECT>';
		}else if(mediatype == 'wmv'){
			var string = '<object align=center classid=CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95 class=OBJECT id=MediaPlayer width='+width+' height='+height+' >\
			<param name=AUTOSTART VALUE=true >\
			<param name=ShowStatusBar value=-1>\
			<param name=Filename value="'+ThcMediaUrl+'">\
	<embed type=application/x-oleobject flename=mp src="'+ThcMediaUrl+'" width='+width+' height='+height+'></embed>\
			</object>';
		}else if(mediatype == 'wma'){
			var string = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" id="MediaPlayer1" >\
			<param name="Filename" value="'+ThcMediaUrl+'">\
			<param name="PlayCount" value="1">\
			<param name="AutoStart" value="0">\
			<param name="ClickToPlay" value="1">\
			<param name="DisplaySize" value="0">\
			<param name="EnableFullScreen Controls" value="1">\
			<param name="ShowAudio Controls" value="1">\
			<param name="EnableContext Menu" value="1">\
			<param name="ShowDisplay" value="1">\
		</object>';
		}else if(mediatype == 'mp3'){
			var string = '<EMBED src="'+ThcMediaUrl+'" width='+width+' height='+height+' volume=70 autostart=true></EMBED>';
		}else if(mediatype == 'swf'){
			var string = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'+width+'" height="'+height+'">\
			<param name="movie" value="'+ThcMediaUrl+'" />\
			<param name="quality" value="high" />\
			<embed src="11" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'+width+'" height="'+height+'"></embed>\
			</object>';
		}else{
			alert(HD_LANG['formatnonsupport']);
		}
	
	var element = document.createElement("span");
	element.style.border = "1px";
	element.innerHTML = string;
	ThcInsertHTML(element);
}

ThcCodeSwitch = function (){
	ThcViewNone();
	var html = '<div id="thcTextDiv" style="border:1px solid #A6B6C5;background: rgb(255, 255, 255) none repeat scroll 0% 0%; width: 736px; height: 400px;">'
		+'<textarea name="thcText" id="thcText" style="background: #fff; width: 100%; height: 400px; font-size: 14px; line-height: 2; margin: 0; padding: 0; border: 0px none #FFFFFF;">'
		+thcIframeDoc.body.innerHTML+'</textarea>'
		html += '</div>';
	html += '<div  style="width:736px; padding: 5px 0pt; text-align: center;"><input type="button" value="'+HD_LANG['update']+'" onclick = "ThcCodeUpdate();" style = "margin-right:10px;" /><input type="button" onclick="ThcViewNone();" value="'+HD_LANG['cancel']+'"/></div>';	
	
	init(html); 
	document.getElementById('thcText').focus();
}
ThcCodeUpdate = function(){
	thcIframeDoc.body.innerHTML = document.getElementById('thcText').value;
	ThcFilterCode();
	ThcViewNone();
}

ThcCodePreview = function(){
	ThcFilterStyle();
	var newWindow = window.open('', 'ThcEditor','width=800,height=600,left=30,top=30,resizable=yes,scrollbars=yes');
	var editorvalue = editor.getHTML();
	var innerstring = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';	
	innerstring += '<html  xmlns="http://www.w3.org/1999/xhtml"><head><title>ThcEditor</title>';
	innerstring += '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><LINK href="/thceditor/editor_neirong.css" type=text/css rel=stylesheet></head><body>';
	innerstring += '<div class="wordcut">'+editorvalue+'</div>';
	innerstring += '</body></html>';
	newWindow.document.open();
	newWindow.document.write(innerstring);
	newWindow.document.close();
	
}

function ThcTbcssBeFore(){
	if(tbStyleTable.caption!=null){
		document.getElementById('tbCaption').value=tbStyleTable.caption.innerHTML;
	}
	document.getElementById('tbDesc').value = tbStyleTable.summary;
	document.getElementById('tbCssFloat').value = tbStyleTable.style.cssFloat;
	if(tbStyleTable.style.height){
		document.getElementById('tbHeight').value=(tbStyleTable.style.height.indexOf('%')!='-1')?tbStyleTable.style.height.substring(0,tbStyleTable.style.height.indexOf('%')):((tbStyleTable.style.height.indexOf('px')!='-1')?tbStyleTable.style.height.substring(0,tbStyleTable.style.height.indexOf('px')):tbStyleTable.style.height);
		document.getElementById('tbHeightS').value= (tbStyleTable.style.height.indexOf('%')!='-1')?'%':'px';
	}
	document.getElementById('tbTextAlign').value=tbStyleTable.style.textAlign;
	document.getElementById('tbWidth').value=(tbStyleTable.style.width.indexOf('%')!='-1')?tbStyleTable.style.width.substring(0,tbStyleTable.style.width.indexOf('%')):((tbStyleTable.style.width.indexOf('px')!='-1')?tbStyleTable.style.width.substring(0,tbStyleTable.style.width.indexOf('px')):tbStyleTable.style.width);
	document.getElementById('tbWidthS').value=(tbStyleTable.style.width.indexOf('%')!='-1')?'%':'px';
	document.getElementById('tbVerticalAlign').value=tbStyleTable.style.verticalAlign;
	document.getElementById('tbCellSpacing').value=tbStyleTable.cellSpacing;
	document.getElementById('tbCellPadding').value=tbStyleTable.cellPadding;
	document.getElementById('tbBorder').value=tbStyleTable.border;
	document.getElementById('tbFrame').value=tbStyleTable.frame;
	document.getElementById('tbRules').value=tbStyleTable.rules;
}

function ThcTableStyle(){
	var tbCaption = document.getElementById('tbCaption').value;
	var tbDesc = document.getElementById('tbDesc').value;
	var tbCssFloat = document.getElementById('tbCssFloat').value;
	var tbHeight = document.getElementById('tbHeight').value;
	var tbHeightS = document.getElementById('tbHeightS').value;
	var tbTextAlign = document.getElementById('tbTextAlign').value;
	var tbWidth = document.getElementById('tbWidth').value;
	var tbWidthS = document.getElementById('tbWidthS').value;
	
	var tbVerticalAlign = document.getElementById('tbVerticalAlign').value;
	var tbCellSpacing = document.getElementById('tbCellSpacing').value;
	var tbCellPadding = document.getElementById('tbCellPadding').value;
	var tbBorder = document.getElementById('tbBorder').value;
	var tbFrame = document.getElementById('tbFrame').value;
	var tbRules = document.getElementById('tbRules').value;
	
	var caption = tbStyleTable.caption;
	if(tbCaption!=''){
		if (!caption) {
			caption = thcIframeDoc.createElement("caption");
			tbStyleTable.insertBefore(caption, tbStyleTable.firstChild);
		}
		caption.innerHTML = tbCaption;
	}else{
		if (caption) {
			caption.parentNode.removeChild(caption);
		}
	}
	tbStyleTable.style.width = ""+tbWidth+tbWidthS;
	if(tbHeight!=''){
		tbStyleTable.style.height = ""+tbHeight+tbHeightS;
	}
	tbStyleTable.summary = tbDesc;
	tbStyleTable.style.cssFloat = tbCssFloat
	tbStyleTable.style.textAlign = tbTextAlign;
	tbStyleTable.style.verticalAlign = tbVerticalAlign;
	tbStyleTable.cellSpacing = tbCellSpacing;
	tbStyleTable.cellPadding = tbCellPadding;
	tbStyleTable.border = tbBorder;
	tbStyleTable.frame = tbFrame;
	tbStyleTable.rules = tbRules;
	ThcViewNone(); 
}
function ThcStyleBefore(obj,tag){
	if(obj.style.height){
		document.getElementById(tag+'Height').value=(obj.style.height.indexOf('%')!='-1')?obj.style.height.substring(0,obj.style.height.indexOf('%')):((obj.style.height.indexOf('px')!='-1')?obj.style.height.substring(0,obj.style.height.indexOf('px')):obj.style.height);
		document.getElementById(tag+'HeightS').value= (obj.style.height.indexOf('%')!='-1')?'%':'px';
	}
	if(obj.align){
		document.getElementById(tag+'TextAlign').value=obj.align;}
	if(obj.style.width){
		document.getElementById(tag+'Width').value=(obj.style.width.indexOf('%')!='-1')?obj.style.width.substring(0,obj.style.width.indexOf('%')):((obj.style.width.indexOf('px')!='-1')?obj.style.width.substring(0,obj.style.width.indexOf('px')):obj.style.width);
		document.getElementById(tag+'WidthS').value=(obj.style.width.indexOf('%')!='-1')?'%':'px';}
	if(obj.valign){
		document.getElementById(tag+'VerticalAlign').value=obj.valign;}
}
function ThcStyle(obj,tag){
	var trHeight = document.getElementById(tag+'Height').value;
	var trHeightS = document.getElementById(tag+'HeightS').value;
	var trTextAlign = document.getElementById(tag+'TextAlign').value;
	var trWidth = document.getElementById(tag+'Width').value;
	var trWidthS = document.getElementById(tag+'WidthS').value;
	var trVerticalAlign = document.getElementById(tag+'VerticalAlign').value;	
	if(trWidth!=''){
		obj.style.width = ""+trWidth+trWidthS;
	}
	if(trHeight!=''){
		obj.style.height = ""+trHeight+trHeightS;
	}
	obj.align = trTextAlign;
	obj.valign = trVerticalAlign;
	ThcViewNone(); 
}
function thcTableFormate(){
	tbStyleTable= '';
	tbbody = '';
	tbtr = '';
	tbtd = '';
	tbindex = '';
}

function ThcHtmlReplace(str){
	str = str.replace(/&/g,'&amp;');
	str = str.replace(/</g,'&lt;');
	str = str.replace(/>/g,'&gt;');
	str = str.replace(/"/g,'&quot;');
	return str;
}

function ThcScriptReplace(str){
	str = str.replace(/<(script.*?)>/gi, "&lt;$1&gt;");
	str = str.replace(/<\/script>/gi, "&lt;/script&gt;");
	return str;
}

function ThcSelectHtml(){
	if(browser == 'IE'){
		thcSelect = thcIframeDoc.selection;
		thcRange = thcSelect.createRange();
		thcRangeText = thcRange.text;
	}else{
		thcSelect = document.getElementById('thcIframe').contentWindow.getSelection();
		thcRange = thcSelect.getRangeAt(0);
		thcRangeText = thcRange.toString();
	}
}

function ThcInsertHTML(insertNode){
	try{
	if (browser == 'IE') {
		if (thcSelect.type.toLowerCase() == 'control') {
			thcRange.item(0).outerHTML = '';
			thcRange.pasteHTML(insertNode.outerHTML);
		} else {
			thcRange.pasteHTML(insertNode.outerHTML);
		}
	} else {
		thcSelect.removeAllRanges();
		thcRange.deleteContents();
		var startRangeNode = thcRange.startContainer;
		var startRangeOffset = thcRange.startOffset;
		var newRange = document.createRange();
		if (startRangeNode.nodeType == 3 && insertNode.nodeType == 3) {
			startRangeNode.insertData(startRangeOffset, insertNode.nodeValue);
			newRange.setEnd(startRangeNode, startRangeOffset + insertNode.length);
			newRange.setStart(startRangeNode, startRangeOffset + insertNode.length);
		} else {
			var afterNode;
			if (startRangeNode.nodeType == 3) {
				 var textNode = startRangeNode;
				 startRangeNode = textNode.parentNode;
				 var text = textNode.nodeValue;
				 var textBefore = text.substr(0, startRangeOffset);
				 var textAfter = text.substr(startRangeOffset);
				 var beforeNode = document.createTextNode(textBefore);
				 var afterNode = document.createTextNode(textAfter);
				 startRangeNode.insertBefore(afterNode, textNode);
				 startRangeNode.insertBefore(insertNode, afterNode);
				 startRangeNode.insertBefore(beforeNode, insertNode);
				 startRangeNode.removeChild(textNode);
			} else {
				if (startRangeNode.tagName.toLowerCase() == 'html') {
					startRangeNode = startRangeNode.childNodes[0].nextSibling;
					afterNode = startRangeNode.childNodes[0];
				} else {
					afterNode = startRangeNode.childNodes[startRangeOffset];
					}
					startRangeNode.insertBefore(insertNode, afterNode);
				}
				newRange.setEnd(afterNode, 0);
				newRange.setStart(afterNode, 0);
			}
			thcSelect.addRange(newRange);
		}
	}catch(e){
		var br = thcIframeDoc.createElement('br');
		insertAfter(br,insertNode);	
	}
	ThcViewNone();
}

function ThcPicChange(){
	var evt = ThcGetEvent();
	var element = evt.srcElement||evt.target;
	if(element.id == "thcScript"||element.id == "thcScript_m"){return;}
	var srcarr = element.src.split('\/');
	var srcend = srcarr[srcarr.length-1];
	if(srcend.indexOf("_over")>0){
		srcarr[srcarr.length-1] = srcend.replace(/_over.gif/g,'.gif');
	}else{
		srcarr[srcarr.length-1] = srcend.replace(/.gif/g,'_over.gif');
	}
	var srcstr = srcarr.join('\/');
	element.setAttribute("src",srcstr);
}

var biaoqian_tag = document.getElementById("biaoqian_tag");
var yuanyin_tag = document.getElementById("yuanyin_tag");
var zhaiyao_tag = document.getElementById("zhaiyao_tag");
var fujian_tag = document.getElementById("fujian_tag");
var biaoqian_but = document.getElementById("biaoqian_but");
var yuanyin_but = document.getElementById("yuanyin_but");
var zhaiyao_but = document.getElementById("zhaiyao_but");
var fujian_but = document.getElementById("fujian_but");

function thcSwitchBtab(thccase){
	switch(thccase){
		case "biaoqian":
			if(biaoqian_tag && biaoqian_tag.style.display == "block"){
				biaoqian_but.className=null;
				biaoqian_tag.style.display = "none";
			}else{
				if(biaoqian_tag || biaoqian_but){
					biaoqian_but.className="bg-no-top";
					biaoqian_tag.style.display = "block";
				}
				if(yuanyin_tag || yuanyin_but){
					yuanyin_but.className=null;
					yuanyin_tag.style.display = "none";
				}
				if(zhaiyao_but || zhaiyao_but){
					zhaiyao_but.className=null;
					zhaiyao_tag.style.display = "none";
				}
				if(fujian_tag || fujian_but){
					fujian_but.className="null";
					fujian_tag.style.display = "none";
				}
			}
		break;
		case "yuanyin":
			if(yuanyin_tag && yuanyin_tag.style.display == "block"){
				yuanyin_but.className=null;
				yuanyin_tag.style.display = "none";
			}else{
				if(biaoqian_tag || biaoqian_but){
					biaoqian_but.className=null;
					biaoqian_tag.style.display = "none";
				}
				if(yuanyin_tag || yuanyin_but){
					yuanyin_but.className="bg-no-top";
					yuanyin_tag.style.display = "block";
				}
				if(zhaiyao_but || zhaiyao_but){
					zhaiyao_but.className=null;
					zhaiyao_tag.style.display = "none";
				}
				if(fujian_tag || fujian_but){
					fujian_but.className="null";
					fujian_tag.style.display = "none";
				}
			}
		break;
		case "zhaiyao":
			if(zhaiyao_tag && zhaiyao_tag.style.display == "block"){
				zhaiyao_but.className=null;
				zhaiyao_tag.style.display = "none";
			}else{
				if(biaoqian_tag || biaoqian_but){
					biaoqian_but.className=null;
					biaoqian_tag.style.display = "none";
				}
				if(yuanyin_tag || yuanyin_but){
					yuanyin_but.className=null;
					yuanyin_tag.style.display = "none";
				}
				if(zhaiyao_but || zhaiyao_but){
					zhaiyao_but.className="bg-no-top";
					zhaiyao_tag.style.display = "block";
				}
				if(fujian_tag || fujian_but){
					fujian_but.className="null";
					fujian_tag.style.display = "none";
				}
			}
		break;
		case "fujian":
			if(fujian_tag && fujian_tag.style.display == "block"){
				fujian_but.className=null;
				fujian_tag.style.display = "none";
			}else{
				if(biaoqian_tag || biaoqian_but){
					biaoqian_but.className=null;
					biaoqian_tag.style.display = "none";
				}
				if(yuanyin_tag || yuanyin_but){
					yuanyin_but.className=null;
					yuanyin_tag.style.display = "none";
				}
				if(zhaiyao_but || zhaiyao_but){
					zhaiyao_but.className=null;
					zhaiyao_tag.style.display = "none";
				}
				if(fujian_tag || fujian_but){
					fujian_but.className="bg-no-top";
					fujian_tag.style.display = "block";
				}
			}
		break;
		default:
			biaoqian_but.className=null;
			biaoqian_tag.style.display = "none";
			yuanyin_but.className=null;
			yuanyin_tag.style.display = "none";
			zhaiyao_but.className=null;
			zhaiyao_tag.style.display = "none";
			fujian_but.className=null;
			fujian_tag.style.display = "none";
		break;
	}
	daxiao();
}

function thcIframeClick(){
	ThcViewNone();
	thcSwitchBtab()
	thcElement = null;
	if(browser == "IE"){
		var evt = window.frames("thcIframe").window.event;
	}else{
		var evt = ThcGetEvent();
	}
	thcElement = evt.srcElement||evt.target;
	
	if(thcElement&&thcElement.tagName.toLowerCase() == "div"&&thcElement.className == "hdwiki_tmml"){
		isSection = 1;
	}else{
		isSection = 0;
	}
	thcTableFlag = 1;

	thcTextCssSelect.selectedIndex=0;
	
	ThcReClickDown(thcElement);
}

function ThcReClickDown(obj){
	var elname = obj.tagName.toLowerCase();
	switch(elname){	
		case 'strong' :
			var thcsrc = document.getElementById('thcBold').src;
			if(thcsrc.lastIndexOf('_over.gif')!=-1){ThcReClickDown(obj.parentNode);return;}
			var string = thcsrc.replace(/.gif/g,'_over.gif');
			document.getElementById('thcBold').setAttribute("src",string);
			ThcReClickDown(obj.parentNode);
			break;
		case 'u' :
			var thcsrc = document.getElementById('thcItalic').src;
			if(thcsrc.lastIndexOf('_over.gif')!=-1){ThcReClickDown(obj.parentNode);return;}
			var string = thcsrc.replace(/.gif/g,'_over.gif');
			document.getElementById('thcItalic').setAttribute("src",string);
			ThcReClickDown(obj.parentNode);
			break;
		case 'em' :
			var thcsrc = document.getElementById('thcUnderline').src;
			if(thcsrc.lastIndexOf('_over.gif')!=-1){ThcReClickDown(obj.parentNode);return;}
			var string = thcsrc.replace(/.gif/g,'_over.gif');
			document.getElementById('thcUnderline').setAttribute("src",string);
			ThcReClickDown(obj.parentNode);
			break;
		default : return;
	}
}

function ThcPicover(obj){
	if(!obj){return;}
	var thcsrc = obj.src;
	if(thcsrc.lastIndexOf('_over.gif')!=-1){
		var string = thcsrc.replace(/_over.gif/g,'.gif');
		obj.setAttribute("src",string);
	}
}

function thcSelectCheck(obj,thcSelctString){
	var flag=0;
	for(i=0;i<obj.options.length;i++){
		if(obj.options[i].value == thcSelctString){
			obj.selectedIndex=i; flag=1;
		}
	}
	if(flag==0){obj.selectedIndex=0;}
}

function thcStopEvent(e){
	if(browser == "IE"){
		e.cancelBubble = true;
		e.returnValue = false;
	} else {
		e.preventDefault();
		e.stopPropagation();
	}
}

function thcContextmenu(e) {
	if(browser == "IE"){
		var evt = window.frames("thcIframe").window.event;
	}else{var evt = ThcGetEvent();}
	thcElement = evt.srcElement||evt.target;
	thcTableFlag = 1;
	thcStopEvent(e);
	var thcRmenu = new thcRButton();
	thcRmenu.Init(e);
}
function ThcViewNone(){
	thcView.style.display = "none";
	if (document.getElementById('float_window')) {
		document.getElementById('bgiframe').style.display = "none";
		document.getElementById('float_window').style.display = "none";
	}
}

function thcfocus(){
	if(browser == "IE"){
		thcIframeDoc.body.focus();
	}else{
		var editor = document.getElementById("thcIframe").contentWindow; 
		editor.focus();	
	}
}
function ThcKeyDown(ev){
	var keyEvent = (document.all && ev.type == "keydown") || (ev.type == "keypress");
	if (keyEvent && ev.ctrlKey) {
		var key = String.fromCharCode(document.all ? ev.keyCode : ev.charCode).toLowerCase();
		switch(key){
			case "v":
				if(browser != "IE"){
					ThcSelectHtml();
					var input = document.getElementById('pasteinput');
					input.focus();
					input.select();
					setTimeout(function(){
						var span = thcIframeDoc.createElement("span");
						ThcInsertHTML(span)
						span.appendChild( document.createTextNode( input.value ));
						span.parentNode.insertBefore(span.lastChild,span);
						span.parentNode.removeChild(span);
					},10);
				}
				break;
			case "w":
				thcStopEvent(ev);
				ThcButtonClick('section');
				break;
			case "q":
				thcStopEvent(ev);
				ThcButtonClick('innerlink');
				break;
			case "l":
				thcStopEvent(ev);
				ThcButtonClick('justifyleft');
				break;
			case "e":
				thcStopEvent(ev);
				ThcButtonClick('justifycenter');
				break;
			case "a":
				thcStopEvent(ev);
				ThcButtonClick('selectall');
				break;		
		}
	}
	if (keyEvent && ev.altKey) {
		var key = String.fromCharCode(document.all ? ev.keyCode : ev.charCode).toLowerCase();
		switch(key){
			case "w":thcStopEvent(ev);
				ThcButtonClick('section');
				break;
			case "q":thcStopEvent(ev);
				ThcButtonClick('innerlink');
				break;
			case "l":thcStopEvent(ev);
				ThcButtonClick('justifyleft');
				break;
			case "e":thcStopEvent(ev);
				ThcButtonClick('justifycenter');
				break;
		}
	}
}

var winWidth = 0;
var winHeight = 0;
function findDimensions(){
	if (window.innerWidth){
		winWidth = window.innerWidth;
	}else if ((document.body) && (document.body.clientWidth)){
		winWidth = document.body.clientWidth;
	}
	if (window.innerHeight){
		winHeight = window.innerHeight;
	}else if ((document.body) && (document.body.clientHeight)){
		winHeight = document.body.clientHeight;
	}
	if (document.documentElement  && document.documentElement.clientHeight && document.documentElement.clientWidth){
		winHeight = document.documentElement.clientHeight;
		winWidth = document.documentElement.clientWidth;
	}
}
function daxiao(){
	findDimensions();
	var toplength = document.getElementById('tags_tt').clientHeight+12;
	var bottomlength = 45;
	if(document.getElementById('thcBase').style.display ==''||document.getElementById('thcBase').style.display == "block"){
		toplength += document.getElementById('thcBase').clientHeight;
	}
	else if(document.getElementById('thcHigh').style.display == "block"){
		toplength += document.getElementById('thcHigh').clientHeight;
	}
	else if(document.getElementById('thcHighTable').style.display == "block"){
		toplength += document.getElementById('thcHighTable').clientHeight;
	}
	if(document.getElementById('biaoqian_tag')){
		bottomlength += document.getElementById('biaoqian_tag').offsetHeight;
	}
	if(document.getElementById('yuanyin_tag')){
		bottomlength += document.getElementById('yuanyin_tag').offsetHeight;
	}
	if(document.getElementById('zhaiyao_tag')){
		bottomlength += document.getElementById('zhaiyao_tag').offsetHeight;
	}
	if(document.getElementById('fujian_tag')){
		bottomlength += document.getElementById('fujian_tag').offsetHeight;
	}
	if(winHeight-toplength-bottomlength < 10){ return;}
	thcIframe.style.height = winHeight-toplength-bottomlength+"px";
	//thcTextarea.style.height = winHeight-toplength-bottomlength-2+"px";
	
	var fdiv = document.getElementById("float_window");
    fdiv.style.left = (document.body.clientWidth - fdiv.clientWidth) / 2 + "px";
    fdiv.style.top = (document.body.clientHeight - fdiv.clientHeight) / 2 + "px";
}

function thcGetChangeImg(){
	var thcImgs = document.getElementsByTagName('img');
	for( i=0;i<thcImgs.length;i++){
		var obj = thcImgs[i];
		if(document.addEventListener){
			obj.addEventListener('mouseover', ThcPicChange, false);
			obj.addEventListener('mouseout', ThcPicChange, false);
		}else if(document.attachEvent){
			obj.attachEvent('onmouseover', ThcPicChange);
			obj.attachEvent('onmouseout', ThcPicChange);
		}
	}
}


function urlreplace(tagname,attribute,regex){
	var objs = thcIframeDoc.getElementsByTagName(tagname);
	for(var i=0;i<objs.length;i++){
		var href = objs[i].getAttribute(attribute).replace(regex,'');
		objs[i].setAttribute(attribute,href);
	}
}



function deleteTag(gTagObj){
	gTagObj.parentNode.parentNode.removeChild(gTagObj.parentNode);
	if(document.getElementById('biaoqian_tag').getElementsByTagName('input').length%6==0){
		daxiao();
	}
}
function addTag(objname){
	var ul = document.getElementById(objname);
	if(ul.getElementsByTagName('input').length>=20){
		alert(HD_LANG['labeltwentymore']);
	}else{
		//ul.innerHTML = ul.innerHTML+'<li><input type="text" class="adr_tag_input" maxlength="20" /><span onclick="deleteTag(this)">['+HD_LANG['delete']+']</span></li>';
		var li = document.createElement('li');
		li.innerHTML = '<input type="text" name="tags[]" class="adr_tag_input" maxlength="20" /><span onclick="deleteTag(this)">['+HD_LANG['delete']+']</span>';
		ul.appendChild(li);
		if((ul.getElementsByTagName('input').length-1)%6 ==0){
			daxiao();
		}
	};
}

// JavaScript Document

function ThcTbcssBeFore(){
	if(tbStyleTable.caption!=null){
		document.getElementById('tbCaption').value=tbStyleTable.caption.innerHTML;
	}
	document.getElementById('tbDesc').value = tbStyleTable.summary;
	document.getElementById('tbCssFloat').value = tbStyleTable.style.cssFloat;
	if(tbStyleTable.style.height){
		document.getElementById('tbHeight').value=(tbStyleTable.style.height.indexOf('%')!='-1')?tbStyleTable.style.height.substring(0,tbStyleTable.style.height.indexOf('%')):((tbStyleTable.style.height.indexOf('px')!='-1')?tbStyleTable.style.height.substring(0,tbStyleTable.style.height.indexOf('px')):tbStyleTable.style.height);
		document.getElementById('tbHeightS').value= (tbStyleTable.style.height.indexOf('%')!='-1')?'%':'px';
	}
	document.getElementById('tbTextAlign').value=tbStyleTable.style.textAlign;
	document.getElementById('tbWidth').value=(tbStyleTable.style.width.indexOf('%')!='-1')?tbStyleTable.style.width.substring(0,tbStyleTable.style.width.indexOf('%')):((tbStyleTable.style.width.indexOf('px')!='-1')?tbStyleTable.style.width.substring(0,tbStyleTable.style.width.indexOf('px')):tbStyleTable.style.width);
	document.getElementById('tbWidthS').value=(tbStyleTable.style.width.indexOf('%')!='-1')?'%':'px';
	document.getElementById('tbVerticalAlign').value=tbStyleTable.style.verticalAlign;
	document.getElementById('tbCellSpacing').value=tbStyleTable.cellSpacing;
	document.getElementById('tbCellPadding').value=tbStyleTable.cellPadding;
	document.getElementById('tbBorder').value=tbStyleTable.border;
	document.getElementById('tbFrame').value=tbStyleTable.frame;
	document.getElementById('tbRules').value=tbStyleTable.rules;
}
function ThcTableStyle(){
	var tbCaption = document.getElementById('tbCaption').value;
	var tbDesc = document.getElementById('tbDesc').value;
	var tbCssFloat = document.getElementById('tbCssFloat').value;
	var tbHeight = document.getElementById('tbHeight').value;
	var tbHeightS = document.getElementById('tbHeightS').value;
	var tbTextAlign = document.getElementById('tbTextAlign').value;
	var tbWidth = document.getElementById('tbWidth').value;
	var tbWidthS = document.getElementById('tbWidthS').value;

	var tbVerticalAlign = document.getElementById('tbVerticalAlign').value;
	var tbCellSpacing = document.getElementById('tbCellSpacing').value;
	var tbCellPadding = document.getElementById('tbCellPadding').value;
	var tbBorder = document.getElementById('tbBorder').value;
	var tbFrame = document.getElementById('tbFrame').value;
	var tbRules = document.getElementById('tbRules').value;

	var caption = tbStyleTable.caption;
	if(tbCaption!=''){
		if (!caption) {
			caption = thcIframeDoc.createElement("caption");
			tbStyleTable.insertBefore(caption, tbStyleTable.firstChild);
		}
		caption.innerHTML = tbCaption;
	}else{
		if (caption) {
			caption.parentNode.removeChild(caption);
		}
	}
	tbStyleTable.style.width = ""+tbWidth+tbWidthS;
	if(tbHeight!=''){
		tbStyleTable.style.height = ""+tbHeight+tbHeightS;
	}
	tbStyleTable.summary = tbDesc;
	tbStyleTable.style.cssFloat = tbCssFloat
	tbStyleTable.style.textAlign = tbTextAlign;
	tbStyleTable.style.verticalAlign = tbVerticalAlign;
	tbStyleTable.cellSpacing = tbCellSpacing;
	tbStyleTable.cellPadding = tbCellPadding;
	tbStyleTable.border = tbBorder;
	tbStyleTable.frame = tbFrame;
	tbStyleTable.rules = tbRules;
	ThcViewNone();
}
function ThcStyleBefore(obj,tag){
	if(obj.style.height){
		document.getElementById(tag+'Height').value=(obj.style.height.indexOf('%')!='-1')?obj.style.height.substring(0,obj.style.height.indexOf('%')):((obj.style.height.indexOf('px')!='-1')?obj.style.height.substring(0,obj.style.height.indexOf('px')):obj.style.height);
		document.getElementById(tag+'HeightS').value= (obj.style.height.indexOf('%')!='-1')?'%':'px';
	}
	if(obj.align){
		document.getElementById(tag+'TextAlign').value=obj.align;}
	if(obj.style.width){
		document.getElementById(tag+'Width').value=(obj.style.width.indexOf('%')!='-1')?obj.style.width.substring(0,obj.style.width.indexOf('%')):((obj.style.width.indexOf('px')!='-1')?obj.style.width.substring(0,obj.style.width.indexOf('px')):obj.style.width);
		document.getElementById(tag+'WidthS').value=(obj.style.width.indexOf('%')!='-1')?'%':'px';}
	if(obj.valign){
		document.getElementById(tag+'VerticalAlign').value=obj.valign;}
}
function ThcStyle(obj,tag){
	var trHeight = document.getElementById(tag+'Height').value;
	var trHeightS = document.getElementById(tag+'HeightS').value;
	var trTextAlign = document.getElementById(tag+'TextAlign').value;
	var trWidth = document.getElementById(tag+'Width').value;
	var trWidthS = document.getElementById(tag+'WidthS').value;
	var trVerticalAlign = document.getElementById(tag+'VerticalAlign').value;
	if(trWidth!=''){
		obj.style.width = ""+trWidth+trWidthS;
	}
	if(trHeight!=''){
		obj.style.height = ""+trHeight+trHeightS;
	}
	obj.align = trTextAlign;
	obj.valign = trVerticalAlign;
	ThcViewNone();
}
function thcTableFormate(){
	tbStyleTable= '';
	tbbody = '';
	tbtr = '';
	tbtd = '';
	tbindex = '';
}


var rbutobj = '';
function thcRButton(){
	this.Init = function(e){
		rbutobj = thcElement;
		//thcIframeDoc.body.focus();
		ThcSelectHtml();
		thcRBControl(e);
	}
}

var thcRBcell = {
	cut : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/ed_cut.gif" /></td><td class="adr_rb_right"><a href ="#" onclick = "ThcButtonClick(\'cut\')">'+HD_LANG['cut']+'</a></td></tr>',
	copy : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/ed_copy.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'copy\')">'+HD_LANG['copy']+'</a></td></tr>',
	paste : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/paste.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'paste\');">'+HD_LANG['paste']+'</a></td></tr>',
	section : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/section.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'section\')">'+HD_LANG['addcatalog']+'</a></td></tr>',
	removesection : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/section.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'removesection\')">'+HD_LANG['delcatalog']+'</a></td></tr>',
	innerlink : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/innerlink.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'innerlink\')">'+HD_LANG['innerlink']+'</a></td></tr>',
	unlink : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/2.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'unlink\')">'+HD_LANG['dellink']+'</a></td></tr>',

	bold : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/bold.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'bold\')">'+HD_LANG['bold']+'</a></td></tr>',
	italic : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/italic.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'italic\')">'+HD_LANG['italic']+'</a></td></tr>',
	underline : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/underline.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'underline\')">'+HD_LANG['underline']+'</a></td></tr>',
	justifyleft : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/ed_align_left.gif"  /></td><td class="adr_rb_right"><a href ="#" onclick = "ThcButtonClick(\'justifyleft\')">'+HD_LANG['left']+'</a></td></tr>',
	justifycenter : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/ed_align_center.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcButtonClick(\'justifycenter\')">'+HD_LANG['center']+'</a></td></tr>',

	imgprop : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/picture-por.gif"  /></td><td class="adr_rb_right"><a href ="#" onclick = "ThcImgButtonClick()">'+HD_LANG['picproperty']+'</a></td></tr>',

	rowinabove : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/row-insert-above.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'rowinabove\')">'+HD_LANG['upinsertrow']+'</a></td></tr>',
	rowinunder : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/row-insert-under.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'rowinunder\')">'+HD_LANG['downinsertrow']+'</a></td></tr>',
	rowdelete : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/row-delete.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'rowdelete\')">'+HD_LANG['delrow']+'</a></td></tr>',

	colinbefore : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/col-insert-before.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'colinbefore\')">'+HD_LANG['leftinsertcol']+'</a></td></tr>',
	colinafter : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/col-insert-after.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'colinafter\')">'+HD_LANG['rightinsertcol']+'</a></td></tr>',
	coldelete : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/col-delete.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'coldelete\')">'+HD_LANG['delcol']+'</a></td></tr>',

	cellinbefore : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/cell-insert-before.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'cellinbefore\')">'+HD_LANG['leftinsertcell']+'</a></td></tr>',
	cellinafter : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/cell-insert-after.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'cellinafter\')">'+HD_LANG['rightinsertcell']+'</a></td></tr>',
	celldelete : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/cell-delete.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "ThcTableButtonView(\'celldelete\')">'+HD_LANG['delcell']+'</a></td></tr>',
	line : '<tr><td height="1" bgcolor="#FFFFFF"></td><td height="1" bgcolor="#78a3d2"></td></tr>',
	delnode1 : '<tr><td class="adr_rb_left"><img src="./js/editor/images/rightbutton/delete.gif" /></td><td class="adr_rb_right" ><a href ="#" onclick = "del()">'+HD_LANG['delete'],
	delnode2 : HD_LANG['element']+'</a></td></tr>'
}

function ThcImgButtonClick(){
	if ((thcElement != null) && (thcElement.tagName.toLowerCase() == 'img')) {
		var pnode = thcElement.parentNode;
		var pnodeclone = thcElement.parentNode;
		while(pnodeclone.tagName.toLowerCase()!="body"){
			if(pnodeclone.tagName.toLowerCase()=="td"||pnodeclone.tagName.toLowerCase()=="div"){
				var flag = 1;
				break;
			}else{
				pnodeclone = pnodeclone.parentNode;	
			}
		}
		var ppnode = pnode.parentNode;
		if(flag){
			ppnode = pnodeclone;
		}
		var ppnode_tag = ppnode.tagName.toLowerCase();
		var alignvalue = "";
		var altvalue = "";
		switch(ppnode_tag){
			case "div":
				if(ppnode.className){
					switch(ppnode.className){
						case "img img_l":
							alignvalue = "left";
						break;
						case "img img_r":
						default:
							alignvalue = "right";
						break;
					}
				}
				var altobj = ppnode.getElementsByTagName("strong")[0];
				if(altobj){
					altvalue = altobj.innerHTML;
				}
			break;
			case "tr":
			case "td":
				var p = ppnode.parentNode;
				while(p.tagName.toLowerCase()!="table"){				
					p = p.parentNode;
				}
				var l = p.getElementsByTagName("img").length;
				if(l>1){
					var alignvalue = "right";
				}else{
					var alignvalue = p.align;
					var altobj = p.getElementsByTagName("font")[0];
					if(browser == "IE"){
						if(altobj.innerText){
							altvalue = altobj.innerText;							
						}
					}else{
						while(altobj){
							if(altobj.firstChild&&altobj.firstChild.nodeType != 3){
								altobj = altobj.firstChild;
							}else{
								altvalue = altobj.innerHTML;
								break;
							}
						}						
					}
				}	
			break;
			default: break;
		}
		var html = '<div id="insetimg">'
	        +'<div id="upload">'
	            +'<fieldset>'
	                +'<ol>';
		if(alignvalue == "left"){
			html += '<li>'+HD_LANG['align']+' <input type="radio" checked="checked" value="left" name="picAlign"/>'+HD_LANG['left']+'<input type="radio" value="right" name="picAlign"/>'+HD_LANG['right']+'</li>';	
		}else{
			html += '<li>'+HD_LANG['align']+' <input type="radio" value="left" name="picAlign"/>'+HD_LANG['left']+'<input type="radio" checked="checked" value="right" name="picAlign"/>'+HD_LANG['right']+'</li>';
		}		
	    html += '</ol>'
	            +'</fieldset>'
	        +'</div>'
	        +'<div class="adr_imgplt">'
	            +'<fieldset>'
	                +'<legend>'
	                    +HD_LANG['pictitle']
	                +'</legend>'
	                +'<textarea name="picAlt" rows="3" style="width:97%">'
					+altvalue
	                +'</textarea>'
	            +'</fieldset>'
	        +'</div>'
	        +'<div style="padding: 5px 0pt; width: 380px; text-align: center;">'
	            +'<span><input type="button" onclick="ThcImgSetting();" value="'+HD_LANG['confirm']+'" /></span><span><input type="button" onclick="ThcViewNone();" value="'+HD_LANG['cancel']+'"/></span>'
	        +'</div>'
	    +'</div>';
		init(html);
	}
}
function ThcImgSetting(){
	var i = 0;
    while (document.formpic.picAlign[i]) {
        if (document.formpic.picAlign[i].checked) {
            var picalign = document.formpic.picAlign[i].value;
            break;
        }else 
            i++;
    }
	var picalt = document.formpic.picAlt.value;
	if(!picalt){
		picalt = document.getElementById('title').value;
	}
	if(thcElement.align){
		thcElement.align = "";	
	}
	var clonenode = thcElement.parentNode.cloneNode(true);
	if(clonenode.tagName.toLowerCase()!='a'){
		clonenode = thcElement.cloneNode(true);
	}
	var w = thcElement.offsetWidth;
	var div = thcIframeDoc.createElement('div');
	if(picalign == "right"){
		div.className = "img img_r";	
	}else{
		div.className = "img img_l";
	}
	var strong = thcIframeDoc.createElement('strong');
	strong.innerHTML = picalt;
	div.appendChild(clonenode);	
	div.appendChild(strong);
	setTimeout(function(){strong.style.width = w+"px";},3000);
    var pnode = thcElement.parentNode;
	var pnodeclone = thcElement.parentNode;
	while(pnodeclone.tagName.toLowerCase()!="body"){
		if(pnodeclone.tagName.toLowerCase()=="td"||pnodeclone.tagName.toLowerCase()=="div"){
			var flag = 1;
			break;
		}else{
			pnodeclone = pnodeclone.parentNode;	
		}
	}
	var ppnode = pnode.parentNode;
	if(flag){
		ppnode = pnodeclone;
	}
	var ppnode_tag = ppnode.tagName.toLowerCase();
	switch(ppnode_tag){
		case "div":
			if(ppnode.className=="img img_l"||ppnode.className=="img img_r"){
			}else if(pnode.tagName.toLowerCase() == "a"&&pnode.childNodes.length<=1){
				ppnode = pnode;
			}else{
				ppnode = thcElement;
			}
		break;
		case "tr":
		case "td":
			var p = ppnode.parentNode;
			while(p.tagName.toLowerCase()!="table"){
				p = p.parentNode;
			}
			var l = p.getElementsByTagName("img").length;
			if(l>1){
				insertbefore(pnode,div);
    			pnode.parentNode.removeChild(pnode);
				ThcViewNone();
				return;
			}else{
				ppnode = p;
			}	
		break;
		default: ppnode = thcElement;break;
	}
	insertbefore(ppnode,div);
	ppnode.parentNode.removeChild(ppnode);
    ThcViewNone();
    return;
}




function del(){
	if(rbutobj.tagName.toLowerCase == "body"||rbutobj.tagName.toLowerCase == "html"){
		ThcViewNone();
		return;
	}
	rbutobj.parentNode.removeChild(rbutobj);
	ThcViewNone();
}

function thcGetPos(el) {
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if (el.offsetParent) {
		var tmp = thcGetPos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};

function thcRemoveLink(){
	while (rbutobj.firstChild)
		rbutobj.parentNode.insertBefore(rbutobj.firstChild, rbutobj);
		rbutobj.parentNode.removeChild(rbutobj);
}

function thcRBControl(evt){
	evt || (evt = window.event);
	var iframeXY = thcGetPos(thcIframe);
	thcView.style.left = evt.clientX +iframeXY.x+3+ "px";
	thcView.style.top = evt.clientY +iframeXY.y+2+ "px";
	thcView.style.display = 'block';
	thcView.style.overflow = 'visible';
	var string = '<div class="adr_right_button"><table border="0" cellpadding="0" cellspacing="0">';
	var objtagName = rbutobj.tagName.toUpperCase();
	switch(rbutobj.tagName.toLowerCase()){	
		case "a":
			if(thcRangeText!=''){
				string += thcRBcell['cut']+thcRBcell['copy']+thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter']+thcRBcell['line'];
			}else{
				string += thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter']+thcRBcell['line'];
			}
			if(rbutobj.href!=''){
				string += thcRBcell['unlink']+thcRBcell['delnode1']+objtagName+thcRBcell['delnode2'];
			}else{
				//string += thcRBcell['weblink'];
			}
			break;
		case "table" : string += /*thcRBcell['tableprop']+*/thcRBcell['delnode1']+objtagName+thcRBcell['delnode2'];
			break;
		case "td":
			string += thcRBcell['rowinabove']+thcRBcell['rowinunder']+thcRBcell['rowdelete']+thcRBcell['line'];
			string += thcRBcell['colinbefore']+thcRBcell['colinafter']+thcRBcell['coldelete']+thcRBcell['line'];
			string += thcRBcell['cellinbefore']+thcRBcell['cellinafter']+thcRBcell['celldelete']+thcRBcell['line'];
			if(thcRangeText!=''){
				string += thcRBcell['cut']+thcRBcell['copy']+thcRBcell['paste']+thcRBcell['line']+thcRBcell['innerlink']+thcRBcell['line'];
			}
			string += thcRBcell['paste']+thcRBcell['justifyleft']+thcRBcell['justifycenter'];
			break;
		case "body":
			if(thcRangeText!=''){
				string += thcRBcell['cut']+thcRBcell['copy']+thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter']+thcRBcell['line']+thcRBcell['innerlink'];
			}else{
				string += thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter'];
			}
			break;
		case "img" : string += thcRBcell['imgprop']+thcRBcell['delnode1']+objtagName+thcRBcell['delnode2'];
			break;
		
		case "div" :
			if(rbutobj.className == 'hdwiki_tmml'){
				string += thcRBcell['removesection'];	
				break;
			}
		case "html" : return;
		break;
		default:
			if(thcRangeText!=''){
				string += thcRBcell['cut']+thcRBcell['copy']+thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter']+thcRBcell['line']+thcRBcell['section']+thcRBcell['innerlink']+thcRBcell['line']+thcRBcell['delnode1']+objtagName+thcRBcell['delnode2'];
			}else{
				string += thcRBcell['paste']+thcRBcell['line']+thcRBcell['justifyleft']+thcRBcell['justifycenter']+thcRBcell['line']+thcRBcell['delnode1']+objtagName+thcRBcell['delnode2'];}
	}
	string += "</table></div>";
	thcView.innerHTML = string;
}

function preg_replace(search, replace, str) {
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], "ig");
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

var MSG_UNLOAD= HD_LANG['cententnotsave'];
var UnloadConfirm = {};
UnloadConfirm.set = function(confirm_msg){
    window.onbeforeunload = function(event){
        event = event || window.event;
        event.returnValue = confirm_msg;
    }
}
UnloadConfirm.clear = function(){
    window.onbeforeunload = function(){};
}
UnloadConfirm.set(MSG_UNLOAD);

function reasonvalue(){
	var reasons = document.getElementsByName('editreason[]');
	for(var i = 0;i<reasons.length;i++){
		if(reasons[i].checked){
			return 1;
		}
		if(reasons[i].tagName.toLowerCase() == "textarea"&&reasons[i].value != ''){
			return 1;
		}
	}
	return;
}
ThcEditor = function(){
	this.Init = function(){
		browser = ThcGetBrowser();
		daxiao();
		window.onresize=daxiao;
		thcIframeDoc = (document.all)?document.frames("thcIframe").document:document.getElementById("thcIframe").contentDocument;
		thcIframeDoc.designMode = 'On';
		thcIframeDoc.open();
		var inithtml = document.getElementById('initDate').value;
		if((inithtml == "")&&(browser!='IE')){
			inithtml = "<br/>";	
		}
		thcIframeDoc.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
			+'<html xmlns="http://www.w3.org/1999/xhtml">'
			+'<head>'
			+'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
			+'<title>thcEditor</title>'
			+'<LINK href="js/editor/editor_neirong.css" type=text/css rel=stylesheet>'
			+'</head>'
			+'<body>'+inithtml+'</body></html>');
		thcIframeDoc.close();
		thcfocus();
		if(browser == "IE"){
			thcIframeDoc.body.onpaste=function(){
				var pstr=window.clipboardData.getData("text");
				window.clipboardData.setData("Text", pstr);
			}
		}
		
		if(thcIframeDoc.addEventListener){
			thcIframeDoc.addEventListener('mousedown', ThcViewNone, false);
			thcIframeDoc.addEventListener('click', thcIframeClick, false);
			thcIframeDoc.addEventListener("contextmenu", thcContextmenu, false);			
			thcIframeDoc.addEventListener('keypress', ThcKeyDown, false);
			thcIframeDoc.addEventListener('keydown', ThcKeyDown, false);	
		}else if(thcIframeDoc.attachEvent){
			thcIframeDoc.attachEvent('onmousedown', ThcViewNone);
			thcIframeDoc.attachEvent('onclick', thcIframeClick);
			thcIframeDoc.attachEvent("oncontextmenu", thcContextmenu);
			thcIframeDoc.attachEvent('onkeydown', ThcKeyDown);
		}
		thcGetChangeImg();
	}
}

ThcEditor.prototype.getHTML = function(){
	ThcFilterCode();
	var html='';
	if(thcIframeDiv.style.display != 'block'){
		ThcCodeSwitch();
	}
	html=thcIframeDoc.body.innerHTML;
	html = preg_replace(['<style.*?>[\\\s\\\S]*?<\/style>', '<script.*?>[\\\s\\\S]*?<\/script>', '<noscript.*?>[\\\s\\\S]*?<\/noscript>', '<select.*?>[\s\S]*?<\/select>', '<object.*?>[\s\S]*?<\/object>', '<!--[\\\s\\\S]*?-->', ' on[a-zA-Z]{3,16}\\\s?=\\\s?"[\\\s\\\S]*?"'], '', html);
	html= html.replace(/(\r\n|\n|\r)/ig, '');
 	return html;
}

ThcCodePublish = function(){
	var siteurl = document.getElementById('siteurl').value+"/";
	var regex = new RegExp(siteurl,"g");
	urlreplace('A','href',regex);
	urlreplace('IMG','src',regex);

	var tags = document.getElementsByName('tags[]');
	var tagsvalue=0;
	for(var i=0;i<tags.length;i++){
		if(tags[i].value!==''){
			tagsvalue++;
		}
	}
	if(tagsvalue<1){
		alert(HD_LANG['tagnone']);
		thcSwitchBtab();
		thcSwitchBtab('biaoqian');
		return;
	}
	var thcEditorCode = editor.getHTML();
	thcEditorCode = thcEditorCode.replace(regex,'');
	document.getElementById("thcTextarea").value = thcEditorCode;
	if(thcEditorCode==''){
		alert(HD_LANG['cententnone']);
		return false;
	}
	if(document.getElementById('reasonelement')){
		if(reasonvalue()){
			UnloadConfirm.clear();
			document.editor.submit();
		}else{
			thcSwitchBtab();
			thcSwitchBtab('yuanyin');
			alert(HD_LANG['editreason']);
		}
	}else{
		UnloadConfirm.clear();
		document.editor.submit();
	}

}

ThcCodeCancel = function(){
	if(confirm( HD_LANG['goout'])){
		UnloadConfirm.clear();
		history.back();
	}else{
		return;
	}
}

var editor = new ThcEditor();
ThcViewNone();
editor.Init();
