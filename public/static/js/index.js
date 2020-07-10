
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

// 得到当前选中tab
function global_getSelectedTabIndex(){
    return $('#min_title_list li.active').index();
}

// 得到当前选中tab的iframe
function global_getSelectedIframe(){
    var index = global_getSelectedTabIndex();
    return $('.show_iframe').eq(index).find('iframe')[0];
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