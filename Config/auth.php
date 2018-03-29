<?php
return [
	// 用户默认密码, 配置用户后默认使用本密码登录, 之后可以自行修改密码
	'default_password' => 'admin',
	
	'users' => [
		[
			'username' => 'admin',
			'access_dir' => [],
			'allow_access_ip' => []
		],
		[
			'username' => 'yaoyao',
			'access_dir' => []
		],
	],
	// 公共访问文件夹
	'public_access_dir' => [
	],

	//允许访问IP列表(如果空则没有IP限制)例如 某个具体的IP: '127.0.0.1' 或 IP范围'127.0.0.1/24'
	'public_allow_access_ip' =>[
	]
];