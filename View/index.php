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
        <a class="btn btn-primary-outline radius size-MINI" href='<?=BASE_URL?>/index.php?a=logout' >退出登录</a>
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

<script type="text/javascript">
var base_url = '<?=BASE_URL?>';
</script>
<script type="text/javascript" src="<?=STATIC_PATH?>/js/index.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/js/jstree.js"></script>
</body>
</html>