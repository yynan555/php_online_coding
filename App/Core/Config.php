<?php
namespace App\Core;

class Config
{
	static private $configs = [];

	static public function get($conf_key , $default='')
	{
		if(empty($conf_key) || count(explode('.', $conf_key)) !== 2){
			throw new \Exception("param error: [$conf_key] ,parameter such as 'app.conf1'");
			
		}
		$conf_arr = explode('.', $conf_key);
		$file_name = $conf_arr[0];
		$conf_name = $conf_arr[1];
		$file_path = str_replace("\\",'/',BASE_DIR).'/Config/'.$file_name.'.php';

		if( isset(self::$configs[$file_name]) ){
			if( !isset(self::$configs[$file_name][$conf_name]) ){
				if(!empty($default)) return $default;
				throw new \Exception("Config [$conf_name] Undefined in file : $file_path");
			}
			return self::$configs[$file_name][$conf_name];
		}else{
			if( is_file($file_path) ){
				$arr = require($file_path);
				self::$configs[$file_name] = $arr;
				return self::$configs[$file_name][$conf_name];
			}else{
				if(!empty($default)) return $default;
				throw new \Exception("not found file : $file_path");
			}
		}
	}
}