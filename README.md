# php_online_coding
这个项目是以PHP为基础，实现在浏览器中对服务器代码进行在线编辑和查看。

# 功能
- 文件及文件夹增删改查
- 文件内容编辑
- 文件及文件夹上传
- 项目访问IP限制

# 如何使用
- 1，将该项目放到可以被客户端访问的路径中。
- 2，进入Config/app.php，设置该编辑器可以在线访问的服务器文件夹路径 和 登录密码。（如果是linux系统，需要设置改路径可修改权限为777）
```html
'edit_limit_dir' => ['D:\WWW\test'], //设置可以编辑的根文件夹
'allow_ip_list'  => ['127.0.0.1/24'] //设置允许访问的IP及范围
```
- 3，在浏览器中访问该项目，输入设置好的密码即可。

# 注意
该项目只是用于学习、开发及测试阶段。安全性，效率等问题还有待提高。

# 感谢
感谢 [jQuery](https://github.com/jquery/jquery) 、[ace](https://github.com/ajaxorg/ace) 、 [jstree](https://github.com/vakata/jstree) 、 [layer](https://github.com/sentsin/layer) 、[H-ui 前端框架](http://www.h-ui.net/)
