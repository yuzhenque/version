$(document).ready(function () {
//    var width = $(".mod-data").width();
//    var mainWidth = $(".mod-main").width();
//    if(mainWidth+120+5 > width){
//        $(".mod-main").width(width-120-5);
//    }
    $('.go-btn').click(function () {
        var a = $(this).attr('href') || $(this).attr('rel') || $(this).attr('alt');
        window.location.href = a;
    });
    $(".ajax_page").click(function () {
        var ajax_page = $(this);
        var url = ajax_page.attr('href');
        var data_to = ajax_page.attr('data-to');
        $.ajax({
            type: "get",
            url: url,
            dataType: "json",
            success: function (data) {
                if (data.status == 200) {
                    $("." + data_to).append(data.data.html);
                    next_url = data.data.page.ajax_next_page;
                    if (next_url) {
                        ajax_page.attr('href', next_url);
                    } else {
                        ajax_page.removeClass('ajax_page').html('到底了！');
                        ajax_page.unbind("click");
                    }
                } else {
                    $.warn(data.info);
                }
            }
        });
    });
});