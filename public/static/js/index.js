var tree_obj = $('#tree');
$(function () {
    tree_obj.jstree({
            'core' : {
                'data' : {
                    'url' : base_url+'/index.php?a=dir_list',
                    'data' : function (node) {
                        dir_name = node.id;
                        if('#' == node.id){
                            dir_name = '';
                        }
                        return { 'dir_name' : dir_name };
                    }
                },
                'check_callback' : function(o, n, p, i, m) {
                    if(m && m.dnd && m.pos !== 'i') { return false; }
                    if(o === "move_node" || o === "copy_node") {
                        if(this.get_node(n).parent === this.get_node(p).id) { return false; }
                    }
                    return true;
                },
                'themes' : {
                    'responsive' : false,
                    'variant' : 'small',
                    'stripes' : true
                }
            },
            'contextmenu' : {
                'items' : function(node) {
                    var tmp = $.jstree.defaults.contextmenu.items();
                    delete tmp.create.action;
                    tmp.create.label = "New";
                    tmp.create.submenu = {
                        "create_folder" : {
                            "separator_after"   : true,
                            "label"             : "Folder",
                            "action"            : function (data) {
                                var folder_name = "new_folder";
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                inst.create_node(obj, { type : "folder" ,text: folder_name , id: node.id+'/'+folder_name}, "last", function (new_node) {
                                    setTimeout(function () { inst.edit(new_node); },0);
                                });
                            }
                        },
                        "create_file" : {
                            "label"             : "File",
                            "action"            : function (data) {
                                var file_name = "new_file";
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                inst.create_node(obj, { type : "file" ,text: file_name, id: node.id+'/'+file_name}, "last", function (new_node) {
                                    setTimeout(function () { inst.edit(new_node); },0);
                                });
                            }
                        }
                    };
                    if(this.get_type(node) === "file") {
                        delete tmp.create;
                    }
                    return tmp;
                }
            },
            'types' : {
                'default' : { 'icon' : 'folder' },
                'folder' : { 'icon' : 'folder' },
                'file' : { 'valid_children' : [], 'icon' : 'file' }
            },
            'unique' : {
                'duplicate' : function (name, counter) {
                    return name + ' ' + counter;
                }
            },
            'plugins' : ['state','dnd','types','contextmenu','unique']
        })
        .on('delete_node.jstree', function (e, data) {
            //向服务端发送删除指令
            jstree_post(
                base_url+'/index.php?a=delete_node', 
                { 'path' : data.node.id ,'type' :  data.node.type}, 
                function(respone_data){
                    if(respone_data.error_no < 100){
                        msg(respone_data.msg,1000,1);
                    }else{
                        msg(respone_data.msg,3000,2);
                        reload_tree(data.parent);
                    }
                }
            );
        })
        .on('create_node.jstree', function (e, data) {
            jstree_post(
                base_url+'/index.php?a=create_node', 
                { 'type' : data.node.type, 'path' : data.node.parent, 'name' : data.node.text }, 
                function(respone_data){
                    if(respone_data.error_no < 100){
                        msg(respone_data.msg,1000,1);
                    }else{
                        msg(respone_data.msg,3000,2);
                    }
                    data.instance.set_id(data.node, respone_data.data.new_path);
                }
            );
        })
        .on('rename_node.jstree', function (e, data) {
            jstree_post(
                base_url+'/index.php?a=rename_node', 
                {'path' : data.node.id, 'name' : data.text }, 
                function(respone_data){
                    if(respone_data.error_no < 100){
                        msg(respone_data.msg,1000,1);
                        if(data.node.type == 'file') {
                            data.instance.set_id(data.node, respone_data.data.new_path);
                        }else{
                            reload_tree(data.node.parent);
                        }
                    }else{
                        msg(respone_data.msg,3000,2);
                        reload_tree(data.node.id);
                    }
                }
            );
        })
        .on('move_node.jstree', function (e, data) {
            jstree_post(
                base_url+'/index.php?a=move_node', 
                { 'old_path' : data.node.id, 'move_path' : data.parent+'/'+data.node.text }, 
                function(respone_data){
                    if(respone_data.error_no < 100){
                        msg(respone_data.msg,1000,1);
                        data.instance.set_id(data.node, respone_data.data.new_path);
                        reload_tree(data.parent);
                    }else{
                        msg(respone_data.msg,3000,2);
                        reload_tree(data.node.id);
                    }
                }
            );
        })
        .on('copy_node.jstree', function (e, data) {
            jstree_post(
                base_url+'/index.php?a=copy_node', 
                { 'from_path' : data.original.id, 'to_path' : data.parent+'/'+data.original.text, 'type' : data.original.type }, 
                function(respone_data){
                    if(respone_data.error_no < 100){
                        msg(respone_data.msg,1000,1);
                        data.instance.set_id(data.node, data.parent+'/'+data.original.text);
                        if(data.original.type != 'file') {
                            reload_tree(data.parent);
                        }
                    }else{
                        msg(respone_data.msg,3000,2);
                        reload_tree(data.node.id);
                    }
                }
            );
        })
        .on('dblclick.jstree', function (e, data ,a) {
            selected_id = tree_obj.jstree().get_selected();
            var node = tree_obj.jstree("get_node", selected_id);
            // 如果是文件, 则打开
            if(node.type === "file"){
                var file_path = node.id;
                var file_title = node.text;
                var url = base_url+'/index.php?a=get_file&file_path='+file_path;
                // 给标题添加文件路径后缀
                // var dir_path = getNowSelectDir(false);
                // if(dir_path){
                //     file_title += ' - '+dir_path;
                // }
                open_file(file_title,url);
            }
        })
        .on('changed.jstree', function (e, data) {
            this_node = data.instance.get_node(data.node);
            node_type = this_node.type;
            var file_path = '';
            if(node_type == 'file' ){
                file_path = this_node.parent;
            }else{
                file_path = this_node.id;
            }
            if(file_path){
                $('#new_select_path').attr('path_data',file_path);
                $('#new_select_path').html(file_path.replace(getRootPath(),''));
                if(!$('#new_select_path').html()){
                    $('#new_select_path').html('访问域根路径');
                }
            }
        });
});
// 文件上传弹出框
btn_uploadfile.onclick = function(e){
    var dir_path = getNowSelectDir();
    layer.open({
        type: 2,
        title: '上传文件到 :['+$('#new_select_path').html()+']',
        area: ['390px', '500px'],
        shade: 0,
        maxmin: true,
        offset: '100px',
        content: '<?=\Lib\CommonFun::url()?>/index.php?a=show_upload_file&dir_path='+dir_path,
      });
}

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
// 绑定关闭页面前事件
$(window).bind('beforeunload', function(){ 
    return '确认关闭当前编辑器吗'; 
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
// 获取当前选中节点 is_absolute 是绝对的地址
function getNowSelectDir(is_absolute = true){
    var dir_path = $('#new_select_path').attr('path_data');
    if(!is_absolute){
        dir_path = dir_path.replace(getRootPath(),'');
    } 
    return dir_path;
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