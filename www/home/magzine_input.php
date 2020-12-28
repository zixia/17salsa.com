<?php








?>
<html>
<head>
</head>
<body>
<form id="magzinesrc" target="./magzine.php">
<div id="colnum">
共<input type=text size=4 id="cols">个栏目&nbsp;
<input type=button id="colset" value="设置" onClick="javascript:this.form.colset.disabled=true;this.form.cols.disabled=true;">
</div>

<div id="col1">
<b>栏目1：</b>
<input type=hidden size=4 id="col1num"><br/>
<input type=text size=16 id="col1name">&nbsp;
<input type=text size=16 id="col1more">&nbsp;
<select id="col1type">
  <option value=1>双栏有推荐</option>
  <option value=2>单栏有推荐</option>
  <option value=3>双栏无推荐</option>
  <option value=4>单栏无推荐</option>
</select><br/>
<textarea cols=40 rows=10 id="col1url"></textarea><br/>
<br/>增加栏目<input type=button size=4 id="col1addcol" value=" + ">
</div>

</form>
<div id=""
</body>
</html>
