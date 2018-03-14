<?php
namespace Core;

class App
{
    public static function run($action)
    {
        //执行请求
        $controller = new \App\Controller();

        $method = new \ReflectionMethod(\App\Controller::class, $action);
        $method_parames = $method->getParameters();
        $act_params = [];
        foreach($method_parames as $key => $param){
            $param_name = $param->name;
            if( !empty(\Lib\CommonFun::params($param_name)) ){
                $act_params[$key] = \Lib\CommonFun::params($param_name);
            }else{
                break;
            }
        }
        call_user_func_array(array($controller, $action), $act_params);
    }
}