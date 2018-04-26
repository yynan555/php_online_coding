<?php
return [
	// 注意: 修改配置中的 访问目录(*access_dirs) 后, 需要重新登录. 

	// 用户默认密码, 配置用户后默认使用本密码登录, 之后可以自行修改密码
	'default_password' => 'admin',
	// 如果用户修改密码，但是忘记，可以进入 Cache/User 文件夹, 里面文件命名方式为 md5('用户名'), 可以点击查看对应文件中信息是否为该用户, 对其进行 删除 或 修改

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
			'super_user' => true, //是否可以修改本项目 ,请慎重指定(默认有访问数据库权限)
			'email' => ''
		],
		[
			'name' => 'user1',
			'access_dirs' => ['D:/WWW/test'], // 该用户可以访问的文件夹
			'access_database_able' => true, // 是否具有操作数据库能力(super_user 拥有该权限)
			'access_ips' => [], // 该用户可以进行登录的IP
			'email' => ''
		],
	],

	// mailable 允许发送email 在用户登录的时候会给超级管理员和自己发送邮件, 需配合users中的email进行发送邮件
	// 在配置完成邮箱以及用户邮箱账号, 会在以下两种情况为用户对应邮箱发送消息:
	// 1, 用户登录 (为该用户 和 super_user发送 包括登录时间,登录网站,登录IP及简单地理信息等信息)
	// 2, 用户修改密码 (为该用户发送邮件 ,其中包含修改后的账号密码)
	'email_host' => '',
	'email_username' => '',
	'email_password' => '',

	// 文件相关
	// 不可编辑文件类型 后缀名列表
	'unable_suffix' => 'pdf xls xlsx crt pem cer ppt pptx doc docx zip gz tar rar fla jar apk mp3 mp4 rmvb ico',
	// 用于显示图片的类型 后缀名列表
	'img_suffix' => 'jpg png jpeg gif bmp',
];