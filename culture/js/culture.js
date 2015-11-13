/*原生JS
 * window.onload=function(){  
	var aA=document.getElementsByTagName("a");
	for(var i=0;i<aA.length;i++){
		//鼠标经过时伸长
		aA[i].onmouseover=function(){
			clearInterval(this.time);//清理动画效果，以免动画累加
			var This=this;
			This.time=setInterval(function(){
				This.style.width=This.offsetWidth+8+"px";
				if(This.offsetWidth>160){
					clearInterval(This.time);//停止动画效果
				}
			},30)
		}
		//鼠标离开时收缩
		aA[i].onmouseout=function(){
			clearInterval(this.time);//清理动画效果，以免动画累加
			var This=this;
			This.time=setInterval(function(){
				This.style.width=This.offsetWidth-8+"px";
				if(This.offsetWidth<=120){
					This.style.width="120px";
					clearInterval(This.time);//停止动画效果
				}
			},30)
		}
	}
}*/
//使用jquery实现
/*$(function(){ 
	//相当于windows.onload,并比其性能要好
	$("a").hover(
		//鼠标的移入移出函数，需要两个参数：移入、移出
		function(){
			//使用jQuery自带的动画函数，且当把动画打开时先用stop()函数清理上一个动画
			$(this).stop().animate({"width":"160px"},200);
		},
		function(){
			$(this).stop().animate({"width":"120px"},200);
		}
	
	)
})*/

// 检测左右滑动插件
(function(){ 
    var LSwiperMaker = function(o){ 
        var that = this;
        this.config = o;
        this.control = false;
        this.sPos = {};
        this.mPos = {};
        this.dire;
        // this.config.bind.addEventListener('touchstart', function(){ return that.start(); } ,false);
        // 这样不对的，event对象只在事件发生的过程中才有效;
        this.config.bind.addEventListener('touchstart', function(e){ return that.start(e); } ,false);
        this.config.bind.addEventListener('touchmove', function(e){ return that.move(e); } ,false);
        this.config.bind.addEventListener('touchend', function(e){ return that.end(e); } ,false);
    };

    LSwiperMaker.prototype.start = function(e){ 
         var point = e.touches ? e.touches[0] : e;
         this.sPos.x = point.screenX;
         this.sPos.y = point.screenY;
    };
    LSwiperMaker.prototype.move = function(e){  
        var point = e.touches ? e.touches[0] : e;
        this.control = true;
        this.mPos.x = point.screenX;
        this.mPos.y = point.screenY;
    };
    LSwiperMaker.prototype.end = function(e){
        this.config.dire_h  && (!this.control ? this.dire = null : this.mPos.x > this.sPos.x ? this.dire = 'R' : this.dire = 'L');
        this.config.dire_h  || (!this.control ? this.dire = null : this.mPos.y > this.sPos.y ? this.dire = 'D' : this.dire = 'U');
        this.control = false;
        this.config.backfn(this);
    };
    window.LSwiperMaker = LSwiperMaker;
    document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);// 禁止微信touchmove冲突
}());
 

$(function(){
		/*$("a").hover(
				//鼠标的移入移出函数，需要两个参数：移入、移出
				function(){
					//使用jQuery自带的动画函数，且当把动画打开时先用stop()函数清理上一个动画
					$(this).stop().animate({"width":"160px"},200);
				},
				function(){
					$(this).stop().animate({"width":"120px"},200);
				}
			
			)*/
		
    var loadfirNew = false;     //用于判断是否是第一次加载
    var loadfirStory = false;
    var loadfirVideo = false;
    
    var NewpageNum = 1;         //默认为1页
    var StoryNum = 1;
    var VideoNum = 1;

    var $nowmenu = $("ul.newsheader").find("li:first-child");    //当前菜单
   
    var height = $("#maxwrap").css("height")>$(window).height()?$("#maxwrap").css("height"):$(window).height(); 
     $("#maxwrap").css("height",height);    //设置触摸为当前屏幕的最大高度

    var width   = $(window).width();                //获取屏幕宽度
    $(".container").css("width",width);             //设置容器宽度
    $(".containwarp").css("width",width*3);         //设置包裹层宽度

    $(".newslist").css("width",width-20);           //设置list宽度
    $(".storylist").css("width",width-20);
    $(".videolist").css("width",width-20);

    $(".newsheader li a").click(function(){
        $nowmenu = $(this).parent();        //保存当前的li节点
        $(this).find('h2').addClass("active");
        $(this).parent().siblings().find('h2').removeClass("active");
        var listname = $(this).find("h2").text();

        var page = $(this).find('h2').attr("data-page");    //获取页面页码基本上为1
        getNowPage(listname);
        // page++;
        // $(this).find('h2').attr("data-page",page); 

        return false;
    });

    var a = new LSwiperMaker({
        bind:document.getElementById("maxwrap"),  // 绑定的DOM对象 
        dire_h:true,     //true 判断左右， false 判断上下
        backfn:function(o){    //回调事件
             if(o.dire === 'L'){
                if($nowmenu.find("h2").text() !== "视频集"){
                    $nowmenu = $nowmenu.next();
                    $nowmenu.find('h2').addClass("active");
                    $nowmenu.siblings().find('h2').removeClass("active");
                    var listname = $nowmenu.find("h2").text();
                   
                    getNowPage(listname);
                   
                }
                
             }else if(o.dire === 'R'){
                if($nowmenu.find("h2").text() !== "列表文章"){
                    $nowmenu = $nowmenu.prev();
                    $nowmenu.find('h2').addClass("active");
                    $nowmenu.siblings().find('h2').removeClass("active");
                    var listname = $nowmenu.find("h2").text();   

                    getNowPage(listname);
                    
                }
             }
        }   
    });

    function getNowPage( listname ){
        var page = 1;
        if (listname == "列表文章"){
            if(loadfirNew == false){
                loadfirNew = true; 
                getPageContent(page,1);
            }
            $(".containwarp").animate({right:0},"slow");
        }else if(listname == "故事集") {
            if(loadfirStory == false){
                loadfirStory = true;
                getPageContent(page,2);
            }
            $(".containwarp").animate({right:width},"slow");
        }else if(listname == "视频集"){    //不可能到这里
            if(loadfirVideo == false){
                loadfirVideo = true;
                getPageContent(page,3);
                // console.log(11);
            }
            $(".containwarp").animate({right:width*2},"slow");
        }
    }

    getPageContent(1,1);        //初始化第一页功能
    loadfirNew = true;
    function getPageContent(page,moduletype){
        $.ajax({
            url:"./php/information_show.php",
            dataType:"json", 
            data:{
                type:"list",
                moduleId:moduletype,       //1:文章列表 2:故事集列表 3:视频集列表
                page:page
            },
            success:function(data){
                console.log(data);
                if(moduletype === 1 ){
                    NewpageNum = data.PageNum;
                }else if(moduletype === 2){
                    StoryNum = data.PageNum;
                }else if( moduletype === 3){
                    VideoNum = data.PageNum;
                }

                if(moduletype == 1){   //列表文章
                    $.each(data,function(i,item){
                        if(item.id != undefined){
                            var str = '<li class="newsli clearfix"><a href="news.html?arctileId='+item.id+'" class="newstitle">'+item.title+'</a>';
                            item.date = item.date.substr(0,10);
                            str += '<span class="newdate">'+item.date+'</span>';
                             $(".newul").append(str);
                        }
                    });
                    if(data.PageNum > 1){
                        var str = '<div class="loadmorebar" data-moduleId="1"><a href="javascript:void(0);">点击加载更多</a></div>'
                        $(".newul").parent().append(str);
                    }

                }else if(moduletype == 2){
                    $.each(data,function(i,item){
                        if(item.id != undefined){
                            var str = '<li class="newsli clearfix"><a href="news.html?arctileId='+item.id+'" class="newstitle">'+item.title+'</a>';
                            item.date = item.date.substr(0,10);
                            str += '<span class="newdate">'+item.date+'</span>';

                            $(".storyul").append(str);
                        }
                    });
                    if(data.PageNum > 1){
                        var str = '<div class="loadmorebar" data-moduleId="2"><a href="javascript:void(0);">点击加载更多</a></div>'
                        $(".storyul").parent().append(str);
                    }
                }else if(moduletype == 3){
                    $.each(data,function(i,item){
                        if(item.id != undefined){
                            var str = '<li class="videoli clearfix"><a href="javascript:void(0);"><img class="leftimg" src="'+item.thumb+'" /><div class="video_flag"></div></a>';                       
                            str += '<div class="newscontent"><div class="title"><a href="video.html?arctileId='+item.id+'">'+item.title+'</a></div>';
                            item.date = item.date.substr(0,10);
                            str += '<span class="date">'+item.date+'</span>';
                            $(".videoul").append(str);
                        }
                    });
                    if(data.PageNum > 1){
                        var str = '<div class="loadmorebar" data-moduleId="2"><a href="javascript:void(0);">点击加载更多</a></div>'
                        $(".videoul").parent().append(str);
                    }
                }
            },
            error:function(e){
                console.error(e);
            }
        });
    }

    $("body").delegate(".loadmorebar","click",function(){       //加载下一页
        var self = $(this);
        var datamoduleId = $(this).attr("data-moduleId");
        // $(this).attr("data-moduleId")
        $(".newsheader h2").each(function(i,item){
            if($(this).attr("data-moduleId") == datamoduleId){
                var page = $(this).attr("data-page");   //获取页码
                page ++;
                if(datamoduleId == 1 ){
                    if( page > NewpageNum){
                        tipTogle("已经到最后一页");
                    }else{
                         $(this).attr("data-page",page);
                        getPageContent(page,datamoduleId);
                        self.remove();
                    }
                }else if(datamoduleId == 2){
                    if( page > StoryNum){
                        tipTogle("已经到最后一页");
                    }else{
                         $(this).attr("data-page",page);
                        getPageContent(page,datamoduleId);
                        self.remove();
                    }
                }else if(datamoduleId == 3){
                    if( page > VideoNum){
                        tipTogle("已经到最后一页");
                    }else{
                         $(this).attr("data-page",page);
                        getPageContent(page,datamoduleId);
                        self.remove();
                    }
                }
            }
        }); 
    });
    // 设置弹出框
    function tipTogle(str){
        $(".popOperTip").remove();
        $("body").append('<div class="popOperTip">'+str+'</div>');
        $(".popOperTip").fadeIn(1000);
        setTimeout('$(".popOperTip").fadeOut(1000)',1500);
    }
})