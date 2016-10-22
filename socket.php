<?php
	error_reporting(E_ALL);
	set_time_limit(0);
	ob_implicit_flush();

	$socket=new socket('127.0.0.1','8000');
	$socket->run();

	class socket{
		protected $hand;
		public $soc;
		public $socs;
		public function  __construct($address,$port)
		{
			//建立套接字
			$this->soc=$this->createSocket($address,$port);
			$this->socs=array($this->soc);

		}
		public function createSocket()
		{
			$socket= socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
	        socket_bind($socket, '127.0.0.1','8000');
	        socket_listen($socket);
	        return $socket;
		}

		public function run(){
			while(true){
				$arr=$this->socs;
				$write=$except=NULL;
				socket_select($arr,$write,$except, NULL);
				foreach($arr as $k=>$v){
					if($this->soc == $v){
						$client=socket_accept($this->soc);
						if($client <0){
							echo "socket_accept() failed";
						}else{
							// array_push($this->socs,$client);
							// unset($this[]);
							$this->socs[]=$client;
						}
					}else{
						//从已连接的socket接收数据  返回的是从socket中接收的字节数
						$byte=socket_recv($v, $buff,20480, 0);
						//如果接收的字节是0 返回
						if($byte<7)
							continue;
						//判断有没有握手没有握手则进行握手,如果握手了 则进行处理
						if(!$this->hand[(int)$client]){
							//进行握手操作
							//提取websocket传的key并进行加密
							$buf  = substr($buff,strpos($buff,'Sec-WebSocket-Key:')+18);
					        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
					     
					        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
							$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
					        $new_message .= "Upgrade: websocket\r\n";
					        $new_message .= "Sec-WebSocket-Version: 13\r\n";
					        $new_message .= "Connection: Upgrade\r\n";
					        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
					        socket_write($v,$new_message,strlen($new_message));
					        // socket_write(socket,$upgrade.chr(0), strlen($upgrade.chr(0)));
					        $this->hand[(int)$client]=true;
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
					           	//遇到的问题  刚连接的时候就发送数据  显示 state connecting
					            $s = 12;  
					            $e = strlen($msg[1])-2;  
					            $n = 0;  
					            for ($i=$s; $i<= $e; $i+= 2) {  
					                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));  
					                $n++;  
					            }
					            //发送数据到客户端
					           	//如果长度大于125 将数据分块
					           	$block=str_split($data,125);
					           	$mess=array(
					           		'mess'=>$block[0],
					           		);
					           	// $writes ="\x81".chr(strlen($block[0])).$block[0];
					           	foreach ($this->socs as $keys => $values) {
					           		print_r('aaaaa=>'.$values);
					           		$mess['name']="游客{$v}";
					           		$str=json_encode($mess);
					           		$writes ="\x81".chr(strlen($str)).$str;
					           		// if($this->hand[(int)$values])
					           			socket_write($values,$writes,strlen($writes));
					           	}
					        }
						}
					}
				}
			}
		}
		
	}
