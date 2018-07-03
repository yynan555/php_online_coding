<pre id="file_area" style="height: 100%;width: 100%;margin:0px auto 0 auto;border-radius:0px; padding:0px;"><?=htmlspecialchars($file_content)?></pre>

<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-min-noconflict/ext-language_tools.js"></script>
<script>
    var editor;
    var $ = parent.$;
    $(function(){
        //初始化对象
        editor = ace.edit( 'file_area' );

        //设置风格和语言（更多风格和语言，请到github上相应目录查看）
        theme = "monokai"; //黑色主题
        // theme = "clouds"; //白色主题
        language = "<?=( isset($file_info['extension']) )?$file_info['extension']:'Text';?>";

        editor.setTheme("ace/theme/" + theme);
        if(language){
            if(language == 'js'){
                language = 'javascript';
            }elseif(language == 'htm'){
                language = 'html';
            }
            editor.session.setMode("ace/mode/" + language);
        }

        // //字体大小
        editor.setFontSize(18);

        // 设置改编辑器的内容
        // editor.setValue('');

        // //设置只读（true时只读，用于展示代码）
        <?php if(!$file_info['is_writeable']): ?>
            $("#min_title_list .active").addClass('gray_color');
            editor.setReadOnly(true);
        <?php endif; ?>

        // //自动换行,设置为off关闭
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
            name: 'save',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: function(editor) {
                save_file();
            }
        });
        editor.commands.addCommand({
            name: 'close',
            bindKey: {win: 'Ctrl-W',  mac: 'Command-W'},
            exec: function(editor) {
                parent.global_closeOpenWindow();
            }
        });

        editor.getSession().on('change', function(e) {
            is_save = false;
            // 将该文件名变为待保存
            $("#min_title_list .active").addClass('red_color');
        });
    })
</script>
<script>
    var file_path = '<?=$file_path?>'; //文件路径
    var file_key = '<?=$file_key?>'; // 文件校验码
    var is_save = true;

    function save_file()
    {
        file_content = editor.getValue();
        if(is_save === true){
            parent.msg('未修改',1000,6);
            return false;
        }
        data = {
            'file_path' : file_path,
            'file_key' : file_key,
            'file_content' : file_content
        };
        $.post('<?=BASE_URL?>/index.php?a=save_file',data,function(respone_data){
            if(respone_data.error_no === 0){
                file_key = respone_data.data.file_key;
                $("#min_title_list .active").removeClass('red_color');
                is_save = true;
                parent.msg('保存成功',1000,1);

            }else{
                parent.msg(respone_data.msg,3000,2);
            }
        });
    }
</script>
