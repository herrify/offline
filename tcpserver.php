<?php  
//ȷ�������ӿͻ���ʱ���ᳬʱ  
set_time_limit(0);  
//����IP�Ͷ˿ں�  
$address = "54.223.34.241";  
$port = 12260;
/** 
 * ����һ��SOCKET  
 * AF_INET=��ipv4 �����ipv6�������Ϊ AF_INET6 
 * SOCK_STREAMΪsocket��tcp���ͣ������UDP��ʹ��SOCK_DGRAM 
*/  
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() fail:" . socket_strerror(socket_last_error()) . "/n");  
//����ģʽ  
//socket_set_block($sock) or die("socket_set_block() fail:" . socket_strerror(socket_last_error()) . "/n");  
//�󶨵�socket�˿�  
//$result = socket_bind($sock, $address, $port) or die("socket_bind() fail:" . socket_strerror(socket_last_error()) . "/n");
$connection = socket_connect($sock, '54.223.34.241', 12260) or die("Could not connet server\n");
//��ʼ����  
$result = socket_listen($sock, 4) or die("socket_listen() fail:" . socket_strerror(socket_last_error()) . "/n");  
echo "OK\nBinding the socket on $address:$port ... ";  
echo "OK\nNow ready to accept connections.\nListening on the socket ... \n";  
do { // never stop the daemon  
    //�������������󲢵���һ��������Socket������ͻ��˺ͷ����������Ϣ  
    $msgsock = socket_accept($sock) or  die("socket_accept() failed: reason: " . socket_strerror(socket_last_error()) . "/n");  
    while(1){
		//��ȡ�ͻ�������  
		echo "Read client data \n";  
		//socket_read������һֱ��ȡ�ͻ�������,ֱ������\n,\t����\0�ַ�.PHP�ű�����д�ַ�����������Ľ�����.  
		$buf = socket_read($msgsock, 8192);  
		echo "Received msg: $buf   \n";

		if($buf == "bye"){
			//���յ�������Ϣ���ر����ӣ��ȴ���һ������
			socket_close($msgsock);
			continue;
		}
		  
		//���ݴ��� ��ͻ���д�뷵�ؽ��  
		$msg = "welcome \n";  
		socket_write($msgsock, $msg, strlen($msg)) or die("socket_write() failed: reason: " . socket_strerror(socket_last_error()) ."/n");  		
	}  
      
} while (true);  
socket_close($sock);  
?>