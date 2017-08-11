<?php
date_default_timezone_set("Asia/Hong_Kong"); 
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ERROR);
ob_implicit_flush();
session_start();
$sk=new Sock('127.0.0.2',8000);
$sk->run();
class Sock{
	public $sockets;
	public $users;
	public $master;
	
	private $sda=array();//已接收的数据
	private $slen=array();//数据总长度
	private $sjen=array();//接收数据的长度
	private $ar=array();//加密key
	private $n=array();
	
	public function __construct($address, $port){
		$this->master=$this->WebSocket($address, $port);
		//把socket放到变量里面
		$this->sockets=array($this->master);
	}
	
	//对创建的socket循环进行监听，处理数据
	function run(){
	    //死循环，直到socket断开
		while(true){
		    
		    /*
		    //这个函数是同时接受多个连接的关键，我的理解它是为了阻塞程序继续往下执行。
		      socket_select ($sockets, $write = NULL, $except = NULL, NULL);
		      
		       $sockets可以理解为一个数组，这个数组中存放的是文件描述符。当它有变化
		                  （就是有新消息到或者有客户端连接/断开）时，socket_select函数才会返回，  继续往下执行。
		                        
		      $write是监听是否有客户端写数据，传入NULL是不关心是否有写变化。
		      $except是$sockets里面要被排除的元素，传入NULL是”监听”全部。
    		   
    		   最后一个参数是超时时间
                        如果为0：则立即结束
                        如果为n>1: 则最多在n秒后结束，如遇某一个连接有新动态，则提前返回
                        如果为null：如遇某一个连接有新动态，则返回
                		      
		    */
		    
			$changes=$this->sockets;
			$write=NULL;
			$except=NULL;
			//这个函数是同时接受多个连接的关键，我的理解它是为了阻塞程序继续往下执行。
			socket_select($changes,$write,$except,NULL);
			foreach($changes as $sock){
			    
			    //如果有新的client连接进来，则
				if($sock==$this->master){
				    echo "new in";
				   // print_r($sock);
				    //接受一个socket连接
					$client=socket_accept($this->master);
 					//echo " c is ".$client;
					socket_recv($sock,$buf,1000,0);
					//给新连接进来的socket一个唯一的ID
					$key=uniqid();
					$this->sockets[]=$client;  //将新连接进来的socket存进连接池
					$this->users[$key]=array(
						'socket'=>$client,    //记录新连接进来client的socket信息
						'shou'=>false  ,       //标志该socket资源没有完成握手
						'send_id'=>null,
					    'to_id'  =>null ,
					    'name'   =>null
					);
				}else{
					$len=0;
					$buffer='';
					do{
					    //读取该socket的信息，注意：第二个参数是引用传参即接收数据，第三个参数是接收数据的长度
						$l=socket_recv($sock,$buf,1000,0);
						
						$len+=$l;
						$buffer.=$buf;
						//echo iconv('utf-8','gbk//IGNORE',"msg is ".$buf);
					}while($l==1000);
					
					//根据socket在user池里面查找相应的$k,即健ID
					$k=$this->search($sock);
					
					//退出,//如果接收的信息长度小于7，则该client的socket为断开连接
					if($len<7){
					    //给该client的socket进行断开操作，并在$this->sockets和$this->users里面进行删除
						
					    $this->send2($k);
						continue;
					}
					//判断该socket是否已经握手
					if(!$this->users[$k]['shou']){
					    //如果没有握手，则进行握手处理
						$this->woshou($k,$buffer);
					}else{
					    //走到这里就是该client发送信息了，对接受到的信息进行uncode处理
						$buffer = $this->uncode($buffer,$k);
						if($buffer==false){
							continue;
						}
						
						parse_str($buffer,$g);
						
						if($g['type'] == 'init'){
						    $this->users[$key]['send_id']=$g['send_id'];
						    $this->users[$key]['name']=$g['name'];
						}
						//如果不为空，则进行消息推送操作
						$this->send($k,$buffer);
					}
				}
			}
			
		}
		
	}
	
	//指定关闭$k对应的socket
	function close($k){
	    //断开相应socket
		socket_close($this->users[$k]['socket']);
		//删除相应的user信息
		unset($this->users[$k]);
		//重新定义sockets连接池
		$this->sockets=array($this->master);
		foreach($this->users as $v){
			$this->sockets[]=$v['socket'];
		}
		//输出日志
		$this->e("key:$k close");
	}
	
	//根据sock在users里面查找相应的$k
	function search($sock){
		foreach ($this->users as $k=>$v){
			if($sock==$v['socket'])
			return $k;
		}
		return false;
	}
	
	//创建一个socket
	function WebSocket($address,$port){
		$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);//1表示接受所有的数据包
		socket_bind($server, $address, $port);
		socket_listen($server);
		
		//在cmd里写出一些信息
		$this->e('Server Started : '.date('Y-m-d H:i:s'));
		$this->e('Listening on   : '.$address.' port '.$port);
		return $server;
	}
	
	
	function woshou($k,$buffer){
// 	    echo "my say";
		$buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
		$key  = trim(substr($buf,0,strpos($buf,"\r\n")));
	
		$new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
		
		$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$new_message .= "Upgrade: websocket\r\n";
		$new_message .= "Sec-WebSocket-Version: 13\r\n";
		$new_message .= "Connection: Upgrade\r\n";
		$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
		
		socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
		$this->users[$k]['shou']=true;
		return true;
		
	}
	
	function uncode($str,$key){
		$mask = array();  
		$data = '';  
		$msg = unpack('H*',$str);
		$head = substr($msg[1],0,2);  
		if ($head == '81' && !isset($this->slen[$key])) {  
			$len=substr($msg[1],2,2);
			$len=hexdec($len);
			if(substr($msg[1],2,2)=='fe'){
				$len=substr($msg[1],4,4);
				$len=hexdec($len);
				$msg[1]=substr($msg[1],4);
			}else if(substr($msg[1],2,2)=='ff'){
				$len=substr($msg[1],4,16);
				$len=hexdec($len);
				$msg[1]=substr($msg[1],16);
			}
			$mask[] = hexdec(substr($msg[1],4,2));  
			$mask[] = hexdec(substr($msg[1],6,2));  
			$mask[] = hexdec(substr($msg[1],8,2));  
			$mask[] = hexdec(substr($msg[1],10,2));
			$s = 12;
			$n=0;
		}else if($this->slen[$key] > 0){
			$len=$this->slen[$key];
			$mask=$this->ar[$key];
			$n=$this->n[$key];
			$s = 0;
		}
		
		$e = strlen($msg[1])-2;
		for ($i=$s; $i<= $e; $i+= 2) {  
			$data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));  
			$n++;  
		}  
		$dlen=strlen($data);
		
		if($len > 255 && $len > $dlen+intval($this->sjen[$key])){
			$this->ar[$key]=$mask;
			$this->slen[$key]=$len;
			$this->sjen[$key]=$dlen+intval($this->sjen[$key]);
			$this->sda[$key]=$this->sda[$key].$data;
			$this->n[$key]=$n;
			return false;
		}else{
			unset($this->ar[$key],$this->slen[$key],$this->sjen[$key],$this->n[$key]);
			$data=$this->sda[$key].$data;
			unset($this->sda[$key]);
			return $data;
		}
		
	}
	
	
	function code($msg){
		$frame = array();  
		$frame[0] = '81';  
		$len = strlen($msg);
		if($len < 126){
			$frame[1] = $len<16?'0'.dechex($len):dechex($len);
		}else if($len < 65025){
			$s=dechex($len);
			$frame[1]='7e'.str_repeat('0',4-strlen($s)).$s;
		}else{
			$s=dechex($len);
			$frame[1]='7f'.str_repeat('0',16-strlen($s)).$s;
		}
		$frame[2] = $this->ord_hex($msg);  
		$data = implode('',$frame);  
		return pack("H*", $data);  
	}
	
	function ord_hex($data)  {  
		$msg = '';  
		$l = strlen($data);  
		for ($i= 0; $i<$l; $i++) {  
			$msg .= dechex(ord($data{$i}));  
		}  
		return $msg;  
	}
	
	//用户加入
	function send($k,$msg){
	    //echo "in ";
	    //echo "k is ".$k;
	    //echo "msg is ".$msg;
		parse_str($msg,$g);
		$ar=array();
		if($g['type']=='init'){
		    /*
		     * $k :我在socket池里对应的id 或 key
		     * */
			$this->users[$k]['send_id']=$g['send_id'];
			$ar['type']='init';
			$ar['send_id']=$g['send_id'];
			$ar['name']=$g['name'];
			$this->init($k,$ar);
		}else{
		    $to_id = $g['to_id'];
		    $key = $this->findUser($to_id);
			$ar['nrong']=$g['nrong'];
			//$key=$g['key'];
			$this->send1($k,$ar,$key);
		}
		
	}
	
	/**
	 * 
	 * @param 初始化用户的id $key
	 * @param 返回给初始化用户的内容 $ar
	 */
	function init($key,$ar){
	    //找到所有在线的用户返回
	    $arr = array();
	    foreach ($this->users as $k=>$v){
	        if($key == $k){
	            continue;
	        }
	        $arr[] = array(
	            'key'=>$k,
	            //其他用户的id
	            'other_id'  =>$v['send_id'],
	            'other_name'=>$v['name'],
	            'type'      =>'init',
	            'time'      =>date('m-d H:i:s')
	        );
	    }
	    $ar['users']=$arr;
	    $str = $this->code(json_encode($ar,JSON_UNESCAPED_UNICODE));
	    socket_write($this->users[$key]['socket'], $str,strlen($str));
	    
	    //群发
	    $users=$this->users;
	    
	    echo $users[$key];
	    $qunfa = array(
	        'key'=>$key,
	        'other_id' =>$users[$key]['send_id'],
	        'other_name'=>$users[$key]['name'],
            'type'      =>'init_qun',
            'time'      =>date('m-d H:i:s')
	    );
	    $qunfa = $this->code(json_encode($qunfa,JSON_UNESCAPED_UNICODE));
	    unset($users[$key]);
	    foreach($users as $v){
	        echo "qunfa";
	        socket_write($v['socket'],$qunfa,strlen($qunfa));
	    }
	}
	
	//查找接收者的key
	function findUser($to_id){
	    foreach ($this->users as $k=>$v){
	       // echo " k is ".$k;
	      //  echo "find id is ".$to_id;
	      //  echo " v-send_id is ".$v['send_id'];
// 	        echo " v-to_id ".$v['to_id'];
	        
	        if($v['send_id'] == $to_id){
	           // echo " return k is ".$k;
	            return $k;
	        }
	    }
	}
	
	function getusers(){
		$ar=array();
		foreach($this->users as $k=>$v){
			$ar[]=array('code'=>$k,'name'=>$v['name']);
		}
		return $ar;
	}
	
	//$k 发信息人的code $key接受人的 code
	function send1($k,$ar,$key='all'){
		$ar['code1']=$key;    //接受人
		$ar['code']=$k;       //发信息人
		$ar['time']=date('m-d H:i:s');
		$str = $this->code(json_encode($ar));
		//群发
		if($key=='all'){
			$users=$this->users;
			if($ar['type']=='add'){
				$ar['type']='madd';
				$ar['users']=$this->getusers();
				$str1 = $this->code(json_encode($ar));
				//echo iconv('utf-8','gbk//IGNORE',"str1 is ".$str1);
				 
				socket_write($users[$k]['socket'],$str1,strlen($str1));
				unset($users[$k]);
			}
			foreach($users as $v){
				socket_write($v['socket'],$str,strlen($str));
			}
		}else{
			socket_write($this->users[$k]['socket'],$str,strlen($str));
			socket_write($this->users[$key]['socket'],$str,strlen($str));
		}
	}
	
	//用户退出
	function send2($k){
		$this->close($k);
		$users=$this->users;
		$ar['type']='rmove';
		$ar['other_id']=$users[$k]['send_id'];
		$ar['other_name'] = $users[$k]['name'];
		
		$this->send1(false,$ar,'all');
	}
	
	//向客户的打印信息
	function e($str){
		//$path=dirname(__FILE__).'/log.txt';
		$str=$str."\n";
		//error_log($str,3,$path);
		echo iconv('utf-8','gbk//IGNORE',$str);
	}
}
?>
