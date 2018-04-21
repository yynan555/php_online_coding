<?php
namespace App\Core;

use \App\Core\CommonFun;
use \App\Core\UserAuth;

class App
{
    public static function run()
    {
        session_start();

        date_default_timezone_set("Asia/Shanghai");

        $action = CommonFun::params('a','index');
        // 执行方法
        self::exec('\App\Controller\Controller',$action);
    }

    public static function exec($class_name,$action)
    {
        //执行请求
        $controller = new $class_name();

        if(!method_exists($controller,$action)){
            throw new \Exception("not found function:{$action} in {$class_name}");
        }

        $act_params = [];
        $method = new \ReflectionMethod($class_name, $action);
        $method_parames = $method->getParameters();
        foreach($method_parames as $key => $param){
            $param_name = $param->name;
            if( !empty(CommonFun::params($param_name)) ){
                $act_params[$param_name] = CommonFun::params($param_name);
            }else{
                break;
            }
        }
        // 执行控制器的前置方法
        if(method_exists($controller,'beforeFun')){
            $controller->beforeFun($action,$act_params);
        }  
        // 执行控制器方法
        call_user_func_array(array($controller, $action), $act_params);
        // 执行控制器的后置方法
        if(method_exists($controller,'afterFun')){
            $controller->afterFun($action,$act_params);
        }
    }
}