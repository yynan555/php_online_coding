<?php
return [
	
	// 访问密码
	'password' => 'admin',
	// 允许可以访问的项目路径
	'edit_limit_dir' => ['D:\WWW\test'],

	//允许访问IP列表(如果空则没有IP限制)例如 某个具体的IP: '127.0.0.1' 或 IP范围'127.0.0.1/24'
	'allow_ip_list' =>[],

	//读文件
	// 不可编辑文件类型 后缀名列表
	'unable_suffix' => 'pdf xls xlsx crt pem cer ppt pptx doc docx zip gz tar rar fla jar apk mp3 mp4 rmvb ico',
	// 用于显示图片的类型 后缀名列表
	'img_suffix' => 'jpg png jpeg gif bmp',
];