//Jquery常用扩展库，扩展jQuery对象本身，在jQuery命名空间上增加新函数，如：$.match_data(value, name)
jQuery.extend({
    //json对象即关联数组，正则匹配规则
	regex : {
		'require'  : /.*/, //匹配任意字符，除了空和断行符
		'email'    : /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,  //电子邮件
		'phone'    : /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/, //固定电话
		'mobile'   : /^((\(\d{2,3}\))|(\d{3}\-))?(13|15|18|14|17)\d{9}$/,  //移动手机号码
		'url'      : /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/,  //url地址
		'domain'   : /^[A-Za-z0-9]+(\.[A-Za-z0-9]+)+$/, //域名
		'currency' : /^\d+(\.\d+)?$/,  //货币
		'number'   : /^\d+$/,           //纯数字
		'zip'      : /^[1-9]\d{5}$/,
		'qq' 	   : /^[1-9]\d{4,12}$/,       //QQ号码
		'integer'  : /^[-\+]?\d+$/,           //整数
		'double'   : /^[-\+]?\d+(\.\d+)?$/,   //双整型
		'english'  : /^[A-Za-z]+$/,           //纯英文字符
		'string'   : /^[A-Za-z_]+[0-9_a-zA-Z]*$/,    //以英文开头的英文和数字组成的字符串
		'dir'      : /^[A-Za-z_]+[0-9_a-zA-Z\/]*$/,
		'ip' 	   : /^(\d+)\.(\d+)\.(\d+)\.(\d+)$/, //ip地址
		'card'     : /^(\d{14}|\d{17})(\d|[xX])$/,   //匹配身份证
        'license'  : /^\d{15}$/, //匹配15位营业执照注册号
		'birth'    : /^\d{4}.{1}\d{1,2}.{1}\d{1,2}/,  //匹配生日
		'method'   : /^[A-Za-z_]+[0-9_a-zA-Z]*(\:|：).+$/,
		'username' : /^[A-Za-z_]+[0-9_a-zA-Z]*[0-9a-zA-Z]+$/
	},
	
	//匹配数据的合法性
	match_data : function(value, name, flags){
		var pattern = this.regex[name] ? this.regex[name] : this.regex['require'];
		var reg = new RegExp(pattern, flags);
		return reg.test(value);
	},
	
	//生成一个在min到max之间的随机数
	randnum : function(min,max){
		 var Range = max - min;
	     var Rand = Math.random();
	     return(min + Math.round(Rand * Range));
	},
	
	//设置cookie
	setcookie : function(name,value,expire){
		expire = expire>0 ? expire : 0;
	    var exp  = new Date();    //new Date("December 31, 9998");
	    exp.setTime(exp.getTime() + expire*24*60*60*1000);
	    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	},
	
	//获取cookie值
	getcookie : function(name){
		var arr,reg=new RegExp("(^|)"+name+"=([^;]*)(;|$)");  
	    if(arr=document.cookie.match(reg))
	    	return unescape(arr[2]);
	    else
	    	return null;  
	},
	
	//删除cookie
	delcookie : function(name){
		var exp = new Date();
	    exp.setTime(exp.getTime() - 1);
	    var cval=$.getcookie(name);
	    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
	},
	
	//全选或反选复选框
	checkbox : function(o,container,v){
		var ck = o.checked;
		if(ck == true || $(o).attr('checked') == 'checked' || v){
			$(container+' input[type=checkbox]').each(function(){
				if(!$(this).attr("disabled"))$(this).attr('checked',true)
			});
		}
		else if(ck==false || !v)
			$(container+' input[type=checkbox]').attr('checked',false);
	},
	
	//过滤获取文件名
	getFileName : function(path){
		path = path.split("/");
		var l = path.length;
		if(l>1) return path[l-1];
		else return path[0];
	},
	
	//过滤获取文件后缀名
	getFileExt : function(file){
		file = file.split(".");
		var l = file.length;
		if(l>1) return '.'+file[l-1];
		else return -1;
	},
	
	//过滤html标签
	strip_tags : function(str){
		return str.replace(/<.*?>/g,"");
	},
	
	//将数组使用某个字符进行连接成字符串
	implode : function(glue, array, ignore){
		var values = '';
		if($.isArray(array)){
			$.each(array, function(i, n){
				values += ignore!=undefined && ignore == i ? '' : ((values=='' ? '' : glue) + n);
			});
		}
		return values;
	}
});

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        var path = options.path ? '; path=' + options.path : '';
        var domain = options.domain ? '; domain=' + options.domain : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

//点击滚动到指定ID
(function ($) {
    $.scrollToID = $.fn.scrollToID = function(id, gap){
        gap = !isNaN(Number(gap))? gap : 50;
        var x = $(id).offset().left + $(this).scrollLeft() - gap + 0;
        var y = $(id).offset().top + $(this).scrollTop() - gap + 0;

        if (!(this instanceof $)) return $.fn.scrollToID.apply($('html, body'), arguments);

        return $(this).stop().animate({
            scrollLeft: x,
            scrollTop: y
        }, 800);
    };
})(jQuery);