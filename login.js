$(document).ready(function(){
	//随机背景图片
    var random_bg=Math.floor(Math.random()*5+1);
    var bg='url(./img/loginbg_'+random_bg+'.jpg)';
    $("body").css("background-image",bg);

});