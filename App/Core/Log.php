<?php
namespace App\Core;

use App\Core\CommonFun;
use App\Core\File;
use App\Core\UserAuth;

class Log
{
	// 日志存放位置
	const LOG_DIR = BASE_DIR.'/Log';

	public static function write($content){
		$username = (isset($_SESSION) && (UserAuth::session('username')) )? UserAuth::session('username'):'Unknown';
		$new_log_filepath = self::LOG_DIR.'/'.date('Ym').'/'.date('d_').$username.'.log';
		
		if(!is_file(CommonFun::dealPath($new_log_filepath,'u2g'))){
			File::createFile($new_log_filepath);
		}
		File::setFileContent($new_log_filepath, self::logFormat($content) ,'a');
	}
	// 日志格式
	private static function logFormat($content)
	{
		$content_arr = [
			'['.date('Y-m-d H:i:s',time()).']',
			'('.(empty(UserAuth::session('address'))?CommonFun::get_ip_address():UserAuth::session('address')).')',
			$content
		];
		$content = implode("\t", $content_arr);
		return $content."\n";
	}
}