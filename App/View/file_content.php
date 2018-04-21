<html>
<head>

</head>
<body style="margin:0px auto 0 auto;border-radius:0px; padding:0px;">
<?php
    include 'file_content_code.php';
?>
<script type="text/javascript">
	// 关闭这个窗口
	function closeThisWindow(){
		var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
		parent.layer.close(index); //再执行关闭
	}
</script>
</body>
</html>