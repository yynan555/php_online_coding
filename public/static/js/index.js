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

// 刷新树
function reload_tree(id = '')
{
    if(id == ''){
        tree_obj.jstree(true).refresh();
    }else{
        tree_obj.jstree(true).refresh_node(tree_obj.jstree().get_node(id));
    }
}
// 提示框
function msg(msg,time,icon)
{
    layer.msg(msg, {
        time: time, //2秒关闭（如果不配置，默认是3秒）
        offset : ['80%',''],
        icon:icon
    });
}
// post请求
function jstree_post(url, data, success_callback)
{
    $.post(url, data)
        .done(function (respone_data) {
            success_callback(respone_data);
        })
        .fail(function () {
            msg('请求失败',3000,2);
            reload_tree();
        });
}

// 获取根节点
function getRootPath(){
    return tree_obj.jstree("get_node", '#').children[0];
}