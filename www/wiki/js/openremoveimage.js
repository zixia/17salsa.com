function openremoveimage(E){
	var url = E.href, title, img=$(E).find('img');
	title = img.attr('title');
	if(!title||title == 'null') title = img.attr('alt') || Lang.BigImage;
	if (url.match(/(\w+_){4}/i) && url.match(/(\w+)\.html/i)){
		url = url.match(/(\w+)\.html/i);
		var a = url[1].split("_");
		url = "http://"+a[0]+".att.hudong.com/"+a[1]+"/"+a[2]+"/"+a[3]+"."+a[4];
	}else if(url.match(/\.(jpg|gif|png)$/i) == null){
		return true;
	}
	$.dialog.box("image", title, 'img:'+url, E);
	return false;
}

$(document).ready(function(){
	var a, imgs = document.images, url;
	for(i=0; i<imgs.length; i++){
		url = imgs[i].src;
		a = $(imgs[i]).parent("a");
		if (url.indexOf('http:') == 0 && url.indexOf('uploads') > 0){
			url = 'uploads'+url.split('uploads')[1];
			if (imgs[i].src != url) imgs[i].src = url;
			if (a.size() == 1)a.attr('href', url);
		}
		
		if (a.size() == 1){
			a.click(function(){
				return openremoveimage(this);
			});
		}
	}
});