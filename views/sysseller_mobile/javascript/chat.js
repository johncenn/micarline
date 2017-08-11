

var so=false,n=false;
var ex = "http://"+window.location.host+"/";
//var db_url = "http://www.micarline.net/chat/talkAdd_db";
var db_url = ex+"git/micarshop/chat/talkAdd_db";

var db_change = "http://localhost/git/micarshop/chat/change";
$(function(){
	
	var url='ws://127.0.0.2:8000';
	//var url='ws://120.25.77.104:8000';
	//var scollheight = 0 ;
	var ex = "http://"+window.location.host+"/";
	if(t_id == ""){
		console.log("kon");
		return ;
	}
	
	so=new WebSocket(url);
	//连接sock初始化
	so.onopen=function(){
		if(so.readyState==1){
			so.send('type=init&send_id='+f_id+'&name='+f_name);
			console.log("已连接");
		}
	}
	
	//退出聊天室
	so.onclose=function(){
		console.log("退出聊天室");
		//so.send('type=init&send_id='+user_id+'&name='+name);
		
	}
	
	so.onmessage=function(msg){
		console.log(msg);
		eval('var da='+msg.data);
		var type = da.type;
		
		if(type == 'init'){
			
			console.log('init');
			
		}else if(type == 'rmove'){
			
			console.log("rmove");
			
		}else if(type == "init_qun"){
			
			console.log("群发init");
			
		}else if(type == 'talk'){
			
			if(da.send_id == f_id){
				return ;
			}
			
			var ava_img = f_ava ;
			var class_type = "message_right";
			
			if(da.send_id != f_id){
				class_type = "message_left";
				ava_img = t_ava;
				
				//到数据库把此条数据的状态改为已读
				var message_id = da.msg_id;
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text";
				xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
				console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
				
			}
			
			if(ava_img == ""){
				ava_img = "/upload/car.jpg";
			}
			if(ava_img.substring(0,4) == "http"){
				
			}else{
				ava_img = ex + ava_img ;
			}
			var m_t = $("#chat-messages");
			var message = $('<div class='+class_type+'></div>');
			var img = $('<img src='+ava_img+' />');
			var bubble = $('<div class=bubble></div>');
			var corner = $('<div class=corner></div>');
			var line = $('<div class=line></div>');
			var span = $('<span>'+da.con+'</span>');
			
			corner.append(line);
			bubble.append(corner);
			bubble.append(span);
			
			message.append(img);
			message.append(bubble);
			
			m_t.append(message);
			
			scollheight = scollheight + parseInt($("#chat-messages").height());
			$("#chat-messages").scrollTop(scollheight);
			
			
			
		}else if(type == 'talk_img'){
			
			if(da.send_id == f_id){
				return ;
			}
			
			var ava_img = f_ava ;
			var class_type = "message_right";
			
			if(da.send_id != f_id){
				class_type = "message_left";
				ava_img = t_ava;
				
				//到数据库把此条数据的状态改为已读
				var message_id = da.msg_id;
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text";
				xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
				console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
				
				
			}
			
			if(ava_img == ""){
				ava_img = "/upload/car.jpg";
			}
			
			if(ava_img.substring(0,4) == "http"){
				
			}else{
				ava_img = ex + ava_img ;
			}
			var s_img = ex + da.con ;
			var m_t = $("#chat-messages");
			var message = $('<div class='+class_type+'></div>');
			var img = $('<img src='+ava_img+' />');
			var bubble = $('<div class=bubble></div>');
			var corner = $('<div class=corner></div>');
			var line = $('<div class=line></div>');
			//var span = $('<span>'+da.con+'</span>');
			var span = $('<img  src='+s_img+'></img>');
			corner.append(line);
			bubble.append(corner);
			bubble.append(span);
			
			message.append(img);
			message.append(bubble);
			
			m_t.append(message);
			
			scollheight = scollheight + parseInt($("#chat-messages").height());
			$("#chat-messages").scrollTop(scollheight);
			
			
		}else if(type == 'talk_bimg'){
			if(da.send_id == f_id){
				return ;
			}
			
			var ava_img = f_ava ;
			var class_type = "message_right";
			
			if(da.send_id != f_id){
				class_type = "message_left";
				ava_img = t_ava;
				
				
				//到数据库把此条数据的状态改为已读
				var message_id = da.msg_id;
				var xhr = new XMLHttpRequest();
				xhr.responseType = "text";
				xhr.open("GET",db_change+"?mid="+message_id,true);
				xhr.onload = function(e){
				console.log("返回   "+this.responseText);
					//var msg_id = this.responseText;
				}
				xhr.send();
				
			}
			
			if(ava_img == ""){
				ava_img = "/upload/car.jpg";
			}
			
			if(ava_img.substring(0,4) == "http"){
				
			}else{
				ava_img = ex + ava_img ;
			}
			
			var s_img = ex +"upload/talkimg/"+ da.con ;
			var m_t = $("#chat-messages");
			var message = $('<div class='+class_type+'></div>');
			var img = $('<img  src='+ava_img+' />');
			var bubble = $('<div class=bubble></div>');
			var corner = $('<div class=corner></div>');
			var line = $('<div class=line></div>');
			//var span = $('<span>'+da.con+'</span>');
			var span = $('<img class="bq" src='+s_img+'></img>');
			corner.append(line);
			bubble.append(corner);
			bubble.append(span);
			
			message.append(img);
			message.append(bubble);
			
			m_t.append(message);
			
			scollheight = scollheight + parseInt($("#chat-messages").height());
			$("#chat-messages").scrollTop(scollheight);
			
			
		}
	}
	
	//发送文本内容
	$(".sendBtn").click(function(){
		//console.log("send");
		
		var content = $(".area").val();
		var class_type = "message_right";
		var ava_img = f_ava ;
		
		if($.trim(content) == ""){
			alert("内容不能为空");
			return ;
		}
		
		if(ava_img == ""){
				ava_img = "/upload/car.jpg";
		}
			
		if(ava_img.substring(0,4) == "http"){
			
		}else{
			ava_img = ex + ava_img ;
		}
		
		var m_t = $("#chat-messages");
		var message = $('<div class='+class_type+'></div>');
		var img = $('<img src='+ava_img+' />');
		var bubble = $('<div class=bubble></div>');
		var corner = $('<div class=corner></div>');
		var line = $('<div class=line></div>');
		var span = $('<span>'+content+'</span>');
		
		corner.append(line);
		bubble.append(corner);
		bubble.append(span);
		message.append(img);
		message.append(bubble);
		m_t.append(message);
		scollheight = scollheight + parseInt($("#chat-messages").height());
		$("#chat-messages").scrollTop(scollheight);
		
		//插入数据库
		var formData = new FormData();
		formData.append("u_id",f_id);
		formData.append("u_name",f_name);
		formData.append("t_id",t_id);
		formData.append("t_name",t_name);
		formData.append("content",content);
		formData.append("type","text");
		var xhr = new XMLHttpRequest();
		xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			console.log(this.responseText);
			
			var msg_id = this.responseText;
			so.send('type=talk&send_id='+f_id+'&t_id='+t_id+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id);
		}
		xhr.send(formData);
		
		
		//console.log(f_ava);
		$('.area').val("");
		$('.area').css("height","0.8rem");
	});
	
	
	//点击图片响应
	$(".yuliu").click(function(){
		test();
	});
	
	/**
	 * 表情处理 start
	 */
	//表情点击显示
	$('.biaoqing').bind('click',function(e){
		stopPropagation(e);
		 $('.talk_img').show();
	 });
	
	//点击其他地方表情隐藏
	$(document).bind('click',function(){
	    $('.talk_img').hide();
	});
	
	$('.event_one').bind('click',function(e){
		
		stopPropagation(e);
		var t = $(this).get(0) ;
		var i_img = t.getElementsByTagName("img");
		var $img = $(i_img);
		
		
		var content = $img.attr('alt');
		var class_type = "message_right";
		var ava_img = f_ava ;
		
		if(ava_img == ""){
				ava_img = "/upload/car.jpg";
		}
			
		if(ava_img.substring(0,4) == "http"){
			
		}else{
			ava_img = ex + ava_img ;
		}
		
		var s_img = ex +"upload/talkimg/"+ content ;
		var m_t = $("#chat-messages");
		var message = $('<div class='+class_type+'></div>');
		var img = $('<img  src='+ava_img+' />');
		var bubble = $('<div class=bubble></div>');
		var corner = $('<div class=corner></div>');
		var line = $('<div class=line></div>');
		//var span = $('<span>'+da.con+'</span>');
		var span = $('<img class="bq" src='+s_img+'></img>');
		
		corner.append(line);
		bubble.append(corner);
		bubble.append(span);
		message.append(img);
		message.append(bubble);
		m_t.append(message);
		
		scollheight = scollheight + parseInt($("#chat-messages").height());
		$("#chat-messages").scrollTop(scollheight);
		$('.talk_img').hide();
		
		//记录保存数据库
		var formData = new FormData();
		formData.append("u_id",f_id);
		formData.append("u_name",f_name);
		formData.append("t_id",t_id);
		formData.append("t_name",t_name);
		formData.append("content",content);
		formData.append("type","b_img");
		var xhr = new XMLHttpRequest();
		xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			console.log(this.responseText);
			var msg_id = this.responseText;
			so.send('type=talk_bimg&send_id='+f_id+'&t_id='+t_id+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id);
		}
		xhr.send(formData);
		
	 });
	
});

//防止冒泡方法
function stopPropagation(e) {
    if (e.stopPropagation) 
      e.stopPropagation();//停止冒泡  非ie
    else
      e.cancelBubble = true;//停止冒泡 ie
}

function test(){
	var file = $("#upp").get(0);
	console.log(file);
	file.click();
}

function select_photo(){
	console.log("选择了");
	var logURL = "http://"+window.location.host+"/git/micarshop/chat/photo_upload";
	var upload_URL = "";
	var file = $('#upp').get(0).files[0];
	
	if(file){
		console.log(file);
		var fd = new FormData();
		fd.append("file", file);
		var xhr = new XMLHttpRequest();
		xhr.open("POST", logURL, false);
		xhr.onload = function(){
			console.log(this.responseText);
			//console.log(so);
			var content = this.responseText ;
			console.log("img is "+content);
			if(content == "fai"){
				alert("图片发送失败");
				return ;
			}
			send_img(content);
			//so.send('type=talk_img&send_id='+f_id+'&t_id='+t_id+'&con='+content+'&ava_img='+f_ava);
			//console.log('ok');
		}
		xhr.send(fd);
		
	}else{
		console.log("un");
	}
}

//发送图片 且 插入数据库
function send_img(content){
	
	
	var s_img = ex + content ;
	var class_type = "message_right";
	var ava_img = f_ava ;
	
	if(ava_img == ""){
		ava_img = "/upload/car.jpg";
	}
		
	if(ava_img.substring(0,4) == "http"){
		
	}else{
		ava_img = ex + ava_img ;
	}
	
	var m_t = $("#chat-messages");
	var message = $('<div class='+class_type+'></div>');
	var img = $('<img src='+ava_img+' />');
	var bubble = $('<div class=bubble></div>');
	var corner = $('<div class=corner></div>');
	var line = $('<div class=line></div>');
	var span = $('<img  src='+s_img+'></img>');
	
	corner.append(line);
	bubble.append(corner);
	bubble.append(span);
	message.append(img);
	message.append(bubble);
	m_t.append(message);
	
	scollheight = scollheight + parseInt($("#chat-messages").height());
	$("#chat-messages").scrollTop(scollheight);
	
	var formData = new FormData();
	formData.append("u_id",f_id);
	formData.append("u_name",f_name);
	formData.append("t_id",t_id);
	formData.append("t_name",t_name);
	formData.append("content",content);
	formData.append("type","img");
	var xhr = new XMLHttpRequest();
	xhr.open("POST",db_url,true);
	xhr.onload = function(e){
		console.log(this.responseText);
		var msg_id = this.responseText;
		so.send('type=talk_img&send_id='+f_id+'&t_id='+t_id+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id);
	}
	xhr.send(formData);
}


















