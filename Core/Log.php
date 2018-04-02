<?php
namespace Core;

use \Core\CommonFun;
use \Core\File;
use \Core\UserAuth;

class Log
{
	// 日志存放位置
	const LOG_DIR = BASE_DIR.'/Cache/Log';

	public static function write($content){
		$new_log_filepath = self::LOG_DIR.'/'.date('Y_m_d').'.log';
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
			(isset($_SESSION) && (UserAuth::session('username')) )? UserAuth::session('username'):'null',
			'('.UserAuth::session('address').')',
			$content
		];
		$content = implode("\t", $content_arr);
		return $content."\n";
	}
}