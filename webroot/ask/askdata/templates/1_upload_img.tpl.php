<?php if(!defined('IN_CYASK')) exit('Access Denied'); ?>
<html>
<head>
<title>上传图片</title>
<style type="text/css">
<!--
form {margin: 0px;FONT-SIZE: 12px;}
input {margin: 0px;border:1px solid #555555;FONT-SIZE: 12px;}
div {margin: 0px;FONT-SIZE: 12px;}
-->
</style>
</head>
<body style="background-color:#efefef">
<form name="pastefm" action="" method="post" enctype="multipart/form-data" onsubmit="return check_pastefm(this);">
<input type="file" name="uploadfile" size="8" />
<input type="submit" name="upload" value="上传" />
</form>
<script type="text/javascript">
<?php if($return_msg) { echo $return_msg;?>
<?php } ?>
</script>
<script type="text/javascript">
function check_pastefm(f)
{
if(f.uploadfile.value=="")
{
alert('请选择要上传的图片');
return false;
}
parent.document.getElementById('upwin').value="正在上传,请稍等...";
}
</script>
</body>
</html>

