<!DOCTYPE html>
<html>
<head>
	<title></title>
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/css/index.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui/css/H-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_PATH?>/lib/h-ui.admin/css/H-ui.admin.css" />
</head>
<body>
	<div id='div_upload_file'>
        <div class="file-box btn-success radius">
            <input class="input_files file-btn" type="file" name='files[]' webkitdirectory />选择文件夹(文件夹名中若有'.',则会替换为'_')
        </div>
        <div class="file-box btn-secondary radius">
            <input  class="input_files btn-secondary radius file-btn" type="file" name='files2[]' multiple />选择文件
        </div>
        <hr>
        <button id='upload_file_action' class="btn btn-primary ">点击上传</button>
        <br>
        <br>
        <hr>
        <div>
            <h4>服务器上传配置信息</h4>
            【post最大尺寸:<?=ini_get('post_max_size')?>】
            【上传最大文件尺寸:<?=ini_get('upload_max_filesize')?>】
            【上传最大文件数:<?=ini_get('max_file_uploads')?>个文件】<br>

        </div>
        <hr>
        <div>
            <h4>当前上传信息</h4>
            【当前文件总大小:<span id='now_size'>0</span>MB】
            【上传文件数:<span id='now_num'>0</span>】
        </div>
        <hr>
        待上传列表:<button onclick="clear_files()" class="btn btn-danger size-MINI radius">清空待上传列表</button>
        <table id='tbl_file_list' class="table table-border table-bordered table-striped">
            <thead>
              <tr>
                <th>file name</th>
                <th>size(MB)</th>
              </tr>
            </thead>
            <tbody id="tbl_file_list_tbody"></tbody>
          </table>
	</div>
</body>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    var files = [];
    var new_path = '<?=$dir_path?>';

    $(function(){
      $(".input_files").change(function(){
        var file_length = this.files.length;
        for(var i=0; i<file_length ;i++){
            add_file(this.files[i]);
        }
      });
    })

    upload_file_action.onclick = function(){
        if(!files.length){
            alert('没有选择需要上传文件');
            return false;
        }
        var fd = new FormData();
        for (var i = 0; i < files.length; i++) {
            if(files[i]['webkitRelativePath']){
                fd.append(files[i]['webkitRelativePath'], files[i]);
            }else{
                fd.append(files[i]['name'], files[i]);
            }
        }
        $.ajax({
            url: '<?=\Lib\CommonFun::url()?>/index.php?a=upload_file&path='+new_path,
            method: "POST",
            data: fd,
            contentType: false,
            processData: false,
            cache: false,
            success: function(data){
            	if(data.error_no == 0){
            		parent.msg('上传完成',3000,1);
	                parent.reload_tree(new_path);
	            	clear_files();
            	}else{
            		parent.msg('上传失败 :['+data.msg+']',3000,2);
            	}

            },
            error: function(){
            	parent.msg('上传失败',3000,2);
            }

        });
    }

	function add_file(file_obj)
    {
        var file_name = '';
        var file_size_sum = 0;
        if(file_obj.webkitRelativePath){
            file_name = file_obj.webkitRelativePath;
        }else{
            file_name = file_obj.name;
        }

        var now_file_size = Math.round(parseFloat(file_obj.size/(1024*1024))*10000)/10000;
        file_size_sum = Math.round( parseFloat( ($('#now_size').html()) + now_file_size)*10000)/10000 ;

        file_append_display_list(file_name,now_file_size);

        $('#now_size').html(file_size_sum);
        $('#now_num').html(parseInt($('#now_num').html()) + 1);

        files.push(file_obj);
    }
    function file_append_display_list(file_name,size)
    {
        $('#tbl_file_list_tbody').append(file_table_item(file_name,size));
    }
    function file_table_item(file_name,size)
    {
        return '<tr><th>'+file_name+'</th><td>'+size+'</td></tr>';
    }

    function clear_files()
    {
    	files = [];
    	$('#now_size').html(0);
    	$('#tbl_file_list_tbody').html('');
        $('#now_num').html(0);
    }
</script>
</html>