<!DOCTYPE html>
<html>
<body>
	<div style="margin:100px auto;width:150px">
		<h1>设置密码</h1>
		<form action='<?=BASE_URL?>/index.php?a=set_password' method='POST'>
			旧密码 : <input type='password' name='old_password'><br/>
			新密码 : <input type='password' name='new_password'><br/>
			再次输入 : <input type='password' name='new_password2'><br/>
			<span style="color:red"><?=isset($error_msg)?$error_msg:'';?></span><br/>
			<input type='submit' value='确认修改' />
		</form>
	</div>
</body>
</html>