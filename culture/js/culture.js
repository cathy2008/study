window.onload=function(){
	alert("89");
	var aA=document.getElementsByTagName("a");
	for(var i=0;i<aA.length;i++){
		aA[i].onmouseover=function(){
			var This=this;
			This.time=setInterval(function(){
				This.style.width=This.offsetWidth+8+"px";
				if(This.offsetWidth>120){
					clearInterval(This.time);
				}
			},30)
		}
	}
}