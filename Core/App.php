<?php
namespace Core;

use \Lib\CommonFun;
use \Core\UserAuth;

class App
{
    public static function run()
    {
        session_start();

        $action = CommonFun::params('a');
        // 身份验证
        if(!UserAuth::checkAuth() && $action != 'login'){
           CommonFun::view('login');
        }

        // 默认进入显示页面
        if(empty($action) && !CommonFun::is_ajax()){
            CommonFun::view('index');
        }
        // 执行方法
        self::exec('\App\Controller',$action);
    }

    public static function exec($class_name,$action)
    {
        //执行请求
        $controller = new $class_name();

        $method = new \ReflectionMethod($class_name, $action);
        $method_parames = $method->getParameters();
        $act_params = [];
        foreach($method_parames as $key => $param){
            $param_name = $param->name;
            if( !empty(CommonFun::params($param_name)) ){
                $act_params[$key] = CommonFun::params($param_name);
            }else{
                break;
            }
        }
        call_user_func_array(array($controller, $action), $act_params);
    }
}