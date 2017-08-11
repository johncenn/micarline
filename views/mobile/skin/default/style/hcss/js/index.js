//var seach_url = "http://"+window.location.host+"/git/micarshop/";
var seach_url = "http://"+window.location.host+"/";
console.log(seach_url);
$(function(){
	  //var seach_url = ;
	  //监听输入框，获取里面的内容
	  $('#searchKey').bind('input propertychange', function() {
		 var  list2 = $("<div class='list i2'></div>");
	  	$(".list").hide();
	 	var str = $(this).val();
	 	console.log(str);
	 	if(str.length == 0){
	 		$(".list").show();
	 		//清空所有元素，包括当前
	 		$(".i2").remove();

	 		
	 	}else{
	 		var xhr = new XMLHttpRequest();
			xhr.open("GET",seach_url+"site/mapStore_seach?content="+str,true);
			xhr.onload = function(e){
				
				var msg= this.responseText;
				eval('var da='+msg);
				console.log("返回");
				console.log(da);
				
				if(da.length>0){
					var wrap = $(".hui-wrap");
					
					
					for(var i = 0 ; i<da.length;++i){
						
						var gps = da[i]['gps'];
						if(gps == ""){
							gps = "23,113";
						}
						
						
						var href = 'http://apis.map.qq.com/tools/poimarker?type=0&marker=coord:'+gps+';title:'+
							da[i]['store_name']+';addr:'+da[i]['address']+"&key=OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77&referer=myapp";
						var a = $("<a href="+href+" class=item ></a>");
						var div1 = $("<div class=icon><img src="+img_url+"/local.png /></div>");
						var content = $("<div class=content></div>");
						var c_div1 = $("<div class=title><p>"+da[i]['store_name']+"</p><p>"+da[i]['address']+"</p></div>");
						var c_div2 = $("<div class=go><img src="+img_url+"/go.png /><span>去这里</span></div>");
						
						content.append(c_div1);
						content.append(c_div2);
						a.append(div1);
						a.append(content);
						list2.append(a);
					}
					wrap.append(list2);
				}
			}
			xhr.send();
	 	}
	 	
	 	
	 	
	 	
	 });
	 
	  
	
})
