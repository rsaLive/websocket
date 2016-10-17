<?php
set_time_limit(0);
set_time_limit(0);
	class socket{
		protected $hand;
		public function  index()
		{
			$sockets=array();
			//建立套接字
			$socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
			socket_set_option($socket, SOL_SOCKET , SO_REUSEADDR, 1);
			socket_bind($socket,'127.0.0.1','8002');
			socket_listen($socket);
			$sockets[]=$socket;


			while(true){
				$write=$except=null;
				socket_select($sockets, $write, $except, null);
				foreach($sockets as $k=>$v){
					if($socket == $v){
						$client=socket_accept($socket);
						if($client <0){
							echo "socket_accept() failed";
						}else{
							array_push($sockets,$client);
						}
					}else{
						//从已连接的socket接收数据  返回的是从socket中接收的字节数
						$byte=socket_recv($v, $buff,20480, 0);
						//如果接收的字节是0 返回
						if($byte==0)
							return;
						//判断有没有握手没有握手则进行握手,如果握手了 则进行处理
						if(!$this->hand){
							//进行握手操作
							//提取websocket传的key并进行加密
							$buf  = substr($buff,strpos($buff,'Sec-WebSocket-Key:')+18);
					        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
					     
					        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));

							// $keys=preg_match("/Sec-WebSocket-Key:(.*)\r\n/",$buff);
							// $key=$keys[1];
							// $new_key=base64_encode(sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11'),true);
							// preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match);
							// $key = $match[1]; 
							// base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

							$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
					        $new_message .= "Upgrade: websocket\r\n";
					        $new_message .= "Sec-WebSocket-Version: 13\r\n";
					        $new_message .= "Connection: Upgrade\r\n";
					        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
					         // $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
				          //      "Upgrade: websocket\r\n" .
				          //      "Connection: Upgrade\r\n" .
				          //      "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
				          //      "\r\n";
					        socket_write($v,$new_message,strlen($new_message));
					        // socket_write(socket,$upgrade.chr(0), strlen($upgrade.chr(0)));
					        $this->hand=true;
						}else{
							//处理数据操作
							//$buff
						}

					}
				}
			}
			echo $socket;
		}
		
	}

	$socket=new socket();
	$socket->index();