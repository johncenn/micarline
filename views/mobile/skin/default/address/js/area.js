function Area(){
	
}

//为每一个地址添加一个click事件
Area.prototype.init  = function(item){
	
	item.each(function(){
		
		this.addEventListener('click',function(){
			console.log("aa");
		})
	});
}

//jq对象
Area.prototype.seach = function(obj,flag){
	
	if(flag == "area"){
		var content = obj.val().length;
		if(content < 1){
			return false;
		}
	}else if(flag == "address"){
		var content = obj.val().length;
		if(content < 5){
			
			return false;
		}
	}else if(flag == "name"){
		var content = obj.val().length;
		if(content < 2){
			
			return false;
		}
	}else if(flag == "phone"){
		var phone = obj.val();
		if(phone.length > 11){
			console.log("false") ;
			return false;
		}
		var pattern = /(13\d|14[57]|15[^4,\D]|17[13678]|18\d)\d{8}|170[0589]\d{7}/;
		var pa = pattern.test(phone) ;
		if(!pa){
			return false;
		}
	}
	
	return true;
	
}
