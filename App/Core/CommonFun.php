<?php
namespace App\Core;
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

        exit(json_encode($result,JSON_UNESCAPED_UNICODE));
    }
    static public function arr2Json($data)
    {
        header('Content-type: text/json');
        exit(json_encode($data,JSON_UNESCAPED_UNICODE));
    }

    // 跳转到某操作
    static public function go_operation($url='')
    {
        if($url){
            header('Location: '.self::url().'/index.php?a='.$url);
        }else{
            header('Location: '.self::url());
        }
        
        exit;
    }

    // 获取绝对路径
    static public function url($url = '')
    {
        static $static_url;
        if(!$static_url){
            $static_url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            //如果右侧有'/' 将其取消
            $static_url = rtrim($static_url,'/');

            if(isset($_SERVER['REQUEST_SCHEME'])){
                $static_url = $_SERVER["REQUEST_SCHEME"].'://'.$static_url;
            }else{
                $static_url = 'http://'.$static_url;
            }
            if( ($pos = strpos($static_url, '/index.php')) !== false){
                $static_url = substr( $static_url,0,$pos );
            }
        }
        return $static_url.$url;
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
        define('STATIC_PATH', CommonFun::url('/public/static'));
        define('BASE_URL', CommonFun::url());

        $include_file_path = BASE_DIR.'/App/View/'.$view_name.'.php';
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
    // 数组排序
    public static function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
    
    /**
     * 如果是win系统, 处理路径
     * @param $path
     * @param bool $G2U 国标码转为UTF-8
     * @return bool|mixed|string
     */
    public static function dealPath($path, $type = '')
    {
        static $is_win;
        if($is_win === null){
            $is_win = (strtoupper(substr(PHP_OS,0,3))==='WIN')?true:false;
        }
        if(!$is_win){
            return $path;
        }

        $path = str_replace("\\",'/',$path);

        if($type == 'u2g'){
            $path = self::iconv_to($path,'UTF-8','GBK');
        }else if($type == 'g2u'){
            // 判断编码是否为utf-8
            if(!mb_detect_encoding($path, 'UTF-8', true)){
                $path = self::iconv_to($path,'GBK','UTF-8');
            }
        }
        return $path;
    }
    public static function iconv_to($str,$from,$to){
        if (!function_exists('iconv')){
            return $str;
        }

        if(function_exists('mb_convert_encoding')){
            $result = @mb_convert_encoding($str,$to,$from);
        }else{
            $result = @iconv($from, $to, $str);
        }
        if(strlen($result)==0){ 
            return $str;
        }
        return $result;
    }

    /**
     * 判断IP是否在某个网络内 
     * @param $ip
     * @param $network
     * @return bool
    */
    public static function ip_in_network($ip, $network)
    {
        $ip = (double) (sprintf("%u", ip2long($ip)));
        $s = explode('/', $network);
        $network_start = (double) (sprintf("%u", ip2long($s[0])));
        if(isset($s[1])){
            $network_len = pow(2, 32 - $s[1]);
        }else{
            $network_len = 1;
        }

        $network_end = $network_start + $network_len - 1;

        if ($ip >= $network_start && $ip <= $network_end)
        {
            return true;
        }
        return false;
    }

    public static function get_ip_address($ip = ''){
        if(empty($ip)){
            $ip = self::get_ip();
        }
//        $request_ip_url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;

        $ip_info = '';// json_decode(file_get_contents($request_ip_url),true);

        if($ip_info && isset($ip_info['code']) && $ip_info['code'] == 0){
            return $ip_info['data']['region'].$ip_info['data']['city'].$ip_info['data']['county'].' '.$ip_info['data']['isp'].' ip: '.$ip;
        }else{
            return $ip;
        }
    }
    //不同环境下获取真实的IP
    public static function get_ip(){
        //判断服务器是否允许$_SERVER
        if(isset($_SERVER)){
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        }else{
            //不允许就使用getenv获取  
            if(getenv("HTTP_X_FORWARDED_FOR")){
                  $realip = getenv( "HTTP_X_FORWARDED_FOR");
            }elseif(getenv("HTTP_CLIENT_IP")) {
                  $realip = getenv("HTTP_CLIENT_IP");
            }else{
                  $realip = getenv("REMOTE_ADDR");
            }
        }

        return $realip;
    }

    public static function base64_encode_image($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}