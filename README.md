# php_online_coding
这个项目是以PHP为基础，实现在浏览器中对服务器代码进行在线编辑和查看。

# 功能
- 基于Jstree的文件及文件夹查看、添加、删除、移动、复制操作
- 文件内容在线查看和编辑
- 文件及文件夹上传
- 用户访问IP、访问文件和尝试输入密码次数限制
- 用户修改密码
- 用户操作日志记录
- 用户登录及修改密码邮件通知

# 如何使用
- 1，将该项目放到可以被客户端访问的路径中。
- 2，进入Config/app.php，设置该编辑器可以在线访问的服务器文件夹路径 和 登录密码。（如果是linux系统，需要设置改路径可修改权限为777）
```html
	// 用户默认密码, 配置用户后默认使用本密码登录, 之后可以自行修改密码
	'default_password' => 'admin',

	// 公共访问文件夹
	'public_access_dirs' => [
		'D:\WWW\test'
	],

	//允许访问IP列表(如果空则没有IP限制)例如 某个具体的IP: '127.0.0.1' 或 IP范围'127.0.0.1/24'
	'public_access_ips' =>[
	],

	// 用户表 , 配置用户与其相对应的可访问文件和ip
	'users' => [
		[
			'name' => 'admin',
			'access_dirs' => ['D:\WWW\yao要.txt'], // 该用户可以访问的文件夹
			'access_ips' => [], // 该用户可以进行登录的IP
			'super_user' => true, //是否可以修改本项目 ,请慎重指定
			'email' => 'asdif@qq.com'
		],
		[
			'name' => '测试账号',
			'access_dirs' => []
		],
	],
	// 基于stmp 允许发送email 在用户登录的时候会给超级管理员和自己发送邮件, 需配合users中的email进行发送邮件
	'email_host' => '',
	'email_username' => '',
	'email_password' => '',
```
- 3，在浏览器中访问该项目！。

# 注意
该项目只是用于学习、开发及测试阶段。安全性，效率等问题还有待提高。

# 感谢
感谢 [jQuery](https://github.com/jquery/jquery) 、[ace](https://github.com/ajaxorg/ace) 、 [jstree](https://github.com/vakata/jstree) 、 [layer](https://github.com/sentsin/layer) 、[H-ui 前端框架](http://www.h-ui.net/)
