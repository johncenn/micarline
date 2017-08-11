<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<title><?php echo $this->_siteConfig->name;?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/micarpro/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/micarpro/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/micarpro/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<!--[if IE]><script src="<?php echo $this->getWebViewPath()."javascript/html5.js";?>"></script><![endif]-->
	<script src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
	<script src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
	<script type="text/javascript" src="<?php echo $this->getWebViewPath()."javascript/jquery-ui.min.js";?>"></script>
	<script type="text/javascript" src="<?php echo $this->getWebViewPath()."javascript/chat.js";?>"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.0.0.js"></script>
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."style/style.css";?>">
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."style/chat.css";?>">
</head>
<body>
<?php $ava_img?>

<!-- 右边竖条  s-->
<div class="right">
	<span>在线联系</span>
	
	<?php if($this->user){?>
		<?php foreach($this->user as $k => $v){?>
			key:<?php echo isset($k)?$k:"";?>---value:<?php echo isset($v)?$v:"";?><br/>
		<?php }?>
		
		<?php $uid = $this->user['user_id']?>
		<?php $query = new IQuery("user");$query->where = "id = $uid";$items = $query->find(); foreach($items as $key => $first){?> 
			<?php $ava_img=$first['head_ico']?>
		<?php }?>
		<input class="self_id" type="hidden" value="<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>"/>
		<input class="self_name" type="hidden" value="<?php echo isset($this->user['username'])?$this->user['username']:"";?>"/>
		<div class="message" id="showFriend" >
			<a href="javascript:"></a>
		</div>
		<?php }else{?>
		<input class="self_id" type="hidden" value=""/>
		<input class="self_name" type="hidden" value=""/>
		<div class="message" id="login">
			<a href="<?php echo IUrl::creatUrl("/simple/login");?>"></a>
		</div>	
	<?php }?>
		
	<script type="text/javascript">
		var user_ava = '<?php echo isset($this->user['head_ico'])?$this->user['head_ico']:"";?>';
	</script>
	
</div>

<!-- 右边竖条  e-->

<?php if($this->user){?>
<!-- 聊天窗口我好友列表   s -->
<div class="chat_list" >
	<div class="chat_top">
		<h1>
			<i></i>
			联系人
		</h1>
		<span class="close"></span>		
	</div>
	<?php $f_uid?> <!-- 对方的id -->
	<?php $nf_uid?>
	<!-- 好友列表 s -->
	<div class="chat_user">
		<dl class="chat_user_friend">
			<dt>
				我的好友
				<span class="show"></span>
			</dt>
			<!-- 查找好友表 -->
			<?php $query = new IQuery("friends");$query->where = "(f_id = {$this->user['user_id']} or t_id = {$this->user['user_id']}) and state = 1";$items = $query->find(); foreach($items as $key => $item){?>
				
				<?php if($item['f_id'] == $this->user['user_id']){?>
					<?php $f_uid = $item['t_id']?>
				<?php }else{?>
					<?php $f_uid = $item['f_id']?>	
				<?php }?>	
				
			<dd title="你好" from_id = "<?php echo isset($f_uid)?$f_uid:"";?>">
				<span class="user_avatar">
						 <?php $query = new IQuery("user");$query->where = "id = $f_uid";$items = $query->find(); foreach($items as $key => $first){?> 
						 	<?php if($first['head_ico'] == ""){?>
						 		<img alt="" src="<?php echo IUrl::creatUrl("upload/car.jpg");?>"> 
								<?php $ava_img='upload/car.jpg'?>
						 		<?php }else{?>
						 		<img alt="" src="<?php echo IUrl::creatUrl("".$first['head_ico']."");?>"> 
								<?php $ava_img=$first['head_ico']?>
						 	<?php }?>
							<!-- <?php $ava_img1=$first['head_ico1']?> -->
						<?php }?>
					<i class="offline" f_id="<?php echo isset($f_uid)?$f_uid:"";?>"></i>
				</span>
				
				<?php if($item['f_id'] == $this->user['user_id']){?>
					<h5><?php echo isset($item['t_name'])?$item['t_name']:"";?></h5><!-- <?php echo isset($item['t_id'])?$item['t_id']:"";?> -->
					<a href="javascript:"  onclick="talk(<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>,<?php echo isset($item['t_id'])?$item['t_id']:"";?>,<?php echo isset($item['id'])?$item['id']:"";?>,'<?php echo IUrl::creatUrl("".$ava_img."");?>','<?php echo isset($item['t_name'])?$item['t_name']:"";?>')"></a>
					<?php }else{?>
					<h5><?php echo isset($item['f_name'])?$item['f_name']:"";?></h5>
					<a href="javascript:"  onclick="talk(<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>,<?php echo isset($item['f_id'])?$item['f_id']:"";?>,<?php echo isset($item['id'])?$item['id']:"";?>,'<?php echo IUrl::creatUrl("".$ava_img."");?>','<?php echo isset($item['f_name'])?$item['f_name']:"";?>') "></a>
				<?php }?> 
				
			</dd>
			<?php }?>
		</dl>
		
		<dl class="chat_user_recent">
			<dt>
				最近联系人
				<span class="show"></span>
			</dt>
			
			<?php $l_uid?>
			<!-- 查找临时的人 -->
	<?php $query = new IQuery("friends");$query->where = "(f_id = {$this->user['user_id']} or t_id = {$this->user['user_id']}) and state = 0";$items = $query->find(); foreach($items as $key => $item){?>
			
			<?php if($item['f_id'] == $this->user['user_id']){?>
				<?php $l_uid = $item['t_id']?>
			<?php }else{?>
				<?php $l_uid = $item['f_id']?>
			<?php }?>
				
			<dd title="你好   <?php echo isset($l_uid)?$l_uid:"";?>" from_id = "<?php echo isset($l_uid)?$l_uid:"";?>">
				<span class="user_avatar">
					
						<?php $query = new IQuery("user");$query->where = "id = $l_uid";$items = $query->find(); foreach($items as $key => $first){?> 
						 	<?php if($first['head_ico'] == ""){?>
						 		<img alt="" src="<?php echo IUrl::creatUrl("upload/car.jpg");?>"> 
								<?php $ava_img='upload/car.jpg'?>
						 		<?php }else{?>
						 		<img alt="" src="<?php echo IUrl::creatUrl("".$first['head_ico']."");?>"> 
								<?php $ava_img=$first['head_ico']?>
						 	<?php }?>
							<!-- <?php $ava_img1=$first['head_ico1']?> -->
						<?php }?>
						<i class="offline" f_id="<?php echo isset($l_uid)?$l_uid:"";?>"></i>
				</span>
					<?php if($item['f_id'] == $this->user['user_id']){?>
					<h5><?php echo isset($item['t_name'])?$item['t_name']:"";?></h5>
					<a href="javascript:"  onclick="talk(<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>,<?php echo isset($item['t_id'])?$item['t_id']:"";?>,<?php echo isset($item['id'])?$item['id']:"";?>,'<?php echo isset($ava_img)?$ava_img:"";?>','<?php echo isset($item['t_name'])?$item['t_name']:"";?>')"></a>
					<?php }else{?>
					<h5><?php echo isset($item['f_name'])?$item['f_name']:"";?></h5>
					<a href="javascript:"  onclick="talk(<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>,<?php echo isset($item['f_id'])?$item['f_id']:"";?>,<?php echo isset($item['id'])?$item['id']:"";?>,'<?php echo isset($ava_img)?$ava_img:"";?>','<?php echo isset($item['f_name'])?$item['f_name']:"";?>')"></a>
				<?php }?>
			</dd>
			<?php }?>
		</dl>
	</div>
	
</div>
<?php }?>
<!-- 聊天窗口我好友列表 e -->


<?php if($this->user){?>
<!-- 聊天窗口内容接收与发送 s msg-dialog 1-->
<?php $i=1?>	
<?php $to_Id?>
<?php $to_uname?>
<?php $ava?>
<?php $query = new IQuery("friends");$query->where = "f_id = {$this->user['user_id']} or t_id = {$this->user['user_id']}";$items = $query->find(); foreach($items as $key => $item){?>
		<?php if($item['f_id'] == $this->user['user_id']){?>
			<?php $to_Id = $item['t_id']?>
			<?php }else{?>
			<?php $to_Id = $item['f_id']?>
		<?php }?>
		
		<?php $query = new IQuery("user");$query->where = "id = $to_Id";$items = $query->find(); foreach($items as $key => $first){?>
			<?php $to_uname = $first['username']?>
			<?php if($first['head_ico'] == ""){?>
				
				<?php $ava='upload/car.jpg'?>
			<?php }else{?>
				<?php $ava = $first['head_ico']?>
			<?php }?>
		<?php }?>
		
<div class="msg-dialog isshow<?php echo isset($item['id'])?$item['id']:"";?>" id="dialog" draggable="false" from_id="">
	<div id="dialog_header" class="d_h" value="<?php echo isset($i)?$i:"";?>" draggable="true">
	<?php if($ava == ""){?>
		<img class="to_user_img" alt="" src="<?php echo IUrl::creatUrl("upload/car.jpg");?>"> 
		<?php }else{?>
		<img class="to_user_img" alt="" src="<?php echo IUrl::creatUrl("".$ava."");?>"> 
	<?php }?>
		<span><?php echo isset($to_uname)?$to_uname:"";?></span>
		<div class="close_big">
			<span>×</span>
		</div>
	</div>
	
	
	<input class="to" type="hidden" value="<?php echo isset($to_Name)?$to_Name:"";?>"/>
	
	<!-- 聊天记录 -->
	<div class="dialog-body">
		<!-- 用户的聊天内容 -->
		<div class="msg_list">
			<div class="chat_con"> 
				<!-- 发送者信息 -->
			<!--	<div class="to_user">
					<span class="user-avatar">
						<img alt="" src="<?php echo IUrl::creatUrl("")."".$user_ico."";?>">
					</span>-->
					<!-- 发送给用户的内容 s-->
					<!--<dl>
						<dt>2017-05-16 10:49:19</dt>
						<dd>kdf;msdlfmmsdlfmsdklfmsdklmsdlfmmsdlfmsdklfmsdklgsdklfmsdklggsdklfmsdklg</dd>
						<dd class="arrow"></dd>
					</dl>
				</div>-->
				<!-- 发送给用户的内容 e-->
				
				<!-- 接收用户的内容  s-->
				<!-- <div class="from_user">
					<span class="user-avatar">
						<img alt="" src="<?php echo $this->getWebSkinPath()."image/logo.png";?>">
					</span> -->
					<!-- 发送内容 -->
					<!--<dl>
						<dt>2017-05-16 10:49:19</dt>
						<dd>kdf;msdlfmmsklmsdlfmmsklfklfmsdklgmsdlfmmsklfklfmsdklgfklfmsdklg</dd>
						<dd class="arrow"></dd>
					</dl>
				</div> -->
				<!-- 接收用户的内容 e-->
				
			</div>
		</div>
		<div class="msg-input-box">
			<!-- 表情栏 s-->
			
			<!-- 表情 -->
			<div class="s_img">
				<?php for($num = 1 ; $num<=77 ; $num = $num+1){?>
					<div  class="event_one">
						<img alt="<?php echo isset($num)?$num:"";?>.gif" src="<?php echo IUrl::creatUrl("upload/talkimg/".$num."");?>.gif">
					</div>
				<?php }?>
			</div>
			
			<div class="msg-input-title">
				<span class="chat_show">
					<img alt="" src="<?php echo $this->getWebSkinPath()."image/show.png";?>">
				</span>
				
				<span class="chat_phpne">
					
					<img alt="" src="<?php echo $this->getWebSkinPath()."image/photo.png";?>" class="photo_upload">
						<input type="file" id="upp" name="photo" style="display: none" onchange="select_photo()" />
					</img>
				</span>
				
				<span class="message">
					消息记录
					<img alt="" src="<?php echo $this->getWebSkinPath()."image/down.png";?>">
				</span>
			</div>
			<!-- 表情栏 e-->
			
			<!-- 内容输入框 s-->
			<textarea class="send_message"></textarea>
			<!-- 内容输入框 e-->
			<span class="ad"><a href="#">广告词可以在这里加链接</a></span>
			<span class="send_btn" ><a href="javascript:">发送消息</a></span>
						
		</div>
	</div>
	
	<div class="dialog_chat_right" >
		<!-- 消息记录 -->
	</div>
	
</div>
<?php $i=$i+1?>	
<?php }?>
<?php }?>
<!-- 聊天窗口内容接收与发送 s msg-dialog-->


<!--

模版使用字体图标为优化过的 awesome 3.0 图标字体库

使用帮助见:http://www.bootcss.com/p/font-awesome/

 -->
<div class="header_top">
	<div class="web">
		<div class="welcome">
			欢迎您来到 <?php echo $this->_siteConfig->name;?>！
			<?php if($this->user){?>
				<a href="<?php echo IUrl::creatUrl("/ucenter/index");?>">个人中心</a>
				<a href="<?php echo IUrl::creatUrl("/simple/logout");?>">退出</a>
			<?php }else{?>
				<a href="<?php echo IUrl::creatUrl("/simple/login");?>">登录</a>
				<a class="reg" href="<?php echo IUrl::creatUrl("/simple/reg");?>">免费注册</a>
			<?php }?>
		</div>
		<div class="header_cart" name="mycart">
			<a href="<?php echo IUrl::creatUrl("/simple/cart");?>" class="go_cart">
				<i class="icon-shopping-cart"></i>
				购物车（<em class="count" name="mycart_count"]>0</em>）
			</a>
			<div class="cart_simple" id="div_mycart"></div>
		</div>
		<!--购物车模板 开始-->
		<script type='text/html' id='cartTemplete'>
		<div class='cart_panel'>
			<ul class='cart_list'>
				<%for(var item in goodsData){%>
				<%var data = goodsData[item]%>
				<li id="site_cart_dd_<%=item%>">
					<em>共<%=data['count']%>件</em>
					<a target="_blank" href="<?php echo IUrl::creatUrl("/site/products/id/<%=data['goods_id']%>");?>">
						<img src="<?php echo IUrl::creatUrl("")."<%=data['img']%>";?>">
						<h5><%=data['name']%></h5>
					</a>
					<span>￥ <%=data['sell_price']%></span>
					<del onclick="removeCart('<%=data['id']%>','<%=data['type']%>');$('#site_cart_dd_<%=item%>').hide('slow');">删除</del>
				</li>
				<%}%>
				<%if(goodsCount){%>
				<div class="cart_total">
					<p>共<span name="mycart_count"><%=goodsCount%></span>件商品</p>
					<p>商品总额：<em name="mycart_sum">￥<%=goodsSum%></em></p>
					<a href="<?php echo IUrl::creatUrl("simple/cart");?>">去购物车结算</a>
				</div>
				<%}else{%>
				<div class='cart_no'>购物车空空如也~</div>
				<%}%>
			</ul>
		</div>
		</script>
		<ul class="top_tool">
			<li><a href="<?php echo IUrl::creatUrl("ucenter/index");?>">个人中心</a></li>
			<?php if($this->user){?>
			<li><a href="<?php echo IUrl::creatUrl("/simple/seller");?>">申请开店</a></li>
			<?php }?>
			<li><a href="<?php echo IUrl::creatUrl("/seller/index");?>">我是商家</a></li>
			<li><a href="<?php echo IUrl::creatUrl("/site/help_list");?>">使用帮助</a></li>
		</ul>
	</div>
</div>

<?php
	
	//获取板块信息
	//echo "session is <br/>";
	//$type   = ISession::get('plate_type');
	//$type = $plate_type[0]['plate_id'];
	
?>



<header class="header web">
	<nav class="header_nav">
		<div class="goods_nav">
			<h1 class="logo">
				<!-- 这里的LOGO图片会自动靠左居中.因此只需要制作一个透明的LOGO图片即可 LOGO最大尺寸 200*90 -->
				<a href="<?php echo IUrl::creatUrl("");?>">
					<img src="<?php if($this->_siteConfig->logo){?><?php echo IUrl::creatUrl("")."".$this->_siteConfig->logo."";?><?php }else{?><?php echo $this->getWebSkinPath()."image/logo.png";?><?php }?>">
				</a>
			</h1>
			<ul class="cat_list none">
				<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
				
							
				<li>
					<h3><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>"><?php echo isset($first['name'])?$first['name']:"";?></a></h3>
					<div class="cat_more">
						<ul>
							<?php foreach(Api::run('getCategoryExtendList',array('#categroy_id#',$first['id']),24) as $key => $item){?>
							<li>
								<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" target="_blank" title="<?php echo isset($item['name'])?$item['name']:"";?>">
									<img class="img-lazyload" src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/40/h/40");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>">
									<strong><?php echo isset($item['name'])?$item['name']:"";?></strong>
									<em>选购</em>
								</a>
							</li>
							<?php }?>
						</ul>
					</div>
				</li>
				
				<?php }?>
			</ul>
		</div>
		<ul class="site_nav">
			<li><a href="<?php echo IUrl::creatUrl("/site/index");?>">首页</a></li>
			<?php foreach(Api::run('getGuideList') as $key => $item){?>
			<li><a href="<?php echo IUrl::creatUrl("".$item['link']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></li>
			<?php }?>
		</ul>
	</nav>
	
	<div class="tran_seach">切换</div>
	<div class="header_search goods">
		
		<form method='get' action='<?php echo IUrl::creatUrl("/");?>'>
			<input type='hidden' name='controller' value='site'>
			<input type='hidden' name='action' value='search_list'>
			<div class="search_box">
				<input class="input_keywords" type="text" name='word' autocomplete="off" placeholder="输入关键词">
				<label class="input_submits">
					<input type="submit" value="">
					<i class="icon-search"></i>
				</label>
			</div>
		</form>
		<div class="hot_words">
			<?php foreach(Api::run('getKeywordList',2) as $key => $item){?>
			<?php $tmpWord = urlencode($item['word']);?>
			<a href="<?php echo IUrl::creatUrl("/site/search_list/word/".$tmpWord."");?>"><?php echo isset($item['word'])?$item['word']:"";?></a>
			<?php }?>
		</div>
	</div>
	
	<!-- 店铺搜索 -->
	<div class="header_search store">
		
		<form method='get' action='<?php echo IUrl::creatUrl("/site/store_seach");?>'>
			<input type='hidden' name='controller' value='site'>
			<input type='hidden' name='action' value='search_list'>
			<div class="search_box">
				<input class="input_keywords" type="text" name='word' autocomplete="off" placeholder="店铺搜索">
				<label class="input_submits">
					<input type="submit" value="">
					<i class="icon-search"></i>
				</label>
			</div>
		</form>
		<div class="hot_words">
			<!-- 
			<?php foreach(Api::run('getKeywordList',2) as $key => $item){?>
			<?php $tmpWord = urlencode($item['word']);?>
			<a href="<?php echo IUrl::creatUrl("/site/search_list/word/".$tmpWord."");?>"><?php echo isset($item['word'])?$item['word']:"";?></a>
			<?php }?> -->
			
			
			<a href="<?php echo IUrl::creatUrl("/site/store_seach?controller=site&action=search_list&word=米喀");?>">米喀</a>
			<a href="<?php echo IUrl::creatUrl("/site/store_seach?controller=site&action=search_list&word=香车");?>">香车</a>
		</div>
	</div>

</header>

<script type="text/javascript">

	$(".tran_seach").toggle(function(){
		$(".goods").hide();
		$(".store").show();
	},function(){
		$(".store").hide();
		$(".goods").show();
	});

			
		
</script>

<!--主要模板内容 开始-->
<!-- 焦点图和选项卡插件 -->
<script src="<?php echo $this->getWebViewPath()."javascript/FengFocus.js";?>"></script>
<script src="<?php echo $this->getWebViewPath()."javascript/FengTab.js";?>"></script>
<script src="<?php echo $this->getWebViewPath()."javascript/jquery.marquee.js";?>"></script>

<?php
	//$type   = ISession::get('plate_type');
	//$type = $plate_type[0]['plate_id'];		//int 
	//$type = 2;
?>


<section id="home_fouse" class="home_fouse">
	<?php if($this->index_slide){?>
	<ul>
		<?php foreach($this->index_slide as $key => $item){?>
		<li><a href="<?php echo IUrl::creatUrl("".$item['url']."");?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."");?>"></a></li>
		<?php }?>
	</ul>
	<?php }?>
</section>

<section class="home_focus_show">
	<div class="promise">
		<ul>
			<li><i class="icon-truck"></i><span>货到付款</span></li>
			<li><i class="icon-star"></i><span>星级服务</span></li>
			<a href="<?php echo IUrl::creatUrl("/site/points");?>"><li><i class="icon-tags"></i><span>积分商品</span></li></a>
			<li><i class="icon-time"></i><span>急速送货</span></li>
			<li><i class="icon-umbrella"></i><span>安全保证</span></li>
			<li><i class="icon-wrench"></i><span>售后保证</span></li>
		</ul>
	</div>
	<div class="show"><?php echo Ad::show("首页焦点图下广告1_305*160(xiaomi)");?></div>
	<div class="show"><?php echo Ad::show("首页焦点图下广告2_305*160(xiaomi)");?></div>
	<div class="show"><?php echo Ad::show("首页焦点图下广告3_305*160(xiaomi)");?></div>
</section>

<section class="home_rec">
	<header>
		<h3>推荐商品</h3>
		<div class="control">
			<i id="home_rec_left" class="icon-angle-left"></i>
			<i id="home_rec_right" class="icon-angle-right"></i>
		</div>
	</header>
	<div id="home_rec" class="con">
		<ul>
			<?php foreach(Api::run('getCommendRecom',10) as $key => $item){?>
			<!--  -->
			<li>
				<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>">
					<img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/170/h/170");?>">
					<h4><?php echo isset($item['name'])?$item['name']:"";?></h4>
					<del>￥<?php echo isset($item['market_price'])?$item['market_price']:"";?></del>
					<em>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></em>
				</a>
			</li>
			<?php }?>
		</ul>
	</div>
</section>


<!-- 开始循环楼层 -->
<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>



<section class="home_floor">
	<header class="floor_head">
		<h2><?php echo isset($first['name'])?$first['name']:"";?></h2>
		<nav class="floor_nav">
			<ul>
				<?php foreach(Api::run('getCategoryByParentid',array('#parent_id#',$first['id'])) as $key => $second){?>
				<li><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a></li>
				<?php }?>
				<li class="more"><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>">更多 <i class="icon-angle-right"></i></a></li>
			</ul>
		</nav>
	</header>
	<section class="floor_body">
		<div class="floor_show">
			<?php echo Ad::show("汽车广告",$first['id']);?>
		
		</div>
		<div class="floor_goods">
			<ul>
				<?php foreach(Api::run('getCategoryExtendList',array('#categroy_id#',$first['id']),8) as $key => $item){?>
				<li>
					<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>">
						<img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/170/h/170");?>">
						<h4><?php echo isset($item['name'])?$item['name']:"";?></h4>
						<em>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></em>
						<del>￥<?php echo isset($item['market_price'])?$item['market_price']:"";?></del>
					</a>
				</li>
				<?php }?>
			</ul>
		</div>
	</section>
</section>
<?php }?>


<script>
//dom载入完毕执行
$(function(){
	// 调用焦点图
	$("#home_fouse").FengFocus({trigger : "mouseover"});
	$('#home_rec').kxbdSuperMarquee({
		distance:1215,
		time:5,
		btnGo:{left:'#home_rec_left',right:'#home_rec_right'},
		direction:'left'
	});
	
	
	
});
</script>

<!--主要模板内容 结束-->

<footer class="foot">
	<section class="service">
		<ul>
			<li class="item1">
				<i class="icon-star"></i>
				<strong>正品优选</strong>
			</li>
			<li class="item2">
				<i class="icon-globe"></i>
				<strong>上市公司</strong>
			</li>
			<li class="item3">
				<i class="icon-group"></i>
				<strong>300家连锁门店</strong>
			</li>
			<li class="item4">
				<i class="icon-plane"></i>
				<strong>长株潭次日达</strong>
			</li>
			<li class="item5">
				<i class="icon-gift"></i>
				<strong>满99包邮</strong>
			</li>
		</ul>
	</section>
	<section class="help">
		<?php $catIco = array('help-new','help-delivery','help-pay','help-user','help-service')?>
		<?php foreach(Api::run('getHelpCategoryFoot') as $key => $helpCat){?>
		<dl class="help_<?php echo $key+1;?>">
			<dt><?php echo isset($helpCat['name'])?$helpCat['name']:"";?></dt>
			<?php foreach(Api::run('getHelpListByCatidAll',array('#cat_id#',$helpCat['id'])) as $key => $item){?>
			<dd><a href="<?php echo IUrl::creatUrl("/site/help/id/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></dd>
			<?php }?>
		</dl>
		<?php }?>
		<div class="contact">
			<em>400-888-8888</em>
			<span>周一到周日 8:00-18:00</span>
			<span>（仅收市话费）</span>
			<a href="#"><i class="icon-comments"></i> 24小时在线客服</a>
		</div>
	</section>
	<section class="copy">
		<?php echo IFilter::stripSlash($this->_siteConfig->site_footer_code);?>
	</section>
</footer>



</body>
</html>
<script>
//当首页时隐藏分类
<?php if(IWeb::$app->getController()->getId() == 'site' && IWeb::$app->getController()->getAction()->getId() == 'index'){?>
$('.cat_list').removeClass('none');
<?php }?>

$(function(){
	
	//$( "#date" ).datepicker();
	$('input:text[name="word"]').val("<?php echo $this->word;?>");
	
	//聊天
	$(".message").mouseover(function(){
		$(".right span").show();
	});
	
	$(".message").mouseout(function(){
		$(".right span").hide();
	});
	
	
	
	//响应拖拽
	var dh = $(".d_h");
	for(var i=0; i < dh.length; ++i){
		var dialog_header = dh[i];
		var flag = false;
		
		/*
		dialog_header.addEventListener('touchstart',function(){
			flag = true;
			var index = $(this).attr("value");
			test(flag,index-1);
		},false);
		
		
		dialog_header.addEventListener('touchend',function(){
			flag = false;
			var index = $(this).attr("value");
			test(flag,index-1);
		},false);
		*/
		
		dialog_header.ondragstart  = function(){
			flag = true;
			var index = $(this).attr("value");
			
			test(flag,index-1);
			
		}
		
		dialog_header.ondragend = function(){
			console.log("down");
			flag = false;
			var index = $(this).attr("value");
			test(flag,index-1);
		}
	}
	
	
	//console.log("dh is ");
	//console.log(dialog_header);
	//var dialog_header = document.getElementById("dialog_header");
	
	$(".close").click(function(){
		$(".chat_list").css("-webkit-transform","translateX(500px)");
	});
	
	$("#showFriend").click(function(){
		$(".chat_list").css("-webkit-transform","translateX(0px)");
	});
	
});

//拖动窗口响应
function test(flag,index){
	if(!flag){
		//document.getElementById("dialog").draggable = false;
		$(".msg-dialog")[index].draggable = false;
		return ;
	}
	//document.getElementById("dialog").draggable = true;
	var md = $(".msg-dialog");
	md[index].draggable = true;
	/*
	for(var i=0;i<md.length;++i){
		md[i].draggable = true;
	}*/
	//console.log(this);
	//$(".msg-dialog").html().draggable = true;
	//var dragdiv = document.querySelector(".msg-dialog");
	
	var dragdiv = md[index];
   	var x, y;  //记录到点击时鼠标到移动框左边和上边的距离

    dragdiv.addEventListener('dragstart', function (e) {
        e.dataTransfer.effectAllowed = "move";  //移动效果
        e.dataTransfer.setData("text", '');  //附加数据，　没有这一项，firefox中无法移动
        x = e.offsetX || e.layerX;
        y = e.offsetY || e.layerY;
        return true;
    }, false);

    document.addEventListener('dragover', function (e) {//取消冒泡 ,不取消则不能触发 drop事件
        e.preventDefault()|| e.stopPropagation();
    }, false);

    document.addEventListener('drop', function (e) {
        dragdiv.style.left = (e.pageX - x) + 'px';
        dragdiv.style.top = (e.pageY - y) + 'px';
        e.preventDefault() || e.stopPropagation();  //不取消，firefox中会触发网页跳转到查找setData中的内容
    }, false);
}


</script>
