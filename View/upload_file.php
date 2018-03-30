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
        <h4>上传文件位置 : [<?=$dir_path?>]</h4>
        <hr>
        <div class="file-box btn-success radius">
            <input class="input_files file-btn" type="file" name='files[]' webkitdirectory />选择文件夹
        </div>
        <div class="file-box btn-secondary radius">
            <input  class="input_files btn-secondary radius file-btn" type="file" name='files2[]' multiple />选择文件
        </div>
        <br>(文件夹名中若有'.',则会替换为'_')
        <hr>
        <div>
            <h4>服务器上传配置信息</h4>
            【post最大尺寸:<?=ini_get('post_max_size')?>】
            【上传最大文件尺寸:<?=ini_get('upload_max_filesize')?>】<br>

        </div>
        <hr>
        <div>
            <h4>当前上传信息</h4>
            【剩余上传文件数:<span id='now_num'>0</span>】
        </div>
        <hr>
        待上传列表:<button onclick="clear_files()" class="btn btn-danger size-M radius">清空</button>
        <table id='tbl_file_list' class="table table-border table-bordered table-striped">
            <thead>
              <tr>
                <th>file name</th>
                <th>size(MB)</th>
                <th>status</th>
              </tr>
            </thead>
            <tbody id="tbl_file_list_tbody"></tbody>
          </table>
	</div>
</body>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/js/upload.js"></script>
<script>
    // 待上传文件列表
    var files = [];
    // 待上传文件的服务器文件夹路径
    var new_path = '<?=$dir_path?>';
    var base_url = '<?=BASE_URL?>';

    $(function(){
      $(".input_files").change(function(){
        var file_length = this.files.length;
        for(var i=0; i<file_length ;i++){
            add_file(this.files[i]);
        }
        upload_file();
      });
    })
</script>
</html>