
var so=false;
var user_id = '1';
var user_name = 'micar';
var ex = "http://"+window.location.host+"/";
var db_url = ex+"chat/talkAdd_db";
//var db_url = ex+"git/micarshop/chat/talkAdd_db";

$(function(){
	var url='ws://120.25.77.104:8000';
	var n=false;
	so=new WebSocket(url);
	
	so.onopen=function(){
		if(so.readyState==1){
			so.send('type=init&send_id='+user_id+'&name='+user_name);
			console.log("已连接");
		}
	}
	
	//退出聊天室
	so.onclose=function(){
		alert("退出了聊天");
		console.log("退出聊天室");
		//so.send('type=init&send_id='+user_id+'&name='+name);
		//location.reload(true);
	}
	
	//接收sock返回来的数据
	so.onmessage=function(msg){
		
		
		
		console.log(msg.data);
		eval('var da='+msg.data);
		console.log(da);
		var type = da.type;
		
		if(type == 'talk'){
			if(da.send_id == user_id){
				return ;
			}
			
			var ava_img = da.ava_tar;
			var content = da.con;
			var time = da.time;
			var timeshow = time+" "+da.send_name+" 给你发送消息";
			var send_name = da.send_name;
			
			//显示
			var user_content = $(".user");
			var msg_box = $("<div class='message_box'></div>");
			var msg_time = $("<div class='message_box_time'>"+timeshow+"</div>");
			var img = $("<img onclick='img_click(this)' title="+da.send_name+" dataname="+da.send_name+" dataid="+da.send_id+"  class='mssage_box_pic' src='"+ava_img+"' />");
			var msg_content = $("<div id='message_content'></div>");
			var p = $("<p class='message_content_inner'>"+content+"</p>");
			
			msg_content.append(p);
			msg_box.append(msg_time);
			msg_box.append(img);
			msg_box.append(msg_content);
			user_content.append(msg_box);
			
			//设置进度条在最低部
			var time = new Date();
			var add = time.getTime();
			$('#con').scrollTop( $('#con')[0].scrollHeight + add );
			
		}else if(type == 'talk_bimg'){
			if(da.send_id == user_id){
				return ;
			}
			
			var ava_img = da.ava_tar;
			var content = da.con;
			var time = da.time;
			var timeshow = time+" "+da.send_name+" 给你发送消息";
			var send_name = da.send_name;
			
			var s_img = ex + "upload/talkimg/"+ content ;
			
			//显示
			var user_content = $(".user");
			var msg_box = $("<div class='message_box'></div>");
			var msg_time = $("<div class='message_box_time'>"+timeshow+"</div>");
			var img = $("<img class='mssage_box_pic' src='"+ava_img+"' />");
			var msg_content = $("<div id='message_content'></div>");
			var p = $("<p class='message_content_inner'></p>");
			var img_a = $("<a></a>");
			var b_img = $("<img src="+s_img+" />");
			
			img_a.append(b_img);
			p.append(img_a);
			msg_content.append(p);
			msg_box.append(msg_time);
			msg_box.append(img);
			msg_box.append(msg_content);
			user_content.append(msg_box);
			
			//设置进度条在最低部
			setbotton($("#con"));
		}
		
	}
	
	
	
/*--发送消息按钮响应-------------------------------------------------------------------------------------------------------*/
	
	
	//发送消息按钮响应
	$(".btn").click(function(){
		console.log(sendName +" "+sendTO);
		
		if(sendName == "" || sendTO == "" ){
			alert("请点击头像选择发送给的用户");
			return ;
		}
		
		//获取内容
		var content = $(".send_message").val();
		
		//时间
		var timeshow = funshowtime();
		
/*		var time = new Date();
		var d = time.getDate();//天
		var m = time.getMonth();//月
		var h = time.getHours();//小时
		var min = time.getMinutes();//分钟
		var timeshow = m+"月"+d+"日"+" "+h+":"+min+"  "+"发送给  "+sendName;
		*/
		
		//显示
		var user_content = $(".user");
		var msg_box = $("<div class='message_box from_self'></div>");
		var msg_time = $("<div class='message_box_time'>"+timeshow+"</div>");
		var img = $("<img class='mssage_box_pic from' src='"+f_ava+"' />");
		var msg_content = $("<div id='message_content'></div>");
		var p = $("<p class='message_content_inner'>"+content+"</p>");
		
		/*console.log("tou");
		console.log(f_ava);*/
		msg_content.append(p);
		msg_box.append(msg_time);
		msg_box.append(img);
		msg_box.append(msg_content);
		user_content.append(msg_box);
		
		$(".send_message").val("");//清空发送输入的内容
		//设置进度条在最低部
		setbotton($("#con"));
		
		//插入数据库
		var formData = new FormData();
		formData.append("u_id",user_id);
		formData.append("u_name",user_name);
		formData.append("t_id",sendTO);
		formData.append("t_name",sendName);
		formData.append("content",content);
		formData.append("type","text");
		var xhr = new XMLHttpRequest();
		xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			console.log(this.responseText);
			//console.log("mid_s is "+this.responseText);
			var msg_id = this.responseText;
			so.send('type=talk&send_id='+user_id+'&t_id='+sendTO+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id+'&send_name='+user_name);
		}
		xhr.send(formData);
		//so.send('type=talk&send_id='+user_id+'&t_id='+sendTO+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id);
		
		
	});
	

/*--点击表情响应-------------------------------------------------------------------------------------------------------*/
	
	//点击表情响应
	$('.event_one').bind('click',function(e){
		stopPropagation(e);
		if(sendName == "" || sendTO == "" ){
			alert("请点击头像选择发送给的用户");
			return ;
		}
		var t = $(this).get(0) ;
		var i_img = t.getElementsByTagName("img");
		var $img = $(i_img);
		var content = $img.attr('alt');//内容
		$('.s_img').hide();
		
		//时间
		var timeshow = funshowtime();
		var s_img = ex + "upload/talkimg/"+ content ;
		
		console.log(s_img);
		
		//显示
		var user_content = $(".user");
		var msg_box = $("<div class='message_box from_self'></div>");
		var msg_time = $("<div class='message_box_time'>"+timeshow+"</div>");
		var img = $("<img class='mssage_box_pic from' src='"+f_ava+"' />");
		var msg_content = $("<div id='message_content'></div>");
		var p = $("<p class='message_content_inner'></p>");
		var img_a = $("<a></a>");
		var b_img = $("<img src="+s_img+" />");
		
		img_a.append(b_img);
		p.append(img_a);
		msg_content.append(p);
		msg_box.append(msg_time);
		msg_box.append(img);
		msg_box.append(msg_content);
		user_content.append(msg_box);
		
		//设置进度条在最低部
		setbotton($("#con"));
		
		//插入数据库
		var formData = new FormData();
		formData.append("u_id",user_id);
		formData.append("u_name",user_name);
		formData.append("t_id",sendTO);
		formData.append("t_name",sendName);
		formData.append("content",content);
		formData.append("type","b_img");
		var xhr = new XMLHttpRequest();
		xhr.open("POST",db_url,true);
		xhr.onload = function(e){
			console.log(this.responseText);
			//console.log("mid_s is "+this.responseText);
			var msg_id = this.responseText;
			so.send('type=talk_bimg&send_id='+user_id+'&t_id='+sendTO+'&con='+content+'&ava_img='+f_ava+'&msg_id='+msg_id+'&send_name='+user_name);
		}
		xhr.send(formData);
		
	});
	
	
	
/*-----发送图片处理----------------------------------------------------------------------------------------------------*/
	
	
	$('.image').click(function(){
		//$('.isshow'+item+' #upp').click();
	});
	
	
	
	
	
	
	
	
	
});


//时间显示函数
function funshowtime(){
	//时间
	var time = new Date();
	var d = time.getDate();//天
	var m = time.getMonth();//月
	var h = time.getHours();//小时
	var min = time.getMinutes();//分钟
	var timeshow = m+"月"+d+"日"+" "+h+":"+min+"  "+"发送给  "+sendName;
	return timeshow;
}

//设置进度条到最低部
function setbotton(jq_div){
	var time = new Date();
	var add = time.getTime();
	jq_div.scrollTop( jq_div[0].scrollHeight + add );
}




