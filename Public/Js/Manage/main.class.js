/* 
 * 主功能JS
 * 实现公用回调等
 */
var g_mainCls = new Object();

/**
 * 图文编辑列表初始化
 */
g_mainCls.check_news_init = 0;
g_mainCls.wd_news_init = function()
{
	$(".news-list,.news-cover").hover(function(){
		$(this).find(".act-btn").show();
	},function(){
		$(this).find(".act-btn").hide();
	});
	g_mainCls.check_news_init = 1;
};
/**
 * 生成一个图文内的信息
 * 
 * @param {string} p_from		来自
 * @param {string} p_backId		生成的内容要往哪放
 * @param {object} p_data		内容
 */
g_mainCls.create_news_edit = function(p_from, p_backId, p_data)
{
	var html = '';
	if(parseInt(p_data.wxso_is_cover) == 1){
		if($('#dom_news_cover_'+ p_backId).length > 0){
			html += '<img src="'+ p_data.wxso_image +'" width="320" height="160"/>';
			html += '<a class="news-btn act-btn cover-btn mywin" href="/manage.php/WxSource/edit/from/'+ p_from +'/iscover/1/back_id/'+ p_backId +'/wxso_id/'+ p_data.wxso_id +'" title="编辑封面信息">编辑</a>';
			html += '<a class="news-btn news-del-btn act-btn cover-btn" onclick="g_mainCls.delete_news_edit(\''+ p_from +'\',\''+ p_data.wxso_id +'\',\''+ p_data['wxso_parent_id'] +'\', \''+ p_backId +'\');">删除</a>';
			html += '<div class="title">'+ p_data.wxso_title +'</div>';
			$('#dom_news_cover_'+ p_backId).html(html);
			mywin('#dom_news_cover_'+ p_backId +' .mywin');
		}
	}else{
		if($('#dom_news_list_li_'+ p_data.wxso_id).length > 0){
			html += '<div class="news-title">'+ p_data.wxso_title +'</div>';
			html += '<div class="news-thumb cover"><img src="'+ p_data.wxso_image +'" width="70" height="70"/></div>';
			html += '<a class="news-btn act-btn cover-btn mywin" href="/manage.php/WxSource/edit/from/'+ p_from +'/iscover/0/back_id/'+ p_backId +'/wxso_id/'+ p_data.wxso_id +'" title="编辑图文信息">编辑</a>';
			html += '<a style="display: none;" class="news-del-btn act-btn" onclick="g_mainCls.delete_news_edit('+ p_data.wxso_id +');">删除</a>';
			$('#dom_news_list_li_'+ p_data.wxso_id).html(html);
			mywin('#dom_news_list_li_'+ p_data.id +' .mywin');
		}else{
			html += '<div id="dom_news_list_li_'+ p_data.wxso_id +'" class="news-list">';
			html += '	<div class="news-title">'+ p_data.wxso_title +'</div>';
			html += '	<div class="news-thumb cover"><img src="'+ p_data.wxso_image +'" width="70" height="70"/></div>';
			html += '	<a class="news-btn act-btn cover-btn mywin" href="/manage.php/WxSource/edit/from/'+ p_from +'/iscover/0/back_id/'+ p_backId +'/wxso_id/'+ p_data.wxso_id +'" title="编辑图文信息">编辑</a>';
			html += '	<a style="display: none;" class="news-del-btn act-btn" onclick="g_mainCls.delete_news_edit('+ p_data.wxso_id +');">删除</a>';
			html += '</div>';
			$('#dom_news_list_'+ p_backId).append(html);
			mywin('#dom_news_list_'+ p_backId +' .mywin');
		}
	}
	g_mainCls.wd_news_init();
};

/**
 * 删除某条新闻
 * 
 * @param {string} p_from 来自
 * @param {int} p_id 要删除的ID
 */
g_mainCls.delete_news_edit = function(p_from, p_id, p_parentId, p_backId)
{
	$.warn("删除将无法恢复，确定要删除此信息？", function(){
		$.loading('正在删除信息');
		$.ajax({
			url: g_domainUrl +'WxSource/delete',
			data: 'wxso_id='+ p_id,
			dataType: "json",
			type: 'POST',
			success: function(p_msg) {
				$.remove_loading();
				if(p_msg.status == 1){
					$.warn('删除成功');
					if($('#dom_news_cover_'+ p_backId).length > 0){
						$('#dom_news_cover_'+ p_backId).html('<a class="mywin" href="/index.php/source/newsedit/from/'+ p_from +'/parent_id/'+ p_parentId +'/iscover/1/back_id/'+ p_backId +'/type/1">添加封面新闻</a>');
						mywin('#dom_news_cover_'+ p_backId +' .mywin');
					}else{
						$('#dom_news_list_li_'+ p_id).remove();
					}
				}else{
					$.warn(p_msg.info);
				}
			},
			error: function() {
				$.remove_loading();
				$.warn('系统出错，操作失败');
			}
		});
	});
};