$(function(){
	var o = $("#manage_ico"),
		i = o.find(".ico img"),
		b = i.data("base");
	if(localStorage.mangeico){
		// getSetIco(i,b)
		i.attr("src",b + localStorage.mangeico +'.jpg');
	}else{
		i.attr("src",b + ~~(Math.random()*17)+'.jpg')
	}
	i.css("display","block");


	var s = '<div class="ico_list"><h3>设置头像</h3><i class="icon-remove"></i><ul>';
	for (var k = 0; k < 18; k++) {
		s+='<li><img data-name="'+k+'" src="'+b+k+'.jpg" ></li>'
	}
	s+='</ul></div>';
	$("body").append(s);
	var icos = $(".ico_list");
	o.on("click",".ico",function() {
		icos.show()
	});
	icos.on("click","i", function(event) {
		icos.hide();
	})
	icos.on("click","img", function(event) {
		var t = $(this);
		i.attr("src",b + t.data("name") +'.jpg');
		localStorage.mangeico = t.data("name");
		icos.hide();
	})
})
function getSetIco(i,b){

}
