//为每个页面加入 微信 js sdk
var scr = document.createElement('script');
scr.setAttribute("type","text/javascript");
scr.setAttribute("src","http://res.wx.qq.com/open/js/jweixin-1.0.0.js");
var ohead=document.getElementsByTagName("head")[0];
ohead.appendChild(scr);


//商品移除购物车
function removeCart(goods_id,type)
{
	var goods_id = parseInt(goods_id);
	$.getJSON(creatUrl("simple/removeCart"),{goods_id:goods_id,type:type},function(content){
		if(content.isError == false)
		{
			$('[name="mycart_count"]').html(content.data['count']);
			$('[name="mycart_sum"]').html(content.data['sum']);
		}
		else
		{
			alert(content.message);
		}
	});
}

//添加收藏夹
function favorite_add_ajax(goods_id,obj)
{
	$.getJSON(creatUrl("simple/favorite_add"),{"goods_id":goods_id,"random":Math.random()},function(content){
		//alert(content.message);
		//console.log(content.message);
		hui.alert(content.message,'好的', function(){
			$(".collect_span").css("color","red");
		});
		
	});
}

//购物车展示
function showCart()
{
	$.getJSON(creatUrl("simple/showCart"),{sign:Math.random()},function(content)
	{
		var cartTemplate = template.render('cartTemplete',{'goodsData':content.data,'goodsCount':content.count,'goodsSum':content.sum});
		$('#div_mycart').html(cartTemplate);
		$('#div_mycart').show();
	});
}


//dom载入成功后开始操作
jQuery(function()
{
	//购物车数量加载
	if($('[name="mycart_count"]').length > 0)
	{
		$.getJSON(creatUrl("simple/showCart"),{sign:Math.random()},function(content)
		{
			$('[name="mycart_count"]').html(content.count);
		});

		//购物车div层显示和隐藏切换
		var mycartLateCall = new lateCall(200,function(){showCart();});
		$('[name="mycart"]').hover(
			function(){
				mycartLateCall.start();
			},
			function(){
				mycartLateCall.stop();
				$('#div_mycart').hide('slow');
			}
		);
	}
	
	//获取主机域名
	//var miUrl = "http://"+window.location.host+"/git/micarshop/wechar/getJSSDK";
	var testUrl = "http://"+window.location.host+"/git/micarshop/wechar/getJSSDK";
	//var testUrl = "http://"+window.location.host+"/wechar/getJSSDK";
	
	//window.location.href = testUrl;

	//发送验证的当前url
	var yzUrl = window.location.href;
	//把所有&替换成|
	yzUrl = yzUrl.replace(/\&/g,"|");
	
	
	
	//console.log(testUrl);
	var xhr = new XMLHttpRequest();
	xhr.open('GET',testUrl+"?url="+yzUrl,true);
	xhr.onload = function(){
		var msg = this.responseText;
		console.log(msg);
		//window.location.href = testUrl+"?url="+yzUrl;
		//return ;
		eval('var da='+msg);
		console.log(da);
		
		
		wx.config({
			debug: false, 
			appId: da.appId,
			timestamp: da.timestamp,
			nonceStr: da.nonceStr,
			signature: da.signature,
			jsApiList: [
			  // 所有要调用的 API 都要加到这个列表中
			  "onMenuShareTimeline",
			  "onMenuShareAppMessage",
			  "onMenuShareQQ"
			]
		});
		
		
		wx.ready(function () {
			
			var utitle = document.title;
			//var img = document.getElementsByTagName('img')[0];
			//if(!img){
			//	img_url = "http://www.micarline.net/upload/car.jpg";
			//}else{
				//console.log(img);
			//	console.log("...........");
			 //	var img_url = img.src;
			 //	console.log(img_url);
			//}
			
			var img_url = "http://www.micarline.net/upload/car.jpg";
			
			// 在这里调用 API
			wx.onMenuShareTimeline({
				title: "自定义分享测试成功", // 分享标题
				link: da.url+"?yaoqing="+da.mid, // 分享链接
				imgUrl: img_url, // 分享图标
				success: function () { 
					// 用户确认分享后执行的回调函数
					//alert("分享成功");
				},
				cancel: function () { 
					// 用户取消分享后执行的回调函数
					//alert("取消分享");
				}
			});
			
			
			//分享到好友
			wx.onMenuShareAppMessage({
				title: "自定义分享测试成功", // 分享标题
				link: da.url+"?yaoqing="+da.mid, // 分享链接
				imgUrl: img_url, // 分享图标
				success: function () { 
					// 用户确认分享后执行的回调函数
					//alert("分享成功");
				},
				cancel: function () { 
					// 用户取消分享后执行的回调函数
					//alert("取消分享");
				}
			});
		})
	}
	
	xhr.send();
	
});

//[ajax]加入购物车
function joinCart_ajax(id,type)
{
	$.getJSON(creatUrl("simple/joinCart"),{"goods_id":id,"type":type,"random":Math.random()},function(content){
		if(content.isError == false)
		{
			var count = parseInt($('[name="mycart_count"]').html()) + 1;
			$('[name="mycart_count"]').html(count);
			alert(content.message);
		}
		else
		{
			alert(content.message);
		}
	});
}

//列表页加入购物车统一接口
function joinCart_list(id)
{
	$.getJSON(creatUrl("/simple/getProducts"),{"id":id},function(content)
	{
		if(!content || content.length == 0)
		{
			joinCart_ajax(id,'goods');
		}
		else
		{
			artDialog.open(creatUrl("/block/goods_list/goods_id/"+id+"/type/radio/is_products/1"),{
				id:'selectProduct',
				title:'选择货品到购物车',
				okVal:'加入购物车',
				ok:function(iframeWin, topWin)
				{
					var goodsList = $(iframeWin.document).find('input[name="id[]"]:checked');

					//添加选中的商品
					if(goodsList.length == 0)
					{
						alert('请选择要加入购物车的商品');
						return false;
					}
					var temp = $.parseJSON(goodsList.attr('data'));

					//执行处理回调
					joinCart_ajax(temp.product_id,'product');
					return true;
				}
			})
		}
	});
}