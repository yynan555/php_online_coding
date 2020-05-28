// 绑定关闭页面前事件
$(window).bind('beforeunload', function(){ 
    return '确认关闭当前编辑器吗'; 
});

// 为页面添加默认键盘事件
$(window).keydown(function(e) {
    if (e.keyCode == 27) { // Esc
        global_closeOpenWindow();
        e.preventDefault();
    }

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

// 左侧框拉动事件
$(function(){
    if(typeof(localStorage) != 'undefined'){
        menu_width = localStorage.getItem('menu_width');
        if(menu_width){
            changeLeftSize(menu_width);
        }
    }

    var is_on_border = false;
    var allow_resize = false;
    $('body').bind({
        mousemove:function(e){
            // 得到左侧边框位置
            var la_width = $('#left_area').width();
            var la_outerWidth = $('#left_area').outerWidth();
            var x = e.clientX;
            if(x>(la_width-3) && x<la_outerWidth){
                $('#left_area').css("cursor",'e-resize');
                is_on_border = true;
            }else{
                $('#left_area').css("cursor",'');
                is_on_border = false;
            }

            // 若鼠标按下 就可以进行窗口大小改变
            if(allow_resize){
                changeLeftSize(x);
                if(typeof(localStorage) != 'undefined'){
                    localStorage.setItem('menu_width',x)
                }
            }
        },
        mousedown:function(e){
            if(is_on_border){
                allow_resize = true;
            }
        },
        mouseup:function(){
            allow_resize = false;
        }
    });
});