<?php
/**
 * 在线文件编辑器
 * github:https://github.com/yynan555/php_online_coding
 */
require_once('./Core/Autoloader.php');
\Core\Autoloader::register();

define('BASE_DIR',  \Core\CommonFun::dealPath(__DIR__) );

\Core\App::run();