<?php
namespace Core;

use \Lib\CommonFun;
use \Lib\Config;

class UserAuth
{
    public static function login($password)
    {
    	if( Config::get('app.password')  == $password){
            $_SESSION['token'] = true;
            CommonFun::go_operation();
        }else{
            CommonFun::view('login',['error_msg'=>'密码错误']);
        }
    }
    public static function logout()
    {
        unset($_SESSION['token']);
        echo '退出成功 返回<a href="'.CommonFun::url().'">登录页面</a>';
    }
    public static function checkAuth($check_value = [])
    {
    	return self::_allowIp() & self::_allowSession();
    }

    private static function _allowIp()
    {
    	$now_user_ip = self::get_ip();
    	$allow_ip = Config::get('app.allow_ip_list');
    	if( empty($allow_ip) ){
    		return true;
    	}
    	
    	foreach($allow_ip as $ip){
	    	if(self::ip_in_network($now_user_ip,$ip)){
	    		return true;
	    	}
    	}
    	CommonFun::view('login',['error_msg'=>"IP限制,当前IP :[$now_user_ip]"]);
    }
    private static function _allowSession()
    {
    	if( !isset($_SESSION['token']) ){
            return false;
        }
        return true;
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
}