// 绑定关闭页面前事件
$(window).bind('beforeunload', function(){ 
    return '确认关闭当前编辑器吗'; 
});
// 为页面添加默认事件
$(window).keydown(function(e) {
    // 全局关闭函数
    if (e.keyCode == 'W'.charCodeAt() && e.ctrlKey) {
        global_closeOpenWindow();
        e.preventDefault();
    }
    // 全局保存函数
    if (e.keyCode == 'S'.charCodeAt() && e.ctrlKey) {
        global_saveFile();
        e.preventDefault();
    }
});

// 文件相关操作方法
function open_file(title,url)
{
    yaoyao_admin_tab(url,title);
}
/*菜单导航*/
function yaoyao_admin_tab(href, title){
    var bStop = false,
        bStopIndex = 0,
        href = href,
        title = title,
        topWindow = $(window.parent.document),
        show_navLi = topWindow.find("#min_title_list li"),
        iframe_box = topWindow.find("#iframe_box");
    show_navLi.each(function() {
        if($(this).find('span').attr("data-href")==href){
            bStop=true;
            bStopIndex=show_navLi.index($(this));
            return false;
        }
    });
    if(!bStop){
        creatIframe(href,title);
        min_titleList();
    }
    else{
        show_navLi.removeClass("active").eq(bStopIndex).addClass("active");
        iframe_box.find(".show_iframe").hide().eq(bStopIndex).show();
        // 下面代码是刷新区域
        // iframe_box.find(".show_iframe").hide().eq(bStopIndex).show().find("iframe").attr("src",href);
    }
}


// 提示框
function msg(msg,time,icon)
{
    layer.msg(msg, {
        time: time, //2秒关闭（如果不配置，默认是3秒）
        offset : ['80%',''],
        icon:icon,
        anim: 0
    });
}


// 关闭当前正在编辑的页面
function global_closeOpenWindow(close_all = false){
    if(close_all){
        $("#min_title_list li i").trigger("click");
        return true;
    }
    var act_li = $('#min_title_list li.active');
    if(act_li && act_li.length == 1){
        if($(act_li[0]).find("i")){
            $(act_li[0]).find("i").trigger("click");
        }
    }else{
        window.location.href="about:blank";
        window.close();
    }
}

// 保存文件
function global_saveFile(){
    $('.show_iframe').each(function(index,item){
        if($(item).css('display') == 'block'){
            $(item).find('iframe')[0].contentWindow.save_file();
        }
    });
}