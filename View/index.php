<!DOCTYPE HTML>
<html>
<head>
    <title>在线代码编辑器</title>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui/css/H-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui.admin/css/H-ui.admin.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/Hui-iconfont/1.0.8/iconfont.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui.admin/skin/default/skin.css" id="skin" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui.admin/css/style.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/jstree/jstree.css" />

    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/css/index.css" />
</head>
<body>
<aside class="Hui-aside" style="top:0px; padding-top:0px">
    <div >
        <button class="btn btn-primary-outline radius size-MINI" onclick="reload_tree()">刷新目录</button>
        <a class="btn btn-primary-outline radius size-MINI" href='<?=BATH_URL?>/index.php?a=logout' >退出登录</a>
    </div>
    <div>
        <div class="panel panel-default">
            <div class="panel-header">当前选中路径
                <button class="btn size-MINI" id="btn_uploadfile" style="float: right;">上传文件</button></div>
            <div id='new_select_path'  class="panel-body">访问域根路径</div>
        </div>
    </div>
    <div class="" id="tree"></div>
</aside>
<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>
<section class="Hui-article-box" style="top:0px">
    <div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
        <div class="Hui-tabNav-wp">
            <ul id="min_title_list" class="acrossTab cl">
            </ul>
        </div>
        <div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a></div>
    </div>
    <div id="iframe_box" class="Hui-article">
    </div>
</section>


<script type="text/javascript" src="<?=STATIC_PATH?>/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/h-ui.admin/js/H-ui.admin.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/jstree/jstree.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/layer/2.4/layer.js"></script>

<script type="text/javascript" src="<?=STATIC_PATH?>/js/index.js"></script>

<script>
var tree_obj = $('#tree');
$(function () {
    tree_obj.jstree({
            'core' : {
                'data' : {
                    'url' : '<?=BATH_URL?>/index.php?a=dir_list',
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
                            "separator_after"	: true,
                            "label"				: "Folder",
                            "action"			: function (data) {
                                var folder_name = "new_folder";
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                inst.create_node(obj, { type : "folder" ,text: folder_name , id: node.id+'/'+folder_name}, "last", function (new_node) {
                                    setTimeout(function () { inst.edit(new_node); },0);
                                });
                            }
                        },
                        "create_file" : {
                            "label"				: "File",
                            "action"			: function (data) {
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
                '<?=\Lib\CommonFun::url()?>/index.php?a=delete_node', 
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
                '<?=\Lib\CommonFun::url()?>/index.php?a=create_node', 
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
                '<?=\Lib\CommonFun::url()?>/index.php?a=rename_node', 
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
                '<?=\Lib\CommonFun::url()?>/index.php?a=move_node', 
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
                '<?=\Lib\CommonFun::url()?>/index.php?a=copy_node', 
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
            // 如果不是文件夹, 则打开
            if(node.type === "file"){
                var file_path = node.id;
                var file_title = node.text;
                var url = '<?=\Lib\CommonFun::url()?>/index.php?a=get_file&file_path='+file_path;
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
                $('#new_select_path').html(file_path.replace(getRootPath(),''));
                if(!$('#new_select_path').html()){
                    $('#new_select_path').html('访问域根路径');
                }
                $('#new_select_path').attr('path_data',file_path);
            }
        });
});
</script>

<script>
    btn_uploadfile.onclick = function(e){
        dir_path = $('#new_select_path').attr('path_data');
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


</script>
</body>
</html>