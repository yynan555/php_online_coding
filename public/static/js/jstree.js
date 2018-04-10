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
                        return { 'dir_path' : dir_name };
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
                    tmp.upload = {
                        "label":"Upload",
                        "action":function(){
                            uploadFile();
                        }
                    };
                    tmp.download = {
                        "label":"Download",
                        "action":function(data){
                            var inst = $.jstree.reference(data.reference);
                            var obj = inst.get_node(data.reference);
                            var path = obj.id;

                            if(obj.type == 'folder'){
                                msg('正在压缩，请等待。。。',2000,6);
                            }

                            global_downloadByUrl(base_url+'/index.php?a=download_file_or_dir&path='+path);
                        }
                    };
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
                        delete tmp.upload;
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
            layer_confirm('删除 '+data.node.text+' 吗？ ','',function(){
                //向服务端发送删除指令
                jstree_post(
                    base_url+'/index.php?a=delete_node', 
                    { 'path' : data.node.id ,'type' :  data.node.type}, 
                    function(respone_data){
                        if(respone_data.error_no < 100){
                            msg(respone_data.msg,1000,1);
                            reload_tree(data.parent);
                        }else{
                            msg(respone_data.msg,3000,2);
                            reload_tree(data.parent);
                        }
                    }
                );
            },function(){
                reload_tree(data.parent);
            })
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
                        // if(data.node.type == 'file') {
                        //     data.instance.set_id(data.node, respone_data.data.new_path);
                        // }else{
                            reload_tree(data.node.parent);
                        // }
                    }else{
                        msg(respone_data.msg,3000,2);
                        reload_tree(data.node.parent);
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
                        reload_tree(data.parent);
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
                        reload_tree(data.parent);
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
            // 当选择节点发生改变是, 调用该方法
            // this_node = data.instance.get_node(data.node);
            // node_type = this_node.type;
            // var file_path = '';
            // if(node_type == 'file' ){
            //     file_path = this_node.parent;
            // }else{
            //     file_path = this_node.id;
            // }
            // if(file_path){
            //     selected_node.id = this_node.id;
            //     selected_node.path = file_path;
            // }
        });
});
// 刷新树
function reload_tree(id = '')
{
    if(id == ''){
        tree_obj.jstree(true).refresh();
    }else{
        tree_obj.jstree(true).refresh_node(tree_obj.jstree().get_node(id));
    }
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
function getRootPaths(){
    return tree_obj.jstree("get_node", '#').children;
}
// 获取当前选中节点 
function getNowSelectDirs(){
    return tree_obj.jstree().get_selected();
}
// 上传文件
function uploadFile(){
    var dir_path = getNowSelectDirs();
    if(dir_path.length != 1){
        alert('请选中一个节点, 再点击\'上传文件\'');
        return false;
    }
    dir_path = dir_path[0];
    layer.open({
        type: 2,
        title: '上传文件',
        area: ['390px', '500px'],
        shade: 0,
        maxmin: true,
        offset: '100px',
        content: base_url+'/index.php?a=show_upload_file&dir_path='+dir_path,
    });
}

// 询问框
function layer_confirm(title,content,ok_fun,cancel_fun)
{
    layer.open({
        type: 1
        ,title: title
        ,shade: 0
        ,closeBtn: 0
        ,anim: 0
        ,btn: ['确认', '取消']
        ,content:content
        ,offset: [
            ($(window).height()/2)-Math.random()*150
            ,($(window).width()/2)-Math.random()*150
        ]
        ,yes: function(index){
            ok_fun();
            layer.close(index);
        },
        btn2: function(index){
            cancel_fun();
            layer.close(index);
        }
    });
}