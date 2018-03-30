<?php
namespace Core;

use \Core\CommonFun;
use \Core\Config;

class UserAuth
{    
    const USER_CACHE_PATH = BASE_DIR.'/Cache/User';
    const SESSION_PREFIX = 'poc_';
	// 检测用户是否合法
    public static function checkAuth($check_value = [])
    {
    	if( !( self::_allowSession()) ){
    		return false;
    	}
        return true;
    }
    // 登录
    public static function login($username,$password)
    {
        if(empty($username) || empty($password)) CommonFun::view('login',['error_msg'=>'用户名或密码未填写']);

        $now_user = [];
        foreach(Config::get('app.users') as $user){
            if($user['name'] === $username){
                $now_user = $user;
                break;
            }
        }
        if($now_user){
            self::_ipCheck($username);
            $user_info = self::getUserCache($now_user['name']);
            // 用户缓存不存在则创建用户默认缓存
            if(!$user_info){
                $user_info = [
                    'username' => $username,
                    'try_login_num' => 0,
                    'password' => md5(Config::get('app.default_password'))
                ];
                self::setUserCache($now_user['name'],$user_info);
            }
            // 用户尝试次数+1
            $user_info['try_login_num'] += 1;

            if(Config::get('app.password_attempts_num') && ($user_info['try_login_num'] >= Config::get('app.password_attempts_num'))){
                CommonFun::view('login',['error_msg'=>'密码错误次数过多, 不可登录, 可以通过在服务器中删除 [/Cache/User/'.md5($username).'] 文件, 再使用默认密码登录']);
            }
            // 判断用户密码是否正确
            if(md5($password) !== $user_info['password']){
                self::setUserCache($now_user['name'],$user_info);

                $limit_login_message = empty(Config::get('app.password_attempts_num'))?'':'，剩余尝试次数:'.(Config::get('app.password_attempts_num')-$user_info['try_login_num']);
                CommonFun::view('login',['error_msg'=>'密码错误'.$limit_login_message]);
            }
            // 登录成功 设置尝试登录次数为0
            self::setUserCache($now_user['name'],['try_login_num'=>0]);

            $_SESSION[self::SESSION_PREFIX.'username'] = $username;
            if(isset($now_user['super_user']) && $now_user['super_user'] === true) $_SESSION[self::SESSION_PREFIX.'is_super_user'] = true;
            CommonFun::go_operation();
        }else{
            CommonFun::view('login',['error_msg'=>'该用户不存在']);
        }
    }
    // 登出
    public static function logout()
    {
        session_destroy(); 
    }
    // 设置密码
    public static function setPassword($old_password,$new_password)
    {
        if(self::checkAuth()){
            $user_info = self::getUserCache($_SESSION[self::SESSION_PREFIX.'username']);
            if($user_info['password'] !== md5($old_password)) CommonFun::view('user_set_password',['error_msg'=>'原密码错误']);

            self::setUserCache($_SESSION[self::SESSION_PREFIX.'username'],[ 'password'=>md5($new_password) ]);
            return true;
        }else{
            return false;
        }
    }
    // 获取当前登录用户允许访问根文件夹
    public static function getLimitDir()
    {
        if(isset($_SESSION[self::SESSION_PREFIX.'limit_dirs'])){
            return $_SESSION[self::SESSION_PREFIX.'limit_dirs'];
        }else{
            $limit_dirs = Config::get('app.public_access_dirs',[]);
            $now_user = [];
            foreach(Config::get('app.users') as $user){
                if($user['name'] === $_SESSION[self::SESSION_PREFIX.'username']){
                    $now_user = $user;
                    break;
                }
            }
            if(isset($now_user['access_dirs']) && !empty($now_user['access_dirs'])){
                $limit_dirs = array_merge($limit_dirs,$now_user['access_dirs']);
            }
            // 判断是否是超级用户
            if(self::isSuperUser()){
                $limit_dirs = array_merge($limit_dirs,(array)BASE_DIR);
            }

            $limit_dirs = array_unique($limit_dirs);
            foreach ($limit_dirs as &$limit_dir) {
                $limit_dir = CommonFun::dealPath($limit_dir);
            }
            $_SESSION[self::SESSION_PREFIX.'limit_dirs'] = $limit_dirs;
            return $_SESSION[self::SESSION_PREFIX.'limit_dirs'];
        }
    }
    // 获取当前登录用户允许IP域
    public static function getLimitIP($username)
    {
        $limit_ips = Config::get('app.public_access_ips',[]);
        $now_user = [];
        foreach(Config::get('app.users') as $user){
            if($user['name'] === $username){
                $now_user = $user;
                break;
            }
        }
        if(isset($now_user['access_ips']) && !empty($now_user['access_ips'])){
            $limit_ips = array_merge($limit_ips,$now_user['access_ips']);
            $limit_ips = array_unique($limit_ips);
        }
        return $limit_ips;
    }
    private static function _ipCheck($username)
    {
    	$now_user_ip = self::get_ip();
    	$allow_ip = self::getLimitIP($username);
    	if( empty($allow_ip) ){
    		return true;
    	}
    	
    	foreach((array)$allow_ip as $ip){
	    	if(self::ip_in_network($now_user_ip,$ip)){
	    		return true;
	    	}
    	}
    	CommonFun::view('login',['error_msg'=>"当前IP不可登录 :[$now_user_ip]"]);
    }
    private static function _allowSession()
    {
    	if( !isset($_SESSION[self::SESSION_PREFIX.'username']) ){
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
    // 检测文件路径是否在允许访问域中
    public static function checkDomain()
    {
        static $allow_root_paths = null;
        if($allow_root_paths === null){
            $allow_root_paths = UserAuth::getLimitDir();
        }
        if(empty($allow_root_paths)) CommonFun::respone_json('该用户暂无可访问路径','',199);

        $args = func_get_args();

        foreach($args as $path){
            if(empty($path)) CommonFun::respone_json('路径错误','',199);

            if(!self::isSuperUser() && (strpos($path,  BASE_DIR ) === 0)) return false;

            foreach($allow_root_paths as $allow_path){
                if(!empty($allow_path) && strpos( $path ,  $allow_path ) === 0){
                    continue 2;
                }
            }
            return false;
        }
        return true;
    }
    // 得到用户缓存信息
    private static function getUserCache($username)
    {
        $usercache_filename = self::USER_CACHE_PATH.'/'.md5($username);
        if(!is_file($usercache_filename)) return false;

        return json_decode(File::getFileContent($usercache_filename), true);
    }
    // 设置用户缓存信息
    private static function setUserCache($username, $info)
    {
        $usercache_filename = self::USER_CACHE_PATH.'/'.md5($username);

        if( !($user_old_info = self::getUserCache($username)) ){
            File::createFile($usercache_filename);
        }else{
            $info = array_merge($user_old_info, $info);
        }

        File::setFileContent($usercache_filename, json_encode($info));
    }
    public static function isSuperUser()
    {
        if(isset($_SESSION[self::SESSION_PREFIX.'is_super_user']) && $_SESSION[self::SESSION_PREFIX.'is_super_user'] === true){
            return true;
        }else{
            return false;
        }
    }
}