function trim(str)
{
     return str.replace(/(^\s*)(\s*$)/g, '');
}

function isEmail(str){
  var reg = /^([a-zA-Z0-9_-\.])+@([a-zA-Z0-9_-](\.)?)+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
  return reg.test(str);
}

function isValidTrueName(strName){
  var str = trim(strName); //判断是否为全英文大写或全中文，可以包含空格
  var reg = /^[A-Z a-z u4E00-u9FA5]+$/;
  if(reg.test(str)){
    return false;
  }
  return true;
}

function isTel(str){
  var reg=/^(((\()?[0\+]\d{2,3}(\))?(-)?)?(0\d{2,3})-)?(\d{7,11})(-(\d{3,}))?$/ ;
  if(str.length<7 || str.length>24){
    return false;
  }
  else{
    return reg.test(str);
  }
}

function checkInput(theform)
{
  var userName = document.getElementById("name").value;
  var userPhone = document.getElementById("phone").value;
  var userEmail = document.getElementById("email").value;

  if(userName.length == 0)
  {
     alert("姓名不能为空，请重试");
     theform.name.focus();
     return false;
  }
  else if(userName.length < 2) //!isValidTrueName(userName))
  {
     alert("姓名输入不正确，请重试");
     theform.name.focus();
     return false;
  }
  else if(userPhone.length == 0 && userEmail.length == 0)
  {
     alert("联系方式不能为空，请重试");
     theform.phone.focus();
     return false;
  }
  else if(!isTel(userPhone))
  {
     alert("电话号码输入不正确，请重试");
     theform.phone.focus();
     return false;
  }
  else if(!isEmail(userEmail))
  {
     alert("邮箱输入不正确，请重试");
     theform.email.focus();
     return false;
  }

  document.getElementById("name").value = escape(userName.substr(0,30));
  document.getElementById("phone").value = escape(userPhone.substr(0,20));
  document.getElementById("email").value = escape(userEmail.substr(0,50));

  return true;
}

