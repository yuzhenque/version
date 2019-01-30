/**
 * Loading界面JQuery扩展类
 * 
 * 使用方法：
 * 加载loading界面：$.loading('显示提示信息，可为空')
 * 移除loading界面：$.remove_loading();
 * 
 */

jQuery.extend({
	loading_handle : null,
	
	loading_color : {'bgColor':'#000000', 'fontColor':'#ffa200', 'ldColor':'#ffa200', 'hoverColor':'#F5F5F5'},
	
	_loading_init : function(){
		$("body").append("<div id='loading-background' style='display:none;z-index:99999998;left:0;top:0;'></div><div style='display:none;z-index:99999999;' id='loading-container'><div class='loading-div' style='width:100%;height:60px;text-align:center;color:"+this.loading_color.fontColor+";font-weight:bold;font-size:1.5em;'></div><p class='loading-p' style='width:220px;height:22px;margin:auto;'></p></div>");
		$("#loading-background").css({
			position : "absolute",
			width : $(window).width()+"px",
			height : ($('body').height() > $(window).height() ? $('body').height() : $(window).height()) +"px",
			background : this.loading_color.bgColor,
			opacity : 0.8
		}).fadeIn();
		$("#loading-container").css({
			position : "absolute",
			width : $(window).width()+"px",
			height : "60px",
			left: 0,
			top : $(window).scrollTop() + ($(window).height() - 100)/2 + 'px',
			marginTop : "-30px"
		}).fadeIn();
		
		for(var i=0; i<10; i++){
			$("#loading-container .loading-p").append("<span"+(i==0 ? " class='loading-hover'" : "")+"></span>");
		}
		
		$("#loading-container .loading-p span").css({
			display : "block",
			float : "left",
			margin : "1px",
			background : this.loading_color.ldColor,
			width : "20px",
			height : "20px",
			'border-radius' : '10px'
		});
		
		$("#loading-container span.loading-hover").css("background",this.loading_color.hoverColor);
	},
	
	//显示loading界面
	loading :  function(message){
		if($("#loading-container").length <= 0){
			this._loading_init();
			this._loading_auto();
		}
		$("#loading-container .loading-div").html(message?message:'');
	},
	
	_loading_auto : function(){
		this.loading_handle = setTimeout(function(){
			var $hover = $("#loading-container span.loading-hover");
			var $next = $hover.next("span").length>0 ? $hover.next("span") : $("#loading-container .loading-p span").eq(0);
			$hover.css("background",$.loading_color.ldColor).removeClass("loading-hover");
			$next.css("background",$.loading_color.hoverColor).addClass("loading-hover");
			
			$._loading_auto();
		},120);
	},
	
	
	//移除loading界面
	remove_loading : function(){
		window.clearTimeout(this.loading_handle);
		$("#loading-background").fadeOut(function(){
			$(this).remove();
		});
		$("#loading-container").remove();
	}
});