<?php
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

							// preg_match("/Sec-WebSocket-Key:(.*)\r\n/",$match);
							// $key=$match[1];
							// $new_key=base64_encode(sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11'),true);

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
							//$buff  解析数据帧
							// echo $buff;
							$mask = array();  
					        $data = '';  
					        $msg = unpack('H*',$buff);  //用unpack函数从二进制将数据解码
					        $head = substr($msg[1],0,2);  
					        if (hexdec($head{1}) === 8) {  
					            $data = false;  
					        }else if (hexdec($head{1}) === 1){  
					            $mask[] = hexdec(substr($msg[1],4,2));  
					            $mask[] = hexdec(substr($msg[1],6,2));  
					            $mask[] = hexdec(substr($msg[1],8,2));  
					            $mask[] = hexdec(substr($msg[1],10,2));  
					           	
					            $s = 12;  
					            $e = strlen($msg[1])-2;  
					            $n = 0;  
					            for ($i=$s; $i<= $e; $i+= 2) {  
					                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));  
					                $n++;  
					            }
					            print_r($data);  
					        }
						}

					}
				}
			}
			echo $socket;
		}
		
	}

	$socket=new socket();
	$socket->index();