<?php
//ini_set("max_execution_time", "5000");
header('Content-Type: text/html; charset=utf-8');

include_once('crypt_php.php');


$server_b = "http://a98867w4.bget.ru/all/s_radoid/server_b";//Путь к скриптам другого сервера

$id = '7';//Порядковый id пакета
$notice = "lolololololo";//Текст, который нужно передать


$send = new_event($id, $notice, $server_b);

if($send)
	echo 'Сообщение доставлено';
else
	echo 'Сообщение не доставлено';



?>