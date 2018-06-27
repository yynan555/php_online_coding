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

// 下载文件
function global_downloadByUrl(url) {
    try{ 
        var elemIF = document.createElement("iframe");   
        elemIF.src = url;
        elemIF.style.display = "none";
        document.body.appendChild(elemIF);
    }catch(e){ 
        console.log('download error',e)
    } 
}

// 左侧框拉动事件
$(function(){
    if(typeof(localStorage) != 'undefined'){
        menu_width = localStorage.getItem('menu_width');
        if(menu_width){
            changeLeftSize(menu_width);
        }
    }

    var is_on_border = false;
    var allow_resize = false;
    $('body').bind({
        mousemove:function(e){
            // 得到左侧边框位置
            var la_width = $('#left_area').width();
            var la_outerWidth = $('#left_area').outerWidth();
            var x = e.clientX;
            if(x>(la_width-3) && x<la_outerWidth){
                $('#left_area').css("cursor",'e-resize');
                is_on_border = true;
            }else{
                $('#left_area').css("cursor",'');
                is_on_border = false;
            }

            // 若鼠标按下 就可以进行窗口大小改变
            if(allow_resize){
                changeLeftSize(x);
                if(typeof(localStorage) != 'undefined'){
                    localStorage.setItem('menu_width',x)
                }
            }
        },
        mousedown:function(e){
            if(is_on_border){
                allow_resize = true;
            }
        },
        mouseup:function(){
            allow_resize = false;
        }
    });
});

// 改变左侧窗口大小
function changeLeftSize(width){
    $('#left_area').css("width",width); //左侧宽度
    $('.dislpayArrow').offset({"left":parseInt(width)+3}); // 左侧缩回按钮
    $('.Hui-article-box').offset({"left":width}); // 右侧区域
    $('#user_center').css("width",width); // 用户中心
}
/*左侧菜单-隐藏显示*/
function _displaynavbar(obj){
    $left_area = $('#left_area');
    if($(obj).hasClass("open")){
        $(obj).removeClass("open");
        $left_area.css("left",0);
        $('.dislpayArrow').css("left",$left_area.width()+3);
        $('.Hui-article-box').css("left",$left_area.width());
    }else{
        $(obj).addClass("open");
        var la_outerWidth = $left_area.outerWidth()+1;
        $left_area.css("left",-la_outerWidth);
        $('.dislpayArrow').css("left", 1);
        $('.Hui-article-box').css("left",0);
    }
}