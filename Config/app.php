<?php
return [
	// 注意: 修改配置文件后, 需要重新登录. 清除session中的数据

	// 用户默认密码, 配置用户后默认使用本密码登录, 之后可以自行修改密码
	'default_password' => 'admin',

	// 公共访问文件夹
	'public_access_dirs' => [
		// 'D:\WWW\test'
	],

	//允许访问IP列表(如果空则没有IP限制)例如 某个具体的IP: '127.0.0.1' 或 IP范围'127.0.0.1/24'
	'public_access_ips' =>[
	],

	// 允许用户尝试密码次数 不填则可以无限次尝试
	'password_attempts_num' => '10',

	// 用户表 , 配置用户与其相对应的可访问文件和ip
	'users' => [
		[
			'name' => 'admin',
			'access_dirs' => ['D:/WWW/test'], // 该用户可以访问的文件夹
			'access_ips' => [], // 该用户可以进行登录的IP
			'super_user' => true, //是否可以修改本项目 ,请慎重指定
			'email' => ''
		],
	],

	// mailable 允许发送email 在用户登录的时候会给超级管理员和自己发送邮件, 需配合users中的email进行发送邮件
	'email_host' => '',
	'email_username' => '',
	'email_password' => '',

	// 文件相关
	// 不可编辑文件类型 后缀名列表
	'unable_suffix' => 'pdf xls xlsx crt pem cer ppt pptx doc docx zip gz tar rar fla jar apk mp3 mp4 rmvb ico',
	// 用于显示图片的类型 后缀名列表
	'img_suffix' => 'jpg png jpeg gif bmp',
];