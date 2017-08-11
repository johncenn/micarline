


function stopPropagation(e) {
	if (e.stopPropagation) 
	  e.stopPropagation();//停止冒泡  非ie
	else
	  e.cancelBubble = true;//停止冒泡 ie
}

//滑动图片处理
$('#s').bind('touchmove',function(e){
	stopPropagation(e);
	 //$('.isshow'+item+' .s_img').show();
});

//优惠券响应
$(".shop_juan").click(function(){
	hui.maskShow();
	$(".juan_bottom").css("bottom","0");
	
	
	//全局mask响应
	$("#hui-mask").click(function(){
		hui.maskHide();
		$(".juan_bottom").css("bottom","-105%");
	});
	
	$(".j_btn").click(function(){
		$("#hui-mask").click();
	});
});


//促销打折响应
$(".point_desc").click(function(){
	hui.maskShow();
	$(".dazhe").css("bottom","0");
	
	
	//全局mask响应
	$("#hui-mask").click(function(){
		hui.maskHide();
		$(".dazhe").css("bottom","-105%");
	});
	
	$(".dazhe_btm").click(function(){
		$("#hui-mask").click();
	});
	
});

//基础服务响应
$(".server").click(function(){
	hui.maskShow();
	$(".btm_server").css("bottom","0");
	
	
	//全局mask响应
	$("#hui-mask").click(function(){
		hui.maskHide();
		$(".btm_server").css("bottom","-105%");
	});
	
	$(".server_btn").click(function(){
		$("#hui-mask").click();
	});
	
});

//底部规格选择响应
var sum_price = 0 ;
$(".g_select p").click(function(){
	
	//变色
	$(this).css("border-color","red").siblings("p").css("border-color","#E0E0E0");
	
	var input_sib = $(this).siblings("input[type=hidden]");
	console.log(input_sib.val());
	
	if(input_sib.val() == 2){
		var img = $(this).children();
		var src = img.attr("src");
		
		$(".product_img img").attr("src",src);
		
	}
	
	//input_sib.attr("val",$(this).attr("value"));//选中了多少价钱
	//input_sib.attr("select",1);//在点击购买的时候识别
	

	/*//重新统计
	sum_price = 0 ;
	var default_input = $(".s_a_box .guige_select_box input[type=hidden]");
	for(var i = 0 ; i < default_input.length ; ++i){
		var temp = default_input[i];
		temp = $(temp);
		
		var price = temp.attr("val") ;
		price = parseInt(price);
		sum_price = sum_price + price;
		console.log(price);
		console.log(sum_price);
	}
	$(".product_xq .pxq_price").html("¥"+sum_price);*/
	
	//默认的总价
	/*var le = $(".s_a_box .g_select p:first-child");
	var sum_price = 0 ;
	for(var i = 0 ; i < le.length ; ++i){
		var temp = le[i];
		var t = $(temp);
		var price = t.attr("value") ;
		price = parseInt(price);
		sum_price  = price + sum_price;
		console.log(sum_price);
	}*/
	
	//console.log(le);
});


$(".guige").click(function(){
	hui.maskShow();
	$(".btn_guige").css("bottom","0");
	
	
	//全局mask响应
	$("#hui-mask").click(function(){
		hui.maskHide();
		$(".btn_guige").css("bottom","-105%");
		
		var buynums = $.trim($('#buyNums').val());
		var title = "购买数量 *"+buynums;
		
		$(".guige .tips").html(title);
		
		
	});
	
	$(".shop_car").click(function(){
		$("#hui-mask").click();
	});
});

//底部添加收藏响应
$(".list_shopcar").click(function(){
	hui.maskShow();
	$(".btn_guige").css("bottom","0");
	
	
	//全局mask响应
	$("#hui-mask").click(function(){
		hui.maskHide();
		$(".btn_guige").css("bottom","-105%");
		
		var buynums = $.trim($('#buyNums').val());
		var title = "购买数量 *"+buynums;
		
		$(".guige .tips").html(title);
		
		
	});
	
	$(".shop_car").click(function(){
		$("#hui-mask").click();
	});
	
	
});









