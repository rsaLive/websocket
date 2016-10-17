<html>
<script src="//cdn.bootcss.com/jquery/3.1.1/jquery.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<input type="text" name="content">
	<body>
		
	</body>
	<script>
		var ws= new WebSocket('ws://127.0.0.1:8002');
		ws.onopen=function(evt){
			console.log('握手成功'+evt);
		}
		ws.onerror=function(evt){
			console.log('error'+evt);
		}
	</script>
</html>