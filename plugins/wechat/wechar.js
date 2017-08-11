//为每个页面加入 微信 js sdk
var scr = document.createElement('script');
scr.setAttribute("type","text/javascript");
scr.setAttribute("src","http://res.wx.qq.com/open/js/jweixin-1.0.0.js");
var ohead=document.getElementsByTagName("head")[0];
ohead.appendChild(scr);


function loggn(url,openid,nickname){
	//console.log("url is "+url);
	//alert(openid+nickname);
	
	var u = window.location.href;
	var local = "http://www.micarline.net/senlin2/index.php/site/index";
	//u=u+"&openid="+openid;
	var shareUrl = local+"?friendopenid="+openid+"&friendname="+nickname;
	console.log(shareUrl);
	//把所有&替换成|
	u = u.replace(/\&/g,"|");
	
	var xhr = new XMLHttpRequest();
	u = url+"?url="+u;
	console.log(u);
	xhr.open('GET',u,false);
	xhr.onload = function(){
		var msg = this.responseText;
		var jsonObj=eval("("+msg+")");
		console.log(jsonObj);
		console.log(jsonObj.appId);
		
		wx.config({
			debug: false, 
			appId: jsonObj['appId'],
			timestamp: jsonObj['timestamp'],
			nonceStr: jsonObj['nonceStr'],
			signature: jsonObj['signature'],
			jsApiList: [
			  // 所有要调用的 API 都要加到这个列表中
			  "onMenuShareTimeline",
			  "onMenuShareAppMessage",
			  "onMenuShareQQ"
			]
		});
		
		
		
wx.ready(function () {
		 	
			/**/
		 	var utitle = document.title;
			var img = document.getElementsByTagName('img')[0];
			if(!img){
				img_url = "http://www.micarline.net/wap/micarlogo.jpg";
			}else{
				//console.log(img);
			//	console.log("...........");
			 	var img_url = img.src;
			 //	console.log(img_url);
			}
			
		 	
			// 在这里调用 API 
			wx.onMenuShareTimeline({
				title: nickname+"邀请你来浇水"+"参加恒福gogopark官方微信线上植树, 齐植树，赢43寸液晶大电视", // 分享标题
				link: shareUrl, // 分享链接
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
			
			//发送给朋友
			wx.onMenuShareAppMessage({
				title: nickname+"邀请你来浇水", // 分享标题
				desc: "参加恒福gogopark官方微信线上植树，赢43寸液晶大电视",
				link: shareUrl, // 分享链接
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
			
			
		});
		
		
	}
	
	xhr.send();
}






























