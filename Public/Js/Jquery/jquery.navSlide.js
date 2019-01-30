/*
*	触屏标签左右拉动jquery插件 1.0
*	作者：ChaRmFree
	QQ	: 410232098
*	时间：2014.12.17
*/

(function($){
	$.fn.navSlide=function(options){
		var setting={
			slideWrap:'slide_wrap',
			slideNavLeft:0,		//开始的left值
			rebound:true,		//是否有回弹效果
			reboundSpeed:300,	//回弹的速度
			callback:function(index){
				// alert(index);
			}

		};
		if(options){$.extend(setting, options)};
			
		var winWidth=$(this).width();//获取窗口宽度
		
		var slideBox=$(this);//slide容器

		var slideNav=slideBox.children('.'+setting.slideWrap);//slideNav容器

		var slideWidth=(function(){//slideNav的总宽度
			var allWidth=0;
			slideNav.children('div').each(function(){
				allWidth+=$(this).outerWidth(true);
			});
			return allWidth;
		})();
		
		var Event = {
			addHandler: function (oElement, sEvent, fnHandler) {
				oElement.addEventListener ? oElement.addEventListener(sEvent, fnHandler, false) : oElement.attachEvent("on" + sEvent, fnHandler)
			},
			removeHandler: function (oElement, sEvent, fnHandler) {
				oElement.removeEventListener ? oElement.removeEventListener(sEvent, fnHandler, false) : oElement.detachEvent("on" + sEvent, fnHandler)
			}
		};
		
		var startX,startY,moveX,startLeft,endLeft;
		var isScrolling;//判断上下滑动还是左右滑动
		var isNum;//判断有没有执行过
		
		//选项标签总宽度大于屏幕宽度，才设置left
		if(slideWidth>winWidth && setting.slideNavLeft>=0){
			slideNav.css('left',setting.slideNavLeft);
		}else{
			slideNav.css('left',0);
		}
		slideNav.css('width',slideWidth);
		
		//选项标签总宽度大于屏幕宽度,才可以拖拽
		if(slideWidth>winWidth){
			if(isPc()){
				slideBox[0].addEventListener('mousedown',dragStart,false);
			}else{
				slideBox[0].addEventListener('touchstart',dragStart,false);
			}
		}
		
		//拖拽开始
		function dragStart(e){
			if(isPc()){
				e= e || window.event; 
				var mousePos = mouseCoords(e); 
				startX = mousePos.x; 
				startY = mousePos.y; 
			}else{
				startX = e.touches[0].pageX;
				startY = e.touches[0].pageY;
			}
			startLeft=parseInt(slideNav.css('left'));
			isScrolling=false;
			isNum=1;
			if(isPc()){
				slideBox[0].addEventListener('mousemove',dragMove,false);
				slideBox[0].addEventListener('mouseup',dragEnd,false);
			}else{
				slideBox[0].addEventListener('touchmove',dragMove,false);
				slideBox[0].addEventListener('touchend',dragEnd,false);
			}
		}
		//拖拽移动
		function dragMove(e){
			var distance;
			if(isPc()){
				e= e || window.event; 
				var mousePos = mouseCoords(e);
				distance={
					x:mousePos.x - startX,
					y:mousePos.y - startY
				};
			}else{
				distance={
					x:e.touches[0].pageX-startX,
					y:e.touches[0].pageY-startY
				};
			}
			
			if(isNum==1){
				if(Math.abs(distance.x)>Math.abs(distance.y)) isScrolling=true;
				isNum=2;
			}
			if(isScrolling){
				e.preventDefault();
				moveX = distance.x + startLeft;
				slideNav.css('left',moveX);

				if(!setting.rebound){//回弹效果
					slideBox[0].ontouchend=null;
					if(moveX>=0){
						slideNav.css('left',0);
					}
					if(moveX<=winWidth-slideWidth){
						slideNav.css('left',winWidth-slideWidth);
					}
				}

			}
		}
		//拖拽结束
		function dragEnd(e){
			if(isPc()){
				slideBox[0].removeEventListener('mousemove',dragMove,false);
				slideBox[0].removeEventListener('mouseup',dragEnd,false);
			}else{
				slideBox[0].removeEventListener('touchmove',dragMove,false);
				slideBox[0].removeEventListener('touchend',dragEnd,false);
			}
			if(isScrolling){
				endLeft=parseInt(slideNav.css('left'));
				if(endLeft>=0){
					slideNav.animate({left:0},setting.reboundSpeed);
				}else if(endLeft<=winWidth-slideWidth){
					slideNav.animate({'left':winWidth-slideWidth},setting.reboundSpeed);
				}
			}
			
		}
		
		/**
		 * 判断是否PC
		 * @returns {Boolean}
		 */
		function isPc()
		{
			var userAgentInfo = navigator.userAgent;
			var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
			var flag = true;
			for (var v = 0; v < Agents.length; v++) {
				if (userAgentInfo.indexOf(Agents[v]) > 0) {
					flag = false;
					break;
				}
			}
			return flag;
		}
		function mouseCoords(ev)
		{
			if (ev.pageX || ev.pageY) {
				return {x: ev.pageX, y: ev.pageY};
			}
			return {
				x: ev.clientX + document.body.scrollLeft - document.body.clientLeft,
				y: ev.clientY + document.body.scrollTop - document.body.clientTop
			};
		} 
		//标签点击回调函数
		slideNav.children('div').click(function(){
			(setting.callback)($(this).index())
		})
	}

})(jQuery)