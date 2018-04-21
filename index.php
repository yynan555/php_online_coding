<?php
/**
 * 在线文件编辑器
 * github:https://github.com/yynan555/php_online_coding
 */
require_once('./App/Core/Autoloader.php');
\App\Core\Autoloader::register();

define('BASE_DIR', \App\Core\CommonFun::dealPath(__DIR__) );

\App\Core\App::run();