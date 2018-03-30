// 向服务器上传文件
function upload_file()
{
    // 判断当前可上传文件队列是否存在 && 当前上传文件是否小于2(允许同时上传数)
    if( !files || (files.length <= 0) ){
        parent.reload_tree(new_path);
        return false;
    }
    // 从待上传列表中选择一条未进行上传的文件
    file_obj = files.shift();
    // 执行上传, success: 上传数-1;再次执行upload_file
    //          faild:  上传数-1; 将当前上传文件标记为上传失败; 再次执行upload_file
    setFileShowContent(file_obj.RelativePath,'上传中');
    upload_one_file(file_obj,function(data){
        if(data.error_no == 0 && data.data.error_list.length<1){
            // parent.reload_tree(new_path);
            // clear_files();
            setFileShowContent(file_obj.RelativePath,'');
            parent.msg('上传完成 ['+file_obj.RelativePath+']',3000,1);
        }else{
            setFileShowContent(file_obj.RelativePath,'上传失败');
            parent.msg('上传失败 :['+data.msg+']',3000,2);
        }
        $('#now_num').html(parseInt($('#now_num').html()) -1);
        upload_file();
    },function(){
        $('#now_num').html(parseInt($('#now_num').html()) -1);
        setFileShowContent(file_obj.RelativePath,'上传失败');
        upload_file();
    });
}

// 设置文件展示的内容
function setFileShowContent(file_name, status)
{
    var tr = $('#tbl_file_list_tbody tr[file_name="'+file_name+'"]');
    if(!status){ // 如果没有输入状态, 则删除当前节点
        tr.remove();
    }else{ // 如果有状态, 这修改其装态
        tr.children('td.status').html(status);
    }
}

function add_file(file_obj)
{
    // 如果文件对象的webkitRelativePath 不为空,则使用该名称作为文件
    if(file_obj.webkitRelativePath){
        file_obj.RelativePath = file_obj.webkitRelativePath;
    }else{
        file_obj.RelativePath = file_obj.name;
    }
    // 判断当前上传文件列表中是否已经存在该文件
    for(var i=0; i<files.length;i++){
        // 如果当前文件有路径信息 , 去除掉重复文件
        if(file_obj.RelativePath == files[i].RelativePath) return false;
    }
    // 需要过滤掉重复选择
    file_append_display_list(file_obj);

    $('#now_num').html(parseInt($('#now_num').html()) + 1);

    files.push(file_obj);
}
// 将文件列表中追加一个文件
function file_append_display_list(file_obj)
{
    $('#tbl_file_list_tbody').append(file_item_fromat(file_obj));
}
// 得到一个文件元素html
function file_item_fromat(file_obj)
{
    // 计算当前文件大小
    var now_file_size = Math.round(parseFloat(file_obj.size/(1024*1024))*10000)/10000;
    return '<tr file_name="'+file_obj.RelativePath+'"><td>'+file_obj.RelativePath+'</td><td>'+now_file_size+'</td><td class="status">等待</td></tr>';
}
// 清理待上传文件域
function clear_files()
{
	files = [];
	$('#now_size').html(0);
	$('#tbl_file_list_tbody').html('');
    $('#now_num').html(0);
}

// 上传一个文件
function upload_one_file(file_obj,success_fun,error_fun)
{
    if(!file_obj) return false;
    var fd = new FormData();

    fd.append(file_obj.RelativePath, file_obj);
    
    $.ajax({
        url: base_url+'/index.php?a=upload_file&path='+new_path,
        method: "POST",
        data: fd,
        contentType: false,
        processData: false,
        cache: false,
        success: function(data){
            success_fun(data);
        },
        error: function(){
            parent.msg('上传失败，请求错误',3000,2);
            error_fun();
        }
    });
}