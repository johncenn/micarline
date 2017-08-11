

function cart_deal(){
	
	var sum_good_price = 0.00;//总价
	
	//（每个店铺全选效果）为每个父店铺元素添加响应
	this.select = function(obj){
		
		for(var i = 0 ; i<obj.length;++i){
			
			obj[i].addEventListener("click",function(){
				
				//console.log($(this).attr("val"));
				var f = $(this).attr("val");
				if(f == 0){
					//console.log("in if");
					
					//效果
					setcolor(this,f);
					
					var items = $(this).children(".store_id");
					var store_id = items.val();
					//每个店铺下面的商品
					var store_goods = $(".store_item_radio"+store_id);
					
					//子效果
					for(var i = 0 ; i < store_goods.length ; ++i){
						
						var store_goods_item = store_goods.get(i);
						setcolor(store_goods_item,f);
						
						var fl = $(store_goods_item).attr("m");
						//console.log("fl is "+fl)
						//计算价格
						if(fl == 0){
							//console.log("计算加");
							calculation_price(store_goods_item,0);
						}
						
						
						//console.log(sum_good_price);
					}
					
				}else{
					
					//把全选选项设成原始效果
					var sum_radio_val = $(".sum_radio").attr("val");
					if(sum_radio_val == 1){
						setcolor($(".sum_radio").get(0),f);
					}
					
					var f = 2;
					//效果
					setcolor(this,f);
					
					var items = $(this).children(".store_id");
					var store_id = items.val();
					
					//每个店铺下面的商品
					var store_goods = $(".store_item_radio"+store_id);
					
					//子效果
					for(var i = 0 ; i < store_goods.length ; ++i){
						
						var store_goods_item = store_goods.get(i);
						
						setcolor(store_goods_item,f);
						
						var fl = $(store_goods_item).attr("m");
						//console.log("fl is "+fl)
						//计算价格
						if(fl == 1){
							//console.log("计算加");
							calculation_price(store_goods_item,1);
						}
						
						//console.log(sum_good_price);
						//store_goods_item.flag = 1;
					}
					
					//this.flag = 1;
				}
				
			});
		}
	}
	
	
	//每个子商店选择效果
	this.chilSelect = function(obj){
		//console.log(obj);
		
		for(var i = 0 ; i < obj.length; ++i ){
			var radio_item = obj.get(i);
			
			radio_item.addEventListener('click',function(){
				var flag = $(this).attr("val");
				if(flag == 0){
					//console.log(this);
					setcolor(this,flag);
					calculation_price(this,0);//加
				}else{
					setcolor(this,this.flag);
					calculation_price(this,1);//减
				}
				
				
			});
			//console.log(radio_item);
		}
		
	}
	
	//通过子节点响应父节点    -----效果响应，只关注效果
	this.listenChil = function(){
		
		var warp = $(".wrap");
		var collect = warp.children(".collect");
		var index = 0 ;
		
		for(var i = 0 ;i < collect.length; ++i){
			
			var collections = collect.get(i);//获取每个商家
			var radio_items = $(collections).find(".radio");//获取每个商家下的radio
			
			//为每个商家的radio添加响应
			for(var j = 0 ; j < radio_items.length ; ++j ){
				radio_items.get(j).addEventListener('click',function(){
					/*index = index + 1;
					if(index == )*/
					
					var radio_par_collect = $(this).parents(".collect");
					var r_num = radio_par_collect.find(".collect_content");
					var radio_sum = radio_par_collect.find(".radio");//按钮总数
					var radio_length = radio_sum.length;
					var temp = 0 ;
					//遍历
					radio_sum.each(function(){
						var attr = $(this).attr("val");
						temp = temp + Number(attr);
					});
					if(temp == radio_length){
						var header = radio_par_collect.siblings(".header");
						var h_radio = header.find(".store_radio");
						setcolor(h_radio.get(0),0);
						//console.log(h_radio);
					}else if(temp == 0){
						console.log(radio_par_collect);
						var header = radio_par_collect.siblings(".header");
						var h_radio = header.find(".store_radio");
						setcolor(h_radio.get(0),1);
					}
					
					//console.log(radio_sum);
					
				});
			}
			
			
			
		}
		
	}
	
	
	
	//全选响应
	this.selectAll = function(){
		
		
		$(".allselect").get(0).addEventListener('click',function(){
			var flag = $(".allselect .sum_radio ").attr("val");
			//console.log(flag)
			if(flag == 0){
				
				setcolor($(".allselect .sum_radio ").get(0),flag);
				var store_radio = $(".store_radio");
				//情况总数
				//sum_good_price = 0.00;
				//把值全部设成0
				
				store_radio.each(function(){
					$(this).attr("val",0);
					$(this).click();
				});
				
			}else{
				//console.log(flag);
				setcolor(this,flag);
				
				var store_radio = $(".store_radio");
				store_radio.each(function(){
					$(this).click();
				});
			}
			
		});
		
		
		
	}
	
	
/**
 * 选中的效果
 * @param {this对象，非jq对象} obj
 * @param {标致位1或者2} flag
 */
function setcolor(obj,flag){
	
	if(flag == 0){
		//效果
		$(obj).children(".hui-icons").css("display","block");
		$(obj).css("background","red");
		$(obj).css("border-color","red");
		$(obj).attr("val","1");
	}else{
		$(obj).children(".hui-icons").css("display","none");
		$(obj).css("background","none");
		$(obj).css("border-color","#A9A9B0");
		$(obj).attr("val","0");
	}
}



/**
 * 计算总价
 * @param {非jq的obj} obj
 * @param {标致位1或者2} flag
 */
function calculation_price(obj,flag){
	//加
	if(flag == 0){
		var price_obj = $(obj).children(".goods_price");
		var goods_num = $(obj).children(".goods_nums");
		
		
		var price = price_obj.val();
		var num = goods_num.val();
		
		price = Number(price);
		num = Number(num);
		
		//console.log(obj);
		
		sum_good_price = sum_good_price + price * num;
		$(".cart_price").html("¥"+sum_good_price);
		$(obj).attr("m","1");

		
	}else{
		//减
		var price_obj = $(obj).children(".goods_price");
		var price = price_obj.val();
		price = parseFloat(price);
		
		var goods_num = $(obj).children(".goods_nums");
		var num = goods_num.val();
		num = Number(num);
		
		sum_good_price = ((sum_good_price*10000) - (price*10000 * num)  )/10000 ;
		$(".cart_price").html("¥"+sum_good_price);
		$(obj).attr("m","0");
	}
	
}


//监听每个按钮的响应
this.btnclick = function(){
	var radios = $(".radio");
	
	radios.each(function(){
		$(this).get(0).addEventListener('click',function(){
			
			var temp = 0;
			
			//判断是否全选了  或者  全不选
			radios.each(function(){
				
				var i = $(this).attr("val");
				i = Number(i);
				temp = temp + i;
				
			});
			
			if(temp == radios.length){
				//console.log("全选");
				setcolor($(".sum_radio"),0);
				
				
			}else{
				//console.log("不全选");
				setcolor($(".sum_radio"),1);
			}
			
		});
	});
	
}


//为每个编辑添加响应
this.edit = function(){
	
	var edit_btn = $(".operation");
	edit_btn.each(function(){
		
		$(this).get(0).addEventListener('click',function(){
			
			var _this = this;
			var $collection = $(_this).parents(".collection");
			
			//获取当前状态
			var status = $(this).attr("flag");
			//console.log(status);
			if(status == 'n'){
				
				//设置编辑样式
				$collection.find(".con").hide(1000);
				$collection.find(".edit_con").show(300);
				$(this).find("span").html("完成");
				$(this).attr("flag","y");
				var store_id = $(_this).attr("val");
			
			}else if(status == 'y'){
				
				//完成编辑样式
				//var edit_con = $collection.find(".edit_con");
				var goods = $collection.find(".collect");
				
				$collection.find(".edit_con").hide(1000);
				$collection.find(".con").show(300);
				$(this).find("span").html("编辑");
				$(this).attr("flag","n");
				var store_id = $(_this).attr("val");
				var data = {};
				//var nums_box = edit_con.find(".s_num");
				goods.each(function(){
					
					var good_id = $(this).attr("good_id");
					var nums_box = $(this).find(".s_num");
					var num = nums_box.val();
					num = Number(num);
					
					//更新数据
					var old_num = $(this).find("#h_num").val()//之前
					old_num = Number(old_num);
					var opt = 1;//默认为增加
					
					//判断是增加还是减少
					if(num != old_num){
						
						data[good_id]=num-old_num;
						$(this).find(".num").html("x"+num);
						$(this).find("#h_num").val(num)
						$(this).find(".goods_nums").val(num);
					}else{
						return ;
					}
					
				});
				
				
				//把obj解成json数据传过去
				var json_data = JSON.stringify(data);
				//console.log(json_data);
				
				var xhr = new XMLHttpRequest();
				xhr.open("GET",crud_url+"?data="+json_data,true);
				xhr.onload = function(e){
					
					var msg = this.responseText;
					if(msg == "err" ){
						hui.alert("更新失败");
					}
				}
				xhr.send();
				
			}
			
			
		});
		
		
		
	});
}


//为每个商品的增加或减少添加响应
this.add_subtract = function(){
	
	var number_box = $(".number-box");
	
	number_box.each(function(){
		
		var _this = this;//整个输入框对象
		
		var reduce_stop_flag = 1;
		var add_stop_flag = 1;
		
		$(this).find(".reduce").get(0).addEventListener('click',function(){
			add_stop_flag = 1;
			var price = $(_this).parents(".collect").find(".goods_price").val();//商品价格
			var old_num = $(_this).parents(".collect").find("#h_num").val();
			var now_num = $(_this).find(".s_num").val();
			var glob_price = Number(sum_good_price);
			
			var min_num = $(_this).attr("min");
			
			if(reduce_stop_flag == 0){
				hui.alert("亲,不能再少了");
				return ;
			}
			
			if(min_num == now_num){
				reduce_stop_flag = 0;
				
			}
			
			price = Number(price);
			
			
			if(glob_price == 0){
				
			}else{
				glob_price = glob_price - price;
				console.log(glob_price);
				sum_good_price = glob_price;
				
				$(".cart_price").html("¥"+sum_good_price);
			}
			
			
		});
		
		$(this).find(".add").get(0).addEventListener('click',function(){
			//console.log(this);
			reduce_stop_flag = 1;
			
			var price = $(_this).parents(".collect").find(".goods_price").val();//商品价格
			var now_num = $(_this).find(".s_num").val();//当前数量
			var glob_price = Number(sum_good_price);//全局总价
			var max_num = $(_this).attr("max");//购买的最大值
			
			if(add_stop_flag == 0){
				hui.alert("亲,商品没有了");
				return ;
			}
			
			if(max_num == now_num){
				add_stop_flag = 0;
			}
			
			
			price = Number(price);
			
			if(glob_price == 0){
				
			}else{
				glob_price = glob_price + price;
				console.log(glob_price);
				sum_good_price = glob_price;
				
				$(".cart_price").html("¥"+sum_good_price);
			}
			
			
		});
		
		
	})
	
}


this.del_btn = function(){
	
	
}


this.init = function(){
	
	var collect = $(".collect");
	collect.each(function(){
		var _this = this;
		var s_num = $(this).find(".s_num");
		var now_num = $(this).find("#h_num").val();
		s_num.val(now_num)
		//console.log(s_num.val());
		
		
		
		$(_this).find(".del_btn").each(function(){
			var del_this = this;
			del_this.addEventListener('click',function(){
				
				hui.confirm('确认要删除吗？', ['取消','确定'], function(){
					
					//发送删除请求
					var good_id = $(_this).attr("good_id");//要删除的商品id
					var xhr = new XMLHttpRequest();
					xhr.open("GET",del_url+"?good_id="+good_id,true);
					xhr.onload = function(e){
						
						var msg = this.responseText;
						console.log(msg);
						if(msg == "err" ){
							hui.alert("删除失败");
							return ;
						}else{
							
							//删除效果
							var brothers = $(_this).siblings(".collect");
							
							if(brothers.length <= 0){
								var collection = $(_this).parents(".collection");
								collection.hide(500);
								setTimeout(function(){
									collection.remove();
								},500);
							}else{
								$(_this).hide(500);
								setTimeout(function(){
									$(_this).remove();
								},500);
								
								
							}
						}
					}
					
					xhr.send();
						
					
						
						
			    });
				
				
				
				
				
				
			});
		});
		
		
		
	});
}

this.btn_commit = function(){
	
	var buy = new Cart_buy();
	
	var btn_commit =  $(".cartbottom_right").get(0);
	btn_commit.addEventListener('click',function(){
		
		
		var is = buy.commit();
		if(is){
			
			var data = buy.commodity();
			//console.log(data);
			
			var from = $("#datafrom");
			$("#dfrom").val(data);	//
			from.submit();
			
			/*
			var formData = new FormData();
			formData.append("data",data);
			var xhr = new XMLHttpRequest();
			xhr.open("POST",buyUrl,true);
			xhr.onload = function(e){
				console.log("返回   "+this.responseText);
			}
			xhr.send(formData);
			*/
			
		}else{
			
			hui.alert("商品选择不能为空");
			return false;
		}
		
	});
}
	
	
}

