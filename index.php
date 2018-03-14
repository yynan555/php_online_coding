<?php
/**
 * 在线文件编辑器 入口文件
 */
// 配置开始**********************************
define('EDIT_LIMIT_DIR', 'D:\WWW\laravel55');
define('PASSWORD', 'admin');
// 配置结束**********************************

ini_set('max_execution_time', '0');
// ini_set('post_max_size', '150M');
// ini_set('upload_max_filesize', '100M');
// ini_set('max_file_uploads', '500');


define('BASE_DIR',  __DIR__ );
require_once('./Core/Autoloader.php');
\Core\Autoloader::register();
session_start();

use \Lib\CommonFun;

$action = CommonFun::params('a');

// 身份验证
if(!CommonFun::check_token() && $action != 'login'){
   CommonFun::view('login');
}

// 默认进入显示页面
if(empty($action) && !CommonFun::is_ajax()){
    CommonFun::view('index');
}

\Core\App::run($action);