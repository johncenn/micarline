
var item =null;
var t_name=null;
var avatar = "";
var to_ava = "";
var scollheight = 0;
var tan = 1;
//var db_url = "http://www.micarline.net/chat/talkAdd_db";
var db_url = "http://localhost/git/micarshop/chat/talkAdd_db";

var ex = "http://"+window.location.host+"/";
var so=false;
var user_id = null;
var user_name = null;

$(function(){
	

	user_id=$(".self_id").val();
	user_name=$(".self_name").val();
	var db_weidu = 'http://localhost/git/micarshop/chat/weidu?f_id='+user_id;
	//从数据库里获取未读消息
	var xhr = new XMLHttpRequest();
	xhr.responseType = "text";
		xhr.open("GET",db_weidu,true);
	xhr.onload = function(e){
		//console.log("返回   "+this.responseText);
		var msg = this.responseText;
		if(msg == ""){
			return ;
		}
		
		//$("").show();
		eval('var da='+msg);
		console.log(da);
		
		$(".chat_list").css("-webkit-transform","translateX(0)");
		
		for(var j = 0;j < da.length;++j){
			$('dd[from_id='+da[j].f_id+']').css("background","rgba(80,150,254,0.5)");
		}
		
		
		
	}
	xhr.send();
	
	var users={};
	var url='ws://127.0.0.2:8000';
	//var url='ws://120.25.77.104:8000';
	var n=false;
	
	
	//console.log(user_id+" "+user_name);
	if(user_id == ""){
		//console.log("kon")
		return ;
	}
	
	so=new WebSocket(url);
	//连接sock初始化
	so.onopen=function(){
		if(so.readyState==1){
			so.send('type=init&send_id='+user_id+'&name='+user_name);
			console.log("已连接");
		}
	}
	
	
	//退出聊天室
	so.onclose=function(){
		console.log("退出聊天室");
		//so.send('type=init&send_id='+user_id+'&name='+name);
		
	}
	
	//接收sock返回来的数据
	so.onmessage=function(msg){
		console.log(msg);
		eval('var da='+msg.data);
		var type = da.type;
		if(type == 'init'){
			var fs = $(".chat_user i");
			
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				//console.log($u);
				//遍历在sock容器里面的用户
				for(var j = 0;j < da.users.length;++j){
					if(fid == da.users[j]['other_id']){
						$u.attr("class","online");
					}else{
						$u.attr("class","offline");
					}
				}
			}
			
		}else if(type == 'rmove'){
			//好友下线
			console.log("rmove");
			var fs = $(".chat_user i");
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				
				if(fid == da.other_id){
					$u.attr("class","offline");
				}
			}
		}else if(type == "init_qun"){
		//	console.log("群发init");
			
			//遍历单个用户
			var fs = $(".chat_user i");
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				
				if(fid == da.other_id){
					$u.attr("class","online");
				}
				
			}
		}else if(type == 'talk'){
			
			if(da.send_id == user_id){
				return ;
			}
			$(".chat_list").css("-webkit-transform","translateX(0)");
			var db_change = "http://localhost/git/micarshop/chat/change";
			
			
			var class_type = "to_user";
			//头像处理
			var avaImg = user_ava;
			
			$('dd[from_id='+da.send_id+']').css("background","rgba(80,150,254,0.5)");
			
			if(da.send_id != user_id){
				class_type = "from_user";
				avaImg = to_ava ;
				
				var message_id = da.msg_id;
				//var message_id =2;
				//到数据库把此条数据的状态改为已读
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text";
					xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
					console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
			}
			
			//var chatCon = $(".chat_con");
			//console.log("弹 is "+tan);
			/*if(tan == 1){
				
				$('dd[from_id='+da.send_id+'] a').click();
				tan = tan +1;
			}*/
			
			var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
			var divhear = $('<div class='+class_type+'></div>');
			
			
			if(avaImg == ""){
				avaImg = "/upload/car.jpg";
			}
			
			if(avaImg.substring(0,4) == "http"){
				
			}else{
				avaImg = ex + avaImg ;
			}
			
			var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
			
			//console.log("username is "+user_name);
			
			/**
			 * ajax 发送请求插入数据库 
			 *	start
			 */
			
			//如果send_id 等于 当前的 id，则f_name是username
			
			var s_name = user_name;
			var to_name = t_name;
			if(user_id != da.send_id){
				s_name = t_name ;
				to_name = user_name ;
			}
			
			//console.log("s_name is "+s_name+" "+"to_name is "+to_name);
			
			
			/*end*/
			
			var dl = $('<dl></dl>');
			var dt = $('<dt>'+da.time+'</dt>');
			var dd1 = $('<dd>'+da.con+'</dd>');
			var dd2 = $('<dd class=arrow></dd>');
			
			dl.append(dt);
			dl.append(dd1);
			dl.append(dd2);
			
			divhear.append(avatar);
			divhear.append(dl);
			
			chatCon.append(divhear);
			
			//$('.isshow'+item+' .dialog-body .chat_con .msg_list');
			//$('.msg_list').scrollTop($('.msg_list').height());
			//console.log("height is ");
			scollheight =scollheight + parseInt($('.msg_list').height());
			console.log("height "+scollheight)
			$('.msg_list').scrollTop(scollheight);
			//$('.isshow'+item+' .dialog-body .chat_con .msg_list').scrollTop($('.isshow'+item+' .dialog-body .chat_con .msg_list').height());
			
			
		}else if(type == "talk_img"){
			
			if(da.send_id == user_id){
				return ;
			}
			
			$(".chat_list").css("-webkit-transform","translateX(0)");
			var class_type = "to_user";
			//头像处理
			var avaImg = user_ava;
			
			$('dd[from_id='+da.send_id+']').css("background","rgba(80,150,254,0.5)");
			
			if(da.send_id != user_id){
				class_type = "from_user";
				avaImg = to_ava ;
				var db_change = "http://localhost/git/micarshop/chat/change";
				var message_id = da.msg_id;
				//var message_id =2;
				//到数据库把此条数据的状态改为已读
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text"
					xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
					console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
			}
			
			//var chatCon = $(".chat_con");
			//console.log("弹 is "+tan);
			/*
			if(tan == 1){
				
				$('dd[from_id='+da.send_id+'] a').click();
				tan = tan +1;
			}*/
			var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
			var divhear = $('<div class='+class_type+'></div>');
			
			
			if(avaImg == ""){
				avaImg = "/upload/car.jpg";
			}
			
			if(avaImg.substring(0,4) == "http"){
				
			}else{
				avaImg = ex + avaImg ;
			}
			
			var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
			
			var s_name = user_name;
			var to_name = t_name;
			if(user_id != da.send_id){
				s_name = t_name ;
				to_name = user_name ;
			}
			
			//console.log("s_name is "+s_name+" "+"to_name is "+to_name);
			
			
			/*end*/
			var s_img = ex + da.con ;
			var dl = $('<dl></dl>');
			var dt = $('<dt>'+da.time+'</dt>');
			var dd1 = $('<img src='+s_img+'></img>');
			//var dd1 = $('<dd>'+da.con+'</dd>');
			var dd2 = $('<dd class=arrow></dd>');
			
			dl.append(dt);
			dl.append(dd1);
			dl.append(dd2);
			
			divhear.append(avatar);
			divhear.append(dl);
			
			chatCon.append(divhear);
			
			//$('.isshow'+item+' .dialog-body .chat_con .msg_list');
			//$('.msg_list').scrollTop($('.msg_list').height());
			//console.log("height is ");
			scollheight =scollheight + parseInt($('.msg_list').height());
			//console.log("height "+scollheight)
			$('.msg_list').scrollTop(scollheight);
			
			
			
		}else if(type == "talk_bimg"){
			
			if(da.send_id == user_id){
				return ;
			}
			
			$(".chat_list").css("-webkit-transform","translateX(0)");
			var class_type = "to_user";
			//头像处理
			var avaImg = user_ava;
			
			$('dd[from_id='+da.send_id+']').css("background","rgba(80,150,254,0.5)");
			
			if(da.send_id != user_id){
				class_type = "from_user";
				avaImg = to_ava ;
				var db_change = "http://localhost/git/micarshop/chat/change";
				var message_id = da.msg_id;
				//console.log(message_id);
				//return ;
				//var message_id =2;
				//到数据库把此条数据的状态改为已读
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text";
					xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
					console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
			}
			
			//var chatCon = $(".chat_con");
			//console.log("弹 is "+tan);
			/*
			if(tan == 1){
				
				$('dd[from_id='+da.send_id+'] a').click();
				tan = tan +1;
			}*/
			var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
			var divhear = $('<div class='+class_type+'></div>');
			
			
			if(avaImg == ""){
				avaImg = "/upload/car.jpg";
			}
			
			if(avaImg.substring(0,4) == "http"){
				
			}else{
				avaImg = ex + avaImg ;
			}
			
			var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
			
			var s_name = user_name;
			var to_name = t_name;
			if(user_id != da.send_id){
				s_name = t_name ;
				to_name = user_name ;
			}
			
			//console.log("s_name is "+s_name+" "+"to_name is "+to_name);
			
			
			/*end*/
			var s_img = ex +"upload/talkimg/"+ da.con ;
			var dl = $('<dl></dl>');
			var dt = $('<dt>'+da.time+'</dt>');
			var dd1 = $('<img class="bq" src='+s_img+'></img>');
			//var dd1 = $('<dd>'+da.con+'</dd>');
			var dd2 = $('<dd class=arrow></dd>');
			
			dl.append(dt);
			dl.append(dd1);
			dl.append(dd2);
			
			divhear.append(avatar);
			divhear.append(dl);
			
			chatCon.append(divhear);
			
			//$('.isshow'+item+' .dialog-body .chat_con .msg_list');
			//$('.msg_list').scrollTop($('.msg_list').height());
			//console.log("height is ");
			scollheight =scollheight + parseInt($('.msg_list').height());
			//console.log("height "+scollheight)
			$('.msg_list').scrollTop(scollheight);
			
			
			
			
		}
	}
	
	console.log($(".msg-dialog:visible").length);
	
	//点击聊天响应
	$(".send_btn").click(function(){
		var con = $('.isshow'+item+' .send_message').val();
		$('.isshow'+item+' .send_message').val("");
		//console.log("con is  "+con+" t id is "+t_id);
		
		
		var class_type = "to_user";
		//头像处理
		var avaImg = user_ava;
		
		if(avaImg == ""){
			avaImg = "/upload/car.jpg";
		}
		
		if(avaImg.substring(0,4) == "http"){
			
		}else{
			avaImg = ex + avaImg ;
		}
		
		var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
		var divhear = $('<div class='+class_type+'></div>');
		var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
		
		 
		
		//发送
		var dl = $('<dl></dl>');
		var dt = $('<dt>'+getNowFormatDate()+'</dt>');
		var dd1 = $('<dd>'+con+'</dd>');
		var dd2 = $('<dd class=arrow></dd>');
		dl.append(dt);
		dl.append(dd1);
		dl.append(dd2);
		divhear.append(avatar);
		divhear.append(dl);
		chatCon.append(divhear);
		
		scollheight =scollheight + parseInt($('.msg_list').height());
		console.log("height "+scollheight)
		$('.msg_list').scrollTop(scollheight);
		
		
		
		
		
		/**
		 * ajax 发送请求插入数据库 
		 *	start
		 */
		var formData = new FormData();
		formData.append("u_id",user_id);
		formData.append("u_name",user_name);
		formData.append("t_id",t_id);
		formData.append("t_name",t_name);
		formData.append("content",con);
		formData.append("type","text");
		
		var xhr = new XMLHttpRequest();
		xhr.responseType = "text"
			xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			//console.log(this.responseText);
			var msg_id = this.responseText;
			so.send('type=talk&send_id='+user_id+'&t_id='+t_id+'&con='+con+'&ava_img='+avatar+'&msg_id='+msg_id);
		}
		xhr.send(formData);
		
		
	});
	
	
	
	
	
	/**
	 * 表情处理 start
	 */
	//表情点击显示
	$('.chat_show').bind('click',function(e){
		stopPropagation(e);
		 $('.isshow'+item+' .s_img').show();
	 });
	
	$('.event_one').bind('click',function(e){
		
		stopPropagation(e);
		var t = $(this).get(0) ;
		var i_img = t.getElementsByTagName("img");
		var $img = $(i_img);
		//console.log($img.attr('alt'));
		
		var content = $img.attr('alt');
		$('.s_img').hide();
		
		
		var class_type = "to_user";
		//头像处理
		var avaImg = user_ava;
		
		if(avaImg == ""){
			avaImg = "/upload/car.jpg";
		}
		
		if(avaImg.substring(0,4) == "http"){
			
		}else{
			avaImg = ex + avaImg ;
		}
		
		var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
		var divhear = $('<div class='+class_type+'></div>');
		var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
		
		var s_img = ex +"upload/talkimg/"+ content ;
		var dl = $('<dl></dl>');
		var dt = $('<dt>'+getNowFormatDate()+'</dt>');
		var dd1 = $('<img class="bq" src='+s_img+'></img>');
		//var dd1 = $('<dd>'+da.con+'</dd>');
		var dd2 = $('<dd class=arrow></dd>');
		
		dl.append(dt);
		dl.append(dd1);
		dl.append(dd2);
		
		divhear.append(avatar);
		divhear.append(dl);
		
		chatCon.append(divhear);
		
		//$('.isshow'+item+' .dialog-body .chat_con .msg_list');
		//$('.msg_list').scrollTop($('.msg_list').height());
		//console.log("height is ");
		scollheight =scollheight + parseInt($('.msg_list').height());
		console.log("height "+scollheight)
		$('.msg_list').scrollTop(scollheight);
		
		
		var formData = new FormData();
		formData.append("u_id",user_id);
		formData.append("u_name",user_name);
		formData.append("t_id",t_id);
		formData.append("t_name",t_name);
		formData.append("content",content);
		formData.append("type","b_img");
		
		var xhr = new XMLHttpRequest();
		xhr.responseType = "text"
			xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			console.log(this.responseText);
			var msg_id = this.responseText;
			so.send('type=talk_bimg&send_id='+user_id+'&t_id='+t_id+'&con='+content+'&ava_img='+to_ava+'&msg_id='+msg_id);
		}
		xhr.send(formData);
		
		
		
		
	 });
	//点击其他地方表情隐藏
	$(document).bind('click',function(){
	    $('.s_img').hide();
	});
	
	$('.event_one').hover(function(){
		$(this).css("background","#c2c2c2");
	},function(){
		$(this).css("background","white");
	});
	
	/**
	 * 表情处理 end
	 */
	 
	 
	 
	  /**
	 *	图片发送 s
	 */
	 $('.photo_upload').click(function(){
		$('.isshow'+item+' #upp').click();
	});
	 
	 /**
	 *	图片发送 end
	 */
	 
	
	
	
	
	//关闭整个聊天窗口
	$('.close_big').click(function(){
		//$(".close_big").css("background","red");
		$('.isshow'+item).hide();
	}) ;
	
	
	$("dd").click(function(){
		//console.log("an");
		$(this).css("background","white");
	});
});


var init = 1;
var isin = new Array();

//获取当前时间
function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
            + " " + date.getHours() + seperator2 + date.getMinutes()
            + seperator2 + date.getSeconds();
    return currentdate;
}

//聊天初始化
function talk(f_id,tid,id,ava,tName){
	console.log("talk");
	$('.isshow'+id+' .dialog-body .chat_con').empty();
	//isin.push(id);
	var tarr = isin.indexOf(id);
	to_ava = ava ;
	item = id ;
	t_id = tid;
	avatar = ava;
	t_name = tName;
	$(".msg-dialog").hide();
	$(".isshow"+id).show();
	$(".isshow"+id).css("left","600");
	$(".isshow"+id).css("top","200");
	$(".send_message").focus();
	//console.log("user_ava is "+user_ava);
	$('dd[from_id='+t_id+']').css("background","rgba(80,150,254,0.5)");
	
	
	/*
	$('.isshow'+id+' .chat_show').click(function(){
		$('.isshow'+id+' .s_img').show();
	});*/
	
		//isin.push(id);
		var logURL = "http://"+window.location.host+"/git/micarshop/chat/getTalkLog";
		//var logURL = "http://"+window.location.host+"/chat/getTalkLog";
		var xhr = new XMLHttpRequest();
		xhr.open('POST',logURL,true);
		
		var formData = new FormData();
		formData.append("t_id",t_id);
		formData.append("f_id",f_id);
		xhr.onload = function(){
			var msg = this.responseText;
			//console.log(msg);
			if(msg == ""){
				return ;
			}
			eval('var da='+msg);
			//console.log(da);
			
			for(var i = 0 ; i<da.length ; ++i){
				var class_type = "to_user";
				avatar = user_ava;
				if(da[i].f_id != f_id){
					class_type = "from_user";
					avatar = ava ;
				}
				
				//console.log("substr is ");
				//console.log(avatar.substring(0,4));
				
				if(avatar == ""){
					avatar = "/upload/car.jpg";
				}
				
				if(avatar.substring(0,4) == "http"){
					
				}else{
					avatar = ex + avatar ;
				}
				
				var chatCon = $('.isshow'+id+' .dialog-body .chat_con');
				var divhear = $('<div class='+class_type+'></div>');
				var avata = $('<span class=user-avatar><img src='+avatar+'></span>');
				
				var dl = $('<dl></dl>');
				var dt = $('<dt>'+da[i].add_time+'</dt>');
				
				if(da[i].type == "text"){
					var dd1 = $('<dd>'+da[i].t_msg+'</dd>');
				}else if(da[i].type == "img"){
					var s_img = ex + da[i].t_msg ;
					var dd1 = $('<img src='+s_img+'></img>')
				}else if(da[i].type == "b_img"){
					var s_img = ex +"upload/talkimg/"+ da[i].t_msg ;
					var dd1 = $('<img class="bq" src='+s_img+'></img>');
				}
				
				
				
				var dd2 = $('<dd class=arrow></dd>');
				
				dl.append(dt);
				dl.append(dd1);
				dl.append(dd2);
				
				divhear.append(avata);
				divhear.append(dl);
				
				chatCon.append(divhear);
				
				scollheight =scollheight + parseInt($('.msg_list').height());
				//console.log("height "+scollheight)
				$('.msg_list').scrollTop(scollheight);
			}
			
			
		}
		xhr.send(formData);
		//init = init +1;
	
	
	
	
	
}

//防止冒泡方法
function stopPropagation(e) {
    if (e.stopPropagation) 
      e.stopPropagation();//停止冒泡  非ie
    else
      e.cancelBubble = true;//停止冒泡 ie
  }

function select_photo(){
	console.log("选择了");
	//var logURL = "http://"+window.location.host+"/git/micarshop/chat/photo_upload";
	var logURL = "http://"+window.location.host+"/chat/photo_upload";
	var upload_URL = "";
	var file = $('.isshow'+item+' #upp').get(0).files[0];
	if(file){
		
		console.log(file);
		var fd = new FormData();
		fd.append("file", file);
		var xhr = new XMLHttpRequest();
		xhr.open("POST", logURL, true);
		xhr.onload = function(){
			console.log(this.responseText);
			console.log(so);
			var content = this.responseText ;
			if(content == "fai"){
				alert("图片发送失败");
				return ;
			}
			send_img(content);
			//so.send('type=talk_img&send_id='+user_id+'&t_id='+t_id+'&con='+content+'&ava_img='+to_ava);
		}
		xhr.send(fd);
		
	}else{
		console.log("un");
	}
}

function send_img(content){
	
	var class_type = "to_user";
	//头像处理
	var avaImg = user_ava;
	
	if(avaImg == ""){
		avaImg = "/upload/car.jpg";
	}
	
	if(avaImg.substring(0,4) == "http"){
		
	}else{
		avaImg = ex + avaImg ;
	}
	
	var chatCon = $('.isshow'+item+' .dialog-body .chat_con');
	var divhear = $('<div class='+class_type+'></div>');
	var avatar = $('<span class=user-avatar><img src='+avaImg+'></span>');
	
	var s_img = ex + content ;
	var dl = $('<dl></dl>');
	var dt = $('<dt>'+getNowFormatDate()+'</dt>');
	var dd1 = $('<img src='+s_img+'></img>');
	//var dd1 = $('<dd>'+da.con+'</dd>');
	var dd2 = $('<dd class=arrow></dd>');
	
	dl.append(dt);
	dl.append(dd1);
	dl.append(dd2);
	
	divhear.append(avatar);
	divhear.append(dl);
	
	chatCon.append(divhear);
	
	//$('.isshow'+item+' .dialog-body .chat_con .msg_list');
	//$('.msg_list').scrollTop($('.msg_list').height());
	//console.log("height is ");
	scollheight =scollheight + parseInt($('.msg_list').height());
	//console.log("height "+scollheight)
	$('.msg_list').scrollTop(scollheight);
	
	
	
	var formData = new FormData();
	formData.append("u_id",user_id);
	formData.append("u_name",user_name);
	formData.append("t_id",t_id);
	formData.append("t_name",t_name);
	formData.append("content",content);
	formData.append("type","img");
	
	var xhr = new XMLHttpRequest();
	xhr.responseType = "text"
		xhr.open("POST",db_url,true);
	xhr.onload = function(e){
		var msg_id = this.responseText;
		
		so.send('type=talk_img&send_id='+user_id+'&t_id='+t_id+'&con='+content+'&ava_img='+to_ava+'&msg_id='+msg_id);
	}
	xhr.send(formData);
	
}

function test_kua(){
	console.log("可以执行");
	//parent.location.reload();
}

function add(u_id,u_name,t_id,t_name){
	console.log(u_id+" "+u_name+" "+t_id+" "+t_name);
//	var url = "index.php?controller=chat&action=add";
//	
//	var xhr = new XMLHttpRequest();
//	xhr.open("GET",url,true);
//	xhr.onload = function(e){
//		console.log(e);
//	}
//	xhr.send();
}

/*
console.log(user_id)

if(123){
	return 1;
}

function chat_get(u_id,n){
	$(".chat_list").css("-webkit-transform","translateX(0)");
	user_id = u_id;
	name = n;
}


var users={};
var url='ws://127.0.0.2:8000';
var so=false,n=false;


//聊天加载好友是否在线
function chat_get(user_id,name){
	console.log("id is 1 "+user_id);
	$(".chat_list").css("-webkit-transform","translateX(0)");
	
	
	
	so=new WebSocket(url);
	//连接sock
	so.onopen=function(){
		if(so.readyState==1){
			so.send('type=init&send_id='+user_id+'&name='+name);
			console.log("已连接");
		}
	}
	
	//退出聊天室
	so.onclose=function(){
		console.log("退出聊天室");
		//so.send('type=init&send_id='+user_id+'&name='+name);
		
	}
	
	//接收sock返回来的数据
	so.onmessage=function(msg){
		console.log(msg.data);
		eval('var da='+msg.data);
		var type = da.type;
		
		//初始化
		if(type == 'init'){
			var fs = $(".chat_user i");
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				console.log(fid);
				//遍历在sock容器里面的用户
				for(var j = 0;j < da.users.length;++j){
					if(fid == da.users[j]['other_id']){
						$u.attr("class","online");
					}else{
						$u.attr("class","offline");
					}
				}
			}
			
		}else if(type == 'rmove'){
			//好友下线
			console.log("rmove");
			var fs = $(".chat_user i");
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				
				if(fid == da.other_id){
					$u.attr("class","offline");
				}
			}
		}else if(type == "init_qun"){
			console.log("群发init");
			
			//遍历单个用户
			var fs = $(".chat_user i");
			for(var i = 0; i < fs.length; ++i){
				var $u = $(fs[i]);	//每个好友
				var fid = $u.attr("f_id");//每个好友的id
				
				if(fid == da.other_id){
					$u.attr("class","online");
				}
				
			}
		}
		
		
		
	}
	
	
	
	
	
	
	
}

var f_id = null;
var t_id = null;
var userName = null;
var toName = null;

//聊天初始化
function talk(f_id,t_id,id){
	
	$(".msg-dialog").hide();
	$(".isshow"+id).show();
	$(".send_message").focus();
	if(!so){
		so=new WebSocket(url);
		console.log("new");
		//连接sock
		so.onopen=function(){}
	}
	console.log("s");
	console.log("length "+$('.isshow'+id+' .send_btn').length);
	$('.isshow'+id+' .send_btn').click(function(){
		console.log("send");
		var con = $('.isshow'+id+' .send_message').val();
		console.log("send  "+con+" to "+t_id+" "+"send_id is "+f_id);
		
		so.send('type=talk&send_id='+f_id+'&t_id='+t_id+'&con='+con);
		//接收sock返回来的数据
		so.onmessage=function(msg){
			console.log(msg);
		}
		
	});
	
}

function send_msg(item,f_id){
	console.log(item);
	var t_id = $('.isshow'+item+' .to').val();
	var con = $('.isshow'+item+' .send_message').val();
	console.log(con);
	
}

*/


















