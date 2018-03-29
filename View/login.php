<!DOCTYPE html>
<html>
<head>
	<title>在线代码编辑 - 登录</title>
</head>
<body>
	<h1>登录</h1>
	<form action='<?=BASE_URL?>/index.php?a=login' method='POST'>
		密码: <input type='password' name='password'><span style="color:red"><?=isset($error_msg)?$error_msg:'';?></span><br/>
		<input type='submit'/>
	</form>
</body>
</html>