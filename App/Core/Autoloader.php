<?php
namespace App\Core;
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            // 判断如果定义BASE_DIR 则使用它
            if(defined('BASE_DIR')){
                $file = BASE_DIR.DIRECTORY_SEPARATOR.$file;
            }
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
}