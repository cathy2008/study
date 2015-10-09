$(document).ready(function(){
	//随机背景图片
    var random_bg=Math.floor(Math.random()*5+1);
//    console.log(random_bg);
//    console.log(Math.random());
    var bg='url(./img/loginbg_'+random_bg+'.jpg)';
    $("body").css("background-image",bg);
    $(".username,.pwd").focus(function(){
    	$(".usernameinfo,.pwdinfo,.pwderror,.pwdsuccess").hide(200);
//    	console.log("123");
    });
    $("#submit").bind("click",function(){
    	var username=$(".username").val();
    	var pwd=$(".pwd").val();
//    	console.log(username);
//    	console.log(pwd);
    	
    	if(username==""){
    		$(".usernameinfo").show(200);
    		return;
    	}else if(pwd==""){
    		$(".pwdinfo").show(200);
    		return;
    	}
    	$.ajax({	
    		url:"./php/index.php",
    		datatype:"json",
    		data:{'number':username,'pwd':pwd},
    		success:function(data){
    			console.log(data.error);
    			if(data.error==1){
    				$(".pwdsuccess").show(200);
    			}else if(data.error==0){
    				$(".pwderror").show(200);
    			}
    		},
    		error:function(){
    			console.log("23937");
    		}
    		
    	});   	
    });
});