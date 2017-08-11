
function Cart_buy(){
	
	
}

//提交验证
Cart_buy.prototype.commit = function(){
	
	var radios = $(".radio");
	var result = 0 ;
	for(var i = 0 ; i<radios.length ; i++){
		var item = $(radios[i]).attr("val");
		result = result + item ;
	}
	
	if(result > 0){
		return true;
	}else{
		return false;
	}
	
}


//统计数据
Cart_buy.prototype.commodity = function(){
	
	//获取店铺个数
	var stores = $(".collection");
	//console.log(stores);
	//console.log(stores.length);
	var stores_obj = {};
	var goods = 0;
	
	for(var i = 0 ; i< stores.length;i++){
		
		var store_id = $(stores[i]).attr("store_id");//店铺id
		
		//console.log(store_id);
		
		goods = $(stores[i]).find(".collect");
		
		var temp = new Array();
		var str;
		for(var y=0;y<goods.length;y++){
			var good = $(goods[y]);
			
			
			var c_temp = good.find(".radio");//每个商品
			var c_attr = c_temp.attr("val");//获取商品里面的val属性值
			
			//如果选中的话就把商品id存进临时数组里去
			if(c_attr == 1){
				var good_id = good.attr("good_id");
				var good_num = good.find("#h_num").val();
				var good_price = good.find(".price span")[0].innerHTML;
				var good_desc = good.find(".title span").html();
				var good_img = good.find(".s_img .image img").attr("src");
				//var good_guige = good.find("")
				
				//console.log(good_price);
				
				str= good_id+"*"+good_num+"*"+good_price+"*"+good_desc+"*"+good_img;
				//alert(str);
				temp.push(str);
			}
		}
//		console.log(temp[0]);
		
		if(temp.length >0){
			
			
			var good_ids = temp.join();
			//console.log(good_ids);
			//alert(good_ids);
			stores_obj[store_id] = good_ids ;
		}
		
	}
	return JSON.stringify(stores_obj);
	//console.log(stores_obj);
	
}


















