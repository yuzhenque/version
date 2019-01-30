/**
 * 打开子窗口JQuery扩展类
 * Jquery常用扩展库，扩展jQuery对象本身，在jQuery命名空间上增加新函数，如：$.match_data(value, name)
 */

$.extend({
	window_pos : {left:0,top:0},
	click_handle : null,
	post_data : '',
	//显示子窗口并进行初始化
	window_init : function(title, data){
		if($("#window-container").length <= 0){
			var html = "<div onclick='$.window_close()' id='window-layer'></div>";
			html += "<div id='window-container'><span onclick='$.window_close()' class='close' title='关闭'></span><div class='onecolumn'>";
			html += "<div class='header'><h3>" + title + "</h3></div><div class='content' style='overflow:auto;'></div></div></div>";
			$("body").append(html);
			$("#window-container").css({
				left : this.window_pos.left + "px",
				top : this.window_pos.top + "px",
				width : 0,
				height : 0
			});
			$("#window-container").animate({
				left : '50%',
				top : '50%',
				width : ($("#wrapper").width()*0.7) + "px",
				height : ($("#wrapper").height()*0.7) + "px",
				marginLeft:-($("#wrapper").width()*0.35) + "px",
				marginTop:-($("#wrapper").height()*0.35) + "px"
			});
			
			$("#window-container .content").height($("#wrapper").height()*0.7-83);
			
			$("#window-layer").css({opacity : "0.3"}).fadeIn();
		}else{
			$("#window-container .header h3").html(title);
		}
		$("#window-container .content").html("").append(data);
		
		mywin("#window-container .mywin");
		lightboxShow("#window-container .lightbox");
	},
	
	window_close : function(){
		$("#window-layer").fadeOut(function(){
			$(this).remove();
		});
		$("#window-container").animate({
			left : this.window_pos.left + "px",
			top : this.window_pos.top + "px",
			width : 0,
			height : 0,
			marginTop : 0,
			marginLeft : 0
		},"slow",function(){
			$(this).remove();
		});
	}
});

//扩展jQuery元素集来提供新的方法，如：$(this).show_window()
(function($,doc){
	$.fn.append_window = function(data){
		$.window_pos = $(this).offset();
		$.click_handle = $(this);
		var title = $(this).attr("title");
		$.window_init(title+"", data);
		
		//绑定表单自动验证事件
		$("#window-container input,#window-container select,#window-container textarea").blur(function(){
			CHECK_UNIQUE = true;
			$(this).checkData();
		}).focus(function(){
			if($(this).nextAll(".DESC").length>0){
				$(this).nextAll(".DESC").show();
				$(this).nextAll(".MSG_BOX").remove();
			}
		});
	},
	
	//显示子窗口
	$.fn.show_window = function(taglist, toggle){
		var a = $(this).attr("href") || $(this).attr("rel") || $(this).attr("alt");
		var title = $(this).attr("title");
		$.window_pos = $(this).offset();
		$.click_handle = $(this);
		$.ajax({
			url : a,
			data : $.post_data,//post提交的数据
			success : function(dat){
				$.remove_loading();
				//返回数据为json对象
				if(typeof dat == "object"){
					$.msgbox(dat.info,dat.status,function(){
						//默认不跳转
						if(dat.url != 'nojump'){
							document.location.href = dat.url;
						}
					});
					$.window_close();
				}else{
					$.window_init(title+"", dat);
					//加入标签切换内容的功能
					if(taglist != undefined){
						if(typeof taglist == "function"){
							eval(taglist());
						}else{
							$("#window-container .header").append("<ul id='window-taglist'></ul>");
							var idx = 0;
							$.each(taglist, function(key, value){
								$("#window-taglist").append("<li"+(idx==0 ? " class='ui-tag-cur'" : "")+" lang='#" + key + "'>" + value + "<span></span></li>");
								if(idx > 0 && !toggle){
									$("#" + key).hide();
								}
								idx ++;
							});
							
							$("#window-taglist li").click(function(){
								if(!$(this).hasClass("ui-tag-cur")){
									if(!toggle){
										$($("#window-taglist li.ui-tag-cur").attr("lang")).hide();
										$($(this).attr("lang")).show();
									}else{
										$($(this).attr("lang")).parent().scrollToID($(this).attr("lang"),171);
									}
									$("#window-taglist li.ui-tag-cur").removeClass("ui-tag-cur");
									$(this).addClass("ui-tag-cur");
								}
							});
						}
					}
					
					//绑定表单自动验证事件
					$("#window-container input,#window-container select,#window-container textarea").blur(function(){
						CHECK_UNIQUE = true;
						$(this).checkData();
					}).focus(function(){
						if($(this).nextAll(".DESC").length>0){
							$(this).nextAll(".DESC").show();
							$(this).nextAll(".MSG_BOX").remove();
						}
					});
					hoverTag("tr.item,.edit-item");
				}
			},
			beforeSend : function(){
				$.loading('数据加载中，请稍候');
			}
		});
	},
	
	//满屏展示指定html标签
	$.fn.maximize = function(){
		var pos = $(this).offset();
		$("body").append("<div id='maximize-bg' style='position:absolute;top:0;left:0;width:100%;height:100%;z-index:999999997;'></div>");
		$(this).clone().val($(this).val()).appendTo("body").attr("id","save-code-tag").attr("lang",$(this).attr("id")).css({"position":"absolute",zIndex:999999998,left:pos.left+"px",top:pos.top+"px","font-size":"12px"}).animate({
			top : "65px",
			left : 0,
			width : ($(window).width()-$(this).outerWidth()+$(this).width())+"px",
			height : ($(window).height()-$(this).outerHeight()+$(this).height()-65)+"px"
		},"slow",function(){
			$("#maximize-bg").css("background","#FFFFFF");
			$("body").append("<span class='ui-btn ui-btn-green code-save'>保 存</span><span class='ui-btn ui-btn-gray code-cancle'>取 消</span>");
			$(".code-cancle").click(function(){
				$(".code-save,.code-cancle,#maximize-bg").remove();
				var $tag = $("#"+$("#save-code-tag").attr("lang"));
				var pos = $tag.offset();
				$("#save-code-tag").animate({
					left : pos.left + "px",
					top : pos.top + "px",
					width : $tag.width()+"px",
					height : $tag.height()+"px"
				},"slow",function(){
					$(this).remove();
				});
			});
			$(".code-save").click(function(){
				$("#"+$("#save-code-tag").attr("lang")).val($("#save-code-tag").val());
				$(".code-cancle").trigger("click");
			});
		});
	}
})(jQuery,document);