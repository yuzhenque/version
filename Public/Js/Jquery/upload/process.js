/**
 * 上传文件JS处理文件
 */
var g_reload = 0;
var uploadify_location_href = '/';
var uploadify_process = false;

function uploadify_checked(id, multi, cid){
	$("#"+id+" .hide-path").click(function(){
		var $this = $(this).parent();
		$this.find(".uploadify-checked").toggleClass("uploadify-true");
		if(!multi){
			//选择单个文件
			if($this.find(".uploadify-checked").hasClass("uploadify-true")){
				$("#"+cid+"-btn").css("position","absolute");
				$("#"+cid+"-box").find(".uploadify-true").each(function(){
					if($(this).parent("div").attr("id") != id){
						$(this).removeClass("uploadify-true");
					}
				});
			}else{
				$("#"+cid+"-btn").css("position","");
			}
		}
	});
}

//按确定按钮
function uploadify_select_file(name, cid){
	if($("#"+cid+"-box .upload-completed").length > 0){
		var $trues = $("#"+cid+"-box .uploadify-true");
		if($trues.length > 0){
			var _values = new Array;
			var values = '';
			
			//可选择多个文件
			if($("#"+cid+"-box").hasClass("can-multi-file")){
				values = $.trim(window.parent.$("#uploadify-"+name).val());
				_values = values.split(",");
			}else{
				window.parent.$("#uploadify-container-"+name).html("");
			}
			$trues.each(function(){
				var vid = parseInt($(this).children("input").val());
				if($.inArray(vid, _values)==-1 && vid>0 && $("#uploadify-item-"+vid).length<=0){
					values += (values=='' ? '' : ',') + vid;
					var $img = $(this).prevAll("img");
					var _html = '';
					if($img.length>0){
						_html = "<a href='" + $img.attr("src") + "' class='lightbox' target='_blank'><img style='"+$img.attr("style")+"' width='"+$img.attr("width")+"' height='"+$img.attr("height")+"' src='" + $img.attr("src") + "' /></a>";
						window.parent.$("#uploadify-container-"+name).append("<span class='uploadify-fileItms' id='uploadify-item-"+vid+"'>"+_html+"<span onclick='uploadify_remove("+vid+")' class='uploadify-cancle' title='移除'></span></span>");
					}else{
						var $hide = $(this).prevAll("a.hide-path");
						_html = "<a href='"+$hide.attr("rel")+"' target='_blank'"+($hide.hasClass("clone") ? "style='"+$hide.attr("style")+"'" : "")+"><span class='fileType'>"+$hide.children(".fileType").text()+"</span><span class='fileName'>"+$hide.children(".fileName").text()+"</span></a>";
						window.parent.$("#uploadify-container-"+name).append("<span class='uploadify-fileItms' id='uploadify-item-"+vid+"' title='"+$hide.children(".fileName").text()+"'>"+_html+"<span onclick='uploadify_remove("+vid+")' class='uploadify-cancle' title='移除'></span></span>");
					}
				}
			});
			window.parent.$("#uploadify-"+name).attr("value",values);
			window.parent.tb_remove();
		}else{
			$.warn("请必须"+($("#"+cid+"-box").hasClass('can-multi-file') ? '至少' : '' )+"选择一"+($("#"+cid+"-box").hasClass('is-image-file')?'张图片':'个文件'));
		}
	}else{
		$.warn('您还没有上传'+($("#"+cid+"-box").hasClass('is-image-file')?'图片':'文件')+'，请先上传');
	}
}

//从历史上传文件列表选择文件
function history_file(box){
	$("#"+box+" .upload-completed .hide-path").click(function(){
		var $this = $(this).parent();
		if(!$("#"+box).hasClass("can-multi-file") && $this.find(".uploadify-true").length<=0){
			$("#"+box+" .uploadify-true").removeClass("uploadify-true");
		}
		$this.find(".uploadify-checked").toggleClass("uploadify-true");
	});
}

//删除指定文件
function uploadify_delete(upload_id, file_id){
	$.warn("您确实要删除该"+($("#"+file_id).find("img").length>0?"图片":"文件")+"信息吗？",function(){
		$.ajax({
			url : uploadify_location_href,
			data : "id=" + $("#"+file_id).find(".uploadify-checked input").val(),
			type : "POST",
			dataType : "json",
			success : function(json){
				if(json.status == $.CODE_SUCCESS){
					if($(".uploadify-clock").length>0){
						$("#"+upload_id).uploadify('cancel', file_id);
						$("#"+upload_id).data('uploadify').cancelUpload(file_id, false);
						
						$("#"+upload_id).data('uploadify').queueData.files[file_id]['uploaded']=true;
						
						if(!$("#"+upload_id).parent().hasClass("can-multi-file") && $("#"+upload_id).parent().find(".uploadify-true").length<=0){
							$("#"+upload_id+"-btn").css("position","");
						}
					}else{
						$("#"+file_id).remove();
					}
				}else{
					$.error(json.info);
				}
			},
			error : function(){
				$.error("系统出错，删除文件失败");
			}
		});
	});
}