<!DOCTYPE html>
<html>
<head>
	<title>在线代码编辑 - 登录</title>
</head>
<body>
	<div style="margin:100px auto;width:150px">
		<h1>登录</h1>
		<form action='<?=BASE_URL?>/index.php?a=login' method='POST'>
			用户名 : <input type='text' name='username'><br/>
			密&nbsp;&nbsp;&nbsp;&nbsp;码 : <input type='password' name='password'><br/>
			<span style="color:red"><?=isset($error_msg)?$error_msg:'';?></span><br/>
			<input type='submit' value='登录' />
		</form>
	</div>
</body>
</html>