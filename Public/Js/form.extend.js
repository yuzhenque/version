/**
 * 表单自动验证JQuery扩展JS类库
 * 扩展jQuery元素集来提供新的方法，如：$(this).is_empty()
 */
jQuery.fn.extend({
    _value: null,
    //获取值
    get_value: function () {
        if (this._value === null) {
            this._value = $.trim($(this).val());
        }

        return this._value;
    },
    //判断值是否为空
    is_empty: function () {
        return $(this).get_value() === '';
    },
    //检查数据的合法性
    match_data: function (name) {
        var value = $(this).get_value();
        //参考fn.extend.js
        return value != '' ? $.match_data(value, name) : true;
    },
    //检查数据的长度合法性，即最小长度、最大长度、固定长度
    eq_len: function (classname) {
        var value = $(this).get_value();
        //最小和最大，比如class属性为min_5 max_20
        var reg = new RegExp(/^(min|max)_\d+$/i);
        var cls = classname.split('_');
        cls[1] = $.implode('_', cls, 0);
        if (reg.test(classname)) {
            var len = parseInt(cls[1]);
            if (cls[0] == 'min') {
                if (len > value.length)
                    return "长度必须大于" + cls[1];
            } else if (len < value.length)
                return "长度必须小于" + cls[1];

            return true;
        }
        //lt_ gt_ egt_ elt_
        reg = new RegExp(/^(lt|gt|egt|elt)_\w+$/i);
        if (reg.test(classname)) {
            var $gl = $("#" + cls[1]).length > 0 ? $("#" + cls[1]) : $("input[name=" + cls[1] + "]");
            var thevalue = $._get_float_value($gl.length > 0 ? $.trim($gl.val()) : cls[1]);
            switch (cls[0]) {
                case 'gt':
                    if (thevalue >= $._get_float_value(value))
                        return "值必须大于" + ($gl.length > 0 ? $gl._getTitle() : cls[1]);
                    break;
                case 'egt':
                    if (thevalue > $._get_float_value(value))
                        return "值必须大于等于" + ($gl.length > 0 ? $gl._getTitle() : cls[1]);
                    break;
                case 'lt':
                    if (thevalue <= $._get_float_value(value))
                        return "值必须小于" + ($gl.length > 0 ? $gl._getTitle() : cls[1]);
                    break;
                case 'elt':
                    if (thevalue < $._get_float_value(value))
                        return "值必须小于等于" + ($gl.length > 0 ? $gl._getTitle() : cls[1]);
                    break;
            }

            return true;
        }
        //eq_，数据一致性判断
        reg = new RegExp(/^eq_.+?$/i);
        if (reg.test(classname)) {
            var $re = $("#" + cls[1]).length > 0 ? $("#" + cls[1]) : $("input[name=" + cls[1] + "]");
            var revalue = $.trim($re.val());
            if (revalue != value && revalue)
                return "同" + $re._getTitle() + "不一致";

            return true;
        }
        //neq_，数据不一致性判断
        reg = new RegExp(/^neq_.+?$/i);
        if (reg.test(classname)) {
            var $re = $("#" + cls[1]).length > 0 ? $("#" + cls[1]) : $("input[name=" + cls[1] + "]");
            var revalue = $.trim($re.val());
            if (revalue == value && revalue)
                return "同" + $re._getTitle() + "一致";

            return true;
        }
        return false;
    },
    //根据表单对应的id或name获取对应的label标签内容
    _getTitle: function () {
		var title = $(this).attr('alt');
        var name = $(this).attr("id");
        var name = name != '' && !name ? name : $(this).attr("name");
        var $label = new Object;
		
		if (title == '' || title == undefined) {
			if (name) {
				$label = $("label[for=" + name.replace(new RegExp(/[\[\]]*/g), '') + "]");
			}
			$label = $label.length > 0 ? $label : $(this).parent().prev();
			//stringObject.replace()
			title = $.trim($label.html()).replace(new RegExp(/[：: ]*/g), '');
		}
		
        return title;
    },
    //组装url地址参数
    _getParams: function () {
        var url = $(this).attr("src");
        var params = $(this).attr("lang");
        var _data = "";
        if (params && params != "") {
            params = params.split(",");
            var _len = params.length;
            if (_len > 0) {
                for (var i = 0; i < _len; i++) {
                    if ($("#" + params[i]).length > 0) {
                        _data += "&" + params[i] + "=" + $("#" + params[i]).val();
                    }
                }
            }
        }

        return {
            url: url && url != "" ? url : "",
            data: _data
        };
    },
    //检查数据的合法性
    checkData: function () {
        //取得第一个DOM元素，并对他直接操作，不通过jQuery函数，$(this).get(0)与$(this)[0]等价
        var classname = $(this)[0].className;

        if (classname == '')
            return false;
        var success = false;
        var title = $(this)._getTitle();

        if ($(this).hasClass("require_tag")) {
            if ($(this).is_empty()) {
                $(this)._msg('请至少选择一个栏目', 'error', true);
                return false;
            }
            success = true;
        } else if ($(this).hasClass("require")) {
            //判断表单必填字段是否为空
            if ($(this).is_empty()) {
                $(this)._msg(title + '不能为空', 'error');
                return false;
            }
            success = true;
        }

        var classes = classname.split(' ');
        var len = classes.length;
        //遍历每个元素的class属性
        for (var i = 0; i < len; i++) {
            if (classes[i] == 'require')
                continue;
            //匹配输入的字符长度，是否符合要求
            var _msg = $(this).eq_len(classes[i]);
            //_msg为提醒信息字符串
            if (_msg !== false) {
                if (_msg !== true) {
                    $(this)._msg(title + _msg, 'warn');
                    return false;
                }
                success = true;
            }
            //参考fn.extend.js
            if ($.regex[classes[i]]) {
                //正则检查数据合法性
                if (!$(this).match_data(classes[i])) {
                    $(this)._msg(title + '格式有误', 'warn');
                    return false;
                }
                success = true;
            }
        }

        //检查数值的唯一性
        if ($(this).hasClass("unique") && CHECK_UNIQUE) {
            //组装url参数
            var params = $(this)._getParams();
            //注意：要在form里面传参数，就必须在form里面id="cate"
            var act = $(this).parentsUntil('form #cate', 'form').attr('action');
            url = params.url != "" ? params.url : (act ? act : location.href);

            var $this = $(this);
            $.ajax({
                url: url,
                data: 'field=' + $(this).attr('name') + "&value=" + $(this).get_value() + "&unique=true" + params.data,
                dataType: 'json',
                type: 'POST',
                async: false,
                success: function (dat) {
                    if (dat) {
                        if (dat.status == 1) {
                            success = true;
                            $this._msg('OK!', 'success');
                        } else {
                            success = false;
                            $this._msg("该" + title + '已存在', 'error');
                        }
                    } else {
                        success = false;
                        $.warn("系统异常，请求验证失败", 'error');
                    }
                },
                error: function () {
                    success = false;
                    $.msgbox('系统出错，数据验证失败!', $.CODE_ERROR);
                }
            });
        } else if (success) {
            $(this)._msg('OK!', 'success');
        }
    },
    //在表单元素后显示提醒信息
    _msg: function (msg, _type, force) {
        var value = $(this).get_value();
        //不显示提醒信息
        if (!force && !$(this).hasClass("require") && value == '') {
            return;
        }
        _type = _type ? _type : 'info';
        $(this).nextAll(".DESC").hide();
        if ($(this).nextAll(".MSG_BOX").length > 0) {
            $(this).nextAll(".MSG_BOX").remove();
        }
        var $this = $(this).next(".JOIN_POS").length > 0 ? $(this).next(".JOIN_POS") : $(this);
        $this.after("<span class='MSG_BOX " + _type + "'>" + msg + "</span>");
    },
    //移出元素时提示
    delMe: function (func) {
        if (confirm("您确实要移除“" + $(this).text())) {
            $(this).remove();
            typeof func == 'function' ? eval(func()) : '';
        }
    },
    //提交排序表单
    orderForm: function (orderAction) {
        $(this).attr("action", orderAction ? orderAction : document.location.href).submit();
    }
});

//Jquery常用扩展库，扩展jQuery对象本身，在jQuery命名空间上增加新函数，如：$.match_data(value, name)
jQuery.extend({
    _items: null,
    _form: null,
    _icos: {'0': '&#x22;', '1': '&#x24;', '2': '&#x23;', '3': '&#x21;'},
    _submiting: false,
    CODE_SUCCESS: 1,
    CODE_ERROR: 0,
    CODE_WARN: 2,
    CODE_INFO: 3,
    //提交表单
    submitform: function (form) {
        this._form = form;
        this._items = $(form).find(".unique");
        if (this._items.length > 0) {
            this._chkunique(0);
        } else {
            this.toAjaxForm();
        }
    },
    //获得浮点数
    _get_float_value: function (value) {
        if ($.match_data(value, 'double')) {
            value = value.substr(0, 1) == '-' ? -parseFloat(value.substr(1)) : parseFloat(value);
        }

        return value;
    },
    //ajax提交表单
    toAjaxForm: function () {
        if ($(this._form).hasClass("toAjaxForm")) {
            if ($._submiting) {
                alert('网络不给力，数据还在处理中，请勿进行重复提交');
                return false;
            }
            $.ajax({
                url: $(this._form).attr("action"),
                type: "POST",
                dataType: "json",
                data: $(this._form).serialize(),
                beforeSend: function () {
                    $.loading("数据正在提交，请稍候...");
                    $._submiting = true;
                },
                success: function (dat) {
                    $.remove_loading();
                    $._submiting = false;
					
					var jsStr = '';
					var jsEndStr = '';
					var jumpUrl = dat.url;
					if(dat.status == 1){
						if(jumpUrl.indexOf("<js>") != -1){
							jsStr	= jumpUrl.substring(jumpUrl.indexOf("<js>") + 4, jumpUrl.indexOf("</js>"));
							jumpUrl	= jumpUrl.replace("<js>"+ jsStr +"</js>", '');
							eval(jsStr);
						}
						if(jumpUrl.indexOf("<jsend>") != -1){
							jsEndStr	= jumpUrl.substring(jumpUrl.indexOf("<jsend>") + 7, jumpUrl.indexOf("</jsend>"));
							jumpUrl	= jumpUrl.replace("<jsend>"+ jsEndStr +"</jsend>", '');
							eval(jsEndStr);
						}
					}
					if(jumpUrl != ''){
						$.msgbox(dat.info, dat.status, jumpUrl, dat.wait);
					}else{
						$.warn(dat.info);
					}
                },
                error: function () {
                    $.remove_loading();
                    $._submiting = false;
                    $.msgbox('系统出错，数据提交失败', this.CODE_ERROR);
                }
            });
        } else {
            $(this._form).submit();
        }
    },
    showmsg: function (message, url, wait) {
        if (typeof message == "object") {
            var len = message.length;
            var _msg = new Array;
            for (var i = 0; i < len; i++) {
                _msg[i] = $(message[i]).text();
            }

            message = _msg;
        }

        $.error(message, url, wait);
    },
    //检查数据的唯一性
    _chkunique: function (idx) {
        idx = parseInt(idx);
        if (idx >= this._items.length - 1) {
            this.toAjaxForm();
            return true;
        }

        var $this = $(this._items[idx]);
        var params = $this._getParams();
        var url = params.url != "" ? params.url : ($(this._form).action ? $(this._form).action : location.href);
        var _value = $this.get_value();
        var title = $this._getTitle();
        $.ajax({
            url: url,
            data: 'field=' + $this.attr('name') + "&value=" + _value + "&unique=true" + params.data,
            dataType: 'json',
            type: 'POST',
            beforeSend: function () {
                $.loading("数据正在验证中，请稍候...");
            },
            success: function (dat) {
                $.remove_loading();
                if (dat) {
                    if (dat.status == $.CODE_SUCCESS) {
                        $this._msg('OK!', 'success');
                        $._chkunique(idx + 1);
                    } else if (dat.status == $.CODE_ERROR) {
                        $this._msg("该" + title + '已存在', 'error');
                        $.showmsg("该" + title + '“' + _value + '”已存在');
                    } else {
                        $this._msg(dat.info, 'warn');
                        $.showmsg(dat.info);
                    }
                } else {
                    $.warn("系统异常，请求验证失败");
                }
            },
            error: function () {
                $.remove_loading();
                $.warn("系统异常，操作失败");
            }
        });
    },
    error: function (data, url, wait) {
        this.msgbox(data, this.CODE_ERROR, url, wait);
    },
    warn: function (data, url, wait) {
        this.msgbox(data, this.CODE_WARN, url, wait);
    },
    success: function (data, url, wait) {
        this.msgbox(data, this.CODE_SUCCESS, url, wait);
    },
    //消息弹出框
    msgbox: function (data, code, url, wait) {
        var message = '';
        if (typeof data == "object") {
            for (var i in data) {
                message += '<p>' + data[i] + '</p>';
            }
        } else {
            message = data;
        }

        if (typeof url == 'function') {
            var footer = '<span id="MSGBOX-OKBTN" class="ui-btn ui-btn-green">确 定</span>';
            if (wait !== false) {
                footer += ' <span id="MSGBOX-CANCLE" class="ui-btn">取 消</span>';
            }
        } else if (url === false) {
            var footer = '<span id="MSGBOX-CANCLE" class="ui-btn ui-btn-green">确 定</span>';
        } else if (url === 'reload') {
            var footer = '<span id="MSGBOX-CANCLE" class="ui-btn ui-btn-green" onclick="window.location.reload();">确 定</span>';
        } else {
            url = url ? url : document.location.href;
            wait = wait > 0 ? wait : 3;
            var footer = code == this.CODE_SUCCESS ? '系统 <b id="Alert_Wait">' + wait + '</b> 秒后将自动跳转， 不想等待请<a href="' + url + '">点击这里</a>' : '<span id="MSGBOX-CANCLE" class="ui-btn ui-btn-green">关 闭</span>';
        }
        //元素不存在
        if ($("#Alert_Containter").length <= 0) {
            var html = '<div class="Alert_Background"></div>' +
                    '<div class="Alert_Content">' +
                    '<div class="In_Content">' +
                    '<div class="header header_t_' + code + '">信息提示</div>' +
                    '<div class="container">' +
                    '<div id="Alert_Containter" class="color_' + code + '"></div><div class="footer">' + footer + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            $("body").append(html);
        }
        $(".Alert_Content .footer").html(footer);
        //信息填充
        $('.Alert_Background').height($(document).height());
        $("#Alert_Containter").html(message);
        $(".Alert_Content .In_Content").css("marginTop", -($(".Alert_Content .In_Content").outerHeight() / 2) + $(document).scrollTop() + "px");

        $("#MSGBOX-OKBTN").length <= 0 | $("#MSGBOX-OKBTN").unbind().click(function () {
            $._closeMsgbox();
            eval(url());
        });

        $("#MSGBOX-CANCLE").length <= 0 | $("#MSGBOX-CANCLE").unbind().click(function () {
            $._closeMsgbox();
            typeof wait == 'function' ? eval(wait()) : '';
        });

        if (code == this.CODE_SUCCESS && typeof wait != 'function' && typeof url != 'function') {
            this._autofunc(url);
        }
    },
    _closeMsgbox: function () {
        $('.Alert_Content').remove();
        $('.Alert_Background').fadeOut(function () {
            $(this).remove();
        });
    },
    _autofunc: function (url) {
        setTimeout(function () {
            var s = parseInt($("#Alert_Wait").text());
            if (s < 1) {
                s = 1;
            }
            $("#Alert_Wait").html('' + (s - 1));
            if (s == 1)
                document.location.href = url;
            $._autofunc(url);
        }, 1000);
    }
});