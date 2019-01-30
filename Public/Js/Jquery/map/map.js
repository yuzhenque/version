/**
 * JQuery百度地图扩展
 */
document.write('<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.3"></script>');

(function($,doc){
	$.fn.baidu = function(settings){
		var _id = $(this).attr("id");
		if(_id==""){
			_id = "MAP_" + (new Date().getTime());
			$(this).attr({id:_id});
		}
		
		//地图控件摆放位置
		var _anchor = [
		    BMAP_ANCHOR_TOP_LEFT,	//0左上角
		    BMAP_ANCHOR_TOP_RIGHT,	//1右上角
		    BMAP_ANCHOR_BOTTOM_LEFT,//2左下角
		    BMAP_ANCHOR_BOTTOM_RIGHT//3右下角
		];
		
		//地图缩放平移控件显示外观
		var _types = [
			BMAP_NAVIGATION_CONTROL_LARGE, 	//表示显示完整的平移缩放控件。
			BMAP_NAVIGATION_CONTROL_SMALL, 	//表示显示小型的平移缩放控件。
			BMAP_NAVIGATION_CONTROL_PAN, 	//表示只显示控件的平移部分功能。
			BMAP_NAVIGATION_CONTROL_ZOOM,	//表示只显示控件的缩放部分功能。
		];
		
		//显示地图类型配置参数
		var _mapTypes = {
			mapTypes : [BMAP_NORMAL_MAP,BMAP_HYBRID_MAP],
			anchor : _anchor[1]
		};
		
		var defaults = {
			zoom : 16,			//默认打开地图缩放级别
			xpoint : 118.07408,	//默认中心点经度
			ypoint : 24.45124,	//默认中心点纬度
			marker : false,		//是否在地图上显示以中心点为坐标的标注点
			minZoom : 1,		//最小缩放级别
			maxZoom : 19,		//最大缩放级别
			scroll : true,		//是否启用滚动鼠标滚轮进行缩放地图的功能
			nvgat : {
				show : false,	//是否显示缩放平移控件，true显示
				anchor : 0,		//摆放位置(0左上角，1右上角，2左下角，3右下角)
				type : 0		//显示外观
			},
			maptype : false,	//是否显示地图类型控件
			overview : false
		};
		
		settings = $.extend(defaults, settings);
		
		var map = $.baiduMap = new BMap.Map(_id, {minZoom:settings.minZoom,maxZoom:settings.maxZoom});
//		map.addEventListener("tilesloaded",function(){
//			$("#"+_id).css({background:"#A6C2DE"});
//		});
		
		if(settings.scroll){
			map.enableScrollWheelZoom();
		}
		
		//显示缩放平移控件
		if(settings.nvgat.show){
			map.addControl(new BMap.NavigationControl({anchor:_anchor[settings.nvgat.anchor],type:_types[settings.nvgat.type]}));
		}
		
		//显示地图类型控件
		if(settings.maptype){
			map.addControl(new BMap.MapTypeControl(_mapTypes));
		}
		
		//显示缩略图控件
		if(settings.overview){
			map.addControl(new BMap.OverviewMapControl({isOpen:true, anchor: _anchor[2]}));   
		}
		
		map.centerAndZoom(new BMap.Point(settings.xpoint, settings.ypoint), settings.zoom);
		
		if(settings.marker){
			$.addPoint(settings.xpoint, settings.ypoint);
		}
	}
})(jQuery,document);

jQuery.extend({
	//百度地图对象
	baiduMap : null,
	
	mapLabel : null,
	
	//手绘地图图层对象
	handLayer : null,
	
	icons : {main:1,food:1,lodging:1,music:1,scene:1,service:1,shopping:1},
	
	/**
	 * 向地图增加一个标注点
	 * label : 地图上显示文字说明
	 */
	addPoint : function(xpoint,ypoint,label,img){
		if(this.baiduMap === null) return false;
		var marker = new BMap.Marker(new BMap.Point(xpoint, ypoint));
		this.baiduMap.addOverlay(marker);
		if(label){
			marker.setLabel(new BMap.Label(label,{offset:new BMap.Size(20,-10)}));
		}
		
		return marker;
	},
	
	//向地图添加一个覆盖物，行程成功后返回新增覆盖层DIV对象
	addLabel : function(settings){
		if(this.baiduMap === null) return false;
		if(this.mapLabel===null){
			this.mapLabel = this.mapOverlay();
		}
		
		//默认配置
		var defaults = {
			xpoint : 118.07408,
			ypoint : 24.45124,
			width : 60,
			height : 80,
			drag : false, //是否启用拖拽功能
			data : null
		};
		
		settings = $.extend(defaults, settings);
		
		if(settings.data === null) return false;
		var point = new BMap.Point(settings.xpoint, settings.ypoint); 
		var newLabel = new this.mapLabel(point,settings.width,settings.height,settings.data,settings.drag);
		this.baiduMap.addOverlay(newLabel);
		
		//返回新增覆盖层DIV对象
		return newLabel.getDiv();
	},
	
	//创建一个覆盖物对象
	mapOverlay : function(){
		if(this.baiduMap === null) return false;
		//自定义覆盖物的构造函数
		var mapOverlay = function(center,width,height,data,drag){
			this._center = center;  
			this._width = width;
			this._height = height;
			this._data = data;
			this._drag = drag;
		};
		
		//继承API的BMap.Overlay 
		mapOverlay.prototype = new BMap.Overlay();
		
		//进行初始化设置
		mapOverlay.prototype.initialize = function(map){
			this._map = map;
			
			//创建div元素，作为自定义覆盖物的容器  
			var div = document.createElement("div");  
			div.className = "mapLabel";
			div.style.width = this._width + "px";
			div.style.height  = this._height + "px";
			if(this._data){
				div.innerHTML = this._data + "<div class='dir'></div>";
			}
			var _drag = this._drag;
			div.onmouseover = function(){
				if(!_drag) $.baiduMap.disableDragging();
			};
			div.onmouseout = function(){
				if(!_drag) $.baiduMap.enableDragging();
			};
			
			//将div添加到覆盖物容器中  
			map.getPanes().markerPane.appendChild(div);
			
			this._div = div;
			
			 //需要将div元素作为方法的返回值，当调用该覆盖物的show、  
			 //hide方法，或者对覆盖物进行移除时，API都将操作此元素。
			return div;
		};
		
		//绘制覆盖物
		mapOverlay.prototype.draw = function(){
			 //根据地理坐标转换为像素坐标，并设置给容器  
			 var pos = this._map.pointToOverlayPixel(this._center);  
			 this._div.style.left = pos.x - (this._width+12)/2 - 9 + "px";  
			 this._div.style.top = pos.y - this._height - 28 + "px";  
		};
		
		//实现显示方法  
		mapOverlay.prototype.show = function(){
			if (this._div){
				this._div.style.display = "";
			}
		};
		
		//实现隐藏方法  
		mapOverlay.prototype.hide = function(){
			if (this._div){
				this._div.style.display = "none";  
			}
		};
		
		//返回新增层对象
		mapOverlay.prototype.getDiv = function(){
			return this._div;
		};
		
		return mapOverlay;
	},
	
	//设置中心点
	setCenter : function(xpoint,ypoint){
		if(this.baiduMap === null) return false;
		this.baiduMap.setCenter(new BMap.Point(xpoint, ypoint));
	},
	
	//将地图中心点动态移动到指定的点
	panTo : function(xpoint,ypoint){
		if(this.baiduMap === null) return false;
		this.baiduMap.panTo(new BMap.Point(xpoint, ypoint));
	},
	
	//移除地图上所有自定义的覆盖物
	clearOverlay : function(){
		if(this.baiduMap === null) return false;
		this.baiduMap.clearOverlays();
	},
	
	//获取地图当前缩放级别
	getZoom : function(){
		if(this.baiduMap === null) return false;
		return this.baiduMap.getZoom();
	}
});