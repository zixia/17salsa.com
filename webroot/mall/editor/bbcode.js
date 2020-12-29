var EDITOR_FILE_HANDLER = "editor/attachment.php";
var bbcode = {
  html2bbCode: function(elem, nofirst) {
    if (typeof(elem) == "string") {
      var tmp = document.createElement("DIV");
      tmp.innerHTML = elem;
      elem = tmp;
    }
    if (elem.tagName == "IFRAME" || elem.tagName == "SCRIPT" || elem.tagName == "STYLE") {
      return "";
    }
    var bbCode = new Array();
    var hasChildren = elem.hasChildNodes && elem.hasChildNodes();
    var tag = false;
    if (nofirst){
      var nodeType = elem.nodeType;
      switch (nodeType) {
        case 3:
          bbCode.push(elem.nodeValue);
        break;
        case 1:
          var tags = this.getBbCodeTag(elem);
          tags = !tags ? [] : tags;
          if (tags.length>0) {
            for (var j=0; j< tags.length; j++) {
              var tag = tags[j];
              if (tag.tagName == "") {
                bbCode.push(tag.value)
              } else {
                bbCode.push("[");
                bbCode.push(tag.tagName);
                if (tag.value!="") {
                  bbCode.push("=");
                  bbCode.push(tag.value);
                }
                bbCode.push("]");
                if (tag.inner) {
                  bbCode.push(tag.inner);
                }
              }
            }
          } else {
            //bbCode.push("&lt;");
            //bbCode.push(elem.tagName);
            //bbCode.push("&gt;");
            var lastUnbeknown = elem.tagName;
          }
        break;
      }
    }
    if (hasChildren && elem.nodeType == 1) {
      Element.cleanWhitespace(elem);
      for (var i=0; i < elem.childNodes.length; i++) {
        if (elem.tagName == "OL" || elem.tagName == "UL") {
          if (elem.childNodes[i].nodeType != 1) {
            continue;
          }
        }
        var c = this.html2bbCode(elem.childNodes[i], true);
        bbCode.push(c);
      }
    }

    if (tags) {
      for (var j=tags.length-1; j>=0; j--){
        var tag = tags[j];
        if (tag.tagName != "" && !tag.noCloseTag)
          bbCode.push("[/"+tag.tagName+"]");
      }
    }
    if (lastUnbeknown && lastUnbeknown != "") {
      //bbCode.push("&lt;/");
      //bbCode.push(lastUnbeknown);
      //bbCode.push("&gt;");
      lastUnbeknown = false;
    }
    tags = false;
    delete tags;
    return bbCode.join("");
  },

  getBbCodeTag: function(elem) {
    var res = [];
    var rtags = htmlTagParser.parseElementStyle(elem);
    if (rtags.length>0) {
      htmlTagParser._pushTag(res, rtags);
    }
    switch (elem.tagName) {
      case "SPAN":
        htmlTagParser._pushTag(res, htmlTagParser.parseSpan(elem));
      break;
      case "FONT":
        htmlTagParser._pushTag(res, htmlTagParser.parseFont(elem));
      break;
      case "DIV":
        htmlTagParser._pushTag(res, htmlTagParser.parseDiv(elem));
      break;
      case "UL":
      case "OL":
      case "LI":
        htmlTagParser._pushTag(res, htmlTagParser.parseList(elem));
      break;
      default :
        htmlTagParser._pushTag(res, htmlTagParser.parseOther(elem));
    }

    return res;
  },
  bbcode2Html: function (str) {
    str = str.replace(/</igm, '&lt;');
    str = str.replace(/>/igm, '&gt;');
    str = str.replace(/\[hr\]/igm, '<hr />');
    str = str.replace(/\[br\]/igm, '<br />');
    str = str.replace(/\[b\]/igm, '<strong>');
    str = str.replace(/\[\/b\]/igm, '</strong>');
    str = str.replace(/\[i\]/igm, '<i>');
    str = str.replace(/\[\/i\]/igm, '</i>');
    str = str.replace(/\[u\]/igm, '<u>');
    str = str.replace(/\[\/u\]/igm, '</u>');
    str = str.replace(/\[align=(left|center|right)\]/igm, '<div style="text-align:$1;">');
    str = str.replace(/\[\/align\]/igm, '</div>');
    str = str.replace(/\[list\]/igm, '<ul>');
    str = str.replace(/\[list=(\d+)\]/igm, '<ul start="$1" type="1"><li>abe2dbd2312123dfgdfsfssas');
    str = str.replace(/\[list=([a-zA-Z]+)\]/igm, '<ul start="$1" type="a"><li>abe2dbd2312123dfgdfsfssas');
    str = str.replace(/\[\/list\]/igm, '</li></ul>');
    str = str.replace(/\[\*\]/igm, '</li><li>');
    str = str.replace(/\[indent\]/igm, '<blockquote>');
    str = str.replace(/\[\/indent\]/igm, '</blockquote>');
    str = str.replace(/\[url\](((?!\[\/url\]).)+)\[\/url\]/igm, '<a href="$1" title="$1" target="_blank">$1</a>');
    str = str.replace(/\[url=([^\]\[]*)\]/igm, '<a href="$1" title="$1" target="_blank">');
    str = str.replace(/\[\/url\]/igm, '</a>');
    str = str.replace(/\[img\](.+?)\[\/img\]/igm, '<img src="$1" alt="$1" />');
    str = str.replace(/\[img(=(\d+),(\d+))?\](.+?)\[\/img\]/igm, '<img width="$2" height="$3" src="$4" alt="$4" />');
    str = str.replace(/\[backcolor=([^\]\[]*)\]/igm, '<span style="background-color:$1;">');
    str = str.replace(/\[\/backcolor\]/igm, '</span>');
    str = str.replace(/\[color=([^\]\[]*)\]/igm, '<font color="$1">');
    str = str.replace(/\[\/color\]/igm, '</font>');
    str = str.replace(/\[size=([^\]\[]*)\]/igm, '<font size="$1">');
    str = str.replace(/\[\/size\]/igm, '</font>');
    str = str.replace(/\[hr\]\[\/hr\]/igm, '<hr />');
    str = str.replace(/\[attach=([^\]\[]*)\](\d+)\[\/attach\]/igm, '<img src="$1" alt="$1" />');
    str = str.replace(/\[table=(([\d]+%?)((,)([a-z#0-9]+))?)\]/igm, '<table width="$2" bgcolor="$5">');
    str = str.replace(/\[tr\]/igm, '<tr>');
    str = str.replace(/\[td\]/igm, '<td>');
    str = str.replace(/\[\/tr\]/igm, '</tr>');
    str = str.replace(/\[\/td\]/igm, '</td>');
    str = str.replace(/\[\/table\]/igm, '</table>');
    str = str.replace(/\[font=([^\]\[]*)\]/igm, '<font face="$1">');
    str = str.replace(/\[\/font\]/igm, '</font>');
    //str = str.replace(//igm, '/>');
    str = str.replace(/(\r)?\n/g, '<br />');
    //str = str.replace(/\s/igm, '&nbsp;');
    str = str.replace(/<li>abe2dbd2312123dfgdfsfssas(((?!<\/li>)[\s\S])*)<\/li>/ig, "");
    return str;
  }

}

htmlTagParser = {
  _pushTag: function (arr, tags){
    if (tags.length > 0)
    {
      for(var i=0;i<tags.length; i++)
      {
        arr.push(tags[i]);
      }
    } else if(tags.tagName) {
      arr.push(tags);
    }
  },
  parseElementStyle: function(elem) {
    var res = new Array();

    if (elem.style.color != "") {
      this._pushTag(res, {tagName: "color", value: rgbToHex(elem.style.color)});
    }

    if (elem.style.fontSize != "") {
      this._pushTag(res, {tagName: "size", value: elem.style.fontSize.replace("px", "")});
    }
    if (elem.style.fontFamily != "") {
      this._pushTag(res, {tagName: "font", value: elem.style.fontFamily});
    }

    if (elem.style.textDecoration != "")
    {
      this._pushTag(res,{tagName: "u", value: ""});
    }

    if (elem.style.fontWeight == "bold"){
      this._pushTag(res, {tagName: "b", value: ""});
    }

    if (elem.style.fontStyle == "italic") {
      this._pushTag(res, {tagName: "i", value: ""});
    }

    return res;
  },
  parseSpan: function(elem) {
    var res = new Array();
    if (elem.face) {
      res.push({tagName: "font", value:elem.face});
    }
    return res;
  },
  parseList: function(elem) {
    var result = new Array();
    if (elem.tagName.toUpperCase() == "OL" || elem.tagName.toUpperCase() == "UL"){
      if (elem.firstChild.nodeType == 1 && elem.firstChild.tagName == elem.tagName) {
        result.push({tagName:"indent", value:""});
        return result;
      }
    }
    if (elem.tagName.toUpperCase() == "OL" || (elem.tagName == "UL" && (elem.type != "" || elem.getAttribute("start") != null))) {

      var val = elem.start == "" || elem.start == "-1" ? "1" : elem.start;

      if (elem.tagName == "UL" && elem.getAttribute("start") != null) {
        val = elem.getAttribute("start");
      }
      result.push({tagName:"list", value: val});
    } else if (elem.tagName.toUpperCase() == "UL") {
      result.push({tagName:"list", value: ""});
    }

    if (elem.tagName.toUpperCase() == "LI") {
      if (elem.parentNode.tagName == "OL" || elem.parentNode.tagName == "UL") {
        result.push({tagName:"*", value: "", noCloseTag: true});
      }
    }
    return result;
  },
  parseFont: function(elem) {
    var res = new Array();
    if (elem.color != "" || elem.style.color != ""){
      res.push({tagName: "color", value: elem.color || elem.style.color});
    }
    if (elem.size != "" || elem.style.fontSize != ""){
      var s = "";
      if (elem.size != "") {
        s = elem.size;
      } else {
        s = elem.style.fontSize.replace("px", "");
      }
      res.push({tagName: "size", value: s});
    }
    if (elem.face) {
      res.push({tagName: "font", value:elem.face});
    }
    return res;
  },
  parseDiv: function(elem) {
    var res = new Array();
    if (elem.style.textAlign != ""){
      res.push({tagName: "align", value: elem.style.textAlign});
    }
    return res;
  },
  parseOther: function(elem) {
    var res = new Array();
    switch (elem.tagName) {
      case "BR" :
        res.push({tagName: "", value: "\r\n", noCloseTag: true});
      break;
      case "STRONG" :
        res.push({tagName: "b", value: ""});
      break;
      case "U" :
        res.push({tagName: "u", value: ""});
      break;
      case "I" :
      case "EM" :
        res.push({tagName: "i", value: ""});
      break;
      case "P" :
        if (elem.align != "" || elem.style.textAlign != "")
          res.push({tagName: "align", value: elem.style.textAlign || elem.align});
      break;
      case "A" :
        var val = /(\{\$[a-zA-Z0-9\_\.]+?\})/.exec(elem.href);
        if (val){
            val = val[0];
        }else{
            val = elem.href
        }
        res.push({tagName: "url", value: val});
      break;
      case "BLOCKQUOTE":
        if (elem.firstChild.nodeType == 1) {
          if (elem.firstChild.tagName == "P" && elem.firstChild.align == "") {
            var pTag = elem.firstChild;
            for (var i=0;i<pTag.childNodes.length; i++) {
              elem.appendChild(pTag.childNodes[i]);
            }
            elem.removeChild(pTag);
          }
        }
        res.push({tagName: "indent", value: ""});
      break;
      case "IMG":
        var width = Math.max(parseInt(elem.style.width), parseInt(elem.width));
        var height = Math.max(parseInt(elem.style.height), parseInt(elem.height));
        var size = "";
        if (height > 0 && width > 0)
        {
          size = width+","+height;
        }
        res.push({tagName: "img", value:size , inner:elem.src});
      break;
      case "HR":
        res.push({tagName: "hr", value: "", noCloseTag:true});
      break;
      case "SCRIPT":
        res.push({tagName: "", value: "", noCloseTag:true});
      break;
    }
    return res;
  }
}

function rgbToHex(value)
{
  if (value.indexOf("#")!=-1)
  {
    return value;
  }
  value=value.replace("rgb(","")
  value=value.replace(")","")
  value=value.split(",")
  r=parseInt(value[0]);
  g=parseInt(value[1]);
  b=parseInt(value[2]);
  r = r.toString(16);
  if (r.length == 1) {
    r = "0" + r;
  }
  g = g.toString(16);
  if (g.length == 1) {
    g = "0" + g;
  }
  b = b.toString(16);
  if (b.length == 1) {
    b = "0" + b;
  }
  return ("#" + r + g + b).toUpperCase();
}
