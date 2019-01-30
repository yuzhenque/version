/**
 *	按时间滚动Jquery插件1.0
 *	
 *	@author bluefoot<bluefoot@qq.com>
 *	@version 2012-08-05
 */
(function($){
	$.fn.TimeScroll = function(options){
		var setting = {
			childEl:'li', //内层元素。用来放图的层
			parentEl: '',
			per_left: 990,
			time:5 //滚动时间
		};
		if(options){
			$.extend(setting, options);
		};
		var self = this;
		var childEl = $(this).find(setting.childEl);
		var cur_id = 0;
		var will_left = 0;
		var per_left = setting.per_left;
		var count = childEl.size();
		var time = setting.time*1000;
		var method = 1;
		if(count <= 1){
			return;
		}
		var interval = setInterval(Scroll, time);
		
		$(this).width(count * setting.per_left);
		
		$(this).mouseover(function(){
			clearInterval(interval);
		});
		$(this).mouseout(function(){
			interval = setInterval(Scroll, time);
		});
		
		if(setting.parentEl != ''){
			for(var i=0; i<count; i++){
				$('#'+ setting.parentEl +'_'+ i).click(function(){
					Scroll($(this).attr('id').replace(setting.parentEl +'_', ''));
					clearInterval(interval);
					interval = setInterval(Scroll, time);
				});
			}
		}
		
		function Scroll(p_will){
			if(count > 0){
				p_will = p_will || null;
				if(p_will !== null){
					cur_id = p_will;
				}else{
					if(method == 1){
						cur_id++;
						if(cur_id >= count){
							method = 2;
							cur_id -= 2;
						}
					}else{
						cur_id--;
						if(cur_id < 0){
							method = 1;
							cur_id += 2;
						}
					}
				}
				will_left = 0 - cur_id * per_left;
				$(self).animate({'left': will_left+'px'}, 500, function(){
					if(setting.parentEl != ''){
						for(var i=0; i<count; i++){
							if(i == cur_id){
								$('#'+ setting.parentEl +'_'+ i).addClass('on');
							}else{
								$('#'+ setting.parentEl +'_'+ i).removeClass('on');
							}
						}
					}
				});
			}
		}
	};

})(jQuery);