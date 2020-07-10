<pre id="file_area" style="height: 100%;width: 100%;margin:0px auto 0 auto;border-radius:0px; padding:0px;"><?=htmlspecialchars($file_content)?></pre>

<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ace.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ext-settings_menu.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ext-language_tools.js"></script>
<style>
    /* 智能补全宽度修改 */
    .ace_editor.ace_autocomplete {
        width: 50%;
    }
    .ace-monokai .ace_marker-layer .ace_selected-word {
        border: 1px solid #fff;
    }
</style>
<script>
    var editor;
    var ace_language;
    var $ = parent.$;
    $(function(){
        //初始化对象
        editor = ace.edit( 'file_area' );

        //设置风格和语言（更多风格和语言，请到github上相应目录查看）
        theme = "monokai"; //黑色主题
        // theme = "clouds"; //白色主题
        language = "<?=( isset($file_info['extension']) )?$file_info['extension']:'Text';?>";

        editor.setTheme("ace/theme/" + theme);

        // 文件类型到ace文件标识
        var language_map = {
            js: 'javascript',
            htm: 'html',
            md: 'markdown'
        };

        if(language){
            if (typeof language_map[language] !== "undefined") {
                language = language_map[language];
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
        ace_language = ace.require("ace/ext/language_tools");
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
        editor.commands.addCommand({
            name: 'close2',
            bindKey: {win: 'Esc',  mac: 'Esc'},
            exec: function(editor) {
                parent.global_closeOpenWindow();
            }
        });
        editor.commands.addCommand({
            name: 'toNextLine',
            bindKey: {win: 'Shift+Enter',  mac: ''},
            exec: function(_editor) {
                _editor.selection.clearSelection();
                _editor.navigateLineEnd();
                _editor.insert("\n");
            }
        });

        editor.commands.addCommand({
            name: "showKeyboardShortcuts",
            bindKey: {win: "Ctrl-Alt-h", mac: "Command-Alt-h"},
            exec: function(editor) {
                ace.config.loadModule("ace/ext/keybinding_menu", function(module) {
                    module.init(editor);
                    editor.showKeyboardShortcuts()
                })
            }
        })

        editor.commands.addCommand({
            name: "yao_addselect_next",
            bindKey: {win: "Alt+J", mac: ""},
            exec: function(editor) {
                editor.selectMore(1);
            }
        })

        editor.commands.addCommands([{
            name: "showSettingsMenu",
            bindKey: {win: "Ctrl-q", mac: "Ctrl-q"},
            exec: function(editor) {
                editor.showSettingsMenu();
            },
            readOnly: true
        }]);
        ace.require('ace/ext/settings_menu').init(editor);
        editor.getSession().on('change', function(e) {
            is_save = false;
            // 将该文件名变为待保存
            $("#min_title_list .active").addClass('red_color');
        });
    })
</script>
<script><!-- 代码自动补全 -->
    <?php
        $autoCompleterData = [];
        $autoCompleterRawData = require(BASE_DIR.'/Config/autoCompleter.php');
        if (!empty($autoCompleterRawData)) {
            foreach ($autoCompleterRawData as $meta => $keywordsInfos) {
                foreach ($keywordsInfos as $keyword => $doc) {
                    if (is_numeric($keyword)) {
                        $keyword = $doc;
                        $doc = '';
                    }
                    if (!$keyword) continue;
                    $autoCompleterData[] = [
                        'name' => $keyword,
                        'value' => $keyword,
                        'docHTML' => $doc,
                        'meta' => $meta,
                        'score' => 999
                    ];
                }
            }
        }
        echo 'var $autoCompleterData = '.json_encode($autoCompleterData).';';
    ?>

    // 解析当前页面方法
    //  解析(方法)
    var reg = /function[\s]+([0-9a-zA-Z_]*)(\([0-9a-zA-Z\s\$\,_\(\)\=\[\]]*\))\s*\{?/g;
    var autoCompleter_preStr = '$this->'; // 自动补全前缀
    while(reg.exec(editor.getValue()) != null)
    {
        var value = autoCompleter_preStr+RegExp.$1+RegExp.$2+';';
        $autoCompleterData.push({
            name: value,
            value: value,
            docHTML: value,
            meta: 'local',
            score: 1000
        });
    }
    //  解析(属性)
    var reg = /(public|private|protect)\s+\$([a-zA-Z0-9_]+)/g;
    while(reg.exec(editor.getValue()) != null)
    {
        var value = autoCompleter_preStr+RegExp.$2;
        $autoCompleterData.push({
            name: value,
            value: value,
            docHTML: value,
            meta: 'local',
            score: 1001
        });
    }

    if (typeof $autoCompleterData !== "undefined" && $autoCompleterData.length > 0) {
        ace_language.addCompleter({
            getCompletions: function(editor, session, pos, prefix, callback) {
                callback(null,  $autoCompleterData);
            }
        });
    }
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
