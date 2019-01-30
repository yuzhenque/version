/**
 * JS初始化设置
 */
var CHECK_UNIQUE = true;

//当前页面一组副文本编辑器对象
var FORM_EDITORS = new Array;

//页面当前是否处于已编辑未保存状态
var PAGE_EDIT_STATUS = false;

function _submitFormData(btn){
	CHECK_UNIQUE = false;
	var $form = $(btn).parents("form");
	$form.find(".edit-item input,.edit-item select,.edit-item textarea").trigger("blur");
	var $errors = $form.find(".edit-item .error,.edit-item .warn, easytip-text");
	if($errors.length<=0){
		if(FORM_EDITORS.length>0){
			for(var i=0;i<FORM_EDITORS.length;i++){
				FORM_EDITORS[i].sync();
			}
		}
		$.submitform($form);
	}else{
		$.showmsg($errors);
	}
}

function hoverTag(tag){
	$(tag).hover(function(){
		$(this).css("background-color","#fff5d3");
	},function(){
		$(this).css("background-color","");
	});
}

//DOM文档载入完成后执行的函数，作用如同$(document).ready()一样
$(function(){
	//表单域自动验证
	if($(".edit-item").length>0){
        //获取.edit-item下面的表单元素对象，blur事件绑定函数
		$(".edit-item input,.edit-item select,.edit-item textarea").blur(function(){
			CHECK_UNIQUE = true;
            //参考form.extend.js对应方法
			$(this).checkData();
		}).focus(function(){
            //查找当前元素之后所有的同辈元素
			if($(this).nextAll(".DESC").length>0){
                //显示描述信息，<span class="DESC" style="display: none;">保持默认即可</span>
				$(this).nextAll(".DESC").show();
                //提示框删除，<span class="MSG_BOX success">OK!</span>
				$(this).nextAll(".MSG_BOX").remove();
			}
		});
	}
	
	//保存数据
	$(".submit").click(function(){
		$(".content form input[name=CONTINUE_EDIT]").remove();
		_submitFormData(this);
	});
	
	//保存并继续添加
	$(".save-continue").click(function(){
		if($(".content form input[name=CONTINUE_EDIT]").length<=0){
			$(".content form").append("<input type='hidden' name='CONTINUE_EDIT' value='1'>");
		}
		
		_submitFormData(this);
	});
	
	//标签切换
	$(".ui-tag li").click(function(){
		if(!$(this).hasClass(".ui-tag-cur")){
			var $cur = $(".ui-tag li.ui-tag-cur");
			$($cur.attr("lang")).hide();
			$cur.removeClass("ui-tag-cur");
			$(this).addClass("ui-tag-cur");
			$($(this).attr("lang")).show();
		}
		if(typeof ui_tag_other == 'function' && ui_tag_other != undefined){
			ui_tag_other();
		}
	});
	$(".ui-tag li").each(function(){
		if($(this).hasClass('ui-tag-cur')){
			$($(this).attr("lang")).show();
		}else{
			$($(this).attr("lang")).hide();
		}
		if(typeof ui_tag_other == 'function' && ui_tag_other != undefined){
			ui_tag_other();
		}
	});
	
	hoverTag("tr.item,.edit-item");
	
	change_status("a.checkbox");
	
	
	$(".delete-btn").click(function(){
		var a = this.href || this.rel || this.alt;
		
		$.warn("您确实要删除数据吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				type:'get',
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		
		return false;
	});
	
	
	
	$(".betch-delete-btn").click(function(){
		var name = this.name;
		if($(name).find(".items input:checked").length<=0){
			$.warn("请先选择要删除的数据行");
			return false;
		}
		
		var a = this.href || this.rel || this.alt || $($(this).attr('name')).attr('action');
		var self = this;
		
		a = a.replace('betch_update', 'delete');
		
		$.warn("您确实要删除这些数据吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				type:'POST',
				data: $($(self).attr('name')).serialize(),
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		
		return false;
	});
	
	$(".pass-btn").click(function(){
		var a = this.href || this.rel || this.alt;
		$.warn("您确实要通过该数据信息吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		
		return false;
	});
	
	$(".refuse-btn").click(function(){
		var a = this.href || this.rel || this.alt;
		$.warn("您确实要拒绝该数据信息吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		return false;
	});
	
	$(".betch-pass-btn").click(function(){
		var name = this.name;
		if($(name).find(".item input:checked").length<=0){
			$.warn("请先选择要审核通过的数据行");
			return false;
		}
		$.warn("您确实要批量审核通过选中数据信息吗？",function(){
			$(name).submit();
		});
		return false;
	});
	
	$('.betch-edit-btn').click(function(){
		var a = this.href || this.rel || this.alt || $($(this).attr('name')).attr('action');
		var self = this;
		var t = this.title || '修改这些数据';
		
		var name = this.name;
		if($(name).find(".items input:checked").length<=0){
			$.warn("请先选择要修改的数据行");
			return false;
		}
		
		a = a.replace('delete', 'betch_update');
		
		$.warn("您确实要"+ t +"吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				type:'POST',
				data: $($(self).attr('name')).serialize(),
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		
		return false;
	});
	
	$(".method-btn").click(function(){
		var a = this.href || this.rel || this.alt;
		var t = this.title || $(this).html();
		$.warn("您确实要"+ t +"吗？",function(){
			$.loading("数据更新中");
			$.ajax({
				url : a,
				dataType:'json',
				success: function(p_msg){
					$.remove_loading();
					if(p_msg.status == 0){
						$.warn(p_msg.info);
					}else{
						$.success(p_msg.info, 'reload');
					}
				},
				error:function(){
					$.remove_loading();
					$.warn("系统错误");
				}
			});
		});
		
		return false;
	});
	
	//doc_resize();
	
	
	
	/*搜索框提示*/
	$("input.prompt").focus(function(){
		$(this).next(".prompt-desc").hide();
	}).blur(function(){
		if($(this).val()=="") $(this).next(".prompt-desc").show();
	});
	
	$("input.prompt").each(function(i){
		show_input_tip(this, i);
	});
	
	$('#search_field').change(function(){
		$('#search_keyword').val('');
		$('#search_keyword').attr('title', g_searchNotice[$(this).val()][1]);
		show_input_tip('#search_keyword');
	});
	
	//默认值
	if($('#search_field').length > 0){
		$('#search_keyword').attr('title', g_searchNotice[$('#search_field').val()][1]);
		show_input_tip('#search_keyword');
	}
	$('#search_keyword').keyup(function(p_event){
		if(p_event.keyCode == 13 && $(this).val() != ''){
			$('#search_form').submit();
		}
	});
	$('#search_keyword').focus();
	/*----------搜索框提示----------*/
	
});

//当窗口大小变化时，调用resize事件绑定的处理函数
$(window).resize(function(){
//	doc_resize();
});

//计算窗口大小使界面铺满整个窗口
function doc_resize(){
//	var width = $(window).width();
//	var height = $(window).height();
//	var wrap_height = 0, shortcut_height=0;
//	
//	if($('.footer-bottom').length > 0){
//		height -= 31;
//		shortcut_height = height - $('#header').height();
//	}else{
//		shortcut_height = height - $('#header').height() - 31;
//	}
//	
//	wrap_height = height - 168;
//	
//	$("#outside-wrap").height(wrap_height);
//	$("#wrapper").css({height: wrap_height +"px"});
//	$("#shortcut").height(shortcut_height);
}

function show_shortcut(){
	$("#shortcut").animate({
		left : "-215px",
		opacity : 0.2
	},"slow",function(){
		$("#show-shortcut").show().animate({
			left : "25px"
		},200);
	});
	$("#content_wrapper").animate({
		paddingLeft : "25px"
	});
}

function hide_shortcut(){
	$("#show-shortcut").show().animate({
		left : "-70px"
	},200,function(){
		$("#shortcut").animate({
			left : "10px",
			opacity : 1
		});
		$("#content_wrapper").animate({
			paddingLeft : "235px"
		});
	});
}

//改变某个字段状态
function change_status(checkbox){
	$(checkbox).click(function(){
		$this = $(this);
		$.ajax({
			url : $this.attr("rel"),
			data : 'status=' + ($this.hasClass("true") ? '1' : '0'),
			dataType : "json",
			success : function(dat){
				if(dat.code == $.CODE_SUCCESS){
					$this.toggleClass("true");
					$this.toggleClass("false");
				}else{
					$.error(dat.data);
				}
			},
			error : function(){
				$.error('系统出错，操作失败');
			}
		});
		
		return false;
	});
}

/**
 * 显示提示
 */
function show_input_tip(p_dom, i)
{
	$(p_dom).attr("placeholder", "");
	var id = $(p_dom).attr("id");
	i = i || '';
	if (!id) {
		var myDate = new Date();
		id = "prompt" + i + myDate.getTime();
		$(p_dom).attr("id", id);
	}
	var pos = $(p_dom).position();
	$(p_dom).next('.prompt-desc').remove();
	$(p_dom).after("<label for='" + id + "' class='prompt-desc' style='left:" + pos.left + "px;top:" + pos.top + "px;'>" + $(p_dom).attr("title") + "</label>");
	if ($(p_dom).val() != "") {
		$(p_dom).next(".prompt-desc").hide();
	}
}

/**
 * 获取水费打印数据，并打印
 */
function print_operation_charge(p_opch_id)
{
	var items = '';
	$('.items').each(function(){
		if($(this).attr('checked') == true || $(this).attr('checked') == 'checked'){
			items += $(this).val() +'|';
		}
	});
	items = p_opch_id || items;
	if(items == ''){
		$.warn("请选择要打印的收据");
		return;
	}
	$.loading("获取打印数据");
	$.ajax({
		url : g_domainUrl +'OperationCharge/get_print_data',
		type : "POST",
		data : {items: items},
		dataType : "json",
		success : function(p_msg){
			$.remove_loading();
			if(p_msg.status == 0){
				$.warn(p_msg.info);
			}else{
				create_print(p_msg.info.data, p_msg.info.width, p_msg.info.height);
			}
		}
	});
	return false;
}

/**
 * 获取水费打印数据，并复打印
 */
function print_operation_charge_reprint(p_opch_id)
{
	var items = p_opch_id || $("input[name='item']:checked").val();
	if(items == ''){
		$.warn("请选择要打印的收据");
		return;
	}
	$.loading("获取打印数据");
	$.ajax({
		url : g_domainUrl +'OperationCharge/get_print_data',
		type : "POST",
		data : {items: items, 'reprint': 1},
		dataType : "json",
		success : function(p_msg){
			$.remove_loading();
			if(p_msg.status == 0){
				$.warn(p_msg.info);
			}else{
				create_print(p_msg.info.data, p_msg.info.width, p_msg.info.height);
			}
		}
	});
	return false;
}

function get_checkbox_value(p_dom)
{
	var items = '';
	$(p_dom).each(function(){
		if($(this).attr('checked') == true || $(this).attr('checked') == 'checked'){
			items += $(this).val() +'|';
		}
	});
	return items;
}

/**
 * 获取业务打印数据，并打印
 */
function print_operation_other()
{
	var items = '';
	$('.items').each(function(){
		if($(this).attr('checked') == true || $(this).attr('checked') == 'checked'){
			items += $(this).val() +'|';
		}
	});
	if(items == ''){
		$.warn("请选择要打印的收据");
		return;
	}
	$.loading("获取打印数据");
	$.ajax({
		url : g_domainUrl +'OperationOther/get_print_data',
		type : "POST",
		data : {items: items},
		dataType : "json",
		success : function(p_msg){
			$.remove_loading();
			if(p_msg.status == 0){
				$.warn(p_msg.info);
			}else{
				create_print(p_msg.info.data, p_msg.info.width, p_msg.info.height);
			}
		}
	});
	return false;
}

/**
 * 获取业务打印数据，并复打印
 */
function print_operation_other_reprint()
{
	var items = $("input[name='item']:checked").val();
	if(items == ''){
		$.warn("请选择要打印的收据");
		return;
	}
	$.loading("获取打印数据");
	$.ajax({
		url : g_domainUrl +'OperationOther/get_print_data',
		type : "POST",
		data : {items: items, 'reprint': 1},
		dataType : "json",
		success : function(p_msg){
			$.remove_loading();
			if(p_msg.status == 0){
				$.warn(p_msg.info);
			}else{
				create_print(p_msg.info.data, p_msg.info.width, p_msg.info.height);
			}
		}
	});
	return false;
}

/**
 * 打印
 * 
 * @param {array} p_data	数据
 * @param {int} p_width		纸张宽
 * @param {int} p_height	纸张高
 */
function create_print(p_data, p_width, p_height)
{
   if(p_data == null){
	   return;
   }
   var idx1;
   LODOP.PRINT_INIT("水管家打印");
   LODOP.PRINT_INITA(0, 0, p_width +'mm', p_height +'mm', "");
   LODOP.SET_PRINT_MODE("POS_BASEON_PAPER",true);
   var len = p_data.length;
   if(len > 0){
	   for(idx1 in p_data){
		   eval(p_data[idx1]);
		   LODOP.NEWPAGE();
	   }
	   LODOP.SET_PRINT_PAGESIZE(1, p_width +'mm', p_height +'mm', "水管家打印");
	   LODOP.PREVIEW();
	   window.location.reload();
   }
}