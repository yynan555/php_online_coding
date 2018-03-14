<pre id="file_area" style="height: 100%;width: 100%;margin:0px auto 0 auto;border-radius:0px; padding:0px;"><?=htmlspecialchars($file_content)?></pre>
<script src="https://static.jstree.com/latest/assets/dist/libs/jquery.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/layer/2.4/layer.js"></script>
<script src="<?=STATIC_PATH?>/lib/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=STATIC_PATH?>/lib/ace/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
<script>
    var editor;
    $(function(){
        //初始化对象
        editor = ace.edit("file_area");

        //设置风格和语言（更多风格和语言，请到github上相应目录查看）
        theme = "monokai"; //黑色主题
        // theme = "clouds"; //白色主题
        language = "<?=( isset($file_info['extension']) )?$file_info['extension']:'';?>";
        if(language == 'js') language = 'javascript';

        editor.setTheme("ace/theme/" + theme);
        editor.session.setMode("ace/mode/" + language);

        //字体大小
        editor.setFontSize(18);

        //设置只读（true时只读，用于展示代码）
        editor.setReadOnly(false);

        //自动换行,设置为off关闭
        editor.setOption("wrap", "free");

        //启用提示菜单
        ace.require("ace/ext/language_tools");
        editor.setOptions({
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true
        });

        // 保存命令
        editor.commands.addCommand({
            name: 'myCommand',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: function(editor) {
                var file_content = editor.getValue();
                save_file(file_content);
            }
        });
        editor.getSession().on('change', function(e) {
            is_save = false;
            // 将该文件名变为待保存
            parent.$("#min_title_list .active").addClass('red_color');
        });
    })
</script>
<script>
    var file_path = '<?=$file_path?>'; //文件路径
    var file_key = '<?=$file_key?>'; // 文件校验码
    var is_save = true;

    function save_file(file_content)
    {
        if(is_save === true){
            return false;
        }
        data = {
            'file_path' : file_path,
            'file_key' : file_key,
            'file_content' : file_content
        };
        $.post('<?=BATH_URL?>/index.php?a=save_file',data,function(respone_data){
            if(respone_data.error_no === 0){
                file_key = respone_data.data.file_key;
                parent.$("#min_title_list .active").removeClass('red_color');
                msg('保存成功',1000,1);

            }else{
                msg(respone_data.msg,3000,2);
            }
        });
    }
    function msg(msg,time,icon)
    {
        layer.msg(msg, {
            time: time, //2秒关闭（如果不配置，默认是3秒）
            offset : ['80%',''],
            icon:icon
        });
    }
</script>
