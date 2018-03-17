<?php
namespace Lib;
/**
 * public function.
 * User: yaoyao
 * Date: 2018/2/24
 * Time: 13:48
 */
class CommonFun
{
    // 向客户端返回json字符串
    static public function respone_json( $msg, $data=[], $error_no = 0 )
    {
        header('Content-type: text/json;charset=UTF-8');
        $result['msg'] = $msg;
        $result['error_no'] = $error_no;
        if( !empty($data) ){
            $result['data'] = $data;
        }

        exit(json_encode($result));
    }
    static public function arr2Json($data)
    {
        header('Content-type: text/json');
        exit(json_encode($data));
    }

    // 跳转到某操作
    static public function go_operation($url='')
    {
        header('Location: '.self::url().'/index.php?a='.$url);
        exit;
    }

    // 获取绝对路径
    static public function url($url = '')
    {
        
        $base_path = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        if(isset($_SERVER['REQUEST_SCHEME'])){
            $base_path = $_SERVER["REQUEST_SCHEME"].'://'.$base_path;
        }else{
            $base_path = 'http://'.$base_path;
        }
        if( strpos($base_path, 'index.php') !== false){
            $url = preg_replace('#(.*)/+index\.php\?a=(.*)#i', "$1", $base_path).$url;
        }else{
            $url = $base_path.$url;
        }
        return $url;
    }

    // 检测用户身份
    static public function check_token()
    {
        if( !isset($_SESSION['token']) ){
            return false;
        }
        return true;
    }
    // 返回客户端视图
    static public function view($view_name , $data=[])
    {
        define('STATIC_PATH', CommonFun::url('public/static'));
        define('BATH_URL', CommonFun::url());

        $include_file_path = BASE_DIR.'/View/'.$view_name.'.php';
        if( is_file($include_file_path) ){
            if(!empty($data) && is_array($data) ) extract($data);
            include($include_file_path);
            exit;
        }else{
            throw new \Exception('not found this view file: '.$include_file_path);
        }
    }
    // 判断是否为ajax请求
    static public function is_ajax()
    {
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            return true;
        }else{
            return false;
        };
    }

    // 获取客户端传入参数
    static public function params($key, $default = '')
    {
        if(isset($_REQUEST[$key])){
            return $_REQUEST[$key];
        }
        return $default;
    }

    // 格式化打印数据
    static public function p($var)
    {
        if(is_bool($var)){
            var_dump($var);
        }else if(is_null($var)){
            var_dump(NULL);
        }else{
            echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>".print_r($var,true)."</pre>";
        }
    }

}