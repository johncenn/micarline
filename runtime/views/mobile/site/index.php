<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title><?php echo $this->_siteConfig->name;?></title>
    <link type="image/x-icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" rel="icon">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="apple-touch-icon-precomposed" href="<?php echo $this->getWebSkinPath()."image/logo.gif";?>">
    <script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script> <script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/form/form.js"></script> <script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/micarpro/runtime/_systemjs/autovalidate/style.css" /> <script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/micarpro/runtime/_systemjs/artdialog/skins/aero.css" /> <script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
    <script src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
    <script src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
    <link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."style/style.css";?>">
</head>

<body>
    <!-- 顶部通栏 -->
    <header class="header_box">
        <div class="header">
            <?php if(IWeb::$app->getController()->getId() == 'site' && IWeb::$app->getController()->getAction()->getId() == 'index'){?>
            <div class="header_home"><i class="icon-home"></i></div>
            <?php }else{?>
            <div class="header_back" onclick="window.history.back();"><i class="icon-chevron-left"></i></div>
            <?php }?>
            <h1 id="page_title" class="page_title"><?php echo $this->_siteConfig->name;?></h1>
        
            <!-- <div class="header_so_btn" onclick="$('.header_search').toggle();"><i class="icon-search"></i></div> -->
            <div class="header_so_btn" onclick="jump_url()"><i class="icon-search"></i></div>
        
        </div>
    </header>
    <!-- 顶部搜索 -->
    <section class="header_search">
        <form method='get' action="<?php echo IUrl::creatUrl("/");?>">
            <input type='hidden' name='controller' value='site'>
            <input type='hidden' name='action' value='search_list'>
            <input class="keywords" type="text" name='word' autocomplete="off" placeholder="请输入关键词...">
            <input class="submit" type="submit" value="搜索">
        </form>
    </section>
    <!-- 引入内页 -->
    <section class="viewport">
        <style>
.viewport{
	top: .45rem;
}

.customer{
	width: 0.5rem;
	height: 0.5rem;
	position: fixed;
	top: 70%;
	right: 3%;
	z-index: 100;
	background: white;
}

.customer img{
	width: 0.5rem;
	height: 0.5rem;
}

</style>
<script src="<?php echo $this->getWebViewPath()."javascript/jquery.slider.js";?>"></script>
<?php
	
	//获取板块信息
	//echo "session is <br/>";
	//$type   = ISession::get('plate_type');
	//$type = $plate_type[0]['plate_id'];
	
	//$openId = ISession::get('wechat_openid');
	$openId = 'o7WLlwS6UmVJ2nPgYrqBf7eP7EFg';
	$oauth_user = new IModel('oauth_user');
	$wechatuser = $oauth_user->getObj("oauth_user_id = '$openId' ");
	$f_id = $wechatuser['user_id'];
	
	$user_model = new IModel('user');
	$user = $user_model->getObj("id = '$f_id' ");	
	ISession::set("wechatuser",$user);
	//echo "f_id is ".$user['username'];
?>

<div class="customer">
	<img alt="客服" src="<?php echo $this->getWebSkinPath()."image/customer.png";?>">
</div>

<!--幻灯片 开始-->
<div class="home_banner">
    <?php if($this->index_slide){?>
    <ul>
        <?php foreach($this->index_slide as $key => $item){?>
        <li>
            <a href="<?php echo isset($item['url'])?$item['url']:"";?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>"></a>
        </li>
        <?php }?>
    </ul>
    <?php }?>
</div>


<!-- 首页菜单 -->
<nav class="home_nav">
    <ul>
        <li class="map"><a href="<?php echo IUrl::creatUrl("site/sitemap");?>"><i class="icon-th-list"></i><span>全部分类</span></a></li>
        <li class="cart"><a href="<?php echo IUrl::creatUrl("simple/cart");?>"><i class="icon-shopping-cart"></i><span>购物车</span></a></li>
        <li class="groupon"><a href="<?php echo IUrl::creatUrl("site/groupon");?>"><i class="icon-legal"></i><span>今日团购</span></a></li>
        <li class="favorite"><a href="<?php echo IUrl::creatUrl("ucenter/favorite");?>"><i class="icon-star"></i><span>我的收藏</span></a></li>
    </ul>
    
    <ul style="margin-top: 0.2rem">
        <li class="map"><a  href="<?php echo IUrl::creatUrl("site/map_seach");?>"><i style="background: #3DCE3D;" class="icon-search"></i><span>地图搜索</span></a></li>
        <li class="cart"><a href="<?php echo IUrl::creatUrl("simple/cart");?>"><i class="icon-shopping-cart"></i><span>购物车</span></a></li>
        <li class="groupon"><a href="<?php echo IUrl::creatUrl("site/point_goods");?>"><i style="background: #CCCC1D;" class="icon-Tags"></i><span>积分商品</span></a></li>
        <li class="favorite"><a href="<?php echo IUrl::creatUrl("ucenter/favorite");?>"><i style="background: #0083EB;" class="icon-Gift"></i><span>自营商品</span></a></li>
    </ul>
    
</nav>
<?php echo Ad::show("首页顶部通栏100%*120(mobile)");?>
<!-- 首页推荐商品列表 -->
<h2 class="home_title"><i class="icon-gift"></i><strong>商品推荐</strong></h2>
<section class="home_goods">
    <ul>
        <?php foreach(Api::run('getCommendRecom') as $key => $item){?>
       
        <li>
            <a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>">
                <img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/300/h/300");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>">
                <span><?php echo isset($item['name'])?$item['name']:"";?></span>
            </a>
        </li>
        <?php }?>
    </ul>
</section>

<?php echo Ad::show("首页中部通栏100%*200(mobile)");?>

<span class="recSeller">推荐商家</span>

<div class="line"></div>

<div class="carMap">

</div>


 <!--定位JS-->
<script type="text/javascript" src="https://3gimg.qq.com/lightmap/components/geolocation/geolocation.min.js"></script>
<!--计算距离JS-->
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&libraries=drawing,geometry,autocomplete,convertor"></script>


<script>

$(function() {
	console.log("ininin");
	var arr;
	//向腾讯地图发请求
	
	<?php 
		$map_temp = ISession::get('map');		
	?>
	<?php if(!$map_temp){?>
	$.ajax({
		url:"index.php?controller=getStore&action=get",
		type:'get',
		dataType: 'json',
        
       	success:function(res){
       		arr = res;
       		
       		var key = "GWEBZ-TN635-YIEI5-QV4LJ-RXJ2H-7KFXC";
        	var referer = "testMap";
        	var geolocation = new qq.maps.Geolocation(key,referer);
            var options = {timeout: 8000};

    		//定位
    		geolocation.getLocation(showPosition, showErr, options);
    		
    		
    		function showPosition(res){
    			
    			console.log('定位成功');
    			//定位成功，封装当前的位置
    			var lat = res.lat;
    			var lng = res.lng;
    			var d = new qq.maps.LatLng(lat,lng);
    			
    			
    			
    			
    			//循环把坐标拿出来
    			for(var key in arr){
    				
    				var u_lat = arr[key]['gps'].split(',')[0];
    	       		var u_lng = arr[key]['gps'].split(',')[1];
    	       		var m = new qq.maps.LatLng(u_lat,u_lng);
    	       		
    	       		//相差距离
    	       		var t = qq.maps.geometry.spherical.computeDistanceBetween(m, d);
    	       		arr[key]['length'] = t;
    	       		
    			}
    			
    			//排好序的结果
    			var temp = sort(arr);
    			
    			//发送请求去保存至session
    			var xhr = new XMLHttpRequest();
    			var save_map_url = "index.php?controller=getStore&action=save_temp_map";
    			xhr.open("GET",save_map_url,true);
    			xhr.onload = function(e){
   					console.log("返回   "+this.responseText);
   				}
   				xhr.send();
    			addCarMap(temp,'carMap');
    			
    			
    			
    			
    			
    		}
    		
    		function showErr(){
    			console.log('定位失败');
    		}
    		
       	},
       	
       	error:function(res){
       		console.log('err');
       	}
		
	});
	<?php }else{?>
	console.log("temp is ");
	console.log($("#temp_map").val());
	<?php }?>
	
	//排序地图方法
	function sort(result){
		for (var i = 0;i<result.length;i++) {
			for (var j = 0; j < result.length-i-1; j++) {
				if (result[j]['length']>result[j+1]['length']) {
						
						var temp = result[j];
						result[j] = result[j+1];
						result[j+1] = temp;
					}
				}
		}
		return result;
		
	}
	
	//显示
	function addCarMap(res,posiable){
		console.log(res);
		console.log("距离");
		var carNum = res.length;
		if(carNum > 10){
			carNum = 10;
		}
		var parent = $(".carMap");
		//var odiv = $("<div class=store>1234</div>");
		//parent.appendTo(odiv);
		//console.log(odiv);
		for(var i = 0; i < carNum ; i++){
			
			var odiv = $("<div class=store></div>");
			var a = $('<a href="site/home/id/'+res[i].id+'"></a>');
			
			
			var store_img = $("<div class=store_img></div>");
			var header = $("<img src=/"+res[i].store_label+"></img>");
			
			
			var l = res[i].length;
			var tral = 0;
			if(l > 1000){
				tral = parseInt(l/1000)+" km";
			}else{
				tral = parseInt(l)+" m";
			}
			
			var desc = $("<div class=store_desc></div>");
			var title = $("<h3>"+res[i].store_name+"</h3>");
			var sell = $("<span id=sell>主营："+res[i].store_zy+"</span>");
			var address = res[i][0];
			var temp_address = " ";
			 for(var aaaa in address){
				
				temp_address = temp_address + address[aaaa]+" ";
			} 
			 
			 //console.log(temp_address);//res[i].address
			
			var address = $("<span id=address>地址："+temp_address+" "+res[i].address+"</span>");
			var cell = $("<span id=cell>电话： "+res[i].mobile+"</span>");
			var spen = $("<font>"+tral+"(直线距离)</font>");
			
			//店铺介绍
			title.appendTo(desc);
			sell.appendTo(desc);
			address.appendTo(desc);
			cell.appendTo(desc);
			spen.appendTo(desc);
			
			//头像
			header.appendTo(store_img);
			
			//超链接a
			store_img.appendTo(a);
			desc.appendTo(a);
			
			//插入到最后一个的父类里面
			a.appendTo(odiv);
			
			odiv.appendTo(parent);
			
			console.log("inin");
		}
		
		//$()
	}
	
	
	
	
    // 设置首页导航为当前
    $(".nav_home").addClass('on');
    // 设置焦点图
    $(".home_banner").MobileSlider({
        width: 720,
        height: 360
    });
    
    
    
    $(".customer").click(function(){
    	console.log("okokool");
    	window.location.href = ex+"site/userchatlist";
    });
	
	
	 //链接socket
    var url='ws://120.25.77.104:8000';
    var so= new WebSocket(url);
  	//连接sock初始化
	so.onopen=function(){
		if(so.readyState==1){
			so.send('type=init&send_id=<?php echo isset($user['id'])?$user['id']:"";?>&name=<?php echo isset($user['username'])?$user['username']:"";?>');
			console.log("已连接");
		}
	}
    
	//接收sock返回来的数据
	so.onmessage=function(msg){
		eval('var da='+msg.data);
		var type = da.type;
		
		if(type =='talk' || type =='talk_img' || type =='talk_bimg' ){
			$(".customer img").attr("src","<?php echo $this->getWebSkinPath()."image/customer_b.png";?>");
		}
	}
    
    
})
</script>

    </section>
    <!-- 会员登陆与否显示不同内容 -->
    <?php if($this->user){?>
    <?php }else{?>
    <!-- 
    <section class="footer_login">
        <a class="login" href="<?php echo IUrl::creatUrl("simple/login");?>">登录</a>
        <a class="reg" href="<?php echo IUrl::creatUrl("simple/reg");?>">注册</a>
    </section> -->
    <?php }?>
    <!--底部菜单-->
    <nav class="footer_nav">
        <ul>
            <li class="nav_home"><a href="<?php echo IUrl::creatUrl("site");?>"><i class="icon-home"></i><span>首页</span></a></li>
            <li class="nav_cart"><a href="<?php echo IUrl::creatUrl("simple/cart");?>"><i class="icon-shopping-cart"></i><span>购物车</span></a></li>
            <li class="nav_user"><a href="<?php echo IUrl::creatUrl("/ucenter/index");?>"><i class="icon-user"></i><span>个人中心</span></a></li>
            <li class="nav_map"><a href="<?php echo IUrl::creatUrl("site/sitemap");?>"><i class="icon-reorder"></i><span>分类</span></a></li>
        </ul>
    </nav>
    <script src='<?php echo $this->getWebViewPath()."javascript/mobile.js";?>'></script>
</body>

</html>

<script type="text/javascript">

var ex = "http://"+window.location.host+"/";
function jump_url(){
	
	//window.location.href = ex+"site/goseachpage";
	window.location.href = ex+"git/micarshop/site/goseachpage";
	
}

</script>

