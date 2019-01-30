/**
 * ------------------------------------
 * 弹框组件
 * 
 * @Example:
 * 1.<a href="http://baidu.com?width=100&height=100" class="thickbox">打开百度</a>
 * 2.<a href="javascript:void(0);" onclick="tb_show('http://baidu.com', 'width=100&height=100')">打开百度</a>
 * 
 * 若URL直接为参数串
 * '?width=1&height=1&TB_inner=1&inlineId=HTML_ID'
 * TB_inner=true: 代表不打开链接，直接赋HTML在页面中
 * inlineId=HTML_ID：若此值为页面中的ID，则会把此ID的子节点，全部转到本次弹框。若无值，则把p_inner值附值
 * 若包含：TB_top=1，则会在最顶层打开窗体
 * 
 * 其中
 * 1.<a></a> 也可以是 <area></area> <input/> <li></li>
 * 2.href="http://baidu.com"。可写在alt="http://baidu.com"。同样可识别
 * ------------------------------------
 */

/**
 * 窗口是否加载完
 */
var winonload;

/**
 * 已生成的窗口的最大ID
 */
var winid = 1;

$(document).ready(function(){
	if(winonload == "loaded" ){
		return;
	}
	winonload = 'loaded';
	tb_init('a.thickbox, area.thickbox, input.thickbox, li.thickbox');
});


/**
 * 初始化
 * 
 * @param String p_domChunk	要自动弹框的包含class的dom['a.thickbox, area.thickbox, input.thickbox']
 */
function tb_init(p_domChunk){
	$(p_domChunk).click(function(){
		var a = this.href || this.alt;
		var c = false;
		var d = this.title;
		tb_show(a, c, 0, d);
		this.blur();
		return false;
	});
}

/**
 * 打开弹框
 * 
 * @param String	url			需要在弹框打开的URL
 * @param String	inner		一些附属参数
 * @param int		p_autoClose	自动关闭时长
 * @param String	p_title		默认标题
 */
function tb_show(p_url, p_inner, p_autoClose, p_title) {
	var queryString = p_url.replace(/^[^\?]+\??/,'');
    var params = tb_parseQuery( queryString );

	p_autoClose = p_autoClose || 0;
	p_title		= p_title || '';
	
	//如果有置顶属性，则在最顶层打开
	if(params['TB_top']){
		p_url = p_url.replace('&TB_top=1', '');
		p_url = p_url.replace('TB_top=1&', '');
		p_url = p_url.replace('TB_top=1', '');
		window.top.tb_show(p_url, p_inner);
		return;
	}
	
	try {

		if(document.getElementById("bodyInner") === null){
			var newElement = document.createElement('div');
			newElement.id = 'bodyInner';
			document.body.insertBefore(newElement,document.body.firstChild);

		}

		if(document.getElementById("TB_overlay") === null){
			$("body").append("<div id='TB_overlay' onclick=\"tb_remove('TB_window_"+winid+"');$.event.fix(event).stopPropagation();\"></div>");
		}
		
		if (typeof document.body.style.maxHeight === "undefined") {
			if(document.getElementById("TB_HideSelect") === null){
				$("body").append("<iframe id='TB_HideSelect'></iframe>");
			}
		}
		
		if( !document.getElementById("TB_window_"+winid) ){
			$("#bodyInner").append("<div id='TB_window_"+winid+"' class='TB_window'></div>");
		}

		if(document.getElementById('win_txt')!= undefined && document.getElementById('win_txt').style.display != 'none'){
			document.getElementById('win_txt').style.display = 'none';
		}
		if(tb_detectMacXFF()){
			$("#TB_overlay").addClass("TB_overlayMacFFBGHack");
		}else{
			$("#TB_overlay").addClass("TB_overlayBG");
		}

		var urlNoQuery = p_url.split('TB_');
	
		TB_WIDTH  = parseInt(params['width']) || 800;
		TB_HEIGHT = parseInt(params['height']) || 550;
		TB_TOP  = params['top'];
		TB_LEFT = params['left'];

		if( urlNoQuery[0] == '?' || urlNoQuery[0] == '#' ){
			urlNoQuery[0] = urlNoQuery[0].replace('?', '');
			urlNoQuery[0] = urlNoQuery[0].replace('#', '');
		}
		
		if(p_title != ''){
			if(urlNoQuery[0].indexOf('?') != -1){
				urlNoQuery[0] += '&tb_title='+ p_title;
			}else{
				urlNoQuery[0] += '?tb_title='+ p_title;
			}
		}
		
		//检查是否为图片
		var perfix = urlNoQuery[0].substring(urlNoQuery[0].indexOf('.')+1);

		var regExp=/(jpg|jpeg|gif|png)/gi;

		if(perfix.match(regExp)){
			 var i = new Image(); //新建一个图片对象
             i.src = urlNoQuery[0];//将图片的src属性赋值给新建的图片对象的src
			 if(i.height > 500){
				 TB_WIDTH = i.width * (500 / i.height);
				 TB_HEIGHT = 500;
			 }else if(i.height < 500 && i.width < $(document).width()){
				TB_HEIGHT = i.height;
				TB_WIDTH = i.width;
			 }
			 delete i;
		}
		
		//设置为居中
		var clientWidth  = parseInt(document.documentElement.clientWidth);
		var clientHeight = parseInt(document.documentElement.clientHeight);
		if(!params['TB_center']){
			if( !TB_TOP ){
				//$('#TB_window_'+winid).css('top', ($('body').height() - TB_HEIGHT)/2);
				var bodyHeight = parseInt($(document).height());
				var scrollTop = parseInt($(document).scrollTop());
				
				if(clientHeight > TB_HEIGHT){
					TB_TOP = (clientHeight - TB_HEIGHT)/2 + scrollTop - 6;
				}else if(clientHeight < TB_HEIGHT){
					TB_TOP = 10 + scrollTop;
				}else if(TB_HEIGHT + scrollTop > bodyHeight){
					TB_TOP = bodyHeight - TB_HEIGHT;
				}else{
					TB_TOP = 130 + scrollTop;
				}
//				alert(TB_TOP +'|'+ bodyHeight + '|' + TB_HEIGHT + '|' +scrollTop + '|' +clientHeight);
			}
			if( !TB_LEFT ){
				TB_LEFT = (clientWidth - TB_WIDTH)/2;
			}
		}
		//默认
		else{
			TB_TOP		= 105;
			TB_LEFT		= 182;
			TB_WIDTH	= clientWidth - TB_LEFT;
			TB_HEIGHT	= clientHeight - TB_TOP;
		}
//		alert(TB_TOP +'|'+ TB_LEFT +'|'+ TB_WIDTH +'|'+ TB_HEIGHT);

	
		var winCon = "<div class='winbox'>";
		//winCon += "<div class='winbox_top'>";
		//winCon += "	<div class='winbox_top_l'></div>";
		//winCon += "	<div id='winbox_top_m' class='winbox_top_m' style='width:"+ (TB_WIDTH-0) +"px'></div>";
		//winCon += "	<div class='winbox_top_r'></div>";
		//winCon += "</div>";
		winCon += "<div id='winbox_con_id' class='winbox_con' style='width:"+ (TB_WIDTH) +"px'>";
		if(perfix.match(regExp)){
			winCon += "	<a class='menu_close3' onclick=\"tb_remove('TB_window_"+winid+"');$.event.fix(event).stopPropagation();\" id='TB_closeWindowButton' title='关闭'></a><div class='winbox_con_r relative' id='box_inner' style='width:"+ (TB_WIDTH) +"px'><img src='"+urlNoQuery[0]+"' id='TB_iframeContent' scrolling='no' name='TB_iframeContent"+Math.round(Math.random()*1000)+"' style='width:"+ TB_WIDTH +"px;height:"+ TB_HEIGHT +"px;'/></div>";
		}else{
			winCon += "	<a class='menu_close3' onclick=\"tb_remove('TB_window_"+winid+"');$.event.fix(event).stopPropagation();\" id='TB_closeWindowButton' title='关闭'></a><div class='winbox_con_r relative' id='box_inner' style='width:"+ (TB_WIDTH) +"px'><iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='TB_iframeContent' scrolling='no' name='TB_iframeContent"+Math.round(Math.random()*1000)+"' style='width:"+ TB_WIDTH +"px;height:"+ TB_HEIGHT +"px;' allowtransparency='false'></iframe></div>";
		}
		winCon += "</div>";
		//winCon += "<div class='winbox_btm'>";
		//winCon += "	<div class='winbox_btm_l'></div>";
		//winCon += "	<div id='winbox_btm_m' class='winbox_btm_m' style='width:"+ (TB_WIDTH-0) +"px'></div>";
		//winCon += "	<div class='winbox_btm_r'></div>";
		//winCon += "</div>";
		winCon += "</div>";
	
		$("#TB_window_"+winid).html(winCon);
	
		if(p_url.indexOf('TB_inner') != -1){

			$("#box_inner").html('<div id="TB_ajaxContent"'+ (params['TB_padding']=='false' ? ' style="padding:0"' : '') +'></div>');
			if(p_inner){
				$("#TB_ajaxContent").html(p_inner);
			}else{
				$("#TB_ajaxContent").html($('#' + params['inlineId']).html());
			}
			$("#TB_ajaxContent").height(TB_HEIGHT);
		}

		$('#TB_window_'+winid).css('top', TB_TOP +'px');
		$('#TB_window_'+winid).css('left', TB_LEFT+'px');
		
		$('#TB_window_'+winid+' > div, #TB_window_'+winid+' > div > div').css('width', TB_WIDTH+'px');
		$('#TB_window_'+winid+' div#TB_ajaxContent').css('width', TB_WIDTH +'px');
//		alert($("#TB_window_"+winid).html());

		//如果有两窗口并列，且宽度一致时，要使两窗口重合
		if($('#TB_window_'+ (winid - 1)).length > 0 && $('#TB_window_'+ winid).width() == $('#TB_window_'+ (winid - 1)).width()){
			$('#TB_window_'+winid).css('left', $('#TB_window_'+ (winid - 1)).css('left'));
		}
	
		$("#TB_closeWindowButton").click(tb_remove);
//		$("#TB_ajaxContent").append($('#' + params['inlineId']).children());
		if(p_url.indexOf('TB_inner') != -1){
			$("#TB_window_"+winid).unload(function () {
				$('#' + params['inlineId']).append( $("#TB_ajaxContent").children() );
			});
		}
		$("#TB_load").remove();
//		$("#TB_window_"+winid).animate({'left': TB_LEFT}, 500);
		$("#TB_window_"+winid).fadeIn(500);

		if(p_autoClose > 0){
			setTimeout('tb_remove("TB_window_'+ winid +'");', p_autoClose * 1000);
		}
		winid++;
	} catch(e) {
		alert(e);
	}
}

function thisclose(){	
	tb_remove();
}

function tb_remove(domId) {
	$("#TB_closeWindowButton").unbind("click");
	
	if (domId == null){
		domId = 'TB_window_'+ (winid-1);
	}

	if(domId == 'all'){
		$(".TB_window").remove();
	}else{
		$("#"+ domId).remove();
	}

	$('#TB_HideSelect').remove();
	$('#TB_overlay').remove();
	$("#TB_load").remove();
	if (typeof document.body.style.maxHeight == "undefined") {

	}
	$('#fullMail').hide();
	
	return false;
}
function tb_position() {

}
function tb_parseQuery ( query ) {
	var Params = {};
	if ( ! query ) {
		return Params;
	}
	var Pairs = query.split(/[;&]/);
	for ( var i = 0; i < Pairs.length; i++ ) {
		var KeyVal = Pairs[i].split('=');
		if ( ! KeyVal || KeyVal.length != 2 ) {
			continue;
		}
		var key = unescape( KeyVal[0] );
		var val = unescape( KeyVal[1] );
		val = val.replace(/\+/g, ' ');
		Params[key] = val;
	}
	return Params;
}
function tb_getPageSize(){
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	arrayPageSize = [w,h];
	return arrayPageSize;
}
function tb_detectMacXFF() {
	var userAgent = navigator.userAgent.toLowerCase();
	if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox')!=-1) {
		return true;
	}
}
function tb_strreplace( str ){
	if( str == "" || str == null || str == 0 ){
		return "";
	}
	str = str.replace(/\[(.*?)\]/g, '<span style="color:#fa9611">$1<\/span>');
	return str;
}