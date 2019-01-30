/**
 * 自定义弹窗
 */
var destory_uploadfy_obj = null;
function mywin(container, tags, func){
	var overlay = "MYWIN_OVERLAY";
	var mywin	= "MYWIN_BOX";
	var $win = $(window.document.body);
	
	//移除窗口
	var remove = function(){
		if(destory_uploadfy_obj){
			$(destory_uploadfy_obj).uploadify('destroy');
			destory_uploadfy_obj = null;
		}
		$("."+overlay).remove();
		$("#"+mywin).remove();
		if($win.find("."+overlay).length>0){
			$win.find("."+overlay).remove();
		}
		
		if(jQuery.browser.msie && jQuery.browser.version < 7){
			$("body select").removeClass("hideSelect");
		}
	};
	
	//调整窗口位置，使其自适应屏幕
	var position = function(){
		var config = {position : "absolute",left : 0, top: 0, background : "#000000",opacity : "0.3",zIndex : 1000000};
		var height = 0,width = 0;
		if($win.find(".frame-top").length>0){
			height = $win.find(".frame-top").outerHeight();
			width = $win.find(".frame-main-nav").outerWidth();
			$win.find("."+overlay).css(config);
			$win.find("#MYWIN_TOP").css({top:0,height:height+"px",width:"100%"});
			$win.find("#MYWIN_LEFT").css({top:height+"px",height:$win.find("#frame-main").height()+"px",width:width+"px"});
		}
		$("#"+overlay).css(config).css({top:0, left:0, width:"100%", height: $('body').height() > $(window).height() ? $('body').height() : $(window).height()});
		$("#"+mywin).css({position : "absolute", left:($(window).width() - $("#"+mywin).width())/2 + 'px', top: (($(window).height() - $("#"+mywin).height())/2 + $(window).scrollTop()) + 'px', zIndex : 1000001,background:"#fff"});
		
		$("."+overlay).show();
		$("#"+mywin).show();
		
		$("#MYWIN_BOX .mywin-title").width($("#MYWIN_BOX").width()-20);
	};
	
	var show = function(_t){
		if($win.find("."+overlay).length<=0){
			if($win.find(".frame-top").length>0){
				$win.append("<div id='MYWIN_TOP' class='"+overlay+"'></div><div id='MYWIN_LEFT' class='"+overlay+"'></div>");
				$win.find("."+overlay).click(remove);
			}
			
			var tagHtml = '', idx = 0;
			
			if(tags){
				tagHtml = "<ul class='MYWIN-TAG'>";
				for(var tagid in tags){
					tagHtml += '<li'+(idx == 0 ? ' class="ui-tag-cur" ' : '')+' lang="#' + tagid + '">' + tags[tagid] + '<span></span></li>';
					idx ++;
				}
				tagHtml += "</ul>";
			}else{
				tagHtml = "<span title='关闭' class='close'></span>";
			}
			
			$("body").append("<div class='"+overlay+"' id='"+overlay+"'></div><div id='"+mywin+"'><div class='mywin-title'>" + _t + tagHtml + "</div><div class='mywin-box'></div></div>");
			
			if(tags){
				//标签切换
				$(".MYWIN-TAG li").click(function(){
					if(!$(this).hasClass("ui-tag-cur")){
						var $cur = $(".MYWIN-TAG li.ui-tag-cur");
						$($cur.attr("lang")).hide();
						$cur.removeClass("ui-tag-cur");
						$(this).addClass("ui-tag-cur");
						$($(this).attr("lang")).show();
						typeof func == 'function' ? eval(func()) : '';
					}
				});
			}
			
			$("#"+overlay).click(remove);
			$("#"+mywin+" .close").click(remove);
			
			if(jQuery.browser.msie && jQuery.browser.version < 7){
				$("body select").addClass("hideSelect");
			}
		}
		position();
		
		$(window).resize(function(){
			position();
		});
	};
	
	var init = function(_container){
		$(_container ? _container : container).each(function(){
			wininit(this);
		});
	};
	
	var wininit = function(_container){
		var $container = $(_container);
		var eventName = $container.attr("action") || $container.attr("method") ? 'submit' : 'click';
		$container.unbind().die();
		$container.bind(eventName, function(){
			var t = this.title || $(this).html();
			if($("#"+mywin).length<=0){
				show(t);
				$("."+overlay).hide();
				$("#"+mywin).hide();
			}
			var a = $(this).attr('href') || $(this).attr('rel') || $(this).attr('alt') || $(this).attr('action');
			var reg = new RegExp(/\.(jpg|jpeg|png|gif|bmp)/i);
			//关闭弹出框
			if(reg.test(a.split(a.length-5,5))){
				$("#MYWIN_BOX .mywin-title").remove();
				$("#MYWIN_BOX .mywin-box").html("<img title='点击关闭' style='cursor:pointer;' src='"+a+"' />");
				$("#MYWIN_BOX .mywin-box img").click(remove);
				position();
				$("#MYWIN_BOX").css({"background":"none",border:0});
				return false;
			}
			if(destory_uploadfy_obj){
				$(destory_uploadfy_obj).uploadify('destroy');
				destory_uploadfy_obj = null;
			}
			$.loading('加载中......');
			$.ajax({
				url : a,
				type : $container.attr("method") ? $container.attr("method") : 'GET',
				data : eventName=='submit' ? $container.serialize() : '',
				success : function(dat){
					if(typeof dat == "object"){
						$.msgbox(dat.data,dat.code,function(){
							document.location.href = dat.url;
						});
						remove();
					}else{
						$("#MYWIN_BOX .mywin-box").html(dat);
						
						//绑定表单自动验证事件
						$("#MYWIN_BOX input,#MYWIN_BOX select,#MYWIN_BOX textarea").blur(function(){
							CHECK_UNIQUE = true;
							$(this).checkData();
						}).focus(function(){
							if($(this).nextAll(".DESC").length>0){
								$(this).nextAll(".DESC").show();
								$(this).nextAll(".MSG_BOX").remove();
							}
						});
						
						position();
						init("#MYWIN_BOX .mywin-box .mywin");
						
					}
					$.remove_loading();
				},
				error : function(){
					remove();
					$.error('系统出错，操作失败');
				}
			});
			return false;
		});
	};
	//前面是函数定义，这里是函数调用
	init();
}
//文档加载完毕时调用函数
$(function(){
	mywin(".mywin");
});