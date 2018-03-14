<?php
namespace Core;
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
}