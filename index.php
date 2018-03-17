<?php
/**
 * 在线文件编辑器 入口文件
 */
ini_set('max_execution_time', '0');

define('BASE_DIR',  __DIR__ );

require_once('./Core/Autoloader.php');
\Core\Autoloader::register();

\Core\App::run();